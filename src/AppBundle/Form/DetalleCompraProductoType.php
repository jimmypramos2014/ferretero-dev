<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;


class DetalleCompraProductoType extends AbstractType
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
            // ->add('proveedor',EntityType::class,array(
            //     'class' => 'AppBundle:Proveedor',
            //     'attr'=> array('class' => 'form-control ','maxlength'=>'8','data-placeholder'=>'Seleccione un proveedor','required'=>'required'),
            //     'label'         => 'Proveedor',
            //     'required'      => true,
            //     'mapped'        => false,
            //     'query_builder' => function(EntityRepository $er) use ($options)
            //     {
            //         $qb = $er->createQueryBuilder('p');
            //         $qb->leftJoin('p.empresa','e');
            //         $qb->where('p.estado = 1');

            //         if($options['empresa'] != ''){
            //             $qb->andWhere('e.id ='.$options['empresa']);
            //         }
                    
            //         return $qb;
            //     }                   
            //     ))
            ->add('proveedor_select',ChoiceType::class,array(
                'attr'          => array('class' => 'form-control select2 prov','required'=>'required'),
                'label'         => 'Proveedor',
                'placeholder'   => 'Seleccione un proveedor',
                'required'      => false,
                'mapped'        => false,             
                ))
            ->add('local',EntityType::class,array(
                'class' => 'AppBundle:EmpresaLocal',
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Tienda',
                'required'      => true,
                'mapped'        => false,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('el');
                    $qb->leftJoin('el.empresa','e');
                    $qb->where('e.id = '.$options['empresa']);

                    return $qb;
                }                     
                ))
            ->add('documento',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('Guía' => 'guia','Boleta' => 'boleta','Factura' => 'factura'),                
                'label'         => 'Tipo de documento',
                'multiple'      => false,
                'expanded'      => false,
                'required'      => true,
                'mapped'        => false
                ))
            ->add('numero_documento',null,array(
                'label'         => 'Número',
                'attr'=> array('class' => 'form-control','placeholder'=>'Número documento','style'=>'','data-mask'=>'AAAA-00000000','required'=>'required'),
                'required'      => true,
                'mapped'        => false
                ))
            ->add('archivo',FileType::class,array(
                'label'         => 'Adjuntar documento',
                'attr'          => array('class' => 'form-control','accept'=>'image/jpeg,image/png,image/gif,application/pdf'),
                'required'      => false,
                ))
            ->add('voucher',FileType::class,array(
                'label'         => 'Adjuntar voucher de pago',
                'attr'          => array('class' => 'form-control','accept'=>'image/jpeg,image/png,image/gif,application/pdf'),
                'required'      => false,
                'mapped'        => false
                ))                                            
            ->add('productoXLocal',ChoiceType::class,array(
                //'class' => 'AppBundle:ProductoXLocal',
                'attr'=> array('class' => 'form-control select2'),
                'label'         => 'Producto',
                'placeholder'   => 'Seleccione un producto',
                'required'      => false,
                'mapped'        => false,
                // 'query_builder' => function(EntityRepository $er) use ($options)
                // {
                //     $qb = $er->createQueryBuilder('pxl');

                //     if($options['local'] != ''){
                //         $qb->where('pxl.local ='.$options['local']);
                //     }                   

                //     return $qb;
                // }                  
                ))
            ->add('producto_nuevo',null,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control','placeholder'=>'Nombre producto','style'=>'margin-top: 32px;pointer-events:none'),
                'required'      => false,
                'mapped'        => false
                ))
            ->add('codigo_producto_nuevo',null,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control','placeholder'=>'Código producto','style'=>'margin-top: 32px;pointer-events:none'),
                'required'      => false,
                'mapped'        => false
                ))
            ->add('unidad',null,array(
                'label'         => 'Unidad',
                'attr'=> array('class' => 'form-control','readonly'=>'readonly'),
                'required'      => false,
                'mapped'        => false
                ))            
            ->add('precio',null,array(
                'label'         => 'Precio',
                'attr'=> array('class' => 'form-control solonumeros'),
                'required'      => false,
                'mapped'        => false
                ))
            ->add('cantidad',null,array(
                'label'         => 'Cantidad',
                'attr'=> array('class' => 'form-control'),
                'required'      => false,
                'mapped'        => false
                ))            
            ->add('fecha',null,array(
                'attr'=> array('class' => 'form-control setcurrentdate '),
                'label'         => 'Fecha',
                'format' => $options['date_format'],
                'html5' => false,
                'widget' => 'single_text',
                'required'         => true,
                'mapped'        => false,
                ))
            ->add('moneda',EntityType::class,array(
                'class' => 'AppBundle:Moneda',
                'label'         => 'Moneda',
                'attr'=> array('class' => 'form-control'),
                'required'      => true,
                'mapped'        => false,
                 
                ))
            ->add('observacion',TextareaType::class,array(
                'label'         => 'Observaciones',
                'attr'=> array('class' => 'form-control'),
                'required'      => false,
                'mapped'        => false
                ))
            ->add('forma_pago',EntityType::class,array(
                'class' => 'AppBundle:FormaPago',
                'attr'=> array('class' => 'form-control','required'=>'required'),
                //'choices'       => array('Contado' => '1','Crédito' => '2','Tarjeta de crédito' => '3','Nota de crédito' => '4'),
                'label'         => 'Forma de pago',
                'required'      => true,
                'mapped'        => false,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('e');
                    //$qb->where('e.estado = 1');
                    $qb->where('e.id IN (1,2,4)');

                    return $qb;
                }                  
                ))
            ->add('numero_dias',IntegerType::class,array(
                'attr'=> array('class' => 'form-control solonumeros'),
                'label'         => 'Días de crédito',
                'required'      => false,
                'mapped'        => false
                ))
            ->add('doc_rel_notacredito',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nro. Factura relacionada',
                'required'      => false,
                'mapped'        => false
                ))
            ->add('valor_notacredito',null,array(
                'attr'=> array('class' => 'form-control solonumeros'),
                'label'         => 'Monto',
                'required'      => false,
                'mapped'        => false
                ))
            ->add('num_notacredito',null,array(
                'label'         => 'Nro. Nota de crédito',
                'attr'=> array('class' => 'form-control '),
                'required'      => false,
                'mapped'        => false
                ))
            ->add('condicion',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('Pagado' => 'pagado','Pendiente' => 'pendiente'),                
                'label'         => 'Condición del pago',
                'multiple'      => false,
                'expanded'      => false,
                'required'      => true,
                'mapped'        => false
                ))                                                                                                                                                           
            ;


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $detalleCompra = $event->getData();
            $form = $event->getForm();

            if (!$detalleCompra || null === $detalleCompra->getId()) {
                $form
                    ->add('local',EntityType::class,array(
                        'class' => 'AppBundle:EmpresaLocal',
                        'attr'=> array('class' => 'form-control'),
                        'label'         => 'Tienda',
                        'required'      => true,
                        'mapped'        => false,
                        'data'          => $this->em->getRepository('AppBundle:EmpresaLocal')->find($this->session->get('local')),
                        'query_builder' => function(EntityRepository $er) use ($options)
                        {
                            $qb = $er->createQueryBuilder('el');
                            $qb->leftJoin('el.empresa','e');
                            $qb->where('e.id = '.$options['empresa']);

                            return $qb;
                        }                   
                    ))
                    // ->add('documento_relacionado_notacredito',null,array(
                    //     'attr'=> array('class' => 'form-control'),
                    //     'label'         => 'Nro. Factura relacionada',
                    //     'required'      => false,
                    //     'data'        => ''
                    //     ))
                    // ->add('monto_notacredito',NumberType::class,array(
                    //     'attr'=> array('class' => 'form-control solonumeros'),
                    //     'label'         => 'Monto',
                    //     'required'      => false,
                    //     'data'        => 0
                    //     ))
                    // ->add('numero_notacredito',null,array(
                    //     'label'         => 'Nro. Nota de crédito',
                    //     'attr'=> array('class' => 'form-control '),
                    //     'required'      => false,
                    //     'data'        => ''
                    //     ))     
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
            'data_class' => 'AppBundle\Entity\FacturaCompra',
            'local'=>'',
            'empresa'=>'',
            'date_format' => 'dd/MM/yyyy',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_detallecompra_producto';
    }


}
