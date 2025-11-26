<div class="col-12 d-flex justify-content-center">
    <div class="col-11 p-2 @if(isset($retiros) && sizeof($retiros)>0) mt-5 mb-3 @else my-5 @endif contenedorPortada">
        @if(isset($pendientes) && sizeof($pendientes)>0)
            <div class="col-12 my-1 ConEntPend">
                <h4 class="m-0 p-0 py-2 text-center">Entregas pendientes.</h4>
            </div>
        @endif
        <div class="col-12 portadaInicio">
            @if(isset($pendientes) && sizeof($pendientes)>0)
                @foreach($pendientes as $venta)
                    <x-pedido-pendiente-component :venta="$venta"/>
                @endforeach
                @if ($totalPendientes>5)
                    <div class="col-12 d-flex align-items-center justify-content-center p-2" id="divShowMasPendientes">
                        <h5 class="col-auto m-0 py-2 px-3 text-center">Mostrar mas.</h5>
                    </div>
                @endif
            @else
            <div class="sinEntregasP">
                <h4 class="m-0 p-0 py-2 text-center">Sin entregas pendientes.</h4>
            </div>
            @endif
        </div>
    </div>
</div>
@if(isset($retiros) && sizeof($retiros)>0)
<div class="col-12 d-flex justify-content-center">
    <div class="col-11 p-2 mb-5 contenedorPortada">
        <div class="col-12 my-1 ConEntPend">
            <h4 class="m-0 p-0 py-2 text-center">Retiros pendientes.</h4>
        </div>
        <div class="col-12 portadaInicio">
            @foreach($retiros as $venta)
                <x-retiro-pendiente-component :venta="$venta"/>
            @endforeach
            @if ($totalRetiros>5)
                <div class="col-12 d-flex align-items-center justify-content-center p-2" id="divShowMasPendientes">
                    <h5 class="col-auto m-0 py-2 px-3 text-center">Mostrar mas.</h5>
                </div>
            @endif
        </div>
    </div>
</div>
@endif