<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class GastoType extends AbstractType
{


    protected $em;
    protected $activedate;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em       = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('monto',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Monto',
                'required'      => true,
                ))
            ->add('fecha',null,array(
                'attr'=> array('class' => 'form-control gasto '),
                'label'         => 'Fecha',
                'format' => $options['date_format'],
                'html5' => false,
                'widget' => 'single_text'
                ))                 
            ->add('empresa',null,array(
                'attr'=> array('class' => 'd-none'),
                'label'=> false,
                'data'  => $options['empresa'],
                ))
            ->add('numero_documento',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'=> 'Num.Documento',
                'required'  => false,
                ))            
            ->add('local',EntityType::class,array(
                'class' => 'AppBundle:EmpresaLocal',
                'attr'=> array('class' => 'form-control'),
                'label'=> 'Local',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('p');
                    $qb->leftJoin('p.empresa','e');
                    $qb->where('e.id = '.$options['empresa']->getId());
                    $qb->orderBy('p.nombre');
                    return $qb;
                }                    
                ))            
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))
            // ->add('proveedor',ChoiceType::class,array(
            //     'attr'          => array('class' => 'form-control select2 prov','required'=>'required'),
            //     'label'         => 'Proveedor',
            //     'placeholder'   => 'Seleccione un proveedor',
            //     'required'      => false,
            //     'invalid_message' => 'That is not a valid issue number',   
            //     ))            
            ->add('proveedor',EntityType::class,array(
                'class' => 'AppBundle:Proveedor',
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Proveedor',
                'placeholder'   => 'Seleccionar proveedor',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('p');
                    $qb->leftJoin('p.empresa','e');
                    $qb->leftJoin('p.proveedorXTipo','t');
                    $qb->where('e.id = '.$options['empresa']->getId());
                    $qb->andWhere('t.id = 1');
                    $qb->orderBy('p.nombre');
                    return $qb;
                }                 
                ))
            ->add('servicio',EntityType::class,array(
                'class' => 'AppBundle:ProveedorServicio',
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Servicio',
                'placeholder'   => 'Seleccionar servicio',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('s');
                    $qb->leftJoin('s.empresa','e');
                    $qb->where('e.id = '.$options['empresa']->getId());
                    $qb->orderBy('s.nombre');
                    return $qb;
                }                 
                ))
            ->add('condicion',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Estado del pago',
                'choices'       => array('Pendiente' => 0,'Pagado' => 1),
                //'multiple'      => false,
                'required'      => true,
                //'expanded'      => true,
                ))
            ->add('archivo',FileType::class,array(
                'label'         => 'Adjuntar documento',
                'attr'          => array('class' => 'form-control'),
                'required'      => false,
                ))
            ->add('observacion',TextareaType::class,array(
                'label'         => 'Observaciones',
                'attr'=> array('class' => 'form-control'),
                'label'=> 'Observaciones',
                'required'  => false,
                ))                   
            ;



        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $gasto = $event->getData();
            $form = $event->getForm();

            if (!$gasto || null === $gasto->getId()) {
                $form
                    ->add('condicion',ChoiceType::class,array(
                        'attr'=> array('class' => 'form-control'),
                        'label'         => 'Estado del pago',
                        'choices'       => array('Pendiente' => 0,'Pagado' => 1),
                        //'multiple'      => false,
                        'required'      => true,
                        //'expanded'      => true,
                        'data'          => '0',
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
            'data_class' => 'AppBundle\Entity\Gasto',
            'empresa'=>'',
            'date_format' => 'dd/MM/yyyy',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_gasto';
    }


}
