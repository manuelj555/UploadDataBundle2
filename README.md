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

Con esto ya se ha instalado correctamente el bundle.
