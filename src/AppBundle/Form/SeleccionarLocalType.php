<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SeleccionarLocalType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('local', EntityType::class,array(
                'class'     => 'AppBundle:EmpresaLocal',
                'attr'      => array('class' => 'form-control '),
                'label'     => 'Local',
                'placeholder'     => 'Seleccionar local',
                'required'  => true,
                //'data'      => $options['local'],
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('el');
                    $qb->leftJoin('el.empresa','e');
                    $qb->where('el.estado = 1');

                    if($options['empresa'] != ''){
                        $qb->andWhere('e.id ='.$options['empresa']);
                    }
                    
                    return $qb;
                }                   
                ))
            ->add('caja',EntityType::class,array(
                'class' => 'AppBundle:Caja',
                'attr'=> array('class' => 'form-control ','style'=>'pointer-events:none'),
                'label'         => 'Caja',
                'placeholder'   => 'Seleccionar caja',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('c');
                    $qb->leftJoin('c.local','l');
                    $qb->leftJoin('l.empresa','e');
                    $qb->where('c.estado = 1');

                    if($options['empresa'] != ''){
                        $qb->andWhere('e.id ='.$options['empresa']);
                    }
                    
                    return $qb;
                }                  
                ))               
            // ->add('caja', EntityType::class,array(
            //     'class'     => 'AppBundle:Caja',
            //     'attr'      => array('class' => 'form-control '),
            //     'label'     => 'Caja',
            //     'placeholder'     => 'Seleccionar caja',
            //     'required'  => true,
            //     'query_builder' => function(EntityRepository $er) use ($options)
            //     {
            //         $qb = $er->createQueryBuilder('c');
            //         $qb->leftJoin('c.local','l');
            //         $qb->where('c.estado = 1');

            //         if($options['local'] != ''){
            //             $qb->andWhere('l.id ='.$options['local']);
            //         }
                    
            //         return $qb;
            //     }                   
            //     ))
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //'data_class' => 'AppBundle\Entity\Caja',
            'empresa' => '',
            'local' => ''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_seleccionar_local';
    }


}
