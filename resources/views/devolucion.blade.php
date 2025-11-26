@extends('layouts.index')

@section('head')
    @vite([
        "resources/css/myStyles/inicio.css",
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
        <div class="col-11 p-2">
            <table class="col-12 btnsDev">
                <thead>
                    <tr>
                        <th class="col-6">
                            <div class="col-auto d-flex align-items-center justify-content-start verVenta">
                                <a href="{{ route("venta.venta",$devolucionV->venta) }}" class="text-decoration-none text-reset col-auto d-flex align-items-center justify-content-enter px-2 py-1">
                                    <img class="img-fluid" src="{{ asset("build/assets/icons/back.svg") }}" alt="volver">
                                    <p class="p-0 ps-1 m-0">Ver venta</p>
                                </a>
                            </div>
                        </th>
                        <th class="col-6">
                            <div class="col-12 d-flex align-items-center justify-content-end">
                            <div class="col-auto d-flex align-items-center justify-content-between switchDevs">
                                @if (sizeof($idDevsVenta)>1)
                                    @php
                                        $idsDevsVenta=Arr::pluck($idDevsVenta,"id");
                                        $prevIndexDev=array_search($devolucionV->id,$idsDevsVenta);
                                        if($prevIndexDev-1<0){
                                            $prevIndexDev=sizeof($idDevsVenta)-1;
                                        }else{
                                            $prevIndexDev-=1;
                                        }
                                        $idPrevDev=$idDevsVenta[$prevIndexDev]["id"];
                                        
                                    @endphp
                                    <a href="{{ route("venta.devolucion",$idPrevDev) }}" class="text-decoration-none text-reset prevDev pe-1 ps-2 me-1 col-auto d-flex align-items-center justify-content-enter">
                                        <img class="img-fluid" src="{{ asset("build/assets/icons/previous.svg") }}" alt="Anterior">
                                        <p class="p-1 m-0">Anterior</p>
                                    </a>
                                @endif
                                <p class="mx-2 my-0 px-2 py-1">{{ (array_search($devolucionV->id,$idDevsVenta)+1)." de ".(sizeof($idDevsVenta)) }}</p>
                                @if (sizeof($idDevsVenta)>1)
                                    @php
                                        $idsDevsVenta=Arr::pluck($idDevsVenta,"id");
                                        $nextIndexDev=array_search($devolucionV->id,$idsDevsVenta);
                                        if($nextIndexDev+1>=sizeof($idsDevsVenta)){
                                            $nextIndexDev=0;
                                        }else{
                                            $nextIndexDev+=1;
                                        }
                                        $idNextDev=$idDevsVenta[$nextIndexDev]["id"];
                                    @endphp
                                    <a href="{{ route("venta.devolucion",$idNextDev) }}" class="text-decoration-none text-reset nextDev ps-1 pe-2 ms-1 col-auto d-flex align-items-center justify-content-enter">
                                        <p class="p-1 m-0">Siguiente</p>
                                        <img class="img-fluid" src="{{ asset("build/assets/icons/next.svg") }}" alt="Anterior">
                                    </a>
                                @endif
                            </div>
                        </th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="col-11 productos p-2">
            <table class="col-12">
                <thead>
                    <tr>
                        <th class="col-1 firstTh"><p class="p-2 m-0">Producto</p></th>
                        <th class="col-2"><p class="p-2 m-0">Motivo</p></th>
                        <th class="col-2"><p class="p-2 m-0">Tipo</p></th>
                        <th class="col-2"><p class="p-2 m-0">Cantidad</p></th>
                        <th class="col-2"><p class="p-2 m-0">Medida</p></th>
                        <th class="col-2"><p class="p-2 m-0">Total</p></th>
                    </tr>
                </thead>
                @if (isset($devolucionV))
                    <tbody>
                        @foreach ($devolucionV->productos as $producto)
                            <x-devolucion-tabla-component :producto="$producto"/>
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
                                <p id="totalProductos" class="col-4 py-2 pe-2 m-0 txtTh">{{ sizeof($devolucionV->productos) }}</p>
                            </div>
                        </th>
                        <th class="col-4 centerTable">
                            <div class="col-12 d-flex align-items-center justify-content-evenly">
                                <p class="col-auto py-2 ps-2 pe-1 m-0 text-end">Fecha de Devolución:</p>
                                <p class="col-auto py-2 pe-2 m-0 text-start txtTh">{{ date_create($devolucionV->fecha_devolucion)->format("d/m/Y H:i") }}</p>
                            </div>
                        </th>
                        <th class="col-4">
                            <div class="col-12 d-flex align-items-center justify-between">
                                <p class="col-auto py-2 ps-2 m-0">Total:</p>
                                <p id="totalVenta" class="col-8 py-2 pe-2 m-0 text-end txtTh">${{ $fmt->format($devolucionV->monto_total) }}</p>
                            </div>
                        </th>
                    </tr>
                </thead>
            </table>
            <table class="col-12 footerTable">
                <thead>
                    <tr>
                        @if (sizeof($devolucionV->recibos)>0)
                            <th class="col-6 centerTable">
                                <p class="p-2 m-0">Recibos</p>
                            </th>
                        @endif
                        <th class="col-6">
                            @if(strcmp($devolucionV->tipo_pago,"Devolucion")!=0)
                                <p class="col-12 p-2 m-0 text-center">Método de pago:{{ " ".$devolucionV->tipo_pago }}</p>
                            @else
                                <p class="col-12 p-2 m-0 text-center">Devolución previa al pago de la venta</p>
                            @endif
                        </th>
                    </tr>
                </thead>
                @if (sizeof($devolucionV->recibos)>0)
                    <tbody>
                        <tr class="lastTr devolucion">
                            @if (sizeof($devolucionV->recibos)>0)
                                <td class="d-flex flex-column align-items-center justify-content-start recibosTd position-relative">
                                    <div id="recibosDevVenta" class="carousel slide col-8 d-flex align-items-center justify-content-center" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            @php $i=0; @endphp
                                            @foreach ($devolucionV->recibos as $recibo)
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
                                    <button class="carousel-control-prev" type="button" data-bs-target="#recibosDevVenta" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Anterior</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#recibosDevVenta" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Siguiente</span>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    </tbody>
                @endif
            </table>
        </div>
    </div>
@endsection

@section('footer')
@vite([
    "resources/js/app/venta.js",
])
<script>
    
</script>
@endsection