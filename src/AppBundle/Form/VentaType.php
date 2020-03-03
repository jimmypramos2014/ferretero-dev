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

class VentaType extends AbstractType
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
            ->add('condicion',CheckboxType::class,array(
                'data'  => false,
                'label'  => 'Pendiente de entrega',
                'label_attr'    => array('class' => 'mr-2'),
                'required'      => false,
                ))
            ->add('total')
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
            ->add('detalleVenta', CollectionType::class, array(
                'entry_type' => DetalleVentaType::class,
                'entry_options' => array('label' => false),
                'required'=>false,
                'allow_add' => true,
                //'by_reference' => true,
            ))
            ->add('ventaFormaPago', CollectionType::class, array(
                'entry_type' => VentaFormaPagoType::class,
                'entry_options' => array('label' => false),
                'required'=>false,
                'allow_add' => true,
                //'by_reference' => true,
            ))
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))                                
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Venta'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_venta';
    }


}
