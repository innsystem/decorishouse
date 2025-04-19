<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create('pt_BR');
        
        $depoimentos = [
            'Encontrei produtos incríveis para minha cozinha neste site. Super recomendo!',
            'Atendimento excelente e produtos de qualidade. Amei cada peça que comprei!',
            'Minha cozinha ficou linda com os produtos que comprei aqui.',
            'Preços justos e entrega rápida. Voltarei a comprar com certeza!',
            'Os itens de decoração transformaram minha casa. Obrigado!',
            'Site confiável e produtos de qualidade. Recomendo a todos!',
            'As panelas que comprei são maravilhosas e duráveis.',
            'Itens lindos e funcionais para a cozinha, adorei!',
            'Comprei vários itens e todos chegaram perfeitos. Muito satisfeita!',
            'Ótimas dicas de decoração e produtos excelentes.',
            'Os utensílios de cozinha são práticos e bonitos, como mostrados no site.',
            'A decoração da minha cozinha ficou incrível com as sugestões daqui.',
            'Compramos online e recebemos produtos de excelente qualidade.',
            'Os produtos têm ótimo custo-benefício. Vale cada centavo!',
            'O atendimento ao cliente é fantástico e os produtos são de primeira linha.'
        ];
        
        return [
            'name' => $faker->name,
            'avatar' => null,
            'content' => $faker->randomElement($depoimentos),
            'rating' => $faker->numberBetween(4, 5),
            'localization' => $faker->city . ', ' . $faker->stateAbbr,
            'sort_order' => $faker->numberBetween(1, 100),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
