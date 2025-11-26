<?php

namespace Database\Seeders;

use App\Models\UbicacionVenta;
use App\Models\Venta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UbicacionVentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ventas = Venta::where('tipo_venta', 'Envio')
                        ->whereNotExists(function ($q) {
                            $q->select(DB::raw(1))
                            ->from('ubicaciones_ventas')
                            ->whereColumn('ubicaciones_ventas.venta', 'ventas.id');
                        })
                        ->get();
        foreach ($ventas as $venta) {
            UbicacionVenta::factory()->withVenta($venta->id)->create();
        }
    }
}
