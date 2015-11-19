## CookBook

### Como añadir una acción a una configuración de carga de archivos:

Basicamente hay que reescribir dos métodos del `UploadConfig`.

 * El método `getInstance`, para añadir la acción a la colección de acciones.
 * El método `configureList`, para añadir la columna de la acción en el listado.

Ejemplo:

```php
<?php

namespace AppBundle\Upload\Config;

use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Mapper\ListMapper;

class XXXUploadConfig extends UploadConfig
{
    ...

    public function getInstance()
    {
        $upload = parent::getInstance();

        $upload->addAction(new UploadAction('nombre_accion'));

        return $upload;
    }

    /**
     * @param ListMapper $mapper
     */
    public function configureList(ListMapper $mapper)
    {
        parent::configureList($mapper);

        $mapper->addAction('pre_calculate', array(
            'position' => 210,
            'condition' => function (Upload $upload) {
                return true;
            },
            'route' => 'upload_data_upload_custom_action',
            'parameters' => array('action' => 'nombre_accion'),
        ));
    }
}