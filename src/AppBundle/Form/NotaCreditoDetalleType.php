<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class NotaCreditoDetalleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cantidad',null,array(
                'attr'=> array('class' => 'form-control cantidadinput'),
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
            ->add('descuento',NumberType::class,array(
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
            ->add('notaCredito',EntityType::class,array(
                'class' => 'AppBundle:NotaCredito',
                'label'         => false,
                'attr'=> array('class' => 'form-control d-none'),
                'required'      => true,                    
                ))
            ->add('tipoImpuesto',null,array(
                'class' => 'AppBundle:TipoImpuesto',
                'label'         => false,
                'attr'=> array('class' => 'form-control tipoimpuesto'),
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('ti');

                    return $qb;
                }              
                ))               
            ;   
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\NotaCreditoDetalle'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_notacreditodetalle';
    }


}
