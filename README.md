UploadDataBundle2
==================

Bundle para la gestión de carga de archivo de datos

Instalación
----

Ejecutar 

    composer require "manuelj555/upload-data-bundle" "~4.0@dev"

Luego de ello, registrar los bundles en el **config/bundles.php**:

```php

return [
    ...
    Knp\Bundle\PaginatorBundle\KnpPaginatorBundle::class => ['all' => true], //solo si no esta antes agregado
    Manuel\Bundle\UploadDataBundle\UploadDataBundle::class => ['all' => true],
];
```

Por ultimo se debe crear la base de datos (si no se ha hecho aun) y agregar a la bd las tablas competentes al bundle, por lo que se deben ejecutar los siguientes comandos de consola:

    app\console doctrine:database:create
    console doctrine:schema:update --force

Con esto ya se ha instalado correctamente el bundle.

## Cargando Archivos

[Uso](./Resources/doc/usage.md)

