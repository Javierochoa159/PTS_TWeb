<?php

namespace Database\Seeders;

use App\Models\DevolucionVenta;
use App\Models\Venta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevolucionVentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i=0;$i<50;$i++){
            $venta=Venta::whereNotExists(function ($q){
                            $q->select(DB::raw(1))
                            ->from('devoluciones_ventas')
                            ->whereColumn('devoluciones_ventas.venta', 'ventas.id');
                    })->inRandomOrder()->first();
            DevolucionVenta::factory()->withVenta($venta->id)->create();
        }
    }
}
