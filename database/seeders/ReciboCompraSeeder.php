<?php

namespace Database\Seeders;

use App\Models\Compra;
use App\Models\RecibosCompra;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReciboCompraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idCompras=Compra::select("id","tipo_pago")->where("tipo_pago","!=","Efectivo")->get();
        foreach($idCompras as $idCompra){
            for($i=0;$i<rand(1,2);$i++){
                RecibosCompra::create([
                    "url_img_online"=>$this->randRecibo(),
                    "compra"=>$idCompra->id
                ]);
            }
        }
    }

    private function randRecibo(){
        $fotos=[
            "https://imgs.search.brave.com/LQPi6CvLKu455HibCXmsZrUeinciM-Scxb0If8hOprE/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWcu/ZnJlZXBpay5jb20v/dmVjdG9yLXByZW1p/dW0vcmVnaXN0cm8t/cmVjaWJvLWVmZWN0/aXZvLWZvbmRvLWdy/aXMtZG9jdW1lbnRv/LWZpbmFuY2llcm8t/cGFnby10aWVuZGEt/bWlub3Jpc3RhLW8t/dGllbmRhLWlsdXN0/cmFjaW9uLXZlY3Rv/cmlhbF8yODc5NjQt/NDg5OS5qcGc_c2Vt/dD1haXNfaHlicmlk/Jnc9NzQwJnE9ODA",
            "https://imgs.search.brave.com/J6ztPR8McXO9IWsZicur5v3nmDJcJgtOvLQagZAyWeI/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tYXJr/ZXRwbGFjZS5jYW52/YS5jb20vRUFGb2RM/VzZYOUkvMS8wLzEx/MzF3L2NhbnZhLWRv/Y3VtZW50by1hNC1m/YWN0dXJhLWxpbXBp/by1ibGFuY28tWWdt/NDktTk1URmsuanBn",
        ];
        $id=rand(0,1);
        return $fotos[$id];
    }
}
