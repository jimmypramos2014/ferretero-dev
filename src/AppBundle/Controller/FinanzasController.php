<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Finanzas controller.
 *
 * @Route("finanzas")
 */
class FinanzasController extends Controller
{

    /**
     * Muestra grafico de ventas por trabajador diaria
     *
     * @Route("/ventatrabajadordiaria", name="finanzas_ventatrabajadordiaria")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function ventaTrabajadorDiariaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $ventas_diaria_x_trabajador  =  $this->container->get('AppBundle\Util\Util')->obtenerVentaDiariaXTrabajador($empresa);
        $data = array();
        $i = 0;
        foreach($ventas_diaria_x_trabajador as $venta_diaria_x_trabajador){
        	$empleado = $em->getRepository('AppBundle:Empleado')->find((int)$venta_diaria_x_trabajador['id']); 
        	$data[$i]['name'] = $empleado->getNombres().' '.$empleado->getApellidoPaterno().' '.$empleado->getApellidoMaterno();
            $data[$i]['data'] = (float)$venta_diaria_x_trabajador['subtotal'];
        	$data[$i]['y'] = (float)$venta_diaria_x_trabajador['subtotal'];
        	//$data[$i]['drilldown'] = $empleado->getNombres().' '.$empleado->getApellidoPaterno().' '.$empleado->getApellidoMaterno();
        	$i++;
        }


        return $this->render('finanzas/ventatrabajadordiaria.html.twig', array(
            'titulo'   => 'Venta por trabajador diaria',
            'data'     => json_encode($data) 
        ));
    }

    /**
     * Muestra grafico de ventas por trabajador mensual
     *
     * @Route("/ventatrabajadormensual", name="finanzas_ventatrabajadormensual")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function ventaTrabajadorMensualAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro 			= $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio = $filtro['fecha_inicio'];
        $fecha_fin 		= $filtro['fecha_fin'];

        $ventas_x_trabajador  =  $this->container->get('AppBundle\Util\Util')->obtenerVentaXTrabajador($empresa,$fecha_inicio,$fecha_fin);

        $data = array();
        $i = 0;
        foreach($ventas_x_trabajador as $venta_x_trabajador){

					$empleado = $em->getRepository('AppBundle:Empleado')->find((int)$venta_x_trabajador['id']); 
					$data[$i]['name'] = $empleado->getNombres().' '.$empleado->getApellidoPaterno().' '.$empleado->getApellidoMaterno();
					$data[$i]['y'] = (float)$venta_x_trabajador['subtotal'];
					$data[$i]['data'] = (float)$venta_x_trabajador['subtotal'];
					$i++;

        }


        return $this->render('finanzas/ventatrabajadormensual.html.twig', array(
            'titulo'   => 'Venta por trabajador mensual',
            'data'     => json_encode($data),
            'fecha_inicio' 		 => $fecha_inicio,
            'fecha_fin' 		 => $fecha_fin,
            'form' 		 => $form->createView(),
        ));
    }

    /**
     * Muestra grafico de ventas por local diaria
     *
     * @Route("/ventalocaldiaria", name="finanzas_ventalocaldiaria")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function ventaLocalDiariaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $ventas_diaria_x_local  =  $this->container->get('AppBundle\Util\Util')->obtenerVentaDiariaXLocal($empresa);
        $data = array();
        $i = 0;
        foreach($ventas_diaria_x_local as $venta_diaria_x_local){
        	$localObj = $em->getRepository('AppBundle:EmpresaLocal')->find((int)$venta_diaria_x_local['id']); 
        	$data[$i]['name'] = $localObj->getNombre();
        	$data[$i]['y'] = (float)$venta_diaria_x_local['subtotal'];
        	$data[$i]['data'] = (float)$venta_diaria_x_local['subtotal'];
        	$i++;
        }


        return $this->render('finanzas/ventalocaldiaria.html.twig', array(
            'titulo'   => 'Venta por local diaria',
            'data'     => json_encode($data) 
        ));
    }

    /**
     * Muestra grafico de ventas por local mensual
     *
     * @Route("/ventalocalmensual", name="finanzas_ventalocalmensual")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function ventaLocalMensualAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaLocalMensualFiltroType',null);

        $filtro 			= $request->request->get('appbundle_ventalocalmensual_filtro');
        $fecha_inicio = $filtro['fecha_inicio'];
        $fecha_fin 		= $filtro['fecha_fin'];

        $ventas_x_local  =  $this->container->get('AppBundle\Util\Util')->obtenerVentaXLocal($empresa,$fecha_inicio,$fecha_fin);

        $data = array();
        $i = 0;
        foreach($ventas_x_local as $venta_x_local){

					$localObj = $em->getRepository('AppBundle:EmpresaLocal')->find((int)$venta_x_local['id']); 
					$data[$i]['name'] = $localObj->getNombre();
					$data[$i]['y'] = (float)$venta_x_local['subtotal'];
					$data[$i]['data'] = (float)$venta_x_local['subtotal'];
					$i++;

        }


        return $this->render('finanzas/ventalocalmensual.html.twig', array(
            'titulo'   => 'Venta por local mensual',
            'data'     => json_encode($data),
            'fecha_inicio' 		 => $fecha_inicio,
            'fecha_fin' 		 => $fecha_fin,
            'form' 		 => $form->createView(),
        ));
    }


}
