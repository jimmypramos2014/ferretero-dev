<?php

namespace AppBundle\EventListener;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use AppBundle\Entity\Editor;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Listener responsible for adding the default user role at registration
 */
class RegistrationListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(UrlGeneratorInterface $router) {

        $this->router = $router;
    }
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        );
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        /** @var $user \FOS\UserBundle\Model\UserInterface **/
        $user = $event->getForm()->getData();

        $rolesArr = array('ROLE_ADMIN');

        // if($user->getTipoSuscripcion() == 'basico'){
        //     $rolesArr = array('ROLE_EDITOR');
        // }
        
        // if($user->getTipoSuscripcion() == 'premium'){
        //     $rolesArr = array('ROLE_PREMIUM');
        // }   

        //$user->setEnabled(false);
        $user->setRoles($rolesArr);

        //$this->crearEditorYAsignarMercados($user);

        $url = $this->router->generate('fos_user_security_login');

        $event->setResponse(new RedirectResponse($url));

    }

    // private function crearEditorYAsignarMercados($user)
    // {
    //     $em  = $GLOBALS['kernel']->getContainer()->get('doctrine')->getManager();

    //     $dql = "SELECT e FROM AppBundle:Editor e";
    //     $dql .= " JOIN e.usuario u";
    //     $dql .= " WHERE u.id =:usuario ";

    //     $resp = $em->createQuery($dql);
    //     $resp->setParameter('usuario',$user->getId());

    //     $editors = $resp->getResult($user->getId());

    //     //ladybug_dump_die($user->getNombres());        

    //     if(count($editors) == 0){

    //         $usuario = $em->getRepository('AppBundle:Usuario')->find(1);

    //         $editorObj = new Editor();
    //         $editorObj->setUsuario($user);
    //         //$editorObj->setUsuarioCreacion($user);
    //         $editorObj->setEstado(true);
    //         $editorObj->setCondicion(true);
    //         $em->persist($editorObj);

    //         $mercados = $em->getRepository('AppBundle:MercadoLocal')->findAll();
    //         foreach($mercados as $mercado){

    //             $mercado->addUsuario($editorObj);
    //             $em->persist($mercado);
                
    //         }


    //     }else{

    //         foreach($editors as $editor){
    //             $editorObj = $editor;
    //         }

    //         return true;
    //     }

    // } 

   
}