<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class CajaCierreType extends AbstractType
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
            ->add('totalDejada',NumberType::class,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Total dejado en caja',
                'required'      => true,
                'data'          => '0'
                ))
            // ->add('totalDeposito',NumberType::class,array(
            //     'attr'=> array('class' => 'form-control'),
            //     'label'         => 'Total por depósito',
            //     'required'      => true,
            //     'data'          => '0'
            //     ))
            // ->add('totalTransferencia',NumberType::class,array(
            //     'attr'=> array('class' => 'form-control'),
            //     'label'         => 'Total por transferencia',
            //     'required'      => true,
            //     'data'          => '0'
            //     ))
            // ->add('totalCheque',NumberType::class,array(
            //     'attr'=> array('class' => 'form-control'),
            //     'label'         => 'Total en cheque',
            //     'required'      => true,
            //     'data'          => '0'
            //     ))
            // ->add('totalEfectivo',NumberType::class,array(
            //     'attr'=> array('class' => 'form-control'),
            //     'label'         => 'Total en efectivo',
            //     'required'      => true,
            //     'data'          => '0'
            //     ))
            ->add('totalRecaudado',NumberType::class,array(
                'attr'=> array('class' => 'form-control','readonly'=>'readonly'),
                'label'         => 'Efectivo en caja',
                'required'      => true,
                'data'          => $options['total_recaudado']
                ))
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))
            ->add('cajaApertura',EntityType::class,array(
                'class' => 'AppBundle:CajaApertura',
                'attr'=> array('class' => 'form-control d-none'),
                'label'         => false,
                'required'      => true,
                'data'  => $this->em->getRepository('AppBundle:CajaApertura')->find($options['caja_apertura_id'])               
                ))
            ->add('usuario',EntityType::class,array(
                'class' => 'AppBundle:Usuario',
                'attr'=> array('class' => 'form-control d-none'),
                'label'         => false,
                'required'      => true,
                'data'  => $this->em->getRepository('AppBundle:Usuario')->find($this->session->get('usuario'))               
                ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CajaCierre',
            'caja_apertura_id' => '',
            'total_recaudado'  => 0 
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_cajacierre';
    }


}
