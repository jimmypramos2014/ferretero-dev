<?php
namespace AppBundle\Component\Authentication\Handler;
 
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;



class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    
    protected $router;
    protected $session;
    protected $security;
    protected $em;
    
    public function __construct(RouterInterface $router,SessionInterface $session,Security $security,EntityManagerInterface $em)
    {
        $this->router   = $router;
        $this->session  = $session;
        $this->security = $security;
        $this->em       = $em;
    }


    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {

        $usuario = $this->security->getUser();



        if (in_array("ROLE_VENDEDOR", $usuario->getRoles())) {

            $empleado   = $this->em->getRepository('AppBundle:Empleado')->findOneBy(array('estado'=>true,'usuario'=>$usuario));

            // $empresaObj   = $this->em->getRepository('AppBundle:Empresa')->find($empleado->getLocal()->getEmpresa()->getId());

            // if(!$empresaObj->getEstadoPago())
            // {
            //     $this->session->getFlashBag()->add('danger', $empresaObj->getMensajePago());

            //     $response = new RedirectResponse($this->router->generate('fos_user_security_logout')); 
            //     return $response;
            // }

            $this->session->set('empleado',$empleado->getId());
            $this->session->set('rol',$empleado->getPuesto()->getNombre());
            $this->session->set('local',$empleado->getLocal()->getId());
            $this->session->set('empresa',$empleado->getLocal()->getEmpresa()->getId());
            $this->session->set('usuario',$usuario->getId());
            $this->session->set('departamento',$empleado->getLocal()->getEmpresa()->getDistrito()->getProvincia()->getDepartamento()->getId());

            $response = new RedirectResponse($this->router->generate('seleccionar_local')); 
            return $response;

        }

        if (in_array("ROLE_ALMACENERO", $usuario->getRoles())) {

            $empleado   = $this->em->getRepository('AppBundle:Empleado')->findOneBy(array('estado'=>true,'usuario'=>$usuario));

            // $empresaObj   = $this->em->getRepository('AppBundle:Empresa')->find($empleado->getLocal()->getEmpresa()->getId());

            // if(!$empresaObj->getEstadoPago())
            // {
            //     $this->session->getFlashBag()->add('danger', $empresaObj->getMensajePago());

            //     $response = new RedirectResponse($this->router->generate('fos_user_security_logout')); 
            //     return $response;
            // }

            $this->session->set('empleado',$empleado->getId());
            $this->session->set('rol',$empleado->getPuesto()->getNombre());
            $this->session->set('local',$empleado->getLocal()->getId());
            $this->session->set('empresa',$empleado->getLocal()->getEmpresa()->getId());
            $this->session->set('usuario',$usuario->getId());
            $this->session->set('departamento',$empleado->getLocal()->getEmpresa()->getDistrito()->getProvincia()->getDepartamento()->getId());


            $response = new RedirectResponse($this->router->generate('seleccionar_local')); 
            return $response;

        }


        if (in_array("ROLE_ADMIN", $usuario->getRoles())  && !in_array("ROLE_SUPER_ADMIN", $usuario->getRoles())) {

            $administrador   = $this->em->getRepository('AppBundle:Empleado')->findOneBy(array('estado'=>true,'usuario'=>$usuario));

            // $empresaObj   = $this->em->getRepository('AppBundle:Empresa')->find($administrador->getLocal()->getEmpresa()->getId());

            // if(!$empresaObj->getEstadoPago())
            // {

            //     $this->session->getFlashBag()->add('danger', $empresaObj->getMensajePago());

            //     $response = new RedirectResponse($this->router->generate('fos_user_security_logout')); 
            //     return $response;
            // }


            $this->session->set('empleado',$administrador->getId());
            $this->session->set('rol','Administrador');
            $this->session->set('local',$administrador->getLocal()->getId());
            $this->session->set('usuario',$usuario->getId());
            $this->session->set('empresa',$administrador->getLocal()->getEmpresa()->getId());
            $this->session->set('departamento',$administrador->getLocal()->getEmpresa()->getDistrito()->getProvincia()->getDepartamento()->getId());
            
            $response = new RedirectResponse($this->router->generate('dashboard')); 
            return $response;




        }
 
        if (in_array("ROLE_SUPER_ADMIN", $usuario->getRoles())) {

            $this->session->set('rol','Superadmin');
            $this->session->set('usuario',$usuario->getId());

            $response = new RedirectResponse($this->router->generate('empresa_index')); 
            return $response;

        }

        $response = new RedirectResponse($this->router->generate('dashboard')); 
        return $response;  

    }
 


}