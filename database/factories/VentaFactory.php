<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipoVenta=$this->faker->randomElement(["Envio","Local"]);
        switch($tipoVenta){
            case "Envio":   $estadoEntrega=$this->faker->randomElement(["Pendiente","Completa"]);
                            $nombReceptor=$this->faker->name();
                            $TelfReceptor=$this->faker->phoneNumber();
            break;
            case "Local":   $estadoEntrega=$this->faker->randomElement(["Pendiente","Completa"]);
                            switch($estadoEntrega){
                                case "Pendiente":   $nombReceptor=$this->faker->name();
                                                    $TelfReceptor=$this->faker->phoneNumber();
                                break;
                                case "Completa":    $nombReceptor=null;
                                                    $TelfReceptor=null;
                                break;
                                default:    $estadoEntrega="Completa";
                                            $nombReceptor=null;
                                            $TelfReceptor=null;

                            }
            break;
            default:$tipoVenta="Local"; 
                    $estadoEntrega="Completa";
                    $nombReceptor=null;
                    $TelfReceptor=null;
        }
        $total=$this->faker->randomFloat(4,300,15000);
        $fechaV=$this->faker->dateTimeBetween("-2 years","now");
        $fechaV=Carbon::parse($fechaV);
        $nowV=Carbon::parse(now());
        $nowMin=$nowV->setTimestamp(now()->getTimestamp()-604800);
        if(!$fechaV->between($nowMin,$nowV)){
            $estadoEntrega="Completa";
        }
        if(strcmp($estadoEntrega,"Completa")==0){
            $tipoPago=$this->faker->randomElement(["Tarjeta","Efectivo","Mixto"]);
        }else{
            $tipoPago=$this->faker->randomElement(["Tarjeta","Efectivo","Mixto","Pendiente"]);
        }
        return [
            "monto_subtotal" => $total,
            "monto_total" => $total,
            "tipo_pago" => $tipoPago,
            "tipo_venta" => $tipoVenta,
            "nombre_receptor" => $nombReceptor,
            "telefono_receptor" => $TelfReceptor,
            "estado_entrega" => $estadoEntrega,
            "fecha_venta" => $fechaV,
        ];
    }
}
