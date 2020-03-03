<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\FacturaVenta;

/**
 * Proveedor controller.
 *
 * @Route("reporte")
 */
class ReporteController extends Controller
{

    /**
     * Muestra un reporte de los productos mas vendidos
     *
     * @Route("/productosmasvendidos", name="reporte_productosmasvendidos")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function productosMasVendidosAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro 			= $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio = $filtro['fecha_inicio'];
        $fecha_fin 		= $filtro['fecha_fin'];        

        $productosmasvendidos  =  $this->container->get('AppBundle\Util\Util')->obtenerProductosMasVendidos($empresa,$fecha_inicio,$fecha_fin);


        return $this->render('reporte/productosmasvendidos.html.twig', array(
            'titulo'   							=> 'Productos más vendidos',
            'productosmasvendidos'  => $productosmasvendidos,
            'fecha_inicio' 		 			=> $fecha_inicio,
            'fecha_fin' 		 				=> $fecha_fin,
            'form' 		 							=> $form->createView(),            
        ));
    }

    /**
     * Muestra un reporte de las ventas diarias
     *
     * @Route("/ventasdiarias", name="reporte_ventasdiarias")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')  ")
     */
    public function ventasDiariasAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro 			= $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio = $filtro['fecha_inicio'];
        $fecha_fin 		= $filtro['fecha_fin'];        

        $ventas  =  $this->container->get('AppBundle\Util\Util')->ventasDiarias($empresa,$fecha_inicio,$fecha_fin);


        return $this->render('reporte/ventasdiarias.html.twig', array(
            'titulo'   							=> 'Ventas diarias',
            'ventas'  							=> $ventas,
            'fecha_inicio' 		 			=> $fecha_inicio,
            'fecha_fin' 		 				=> $fecha_fin,
            'form' 		 							=> $form->createView(),            
        ));
    }

    /**
     * Muestra un reporte de los clientes
     *
     * @Route("/cliente", name="reporte_cliente")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function clienteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        //$clientes  = $em->getRepository('AppBundle:Cliente')->findBy(array('estado'=>true,'local'=>$local));

        $dql = "SELECT c FROM AppBundle:Cliente c ";
        $dql .= " JOIN c.local l";
        $dql .= " JOIN l.empresa e";
        $dql .= " WHERE  c.estado = 1  ";

        if($empresa != ''){
            $dql .= " AND e.id =:empresa  ";
        }

        $dql .= " ORDER BY c.razonSocial ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
        $clientes =  $query->getResult();    


        return $this->render('reporte/cliente.html.twig', array(
            'titulo'   							=> 'Reporte de Clientes',
            'clientes'  						=> $clientes,
        ));
    }

    /**
     * Muestra un reporte de las ventas anuladas
     *
     * @Route("/ventasanuladas", name="reporte_ventasanuladas")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')   or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')  ")
     */
    public function ventasAnuladasAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro             = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio = $filtro['fecha_inicio'];
        $fecha_fin      = $filtro['fecha_fin'];        

        $ventas  =  $this->container->get('AppBundle\Util\Util')->ventasAnuladas($empresa,$fecha_inicio,$fecha_fin);


        return $this->render('reporte/ventasanuladas.html.twig', array(
            'titulo'                            => 'Ventas anuladas',
            'ventas'                            => $ventas,
            'fecha_inicio'                      => $fecha_inicio,
            'fecha_fin'                         => $fecha_fin,
            'form'                              => $form->createView(),            
        ));
    }

    /**
     * Muestra un reporte de los proveedores
     *
     * @Route("/proveedor", name="reporte_proveedor")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function proveedorAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $dql = "SELECT p FROM AppBundle:Proveedor p ";
        $dql .= " JOIN p.empresa e";
        $dql .= " WHERE  p.estado = 1  ";

        if($empresa != ''){
            $dql .= " AND e.id =:empresa  ";
        }

        $dql .= " ORDER BY p.nombre ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
        $proveedores =  $query->getResult();    


        return $this->render('reporte/proveedor.html.twig', array(
            'titulo'                            => 'Reporte de Proveedores',
            'proveedores'                       => $proveedores,
        ));
    }

    /**
     * Muestra un reporte de las ventas pagadas al contado
     *
     * @Route("/ventascontado", name="reporte_ventascontado")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')  or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')  ")
     */
    public function ventasContadoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        //$form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro             = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio = $filtro['fecha_inicio'];
        $fecha_fin      = $filtro['fecha_fin'];        

        $ventas  =  $this->container->get('AppBundle\Util\Util')->ventasContado($empresa,$fecha_inicio,$fecha_fin);

        // dump(count($ventas));
        // dump($empresa);
        // die();


        return $this->render('reporte/ventascontado.html.twig', array(
            'titulo'                            => 'Ventas contado',
            'ventas'                            => $ventas,
            'fecha_inicio'                      => $fecha_inicio,
            'fecha_fin'                         => $fecha_fin,
            //'form'                              => $form->createView(),            
        ));
    }

    /**
     * Muestra un reporte de las ventas pagadas al credito
     *
     * @Route("/ventascredito", name="reporte_ventascredito")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')  or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')  ")
     */
    public function ventasCreditoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro         = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio   = $filtro['fecha_inicio'];
        $fecha_fin      = $filtro['fecha_fin'];        

        $ventas  =  $this->container->get('AppBundle\Util\Util')->ventasCredito($empresa,$fecha_inicio,$fecha_fin);
        //$ventasCreditoEnDolares  =  $this->container->get('AppBundle\Util\Util')->ventasCreditoEnDolares($empresa,$fecha_inicio,$fecha_fin);


        return $this->render('reporte/ventascredito.html.twig', array(
            'titulo'                            => 'Ventas crédito',
            'ventas'                            => $ventas,
            //'ventasCreditoEnDolares'            => $ventasCreditoEnDolares,
            'fecha_inicio'                      => $fecha_inicio,
            'fecha_fin'                         => $fecha_fin,
            'form'                              => $form->createView(),            
        ));
    }

    /**
     * Muestra un reporte de las ventas pagadas con tarjeta d credito
     *
     * @Route("/ventastarjetacredito", name="reporte_ventastarjetacredito")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')  or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')  ")
     */
    public function ventasTarjetaCreditoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro         = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio   = $filtro['fecha_inicio'];
        $fecha_fin      = $filtro['fecha_fin'];        

        $ventas  =  $this->container->get('AppBundle\Util\Util')->ventasTarjetaCredito($empresa,$fecha_inicio,$fecha_fin);


        return $this->render('reporte/ventastarjetacredito.html.twig', array(
            'titulo'                            => 'Ventas tarjeta de crédito',
            'ventas'                            => $ventas,
            'fecha_inicio'                      => $fecha_inicio,
            'fecha_fin'                         => $fecha_fin,
            'form'                              => $form->createView(),            
        ));
    }

    /**
     * Muestra un reporte de las ventas pagadas con nota d credito
     *
     * @Route("/ventasnotacredito", name="reporte_ventasnotacredito")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')  or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')  ")
     */
    public function ventasNotaCreditoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro         = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio   = $filtro['fecha_inicio'];
        $fecha_fin      = $filtro['fecha_fin'];        

        $ventas  =  $this->container->get('AppBundle\Util\Util')->ventasNotaCredito($empresa,$fecha_inicio,$fecha_fin);


        return $this->render('reporte/ventasnotacredito.html.twig', array(
            'titulo'                            => 'Ventas nota de crédito',
            'ventas'                            => $ventas,
            'fecha_inicio'                      => $fecha_inicio,
            'fecha_fin'                         => $fecha_fin,
            'form'                              => $form->createView(),            
        ));
    }

    /**
     * Muestra un reporte de ventas detalladas
     *
     * @Route("/detalleventa", name="reporte_detalleventa")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')   or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')  ")
     */
    public function detalleVentaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro             = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio = $filtro['fecha_inicio'];
        $fecha_fin      = $filtro['fecha_fin'];        

        $ventas  =  $this->container->get('AppBundle\Util\Util')->detalleVenta($empresa,$fecha_inicio,$fecha_fin);


        return $this->render('reporte/detalleventa.html.twig', array(
            'titulo'                            => 'Detalle de ventas',
            'ventas'                            => $ventas,
            'fecha_inicio'                      => $fecha_inicio,
            'fecha_fin'                         => $fecha_fin,
            'form'                              => $form->createView(),            
        ));
    }

    /**
     * @Route("/modificacionstockproducto", name="reporte_modificacionstockproducto")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function modificacionStockProductoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $dql = "SELECT lm FROM AppBundle:LogModificacion lm ";
        $dql .= " JOIN lm.productoXLocal pxl";
        $dql .= " JOIN lm.modificacionTipo mt";
        $dql .= " JOIN pxl.producto p";
        $dql .= " WHERE  p.estado = 1  AND mt.nombre = 'stock' ";

        if($local != ''){
            $dql .= " AND pxl.local =:local  ";
        }

        $dql .= " ORDER BY p.nombre ";

        $query = $em->createQuery($dql);

        if($local != ''){
            $query->setParameter('local',$local);         
        }
 
        $logs =  $query->getResult();    
        
        return $this->render('reporte/modificacionstockproducto.html.twig', array(
            'logs' => $logs,
            'titulo' => 'Stock modificados',
        ));

    }   

    /**
     * Muestra un reporte de las ventas pagadas al credito
     *
     * @Route("/comprasanulada", name="reporte_comprasanulada")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function comprasAnuladaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro         = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio   = $filtro['fecha_inicio'];
        $fecha_fin      = $filtro['fecha_fin'];        

        $compras  =  $this->container->get('AppBundle\Util\Util')->comprasAnulada($empresa,$fecha_inicio,$fecha_fin);


        return $this->render('reporte/comprasanulada.html.twig', array(
            'titulo'                            => 'Compras anuladas',
            'compras'                            => $compras,
            'fecha_inicio'                      => $fecha_inicio,
            'fecha_fin'                         => $fecha_fin,
            'form'                              => $form->createView(),            
        ));
    }


    /**
     * Muestra un reporte de las cajas
     *
     * @Route("/caja", name="reporte_caja")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')  or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')  ")
     */
    public function cajaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $aperturas = null;

        $filtro     = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha      = ($filtro['fecha_inicio'])? $filtro['fecha_inicio'] : '';
        $caja       = ($filtro['caja']) ? $filtro['caja']:'';
        $forma_pago = ($filtro['forma_pago']) ? $filtro['forma_pago']:'';

        if($caja != '' && $fecha != ''){
            $aperturas  =  $this->container->get('AppBundle\Util\Util')->cajaAperturas($empresa,$caja,$fecha,$forma_pago);
        }

        $form    = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType', null,array('empresa'=>$empresa)); 

        //dump($caja);
        //dump($aperturas); 

        return $this->render('reporte/caja.html.twig', array(
            'titulo'           => 'Detalle de ventas por caja',
            'aperturas'        => $aperturas,
            'fecha'            => $fecha,
            'caja'             => $caja,
            'forma_pago'       => $forma_pago,
            'form'             => $form->createView(),            
        ));
    }


    /**
     * Muestra un reporte de los productos valorizados x stock
     *
     * @Route("/productosvalorizados/stock", name="reporte_productosvalorizados_stock")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')  or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')  ")
     */
    public function productosvalorizadosStockAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa)); 


        return $this->render('reporte/productosvalorizadostock.html.twig', array(
            'titulo'                            => 'Stock valorizado',
            'form'                              => $form->createView(),            
        ));
    }


    /**
     * Muestra un reporte de las ventas diarias
     *
     * @Route("/siderperu", name="reporte_siderperu")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR') ")
     */
    public function siderperuAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro             = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio       = $filtro['fecha_inicio'];
        $fecha_fin          = $filtro['fecha_fin'];        

        $ventas  =  $this->container->get('AppBundle\Util\Util')->siderperu($empresa,$fecha_inicio,$fecha_fin);


        return $this->render('reporte/siderperu.html.twig', array(
            'titulo'            => 'Siderperú',
            'ventas'            => $ventas,
            'fecha_inicio'      => $fecha_inicio,
            'fecha_fin'         => $fecha_fin,
            'form'              => $form->createView(),            
        ));
    }


    /**
     * Muestra un reporte de los productos mas vendidos
     *
     * @Route("/documentosconerror", name="reporte_documentosconerror")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function documentosConErrorAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>1));

        $filtro             = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio = $filtro['fecha_inicio'];
        $fecha_fin      = $filtro['fecha_fin'];        

        $documentos  =  $this->container->get('AppBundle\Util\Util')->obtenerDocumentosConError($fecha_inicio,$fecha_fin);


        return $this->render('reporte/documentosconerror.html.twig', array(
            'titulo'                => 'Documentos con error',
            'documentos'  => $documentos,
            'fecha_inicio'          => $fecha_inicio,
            'fecha_fin'             => $fecha_fin,
            'form'                  => $form->createView(),            
        ));
    }

    /**
     * Enviar registro a NUBEFACT
     *
     * @Route("/{id}/enviar/nubefact", name="reporte_enviar_nubefact")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function enviarNubefactAction(Request $request, FacturaVenta $facturaVenta)
    {
        $localObj = $facturaVenta->getLocal();

        $leer_respuesta = array();
        if($localObj->getUrlFacturacion() != '' && $localObj->getTokenFacturacion() != '' && $localObj->getFacturacionElectronica() != false)
        {

            $data_json = $this->container->get('AppBundle\Util\Util')->generarArchivoPerdidoJson($facturaVenta);
            $respuesta = $this->container->get('AppBundle\Util\Util')->enviarArchivoJson($data_json,$localObj);
            $leer_respuesta = json_decode($respuesta, true);

            if(isset($leer_respuesta['errors']))
            {

                $this->addFlash("danger",$leer_respuesta['errors']);
                return $this->redirectToRoute('reporte_documentosconerror'); 
            }
            else
            {
                $mensaje = implode(" | ", $leer_respuesta);
                $this->addFlash("success"," El documento fue enviado.".$mensaje);
                return $this->redirectToRoute('reporte_documentosconerror'); 

            }

        }
        else
        {
            $this->addFlash("danger"," La facturación electrónica está inactiva.");
            return $this->redirectToRoute('reporte_documentosconerror'); 

        }

        return $this->redirectToRoute('reporte_documentosconerror');
    }

    /**
     * Muestra un reporte de el registro de ventas 
     *
     * @Route("/registro/ventas", name="reporte_registro_ventas")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO') ")
     */
    public function registroVentasAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro             = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio       = $filtro['fecha_inicio'];
        $fecha_fin          = $filtro['fecha_fin'];        

        $ventas  =  $this->container->get('AppBundle\Util\Util')->registroVentas($empresa,$fecha_inicio,$fecha_fin);


        return $this->render('reporte/registroventas.html.twig', array(
            'titulo'            => 'Registro de ventas',
            'ventas'            => $ventas,
            'fecha_inicio'      => $fecha_inicio,
            'fecha_fin'         => $fecha_fin,
            'form'              => $form->createView(),            
        ));
    }


    /**
     * Muestra un reporte del registro de ventas al detalle
     *
     * @Route("/registro/ventas/detalle", name="reporte_registro_ventas_detalle")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function registroVentasDetalleAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro             = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio       = $filtro['fecha_inicio'];
        $fecha_fin          = $filtro['fecha_fin'];        

        $ventas  =  $this->container->get('AppBundle\Util\Util')->registroVentasDetalle($empresa,$fecha_inicio,$fecha_fin);


        return $this->render('reporte/registroventasdetalle.html.twig', array(
            'titulo'            => 'Registro de ventas - Detalle',
            'ventas'            => $ventas,
            'fecha_inicio'      => $fecha_inicio,
            'fecha_fin'         => $fecha_fin,
            'form'              => $form->createView(),            
        ));
    }

   
    /**
     * Muestra un reporte de ventas detallada por producto
     *
     * @Route("/venta/detalle/producto", name="reporte_venta_detalle_producto")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function ventaDetalleProductoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        //Actualizamos precio costo en detalle_venta

        // $detalles = $em->getRepository('AppBundle:DetalleVenta')->findAll();

        // foreach($detalles as $detalle)
        // {
        //     if($detalle->getProductoXLocal())
        //     {
        //         $precio_costo = $this->container->get('AppBundle\Util\Util')->obtenerPrecioCosto($detalle->getProductoXLocal()->getId(),$detalle->getCantidad());
        //         $detalle->setPrecioCosto($precio_costo);
        //         $em->persist($detalle);                
        //     }


        // }

        // try {
        //     $em->flush();
        // } catch (\Exception $e) {
            
        // }


        $ventas  =  $this->container->get('AppBundle\Util\Util')->ventaDetalleProducto($empresa);
        $ventas = $this->construirDataVentasDetalleProducto($ventas);

        return $this->render('reporte/ventadetalleproducto.html.twig', array(
            'titulo'            => 'Inteligencia de negocios',
            'ventas'             => $ventas,
        ));
    }


    private function construirDataVentasDetalleProducto($ventas)
    {
        $data = array();

        $j=0;
        foreach($ventas as $i=>$value)
        {
            $data[$j]['Ano'] = $value['ano'];
            $data[$j]['Mes'] = $this->nombreMes($value['mes']);
            $data[$j]['Fecha'] = $value['fecha'];
            $data[$j]['Ticket'] = $value['ticket'];
            $data[$j]['Documento'] = $value['documento'];
            $data[$j]['Cliente'] = $value['cliente'];
            $data[$j]['Local'] = $value['local'];
            $data[$j]['Producto'] = $value['producto'];
            $data[$j]['Cantidad'] = $value['cantidad'];
            $data[$j]['Precio'] = $value['precio'];
            $data[$j]['Subtotal'] = $value['subtotal'];
            $data[$j]['Precio_costo'] = ($value['precio_costo']) ? $value['precio_costo'] : '0';
            $data[$j]['Forma_pago'] = $value['forma_pago'];
            $data[$j]['Ganancia'] = $value['ganancia'];

            $j++;

        }



        $data_json = json_encode($data);
        return $data_json; 
    }

    private function nombreMes($mes){

        switch ($mes) {
            case '1':
                $nombre_mes = 'Enero';
                break;
            case '2':
                $nombre_mes = 'Febrero';
                break;
            
            case '3':
                $nombre_mes = 'Marzo';
                break;
            
            case '4':
                $nombre_mes = 'Abril';
                break;
            
            case '5':
                $nombre_mes = 'Mayo';
                break;
            
            case '6':
                $nombre_mes = 'Junio';
                break;
            
            case '7':
                $nombre_mes = 'Julio';
                break;
            
            case '8':
                $nombre_mes = 'Agosto';
                break;
            
            case '9':
                $nombre_mes = 'Setiembre';
                break;
            
            case '10':
                $nombre_mes = 'Octubre';
                break;
            
            case '11':
                $nombre_mes = 'Noviembre';
                break;
            
            case '12':
                $nombre_mes = 'Diciembre';
                break;
            
        }

        return $nombre_mes;

    }


    /**
     * Muestra un reporte de el registro de ventas 
     *
     * @Route("/ganancias", name="reporte_ganancias")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function gananciasAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro             = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio       = $filtro['fecha_inicio'];
        $fecha_fin          = $filtro['fecha_fin'];        

        $ventas  =  $this->container->get('AppBundle\Util\Util')->ganancias($empresa,$fecha_inicio,$fecha_fin);


        return $this->render('reporte/ganancias.html.twig', array(
            'titulo'            => 'Ganancias',
            'ventas'            => $ventas,
            'fecha_inicio'      => $fecha_inicio,
            'fecha_fin'         => $fecha_fin,
            'form'              => $form->createView(),            
        ));
    }

    /**
     * Muestra un reporte de las ventas diarias
     *
     * @Route("/movimiento/productos", name="reporte_movimiento_productos")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR') ")
     */
    public function movimientoProductosAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $filtro             = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $local_selec        = ($filtro['local']) ? $filtro['local'] : $local;
        $producto           = ($request->request->get('producto_select')) ? $request->request->get('producto_select'): null;     

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa,'local'=>$local_selec));

        $ventas = null;

        if($producto){
            $productoXLocal = $em->getRepository('AppBundle:ProductoXLocal')->find($producto);
            $ventas  =  $this->container->get('AppBundle\Util\Util')->movimientoProductos($local,$producto);
        }


        $dql = "SELECT pxl FROM AppBundle:ProductoXLocal pxl  ";
        $dql .= " JOIN pxl.local l  ";
        $dql .= " JOIN pxl.producto p ";
        $dql .= "  WHERE pxl.estado = 1 AND p.estado = 1 ";

        if ($local != '') {

            $dql .= " AND  l.id =:local";
        }

        $dql .= " ORDER BY p.nombre ASC";

        $resp = $em->createQuery($dql);

        if ($local !='') {
            $resp->setParameter('local', $local_selec);
        }


        return $this->render('reporte/movimientoproductos.html.twig', array(
            'titulo'            => 'Movimiento de productos',
            'ventas'            => $ventas,
            'local_selec'       => $local_selec,
            'producto'          => $producto,
            'productoXLocal'    => $productoXLocal,
            'form'              => $form->createView(),
            'lista_producto'    => $resp->getResult()          
        ));
    }

    /**
     * Muestra un reporte de ventas detalladas
     *
     * @Route("/detallecompra", name="reporte_detallecompra")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')   or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')  ")
     */
    public function detalleCompraAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa));

        $filtro             = $request->request->get('appbundle_ventatrabajadormensual_filtro');
        $fecha_inicio = $filtro['fecha_inicio'];
        $fecha_fin      = $filtro['fecha_fin'];        

        $ventas  =  $this->container->get('AppBundle\Util\Util')->detalleCompra($empresa,$fecha_inicio,$fecha_fin);


        return $this->render('reporte/detallecompra.html.twig', array(
            'titulo'                            => 'Detalle de compras',
            'ventas'                            => $ventas,
            'fecha_inicio'                      => $fecha_inicio,
            'fecha_fin'                         => $fecha_fin,
            'form'                              => $form->createView(),            
        ));
    }

}
