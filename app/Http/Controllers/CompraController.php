<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Foto;
use App\Models\Producto;
use App\Models\ProductosCompra;
use App\Models\Proveedor;
use App\Models\RecibosCompra;
use App\Models\RecibosDevolucionVenta;
use App\Models\RecibosVenta;
use App\Models\User;
use App\Models\Venta;
use App\Rules\ValidPass;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Error;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Decoders\Base64ImageDecoder;
use Intervention\Image\Decoders\DataUriImageDecoder;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;

class CompraController extends Controller
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
        session()->put("pagina","compras");

        if(session()->has("filtroCompras")){
            $provs = new Proveedor();
            $provs=$provs->getAllNameProveedores();
            if(!session()->has("proveedores")){
                session()->put("proveedores",$provs->pluck("id")->toArray());
            }
            $compraDB=new Compra();
            $this->ordenarCompras(session("filtroCompras")["orden"]);
            $orden=$this->getOrdenCompras();
            $prov=session("filtroCompras")["proveedor"];
            $pago=session("filtroCompras")["pago"];
            $fInicio=session("filtroCompras")["fInicio"];
            $fFin=session("filtroCompras")["fFin"];
            $comprasData=$compraDB->getAllComprasActivas($orden,$prov,$pago,$fInicio,$fFin);
            $gastos=$compraDB->gastosMensuales();
            $data=[
                "compras"=>$comprasData["compras"],
                "totalCompras"=>$comprasData["totalCompra"],
                "proveedores"=>$provs,
                "gastos"=>$gastos[0],
                "gastosVisibles"=>$gastos[1]
            ];
            if(session()->has("totalesCompras")){
                $tV=session("totalesCompras");
                if(isset($tV["rango"]))$rango=$tV["rango"];else $rango="Hoy";
                if(isset($tV["fInicio"]))$fInicio=$tV["fInicio"];else $fInicio=null;
                if(isset($tV["fFin"]))$fFin=$tV["fFin"];else $fFin=null;
                if(isset($tV["tipoPago"]))$tipoPago=$tV["tipoPago"]; else $tipoPago="Todo";
                if(isset($tV["proveedor"]))$proveedor=$tV["proveedor"]; else $proveedor="Todo";
                $data["totalesCompras"]=$compraDB->getTotalesCompras($rango,$fInicio,$fFin,$tipoPago,$proveedor);
            }else{
                $data["totalesCompras"]=$compraDB->getTotalesCompras();
            }
            return view("compras",$data);
        }else{
            $provs = new Proveedor();
            $provs=$provs->getAllNameProveedores();
            if(!session()->has("proveedores")){
                session()->put("proveedores",$provs->pluck("id")->toArray());
            }
            $compraDB=new Compra();
            if(!session()->has("ordenarCompras")){
                $this->ordenarCompras(null);
            }
            $orden=$this->getOrdenCompras();
            $comprasData=$compraDB->getAllComprasActivas($orden);
            $gastos=$compraDB->gastosMensuales();
            $data=[
                "compras"=>$comprasData["compras"],
                "totalCompras"=>$comprasData["totalCompra"],
                "proveedores"=>$provs,
                "gastos"=>$gastos[0],
                "gastosVisibles"=>$gastos[1]
            ];
            if(session()->has("totalesCompras")){
                $tV=session("totalesCompras");
                if(isset($tV["rango"]))$rango=$tV["rango"];else $rango="Hoy";
                if(isset($tV["fInicio"]))$fInicio=$tV["fInicio"];else $fInicio=null;
                if(isset($tV["fFin"]))$fFin=$tV["fFin"];else $fFin=null;
                if(isset($tV["tipoPago"]))$tipoPago=$tV["tipoPago"]; else $tipoPago="Todo";
                if(isset($tV["proveedor"]))$proveedor=$tV["proveedor"]; else $proveedor="Todo";
                $data["totalesCompras"]=$compraDB->getTotalesCompras($rango,$fInicio,$fFin,$tipoPago,$proveedor);
            }else{
                $data["totalesCompras"]=$compraDB->getTotalesCompras();
            }
            return view("compras",$data);
        }
    }

    public function cleanIndex(){
        if(session()->has("ordenarCompras")){
            session()->forget("ordenarCompras");
        }
        if(session()->has("filtroCompras")){
            session()->forget("filtroCompras");
        }
        if(session()->has("totalesCompras")){
            session()->forget("totalesCompras");
        }
        return redirect()->to(route("compra.index"));
    }

    public function newCompra(Request $request){
        $producto=$request->array([
            "idProd-newCompra",
            "precioCompra-newCompra",
            "precioVenta-newCompra",
            "proveedor-newCompra",
            "cantidad-newCompra",
            "medida-newCompra"]);
        $rulesNewProd=[
            "idProd-newCompra" => ["required"],
            "precioCompra-newCompra" => ["required", "decimal:0,2", "min:0.01", "max:999999999.99"],
            "precioVenta-newCompra" => ["required", "decimal:0,2", "min:0.01", "max:999999999.99"],
            "proveedor-newCompra" => ["required"],
            "cantidad-newCompra" => ["required", "decimal:0,2", "min:0.01", "max:999999999.99"],
            "medida-newCompra" => ["required", "in:Unidad,Kilogramo,Metro,Litro,precio"],
        ];
        $request->validate($rulesNewProd);

        $errores=[];

        if(!isset($producto["proveedor-newCompra"])){
            $errores["proveedor-newCompra"]="Elija al menos un proveedor.";
        }else{
            if(!in_array($producto["proveedor-newCompra"],session("proveedores"))){
                $errores["proveedor-newCompra"]="Elija un proveedor válido.";
            }
        }

        if(!strcmp($producto["cantidad-newCompra"],"Unidad")==0 && $producto["cantidad-newCompra"]<1){
            $errores["cantidad-newCompra"]="Ingrese una cantidad mayor a 1.";
        }

        if(!empty($errores)){
            return redirect()->back()->withInput()->withErrors($errores);
        }
        $producto["precioVenta-newCompra"]=BigDecimal::of($producto["precioVenta-newCompra"]);
        if(session()->has("trueEditCompra")){
            $oldCompra=session("trueEditCompra")["compra"];
            $idsProdsCompra=$oldCompra->productosCompra->pluck("producto")->toArray();
            if(in_array($producto["idProd-newCompra"],$idsProdsCompra)){
                $indexProdCompra=array_search($producto["idProd-newCompra"],$idsProdsCompra);
                if($indexProdCompra!==false){
                    if($oldCompra->proveedor!=$producto["proveedor-newCompra"]){
                        session()->forget("trueEditCompra");
                        if(!session()->has("compra")){
                            $prov=$producto["proveedor-newCompra"];
                            unset($producto["proveedor-newCompra"]);
                            $compra=[
                                "productos"=>[$producto],
                                "proveedor"=>$prov,
                                "montoTotal"=>BigDecimal::of("0.0000")
                            ];
                            session()->put("compra",$compra);
                            $idProv=session("compra")["proveedor"];
                        }else{
                            if(session("compra")["proveedor"]!=$producto["proveedor-newCompra"]){
                                $prov=$producto["proveedor-newCompra"];
                                unset($producto["proveedor-newCompra"]);
                                $compra=[
                                    "productos"=>[$producto],
                                    "proveedor"=>$prov,
                                    "montoTotal"=>BigDecimal::of("0.0000")
                                ];
                                session()->put("compra",$compra);                 
                            }else{
                                unset($producto["proveedor-newCompra"]);
                                $compra=session("compra");
                                $idProdsCompra=Arr::pluck($compra["productos"],"idProd-newCompra");
                                if(!empty($idProdsCompra) && in_array($producto["idProd-newCompra"],$idProdsCompra)){
                                    $key=array_search($producto["idProd-newCompra"],$idProdsCompra);
                                    $compra["productos"][$key]=$producto;
                                }else{
                                    $compra["productos"][]=$producto;
                                }
                                session()->put("compra",$compra);
                            }
                            $idProv=session("compra")["proveedor"];
                        }
                    }else{
                        $oldProd=$oldCompra->productosCompra[$indexProdCompra];
                        $oldTotalCompra=BigDecimal::of($oldCompra->monto_total)->toScale(4,RoundingMode::HALF_UP);
                        $oldTotalProd=BigDecimal::of($oldProd->total_producto)->toScale(4,RoundingMode::HALF_UP);
                        $newCant=BigDecimal::of($producto["cantidad-newCompra"])->toScale(4,RoundingMode::HALF_UP);
                        $newPrec=BigDecimal::of($producto["precioCompra-newCompra"])->toScale(4,RoundingMode::HALF_UP);
                        $medida=$producto["medida-newCompra"];
                        $newTotalCompra=$oldTotalCompra->minus($oldTotalProd)->toScale(4,RoundingMode::HALF_UP);
                        switch($medida){
                            case "precio":  $cantidad = $newCant->dividedBy($newPrec, 4, RoundingMode::DOWN);
                                            $newTotalProd=$newCant;
                                            $newTotalCompra=$newTotalCompra->plus($newTotalProd)->toScale(4,RoundingMode::HALF_UP);
                                            $oldProd->total_producto=$newTotalProd->__toString();
                                            $oldProd->precio_compra=$newPrec->__toString();
                                            $oldProd->cantidad=$cantidad->__toString();
                            break;
                            default:    $newTotalProd=$newPrec->multipliedBy($newCant)->toScale(4, RoundingMode::HALF_UP);
                                        $newTotalCompra=$newTotalCompra->plus($newTotalProd)->toScale(4,RoundingMode::HALF_UP);
                                        $oldProd->total_producto=$newTotalProd->__toString();
                                        $oldProd->precio_compra=$newPrec->__toString();
                                        $oldProd->cantidad=$newCant->__toString();
                        }
                        $oldCompra->monto_total=$newTotalCompra->__toString();
                        $idProv=$oldCompra->proveedor;
                        $oldCompra[$oldProd->producto]=$producto["precioVenta-newCompra"]->__toString();
                    }
                }else{
                    return redirect()->back()->withInput()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                }
            }else{
                return redirect()->back()->withInput()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }
        }elseif(!session()->has("compra")){
            $prov=$producto["proveedor-newCompra"];
            unset($producto["proveedor-newCompra"]);
            $compra=[
                "productos"=>[$producto],
                "proveedor"=>$prov,
                "montoTotal"=>BigDecimal::of("0.0000")
            ];
            session()->put("compra",$compra);
            $idProv=session("compra")["proveedor"];
        }else{
            if(session("compra")["proveedor"]!=$producto["proveedor-newCompra"]){
                $prov=$producto["proveedor-newCompra"];
                unset($producto["proveedor-newCompra"]);
                $compra=[
                    "productos"=>[$producto],
                    "proveedor"=>$prov,
                    "montoTotal"=>BigDecimal::of("0.0000")
                ];
                session()->put("compra",$compra);                 
            }else{
                unset($producto["proveedor-newCompra"]);
                $compra=session("compra");
                $idProdsCompra=Arr::pluck($compra["productos"],"idProd-newCompra");
                if(!empty($idProdsCompra) && in_array($producto["idProd-newCompra"],$idProdsCompra)){
                    $key=array_search($producto["idProd-newCompra"],$idProdsCompra);
                    $compra["productos"][$key]=$producto;
                }else{
                    $compra["productos"][]=$producto;
                }
                session()->put("compra",$compra);
            }
            $idProv=session("compra")["proveedor"];
        }
        $tipoC=$request->input("tipoCompra");
        switch($tipoC){
            case "añadirMas":   return redirect()->to(route("proveedor.proveedor",$idProv));
            default:    return redirect()->to(route("compra.procesarcompra"));
        }
    }

    public function procesarCompra(){
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
        if(!session()->has("trueEditCompra")){
            $productosCompra=[];
            $proveedor=null;
            $totalCompra=BigDecimal::of("0.0000");
            if(session()->has("compra")){
                $compra=session("compra");
                $provDB=new Proveedor();
                $proveedor=$provDB->getProveedorCompra($compra["proveedor"]);
                $prodDB=new Producto();
                $productosCompra=$compra["productos"];
                for($i=0;$i<sizeof($productosCompra);$i++){
                    $precioProd=BigDecimal::of($productosCompra[$i]["precioCompra-newCompra"])->toScale(4, RoundingMode::HALF_UP);
                    $productosCompra[$i]["precioCompra-newCompra"]=$precioProd;
                    $precioProdV=BigDecimal::of($productosCompra[$i]["precioVenta-newCompra"])->toScale(4, RoundingMode::HALF_UP);
                    $productosCompra[$i]["precioVenta-newCompra"]=$precioProdV;
                    $cantProd=BigDecimal::of($productosCompra[$i]["cantidad-newCompra"])->toScale(4, RoundingMode::HALF_UP);
                    switch($productosCompra[$i]["medida-newCompra"]){
                        case "precio":  $cantidad = $cantProd->dividedBy($precioProd, 4, RoundingMode::DOWN);
                                        $totalProd=$cantProd;
                                        $cantProd=$cantidad;
                                        $productosCompra[$i]["cantidad-newCompra"]=$cantProd;
                                        $productosCompra[$i]["total-newCompra"]=$totalProd;
                                        break;
                        default:    $totalProd=$precioProd->multipliedBy($cantProd)->toScale(4, RoundingMode::HALF_UP);
                                    $productosCompra[$i]["cantidad-newCompra"]=$cantProd;
                                    $productosCompra[$i]["total-newCompra"]=$totalProd;
                        
                    }
                    $productosCompra[$i]["producto-newCompra"]=$prodDB->getProductoCompra($productosCompra[$i]["idProd-newCompra"]);
                    $totalCompra=$totalCompra->plus($productosCompra[$i]["total-newCompra"])->toScale(4, RoundingMode::HALF_UP);
                }
                $compra["productos"]=$productosCompra;
                $compra["montoTotal"]=$totalCompra;
                session()->put("compra",$compra);
            }
            $data=[
                "proveedor"=>$proveedor,
                "productos"=>$productosCompra,
                "totalCompra"=>$totalCompra
            ];
            return view("procesarCompra",$data);
        }else{
            $data=[
                "compra"=>session("trueEditCompra")["compra"]
            ];
            return view("procesarCompra",$data);
        }
    }

    public function editProdCompra(){
        $idProd=request()->input("idProd");
        if(is_null($idProd)){
            return json_encode(["Error"=>"Ocurrió un error al intentar redireccionar la pagina."]);
        }else{
            session()->flash("editarCompra",true);
            return json_encode(["success"=>true,"redirect"=>route("producto.producto",$idProd)]);
        }
    }
    public function saveCompra(){
        $dataCompra=request()->array(["fechaCompra-newCompra", "metodoPago-newCompra", "fotos-newCompra"]);
        $rulesNewCompra=[
            "fechaCompra-newCompra" => ["required", "date", Rule::date()->beforeOrEqual(date("Y-m-d H:i",now()->getTimestamp())), Rule::date()->afterOrEqual(date("Y-m-d H:i",now()->getTimestamp()-31536000))],
            "metodoPago-newCompra" => ["required", "in:Tarjeta,Efectivo,Mixto"],
        ];
        request()->validate($rulesNewCompra);
        $errores=[];
        
        if(in_array($dataCompra["metodoPago-newCompra"],["Tarjeta","Mixto"])){
            if(!isset($dataCompra["fotos-newCompra"])){
                $errores["fotos-newCompra"]="Ingrese al menos un recibo de compra.";
            }
        }
        if(isset($dataCompra["fotos-newCompra"]) && sizeof($dataCompra["fotos-newCompra"])>5){
            $errores["fotos-newCompra"]="Ingrese hasta 5 fotos.";
        }

        if(session()->has("trueEditCompra")){
            if(isset($dataCompra["fotos-newCompra"])){
                $fotosDB=new RecibosCompra();
                $oldFotos=$fotosDB->getAllRecibosCompra(session("trueEditCompra")["idCompra"]);
                $oldFotosUrl=$oldFotos->pluck("url_img")->toArray();
                $oldFotosUrlOnline=$oldFotos->pluck("url_img_online")->toArray();
                $fotoIsOld=[];
                foreach($dataCompra["fotos-newCompra"] as $foto){
                    if(!in_array($foto,$oldFotosUrl) && !in_array($foto,$oldFotosUrlOnline)){
                        if(is_array(explode(",",$foto))){
                            if(base64_decode(explode(",",$foto)[1], true) === false){
                                $errores["fotos-newCompra"]="Error. Ingrese fotos validas.";
                                break;
                            }
                        }
                        if(!is_array(explode(";",$foto))){
                            $errores["fotos-newCompra"]="Error. Ingrese fotos validas.";
                            break;
                        }else{
                            $type=explode("/",explode(";",$foto)[0]);
                            if(!is_array($type)){
                                $errores["fotos-newCompra"]="Error. Ingrese fotos validas.";
                                break;
                            }
                        }
                    }
                    else{
                        $fotoIsOld[]=$foto;
                    }
                }
            }
        }else{
            if(isset($dataCompra["fotos-newCompra"])){
                foreach($dataCompra["fotos-newCompra"] as $foto){
                    if(is_array(explode(",",$foto))){
                        if(base64_decode(explode(",",$foto)[1], true) === false){
                            $errores["fotos-newCompra"]="Ingrese fotos validas.";
                            break;
                        }
                    }
                    if(!is_array(explode(";",$foto))){
                        $errores["fotos-newCompra"]="Ingrese fotos validas.";
                        break;
                    }else{
                        $type=explode("/",explode(";",$foto)[0]);
                        if(!is_array($type)){
                            $errores["fotos-newCompra"]="Ingrese fotos validas.";
                            break;
                        }
                    }
                }
            }
        }

        if(!empty($errores)){
            return redirect()->back()->withInput()->withErrors($errores);
        }
        if(session()->has("trueEditCompra")){
            $compra=session("trueEditCompra")["compra"];
            try{
                $invalidVentas=null;
                foreach($compra->productosCompra as $producto){
                    $upProd=[];
                    if(isset($compra[$producto->producto])){
                        $upProd["precio_venta"]=$compra[$producto->producto];
                    }
                    $prodDB=new Producto();
                    $oldProd=Producto::where("id","=",$producto->producto)->select("cantidad_disponible")->first();
                    $oldCantProdCompra=ProductosCompra::where("id","=",$producto->id)->select("cantidad")->first();
                    if(!isset($oldProd) || !isset($oldCantProdCompra)){
                        session()->forget("trueEditCompra");
                        return redirect()->to(route("compra.compra",$compra->id))->with("mensaje",["Mensaje"=>"Compra modificada, ocurrio un error al actualizar las cantidades de los productos, vincular los productos a la compra y actualizar los recibos.<br>Modifique la compra para registrar correctamente los datos.","Error"=>""]);
                    }else{
                        $oldCantDisp=BigDecimal::of($oldProd->cantidad_disponible)->toScale(4,RoundingMode::HALF_UP);
                        $oldCantTotalProdCompra=BigDecimal::of($oldCantProdCompra->cantidad)->toScale(4,RoundingMode::HALF_UP);
                        $newCantTotalProdCompra=BigDecimal::of($producto->cantidad)->toScale(4,RoundingMode::HALF_UP);
                        if($oldCantTotalProdCompra->isLessThan($newCantTotalProdCompra)){
                            $totalCant=$newCantTotalProdCompra->minus($oldCantTotalProdCompra)->toScale(4,RoundingMode::HALF_UP);
                            $newCantDisp=$oldCantDisp->plus($totalCant)->toScale(4,RoundingMode::HALF_UP);
                            $upProd["cantidad_disponible"]=$newCantDisp->__toString();
                            if(!$prodDB->editProducto($upProd,$producto->producto)){
                                session()->forget("trueEditCompra");
                                return redirect()->to(route("compra.compra",$compra->id))->with("mensaje",["Mensaje"=>"Compra modificada, ocurrio un error al actualizar las cantidades de los productos, vincular los productos a la compra y actualizar los recibos.<br>Modifique la compra para registrar correctamente los datos.","Error"=>""]);
                            }
                        }elseif($oldCantTotalProdCompra->isGreaterThan($newCantTotalProdCompra)){
                            if($oldCantDisp->minus($oldCantTotalProdCompra)->isLessThan(0)){
                                if($invalidVentas==null){
                                    $invalidVentas=Venta::with([
                                                            'productosVenta' => function($q){
                                                                $q->select(
                                                                    "productos_ventas.id",
                                                                    "productos_ventas.venta",
                                                                    "productos_ventas.producto",
                                                                    "productos_ventas.cantidad",
                                                                )
                                                                ->withTrashed();
                                                            },
                                                            'devoluciones' => function($q){
                                                                $q->select(
                                                                    "devoluciones_ventas.id",
                                                                    "devoluciones_ventas.venta"
                                                                )
                                                                ->with([
                                                                    "productos" => function($q){
                                                                        $q->select(
                                                                            "productos_devoluciones_ventas.producto",
                                                                            "productos_devoluciones_ventas.devolucion",
                                                                            "productos_devoluciones_ventas.cantidad",
                                                                            "productos_devoluciones_ventas.tipo_devolucion",
                                                                        );
                                                                    }
                                                                ]);
                                                            },
                                                        ])
                                                        ->where("created_at",">=",$compra->created_at)
                                                        ->where("created_at","<=",now())
                                                        ->where("venta_invalida","=","0")
                                                        ->withTrashed()
                                                        ->orderBy("created_at","asc")
                                                        ->get();
                                    if(!isset($invalidVentas)){
                                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                                    }
                                }
                                $cantidadVendida=BigDecimal::of(0);
                                foreach($invalidVentas as $venta){
                                    $idsProdsVenta=$venta->productosVenta->pluck("producto")->toArray();
                                    if(in_array($producto->producto,$idsProdsVenta)){
                                        $indexProdVenta=array_search($producto->producto,$idsProdsVenta);
                                        if($indexProdVenta===false){
                                            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                                        }else{
                                            $prodCompraVenta=$venta->productosVenta[$indexProdVenta];
                                            $cantProdVenta=BigDecimal::of($prodCompraVenta->cantidad)->toScale(4,RoundingMode::HALF_UP);
                                            $cantidadVendida=$cantidadVendida->plus($cantProdVenta)->toScale(4,RoundingMode::HALF_UP);
                                            if($cantidadVendida->isGreaterThan($newCantTotalProdCompra)){
                                                foreach($venta->productosVenta as $prod){
                                                    $oldProdVenta=Producto::where("id","=",$prod->producto)->select("cantidad_disponible")->first();
                                                    if(!isset($oldProdVenta)){
                                                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                                                    }
                                                    $oldCantProdVenta=BigDecimal::of($oldProdVenta->cantidad_disponible)->toScale(4,RoundingMode::HALF_UP);
                                                    $cantProdVendida=BigDecimal::of($prod->cantidad)->toScale(4,RoundingMode::HALF_UP);
                                                    $cantTotalProdDev=BigDecimal::of(0);
                                                    foreach($venta->devoluciones as $dev){
                                                        $idsProdsDev=$dev->productos->pluck("producto")->toArray();
                                                        $indexProdDevs=array_keys(array_filter($idsProdsDev,function($prodDev) use($prod){
                                                            return $prodDev==$prod->producto;
                                                        }));
                                                        foreach($indexProdDevs as $indexProdDev){
                                                            $prodDev=$dev->productos[$indexProdDev];
                                                            if(strcmp($prodDev->tipo_devolucion,"Fallado")!=0){
                                                                $cantProdDev=BigDecimal::of($prodDev->cantidad)->toScale(4,RoundingMode::HALF_UP);
                                                                $cantTotalProdDev=$cantTotalProdDev->plus($cantProdDev);
                                                            }
                                                        }
                                                    }
                                                    if($cantTotalProdDev->isGreaterThan(0)){
                                                        $cantProdVendida=$cantProdVendida->minus($cantTotalProdDev)->toScale(4,RoundingMode::HALF_UP);
                                                    }
                                                    if($producto->producto == $prod->producto){
                                                        $oldCantDisp=$oldCantDisp->plus($cantProdVendida);
                                                    }else{
                                                        $newCantProdV=$oldCantProdVenta->plus($cantProdVendida)->toScale(4,RoundingMode::HALF_UP);
                                                        $upProd["cantidad_disponible"]=$newCantProdV->__toString();
                                                        if(!$prodDB->editProducto($upProd,$prod->producto)){
                                                            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                                                        }
                                                    }
                                                }
                                                $recVentaDB=new RecibosVenta;
                                                $recibosV=$recVentaDB->getAllRecibosVenta($venta->id);
                                                if(isset($recibosV)){
                                                    foreach($recibosV as $reciboV){
                                                        $nameFoto=explode("/",$reciboV["url_img"])[sizeof(explode("/",$reciboV["url_img"]))-1];
                                                        if (Storage::disk('public')->exists("/fotos/recibos/ventas/".$nameFoto)) {
                                                            Storage::disk('public')->delete("/fotos/recibos/ventas/".$nameFoto);
                                                        }
                                                    }
                                                }
                                                $recDevVentaDB=new RecibosDevolucionVenta;
                                                $recibosDV=$recDevVentaDB->getAllRecibosDevVenta($venta->id);
                                                if(isset($recibosDV)){
                                                    foreach($recibosDV as $reciboDV){
                                                        $nameFoto=explode("/",$reciboDV["url_img"])[sizeof(explode("/",$reciboDV["url_img"]))-1];
                                                        if (Storage::disk('public')->exists("/fotos/recibos/devolucionesVentas/".$nameFoto)) {
                                                            Storage::disk('public')->delete("/fotos/recibos/devolucionesVentas/".$nameFoto);
                                                        }
                                                    }
                                                }
                                                $ventaDB=new Venta();
                                                if(!$ventaDB->editVenta(["venta_invalida"=>1],$venta->id)){
                                                    return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                                                }
                                                if(!$ventaDB->deleteVenta($venta->id)){
                                                    return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $totalCant=$oldCantTotalProdCompra->minus($newCantTotalProdCompra)->toScale(4,RoundingMode::HALF_UP);
                            $newCantDisp=$oldCantDisp->minus($totalCant)->toScale(4,RoundingMode::HALF_UP);
                            $upProd["cantidad_disponible"]=$newCantDisp->__toString();
                            if(!$prodDB->editProducto($upProd,$producto->producto)){
                                session()->forget("trueEditCompra");
                                return redirect()->to(route("compra.compra",$compra->id))->with("mensaje",["Mensaje"=>"Compra modificada, ocurrio un error al actualizar las cantidades de los productos, vincular los productos a la compra y actualizar los recibos.<br>Modifique la compra para registrar correctamente los datos.","Error"=>""]);
                            }
                        }
                    }
                }
            }catch(Error $e){
                session()->forget("trueEditCompra");
                return redirect()->to(route("compra.compra",$compra->id))->with("mensaje",["Mensaje"=>"Compra modificada, ocurrio un error al actualizar las cantidades de los productos, vincular los productos a la compra y actualizar los recibos.<br>Modifique la compra para registrar correctamente los datos.","Error"=>""]);
            }
            try{
                $prodComp=new ProductosCompra();
                foreach($compra->productosCompra as $producto){
                    if(!$prodComp->editProductoCompra([
                        "cantidad"=>$producto->cantidad,
                        "precio_compra"=>$producto->precio_compra,
                        "total_producto"=>$producto->total_producto,
                    ],$producto->id)){
                        session()->forget("trueEditCompra");
                        return redirect()->to(route("compra.compra",$compra->id))->with("mensaje",["Mensaje"=>"Compra modificada, ocurrio un error al actualizar los datos de los productos.<br>Modifique la compra para registrar correctamente los datos.","Error"=>""]);
                    }
                }
            }catch(Error $e){
                session()->forget("trueEditCompra");
                return redirect()->to(route("compra.compra",$compra->id))->with("mensaje",["Mensaje"=>"Compra modificada, ocurrio un error al actualizar los datos de los productos.<br>Modifique la compra para registrar correctamente los datos.","Error"=>""]);
            }
            try{
                $compraDB=new Compra();
                $editCompra=[
                    "monto_total"=>$compra->monto_total,
                    "tipo_pago"=>$compra->tipo_pago,
                    "fecha_compra"=>$dataCompra["fechaCompra-newCompra"],
                ];
                if(!$compraDB->editCompra($editCompra,$compra->id)){
                    session()->forget("trueEditCompra");
                    return redirect()->back()->withInput()->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar modificar la compra.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                }
            }catch(Error $e){
                session()->forget("trueEditCompra");
                return redirect()->to(route("inicio.inicio"))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar crear la compra.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }
            try{
                if(isset($oldFotos)){
                    foreach($oldFotos as $foto){
                        if(!in_array($foto["url_img"],$fotoIsOld) && !in_array($foto["url_img_online"],$fotoIsOld)){
                            $nameFoto=explode("/",$foto["url_img"])[sizeof(explode("/",$foto["url_img"]))-1];
                            if (Storage::disk('public')->exists("/fotos/recibos/compras/".$nameFoto)) {
                                Storage::disk('public')->delete("/fotos/recibos/compras/".$nameFoto);
                                $fotosDB->deleteFoto($foto["id"]);
                            }
                        }
                    }
                }
                if(isset($dataCompra["fotos-newCompra"])){
                    $urlFotos=[];
                    foreach($dataCompra["fotos-newCompra"] as $foto){
                        if(!in_array($foto,$fotoIsOld)){
                            $manager = new ImageManager(new Driver());
                            $image = $manager->read($foto, [DataUriImageDecoder::class,Base64ImageDecoder::class,]);
                            $urlfoto="/fotos/recibos/compras/".Str::random(12) . '.webP';
                            $urlFotos[]=$urlfoto;
                            if($image->width()>750){
                                Storage::disk('public')->put($urlfoto, $image->scale(750)->toWebp());
                            }else{
                                Storage::disk('public')->put($urlfoto, $image->toWebp());
                            }
                        }
                    }
                    $fotoDB=new RecibosCompra();
                    foreach($urlFotos as $urlFoto){
                        if(!$fotoDB->newReciboCompra([
                            "url_img"=>"/storage".$urlFoto,
                            "url_img_online"=>"/storage".$urlFoto,
                            "compra"=>$compra->id
                        ])){
                            session()->forget("trueEditCompra");
                            return redirect()->to(route("compra.compra",$compra->id))->with("mensaje",["Mensaje"=>"Compra modificada, ocurrio un error al actualizar los recibos de la compra.</br>Modifique la compra para registrar correctamente los recibos","Error"=>""]);
                        }
                    }
                }
            }catch(Error $e){
                session()->forget("trueEditCompra");
                return redirect()->to(route("compra.compra",$compra->id))->with("mensaje",["Mensaje"=>"Compra modificada, ocurrio un error al actualizar los recibos de la compra.</br>Modifique la compra para registrar correctamente los recibos","Error"=>""]);
            }
            session()->forget("trueEditCompra");
            return redirect()->to(route("compra.compra",$compra->id))->with("mensaje",["Mensaje"=>"Compra modificada exitosamente.","Success"=>""]);
        }else{
            try{
                $compraDB=new Compra();
                $compraSession=session("compra");
                $newCompra=[
                    "monto_total"=>$compraSession["montoTotal"]->__toString(),
                    "tipo_pago"=>$dataCompra["metodoPago-newCompra"],
                    "proveedor"=>$compraSession["proveedor"],
                    "fecha_compra"=>$dataCompra["fechaCompra-newCompra"],
                ];
                $idNewCompra=$compraDB->newCompra($newCompra);
                if(isset($idNewCompra)){
                    session()->forget("compra");
                }
            }catch(Error $e){
                return redirect()->to(route("inicio.inicio"))->with("mensaje",["Mensaje"=>"Ocurrio un error al intentar crear la compra.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }
            try{
                if(isset($idNewCompra)){
                    foreach($compraSession["productos"] as $producto){
                        $oldCant=Producto::select("cantidad_disponible")->where("id","=",$producto["producto-newCompra"]->id)->first();
                        $oldCant=BigDecimal::of($oldCant["cantidad_disponible"])->toScale(4, RoundingMode::HALF_UP);
                        $newCant=$oldCant->plus($producto["cantidad-newCompra"])->toScale(4, RoundingMode::HALF_UP);
                        $aux=$newCant->toScale(0, RoundingMode::HALF_UP);
                        if($aux->isLessThan($newCant)){
                            $upProd=["cantidad_disponible"=>$newCant->__toString()];
                        }else{
                            $upProd=["cantidad_disponible"=>$aux->__toString()];
                        }
                        $upProd["precio_venta"]=$producto["precioVenta-newCompra"]->toScale(2, RoundingMode::HALF_UP)->__toString();
                        $prodDB=new Producto();
                        $res=$prodDB->editProducto($upProd,$producto["producto-newCompra"]->id);
                        if(!$res){
                            return redirect()->to(route("compra.compra",$idNewCompra))->with("mensaje",["Mensaje"=>"Compra registrada, ocurrio un error al actualizar las cantidades de los productos, vincular los productos a la compra y registrar los recibos.<br>Modifique la compra para registrar correctamente los productos.","Error"=>""]);
                        }
                    }
                }
            }catch(Error $e){
                return redirect()->to(route("compra.compra",$idNewCompra))->with("mensaje",["Mensaje"=>"Compra registrada, ocurrio un error al actualizar las cantidades de los productos, vincular los productos a la compra y registrar los recibos.<br>Modifique la compra para registrar correctamente los productos.","Error"=>""]);
            }
            try{
                if(isset($idNewCompra)){
                    $prodComp=new ProductosCompra();
                    foreach($compraSession["productos"] as $producto){
                        $res=$prodComp->newProductoCompra([
                            "producto"=>$producto["producto-newCompra"]->id,
                            "compra"=>$idNewCompra,
                            "cantidad"=>$producto["cantidad-newCompra"]->toScale(2, RoundingMode::HALF_UP)->__toString(),
                            "precio_compra"=>$producto["precioCompra-newCompra"]->__toString(),
                            "total_producto"=>$producto["total-newCompra"]->__toString(),
                        ]);
                        if(!$res){
                            return redirect()->to(route("compra.compra",$idNewCompra))->with("mensaje",["Mensaje"=>"Compra registrada, ocurrio un error al vincular los productos a la compra.<br>Modifique la compra para registrar correctamente los productos.","Error"=>""]);
                        }
                    }
                }
            }catch(Error $e){
                return redirect()->to(route("compra.compra",$idNewCompra))->with("mensaje",["Mensaje"=>"Compra registrada, ocurrio un error al vincular los productos a la compra.<br>Modifique la compra para registrar correctamente los productos.","Error"=>""]);
            }
            if(isset($dataCompra["fotos-newCompra"])){
                try{
                    if(isset($idNewCompra)){
                        $urlFotos=[];
                        foreach($dataCompra["fotos-newCompra"] as $foto){
                            $manager = new ImageManager(new Driver());
                            $image = $manager->read($foto, [DataUriImageDecoder::class,Base64ImageDecoder::class,]);
                            $urlfoto="/fotos/recibos/compras/".Str::random(12) . '.webP';
                            $urlFotos[]=$urlfoto;
                            if($image->width()>750){
                                Storage::disk('public')->put($urlfoto, $image->scale(750)->toWebp());
                            }else{
                                Storage::disk('public')->put($urlfoto, $image->toWebp());
                            }
                        }
                        $fotoDB=new RecibosCompra();
                        foreach($urlFotos as $urlFoto){
                            $res=false;
                            while(!$res){
                                $res=$fotoDB->newReciboCompra([
                                                "url_img"=>"/storage".$urlFoto,
                                                "url_img_online"=>"/storage".$urlFoto,
                                                "compra"=>$idNewCompra
                                            ]);
                            }
                        }
                    }
                }catch(Error $e){
                    return redirect()->to(route("compra.compra",$idNewCompra))->with("mensaje",["Mensaje"=>"Compra registrada, ocurrio un error al almacenar los recibos de la compra.</br>Modifique la compra para registrar correctamente los recibos","Error"=>""]);
                }
            }
            return redirect()->to(route("compra.inicio"))->with("mensaje",["Mensaje"=>"Compra registrada exitosamente.","Success"=>""]);
        }
    }

    public function deleteProdCompra(){
        $fmt = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
        $fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 4);
        $idProd=request()->input("idProd");
        if($idProd!=null && session()->has("compra")){
            $compra=session("compra");
            $idsProdCompra=Arr::pluck($compra["productos"],"idProd-newCompra");
            if(in_array($idProd,$idsProdCompra)){
                $key=array_search($idProd,$idsProdCompra);
                $deletedProd=$compra["productos"][$key];
                $arrMin=array_slice($compra["productos"],0,$key);
                $trashArr=array_splice($compra["productos"],0,$key+1,$arrMin);
                $newTotalCompra=BigDecimal::of("0.0000");
                $newTotalProds=sizeof($compra["productos"]);
                if($newTotalProds==0){
                    session()->forget("compra");
                }else{
                    $compra["montoTotal"]=$compra["montoTotal"]->minus($deletedProd["total-newCompra"])->toScale(4, RoundingMode::HALF_UP);
                    $newTotalCompra=$compra["montoTotal"];
                    session()->put("compra",$compra);
                }
                $newTotalCompra=$fmt->format($newTotalCompra->__toString());
                return json_encode(["success"=>true,"totalCompra"=>$newTotalCompra,"totalProds"=>$newTotalProds]);
            }
        }
        return json_encode(["Error"=>"Ocurrió un error al intentar eliminar el producto."]);
    }

    public function mostrarCompra($idCompra){
        if(session()->has("trueEditCompra") && session("trueEditCompra")["compra"]->id == $idCompra){
            $trueEditCompra=session("trueEditCompra");
            return view("compra",["compra"=>$trueEditCompra["compra"]]);
        }else{
            $compraDB=new Compra();
            $compra=$compraDB->getCompra($idCompra);
            if(!isset($compra)){
                return redirect()->to(route("compra.inicio"))->with("mensaje",["Mensaje"=>"Compra no disponible.","Error"=>""]);
            }else{
                return view("compra",["compra"=>$compra]);
            }
        }
    }

    public function buscarCompra(Request $request){
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
        $busqueda=$request->array(["orden","pago","proveedor","fechas","fInicio","fFin","todo"]);
        if(isset($busqueda["todo"])){
            session()->forget("filtroCompras");
            $this->ordenarCompras(null);
            return redirect()->to(route("compra.index"));
        }
        else{
            if(!session()->has("filtroCompras")){
                session()->put("filtroCompras",[
                    "orden"=>null,
                    "proveedor"=>null,
                    "pago"=>null,
                    "fInicio"=>null,
                    "fFin"=>null
                ]);
            }
            if(isset($busqueda["orden"])){
                $filtro=session("filtroCompras");
                $filtro["orden"]=$busqueda["orden"];
                session()->put("filtroCompras",$filtro);
            }
            if(isset($busqueda["proveedor"])){
                $filtro=session("filtroCompras");
                $filtro["proveedor"]=$busqueda["proveedor"];
                session()->put("filtroCompras",$filtro);
            }
            if(isset($busqueda["pago"])){
                $filtro=session("filtroCompras");
                if(in_array($busqueda["pago"],["Tarjeta","Efectivo","Mixto"])){
                    $filtro["pago"]=$busqueda["pago"];
                }else{
                    $filtro["pago"]=null;
                }
                session()->put("filtroCompras",$filtro);
            }
            if(isset($busqueda["fechas"])){
                if(isset($busqueda["fInicio"])){
                    $filtro=session("filtroCompras");
                    $fI=date_create($busqueda["fInicio"]);
                    if(date_timestamp_get($fI)<1420081200){
                        $filtro["fInicio"]=date("Y-m-d H:i",1420081200);
                    }else{
                        $filtro["fInicio"]=date("Y-m-d H:i",date_timestamp_get($fI));
                    }
                    session()->put("filtroCompras",$filtro);
                }elseif(isset(session("filtroCompras")["fInicio"])){
                    $filtro=session("filtroCompras");
                    $filtro["fInicio"]=null;
                    session()->put("filtroCompras",$filtro);
                }
                if(isset($busqueda["fFin"])){
                    $filtro=session("filtroCompras");
                    $fF=date_create($busqueda["fFin"]);
                    if(date_timestamp_get($fF)<1420081200){
                        $filtro["fFin"]=date("Y-m-d H:i",1420081200);
                    }else{
                        $filtro["fFin"]=date("Y-m-d H:i",date_timestamp_get($fF));
                    }
                    session()->put("filtroCompras",$filtro);
                }elseif(isset(session("filtroCompras")["fFin"])){
                    $filtro=session("filtroCompras");
                    $filtro["fFin"]=null;
                    session()->put("filtroCompras",$filtro);
                }
            }
            return redirect()->to(route("compra.index"));
        }
    }

    public function ordenarCompras($orden){
        switch($orden){
            case "MasReciente": session()->put("ordenarCompras","MasReciente");
                    break;
            case "MenosReciente": session()->put("ordenarCompras","MenosReciente");
                    break;
            case "MayorMonto": session()->put("ordenarCompras","MayorMonto");
                    break;
            case "MenorMonto": session()->put("ordenarCompras","MenorMonto");
                    break;
            default: session()->put("ordenarCompras","MasReciente");
        }
    }

    public function setOrdenCompras(){
        $orden=request()->input("orden");
        $this->ordenarCompras($orden);
        return redirect()->to(route("compra.index"));
    }

    private function getOrdenCompras(){
        switch(session("ordenarCompras")){
            case "MasReciente": return ["orden"=>"fecha_compra", "direccion"=>"desc"];
            case "MenosReciente": return ["orden"=>"fecha_compra", "direccion"=>"asc"];
            case "MayorMonto": return ["orden"=>"monto_total", "direccion"=>"desc"];
            case "MenorMonto": return ["orden"=>"monto_total", "direccion"=>"asc"];
            default: return ["orden"=>"fecha_compra", "direccion"=>"desc"];
        };
    }

    public function getTotalesCompras(){
        $data=request()->array(["rango","fFin","fInicio","tipoPago","proveedor"]);
        if(sizeof($data)==0){
            return redirect()->to(route("compra.index"));
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
                            if(!session()->has("totalesCompras")){
                                session()->put("totalesCompras",[
                                    "rango"=>"Personal",
                                    "fInicio"=>$fInicio->toDateTimeString(),
                                    "fFin"=>$fFin->toDateTimeString(),
                                ]);
                            }else{
                                $totalesCompras=session("totalesCompras");
                                $totalesCompras["rango"]="Personal";
                                $totalesCompras["fInicio"]=$fInicio->toDateTimeString();
                                $totalesCompras["fFin"]=$fFin->toDateTimeString();
                                session()->put("totalesCompras",$totalesCompras);
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
                            if(!session()->has("totalesCompras")){
                                session()->put("totalesCompras",[
                                    "rango"=>"Personal",
                                    "fInicio"=>$fInicio->toDateTimeString(),
                                ]);
                            }else{
                                $totalesCompras=session("totalesCompras");
                                $totalesCompras["rango"]="Personal";
                                $totalesCompras["fInicio"]=$fInicio->toDateTimeString();
                                $totalesCompras["fFin"]=null;
                                session()->put("totalesCompras",$totalesCompras);
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
                            if(!session()->has("totalesCompras")){
                                session()->put("totalesCompras",[
                                    "rango"=>"Personal",
                                    "fFin"=>$fFin->toDateTimeString(),
                                ]);
                            }else{
                                $totalesCompras=session("totalesCompras");
                                $totalesCompras["rango"]="Personal";
                                $totalesCompras["fFin"]=$fFin->toDateTimeString();
                                $totalesCompras["fInicio"]=null;
                                session()->put("totalesCompras",$totalesCompras);
                            }
                        }elseif(!isset($data["fInicio"]) && !isset($data["fFin"])){
                            if(session()->has("totalesCompras")){
                                $totalesCompras=session("totalesCompras");
                                $totalesCompras["rango"]="Personal";
                                $totalesCompras["fInicio"]=null;
                                $totalesCompras["fFin"]=null;
                                session()->put("totalesCompras",$totalesCompras);
                            }
                        }
                    }
                    if(!session()->has("totalesCompras")){
                        session()->put("totalesCompras",["rango"=>$data["rango"]]);
                    }else{
                        $totalesCompras=session("totalesCompras");
                        $totalesCompras["rango"]=$data["rango"];
                        session()->put("totalesCompras",$totalesCompras);
                    }
                }else{
                    if(!session()->has("totalesCompras")){
                        session()->put("totalesCompras",["rango"=>"Hoy"]);
                    }else{
                        $totalesCompras=session("totalesCompras");
                        $totalesCompras["rango"]="Hoy";
                        session()->put("totalesCompras",$totalesCompras);
                    }
                }
            }
            if(isset($data["tipoPago"])){
                if(in_array($data["tipoPago"],["Tarjeta","Efectivo","Mixto","Todo"])){
                    if(!session()->has("totalesCompras")){
                        session()->put("totalesCompras",["tipoPago"=>$data["TipoPago"]]);
                    }else{
                        $totalesCompras=session("totalesCompras");
                        $totalesCompras["tipoPago"]=$data["tipoPago"];
                        session()->put("totalesCompras",$totalesCompras);
                    }
                }else{
                    if(!session()->has("totalesCompras")){
                        session()->put("totalesCompras",["tipoPago"=>"Todo"]);
                    }else{
                        $totalesCompras=session("totalesCompras");
                        $totalesCompras["tipoPago"]="Todo";
                        session()->put("totalesCompras",$totalesCompras);
                    }
                }
            }
            if(isset($data["proveedor"])){
                if(in_array($data["proveedor"],session("proveedores"))){
                    if(!session()->has("totalesCompras")){
                        session()->put("totalesCompras",["proveedor"=>$data["proveedor"]]);
                    }else{
                        $totalesCompras=session("totalesCompras");
                        $totalesCompras["proveedor"]=$data["proveedor"];
                        session()->put("totalesCompras",$totalesCompras);
                    }
                }else{
                    if(!session()->has("totalesCompras")){
                        session()->put("totalesCompras",["proveedor"=>"Todo"]);
                    }else{
                        $totalesCompras=session("totalesCompras");
                        $totalesCompras["proveedor"]="Todo";
                        session()->put("totalesCompras",$totalesCompras);
                    }
                }
            }
            return redirect()->to(route("compra.index"));
        }
    }

    public function deleteCompra(){
        $deleteC=request()->input("eliminarCompra");
        $idCompra=request()->input("idCompra-deleteCompra");
        if(!isset($deleteC) || !isset($idCompra)){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }else{
            $rulesAdmin=[
                "passAdmin-deleteCompra" => ["required","min:6", "max:16", new ValidPass()]
            ];
            request()->validate($rulesAdmin);
            $pass=request()->input("passAdmin-deleteCompra");
            $compra=request()->input("idCompra-deleteCompra");
            $btnDelete=request()->input("eliminarCompra");
            if(!isset($btnDelete) || (!isset($compra))){
                return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }else{
                $admin=User::select("password")->where("name","=","Administrador")->first();
                if (Hash::check($pass, $admin->password)) {
                    try{
                        $recCompraDB=new RecibosCompra;
                        $recibosV=$recCompraDB->getAllRecibosCompra($compra);
                        if(isset($recibosV)){
                            foreach($recibosV as $reciboV){
                                $nameFoto=explode("/",$reciboV["url_img"])[sizeof(explode("/",$reciboV["url_img"]))-1];
                                if (Storage::disk('public')->exists("/fotos/recibos/compras/".$nameFoto)) {
                                    Storage::disk('public')->delete("/fotos/recibos/compras/".$nameFoto);
                                }
                            }
                        }
                        $compraDB=new Compra();
                        if(!$compraDB->deleteCompra($idCompra)){
                            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                        }else{
                            return redirect()->to(route("compra.inicio"))->with("mensaje",["Mensaje"=>"¡Compra eliminada!","Success"=>""]);
                        }
                    }catch(Error $e){
                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                    }
                }else{
                    return redirect()->back()->withErrors(["passAdmin-deleteCompra"=>"Contraseña incorrecta."]);
                }
            }    
        }
    }
    public function editarCompra(){
        $editC=request()->input("editarCompra");
        $idCompra=request()->input("idCompra-editCompra");
        if(!isset($editC) || !isset($idCompra)){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }else{
            $rulesAdmin=[
                "passAdmin-editCompra" => ["required","min:6", "max:16", new ValidPass()]
            ];
            request()->validate($rulesAdmin);
            $pass=request()->input("passAdmin-editCompra");
            $compra=request()->input("idCompra-editCompra");
            $btnDelete=request()->input("editarCompra");
            if(!isset($btnDelete) || (!isset($compra))){
                return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }else{
                $admin=User::select("password")->where("name","=","Administrador")->first();
                if (Hash::check($pass, $admin->password)) {
                    $compraDB=new Compra();
                    $oldCompra=$compraDB->getCompra($compra);
                    if(!isset($oldCompra)){
                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                    }else{
                        $trueEditCompra=[
                            "idCompra"=>$compra,
                            "compra"=>$oldCompra
                        ];
                        session()->put("trueEditCompra",$trueEditCompra);
                        return redirect()->to(route("compra.procesarcompra"))->with("mensaje",["Mensaje"=>"¡Editando compra!","Success"=>""]);
                    }
                }else{
                    return redirect()->back()->withErrors(["passAdmin-editCompra"=>"Contraseña incorrecta."]);
                }
            }    
        }
    }

    public function descartarEditCompra(){
        if(session()->has("trueEditCompra")){
            $compra=session("trueEditCompra");
            session()->forget("trueEditCompra");
            return redirect()->to(route("compra.compra",$compra))->with("mensaje",["Mensaje"=>"¡Edición cancelada!","Success"=>""]);
        }else{
            return redirect()->to(route("compra.inicio"))->with("mensaje",["Mensaje"=>"¡Edición cancelada!","Success"=>""]);
        }
    }

    public function getOldProdsCompra(){
        $idCompra=request()->input("idCompra");
        if(!isset($idCompra)){
            return json_encode(["Error"=>"Error al reiniciar los productos."]);
        }else{
            $compraDB=new Compra();
            $compra=$compraDB->getCompra($idCompra);
            $trueEditCompra=session("trueEditCompra");
            $trueEditCompra["compra"]=$compra;
            session()->put("trueEditCompra",$trueEditCompra);
            $divProds="<table><tbody id='tableProdsEditCompra'>";
            foreach($compra->productosCompra as $producto){
                $data=[
                    "producto"=>$producto,
                    "tipo"=>"editCompra"
                ];
                $divProds.=view("components.productos-tabla-component",$data)->render();
            }
            $divProds.="</tbody></table>";
            $fmt = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
            $fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 4);
            return json_encode([
                            "success"=>true,
                            "divProds"=>$divProds,
                            "oldTotalCompra"=>"$".$fmt->format($compra->monto_total)
                        ]);
        }
    }

    public function revertirCompra(){
        $revertC=request()->input("revertirCompra");
        $idCompra=request()->input("idCompra-revertCompra");
        if(!isset($revertC) || !isset($idCompra)){
            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
        }else{
            $rulesAdmin=[
                "passAdmin-revertCompra" => ["required","min:6", "max:16", new ValidPass()]
            ];
            request()->validate($rulesAdmin);
            $pass=request()->input("passAdmin-revertCompra");
            $compra=request()->input("idCompra-revertCompra");
            $btnDelete=request()->input("revertirCompra");
            if(!isset($btnDelete) || (!isset($compra))){
                return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
            }else{
                $admin=User::select("password")->where("name","=","Administrador")->first();
                if (Hash::check($pass, $admin->password)) {
                    $compraDB=new Compra();
                    try{
                        $oldCompra=$compraDB->getCompra($compra);
                        if(!isset($oldCompra)){
                            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                        }else{
                            $invalidVentas=Venta::with([
                                                    'productosVenta' => function($q){
                                                        $q->select(
                                                            "productos_ventas.id",
                                                            "productos_ventas.venta",
                                                            "productos_ventas.producto",
                                                            "productos_ventas.cantidad",
                                                        )
                                                        ->withTrashed();
                                                    },
                                                    'devoluciones' => function($q){
                                                        $q->select(
                                                            "devoluciones_ventas.id",
                                                            "devoluciones_ventas.venta"
                                                        )
                                                        ->with([
                                                            "productos" => function($q){
                                                                $q->select(
                                                                    "productos_devoluciones_ventas.producto",
                                                                    "productos_devoluciones_ventas.devolucion",
                                                                    "productos_devoluciones_ventas.cantidad",
                                                                    "productos_devoluciones_ventas.tipo_devolucion",
                                                                );
                                                            }
                                                        ]);
                                                    },
                                                ])
                                                ->where("created_at",">=",$oldCompra->created_at)
                                                ->where("created_at","<=",now())
                                                ->where("venta_invalida","=","0")
                                                ->withTrashed()
                                                ->orderBy("created_at","desc")
                                                ->get();
                            if(!isset($invalidVentas)){
                                return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                            }else{
                                foreach($oldCompra->productosCompra as $producto){
                                    $upProd=[];
                                    $prodDB=new Producto();
                                    $cantProdComprada=BigDecimal::of($producto->cantidad);
                                    $oldProdComrpa=Producto::where("id","=",$producto->producto)->select("cantidad_disponible")->first();
                                    if(!isset($oldProdComrpa)){
                                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                                    }
                                    $oldCantProd=BigDecimal::of($oldProdComrpa->cantidad_disponible)->toScale(4,RoundingMode::HALF_UP);
                                    if(($oldCantProd->minus($cantProdComprada))->isLessThan(0)){
                                        $cantidadRecuperada=BigDecimal::of(0);
                                        foreach($invalidVentas as $venta){
                                            $idsProdsVenta=$venta->productosVenta->pluck("producto")->toArray();
                                            if(in_array($producto->producto,$idsProdsVenta)){
                                                foreach($venta->productosVenta as $prod){
                                                    $oldProdVenta=Producto::where("id","=",$prod->producto)->select("cantidad_disponible")->first();
                                                    if(!isset($oldProdVenta)){
                                                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                                                    }
                                                    $oldCantProdVenta=BigDecimal::of($oldProdVenta->cantidad_disponible)->toScale(4,RoundingMode::HALF_UP);
                                                    $cantProdVendida=BigDecimal::of($prod->cantidad)->toScale(4,RoundingMode::HALF_UP);
                                                    $cantTotalProdDev=BigDecimal::of(0);
                                                    foreach($venta->devoluciones as $dev){
                                                        $idsProdsDev=$dev->productos->pluck("producto")->toArray();
                                                        $indexProdDevs=array_keys(array_filter($idsProdsDev,function($prodDev) use($prod){
                                                            return $prodDev==$prod->producto;
                                                        }));
                                                        foreach($indexProdDevs as $indexProdDev){
                                                            $prodDev=$dev->productos[$indexProdDev];
                                                            if(strcmp($prodDev->tipo_devolucion,"Fallado")!=0){
                                                                $cantProdDev=BigDecimal::of($prodDev->cantidad)->toScale(4,RoundingMode::HALF_UP);
                                                                $cantTotalProdDev=$cantTotalProdDev->plus($cantProdDev);
                                                            }
                                                        }
                                                    }
                                                    $cantTotalProdVendido=$cantProdVendida->minus($cantTotalProdDev)->toScale(4,RoundingMode::HALF_UP);
                                                    if($prod->producto==$producto->producto){
                                                        $cantidadRecuperada=$cantidadRecuperada->plus($cantTotalProdVendido)->toScale(4,RoundingMode::HALF_UP);
                                                    }
                                                    $newCantProdV=$oldCantProdVenta->plus($cantTotalProdVendido)->toScale(4,RoundingMode::HALF_UP);
                                                    $upProd["cantidad_disponible"]=$newCantProdV->__toString();
                                                    if(!$prodDB->editProducto($upProd,$prod->producto)){
                                                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                                                    }
                                                }
                                                $ventaDB=new Venta();
                                                $recVentaDB=new RecibosVenta;
                                                $recibosV=$recVentaDB->getAllRecibosVenta($venta->id);
                                                if(isset($recibosV)){
                                                    foreach($recibosV as $reciboV){
                                                        $nameFoto=explode("/",$reciboV["url_img"])[sizeof(explode("/",$reciboV["url_img"]))-1];
                                                        if (Storage::disk('public')->exists("/fotos/recibos/ventas/".$nameFoto)) {
                                                            Storage::disk('public')->delete("/fotos/recibos/ventas/".$nameFoto);
                                                        }
                                                    }
                                                }
                                                $recDevVentaDB=new RecibosDevolucionVenta;
                                                $recibosDV=$recDevVentaDB->getAllRecibosDevVenta($venta->id);
                                                if(isset($recibosDV)){
                                                    foreach($recibosDV as $reciboDV){
                                                        $nameFoto=explode("/",$reciboDV["url_img"])[sizeof(explode("/",$reciboDV["url_img"]))-1];
                                                        if (Storage::disk('public')->exists("/fotos/recibos/devolucionesVentas/".$nameFoto)) {
                                                            Storage::disk('public')->delete("/fotos/recibos/devolucionesVentas/".$nameFoto);
                                                        }
                                                    }
                                                }
                                                if(!$venta->venta_invalida){
                                                    if(!$ventaDB->editVenta(["venta_invalida"=>1],$venta->id)){
                                                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                                                    }
                                                }
                                                if($venta->deleted_at==null){
                                                    if(!$ventaDB->deleteVenta($venta->id)){
                                                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                                                    }
                                                }

                                            }
                                            if($cantidadRecuperada->isGreaterThanOrEqualTo($cantProdComprada)){
                                                break;
                                            }
                                        }
                                        $oldCantProd=$oldCantProd->plus($cantidadRecuperada);
                                    }
                                    $newCantDisp=$oldCantProd->minus($cantProdComprada)->toScale(4,RoundingMode::HALF_UP);
                                    $upProd["cantidad_disponible"]=$newCantDisp->__toString();
                                    if(!$prodDB->editProducto($upProd,$producto->producto)){
                                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                                    }
                                }
                            }
                        }
                    }catch(Error $e){
                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                    }
                    try{
                        $recCompraDB=new RecibosCompra;
                        $recibosC=$recCompraDB->getAllRecibosCompra($compra);
                        if(isset($recibosC)){
                            foreach($recibosC as $reciboC){
                                $nameFoto=explode("/",$reciboC["url_img"])[sizeof(explode("/",$reciboC["url_img"]))-1];
                                if (Storage::disk('public')->exists("/fotos/recibos/compras/".$nameFoto)) {
                                    Storage::disk('public')->delete("/fotos/recibos/compras/".$nameFoto);
                                }
                            }
                        }
                        if(!$compraDB->editCompra(["compra_deshecha"=>1],$compra)){
                            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                        }
                        if(!$compraDB->deleteCompra($compra)){
                            return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                        }else{
                            return redirect()->to(route("compra.inicio"))->with("mensaje",["Mensaje"=>"¡Compra deshecha!","Success"=>""]);
                        }
                    }catch(Error $e){
                        return redirect()->back()->with("mensaje",["Mensaje"=>"Ocurrio un error inesperado.<br>Intente nuevamente en unos segundos.","Error"=>""]);
                    }
                }else{
                    return redirect()->back()->withErrors(["passAdmin-revertCompra"=>"Contraseña incorrecta."]);
                }
            }    
        }
    }
}
