<?php

namespace App\Filament\Negocio\Pages;

use App\Models\Business;
use App\Support\SiteConfig;
use App\Support\StoredUpload;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class BusinessProfile extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Negocio';

    protected static ?string $navigationLabel = 'Mi negocio';

    protected static ?string $title = 'Mi negocio';

    protected static ?int $navigationSort = 0;

    protected ?string $heading = 'Perfil del negocio';

    protected ?string $subheading = 'Actualizá la información que ven los clientes y el equipo de DescuentoNic.';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->business_id;
    }

    public function mount(): void
    {
        $business = $this->business();

        abort_unless($business, 404);

        $data = $business->attributesToArray();
        unset($data['logo_path']);

        $this->form->fill($data);
    }

    public function business(): ?Business
    {
        return auth()->user()?->business?->fresh() ?? auth()->user()?->business;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->model($this->business())
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $departments = SiteConfig::departments();
        $business = $this->business();

        return $schema->components([
            Section::make('Identidad')
                ->schema([
                    SchemaView::make('filament.forms.business-logo-preview')
                        ->viewData(fn (): array => [
                            'url' => $business?->logoUrl(),
                            'name' => $business?->name ?? 'Tu negocio',
                            'status' => $business?->status,
                        ]),
                    FileUpload::make('logo_path')
                        ->label('Cambiar logo')
                        ->image()
                        ->disk('public')
                        ->directory('logos')
                        ->visibility('public')
                        ->imageEditor()
                        ->imageEditorAspectRatios(['1:1'])
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('1:1')
                        ->imageResizeTargetWidth('400')
                        ->imageResizeTargetHeight('400')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->getUploadedFileNameForStorageUsing(function ($file): string {
                            return \Illuminate\Support\Str::ulid().'.'.StoredUpload::extensionFor($file);
                        })
                        ->helperText('Subí una imagen solo si querés reemplazar el logo. Ideal 400×400 px.'),
                    TextInput::make('name')
                        ->label('Nombre del negocio')
                        ->required()
                        ->maxLength(120),
                    Textarea::make('description')
                        ->label('Descripción')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Contacto y ubicación')
                ->schema([
                    TextInput::make('phone')->label('Teléfono')->tel(),
                    TextInput::make('website')->label('Sitio web')->url(),
                    Select::make('department')
                        ->label('Departamento')
                        ->options(array_combine($departments, $departments))
                        ->required()
                        ->searchable(),
                    TextInput::make('city')->label('Ciudad'),
                    TextInput::make('address')->label('Dirección')->columnSpanFull(),
                    Select::make('category_id')
                        ->label('Categoría')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload(),
                ])
                ->columns(2),
        ]);
    }

    public function save(): void
    {
        $business = $this->business();

        abort_unless($business, 404);

        $data = $this->form->getState();

        if (! array_key_exists('logo_path', $data) || blank($data['logo_path'])) {
            unset($data['logo_path']);
        } else {
            $data['logo_path'] = StoredUpload::ensureExtensionMatchesMime($data['logo_path']);
            $previous = $business->logo_path;
            if (filled($previous) && $previous !== $data['logo_path'] && Storage::disk('public')->exists($previous)) {
                Storage::disk('public')->delete($previous);
            }
        }

        unset(
            $data['status'],
            $data['plan'],
            $data['is_featured'],
            $data['featured_until'],
            $data['notes'],
            $data['rejection_reason'],
            $data['approved_at'],
            $data['approved_by'],
            $data['rejected_at'],
            $data['rejected_by'],
        );

        $business->update($data);
        auth()->user()?->unsetRelation('business');

        Notification::make()
            ->title('Negocio actualizado')
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
                            ->color('success')
                            ->submit('save'),
                    ]),
                ]),
        ]);
    }
}
