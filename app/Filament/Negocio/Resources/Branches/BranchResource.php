<?php

namespace App\Filament\Negocio\Resources\Branches;

use App\Filament\Negocio\Resources\Branches\Pages\ManageBranches;
use App\Models\BusinessBranch;
use App\Support\SiteConfig;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BranchResource extends Resource
{
    protected static ?string $model = BusinessBranch::class;

    protected static ?string $modelLabel = 'sucursal';

    protected static ?string $pluralModelLabel = 'sucursales';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Negocio';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        $departments = SiteConfig::departments();

        return $schema->components([
            Section::make('Sucursal')
                ->schema([
                    TextInput::make('name')->label('Nombre')->required()->placeholder('Ej. Sucursal Metrocentro'),
                    Select::make('department')
                        ->label('Departamento')
                        ->options(array_combine($departments, $departments))
                        ->required()
                        ->searchable(),
                    TextInput::make('city')->label('Ciudad'),
                    TextInput::make('phone')->label('Teléfono')->tel(),
                    TextInput::make('address')->label('Dirección')->columnSpanFull(),
                    Toggle::make('is_main')->label('Sucursal principal')->inline(false),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('department')->label('Depto.')->badge()->color('gray'),
                TextColumn::make('city')->label('Ciudad')->placeholder('—'),
                TextColumn::make('phone')->label('Teléfono')->placeholder('—'),
                IconColumn::make('is_main')->label('Principal')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nueva sucursal')
                    ->icon(Heroicon::OutlinedPlus)
                    ->color('success')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['business_id'] = auth()->user()?->business_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()->color('gray')->outlined(),
                DeleteAction::make()->color('danger')->outlined(),
            ])
            ->emptyStateHeading('Sin sucursales')
            ->emptyStateDescription('Agregá sucursales para que los clientes sepan dónde canjear.')
            ->emptyStateIcon(Heroicon::OutlinedMapPin)
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Agregar sucursal')
                    ->icon(Heroicon::OutlinedPlus)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['business_id'] = auth()->user()?->business_id;

                        return $data;
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('business_id', auth()->user()?->business_id);
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->business_id;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBranches::route('/'),
        ];
    }
}
