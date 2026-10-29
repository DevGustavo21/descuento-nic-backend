<?php

namespace App\Filament\Admin\Resources\Redemptions;

use App\Filament\Admin\Resources\Redemptions\Pages\ListRedemptions;
use App\Models\Redemption;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RedemptionResource extends Resource
{
    protected static ?string $model = Redemption::class;

    protected static ?string $modelLabel = 'canje';

    protected static ?string $pluralModelLabel = 'canjes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Operación';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('redeemed_at', 'desc')
            ->columns([
                TextColumn::make('redeemed_at')->label('Fecha')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('coupon.code')->label('Cupón')->searchable()->copyable(),
                TextColumn::make('coupon.title')->label('Oferta')->limit(36)->toggleable(),
                TextColumn::make('business.name')->label('Negocio')->searchable()->sortable(),
                TextColumn::make('customer_phone')
                    ->label('Teléfono')
                    ->placeholder('—')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('source')
                    ->label('Fuente')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_store' => 'En tienda',
                        'qr' => 'QR',
                        'online' => 'Online',
                        default => $state,
                    }),
                TextColumn::make('notes')->label('Notas')->limit(30)->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('business_id')
                    ->label('Negocio')
                    ->relationship('business', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('coupon_id')
                    ->label('Cupón')
                    ->relationship('coupon', 'code')
                    ->searchable()
                    ->preload(),
                Filter::make('redeemed_at')
                    ->form([
                        DatePicker::make('from')->label('Desde')->native(false),
                        DatePicker::make('until')->label('Hasta')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('redeemed_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('redeemed_at', '<=', $date));
                    }),
            ])
            ->emptyStateHeading('Sin canjes')
            ->emptyStateDescription('Cuando un comercio registre un canje aparecerá en este historial.');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRedemptions::route('/'),
        ];
    }
}
