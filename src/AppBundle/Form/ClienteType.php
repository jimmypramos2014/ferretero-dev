<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ClienteType extends AbstractType
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
            ->add('ciudad',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Ciudad',
                'required'      => false,
                ))
            ->add('direccion',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Dirección',
                'required'      => false,
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
            ->add('web',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Web',
                'required'      => false,
                ))
            ->add('sexo',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('Masculino' => 'M','Femenino' => 'F'),                
                'label'         => 'Sexo',
                'required'      => false,
                ))                                               
            ->add('ruc',null,array(
                'attr'=> array('class' => 'form-control '),
                'label'         => 'RUC o DNI',
                'required'      => true,
                ))           
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))
            ->add('local',EntityType::class,array(
                'class' => 'AppBundle:EmpresaLocal',
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Local',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('el');

                    if($options['empresa'] != ''){
                        $qb->where('el.empresa ='.$options['empresa']);
                    }
                    

                    return $qb;
                }                   
                ))
            ->add('razonSocial',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nombre o Razón social',
                'required'      => true,
                ))
            ->add('codigo',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Código',
                'required'      => false,
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
            ->add('condicion',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('BAJA DEFINITIVA' => 'BAJA DEFINITIVA','ACTIVO' => 'ACTIVO','ANUL.PROVI.-ACTO ILI'=>'ANUL.PROVI.-ACTO ILI','ANULACION - ACTO ILI'=>'ANULACION - ACTO ILI','ANULACION - ERROR SU'=>'ANULACION - ERROR SU','BAJA DE OFICIO'=>'BAJA DE OFICIO','BAJA MULT.INSCR. Y O'=>'BAJA MULT.INSCR. Y O','BAJA PROV. POR OFICI'=>'BAJA PROV. POR OFICI','BAJA PROVISIONAL'=>'BAJA PROVISIONAL','INHABILITADO-VENT.UN'=>'INHABILITADO-VENT.UN','NUM. INTERNO IDENTIF'=>'NUM. INTERNO IDENTIF','OTROS OBLIGADOS'=>'OTROS OBLIGADOS','PENDIENTE DE INI. DE'=>'PENDIENTE DE INI. DE','SUSPENSION TEMPORAL'=>'SUSPENSION TEMPORAL'),                
                'label'         => 'Estado en la SUNAT',
                'required'      => false,
                ))
            ->add('tipoDocumento',EntityType::class,array(
                'class'         => 'AppBundle:TipoDocumento',
                'attr'          => array('class' => 'form-control'),
                'label'         => 'Tipo de documento',
                'required'      => true,
                ))
            ->add('tipo',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('Mayorista' => 'mayorista','Minorista' => 'minorista'),                
                'label'         => 'Tipo',
                'required'      => false,
                ))                                                                
            ;


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $cliente = $event->getData();
            $form = $event->getForm();

            if (!$cliente || null === $cliente->getId()) {
                $form
                    ->add('tipoDocumento',EntityType::class,array(
                        'class'         => 'AppBundle:TipoDocumento',
                        'attr'=> array('class' => 'form-control'),
                        'label'         => 'Tipo de documento',
                        'required'      => true,
                        'data'          => $this->em->getRepository('AppBundle:TipoDocumento')->find(2)
                        ))                   
                    ->add('ruc',null,array(
                        'attr'=> array('class' => 'form-control '),
                        'label'         => 'RUC o DNI',
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
            'data_class' => 'AppBundle\Entity\Cliente',
            'empresa' => ''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_cliente';
    }


}
