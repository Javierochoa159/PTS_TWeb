<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\ProveedorProducto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProveedorProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idProds=Producto::select("id")->get();
        foreach($idProds as $idProd){
            ProveedorProducto::create([
                "proveedor"=>Proveedor::inRandomOrder()->first()->id,
                "producto"=>$idProd->id
            ]);
        }
    }
}
