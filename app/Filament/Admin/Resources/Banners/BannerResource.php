<?php

namespace App\Filament\Admin\Resources\Banners;

use App\Filament\Admin\Resources\Banners\Pages\CreateBanner;
use App\Filament\Admin\Resources\Banners\Pages\EditBanner;
use App\Filament\Admin\Resources\Banners\Pages\ListBanners;
use App\Models\Banner;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $modelLabel = 'banner';

    protected static ?string $pluralModelLabel = 'banners';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Contenido')
                ->schema([
                    TextInput::make('kicker')->label('Antetítulo')->maxLength(80),
                    TextInput::make('title')->label('Título')->required()->maxLength(160)->columnSpanFull(),
                    Textarea::make('text')->label('Texto')->rows(2)->columnSpanFull(),
                    TextInput::make('cta_label')->label('Texto del botón'),
                    TextInput::make('cta_url')->label('URL del botón')->url(),
                    FileUpload::make('image_path')
                        ->label('Imagen')
                        ->image()
                        ->directory('banners')
                        ->disk('public')
                        ->imageEditor(),
                    TextInput::make('image_url')
                        ->label('URL de imagen (alternativa)')
                        ->url()
                        ->helperText('Si no subís archivo, podés pegar una URL pública.'),
                    Select::make('tone')
                        ->label('Tono del degradado')
                        ->options([
                            'from-rose-700/85 to-rose-900/70' => 'Rosa',
                            'from-indigo-800/85 to-slate-900/70' => 'Índigo',
                            'from-amber-700/80 to-stone-900/70' => 'Ámbar',
                            'from-teal-700/85 to-slate-900/70' => 'Teal',
                            'from-sky-700/85 to-slate-900/70' => 'Cielo',
                        ])
                        ->default('from-rose-700/85 to-rose-900/70')
                        ->required(),
                ])
                ->columns(2),
            Section::make('Programación')
                ->schema([
                    TextInput::make('sort_order')->label('Orden')->numeric()->default(0),
                    Toggle::make('is_active')->label('Activo')->default(true),
                    DateTimePicker::make('starts_at')->label('Inicio')->native(false),
                    DateTimePicker::make('ends_at')->label('Fin')->native(false),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Imagen')
                    ->getStateUsing(fn (Banner $record) => $record->resolvedImageUrl()),
                TextColumn::make('title')->label('Título')->searchable()->limit(40),
                TextColumn::make('sort_order')->label('Orden')->sortable(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
                TextColumn::make('starts_at')->label('Inicio')->dateTime('d M Y')->placeholder('Ya'),
                TextColumn::make('ends_at')->label('Fin')->dateTime('d M Y')->placeholder('Sin fin'),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Activo'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Sin banners')
            ->emptyStateDescription('Creá slides para el carrusel del home.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBanners::route('/'),
            'create' => CreateBanner::route('/create'),
            'edit' => EditBanner::route('/{record}/edit'),
        ];
    }
}
