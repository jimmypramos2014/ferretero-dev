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
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DetalleVentaClienteType extends AbstractType
{
    protected $security;
    protected $usuario;
    protected $em;
    protected $session;

    public function __construct(Security $security,EntityManagerInterface $em,SessionInterface $session)
    {
        $this->security = $security;
        $this->usuario  = $this->security->getUser();
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
            ->add('vendedor',EntityType::class,array(
                'class' => 'AppBundle:Empleado',
                'attr'=> array('class' => 'form-control d-none'),
                'label'         => false,
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('e');
                    $qb->leftJoin('e.usuario','u');
                    $qb->where('e.estado = 1');

                    $qb->andWhere('u.id ='.$this->usuario->getId());

                    return $qb;
                }                 
                ))
            ->add('forma_pago',EntityType::class,array(
                'class' => 'AppBundle:FormaPago',
                'attr'=> array('class' => 'form-control','required'=>'required'),
                //'choices'       => array('Contado' => '1','Crédito' => '2','Tarjeta de crédito' => '3','Nota de crédito' => '4'),
                'label'         => false,
                'required'      => false,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('e');
                    $qb->where('e.estado = 1');

                    return $qb;
                }                  
                ))
            ->add('numero_dias',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('0' => '0','5' => '5','10' => '10','15' => '15','20' => '20','30' => '30','45' => '45','60' => '60'),
                'label'         => false,
                'placeholder'   => 'Días de crédito',
                'data'          => '0',
                'required'      => false,
                ))
            ->add('monto_acuenta',NumberType::class,array(
                'attr'=> array('class' => 'form-control solonumeros','placeholder'=>'Monto a cta.','readonly'=>'readonly'),
                'label'         => false,
                //'placeholder'   => 'Monto a cuenta',
                //'data'          => '0',
                'required'      => false,
                ))            
            // ->add('cliente',EntityType::class,array(
            //     'class' => 'AppBundle:Cliente',
            //     'attr'=> array('class' => 'form-control chosen-select','data-placeholder'=>'Seleccione un cliente'),
            //     'label'         => false,
            //     'required'      => false,
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
            ->add('cliente_select',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control select2','required'=>'required'),
                'label'         => false,
                'placeholder'   => 'Seleccione un cliente',
                'required'      => false,
                'data'          => 'guia',
                //'mapped'        => false,             
                ))                   
            ->add('documento',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'choices'       => $comprobantes_array,//array('Nota de venta' => 'guia','Boleta' => 'boleta','Factura' => 'factura'),                
                'label'         => false,
                'data'          => 'guia',
                'required'      => false,
                ))
            ->add('numero_voucher',null,array(
                'label'         => false,
                'attr'          => array('class' => 'form-control','readonly'=>'readonly','placeholder'=>'Voucher'),
                'required'      => false,
                ))
            ->add('cliente_nuevo',null,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control','placeholder'=>'Cliente','required'=>'required'),
                'required'      => false,
                ))
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))
            ->add('ticket',null,array(
                'label'         => 'Num.Ticket ',
                'attr'=> array('class' => 'form-control'),
                'required'      => false,
                )) 
            ->add('motivo_anulacion',TextareaType::class,array(
                'label'         => 'Motivo',
                'attr'=> array('class' => 'form-control'),
                'required'      => false,
                ))
            ->add('condicion',CheckboxType::class,array(
                'data'  => false,
                'label'  => 'Entrega pendiente',
                'required'      => false,
                ))
            ->add('numero_guiaremision',null,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control','placeholder'=>'Guia de remisión'),
                'required'      => false,
                ))
            ->add('movimiento_almacen',CheckboxType::class,array(
                'data'  => true,
                'label'  => 'Movimiento de almacen',
                'required'  => false,
                ))
            ->add('fecha',DateType::class,array(
                'attr'=> array('class' => 'form-control setcurrentdate ','required'=>'required','placeholder'=>'Fecha'),
                'label'         => false,
                'format' => $options['date_format'],
                'html5' => false,
                'widget' => 'single_text',
                'required'  => false,
                ))
            ->add('numero_documento',null,array(
                'label'         => false,
                'attr'          => array('class' => 'form-control','placeholder'   => 'Nro. documento','data-mask'=>'AAAA-00000000'),
                'required'      => false,
                ))
            ->add('numero_proforma',null,array(
                'label'         => false,
                'attr'          => array('class' => 'form-control','placeholder'=>'Nro. proforma relacionada'),
                'required'      => false,
                ))
            ->add('enviarFactura',CheckboxType::class,array(
                'data'  => false,
                'label'  => 'Enviar factura',
                'required'      => false,
                ))
            ->add('detraccion',CheckboxType::class,array(
                'data'  => false,
                'label'  => 'Detracción',
                'required'      => false,
                ))
            ->add('validezOferta',null,array(
                'label'         => false,
                'attr'          => array('class' => 'form-control','placeholder'=>'Validez de la oferta (Ej. 3 dias)'),
                'required'      => false,
                ))
            ->add('plazoEntrega',null,array(
                'label'         => false,
                'attr'          => array('class' => 'form-control','placeholder'=>'Plazo de entrega  (Ej. INMEDIATA)'),
                'required'      => false,
                ))
            ->add('empleadoCotiza',null,array(
                'label'         => false,
                'attr'          => array('class' => 'form-control','placeholder'=>'Nombre de quien cotiza'),
                'required'      => false,
                ))
            ->add('correoEmpleadoCotiza',null,array(
                'label'         => false,
                'attr'          => array('class' => 'form-control','placeholder'=>'Correo de quien cotiza'),
                'required'      => false,
                ))
            ->add('telefonoCliente',null,array(
                'label'         => false,
                'attr'          => array('class' => 'form-control','placeholder'=>'Teléfono del cliente'),
                'required'      => false,
                ))
            ->add('incluirIgv',CheckboxType::class,array(
                'data'  => true,
                'label'  => 'Incluir IGV',
                'required'      => false,
                ))
            ->add('moneda',EntityType::class,array(
                'class' => 'AppBundle:Moneda',
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => false,
                'required'      => false,
                ))
            ->add('activarPrecioRango',CheckboxType::class,array(
                'data'  => false,
                'label'  => 'Precio por rangos',
                'required'      => false
                ))            
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //'data_class' => 'AppBundle\Entity\Producto'
            'empresa'=>'',
            'date_format' => 'dd/MM/yyyy',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_detalleventa_cliente';
    }


}
