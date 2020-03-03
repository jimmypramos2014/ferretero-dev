<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Doctrine\ORM\EntityRepository;


class DetalleProformaType extends AbstractType
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
            ->add('subtotal',NumberType::class,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control subtotalinput solonumeros','readonly'=>true),
                'required'      => false,
                ))
            ->add('precio',NumberType::class,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control precioinput solonumeros'),
                'required'      => false,
                ))
            // ->add('productoXLocal',null,array(
            //     'label'         => false,
            //     'attr'=> array('class' => 'form-control d-none'),
            //     'required'      => false,
            //     ))
            ->add('cantidadEntregada',null,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control','readonly'=>true),
                'required'      => false,
                ))
            ->add('proforma',EntityType::class,array(
                'class' => 'AppBundle:Proforma',
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
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\DetalleProforma',
            'by_reference' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_detalleproforma';
    }


}
