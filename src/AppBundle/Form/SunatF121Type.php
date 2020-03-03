<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;


class SunatF121Type extends AbstractType
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
            ->add('local', EntityType::class,array(
                'class'     => 'AppBundle:EmpresaLocal',
                'attr'      => array('class' => 'form-control '),
                'label'     => 'Local',
                'required'  => true,
                'data'      => $this->em->getRepository('AppBundle:EmpresaLocal')->find($options['local']),
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
            ->add('producto',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control select2'),
                'label'         => 'Producto',
                'placeholder'   => 'Seleccione un producto',
                'required'      => false,
                'mapped'        => false,
                ))            
            ->add('ano',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('2018' => '2018','2019' => '2019','2020' => '2020','2021' => '2021','2022' => '2022','2023' => '2023'),
                'label'         => 'Año',
                'data'          => date('Y'),
                'required'      => true,
                ))
            ->add('mes',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('Enero' => '1','Febrero' => '2','Marzo' => '3','Abril' => '4','Mayo' => '5','Junio' => '6','Julio' => '7','Agosto' => '8','Setiembre' => '9','Octubre' => '10','Noviembre' => '11','Diciembre' => '12'),
                'label'         => 'Mes',
                'data'          => date('m'),
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
            'local' =>'',
            'empresa' => ''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_sunat_f121';
    }


}
