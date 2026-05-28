<?php

/*
|--------------------------------------------------------------------------
| XAMPP Subfolder Entry Point
|--------------------------------------------------------------------------
| Permite acceder al panel via http://localhost/barberortiz/admin sin
| exponer /public/ en la URL del navegador.
|
| El .htaccess raíz enruta todo a este archivo. Al ser servido aquí,
| Apache establece SCRIPT_NAME = /barberortiz/index.php, lo que permite
| a Symfony HttpFoundation calcular correctamente:
|   baseUrl  = /barberortiz
|   pathInfo = /admin/login  (o cualquier ruta de Filament/Livewire)
|
| NO incluir en producción. En producción el DocumentRoot debe
| apuntar directamente a la carpeta public/.
*/

require __DIR__ . '/public/index.php';
