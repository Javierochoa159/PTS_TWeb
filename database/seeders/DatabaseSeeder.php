<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategoriaSeeder::class,
            ProveedorSeeder::class,
            ProductoSeeder::class,
            FotoSeeder::class,
            ProveedorProductoSeeder::class,
            CompraSeeder::class,
            ProductoCompraSeeder::class,
            ReciboCompraSeeder::class,
            VentaSeeder::class,
            ProductoVentaSeeder::class,
            UbicacionVentaSeeder::class,
            ReciboVentaSeeder::class,
            DevolucionVentaSeeder::class,
            ProductosDevolucionVentaSeeder::class,
            ReciboDevolucionVentaSeeder::class,
        ]);
    }
}
