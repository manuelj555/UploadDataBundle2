# Usando el Bundle

El funcionamiento del bundle se basa en una clase de configuración que existende [UploadConfig](https://github.com/manuelj555/UploadDataBundle2/blob/master/Config/UploadConfig.php), por ejemplo:

```php
<?php

namespace AppBundle\Upload;

use Doctrine\Common\Collections\Collection;
use Manuelj555\Bundle\UploadDataBundle\Builder\ValidationBuilder;
use Manuelj555\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuelj555\Bundle\UploadDataBundle\Entity\Upload;
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadAttribute;
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadedItem;
use Manuelj555\Bundle\UploadDataBundle\Mapper\ColumnsMapper;
use Manuelj555\Bundle\UploadDataBundle\Validator\ColumnError;
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

## configureColumns

Este método permite definir las columnas que necesitamos cargar del excel, y de una vez mapearlas a claves de datos usables en los posteriores procesos de lectura, validación y transferencia de los datos.

