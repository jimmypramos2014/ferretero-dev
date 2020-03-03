<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;



class TransporteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nombre o razón social',
                'required'      => true,
                ))
            ->add('ruc',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nro RUC',
                'required'      => true,
                ))
            ->add('estado',HiddenType::class,array(
                'data'      => true,
                ))
            ->add('empresa',EntityType::class,array(
                'attr'          => array('class' => 'd-none'),
                'class'         => 'AppBundle:Empresa',
                'label'         => false,
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                    {
                        $qb = $er->createQueryBuilder('e');
                        $qb->where('e.id = '.$options['empresa']);
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
            'data_class' => 'AppBundle\Entity\Transporte',
            'empresa' => ''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_transporte';
    }


}
