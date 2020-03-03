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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class CompraFormaPagoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('moneda',EntityType::class,array(
                'class' => 'AppBundle:Moneda',
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => 'Moneda',
                'required'      => false,
                ))
            ->add('formaPago',EntityType::class,array(
                'class' => 'AppBundle:FormaPago',
                'attr'=> array('class' => 'form-control','required'=>'required'),
                'label'         => 'Forma de pago',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('e');
                    //$qb->where('e.estado = 1');
                    $qb->where('e.id IN (1,2,4)');

                    return $qb;
                }                     
                ))
            ->add('numeroDias',IntegerType::class,array(
                'attr'=> array('class' => 'form-control solonumeros'),
                'label'   => 'Días de crédito',
                'required'      => false,
                ))
            // ->add('montoACuenta',NumberType::class,array(
            //     'attr'=> array('class' => 'form-control solonumeros','placeholder'=>'Monto a cta.','readonly'=>'readonly'),
            //     'label'         => false,
            //     'required'      => false,
            //     ))
            ->add('condicion',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('Pagado' => 'pagado','Pendiente' => 'pendiente'),                
                'label'         => 'Condición del pago',
                'required'      => true,
                ))               
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CompraFormaPago'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compraformapago';
    }


}
