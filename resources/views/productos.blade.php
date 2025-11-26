@extends('layouts.index')

@section('head')
    @vite([
        "resources/css/myStyles/inicio.css",
        "resources/css/myStyles/productos.css"
    ])
@endsection

@section("aside")
    <div class="categos col-12 d-flex flex-column align-items-center">
        <div class="asideCategos p-2 pb-3 col-11 d-flex flex-column align-items-center">
            <h5 class="text-center p-0 py-2 m-0 mt-2 mb-2">Categorías</h5>
            <div class="categorias py-2 px-0 d-flex flex-column align-items-start justify-content-start form-control">
                @switch(session("pagina"))
                    @case("productos")
                        @if(session()->has("buscar"))
                            <x-categorias-component :categorias="$categorias" text="Todo" type="buscar"/>
                        @else
                            <x-categorias-component :categorias="$categorias" text="Todo" type="link"/>
                        @endif
                    @break
                    @case("productos-todos")
                        @if(session()->has("buscar"))
                            <x-categorias-component :categorias="$categorias" text="Todo" type="buscar"/>
                        @else
                            <x-categorias-component :categorias="$categorias" text="Todo" type="link"/>
                        @endif
                    @break
                @endswitch    
            </div>
        </div>
    </div>
@endsection

@section("content")
    <x-portada-component/>
    <x-buscador-component type="activo"/>
    <x-mensaje-div-component/>

    <div class="productos mx-2 mb-2 pb-3">
        <div class="cabeceraProductos p-3">
            <div class="detalleRes d-flex align-items-center justify-content-between p-2">
                <p class="p-0 m-0 col-auto">
                    @if (strcmp(session("pagina"),"productos")==0) 
                        productos disponibles
                    @else
                        productos
                    @endif
                </p>
                @if (isset($txtCatego))
                    <p class="p-0 m-0"> | </p>
                @endif
                <p class="p-0 m-0 col-8">
                    @if(isset($txtCatego))
                        {{ $txtCatego }}
                    @endif
                </p>
                @if (isset($txtCatego))
                    <p class="p-0 m-0"> | </p>
                @endif
                @if ($productos->onLastPage() && $productos->onFirstPage())
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center contenedor-resultados">
                        <div class="txt-resultados">
                            <p class="text-sm text-black leading-5 dark:text-black">
                                Resultados:
                                @if (isset($productos))
                                    @if ($productos->count()>0)
                                        <span class="font-medium">1</span>
                                        -
                                        <span class="font-medium">{{ sizeof($productos) }}</span>
                                        de
                                        <span class="font-medium">{{ sizeof($productos) }}</span>
                                    @else
                                        <span class="font-medium">{{ sizeof($productos) }}</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                    </div>
                @else
                    {{ $productos->links() }}
                @endif
            </div>
            <div class="detalleRes d-flex align-items-center justify-content-between p-2 ps-4">
                <h4>
                    @if(session()->has("buscar"))
                        Buscar
                    @else
                        Productos
                    @endif
                </h4>
                @if(!session()->has("buscar")) 
                    <div class="dropdown me-3">
                        <button class="btn dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <p class="p-0 m-0">@php
                                switch(session("ordenProductos")){
                                    case "MasVendido": echo "Más Vendido"; break;
                                    case "MenosVendido": echo "Más Vendido"; break;
                                    case "NombreAZ": echo "Nombre"; break;
                                    case "NombreZA": echo "Nombre"; break;
                                    case "MayorPrecio": echo "Precio"; break;
                                    case "MenorPrecio": echo "Precio"; break;
                                    default: echo "Más Vendido";
                                }
                            @endphp</p>
                        </button>
                        <ul class="orden dropdown-menu">
                            <li>
                                <form action="@if(isset($idCatego)){{ route("producto.categoria") }}@else{{ route("producto.buscarproductos") }}@endif" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="orden" value="@if(strcmp(session("ordenProductos"),"MasVendido")==0) MenosVendido @else MasVendido @endif" class="d-flex align-items-center justify-content-between dropdown-item px-1 @if(strcmp(session("ordenProductos"),"MenosVendido")==0 || strcmp(session("ordenProductos"),"MasVendido")==0) selected @endif">
                                        <h6 class="p-0 m-0">Más vendido</h6>
                                        <img class="img-fluid" src="@php
                                        $img="build/assets/icons/";
                                            if(strcmp(session("ordenProductos"),"MenosVendido")==0)$img.="up.svg";
                                            else $img.="down.svg";
                                        echo asset($img);
                                        @endphp" alt="@php
                                                if(strcmp(session("ordenProductos"),"MenosVendido")==0)echo "up";
                                                else echo "down";
                                        @endphp" draggable="false">
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="@if(isset($idCatego)){{ route("producto.categoria") }}@else{{ route("producto.buscarproductos") }}@endif" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    @if (isset($idCatego))
                                        <input type="hidden" name="idCatego" value="{{ $idCatego }}">
                                    @endif
                                    <button type="submit" name="orden" value="@if(strcmp(session("ordenProductos"),"NombreAZ")==0) NombreZA @else NombreAZ @endif" class="d-flex align-items-center justify-content-between dropdown-item px-1 @if(strcmp(session("ordenProductos"),"NombreZA")==0 || strcmp(session("ordenProductos"),"NombreAZ")==0) selected @endif">
                                        <h6 class="p-0 m-0">Nombre</h6>
                                        <img class="img-fluid" src="@php
                                        $img="build/assets/icons/";
                                            if(strcmp(session("ordenProductos"),"NombreZA")==0)$img.="up.svg";
                                            else $img.="down.svg";
                                        echo asset($img);
                                        @endphp" alt="@php
                                                if(strcmp(session("ordenProductos"),"NombreZA")==0)echo "up";
                                                else echo "down";
                                        @endphp" draggable="false">
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="@if(isset($idCatego)){{ route("producto.categoria") }}@else{{ route("producto.buscarproductos") }}@endif" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    @if (isset($idCatego))
                                        <input type="hidden" name="idCatego" value="{{ $idCatego }}">
                                    @endif
                                    <button type="submit" name="orden" value="@if(strcmp(session("ordenProductos"),"MayorPrecio")==0) MenorPrecio @else MayorPrecio @endif" class="d-flex align-items-center justify-content-between dropdown-item px-1 @if(strcmp(session("ordenProductos"),"MenorPrecio")==0 || strcmp(session("ordenProductos"),"MayorPrecio")==0) selected @endif">
                                        <h6 class="p-0 m-0">Precio</h6>
                                        <img class="img-fluid" src="@php
                                        $img="build/assets/icons/";
                                            if(strcmp(session("ordenProductos"),"MenorPrecio")==0)$img.="up.svg";
                                            else $img.="down.svg";
                                        echo asset($img);
                                        @endphp" alt="@php
                                                if(strcmp(session("ordenProductos"),"MenorPrecio")==0)echo "up";
                                                else echo "down";
                                        @endphp" draggable="false">
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <form method="POST" id="formBuscarMas" action="{{ route("producto.buscarproductos") }}" class="d-flex align-items-center justify-content-around p-2 ps-4 col-8">
                        @csrf
                        <input type="text" name="prod" class="col-10 prodBuscar" value="{{ old("prod") ? old("prod") : $prodBuscado}}">
                        <input type="submit" form="formBuscarMas" value="Buscar" class="btn formBuscarBtn">
                    </form>
                    <div class="dropdown ms-5 me-3">
                        <button class="btn dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <p class="p-0 m-0">@if (strcmp(session("pagina"),"productos")==0) Disponible @else Todo @endif</p>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <form action="{{ route("producto.buscarproductos") }}" method="post" class="col-auto px-2">
                                    @csrf
                                    <button type="submit" class="dropdown-item col-12" name="pagina" value="@if (strcmp(session("pagina"),"productos")==0) todos @else disponibles @endif">
                                        <h6 class="col-12 m-0 text-center">
                                            @if (strcmp(session("pagina"),"productos")==0) Todo @else Disponible @endif
                                        </h6>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown me-3">
                        <button class="btn dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <p class="p-0 m-0">@php
                                switch(session("ordenProductos")){
                                    case "MasVendido": echo "Más Vendido"; break;
                                    case "MenosVendido": echo "Más Vendido"; break;
                                    case "NombreAZ": echo "Nombre"; break;
                                    case "NombreZA": echo "Nombre"; break;
                                    case "MayorPrecio": echo "Precio"; break;
                                    case "MenorPrecio": echo "Precio"; break;
                                    default: echo "Más Vendido";
                                }
                            @endphp</p>
                        </button>
                        <ul class="orden dropdown-menu">
                            <li>
                                <form action="{{ route("producto.buscarproductos") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="orden" value="@if(strcmp(session("ordenProductos"),"MenosVendido")==0) MasVendido @else MenosVendido @endif" class="d-flex align-items-center justify-content-between dropdown-item px-1 @if(strcmp(session("ordenProductos"),"MenosVendido")==0 || strcmp(session("ordenProductos"),"MasVendido")==0) selected @endif">
                                        <h6 class="p-0 m-0">Más vendido</h6>
                                        <img class="img-fluid" src="@php
                                        $img="build/assets/icons/";
                                            if(strcmp(session("ordenProductos"),"MenosVendido")==0)$img.="up.svg";
                                            else $img.="down.svg";
                                        echo asset($img);
                                        @endphp" alt="@php
                                                if(strcmp(session("ordenProductos"),"MenosVendido")==0)echo "up";
                                                else echo "down";
                                        @endphp" draggable="false">
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route("producto.buscarproductos") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="orden" value="@if(strcmp(session("ordenProductos"),"NombreAZ")==0) NombreZA @else NombreAZ @endif" class="d-flex align-items-center justify-content-between dropdown-item px-1 @if(strcmp(session("ordenProductos"),"NombreZA")==0 || strcmp(session("ordenProductos"),"NombreAZ")==0) selected @endif">
                                        <h6 class="p-0 m-0">Nombre</h6>
                                        <img class="img-fluid" src="@php
                                        $img="build/assets/icons/";
                                            if(strcmp(session("ordenProductos"),"NombreZA")==0)$img.="up.svg";
                                            else $img.="down.svg";
                                        echo asset($img);
                                        @endphp" alt="@php
                                                if(strcmp(session("ordenProductos"),"NombreZA")==0)echo "up";
                                                else echo "down";
                                        @endphp" draggable="false">
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route("producto.buscarproductos") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="orden" value="@if(strcmp(session("ordenProductos"),"MayorPrecio")==0) MenorPrecio @else MayorPrecio @endif" class="d-flex align-items-center justify-content-between dropdown-item px-1 @if(strcmp(session("ordenProductos"),"MenorPrecio")==0 || strcmp(session("ordenProductos"),"MayorPrecio")==0) selected @endif">
                                        <h6 class="p-0 m-0">Precio</h6>
                                        <img class="img-fluid" src="@php
                                        $img="build/assets/icons/";
                                            if(strcmp(session("ordenProductos"),"MenorPrecio")==0)$img.="up.svg";
                                            else $img.="down.svg";
                                        echo asset($img);
                                        @endphp" alt="@php
                                                if(strcmp(session("ordenProductos"),"MenorPrecio")==0)echo "up";
                                                else echo "down";
                                        @endphp" draggable="false">
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
        <div class="cuerpoProductos d-flex justify-content-center mx-3 mt-4">
            @if (isset($productos) && sizeof($productos)>0)
                <div class="col-10 @php if($productos->count()>=21) echo"gridProductos"; elseif($productos->count()<21 && $productos->count()>16) echo"gridProductos2"; elseif($productos->count()<16 && $productos->count()>12) echo"gridProductos3"; elseif($productos->count()<12 && $productos->count()>8) echo"gridProductos4"; elseif($productos->count()<=8 && $productos->count()>0) echo"gridProductos5"; @endphp">
                    @foreach ($productos as $producto)
                        <x-producto-component :producto="$producto"/>
                    @endforeach
                </div>
            @else
                <div class="h-40 d-flex align-items-start justify-content-center mt-5">
                    <h5 class="m-0 p-0 text-center">No se encontraron Productos.</h5>
                </div>
            @endif
        </div>
        <div class="pieProductos mx-3 py-3">
            @if ($productos->onLastPage() && $productos->onFirstPage())
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
                {{ $productos->links() }}
            @endif
        </div>
    </div>
    
@endsection

@section('footer')
@vite([
    "resources/js/app/productos.js",
    "resources/js/app/categorias.js"
])
<script>
    (()=>{
        const idCatego= @if (isset($idCatego)) @json($idCatego) @elseif(isset($categoBuscada)) @json($categoBuscada) @else @json(null) @endif;
        if(idCatego!=null){
            setTimeout(()=>{
                const categoSelected=document.querySelector("aside .categorias [data-id='"+idCatego+"']");
                if(categoSelected!=null){
                    categoSelected.classList.add("selected");
                    let fin=false;
                    let btnCatego=null;
                    if(categoSelected.classList.contains("txt-catego")){
                        btnCatego=categoSelected.closest(".collapse").previousElementSibling.querySelector("[data-bs-toggle='collapse']");
                    }else if(categoSelected.classList.contains("btn-catego")){
                        btnCatego=categoSelected.closest(".boton-catego").querySelector("[data-bs-toggle='collapse']");
                    }
                    if(btnCatego!=null){
                        btnCatego.click();
                        while(!fin){
                            if(btnCatego.closest(".catego").parentElement.classList.contains("collapse")){
                                btnCatego=btnCatego.closest(".catego").parentElement.previousElementSibling.querySelector("[data-bs-toggle='collapse']");
                                btnCatego.click();
                            }else{
                                fin=true;
                            }
                        }
                    }
                }
            },25);
        }
    })();

</script>

@endsection