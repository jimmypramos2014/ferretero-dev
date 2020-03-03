<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ClientePvType extends AbstractType
{

    protected $session;
    protected $em;

    public function __construct(SessionInterface $session,EntityManagerInterface $em)
    {
        $this->session  = $session;
        $this->em       = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('razonSocial',null,array(
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Nombre',
                'required'      => false,
                ))
            ->add('tipoDocumento',EntityType::class,array(
                'class' => 'AppBundle:TipoDocumento',
                'attr'=> array('class' => 'form-control'),
                'label'         => 'Tipo de documento',
                'required'      => false,
                ))                                          
            ->add('ruc',null,array(
                'attr'=> array('class' => 'form-control '),
                'label'         => 'Nro.Documento',
                'required'      => false,
                ))           
            ->add('direccion',null,array(
                'attr'=> array('class' => 'form-control '),
                'label'         => 'Dirección',
                'required'      => false,
                ))
            ->add('email',null,array(
                'attr'=> array('class' => 'form-control '),
                'label'         => 'Correo electrónico',
                'required'      => false,
                ))
            ->add('telefono',null,array(
                'attr'=> array('class' => 'form-control '),
                'label'         => 'Teléfono',
                'required'      => false,
                ))        
            ->add('tipo',ChoiceType::class,array(
                'attr'=> array('class' => 'form-control'),
                'choices'       => array('Mayorista' => 'mayorista','Minorista' => 'minorista'),                
                'label'         => 'Tipo',
                'data'          => 'minorista',
                'required'      => false,
                ))                                                                                   
            ;


    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //'data_class' => 'AppBundle\Entity\Cliente',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_cliente_pv';
    }


}
