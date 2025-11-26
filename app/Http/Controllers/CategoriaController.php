<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\User;
use App\Rules\ValidPass;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class CategoriaController extends Controller
{
    public function newCategoria(Request $request){
        $categoria=$request->all();
        $rulesNewCatego=[
            "nombre-newCatego" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-]+$/i","min:3", "max:25"],
            "catego-id-newCatego" => ["required"],
        ];
        $request->validate($rulesNewCatego);

        $errores=[];

        if(!in_array($categoria["catego-id-newCatego"],session("categorias")) && $categoria["catego-id-newCatego"]!=0){
            $errores["catego-id-newCatego"]="Error. Elija una categoria válida.";
        }
        
        if(!empty($errores)){
            return redirect()->back()->withInput()->withErrors($errores);
        }

        try{
            $categoDB=new Categoria();
            $newCatego=[
                "titulo"=>$categoria["nombre-newCatego"],
            ];
            if($categoria["catego-id-newCatego"]>0){
                $newCatego["padre"]=$categoria["catego-id-newCatego"];
            }
            $newIdCatego=$categoDB->newCategoria($newCatego);
            if(!isset($newIdCatego)){
                session()->forget("categorias");
                return redirect()->to(route("inicio.inicio"))->with("mensaje",["Mensaje"=>"Categoria creada exitosamente.","Success"=>""]);
            }else{
                $categorias=session("categorias");
                $categorias[]=$newIdCatego;
                session()->put("categorias",$categorias);
                return redirect()->to(route("inicio.inicio"))->with("mensaje",["Mensaje"=>"Categoria creada exitosamente.","Success"=>""]);
            }
        }catch(Error $e){
            return redirect()->to(route("inicio.inicio"))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar crear la categoria.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
    }

    public function editCategoria(Request $request){
        $categoria=$request->all();
        $rulesEditCatego=[
            "nombre-editCatego" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-]+$/i","min:3", "max:25"],
            "catego-id-editCatego" => ["required"],
        ];
        $request->validate($rulesEditCatego);

        $errores=[];

        if(!in_array($categoria["catego-id-editCatego"],session("categorias"))){
            $errores["catego-id-editCatego"]="Error. Elija una categoria válida.";
        }
        
        if(!empty($errores)){
            return redirect()->back()->withInput()->withErrors($errores);
        }

        try{
            $categoDB=new Categoria();
            $editCatego=[
                "titulo"=>$categoria["nombre-editCatego"],
            ];
            $idCatego=$categoria["catego-id-editCatego"];
            if(!$categoDB->editCategoria($editCatego,$idCatego)){
                return redirect()->to(route("inicio.inicio"))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar modificar la categoria.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }
            return redirect()->to(route("inicio.inicio"))->with("mensaje",["Mensaje"=>"Categoria modificada exitosamente.","Success"=>""]);
        }catch(Error $e){
            return redirect()->to(route("inicio.inicio"))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar modificar la categoria.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
    }

    public function deleteCategoria(Request $request){
        $rulesAdmin=[
            "passAdmin-deleteCatego" => ["required","min:6", "max:16", new ValidPass()],
            "catego-id-deleteCatego" => ["required"]
        ];
        request()->validate($rulesAdmin);
        $pass=request()->input("passAdmin-deleteCatego");
        $categoria=$request->input("catego-id-deleteCatego");
        $btnDelete=$request->input("eliminarCatego");
        if(!isset($btnDelete) || (!isset($categoria))){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }else{
            $admin=User::select("password")->where("name","=","Administrador")->first();
            if (Hash::check($pass, $admin->password)) {
                if(!in_array($categoria,session("categorias"))){
                    return redirect()->back()->with("mensaje",["Mensaje"=>"Error. Elija una categoria válida.","Error"=>""]);
                }
                try{
                    $categoDB=new Categoria();
                    if(!$categoDB->deleteCategoria($categoria)){
                        return redirect()->to(route("inicio.inicio"))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar eliminar la categoria.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                    }
                    $categorias=$categoDB->getAllCategorias();
                    if(!isset($categorias)){
                        session()->forget("categorias");
                        return redirect()->to(route("inicio.inicio"))->with("mensaje",["Mensaje"=>"Categoria eliminada exitosamente.","Success"=>""]);
                    }
                    session()->put("categorias",Arr::pluck($categorias,"id"));
                    return redirect()->to(route("inicio.inicio"))->with("mensaje",["Mensaje"=>"Categoria eliminada exitosamente.","Success"=>""]);
                }catch(Error $e){
                    return redirect()->to(route("inicio.inicio"))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar eliminar la categoria.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                }
            }else{
                return redirect()->back()->withErrors(["passAdmin-deleteCatego"=>"Contraseña incorrecta."]);
            }
        }
    }
}
