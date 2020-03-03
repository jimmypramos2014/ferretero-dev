<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class EmpresaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nombre y/o Razón social',
                'required'      => true,
                ))
            ->add('ruc',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'RUC',
                'required'      => true,
                ))
            ->add('descripcion',TextareaType::class,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Descripción',
                'required'      => false,
                ))
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))
            ->add('distrito',EntityType::class,array(
                'class' => 'AppBundle:Distrito',
                'attr'=> array('class' => 'form-control'),
                'placeholder'   => 'Seleccionar distrito',
                'label'         => 'Distrito',
                'required'      => true,
                ))
            ->add('provincia',EntityType::class,array(
                'class' => 'AppBundle:Provincia',
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Provincia',
                'placeholder'   => 'Seleccionar provincia',
                'required'      => false,
                'mapped'        => false
                ))   
            ->add('departamento',EntityType::class,array(
                'class' => 'AppBundle:Departamento',
                'attr'=> array('class' => 'form-control'),
                'placeholder'   => 'Seleccionar departamento',
                'label'         => 'Departamento',
                'required'      => false,
                'mapped'        => false
                ))
            ->add('direccion',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Dirección',
                'required'      => false,
                )) 
            ->add('nombreCorto',null,array(
                'attr'=> array('class' => 'form-control','maxlength'=>'32'),
                'label'         => 'Nombre corto',
                'required'      => true,
                ))
            ->add('mostrarServicios',CheckboxType::class,array(
                'label'  => '¿Mostrar Servicios en el menú?',
                'required'      => false,
                ))
            ->add('direccionWeb',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Página web',
                'required'      => false,
                ))                                                                                                                            
            ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Empresa'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_empresa';
    }


}
