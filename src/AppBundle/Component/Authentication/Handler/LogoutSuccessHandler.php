<?php
namespace AppBundle\Component\Authentication\Handler;
 
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
 
class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    
    protected $router;
    protected $session;
    protected $security;
    
    public function __construct(RouterInterface $router,Security $security,SessionInterface $session)
    {
        $this->router = $router;
        $this->session = $session;
        $this->security = $security;
    }
    
    public function onLogoutSuccess(Request $request)
    {
        $this->session->getFlashBag()->add('danger', 'mensaje hola mundo');

        $response = new RedirectResponse($this->router->generate('fos_user_security_logout'));
        
        return $response;
    }
    
}