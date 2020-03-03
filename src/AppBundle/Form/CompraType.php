<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class CompraType extends AbstractType
{

    protected $em;
    protected $security;
    protected $usuario;

    public function __construct(EntityManagerInterface $em,Security $security)
    {
        $this->em       = $em;
        $this->security = $security;
        $this->usuario  = $this->security->getUser();        
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('empleado',EntityType::class,array(
                'class' => 'AppBundle:Empleado',
                'attr'=> array('class' => 'form-control d-none'),
                'label'         => false,
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('e');
                    $qb->leftJoin('e.usuario','u');
                    $qb->where('e.estado = 1');

                    $qb->andWhere('u.id ='.$this->usuario->getId());

                    return $qb;
                }                 
                ))       
            ->add('detalleCompra', CollectionType::class, array(
                'entry_type' => DetalleCompraType::class,
                'entry_options' => array('label' => false),
                'required'=>false,
                'allow_add' => true,
                //'by_reference' => true,
            ))
            ->add('compraFormaPago', CollectionType::class, array(
                'entry_type' => CompraFormaPagoType::class,
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
            'data_class' => 'AppBundle\Entity\Compra'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compra';
    }


}
