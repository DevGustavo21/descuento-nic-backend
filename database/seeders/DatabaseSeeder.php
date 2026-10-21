<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\User;
use App\Support\BusinessLogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Restaurantes', 'restaurantes', 'utensils', 1],
            ['Farmacias', 'farmacias', 'pill', 2],
            ['Ferreterías', 'ferreterias', 'hammer', 3],
            ['Supermercados', 'supermercados', 'shopping-cart', 4],
            ['Belleza', 'belleza', 'scissors', 5],
            ['Gimnasios', 'gimnasios', 'dumbbell', 6],
            ['Mascotas', 'mascotas', 'paw-print', 7],
            ['Entretenimiento', 'entretenimiento', 'clapperboard', 8],
            ['Cafeterías', 'cafeterias', 'coffee', 9],
            ['Tecnología', 'tecnologia', 'smartphone', 10],
            ['Educación', 'educacion', 'book-open', 11],
        ];

        $categoryModels = collect($categories)->mapWithKeys(function ($row) {
            $model = Category::create([
                'name' => $row[0],
                'slug' => $row[1],
                'icon' => $row[2],
                'sort_order' => $row[3],
            ]);

            return [$row[0] => $model];
        });

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@descuentonic.com',
            'role' => 'admin',
            'phone' => '+505 8888 0000',
        ]);

        $deals = [
            ['Burger House', 'Managua', 'Restaurantes', 30, 'percent', 'Combo doble + bebida incluida', 'store'],
            ['Ferretería El Volcán', 'Granada', 'Ferreterías', 20, 'percent', 'Herramientas eléctricas seleccionadas', 'store'],
            ['Farmacia Salud Total', 'León', 'Farmacias', 15, 'percent', 'Medicamentos genéricos', 'store'],
            ['Café Barista', 'Managua', 'Cafeterías', 25, 'percent', 'Cualquier bebida caliente del menú', 'store'],
            ['Salón Divina', 'Masaya', 'Belleza', 40, 'percent', 'Corte y tinte completo', 'store'],
            ['Pizza Central', 'Estelí', 'Restaurantes', 50, 'percent', 'Pizza grande + 2L de refresco', 'delivery'],
            ['Minisuper Don Pepe', 'Chinandega', 'Supermercados', 10, 'percent', 'Compras mayores de C$500', 'store'],
            ['Veterinaria Amigos', 'Managua', 'Mascotas', 35, 'percent', 'Consulta + vacuna básica', 'store'],
            ['Cinemark Metrocentro', 'Managua', 'Entretenimiento', 20, 'percent', 'Entrada general cualquier función', 'store'],
            ['GymFit Pro', 'Managua', 'Gimnasios', 50, 'percent', 'Membresía mensual + 1 mes', 'store'],
            ['Óptica Cristal', 'León', 'Farmacias', 30, 'percent', 'Armazones y lentes de contacto', 'store'],
            ['Restaurante El Viejo', 'Matagalpa', 'Restaurantes', 15, 'percent', 'Almuerzo ejecutivo lunes a viernes', 'store'],
            ['TecnoShop NIC', 'Managua', 'Tecnología', 18, 'percent', 'Accesorios para celular y laptop', 'online'],
            ['Librería Ateneo', 'Granada', 'Educación', 25, 'percent', 'Libros de texto y útiles escolares', 'store'],
            ['Sushi Nica', 'Managua', 'Restaurantes', 20, 'percent', 'Rolls especiales y ceviche', 'delivery'],
            ['Farmacia Del Pueblo', 'Masaya', 'Farmacias', 20, 'percent', 'Vitaminas y suplementos', 'store'],
            ['Ferretería Construmax', 'Managua', 'Ferreterías', 12, 'percent', 'Materiales de construcción por mayor', 'store'],
            ['Heladería La Fría', 'Granada', 'Restaurantes', 80, 'amount', 'Conos dobles y batidos tropicales', 'store'],
            ['Salón Glamour', 'Managua', 'Belleza', 30, 'percent', 'Uñas acrílicas o gel completo', 'store'],
            ['Pet Shop Nicaragua', 'Managua', 'Mascotas', 15, 'percent', 'Alimentos premium para mascotas', 'store'],
            ['Centro Deportivo NIC', 'Managua', 'Gimnasios', 25, 'percent', 'Clases de fútbol sala y pádel', 'store'],
            ['Supermercado La Unión', 'Managua', 'Supermercados', 8, 'percent', 'Carnes y embutidos de fin de semana', 'store'],
            ['Café Habitual', 'León', 'Cafeterías', 20, 'percent', 'Desayuno completo + café incluido', 'store'],
            ['Claro Nicaragua', 'Managua', 'Tecnología', 15, 'percent', 'Planes prepago y recargas', 'online'],
        ];

        foreach ($deals as $index => $deal) {
            [$name, $department, $categoryName, $value, $type, $title, $channel] = $deal;
            $category = $categoryModels[$categoryName];
            $slug = Str::slug($name);
            BusinessLogo::writeSvg($name, $slug);

            $business = Business::create([
                'name' => $name,
                'slug' => $slug,
                'department' => $department,
                'city' => $department,
                'category_id' => $category->id,
                'status' => 'approved',
                'approved_at' => now(),
                'email_domain' => $slug.'.com.ni',
                'phone' => '+505 8000 '.str_pad((string) (1000 + $index), 4, '0', STR_PAD_LEFT),
            ]);

            if ($index === 0) {
                User::factory()->create([
                    'name' => 'María López',
                    'email' => 'contacto@burgerhouse.com.ni',
                    'role' => 'merchant',
                    'phone' => $business->phone,
                    'business_id' => $business->id,
                ]);
            }

            Coupon::create([
                'business_id' => $business->id,
                'category_id' => $category->id,
                'title' => $title,
                'discount_type' => $type,
                'discount_value' => $value,
                'expires_at' => now()->addDays(20 + $index),
                'max_redemptions' => 80 + ($index * 3),
                'redemptions_count' => $index * 2,
                'channels' => [$channel, 'whatsapp'],
                'payment_methods' => ['cash', 'card', 'transfer'],
                'status' => 'active',
                'terms' => 'No acumulable con otras promociones. Válido en sucursales participantes.',
            ]);
        }
    }
}
