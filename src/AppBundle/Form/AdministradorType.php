<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Form\RegistrationType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class AdministradorType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombres',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nombres',
                'required'      => true,
                ))
            ->add('apellidoPaterno',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Apellido paterno',
                'required'      => true,
                ))
            ->add('apellidoMaterno',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Apellido materno',
                'required'      => true,
                ))
            ->add('dni',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'DNI',
                'required'      => true,
                ))
            ->add('email',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Correo electrónico',
                'required'      => true,
                ))           
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))          
            ->add('local',null,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => 'Local *',
                'required'      => false,
                ))
            ->add('empresa',EntityType::class,array(
                'class'         => 'AppBundle:Empresa',
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => 'Empresa *',
                'placeholder'   => 'Seleccionar empresa',
                'required'      => false,
                'mapped'        => false
                ))
            ->add('usuario', RegistrationType::class)
            ->add('oculto',CheckboxType::class,array(
                //'data'  => false,
                'label'  => 'Ocultar usuario',
                'required'      => false,
                ))
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Empleado'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_administrador';
    }


}
