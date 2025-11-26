@extends('layouts.index')

@section('head')
    @vite([
        "resources/css/myStyles/inicio.css",
        "resources/css/myStyles/ventas.css"
    ])
@endsection
@php
    $fmt0 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt0->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
    $fmt4 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt4->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 4);
@endphp
@section("content")
    <x-portada-component/>
    <x-buscador-component type="activo"/>
    <x-mensaje-div-component/>
    
    <div class="ventas mx-2 mb-2 pb-3">
        <div class="cabeceraVentas p-3">
            <div class="totalesVentaTiempo col-12 p-2">
                <div class="col-12 position-relative">
                    <canvas id="gananciasChart" width="400" height="200" 
                        data-chart="{{ json_encode([
                            "ganancias" => $ganancias,
                            "gananciasVisibles" => $gananciasVisibles,
                            "meses" => ['Ene', 'Feb', "Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"]
                        ]) }}">
                    </canvas>
                    @if(sizeof($ganancias)>sizeof($gananciasVisibles))
                        <button id="btnMostrarMasAnios" class="btn btn-sm btn-outline-secondary mt-2 position-absolute">
                            + {{ sizeof($ganancias)-sizeof($gananciasVisibles) }} años más
                        </button>
                    @endif
                </div>
                <div class="col-12 d-flex align-items-center px-2 justify-content-center contenedorFechasTotalVenta">
                    <div class="col-6">
                        <div class="col-12 my-2 d-flex align-items-center cantidadVentas">
                            <h5 class="m-0 col-4">Cantidad de ventas:</h5>
                            <h5 class="m-0 ps-2 col-auto">{{ isset($totalesVentas) ? $fmt0->format($totalesVentas["totalVentas"]) : "0" }}</h5>
                        </div>
                        <div class="col-12 mb-2 d-flex align-items-center ingresosVentas">
                            <h5 class="m-0 col-4">Ingresos totales:</h5>
                            <h5 class="m-0 ps-2 col-auto">${{ isset($totalesVentas) ? $fmt4->format($totalesVentas["gananciasTotalesReales"]) : "0.0000"  }}</h5>
                        </div>
                    </div>
                    <div class="col-6 d-flex flex-column align-items-end justify-content-center my-2">
                        <div class="col-12 mb-2 d-flex align-items-center justify-content-end">
                            <h5 class="m-0">Rango de fechas:</h5>
                            <div id="div_fechaPersonal" class="col-auto ms-2 d-flex position-relative flex-column justify-content-start {{ $totalesVentas["rango"] ? ($totalesVentas["rango"]=="Personal" ? "" : "d-none" ) : "d-none" }}">
                                <button id="filtro-fechas" class="col-12 btn d-flex align-items-center justify-content-between dropdown-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-filtroFechaTotalVenta" aria-expanded="false">
                                    Fechas
                                </button>
                                <div class="collapse position-absolute right-0" id="collapse-filtroFechaTotalVenta">
                                    <form action="{{ route("venta.totalesventas") }}" method="post" class="card card-body col-12 p-2 d-flex flex-column justify-center align-items-center">
                                        @csrf
                                        <input type="hidden" name="rango" value="Personal">
                                        <div title="Desde" class="d-flex align-items-center justify-content-start col-12">
                                            <label class="col-auto text-start p-1" for="filtro-fInicioTotalVenta">Desde:</label>
                                            <input class="col-9" type="datetime-local" id="filtro-fInicioTotalVenta" name="fInicio" value="{{ isset($totalesVentas) ? (isset($totalesVentas["fInicio"]) ? date_create($totalesVentas["fInicio"])->format("Y-m-d H:i") : "") : "" }}" min="{{ date("Y-m-d H:i",1420081200) }}" max="{{ date("Y-m-d H:i",now()->getTimestamp()) }}">
                                        </div>
                                        <div title="Hasta" class="d-flex align-items-center justify-content-start col-12">
                                            <label class="col-3 text-start p-1" for="filtro-fFinTotalVenta">Hasta:</label>
                                            <input class="col-9" type="datetime-local" id="filtro-fFinTotalVenta" name="fFin" value="{{ isset($totalesVentas) ? (isset($totalesVentas["fFin"]) ? date_create($totalesVentas["fFin"])->format("Y-m-d H:i") : "") : "" }}" min="{{ date("Y-m-d H:i",1420081200) }}" max="{{ date("Y-m-d H:i",now()->getTimestamp()) }}">
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="fechas" value="true" class="col-auto btn" id="btn-filtroFechasTotalVenta">Filtrar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="dropdown ms-2 me-3">
                                <button class="btn dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <p class="p-0 m-0">@switch($totalesVentas["rango"])
                                        @case("Hoy")
                                            Hoy
                                        @break
                                        @case("Semana")
                                            Ultima semana
                                        @break
                                        @case("Mes")
                                            Ultimo mes
                                        @break
                                        @case("Siempre")
                                            Siempre
                                        @break
                                        @case("Personal")
                                            Personalizado
                                        @break
                                        @default
                                            Hoy
                                        @endswitch
                                    </p>
                                </button>
                                <ul class="dropdown-menu col-10">
                                    <li>
                                        <form action="{{ route("venta.totalesventas") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                            @csrf
                                            <button type="submit" name="rango" value="Hoy" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ $totalesVentas["rango"] ? ($totalesVentas["rango"]=="Hoy" ? "selected" : "" ) : "selected" }}">
                                                <h6 class="p-0 m-0">Hoy</h6>
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route("venta.totalesventas") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                            @csrf
                                            <button type="submit" name="rango" value="Semana" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ $totalesVentas["rango"] ? ($totalesVentas["rango"]=="Semana" ? "selected" : "" ) : "" }}">
                                                <h6 class="p-0 m-0">Ultima semana</h6>
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route("venta.totalesventas") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                            @csrf
                                            <button type="submit" name="rango" value="Mes" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ $totalesVentas["rango"] ? ($totalesVentas["rango"]=="Mes" ? "selected" : "" ) : "" }}">
                                                <h6 class="p-0 m-0">Ultimo mes</h6>
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route("venta.totalesventas") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                            @csrf
                                            <button type="submit" name="rango" value="Siempre" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ $totalesVentas["rango"] ? ($totalesVentas["rango"]=="Siempre" ? "selected" : "" ) : "" }}">
                                                <h6 class="p-0 m-0">Siempre</h6>
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <div class="col-12 px-2 d-flex align-items-center justify-content-center">
                                            @csrf
                                            <button type="button" id="btn-filtroTotalVenta-fechaPresonal" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ $totalesVentas["rango"] ? ($totalesVentas["rango"]=="Personal" ? "selected" : "" ) : "" }}">
                                                <h6 class="p-0 m-0">Personalizado</h6>
                                            </button>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 d-flex align-items-center justify-content-end">
                            <h5 class="m-0">Metodo de pago:</h5>
                            <div class="dropdown ms-2 me-3">
                                <button class="btn dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <p class="p-0 m-0">@switch($totalesVentas["tipoPago"])
                                        @case("Todo")
                                            Todo
                                        @break
                                        @case("Tarjeta")
                                            Tarjeta
                                        @break
                                        @case("Efectivo")
                                            Efectivo
                                        @break
                                        @case("Mixto")
                                            Mixto
                                        @break
                                        @default
                                            Todo
                                        @endswitch
                                    </p>
                                </button>
                                <ul class="dropdown-menu col-10">
                                    <li>
                                        <form action="{{ route("venta.totalesventas") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                            @csrf
                                            <button type="submit" name="tipoPago" value="Todo" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ $totalesVentas["rango"] ? ($totalesVentas["rango"]=="Todo" ? "selected" : "" ) : "selected" }}">
                                                <h6 class="p-0 m-0">Todo</h6>
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route("venta.totalesventas") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                            @csrf
                                            <button type="submit" name="tipoPago" value="Tarjeta" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ $totalesVentas["tipoPago"] ? ($totalesVentas["tipoPago"]=="Tarjeta" ? "selected" : "" ) : "" }}">
                                                <h6 class="p-0 m-0">Tarjeta</h6>
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route("venta.totalesventas") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                            @csrf
                                            <button type="submit" name="tipoPago" value="Efectivo" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ $totalesVentas["tipoPago"] ? ($totalesVentas["tipoPago"]=="Efectivo" ? "selected" : "" ) : "" }}">
                                                <h6 class="p-0 m-0">Efectivo</h6>
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route("venta.totalesventas") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                            @csrf
                                            <button type="submit" name="tipoPago" value="Mixto" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ $totalesVentas["tipoPago"] ? ($totalesVentas["tipoPago"]=="Mixto" ? "selected" : "" ) : "" }}">
                                                <h6 class="p-0 m-0">Mixto</h6>
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="detalleRes d-flex align-items-center justify-content-between p-2">
                <p class="p-0 m-0">Todo</p>
                @if ($ventas->onLastPage() && $ventas->onFirstPage())
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center contenedor-resultados">
                        <div class="txt-resultados">
                            <p class="text-sm text-black leading-5 dark:text-black">
                                Resultados:
                                <span class="font-medium">@if(sizeof($ventas)==0) 0 @else 1 @endif</span>
                                -
                                <span class="font-medium">{{ sizeof($ventas) }}</span>
                                de
                                <span class="font-medium">{{ sizeof($ventas) }}</span>
                            </p>
                        </div>
                    </div>
                @else
                    {{ $ventas->links() }}
                @endif
            </div>
            <div class="detalleRes d-flex align-items-center justify-content-between p-2 ps-4">
                <h4>Ventas</h4>
                <form action="{{ route("venta.buscar") }}" method="POST" class="me-3">
                    @csrf
                    <button class="btn d-flex align-items-center" type="submit" id="mostrarVentasDevueltas" value="{{ session()->has("filtroVentas") ? ((session("filtroVentas")["devueltas"] == 1) ? 0 : 1) : 1 }}" name="devueltas">
                        {{ session()->has("filtroVentas") ? ((session("filtroVentas")["devueltas"] == 1) ? "Ocultar ventas canceladas" : "Mostrar ventas canceladas") : "Mostrar ventas canceladas" }}
                    </button>
                </form>
                <div class="d-flex align-items-center justify-content-end">
                    @if(session()->has("filtroVentas"))
                        <form action="{{ route("venta.buscar") }}" method="POST" class="me-3">
                            @csrf
                            <input id="limpiarFiltro" class="btn d-flex align-items-center" type="submit" value="Limpiar filtro" name="todo">
                        </form>
                    @endif
                    <div class="dropdown me-3">
                        <button class="btn dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <p class="p-0 m-0">Pedido</p>
                        </button>
                        <ul class="dropdown-menu col-10" id="pedidoVentas">
                            <li>
                                <form action="{{ route("venta.buscar") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="pedido" value="Envio" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ session()->has("filtroVentas") ? (session("filtroVentas")["pedido"]=="Envio" ? "selected" : "" ) : "" }}">
                                        <h6 class="p-0 m-0">Con Envio</h6>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route("venta.buscar") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="pedido" value="Local" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ session()->has("filtroVentas") ? (session("filtroVentas")["pedido"]=="Local" ? "selected" : "" ) : "" }}">
                                        <h6 class="p-0 m-0">Retiro Local</h6>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown me-3">
                        <button class="btn dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <p class="p-0 m-0">Entrega</p>
                        </button>
                        <ul class="dropdown-menu col-10" id="entregaVentas">
                            <li>
                                <form action="{{ route("venta.buscar") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="entrega" value="Pendiente" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ session()->has("filtroVentas") ? (session("filtroVentas")["entrega"]=="Pendiente" ? "selected" : "" ) : "" }}">
                                        <h6 class="p-0 m-0">Pendiente</h6>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route("venta.buscar") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="entrega" value="Completa" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ session()->has("filtroVentas") ? (session("filtroVentas")["entrega"]=="Completa" ? "selected" : "" ) : "" }}">
                                        <h6 class="p-0 m-0">Completa</h6>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown me-3">
                        <button class="btn dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <p class="p-0 m-0">Metodo de Pago</p>
                        </button>
                        <ul class="dropdown-menu col-10">
                            <li>
                                <form action="{{ route("venta.buscar") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="pago" value="todo" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ session()->has("filtroVentas") ? (session("filtroVentas")["pago"]==null ? "selected" : "" ) : "selected" }}">
                                        <h6 class="p-0 m-0">Todo</h6>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route("venta.buscar") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="pago" value="Tarjeta" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ session()->has("filtroVentas") ? (session("filtroVentas")["pago"]=="Tarjeta" ? "selected" : "" ) : "" }}">
                                        <h6 class="p-0 m-0">Tarjeta</h6>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route("venta.buscar") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="pago" value="Efectivo" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ session()->has("filtroVentas") ? (session("filtroVentas")["pago"]=="Efectivo" ? "selected" : "" ) : "" }}">
                                        <h6 class="p-0 m-0">Efectivo</h6>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route("venta.buscar") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="pago" value="Mixto" class="d-flex align-items-center justify-content-between dropdown-item px-1 {{ session()->has("filtroVentas") ? (session("filtroVentas")["pago"]=="Mixto" ? "selected" : "" ) : "" }}">
                                        <h6 class="p-0 m-0">Mixto</h6>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <div class="col-auto me-3 d-flex position-relative flex-column justify-content-start">
                        <button id="filtro-fechas" class="col-12 btn d-flex align-items-center justify-content-between dropdown-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-filtroFecha" aria-expanded="false">
                            Fechas
                        </button>
                        <div class="collapse position-absolute" id="collapse-filtroFecha">
                            <form action="{{ route("venta.buscar") }}" method="post" class="card card-body col-12 p-2 d-flex flex-column justify-center align-items-center">
                                @csrf
                                <div title="Desde" class="d-flex align-items-center justify-content-start col-12">
                                    <label class="col-auto text-start p-1" for="filtro-fInicio">Desde:</label>
                                    <input class="col-9" type="datetime-local" id="filtro-fInicio" name="fInicio" value="{{ session()->has("filtroVentas") ? (session("filtroVentas")["fInicio"] ? session("filtroVentas")["fInicio"] : "") : "" }}" min="{{ date("Y-m-d H:i",1420081200) }}" max="{{ date("Y-m-d H:i",now()->getTimestamp()) }}">
                                </div>
                                <div title="Hasta" class="d-flex align-items-center justify-content-start col-12">
                                    <label class="col-3 text-start p-1" for="filtro-fFin">Hasta:</label>
                                    <input class="col-9" type="datetime-local" id="filtro-fFin" name="fFin" value="{{ session()->has("filtroVentas") ? (session("filtroVentas")["fFin"] ? session("filtroVentas")["fFin"] : "") : "" }}" min="{{ date("Y-m-d H:i",1420081200) }}" max="{{ date("Y-m-d H:i",now()->getTimestamp()) }}">
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="fechas" value="true" class="col-auto btn" id="btn-filtroFechas">Filtrar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="dropdown me-3">
                        <button class="btn dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <p class="p-0 m-0">@php
                                switch(session("ordenarVentas")){
                                    case "MasReciente": echo "Reciente"; break;
                                    case "MenosReciente": echo "Reciente"; break;
                                    case "MayorMonto": echo "Monto"; break;
                                    case "MenorMonto": echo "Monto"; break;
                                    default: echo "Reciente";
                                }
                            @endphp</p>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <form action="{{ route("venta.orden") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="orden" value="{{ (strcmp(session("ordenarVentas"),"MasReciente")==0) ?"MenosReciente":"MasReciente" }}" class="d-flex align-items-center justify-content-between dropdown-item px-1 @if(strcmp(session("ordenarVentas"),"MenosReciente")==0 || strcmp(session("ordenarVentas"),"MasReciente")==0) selected @endif">
                                        <h6 class="p-0 m-0">Más Reciente</h6>
                                        <img class="img-fluid" src="@php
                                        $img="build/assets/icons/";
                                            if(strcmp(session("ordenarVentas"),"MenosReciente")==0)$img.="up.svg";
                                            else $img.="down.svg";
                                        echo asset($img);
                                        @endphp" alt="@php
                                                if(strcmp(session("ordenarVentas"),"MenosReciente")==0)echo "up";
                                                else echo "down";
                                        @endphp" draggable="false">
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route("venta.orden") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="orden" value="{{ (strcmp(session("ordenarVentas"),"MayorMonto")==0) ?"MenorMonto":"MayorMonto" }}" class="d-flex align-items-center justify-content-between dropdown-item px-1 @if(strcmp(session("ordenarVentas"),"MenorMonto")==0 || strcmp(session("ordenarVentas"),"MayorMonto")==0) selected @endif">
                                        <h6 class="p-0 m-0">Mayor Monto</h6>
                                        <img class="img-fluid" src="@php
                                        $img="build/assets/icons/";
                                            if(strcmp(session("ordenarVentas"),"MenorMonto")==0)$img.="up.svg";
                                            else $img.="down.svg";
                                        echo asset($img);
                                        @endphp" alt="@php
                                                if(strcmp(session("ordenarVentas"),"MenorMonto")==0)echo "up";
                                                else echo "down";
                                        @endphp" draggable="false">
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="cuerpoVentas d-flex align-items-center justify-content-center mx-3">
            @if (isset($ventas) && sizeof($ventas)>0)
                <div class="col-11 listVentas d-flex flex-column align-items-center justify-content-center">
                    @foreach ($ventas as $venta)
                        <x-venta-component :venta="$venta"/>
                    @endforeach
                </div>
            @else
                <div class="m-5">
                    <h3 class="m-0 p-0 text-center">No se encontraron ventas.</h3>
                </div>
            @endif
        </div>
        <div class="pieVentas mx-3 py-3">
            @if ($ventas->onLastPage() && $ventas->onFirstPage())
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center contenedor-resultados">
                    <div class="btns-resultados">
                        <span class="relative z-0 inline-flex rtl:flex-row-reverse shadow-sm rounded-md">
                            <span aria-disabled="true" aria-label="&amp;laquo; Anterior">
                                <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5 dark:bg-gray-800 dark:border-gray-600" aria-hidden="true">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Anterior
                                </span>
                            </span>
                            <span aria-current="page">
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 dark:bg-gray-800 dark:border-gray-600">1</span>
                            </span>
                            <span aria-disabled="true" aria-label="Siguiente &amp;raquo;">
                                <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md leading-5 dark:bg-gray-800 dark:border-gray-600" aria-hidden="true">
                                    Siguiente
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                            </span>
                        </span>
                    </div>
                </div>
                
            @else
                {{ $ventas->links() }}
            @endif
        </div>
    </div>

@endsection

@section('footer')
@vite([
    "resources/js/app/ventas.js"
])
<script>

</script>
@endsection
