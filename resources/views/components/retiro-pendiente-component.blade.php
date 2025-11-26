@props(["venta"])
<a href="{{ route("venta.venta",$venta->id) }}" class="col-auto text-reset text-decoration-none p-2 m-2 d-flex align-items-start justify-content-between ventaPendiente @php
    $fVenta=date_create($venta->fecha_venta)->getTimestamp();
    $now=now()->getTimestamp();
    if(($now-$fVenta)<=259200 && ($now-$fVenta)>84600){
        echo "warning_retiro";
    }elseif(($now-$fVenta)>259200){
        echo "danger_retiro";
    }
@endphp">
    <div class="col-7 p-0 m-0 d-flex align-items-start">
        <p class="p-0 m-0 pe-2">Cliente:</p>
        <p class="p-0 m-0">{{ $venta->nombre_receptor }}</p>
    </div>
    @if(strcmp($venta->tipo_pago,"Pendiente")==0)
        <div class="col-2 p-0 m-0 d-flex align-items-start justify-content-center pagoPendiente">
            <p class="p-0 m-0 px-1">¡Pago Pendiente!</p>
        </div>
    @endif
    <div class="col-3 text-end p-0 m-0 fechaEntregaMax">
        <p class="p-0 m-0">Fecha de venta: {{ date_create($venta->fecha_venta)->format("d/m/Y H:i") }}</p>
    </div>
</a>