<?php

namespace Database\Factories;

use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UbicacionVenta>
 */
class UbicacionVentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $ventaId = null;
    public function withVenta($ventaId): static{
        $this->ventaId = $ventaId;
        return $this;
    }
    public function definition(): array
    {
        if(isset($this->ventaId)){
            $venta=Venta::where("id","=",$this->ventaId)->where("tipo_venta","=","Envio")->select("id","fecha_venta","estado_entrega")->first();
        }else{
            $venta=Venta::where("tipo_venta","=","Envio")->select("id","fecha_venta","estado_entrega")->inRandomOrder()->first();
        }
        $fecha=Carbon::parse($venta->fecha_venta);
        
        $min = $this->controlHora($fecha->copy(), 'min');
        $max = $this->controlHora($fecha->copy()->addDays(7)->setTime(20, 30), 'max');
        
        $fMin = $min->copy()->addHours(rand(0, $min->diffInHours($max)));
        $aux = $min->copy()->addHours(rand(0, $min->diffInHours($max)));

        if($aux->lessThan($fMin)){
            $fMax=$fMin;
            $fMin=$aux;
        }else{
            $fMax=$aux;
        }

        if($fecha->dayOfMonth()==$fMin->dayOfMonth()){
            $fMin=$this->controlHora($fMin->copy(),"min");
        }else{
            $fMin=$this->controlHora($fMin->copy(),"max");
        }
        $fMax=$this->controlHora($fMax->copy(),"max");

        switch($venta->estado_entrega){
            case "Completa":    $fEntrega=$fMin->copy()->addHours(rand(0, $fMin->diffInHours($fMax)));
                                if($fEntrega->dayOfMonth()==$fMax->dayOfMonth()){
                                    $fEntrega=$this->controlHora($fEntrega->copy(),"max");
                                }elseif($fEntrega->dayOfMonth()==$fMin->dayOfMonth() && $fEntrega->hourOfDay()<=$fMin->hourOfDay()){
                                    $fEntrega=$fEntrega->addDay();
                                    $fEntrega=$this->controlHora($fEntrega->copy(),"max");
                                }else{
                                    $fEntrega=$this->controlHora($fEntrega->copy(),"max");
                                }
                                if(!$fMin->lessThan($fEntrega)){
                                    $aux=$fEntrega;
                                    $fEntrega=$fMin;
                                    $fMin=$aux;
                                }
                                if($fMax->lessThan($fEntrega)){
                                    $aux=$fEntrega;
                                    $fEntrega=$fMax;
                                    $fMax=$aux;
                                }
            break;
            default:$fEntrega=null;
        }

        return [
            "venta"=>$venta->id,
            "direccion"=>$this->faker->address(),
            "casa_depto"=>$this->faker->regexify("[a-zA-Z0-9]{1,4}"),
            "manzana_piso"=>$this->faker->regexify("[a-zA-Z0-9]{1,4}"),
            "descripcion"=>$this->faker->randomElement([null,$this->faker->realTextBetween(10,250)]),
            "fecha_entrega_min"=>$fMin,
            "fecha_entrega_max"=>$fMax,
            "fecha_entrega"=>$fEntrega,
        ];
    }
    private function controlHora($fecha,$tipo){
        switch($tipo){
            case "min":
                if($fecha->greaterThan($fecha->copy()->setTimeFromTimeString("20:30"))){
                    $fecha=$fecha->copy()->setTimeFromTimeString("07:30")->addDay();
                }
            break;
            case "max":
                if($fecha->greaterThan($fecha->copy()->setTime(18, 30))){
                    $fecha=$fecha->copy()->setTimeFromTimeString("20:30");
                }
        }
        if($fecha->lessThan($fecha->copy()->setTimeFromTimeString("07:30"))){
            $fecha=$fecha->copy()->setTimeFromTimeString("07:30");
        }
        return $fecha;
    }
}