<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Coupons\CouponResource;
use App\Models\Coupon;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ExpiringCouponsWidget extends TableWidget
{
    protected static ?string $heading = 'Cupones por vencer (7 días)';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Coupon::query()
                    ->with('business')
                    ->where('status', 'active')
                    ->whereNull('force_expired_at')
                    ->whereBetween('expires_at', [now()->toDateString(), now()->addDays(7)->toDateString()])
                    ->orderBy('expires_at')
                    ->limit(8)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('code')->label('Código'),
                TextColumn::make('title')->label('Título')->limit(36),
                TextColumn::make('business.name')->label('Negocio'),
                TextColumn::make('expires_at')->label('Vence')->date('d M Y')->color('warning'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Abrir')
                    ->url(fn (Coupon $record) => CouponResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('Nada por vencer')
            ->emptyStateDescription('No hay cupones activos que caduquen esta semana.');
    }
}
