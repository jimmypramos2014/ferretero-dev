<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityManagerInterface;

class TipoCambioType extends AbstractType
{

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em       = $em;
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fecha',null,array(
                'attr'=> array('class' => 'form-control tipocambio '),
                'label'         => 'Fecha',
                'format' => $options['date_format'],
                'html5' => false,
                'widget' => 'single_text'
                ))  
            ->add('compra',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Compra',
                'required'      => true,
                ))
            ->add('venta',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Venta',
                'required'      => true,
                ))            
            ->add('empresa',EntityType::class,array(
                'class' => 'AppBundle:Empresa',
                'attr'=> array('class' => 'd-none'),
                'label'=> false,
                'data' => $this->em->getRepository('AppBundle:Empresa')->find($options['empresa'])
                ))
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\TipoCambio',
            'empresa'=>'',
            'date_format' => 'dd/MM/yyyy',            
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_tipocambio';
    }


}
