<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends \FOS\UserBundle\Controller\SecurityController
{
    /**
     * We override loginAction to redirect the user depending on their role.
     * If they try to go to /login, they will be redirected accordingly based on their role
     *
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request)
    {
        $auth_checker = $this->get('security.authorization_checker');
        $router = $this->get('router');

        // 307: Internal Redirect
        if ($auth_checker->isGranted(['ROLE_SUPER_ADMIN'])) {
            // SUPER_ADMIN roles go to the `admin_home` route
            return new RedirectResponse($router->generate('empresa_index'), 307);
        }

        if ($auth_checker->isGranted('ROLE_ADMIN')) {
            // Everyone else goes to the `home` route
            return new RedirectResponse($router->generate('dashboard'), 307);

            //$session = new Session();
            //$session->start();
            //$session->getFlashBag()->add('mensajes', 'No existe un año activo para este usuario.');
            //return new RedirectResponse($router->generate('fos_user_security_logout'), 307);
        }

        if ($auth_checker->isGranted('ROLE_VENDEDOR')) {
            // Everyone else goes to the `home` route
            return new RedirectResponse($router->generate('almacen_productosxlocal'), 307);
        }

        if ($auth_checker->isGranted('ROLE_ALMACENERO')) {
            // Everyone else goes to the `home` route
            return new RedirectResponse($router->generate('almacen_productosxlocal'), 307);
        }

        // Always call the parent unless you provide the ENTIRE implementation
        return parent::loginAction($request);
    }



    // /**
    //  * @Route("/logout", name="app_logout")
    //  */
    // public function logoutAction()
    // {

    //     dump('holaaaaaaaaaaaa');
    //     die();        
    //     throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    // }

}