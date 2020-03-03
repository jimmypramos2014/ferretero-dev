<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CajaType extends AbstractType
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
            ->add('nombre',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nombre',
                'required'      => true,
                ))
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))
            ->add('local',EntityType::class,array(
                'attr'=> array('class' => 'form-control'),
                'class' => 'AppBundle:EmpresaLocal',
                'label'         => 'Local',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('l');
                    $qb->leftJoin('l.empresa','e');

                    if($options['empresa'] != ''){
                        $qb->where('e.id ='.$options['empresa']);
                    }                    

                    return $qb;
                }                     
                ))
            ->add('condicion',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control ','style'=>'pointer-events:none;'),
                'choices' => array('Cerrado'=>'cerrado','Abierto'=>'abierto'),
                'label'         => 'Condición',
                'required'      => true,
                ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Caja',
            'empresa' => ''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_caja';
    }


}
