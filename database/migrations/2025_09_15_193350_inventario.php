<?php

use App\Models\Categoria;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->foreignId("padre")->nullable()->references("id")->on("categorias")->constrained("fk_categorias_padre")->onDelete("cascade");
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('direccion');
            $table->string('correo');
            $table->string('telefono');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->longText('descripcion');
            $table->string('codigo')->nullable();
            $table->double('precio_venta');
            $table->set("tipo_medida",['Unidad', 'Kilogramo', 'Metro', 'Litro']);
            $table->double('cantidad_disponible');
            $table->double('cantidad_minima');
            $table->foreignId("categoria")->references("id")->on("categorias")->constrained("fk_productos_categoria")->onDelete("cascade");
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('fotos', function (Blueprint $table) {
            $table->id();
            $table->longText('url_img')->nullable();
            $table->longText('url_img_online');
            $table->foreignId("producto")->references("id")->on("productos")->constrained("fk_fotos_producto")->onDelete("cascade");
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('proveedores_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId("proveedor")->references("id")->on("proveedores")->constrained("fk_proveedores_productos_proveedor")->onDelete("cascade");
            $table->foreignId("producto")->references("id")->on("productos")->constrained("fk_proveedores_productos_producto")->onDelete("cascade");
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->double('monto_total');
            $table->set("tipo_pago",["Tarjeta", "Efectivo", "Mixto"]);
            $table->foreignId("proveedor")->references("id")->on("proveedores")->constrained("fk_compras_proveedor")->onDelete("cascade");
            $table->timestamp('fecha_compra')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
            $table->boolean("compra_deshecha")->default(false);
        });
        Schema::create('productos_compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId("producto")->references("id")->on("productos")->constrained("fk_productos_compras_producto")->onDelete("cascade");
            $table->foreignId("compra")->references("id")->on("compras")->constrained("fk_productos_compras_compra")->onDelete("cascade");
            $table->double('cantidad');
            $table->double('precio_compra');
            $table->double('total_producto');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('recibos_compras', function (Blueprint $table) {
            $table->id();
            $table->longText('url_img')->nullable();
            $table->longText('url_img_online');
            $table->foreignId("compra")->references("id")->on("compras")->constrained("fk_recibos_compras_compra")->onDelete("cascade");
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->set("tipo_pago",["Tarjeta", "Efectivo", "Mixto","Pendiente"]);
            $table->set("tipo_venta",["Envio", "Local"]);
            $table->double('monto_subtotal');
            $table->double('monto_total');
            $table->string('nombre_receptor')->nullable();
            $table->string('telefono_receptor')->nullable();
            $table->set("estado_entrega",["Pendiente", "Viajando", "Completa"])->default("Pendiente");
            $table->timestamp('fecha_venta')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
            $table->boolean("venta_invalida")->default(false);
        });
        Schema::create('productos_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId("producto")->references("id")->on("productos")->constrained("fk_productos_ventas_producto")->onDelete("cascade");
            $table->foreignId("venta")->references("id")->on("ventas")->constrained("fk_productos_ventas_venta")->onDelete("cascade");
            $table->double('cantidad');
            $table->double('precio_venta');
            $table->double('total_producto');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('ubicaciones_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId("venta")->references("id")->on("ventas")->constrained("fk_ubicaciones_ventas_venta")->onDelete("cascade")->unique("venta");
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('direccion');
            $table->string('casa_depto');
            $table->string('manzana_piso');
            $table->string('descripcion')->nullable();
            $table->timestamp('fecha_entrega_min');
            $table->timestamp('fecha_entrega_max');
            $table->timestamp('fecha_entrega')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('recibos_ventas', function (Blueprint $table) {
            $table->id();
            $table->longText('url_img')->nullable();
            $table->longText('url_img_online');
            $table->foreignId("venta")->references("id")->on("ventas")->constrained("fk_recibos_ventas_venta")->onDelete("cascade");
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('devoluciones_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId("venta")->references("id")->on("ventas")->constrained("fk_devoluciones_ventas_venta")->onDelete("cascade");
            $table->set('tipo_pago',["Tarjeta", "Efectivo", "Mixto", "Devolucion"]);
            $table->double('monto_total');
            $table->timestamp('fecha_devolucion')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('productos_devoluciones_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId("producto")->references("id")->on("productos")->constrained("fk_productos_devueltos_ventas_producto")->onDelete("cascade");
            $table->foreignId("devolucion")->references("id")->on("devoluciones_ventas")->constrained("fk_productos_devueltos_ventas_devolucion")->onDelete("cascade");
            $table->set('tipo_devolucion',["Cambio","Fallado","Devolucion"]);
            $table->string('motivo_devolucion');
            $table->double('cantidad');
            $table->double('total_producto');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('recibos_devoluciones_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId("devolucion")->references("id")->on("devoluciones_ventas")->constrained("fk_devoluciones_recibos_ventas_devolucion")->onDelete("cascade");
            $table->longText('url_img')->nullable();
            $table->longText('url_img_online');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('proveedores_productos');
        Schema::dropIfExists('fotos');
        Schema::dropIfExists('compras');
        Schema::dropIfExists('productos_compras');
        Schema::dropIfExists('recibos_compras');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('productos_ventas');
        Schema::dropIfExists('ubicaciones_ventas');
        Schema::dropIfExists('recibos_ventas');
        Schema::dropIfExists('devoluciones_ventas');
        Schema::dropIfExists('productos_devueltos');
        Schema::dropIfExists('devoluciones_recibos_ventas');
    }
};
