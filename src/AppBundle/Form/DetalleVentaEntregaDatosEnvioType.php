<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class DetalleVentaEntregaDatosEnvioType extends AbstractType
{


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('puntoPartida',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Punto de partida',
                'required'      => false,
                ))
            ->add('puntoLlegada',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Punto de llegada',
                'required'      => false,
                ))
            ->add('remitente',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nombre de remitente',
                'required'      => false,
                ))
            ->add('destinatario',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nombre de destinatario',
                'required'      => false,
                ))
            ->add('rucRemitente',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nro RUC remitente',
                'required'      => false,
                ))
            ->add('rucDestinatario',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nro RUC destinatario',
                'required'      => false,
                ))
            ->add('fechaTraslado',null,array(
                'attr'          => array('class' => 'form-control fecha-transporte','readonly'=>'readonly'),
                'label'         => 'Fecha de inicio del traslado',
                'required'      => false,
                'html5'         => false,
                'widget'        => 'single_text',
                'format'        => $options['date_format'],                
                ))
            ->add('costoMinimo',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Costo mínimo',
                'required'      => false,
                ))
            ->add('marca',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Marca',
                'required'      => false,
                ))
            ->add('placa',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Placa',
                'required'      => false,
                ))
            ->add('constanciaInscripcion',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Constancia de Inscripción',
                'required'      => false,
                ))
            ->add('licenciaConducir',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nro de licencia de conducir',
                'required'      => false,
                ))
            ->add('transporte',EntityType::class,array(
                'attr'          => array('class' => 'form-control'),
                'class'         => 'AppBundle:Transporte',
                'label'         => 'Empresa de transporte',
                'placeholder'   => 'Seleccionar empresa de transporte',
                'required'      => false,
                'query_builder' => function(EntityRepository $er) use ($options)
                    {
                        $qb = $er->createQueryBuilder('t');
                        $qb->leftJoin('t.empresa','e');
                        $qb->where('e.id = '.$options['empresa']);
                        $qb->orderBy('t.nombre');
                        return $qb;
                    }                              
                ))
            ->add('identificador',HiddenType::class,array(
                'attr'      => array('class' => 'form-control'),
                'data'      => '',
                'mapped'    => false,
                'required'  => false,
                ))
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\DetalleVentaEntregaDatosEnvio',
            'empresa'    => '',
            'date_format' => 'dd/MM/yyyy',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_detalleventaentregadatosenvio';
    }


}
