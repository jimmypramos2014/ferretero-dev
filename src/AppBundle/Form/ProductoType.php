<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ProductoType extends AbstractType
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
                'label'         => 'Nombre de producto',
                'required'      => true,
                ))
            ->add('precioUnitario',MoneyType::class,array(
                'attr'=> array('class' => 'form-control solonumeros'),
                'label'         => 'Precio unitario',
                'currency'      => false,
                'scale'         => 3,
                'required'      => false,
                ))
            ->add('precioCantidad',MoneyType::class,array(
                'attr'=> array('class' => 'form-control solonumeros'),
                'label'         => 'Precio por mayor',
                'currency'      => false,
                'required'      => false,
                ))
            ->add('precioCompra',MoneyType::class,array(
                'attr'=> array('class' => 'form-control solonumeros','readonly'=>'readonly'),
                'label'         => 'Precio compra',
                'currency'      => false,
                'scale'         => 4,
                'required'      => false,
                ))
            ->add('imagen',FileType::class,array(
                'label'         => 'Imagen producto',
                'attr'=> array('class' => 'form-control'),
                'required'      => false,
                ))
            ->add('codigoBarra',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Código de barras del fabricante',
                'required'      => false,
                ))
            ->add('descripcion',TextareaType::class,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Descripción',
                'required'      => false,
                ))
            ->add('empresa')
            ->add('categoria',EntityType::class,array(
                'class'         => 'AppBundle:ProductoCategoria',
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Categoría',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('u');
                    $qb->join('u.empresa','e');

                    if($options['empresa'] != ''){
                        $qb->where('e.id ='.$options['empresa']);
                    }  
                    $qb->orderBy('u.nombre');
               
                    return $qb;
                }                
                ))
            ->add('marca',EntityType::class,array(
                'class'         => 'AppBundle:ProductoMarca',
                'attr'          => array('class' => 'form-control'),
                'label'         => 'Marca de producto',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('u');
                    $qb->join('u.empresa','e');

                    if($options['empresa'] != ''){
                        $qb->where('e.id ='.$options['empresa']);
                    }  
                    $qb->orderBy('u.nombre');
               
                    return $qb;
                }                  
                ))
            ->add('unidadVenta',EntityType::class,array(
                'attr'          => array('class' => 'form-control'),
                'class'         => 'AppBundle:ProductoUnidad',
                'label'         => 'Unidad de venta',
                'placeholder'   => 'Seleccionar unidad',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('u');
                    $qb->join('u.empresa','e');

                    if($options['empresa'] != ''){
                        $qb->where('e.id ='.$options['empresa']);
                    }  
                    $qb->orderBy('u.nombre');
               
                    return $qb;
                }                                 
                ))
            ->add('unidadCompra',EntityType::class,array(
                'attr'          => array('class' => 'form-control'),
                'class'         => 'AppBundle:ProductoUnidad',
                'label'         => 'Unidad de compra',
                'placeholder'   => 'Seleccionar unidad',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('u');
                    $qb->join('u.empresa','e');

                    if($options['empresa'] != ''){
                        $qb->where('e.id ='.$options['empresa']);
                    }  
                    $qb->orderBy('u.nombre');
                  
                    return $qb;
                }                   
                ))
            ->add('codigo',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Código',
                'required'      => true
                ))                                          
            ->add('fecha_expiracion')
            ->add('moneda',EntityType::class,array(
                'class'         => 'AppBundle:Moneda',
                'attr'          => array('class' => 'form-control'),
                'label'         => 'Moneda',
                'required'      => true               
                ))
            ->add('tipo',ChoiceType::class,array(
                'choices'       => array('Producto'=>'producto','Servicio'=>'servicio'),
                'attr'          => array('class' => 'form-control'),
                'label'         => 'Tipo',                
                'required'      => true               
                ))
            //->add('productoXLocal')                 
            ->add('productoXLocal', CollectionType::class, array(
                'entry_type' => ProductoXLocalType::class,
                'entry_options' => array('label' => false),
                'required'=>true,
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false,
            ))
            ->add('productoXPrecio', CollectionType::class, array(
                'entry_type' => ProductoXPrecioType::class,
                'entry_options' => array('label' => false),
                'required'=>false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('posicion',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Posicion',
                'required'      => false,
            ))
            ->add('peso',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Peso',
                'required'      => false,
            ))                               
            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $producto = $event->getData();
            $form = $event->getForm();

        
            if (!$producto || null === $producto->getId()) {
                $form
                    ->add('codigo',null,array(
                        'attr'=> array('class' => 'form-control'),
                        'label'         => 'Código',
                        'required'      => true,
                        'data'  => $options['codigo'],
                    ))
                    ->add('unidadVenta',EntityType::class,array(
                        'attr'          => array('class' => 'form-control'),
                        'class'         => 'AppBundle:ProductoUnidad',
                        'label'         => 'Unidad de venta',
                        'data'          => $this->em->getRepository('AppBundle:ProductoUnidad')->findOneBy(array('empresa'=>$this->session->get('empresa'),'nombre'=>'UNIDAD')) ,
                        'required'      => true,
                        'query_builder' => function(EntityRepository $er) use ($options)
                        {
                            $qb = $er->createQueryBuilder('u');
                            $qb->join('u.empresa','e');

                            if($options['empresa'] != ''){
                                $qb->where('e.id ='.$options['empresa']);
                            }  
                            $qb->orderBy('u.nombre');
                       
                            return $qb;
                        }                                 
                        ))
                    ->add('unidadCompra',EntityType::class,array(
                        'attr'          => array('class' => 'form-control'),
                        'class'         => 'AppBundle:ProductoUnidad',
                        'label'         => 'Unidad de compra',
                        'data'          => $this->em->getRepository('AppBundle:ProductoUnidad')->findOneBy(array('empresa'=>$this->session->get('empresa'),'nombre'=>'UNIDAD')),
                        'required'      => true,
                        'query_builder' => function(EntityRepository $er) use ($options)
                        {
                            $qb = $er->createQueryBuilder('u');
                            $qb->join('u.empresa','e');

                            if($options['empresa'] != ''){
                                $qb->where('e.id ='.$options['empresa']);
                            }  
                            $qb->orderBy('u.nombre');
                          
                            return $qb;
                        }                   
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
            'data_class'    => 'AppBundle\Entity\Producto',
            'codigo'        => '',
            'empresa'       => ''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_producto';
    }


}
