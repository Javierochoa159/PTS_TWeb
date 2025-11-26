@props(["categorias","type"])

@foreach ($categorias as $categoria)
    @if (empty($categoria['hijos']))
        @if (strcmp($type,"link")==0)
            <form action="{{ route("producto.categoria") }}" method="POST">@csrf<button class="m-0 p-0 px-1 txt-catego" type="submit" name="idCatego" value="{{ $categoria["id"] }}" data-id="{{ $categoria["id"] }}">{{$categoria['titulo']}}</button></form>
        @elseif (strcmp($type,"buscar")==0)
            <form action="{{ route("producto.buscarproductos") }}" method="POST">@csrf<button class="m-0 p-0 px-1 txt-catego" type="submit" name="catego" value="{{ $categoria["id"] }}" data-id="{{ $categoria["id"] }}">{{$categoria['titulo']}}</button></form>
        @else
            <p class="m-0 p-0 txt-catego" data-id="{{ $categoria["id"] }}">{{$categoria['titulo']}}</p>
        @endif
    @else
        <div class="catego">
            <div class="d-flex align-items-center @if (strcmp($type,"link")==0) ps-1 @endif boton-catego">
                <div class="img-catego d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#{{str_replace(' ','_',$categoria['titulo']).$categoria["id"].$type}}" aria-expanded="false">
                    <img class="img-fluid" src="{{ asset("build/assets/icons/plus.svg") }}" alt="Mostrar" title="Mostrar" draggable="false">
                </div>
                @if (strcmp($type,"link")==0)
                    <form action="{{ route("producto.categoria") }}" method="POST">@csrf<input type="hidden"><button class="m-0 p-0 px-1 btn-catego" type="submit" name="idCatego" value="{{ $categoria["id"] }}" data-id="{{ $categoria["id"] }}">{{$categoria['titulo']}}</button></form>
                @elseif (strcmp($type,"buscar")==0)
                    <form action="{{ route("producto.buscarproductos") }}" method="POST">@csrf<input type="hidden"><button class="m-0 p-0 px-1 btn-catego" type="submit" name="catego" value="{{ $categoria["id"] }}" data-id="{{ $categoria["id"] }}">{{$categoria['titulo']}}</button></form>
                @else
                    <p class="m-0 p-0 btn-catego" data-id="{{ $categoria["id"] }}">{{$categoria['titulo']}}</p>
                @endif
            </div>
            <div class="collapse flex-column align-items-start" id="{{str_replace(' ','_',$categoria['titulo']).$categoria["id"].$type}}">
                <x-categorias-component-recursivo :categorias="$categoria['hijos']" :type="$type"/>
            </div>
        </div>
    @endif
@endforeach