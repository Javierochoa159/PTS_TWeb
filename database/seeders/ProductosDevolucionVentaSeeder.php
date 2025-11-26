<?php

namespace Database\Seeders;

use App\Models\DevolucionVenta;
use App\Models\ProductosDevolucionVenta;
use App\Models\ProductosVenta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductosDevolucionVentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devoluciones = DevolucionVenta::select("id","venta")->get();
        foreach ($devoluciones as $devolucion) {
            $prodsVenta=ProductosVenta::where("venta","=",$devolucion->venta)->get();
            $cant=0;
            $cantProds=rand(1,sizeof($prodsVenta));
            $prods=[];
            for($i=0;$i<$cantProds;$i++){
                $idProd="a";
                while(!in_array($idProd,$prods)){
                    $idProd=$prodsVenta[rand(0,sizeof($prodsVenta)-1)]->id;
                    if(!in_array($idProd,$prods)){
                        $prods[]=$idProd;
                    }
                }
            }
            foreach($prodsVenta as $prod){
                if($cant<$cantProds){
                    if(in_array($prod->id,$prods)){
                        ProductosDevolucionVenta::factory()
                                            ->withDevolucion($devolucion->id)
                                            ->withProdvent($prod->id)
                                            ->create();
                        $cant++;
                    }
                }
            }
            if($cant==0){
                DevolucionVenta::where("id","=",$devolucion->id)->forceDelete();
            }
        }
    }
}
