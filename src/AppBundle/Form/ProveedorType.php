<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;


class ProveedorType extends AbstractType
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
            ->add('nombre',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nombre / Razón Social',
                'required'      => true,
                ))
            ->add('direccion',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Dirección',
                'required'      => true,
                ))
            ->add('ruc',TextType::class,array(
                'attr'=> array('class' => 'form-control ','maxlength'=>'11'),
                'label'         => 'RUC',
                'required'      => true,
                ))         
            ->add('telefono',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Teléfono',
                'required'      => false,
                ))
            ->add('celular',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Celular',
                'required'      => false,
                ))            
            ->add('email',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Email',
                'required'      => false,
                ))
            ->add('ciudad',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Ciudad',
                'required'      => false,
                ))
            ->add('web',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Página web',
                'required'      => false,
                ))
            ->add('facebook',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Facebook',
                'required'      => false,
                ))
            ->add('banco_soles',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Entidad Bancaria (soles)',
                'required'      => false,
                ))
            ->add('banco_dolares',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Entidad Bancaria (dólares)',
                'required'      => false,
                ))                 
            ->add('cuenta_soles',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Cuenta bancaria en soles',
                'required'      => false,
                ))
            ->add('cuenta_dolares',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Cuenta bancaria en dólares',
                'required'      => false,
                ))                                                            
            ->add('direccion',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Dirección',
                'required'      => false,
                ))                                                                            
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))            
            ->add('descripcion',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Descripción',
                'required'      => true,
                ))
            ->add('empresa',null,array(
                'attr'=> array('class' => 'd-none'),
                'label'=> false,
                'data'  => $options['empresa'],
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
            ->add('proveedorXTipo',null,array(
                'attr'      => array('class' => ''),
                'label'     => 'Tipo',
                'required'  => true,
                'multiple'  => true,
                'expanded'  => true,
                ))
            ->add('condicion',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('BAJA DEFINITIVA' => 'BAJA DEFINITIVA','ACTIVO' => 'ACTIVO','ANUL.PROVI.-ACTO ILI'=>'ANUL.PROVI.-ACTO ILI','ANULACION - ACTO ILI'=>'ANULACION - ACTO ILI','ANULACION - ERROR SU'=>'ANULACION - ERROR SU','BAJA DE OFICIO'=>'BAJA DE OFICIO','BAJA MULT.INSCR. Y O'=>'BAJA MULT.INSCR. Y O','BAJA PROV. POR OFICI'=>'BAJA PROV. POR OFICI','BAJA PROVISIONAL'=>'BAJA PROVISIONAL','INHABILITADO-VENT.UN'=>'INHABILITADO-VENT.UN','NUM. INTERNO IDENTIF'=>'NUM. INTERNO IDENTIF','OTROS OBLIGADOS'=>'OTROS OBLIGADOS','PENDIENTE DE INI. DE'=>'PENDIENTE DE INI. DE','SUSPENSION TEMPORAL'=>'SUSPENSION TEMPORAL'),                
                'label'         => 'Estado en la SUNAT',
                'required'      => false,
                ))                               
            ;


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $proveedor = $event->getData();
            $form = $event->getForm();

            if (!$proveedor || null === $proveedor->getId()) {
                $form
                    ->add('codigo',null,array(
                        'attr'=> array('class' => 'd-none'),
                        'label'         => false,
                        'required'      => false,
                        'data'  => $options['codigo'],
                    ))
                    ->add('ruc',TextType::class,array(
                        'attr'=> array('class' => 'form-control '),
                        'label'         => 'RUC',
                        'required'      => true,
                        ))                    
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
                    ))
                    ->add('proveedorXTipo',null,array(
                        'attr'      => array('class' => ''),
                        'label'     => 'Tipo',
                        'required'  => true,
                        'multiple'  => true,
                        'expanded'  => true,
                        'data'      => $this->em->getRepository('AppBundle:ProveedorTipo')->findBy(array('id'=>2)),
                        ))   
                    ;                    
            }
        });

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Proveedor',
            'empresa'=>'',
            'codigo'=>''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_proveedor';
    }


}
