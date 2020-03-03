<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;



class VentaLocalMensualFiltroType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fecha_inicio',null,array(
                'attr'=> array('class' => 'form-control datepicker'),
                'label'         => 'Fecha inicio',
                'required'      => false,
                ))         
            ->add('fecha_fin',null,array(
                'label'         => 'Fecha fin',
                'attr'=> array('class' => 'form-control datepicker','style'=>''),
                'required'      => false,
                ))
                           
            ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_ventalocalmensual_filtro';
    }


}
