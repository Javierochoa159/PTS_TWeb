@props(["type"])

<div class="col-12 buscadorInicio d-flex justify-content-center mb-5">
    <div class="col-8 p-3">
        <form action="{{ route("producto.buscarproductos") }}" class="d-flex" id="formBuscar" role="search" method="post">
            @csrf
            <input type="hidden" name="tipo" value="buscar">
            <input class="form-control" type="text" name="prod" placeholder="Nombre/Código del producto" aria-label="Nombre/Código del producto">
            <input class="btnBuscar" form="formBuscar" type="submit" value=""></input>
        </form>
    </div>
</div>