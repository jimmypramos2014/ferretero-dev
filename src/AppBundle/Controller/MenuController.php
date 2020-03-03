<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Menu controller.
 *
 * @Route("menu")
 */
class MenuController extends Controller
{

    /**
     * @Route("/", name="menu_index")
     * @Security("has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        if($empresa == 29)
        {
        	return $this->render('menu/index29.html.twig');
        }
        elseif($empresa == 27)
        {
            return $this->render('menu/index27.html.twig');
        }         
        else
        {
        	return $this->render('menu/index.html.twig');
        }   	

        

    }	
}