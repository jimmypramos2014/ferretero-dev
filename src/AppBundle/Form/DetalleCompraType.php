<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;



class DetalleCompraType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cantidad',NumberType::class,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control cantidadinput solonumeros'),
                'required'      => false,
                ))
            ->add('precio',NumberType::class,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control precioinput solonumeros'),
                'scale' => 5,
                'required'      => false,
                ))            
            ->add('subtotal',NumberType::class,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control subtotalinput solonumeros','readonly'=>true),
                'scale' => 5,
                'required'      => false,
                ))
            ->add('productoXLocal',null,array(
                'class' => 'AppBundle:ProductoXLocal',
                'label'         => false,
                'attr'=> array('class' => 'form-control '),
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('pxl');
                    //$qb->setFirstResult(1);
                    $qb->setMaxResults(1);


                    return $qb;
                }
                ))               
            // ->add('productoXLocal',null,array(
            //     'label'         => false,
            //     'attr'=> array('class' => 'form-control d-none'),
            //     'required'      => false,
            //     ))
            // ->add('cantidadEntregada',null,array(
            //     'label'         => false,
            //     'attr'=> array('class' => 'form-control','readonly'=>true),
            //     'required'      => false,
            //     ))
            ->add('compra',EntityType::class,array(
                'class' => 'AppBundle:Compra',
                'label'         => false,
                'attr'=> array('class' => 'form-control d-none'),
                'required'      => true,
                // 'query_builder' => function(EntityRepository $er) use ($options)
                // {
                //     $qb = $er->createQueryBuilder('p');
                //     $qb->setMaxResults(1);

                //     return $qb;
                // }                     
                ))
                           
            ;

    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\DetalleCompra',
            'by_reference' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_detallecompra';
    }


}
