<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar se já existem depoimentos
        if (Testimonial::count() == 0) {
            // Criar 10 depoimentos usando a factory
            Testimonial::factory(10)->create();
        }
    }
}
