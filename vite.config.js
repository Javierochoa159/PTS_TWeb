import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 'resources/js/app.js',
                'resources/css/myStyles/compra.css',
                'resources/css/myStyles/compras.css',
                'resources/css/myStyles/index.css',
                'resources/css/myStyles/inicio.css',
                'resources/css/myStyles/mapa.css',
                'resources/css/myStyles/procesarCompra.css',
                'resources/css/myStyles/procesarVenta.css',
                'resources/css/myStyles/producto.css',
                'resources/css/myStyles/productos.css',
                'resources/css/myStyles/proveedor.css',
                'resources/css/myStyles/proveedores.css',
                'resources/css/myStyles/venta.css',
                'resources/css/myStyles/ventas.css',
                'resources/js/app/categorias.js',
                'resources/js/app/compra.js',
                'resources/js/app/compras.js',
                'resources/js/app/dropImgs.js',
                'resources/js/app/index.js',
                'resources/js/app/inicio.js',
                'resources/js/app/litemap.js',
                'resources/js/app/mapa.js',
                'resources/js/app/procesarDevVenta.js',
                'resources/js/app/procesarCompra.js',
                'resources/js/app/procesarVenta.js',
                'resources/js/app/producto.js',
                'resources/js/app/productos.js',
                'resources/js/app/proveedor.js',
                'resources/js/app/venta.js',
                'resources/js/app/ventas.js',
            ],
            refresh: [
                'resources/views/**',
                'resources/assets/**'
            ],
        }),
        tailwindcss(),
        viteStaticCopy({
            targets: [
                {
                    src: 'resources/assets',
                    dest: ''
                },
            ],
            watch: true
        }),
    ],
});
