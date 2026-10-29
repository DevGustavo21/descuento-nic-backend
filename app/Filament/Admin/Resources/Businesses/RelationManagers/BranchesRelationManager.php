<?php

namespace App\Filament\Admin\Resources\Businesses\RelationManagers;

use App\Support\SiteConfig;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'branches';

    protected static ?string $title = 'Sucursales';

    public function form(Schema $schema): Schema
    {
        $departments = SiteConfig::departments();

        return $schema->components([
            TextInput::make('name')->label('Nombre')->required(),
            Select::make('department')
                ->label('Departamento')
                ->options(array_combine($departments, $departments))
                ->required()
                ->searchable(),
            TextInput::make('city')->label('Ciudad'),
            TextInput::make('address')->label('Dirección'),
            TextInput::make('phone')->label('Teléfono'),
            Toggle::make('is_main')->label('Sucursal principal'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre'),
                TextColumn::make('department')->label('Depto.'),
                TextColumn::make('city')->label('Ciudad'),
                TextColumn::make('phone')->label('Teléfono'),
                IconColumn::make('is_main')->label('Principal')->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
