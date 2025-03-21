<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            'Sala' => [
                'Sofás',
                'Poltronas',
                'Tapetes',
                'Racks & Painéis de TV',
                'Adegas & Bares',
                'Luminárias & Abajures',
                'Quadros & Espelhos',
                'Cortinas & Persianas',
            ],
            'Quarto' => [
                'Camas & Colchões',
                'Criados-mudos',
                'Guarda-roupas',
                'Penteadeiras',
                'Espelhos',
                'Luminárias & Abajures',
                'Tapetes',
                'Roupas de Cama',
            ],
            'Banheiro' => [
                'Toalheiros & Porta-Toalhas',
                'Espelhos',
                'Tapetes de Banheiro',
                'Organizadores',
                'Kits de Banheiro',
                'Lixeiras',
            ],
            'Cozinha' => [
                'Mesas & Cadeiras',
                'Bancadas & Ilhas',
                'Armários de Cozinha',
                'Louças & Talheres',
                'Panelas & Utensílios',
                'Organizadores',
                'Iluminação',
            ],
            'Escritório' => [
                'Mesas de Escritório',
                'Cadeiras Ergonômicas',
                'Estantes & Organizadores',
                'Luminárias',
                'Tapetes',
                'Quadros Decorativos',
            ],

            'Área Externa' => [
                'Móveis para Jardim',
                'Churrasqueiras & Acessórios',
                'Iluminação Externa',
                'Ombrelones & Tendas',
                'Decoração Externa',
                'Piscinas & Espreguiçadeiras',
            ],
        ];

        DB::table('categories')->delete();

        foreach ($categories as $parentName => $subcategories) {
            // Criando a categoria principal (parent)
            $slug_category = Str::slug($parentName);
            $parent = Category::create([
                'name' => $parentName,
                'slug' => $slug_category,
                'thumb' => null,
                'parent_id' => null,
                'description' => null,
                'status' => 1,
            ]);

            // Criando as subcategorias
            foreach ($subcategories as $subcategoryName) {
                Category::create([
                    'name' => $subcategoryName,
                    'slug' => $slug_category . '-' . Str::slug($subcategoryName),
                    'thumb' => null,
                    'parent_id' => $parent->id,
                    'description' => null,
                    'status' => 1,
                ]);
            }
        }

    }
}
