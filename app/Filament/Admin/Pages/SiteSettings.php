<?php

namespace App\Filament\Admin\Pages;

use App\Support\Audit;
use App\Support\SiteConfig;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    protected static ?string $navigationLabel = 'Configuración';

    protected static ?string $title = 'Configuración del sitio';

    protected static ?int $navigationSort = 1;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function mount(): void
    {
        $all = SiteConfig::all();

        $this->form->fill([
            'departments' => array_values($all['departments'] ?? []),
            'free_email_domains' => implode("\n", $all['free_email_domains'] ?? []),
            'channels' => collect($all['channels'] ?? [])
                ->map(fn ($label, $key) => ['key' => $key, 'label' => $label])
                ->values()
                ->all(),
            'payment_methods' => collect($all['payment_methods'] ?? [])
                ->map(fn ($label, $key) => ['key' => $key, 'label' => $label])
                ->values()
                ->all(),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $departmentCatalog = config('descuentonic.departments', []);

        return $schema->components([
            Section::make('Departamentos')
                ->description('Marcá solo los departamentos donde DescuentoNic opera. Aparecen en el registro de negocios y en los filtros del catálogo.')
                ->schema([
                    CheckboxList::make('departments')
                        ->label('Departamentos activos')
                        ->options(array_combine($departmentCatalog, $departmentCatalog))
                        ->columns([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                        ->bulkToggleable()
                        ->required()
                        ->helperText('Si desmarcás uno, deja de ofrecerse en formularios y filtros. Los negocios ya guardados con ese departamento no se borran.'),
                ]),
            Section::make('Dominios de correo bloqueados')
                ->description('Correos gratuitos/temporales que no se aceptan en el alta de negocios. Uno por línea.')
                ->schema([
                    Textarea::make('free_email_domains')->label('Dominios')->rows(8)->required(),
                ]),
            Section::make('Canales de venta')
                ->description('Opciones que el comercio elige al publicar un cupón (tienda, delivery, etc.).')
                ->schema([
                    Repeater::make('channels')
                        ->label('Canales')
                        ->schema([
                            TextInput::make('key')
                                ->label('Clave técnica')
                                ->required()
                                ->helperText('Identificador interno estable, en minúsculas y sin espacios. Ej: store, delivery, whatsapp.'),
                            TextInput::make('label')
                                ->label('Nombre visible')
                                ->required()
                                ->helperText('Texto que ve el usuario. Ej: En tienda física, Delivery.'),
                        ])
                        ->columns(2)
                        ->reorderable(false)
                        ->addActionLabel('Agregar canal')
                        ->helperText('La clave se guarda en la base de datos; el nombre visible es solo para la interfaz. No cambies claves ya usadas o los cupones antiguos pueden dejar de filtrarse bien.'),
                ]),
            Section::make('Medios de pago')
                ->description('Formas de pago aceptadas que el comercio marca en cada cupón.')
                ->schema([
                    Repeater::make('payment_methods')
                        ->label('Medios')
                        ->schema([
                            TextInput::make('key')
                                ->label('Clave técnica')
                                ->required()
                                ->helperText('Identificador interno. Ej: cash, card, transfer.'),
                            TextInput::make('label')
                                ->label('Nombre visible')
                                ->required()
                                ->helperText('Texto para la interfaz. Ej: Efectivo, Tarjeta.'),
                        ])
                        ->columns(2)
                        ->reorderable(false)
                        ->addActionLabel('Agregar medio')
                        ->helperText('Igual que canales: clave = dato guardado; nombre visible = lo que se muestra al público y al comercio.'),
                ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        SiteConfig::save($state);
        Audit::log('settings.updated', null, ['keys' => array_keys($state)]);

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Guardar cambios')
                            ->submit('save'),
                    ]),
                ]),
        ]);
    }
}
