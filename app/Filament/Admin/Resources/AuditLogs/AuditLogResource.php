<?php

namespace App\Filament\Admin\Resources\AuditLogs;

use App\Filament\Admin\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $modelLabel = 'evento';

    protected static ?string $pluralModelLabel = 'auditoría';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Cuándo')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('user.name')->label('Quién')->placeholder('Sistema'),
                TextColumn::make('action')
                    ->label('Acción')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'business.approved' => 'Negocio aprobado',
                        'business.rejected' => 'Negocio rechazado',
                        'coupon.paused' => 'Cupón pausado',
                        'coupon.activated' => 'Cupón activado',
                        'coupon.force_expired' => 'Cupón vencido',
                        'user.activated' => 'Usuario activado',
                        'user.deactivated' => 'Usuario desactivado',
                        'impersonation.start' => 'Impersonación iniciada',
                        'impersonation.stop' => 'Impersonación finalizada',
                        'settings.updated' => 'Configuración actualizada',
                        default => $state,
                    }),
                TextColumn::make('subject_type')
                    ->label('Sujeto')
                    ->formatStateUsing(fn (?string $state, AuditLog $record): string => $state
                        ? class_basename($state).'#'.$record->subject_id
                        : '—'),
                TextColumn::make('meta')
                    ->label('Detalle')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : '—')
                    ->limit(60)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Acción')
                    ->options([
                        'business.approved' => 'Negocio aprobado',
                        'business.rejected' => 'Negocio rechazado',
                        'coupon.paused' => 'Cupón pausado',
                        'coupon.activated' => 'Cupón activado',
                        'coupon.force_expired' => 'Cupón vencido',
                        'impersonation.start' => 'Impersonación',
                        'settings.updated' => 'Configuración',
                    ]),
            ])
            ->emptyStateHeading('Sin eventos')
            ->emptyStateDescription('Las acciones de moderación quedarán registradas aquí.');
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
            'index' => ListAuditLogs::route('/'),
        ];
    }
}
