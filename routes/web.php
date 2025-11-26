<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CompraController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\VentaController;

Route::get('/', [HomeController::class,"index"])
->name("inicio.index");
Route::post('/', [HomeController::class,"cleanIndex"])
->name("inicio.inicio");
Route::post('/finadmin', [HomeController::class,"cerrarSession"])
->name("inicio.finadmin");
Route::post('/admin', [HomeController::class,"sessionAdmin"])
->name("inicio.admin");

Route::post('categoria/new-categoria', [CategoriaController::class,"newCategoria"])
->name("categoria.newcatego");
Route::post('categoria/edit-categoria', [CategoriaController::class,"editCategoria"])
->name("categoria.editcatego");
Route::post('categoria/delete-categoria', [CategoriaController::class,"deleteCategoria"])
->name("categoria.deletecatego");


Route::get('/productos', [ProductoController::class,"index"])
->name("producto.index");
Route::post('/productos', [ProductoController::class,"cleanIndex"])
->name("producto.inicio");
Route::post('/productos/newprod', [ProductoController::class,"newProducto"])
->name("producto.newprod");
Route::post('/producto/editprod', [ProductoController::class,"editProducto"])
->name("producto.editprod");
Route::post('/productos/deleteprod', [ProductoController::class,"deleteProducto"])
->name("producto.deleteprod");

Route::post('/productos/buscarproductos', [ProductoController::class,"buscarProducto"])
->name("producto.buscarproductos");
Route::get('/productos/buscar', [ProductoController::class,"buscarProd"])
->name("producto.buscar");

Route::post('/productos/categos', [ProductoController::class,"mostrarProductosCatego"])
->name("producto.categoria");

Route::get('/productos-todos', [ProductoController::class,"todos"])
->name("producto.todos");
Route::post('/productos-todos', [ProductoController::class,"cleanTodos"])
->name("producto.todosinicio");

Route::post('/producto/addtocart', [ProductoController::class,"addToCart"])
->name("producto.addtocart");
Route::post('/producto/refreshcart', [ProductoController::class,"refreshCart"])
->name("producto.refreshcart");
Route::post('/producto/refreshallcart', [ProductoController::class,"refreshAllCart"])
->name("producto.refreshallcart");
Route::post('/producto/deleteofcart', [ProductoController::class,"deleteOfCart"])
->name("producto.deleteofcart");

Route::get('/producto/{producto}', [ProductoController::class,"mostrarProducto"])
->name("producto.producto");


Route::get('/proveedores', [ProveedorController::class,"index"])
->name("proveedor.index");
Route::post('/proveedores', [ProveedorController::class,"cleanIndex"])
->name("proveedor.inicio");

Route::post('/proveedor/new-proveedor', [ProveedorController::class,"newProveedor"])
->name("proveedor.newprov");
Route::post('/proveedor/edit-proveedor', [ProveedorController::class,"editProveedor"])
->name("proveedor.editprov");
Route::post('/proveedor/delete-proveedor', [ProveedorController::class,"deleteProveedor"])
->name("proveedor.deleteprov");

Route::post('/proveedores/orden', [ProveedorController::class,"ordenarProveedores"])
->name("proveedor.orden");

Route::get('/proveedor/{proveedor}', [ProveedorController::class,"mostrarProveedor"])
->name("proveedor.proveedor");

Route::get('/ventas',[VentaController::class, "index"])
->name("venta.index");
Route::post('/ventas',[VentaController::class, "cleanIndex"])
->name("venta.inicio");

Route::get('/ventas/procesarventa',[VentaController::class, "procesarVenta"])
->name("venta.procesarventa");
Route::post('/ventas/saveventa',[VentaController::class, "saveVenta"])
->name("venta.saveventa");
Route::post('/ventas/limpiarcarrito',[VentaController::class, "limpiarCarrito"])
->name("venta.limpiarcarrito");
Route::post('/ventas/editarventa',[VentaController::class, "editarVenta"])
->name("venta.editarventa");
Route::post('/ventas/devolucionventa',[VentaController::class, "devolucionVenta"])
->name("venta.devolucionventa");
Route::post('/ventas/setentrega',[VentaController::class, "setEntrega"])
->name("venta.setentrega");
Route::post('/ventas/descartarmodventa',[VentaController::class, "descartarModVenta"])
->name("venta.descartarmodventa");
Route::post('/ventas/savemodificacionventa',[VentaController::class, "saveModificacionVenta"])
->name("venta.savemodificacionventa");
Route::post('/ventas/descartardevventa',[VentaController::class, "descartarDevVenta"])
->name("venta.descartardevventa");
Route::post('/ventas/savedevolucionventa',[VentaController::class, "saveDevolucionVenta"])
->name("venta.savedevolucionventa");
Route::post('/ventas/devoluciones',[VentaController::class, "ventaDevoluciones"])
->name("venta.devoluciones");
Route::get('/ventas/devolucion/{devolucion}',[VentaController::class, "showDevVenta"])
->name("venta.devolucion");
Route::post('/ventas/delete',[VentaController::class, "deleteVenta"])
->name("venta.deleteventa");
Route::post('/ventas/trueeditventa',[VentaController::class, "trueEditVenta"])
->name("venta.trueeditventa");
Route::post('/ventas/deshacer',[VentaController::class, "deshacerVenta"])
->name("venta.deshacerventa");

Route::post('/ventas/totalesventas',[VentaController::class, "getTotalesVentas"])
->name("venta.totalesventas");
Route::post('/ventas/buscar',[VentaController::class, "buscarVenta"])
->name("venta.buscar");
Route::post('/ventas/orden',[VentaController::class, "setOrdenVentas"])
->name("venta.orden");

Route::post('/ventas/getmorependientes',[VentaController::class, "getMorePendientes"])
->name("venta.getmorependientes");

Route::get('/venta/{venta}',[VentaController::class, "mostrarVenta"])
->name("venta.venta");

Route::get('/compras',[CompraController::class, "index"])
->name("compra.index");
Route::post('/compras',[CompraController::class, "cleanIndex"])
->name("compra.inicio");
Route::get('/compras',[CompraController::class, "index"])
->name("compra.index");

Route::get('/compras/procesarcompra',[CompraController::class, "procesarCompra"])
->name("compra.procesarcompra");

Route::post('/compras/newcompra',[CompraController::class, "newCompra"])
->name("compra.newcompra");
Route::post('/compras/editprodcompra',[CompraController::class, "editProdCompra"])
->name("compra.editprodcompra");
Route::post('/compras/editar',[CompraController::class, "editarCompra"])
->name("compra.editarcompra");
Route::post('/compras/deletecompra',[CompraController::class, "deleteCompra"])
->name("compra.deletecompra");
Route::post('/compras/deleteprodcompra',[CompraController::class, "deleteProdCompra"])
->name("compra.deleteprodcompra");
Route::post('/compras/savecompra',[CompraController::class, "saveCompra"])
->name("compra.savecompra");
Route::post('/compras/saveeditcompra',[CompraController::class, "saveEditCompra"])
->name("compra.saveeditcompra");
Route::post('/compras/descartareditcompra',[CompraController::class, "descartarEditCompra"])
->name("compra.descartareditcompra");
Route::post('/compras/getprodscompra',[CompraController::class, "getOldProdsCompra"])
->name("compra.getprodscompra");
Route::post('/compras/revertircompra',[CompraController::class, "revertirCompra"])
->name("compra.revertircompra");

Route::post('/compras/totalescompras',[CompraController::class, "getTotalesCompras"])
->name("compra.totalescompras");
Route::post('/compras/buscar',[CompraController::class, "buscarCompra"])
->name("compra.buscar");
Route::post('/compras/orden',[CompraController::class, "setOrdenCompras"])
->name("compra.orden");

Route::get('/compra/{compra}',[CompraController::class, "mostrarCompra"])
->name("compra.compra");

//Route::post('/inicio/update',[HomeController::class, "update"])
//->name("inicio.update");


Route::get('.well-known/{any}', function () {
    abort(404);
})->where('any', '.*');

Route::get('favicon.ico', function () {
    abort(404);
});

Route::get('robots.txt', function () {
    return "User-agent: *\nDisallow: /";
});

Route::fallback([HomeController::class, 'handleNotFound']);
