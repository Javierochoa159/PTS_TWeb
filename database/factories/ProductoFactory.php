<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipoMedida=$this->faker->randomElement(["Unidad","Kilogramo","Metro","Litro"]);
        switch($tipoMedida){
            case "Unidad":  $cantidadDisp=$this->faker->numberBetween(0,500);
                            break;
            default:    $cantidadDisp=$this->faker->randomFloat(4,0,500);
        }
        $cantidadMin=$this->faker->numberBetween(15,50);
        $categoria = Categoria::whereDoesntHave('hijos')->inRandomOrder()->first()->id;
        return [
            "nombre" => $this->faker->sentence(6),
            "descripcion" => $this->faker->text(250),
            "codigo" => $this->faker->randomElement([null,$this->faker->regexify('[A-Z]{5}[0-4]{3}')]),
            "precio_venta" => $this->faker->randomFloat(2,300,15000),
            "tipo_medida" => $tipoMedida,
            "cantidad_disponible" => $cantidadDisp,
            "cantidad_minima" => $cantidadMin,
            "categoria" => $categoria,
        ];
    }
}
