<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class CuentaBancoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('banco',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Entidad bancaria',
                'required'      => true,
                ))
            ->add('codigo',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Código',
                'required'      => false,
                ))
            ->add('numero',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Número de cuenta',
                'required'      => true,
                ))
            ->add('numeroInterbancario',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Código interbancario',
                'required'      => false,
                ))
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))            
            ->add('moneda',null,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => 'Moneda',
                'placeholder'         => 'Seleccionar moneda',
                'required'      => false,
                ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CuentaBanco'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_cuentabanco';
    }


}
