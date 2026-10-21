# DescuentoNic — Backend

API y paneles Filament v5 para [DescuentoNic](https://github.com/DevGustavo21): cupones de descuento en Nicaragua.

## Stack

- Laravel 13
- Filament v5
- SQLite (local) / MySQL o PostgreSQL (producción)

## Paneles

| Ruta | Uso |
|------|-----|
| `/admin` | Moderación (negocios, categorías, cupones) |
| `/negocio` | Registro, login y gestión de cupones del comercio |
| `/api/coupons` | Catálogo público (consumido por el front Next.js) |
| `/api/filters` | Categorías, departamentos, canales y medios de pago |

## Arranque local

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Cuentas de prueba (contraseña: `password`):

- Admin: `admin@descuentonic.com`
- Negocio: `contacto@burgerhouse.com.ni`

## Variables útiles

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000
DB_CONNECTION=sqlite
```

En producción, apuntá `FRONTEND_URL` al dominio del front Next.js para CORS.
