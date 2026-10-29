<?php

namespace App\Filament\Admin\Resources\Businesses;

use App\Filament\Admin\Resources\Businesses\Pages\EditBusiness;
use App\Filament\Admin\Resources\Businesses\Pages\ListBusinesses;
use App\Filament\Admin\Resources\Businesses\RelationManagers\BranchesRelationManager;
use App\Filament\Admin\Resources\Businesses\RelationManagers\CouponsRelationManager;
use App\Filament\Admin\Resources\Businesses\RelationManagers\UsersRelationManager;
use App\Models\Business;
use App\Notifications\BusinessApprovedNotification;
use App\Notifications\BusinessRejectedNotification;
use App\Support\Audit;
use App\Support\SiteConfig;
use App\Support\StoredUpload;
use Illuminate\Support\Str;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static ?string $modelLabel = 'negocio';

    protected static ?string $pluralModelLabel = 'negocios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Moderación';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = Business::query()->where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        $departments = SiteConfig::departments();

        return $schema->components([
            Section::make('Perfil del negocio')
                ->schema([
                    TextInput::make('name')->label('Nombre')->required()->maxLength(120),
                    FileUpload::make('logo_path')
                        ->label('Logo')
                        ->image()
                        ->directory('logos')
                        ->disk('public')
                        ->visibility('public')
                        ->imageEditor()
                        ->imageEditorAspectRatios(['1:1'])
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->fetchFileInformation(false)
                        ->getUploadedFileNameForStorageUsing(
                            fn ($file): string => Str::ulid().'.'.StoredUpload::extensionFor($file)
                        ),
                    Textarea::make('description')->label('Descripción')->rows(3)->columnSpanFull(),
                    TextInput::make('phone')->label('Teléfono'),
                    TextInput::make('website')->label('Sitio web')->url(),
                    TextInput::make('email_domain')->label('Dominio de correo'),
                    TextInput::make('ruc')->label('RUC'),
                    Select::make('department')
                        ->label('Departamento')
                        ->options(array_combine($departments, $departments))
                        ->required()
                        ->searchable(),
                    TextInput::make('city')->label('Ciudad'),
                    TextInput::make('address')->label('Dirección')->columnSpanFull(),
                    Select::make('category_id')
                        ->label('Categoría')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload(),
                ])
                ->columns(2),
            Section::make('Estado y plan')
                ->schema([
                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'pending' => 'Pendiente',
                            'approved' => 'Aprobado',
                            'rejected' => 'Rechazado',
                        ])
                        ->required(),
                    Select::make('plan')
                        ->label('Plan')
                        ->options([
                            'free' => 'Gratis',
                            'featured' => 'Destacado',
                        ])
                        ->required()
                        ->default('free'),
                    Toggle::make('is_featured')->label('Mostrar como destacado'),
                    DateTimePicker::make('featured_until')
                        ->label('Destacado hasta')
                        ->native(false),
                    Textarea::make('rejection_reason')
                        ->label('Motivo de rechazo')
                        ->rows(2)
                        ->columnSpanFull(),
                    Textarea::make('notes')
                        ->label('Notas internas')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category']))
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (Business $record) => $record->logoUrl())
                    ->getStateUsing(fn (Business $record) => $record->logo_path ?: $record->logoUrl()),
                TextColumn::make('name')->label('Negocio')->searchable()->sortable(),
                TextColumn::make('department')->label('Depto.')->sortable()->toggleable(),
                TextColumn::make('category.name')->label('Categoría')->toggleable(),
                TextColumn::make('email_domain')->label('Dominio')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('plan')
                    ->label('Plan')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'featured' ? 'warning' : 'gray')
                    ->formatStateUsing(fn (string $state): string => $state === 'featured' ? 'Destacado' : 'Gratis'),
                IconColumn::make('is_featured')->label('★')->boolean(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Aprobado',
                        'pending' => 'Pendiente',
                        'rejected' => 'Rechazado',
                        default => $state,
                    }),
                TextColumn::make('created_at')->label('Registro')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                    ]),
                SelectFilter::make('department')
                    ->label('Departamento')
                    ->options(fn () => array_combine(SiteConfig::departments(), SiteConfig::departments())),
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),
                SelectFilter::make('plan')
                    ->label('Plan')
                    ->options([
                        'free' => 'Gratis',
                        'featured' => 'Destacado',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Aprobar')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (Business $record) => $record->status !== 'approved')
                    ->requiresConfirmation()
                    ->action(function (Business $record): void {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                            'rejection_reason' => null,
                            'rejected_at' => null,
                            'rejected_by' => null,
                        ]);

                        Audit::log('business.approved', $record);

                        $record->users()->each(function ($user) use ($record): void {
                            $user->notify(new BusinessApprovedNotification($record));
                        });

                        Notification::make()->title('Negocio aprobado')->success()->send();
                    }),
                Action::make('reject')
                    ->label('Rechazar')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (Business $record) => $record->status !== 'rejected')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motivo del rechazo')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Business $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'rejected_at' => now(),
                            'rejected_by' => auth()->id(),
                            'approved_at' => null,
                            'approved_by' => null,
                        ]);

                        Audit::log('business.rejected', $record, [
                            'reason' => $data['rejection_reason'],
                        ]);

                        $record->users()->each(function ($user) use ($record): void {
                            $user->notify(new BusinessRejectedNotification($record));
                        });

                        Notification::make()->title('Negocio rechazado')->warning()->send();
                    }),
                EditAction::make(),
            ])
            ->emptyStateHeading('Sin negocios')
            ->emptyStateDescription('Cuando un comercio se registre aparecerá aquí.');
    }

    public static function getRelations(): array
    {
        return [
            CouponsRelationManager::class,
            UsersRelationManager::class,
            BranchesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinesses::route('/'),
            'edit' => EditBusiness::route('/{record}/edit'),
        ];
    }
}
