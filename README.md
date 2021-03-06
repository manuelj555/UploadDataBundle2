UploadDataBundle2
=================

Bundle para la gestión de carga de archivo de datos

Instalación
----

Agregar al composer.json:

```json
"require" : {
    "manuelj555/upload-data-bundle": "~2.0@dev",
}
```

Y ejecutar 

    composer update 

Luego de ello, registrar los bundles en el **AppKernel.php**:

```php
public function registerBundles()
{
    $bundles = array(
        ...
        new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(), //solo si no esta antes agregado
        new Ku\AjaxBundle\KuAjaxBundle(), //solo si no esta antes agregado
        new Manuel\Bundle\UploadDataBundle\UploadDataBundle(),
    );
    
    ...
}
```

En el **app/config/routing.yml** agregar:

```yaml
_upload_data:
    resource: "@UploadDataBundle/Resources/config/routing.yml"
    prefix:   /uploads
``` 

Por ultimo se debe crear la base de datos (si no se ha hecho aun) y agregar a la bd las tablas competentes al bundle, por lo que se deben ejecutar los siguientes comandos de consola:

    app\console doctrine:database:create
    console doctrine:schema:update --force

Además ejecutar el comando 
    
    app/console assets:install

Con esto ya se ha instalado correctamente el bundle.


Configurando el bundle AjaxBundle:
___________

 ver: [AjaxBundle](https://github.com/manuelj555/AjaxBundle#flash-messages)

## Cargando Archivos

[Uso](./Resources/doc/usage.md)

