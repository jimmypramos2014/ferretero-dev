<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Doctrine\ORM\EntityRepository;
use AppBundle\Form\RegistrationType;

class EmpleadoType extends AbstractType
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
                'required'      => false,
                ))
            ->add('email',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Correo electrónico',
                'required'      => false,
                ))           
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))
            ->add('oculto',HiddenType::class,array(
                'data'  => false,
                ))            
            ->add('local',null,array(
                'attr'=> array('class' => 'form-control'),
                'class' => 'AppBundle:EmpresaLocal',
                'label'         => 'Local',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('l');
                    $qb->join('l.empresa','e');

                    if($options['empresa'] != ''){
                        $qb->where('e.id ='.$options['empresa']);
                    }                    
                    return $qb;
                }                  
                ))
            ->add('puesto',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Puesto',
                'required'      => true,
                ))
            ->add('usuario', RegistrationType::class)                     
            ;

    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Empleado',
            'empresa'=>''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_empleado';
    }


}
