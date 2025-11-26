@props(["venta"])
<a href="{{ route("venta.venta",$venta->id) }}" class="col-auto text-reset text-decoration-none p-2 m-2 d-flex align-items-start justify-content-between ventaPendiente @php
    $fEntMax=date_create($venta->fecha_entrega_max)->getTimestamp();
    $now=now()->getTimestamp();
    if(($fEntMax-$now)<=216000 && ($fEntMax-$now)>129600){
        echo "warning_envio";
    }elseif(($fEntMax-$now)<=129600 && ($fEntMax-$now)>0){
        echo "danger_envio";
    }elseif(($fEntMax-$now)<=0){
        echo "late_envio";
    }
@endphp">
    <div class="col-7 p-0 m-0 d-flex align-items-start">
        <p class="p-0 m-0 pe-2">Dirección:</p>
        <p class="p-0 m-0">{{ $venta->direccion }}</p>
    </div>
    @if(strcmp($venta->tipo_pago,"Pendiente")==0)
        <div class="col-2 p-0 m-0 d-flex align-items-start justify-content-center pagoPendiente">
            <p class="p-0 m-0 px-1">¡Pago Pendiente!</p>
        </div>
    @endif
    <div class="col-3 text-end p-0 m-0 fechaEntregaMax">
        <p class="p-0 m-0">Entregar antes del {{ date_create($venta->fecha_entrega_max)->format("d/m/Y H:i") }}</p>
    </div>
</a>