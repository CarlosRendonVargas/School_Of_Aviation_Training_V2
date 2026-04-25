# Plan para Corregir Alertas PSR-4

## Problema Detectado
Clases duplicadas en `database/seeders/OtrosSeeders.php` que ya existen en archivos individuales separados. Composer lanza warnings PSR-4 de autoloading.

## Pasos
- [x] 1. Limpiar `OtrosSeeders.php` eliminando las 4 clases duplicadas
- [x] 2. Verificar que `DatabaseSeeder.php` referencia las clases correctas
- [x] 3. Ejecutar `composer dump-autoload` para regenerar el autoloader
- [x] 4. Ejecutar `php artisan serve` para confirmar que no hay más alertas

