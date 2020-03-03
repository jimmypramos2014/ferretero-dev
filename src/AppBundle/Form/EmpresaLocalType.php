<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class EmpresaLocalType extends AbstractType
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
                'label'         => 'Nombre',
                'required'      => true,
                ))
            ->add('direccion',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Dirección',
                'required'      => false,
                ))
            ->add('codigo',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Código',
                'required'      => true,
                ))
            ->add('telefono',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Teléfono',
                'required'      => false,
                ))
            ->add('limite_venta',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Límite venta mensual (S./)',
                'required'      => false,
                ))
            ->add('empresa',EntityType::class,array(
                'attr'=> array('class' => 'form-control'),
                'class' => 'AppBundle:Empresa',
                'label'         => 'Empresa',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('e');

                    if($options['empresa'] != ''){
                        $qb->where('e.id ='.$options['empresa']);
                    }                    

                    return $qb;
                }                     
                ))
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
            ->add('serieGuiaremision',null,array(
                'attr'=> array('class' => 'form-control','data-mask'=>'AAAA'),
                'label'         => 'Número de serie Guia remisión',
                'required'      => true,
                ))                  
            ->add('prefijoVoucher',null,array(
                'attr'=> array('class' => 'form-control','data-mask'=>'AAAA'),
                'label'         => 'Prefijo en voucher de venta',
                'required'      => true,
                ))
            ->add('serieBoleta',null,array(
                'attr'=> array('class' => 'form-control','data-mask'=>'AAAA'),
                'label'         => 'Número de serie en boleta',
                'required'      => true,
                ))
            ->add('serieFactura',null,array(
                'attr'=> array('class' => 'form-control','data-mask'=>'AAAA'),
                'label'         => 'Número de serie en factura',
                'required'      => true,
                ))                                                                           
            ->add('url_facturacion',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'URL Facturacion',
                'required'      => false,
                ))                
            ->add('token_facturacion',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Token facturación',
                'required'      => false,
                ))
            ->add('facturacion_electronica',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Generar facturación electrónica',
                'required'      => true,
                'choices'       => array('NO' => 0,'SI' => 1),
                ))
            ->add('imagenProductoDefault',FileType::class,array(
                'label'         => 'Imagen de producto por defecto',
                'attr'=> array('class' => 'form-control'),
                'required'      => false,
                ))
            ->add('imagenCategoriaDefault',FileType::class,array(
                'label'         => 'Imagen de categoría por defecto',
                'attr'=> array('class' => 'form-control'),
                'required'      => false,
                ))
            ->add('ventaNegativo',CheckboxType::class,array(
                //'data'  => false,
                'label'  => '¿Venta en negativo?',
                'required'      => false,
                ))
            ->add('email',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Correo electrónico',
                'required'      => false,
                ))
            ->add('mostrarGuiaRemision',CheckboxType::class,array(
                //'data'  => false,
                'label'  => 'Guia de remisión',
                'required'      => false,
                ))               
            ;


        // $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
        //     $local = $event->getData();
        //     $form = $event->getForm();

        //     if (!$local || null === $local->getId()) {
        //         $form
        //             ->add('departamento',EntityType::class,array(
        //                 'class'         => 'AppBundle:Departamento',
        //                 'attr'          => array('class' => 'form-control'),
        //                 'placeholder'   => 'Seleccionar departamento',
        //                 'label'         => 'Departamento',
        //                 'required'      => false,
        //                 'mapped'        => false,
        //                 'data'          => $this->em->getRepository('AppBundle:Departamento')->find($this->session->get('departamento'))  
        //             ))
        //             ->add('provincia',EntityType::class,array(
        //                 'class' => 'AppBundle:Provincia',
        //                 'attr'=> array('class' => 'form-control'),
        //                 'label'         => 'Provincia',
        //                 'placeholder'   => 'Seleccionar provincia',
        //                 'required'      => false,
        //                 'mapped'        => false,
        //                 'query_builder' => function(EntityRepository $er) use ($options)
        //                 {
        //                     $qb = $er->createQueryBuilder('p');
        //                     $qb->leftJoin('p.departamento','d');
        //                     $qb->where('d.id = '.$this->session->get('departamento'));

        //                     return $qb;
        //                 }                 
        //             ));                    
        //     }
        // });

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\EmpresaLocal',
            'empresa'=>''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_empresalocal';
    }


}
