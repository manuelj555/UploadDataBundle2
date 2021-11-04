# Usando el Bundle

El funcionamiento del bundle se basa en una clase de configuración que existende [UploadConfig](https://github.com/manuel/UploadDataBundle2/blob/master/Config/UploadConfig.php), por ejemplo:

```php
<?php

namespace AppBundle\Upload;

use Doctrine\Common\Collections\Collection;
use Manuel\Bundle\UploadDataBundle\Builder\ValidationBuilder;
use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Entity\UploadAttribute;
use Manuel\Bundle\UploadDataBundle\Entity\UploadedItem;
use Manuel\Bundle\UploadDataBundle\Mapper\ColumnsMapper;
use Manuel\Bundle\UploadDataBundle\Validator\ColumnError;
use AppBundle\Card;
use Symfony\Component\Validator\Constraints\CardScheme;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;

class UploadCardConfig extends UploadConfig
{
    
    public function configureColumns(ColumnsMapper $mapper)
    {
        $mapper
            ->add('email')
            ->add('card_number', array(
                'label' => 'Card Number',
                'aliases' => array('CardNumber', 'Num Tarjeta', 'Tarjeta'),
                'similar' => true, 
            ))
            ->add('expiration_date', array(
                'required' => false,
                //podemos formatear los datos leidos del archivo subido.
                'formatter' => function($value){ return strtoupper($value); },
            ))
            ->add('name', array(
                'required' => false,
            ));
    }

    public function configureValidations(ValidationBuilder $builder)
    {
        $builder
            ->with('email')
                ->assertNotBlank()
                ->assertEmail()
            ->end()
            ->with('card_number')
                ->assertNotBlank()
                ->addConstraint(new CardScheme(array(
                    'schemes' => array('MASTERCARD'),
                    'message' => 'Card Number is invalid',
                )))
            ->end()
            ->with('expiration_date')
                ->assertNotBlank()
                ->addConstraint(new Regex('/^(1[012]|0[1-9])\/\d{2}$/'))
            ->end();
    }

    public function validateItem(UploadedItem $item, ContextualValidatorInterface $context, Upload $upload)
    {
        //podemos añadir más lógica de validación acá:
        if ($item['email'] == 'no-allowed-email@email.com')) {
            $context->getViolations()->add(new ColumnError('No allowed Email', 'email'));
        }
    }

    public function transfer(Upload $upload, Collection $items)
    {
        foreach ($upload->getValidItems() as $item) {
            $card = new Card();
            $card->setNumber($item['card_number']);
            $card->setEmail($item['email']);
            $card->setExpDate($item['expiration_date']);
            $card->setUsername($item['user']);

            $this->objectManager->persist($card);
        }

        $this->objectManager->flush();
    }

}
```

la clase consta de 4 métodos, de los cuales solo son obligatorios los métodos **configureColumns, configureValidations y transfer**, ya que es por medio de estos, que se leerá la data del archivo, se validará y se procesará para llevar los datos a la lógica de la aplicación.

## configureColumns()

Este método permite definir las columnas que necesitamos cargar del excel, y de una vez mapearlas a claves de datos usables en los posteriores procesos de lectura, validación y transferencia de los datos.

### add(name, options)

Esta función espera dos argumentos, el primero es el key que le daremos al nombre de la columna, y el segundo un array al que le podemos definir una serie de opciones de configuración:

Opcion      | Por Defecto       | Descripcion
 ---        | ---               | --- 
label       | null              | Label a mostrar al usuario para la columna leida del archivo.
required    | true              | Indica si la columna es requerida.
aliases     | array()           | Permite indicar una serie de strings con otros posibles nombres de la columna en el archivo, si la columna coincide con alguno, aparecera mapeada por defecto a este key.
similar     | false             | si es true, permite mapear una columna del archivo que tenga un nombre muy parecido al esperado
formatter   | callback(){}      | permite definir una función que será llamada al leer cada dato de la columna en el archivo, y en ella podemos modificar el contenido leido para ajustarlo a nuestras necesidades (formatear fechas, convertir textos, etc.).

## configureValidations()

Este método permite especificar validaciones para cada una de las columnas que leeremos del archivo.

## transfer()

Con este método realizaremos el proceso de transferencia de los datos leidos a la aplicación.

## Registrar la clase como un Servicio:

Para poder hacer uso de la carga de Tarjetas, debemos registrar la clase `AppBundle\Upload\UploadCardConfig` como un servicio y agregarle las etiquetas necesarias:

```yaml
services:
    app.upload.card_config:
        class: AppBundle\Upload\UploadCardConfig
        tags:
            - { name: upload_data.config, id: 'cards' }
```

La etiqueta `upload_data.config` le indica a symfony que el servicio `app.upload.card_config` es una clase para administrar carga de archivos, donde `id` es el nombre unico que define el tipo de carga, y es usado como parte de la url para la administración y lectura de los ficheros que se suben.

### Visualizar la página de carga de tarjetas

