<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Foto;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\ProveedorProducto;
use App\Models\User;
use Illuminate\Http\Request;
use App\Rules\AlfaNunSpacePunct;
use App\Rules\ValidPass;
use Error;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Decoders\Base64ImageDecoder;
use Intervention\Image\Decoders\DataUriImageDecoder;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\Hash;

class ProductoController extends Controller{
    public function index(){
        if(session()->has("buscar"))session()->forget("buscar");
        if(!session()->has("ordenProductos")){
            $this->ordenarProductos(null);
        }
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
        session()->put("pagina","productos");
        if(session()->has("filtroCatego")){
            $filtro=session("filtroCatego");
            return view("productos",$filtro);
        }else{
            $categos = new Categoria();
            $categos=$categos->getAllCategorias();
            if(!session()->has("categorias")){
                session()->put("categorias",Arr::pluck($categos,"id"));
            }
            $orden=$this->getOrden();
            $prodModel=new Producto();
            $productos=$prodModel->getAllProductos($orden);
            $data=[
                "productos"=>$productos,
                "categorias"=>$categos
            ];
            return view("productos",$data);
        }
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
        return redirect()->to(route("producto.index"));
    }
    public function cleanTodos(){
        if(session()->has("ordenProductos")){
            session()->forget("ordenProductos");
        }
        if(session()->has("filtroCatego")){
            session()->forget("filtroCatego");
        }
        if(session()->has("buscar")){
            session()->forget("buscar");
        }
        return redirect()->to(route("producto.todos"));
    }
    public function todos(){
        if(session()->has("buscar"))session()->forget("buscar");
        if(!session()->has("ordenProductos")){
            $this->ordenarProductos(null);
        }
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
        session()->put("pagina","productos-todos");
        if(session()->has("filtroCatego")){
            $filtro=session("filtroCatego");
            return view("productos",$filtro);
        }else{
            $categos = new Categoria();
            $categos=$categos->getAllCategorias();
            if(!session()->has("categorias")){
                session()->put("categorias",Arr::pluck($categos,"id"));
            }
            $orden=$this->getOrden();
            $prodModel=new Producto();
            $productos=$prodModel->getAllProductos($orden,"todo");
            $data=[
                "productos"=>$productos,
                "categorias"=>$categos,
            ];
            return view("productos",$data);
        }
    }

    public function mostrarProductosCatego(Request $request){
        if(session()->has("buscar"))session()->forget("buscar");
        $filtro=$request->array(["orden","idCatego"]);
        $categos = new Categoria();
        $categos=$categos->getAllCategorias();
        if(!session()->has("categorias")){
            session()->put("categorias",Arr::pluck($categos,"id"));
        }
        if(!isset($filtro["idCatego"]) || !in_array($filtro["idCatego"],session("categorias"))){
            switch(session("pagina")){
                case "productos":       session()->forget("filtroCatego");
                                        return redirect()->to(route("producto.index"));
                case "productos-todos": session()->forget("filtroCatego");
                                        return redirect()->to(route("producto.todos"));
                default:                session()->forget("filtroCatego");
                                        return redirect()->to(route("producto.index"));
            }
        }
        $txtCatego=$this->getCategoProd($categos,$filtro["idCatego"]);
        if(isset($filtro["orden"])){
            $this->ordenarProductos($filtro["orden"]);
        }
        $orden=$this->getOrden();
        $prodDB = new Producto();
        switch(session("pagina")){
            case "productos": $pagina="activos";break;
            case "productos-todos": $pagina="todo";break;
            default: $pagina="activos";
        }
        $productos = $prodDB->buscarProductos($orden,$pagina,$filtro["idCatego"],null);
        $filtro["productos"]=$productos;
        $filtro["categorias"]=$categos;
        $filtro["txtCatego"]=$txtCatego;
        session()->put("filtroCatego",$filtro);
        switch(session("pagina")){
            case "productos":return redirect()->to(route("producto.index"));
            case "productos-todos":return redirect()->to(route("producto.todos"));
            default:return redirect()->to(route("producto.index"));
        }
    }

    public function buscarProducto(){
        if(session()->has("filtroCatego"))session()->forget("filtroCatego");
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
        $busqueda=request()->array(["prod","catego","orden","pagina"]);
        if(session()->has("buscar")){
            if(isset($busqueda["prod"])){
                $busqueda=session("buscar");
                $busqueda["prod"]=request()->input("prod");
                session()->put("buscar",$busqueda);
                return redirect()->to(route("producto.buscar"));
            }
            if(isset($busqueda["pagina"])){
                switch($busqueda["pagina"]){
                    case "activos": session()->put("pagina","productos");
                    break;
                    case "todos": session()->put("pagina","productos-todos");
                    break;
                    default: session()->put("pagina","productos");
                }
                return redirect()->to(route("producto.buscar"));
            }
            if(isset($busqueda["orden"])){
                $busqueda=session("buscar");
                $busqueda["orden"]=request()->input("orden");
                session()->put("buscar",$busqueda);
                return redirect()->to(route("producto.buscar"));
            }
            if(isset($busqueda["catego"])){
                $busqueda=session("buscar");
                $busqueda["catego"]=request()->input("catego");
                session()->put("buscar",$busqueda);
                return redirect()->to(route("producto.buscar"));
            }
        }else{
            if(isset($busqueda["prod"])){
                $busqueda=[];
                $busqueda["prod"]=request()->input("prod");
                session()->put("buscar",$busqueda);
                return redirect()->to(route("producto.buscar"));
            }else{
                if(isset($busqueda["orden"])){
                    $this->ordenarProductos($busqueda["orden"]);
                }else{
                    $this->ordenarProductos(null);
                }
                switch(session("pagina")){
                    case "productos": return redirect()->to(route("producto.index"));
                    case "productos-todos": return redirect()->to(route("producto.todos"));
                    default: return redirect()->to(route("producto.index"));
                }
            }
        }
    }

    public function buscarProd(){
        if(session()->has("buscar")){
            $busqueda=session("buscar");
            if(isset($busqueda["orden"]))
                $this->ordenarProductos($busqueda["orden"]);
            else
                $this->ordenarProductos("default");
            $orden=$this->getOrden();
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
            
            $categos = new Categoria();
            $categos=$categos->getAllCategorias();
            session()->put("categorias",Arr::pluck($categos,"id"));
            if(!session()->has("categorias")){
                session()->put("categorias",Arr::pluck($categos,"id"));
            }
    
            if(isset($busqueda["catego"])){
                if(!in_array($busqueda["catego"],session("categorias"))){
                    unset($busqueda["catego"]);
                    $catego=null;
                }else{
                    $catego=$busqueda["catego"];
                    $txtCatego=$this->getCategoProd($categos,$busqueda["catego"]);
                }
            }else{
                $catego=null;
            }
    
            
            $prodDB=new Producto();
            switch(session("pagina")){
                case "productos":       $pagina="activo";break;
                case "productos-todos": $pagina="todo";break;
                default:                $pagina="activo";session()->put("pagina","productos");
            }
            $productos=$prodDB->buscarProductos($orden,$pagina,$catego,$busqueda["prod"]);
            
            $data=[
                "productos"=>$productos,
                "categorias"=>$categos,
                "prodBuscado"=>$busqueda["prod"]
            ];
            if(isset($txtCatego)){
                $data["categoBuscada"]=$catego;
                $data["txtCatego"]=$txtCatego;
            }
            return view("productos",$data);
        }
        return redirect()->to(route("producto.index"));
    }

    private function getCategoProd($categos,$idCatego){
        foreach($categos as $catego){
            if($catego["id"]==$idCatego && $catego["padre"]!=null){
                return $this->getCategoProd($categos,$catego["padre"])."/".$catego["titulo"];
            }elseif($catego["id"]==$idCatego && $catego["padre"]==null){
                return $catego["titulo"];
            }
        }
        return "";
    }

    public function newProducto(Request $request){
        $producto=$request->all();
        $rulesNewProd=[
            "nombre-newProd" => ["required", "min:3", "max:100", new AlfaNunSpacePunct],
            "descripcion-newProd" => ["required", "min:10", "max:500", new AlfaNunSpacePunct],
            "precioVenta-newProd" => ["required","decimal:0,2","min:0.01","max:999999999.99"],
            "catego-id-newProd" => ["required"],
            "medida-newProd" => ["required","in:Unidad,Kilogramo,Metro,Litro"],
            "cantDispo-newProd" => ["required", "decimal:0,4", "min:0", "max:999999999.99"],
            "cantMinima-newProd" => ["required", "decimal:0,1", "min:1", "max:500"],
            "fotos-newProd" => ["required"],
        ];
        if(isset($producto["codigo-newProd"])){
            $rulesNewProd["codigo-newProd"]=["alpha_num","min:4","max:15"];
        }
        $request->validate($rulesNewProd);

        $errores=[];

        $prodKeys=array_keys($producto);
        $proveedores=[];
        foreach($prodKeys as $prodKey){
            if(is_array(explode("_",$prodKey))){
                $prov=explode("_",$prodKey);
                if(is_array($prov))
                    if(strcmp($prov[0],"proveedor-newProd")==0)
                        $proveedores[]=$prov[1];
            }
        }
        if(empty($proveedores)){
            $errores["proveedor-newProd"]="Elija al menos un proveedor.";
        }else{
            foreach($proveedores as $proveedor){
                if(!in_array($proveedor,session("proveedores"))){
                    $errores["proveedor-newProd"]="Error. Elija un proveedor válido.";
                    break;
                }
            }
        }
        if(!in_array($producto["catego-id-newProd"],session("categorias"))){
            $errores["catego-id-newProd"]="Error. Elija una categoria válida.";
        }
        if(sizeof($producto["fotos-newProd"])>5){
            $errores["fotos-newProd"]="Ingrese hasta 5 fotos.";
        }
        foreach($producto["fotos-newProd"] as $foto){
            if(is_array(explode(",",$foto))){
                if(base64_decode(explode(",",$foto)[1], true) === false){
                    $errores["fotos-newProd"]="Error. Ingrese fotos validas.";
                    break;
                }
            }
            if(!is_array(explode(";",$foto))){
                $errores["fotos-newProd"]="Error. Ingrese fotos validas.";
                break;
            }else{
                $type=explode("/",explode(";",$foto)[0]);
                if(!is_array($type)){
                    $errores["fotos-newProd"]="Error. Ingrese fotos validas.";
                    break;
                }
            }
        }
        if(!empty($errores)){
            return redirect()->back()->withInput()->withErrors($errores);
        }
        try{
            $prodDB=new Producto();
            $newProd=[
                "nombre"=>$producto["nombre-newProd"],
                "descripcion"=>$producto["descripcion-newProd"],
                "precio_venta"=>$producto["precioVenta-newProd"],
                "categoria"=>$producto["catego-id-newProd"],
                "cantidad_disponible"=>$producto["cantDispo-newProd"],
                "cantidad_minima"=>$producto["cantMinima-newProd"],
                "tipo_medida"=>$producto["medida-newProd"]
            ];
            if(isset($producto["codigo-newProd"]))$newProd["codigo"]=$producto["codigo-newProd"];

            $idNewProd=$prodDB->newProducto($newProd);
        }catch(Error $e){
            return redirect()->to(route("inicio.index"))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar crear el producto.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
        try{
            if(isset($idNewProd)){
                $provProdDB=new ProveedorProducto();
                foreach($proveedores as $proveedor){
                    $newIdProvProd=false;
                    while(!$newIdProvProd){
                        $newIdProvProd=$provProdDB->newProveedorProducto(["proveedor"=>$proveedor,"producto"=>$idNewProd]);
                    }
                }
            }
        }catch(Error $e){
            return redirect()->to(route("producto.producto",$idNewProd))->with("mensaje",["Mensaje"=>"Producto registrado, ocurrio un error al intentar registrar los proveedores y las fotos del producto.<br>Modifique el producto para registrar corectamente los proveedores y las fotos.","Error"=>""]);
        }
        try{
            if(isset($idNewProd)){
                $urlFotos=[];
                foreach($producto["fotos-newProd"] as $foto){
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($foto, [DataUriImageDecoder::class,Base64ImageDecoder::class,]);
                    $urlfoto="/fotos/productos/".Str::random(12).'.webP';
                    $urlFotos[]=$urlfoto;
                    if($image->width()>750){
                        Storage::disk('public')->put($urlfoto, $image->scale(750)->toWebp());
                    }else{
                        Storage::disk('public')->put($urlfoto, $image->toWebp());
                    }
                }
                $fotoDB=new Foto();
                foreach($urlFotos as $urlFoto){
                    $fotoDB->newFotoProducto([
                                    "url_img"=>"/storage".$urlFoto,
                                    "url_img_online"=>"/storage".$urlFoto,
                                    "producto"=>$idNewProd
                                ]);
                }
            }
        }catch(Error $e){
            return redirect()->to(route("producto.producto",$idNewProd))->with("mensaje",["Mensaje"=>"Producto creado, ocurrio un error al almacenar las fotos del producto.</br>Modifique el producto para almacenar correctamente las fotos del producto.","Error"=>""]);
        }
        return redirect()->to(route("inicio.index"))->with("mensaje",["Mensaje"=>"Producto creado exitosamente.","Success"=>""]);
    }

    public function editProducto(Request $request){
        $producto=$request->all();
        $rulesEditProd=[
            "idProd-editProd" => ["required"],
            "nombre-editProd" => ["required", "min:3", "max:100", new AlfaNunSpacePunct],
            "descripcion-editProd" => ["required", "min:10", "max:500", new AlfaNunSpacePunct],
            "precioVenta-editProd" => ["required", "decimal:0,2", "min:0.01", "max:999999999.99"],
            "cantMinima-editProd" => ["required", "decimal:0,1", "min:1", "max:500"],
            "catego-id-editProd" => ["required"],
            "fotos-editProd" => ["required"],
        ];
        if(isset($producto["codigo-editProd"])){
            $rulesEditProd["codigo-editProd"]=["alpha_num","min:4","max:15"];
        }
        $request->validate($rulesEditProd);

        $idEditProd=$producto["idProd-editProd"];

        $errores=[];

        $prodKeys=array_keys($producto);
        $proveedores=[];
        foreach($prodKeys as $prodKey){
            if(is_array(explode("_",$prodKey))){
                $prov=explode("_",$prodKey);
                if(is_array($prov))
                    if(strcmp($prov[0],"proveedor-editProd")==0)
                        $proveedores[]=$prov[1];
            }
        }
        if(empty($proveedores)){
            $errores["proveedor-editProd"]="Elija al menos un proveedor.";
        }else{
            foreach($proveedores as $proveedor){
                if(!in_array($proveedor,session("proveedores"))){
                    $errores["proveedor-editProd"]="Error. Elija un proveedor válido.";
                    break;
                }
            }
        }
        if(!in_array($producto["catego-id-editProd"],session("categorias"))){
            $errores["catego-id-editProd"]="Error. Elija una categoria válida.";
        }

        if(sizeof($producto["fotos-editProd"])>5){
            $errores["fotos-editProd"]="Ingrese hasta 5 fotos.";
        }

        $fotosDB=new Foto();
        $oldFotos=$fotosDB->getAllFotosProducto($idEditProd);
        $oldFotosUrl=$oldFotos->pluck("url_img")->toArray();
        $oldFotosUrlOnline=$oldFotos->pluck("url_img_online")->toArray();
        $fotoIsOld=[];
        foreach($producto["fotos-editProd"] as $foto){
            if(!in_array($foto,$oldFotosUrl) && !in_array($foto,$oldFotosUrlOnline)){
                if(is_array(explode(",",$foto))){
                    if(base64_decode(explode(",",$foto)[1], true) === false){
                        $errores["fotos-editProd"]="Error. Ingrese fotos validas.";
                        break;
                    }
                }
                if(!is_array(explode(";",$foto))){
                    $errores["fotos-editProd"]="Error. Ingrese fotos validas.";
                    break;
                }else{
                    $type=explode("/",explode(";",$foto)[0]);
                    if(!is_array($type)){
                        $errores["fotos-editProd"]="Error. Ingrese fotos validas.";
                        break;
                    }
                }
            }
            else{
                $fotoIsOld[]=$foto;
            }
        }
        if(!empty($errores)){
            return redirect()->back()->withInput()->withErrors($errores);
        }

        try{
            $prodDB=new Producto();
            $editProd=[
                "nombre"=>$producto["nombre-editProd"],
                "descripcion"=>$producto["descripcion-editProd"],
                "precio_venta"=>$producto["precioVenta-editProd"],
                "cantidad_minima"=>$producto["cantMinima-editProd"],
                "categoria"=>$producto["catego-id-editProd"],
            ];
            if(isset($producto["codigo-editProd"]))$editProd["codigo"]=$producto["codigo-editProd"];

            $prodDB->editProducto($editProd,$idEditProd);

        }catch(Error $e){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar modificar el producto.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
        try{
            if(isset($idEditProd)){
                $provProdDB=new ProveedorProducto();
                $oldProvs=$provProdDB->getAllProveedoresProducto($idEditProd);
                foreach($oldProvs as $oldProv){
                    if(!in_array($oldProv->proveedor,$proveedores)){
                        $provProdDB->deleteProveedorProducto($oldProv->id);
                    }elseif($oldProv->deleted_at!=null){
                        $res=false;
                        while(!$res){
                            $res=$provProdDB->removeDeletedAt($oldProv->id);
                        }
                    }
                }
                $idOldProvs=$oldProvs->pluck("proveedor")->toArray();
                foreach($proveedores as $proveedor){
                    if(!in_array($proveedor,$idOldProvs)){
                        $newIdProvProd=false;
                        while(!$newIdProvProd){
                            $newIdProvProd=$provProdDB->newProveedorProducto(["proveedor"=>$proveedor,"producto"=>$idEditProd]);
                        }
                    }
                }
            }
        }catch(Error $e){
            return redirect()->to(route("producto.producto",$idEditProd))->with("mensaje",["Mensaje"=>"Producto modificado.<br>Ocurrio un error al intentar modificar los proveedores del producto.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
        try{
            foreach($oldFotos as $foto){
                if(!in_array($foto["url_img"],$fotoIsOld) && !in_array($foto["url_img_online"],$fotoIsOld)){
                    $nameFoto=explode("/",$foto["url_img"])[sizeof(explode("/",$foto["url_img"]))-1];
                    if (Storage::disk('public')->exists("/fotos/productos/".$nameFoto)) {
                        Storage::disk('public')->delete("/fotos/productos/".$nameFoto);
                        $fotosDB->deleteFoto($foto["id"]);
                    }
                }
            }
            $urlFotos=[];
            foreach($producto["fotos-editProd"] as $foto){
                if(!in_array($foto,$fotoIsOld)){
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($foto, [DataUriImageDecoder::class,Base64ImageDecoder::class,]);
                    $urlfoto="/fotos/productos/".Str::random(12) . '.webP';
                    $urlFotos[]=$urlfoto;
                    if($image->width()>750){
                        Storage::disk('public')->put($urlfoto, $image->scale(750)->toWebp());
                    }else{
                        Storage::disk('public')->put($urlfoto, $image->toWebp());
                    }
                }
            }
            $fotoDB=new Foto();
            foreach($urlFotos as $urlFoto){
                if(!$fotoDB->newFotoProducto([
                    "url_img"=>"/storage".$urlFoto,
                    "url_img_online"=>"/storage".$urlFoto,
                    "producto"=>$idEditProd
                ])){
                    return redirect()->to(route("producto.producto",$idEditProd))->with("mensaje",["Mensaje"=>"Producto modificado.<br>Ocurrio un error al modificar las fotos del producto.","Error"=>""]);
                }
            }
        }catch(Error $e){
            return redirect()->to(route("producto.producto",$idEditProd))->with("mensaje",["Mensaje"=>"Producto modificado.<br>Ocurrio un error al modificar las fotos del producto.","Error"=>""]);
        }
        return redirect()->to(route("producto.producto",$idEditProd))->with("mensaje",["Mensaje"=>"Producto modificado exitosamente.","Success"=>""]);
    }

    public function deleteProducto(){
        $rulesAdmin=[
            "passAdmin-deleteProd" => ["required","min:6", "max:16", new ValidPass()]
        ];
        request()->validate($rulesAdmin);
        $pass=request()->input("passAdmin-deleteProd");
        $producto=request()->input("idProd-deleteProd");
        $btnDelete=request()->input("eliminarProducto");
        if(!isset($btnDelete) || (!isset($producto))){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }else{
            $admin=User::select("password")->where("name","=","Administrador")->first();
            if (Hash::check($pass, $admin->password)) {
                try{
                    $idProd=$producto;
                    $fotosDB=new Foto();
                    $fotos=$fotosDB->getAllFotosProducto($idProd);
                    if(isset($fotos)){
                        foreach($fotos as $foto){
                            $nameFoto=explode("/",$foto["url_img"])[sizeof(explode("/",$foto["url_img"]))-1];
                            if (Storage::disk('public')->exists("/fotos/productos/".$nameFoto)) {
                                Storage::disk('public')->delete("/fotos/productos/".$nameFoto);
                            }
                        }
                    }
                    $productoDB=new Producto();
                    if(!$productoDB->deleteproducto($idProd)){
                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar eliminar el producto.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                    }
                    return redirect()->to(route("producto.index"))->with("mensaje",["Mensaje"=>"¡Producto eliminado!","Success"=>""]);
                }catch(Error $e){
                    return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                }
            }else{
                return redirect()->back()->withErrors(["passAdmin-deleteProd"=>"Contraseña incorrecta."]);
            }
        }
    }

    public function mostrarProducto($idProd){
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
        $categosDB = new Categoria();
        $categos=$categosDB->getAllCategorias();
        if(!isset($categos)){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }elseif(!session()->has("categorias")){
            session()->put("categorias",Arr::pluck($categos,"id"));
        }
        $provsDB = new Proveedor();
        $provs=$provsDB->getAllNameProveedores();
        if(!isset($provs)){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }elseif(!session()->has("proveedores")){
            $idProvs=$provs->pluck("id")->toArray();
            session()->put("proveedores",$idProvs);
        }
        if(session()->has("trueEditCompra") && in_array($idProd,session("trueEditCompra")["compra"]->productosCompra->pluck("producto")->toArray())){
            $prodDB=new Producto();
            $producto=$prodDB->getProducto($idProd);
            if(!isset($producto)){
                return redirect()->to(route("producto.inicio"))->with("mensaje",["Mensaje"=>"Producto no disponible.","Error"=>""]);
            }else{
                $idsProdsCompra=session("trueEditCompra")["compra"]->productosCompra->pluck("producto")->toArray();
                $indexIdProd=array_search($idProd,$idsProdsCompra);
                if($indexIdProd !== false){
                    $data=[
                            "categorias"=>$categos,
                            "proveedores"=>$provs,
                            "producto"=>$producto,
                            "indexIdProdCompra"=>$indexIdProd
                          ];
                    return view("producto",$data);
                }else{
                    return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                }
            }
        }else{
            $prodDB=new Producto();
            $producto=$prodDB->getProducto($idProd);
            if(!isset($producto)){
                return redirect()->to(route("producto.inicio"))->with("mensaje",["Mensaje"=>"Producto no disponible.","Error"=>""]);
            }else{
                $data=[
                        "categorias"=>$categos,
                        "proveedores"=>$provs,
                        "producto"=>$producto
                      ];
                return view("producto",$data);
            }
        }
    }

    public function addToCart(){
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
        $prodDB=new Producto();
        $producto=$prodDB->getProductoToCart(request()->input("id"));
        if(!is_array($producto)){
            return json_encode(["Error"=>"Ocurrio un error al agregar el producto al carrito."]);
        }else{
            $fmt = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
            $fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 4);
            $fmt2 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
            $fmt2->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
            $fmt0 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
            $fmt0->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
            $precioVenta = BigDecimal::of($producto["precio_venta"])->toScale(4, RoundingMode::HALF_UP);
            $producto["precio_venta"]=$precioVenta;
            $producto["precio_venta_Print"]=$fmt2->format($precioVenta->__toString());
            $cantidades=["cantidad"=>request()->input("cantidad"),"medida"=>request()->input("medida")];
            switch($cantidades["medida"]){
                case "Unidad":  if(strcmp($producto["tipo_medida"],"Unidad")!=0){
                                    return json_encode(["Error"=>"Ocurrio un error al agregar el producto al carrito."]);
                                }elseif(!is_numeric($cantidades["cantidad"])){
                                    return json_encode(["Error"=>"Ocurrio un error al agregar el producto al carrito."]);
                                }else{
                                    $cantidades["cantidad"]=BigDecimal::of($cantidades["cantidad"])->toScale(4, RoundingMode::HALF_UP);
                                    if($cantidades["cantidad"]->isLessThan("1")){
                                        return json_encode(["Error"=>"Ocurrio un error al agregar el producto al carrito."]);
                                    }
                                    $cantidades["precioT"]=$cantidades["cantidad"]->multipliedBy($precioVenta)->toScale(4, RoundingMode::HALF_UP);
                                }
                                break;
                default:    $tipos=["Kilogramo","Metro","Litro"];
                            if(!in_array($producto["tipo_medida"],$tipos)){
                                return json_encode(["Error"=>"Ocurrio un error al agregar el producto al carrito."]);
                            }elseif(!is_numeric($cantidades["cantidad"])){
                                return json_encode(["Error"=>"Ocurrio un error al agregar el producto al carrito."]);
                            }else{
                                $cantidades["cantidad"]=BigDecimal::of($cantidades["cantidad"])->toScale(4, RoundingMode::HALF_UP);
                                if(strcmp($cantidades["medida"],"precio")==0){
                                    if($cantidades["cantidad"]->isLessThan("0.5")){
                                        return json_encode(["Error"=>"Ocurrio un error al agregar el producto al carrito."]);
                                    }
                                    $cantProd = $cantidades["cantidad"]->dividedBy($precioVenta, 4, RoundingMode::DOWN);
                                    $cantidades["precioT"]=$cantidades["cantidad"]->toScale(4, RoundingMode::HALF_UP);
                                    $cantidades["cantidad"]=$cantProd;
                                }else{
                                    if($cantidades["cantidad"]->isLessThan("0.0001")){
                                        return json_encode(["Error"=>"Ocurrio un error al agregar el producto al carrito."]);
                                    }
                                    $cantidades["precioT"]=$cantidades["cantidad"]->multipliedBy($precioVenta)->toScale(4, RoundingMode::HALF_UP);
                                }
                            }
            }
            $cantDisp=BigDecimal::of($producto["cantidad_disponible"])->toScale(4, RoundingMode::HALF_UP);
            if($cantDisp->isLessThan($cantidades["cantidad"])){
                return json_encode(["Error"=>"Stock insuficiente."]);
            }else{

                $carrito=session("carrito");
                $newProd=false;
                if(is_array($carrito["productos"])){
                    $idProdsCart=Arr::pluck($carrito["productos"],"id");
                    if(in_array($producto["id"],$idProdsCart)){
                        $key=array_search($producto["id"],$idProdsCart);
                        $carrito["productos"][$key]["totalC"]=$carrito["productos"][$key]["totalC"]->plus($cantidades["cantidad"])->toScale(4, RoundingMode::HALF_UP);
                        if($cantDisp->isLessThan($carrito["productos"][$key]["totalC"])){
                            return json_encode(["Error"=>"Stock insuficiente."]);
                        }else{
                            $aux=$carrito["productos"][$key]["totalC"]->toScale(0, RoundingMode::DOWN);
                            if($aux->isLessThan($carrito["productos"][$key]["totalC"])){
                                $carrito["productos"][$key]["totalC_Print"]=$fmt->format($carrito["productos"][$key]["totalC"]->__toString());
                            }else{
                                $carrito["productos"][$key]["totalC_Print"]=$fmt0->format($carrito["productos"][$key]["totalC"]->toScale(0, RoundingMode::DOWN)->__toString());
                            }
                            $carrito["productos"][$key]["totalV"]=$carrito["productos"][$key]["totalV"]->plus($cantidades["precioT"])->toScale(4, RoundingMode::HALF_UP);
                            $carrito["productos"][$key]["totalV_Print"]=$fmt->format($carrito["productos"][$key]["totalV"]->__toString());
                        }
                    }else{
                        $producto["totalC"]=$cantidades["cantidad"];
                        if($cantDisp->isLessThan($producto["totalC"])){
                            return json_encode(["Error"=>"Stock insuficiente."]);
                        }else{
                            $aux=$producto["totalC"]->toScale(0, RoundingMode::DOWN);
                            if($aux->isLessThan($producto["totalC"])){
                                $producto["totalC_Print"]=$fmt->format($producto["totalC"]->__toString());
                            }else{
                                $producto["totalC_Print"]=$fmt0->format($producto["totalC"]->toScale(0, RoundingMode::DOWN)->__toString());
                            }
                            $producto["totalV"]=$cantidades["precioT"];
                            $producto["totalV_Print"]=$fmt->format($producto["totalV"]->__toString());
                            $carrito["productos"][]=$producto;
                            $newProd=true;
                        }
                    }
                }else{
                    $producto["totalC"]=$cantidades["cantidad"];
                    if($cantDisp->isLessThan($producto["totalC"])){
                            return json_encode(["Error"=>"Stock insuficiente."]);
                    }else{
                        $aux=$producto["totalC"]->toScale(0, RoundingMode::DOWN);
                        if($aux->isLessThan($producto["totalC"])){
                            $producto["totalC_Print"]=$fmt->format($producto["totalC"]->__toString());
                        }else{
                            $producto["totalC_Print"]=$fmt0->format($producto["totalC"]->toScale(0, RoundingMode::DOWN)->__toString());
                        }
                        $producto["totalV"]=$cantidades["precioT"];
                        $producto["totalV_Print"]=$fmt->format($producto["totalV"]->__toString());
                        $carrito["productos"][]=$producto;
                        $newProd=true;
                    }
                }
                $carrito["subtotal"]=$carrito["subtotal"]->plus($cantidades["precioT"])->toScale(4, RoundingMode::HALF_UP);
                $carrito["subtotal_Print"]=$fmt->format($carrito["subtotal"]->__toString());
                $carrito["total"]=$carrito["total"]->plus($cantidades["precioT"])->toScale(4, RoundingMode::HALF_UP);
                $carrito["total_Print"]=$fmt->format($carrito["total"]->__toString());
                session()->put("carrito",$carrito);
                if($newProd){
                    $newProdCart=view("components.carrito-producto-component",["producto"=>$producto])->render();
                }
                else{
                    $newProdCart=null;
                }
                return json_encode([
                    "success"=>true,
                    "newProdCart"=>$newProdCart,
                    "subtotal"=>$carrito["subtotal_Print"],
                    "total"=>$carrito["total_Print"]
                ]);
            }
        }
    }

    public function refreshCart(){
        $producto=request()->array(["inputVal","idProd"]);
        if(empty($producto) || !isset($producto["idProd"])){
            return json_encode(["Error"=>"Ocurrio un error al modificar un producto del carrito."]);
        }elseif(!isset($producto["inputVal"])){
            return json_encode(["Error"=>"Ingrese un valor válido para modificar un producto del carrito."]);
        }
        return $this->refreshVal($producto);
    }

    private function refreshVal($producto){
        $carrito=session("carrito");
        if(!is_array($carrito["productos"])){
            return json_encode(["Error"=>"Ocurrio un error al modificar un producto del carrito."]);
        }else{
            $fmt = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
            $fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 4);
            $fmt0 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
            $fmt0->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
            $idProdsCart=Arr::pluck($carrito["productos"], 'id');
            if(in_array($producto["idProd"],$idProdsCart)){
                $key=array_search($producto["idProd"],$idProdsCart);
                $oldProd=$carrito["productos"][$key];
                if(!is_numeric($producto["inputVal"])){
                    return json_encode(["Error"=>"Ingrese un valor numerico válido para modificar un producto del carrito."]);
                }
                $newCantidad=BigDecimal::of($producto["inputVal"])->toScale(4, RoundingMode::HALF_UP);
                $prodDB=new Producto();
                $prodInCart=$prodDB::where("id","=",$oldProd["id"])->select("cantidad_disponible")->first();
                $cantDisp=BigDecimal::of($prodInCart->cantidad_disponible)->toScale(4, RoundingMode::HALF_UP);
                if($cantDisp->isLessThan($newCantidad)){
                    return json_encode(["Error"=>"Stock insuficiente."]);
                }
                $precioVenta=data_get($oldProd,"precio_venta");
                $oldPrec=data_get($oldProd,"totalV");
                $newPrec=BigDecimal::of($newCantidad->multipliedBy($precioVenta))->toScale(4, RoundingMode::HALF_UP);

                $subtotal=$carrito["subtotal"];
                $subtotal=$subtotal->plus($newPrec)->toScale(4, RoundingMode::HALF_UP);
                $subtotal=$subtotal->minus($oldPrec)->toScale(4, RoundingMode::HALF_UP);
                $subtotal_Print=$fmt->format($subtotal->__toString());

                $total=$carrito["total"];
                $total=$total->minus($oldPrec)->toScale(4, RoundingMode::HALF_UP);
                $total=$total->plus($newPrec)->toScale(4, RoundingMode::HALF_UP);
                $total_Print=$fmt->format($total->__toString());
            
                $newPrec_Print=$fmt->format($newPrec->__toString());

                $oldProd["totalC"]=$newCantidad;
                $aux=$oldProd["totalC"]->toScale(0, RoundingMode::DOWN);
                if($aux->isLessThan($oldProd["totalC"])){
                    $oldProd["totalC_Print"]=$fmt->format($oldProd["totalC"]->__toString());
                }else{
                    $oldProd["totalC_Print"]=$fmt0->format($oldProd["totalC"]->toScale(0, RoundingMode::DOWN)->__toString());
                }
                $oldProd["totalV"]=$newPrec;
                $oldProd["totalV_Print"]=$fmt->format($oldProd["totalV"]->__toString());

                $carrito["productos"][$key]=$oldProd;
                $carrito["subtotal"]=$subtotal;
                $carrito["total"]=$total;
                $carrito["subtotal_Print"]=$subtotal_Print;
                $carrito["total_Print"]=$total_Print;
                session()->put("carrito",$carrito);

                return json_encode([
                    "success"=>true,
                    "newPrecioVT"=>$newPrec_Print,
                    "newSubtotal"=>$subtotal_Print,
                    "newTotal"=>$total_Print    
                ]);
            }else{
                return json_encode(["Error"=>"Ocurrio un error con el ID del producto a modificar."]);
            }
        }
    }

    public function refreshAllCart(){
        $prods=request()->array(["modifiedIds","modifiedVals"]);
        if(empty($prods) || !isset($prods["modifiedIds"]) || !isset($prods["modifiedVals"])){
            return json_encode(["Error"=>"Ocurrio un error al intentar modificar un producto."]);
        }
        if(sizeof($prods["modifiedIds"])!=sizeof($prods["modifiedVals"])){
            return json_encode(["Error"=>"Ingrese un valor válido para modificar un producto"]);
        }
        $errorRefresh=false;
        for($i=0;$i<sizeof($prods["modifiedIds"]);$i++){
            $producto=[
                "idProd"=>$prods["modifiedIds"][$i],
                "inputVal"=>$prods["modifiedVals"][$i]
            ];
            $res=$this->refreshVal($producto);
            $res=json_decode($res);
            if(!isset($res->success))$errorRefresh=$res->Error;
        }
        return json_encode(["Error"=>$errorRefresh]);
    }

    public function deleteOfCart(){
        $idProd=request()->input("idProd");
        $carrito=session("carrito");
        if(is_array($carrito["productos"])){
            $idProdsCart=Arr::pluck($carrito["productos"], 'id');
            if(in_array($idProd,$idProdsCart)){
                $fmt = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
                $fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 4);
                $key=array_search($idProd,$idProdsCart);
                $oldPrecio=$carrito["productos"][$key]["totalV"]->toScale(4, RoundingMode::HALF_UP);
                $carrito["subtotal"]=$carrito["subtotal"]->minus($oldPrecio)->toScale(4, RoundingMode::HALF_UP);
                $carrito["subtotal_Print"]=$fmt->format($carrito["subtotal"]->__toString());
                $carrito["total"]=$carrito["total"]->minus($oldPrecio)->toScale(4, RoundingMode::HALF_UP);
                $carrito["total_Print"]=$fmt->format($carrito["total"]->__toString());
                $arrMin=array_slice($carrito["productos"],0,$key);
                $trashArr=array_splice($carrito["productos"],0,$key+1,$arrMin);
                $trashArr=null;
                if(sizeof($carrito["productos"])==0){
                    $carrito["productos"]=null;
                }
                session()->put("carrito",$carrito);
                return json_encode([
                    "success"=>true,
                    "subtotalV"=>$carrito["subtotal_Print"],
                    "totalV"=>$carrito["total_Print"]
                ]);
            }
        }
        return json_encode(["error"=>"Ocurrio un error al eliminar un producto del carrito."]);
    }

    public function ordenarProductos($orden){
        switch($orden){
            case "MasVendido": session()->put("ordenProductos","MasVendido");
                    break;
            case "MenosVendido": session()->put("ordenProductos","MenosVendido");
                    break;
            case "NombreAZ": session()->put("ordenProductos","NombreAZ");
                    break;
            case "NombreZA": session()->put("ordenProductos","NombreZA");
                    break;
            case "MayorPrecio": session()->put("ordenProductos","MayorPrecio");
                    break;
            case "MenorPrecio": session()->put("ordenProductos","MenorPrecio");
                    break;
            default: session()->put("ordenProductos","MasVendido");
        }
    }

    private function getOrden(){
        switch(session("ordenProductos")){
            case "MasVendido": return ["orden"=>"mas_vendidos", "direccion"=>"desc"];
            case "MenosVendido": return ["orden"=>"mas_vendidos", "direccion"=>"asc"];
            case "NombreAZ": return ["orden"=>"nombre", "direccion"=>"asc"];
            case "NombreZA": return ["orden"=>"nombre", "direccion"=>"desc"];
            case "MayorPrecio": return ["orden"=>"precio_venta", "direccion"=>"desc"];
            case "MenorPrecio": return ["orden"=>"precio_venta", "direccion"=>"asc"];
            default: return ["orden"=>"mas_vendidos", "direccion"=>"desc"];
        };
    }
}
