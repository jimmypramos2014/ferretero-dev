<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProductoUnidadType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codigo',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Código',
                'required'      => false,
                ))
            ->add('abreviatura',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Abreviatura',
                'required'      => true,
                ))
            ->add('nombre',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nombre',
                'required'      => true,
                ))
            ->add('descripcion',TextareaType::class,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Descripción',
                'required'      => false,
                ))
            ->add('tipo',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('Normal' => 'normal','Referencial' => 'referencial'),
                'label'         => 'Tipo',
                'required'      => true,
                ))
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))  
            ->add('ratio',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Ratio',
                'required'      => true,
                ))
            ->add('categoria',EntityType::class,array(
                'attr'=> array('class' => 'form-control'),
                'class' => 'AppBundle:ProductoUnidadCategoria',
                'label'         => 'Categoría',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('c');
                    $qb->join('c.empresa','e');

                    if($options['empresa'] != ''){
                        $qb->where('e.id ='.$options['empresa']->getId());
                    }                    

                    return $qb;
                }                    
                ))
            ->add('empresa',null,array(
                'attr'=> array('class' => 'd-none'),
                'label'=> false,
                'data'  => $options['empresa'],
                ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ProductoUnidad',
            'empresa'=>'',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_productounidad';
    }


}
