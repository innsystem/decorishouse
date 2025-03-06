<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'logo', 'value' => null],
            ['key' => 'favicon', 'value' => null],
            ['key' => 'meta_title', 'value' => 'Bem Vindos | InnSystem'],
            ['key' => 'meta_keywords', 'value' => 'negócios, serviços, clientes'],
            ['key' => 'meta_description', 'value' => 'Sistema genérico para gestão de negócios e clientes'],
            ['key' => 'script_head', 'value' => ''],
            ['key' => 'script_body', 'value' => ''],
            ['key' => 'site_name', 'value' => 'InnSystem'],
            ['key' => 'site_proprietary', 'value' => 'InnSystem LTDA'],
            ['key' => 'site_document', 'value' => '12.345.678/0001-90'],
            ['key' => 'site_email', 'value' => 'contato@innsystem.com.br'],
            ['key' => 'telephone', 'value' => '(16) 4002-8922'],
            ['key' => 'cellphone', 'value' => '(16) 99999-9999'],
            ['key' => 'address', 'value' => 'Av. Principal, 123, Ribeirão Preto, SP'],
            ['key' => 'hour_open', 'value' => '08:00 às 18:00'],
            ['key' => 'client_id', 'value' => Str::uuid()],
            ['key' => 'client_secret', 'value' => Str::random(40)],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
