<?php

namespace App\Filament\Concerns;

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
            $components[] = Select::make('business_id')
                ->relationship('business', 'name')
                ->required()
                ->searchable()
                ->preload();
        }

        $components[] = Section::make('Cupón')
            ->schema([
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(120)
                    ->columnSpanFull(),
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
                        : 'Porcentaje de descuento'),
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
                TextInput::make('code')
                    ->label('Código')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('La plataforma genera el código al guardar.'),
                CheckboxList::make('channels')
                    ->label('Canales de venta')
                    ->options(config('descuentonic.channels'))
                    ->columns(2)
                    ->required(),
                CheckboxList::make('payment_methods')
                    ->label('Medios de pago')
                    ->options(config('descuentonic.payment_methods'))
                    ->columns(2)
                    ->required(),
                Textarea::make('terms')
                    ->label('Condiciones')
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'active' => 'Activo',
                        'paused' => 'Pausado',
                    ])
                    ->default('active')
                    ->required(),
            ])
            ->columns(2);

        return $components;
    }
}
