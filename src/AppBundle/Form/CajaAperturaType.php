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

class CajaAperturaType extends AbstractType
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
            //->add('fecha')
            ->add('montoApertura',NumberType::class,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Monto de apertura',
                'required'      => true,
                'data'          => '0'
                ))
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))
            ->add('caja',EntityType::class,array(
                'class' => 'AppBundle:Caja',
                'attr'=> array('class' => 'form-control','style'=>'pointer-events: none;'),
                'label'         => 'Caja',
                'required'      => true,
                'data'  => $this->em->getRepository('AppBundle:Caja')->find($options['caja'])               
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
            'data_class' => 'AppBundle\Entity\CajaApertura',
            'caja' => ''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_cajaapertura';
    }


}
