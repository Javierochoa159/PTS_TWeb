<?php

namespace Database\Seeders;

use App\Models\Foto;
use App\Models\Producto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FotoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idProds=Producto::select("id")->get();
        foreach($idProds as $idProd){
            Foto::create([
                "url_img_online"=>$this->randFoto(),
                "producto"=>$idProd->id
            ]);
        }
    }

    private function randFoto(){
        $fotos=[
            "https://dcdn-us.mitiendanube.com/stores/241/353/products/tornillo-t2-6x11-ba14643e4f3b578b7b15123024256877-1024-1024.webp",
            "https://dcdn-us.mitiendanube.com/stores/241/353/products/27c17927-368a-4604-8872-c020adb16d2f_bo3710_p_1500px1-5aa13d31091ce0e1cb16668100297243-1024-1024.webp",
            "https://dcdn-us.mitiendanube.com/stores/241/353/products/gacoflex-19l-poliuretano21-0925645b4df231cbfa16789066697266-1024-1024.webp",
            "https://dcdn-us.mitiendanube.com/stores/241/353/products/compuesto-bolsa1-2b0a4aa66eaed3469616626357779144-1024-1024.webp",
            "https://dcdn-us.mitiendanube.com/stores/241/353/products/para-cortar-madera1-75c4f765e756401c9916457195145684-1024-1024.webp"
        ];
        $id=rand(0,4);
        return $fotos[$id];
    }
}
