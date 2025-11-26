<?php

namespace App\Http\Controllers;

use App\Models\DevolucionVenta;
use App\Models\Producto;
use App\Models\ProductosDevolucionVenta;
use App\Models\ProductosVenta;
use App\Models\RecibosDevolucionVenta;
use App\Models\RecibosVenta;
use App\Models\UbicacionVenta;
use App\Models\User;
use App\Models\Venta;
use App\Rules\AlfaNunSpacePunct;
use App\Rules\ValidPass;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Carbon\Carbon;
use Error;
use Hamcrest\Core\IsEqual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Decoders\Base64ImageDecoder;
use Intervention\Image\Decoders\DataUriImageDecoder;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class VentaController extends Controller
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
        $ventaDB=new Venta();
        session()->put("pagina","ventas");
        if(session()->has("filtroVentas")){
            if(!session()->has("ordenarVentas")){
                $this->ordenarVentas(null);
            }
            $orden=$this->getOrdenVentas();
            $pago=session("filtroVentas")["pago"];
            $pedido=session("filtroVentas")["pedido"];
            $entrega=session("filtroVentas")["entrega"];
            $fInicio=session("filtroVentas")["fInicio"];
            $fFin=session("filtroVentas")["fFin"];
            $devueltas=session("filtroVentas")["devueltas"];
            $ventasData=$ventaDB->getAllVentasActivas($orden,$pedido,$entrega,$pago,$fInicio,$fFin,$devueltas);
            $ganancias=$ventaDB->gananciasMensuales();
            $data=[
                "ventas"=>$ventasData["ventas"],
                "totalVentas"=>$ventasData["totalVenta"],
                "ganancias"=>$ganancias[0],
                "gananciasVisibles"=>$ganancias[1]
            ];
        }else{
            if(!session()->has("ordenarVentas")){
                $this->ordenarVentas(null);
            }
            $orden=$this->getOrdenVentas();
            $ventasData=$ventaDB->getAllVentasActivas($orden);
            $ganancias=$ventaDB->gananciasMensuales();
            $data=[
                "ventas"=>$ventasData["ventas"],
                "totalVentas"=>$ventasData["totalVenta"],
                "ganancias"=>$ganancias[0],
                "gananciasVisibles"=>$ganancias[1]
            ];
        }
        if(session()->has("totalesVentas")){
            $tV=session("totalesVentas");
            $rango=$tV["rango"];
            if(isset($tV["fInicio"]))$fInicio=$tV["fInicio"];else $fInicio=null;
            if(isset($tV["fFin"]))$fFin=$tV["fFin"];else $fFin=null;
            if(isset($tV["tipoPago"])){
                $tipoPago=$tV["tipoPago"];
                $data["totalesVentas"]=$ventaDB->getTotalesVentas($rango,$fInicio,$fFin,$tipoPago);
            }else{
                $data["totalesVentas"]=$ventaDB->getTotalesVentas($rango,$fInicio,$fFin);
            }
        }else{
            $data["totalesVentas"]=$ventaDB->getTotalesVentas();
        }
        return view("ventas",$data);
    }

    public function cleanIndex(){
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
        if(session()->has("totalesVentas")){
            session()->forget("totalesVentas");
        }
        return redirect()->to(route("venta.index"));
    }

    public function procesarVenta(){
        if(session()->has("editVenta")){
            $ventaDB=new Venta();
            $venta=$ventaDB->getVenta(session("editVenta")["idVenta"]);
            if(!isset($venta)){
                $idVenta=session("editVenta")["idVenta"];
                session()->forget("editVenta");
                return redirect()->to(route("venta.venta",$idVenta))->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }else{
                $data=[
                    "venta"=>$venta
                ];
                return view("procesarVenta",$data);
            }
        }else{
            return view("procesarVenta");
        }
    }

    public function saveVenta(){
        $venta=request()->array([
            "tipoVenta-newVenta",
            "direccion-newVenta",
            "manzanaPiso-newVenta",
            "casaDepto-newVenta",
            "coordsDestino-newVenta",
            "detalles-newVenta",
            "receptor-newVenta",
            "contacto-newVenta",
            "fechaEntregaMin-newVenta",
            "fechaEntregaMax-newVenta",
            "fotos-newVenta",
            "metodoPago-newVenta",
            "estadoEntrega-newVenta",
        ]);
        
        $rulesNewVenta=[
            "tipoVenta-newVenta" => ["required","in:Envio,Local"],
            "metodoPago-newVenta" => ["required","in:Pendiente,Tarjeta,Efectivo,Mixto"]
        ];
        request()->validate($rulesNewVenta);

        $rulesNewVentaPlus=[];
        if(strcmp($venta["tipoVenta-newVenta"],"Envio")==0){
            $rulesNewVentaPlus=[
                "direccion-newVenta" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:10", "max:250"],
                "manzanaPiso-newVenta" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:1", "max:50"],
                "casaDepto-newVenta" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:1", "max:50"],
                "receptor-newVenta" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:7", "max:50"],
                "contacto-newVenta" => ["required","min_digits:9", "max_digits:14"],
                "fechaEntregaMin-newVenta"=>["required", "date"],
                "fechaEntregaMax-newVenta"=>["required", "date"]
            ];
            if(isset($venta["detalles-newVenta"])){
                $rulesNewVentaPlus["detalles-newVenta"] = ["min:10","max:250",new AlfaNunSpacePunct()];
            }
        }elseif(!isset($venta["estadoEntrega-newVenta"])){
            $rulesNewVentaPlus=[
                "receptor-newVenta" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:7", "max:50"],
                "contacto-newVenta" => ["required","min_digits:9", "max_digits:14"],
            ];
        }else{
            $rulesNewVentaPlus=[
                "estadoEntrega-newVenta" => ["in:Completa"]
            ];
        }
        if(strcmp($venta["metodoPago-newVenta"],"Efectivo")!=0 && strcmp($venta["metodoPago-newVenta"],"Pendiente")!=0){
            $rulesNewVentaPlus["fotos-newVenta"]=["required"];
        }
        request()->validate($rulesNewVentaPlus);

        $errores=[];
        if(strcmp($venta["metodoPago-newVenta"],"Efectivo")!=0 && strcmp($venta["metodoPago-newVenta"],"Pendiente")!=0){
            if(sizeof($venta["fotos-newVenta"])>5){
                $errores["fotos-newVenta"]="Ingrese hasta 5 recibos.";
            }
            foreach($venta["fotos-newVenta"] as $foto){
                if(is_array(explode(",",$foto))){
                    if(base64_decode(explode(",",$foto)[1], true) === false){
                        $errores["fotos-newVenta"]="Error. Ingrese recibos validos.";
                        break;
                    }
                }
                if(!is_array(explode(";",$foto))){
                    $errores["fotos-newVenta"]="Error. Ingrese recibos validos.";
                    break;
                }else{
                    $type=explode("/",explode(";",$foto)[0]);
                    if(!is_array($type)){
                        $errores["fotos-newVenta"]="Error. Ingrese recibos validos.";
                        break;
                    }
                }
            }
        }
        if(isset($venta["coordsDestino-newVenta"])){
            if(str_contains($venta["coordsDestino-newVenta"],"[;]")){
                $coords=explode("[;]",$venta["coordsDestino-newVenta"]);
                if(sizeof($coords)!=2){
                    $errores["coordsDestino-newVenta"]="Coordenadas invalidas.";
                }else{
                    foreach($coords as $coord){
                        if(!is_numeric($coord)){
                            $errores["coordsDestino-newVenta"]="Coordenadas invalidas.";
                            break;
                        }
                    }
                }
            }else{
                $errores["coordsDestino-newVenta"]="Coordenadas invalidas.";
            }
        }
        if(!empty($errores)){
                return redirect()->back()->withInput()->withErrors($errores);
            }
        if(strcmp($venta["tipoVenta-newVenta"],"Envio")==0){
            $minF=Carbon::parse($venta["fechaEntregaMin-newVenta"]);
            $maxF=Carbon::parse($venta["fechaEntregaMax-newVenta"]);
            $minF=$this->controlFechas($minF,now(),$maxF);
            $maxF=$this->controlFechas($maxF,$minF,now()->setTimeFromTimeString("20:30")->addDays(7));
            $venta["fechaEntregaMin-newVenta"]=$minF->format("Y-m-d H:i");
            $venta["fechaEntregaMax-newVenta"]=$maxF->format("Y-m-d H:i");
        }

        try{
            $ventaDB=new Venta();
            $carrito=session("carrito");
            $newVenta=[
                "tipo_pago"=>$venta["metodoPago-newVenta"],
                "tipo_venta"=>$venta["tipoVenta-newVenta"],
                "monto_subtotal"=>$carrito["total"]->__toString(),
                "monto_total"=>$carrito["subtotal"]->__toString(),
            ];
            if(!isset($venta["estadoEntrega-newVenta"])){
                $newVenta["nombre_receptor"]=$venta["receptor-newVenta"];
                $newVenta["telefono_receptor"]=$venta["contacto-newVenta"];
            }else{
                $newVenta["estado_entrega"]=$venta["estadoEntrega-newVenta"];
            }
            $idNewVenta=$ventaDB->newVenta($newVenta);
        }catch(Error $e){
            return redirect()->to(route("venta.procesarventa"))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar crear la venta.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
        try{
            if(isset($idNewVenta)){
                $prodVentDB=new ProductosVenta();
                $carrito=session("carrito");
                foreach($carrito["productos"] as $producto){
                    $newProdV=[
                        "producto"=>$producto["id"],
                        "venta"=>$idNewVenta,
                        "cantidad"=>$producto["totalC"]->__toString(),
                        "precio_venta"=>$producto["precio_venta"]->__toString(),
                        "total_producto"=>$producto["totalV"]->__toString(),
                    ];
                    if(!$prodVentDB->newProductoVenta($newProdV)){
                        Venta::where("id","=",$idNewVenta)->forceDelete();
                        return redirect()->to(route("venta.procesarventa"))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar crear la venta.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                    }
                }
            }
        }catch(Error $e){
            Venta::where("id","=",$idNewVenta)->forceDelete();
            return redirect()->to(route("venta.procesarventa"))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar crear la venta.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
        try{
            $idProdsModifieds=[];
            $prodDB=new Producto();
            foreach($carrito["productos"] as $producto){
                $oldCantDisp=BigDecimal::of($producto["cantidad_disponible"])->toScale(4,RoundingMode::HALF_UP);
                $newCantDisp=$oldCantDisp->minus($producto["totalC"])->toScale(4,RoundingMode::HALF_UP);
                $newCant=[
                    "cantidad_disponible"=>$newCantDisp->__tostring()
                ];
                $res=false;
                while(!$res){
                    $res=$prodDB->editProducto($newCant,$producto["id"]);
                }
                $idProdsModifieds[]=$producto["id"];
            }
        }catch(Error $e){
            session()->put("errorVenta",[
                "productosCargados"=>$idProdsModifieds,
                "ubicacion"=>false,
                "recibos"=>false,
                "oldCarrito"=>$carrito,
                "datosVenta"=>$venta,
            ]);
            session()->forget("carrito");
            return redirect()->to(route("venta.procesarventa"))->with("mensaje",["Mensaje"=>"Venta creada.<br>Ocurrio un erro al vincular los productos con la venta.<br>Intente enviar los datos de la venta otra vez.","Error"=>""]);
        }
        try{
            if(strcmp($venta["tipoVenta-newVenta"],"Envio")==0 && isset($idNewVenta)){
                $ubicVentDB=new UbicacionVenta();
                $newUbicacion=[
                    "venta"=>$idNewVenta,
                    "direccion"=>$venta["direccion-newVenta"],
                    "casa_depto"=>$venta["casaDepto-newVenta"],
                    "manzana_piso"=>$venta["manzanaPiso-newVenta"],
                    "fecha_entrega_min"=>$venta["fechaEntregaMin-newVenta"],
                    "fecha_entrega_max"=>$venta["fechaEntregaMax-newVenta"],
                ];
                if(isset($venta["detalles-newVenta"])){
                    $newUbicacion["descripcion"]=$venta["detalles-newVenta"];
                }
                if(isset($venta["coordsDestino-newVenta"])){
                    $newUbicacion["lat"]=explode("[;]",$venta["coordsDestino-newVenta"])[0];
                    $newUbicacion["lng"]=explode("[;]",$venta["coordsDestino-newVenta"])[1];
                }
                $res=false;
                while(!$res){
                    $res=$ubicVentDB->newUbicacionVenta($newUbicacion);
                }
            }
        }catch(Error $e){
            session()->put("errorVenta",[
                "productosCargados"=>$idProdsModifieds,
                "ubicacion"=>false,
                "recibos"=>false,
                "oldCarrito"=>$carrito,
                "datosVenta"=>$venta,
            ]);
            session()->forget("carrito");
            return redirect()->to(route("venta.procesarventa"))->with("mensaje",["Mensaje"=>"Venta creada.<br>Ocurrio un erro al almacenar la ubicación para la entrega de la venta.<br>Intente enviar los datos de la venta otra vez.","Error"=>""]);
        }
        if(isset($venta["fotos-newVenta"])){
            try{
                if(isset($idNewVenta)){
                    $urlFotos=[];
                    foreach($venta["fotos-newVenta"] as $foto){
                        $manager = new ImageManager(new Driver());
                        $image = $manager->read($foto, [DataUriImageDecoder::class,Base64ImageDecoder::class,]);
                        $urlfoto="/fotos/recibos/ventas/".Str::random(12) . '.webP';
                        $urlFotos[]=$urlfoto;
                        if($image->width()>750){
                            Storage::disk('public')->put($urlfoto, $image->scale(750)->toWebp());
                        }else{
                            Storage::disk('public')->put($urlfoto, $image->toWebp());
                        }
                    }
                    $fotoDB=new RecibosVenta();
                    foreach($urlFotos as $urlFoto){
                        $res=false;
                        while(!$res){
                            $res=$fotoDB->newReciboVenta([
                                            "url_img"=>"/storage".$urlFoto,
                                            "url_img_online"=>"/storage".$urlFoto,
                                            "venta"=>$idNewVenta
                                        ]);
                        }
                    }
                }
            }catch(Error $e){
                session()->put("errorVenta",[
                    "productosCargados"=>$idProdsModifieds,
                    "ubicacion"=>true,
                    "recibos"=>false,
                    "oldCarrito"=>$carrito,
                    "datosVenta"=>$venta,
                ]);
                session()->forget("carrito");
                return redirect()->to(route("venta.procesarventa"))->with("mensaje",["Mensaje"=>"Venta creada.<br>Ocurrio un erro al almacenar los recibos de la venta.<br>Intente enviar los datos de la venta otra vez.","Error"=>""]);
            }
        }
        session()->forget("carrito");
        return redirect()->to(route("venta.index"))->with("mensaje",["Mensaje"=>"Venta creada con exito.","Success"=>""]);
    }

    public function limpiarCarrito(){
        if(request()->input("limpiarCarrito")){
            session()->forget("carrito");
            return redirect()->to(route("venta.index"))->with("mensaje",["Mensaje"=>"¡Carrito limpiado!","Success"=>""]);
        }else{
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar limpiar el carrito.","Error"=>""]);
        }
    }

    private function controlFechas($fecha,$min,$max){
        $nowF=Carbon::parse(now());
        if($nowF->hourOfDay()>18 || ($nowF->hourOfDay()>=18 && $nowF->minuteOfHour()>30)){
            $nowF_min=now()->setTimeFromTimeString("07:30")->addDay();
        }elseif($nowF->hourOfDay()<7 || ($nowF->hourOfDay()<=7 && $nowF->minuteOfHour()<30)){
            $nowF_min=$nowF->setTimeFromTimeString("07:30");
        }else{
            $nowF_min=$nowF;
        }
        if($min->lessThan($nowF_min)){
            $f_min=$nowF_min;
        }else{
            $f_min=$min;
        }
        $nowF_max=now()->setDateTimeFrom("20:30")->addDays(7);
        if($nowF_max->lessThan($max)){
            $f_max=$nowF_max;
        }else{
            $f_max=$max;
        }
        if(!$fecha->between($f_min,$f_max)){
            if($fecha->lessThan($f_min)){
                $fecha=$f_min;
            }
            if($fecha->greaterThan($f_max)){
                $fecha=$f_max;
            }
        }
        return $fecha;
    }


    public function buscarVenta(Request $request){
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
        $busqueda=$request->array(["pago","pedido","entrega","fechas","fInicio","fFin","todo","devueltas"]);
        if(isset($busqueda["todo"])){
            session()->forget("filtroVentas");
            $this->ordenarVentas(null);
            return redirect()->to(route("venta.index"));
        }
        else{
            if(!session()->has("filtroVentas")){
                session()->put("filtroVentas",[
                    "pago"=>null,
                    "pedido"=>null,
                    "entrega"=>null,
                    "fInicio"=>null,
                    "fFin"=>null,
                    "devueltas"=>0,
                ]);
            }
            if(isset($busqueda["entrega"])){
                $filtro=session("filtroVentas");
                if(in_array($busqueda["entrega"],["Pendiente","Completa"])){
                    $filtro["entrega"]=$busqueda["entrega"];
                }else{
                    $filtro["entrega"]=null;
                }
                session()->put("filtroVentas",$filtro);
            }
            if(isset($busqueda["pedido"])){
                $filtro=session("filtroVentas");
                if(in_array($busqueda["pedido"],["Envio","Local"])){
                    $filtro["pedido"]=$busqueda["pedido"];
                }else{
                    $filtro["pedido"]=null;
                }
                session()->put("filtroVentas",$filtro);
            }
            if(isset($busqueda["pago"])){
                $filtro=session("filtroVentas");
                if(in_array($busqueda["pago"],["Tarjeta","Efectivo","Mixto"])){
                    $filtro["pago"]=$busqueda["pago"];
                }else{
                    $filtro["pago"]=null;
                }
                session()->put("filtroVentas",$filtro);
            }
            if(isset($busqueda["devueltas"])){
                $filtro=session("filtroVentas");
                if(in_array($busqueda["devueltas"],[1,0])){
                    $filtro["devueltas"]=$busqueda["devueltas"];
                }else{
                    $filtro["devueltas"]=0;
                }
                session()->put("filtroVentas",$filtro);
            }
            if(isset($busqueda["fechas"])){
                if(isset($busqueda["fInicio"])){
                    $filtro=session("filtroVentas");
                    $fI=date_create($busqueda["fInicio"]);
                    if(date_timestamp_get($fI)<1420081200){
                        $filtro["fInicio"]=date("Y-m-d H:i",1420081200);
                    }else{
                        $filtro["fInicio"]=date("Y-m-d H:i",date_timestamp_get($fI));
                    }
                    session()->put("filtroVentas",$filtro);
                }elseif(isset(session("filtroVentas")["fInicio"])){
                    $filtro=session("filtroVentas");
                    $filtro["fInicio"]=null;
                    session()->put("filtroVentas",$filtro);
                }
                if(isset($busqueda["fFin"])){
                    $filtro=session("filtroVentas");
                    $fF=date_create($busqueda["fFin"]);
                    if(date_timestamp_get($fF)<1420081200){
                        $filtro["fFin"]=date("Y-m-d H:i",1420081200);
                    }else{
                        $filtro["fFin"]=date("Y-m-d H:i",date_timestamp_get($fF));
                    }
                    session()->put("filtroVentas",$filtro);
                }elseif(isset(session("filtroVentas")["fFin"])){
                    $filtro=session("filtroVentas");
                    $filtro["fFin"]=null;
                    session()->put("filtroVentas",$filtro);
                }
            }
            return redirect()->to(route("venta.index"));
        }
    }

    public function ordenarVentas($orden){
        switch($orden){
            case "MasReciente": session()->put("ordenarVentas","MasReciente");
                    break;
            case "MenosReciente": session()->put("ordenarVentas","MenosReciente");
                    break;
            case "MayorMonto": session()->put("ordenarVentas","MayorMonto");
                    break;
            case "MenorMonto": session()->put("ordenarVentas","MenorMonto");
                    break;
            default: session()->put("ordenarVentas","MasReciente");
        }
    }

    public function setOrdenVentas(){
        $orden=request()->input("orden");
        $this->ordenarVentas($orden);
        return redirect()->to(route("venta.index"));
    }

    private function getOrdenVentas(){
        switch(session("ordenarVentas")){
            case "MasReciente": return ["orden"=>"fecha_venta", "direccion"=>"desc"];
            case "MenosReciente": return ["orden"=>"fecha_venta", "direccion"=>"asc"];
            case "MayorMonto": return ["orden"=>"monto_total", "direccion"=>"desc"];
            case "MenorMonto": return ["orden"=>"monto_total", "direccion"=>"asc"];
            default: return ["orden"=>"fecha_venta", "direccion"=>"desc"];
        };
    }

    public function mostrarVenta($idVenta){
        $ventaDB=new Venta();
        $venta=$ventaDB->getVenta($idVenta);
        if(isset($venta)){
            $devs=$venta->devoluciones->toArray();
            if(sizeof($devs)>0){
                $devs=$venta->devoluciones;
                $prodsV=$venta->productosVenta;
                $trueCant=0;
                $trueTotalV=BigDecimal::of("0.0000");
                foreach($prodsV as $prodV){
                    $oldCantProd=BigDecimal::of($prodV->cantidad);
                    foreach($devs as $dev){
                        foreach($dev->productos as $devProd){
                            if($prodV->producto == $devProd->producto){
                                $devCantProd=BigDecimal::of($devProd->cantidad);
                                $oldCantProd=$oldCantProd->minus($devCantProd)->toScale(4,RoundingMode::HALF_UP);
                            }
                        }
                    }
                    if($oldCantProd->isGreaterThan(0)){
                        $trueCant+=1;
                    }
                }
                foreach($devs as $dev){
                    $totalDev=BigDecimal::of($dev->monto_total);
                    $trueTotalV=$trueTotalV->plus($totalDev);
                }
                $totalVenta=BigDecimal::of($venta->monto_total)->toScale(4,RoundingMode::HALF_UP);
                $trueTotalV=$totalVenta->minus($trueTotalV)->toScale(4,RoundingMode::HALF_UP);
                $data=[
                    "venta"=>$venta,
                    "trueCantProds"=>$trueCant,
                    "trueTotalVenta"=>$trueTotalV,
                ];
                return view("venta",$data);
            }else{
                $data=[
                    "venta"=>$venta
                ];
                return view("venta",$data);
            }
        }else{
            return redirect()->to(route("venta.inicio"))->with("mensaje",["Mensaje"=>"Venta no disponible.","Error"=>""]);
        }
    }

    public function setEntrega(){
        $entrega=request()->input("entregar");
        $idVenta=request()->input("idVenta");
        if(!isset($entrega) || !isset($idVenta)){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error al confirmar la entrega.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }else{
            $ventaDB=new Venta();
            $venta=$ventaDB->select("tipo_pago")->where("id","=",$idVenta)->first();
            if(!isset($venta)){
                return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error al confirmar la entrega.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }elseif(strcmp($venta->tipo_pago,"Pendiente")==0){
                $rules=[
                    "metodoPago-confirmVenta"=>["required","in:Tarjeta,Efectivo,Mixto"]
                ];
                request()->validate($rules);
                $metodoPago=request()->input("metodoPago-confirmVenta");
                if(strcmp($metodoPago,"Efectivo")!=0){
                    $rules2=[
                        "fotos-confirmVenta"=>["required"]
                    ];
                    request()->validate($rules2);
                    
                    $fotos=request()->input("fotos-confirmVenta");
                    if(sizeof($fotos)>5){
                        return redirect()->back()->withInput()->withErrors(["fotos-confirmVenta"=>"Ingrese hasta 5 recibos."]);
                    }
                    foreach($fotos as $foto){
                        if(is_array(explode(",",$foto))){
                            if(base64_decode(explode(",",$foto)[1], true) === false){
                                return redirect()->back()->withInput()->withErrors(["fotos-confirmVenta"=>"Error. Ingrese recibos validos."]);
                            }
                        }
                        if(!is_array(explode(";",$foto))){
                            return redirect()->back()->withInput()->withErrors(["fotos-confirmVenta"=>"Error. Ingrese recibos validos."]);
                        }else{
                            $type=explode("/",explode(";",$foto)[0]);
                            if(!is_array($type)){
                                return redirect()->back()->withInput()->withErrors(["fotos-confirmVenta"=>"Error. Ingrese recibos validos."]);
                            }
                        }
                    }
                }
                $ubcVenDB=new UbicacionVenta();
                $res1=$ventaDB->editVenta([
                                    "estado_entrega"=>"Completa",
                                    "tipo_pago"=>$metodoPago,
                                ],$idVenta);
                $res2=$ubcVenDB->editarUbicacionVenta(["fecha_entrega"=>now()],$idVenta);
                if(!$res1 && !$res2){
                    return redirect()->to(route("venta.venta",$idVenta))->with("mensaje",["Mensaje"=>"¡Entrega Completada!","Success"=>""]); 
                }
                if(isset($fotos)){
                    try{
                        if(isset($idVenta)){
                            $urlFotos=[];
                            foreach($fotos as $foto){
                                $manager = new ImageManager(new Driver());
                                $image = $manager->read($foto, [DataUriImageDecoder::class,Base64ImageDecoder::class,]);
                                $urlfoto="/fotos/recibos/ventas/".Str::random(12) . '.webP';
                                $urlFotos[]=$urlfoto;
                                if($image->width()>750){
                                    Storage::disk('public')->put($urlfoto, $image->scale(750)->toWebp());
                                }else{
                                    Storage::disk('public')->put($urlfoto, $image->toWebp());
                                }
                            }
                            $fotoDB=new RecibosVenta();
                            foreach($urlFotos as $urlFoto){
                                $res=false;
                                while(!$res){
                                    $res=$fotoDB->newReciboVenta([
                                                    "url_img"=>"/storage".$urlFoto,
                                                    "url_img_online"=>"/storage".$urlFoto,
                                                    "venta"=>$idVenta
                                                ]);
                                }
                            }
                        }
                    }catch(Error $e){
                        return redirect()->back()->with("mensaje",["Mensaje"=>"Pedido entregado.<br>Ocurrio un erro al almacenar los recibos del pago.<br>Intente nuevamente editando la venta.","Error"=>""]);
                    }
                }
                return redirect()->back()->with("mensaje",["Mensaje"=>"!Pedido entregado¡","Success"=>""]);
            }else{
                $ubcVenDB=new UbicacionVenta();
                $res1=$ventaDB->editVenta(["estado_entrega"=>"Completa"],$idVenta);
                $res2=$ubcVenDB->editarUbicacionVenta(["fecha_entrega"=>now()],$idVenta);
                if(!$res1 && !$res2){
                    return redirect()->to(route("venta.venta",$idVenta))->with("mensaje",["Mensaje"=>"¡Entrega Completada!","Success"=>""]); 
                }
                return redirect()->back()->with("mensaje",["Mensaje"=>"!Pedido entregado¡","Success"=>""]);
            }
        }
    }

    public function editarVenta(){
        $idVenta=request()->input("idVenta");
        $editar=request()->input("editar");

        if(!isset($idVenta) || !isset($editar)){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }else{
            $ventaDB=new Venta();
            $venta=$ventaDB::where("id","=",$idVenta)->select("estado_entrega")->first();
            if(!isset($venta) || !isset($venta->estado_entrega) || strcmp($venta->estado_entrega,"Completa")==0){
                return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }else{
                session()->put("editVenta",["idVenta"=>$idVenta]);
                return redirect()->to(route("venta.procesarventa"));
            }
        }
    }

    public function descartarModVenta(){
        if(request()->input("descartarModVenta")){
            if(session()->has("editVenta")){
                $venta=session("editVenta");
                session()->forget("editVenta");
                $idVenta=$venta["idVenta"];
                return redirect()->to(route("venta.venta",$idVenta))->with("mensaje",["Mensaje"=>"!Modificación cancelada!","Success"=>""]);
            }else{
                return redirect()->to(route("venta.index"))->with("mensaje",["Mensaje"=>"!Modificación cancelada!","Success"=>""]);
            }
        }else{
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
    }
    public function descartarDevVenta(){
        if(request()->input("descartarDevVenta")){
            if(session()->has("devVenta")){
                $venta=session("devVenta");
                session()->forget("devVenta");
                return redirect()->to(route("venta.venta",$venta->id))->with("mensaje",["Mensaje"=>"!Devolución cancelada!","Success"=>""]);
            }else{
                return redirect()->to(route("venta.index"))->with("mensaje",["Mensaje"=>"!Devolución cancelada!","Success"=>""]);
            }
        }else{
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
    }

    public function saveModificacionVenta(){
        $venta=request()->array([
            "tipoVenta-newVenta",
            "estadoEntrega-newVenta",
            "confirmarPago-newVenta",
            "metodoPago-newVenta",
            "fotos-newVenta",
            "direccion-newVenta",
            "manzanaPiso-newVenta",
            "casaDepto-newVenta",
            "coordsDestino-newVenta",
            "detalles-newVenta",
            "receptor-newVenta",
            "contacto-newVenta",
            "fechaEntregaMin-newVenta",
            "fechaEntregaMax-newVenta",
        ]);
        $rulesNewVenta=[
            "tipoVenta-newVenta" => ["required","in:Envio,Local"]
        ];
        request()->validate($rulesNewVenta);

        $rulesNewVentaPlus=[];
        if(strcmp($venta["tipoVenta-newVenta"],"Envio")==0){
            $rulesNewVentaPlus=[
                "direccion-newVenta" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:10", "max:250"],
                "manzanaPiso-newVenta" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:1", "max:50"],
                "casaDepto-newVenta" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:1", "max:50"],
                "receptor-newVenta" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:7", "max:50"],
                "contacto-newVenta" => ["required","min_digits:9", "max_digits:14"],
                "fechaEntregaMin-newVenta" => ["required", "date"],
                "fechaEntregaMax-newVenta" => ["required", "date"]
            ];
            if(isset($venta["detalles-newVenta"])){
                $rulesNewVentaPlus["detalles-newVenta"] = ["min:10","max:250",new AlfaNunSpacePunct()];
            }
            if(isset($venta["confirmarPago-newVenta"])){
                $rulesNewVentaPlus["metodoPago-newVenta"]=["required","in:Tarjeta,Efectivo,Mixto"];
                $rulesNewVentaPlus["fotos-newVenta"]=["required"];
            }
        }elseif(!isset($venta["estadoEntrega-newVenta"])){
            $rulesNewVentaPlus=[
                "receptor-newVenta" => ["required", "regex:/^[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 _\\-.,:;]+$/i","min:7", "max:50"],
                "contacto-newVenta" => ["required","min_digits:9", "max_digits:14"]
            ];
            if(isset($venta["confirmarPago-newVenta"])){
                $rulesNewVentaPlus["confirmarPago-newVenta"] = ["in:Confirmar"];
                $rulesNewVentaPlus["metodoPago-newVenta"] = ["required","in:Tarjeta,Efectivo,Mixto"];
                $rulesNewVentaPlus["fotos-newVenta"] = ["required"];
            }
        }else{
            $rulesNewVentaPlus=[
                "estadoEntrega-newVenta" => ["in:Completa"],
                "confirmarPago-newVenta" => ["in:Confirmar"],
                "metodoPago-newVenta" => ["required","in:Tarjeta,Efectivo,Mixto"],
                "fotos-newVenta" => ["required"]
            ];
        }
        request()->validate($rulesNewVentaPlus);

        $errores=[];
        if(isset($venta["coordsDestino-newVenta"])){
            if(str_contains($venta["coordsDestino-newVenta"],"[;]")){
                $coords=explode("[;]",$venta["coordsDestino-newVenta"]);
                if(sizeof($coords)!=2){
                    $errores["coordsDestino-newVenta"]="Coordenadas invalidas.";
                }else{
                    foreach($coords as $coord){
                        if(!is_numeric($coord)){
                            $errores["coordsDestino-newVenta"]="Coordenadas invalidas.";
                            break;
                        }
                    }
                }
            }else{
                $errores["coordsDestino-newVenta"]="Coordenadas invalidas.";
            }
        }
        if(isset($venta["metodoPago-newVenta"]) && strcmp($venta["metodoPago-newVenta"],"Efectivo")!=0){
            if(sizeof($venta["fotos-newVenta"])>5){
                $errores["fotos-newVenta"]="Ingrese hasta 5 recibos.";
            }
            foreach($venta["fotos-newVenta"] as $foto){
                if(is_array(explode(",",$foto))){
                    if(base64_decode(explode(",",$foto)[1], true) === false){
                        $errores["fotos-newVenta"]="Error. Ingrese recibos validos.";
                        break;
                    }
                }
                if(!is_array(explode(";",$foto))){
                    $errores["fotos-newVenta"]="Error. Ingrese recibos validos.";
                    break;
                }else{
                    $type=explode("/",explode(";",$foto)[0]);
                    if(!is_array($type)){
                        $errores["fotos-newVenta"]="Error. Ingrese recibos validos.";
                        break;
                    }
                }
            }
        }
        if(!empty($errores)){
            return redirect()->back()->withInput()->withErrors($errores);
        }
        if(strcmp($venta["tipoVenta-newVenta"],"Envio")==0){
            $minF=Carbon::parse($venta["fechaEntregaMin-newVenta"]);
            $maxF=Carbon::parse($venta["fechaEntregaMax-newVenta"]);
            $minF=$this->controlFechas($minF,now(),$maxF);
            $maxF=$this->controlFechas($maxF,$minF,now()->setTimeFromTimeString("20:30")->addDays(7));
            $venta["fechaEntregaMin-newVenta"]=$minF->format("Y-m-d H:i");
            $venta["fechaEntregaMax-newVenta"]=$maxF->format("Y-m-d H:i");
        }

        try{
            $ventaDB=new Venta();
            $newVenta=[
                "tipo_venta"=>$venta["tipoVenta-newVenta"]
            ];
            if(!isset($venta["estadoVenta-newventa"])){
                $newVenta["nombre_receptor"]=$venta["receptor-newVenta"];
                $newVenta["telefono_receptor"]=$venta["contacto-newVenta"];
            }else{
                $newVenta["estado_entrega"]=$venta["estadoEntrega-newVenta"];
            }
            if(isset($venta["metodoPago-newVenta"])){
                $newVenta["tipo_pago"]=$venta["metodoPago-newVenta"];
            }
            if(!$ventaDB->editVenta($newVenta,session("editVenta")["idVenta"])){
                return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado al modificar la venta.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }
        }catch(Error $e){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado al modificar la venta.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
        try{
            if(strcmp($venta["tipoVenta-newVenta"],"Envio")==0){
                $ventaDB=new Venta();
                $oldVenta=$ventaDB->getVenta(session("editVenta")["idVenta"]);
                if(!isset($oldVenta)){
                    return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un erro al modificar la ubicación de la venta.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                }
                if(isset($oldVenta->ubicacion)){
                    $ubicVentDB=new UbicacionVenta();
                    $newUbicacion=[
                        "venta"=>session("editVenta")["idVenta"],
                        "direccion"=>$venta["direccion-newVenta"],
                        "casa_depto"=>$venta["casaDepto-newVenta"],
                        "manzana_piso"=>$venta["manzanaPiso-newVenta"],
                        "fecha_entrega_min"=>$venta["fechaEntregaMin-newVenta"],
                        "fecha_entrega_max"=>$venta["fechaEntregaMax-newVenta"],
                    ];
                    if(isset($venta["detalles-newVenta"])){
                        $newUbicacion["descripcion"]=$venta["detalles-newVenta"];
                    }
                    if(isset($venta["coordsDestino-newVenta"])){
                        $newUbicacion["lat"]=explode("[;]",$venta["coordsDestino-newVenta"])[0];
                        $newUbicacion["lng"]=explode("[;]",$venta["coordsDestino-newVenta"])[1];
                    }
                    if(!$ubicVentDB->editarUbicacionVenta($newUbicacion,session("editVenta")["idVenta"])){
                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un erro al modificar la ubicación de la venta.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                    }
                }else{
                    $ubicVentDB=new UbicacionVenta();
                    $newUbicacion=[
                        "venta"=>session("editVenta")["idVenta"],
                        "direccion"=>$venta["direccion-newVenta"],
                        "casa_depto"=>$venta["casaDepto-newVenta"],
                        "manzana_piso"=>$venta["manzanaPiso-newVenta"],
                        "fecha_entrega_min"=>$venta["fechaEntregaMin-newVenta"],
                        "fecha_entrega_max"=>$venta["fechaEntregaMax-newVenta"],
                    ];
                    if(isset($venta["detalles-newVenta"])){
                        $newUbicacion["descripcion"]=$venta["detalles-newVenta"];
                    }
                    if(isset($venta["coordsDestino-newVenta"])){
                        $newUbicacion["lat"]=explode("[;]",$venta["coordsDestino-newVenta"])[0];
                        $newUbicacion["lng"]=explode("[;]",$venta["coordsDestino-newVenta"])[1];
                    }
                    if(!$ubicVentDB->newUbicacionVenta($newUbicacion)){
                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un erro al modificar la ubicación de la venta.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                    }
                }
            }
        }catch(Error $e){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un erro al modificar la ubicación de la venta.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
        if(isset($venta["fotos-newVenta"])){
            $ventaDB=new Venta();
            $oldVenta=$ventaDB->getVenta(session("editVenta")["idVenta"]);
            if(!isset($oldVenta)){
                $idVenta=session("editVenta")["idVenta"];
                session()->forget("editVenta");
                return redirect()->to(route("venta.venta",$idVenta))->with("mensaje",["Mensaje"=>"Venta modificada.<br>Ocurrio un erro al almacenar los recibos de la venta.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }
            if(sizeof($oldVenta->recibos->toArray())==0 && strcmp($oldVenta->tipo_pago,"Pendiente")!=0 && strcmp($oldVenta->tipo_pago,"Efectivo")!=0){
                try{
                    $urlFotos=[];
                    foreach($venta["fotos-newVenta"] as $foto){
                        $manager = new ImageManager(new Driver());
                        $image = $manager->read($foto, [DataUriImageDecoder::class,Base64ImageDecoder::class]);
                        $urlfoto="/fotos/recibos/ventas/".Str::random(12) . '.webP';
                        $urlFotos[]=$urlfoto;
                        if($image->width()>750){
                            Storage::disk('public')->put($urlfoto, $image->scale(750)->toWebp());
                        }else{
                            Storage::disk('public')->put($urlfoto, $image->toWebp());
                        }
                    }
                    $fotoDB=new RecibosVenta();
                    foreach($urlFotos as $urlFoto){
                        if(!$fotoDB->newReciboVenta([
                            "url_img"=>"/storage".$urlFoto,
                            "url_img_online"=>"/storage".$urlFoto,
                            "venta"=>session("editVenta")["idVenta"]
                        ])){
                            $idVenta=session("editVenta")["idVenta"];
                            session()->forget("editVenta");
                            return redirect()->to(route("venta.venta",$idVenta))->with("mensaje",["Mensaje"=>"Venta modificada.<br>Ocurrio un erro al almacenar los recibos de la venta.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                        }
                    }
                }catch(Error $e){
                    $idVenta=session("editVenta")["idVenta"];
                    session()->forget("editVenta");
                    return redirect()->to(route("venta.venta",$idVenta))->with("mensaje",["Mensaje"=>"Venta modificada.<br>Ocurrio un erro al almacenar los recibos de la venta.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                }    
            }
        }
        $idVenta=session("editVenta")["idVenta"];
        session()->forget("editVenta");
        return redirect()->to(route("venta.venta",$idVenta))->with("mensaje",["Mensaje"=>"¡Venta modificada!","Success"=>""]);
    }

    public function devolucionVenta(){
        $idVenta=request()->input("idVenta");
        if(!isset($idVenta)){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }else{
            $ventaDB=new Venta();
            $venta=$ventaDB->getVenta($idVenta);
            if($venta!=null){
                $fv=Carbon::parse($venta->fecha_venta);
                $fvduv=$fv->copy()->addDays(5);
                if($fvduv->lessThan($fv)){
                    return redirect()->back()->with("mensaje",["Mensaje"=>"Fecha límite superada.<br>La venta ya no se puede devolver.","Error"=>""]);
                }else{
                    session()->put("devVenta",$venta);
                    return redirect()->to(route("venta.procesarventa"));
                }
            }else{
                return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }
        }
    }

    public function saveDevolucionVenta(){
        $devolucion=request()->array();

        $rulesDevVenta["producto-devVenta"]=["required"];
        $venta=session("devVenta");
        if(strcmp($venta->tipo_pago,"Pendiente")!=0){
            $rulesDevVenta["metodoPago-devVenta"]=["required","in:Tarjeta,Efectivo,Mixto"];
        }
        request()->validate($rulesDevVenta);
        
        $rulesDevVentaPlus=[];
        if(strcmp($venta->tipo_pago,"Pendiente")!=0){
            if(strcmp($devolucion["metodoPago-devVenta"],"Efectivo")!=0){
                $rulesDevVentaPlus["fotos-devVenta"]=["required"];
                request()->validate($rulesDevVentaPlus);
            }
        }

        $idProds=$venta->productosVenta->pluck("producto")->toArray();
        $prods=$venta->productosVenta->toArray();
        $indexProds=[];
        foreach($devolucion["producto-devVenta"] as $idProd){
            if(!in_array($idProd,$idProds)){
                return redirect()->back()->withInput()->withErrors(["producto-devVenta"=>"Seleccione un producto válido."]);
            }else{
                $indexProds[]=array_search($idProd,$idProds);
            }
        }
        
        $i=0;
        foreach($devolucion["producto-devVenta"] as $idProd){
            $rulesDevVentaPlus["motivoDevProd-".$idProd."-devVenta"]=["required","min:10", "max:150", new AlfaNunSpacePunct];
            if(strcmp($venta->tipo_pago,"Pendiente")!=0){
                $rulesDevVentaPlus["tipoDevProd-".$idProd."-devVenta"]=["required","in:Cambio,Fallado,Devolucion"];
            }else{
                $rulesDevVentaPlus["tipoDevProd-".$idProd."-devVenta"]=["required","in:Devolucion"];
            }
            switch($prods[$indexProds[$i]]['producto_relacion']['tipo_medida']){
                case "Unidad":  $rulesDevVentaPlus["cantDevProd-".$idProd."-devVenta"]=["required", "decimal:0", "min:1", "max:".$prods[$indexProds[$i]]['cantidad']];
                                break;
                default:        $rulesDevVentaPlus["cantDevProd-".$idProd."-devVenta"]=["required", "decimal:0,4", "min:0.01", "max:".$prods[$indexProds[$i]]['cantidad']];
            }
            $i++;
        }
        request()->validate($rulesDevVentaPlus);
        
        if(strcmp($venta->tipo_pago,"Pendiente")!=0){
            if(strcmp($devolucion["metodoPago-devVenta"],"Efectivo")!=0){
                if(sizeof($devolucion["fotos-devVenta"])>5){
                    return redirect()->back()->withInput()->withErrors(["fotos-devVenta"=>"Ingrese hasta 5 recibos."]);
                }
                foreach($devolucion["fotos-devVenta"] as $foto){
                    if(is_array(explode(",",$foto))){
                        if(base64_decode(explode(",",$foto)[1], true) === false){
                            return redirect()->back()->withInput()->withErrors(["fotos-devVenta"=>"Error. Ingrese recibos validos."]);
                        }
                    }
                    if(!is_array(explode(";",$foto))){
                        return redirect()->back()->withInput()->withErrors(["fotos-devVenta"=>"Error. Ingrese recibos validos."]);
                    }else{
                        $type=explode("/",explode(";",$foto)[0]);
                        if(!is_array($type)){
                            return redirect()->back()->withInput()->withErrors(["fotos-devVenta"=>"Error. Ingrese recibos validos."]);
                        }
                    }
                }
            }
        }
        
        $total=BigDecimal::of("0.0000");
        for($i=0;$i<sizeof($indexProds);$i++){
            $precioVenta=BigDecimal::of($prods[$indexProds[$i]]["precio_venta"]);
            $oldCant=BigDecimal::of($prods[$indexProds[$i]]["cantidad"]);
            $oldTotalProd=BigDecimal::of($prods[$indexProds[$i]]["total_producto"]);
            $newCant=BigDecimal::of($devolucion["cantDevProd-".$prods[$indexProds[$i]]["producto"]."-devVenta"]);

            if($newCant->isEqualTo($oldCant)){
                $total=$total->plus($oldTotalProd)->toScale(4,RoundingMode::HALF_UP);
            }elseif($newCant->isLessThan($oldCant)){
                $total=$total->plus($newCant->multipliedBy($precioVenta)->toScale(4,RoundingMode::HALF_UP))->toScale(4,RoundingMode::HALF_UP);
            }
        }

        try{
            $devVentDB=new DevolucionVenta();
            $newDevVenta=[
                "venta"=>$venta->id,
                "monto_total"=>$total->__toString(),
            ];
            if(isset($devolucion["metodoPago-devVenta"])){
                $newDevVenta["tipo_pago"]=$devolucion["metodoPago-devVenta"];
            }else{
                $newDevVenta["tipo_pago"]="Devolucion";
            }
            $idNewDevVenta=$devVentDB->newDevVenta($newDevVenta);
            if(!isset($idNewDevVenta)){
                return redirect()->back()->withInput()->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar crear la devolución.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }
        }catch(Error $e){
            return redirect()->back()->withInput()->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar crear la devolución.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
        try{
            if(isset($idNewDevVenta)){
                $prodDevDB=new ProductosDevolucionVenta();
                for($i=0;$i<sizeof($indexProds);$i++){
                    $idProd=$prods[$indexProds[$i]]["producto"];
                    $precioVenta=BigDecimal::of($prods[$indexProds[$i]]["precio_venta"]);
                    $oldCant=BigDecimal::of($prods[$indexProds[$i]]["cantidad"]);
                    $oldTotalProd=BigDecimal::of($prods[$indexProds[$i]]["total_producto"]);
                    $newCant=BigDecimal::of($devolucion["cantDevProd-".$idProd."-devVenta"]);

                    if($newCant->isEqualTo($oldCant)){
                        $cantProd=$oldCant;
                        $totalProd=$oldTotalProd;
                    }elseif($newCant->isLessThan($oldCant)){
                        $cantProd=$newCant;
                        $totalProd=$newCant->multipliedBy($precioVenta)->toScale(4,RoundingMode::HALF_UP);
                    }

                    $newProdDev=[
                        "producto"=>$idProd,
                        "devolucion"=>$idNewDevVenta,
                        "tipo_devolucion"=>$devolucion["tipoDevProd-".$idProd."-devVenta"],
                        "motivo_devolucion"=>$devolucion["motivoDevProd-".$idProd."-devVenta"],
                        "cantidad"=>$cantProd->__toString(),
                        "total_producto"=>$totalProd->__toString(),
                    ];

                    if(!$prodDevDB->newProductoDevolucion($newProdDev)){
                        DevolucionVenta::where("id","=",$idNewDevVenta)->forceDelete();
                        return redirect()->back()->withInput()->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar crear la devolución.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                    }
                }
            }
        }catch(Error $e){
            DevolucionVenta::where("id","=",$idNewDevVenta)->forceDelete();
            return redirect()->back()->withInput()->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar crear la devolución.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
        try{
            for($i=0;$i<sizeof($indexProds);$i++){
                $idProd=$prods[$indexProds[$i]]["producto"];
                $oldCant=BigDecimal::of($prods[$indexProds[$i]]["cantidad"]);
                $newCant=BigDecimal::of($devolucion["cantDevProd-".$idProd."-devVenta"]);

                if($newCant->isEqualTo($oldCant)){
                    $cantProd=$oldCant;
                }elseif($newCant->isLessThan($oldCant)){
                    $cantProd=$newCant;
                }
                if(strcmp($devolucion["tipoDevProd-".$idProd."-devVenta"],"Fallado")!=0){
                    $prodDB=new Producto();
                    $oldProd=Producto::where("id","=",$idProd)->select("cantidad_disponible")->first();
                    if(!isset($oldProd)){
                        return redirect()->to(route("venta.venta",$venta->id))->with("mensaje",["Mensaje"=>"Ocurrio un error al actualizar las cantidades de los productos devueltos.","Error"=>""]);
                    }
                    $oldCantDispProd=BigDecimal::of($oldProd->cantidad_disponible);
                    $newCantDispProd=$oldCantDispProd->plus($cantProd)->toScale(4,RoundingMode::HALF_UP);
                    if(!$prodDB->editProducto(["cantidad_disponible"=>$newCantDispProd->__toString()],$idProd)){
                        return redirect()->to(route("venta.venta",$venta->id))->with("mensaje",["Mensaje"=>"Ocurrio un error al actualizar las cantidades de los productos devueltos.","Error"=>""]);
                    }
                }
            }
        }catch(Error $e){
            return redirect()->to(route("venta.venta",$venta->id))->with("mensaje",["Mensaje"=>"Ocurrio un error al actualizar las cantidades de los productos devueltos.","Error"=>""]);
        }
        try{
            if(isset($idNewDevVenta) && isset($devolucion["fotos-devVenta"])){
                $urlFotos=[];
                foreach($devolucion["fotos-devVenta"] as $foto){
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($foto, [DataUriImageDecoder::class,Base64ImageDecoder::class,]);
                    $urlfoto="/fotos/recibos/devolucionesVentas/".Str::random(12) . '.webP';
                    $urlFotos[]=$urlfoto;
                    if($image->width()>750){
                        Storage::disk('public')->put($urlfoto, $image->scale(750)->toWebp());
                    }else{
                        Storage::disk('public')->put($urlfoto, $image->toWebp());
                    }
                }
                $fotoDB=new RecibosDevolucionVenta();
                foreach($urlFotos as $urlFoto){
                    if(!$fotoDB->newReciboDevVenta([
                            "devolucion"=>$idNewDevVenta,
                            "url_img"=>"/storage".$urlFoto,
                            "url_img_online"=>"/storage".$urlFoto
                    ])){
                    session()->put("errorDevVenta",[
                        "recibosDev"=>$devolucion["fotos-devVenta"]
                    ]);
                        session()->forget("devVenta");
                        return redirect()->to(route("venta.procesarventa"))->with("mensaje",["Mensaje"=>"Devolución creada.<br>Ocurrio un erro al almacenar los recibos de la devolución.<br>Intente enviarlos nuevamente.","Error"=>""]);
                    }
                }
            }
        }catch(Error $e){
            session()->put("errorDevVenta",[
                "recibosDev"=>$devolucion["fotos-devVenta"]
            ]);
            session()->forget("devVenta");
            return redirect()->to(route("venta.procesarventa"))->with("mensaje",["Mensaje"=>"Devolución creada.<br>Ocurrio un erro al almacenar los recibos de la devolución.<br>Intente enviarlos nuevamente.","Error"=>""]);
        }
        $ventaDB=new Venta();
        $oldVenta=$ventaDB->getVenta($venta->id);
        if(!isset($oldVenta)){
            session()->forget("devVenta");
            return redirect()->to(route("venta.venta",$venta->id))->with("mensaje",["Mensaje"=>"Devolución creada con exito.","Success"=>""]);
        }
        if(strcmp($oldVenta->tipo_pago,"Pendiente")==0){
            $totalProdsDev=0;
            $allProdsDevs=[];
            foreach($oldVenta->devoluciones as $devolucion){
                foreach($devolucion->productos as $productoDev){
                    if(!array_key_exists($productoDev->producto,$allProdsDevs)){
                        $allProdsDevs[$productoDev->producto]=BigDecimal::of($productoDev->cantidad)->toScale(4,RoundingMode::HALF_UP);
                    }else{
                        $cantProdDev=BigDecimal::of($productoDev->cantidad)->toScale(4,RoundingMode::HALF_UP);
                        $allProdsDevs[$productoDev->producto]=$allProdsDevs[$productoDev->producto]->plus($cantProdDev)->toScale(4,RoundingMode::HALF_UP);
                    }
                }
            }
            foreach($oldVenta->productosVenta as $producto){
                if(array_key_exists($producto->producto,$allProdsDevs)){
                    $cant=BigDecimal::of($producto->cantidad);
                    if($allProdsDevs[$producto->producto]->isEqualTo($cant)){
                        $totalProdsDev+=1;
                    }
                }
            }
            if($totalProdsDev==sizeof($oldVenta->productosVenta->toArray())){
                if(!$ventaDB->editVenta(["venta_invalida",1],$oldVenta->id)){
                    session()->forget("devVenta");
                    return redirect()->to(route("venta.venta",$venta->id))->with("mensaje",["Mensaje"=>"Devolución creada con exito.","Success"=>""]);
                }
                session()->forget("devVenta");
                return redirect()->to(route("venta.inicio"))->with("mensaje",["Mensaje"=>"¡Venta cancelada!.","Success"=>""]);
            }
        }
        session()->forget("devVenta");
        return redirect()->to(route("venta.venta",$venta->id))->with("mensaje",["Mensaje"=>"Devolución creada con exito.","Success"=>""]);
    }

    public function ventaDevoluciones(){
        $idVenta=request()->input("idVenta");
        if(!isset($idVenta)){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }else{
            $dev=DevolucionVenta::where("venta","=",$idVenta)->select("id")->first();
            if(!isset($dev)){
                return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }else{
                return redirect()->to(route("venta.devolucion",$dev->id));
            }
        }
    }

    public function showDevVenta($idDev){
        $devsDB=new DevolucionVenta();
        $dev=$devsDB->getDevolucion($idDev);
        if(!isset($dev)){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }
        $idDevsVenta=$devsDB::where("venta","=",$dev->venta)
                            ->select("id")->get()->toArray();
        $data=[
            "devolucionV"=>$dev,
            "idDevsVenta"=>$idDevsVenta
        ];
        return view("devolucion",$data);
    }

    public function deleteVenta(){
        $deleteV=request()->input("eliminarVenta");
        $idVenta=request()->input("idVenta-deleteVenta");
        if(!isset($deleteV) || !isset($idVenta)){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos."]);
        }else{
            $rulesAdmin=[
                "passAdmin-deleteVenta" => ["required","min:6", "max:16", new ValidPass()]
            ];
            request()->validate($rulesAdmin);
            $pass=request()->input("passAdmin-deleteVenta");
            $venta=request()->input("idVenta-deleteVenta");
            $btnDelete=request()->input("eliminarVenta");
            if(!isset($btnDelete) || (!isset($venta))){
                return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }else{
                $admin=User::select("password")->where("name","=","Administrador")->first();
                if (Hash::check($pass, $admin->password)) {
                    try{
                        $recVentaDB=new RecibosVenta;
                        $recibosV=$recVentaDB->getAllRecibosVenta($venta);
                        if(isset($recibosV)){
                            foreach($recibosV as $reciboV){
                                $nameFoto=explode("/",$reciboV["url_img"])[sizeof(explode("/",$reciboV["url_img"]))-1];
                                if (Storage::disk('public')->exists("/fotos/recibos/ventas/".$nameFoto)) {
                                    Storage::disk('public')->delete("/fotos/recibos/ventas/".$nameFoto);
                                }
                            }
                        }
                        $recDevVentaDB=new RecibosDevolucionVenta;
                        $recibosDV=$recDevVentaDB->getAllRecibosDevVenta($venta);
                        if(isset($recibosDV)){
                            foreach($recibosDV as $reciboDV){
                                $nameFoto=explode("/",$reciboDV["url_img"])[sizeof(explode("/",$reciboDV["url_img"]))-1];
                                if (Storage::disk('public')->exists("/fotos/recibos/ventas/".$nameFoto)) {
                                    Storage::disk('public')->delete("/fotos/recibos/ventas/".$nameFoto);
                                }
                            }
                        }
                        $ventaDB=new Venta();
                        if(!$ventaDB->deleteVenta($idVenta)){
                            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos."]);
                        }else{
                            return redirect()->to(route("venta.inicio"))->with("mensaje",["Mensaje"=>"¡Venta eliminada!","Success"=>""]);
                        }
                    }catch(Error $e){
                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                    }
                }else{
                    return redirect()->back()->withInput()->withError(["passAdmin-deleteVenta"=>"Contraseña incorrecta."]);
                }
            }    
        }
    }

    public function deshacerVenta(){
        $deshacerV=request()->input("deshacerVenta");
        $idVenta=request()->input("idVenta-deshacerVenta");
        if(!isset($deshacerV) || !isset($idVenta)){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos."]);
        }else{
            $rulesAdmin=[
                "passAdmin-deshacerVenta" => ["required","min:6", "max:16", new ValidPass()]
            ];
            request()->validate($rulesAdmin);
            $pass=request()->input("passAdmin-deshacerVenta");
            $venta=request()->input("idVenta-deshacerVenta");
            $btnDelete=request()->input("deshacerVenta");
            if(!isset($btnDelete) || (!isset($venta))){
                return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }else{
                $admin=User::select("password")->where("name","=","Administrador")->first();
                if (Hash::check($pass, $admin->password)) {
                    try{
                        $ventaDB=new Venta();
                        $oldVenta=$ventaDB->getVenta($idVenta);
                        if(!isset($oldVenta)){
                            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos."]);
                        }
                        if(strcmp($oldVenta->tipo_pago,"Pendiente")==0){
                            $allProdsDevs=[];
                            foreach($oldVenta->devoluciones as $devolucion){
                                foreach($devolucion->productos as $productoDev){
                                    if(!array_key_exists($productoDev->producto,$allProdsDevs)){
                                        $allProdsDevs[$productoDev->producto]=BigDecimal::of($productoDev->cantidad)->toScale(4,RoundingMode::HALF_UP);
                                    }else{
                                        $cantProdDev=BigDecimal::of($productoDev->cantidad)->toScale(4,RoundingMode::HALF_UP);
                                        $allProdsDevs[$productoDev->producto]=$allProdsDevs[$productoDev->producto]->plus($cantProdDev)->toScale(4,RoundingMode::HALF_UP);
                                    }
                                }
                            }
                            foreach($oldVenta->productosVenta as $prodV){
                                $trueCantidadVendida=BigDecimal::of(0);
                                if(array_key_exists($prodV->producto,$allProdsDevs)){
                                    $cantidad=BigDecimal::of($prodV->cantidad)->toScale(4,RoundingMode::HALF_UP);
                                    $trueCantidadVendida=$cantidad->minus($allProdsDevs[$prodV->producto])->toScale(4,RoundingMode::HALF_UP);
                                }else{
                                    $trueCantidadVendida=BigDecimal::of($prodV->cantidad)->toScale(4,RoundingMode::HALF_UP);
                                }
                                $prodDB=new Producto();
                                $oldProd=Producto::select("cantidad_disponible")->where("id","=",$prodV->producto)->first();
                                if(!isset($oldProd)){
                                    return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos."]);
                                }
                                $oldCant=BigDecimal::of($oldProd->cantidad_disponible)->toScale(4,RoundingMode::HALF_UP);
                                $newCant=$oldCant->plus($trueCantidadVendida)->toScale(4,RoundingMode::HALF_UP);
                                $aux=$newCant->toScale(0,RoundingMode::DOWN);
                                if($aux->isLessThan($newCant)){
                                    $newCantDisp=$newCant->__toString();
                                }else{
                                    $newCantDisp=$aux->__toString();
                                }
                                if(!$prodDB->editProducto(["cantidad_disponible"=>$newCantDisp],$prodV->producto)){
                                    return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos."]);
                                }
                            }
                            if(!$ventaDB->editVenta(["venta_invalida"=>1],$idVenta)){
                                return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos."]);
                            }
                            if(!$ventaDB->deleteVenta($idVenta)){
                                return redirect()->to(route("venta.inicio"))->with("mensaje",["Mensaje"=>"¡Venta deshecha!","Success"=>""]);
                            }
                            return redirect()->to(route("venta.inicio"))->with("mensaje",["Mensaje"=>"¡Venta deshecha!","Success"=>""]);
                        }else{
                            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos."]);
                        }
                    }catch(Error $e){
                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                    }
                }else{
                    return redirect()->back()->withInput()->withError(["passAdmin-deshacerVenta"=>"Contraseña incorrecta."]);
                }
            }    
        }
    }

    public function getTotalesVentas(){
        $data=request()->array(["rango","fFin","fInicio","tipoPago"]);
        if(sizeof($data)==0){
            return redirect()->to(route("venta.index"));
        }else{
            if(isset($data["rango"])){
                if(in_array($data["rango"],["Hoy","Semana","Mes","Siempre","Personal"])){
                    if(strcmp($data["rango"],"Personal")==0){
                        if(isset($data["fInicio"]) && isset($data["fFin"])){
                            $fInicio=Carbon::parse($data["fInicio"]);
                            $fFin=Carbon::parse($data["fFin"]);
                            $now=Carbon::parse(now());
                            $minF=Carbon::parse(1420081200);
                            if($fInicio->greaterThan($fFin)){
                                $aux=$fInicio;
                                $fInicio=$fFin;
                                $fFin=$aux;
                            }
                            if($fInicio->lessThan($minF)){
                                $fInicio=$minF;
                            }
                            if($fFin->greaterThan($now)){
                                $fFin=$now;
                            }
                            if(!session()->has("totalesVentas")){
                                session()->put("totalesVentas",[
                                    "rango"=>"Personal",
                                    "fInicio"=>$fInicio->toDateTimeString(),
                                    "fFin"=>$fFin->toDateTimeString(),
                                ]);
                            }else{
                                $totalesVentas=session("totalesVentas");
                                $totalesVentas["rango"]="Personal";
                                $totalesVentas["fInicio"]=$fInicio->toDateTimeString();
                                $totalesVentas["fFin"]=$fFin->toDateTimeString();
                                session()->put("totalesVentas",$totalesVentas);
                            }
                        }elseif(!isset($data["fFin"]) && isset($data["fInicio"])){
                            $fInicio=Carbon::parse($data["fInicio"]);
                            $now=Carbon::parse(now());
                            $minF=Carbon::parse(1420081200);
                            if($fInicio->greaterThan($now)){
                                $fInicio=$now;
                            }
                            if($fInicio->lessThan($minF)){
                                $fInicio=$minF;
                            }
                            if(!session()->has("totalesVentas")){
                                session()->put("totalesVentas",[
                                    "rango"=>"Personal",
                                    "fInicio"=>$fInicio->toDateTimeString(),
                                ]);
                            }else{
                                $totalesVentas=session("totalesVentas");
                                $totalesVentas["rango"]="Personal";
                                $totalesVentas["fInicio"]=$fInicio->toDateTimeString();
                                $totalesVentas["fFin"]=null;
                                session()->put("totalesVentas",$totalesVentas);
                            }
                        }elseif(!isset($data["fInicio"]) && isset($data["fFin"])){
                            $fFin=Carbon::parse($data["fFin"]);
                            $now=Carbon::parse(now());
                            $minF=Carbon::parse(1420081200);
                            if($fFin->greaterThan($now)){
                                $fFin=$now;
                            }
                            if($fFin->lessThan($minF)){
                                $fFin=$minF;
                            }
                            if(!session()->has("totalesVentas")){
                                session()->put("totalesVentas",[
                                    "rango"=>"Personal",
                                    "fFin"=>$fFin->toDateTimeString(),
                                ]);
                            }else{
                                $totalesVentas=session("totalesVentas");
                                $totalesVentas["rango"]="Personal";
                                $totalesVentas["fFin"]=$fFin->toDateTimeString();
                                $totalesVentas["fInicio"]=null;
                                session()->put("totalesVentas",$totalesVentas);
                            }
                        }elseif(!isset($data["fInicio"]) && !isset($data["fFin"])){
                            if(session()->has("totalesVentas")){
                                $totalesVentas=session("totalesVentas");
                                $totalesVentas["rango"]="Personal";
                                $totalesVentas["fInicio"]=null;
                                $totalesVentas["fFin"]=null;
                                session()->put("totalesVentas",$totalesVentas);
                            }
                        }
                    }
                    if(!session()->has("totalesVentas")){
                        session()->put("totalesVentas",["rango"=>$data["rango"]]);
                    }else{
                        $totalesVentas=session("totalesVentas");
                        $totalesVentas["rango"]=$data["rango"];
                        session()->put("totalesVentas",$totalesVentas);
                    }
                }else{
                    if(!session()->has("totalesVentas")){
                        session()->put("totalesVentas",["rango"=>"Hoy"]);
                    }else{
                        $totalesVentas=session("totalesVentas");
                        $totalesVentas["rango"]="Hoy";
                        session()->put("totalesVentas",$totalesVentas);
                    }
                }
            }
            if(isset($data["tipoPago"])){
                if(in_array($data["tipoPago"],["Tarjeta","Efectivo","Mixto","Todo"])){
                    if(!session()->has("totalesVentas")){
                        session()->put("totalesVentas",["tipoPago"=>$data["TipoPago"]]);
                    }else{
                        $totalesVentas=session("totalesVentas");
                        $totalesVentas["tipoPago"]=$data["tipoPago"];
                        session()->put("totalesVentas",$totalesVentas);
                    }
                }else{
                    if(!session()->has("totalesVentas")){
                        session()->put("totalesVentas",["tipoPago"=>"Todo"]);
                    }else{
                        $totalesVentas=session("totalesVentas");
                        $totalesVentas["tipoPago"]="Todo";
                        session()->put("totalesVentas",$totalesVentas);
                    }
                }
            }
            return redirect()->to(route("venta.index"));
        }
    }

    public function getMorePendientes(){
        $offset=request()->input("offset");
        if(!isset($offset)){
            return json_encode(["Error"=>"Ocurrio un error inesperado."]);
        }
        $ventaDB=new Venta();
        $pendientes=$ventaDB->getAllPedidosPendientes($offset);
        if(!isset($pendientes)){
            return json_encode(["Error"=>"Ocurrio un error inesperado."]);
        }
        $renderPendientes=[];
        foreach($pendientes["pendientes"] as $venta){
            $render=view('components.pedido-pendiente-component',["venta"=>$venta])->render();
            $renderPendientes[]=$render;
        }
        return json_encode([
            "success"=>true,
            "pendientes"=>$renderPendientes,
            "totalPendientes"=>$pendientes["totalPendientes"]
        ]);
    }
}
