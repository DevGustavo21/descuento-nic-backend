<?php

namespace App\Filament\Concerns;

use App\Support\SiteConfig;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

trait ConfiguresCouponForm
{
    /**
     * @return array<int, mixed>
     */
    public static function couponFormComponents(bool $forAdmin = false): array
    {
        $components = [];

        if ($forAdmin) {
            $components[] = Section::make('Negocio')
                ->schema([
                    Select::make('business_id')
                        ->label('Negocio')
                        ->relationship('business', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                ]);
        }

        $components[] = Section::make('Oferta')
            ->description('Lo que verá el cliente en el catálogo.')
            ->schema([
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(120)
                    ->columnSpanFull()
                    ->placeholder('Ej. 2x1 en combos de almuerzo'),
                Select::make('discount_type')
                    ->label('Tipo de descuento')
                    ->options([
                        'percent' => 'Porcentaje',
                        'amount' => 'Monto en córdobas',
                    ])
                    ->required()
                    ->default('percent')
                    ->live(),
                TextInput::make('discount_value')
                    ->label('Valor')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->helperText(fn ($get) => $get('discount_type') === 'amount'
                        ? 'Monto a descontar en C$'
                        : 'Porcentaje de descuento (1–100)'),
                TextInput::make('code')
                    ->label('Código')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('La plataforma genera el código al guardar.'),
                Textarea::make('terms')
                    ->label('Condiciones')
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('No acumulable con otras promociones. Válido en sucursales participantes.'),
            ])
            ->columns(2);

        $components[] = Section::make('Vigencia y cupos')
            ->schema([
                DatePicker::make('starts_at')
                    ->label('Inicio (opcional)')
                    ->native(false),
                DatePicker::make('expires_at')
                    ->label('Vencimiento')
                    ->required()
                    ->native(false)
                    ->minDate(now()),
                TextInput::make('max_redemptions')
                    ->label('Canjes disponibles')
                    ->numeric()
                    ->required()
                    ->default(100)
                    ->minValue(1),
                Select::make('status')
                    ->label('Estado')
                    ->options($forAdmin
                        ? [
                            'active' => 'Activo',
                            'paused' => 'Pausado',
                            'expired' => 'Vencido (forzado)',
                        ]
                        : [
                            'active' => 'Activo',
                            'paused' => 'Pausado',
                        ])
                    ->default('active')
                    ->required(),
            ])
            ->columns(2);

        $components[] = Section::make('Canales y pagos')
            ->description('Cómo y dónde puede usar el cliente este cupón.')
            ->schema([
                CheckboxList::make('channels')
                    ->label('Canales de venta')
                    ->options(fn () => SiteConfig::channels())
                    ->columns(2)
                    ->required(),
                CheckboxList::make('payment_methods')
                    ->label('Medios de pago')
                    ->options(fn () => SiteConfig::paymentMethods())
                    ->columns(2)
                    ->required(),
            ])
            ->columns(2);

        return $components;
    }
}
