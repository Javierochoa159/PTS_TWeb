<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\ProveedorProducto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compra>
 */
class CompraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $proveedor = ProveedorProducto::inRandomOrder()->first()->proveedor;
        return [
            "monto_total" => $this->faker->randomFloat(4,300,15000),
            "tipo_pago" => $this->faker->randomElement(["Tarjeta","Efectivo","Mixto"]),
            "proveedor" => $proveedor,
            "fecha_compra" => $this->faker->dateTimeBetween('-2 years','now'),
        ];
    }
}
