@extends('layouts.index')

@section('head')
    @vite([
        "resources/css/myStyles/inicio.css",
        "resources/css/myStyles/mapa.css",
        "resources/css/myStyles/procesarVenta.css",
        "resources/css/myStyles/venta.css",
    ])
@endsection

@php
    $fmt = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 4);
@endphp

@section("content")
    <x-portada-component/>
    <x-buscador-component type="activo"/>
    <x-mensaje-div-component/>

<div class="m-2 py-5 d-flex primerDivProd flex-column align-items-center justify-content-center">
        <div class="col-11 productos p-2">
            <table class="col-12">
                <thead>
                    <tr>
                        @if (sizeof($venta->devoluciones)>0)
                            <th class="col-5 firstTh position-relative">
                                <p class="p-2 m-0">Producto</p>
                                <button type="submit" form="formVerDevoluciones" class="position-absolute showDevoluciones" title="Ver devoluciones">
                                    <img class="img-fluid" src="{{ asset("build/assets/icons/receipt.svg") }}" alt="devoluciones">
                                </div>
                                <form action="{{ route("venta.devoluciones") }}" method="post" id="formVerDevoluciones" class="d-none">
                                    @csrf
                                    <input type="hidden" name="idVenta" value="{{ $venta->id }}">
                                </form>
                            </th>
                        @else
                            <th class="col-5 firstTh"><p class="p-2 m-0">Producto</p></th>
                        @endif
                        <th class="col-2"><p class="p-2 m-0">Precio de venta</p></th>
                        <th class="col-2"><p class="p-2 m-0">Cantidad</p></th>
                        <th class="col-1"><p class="p-2 m-0">Medida</p></th>
                        @if (sizeof($venta->devoluciones)>0)
                            <th class="col-2 position-relative">
                                <p class="p-2 m-0">Total</p>
                                <div class="position-absolute switchVenta" title="Mostrar original">
                                    <img class="img-fluid" src="{{ asset("build/assets/icons/search.svg") }}" alt="ventaOriginal">
                                </div>
                            </th>
                        @else
                            <th class="col-2 position-relative"><p class="p-2 m-0">Total</p></th>
                        @endif
                    </tr>
                </thead>
                @if (sizeof($venta->devoluciones)>0)
                    <tbody class="originalVenta d-none">
                        @foreach ($venta->productosVenta as $producto)
                            <x-producto-venta-tabla-component :producto="$producto" tipo="vendido"/>
                        @endforeach
                    </tbody>
                    <tbody class="actualVenta">
                        @php
                            $idsDev=["devIndex"=>[],"prodDevId"=>[],"prodDevIndex"=>[]];
                            for($i=0;$i<sizeof($venta->devoluciones);$i++){
                                $idsProds=$venta->devoluciones[$i]->productos->pluck("producto")->toArray();
                                for($j=0; $j<sizeof($idsProds);$j++){
                                    if(!in_array($idsProds[$j],$idsDev["prodDevId"])){
                                        $idsDev["prodDevId"][]=$idsProds[$j];
                                        $idsDev["devIndex"][]=$i;
                                        $idsDev["prodDevIndex"][]=$j;
                                    }
                                }
                            }
                        @endphp
                        @foreach ($venta->productosVenta as $producto)
                            @if(!in_array($producto->producto,$idsDev["prodDevId"]))
                                <x-producto-venta-tabla-component :producto="$producto" tipo="vendido"/>
                            @else
                                @php
                                    $devoluciones=$venta->devoluciones;

                                @endphp
                                <x-producto-devolucion-tabla-component :producto="clone $producto" :devoluciones="clone $devoluciones" :idsDev="$idsDev"/>
                            @endif
                        @endforeach
                    </tbody>
                @else
                    <tbody>
                        @foreach ($venta->productosVenta as $producto)
                            <x-producto-venta-tabla-component :producto="$producto" tipo="vendido"/>
                        @endforeach
                    </tbody>
                @endif
            </table>
            <table class="col-12 footerTable">
                <thead>
                    <tr>
                        <th class="col-4">
                            <div class="col-12 d-flex align-items-center justify-between">
                                <p class="col-auto py-2 ps-2 m-0">Cantidad de Productos:</p>
                                 @if(isset($trueTotalVenta))
                                    <p id="totalProductos" class="col-4 py-2 @if(isset($trueTotalVenta))devProd @endif pe-2 m-0 txtTh trueCantV">{{ $trueCantProds }}</p>
                                    <p class="col-4 py-2 pe-2 m-0 txtTh oldCantV d-none">{{ $venta->productosVenta ? sizeof($venta->productosVenta) : "0" }}</p>
                                 @else
                                    <p id="totalProductos" class="col-4 py-2 pe-2 m-0 txtTh">{{ $venta->productosVenta ? sizeof($venta->productosVenta) : "0" }}</p>
                                 @endif
                            </div>
                        </th>
                        <th class="col-5 centerTable">
                            <div class="col-12 d-flex align-items-center justify-content-evenly">
                                <p class="col-auto py-2 ps-2 pe-1 m-0 text-end">Fecha de Venta:</p>
                                <p class="col-auto py-2 pe-2 m-0 text-start txtTh">{{ date_create($venta->fecha_venta)->format("d/m/Y H:i") }}</p>
                            </div>
                        </th>
                        <th class="col-3">
                            <div class="col-12 d-flex align-items-center justify-between">
                                <p class="col-auto py-2 ps-2 m-0">Subtotal:</p>
                                @if(isset($trueTotalVenta))
                                    <p id="totalVenta" class="col-8 py-2 pe-2 @if(isset($trueTotalVenta))devProd @endif m-0 text-end txtTh trueSubTotalV">${{ $fmt->format($trueTotalVenta->__toString()) }}</p>
                                    <p class="col-8 py-2 pe-2 m-0 text-end txtTh oldSubTotalV d-none">${{ $fmt->format($venta->monto_subtotal) }}</p>
                                @else
                                    <p id="totalVenta" class="col-8 py-2 pe-2 m-0 text-end txtTh">${{ isset($trueTotalVenta) ? $fmt->format($trueTotalVenta->__toString()) : $fmt->format($venta->monto_subtotal) }}</p>
                                @endif
                            </div>
                        </th>
                    </tr>
                </thead>
            </table>
            <table class="col-12 footerTable">
                <thead>
                    <tr>
                        <th class="col-3">
                            <div class="col-12 d-flex align-items-center justify-content-center">
                                <p class="col-auto p-2 m-0 text-start">Método de pago:</p>
                                <p class="col-3 p-2 m-0 text-start">{{ $venta->tipo_pago }}</p>
                            </div>
                        </th>
                        <th class="col-2 centerTable">
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
                        <th class="col-4 centerTable">
                            <div class="col-12 d-flex align-items-center justify-content-center">
                                <p class="col-auto p-2 m-0 text-start">Entrega del pedido:</p>
                                <p class="col-3 p-2 m-0 text-start">{{ $venta->estado_entrega }}</p>
                            </div>
                        </th>
                        <th class="col-3">
                            <div class="col-12 d-flex align-items-center justify-between">
                                <p class="col-auto py-2 ps-2 m-0">Total:</p>
                                @if(isset($trueTotalVenta))
                                    <p id="totalVenta" class="col-8 py-2 pe-2 @if(isset($trueTotalVenta))devProd @endif m-0 text-end txtTh trueTotalV">${{ $fmt->format($trueTotalVenta->__toString()) }}</p>
                                    <p class="col-8 py-2 pe-2 m-0 text-end txtTh oldTotalV d-none">${{ $fmt->format($venta->monto_total) }}</p>
                                @else
                                    <p id="totalVenta" class="col-8 py-2 pe-2 m-0 text-end txtTh">${{ $fmt->format($venta->monto_total) }}</p>
                                @endif
                            </div>
                        </th>
                    </tr>
                </thead>
            </table>
            @if (isset($venta->ubicacion) || sizeof($venta->recibos)>0)
                <table class="col-12 footerTable">
                    <thead>
                        @switch($venta->tipo_venta)
                            @case("Envio")
                                @if(sizeof($venta->recibos)==0 && !isset($venta->nombre_receptor))
                                    <th class="col-6"><p class="col-12 p-2 m-0">Ubicación</p></th>
                                    <th class="col-6"></th>
                                @elseif(sizeof($venta->recibos)==0 && isset($venta->nombre_receptor))
                                    <th class="col-6 centerTable"><p class="col-12 p-2 m-0">Ubicación</p></th>
                                    <th class="col-6 centerTable"><p class="col-12 p-2 m-0">Receptor</p></th>
                                @else
                                    <th class="col-4"><p class="p-1 m-0 text-center">Recibos</p></th>
                                    <th class="col-4 centerTable"><p class="col-12 p-2 m-0">Ubicación</p></th>
                                    <th class="col-4 centerTable"><p class="col-12 p-2 m-0">Receptor</p></th>
                                @endif
                            @break
                            @case("Local")
                                @if (!isset($venta->nombre_receptor))
                                    <th class="col-6"><p class="p-1 m-0 text-center border_right">Recibos</p></th>
                                    <th class="col-6"></th>
                                @elseif (isset($venta->nombre_receptor))
                                    <th class="col-5"><p class="p-1 m-0 text-center">Recibos</p></th>
                                    <th class="col-5 centerTable"><p class="col-12 p-2 m-0">Receptor</p></th>
                                @endif
                            @break
                        @endswitch
                    </thead>
                    <tbody>
                        <tr class="lastTr">
                            @if (sizeof($venta->recibos)>0)
                                <td class="d-flex flex-column align-items-center justify-content-start recibosTd position-relative" @if (isset($venta->ubicacion) && strcmp($venta->tipo_venta,"Envio")==0) rowspan="2" @endif>
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
                                </td>
                            @endif
                    @if (isset($venta->ubicacion) && strcmp($venta->tipo_venta,"Envio")==0)
                            <td class="centerTable vetricalAlign"{{ sizeof($venta->recibos)>0 ? 'colspan=2' : "" }}>
                                <div class="col-12 divMapa_Boton" id="divMapa_Boton">
                                    <div id="map" data-lat="{{ $venta->ubicacion->lat }}" data-lng="{{ $venta->ubicacion->lng }}">
                                    </div>
                                    <div class="col-12 position-relative">
                                        <div class="invalid-feedback position-absolute" id="invalid-map">
                                            <p class="p-0 m-0 text-center fs-6 col-12"></p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @if (sizeof($venta->recibos)==0)
                                <td class="vetricalAlign">
                                @if (isset($venta->nombre_receptor))
                                    <div class="col-12 mt-2 mb-3">
                                        <div class="col-12 p-2 d-flex flex-column align-items-center justify-content-center">
                                            <div class="col-11 d-flex flex-column align-items-start justify-content-center">
                                                <h5 class="col-auto m-0">Nombre:</h5>
                                                <p id="direccion-newVenta" class="col-12 p-2 m-0 border_bottom">{{ $venta->nombre_receptor }}</p>
                                            </div>
                                            <div class="col-11 mt-1 d-flex flex-column align-items-start justify-content-center">
                                                <h5 class="col-auto m-0">Telefono:</h5>
                                                <p id="direccion-newVenta" class="col-12 p-2 m-0 border_bottom">{{ $venta->telefono_receptor }}</p>
                                            </div>
                                            @if(isset($venta->ubicacion) && strcmp($venta->tipo_venta,"Envio")==0)
                                                <div class="col-11 mt-1 pb-1 d-flex flex-column align-items-start justify-content-center border_bottom">
                                                    <h5 class="my-2">Fecha/Horario de Entrega</h5>
                                                    <div class="col-11 ps-2 d-flex flex-column justify-content-center align-items-start">
                                                        <div class="col-10 d-flex align-items-center justify-between">
                                                            <h6 class="col-6 text-start m-0 me-1">Desde:</h6>
                                                            <p class="col-auto p-0 m-0">
                                                                {{ $venta->ubicacion ? date_format(date_create($venta->ubicacion->fecha_entrega_min),"d/m/Y H:i") : "--/--/---- --:--" }}
                                                            </p>
                                                        </div>
                                                        <div class="col-10 d-flex align-items-center justify-between">
                                                            <h6 class="col-6 text-start m-0 me-1">Hasta:</h6>
                                                            <p class="col-auto p-0 m-0">
                                                                {{ $venta->ubicacion ? date_format(date_create($venta->ubicacion->fecha_entrega_max),"d/m/Y H:i") : "--/--/---- --:--" }}
                                                            </p>
                                                        </div>
                                                        @if(strcmp($venta->estado_entrega,"Completa")==0)
                                                            <div class="col-10 d-flex align-items-center justify-between">
                                                                <h6 class="col-6 text-start m-0 me-1">Entregado:</h6>
                                                                <p class="col-auto p-0 m-0">
                                                                    {{ date_format(date_create($venta->ubicacion->fecha_entrega),"d/m/Y H:i") }}
                                                                </p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-11 py-3 ps-2 d-flex align-items-center justify-content-evenly border_bottom">
                                                    <h5 class="col-auto m-0 p-0 me-1">Ultima modificación:</h5>
                                                    <p class="col-auto p-0 m-0">
                                                        {{ $venta->updated_at ? date_format(date_create($venta->updated_at),"d/m/Y H:i") : "--/--/---- --:--" }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </td>
                            @endif
                        </tr>
                        <tr>
                    @endif
                            @if (isset($venta->ubicacion) && strcmp($venta->tipo_venta,"Envio")==0)
                                <td class="centerTable vetricalAlign" colspan="2">
                                    <div class="col-12 mt-2 mb-3 datosEnvio">
                                        <div class="col-12 p-2 d-flex flex-column align-items-center justify-content-center">
                                            <div class="col-11 d-flex flex-column align-items-start justify-content-center">
                                                <h5 class="col-auto m-0">Dirección:</h5>
                                                <p id="direccion-newVenta" class="col-12 p-2 m-0 border_bottom">{{ $venta->ubicacion->direccion }}</p>
                                            </div>
                                            <div class="col-md-11 mt-1 d-flex align-items-start justify-content-start border_bottom">
                                                <div class="col-6 d-flex flex-column align-items-start justify-content-center">
                                                    <h5 class="col-auto m-0">Manzana/Piso:</h5>
                                                    <p id="direccion-newVenta" class="col-12 p-2 m-0">{{ $venta->ubicacion->manzana_piso }}</p>
                                                </div>
                                                <div class="col-6 d-flex flex-column align-items-start justify-content-center">
                                                    <h5 class="col-auto m-0">Casa/Depto:</h5>
                                                    <p id="direccion-newVenta" class="col-12 p-2 m-0">{{ $venta->ubicacion->casa_depto }}</p>
                                                </div>
                                            </div>
                                            @if(isset($venta->ubicacion->descripcion))
                                                <div class="col-11 mt-1 d-flex flex-column align-items-start justify-content-center">
                                                    <h5 class="col-auto m-0">Descripción de la ubicación:</h5>
                                                    <pre id="direccion-newVenta" class="col-12 p-2 m-0 border_bottom">{{ $venta->ubicacion->descripcion }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            @endif
                            @if (sizeof($venta->recibos)>0)
                                <td class="vetricalAlign">
                                    @if (isset($venta->nombre_receptor))
                                        <div class="col-12 mt-2 mb-3">
                                            <div class="col-12 p-2 d-flex flex-column align-items-center justify-content-center">
                                                <div class="col-11 d-flex flex-column align-items-start justify-content-center">
                                                    <h5 class="col-auto m-0">Nombre:</h5>
                                                    <p id="direccion-newVenta" class="col-12 p-2 m-0 border_bottom">{{ $venta->nombre_receptor }}</p>
                                                </div>
                                                <div class="col-11 mt-1 d-flex flex-column align-items-start justify-content-center">
                                                    <h5 class="col-auto m-0">Telefono:</h5>
                                                    <p id="direccion-newVenta" class="col-12 p-2 m-0 border_bottom">{{ $venta->telefono_receptor }}</p>
                                                </div>
                                                @if(isset($venta->ubicacion) && strcmp($venta->tipo_venta,"Envio")==0)
                                                    <div class="col-11 mt-1 pb-1 d-flex flex-column align-items-start justify-content-center border_bottom">
                                                        <h5 class="my-2">Fecha/Horario de Entrega</h5>
                                                        <div class="col-11 ps-2 d-flex flex-column justify-content-center align-items-start">
                                                            <div class="col-10 d-flex align-items-center justify-between">
                                                                <h6 class="col-6 text-start m-0 me-1">Desde:</h6>
                                                                <p class="col-auto p-0 m-0">
                                                                    {{ $venta->ubicacion ? date_format(date_create($venta->ubicacion->fecha_entrega_min),"d/m/Y H:i") : "--/--/---- --:--" }}
                                                                </p>
                                                            </div>
                                                            <div class="col-10 d-flex align-items-center justify-between">
                                                                <h6 class="col-6 text-start m-0 me-1">Hasta:</h6>
                                                                <p class="col-auto p-0 m-0">
                                                                    {{ $venta->ubicacion ? date_format(date_create($venta->ubicacion->fecha_entrega_max),"d/m/Y H:i") : "--/--/---- --:--" }}
                                                                </p>
                                                            </div>
                                                            @if(strcmp($venta->estado_entrega,"Completa")==0)
                                                                <div class="col-10 d-flex align-items-center justify-between">
                                                                    <h6 class="col-6 text-start m-0 me-1">Entregado:</h6>
                                                                    <p class="col-auto p-0 m-0">
                                                                        {{ date_format(date_create($venta->ubicacion->fecha_entrega),"d/m/Y H:i") }}
                                                                    </p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-11 py-3 ps-2 d-flex align-items-center justify-content-start border_bottom">
                                                        <h5 class="col-auto m-0 p-0 me-1">Ultima modificación:</h5>
                                                        <p class="col-auto p-0 m-0">
                                                            {{ $venta->updated_at ? date_format(date_create($venta->updated_at),"d/m/Y H:i") : "--/--/---- --:--" }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                    <div class="col-12 py-3 ps-2 d-flex align-items-center justify-content-evenly border_bottom">
                                        <h5 class="col-auto m-0 p-0 me-1">Ultima modificación:</h5>
                                        <p class="col-auto p-0 m-0">
                                            {{ $venta->updated_at ? date_format(date_create($venta->updated_at),"d/m/Y H:i") : "--/--/---- --:--" }}
                                        </p>
                                    </div>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            @else
                <table class="col-12 footerTable">
                    @if(isset($venta->nombre_receptor))
                        <thead>
                            <tr>
                                <th class="col-6 centerTable"><p class="col-12 p-2 m-0">Receptor</p></th>
                                <th class="col-6"></th>
                            </tr>
                        </thead>
                    @endif
                    <tbody class="borderTopNone">
                        <tr class="borderTopNone">
                            @if(isset($venta->nombre_receptor))
                                <td class="co-6">
                                    <div class="col-12 mt-2 mb-3">
                                        <div class="col-12 p-2 d-flex flex-column align-items-center justify-content-center">
                                            <div class="col-11 d-flex flex-column align-items-start justify-content-center">
                                                <h5 class="col-auto m-0">Nombre:</h5>
                                                <p id="direccion-newVenta" class="col-12 p-2 m-0 border_bottom">{{ $venta->nombre_receptor }}</p>
                                            </div>
                                            <div class="col-11 mt-1 d-flex flex-column align-items-start justify-content-center">
                                                <h5 class="col-auto m-0">Telefono:</h5>
                                                <p id="direccion-newVenta" class="col-12 p-2 m-0 border_bottom">{{ $venta->telefono_receptor }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            @else
                                <td class="co-3"></td>
                            @endif
                            <td class="borderTopNone col-6">
                                <div class="col-12 py-3 ps-2 d-flex align-items-center justify-content-evenly">
                                    <h5 class="col-auto m-0 p-0 me-1">Ultima modificación:</h5>
                                    <p class="col-auto p-0 m-0">
                                        {{ $venta->updated_at ? date_format(date_create($venta->updated_at),"d/m/Y H:i") : "--/--/---- --:--" }}
                                    </p>
                                </div>
                            </td>
                            @if(!isset($venta->nombre_receptor))
                                <td class="co-3"></td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            @endif
            <table class="col-12 footerTable">
                <tbody>
                    <tr class="col-12 tdBtnsVenta">
                        <td colspan="{{ sizeof($venta->recibos)>0 ? "3" : "2" }}" class="tdBtnsVenta">
                            <div class="col-12 d-flex align-items-center justify-content-center btnsVenta">
                                <div class="col-12 py-2 d-flex align-items-center justify-content-evenly">
                                    @php
                                        $fVenta=date_create($venta->fecha_venta)->getTimestamp();
                                        $fNow=now()->getTimestamp();
                                    @endphp
                                    @if($fNow<=$fVenta+345600)
                                        <div class="col-auto my-2 d-flex align-items-center justify-between">
                                            <form action="{{ route("venta.devolucionventa") }}" method="post">
                                                @csrf
                                                <input type="hidden" name="idVenta" value="{{ $venta->id }}">
                                                <button type="submit" class="btn btnConfirmarEntrega p-2" value="devolucion">Realizar una devolución</button>
                                            </form>
                                        </div>
                                    @endif
                                    @if(strcmp($venta->estado_entrega,"Completa")!=0)
                                        <div class="col-auto my-2 d-flex align-items-center justify-between">
                                            <form action="{{ route("venta.editarventa") }}" method="post">
                                                @csrf
                                                <input type="hidden" name="idVenta" value="{{ $venta->id }}">
                                                <button type="submit" class="btn btnConfirmarEntrega p-2" name="editar" value="editar">Editar venta</button>
                                            </form>
                                        </div>
                                        <div class="col-auto my-2 d-flex align-items-center justify-between">
                                            <button type="button" class="btn btnConfirmarEntrega p-2" data-bs-toggle="modal" data-bs-target="#modalCompletarPedido">Confirmar entregada</button>
                                        </div>
                                    @endif
                                    @if (session()->has("adminSet") && strcmp($venta->tipo_pago,"Pendiente")==0)
                                        <div class="col-auto my-2 d-flex align-items-center justify-between">
                                            <button type="button" class="btn btnDeleteV p-2" data-bs-toggle="modal" data-bs-target="#modalDeshacerVenta">Deshacer venta</button>
                                        </div>
                                    @endif
                                    @if (session()->has("adminSet") && strcmp($venta->estado_entrega,"Completa")==0)
                                        <!--<div class="col-auto my-2 d-flex align-items-center justify-between">
                                            <form action="" method="post">
                                                <input type="hidden" name="idVenta" value="">
                                                <button type="submit" class="btn btnConfirmarEntrega p-2" value="devolucion">Editar productos</button>
                                            </form>
                                        </div>-->
                                        @php
                                            $now=now()->getTimestamp();
                                            $validDeleteVenta=date_create($venta->fecha_venta)->getTimestamp()+1209600;
                                        @endphp
                                        @if($now>=$validDeleteVenta)
                                            <button type="button" id="btn-deleteVenta" class="btn p-2 btnDeleteV" data-bs-toggle="modal" data-bs-target="#modalEliminarVenta">Eliminar venta</button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @if(strcmp($venta->estado_entrega,"Completa")!=0)
        @if (strcmp($venta->tipo_pago,"Pendiente")==0)
            <div class="modal fade" id="modalCompletarPedido" aria-hidden="true" aria-labelledby="modalCompletarPedidoLabel" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="modalCompletarPedidoLabel">Crear Devolución</h1>
                            <div class="btn-close btn-closeDeleteVenta p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                                <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                            </div>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route("venta.setentrega") }}" novalidate method="post" id="formCompletarPedido">
                                @csrf
                                <input type="hidden" name="idVenta" value="{{ $venta->id }}">
                                <h2 class="mt-2 mb-3 col-12 text-center">Ingrese en metodo de pago para completar la entrega.</h2>
                                <div class="col-12 d-flex flex-column align-items-center justify-content-center">
                                    <div class="col-9">
                                        <div class="col-12 pt-1 pb-2 d-flex flex-column justify-content-center align-items-center">
                                            <div class="col-11 ps-2 d-flex justify-content-start align-items-center">
                                                <label for="metodoPago-confirmVenta" class="p-0 m-0 col-auto">Método de pago:</label>
                                                <select id="metodoPago-confirmVenta" name="metodoPago-confirmVenta" class="ms-2 btn form-control @if ($errors->any()) @if (in_array("metodoPago-confirmVenta",$errors->keys())) invalid @endif @endif">
                                                    <button type="button">
                                                        <selectedcontent></selectedcontent>
                                                    </button>
                                                    <option value="Tarjeta" {{ old("metodoPago-confirmVenta") ? (old("metodoPago-confirmVenta") == "Tarjeta" ? "selected" : "" ) : "selected"  }}>Tarjeta</option>
                                                    <option value="Efectivo" {{ old("metodoPago-confirmVenta") ? (old("metodoPago-confirmVenta") == "Efectivo" ? "selected" : "" ) : "" }}>Efectivo (¡Recibo opcional!)</option>
                                                    <option value="Mixto" {{ old("metodoPago-confirmVenta") ? (old("metodoPago-confirmVenta") == "Mixto" ? "selected" : "" ) : "" }}>Mixto</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <div class="text-center invalid-feedback @error('metodoPago-confirmVenta') is-invalid @enderror" id="invalid-metodoPago-confirmVenta">
                                                    @error('metodoPago-confirmVenta')
                                                        {{ str_replace("metodo pago-confirm venta","metodo de pago",$message) }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div id="div-fotos-confirmVenta" class="fotos-confirmVenta col-12 d-flex flex-column justify-content-center align-items-center">
                                            <div class="drop-area mt-2 text-center d-flex align-items-center justify-content-center flex-column form-control @if ($errors->any()) @if (in_array("fotos-confirmVenta",$errors->keys())) invalid @endif @endif">
                                                <h2 class="form-label">Arrastra y suelta imágenes aquí</h2>
                                                <span>O</span>
                                                <button type="button" class="px-2 py-1 mt-2">Buscar imágenes</button>
                                                <input type="file" accept="image/png, image/jpeg, image/jpg" class="inputFotos" hidden multiple>
                                                <select class="form-control" name="fotos-confirmVenta[]" id="fotos-confirmVenta" multiple hidden required>
                                                    @php $oldFotosEdit=["fotos"=>old("fotos-confirmVenta"),"data-foto"=>[]];
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
                                                        }
                                                    @endphp
                                                </select>
                                                <div class="text-center mt-3 fs-5 invalid-feedback @error('fotos-confirmVenta') is-invalid @enderror" id="invalid-fotos-confirmVenta">
                                                    @error('fotos-confirmVenta')
                                                        @if (str_contains($message,"obligatorio"))
                                                            {{ "Ingrese al menos un recibo de venta." }}
                                                        @else
                                                            {{ str_replace("fotos-confirm venta","fotos",$message) }}
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
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer d-flex align-items-center justify-content-between">
                            <button type="submit" id="btnCompletarPedido" form="formCompletarPedido" class="btn ms-3" name="entregar" value="entregar">Confirmar</button>
                            <button type="button" class="btn me-3" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="modal fade" id="modalCompletarPedido" aria-hidden="true" aria-labelledby="modalCompletarPedidoLabel" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="modalCompletarPedidoLabel">Crear Devolución</h1>
                            <div class="btn-close btn-closeDeleteVenta p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                                <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                            </div>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route("venta.setentrega") }}" method="post" id="formCompletarPedido">
                                @csrf
                                <input type="hidden" name="idVenta" value="{{ $venta->id }}">
                                <h2 class="my-5 col-12 text-center">¿Confirma que el pedido se ha entregado?</h2>
                            </form>
                        </div>
                        <div class="modal-footer d-flex align-items-center justify-content-between">
                            <button type="submit" id="btnCompletarPedido" form="formCompletarPedido" class="btn ms-3" name="entregar" value="entregar">Confirmar</button>
                            <button type="button" class="btn me-3" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
    @if (session()->has("adminSet"))
        @php
            $now=now()->getTimestamp();
            $validDeleteVenta=date_create($venta->fecha_venta)->getTimestamp()+1209600;
        @endphp
        @if($now>=$validDeleteVenta)
            <div class="modal fade" id="modalEliminarVenta" aria-hidden="true" aria-labelledby="modalEliminarVentaLabel" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="modalEliminarVentaLabel">Eliminar venta</h1>
                            <div class="btn-close btn-closeDeleteVenta p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                                <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                            </div>
                        </div>
                        <div class="modal-body">
                            <form class="form-deleteProducto text-center d-flex flex-column align-items-center justify-content-center" novalidate action="{{ route("venta.deleteventa") }}" method="post" id="formEliminarVenta" autocomplete="off">
                                @csrf
                                <input type="number" value="{{ $venta->id }}" name="idVenta-deleteVenta" required hidden>
                                <h2 class="mt-2 mb-3">Ingrese su contraseña para proceder con la <span class="text-decoration-underline">Eliminación</span></h2>
                                <input class="form-control @if ($errors->any()) @if (in_array("passAdmin-deleteVenta",$errors->keys())) invalid @endif @endif" type="password" id="passAdmin-deleteVenta" name="passAdmin-deleteVenta" value="" placeholder="Su contraseña">
                                <div class="invalid-feedback fs-4 text-center @if ($errors->any()) @if (in_array("passAdmin-deleteVenta",$errors->keys())) is-invalid @endif @endif position-relative" name="invalid-passAdmin" id="invalid-passAdmin-deleteVenta">
                                    @error('passAdmin-deleteVenta')
                                        @if (str_contains($message,"obligatorio"))
                                        <p class="p-0 m-0">La contraseña es obligatoria.</p>
                                        @elseif (str_contains($message,"Formato"))
                                        <p class="p-0 m-0">{{ str_replace("pass admin-delete venta","contraseña",$message) }}</p>
                                        <p class="p-0 m-0 fs-5">Ingrese al menos una Mayúscula, un número y un carácter especial.<span class="ms-1">Caracteres</span></p>
                                        <div name="validEspecialsPass" id="validEspecialsPass-deleteVenta" class="validEspecials m-1 py-1 px-2 position-absolute d-none">
                                            <p class="p-0 m-0"># @ | $ % & - _ ¡ ! ¿ ?</p>
                                        </div>
                                        @elseif(str_contains($message,"incorrecta"))
                                        <p class="p-0 m-0">{{ str_replace("pass admin-delete venta","contraseña",$message) }}</p>
                                        @else
                                        <p class="p-0 m-0">{{ "La ".str_replace("pass admin-delete venta","contraseña",$message) }}</p>
                                        @endif
                                    @enderror
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer d-flex align-items-center justify-content-between">
                            <button type="submit" id="btnEliminarVenta" form="formEliminarVenta" class="btn ms-3" name="eliminarVenta" value="delete">Eliminar</button>
                            <input type="button" class="btn me-3" data-bs-dismiss="modal" value="Cancelar">
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="modal fade" id="modalDeshacerVenta" aria-hidden="true" aria-labelledby="modalDeshacerVentaLabel" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalDeshacerVentaLabel">Deshacer venta</h1>
                        <div class="btn-close btn-closeDeleteVenta p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                            <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                        </div>
                    </div>
                    <div class="modal-body">
                        <form class="form-deleteProducto text-center d-flex flex-column align-items-center justify-content-center" novalidate action="{{ route("venta.deshacerventa") }}" method="post" id="formDeshacerVenta" autocomplete="off">
                            @csrf
                            <input type="number" value="{{ $venta->id }}" name="idVenta-deshacerVenta" required hidden>
                            <h2 class="mt-2 mb-3">Ingrese su contraseña para <span class="text-decoration-underline">Deshacer</span> la venta</h2>
                            <input class="form-control @if ($errors->any()) @if (in_array("passAdmin-deshacerVenta",$errors->keys())) invalid @endif @endif" type="password" id="passAdmin-deshacerVenta" name="passAdmin-deshacerVenta" value="" placeholder="Su contraseña">
                            <div class="invalid-feedback fs-4 text-center @if ($errors->any()) @if (in_array("passAdmin-deshacerVenta",$errors->keys())) is-invalid @endif @endif position-relative" name="invalid-passAdmin" id="invalid-passAdmin-deshacerVenta">
                                @error('passAdmin-deshacerVenta')
                                    @if (str_contains($message,"obligatorio"))
                                    <p class="p-0 m-0">La contraseña es obligatoria.</p>
                                    @elseif (str_contains($message,"Formato"))
                                    <p class="p-0 m-0">{{ str_replace("pass admin-deshacer venta","contraseña",$message) }}</p>
                                    <p class="p-0 m-0 fs-5">Ingrese al menos una Mayúscula, un número y un carácter especial.<span class="ms-1">Caracteres</span></p>
                                    <div name="validEspecialsPass" id="validEspecialsPass-deshacerVenta" class="validEspecials m-1 py-1 px-2 position-absolute d-none">
                                        <p class="p-0 m-0"># @ | $ % & - _ ¡ ! ¿ ?</p>
                                    </div>
                                    @elseif(str_contains($message,"incorrecta"))
                                    <p class="p-0 m-0">{{ str_replace("pass admin-deshacer venta","contraseña",$message) }}</p>
                                    @else
                                    <p class="p-0 m-0">{{ "La ".str_replace("pass admin-deshacer venta","contraseña",$message) }}</p>
                                    @endif
                                @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-between">
                        <button type="submit" id="btnDeshacerVenta" form="formDeshacerVenta" class="btn ms-3" name="deshacerVenta" value="deshacer">Deshacer</button>
                        <input type="button" class="btn me-3" data-bs-dismiss="modal" value="Cancelar">
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('footer')
@vite([
    "resources/js/app/dropImgs.js",
    "resources/js/app/venta.js",
    "resources/js/app/litemap.js",
])

<script>
    const invalidConfVenta=@if(in_array("metodoPago-confirmVenta",$errors->keys()) || in_array("fotos-confirmVenta",$errors->keys()))@json(1)@else @json(0)@endif;
    if(invalidConfVenta){
        const btnConfVenta=document.querySelector("button[data-bs-target='#modalCompletarPedido']");
        if(btnConfVenta!=null){
            setTimeout(() => {
                btnConfVenta.click();
            }, 50);
        }
    }
    @if (session()->has("adminSet"))
        var invalidDeleteVenta=@php if($errors->any()){
                                $deleteVenta="passAdmin-deleteVenta";
                                if(in_array($deleteVenta,$errors->keys())){
                                    echo json_encode(1);
                                }else{
                                    echo json_encode(0);
                                }
                                }else{
                                echo json_encode(0);
                                }
        @endphp;
        if(invalidDeleteVenta){
            setTimeout(()=>{
                const btnDelVenta=document.querySelector("#btn-deleteVenta");
                if(btnDelVenta!=null){
                    btnDelVenta.click();
                }
            },50);
        }
    @endif
</script>
@endsection