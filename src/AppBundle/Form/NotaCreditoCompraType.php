<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class NotaCreditoCompraType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fecha',DateType::class,array(
                'attr'=> array('class' => 'form-control setcurrentdate ','required'=>'required'),
                'label'         => 'Fecha',
                'format' => $options['date_format'],
                'html5' => false,
                'widget' => 'single_text',
                'required'  => true,
                ))
            ->add('numero',null,array(
                'label'         => 'Número',
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'required'      => true,
                ))
            ->add('valor',null,array(
                'label'         => 'Valor',
                'attr'=> array('class' => 'form-control solonumeros','required'=>'required'),
                'required'      => true,
                ))
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))
            ->add('local',null,array(
                'class' => 'AppBundle:EmpresaLocal',
                'attr'=> array('class' => 'form-control ','required'=>'required'),
                'label'         => 'Local',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('l');
                    $qb->where('l.id ='.$options['local']);

                    return $qb;
                }                  
            ))
            ->add('facturaCompra',null,array(
                'class' => 'AppBundle:FacturaCompra',
                'label' => 'Factura(s) relacionada(s)',
                'attr'  => array('class' => 'form-control select2-multiple','multiple'=>'multiple'),
                'required'      => false,
                'mapped' => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('f');
                    $qb->where('f.estado = 1');
                    $qb->andWhere('f.local = '.$options['local']);
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
            'data_class' => 'AppBundle\Entity\NotaCreditoCompra',
            'date_format' => 'dd/MM/yyyy',
            'local' => ''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_notacreditocompra';
    }


}
