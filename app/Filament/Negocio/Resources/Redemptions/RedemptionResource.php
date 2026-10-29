<?php

namespace App\Filament\Negocio\Resources\Redemptions;

use App\Filament\Negocio\Resources\Redemptions\Pages\ListRedemptions;
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

    protected static string|UnitEnum|null $navigationGroup = 'Cupones';

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
                TextColumn::make('redeemed_at')
                    ->label('Fecha')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('coupon.code')
                    ->label('Cupón')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('coupon.title')
                    ->label('Oferta')
                    ->limit(40)
                    ->toggleable(),
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
                    })
                    ->color('success'),
                TextColumn::make('notes')
                    ->label('Notas')
                    ->placeholder('—')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('coupon_id')
                    ->label('Cupón')
                    ->relationship(
                        'coupon',
                        'code',
                        fn (Builder $query) => $query->where('business_id', auth()->user()?->business_id),
                    )
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
            ->emptyStateDescription('Cuando registres un canje desde Cupones, aparecerá en este historial.')
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentCheck);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('business_id', auth()->user()?->business_id);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
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
