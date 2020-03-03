<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
//use \Date;

class EgresoType extends AbstractType
{


    protected $em;
    protected $activedate;

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
            ->add('monto',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Monto',
                'required'      => true,
                ))
            ->add('fecha',null,array(
                'attr'=> array('class' => 'form-control gasto '),
                'label'         => 'Fecha',
                'format' => $options['date_format'],
                'html5' => false,
                'widget' => 'single_text'
                ))                 
            ->add('empresa',null,array(
                'attr'=> array('class' => 'd-none'),
                'label'=> false,
                'data'  => $options['empresa'],
                ))
            ->add('concepto',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'=> 'Concepto',
                'required'  => true,
                ))            
            ->add('local',EntityType::class,array(
                'class' => 'AppBundle:EmpresaLocal',
                'attr'=> array('class' => 'form-control'),
                'label'=> 'Local',
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('p');
                    $qb->leftJoin('p.empresa','e');
                    $qb->where('e.id = '.$options['empresa']->getId());
                    $qb->orderBy('p.nombre');
                    return $qb;
                }                    
                ))            
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))
            ->add('beneficiario',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Beneficiario',
                'required'      => true,
                ));


    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Gasto',
            'empresa'=>'',
            'date_format' => 'dd/MM/yyyy',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_gasto';
    }


}
