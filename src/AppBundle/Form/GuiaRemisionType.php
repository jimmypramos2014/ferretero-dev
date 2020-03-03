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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class GuiaRemisionType extends AbstractType
{

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em       = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $distritos      = $this->em->getRepository('AppBundle:Distrito')->findAll();

        $ubigeos = array();
        foreach($distritos as $distrito)
        {

            $ubigeos[$distrito->getProvincia()->getDepartamento()->getNombre()][$distrito->getProvincia()->getNombre()][$distrito->getNombre()] = $distrito->getCodigo();            

        }

        $builder
            ->add('fechaEmision',DateType::class,array(
                'attr'=> array('class' => 'form-control setcurrentdate ','required'=>'required','placeholder'=>'Fecha'),
                'label'         => 'Fecha emisión',
                'format' => $options['date_format'],
                'html5' => false,
                'widget' => 'single_text',
                'required'  => true,
            ))
            ->add('tipo',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'choices'       => array('Remitente' => 'remitente','Transportista' => 'transportista'),                
                'label'         => 'Tipo documento',
                'required'      => true,
            )) 
            //->add('serie')
            //->add('numero')
            ->add('fechaInicioTraslado',DateType::class,array(
                'attr'=> array('class' => 'form-control setcurrentdate ','required'=>'required','placeholder'=>'Fecha'),
                'label'         => 'Fecha inicio traslado',
                'format' => $options['date_format'],
                'html5' => false,
                'widget' => 'single_text',
                'required'  => true,
            ))
            ->add('motivoTraslado',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'choices'       => array('VENTA' => '01','VENTA SUJETA A CONFIRMACION DEL COMPRADOR' => '14','COMPRA' => '02','TRASLADO ENTRE ESTABLECIMIENTOS DE LA MISMA EMPRESA' => '04','TRASLADO EMISOR ITINERANTE CP' => '18','IMPORTACION' => '08','EXPORTACION' => '09','TRASLADO A ZONA PRIMARIA' => '19','OTROS' => '13',),                
                'label'         => 'Motivo traslado',
                'required'      => true,
            )) 
            ->add('tipoTransporte',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'choices'       => array('Público' => 'publico','Privado' => 'privado'),                
                'label'         => 'Tipo transporte',
                'required'      => true,
            )) 
            ->add('peso',null,array(
                'attr'=> array('class' => 'form-control solonumeros','required'=>'required'),
                'label'         => 'Peso',
                'required'      => true,
            )) 
            ->add('numeroBultos',null,array(
                'attr'=> array('class' => 'form-control solonumeros','required'=>'required'),
                'label'         => 'Num.Bultos',
                'required'      => true,
            )) 
            ->add('transportistaDocumento',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'choices'       => array('DNI' => 'dni','RUC' => 'ruc'),
                'label'         => 'Tipo doc. transportista',
                'required'      => true,
            )) 
            ->add('transportistaDocumentoNumero',null,array(
                'attr'=> array('class' => 'form-control solonumeros','required'=>'required'),
                'label'         => 'Num.Documento transportista',
                'required'      => true,
            )) 
            ->add('transportistaDenominacion',null,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => 'Denominacion transportista',
                'required'      => true,
            )) 
            ->add('transportistaPlaca',null,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => 'Placa transportista',
                'required'      => true,
            )) 
            ->add('conductorDocumento',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'choices'       => array('DNI' => 'dni','RUC' => 'ruc'),
                'label'         => 'Tipo doc. conductor',
                'required'      => true,
            )) 
            ->add('conductorDocumentoNumero',null,array(
                'attr'=> array('class' => 'form-control solonumeros','required'=>'required'),
                'label'         => 'Num.Documento conductor',
                'required'      => true,
            )) 
            ->add('conductorNombre',null,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => 'Nombre conductor',
                'required'      => true,
            )) 
            ->add('puntoPartida',null,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => 'Punto de partida',
                'required'      => true,
            )) 
            ->add('puntoLlegada',null,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => 'Punto de llegada',
                'required'      => true,
            )) 
            ->add('ubigeoPartida',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control chosen-select','required'=>'required'),
                'choices'       => $ubigeos,
                'placeholder'   => 'Seleccionar',
                'label'         => 'Ubigeo partida',
                'required'      => true,
            )) 
            ->add('ubigeoLlegada',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control chosen-select','required'=>'required'),
                'choices'       => $ubigeos,
                'placeholder'   => 'Seleccionar',
                'label'         => 'Ubigeo llegada',
                'required'      => true,
            )) 
            ->add('observacion',TextareaType::class,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Observación',
                'required'      => false,
            ))
            ->add('cliente',EntityType::class,array(
                'class' => 'AppBundle:Cliente',
                'label' => 'Cliente',
                'attr'  => array('class' => 'form-control chosen-select','required'=>'required'),
                'placeholder' => 'Seleccionar cliente',
                'required'  => true,
                'mapped' => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('c');
                    $qb->join('c.local','l');
                    $qb->join('l.empresa','e');
                    $qb->where('c.estado = 1');
                    $qb->andWhere('e.id = '.$options['empresa']);
                    $qb->andWhere('c.tipoDocumento = 2 ');
                    $qb->orderBy('c.razonSocial','ASC');

                    return $qb;
                }                    
            ))            
            ->add('facturaVenta',EntityType::class,array(
                'class' => 'AppBundle:FacturaVenta',
                'label' => 'Factura(s) relacionada(s)',
                'attr'  => array('class' => 'form-control chosen-select'),
                'required'  => true,
                'multiple' => true,
                'mapped' => false,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('f');
                    $qb->join('f.venta','v');
                    $qb->join('f.local','l');
                    $qb->join('l.empresa','e');
                    $qb->where('v.estado = 1');
                    $qb->andWhere('e.id = '.$options['empresa']);
                    $qb->andWhere("f.documento = 'factura' ");

                    $qb->orderBy('f.fecha','DESC');

                    return $qb;
                }                    
            ));

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\GuiaRemision',
            'date_format' => 'dd/MM/yyyy',
            'empresa' => ''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_guiaremision';
    }



}
