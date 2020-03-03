<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class PedidoVentaDetalleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cantidadPedida',null,array(
                'attr'=> array('class' => 'form-control cantidadinput'),
                'label'         => false,
                'required'      => false,
            )) 
            ->add('cantidadEntregada',null,array(
                'attr'=> array('class' => 'form-control  cantidadentregada','readonly' => 'readonly'),
                'label'         => false,
                'required'      => false,
            )) 
            ->add('precio',null,array(
                'attr'=> array('class' => 'form-control precioinput solonumeros'),
                'label'         => false,
                'required'      => false,
            )) 
            ->add('impuesto')
            ->add('subtotal',NumberType::class,array(
                'attr'=> array('class' => 'form-control subtotalinput solonumeros'),
                'label'         => false,
                'required'      => false,
                'scale'         => 3,
                'grouping'      => false
            )) 
            ->add('productoXLocal',null,array(
                'class' => 'AppBundle:ProductoXLocal',
                'label'         => false,
                'attr'=> array('class' => 'form-control '),
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('pxl');
                    $qb->setMaxResults(1);

                    return $qb;
                }              
                ))            
            ->add('pedidoVenta',EntityType::class,array(
                'class' => 'AppBundle:PedidoVenta',
                'label'         => false,
                'attr'=> array('class' => 'form-control d-none'),
                'required'      => true,                    
                ))
            ;   
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PedidoVentaDetalle'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_pedidoventadetalle';
    }


}
