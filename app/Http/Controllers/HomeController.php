<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Venta;
use App\Rules\ValidPass;
use Brick\Math\BigDecimal;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index(){
        if(!session()->has("carrito")){
            $fmt = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
            $fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 4);
            $carrito=[
                "productos"=>null,
                "subtotal"=>BigDecimal::of("0.0000"),
                "total"=>BigDecimal::of("0.0000")
            ];
            $carrito["subtotal_Print"]=$fmt->format($carrito["subtotal"]->__toString());
            $carrito["total_Print"]=$fmt->format($carrito["total"]->__toString());
            session()->put("carrito",$carrito);
        }
        session()->put("pagina","inicio");
        $categos = new Categoria();
        $categos=$categos->getAllCategorias();
        if(!session()->has("categorias")){
            session()->put("categorias",Arr::pluck($categos,"id"));
        }
        $provs = new Proveedor();
        $provs=$provs->getAllNameProveedores();
        if(!session()->has("proveedores")){
            session()->put("proveedores",$provs->pluck("id")->toArray());
        }
        $data=[
                "categorias"=>$categos,
                "proveedores"=>$provs
              ];
        return view('inicio',$data);
    }

    public function cleanIndex(){
        if(session()->has("ordenProductos")){
            session()->forget("ordenProductos");
        }
        if(session()->has("filtroCatego")){
            session()->forget("filtroCatego");
        }
        if(session()->has("buscar")){
            session()->forget("buscar");
        }
        if(session()->has("ordenProveedores")){
            session()->forget("ordenProveedores");
        }
        if(session()->has("errorVenta")){
            session()->forget("errorVenta");
        }
        if(session()->has("filtroVentas")){
            session()->forget("filtroVentas");
        }
        if(session()->has("ordenarVentas")){
            session()->forget("ordenarVentas");
        }
        if(session()->has("editVenta")){
            session()->forget("editVenta");
        }
        if(session()->has("ordenarCompras")){
            session()->forget("ordenarCompras");
        }
        if(session()->has("filtroCompras")){
            session()->forget("filtroCompras");
        }
        return redirect()->to(route("inicio.index"));
    }

    public function sessionAdmin(){
        $rulesAdmin=[
            "passAdmin" => ["required","min:6", "max:16", new ValidPass()]
        ];
        request()->validate($rulesAdmin);
        $pass=request()->input("passAdmin");
        $admin=User::select("password")->where("name","=","Administrador")->first();
        if (Hash::check($pass, $admin->password)) {
            session()->put("adminSet",[
                "timeSet" => now()
            ]);
            return redirect()->back()->with("mensaje",["Mensaje"=>"Administrador iniciado","Success"=>""]);
        }else{
            return redirect()->back()->withErrors(["passAdmin"=>"Contraseña incorrecta."]);
        }
    }

    public function cerrarSession(){
        if(session()->has("adminSet")){
            session()->forget("adminSet");
        }
        return redirect()->back()->with("mensaje",["Mensaje"=>"Administrador finalizado","Success"=>""]);
    }

    /*public function update()
    {
        User::create([
            'name' => "Administrador",
            'email' => Hash::make("correo@admin.com"),
            'password' => Hash::make("C@ntAdm1nApp")
        ]);
    }*/

    public function handleNotFound(){
        return redirect()->to(route("inicio.inicio"))->with("mensaje",["Mensaje"=>"Ruta no encontrada.","Error"=>""]);
    }
}
