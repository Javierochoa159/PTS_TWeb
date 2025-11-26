<?php

namespace Database\Factories;

use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DevolucionVenta>
 */
class DevolucionVentaFactory extends Factory
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
            $venta=Venta::where("id","=",$this->ventaId)->select("id","fecha_venta")->first();
        }else{
            $venta=Venta::whereNotExists(function ($q){
                            $q->select(DB::raw(1))
                            ->from('devoluciones_ventas')
                            ->whereColumn('devoluciones_ventas.venta', 'ventas.id');
                    })->select("id","fecha_venta")->inRandomOrder()->first();
        }
        
        $fechaV=Carbon::parse($venta->fecha_venta);
        $fechaDev=Carbon::parse($venta->fecha_venta)->copy()->addDays(random_int(0,3));
        if($fechaDev->equalTo($fechaV)){
            $fechaDev=$fechaDev->copy()->addMinutes(random_int(30,90));
        }
        $fechaDev=$this->controlHora($fechaDev,"max");
        return [
            "venta"=>$venta->id,
            "tipo_pago"=>$this->faker->randomElement(["Tarjeta","Efectivo","Mixto"]),
            "monto_total"=>"0.0000",
            "fecha_devolucion"=>$fechaDev->__toString()
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
