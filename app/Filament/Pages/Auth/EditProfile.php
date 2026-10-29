<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use SensitiveParameter;

class EditProfile extends BaseEditProfile
{
    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Mi perfil';
    }

    public static function getLabel(): string
    {
        return 'Mi perfil';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos personales')
                ->description('Así te ven los demás administradores y el equipo.')
                ->schema([
                    SchemaView::make('filament.forms.avatar-preview')
                        ->viewData(fn (): array => [
                            'url' => $this->getUser()->getFilamentAvatarUrl(),
                            'name' => $this->getUser()->getFilamentName(),
                        ]),
                    $this->getAvatarFormComponent(),
                    $this->getNameFormComponent()
                        ->label('Nombre completo'),
                    $this->getEmailFormComponent()
                        ->label('Correo electrónico'),
                    $this->getPhoneFormComponent(),
                ])
                ->columns(1),
            Section::make('Cambiar contraseña')
                ->description('Dejá estos campos vacíos si no querés cambiarla. Si la cambiás, te pedimos la actual.')
                ->schema([
                    $this->getPasswordFormComponent()
                        ->label('Nueva contraseña'),
                    $this->getPasswordConfirmationFormComponent()
                        ->label('Confirmar nueva contraseña'),
                    $this->getCurrentPasswordFormComponent()
                        ->label('Contraseña actual'),
                ]),
        ]);
    }

    protected function getAvatarFormComponent(): Component
    {
        return FileUpload::make('avatar_path')
            ->label('Cambiar foto')
            ->image()
            ->disk('public')
            ->directory('avatars')
            ->visibility('public')
            ->imageEditor()
            ->imageEditorAspectRatios(['1:1'])
            ->imageResizeMode('cover')
            ->imageCropAspectRatio('1:1')
            ->imageResizeTargetWidth('400')
            ->imageResizeTargetHeight('400')
            ->maxSize(2048)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->helperText('Subí una nueva imagen solo si querés reemplazar la actual. JPG, PNG o WebP · máx. 2 MB.')
            ->deleteUploadedFileUsing(function (?string $file): void {
                if (filled($file) && Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            });
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label('Teléfono')
            ->tel()
            ->maxLength(30)
            ->placeholder('+505 8888 0000');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Avoid hydrating FilePond with the stored path (known endless-spinner bug).
        unset($data['avatar_path']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(#[SensitiveParameter] array $data): array
    {
        if (! array_key_exists('avatar_path', $data) || blank($data['avatar_path'])) {
            unset($data['avatar_path']);
        } else {
            $previous = $this->getUser()->getAttribute('avatar_path');
            if (filled($previous) && $previous !== $data['avatar_path'] && Storage::disk('public')->exists($previous)) {
                Storage::disk('public')->delete($previous);
            }
        }

        return parent::mutateFormDataBeforeSave($data);
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Guardar cambios')
            ->color('success');
    }

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('Volver')
            ->color('gray')
            ->outlined();
    }
}
