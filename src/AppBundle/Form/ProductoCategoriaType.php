<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductoCategoriaType extends AbstractType
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
            ->add('nombre',null,array(
                'label'         => 'Nombre',
                'attr'=> array('class' => 'form-control'),
                'required'      => true,
                ))
            ->add('descripcion',TextareaType::class,array(
                'label'         => 'Descripción',
                'attr'=> array('class' => 'form-control'),
                'required'      => false,
                ))
            ->add('imagen',FileType::class,array(
                'label'         => 'Imagen (Tamaño recomendable 100x100)',
                'attr'          => array('class' => 'form-control','accept'=>'image/*'),
                'required'      => false,
                )) 
            ->add('estado',HiddenType::class,array(
                'data'  => true,
                ))   
            ->add('empresa',EntityType::class,array(
                'class'         => 'AppBundle:Empresa',
                'attr'          => array('class' => 'form-control d-none'),
                'label'         => false,
                'required'      => false,
                'data'          => $this->em->getRepository('AppBundle:Empresa')->find($this->session->get('empresa'))  
                ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ProductoCategoria',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_productocategoria';
    }


}
