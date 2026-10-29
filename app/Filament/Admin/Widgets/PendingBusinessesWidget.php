<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Businesses\BusinessResource;
use App\Models\Business;
use Filament\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingBusinessesWidget extends TableWidget
{
    protected static ?string $heading = 'Cola de aprobación';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Business::query()
                    ->where('status', 'pending')
                    ->latest()
            )
            ->paginated([8])
            ->defaultPaginationPageOption(8)
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (Business $record) => $record->logoUrl())
                    ->getStateUsing(fn (Business $record) => $record->logo_path ?: $record->logoUrl()),
                TextColumn::make('name')->label('Negocio')->searchable(false),
                TextColumn::make('department')->label('Depto.'),
                TextColumn::make('category.name')->label('Categoría'),
                TextColumn::make('created_at')->label('Solicitó')->since(),
            ])
            ->recordActions([
                Action::make('review')
                    ->label('Revisar')
                    ->url(fn (Business $record) => BusinessResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('Sin pendientes')
            ->emptyStateDescription('No hay negocios esperando aprobación.');
    }
}
