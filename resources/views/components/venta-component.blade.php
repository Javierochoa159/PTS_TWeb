@php
    use Brick\Math\BigDecimal;
    use Brick\Math\RoundingMode;
    $fmt4 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt4->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 4);
    $fmt2 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt2->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
    $fmt0 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt0->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
@endphp

<div class="venta col-12 mb-3 @php
    if(strcmp($venta->tipo_venta,"Envio")==0 && strcmp($venta->estado_entrega,"Completa")!=0){
        $fEntMax=date_create($venta->ubicacion->fecha_entrega_max)->getTimestamp();
        $now=now()->getTimestamp();
        if(($fEntMax-$now)<=216000 && ($fEntMax-$now)>129600){
            echo "warning_envio";
        }elseif(($fEntMax-$now)<=129600 && ($fEntMax-$now)>0){
            echo "danger_envio";
        }elseif(($fEntMax-$now)<=0){
            echo "late_envio";
        }
    }elseif(strcmp($venta->tipo_venta,"Envio")!=0 && strcmp($venta->estado_entrega,"Completa")!=0){
        $fVenta=date_create($venta->fecha_venta)->getTimestamp();
        $now=now()->getTimestamp();
        if(($now-$fVenta)<=259200 && ($now-$fVenta)>84600){
            echo "warning_retiro";
        }elseif(($now-$fVenta)>259200){
            echo "danger_retiro";
        }
    }
@endphp">
<div class="col-12 d-flex align-items-center justify-content-between resumenVenta">
    <div class="col-3 p-0 m-0 ms-2 pe-2">
            <p class="p-0 m-0">Monto Total: ${{ $fmt4->format($venta->monto_total_real) }}</p>
        </div>
        <div class="col-3 p-0 m-0 pe-2">
            <p class="p-0 m-0">Cantidad de Articulos: {{ $venta->total_productos_real }}</p>
        </div>
        <div class="col-auto p-0 m-0 pe-2">
            <p class="m-0 p-1 fechaV">Fecha de transacción: {{ date_create($venta->fecha_venta)->format("d/m/Y H:i") }}</p>
        </div>
        <div class="p-1 col-2 m-0 pe-2">
            <p class="p-0 m-0">Entrega: {{ $venta->estado_entrega }}</p>
        </div>
        <button id="btn-detalleVenta_{{ $venta->id }}" class="col-auto btn m-2 btnDetailVenta" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-detalleVenta_{{ $venta->id }}" aria-expanded="false">
            <img class="img-fluid" src="{{ asset("build/assets/icons/down.svg") }}" alt="down" draggable="false">
        </button>
    </div>
    <div class="collapse-detalleVenta col-12 collapse" id="collapse-detalleVenta_{{ $venta->id }}">
        <div class="card card-body col-12 p-2">
            <div class="col-12">
                @foreach ($venta->productosVenta as $producto )
                    <div class="producto col-12 d-flex align-items-center justify-between">
                        <div class="col-7">
                            <p class="p-0 m-0 ps-2">{{ strlen($producto->productoRelacion->nombre)>80 ? mb_substr($producto->productoRelacion->nombre,0,80)."..." : $producto->productoRelacion->nombre }}</p>
                        </div>
                        <div class="col-5 d-flex align-items-center justify-between">
                            <p class="col-3 text-end p-0 m-0">{{ $producto->productoRelacion->tipo_medida }}</p>
                            <p class="col-3 text-center p-0 m-0">@php
                                $cantProd=BigDecimal::of($producto->cantidad)->toScale(4, RoundingMode::HALF_UP);
                                $aux=BigDecimal::of($cantProd)->toScale(0, RoundingMode::DOWN);
                                if($aux->isLessThan($cantProd)){
                                    echo $fmt4->format($cantProd->__toString());
                                }else{
                                    echo $fmt0->format($aux->__toString());
                                }
                            @endphp</p>
                            <p class="col-1 text-center p-0 m-0">X</p>
                            <p class="col-4 text-center p-0 m-0">{{ "$".$fmt2->format(BigDecimal::of($producto->precio_venta)->toScale(2, RoundingMode::HALF_UP)->__toString()) }}</p>
                        </div>
                    </div>
                @endforeach
                @if ($venta->total_productos>3)
                    <div class="producto col-12 d-flex align-items-center justify-between">
                        <div class="col-7">
                            <p class="p-0 m-0 ps-2">...</p>
                        </div>
                        <div class="col-5 d-flex align-items-center justify-between">
                        </div>
                    </div>
                @endif
                <div class="col-12 btnVerVenta pt-2 ps-2 mt-2 d-flex align-items-center justify-between">
                    <a class="text-decoration-none text-reset btn px-4 py-1" href="{{ route("venta.venta",$venta->id) }}">Ver más</a>
                    <p class="col-auto p-0 m-0 px-2 me-2">{{ ($venta->tipo_venta == "Envio") ? ($venta->estado_entrega != "Completa" ? 'Fecha máxima de entrega: '.date_create($venta->ubicacion->fecha_entrega_max)->format("d/m/Y H:i") : 'Fecha de entrega: '.date_create($venta->ubicacion->fecha_entrega)->format("d/m/Y H:i")) : ""}}</p>
                </div>
            </div>
        </div>
    </div>
</div>
