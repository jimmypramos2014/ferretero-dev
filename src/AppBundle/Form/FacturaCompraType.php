<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class FacturaCompraType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fecha',DateType::class,array(
                'attr'=> array('class' => 'form-control setcurrentdate ','required'=>'required','placeholder'=>'Fecha'),
                'label'         => 'Fecha',
                'format' => $options['date_format'],
                'html5' => false,
                'widget' => 'single_text',
                'required'  => false,
                //'mapped' => false
                ))
            //->add('estado')
            // ->add('ticket',null,array(
            //     'label'         => false,
            //     'attr'=> array('class' => 'form-control','readonly'=>'readonly','placeholder'=>'Nro.Ticket'),
            //     'required'      => true,
            //     ))
            ->add('documento',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'choices'       => array('Guía' => 'guia','Boleta' => 'boleta','Factura' => 'factura'),                
                'label'         => 'Tipo de documento',
                'required'      => true,
                ))   
            ->add('compra', CompraType::class)
            ->add('proveedor',EntityType::class,array(
                'class' => 'AppBundle:Proveedor',
                'attr'=> array('class' => 'form-control chosen-select','data-placeholder'=>'Seleccione un proveedor'),
                'label'         => 'Proveedor',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('p');
                    $qb->leftJoin('p.empresa','e');
                    $qb->where('p.estado = 1');
                    $qb->andWhere('e.id ='.$options['empresa']);

                    return $qb;
                }
                ))
            ->add('numero_documento',null,array(
                'label'         => 'Número',
                'attr'=> array('class' => 'form-control','placeholder'=>'Número documento','style'=>'','data-mask'=>'AAAA-00000000','required'=>'required'),
                'required'      => true,
                ))
            ->add('local',EntityType::class,array(
                'class' => 'AppBundle:EmpresaLocal',
                'attr'=> array('class' => 'form-control '),
                'label'         => 'Local',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('p');
                    $qb->leftJoin('p.empresa','e');
                    $qb->where('p.estado = 1');
                    $qb->andWhere('e.id ='.$options['empresa']);

                    return $qb;
                }
                ))
            ->add('observacion',TextareaType::class,array(
                'label'         => 'Observaciones',
                'attr'=> array('class' => 'form-control'),
                'required'      => false,
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
                ))
            ->add('documento_relacionado_notacredito',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nro. Factura relacionada',
                'required'      => false,
                ))
            ->add('monto_notacredito',null,array(
                'attr'=> array('class' => 'form-control solonumeros'),
                'label'         => 'Monto',
                'required'      => false,
                ))
            ->add('numero_notacredito',null,array(
                'label'         => 'Nro. Nota de crédito',
                'attr'=> array('class' => 'form-control '),
                'required'      => false,
                ))                                                                                 
            ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\FacturaCompra',
            'empresa' => '',
            'local' => '',
            'date_format' => 'dd/MM/yyyy',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_facturacompra';
    }


}
