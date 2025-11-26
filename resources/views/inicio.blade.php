@extends('layouts.index')

@section('head')
    @vite("resources/css/myStyles/inicio.css")
@endsection

@section("content")
    <x-portada-component/>
    <x-buscador-component type="activo"/>
    <x-mensaje-div-component/>

    <div class="new-btns mb-5">
        <div class="d-flex align-items-center justify-content-around my-4">
            <button id="btn-newProducto" class="newProducto p-1 ps-2" data-bs-toggle="modal" data-bs-target="#newProducto">
                <div class="d-flex align-items-center">
                    <img class="img-fluid" src="{{ asset('build/assets/icons/new_product.svg') }}" alt="nuevoProducto" draggable="false">
                    <p class="p-0 m-0 px-2">Nuevo Producto</p>
                </div>
            </button>
            <button id="btn-newProveedor" class="newProveedor p-1 ps-2" data-bs-toggle="modal" data-bs-target="#newProveedor">
                <div class="d-flex align-items-center">
                    <img class="img-fluid" src="{{ asset("build/assets/icons/supplier.svg") }}" alt="nuevoProveedor" draggable="false">
                    <p class="p-0 m-0 px-2">Nuevo Proveedor</p>
                </div>
            </button>
        </div>
        <div class="d-flex align-items-center justify-content-around my-4 @if(!session()->has("adminSet")) mt-5 @endif">
            <button id="btn-newCategoria" class="newCategoria p-1 ps-2" data-bs-toggle="modal" data-bs-target="#newCategoria">
                <div class="d-flex align-items-center">
                    <img class="img-fluid" src="{{ asset("build/assets/icons/new_category.svg") }}" alt="nuevaCategoria" draggable="false">
                    <p class="p-0 m-0 px-2">Nueva Categoria</p>
                </div>
            </button>
            @if(!session()->has("adminSet"))
            <button id="btn-editCategoria" class="editCategoria p-1 ps-2" data-bs-toggle="modal" data-bs-target="#editCategoria">
                <div class="d-flex align-items-center">
                    <img class="img-fluid" src="{{ asset("build/assets/icons/edit_category.svg") }}" alt="editarCategoria" draggable="false">
                    <p class="p-0 m-0 px-2">Editar Categoria</p>
                </div>
            </button>
            @endif
        </div>
        @if(session()->has("adminSet"))
        <div class="d-flex align-items-center justify-content-around my-4">
            <button id="btn-editCategoria" class="editCategoria p-1 ps-2" data-bs-toggle="modal" data-bs-target="#editCategoria">
                <div class="d-flex align-items-center">
                    <img class="img-fluid" src="{{ asset("build/assets/icons/edit_category.svg") }}" alt="editarCategoria" draggable="false">
                    <p class="p-0 m-0 px-2">Editar Categoria</p>
                </div>
            </button>
            <button id="btn-deleteCategoria" class="deleteCategoria p-1 ps-2" data-bs-toggle="modal" data-bs-target="#deleteCategoria">
                <div class="d-flex align-items-center">
                    <img class="img-fluid" src="{{ asset("build/assets/icons/delete_category.svg") }}" alt="eliminarCategoria" draggable="false">
                    <p class="p-0 m-0 px-2">Eliminar Categoria</p>
                </div>
            </button>
        </div>
        @endif
    </div>    

    <div class="modal fade" id="newProveedor" aria-hidden="true" aria-labelledby="newProveedorLabel" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="newProveedorLabel">Nuevo Proveedor</h1>
                    <div class="btn-close btn-closeNewProveedor p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                        <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                    </div>
                </div>
                <div class="modal-body py-3">
                    <form class="form-newProveedor" method="post" novalidate action="{{ route("proveedor.newprov") }}" id="formNewProv" autocomplete="off">
                        @csrf
                        <div class="d-flex flex-column align-items-center">
                            <div class="col-9">
                                <div class="col-md-12 mb-3">
                                    <label class="ps-1" for="nombre-newProv">Nombre del Proveedor:</label>
                                    <input type="text" class="form-control @if ($errors->any()) @if (in_array("nombre-newProv",$errors->keys())) invalid @endif @endif" id="nombre-newProv" name="nombre-newProv" placeholder="Nombre del Proveedor" value="{{ old("nombre-newProv") }}" required>
                                    <div class="invalid-feedback @if ($errors->any()) @if (in_array("nombre-newProv",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-nombre-newProv">
                                        @error('nombre-newProv')
                                            {{ str_replace("nombre-new prov","nombre",$message) }}
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="ps-1" for="direccion-newProv">Dirección del Proveedor:</label>
                                    <input type="text" class="form-control @if ($errors->any()) @if (in_array("direccion-newProv",$errors->keys())) invalid @endif @endif" id="direccion-newProv" name="direccion-newProv" placeholder="Dirección del Proveedor" value="{{ old("direccion-newProv") }}" required>
                                    <div class="invalid-feedback @if ($errors->any()) @if (in_array("direccion-newProv",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-direccion-newProv">
                                        @error('direccion-newProv')
                                            {{ str_replace("direccion-new prov","direccion",$message) }}
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="ps-1" for="correo-newProv">Correo del Proveedor:</label>
                                    <input type="email" class="form-control @if ($errors->any()) @if (in_array("correo-newProv",$errors->keys())) invalid @endif @endif" id="correo-newProv" name="correo-newProv" placeholder="Correo@Proveedor.com" value="{{ old("correo-newProv") }}" required>
                                    <div class="invalid-feedback @if ($errors->any()) @if (in_array("correo-newProv",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-correo-newProv">
                                        @error('correo-newProv')
                                            {{ str_replace("correo-new prov","correo",$message) }}
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="ps-1" for="telefono-newProv">Teléfono del Proveedor:</label>
                                    <input type="number" step="1" class="form-control @if ($errors->any()) @if (in_array("telefono-newProv",$errors->keys())) invalid @endif @endif" minlength="9" maxlength="14" id="telefono-newProv" name="telefono-newProv" placeholder="Teléfono del Proveedor" value="{{ old("telefono-newProv") }}" required>
                                    <div class="invalid-feedback @if ($errors->any()) @if (in_array("telefono-newProv",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-telefono-newProv">
                                        @error('telefono-newProv')
                                            {{ str_replace("telefono-new prov","telefono",$message) }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex align-items-center justify-content-between">
                    <input type="submit" id="btn-enviar-newProv" form="formNewProv" class="btn ms-3" value="Añadir proveedor"></input>
                    <div class="col-3 d-flex align-items-center justify-content-between mx-3">
                        <input type="reset" id="btn-limpiar-newProv" form="formNewProv" class="btn" value="Limpiar">
                        <button type="button" class="btn" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="newCategoria" aria-hidden="true" aria-labelledby="newCategoriaLabel" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="newCategoriaLabel">Nueva Categoria</h1>
                    <div class="btn-close btn-closeNewCategoria p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                        <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                    </div>
                </div>
                <div class="modal-body py-3">
                    <form class="form-newCategoria" novalidate method="post" action="{{ route("categoria.newcatego") }}" id="formNewCatego" autocomplete="off">
                        @csrf
                        <div class="d-flex flex-column align-items-center">
                            <div class="col-11">
                                <div class="col-md-12 mb-3">
                                    <label class="ps-1" for="nombre-newCatego">Nombre de la Categoría:</label>
                                    <input type="text" class="form-control @if ($errors->any()) @if (in_array("nombre-newCatego",$errors->keys())) invalid @endif @endif" id="nombre-newCatego" name="nombre-newCatego" placeholder="Nombre de la Categoria" value="{{ old("nombre-newCatego") }}" required>
                                    <div class="invalid-feedback @if ($errors->any()) @if (in_array("nombre-newCatego",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-nombre-newCatego">
                                        @error('nombre-newCatego')
                                            {{ str_replace("nombre-new catego","nombre",$message) }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-11 categorias-newCatego">
                                <label class="ps-1" for="categoria-newCatego">Posicione la nueva Categoría:</label>
                                <div class="categorias col-6 py-2 d-flex flex-column align-items-start justify-content-start">
                                    <x-categorias-component :categorias="$categorias" text="raiz" type="newCatego"/>
                                </div>
                                <input type="number" name="catego-id-newCatego" value="{{ old("catego-id-newCatego")}}" hidden>
                                <input type="text" class="form-control input-newCatego mt-2 @if ($errors->any()) @if (in_array("catego-id-newCatego",$errors->keys())) invalid @endif @endif" name="categoria-newCatego" id="categoria-newCatego" disabled>
                                <div class="invalid-feedback @if ($errors->any()) @if (in_array("catego-id-newCatego",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-categoria-newCatego">
                                    @error('catego-id-newCatego')
                                        {{ str_replace("catego-id-new catego","categoria",$message) }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex align-items-center justify-content-between">
                    <input type="submit" id="btn-enviar-newCatego" form="formNewCatego" class="btn ms-3" value="Añadir Categoria"></input>
                    <div class="col-3 d-flex align-items-center justify-content-between mx-3">
                        <input type="reset" id="btn-limpiar-newCatego" form="formNewCatego" class="btn" value="Limpiar"></input>
                        <button type="button" class="btn" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editCategoria" aria-hidden="true" aria-labelledby="editCategoriaLabel" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="editCategoriaLabel">Editar Categoria</h1>
                    <div class="btn-close btn-closeEditCategoria p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                        <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                    </div>
                </div>
                <div class="modal-body py-3">
                    <form class="form-editCategoria" novalidate method="post" action="{{ route("categoria.editcatego") }}" id="formEditCatego" autocomplete="off">
                        @csrf
                        <div class="d-flex flex-column align-items-center">
                            <div class="col-11">
                                <div class="col-md-12 mb-3">
                                    <label class="ps-1" for="nombre-editCatego">Nuevo nombre de la Categoría:</label>
                                    <input type="text" class="form-control @if ($errors->any()) @if (in_array("nombre-editCatego",$errors->keys())) invalid @endif @endif" id="nombre-editCatego" name="nombre-editCatego" placeholder="Nombre de la Categoria" value="{{ old("nombre-editCatego") }}" required>
                                    <div class="invalid-feedback @if ($errors->any()) @if (in_array("nombre-editCatego",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-nombre-editCatego">
                                        @error('nombre-editCatego')
                                            {{ str_replace("nombre-edit catego","nombre",$message) }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-11 categorias-editCatego">
                                <label class="ps-1" for="categoria-editCatego">Seleccione la categoría a editar:</label>
                                <div class="categorias col-6 py-2 d-flex flex-column align-items-start justify-content-start">
                                    <x-categorias-component :categorias="$categorias" text="" type="editCatego"/>
                                </div>
                                <input type="number" name="catego-id-editCatego" value="{{ old("catego-id-editCatego")}}" hidden>
                                <input type="text" class="form-control input-editCatego mt-2 @if ($errors->any()) @if (in_array("catego-id-editCatego",$errors->keys())) invalid @endif @endif" name="categoria-editCatego" id="categoria-editCatego" disabled>
                                <div class="invalid-feedback @if ($errors->any()) @if (in_array("catego-id-editCatego",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-categoria-editCatego">
                                    @error('catego-id-editCatego')
                                        {{ str_replace("catego-id-edit catego","categoria",$message) }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex align-items-center justify-content-between">
                    <input type="submit" id="btn-enviar-editCatego" form="formEditCatego" class="btn ms-3" value="Editar Categoria"></input>
                    <div class="col-3 d-flex align-items-center justify-content-between mx-3">
                        <input type="reset" id="btn-limpiar-editCatego" form="formEditCatego" class="btn" value="Limpiar"></input>
                        <button type="button" class="btn" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session()->has("adminSet"))
    <div class="modal fade" id="deleteCategoria" aria-hidden="true" aria-labelledby="deleteCategoriaLabel" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="deleteCategoriaLabel">Eliminar Categoria</h1>
                    <div class="btn-close btn-closeDeleteCategoria p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                        <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                    </div>
                </div>
                <div class="modal-body py-3">
                    <form class="form-deleteCategoria" novalidate method="post" action="{{ route("categoria.deletecatego") }}" id="formDeleteCatego" autocomplete="off">
                        @csrf
                        <h2 class="mt-2 mb-3 text-center">Ingrese su contraseña para proceder con la <span class="text-decoration-underline">Eliminación</span></h2>
                        <div class="col-12 d-flex flex-column align-items-center justify-content-center">
                            <input class="form-control @if ($errors->any()) @if (in_array("passAdmin-deleteCatego",$errors->keys())) invalid @endif @endif" type="password" id="passAdmin-deleteCatego" name="passAdmin-deleteCatego" value="" placeholder="Su contraseña">
                            <div class="invalid-feedback fs-4 text-center @if ($errors->any()) @if (in_array("passAdmin-deleteCatego",$errors->keys())) is-invalid @endif @endif position-relative" name="invalid-passAdmin" id="invalid-passAdmin-deleteCatego">
                                @error('passAdmin-deleteCatego')
                                    @if (str_contains($message,"obligatorio"))
                                    <p class="p-0 m-0">La contraseña es obligatoria.</p>
                                    @elseif (str_contains($message,"Formato"))
                                    <p class="p-0 m-0">{{ str_replace("pass admin-delete prod","contraseña",$message) }}</p>
                                    <p class="p-0 m-0 fs-5">Ingrese al menos una Mayúscula, un número y un carácter especial.<span class="ms-1">Caracteres</span></p>
                                    <div name="validEspecialsPass" id="validEspecialsPass-deleteCatego" class="validEspecials m-1 py-1 px-2 position-absolute d-none">
                                        <p class="p-0 m-0"># @ | $ % & - _ ¡ ! ¿ ?</p>
                                    </div>
                                    @elseif(str_contains($message,"incorrecta"))
                                    <p class="p-0 m-0">{{ str_replace("pass admin-delete catego","contraseña",$message) }}</p>
                                    @else
                                    <p class="p-0 m-0">{{ "La ".str_replace("pass admin-delete catego","contraseña",$message) }}</p>
                                    @endif
                                @enderror
                            </div>
                        </div>
                        <div class="mt-3 mb-2 d-flex flex-column align-items-center justify-content-center">
                            <h3 class="col-12 text-center m-0 mb-1 p-0">Seleccione la categoría a eliminar:</h3>
                            <div class="col-9 categorias-deleteCatego">
                                <div class="categorias col-12 py-2 d-flex flex-column align-items-start justify-content-start">
                                    <x-categorias-component :categorias="$categorias" text="" type="deleteCatego"/>
                                </div>
                                <input type="number" name="catego-id-deleteCatego" value="{{ old("catego-id-deleteCatego")}}" hidden>
                                <input type="text" class="form-control input-deleteCatego mt-2 @if ($errors->any()) @if (in_array("catego-id-deleteCatego",$errors->keys())) invalid @endif @endif" name="categoria-deleteCatego" id="categoria-deleteCatego" disabled>
                                <div class="invalid-feedback @if ($errors->any()) @if (in_array("catego-id-deleteCatego",$errors->keys())) is-invalid @endif @endif" id="invalid-categoria-deleteCatego">
                                    @error('catego-id-deleteCatego')
                                        {{ str_replace("catego-id-delete catego","categoria",$message) }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex align-items-center justify-content-between">
                    <input type="submit" id="btn-enviar-deleteCatego" name="eliminarCatego" form="formDeleteCatego" class="btn ms-3" value="Eliminar Categoria"></input>
                    <button type="button" class="btn mx-3" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="modal fade" id="newProducto" aria-hidden="true" aria-labelledby="newProductoLabel" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="newProductoLabel">Nuevo Producto</h1>
                    <div class="btn-close btn-closeNewProducto p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                        <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                    </div>
                </div>
                <div class="modal-body">
                    <form class="form-newProducto" novalidate method="post" action="{{ route("producto.newprod") }}" id="formNewProd" autocomplete="off" >
                        @csrf
                        <div class="d-flex flex-column align-items-center">
                            <div class="col-md-11 mb-3">
                                <label class="ps-1 form-label" for="nombre-newProd">Nombre del Producto:</label>
                                <input type="text" class="form-control @if ($errors->any()) @if (in_array("nombre-newProd",$errors->keys())) invalid @endif @endif" id="nombre-newProd" name="nombre-newProd" placeholder="Nombre del Producto" value="{{ old("nombre-newProd") }}">
                                <div class="invalid-feedback @if ($errors->any()) @if (in_array("nombre-newProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-nombre-newProd">
                                    @error('nombre-newProd')
                                        {{ str_replace("nombre-new prod","nombre",$message) }}
                                    @enderror
                                </div>
                            </div>
                            <div class="col-11 mb-3">
                                <label class="ps-1 form-label" for="descripcion-newProd">Descripción del Producto:</label>
                                <textarea style="height: 120px; max-height:120px" class="form-control @if ($errors->any()) @if (in_array("descripcion-newProd",$errors->keys())) invalid @endif @endif" id="descripcion-newProd" name="descripcion-newProd" placeholder="Descripcion del Producto" required>{{ old("descripcion-newProd") }}</textarea>
                                <div class="invalid-feedback @if ($errors->any()) @if (in_array("descripcion-newProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-descripcion-newProd">
                                    @error('descripcion-newProd')
                                        {{ str_replace("descripcion-new prod","descripcion",$message) }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-2 d-flex justify-content-center">
                            <div class="col-6">
                                <div class="d-flex">
                                    <div class="col-md-6 d-flex flex-column align-items-top precios">
                                        <div class="col-md-12  mb-2">
                                            <div class="col-11">
                                                <label class="ps-1 form-label" for="precioVenta-newProd">Precio de Venta:</label>
                                                <div class="d-flex align-items-center">
                                                    <img class="img-fluid" src="{{ asset("build/assets/icons/money.svg") }}" alt="$" draggable="false">
                                                    <input type="number" step="0.01" maxlength="9" min="0.01" class="form-control @if ($errors->any()) @if (in_array("precioVenta-newProd",$errors->keys())) invalid @endif @endif" id="precioVenta-newProd" name="precioVenta-newProd" placeholder="0,00" value="{{ old("precioVenta-newProd") }}">
                                                </div>
                                                <div class="invalid-feedback @if ($errors->any()) @if (in_array("precioVenta-newProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-precioVenta-newProd">
                                                    @error('precioVenta-newProd')
                                                        {{ str_replace("precio venta-new prod","precio de venta",$message) }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <div class="col-md-11">
                                                <label for="cantDispo-newProd" class="ps-1 form-label">Cant. Disponible:</label>
                                                <input type="number" step="0.01" max="999999999.99" min="0"  class="form-control @if ($errors->any()) @if (in_array("cantDispo-newProd",$errors->keys())) invalid @endif @endif" name="cantDispo-newProd" id="cantDispo-newProd" placeholder="1" value="{{ old("cantDispo-newProd") }}">
                                                <div class="invalid-feedback @if ($errors->any()) @if (in_array("cantDispo-newProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-cantDispo-newProd">
                                                    @error('cantDispo-newProd')
                                                        {{ str_replace("cant dispo-new prod","candidad disponible",$message) }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-12 mb-2">
                                            <div class="col-md-11">
                                                <label class="ps-1 form-label" for="codigo-newProd">Codigo del Producto:</label>
                                                <input type="text" class="form-control @if ($errors->any()) @if (in_array("codigo-newProd",$errors->keys())) invalid @endif @endif" id="codigo-newProd" name="codigo-newProd" placeholder="Ej: 87HweF2 (Opcional)" value="{{ old("codigo-newProd") }}">
                                                <div class="invalid-feedback @if ($errors->any()) @if (in_array("codigo-newProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-codigo-newProd">
                                                    @error('codigo-newProd')
                                                        {{ str_replace("codigo-new prod","codigo",$message) }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <div class="col-md-11">
                                                <label for="cantMinima-newProd" class="ps-1 form-label">Alertar cant. mínima:</label>
                                                <input type="number" step="1" max="50" min="5" title="Alertar stock menor o igual al indicado." class="form-control @if ($errors->any()) @if (in_array("cantMinima-newProd",$errors->keys())) invalid @endif @endif" name="cantMinima-newProd" id="cantMinima-newProd" placeholder="1" value="{{ old("cantMinima-newProd") }}">
                                                <div class="invalid-feedback @if ($errors->any()) @if (in_array("cantMinima-newProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-cantMinima-newProd">
                                                    @error('cantMinima-newProd')
                                                        {{ str_replace("cant minima-new prod","candidad minima",$message) }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 d-flex justify-content-between">
                                    <div class="col-8 categorias-newProd">
                                        <label class="ps-1 form-label" for="categoria-newProd">Seleccione la categoria del Producto:</label>
                                        <div class="categorias py-2 d-flex flex-column align-items-start justify-content-start form-control">
                                            <x-categorias-component :categorias="$categorias" text="" type="newProd"/>
                                        </div>
                                        <input type="number" name="catego-id-newProd" value="{{ old("catego-id-newProd") }}" hidden required>
                                        <input type="text" class="input-categoProd mt-2 form-control @if ($errors->any()) @if (in_array("catego-id-newProd",$errors->keys())) invalid @endif @endif" name="categoria-newProd" id="categoria-newProd" disabled required>
                                        <div class="invalid-feedback @if ($errors->any()) @if (in_array("catego-id-newProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-categoria-newProd">
                                            @error('catego-id-newProd')
                                                {{ str_replace("catego-id-new prod","categoria",$message) }}
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-4 d-flex flex-column align-items-start">
                                        <div class="col-11 mb-2 pe-2">
                                            <label class="ps-1 form-label">Proveedor:</label>
                                            <button id="btn-proveedores-newProd" class="col-10 btn d-flex align-items-center justify-content-between form-control @if ($errors->any()) @if (in_array("proveedor-newProd",$errors->keys())) invalid @endif @endif" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-proveedores-newProd" aria-expanded="false">
                                                Proveedores
                                                <img class="ms-2 img-fluid" src="{{ asset("build/assets/icons/down.svg") }}" alt="down" draggable="false">
                                            </button>
                                            <div class="collapse" id="collapse-proveedores-newProd">
                                                <div class="card card-body col-12 p-2">
                                                    @if (isset($proveedores))
                                                    @foreach ($proveedores as $proveedor)
                                                        <div title="{{ $proveedor->nombre }}" class="d-flex align-items-center justify-content-start ps-2 col-12">
                                                            <input class="me-2" type="checkbox" id="proveedor-newProd_{{ $proveedor->id }}" name="proveedor-newProd_{{ $proveedor->id }}" value="{{ $proveedor->id }}" @if (old("proveedor-newProd_".$proveedor->id)) checked @endif>
                                                            <label class="p-1" for="proveedor-newProd_{{ $proveedor->id }}">{{ strlen($proveedor->nombre)>30 ? mb_substr($proveedor->nombre,0,30)."..." : $proveedor->nombre}}</label>
                                                        </div>
                                                    @endforeach
                                                @endif
                                                </div>
                                            </div>
                                            <div class="invalid-feedback @error('proveedor-newProd') is-invalid @enderror" id="invalid-proveedor-newProd">
                                                @error('proveedor-newProd')
                                                    {{ str_replace("proveedor-new prod","proveedor",$message) }}
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-11 pe-2">
                                            <label for="medida-newProd" class="ps-1 form-label">Tipo de Medida:</label>
                                            <select class="dropdown-toggle form-control @if ($errors->any()) @if (in_array("medida-newProd",$errors->keys())) invalid @endif @endif" name="medida-newProd" id="medida-newProd">
                                                <option value="{{ null }}" disabled {{ old("medida-newProd") ? "" : "selected"}} hidden>Medida</option>
                                                <option value="Unidad" {{ old("medida-newProd") == "Unidad" ? "selected" : ""}}>Unidad</option>
                                                <option value="Kilogramo" {{ old("medida-newProd") == "Kilogramo" ? "selected" : ""}}>Kilogramo</option>
                                                <option value="Litro" {{ old("medida-newProd") == "Litro" ? "selected" : ""}}>Litro</option>
                                                <option value="Metro" {{ old("medida-newProd") == "Metro" ? "selected" : ""}}>Metro</option>
                                            </select>
                                            <div class="invalid-feedback @if ($errors->any()) @if (in_array("medida-newProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-medida-newProd">
                                                @error('medida-newProd')
                                                    {{ str_replace("medida-new prod","medida",$message) }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="div-fotos-newProd" class="fotos-newProd col-5 d-flex flex-column justify-content-center align-items-center">
                                <div class="col-11 drop-area d-flex align-items-center justify-content-center flex-column form-control @if ($errors->any()) @if (in_array("fotos-newProd",$errors->keys())) invalid @endif @endif">
                                    <h2 class="form-label">Arrastra y suelta imágenes aquí</h2>
                                    <span>O</span>
                                    <button class="px-2 py-1 mt-2">Buscar imágenes</button>
                                    <input type="file" accept="image/png, image/jpeg, image/jpg" class="inputFotos" hidden multiple>
                                    <select class="form-control" name="fotos-newProd[]" id="fotos-newProd" multiple hidden required>
                                        @php $oldFotos=["fotos"=>old("fotos-newProd"),"data-foto"=>[]];
                                            if(!empty($oldFotos["fotos"])){
                                                if(is_array($oldFotos["fotos"])){
                                                    foreach($oldFotos["fotos"] as $foto){
                                                        $idOldFoto=bin2hex(random_bytes(length: 3));
                                                        $oldFotos["data-foto"][]=$idOldFoto;
                                                        echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                    }
                                                }else{
                                                    $idOldFoto=random_bytes(length: 7);
                                                    $oldFotos["data-foto"][]=$idOldFoto;
                                                    echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                }
                                            }
                                        @endphp
                                    </select>
                                    <div class="text-center mt-3 fs-5 invalid-feedback @error('fotos-newProd') is-invalid @enderror" id="invalid-fotos-newProd">
                                        @error('fotos-newProd')
                                            @if (str_contains($message,"obligatorio"))
                                                {{ "Ingrese al menos una foto del producto." }}
                                            @else
                                                {{ str_replace("fotos-new prod","fotos",$message) }}
                                            @endif
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-11 previewFotos d-flex p-0 ps-2 mt-3 @php if(!empty($oldFotos["data-foto"]) && sizeof($oldFotos["data-foto"])>3)echo "masTres"; @endphp" id="preview-fotos-newProd">
                                    @for ($i=0;$i<sizeof($oldFotos["data-foto"]);$i++)
                                        <img id="{{ $oldFotos["data-foto"][$i] }}" class="img-fluid my-2 me-2" src="{{ $oldFotos["fotos"][$i] }}" alt="{{ $oldFotos["data-foto"][$i] }}" title="Eliminar" draggable="false">
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex align-items-center justify-content-between">
                    <input type="submit" id="btn-enviar-newProd" form="formNewProd" class="btn ms-3" value="Añadir producto"></input>
                    <div class="col-3 d-flex align-items-center justify-content-between mx-3">
                        <input type="reset" id="btn-limpiar-newProd" form="formNewProd" class="btn" value="Limpiar">
                        <button type="button" class="btn" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
<script>
    (()=>{
        window.addEventListener("load",()=>{
            function oldCatego(oldCategoId){
                if(oldCategoId.value.trim()!==""){
                    const catego=oldCategoId.previousElementSibling.querySelector("[data-id='"+oldCategoId.value+"']");
                    if(catego!=null){
                        catego.click();
                        oldCategoId.nextElementSibling.classList.add("valid");
                        oldCategoId.nextElementSibling.nextElementSibling.classList.add("is-valid");
                        oldCategoId.nextElementSibling.classList.remove("invalid");
                        oldCategoId.nextElementSibling.nextElementSibling.classList.remove("is-invalid");
                    }else{
                        oldCategoId.nextElementSibling.classList.add("invalid");
                        oldCategoId.nextElementSibling.nextElementSibling.classList.add("is-invalid");
                        oldCategoId.nextElementSibling.classList.remove("valid");
                        oldCategoId.nextElementSibling.nextElementSibling.classList.remove("is-valid");
                    }
                }else{
                    oldCategoId.nextElementSibling.classList.add("invalid");
                    oldCategoId.nextElementSibling.nextElementSibling.classList.add("is-invalid");
                }
            }
            var invalidNewProd=@php
                if($errors->any()){
                    $newProdKeys=["nombre-newProd","descripcion-newProd","precioCompra-newProd","precioVenta-newProd","proveedor-newProd","codigo-newProd","medida-newProd","cantDispo-newProd","catego-id-newProd","fotos-newProd"];
                    foreach($newProdKeys as $key){
                        if(in_array($key,$errors->keys())){
                            echo json_encode(1);break;
                        }
                    }
                    echo json_encode(0);
                }else{
                    echo json_encode(0);
                }
            @endphp;
            if(invalidNewProd){
                const categoNewProd=document.querySelector("[name=catego-id-newProd]");
                oldCatego(categoNewProd);
                const previewFotos=document.querySelector("#formNewProd .previewFotos");
                if(previewFotos.childElementCount>0){
                    for (let foto of previewFotos.children) {
                        foto.addEventListener("click", () => deleteFoto(foto));
                    }
                }
                setTimeout(()=>{
                    document.querySelector("#btn-newProducto").click();
                },50);
            }
            var invalidNewProv=@if($errors->any())
                                    @php
                                        $newProvKeys=["nombre-newProv","direccion-newProv","correo-newProv","telefono-newProv"];
                                    @endphp
                                    @foreach($newProvKeys as $key)
                                        @if(in_array($key,$errors->keys()))
                                            @json(1)@break
                                        @endif
                                    @endforeach
                                    @json(0)
                                @else
                                    @json(0)
                                @endif;
            if(invalidNewProv){
                setTimeout(()=>{
                    document.querySelector("#btn-newProveedor").click();
                },50);
            }
            var invalidNewCatego=@php
                if($errors->any()){
                    $newCategoKeys=["nombre-newCatego","catego-id-newCatego"];
                    foreach($newCategoKeys as $key){
                        if(in_array($key,$errors->keys())){
                            echo json_encode(1);break;
                        }
                    }
                    echo json_encode(0);
                }else{
                    echo json_encode(0);
                }
            @endphp;
            if(invalidNewCatego){
                const categoNewCatego=document.querySelector("[name=catego-id-newCatego]");
                oldCatego(categoNewCatego);
                setTimeout(()=>{
                    document.querySelector("#btn-newCategoria").click();
                },50)
            }

            var invalidEditCatego=@php
                if($errors->any()){
                    $editProdKeys=["nombre-editCatego","catego-id-editCatego"];
                    foreach($editProdKeys as $key){
                        if(in_array($key,$errors->keys())){
                            echo json_encode(1);break;
                        }
                    }
                    echo json_encode(0);
                }else{
                    echo json_encode(0);
                }
            @endphp;
            if(invalidEditCatego){
                const categoEditCatego=document.querySelector("[name=catego-id-editCatego]");
                oldCatego(categoEditCatego);
                setTimeout(()=>{
                    document.querySelector("#btn-editCategoria").click();
                },50)
            }

            @if(session()->has("adminSet"))
                var invalidDeleteCatego=@php
                    if($errors->any()){
                        $deleteCategoKeys=["passAdmin-deleteCatego","nombre-deleteCatego","catego-id-deleteCatego"];
                        foreach($deleteCategoKeys as $key){
                            if(in_array($key,$errors->keys())){
                                echo json_encode(1);break;
                            }
                        }
                        echo json_encode(0);
                    }else{
                        echo json_encode(0);
                    }
                @endphp;
                if(invalidDeleteCatego){
                    const categoDeleteCatego=document.querySelector("[name=catego-id-deleteCatego]");
                    oldCatego(categoDeleteCatego);
                    setTimeout(()=>{
                        document.querySelector("#btn-deleteCategoria").click();
                    },50)
                }
            @endif
        });

        function deleteFoto(foto){
            const optFoto = foto.parentElement.previousElementSibling.querySelector("option[data-foto='"+foto.id+"']");
            const select = foto.parentElement;
            optFoto.remove();
            foto.remove();
            if(select.childElementCount<4){
                select.classList.remove("masTres");
            }
        }
    })();
</script>
@vite([
    "resources/js/app/inicio.js",
    "resources/js/app/dropImgs.js",
    "resources/js/app/categorias.js"
])
@endsection