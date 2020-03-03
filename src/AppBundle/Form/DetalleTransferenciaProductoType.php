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
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;



class DetalleTransferenciaProductoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('local_inicio',EntityType::class,array(
                'class' => 'AppBundle:EmpresaLocal',
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Tienda',
                'placeholder'   => 'Seleccionar origen',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('el');

                    if($options['empresa'] != ''){
                        $qb->where('el.empresa ='.$options['empresa']);
                    }
                    

                    return $qb;
                }                  
                ))
            ->add('local_fin',EntityType::class,array(
                'class' => 'AppBundle:EmpresaLocal',
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Tienda a transferir',
                'placeholder'   => 'Seleccionar destino',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('el');

                    if($options['empresa'] != ''){
                        $qb->where('el.empresa ='.$options['empresa']);
                    }
                    

                    return $qb;
                }                  
                ))
            ->add('documento',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('Guía de Transferencia' => 'guia_transferencia'),                
                'label'         => 'Tipo de documento',
                'multiple'      => false,
                'expanded'      => false,
                'required'      => true,
                ))
            ->add('numero_documento',HiddenType::class,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control','placeholder'=>'Número documento','style'=>'margin-top: 32px;'),
                'required'      => false,
                ))            
            ->add('productoXLocal',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control select2'),
                'label'         => 'Producto',
                'required'      => false,
               
                ))
            ->add('codigo_producto',null,array(
                'label'         => 'Código',
                'attr'=> array('class' => 'form-control','style'=>'pointer-events:none'),
                'required'      => false,
                ))
            ->add('descripcion',TextareaType::class,array(
                'label'         => 'Descripción',
                'attr'=> array('class' => 'form-control','style'=>''),
                'required'      => false,
                ))
            ->add('precio',null,array(
                'label'         => 'Precio Costo',
                'attr'=> array('class' => 'form-control'),
                'required'      => true,
                ))
            ->add('cantidad',null,array(
                'label'         => 'Cantidad',
                'attr'=> array('class' => 'form-control'),
                'required'      => true,
                ))
            ->add('stock',null,array(
                'label'         => 'Stock',
                'attr'=> array('class' => 'form-control','style'=>'pointer-events:none'),
                'required'      => false,
                ))                              
            ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //'data_class' => 'AppBundle\Entity\Producto'
            'local'=>'',
            'empresa'=>''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_detalletransferencia_producto';
    }


}
