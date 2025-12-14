# Proyecto Universitario Diciembre 2025 'Administrador de Inventario (Local) y registro de compras y ventas' hecho en Laravel 12 y PHP 8.3 en adelante.

## Como iniciar el proyecto?

Descargue el proyecto y, haciendo uso de la herramienta que considere más adecuada, necesita conseguir un servidor local con 'certificación SSL' y un 'Virtual Host' por el que accederá al proyecto (puede ser un host online).

Lo siguiente que deberá hacer es configurar el archivo .env proporcionado por Laravel con los siguientes parámetros:

### URL Base del sistema

  - APP_URL = "Dado que la aplicacion está ambientada para un entorno local, la url será: *http://localhost/inventario/public/*", aunque tambien funciona en direcciones host online"

### Configuracion de la Base de Datos

  Se está utilizando migraciones para "crear" las tablas necesarias para el correcto funcionamiento del sistema, por lo que necesitará crear una DB para el proyecto.
  Posteriormente, deberá ingresar los datos correspondientes en el archivo .env para que la aplicación se conecte correctamente con la misma.

  Una ves creada la DB y configurado el .env se podra hacer uso de la migracion con el comando: "php artisan migrate:fresh".

  Ademas, se deverá crear un usuario con 'name' = "Administrador" y un 'password', el cual tiene que hacer uso del metodo Hash::make(" ").

### MapBox
  La aplicación utiliza mapbox para diversas funcionalidades relacionadas con el uso de mapas, por lo que necesitará adquirir un token e ingresarlo en esta variable
  
  - VITE_MAPBOX_TOKEN = MyToken

## Storage de la aplicación
  Se está haciendo uso de 'symlink (enlace simbólico)' para poder acceder a la carpeta Storage proporcionada por Laravel, por lo que, si dentro de la carpeta "\Inventario\public" no hay una carpeta (acceso directo) llamada "Storage", se necesitará ejecutar el comando "php artisan storage:link".
  
  En caso de que si lo tenga, pero no sea un acceso directo, entonces se deberá eliminar la carpeta Storage dentro de public y ejecutar el comando.  
  
  En caso de que se use un host online, se tiene que corroborar que el sevicio permita el uso de 'symlink', en caso de no permitirlo se necesita crear una carpeta "uploads" dentro de "\Inventario\public" y, ademas, se deberá modificar el archivo "Inventario\config\filesystems.php" en la sección "public" con algo como:
  
      'public' =>[
        'driver' => 'local',
        'root' => public_path('uploads'),
        'url' => env('APP_URL').'/uploads',
        'visibility' => 'public',
      ],
      
  Y dentro de "uploads" se deberá crear la carpeta "fotos", la cual contendrá las carpetas "productos" y "recibos".
  
  Ademas, dentro de "recibos" se tienen que crear las carpetas "compras", "devolucionesVentas" y "ventas".
