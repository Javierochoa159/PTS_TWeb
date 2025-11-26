@extends('layouts.index')

@section('head')
    @vite([
        "resources/css/myStyles/inicio.css",
        "resources/css/myStyles/procesarVenta.css",
        "resources/css/myStyles/mapa.css"
    ])
@endsection

@php
    $fmt4 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt4->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 4);
    $fmt2 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt2->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
    $fmt0 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt0->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);

    if(!session()->has("editVenta") && !isset($venta)){
        if(!session()->has("devVenta")){
            if(session()->has("carrito")){
                $carrito=session("carrito");
            }
        }else{
            $devVenta=session("devVenta");
        }
    }

@endphp

@section("content")
    <x-portada-component/>
    <x-buscador-component type="activo"/>
    <x-mensaje-div-component/>
    
    <div class="m-2 py-5 d-flex primerDivProd flex-column align-items-center justify-content-center">
        <div class="col-11 productos p-2">
            <form id="{{ isset($devVenta) ? "formConfirmarDevolucion" : "formConfirmarVenta" }}" autocomplete="off" action="{{ isset($carrito) ? route("venta.saveventa") : (isset($venta) ? route("venta.savemodificacionventa") : ( isset($devVenta) ? route("venta.savedevolucionventa") : "")) }}" method="post" novalidate>
                @csrf
                <table class="col-12">
                    <thead>
                        <tr>
                            @if (!isset($devVenta))
                                <th class="col-5 firstTh"><p class="p-2 m-0">Producto a vender</p></th>
                                <th class="col-2"><p class="p-2 m-0">Precio de venta</p></th>
                                <th class="col-2"><p class="p-2 m-0">Cantidad</p></th>
                                <th class="col-1"><p class="p-2 m-0">Medida</p></th>
                                <th class="col-2"><p class="p-2 m-0">Total</p></th>
                            @else
                                <th class="col-1"><p class="p-0 m-0">Devolver</p></th>
                                <th class="col-4"><p class="p-2 m-0">Producto vendido</p></th>
                                <th class="col-2"><p class="p-2 m-0">Precio de venta</p></th>
                                <th class="col-2"><p class="p-2 m-0">Cantidad</p></th>
                                <th class="col-1"><p class="p-2 m-0">Medida</p></th>
                                <th class="col-2"><p class="p-2 m-0">Total</p></th>
                            @endif
                        </tr>
                    </thead>
                    @if(isset($carrito["productos"]))
                        <tbody>
                            @foreach ($carrito["productos"] as $producto)
                                <x-producto-venta-tabla-component :producto="$producto" tipo="vender"/>
                            @endforeach
                        </tbody>
                    @elseif(isset($venta))
                        <tbody>
                            @foreach ($venta->productosVenta as $producto)
                                <x-producto-venta-tabla-component :producto="$producto" tipo="vendido"/>
                            @endforeach
                        </tbody>
                    @elseif(isset($devVenta))
                        @php
                            $idsDev=["devIndex"=>[],"prodDevId"=>[],"prodDevIndex"=>[]];
                            for($i=0;$i<sizeof($devVenta->devoluciones);$i++){
                                $idsProds=$devVenta->devoluciones[$i]->productos->pluck("producto")->toArray();
                                for($j=0; $j<sizeof($idsProds);$j++){
                                    if(!in_array($idsProds[$j],$idsDev["prodDevId"])){
                                        $idsDev["prodDevId"][]=$idsProds[$j];
                                        $idsDev["devIndex"][]=$i;
                                        $idsDev["prodDevIndex"][]=$j;
                                    }
                                }
                            }
                            $devoluciones=$devVenta->devoluciones;
                        @endphp
                        <tbody>
                            @foreach ($devVenta->productosVenta as $producto)
                                @if(!in_array($producto->producto,$idsDev["prodDevId"]))
                                    <x-producto-venta-tabla-component :producto="clone $producto" tipo="devolucion"/>
                                @else
                                    <x-producto-venta-tabla-component :producto="clone $producto" tipo="devolucion" :devoluciones="clone $devoluciones" :idsDev="$idsDev"/>
                                @endif
                            @endforeach
                        </tbody>
                    @endif
                </table>
                <table class="col-12 tableCenter">
                    <thead>
                        <tr>
                            <th id="cantidadProdsVenta" class="col-3">
                                @if (!isset($devVenta))
                                    <div class="col-12 d-flex align-items-center justify-between">
                                        <p class="col-auto py-2 ps-2 m-0">Cantidad de Productos:</p>
                                        <p id="totalProductos" class="col-4 py-2 pe-2 m-0 txtTh">{{ isset($venta) ? (isset($venta->productosVenta) ? sizeof($venta->productosVenta) : "0") : ((isset($carrito)) ? ( isset($carrito["productos"]) ? sizeof($carrito["productos"]): "0") : "0") }}</p>
                                    </div>
                                @else
                                    <div class="col-12 d-flex flex-column align-items-center justify-center position-relative">
                                        <div class="col-12 d-flex align-items-center justify-between">
                                            <p class="col-auto py-2 ps-2 m-0">Cantidad de Productos:</p>
                                            <p id="totalProductos" class="col-4 py-2 pe-2 m-0 txtTh">{{ isset($venta) ? (isset($venta->productosVenta) ? sizeof($venta->productosVenta) : "0") : ((isset($carrito)) ? ( isset($carrito["productos"]) ? sizeof($carrito["productos"]): "0") : (isset($devVenta) ? (isset($devVenta->productosVenta) ? sizeof($devVenta->productosVenta) : "0") : "0")) }}</p>
                                        </div>
                                        <div class="col-4 d-none align-items-center justify-content-end position-absolute top-0 right-0 p-1 devVentDiv">
                                            <div class="col-12 infDevProd">
                                                <p id="totalDevProductos" class="col-auto p-0 py-1 m-0 text-center txtTh">0</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </th>
                            <th class="col-6">
                                <div class="col-12 d-flex flex-column align-items-center justify-content-center py-1">
                                    <div class="col-11 ps-2 d-flex justify-content-center align-items-center">
                                        @if (isset($carrito) && isset($carrito["productos"]))
                                        <label for="tipoVenta-newVenta" class="p-0 m-0 col-auto">Tipo de venta:</label>
                                        <select id="tipoVenta-newVenta" name="tipoVenta-newVenta" class="ms-2 btn form-control @if ($errors->any()) @if (in_array("tipoVenta-newVenta",$errors->keys())) invalid @endif @endif">
                                            <button type="button">
                                                <selectedcontent></selectedcontent>
                                            </button>
                                            <option value="Envio" {{ old("tipoVenta-newVenta") ? (old("tipoVenta-newVenta") == "Envio" ? "selected" : "" ) : "selected"  }}>Con envio</option>
                                            <option value="Local" {{ old("tipoVenta-newVenta") ? (old("tipoVenta-newVenta") == "Local" ? "selected" : "" ) : "" }}>Retio local</option>
                                        </select>
                                        @elseif (isset($venta->productosVenta))
                                        <label for="tipoVenta-newVenta" class="p-0 m-0 col-auto">Tipo de venta:</label>
                                        <select id="tipoVenta-newVenta" name="tipoVenta-newVenta" class="ms-2 btn form-control @if ($errors->any()) @if (in_array("tipoVenta-newVenta",$errors->keys())) invalid @endif @endif">
                                            <button type="button">
                                                <selectedcontent></selectedcontent>
                                            </button>
                                            <option value="Envio" {{ old("tipoVenta-newVenta") ? (old("tipoVenta-newVenta") == "Envio" ? "selected" : "" ) : ( $venta->tipo_venta ? ($venta->tipo_venta == "Envio" ? "selected" : "") : "selected")  }}>Con envio</option>
                                            <option value="Local" {{ old("tipoVenta-newVenta") ? (old("tipoVenta-newVenta") == "Local" ? "selected" : "" ) : ( $venta->tipo_venta ? ($venta->tipo_venta == "Local" ? "selected" : "") : "") }}>Retio local</option>
                                        </select>
                                        @endif
                                    @if (isset($devVenta) && strcmp($devVenta->tipo_pago,"Pendiente")!=0)
                                        <label for="metodoPago-devVenta" class="p-0 m-0 col-auto">Método de pago:</label>
                                        <select id="metodoPago-devVenta" name="metodoPago-devVenta" class="ms-2 btn form-control @if ($errors->any()) @if (in_array("metodoPago-devVenta",$errors->keys())) invalid @endif @endif">
                                            <button type="button">
                                                <selectedcontent></selectedcontent>
                                            </button>
                                            <option value="Tarjeta" {{ old("metodoPago-devVenta") ? (old("metodoPago-devVenta") == "Tarjeta" ? "selected" : "" ) : "selected"  }}>Tarjeta</option>
                                            <option value="Efectivo" {{ old("metodoPago-devVenta") ? (old("metodoPago-devVenta") == "Efectivo" ? "selected" : "" ) : "" }}>Efectivo (¡Recibo opcional!)</option>
                                            <option value="Mixto" {{ old("metodoPago-devVenta") ? (old("metodoPago-devVenta") == "Mixto" ? "selected" : "" ) : "" }}>Mixto</option>
                                        </select>
                                    @endif
                                    </div>
                                    @if (isset($devVenta) && strcmp($devVenta->tipo_pago,"Pendiente")!=0)
                                        <div class="col-12">
                                            <div class="text-center invalid-feedback @error('metodoPago-devVenta') is-invalid @enderror" id="invalid-metodoPago-devVenta">
                                                @error('metodoPago-devVenta')
                                                    {{ str_replace("metodo pago-dev venta","metodo de pago",$message) }}
                                                @enderror
                                            </div>
                                        </div>
                                    @endif
                                    @if (isset($carrito) && isset($carrito["productos"]))
                                    <div class="col-12">
                                        <div class="text-center invalid-feedback @error('tipoVenta-newVenta') is-invalid @enderror" id="invalid-tipoVenta-newVenta">
                                            @error('tipoVenta-newVenta')
                                                {{ str_replace("tipo venta-new venta","tipo de venta",$message) }}
                                            @enderror
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </th>
                            @if (!isset($devVenta))
                                <th id="subtotalVenta" class="col-3">
                                    <div class="col-12 d-flex align-items-center justify-between">
                                        <p class="col-auto py-2 ps-2 m-0">Subtotal:</p>
                                        <p id="totalVenta" class="col-auto py-2 pe-2 m-0 text-end txtTh">$@php
                                            if(isset($carrito)){
                                                echo $carrito["subtotal_Print"];
                                            }elseif(isset($venta)){
                                                echo $fmt4->format($venta->monto_subtotal);
                                            }else{
                                                echo "0,0000";
                                            }
                                        @endphp</p>
                                    </div>
                                </th>
                            @else
                                <th class="col-3">
                                    <div class="col-12 d-flex flex-column align-items-center justify-center position-relative">
                                        <div class="col-12 d-flex align-items-center justify-between">
                                            <p class="col-auto py-2 ps-2 m-0">Total:</p>
                                            <p id="totalVenta" class="col-auto py-2 pe-2 m-0 text-end txtTh">$@php
                                                if(isset($devVenta->monto_total)){
                                                    echo $fmt4->format($devVenta->monto_total);
                                                }else{
                                                    echo "0,0000";
                                                }
                                            @endphp</p>
                                        </div>
                                        <div class="col-9 d-none align-items-center justify-content-end position-absolute top-0 right-0 py-1 px-2 devVentDiv">
                                            <div class="col-12 infDevProd">
                                                <p id="totalDevVenta" class="col-auto py-1 pe-1 m-0 text-end txtTh">$0,0000</p>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                            @endif
                        </tr>
            @if (isset($devVenta))
                    </thead>
                </table>
                <table class="col-12 footerTable">
                        @if(strcmp($devVenta->tipo_pago,"Pendiente")!=0)
                        <thead>
                            <tr>
                                <th class="col-6 border_right">
                                    <p class="py-2 text-center m-0">Recibos</p>
                                </th>
                                <th class="col-6">
                                    <p class="py-2 text-center m-0"></p>
                                </th>
                            </tr>
                        </thead>
                        @endif
            @endif
                    @if (isset($devVenta))
                        <tbody class="footerTable">
                            <tr class="footerTable">
                                @if(strcmp($devVenta->tipo_pago,"Pendiente")!=0)
                                    <td class="border_right">
                                        <div id="div-fotos-newVenta" class="fotos-newVenta col-12 d-flex flex-column justify-content-center align-items-center">
                                            <div class="drop-area mt-2 text-center d-flex align-items-center justify-content-center flex-column form-control @if ($errors->any()) @if (in_array("fotos-devVenta",$errors->keys())) invalid @endif @endif">
                                                <h2 class="form-label">Arrastra y suelta imágenes aquí</h2>
                                                <span>O</span>
                                                <button type="button" class="px-2 py-1 mt-2">Buscar imágenes</button>
                                                <input type="file" accept="image/png, image/jpeg, image/jpg" class="inputFotos" hidden multiple>
                                                <select class="form-control" name="fotos-devVenta[]" id="fotos-devVenta" multiple hidden required>
                                                    @php $oldFotosDevVenta=["fotos"=>old("fotos-devVenta"),"data-foto"=>[]];
                                                        if(!empty($oldFotosDevVenta["fotos"])){
                                                            if(is_array($oldFotosDevVenta["fotos"])){
                                                                foreach($oldFotosDevVenta["fotos"] as $foto){
                                                                    $idOldFoto=bin2hex(random_bytes(length: 3));
                                                                    $oldFotosDevVenta["data-foto"][]=$idOldFoto;
                                                                    echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                                }
                                                            }else{
                                                                $idOldFoto=random_bytes(length: 7);
                                                                $oldFotosDevVenta["data-foto"][]=$idOldFoto;
                                                                echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                            }
                                                        }
                                                    @endphp
                                                </select>
                                                <div class="text-center mt-3 fs-5 invalid-feedback @error('fotos-devVenta') is-invalid @enderror" id="invalid-fotos-devVenta">
                                                    @error('fotos-devVenta')
                                                        @if (str_contains($message,"obligatorio"))
                                                            {{ "Ingrese al menos un recibo de la devolución." }}
                                                        @else
                                                            {{ str_replace("fotos-edit venta","fotos",$message) }}
                                                        @endif
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-11 previewFotos d-flex p-0 ps-2 my-3 @php if(!empty($oldFotosDevVenta["data-foto"]) && sizeof($oldFotosDevVenta["data-foto"])>3)echo "masTres"; @endphp" id="preview-fotos-newVenta">
                                                @for ($i=0;$i<sizeof($oldFotosDevVenta["data-foto"]);$i++)
                                                    <img id="{{ $oldFotosDevVenta["data-foto"][$i] }}" class="img-fluid my-2 me-2" src="{{ $oldFotosDevVenta["fotos"][$i] }}" alt="{{ $oldFotosDevVenta["data-foto"][$i] }}" title="Eliminar" draggable="false">
                                                @endfor
                                            </div>
                                        </div>
                                    </td>
                                @endif
                                <td>
                                    <div class="@if(strcmp($devVenta->tipo_pago,"Pendiente")==0)my-3 @endif col-12 d-flex align-items-center justify-content-evenly">
                                        <input type="reset" id="btnReiniciarDevolucion" form="formConfirmarDevolucion" value="Reiniciar" class="btn btn-dark">
                                        <input type="button" id="btnConfirmacionDevolucion" class="btn" value="Crear Devolucion" data-bs-toggle="modal" data-bs-target="#modalConfirmacionDevolucion">
                                        <input type="button" id="btnCancelarDevolucion" class="btn btn-dark" value="Cancelar" data-bs-toggle="modal" data-bs-target="#modalCancelarDevolucion">
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endif
                </table>
                @if(isset($venta))
                    <table class="col-12 footerTable">
                        <thead>
                            <tr>
                                <th class="col-3">
                                    <div class="col-12 d-flex align-items-center justify-content-center">
                                        <p class="col-auto p-2 m-0 text-start">Método de pago:</p>
                                        <p class="col-3 p-2 m-0 text-start">{{ $venta->tipo_pago }}</p>
                                    </div>
                                </th>
                                <th class="col-3 centerTable">
                                    <div class="col-12 d-flex align-items-center justify-content-center">
                                        <p class="col-auto py-2 ps-2 pe-1 m-0 text-end">Venta</p>
                                        <p class="col-auto py-2 pe-2 m-0 text-start">@switch($venta->tipo_venta)
                                            @case("Envio")
                                                {{ "con ".$venta->tipo_venta }}
                                                @break
                                            @case("Local")
                                                {{ $venta->tipo_venta }}
                                            @break
                                        @endswitch</p>
                                    </div>
                                </th>
                                <th class="col-3 centerTable">
                                    <div class="col-12 mb-2 d-flex flex-column align-items-center">
                                        <div class="col-11 mt-2 d-flex align-items-center justify-content-center">
                                            <label for="estadoEntrega-newVenta" class="col-auto me-2">Pedido entregado</label>
                                            <input type="checkbox" name="estadoEntrega-newVenta" id="estadoEntrega-newVenta" value="Completa" {{ old("estadoEntrega-newVenta") ? (old("estadoEntrega-newVenta") == "Completa" ? "checked" : "" ) : "disabled"  }}>
                                        </div>
                                        <div class="col-12">
                                            <div class="text-center invalid-feedback @error('estadoEntrega-newVenta') is-invalid @enderror" id="invalid-estadoEntrega-newVenta">
                                                @error('estadoEntrega-newVenta')
                                                    {{ str_replace("estado entrega-new venta","estado de la entrega",$message) }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                @if (isset($venta))
                                    <th class="col-3">
                                        <div class="col-12 d-flex align-items-center justify-between">
                                            <p class="col-auto py-2 ps-2 m-0">Total:</p>
                                            <p id="totalVenta" class="col-auto py-2 pe-2 m-0 text-end txtTh">$@php
                                                if(isset($venta->monto_total)){
                                                    echo $fmt4->format($venta->monto_total);
                                                }else{
                                                    echo "0,0000";
                                                }
                                            @endphp</p>
                                        </div>
                                    </th>
                                @endif
                            </tr>
                        </thead>
                    </table>
                @endif
                @if (!isset($devVenta))
                    <table class="col-12 footerTable">
                        <thead>
                            <tr>
                                @if(!isset($venta))
                                    <th class="col-6">
                                        <p class="col-12 p-2 m-0">Ubicación de destino</p>
                                    </th>
                                @else
                                    <th class="{{ strcmp($venta->tipo_pago,"Pendiente")==0 || sizeof($venta->recibos)>0 ? "col-6 border_right" : "col-12" }}">
                                        <p class="col-12 p-2 m-0">Ubicación de destino</p>
                                    </th>
                                @endif
                                @if(isset($venta) && sizeof($venta->recibos)>0)
                                    <th class="col-6"><p class="p-1 m-0 text-center">Recibos</p></th>
                                @elseif(isset($venta) && strcmp($venta->tipo_pago,"Pendiente")==0)
                                    <th class="col-2 border_right"><p class="p-1 m-0 text-center">Recibos</p></th>
                                    <th class="col-4">
                                        <div class="col-12 my-2">
                                            <div class="col-12 d-flex flex-column justify-content-center align-items-center">
                                                <div class="col-11 ps-2 d-flex justify-content-center align-items-center">
                                                    <p class="p-0 m-0 col-auto">Confirmar Pago:</p>
                                                    <input type="checkbox" name="confirmarPago-newVenta" id="confirmarPago-newVenta" value="Confirmar" class="mx-2" {{ old("confirmarPago-newVenta") ? (old("confirmarPago-newVenta") == "Confirmar" ? "checked" : "" ) : "" }}>
                                                    <select id="metodoPago-newVenta" name="metodoPago-newVenta" class="ms-2 btn form-control @if ($errors->any()) @if (in_array("metodoPago-newVenta",$errors->keys())) invalid @endif @endif" disabled>
                                                        <button type="button">
                                                            <selectedcontent></selectedcontent>
                                                        </button>
                                                        <option value="Tarjeta" {{ old("metodoPago-newVenta") ? (old("metodoPago-newVenta") == "Tarjeta" ? "selected" : "" ) : "selected"  }}>Tarjeta</option>
                                                        <option value="Efectivo" {{ old("metodoPago-newVenta") ? (old("metodoPago-newVenta") == "Efectivo" ? "selected" : "" ) : "" }}>Efectivo (¡Recibo opcional!)</option>
                                                        <option value="Mixto" {{ old("metodoPago-newVenta") ? (old("metodoPago-newVenta") == "Mixto" ? "selected" : "" ) : "" }}>Mixto</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <div class="text-center invalid-feedback @error('metodoPago-newVenta') is-invalid @enderror" id="invalid-metodoPago-newVenta">
                                                        @error('metodoPago-newVenta')
                                                            {{ str_replace("metodo pago-new venta","metodo de pago",$message) }}
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </th>
                                @elseif(!isset($venta))
                                    <th class="col-3 centerTable"><p class="p-1 m-0 text-center">Recibos</p></th>
                                @endif
                                @if(!isset($venta))
                                    <th class="col-3">
                                        <div class="col-12 d-flex align-items-center justify-between">
                                            <p class="col-auto py-2 ps-2 m-0">Total:</p>
                                            <p id="totalVenta" class="col-auto py-2 pe-2 m-0 text-end txtTh">$@php
                                                if(isset($carrito)){
                                                    echo $carrito["total_Print"];
                                                }else{
                                                    echo "0,0000";
                                                }
                                            @endphp</p>
                                        </div>
                                    </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td @if (isset($venta) && strcmp($venta->tipo_pago,"Efectivo")==0) colspan="2" @endif class="centerTable ubicacionVenta">
                                    @if ((isset($carrito) && isset($carrito["productos"]) && sizeof($carrito["productos"])>0) || (isset($venta) && sizeof($venta->productosVenta)>0))
                                        <div class="col-12 d-flex flex-column align-items-top justify-content-start">
                                            <div class="col-12 divMapa_Boton" id="divMapa_Boton">
                                                    <div id="map">
                                                    </div>
                                                <div class="col-12 position-relative">
                                                    <div class="invalid-feedback position-absolute" id="invalid-map">
                                                        <p class="p-0 m-0 text-center fs-6 col-12"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                @if (isset($carrito))
                                    <td @if (isset($carrito["productos"])) colspan="2" @endif>@if((isset($carrito["productos"])  && sizeof($carrito["productos"])>0))
                                        <div class="col-12 position-relative">
                                            <div class="col-12 position-absolute d-none disableFotos"></div>
                                            <div id="div-fotos-newVenta" class="fotos-newVenta col-12 d-flex flex-column justify-content-center align-items-center">
                                                <div class="drop-area mt-2 text-center d-flex align-items-center justify-content-center flex-column form-control @if ($errors->any()) @if (in_array("fotos-newVenta",$errors->keys())) invalid @endif @endif">
                                                    <h2 class="form-label">Arrastra y suelta imágenes aquí</h2>
                                                    <span>O</span>
                                                    <button type="button" class="px-2 py-1 mt-2">Buscar imágenes</button>
                                                    <input type="file" accept="image/png, image/jpeg, image/jpg" class="inputFotos" hidden multiple>
                                                    <select class="form-control" name="fotos-newVenta[]" id="fotos-newVenta" multiple hidden required>
                                                        @php $oldFotosEdit=["fotos"=>old("fotos-newVenta"),"data-foto"=>[]];
                                                            if(!empty($oldFotosEdit["fotos"])){
                                                                if(is_array($oldFotosEdit["fotos"])){
                                                                    foreach($oldFotosEdit["fotos"] as $foto){
                                                                        $idOldFoto=bin2hex(random_bytes(length: 3));
                                                                        $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                                        echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                                    }
                                                                }else{
                                                                    $idOldFoto=random_bytes(length: 7);
                                                                    $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                                    echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                                }
                                                            }elseif(isset($venta)){
                                                                $oldFotosEdit["fotos"]=$venta->recibos;
                                                                foreach ($oldFotosEdit["fotos"] as $foto) {
                                                                    $idOldFoto=bin2hex(random_bytes(length: 3));
                                                                    if(isset($foto->url_img)){
                                                                        $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                                        echo '<option data-foto="'.$idOldFoto.'" value="'.$foto->url_img.'" selected></option>';
                                                                    }elseif(isset($foto->url_img_online)){
                                                                        $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                                        echo '<option data-foto="'.$idOldFoto.'" value="'.$foto->url_img_online.'" selected></option>';
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                    </select>
                                                    <div class="text-center mt-3 fs-5 invalid-feedback @error('fotos-newVenta') is-invalid @enderror" id="invalid-fotos-newVenta">
                                                        @error('fotos-newVenta')
                                                            @if (str_contains($message,"obligatorio"))
                                                                {{ "Ingrese al menos un recibo de venta." }}
                                                            @else
                                                                {{ str_replace("fotos-new venta","fotos",$message) }}
                                                            @endif
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-11 previewFotos d-flex p-0 ps-2 my-3 @php if(!empty($oldFotosEdit["data-foto"]) && sizeof($oldFotosEdit["data-foto"])>3)echo "masTres"; @endphp" id="preview-fotos-newVenta">
                                                    @for ($i=0;$i<sizeof($oldFotosEdit["data-foto"]);$i++)
                                                        <img id="{{ $oldFotosEdit["data-foto"][$i] }}" class="img-fluid my-2 me-2" src="{{ $oldFotosEdit["fotos"][$i] }}" alt="{{ $oldFotosEdit["data-foto"][$i] }}" title="Eliminar" draggable="false">
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </td>
                                @elseif (isset($venta))
                                    @if(strcmp($venta->tipo_pago,"Pendiente")==0)
                                        <td colspan="2">
                                            <div class="col-12 position-relative">
                                                <div class="col-12 position-absolute disableFotos"></div>
                                                <div id="div-fotos-newVenta" class="fotos-newVenta col-12 d-flex flex-column justify-content-center align-items-center">
                                                    <div class="drop-area mt-2 text-center d-flex align-items-center justify-content-center flex-column form-control @if ($errors->any()) @if (in_array("fotos-newVenta",$errors->keys())) invalid @endif @endif">
                                                        <h2 class="form-label">Arrastra y suelta imágenes aquí</h2>
                                                        <span>O</span>
                                                        <button type="button" class="px-2 py-1 mt-2">Buscar imágenes</button>
                                                        <input type="file" accept="image/png, image/jpeg, image/jpg" class="inputFotos" hidden multiple>
                                                        <select class="form-control" name="fotos-newVenta[]" id="fotos-newVenta" multiple hidden required>
                                                            @php $oldFotosEdit=["fotos"=>old("fotos-newVenta"),"data-foto"=>[]];
                                                                if(!empty($oldFotosEdit["fotos"])){
                                                                    if(is_array($oldFotosEdit["fotos"])){
                                                                        foreach($oldFotosEdit["fotos"] as $foto){
                                                                            $idOldFoto=bin2hex(random_bytes(length: 3));
                                                                            $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                                            echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                                        }
                                                                    }else{
                                                                        $idOldFoto=random_bytes(length: 7);
                                                                        $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                                        echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                                    }
                                                                }elseif(isset($venta)){
                                                                    $oldFotosEdit["fotos"]=$venta->recibos;
                                                                    foreach ($oldFotosEdit["fotos"] as $foto) {
                                                                        $idOldFoto=bin2hex(random_bytes(length: 3));
                                                                        if(isset($foto->url_img)){
                                                                            $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                                            echo '<option data-foto="'.$idOldFoto.'" value="'.$foto->url_img.'" selected></option>';
                                                                        }elseif(isset($foto->url_img_online)){
                                                                            $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                                            echo '<option data-foto="'.$idOldFoto.'" value="'.$foto->url_img_online.'" selected></option>';
                                                                        }
                                                                    }
                                                                }
                                                            @endphp
                                                        </select>
                                                        <div class="text-center mt-3 fs-5 invalid-feedback @error('fotos-newVenta') is-invalid @enderror" id="invalid-fotos-newVenta">
                                                            @error('fotos-newVenta')
                                                                @if (str_contains($message,"obligatorio"))
                                                                    {{ "Ingrese al menos un recibo de venta." }}
                                                                @else
                                                                    {{ str_replace("fotos-new venta","fotos",$message) }}
                                                                @endif
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-11 previewFotos d-flex p-0 ps-2 my-3 @php if(!empty($oldFotosEdit["data-foto"]) && sizeof($oldFotosEdit["data-foto"])>3)echo "masTres"; @endphp" id="preview-fotos-newVenta">
                                                        @for ($i=0;$i<sizeof($oldFotosEdit["data-foto"]);$i++)
                                                            <img id="{{ $oldFotosEdit["data-foto"][$i] }}" class="img-fluid my-2 me-2" src="{{ $oldFotosEdit["fotos"][$i] }}" alt="{{ $oldFotosEdit["data-foto"][$i] }}" title="Eliminar" draggable="false">
                                                        @endfor
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    @elseif(sizeof($venta->recibos)>0)
                                        <td colspan="2">
                                            <div class="col-12 recibosTd d-flex flex-column align-items-center justify-content-center position-relative">
                                                <div id="recibosVenta" class="carousel slide col-8 d-flex align-items-center justify-content-center" data-bs-ride="carousel">
                                                    <div class="carousel-inner">
                                                        @php $i=0; @endphp
                                                        @foreach ($venta->recibos as $recibo)
                                                            <div class="carousel-item {{ $i==0 ? "active" : "" }}">
                                                                <img src="@php
                                                                    $foto=$recibo->url_img;
                                                                    if($foto!=null && Storage::disk('public')->exists(str_replace("/storage","",$foto))){
                                                                        echo route("inicio.index").$foto;
                                                                    }else{
                                                                        echo $recibo->url_img_online;
                                                                    }
                                                                @endphp" class="img-fluid" alt="reciboVenta" draggable="false">
                                                            </div>
                                                            @php $i++; @endphp
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <button class="carousel-control-prev" type="button" data-bs-target="#recibosVenta" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Anterior</span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#recibosVenta" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Siguiente</span>
                                                </button>
                                            </div>
                                        </td>
                                    @endif
                                @endif
                            </tr>
                            @if(isset($carrito) && isset($carrito["productos"]) || isset($venta))
                            <tr>
                                <td id="tdUbicacion" rowspan="3">
                                    <div class="col-12 mt-2 mb-3 datosEnvio">
                                        <div class="col-12 p-2 d-flex flex-column align-items-center justify-content-center">
                                            <div class="col-11 buscador-container">
                                                <label for="direccion-newVenta" class="ps-2">Dirección de envio</label>
                                                <input type="text" class="form-control" id="direccion-newVenta" name="direccion-newVenta" placeholder="Ej: Calle, Barrio, Localidad... [Enter] para Buscar." value="{{ old("direccion-newVenta") ? old("direccion-newVenta") : ( isset($venta) ? (isset($venta->ubicacion) ? $venta->ubicacion->direccion : "") : "") }}">
                                                <div id="resultados" class="resultados list-group "></div>
                                                <div class="invalid-feedback @error('direccion-newVenta') is-invalid @enderror" id="invalid-direccion-newVenta">
                                                    @error('direccion-newVenta')
                                                        {{ str_replace("direccion-new venta","dirección",$message) }}
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="pt-2 col-md-11 d-flex align-items-start justify-content-start">
                                                <div class="col-4">
                                                    <input type="text" class="form-control" id="manzanaPiso-newVenta" name="manzanaPiso-newVenta" placeholder="Manzana/Piso" value="{{ old("manzanaPiso-newVenta") ? old("manzanaPiso-newVenta") : ( isset($venta) ? (isset($venta->ubicacion) ? $venta->ubicacion->manzana_piso : "") : "") }}">
                                                    <div class="invalid-feedback @error('manzanaPiso-newVenta') is-invalid @enderror" id="invalid-manzanaPiso-newVenta">
                                                        @error('manzanaPiso-newVenta')
                                                            {{ str_replace("manzana piso-new venta","manzana/piso",$message) }}
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-4 ps-2">
                                                    <input type="text" class="form-control" id="casaDepto-newVenta" name="casaDepto-newVenta" placeholder="Casa/Depto" value="{{ old("casaDepto-newVenta") ? old("casaDepto-newVenta") : ( isset($venta) ? (isset($venta->ubicacion) ? $venta->ubicacion->casa_depto : "") : "") }}">
                                                    <div class="invalid-feedback @error('casaDepto-newVenta') is-invalid @enderror" id="invalid-casaDepto-newVenta">
                                                        @error('casaDepto-newVenta')
                                                            {{ str_replace("casa depto-new venta","casa/depto",$message) }}
                                                        @enderror
                                                    </div>
                                                </div>
                                                <input type="text" name="coordsDestino-newVenta" class="form-control" id="coordsDestino-newVenta" value="{{ old("coordsDestino-newVenta") ? old("coordsDestino-newVenta") : ( isset($venta) ? (isset($venta->ubicacion) ? (($venta->ubicacion->lat!=null && $venta->ubicacion->lng!=null) ? $venta->ubicacion->lat."[;]".$venta->ubicacion->lng : "" ) : "") : "") }}" hidden>
                                            </div>
                                            <div class="pt-2 col-md-11 d-flex align-items-center justify-content-start">
                                                <div class="col-12">
                                                    <textarea class="form-control" id="detalles-newVenta" name="detalles-newVenta" placeholder="Detalles extras de la ubicación. (Opcional)">{{ old("detalles-newVenta") ? old("detalles-newVenta") : ( isset($venta) ? (isset($venta->ubicacion) ? ( $venta->ubicacion->descripcion ? $venta->ubicacion->descripcion : "" ) : "") : "") }}</textarea>
                                                    <div class="invalid-feedback @error('detalles-newVenta') is-invalid @enderror" id="invalid-detalles-newVenta">
                                                        @error('detalles-newVenta')
                                                            {{ str_replace("detalles-new venta","detalles",$message) }}
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @if (isset($carrito) && isset($carrito["productos"]) || isset($venta))
                            <tr>
                                <td colspan="2">
                                    <div class="col-12 my-2">
                                        <div class="col-12 p-2 datosReceptor d-flex flex-column align-items-center justify-content-center">
                                            <div class="col-md-11 d-flex flex-column align-items-start justify-content-center">
                                                <div class="col-md-12 mb-2">
                                                    <label for="receptor-newVenta" class="ps-2">Receptor del pedido</label>
                                                    <input type="text" class="form-control" id="receptor-newVenta" name="receptor-newVenta" placeholder="Nombre" value="{{ old("receptor-newVenta") ? old("receptor-newVenta") : ( isset($venta) ? ($venta->nombre_receptor ? $venta->nombre_receptor : "") : "") }}">
                                                    <div class="invalid-feedback @error('receptor-newVenta') is-invalid @enderror" id="invalid-receptor-newVenta">
                                                        @error('receptor-newVenta')
                                                            {{ str_replace("receptor-new venta","nombre del receptos",$message) }}
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <input type="tel" class="form-control" id="contacto-newVenta" name="contacto-newVenta" placeholder="Telefono de Contacto" value="{{ old("contacto-newVenta") ? old("contacto-newVenta") : ( isset($venta) ? ($venta->telefono_receptor ? $venta->telefono_receptor : "") : "") }}">
                                                    <div class="invalid-feedback @error('contacto-newVenta') is-invalid @enderror" id="invalid-contacto-newVenta">
                                                        @error('contacto-newVenta')
                                                            {{ str_replace("contacto-new venta","numero de contacto",$message) }}
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @if (isset($carrito) && isset($carrito["productos"]))
                            <tr>
                                <td colspan="2">
                                    <div class="col-12 my-2">
                                        <div class="col-12 pt-1 pb-2 d-flex flex-column justify-content-center align-items-center">
                                            <div class="col-11 ps-2 d-flex justify-content-start align-items-center">
                                                <label for="metodoPago-newVenta" class="p-0 m-0 col-auto">Método de pago:</label>
                                                <select id="metodoPago-newVenta" name="metodoPago-newVenta" class="ms-2 btn form-control @if ($errors->any()) @if (in_array("metodoPago-newVenta",$errors->keys())) invalid @endif @endif">
                                                    <button type="button">
                                                        <selectedcontent></selectedcontent>
                                                    </button>
                                                    <option value="Tarjeta" {{ old("metodoPago-newVenta") ? (old("metodoPago-newVenta") == "Tarjeta" ? "selected" : "" ) : "selected"  }}>Tarjeta</option>
                                                    <option value="Efectivo" {{ old("metodoPago-newVenta") ? (old("metodoPago-newVenta") == "Efectivo" ? "selected" : "" ) : "" }}>Efectivo (¡Recibo opcional!)</option>
                                                    <option value="Mixto" {{ old("metodoPago-newVenta") ? (old("metodoPago-newVenta") == "Mixto" ? "selected" : "" ) : "" }}>Mixto</option>
                                                    <option value="Pendiente" {{ old("metodoPago-newVenta") ? (old("metodoPago-newVenta") == "Pendiente" ? "selected" : "" ) : ""  }}>Pendiente</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <div class="text-center invalid-feedback @error('metodoPago-newVenta') is-invalid @enderror" id="invalid-metodoPago-newVenta">
                                                    @error('metodoPago-newVenta')
                                                        {{ str_replace("metodo pago-new venta","metodo de pago",$message) }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-2 d-flex flex-column align-items-center">
                                        <div class="col-11 ps-2 d-flex align-items-center justify-content-start">
                                            <label for="estadoEntrega-newVenta" class="col-auto me-2">Pedido entregado</label>
                                            <input type="checkbox" name="estadoEntrega-newVenta" id="estadoEntrega-newVenta" value="Completa" {{ old("estadoEntrega-newVenta") ? (old("estadoEntrega-newVenta") == "Completa" ? "checked" : "" ) : "disabled"  }}>
                                        </div>
                                        <div class="col-12">
                                            <div class="text-center invalid-feedback @error('estadoEntrega-newVenta') is-invalid @enderror" id="invalid-estadoEntrega-newVenta">
                                                @error('estadoEntrega-newVenta')
                                                    {{ str_replace("estado entrega-new venta","estado de la entrega",$message) }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @if (isset($carrito) || isset($venta))
                            <tr>
                                <td @if(isset($venta)) colspan="2" @endif>
                                    @if (isset($carrito["productos"]) || isset($venta))
                                    <div class="col-12 py-2 d-flex flex-column align-items-center justify-content-center fechaEntrega">
                                        <h6 class="my-2">Fecha/Horario de Entrega</h6>
                                        <div class="col-8 d-flex justify-content-between align-items-center">
                                            <label for="fechaEntregaMin-newVenta" class="col-auto text-end">Desde:</label>
                                            <div class="col-10">
                                                <input class="form-control @if ($errors->any()) @if (in_array("fechaEntregaMin-newVenta",$errors->keys())) invalid @endif @endif" type="datetime-local" id="fechaEntregaMin-newVenta" name="fechaEntregaMin-newVenta" min="{{ date("Y-m-d H:i",now()->getTimestamp()) }}" max="{{ now()->addDays(7)->setHour(20)->setMinute(30)->format("Y-m-d H:i") }}" value="{{ old("fechaEntregaMin-newVenta") ? old("fechaEntregaMin-newVenta") : ( isset($venta) ? ($venta->ubicacion ? $venta->ubicacion->fecha_entrega_min : now()->format("Y-m-d H:i")) : now()->format("Y-m-d H:i")) }}">
                                            </div>
                                        </div>
                                        <div class="col-8 my-2 d-flex justify-content-between align-items-center">
                                            <label for="fechaEntregaMax-newVenta" class="col-auto text-end">Hasta:</label>
                                            <div class="col-10">
                                                <input class="form-control @if ($errors->any()) @if (in_array("fechaEntregaMax-newVenta",$errors->keys())) invalid @endif @endif" type="datetime-local" id="fechaEntregaMax-newVenta" name="fechaEntregaMax-newVenta" min="{{ date("Y-m-d H:i",now()->getTimestamp()) }}" max="{{ now()->addDays(7)->setHour(20)->setMinute(30)->format("Y-m-d H:i") }}" value="{{ old("fechaEntregaMax-newVenta") ? old("fechaEntregaMax-newVenta") : ( isset($venta) ? ($venta->ubicacion ? $venta->ubicacion->fecha_entrega_max : now()->addDays(7)->setHour(20)->setMinute(30)->format("Y-m-d H:i")) : now()->addDays(7)->setHour(20)->setMinute(30)->format("Y-m-d H:i")) }}">
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </td>
                                @if (!isset($venta))
                                    <td colspan="2">
                                        <div class="col-12 p-2 d-flex align-items-center justify-content-evenly">
                                            <div class="col-12 d-flex align-items-center justify-content-evenly">
                                                <input type="button" id="btnCancelarVenta" class="btn btn-dark" value="Cancelar" data-bs-toggle="modal" data-bs-target="#modalCancelarVenta">
                                                <input type="reset" id="btnReiniciarVenta" form="formConfirmarVenta" value="Reiniciar" class="btn btn-dark">
                                                <input type="submit" id="btnConfirmarVenta" form="formConfirmarVenta" value="Registrar Venta" class="btn btn-dark">
                                            </div>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                            @endif
                            @if(isset($venta))
                                <td colspan="3">
                                    <div class="col-12 p-2 d-flex align-items-center justify-content-evenly">
                                        <div class="col-12 d-flex align-items-center justify-content-evenly">
                                            <input type="button" id="btnCancelarModificarVenta" class="btn btn-dark" value="Cancelar" data-bs-toggle="modal" data-bs-target="#modalCancelarModificacionVenta">
                                            <input type="reset" id="btnReiniciarModificarVenta" form="formConfirmarVenta" value="Reiniciar" class="btn btn-dark">
                                            <input type="submit" id="btnModificarVenta" form="formConfirmarVenta" value="Modificar Venta" class="btn btn-dark">
                                        </div>
                                    </div>
                                </td>
                            @endif
                        </tbody>
                    </table>
                @endif
            </form>
        </div>
    </div>
    @if (isset($carrito) && isset($carrito["productos"]))
        <div class="modal fade" id="modalCancelarVenta" aria-hidden="true" aria-labelledby="modalCancelarVentaLabel" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalCancelarVentaLabel">Descartar venta</h1>
                        <div class="btn-close btn-closeDeleteVenta p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                            <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                        </div>
                    </div>
                    <div class="modal-body">
                        <form id="formCancelarVenta" action="{{ route("venta.limpiarcarrito") }}" method="post">
                            @csrf
                            <h2 class="my-5 col-12 text-center">Se vaciará el carrito de ventas.</h2>
                            <h2 class="my-5 col-12 text-center">¿Desea continuar?</h2>
                        </form>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-between">
                        <input type="submit" form="formCancelarVenta" class="btn ms-3" name="limpiarCarrito" value="Proceder">
                        <button type="button" class="btn me-3" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if (isset($venta))
        <div class="modal fade" id="modalCancelarModificacionVenta" aria-hidden="true" aria-labelledby="modalCancelarModificacionVentaLabel" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalCancelarModificacionVentaLabel">Descartar modificación</h1>
                        <div class="btn-close btn-closeDeleteVenta p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                            <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                        </div>
                    </div>
                    <div class="modal-body">
                        <form id="formCancelarModificacionVenta" action="{{ route("venta.descartarmodventa") }}" method="post">
                            @csrf
                            <h2 class="my-5 col-12 text-center">¿Cancelar modificación?</h2>
                        </form>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-between">
                        <input type="submit" form="formCancelarModificacionVenta" class="btn ms-3" name="descartarModVenta" value="Descartar">
                        <button type="button" class="btn me-3" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if (isset($devVenta))
        <div class="modal fade" id="modalCancelarDevolucion" aria-hidden="true" aria-labelledby="modalCancelarDevolucionLabel" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalCancelarDevolucionLabel">Descartar devolución</h1>
                        <div class="btn-close btn-closeDeleteVenta p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                            <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                        </div>
                    </div>
                    <div class="modal-body">
                        <form id="formCancelarDevolucion" action="{{ route("venta.descartardevventa") }}" method="post">
                            @csrf
                            <h2 class="my-5 col-12 text-center">¿Descartar la devolución?</h2>
                        </form>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-between">
                        <input type="submit" form="formCancelarDevolucion" class="btn ms-3" name="descartarDevVenta" value="Descartar">
                        <button type="button" class="btn me-3" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalConfirmacionDevolucion" aria-hidden="true" aria-labelledby="modalConfirmacionDevolucionLabel" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalConfirmacionDevolucionLabel">Crear Devolución</h1>
                        <div class="btn-close btn-closeDeleteVenta p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                            <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                        </div>
                    </div>
                    <div class="modal-body">
                        <h2 class="my-5 col-12 text-center">¿Proceder con la devolución?</h2>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-between">
                        <input type="submit" form="formConfirmarDevolucion" class="btn ms-3" value="Crear">
                        <button type="button" class="btn me-3" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('footer')
@vite([
    "resources/js/app/dropImgs.js",
    "resources/js/app/mapa.js",
    "resources/js/app/venta.js",
    "resources/js/app/procesarVenta.js",
])
@if (isset($devVenta))
    @vite("resources/js/app/procesarDevVenta.js")
@endif
<script>
    (()=>{
        var invalidProdsDev={{ in_array("producto-devVenta",$errors->keys()) ? json_encode(1) : json_encode(0) }};
        var invalidForm=@php
            if($errors->any()){
                if(in_array("fotos-newVenta",$errors->keys())){
                    echo json_encode(2);
                }else{
                    echo json_encode(1);
                }
            }else{
                echo json_encode(0);
            }
        @endphp;
        if(invalidForm){
            if(invalidProdsDev){
                const mensajeDiv=document.querySelector(".mensajeDiv");
                if(mensajeDiv!=null){
                    mensajeDiv.firstElementChild.textContent="Elija al menos un producto para la devolución.";
                    mensajeDiv.classList.add("invalid");
                    if(mensajeDiv.classList.contains("valid") || mensajeDiv.classList.contains("invalid")){
                        setTimeout(()=>{
                            mensajeDiv.classList.remove("invalid");
                            mensajeDiv.firstElementChild.textContent="";
                        },6000);
                    }
                }
            }
            if(invalidForm==2){
                const previewFotos=document.querySelector("#formConfirmarVenta .previewFotos");
                if(previewFotos.childElementCount>0){
                    for (let foto of previewFotos.children) {
                        foto.addEventListener("click", () => deleteFoto(foto));
                    }
                }
            }else{
                const metodoPagoDev=document.querySelector("#metodoPago-devVenta");
                if(metodoPagoDev!=null){
                    if(metodoPagoDev.value.trim()!="Efectivo"){
                        const previewFotos=document.querySelector("#formConfirmarDevolucion .previewFotos");
                        if(previewFotos!=null && previewFotos.childElementCount>0){
                            for (let foto of previewFotos.children) {
                                foto.addEventListener("click", () => deleteFoto(foto));
                            }
                        }
                    }
                }
            }
            const btnCV=document.querySelector("#btnConfirmarVenta");
            const btnCMV=document.querySelector("#btnModificarVenta");
            const btnCDV=document.querySelector("#btnConfirmacionDevolucion");
            if(btnCV!=null)btnCV.focus();
            else if(btnCMV!=null)btnCMV.focus();
            else if(btnCDV!=null)btnCDV.focus();
            const tipoV=document.querySelector("#tipoVenta-newVenta");
            if(tipoV!=null && tipoV.value.trim()=="Local"){
                const textarea=document.querySelector("table textarea");
                const inputs=document.querySelectorAll("table input.form-control");
                const checkbox=document.querySelector("table input[type='checkbox']");
                if(textarea!=null && inputs.length>0 && checkbox!=null){
                    textarea.disabled=true;
                    checkbox.disabled=false;
                    for(const input of inputs){
                        if(!input.id.trim().includes("receptor") && !input.id.trim().includes("contacto")){
                            input.disabled=true;
                        }else if(checkbox.checked){
                            input.disabled=true;
                        }
                    }
                }
            }

        }
    
        function deleteFoto(foto){
            const optFoto = foto.parentElement.previousElementSibling.querySelector("option[data-foto='"+foto.id+"']");
            const select = foto.parentElement;
            optFoto.remove();
            foto.remove();
            if(select.childElementCount<4){
                select.classList.remove("masTres");
            }
        }
    })();
</script>
@endsection