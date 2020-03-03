<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ProformaType extends AbstractType
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
            ->add('cliente_select',EntityType::class,array(
                'class' => 'AppBundle:Cliente',
                'attr'=> array('class' => 'form-control chosen-select','data-placeholder'=>'Seleccione un cliente'),
                'label'         => 'Cliente',
                'required'      => false,
                'mapped'        => false,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('c');
                    $qb->leftJoin('c.local','l');
                    $qb->leftJoin('l.empresa','e');
                    $qb->where('c.estado = 1');
                    $qb->andWhere('e.id ='.$options['empresa']);

                    return $qb;
                }                   
                ))
            ->add('cliente',null,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control  mt-4','placeholder'=>'Cliente','required'=>'required'),
                'required'      => false,
                //'mapped'        => false,
                ))
            ->add('fecha',DateType::class,array(
                'attr'=> array('class' => 'form-control setcurrentdate ','required'=>'required','placeholder'=>'Fecha'),
                'label'         => 'Fecha',
                'format' => $options['date_format'],
                'html5' => false,
                'widget' => 'single_text',
                'required'  => false,
                ))
            ->add('condicion',HiddenType::class,array(
                'label'         => false,
                ))
            ->add('numero',null,array(
                'label'         => 'Número',
                'attr'=> array('class' => 'form-control','readonly'=>'readonly','required'=>'required'),
                'required'      => false,
                ))
            ->add('empresa',null,array(
                //'class'         => 'AppBundle:Empresa',
                'label' => false,
                'attr'=> array('class' => 'form-control d-none'),
                'required'      => false,
                'data'          => $this->em->getRepository('AppBundle:Empresa')->find($options['empresa'])  
                ))
            ->add('detalleProforma', CollectionType::class, array(
                'entry_type' => DetalleProformaType::class,
                'entry_options' => array('label' => false),
                'required'=>false,
                'allow_add' => true,
                //'by_reference' => true,
            ))                                                                                                                                                    
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Proforma',
            'empresa'=>'',
            'date_format' => 'dd/MM/yyyy',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_proforma';
    }


}
