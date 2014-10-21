UploadDataBundle2
=================

Bundle para la gestión de carga de archivo de datos

Instalación
----

Agregar al composer.json:

```json
"require" : {
    "manuelj555/upload-data-bundle": "dev-master"
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
        new Manuelj555\Bundle\AjaxFlashBundle\ManuelAjaxFlashBundle(),
        new Manuelj555\Bundle\UploadDataBundle\UploadDataBundle(),
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


Configurando el bundle AjaxFlashBundle:
___________

Este bundle nos permite ver mensajes flash en nuestras peticiones con ajax, y por defecto ofrece 2 plugins de javascript a usar, lo configuracion es la siguiente:

```yaml
manuel_ajax_flash:
    auto_assets:
       pnotify:   # por defecto activamos pnotify
           animation: none
       # sticky:
#    mapping:
#        success:
#            title: Información
#            icon: my-icon
#        info:
#            title: Información
```

Para mayor información sobre como usar el bundle, ver: [AjaxFlashBundle](https://github.com/manuelj555/AjaxFlashBundle)

## Cargando Archivos

(Uso)[https://github.com/manuelj555/UploadDataBundle2/blob/master/Resources/doc/usage.md]

