<?php

namespace App\Filament\Admin\Resources\Users;

use App\Filament\Admin\Resources\Users\Pages\CreateUser;
use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Support\Audit;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'usuario';

    protected static ?string $pluralModelLabel = 'usuarios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Operación';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Cuenta')
                ->schema([
                    FileUpload::make('avatar_path')
                        ->label('Foto')
                        ->image()
                        ->directory('avatars')
                        ->disk('public')
                        ->visibility('public')
                        ->imageEditor()
                        ->imageEditorAspectRatios(['1:1'])
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('1:1')
                        ->imageResizeTargetWidth('400')
                        ->imageResizeTargetHeight('400')
                        ->fetchFileInformation(false),
                    TextInput::make('name')->label('Nombre')->required()->maxLength(120),
                    TextInput::make('email')->label('Correo')->email()->required()->unique(ignoreRecord: true),
                    TextInput::make('phone')->label('Teléfono')->tel(),
                    TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create'),
                    Select::make('role')
                        ->label('Rol')
                        ->options([
                            'admin' => 'Superadmin',
                            'support' => 'Soporte',
                            'merchant' => 'Comercio',
                        ])
                        ->required()
                        ->live()
                        ->disabled(fn () => ! auth()->user()?->isAdmin()),
                    Select::make('business_id')
                        ->label('Negocio')
                        ->relationship('business', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn ($get) => $get('role') === 'merchant'),
                    Toggle::make('is_active')->label('Activo')->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (User $record) => $record->getFilamentAvatarUrl()),
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('email')->label('Correo')->searchable()->copyable(),
                TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'support' => 'info',
                        'merchant' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Superadmin',
                        'support' => 'Soporte',
                        'merchant' => 'Comercio',
                        default => $state,
                    }),
                TextColumn::make('business.name')->label('Negocio')->placeholder('—')->searchable(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
                TextColumn::make('email_verified_at')->label('Verificado')->dateTime('d M Y')->placeholder('No')->toggleable(),
                TextColumn::make('created_at')->label('Alta')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        'admin' => 'Superadmin',
                        'support' => 'Soporte',
                        'merchant' => 'Comercio',
                    ]),
                TernaryFilter::make('is_active')->label('Activo'),
            ])
            ->recordActions([
                Action::make('toggleActive')
                    ->label(fn (User $record) => $record->is_active ? 'Desactivar' : 'Activar')
                    ->icon(fn (User $record) => $record->is_active ? Heroicon::OutlinedNoSymbol : Heroicon::OutlinedCheckCircle)
                    ->color(fn (User $record) => $record->is_active ? 'danger' : 'success')
                    ->visible(fn (User $record) => $record->id !== auth()->id())
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Audit::log($record->is_active ? 'user.activated' : 'user.deactivated', $record);
                        Notification::make()
                            ->title($record->is_active ? 'Usuario activado' : 'Usuario desactivado')
                            ->success()
                            ->send();
                    }),
                Action::make('impersonate')
                    ->label('Entrar como')
                    ->icon(Heroicon::OutlinedArrowRightOnRectangle)
                    ->color('gray')
                    ->visible(fn (User $record) => $record->role === 'merchant'
                        && $record->is_active
                        && $record->business_id
                        && auth()->user()?->canAccessAdmin())
                    ->requiresConfirmation()
                    ->modalHeading('Impersonar comercio')
                    ->modalDescription('Vas a entrar al panel de negocios como este usuario. Podés volver con el banner superior.')
                    ->action(function (User $record) {
                        session(['impersonator_id' => auth()->id()]);
                        Audit::log('impersonation.start', $record);
                        Auth::login($record);

                        return redirect('/negocio');
                    }),
                EditAction::make(),
            ])
            ->emptyStateHeading('Sin usuarios')
            ->emptyStateDescription('Creá cuentas de admin, soporte o comercios.');
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public static function canDelete($record): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
