<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Alerta controller.
 *
 * @Route("alerta")
 */
class AlertaControllerController extends Controller
{
    /**
     * @Route("/transferencia", name="alerta_transferencia")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function transferenciaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $dql = "SELECT txp FROM AppBundle:TransferenciaXProducto txp ";
        $dql .= " JOIN txp.transferencia t";
        $dql .= " JOIN txp.productoXLocal pxl";
        $dql .= " JOIN pxl.local l";
        $dql .= " JOIN pxl.producto p";
        $dql .= " WHERE  p.estado = 1  AND t.local_inicio IS NOT NULL AND t.local_fin IS NOT NULL";
        $dql .= " AND l.empresa =:empresa  ";

        // if($empresa != ''){
        //     $dql .= " AND e.id =:empresa  ";
        // }

        $dql .= " ORDER BY t.fecha DESC ";

        $query = $em->createQuery($dql);

        $query->setParameter('empresa',$empresa);

        // if($empresa != ''){
        //     $query->setParameter('empresa',$empresa);         
        // }
 
        $transferenciasXProducto =  $query->getResult();    
        
        return $this->render('alerta/transferencia.html.twig', array(
            'transferenciasXProducto' => $transferenciasXProducto,
            'titulo' => 'Alerta transferencia tienda',
        ));

    }	


    /**
     * @Route("/modificacionnombreproducto", name="alerta_modificacionnombreproducto")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function modificacionNombreProductoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $dql = "SELECT lm FROM AppBundle:LogModificacion lm ";
        $dql .= " JOIN lm.productoXLocal pxl";
        $dql .= " JOIN pxl.local l";
        $dql .= " JOIN lm.modificacionTipo mt";
        $dql .= " JOIN pxl.producto p";
        $dql .= " WHERE  p.estado = 1  AND mt.nombre = 'nombre' ";

        if($empresa != ''){
            $dql .= " AND l.empresa =:empresa  ";
        }

        $dql .= " ORDER BY lm.fecha DESC ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
        $logs =  $query->getResult();    
        
        return $this->render('alerta/modificacionnombreproducto.html.twig', array(
            'logs' => $logs,
            'titulo' => 'Alerta modificación nombre de Productos',
        ));

    }	

    /**
     * @Route("/modificacionprecioproducto", name="alerta_modificacionprecioproducto")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function modificacionPrecioProductoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $dql = "SELECT lm FROM AppBundle:LogModificacion lm ";
        $dql .= " JOIN lm.productoXLocal pxl";
        $dql .= " JOIN pxl.local l";
        $dql .= " JOIN lm.modificacionTipo mt";
        $dql .= " JOIN pxl.producto p";
        $dql .= " WHERE  p.estado = 1  AND mt.nombre = 'precio' ";

        if($empresa != ''){
            $dql .= " AND l.empresa =:empresa  ";
        }

        $dql .= " ORDER BY lm.fecha DESC ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
        $logs =  $query->getResult();    
        
        return $this->render('alerta/modificacionprecioproducto.html.twig', array(
            'logs' => $logs,
            'titulo' => 'Alerta modificación precio productos ',
        ));

    }	


}
