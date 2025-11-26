<?php

namespace Database\Seeders;

use App\Models\Compra;
use Illuminate\Database\Seeder;

class CompraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Compra::factory(85)->create();
    }
}
