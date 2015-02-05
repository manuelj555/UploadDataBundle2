<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle;

use Doctrine\Common\Collections\Collection;
use Manuel\Bundle\UploadDataBundle\Builder\ValidationBuilder;
use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Mapper\ColumnsMapper;
use Symfony\Component\Validator\Constraints\CardScheme;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class VentasConfig extends UploadConfig
{

    public function configureColumns(ColumnsMapper $mapper)
    {
        $mapper
            ->add('sales_id', array(
                'label' => 'Sales ID',
            ))
            ->add('first_name', array(
                'similar' => true,
                'label' => 'First Name',
            ))
            ->add('last_name', array(
                'similar' => true,
                'label' => 'Last Name',
            ))
            ->add('card_number', array(
                'similar' => true,
                'label' => 'Card Number',
            ));
    }

    public function configureValidations(ValidationBuilder $builder)
    {
        $builder
            ->with('sales_id')
                ->assertNotBlank()
            ->end()
            ->with('first_name')
                ->assertNotBlank()
            ->end()
            ->with('last_name')
                ->assertNotBlank()
            ->end()
            ->with('card_number')
                ->assertNotBlank()
                ->addConstraint(new CardScheme(array("MASTERCARD")))
            ->end()
            ;
    }

    public function transfer(Upload $upload, Collection $items)
    {

    }
}