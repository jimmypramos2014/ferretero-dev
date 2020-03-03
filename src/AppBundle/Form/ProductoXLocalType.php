<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use AppBundle\Form\ProductoType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ProductoXLocalType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stock',null,array(
                'attr'=> array('class' => 'form-control solonumeros'),
                'label'         => 'Stock',
                'required'      => false,
            ))
            ->add('local',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Local',
                'required'      => false,
            ))
            // ->add('producto',ProductoType::class, array(
            //     'codigo'    => $options['codigo'],
            //     'empresa'   => $options['empresa']
            // ))
            ->add('estado',HiddenType::class,array(
                'data'  => true,
            ))
            ;
          
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ProductoXLocal',
            'codigo'  => '',
            'empresa' => ''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_productoxlocal';
    }


}
