<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ServicioType extends AbstractType
{

    protected $session;
    protected $em;

    public function __construct(SessionInterface $session,EntityManagerInterface $em)
    {
        $this->session  = $session;
        $this->em       = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('dni',null,array(
                'attr'=> array('class' => 'form-control','maxlength'=>'8'),
                'label'         => 'DNI',
                'required'      => true,
                ))
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
            ->add('alias',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Alias',
                'required'      => false,
                ))
            ->add('direccion',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Dirección',
                'required'      => true,
                ))
            ->add('celular',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Celular',
                'required'      => true,
                ))
            ->add('telefono',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Teléfono',
                'required'      => false,
                ))
            ->add('email',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Correo',
                'required'      => false,
                ))
            ->add('profesion',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Profesión',
                'required'      => true,
                ))
            ->add('local')
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))
            ->add('distrito',EntityType::class,array(
                'class' => 'AppBundle:Distrito',
                'attr'=> array('class' => 'form-control'),
                'placeholder'   => 'Seleccionar distrito',
                'label'         => 'Distrito',
                'required'      => false,
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
            ->add('fechaNacimiento',null,array(
                'attr'=> array('class' => 'form-control datepicker'),
                'label'         => 'Fecha de nacimiento',
                'format' => $options['date_format'],
                'html5' => false,
                'widget' => 'single_text'
                ))               
            ;


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $servicio = $event->getData();
            $form = $event->getForm();

            if (!$servicio || null === $servicio->getId()) {
                $form
                    ->add('departamento',EntityType::class,array(
                        'class'         => 'AppBundle:Departamento',
                        'attr'          => array('class' => 'form-control'),
                        'placeholder'   => 'Seleccionar departamento',
                        'label'         => 'Departamento',
                        'required'      => false,
                        'mapped'        => false,
                        'data'          => $this->em->getRepository('AppBundle:Departamento')->find($this->session->get('departamento'))  
                    ))
                    ->add('provincia',EntityType::class,array(
                        'class' => 'AppBundle:Provincia',
                        'attr'=> array('class' => 'form-control'),
                        'label'         => 'Provincia',
                        'placeholder'   => 'Seleccionar provincia',
                        'required'      => false,
                        'mapped'        => false,
                        'query_builder' => function(EntityRepository $er) use ($options)
                        {
                            $qb = $er->createQueryBuilder('p');
                            $qb->leftJoin('p.departamento','d');
                            $qb->where('d.id = '.$this->session->get('departamento'));

                            return $qb;
                        }                 
                    ));
            }
        });

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Servicio',
            'date_format' => 'dd/MM/yyyy',

        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_servicio';
    }


}
