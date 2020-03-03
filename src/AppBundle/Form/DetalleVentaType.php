<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;

class DetalleVentaType extends AbstractType
{

    protected $security;
    protected $usuario;
    protected $session;
    protected $em;

    public function __construct(Security $security,SessionInterface $session,EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->usuario  = $this->security->getUser();
        $this->session  = $session;
        $this->em       = $em;        
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cantidad',NumberType::class,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control cantidadinput solonumeros'),
                'required'      => false,
                ))
            ->add('precio',NumberType::class,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control precioinput solonumeros','lang'=>'en-150'),
                'required'      => false,
                ))            
            ->add('subtotal',NumberType::class,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control subtotalinput solonumeros','readonly'=>true),
                'required'      => false,
                ))
            ->add('productoXLocal',null,array(
                'class' => 'AppBundle:ProductoXLocal',
                'label'         => false,
                'attr'=> array('class' => 'form-control '),
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('pxl');
                    //$qb->setFirstResult(1);
                    $qb->setMaxResults(1);


                    return $qb;
                }                   
                // 'query_builder' => function(EntityRepository $er) use ($options)
                // {
                //     $qb = $er->createQueryBuilder('pxl');
                //     $qb->leftJoin('pxl.detalleVenta','dv');
                //     $qb->leftJoin('pxl.local','l');
                //     $qb->where('pxl.estado = 1');                    
                //     $qb->andWhere('l.id = '.$this->session->get('local'));

                //     $qb->setMaxResults(1);

                //     return $qb;
                // }                 
                ))
            ->add('cantidadEntregada',null,array(
                'label'         => false,
                'attr'=> array('class' => 'form-control','readonly'=>true),
                'required'      => false,
                ))
            ->add('venta',EntityType::class,array(
                'class' => 'AppBundle:Venta',
                'label'         => false,
                'attr'=> array('class' => 'form-control d-none'),
                'required'      => true,
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('v');
                    $qb->setMaxResults(1);

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
            'data_class' => 'AppBundle\Entity\DetalleVenta',
            'by_reference' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_detalleventa';
    }


}
