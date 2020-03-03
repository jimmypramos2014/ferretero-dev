<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;

class PedidoVentaType extends AbstractType
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
            //->add('numeroPedido')
            ->add('fecha',DateType::class,array(
                'attr'=> array('class' => 'form-control setcurrentdate ','required'=>'required','placeholder'=>'Fecha'),
                'label'         => 'Fecha',
                'format' => $options['date_format'],
                'html5' => false,
                'widget' => 'single_text',
                'required'  => true,
            ))
            ->add('documento',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'choices'       => array('Factura' => 'factura'),//'Boleta' => 'boleta',            
                'label'         => 'Documento',
                'required'      => true,
            ))            
            ->add('estado',HiddenType::class,array(
                'data'  => true,
            ))
            ->add('terminosPago')
            ->add('descuento')
            ->add('monto',HiddenType::class,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => false,
                'required'      => false,
            ))
            ->add('montoACuenta',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Monto a Cuenta',
                'empty_data'    => '0', 
                'required'      => true,
            ))             
            ->add('impuesto',HiddenType::class,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => false,
                'required'      => false,
            )) 
            //->add('local')
            ->add('caja',EntityType::class,array(
                'class'         => 'AppBundle:Caja',
                'label'         => 'Caja',
                'placeholder'   => 'Seleccione una caja',
                'attr'          => array('class' => 'form-control','required'=>'required'),
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('c');
                    $qb->leftJoin('c.local','l');
                    $qb->where('c.estado = 1');
                    
                    if($options['caja'] != '')
                    {
                        $qb->andWhere('c.id ='.$options['caja']);
                    }
                    else
                    {
                        $qb->andWhere('l.id ='.$options['local']);
                    }

                    return $qb;
                }                 
            ))     
            // ->add('cliente',EntityType::class,array(
            //     'class' => 'AppBundle:Cliente',
            //     'attr'=> array('class' => 'form-control chosen-select','required'=>'required'),
            //     'label'         => 'Cliente',
            //     'placeholder'   => 'Seleccionar cliente',
            //     'required'      => true,
            //     'query_builder' => function(EntityRepository $er) use ($options)
            //     {
            //         $qb = $er->createQueryBuilder('c');
            //         $qb->leftJoin('c.local','l');
            //         $qb->leftJoin('l.empresa','e');
            //         $qb->where('c.estado = 1');
            //         $qb->andWhere('e.id ='.$options['empresa']);
            //         $qb->andWhere('c.tipoDocumento = 2');

            //         return $qb;
            //     }                   
            // ))
            // ->add('cliente_select',ChoiceType::class,array(
            //     'attr'=> array('class' => 'form-control select2','required'=>'required'),
            //     'label'         => 'Cliente',
            //     'placeholder'   => 'Seleccionar cliente',
            //     'required'      => true,
            //     'data'          => 'guia',
            //     'mapped'        => false,             
            // ))                 
            ->add('formaPago',EntityType::class,array(
                'class' => 'AppBundle:FormaPago',
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => 'Pago',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('e');
                    $qb->where('e.estado = 1');

                    return $qb;
                }                   
            ))
            ->add('moneda',EntityType::class,array(
                'class' => 'AppBundle:Moneda',
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => 'Moneda',
                'required'      => true,
            ))
            ->add('incluirIgv',CheckboxType::class,array(
                //'data'  => false,
                'label'  => 'Incluir IGV',
                'required'      => false,
            ))                                         
            ->add('pedidoVentaDetalle', CollectionType::class, array(
                'entry_type' => PedidoVentaDetalleType::class,
                'entry_options' => array('label' => false),
                'required'=>false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('diasCredito',IntegerType::class,array(
                'attr'=> array('class' => 'form-control'),
                //'choices'       => array('0' => '0','5' => '5','10' => '10','15' => '15','20' => '20','30' => '30','45' => '45','60' => '60'),
                'label'         => 'Días de Crédito',
                'data'          => 0,
                'required'      => false,
            ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $pedidoVenta = $event->getData();
            $form = $event->getForm();

            if (!$pedidoVenta || null === $pedidoVenta->getId()) {
                $form
                    ->add('caja',EntityType::class,array(
                        'class'         => 'AppBundle:Caja',
                        'label'         => 'Caja',
                        'placeholder'   => 'Seleccione una caja',
                        'attr'          => array('class' => 'form-control','required'=>'required'),
                        'required'      => true,
                        'data'          => ($options['caja'] != '') ? $this->em->getRepository('AppBundle:Caja')->find($options['caja']) : '',
                        'query_builder' => function(EntityRepository $er) use ($options)
                        {
                            $qb = $er->createQueryBuilder('c');
                            $qb->leftJoin('c.local','l');
                            $qb->where('c.estado = 1');
                            
                            if($options['caja'] != '')
                            {
                                $qb->andWhere('c.id ='.$options['caja']);
                            }
                            else
                            {
                                $qb->andWhere('l.id ='.$options['local']);
                            }

                            return $qb;
                        }                 
                    ))
                    ->add('incluirIgv',CheckboxType::class,array(
                        'data'  => true,
                        'label'  => 'Incluir IGV',
                        'required'      => false,
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
            'data_class' => 'AppBundle\Entity\PedidoVenta',
            'empresa' => '',
            'local' => '',
            'caja' => '',
            'date_format' => 'dd/MM/yyyy'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_pedidoventa';
    }


}
