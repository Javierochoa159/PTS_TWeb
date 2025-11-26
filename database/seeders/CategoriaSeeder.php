<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Categoria::create([
            "titulo"=>"Categoria 1"
        ]);
        Categoria::create([
            "titulo"=>"Categoria 2"
        ]);
        Categoria::create([
            "titulo"=>"Categoria 3"
        ]);
        Categoria::create([
            "titulo"=>"Categoria 4"
        ]);
        Categoria::create([
            "titulo"=>"Categoria 5"
        ]);
        Categoria::create([
            "titulo"=>"Categoria 6"
        ]);
        Categoria::create([
            "titulo"=>"Categoria 7"
        ]);
        Categoria::create([
            "titulo"=>"Categoria 8"
        ]);
        Categoria::factory(10)->create();
        Categoria::factory(10)->create();
        Categoria::factory(10)->create();
        Categoria::factory(10)->create();
    }
}
