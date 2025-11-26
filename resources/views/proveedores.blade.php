@extends('layouts.index')

@section('head')
    @vite([
        "resources/css/myStyles/inicio.css",
        "resources/css/myStyles/proveedores.css"
    ])
@endsection

@section("content")
    <x-portada-component/>
    <x-buscador-component type="activo"/>
    <x-mensaje-div-component/>
    
    <div class="proveedores mx-2 mb-2 pb-3">
        <div class="cabeceraProveedores p-3">
            <div class="detalleRes d-flex align-items-center justify-content-between p-2">
                <p class="p-0 m-0">Todo</p>
                @if ($proveedores->onLastPage() && $proveedores->onFirstPage())
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center contenedor-resultados">
                        <div class="txt-resultados">
                            <p class="text-sm text-black leading-5 dark:text-black">
                                Resultados:
                                <span class="font-medium">1</span>
                                -
                                <span class="font-medium">{{ sizeof($proveedores) }}</span>
                                de
                                <span class="font-medium">{{ sizeof($proveedores) }}</span>
                            </p>
                        </div>
                    </div>
                @else
                    {{ $proveedores->links() }}
                @endif
            </div>
            <div class="detalleRes d-flex align-items-center justify-content-between p-2 ps-4">
                <h4>Proveedores</h4>
                <div class="d-flex align-items-center justify-content-end">
                    <div class="dropdown me-3">
                        <button class="btn dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <p class="p-0 m-0">@php
                                switch(session("ordenProveedores")){
                                    case "NombreAZ": echo "Nombre"; break;
                                    case "NombreZA": echo "Nombre"; break;
                                    case "MasReciente": echo "Compras"; break;
                                    case "MenosReciente": echo "Compras"; break;
                                    default: echo "Nombre";
                                }
                            @endphp</p>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <form action="{{ route("proveedor.orden") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="orden" value="@if(strcmp(session("ordenProveedores"),"NombreAZ")==0) NombreZA @else NombreAZ @endif" class="d-flex align-items-center justify-content-between dropdown-item px-1 @if(strcmp(session("ordenProveedores"),"NombreAZ")==0 || strcmp(session("ordenProveedores"),"NombreZA")==0) selected @endif">
                                        <h6 class="p-0 m-0">Nombre</h6>
                                        <img class="img-fluid" src="@php
                                        $img="build/assets/icons/";
                                            if(strcmp(session("ordenProveedores"),"NombreAZ")==0)$img.="up.svg";
                                            else $img.="down.svg";
                                        echo asset($img);
                                        @endphp" alt="@php
                                                if(strcmp(session("ordenProveedores"),"NombreAZ")==0)echo "up";
                                                else echo "down";
                                        @endphp" draggable="false">
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route("proveedor.orden") }}" method="POST" class="col-12 px-2 d-flex align-items-center justify-content-center">
                                    @csrf
                                    <button type="submit" name="orden" value="@if(strcmp(session("ordenProveedores"),"MasReciente")==0) MenosReciente @else MasReciente @endif" class="d-flex align-items-center justify-content-between dropdown-item px-1 @if(strcmp(session("ordenProveedores"),"MasReciente")==0 || strcmp(session("ordenProveedores"),"MenosReciente")==0) selected @endif">
                                        <h6 class="p-0 m-0">Compras</h6>
                                        <img class="img-fluid" src="@php
                                        $img="build/assets/icons/";
                                            if(strcmp(session("ordenProveedores"),"MasReciente")==0)$img.="up.svg";
                                            else $img.="down.svg";
                                        echo asset($img);
                                        @endphp" alt="@php
                                                if(strcmp(session("ordenProveedores"),"MasReciente")==0)echo "up";
                                                else echo "down";
                                        @endphp" draggable="false">
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <div></div>
                </div>
            </div>
        </div>
        <div class="cuerpoProveedores d-flex align-items-center justify-content-center mx-3">
            @if (isset($proveedores) && sizeof($proveedores)>0)
                <div class="col-10 listProveedores d-flex flex-column align-items-center justify-content-center">
                    @foreach ($proveedores as $proveedor)
                        <x-proveedor-component :proveedor="$proveedor"/>
                    @endforeach
                </div>
            @else
            <div class="m-5">
                <h3 class="m-0 p-0 text-center">No se encontraron Proveedores.</h3>
            </div>
            @endif
        </div>
        <div class="pieProveedores mx-3 py-3">
            @if ($proveedores->onLastPage() && $proveedores->onFirstPage())
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
                {{ $proveedores->links() }}
            @endif
        </div>
    </div>
    
@endsection

@section('footer')
@vite([
    "resources/js/app/proveedor.js",
    "resources/js/app/categorias.js"
])
@endsection