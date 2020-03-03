<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class VentaFormaPagoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('condicion')
            //->add('moneda')
            //->add('venta')
            ->add('formaPago',EntityType::class,array(
                'class' => 'AppBundle:FormaPago',
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => false,
                'required'      => false,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('e');
                    $qb->where('e.estado = 1');

                    return $qb;
                }                   
                ))
            ->add('numeroDias',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('0' => '0','5' => '5','10' => '10','15' => '15','20' => '20','30' => '30','45' => '45','60' => '60'),
                'label'         => false,
                'placeholder'   => 'Días de crédito',
                'data'          => '0',
                'required'      => false,
                ))
            ->add('montoACuenta',NumberType::class,array(
                'attr'=> array('class' => 'form-control solonumeros','placeholder'=>'Monto a cta.','readonly'=>'readonly'),
                'label'         => false,
                'required'      => false,
                ))
            ->add('moneda',EntityType::class,array(
                'class' => 'AppBundle:Moneda',
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => false,
                'required'      => false,

                ))                   
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\VentaFormaPago'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_ventaformapago';
    }


}
