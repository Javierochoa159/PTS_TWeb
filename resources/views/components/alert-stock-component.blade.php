@if ($cantidad<=$minimo)
    @if ($cantidad > ($minimo*0.35))
        <div class="alert-stock position-absolute" title="!Empieza a faltar stock!">
            <img src="{{ asset("build/assets/icons/warning.svg") }}" alt="Warning" draggable="false">
        </div>
    @elseif($cantidad > 0)
        <div class="alert-stock position-absolute" title="¡Queda poco stock!">
            <img src="{{ asset("build/assets/icons/invalid.svg") }}" alt="Alert" draggable="false">
        </div>
    @else
        <div class="alert-stock-empty position-absolute" title="¡Sin stock!">
            <img src="{{ asset("build/assets/icons/invalid.svg") }}" alt="Empty" draggable="false">
        </div>
    @endif
@endif