<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FacturaVentaType extends AbstractType
{

    protected $em;
    protected $session;

    public function __construct(EntityManagerInterface $em,Security $security,SessionInterface $session)
    {
        $this->em       = $em;
        $this->session  = $session;
      
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $comprobantes_array = array();
        $comprobantes = $this->em->getRepository('AppBundle:ComprobanteXEmpresa')->findBy(array('empresa'=>$this->session->get('empresa'),'estado'=>1));

        foreach($comprobantes as $comprobante)
        {
            $comprobantes_array[$comprobante->getComprobante()->getNombre()] = $comprobante->getComprobante()->getNombreSistema();
        }


        $builder        
            // ->add('cliente',EntityType::class,array(
            //     'class' => 'AppBundle:Cliente',
            //     'attr'=> array('class' => 'form-control chosen-select','data-placeholder'=>'Seleccione un cliente'),
            //     'label'         => 'Cliente',
            //     'required'      => false,
            //     'mapped'        => true,
            //     'query_builder' => function(EntityRepository $er) use ($options)
            //     {
            //         $qb = $er->createQueryBuilder('c');
            //         $qb->leftJoin('c.local','l');
            //         $qb->leftJoin('l.empresa','e');
            //         $qb->where('c.estado = 1');
            //         $qb->andWhere('e.id ='.$options['empresa']);

            //         return $qb;
            //     }                   
            //     ))        
            ->add('fecha',DateType::class,array(
                'attr'=> array('class' => 'form-control setcurrentdate ','required'=>'required','placeholder'=>'Fecha'),
                'label'         => 'Fecha',
                'format' => $options['date_format'],
                'html5' => false,
                'widget' => 'single_text',
                'required'  => false,
                //'mapped' => false
                ))
            ->add('venta', VentaType::class)
            ->add('voucher',null,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control','readonly'=>'readonly','placeholder'=>'Nro.Voucher'),
                'required'      => false,
                'mapped'      => false,
                ))
            ->add('clienteNombre',null,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control ','placeholder'=>'Cliente','required'=>'required'),
                'required'      => false,
                //'mapped'        => false,
                ))
            ->add('local',null,array(
                //'class'         => 'AppBundle:Empresa',
                'label' => false,
                'attr'=> array('class' => 'form-control d-none'),
                'required'      => false,
                'data'          => $this->em->getRepository('AppBundle:EmpresaLocal')->find($options['local'])  
                ))
            ->add('caja',EntityType::class,array(
                'class'         => 'AppBundle:Caja',
                'label'         => false,
                'placeholder'   => 'Seleccione una caja',
                'attr'          => array('class' => 'form-control','required'=>'required'),
                'required'      => false,
                //'data'          => $this->em->getRepository('AppBundle:Caja')->find($options['caja']),
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('c');
                    $qb->leftJoin('c.local','l');
                    $qb->where('c.estado = 1');
                    //$qb->andWhere('el.id = abierto');
                    
                    if($options['caja'] != ''){
                        $qb->andWhere('c.id ='.$options['caja']);
                    }
                    else{
                        $qb->andWhere('l.id ='.$options['local']);
                    }

                    return $qb;
                }                 
                ))             
            ->add('numeroProforma',null,array(
                'label'         => 'Número de proforma',
                'attr'=> array('class' => 'form-control','readonly'=>'readonly','required'=>'required'),
                'required'      => false,
                ))
            ->add('numeroGuiaremision',null,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control','placeholder'=>'Nro. guia de remision'),
                'required'      => false,
                ))            
            ->add('documento',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'choices'       => $comprobantes_array,//array('Nota de venta' => 'guia','Boleta' => 'boleta','Factura' => 'factura'),                
                'label'         => false,
                'required'      => false,
                ))
            ->add('detraccion',CheckboxType::class,array(
                'data'  => false,
                'label'  => '¿Detracción?',
                'required'      => false,
                ))                                                           
            ->add('incluirIgv',CheckboxType::class,array(
                //'data'  => false,
                'label'  => 'Incluir IGV',
                'required'      => false,
                ))              
            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $factura = $event->getData();
            $form = $event->getForm();

            if (!$factura || null === $factura->getId()) {
                $form
                    ->add('documento',ChoiceType::class,array(
                        'attr'=> array('class' => 'form-control','required'=>'required'),
                        'choices'       => array('Nota de venta' => 'guia','Boleta' => 'boleta','Factura' => 'factura'),                
                        'label'         => false,
                        'data'          => 'guia',
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
            'data_class' => 'AppBundle\Entity\FacturaVenta',
            'empresa'=>'',
            'local'=>'',
            'caja'=>'',
            'date_format' => 'dd/MM/yyyy',            
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_facturaventa';
    }


}
