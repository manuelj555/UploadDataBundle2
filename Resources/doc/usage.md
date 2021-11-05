# Usando el Bundle

El funcionamiento del bundle se basa en una clase de configuración que existende [UploadConfig](../../Config/UploadConfig.php), por ejemplo:

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
    
    protected function configureColumns(ColumnsMapper $mapper)
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

    protected function configureValidations(ValidationBuilder $builder)
    {
        $builder
            ->for('email')
                ->assertNotBlank()
                ->assertEmail()
                ->assertEntityExist(Card::class, 'email')
            ->end()
            ->for('card_number')
                ->assertNotBlank()
                ->addConstraint(new CardScheme(array(
                    'schemes' => array('MASTERCARD'),
                    'message' => 'Card Number is invalid',
                )))
            ->end()
            ->for('expiration_date')
                ->assertNotBlank()
                ->addConstraint(new Regex('/^(1[012]|0[1-9])\/\d{2}$/'))
            ->end();
    }

    protected function validateItem(UploadedItem $item, ContextualValidatorInterface $context, Upload $upload)
    {
        //podemos añadir más lógica de validación acá:
        if ($item['email'] == 'no-allowed-email@email.com')) {
            $context->getViolations()->add(new ColumnError('No allowed Email', 'email'));
        }
    }

    protected function transfer(Upload $upload, Collection $items)
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

        return $this->redirectToRoute('read_path', ['id' => $upload->getId()]);
    }

    /**
     * En esta ruta hacemos match de las columnas esperadas y las que tiene el excel.
     * Luego de hacerlo se ejecuta el match y se lee el archivo.
     * 
     * @Route("/read/{id}", name="read_path")
     */
    public function read(Request $request, Upload $upload): Response
    {
        $configHelper = $this->getHelper(UploadCardConfig::class);
        $matchInfo = $configHelper->getDefaultMatchInfo($upload);

        if ($request->isMethod('post')) {
            $configHelper->applyMatch($matchInfo, $request->request->get('columns'));
             if (!$configHelper->read($upload)) {
                throw $configHelper->getLastException();
            }
            
            $this->addFlash('success', 'Archivo leído con exito');

            return $this->redirectToRoute('validate_path', [
                'id' => $upload->getId()
            ]);
        }

        return $this->render('@UploadData/Read/select_columns.html.twig', [
            'match_info' => $matchInfo,
        ]);
    }
    
    /**
     * En esta ruta validamos el archivo y mostramos los resultados.
     *  
     * @Route("/validate/{id}", name="validate_path")
     */
    public function validate(Upload $upload): Response
    {
        $configHelper = $this->getHelper(UploadCardConfig::class);

        if (!$configHelper->validate($upload)) {
            throw $configHelper->getLastException();
        }
        
        $this->addFlash('success', 'Archivo validado con exito');

        return $this->render('@UploadData/Upload/show.html.twig', [
            'upload' => $upload,
            'config_helper' => $configHelper,
        ]);
    }    
    
    /**
     * En esta ruta Transferimos los datos del archivo a su destino final.
     *  
     * @Route("/transfer/{id}", name="transfer_path")
     */
    public function transfer(Upload $upload): Response
    {
        $configHelper = $this->getHelper(UploadCardConfig::class);

        if (!$configHelper->transfer($upload)) {
            throw $configHelper->getLastException();
        }
        
        $this->addFlash('success', 'Archivo transferido con exito');

        return $this->render('@UploadData/Upload/show.html.twig', [
            'upload' => $upload,
            'config_helper' => $configHelper,
        ]);
    }
        
    /**
     * Si lo necesitamos podemos eliminar una carga de un archivo.
     *  
     * @Route("/delete/{id}", name="delete_path")
     */
    public function delete(Upload $upload): Response
    {
        $configHelper = $this->getHelper(UploadCardConfig::class);

        if (!$configHelper->delete($upload)) {
            throw $configHelper->getLastException();
        }
        
        $this->addFlash('success', 'Archivo eliminado con exito');

        return $this->redirectToRoute('other_path');
    }
}
```
En el siguiente ejemplo todo el proceso se hace en un único paso y además, el usuario
no necesita hacer un match manual de las columnas del excel:

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
        // Con este método hacemos un match por defecto de las columnas del excel
        // y las columnas esperadas por nosotros:
        $configHelper->configureDefaultMatch($upload);

        // Luego procesamos lectura, validacion y transferencia 
        try {
            $configHelper->read($upload, true);
            $configHelper->validate($upload, false, true);
            $configHelper->transfer($upload, true);
        } catch (UploadProcessException $e) {
            // ocurrió un error
        }

        return $this->render('@UploadData/Upload/show.html.twig', [
            'upload' => $upload,
            'config_helper' => $configHelper,
        ]);
    }
    
    /**
     * Este ejemplo muestra como hacer todo más simple aún:
     * @Route("/upload", methods={"post"})
     */
    public function simpleProcess(Request $request): Response
    {
        $configHelper = $this->getHelper(UploadCardConfig::class);
        $upload = $configHelper->upload(
            $request->files->get('file'), 
            [], // acá pasamos datos adicionales del formulario si hace falta
            [
                'created_by' => $this->getUser()->getId(),
            ], // acá pasamos atributos que quedarán guardados en bd si hace falta
        );
        // Con este método hacemos un match por defecto de las columnas del excel
        // y las columnas esperadas por nosotros:
        try {
            if ($configHelper->processAll($upload)) {
                // transferido con exito
            } else {
                // hay registros con errores de validación
                
                // Si necesitamos que la transferencia se haga a pesar de haber errores
                // de validación en algunos registros. debemos llamar al processAll
                // pasando false como segundo argumento
            }
        } catch (UploadProcessException $e) {
            // ocurrió un error
        }

        return $this->render('@UploadData/Upload/show.html.twig', [
            'upload' => $upload,
            'config_helper' => $configHelper,
        ]);
    }
}
```
## Validadores especiales

Se ofrecen ciertos constraints para las columnas del archivo como:

 * assertNotNull($config = null);
 * assertNotBlank($config = null);
 * assertBlank($config = null);
 * assertCallback($callback, $config = null);
 * assertDate($config = null);
 * assertDatetime($config = null);
 * assertEmail($config = null);
 * assertType($type, $config = null);
 * assertEntityExist($class, $property, $config = []);

### Como funciona `assertEntityExist`:

Este validador se encarga de hacer busquedas en la base de datos bajo ciertos criterios.

La idea es que este validador es eficiente para cuando tenemos un archivo grande y estamos
validando datos repetitivos, por ejemplo, paises, tipos de datos en base de datos, etc. Ya
que inicialmente se cargan los registros de la entidad dada de la base de datos a un arreglo
en memoria y las busquedas se hacen contra dicho arreglo.

**El validador guarda resultados previos de comparación para evitar consultas repetitivas.**

Los parametros que se pueden pasar son los siguientes:

 * $class: Entidad donde se va a consultar el dato.
 * $property: propiedad donde se almacena el dato a buscar, puede ser un nombre o un propertyPath.
 * $config: Para la configuración se pueden pasar las siguientes opciones:
    * hydrate (opcional) Modo de obtener los datos de la base de datos:
        * Doctrine\ORM\Query::HYDRATE_OBJECT // valor por defecto, más memoria si son muchos datos.
        * Doctrine\ORM\Query::HYDRATE_ARRAY // Si necesitamos la data en formato más liviano.
        * Doctrine\ORM\Query::HYDRATE_SIMPLEOBJECT // Si necesitamos la data en formato más liviano.
      
    * query_builder (opcional):
        ```php
        assertEntityExist(Entity::class, 'property', [
            'query_builder' => function(EntityRepository $repository) {
                return $repository->createQueryBuilder('a')->where('a.active = true');
            },
        ]);
        ```
      
    * success (opcional):
        ```php
        assertEntityExist(Entity::class, 'property', [
            'success' => function($entityFromDatabase, UploadedItem $item, $propertyValue, $propertyName) {
                // podemos por ejemplo guardar data adicional en el item de carga.
                $item['additional_value'] = $entityFromDatabase->getImportantValue();
                $item->setExtra('other_value', $entityFromDatabase->getOtherValue());
            },
        ]);
        ``` 
      
    * comparator (opcional):
        ```php
        assertEntityExist(Entity::class, 'property', [
            'comparator' => function($entityFromDatabase, $propertyValue, $propertyName) {
                // cuando la logica de comparación y busqueda es más compleja que comparar
                // igualdades, esté método nos va a servir. 
                return ($entityFromDatabase->getPropertyXXX() === $propertyValue)
                    or (in_array($propertyValue, $entityFromDatabase->getPropertyYYY()));
            },
        ]);
        ```

