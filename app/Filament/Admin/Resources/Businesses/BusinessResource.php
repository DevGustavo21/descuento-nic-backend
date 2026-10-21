<?php

namespace App\Filament\Admin\Resources\Businesses;

use App\Filament\Admin\Resources\Businesses\Pages\EditBusiness;
use App\Filament\Admin\Resources\Businesses\Pages\ListBusinesses;
use App\Models\Business;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static ?string $modelLabel = 'negocio';

    protected static ?string $pluralModelLabel = 'negocios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nombre')->required(),
            FileUpload::make('logo_path')
                ->label('Logo')
                ->image()
                ->directory('logos')
                ->disk('public')
                ->imageEditor(),
            TextInput::make('phone')->label('Teléfono'),
            TextInput::make('website')->label('Sitio web')->url(),
            TextInput::make('email_domain')->label('Dominio de correo'),
            TextInput::make('ruc')->label('RUC'),
            Select::make('department')
                ->options(array_combine(config('descuentonic.departments'), config('descuentonic.departments')))
                ->required()
                ->searchable(),
            Select::make('category_id')
                ->relationship('category', 'name')
                ->searchable()
                ->preload(),
            Select::make('status')
                ->options([
                    'pending' => 'Pendiente',
                    'approved' => 'Aprobado',
                    'rejected' => 'Rechazado',
                ])
                ->required(),
            Textarea::make('notes')->label('Notas internas')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('department'),
                TextColumn::make('category.name'),
                TextColumn::make('email_domain'),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->since()->label('Registro'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Aprobar')
                    ->visible(fn (Business $record) => $record->status !== 'approved')
                    ->action(function (Business $record): void {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => Carbon::now(),
                        ]);
                    }),
                \Filament\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinesses::route('/'),
            'edit' => EditBusiness::route('/{record}/edit'),
        ];
    }
}
