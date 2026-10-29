<?php

namespace App\Filament\Negocio\Widgets;

use App\Filament\Negocio\Resources\Coupons\CouponResource;
use App\Models\Coupon;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ExpiringCouponsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $businessId = auth()->user()?->business_id;

        return $table
            ->heading('Por vencer')
            ->description('Cupones activos que caducan en 7 días.')
            ->query(
                Coupon::query()
                    ->where('business_id', $businessId)
                    ->where('status', 'active')
                    ->whereNull('force_expired_at')
                    ->whereBetween('expires_at', [now()->toDateString(), now()->addDays(7)->toDateString()])
                    ->orderBy('expires_at')
            )
            ->defaultPaginationPageOption(5)
            ->paginated([5])
            ->columns([
                TextColumn::make('code')->label('Código')->badge()->color('gray'),
                TextColumn::make('title')->label('Cupón')->limit(28),
                TextColumn::make('expires_at')->label('Vence')->date('d M')->color('warning'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Editar')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->url(fn (Coupon $record) => CouponResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('Nada por vencer')
            ->emptyStateDescription('Ningún cupón activo caduca esta semana.')
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle);
    }
}
