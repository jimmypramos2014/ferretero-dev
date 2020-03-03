<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductoXPrecioType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rangoInicial',null,array(
                'attr'=> array('class' => 'form-control solonumeros'),
                'label'         => 'Rango inicial',
                'required'      => false,
            ))
            ->add('rangoFinal',null,array(
                'attr'=> array('class' => 'form-control solonumeros'),
                'label'         => 'Rango final',
                'required'      => false,
            ))
            ->add('precio',null,array(
                'attr'=> array('class' => 'form-control solonumeros'),
                'label'         => 'Precio',
                'required'      => false,
            ));
            //->add('producto');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ProductoXPrecio'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_productoxprecio';
    }


}
