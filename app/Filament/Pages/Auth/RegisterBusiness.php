<?php

namespace App\Filament\Pages\Auth;

use App\Models\Business;
use App\Models\Category;
use App\Rules\CorporateEmail;
use App\Support\SiteConfig;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Database\Eloquent\Model;
use SensitiveParameter;

class RegisterBusiness extends BaseRegister
{
    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent()
                    ->label('Tu nombre'),
                TextInput::make('business_name')
                    ->label('Negocio que representás')
                    ->required()
                    ->maxLength(255),
                Select::make('category_id')
                    ->label('Rubro')
                    ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('department')
                    ->label('Departamento')
                    ->options(fn () => array_combine(SiteConfig::departments(), SiteConfig::departments()))
                    ->required()
                    ->searchable(),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->required()
                    ->maxLength(30),
                TextInput::make('website')
                    ->label('Sitio web (opcional)')
                    ->url()
                    ->maxLength(255),
                $this->getEmailFormComponent()
                    ->label('Correo corporativo')
                    ->helperText('Debe ser @tuempresa.com. No se aceptan Gmail, Outlook ni Yahoo.')
                    ->rule(new CorporateEmail),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return 'Registra tu negocio';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(#[SensitiveParameter] array $data): Model
    {
        $domain = strtolower(explode('@', $data['email'])[1] ?? '');

        $business = Business::create([
            'name' => $data['business_name'],
            'phone' => $data['phone'],
            'department' => $data['department'],
            'website' => $data['website'] ?? null,
            'email_domain' => $domain,
            'category_id' => $data['category_id'] ?? null,
            'status' => 'pending',
        ]);

        unset($data['business_name'], $data['department'], $data['website'], $data['category_id']);

        $data['role'] = 'merchant';
        $data['business_id'] = $business->id;

        return $this->getUserModel()::create($data);
    }
}
