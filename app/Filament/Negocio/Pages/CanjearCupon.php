<?php

namespace App\Filament\Negocio\Pages;

use App\Models\Coupon;
use App\Services\RedeemCoupon;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class CanjearCupon extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static string|UnitEnum|null $navigationGroup = 'Cupones';

    protected static ?string $navigationLabel = 'Canjear';

    protected static ?string $title = 'Canjear cupón';

    protected static ?string $slug = 'canjear';

    protected static ?int $navigationSort = 0;

    protected ?string $heading = 'Canjear cupón';

    protected ?string $subheading = 'Escaneá el QR del cliente o escribí el código, y pedí su teléfono.';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public ?string $code = null;

    public static function getRoutePath(\Filament\Panel $panel): string
    {
        return '/canjear/{code?}';
    }

    public static function getRelativeRouteName(\Filament\Panel $panel): string
    {
        return 'canjear';
    }

    public static function getNavigationItemActiveRoutePattern(): string
    {
        return static::getRouteName().'*';
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->business_id;
    }

    public function mount(?string $code = null): void
    {
        $normalized = $code ? strtoupper(preg_replace('/\s+/', '', $code) ?? '') : null;

        $this->code = $normalized;
        $this->form->fill([
            'code' => $normalized,
            'customer_phone' => null,
            'notes' => null,
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos del canje')
                ->description('El cliente muestra el código o el QR. Vos pedís el teléfono para que no canjee dos veces.')
                ->schema([
                    TextInput::make('code')
                        ->label('Código del cupón')
                        ->required()
                        ->live(onBlur: true)
                        ->autocomplete(false)
                        ->extraInputAttributes(['style' => 'text-transform:uppercase; letter-spacing:0.08em; font-weight:700;'])
                        ->helperText('Escaneá el QR o escribí el código que muestra el cliente.'),
                    TextInput::make('customer_phone')
                        ->label('Teléfono del cliente')
                        ->tel()
                        ->required()
                        ->helperText('Obligatorio. Cada número solo puede canjear este cupón una vez.'),
                    TextInput::make('notes')
                        ->label('Nota (opcional)')
                        ->maxLength(120),
                ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            SchemaView::make('filament.negocio.pages.canjear-hint')
                ->viewData(fn (): array => [
                    'preview' => $this->couponPreview(),
                ]),
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('redeem')
                ->footer([
                    Actions::make([
                        Action::make('redeem')
                            ->label('Registrar canje')
                            ->icon(Heroicon::OutlinedCheck)
                            ->color('success')
                            ->submit('redeem'),
                    ]),
                ]),
        ]);
    }

    public function redeem(): void
    {
        $businessId = auth()->user()?->business_id;

        abort_unless($businessId, 403);

        $data = $this->form->getState();
        $source = filled($this->code) ? 'qr' : 'in_store';

        try {
            $result = app(RedeemCoupon::class)->handleForBusiness(
                $businessId,
                $data['code'],
                $data['customer_phone'],
                $source,
                $data['notes'] ?? null,
            );

            $coupon = $result['coupon'];

            Notification::make()
                ->title('Canje registrado')
                ->body($coupon->code.' · quedan '.$coupon->remainingRedemptions().' de '.$coupon->max_redemptions)
                ->success()
                ->send();

            $this->form->fill([
                'code' => null,
                'customer_phone' => null,
                'notes' => null,
            ]);
            $this->code = null;
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?: 'No se pudo registrar el canje.';

            Notification::make()
                ->title($message)
                ->danger()
                ->send();
        }
    }

    /**
     * @return array{title: string, discount: string, remaining: int, max: int}|null
     */
    protected function couponPreview(): ?array
    {
        $businessId = auth()->user()?->business_id;
        $code = $this->data['code'] ?? $this->code;

        if (! $businessId || blank($code)) {
            return null;
        }

        $normalized = strtoupper(preg_replace('/\s+/', '', (string) $code) ?? '');

        $coupon = Coupon::query()
            ->where('business_id', $businessId)
            ->whereRaw('UPPER(code) = ?', [$normalized])
            ->first();

        if (! $coupon) {
            return null;
        }

        return [
            'title' => $coupon->title,
            'discount' => $coupon->discountLabel(),
            'remaining' => $coupon->remainingRedemptions(),
            'max' => $coupon->max_redemptions,
        ];
    }
}
