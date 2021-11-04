# Usando el Bundle

El funcionamiento del bundle se basa en una clase de configuración que existende [UploadConfig](./Config/UploadConfig.php), por ejemplo:

```php
<?php

namespace App\Upload;

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
                'formatter' => function($value) { return strtoupper($value); },
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

La clase consta de 4 métodos, de los cuales solo son obligatorios los métodos **configureColumns, configureValidations y transfer**, ya que es por medio de estos, que se leerá la data del archivo, se validará y se procesará para llevar los datos a la lógica de la aplicación.

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

## Cargando y procesando un archivo

Este es un controlador de ejemplo para llevar a cabo la carga de un archivo en diferentes pasos:

```php
<?php

use Manuel\Bundle\UploadDataBundle\Controller\AbstractUploadController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Upload\UploadCardConfig;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;

class UploadCardController extends AbstractUploadController
{
    /**
     * En esta ruta hacemos la carga del archivo excel y lo procesamos con el configHelper 
     * @Route("/upload", methods={"post"})
     */
    public function process(Request $request): Response
    {
        $configHelper = $this->getHelper(UploadCardConfig::class);
        $upload = $configHelper->upload(
            $request->files->get('file'), 
            [], // acá pasamos datos adicionales del formulario si hace falta
            [
                'created_by' => $this->getUser()->getId(),
            ], // acá pasamos atributos que quedarán guardados en bd si hace falta
        );
        
        $this->addFlash('success', 'Archivo cargado con exito');

        return $this->redirectToRoute('list');
    }

    /**
     * En esta ruta hacemos la carga del archivo excel y lo procesamos con el configHelper 
     * @Route("/read/{id}")
     */
    public function read(Request $request, Upload $upload): Response
    {
        $configHelper = $this->getHelper(UploadCardConfig::class);
        $upload = $configHelper->read(
            $request->files->get('file'), 
            [], // acá pasamos datos adicionales del formulario si hace falta
            [
                'created_by' => $this->getUser()->getId(),
            ], // acá pasamos atributos que quedarán guardados en bd si hace falta
        );
        
        $this->addFlash('success', 'Archivo cargado con exito');

        return $this->redirectToRoute('list');
    }
}
```


