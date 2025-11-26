<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\User;
use App\Rules\ValidEmail;
use App\Rules\ValidPass;
use Brick\Math\BigDecimal;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProveedorController extends Controller
{
    public function index(){
        if(!session()->has("ordenProveedores")){
            session()->put("ordenProveedores","NombreAZ");
        }
        if(!session()->has("carrito")){
            $carrito=[
                "productos"=>null,
                "subtotal"=>BigDecimal::of("0.0000"),
                "total"=>BigDecimal::of("0.0000")
            ];
            session()->put("carrito",$carrito);
        }
        session()->put("pagina","proveedores");
        $provModel=new Proveedor();
        $proveedores=$provModel->getAllProveedores();
        if(!session()->has("proveedores")){
            $provs=$provModel->getAllNameProveedores();
            session()->put("proveedores",$provs->pluck("id")->toArray());
        }
        $data=[
            "proveedores"=>$proveedores
        ];
        return view("proveedores",$data);
    }

    public function cleanIndex(){
        if(session()->has("ordenProveedores")){
            session()->forget("ordenProveedores");
        }
        return redirect()->to(route("proveedor.index"));
    }

    public function newProveedor(Request $request){
        $proveedor=$request->all();
        $rulesNewProveedor=[
            "nombre-newProv" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:7", "max:50"],
            "direccion-newProv" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:10", "max:100"],
            "correo-newProv" => ["required", new ValidEmail(), "min:5", "max:75"],
            "telefono-newProv" => ["required","min_digits:9", "max_digits:14"],
        ];
        $request->validate($rulesNewProveedor);
        try{
            $proveedorDB=new Proveedor();
            $newProv=[
                "nombre"=>$proveedor["nombre-newProv"],
                "direccion"=>$proveedor["direccion-newProv"],
                "correo"=>$proveedor["correo-newProv"],
                "telefono"=>$proveedor["telefono-newProv"],
            ];
            $proveedores=session("proveedores");
            $proveedores[]=$proveedorDB->newProveedor($newProv);
            session()->put("proveedores",$proveedores);
        }catch(Error $e){
            return redirect()->to(route("inicio.index"))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar crear el proveedor.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
        return redirect()->to(route("inicio.index"))->with("mensaje",["Mensaje"=>"Proveedor creado exitosamente.","Success"=>""]);
    }

    public function editProveedor(Request $request){
        $proveedor=$request->all();
        $rulesEditProveedor=[
            "id-editProv" => ["required"],
            "nombre-editProv" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:7", "max:50"],
            "direccion-editProv" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:10", "max:100"],
            "correo-editProv" => ["required", new ValidEmail(), "min:5", "max:75"],
            "telefono-editProv" => ["required","min_digits:9", "max_digits:14"],
        ];
        $request->validate($rulesEditProveedor);
        
        $idProv=$proveedor["id-editProv"];
        if(!in_array($idProv,session("proveedores"))){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado al intentar editar el proveedor.","Error"=>""]);
        }else{
            try{
                $proveedorDB=new Proveedor();
                $editProv=[
                    "nombre"=>$proveedor["nombre-editProv"],
                    "direccion"=>$proveedor["direccion-editProv"],
                    "correo"=>$proveedor["correo-editProv"],
                    "telefono"=>$proveedor["telefono-editProv"],
                ];
                if(!$proveedorDB->editProveedor($editProv,$idProv))
                return redirect()->to(route("proveedor.proveedor",$idProv))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar editar al proveedor.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }catch(Error $e){
                return redirect()->to(route("proveedor.proveedor",$idProv))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar editar al proveedor.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }
            return redirect()->to(route("proveedor.proveedor",$idProv))->with("mensaje",["Mensaje"=>"Proveedor editado exitosamente.","Success"=>""]);
        }
    }

    public function deleteProveedor(Request $request){
        $rulesAdmin=[
            "passAdmin-deleteProv" => ["required","min:6", "max:16", new ValidPass()]
        ];
        request()->validate($rulesAdmin);
        $pass=request()->input("passAdmin-deleteProv");
        $proveedor=$request->input("idProv-deleteProv");
        $btnDelete=$request->input("eliminarProveedor");
        if(!isset($btnDelete) || (!isset($proveedor))){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }else{
            $admin=User::select("password")->where("name","=","Administrador")->first();
            if (Hash::check($pass, $admin->password)) {
                try{
                    $proveedorDB=new Proveedor();
                    if(!$proveedorDB->deleteProveedor($proveedor)){
                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar eliminar el proveedor.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                    }
                    return redirect()->to(route("proveedor.index"))->with("mensaje",["Mensaje"=>"¡Proveedor eliminado!","Success"=>""]);
                }catch(Error $e){
                    return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                }
            }else{
                return redirect()->back()->withErrors(["passAdmin-deleteProv"=>"Contraseña incorrecta."]);
            }
        }
    }

    public function ordenarProveedores(){
        $orden=request()->input("orden");
        switch($orden){
            case "NombreAZ":
                session()->put("ordenProveedores","NombreAZ");
                break;
            case "NombreZA":
                session()->put("ordenProveedores","NombreZA");
                break;
            case "MasReciente":
                session()->put("ordenProveedores","MasReciente");
                break;
            case "MenosReciente":
                session()->put("ordenProveedores","MenosReciente");
                break;
            default:
                session()->put("ordenProveedores","NombreAZ");
        }
        return to_route("proveedor.index");
    }

    public function mostrarProveedor($idProveedor){
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
        $provDB=new Proveedor();
        if(!session()->has("proveedores")){
            $proveedores=$provDB->getAllNameProveedores();
            session()->put("proveedores",$proveedores->pluck("id")->asArray());
        }
        if(session()->has("trueEditCompra") && session("trueEditCompra")["compra"]->proveedor==$idProveedor){
            $trueEditCompra=session("trueEditCompra");
            $proveedor=$provDB->getProveedor($trueEditCompra["compra"]->proveedor);
            if(!isset($proveedor)){
                return redirect()->to(route("proveedor.inicio"))->with("mensaje",["Mensaje"=>"Proveedor no disponible.","Error"=>""]);
            }else{
                session()->put("pagina","proveedor");
                return view("proveedor",["proveedor"=>$proveedor]);
            }
        }else{
            if(in_array($idProveedor,session("proveedores"))){
                $proveedor=$provDB->getProveedor($idProveedor);
                if(!isset($proveedor)){
                    return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                }else{
                    session()->put("pagina","proveedor");
                    return view("proveedor",["proveedor"=>$proveedor]);
                }
            }else{
                return redirect()->to(route("proveedor.inicio"))->with("mensaje",["Mensaje"=>"Proveedor no disponible.","Error"=>""]);
            }
        }
    }
}
