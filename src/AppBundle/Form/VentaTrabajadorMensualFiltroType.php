<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;


class VentaTrabajadorMensualFiltroType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fecha_inicio',null,array(
                'attr'=> array('class' => 'form-control datepicker fecha'),
                'label'         => 'Fecha inicio',
                'required'      => false,
            ))         
            ->add('fecha_fin',null,array(
                'label'         => 'Fecha fin',
                'attr'=> array('class' => 'form-control datepicker','style'=>''),
                'required'      => false,
            ))
            ->add('caja',EntityType::class,array(
                'class' => 'AppBundle:Caja',
                'attr'=> array('class' => 'form-control'),
                'label'=> 'Caja',
                'required'      => false,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('c');
                    $qb->leftJoin('c.local','l');
                    $qb->leftJoin('l.empresa','e');                    
                    $qb->where('e.id = '.$options['empresa']);
                    $qb->andWhere('c.estado = 1');
                    $qb->orderBy('c.nombre');
                    return $qb;
                }                    
            ))                             
            ->add('local',EntityType::class,array(
                'class' => 'AppBundle:EmpresaLocal',
                'attr'=> array('class' => 'form-control'),
                'label'=> 'Local',
                'required'      => false,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('l');
                    $qb->leftJoin('l.empresa','e');                    
                    $qb->where('e.id = '.$options['empresa']);
                    $qb->andWhere('l.estado = 1');
                    $qb->orderBy('l.nombre');
                    return $qb;
                }                    
            ))
            ->add('producto',EntityType::class,array(
                'class' => 'AppBundle:Producto',
                'attr'=> array('class' => 'form-control'),
                'label'=> 'Producto',
                'required'      => false,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('p');
                    $qb->leftJoin('p.empresa','e');                    
                    $qb->where('e.id = '.$options['empresa']);
                    $qb->andWhere('p.estado = 1');
                    $qb->orderBy('p.nombre');
                    return $qb;
                }                    
            ))
            ->add('forma_pago',EntityType::class,array(
                'class' => 'AppBundle:FormaPago',
                'attr'=> array('class' => 'form-control'),
                'label'=> 'Forma de pago',
                'required'      => false,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('f');
                    $qb->where('f.estado = 1');
                    $qb->orderBy('f.nombre');
                    return $qb;
                }                    
            ))
            // ->add('productoXLocal',EntityType::class,array(
            //     'class' => 'AppBundle:ProductoXLocal',
            //     'attr'=> array('class' => 'form-control'),
            //     'label'=> 'Producto',
            //     'required'      => false,
            //     'query_builder' => function(EntityRepository $er) use ($options)
            //     {
            //         $qb = $er->createQueryBuilder('pxl');
            //         $qb->leftJoin('pxl.local','l');
            //         $qb->leftJoin('pxl.producto','p');
                    
            //         if($options['local'] != ''){
            //             $qb->where('l.id = '.$options['local']);
            //         }

            //         $qb->andWhere('p.estado = 1 ');
            //         $qb->andWhere('pxl.estado = 1 ');                  
  
            //         $qb->orderBy('p.nombre');
            //         return $qb;
            //     }                    
            // ))   
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'empresa' => '',
            'local'   => '',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_ventatrabajadormensual_filtro';
    }


}
