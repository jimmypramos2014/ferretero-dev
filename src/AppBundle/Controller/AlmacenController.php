<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity\Transferencia;
use AppBundle\Entity\TransferenciaXProducto;
use AppBundle\Entity\FacturaVenta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Almacen controller.
 *
 * @Route("almacen")
 */
class AlmacenController extends Controller
{
    /**
     * @Route("/productosxlocal", name="almacen_productosxlocal")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        // if(!$session->get('caja')){
        //     return $this->redirectToRoute('seleccionar_local');
        // }

        // $dql = "SELECT pxl FROM AppBundle:ProductoXLocal pxl ";
        // $dql .= " JOIN pxl.local l";
        // $dql .= " JOIN pxl.producto p";
        // $dql .= " JOIN l.empresa e";
        // $dql .= " WHERE  p.estado = 1  ";

        // if($empresa != ''){
        //     $dql .= " AND e.id =:empresa  ";
        // }

        // $dql .= " ORDER BY p.nombre ";

        // $query = $em->createQuery($dql);

        // if($empresa != ''){
        //     $query->setParameter('empresa',$empresa);         
        // }
 
        // $productosXLocal =  $query->getResult();   

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa)); 
        
        return $this->render('almacen/index.html.twig', array(
            'form'     => $form->createView(),
            'titulo' => 'Stock',
        ));

    }

    /**
     * Lista de Transferencia de productos entre locales
     *
     * @Route("/transferencia/lista", name="almacen_lista_transferencia")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR')")
     */
    public function listaTransferenciaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $formTransporte = $this->createForm('AppBundle\Form\TransferenciaXTransporteType', null,array('empresa'=>$empresa));

        $formEmpresaTransporte = $this->createForm('AppBundle\Form\TransporteType',null,array('empresa'=>$empresa));

        $dql = "SELECT t FROM AppBundle:Transferencia t ";
        $dql .= " JOIN t.empresa e";
        $dql .= " WHERE  e.id =:empresa  AND t.estado = 1 ";
        $dql .= " ORDER BY t.fecha DESC ";

        $query = $em->createQuery($dql);

        $query->setParameter('empresa',$empresa);         
 
        $transferencias =  $query->getResult();    

        $form = $this->createForm('AppBundle\Form\VentaTrabajadorMensualFiltroType',null,array('empresa'=>$empresa)); 

        return $this->render('almacen/listaTransferencia.html.twig', array(
            'transferencias'        => $transferencias,
            'titulo'                => 'Lista de Transferencias',
            'formTransporte'        => $formTransporte->createView(),
            'formEmpresaTransporte' => $formEmpresaTransporte->createView(),
            'form'                  => $form->createView(),
        ));

    }


    /**
     * Muestra el formato de impresion de guia de remision.
     *
     * @Route("/{id}/guiaremision", name="almacen_show_guiaremision")
     * @Method({"POST","GET"})
     */
    public function showGuiaremisionAction(Request $request,Transferencia $transferencia)
    {

        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $host           = $request->getHost();

        $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);
        $empresaObj     = $em->getRepository('AppBundle:Empresa')->find($empresa);


        //$facturaVenta   = $em->getRepository('AppBundle:FacturaVenta')->findOneBy(array('ticket'=>$transferencia->getNumeroDocumento()));

        $dql = "SELECT fv FROM AppBundle:FacturaVenta fv ";
        $dql .= " JOIN fv.venta v";
        $dql .= " JOIN fv.local l";
        $dql .= " JOIN l.empresa e";
        $dql .= " WHERE  e.id =:empresa  AND fv.ticket =:ticket  AND v.estado = 1 ";

        $query = $em->createQuery($dql);

        $query->setParameter('empresa',$empresa);
        $query->setParameter('ticket',$transferencia->getNumeroDocumento());         
 
        $facturaVenta =  $query->getOneOrNullResult();


        $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
        $dql .= " JOIN cxd.documento d";
        $dql .= " JOIN d.local l";
        $dql .= " WHERE  l.id =:local  AND d.codigo = '02' AND cxd.estado = 1 ";

        $query = $em->createQuery($dql);

        $query->setParameter('local',$local);         
 
        $componentesXDocumento =  $query->getResult();

        $html = $this->render('almacen/showGuiaremision.html.twig', array(
                'transferencia' => $transferencia,
                'facturaVenta'  => $facturaVenta,
                'componentesXDocumento'  => $componentesXDocumento,
                'localObj'      => $localObj,
                'host'          => $host
            ))->getContent();

        $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-height' => $empresaObj->getGuiaremisionLargo(),'page-width'  => $empresaObj->getGuiaremisionAncho(),'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));//'page-size'=> "A4",

        $f = 'guiaremision_'.$facturaVenta->getTicket();

        return new \Symfony\Component\HttpFoundation\Response(
                $pdf, 200, array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'inline; filename="'.$f.'.pdf"',
                     
        ));

    }


    /**
     * Transferencia de productos entre locales
     *
     * @Route("/transferencia", name="almacen_transferencia")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function transferenciaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $formProducto = $this->createForm('AppBundle\Form\DetalleTransferenciaProductoType', null,array('local'=>$local,'empresa'=>$empresa));

        $detalleCompras = $em->getRepository('AppBundle:DetalleCompra')->findAll();

        $productosXLocal      = $em->getRepository('AppBundle:ProductoXLocal')->findBy(array('local'=>$local));   

        return $this->render('almacen/transferencia.html.twig', array(
            'productosXLocal' => $productosXLocal,
            'titulo' => 'Transferencias',
            'formProducto'   => $formProducto->createView(),
        ));

    }


    /**
     * Crea una nueva transferencia de productos entre tiendas
     *
     * @Route("/new/transferencia", name="almacen_new_transferencia")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR')")
     */
    public function newTransferenciaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $fecha = new \DateTime();

        $formProducto = $request->request->get('appbundle_detalletransferencia_producto');



        $local_inicio       = $formProducto['local_inicio'];
        $local_fin          = $formProducto['local_fin'];
        $documento          = $formProducto['documento'];
        $numero_documento   = $formProducto['numero_documento'];
        $descripcion        = $formProducto['descripcion'];

        $dql = "SELECT pxl FROM AppBundle:ProductoXLocal pxl ";
        $dql .= " JOIN pxl.producto p";
        $dql .= " JOIN pxl.local l";
        $dql .= " JOIN l.empresa e";
        $dql .= " WHERE e.id =:empresa AND p.estado = 1 ORDER BY p.nombre ";
        $query = $em->createQuery($dql);
        $query->setParameter('empresa',$empresa);

        $productosXLocal =  $query->getResult();


        //Guardamos el objeto Transferencia
        $transferencia  = new Transferencia();
        $localInicio    = $em->getRepository('AppBundle:EmpresaLocal')->find($local_inicio);
        $localFin       = $em->getRepository('AppBundle:EmpresaLocal')->find($local_fin);
        $transferencia->setLocalInicio($localInicio);
        $transferencia->setLocalFin($localFin);
        $transferencia->setFecha($fecha);
        $transferencia->setDescripcion($descripcion);
        $transferencia->setDocumento($documento);
        $usuario    = $em->getRepository('AppBundle:Usuario')->find($session->get('usuario'));
        $transferencia->setUsuario($usuario);
        $numero_documento = mt_rand(100000,999999);
        $transferencia->setNumeroDocumento($numero_documento);//$numero_documento

        $motivoTraslado    = $em->getRepository('AppBundle:MotivoTraslado')->findOneBy(array('codigo'=>'11'));
        $transferencia->setMotivoTraslado($motivoTraslado);
        $empresaObj       = $em->getRepository('AppBundle:Empresa')->find($empresa);
        $transferencia->setEmpresa($empresaObj);
        $transferencia->setEstado(true);
        $em->persist($transferencia);


        $pago_total = 0;
        foreach($productosXLocal as $productoXLocal){

            if(null !== $request->request->get('productoid_'.$productoXLocal->getId()) ){

                $producto_id = (int)$request->request->get('productoid_'.$productoXLocal->getId());

                if($producto_id){                    

                    $precio     = $request->request->get('precio_'.$producto_id);
                    $cantidad   = (double)$request->request->get('cantidad_'.$producto_id);

                    $subtotal = $cantidad*$precio;

                    //Guardamos el detalle de la transferencia
                    $transferenciaXProducto = new TransferenciaXProducto();
                    $transferenciaXProducto->setProductoXLocal($productoXLocal);
                    $transferenciaXProducto->setCantidad($cantidad);
                    $transferenciaXProducto->setTransferencia($transferencia);
                    $transferenciaXProducto->setPrecio($precio);
                    $em->persist($transferenciaXProducto);

                    $pago_total += $subtotal;

                    //Actualizamos almacen
                    $productoXLocalDestino    = $em->getRepository('AppBundle:ProductoXLocal')->findOneBy(array('local'=>$localFin,'producto'=>$productoXLocal->getProducto() ));

                    $this->container->get('AppBundle\Util\Util')->aumentarTransferenciaAlmacen($productoXLocalDestino->getId(),$cantidad);

                    //Guardamos data de formato sunatf121
                    //$this->container->get('AppBundle\Util\Util')->registrarSunatF121($transferencia,$productoXLocalDestino,'09','11',$cantidad,'entrada');

                    $this->container->get('AppBundle\Util\Util')->disminuirAlmacen($productoXLocal->getId(),$cantidad);

                    //Guardamos data de formato sunatf121
                    //$this->container->get('AppBundle\Util\Util')->registrarSunatF121($transferencia,$productoXLocal,'09','11',$cantidad,'salida');

                }

            }
        

        }


        try {

            $em->flush();
            $this->addFlash("success", "La transferencia fue realizada exitosamente.");

        } catch (\Exception $e) {

            $this->addFlash("danger", $e."-Ocurrió un error inesperado, el producto(s) no se transfirió.");
            return $this->redirectToRoute('almacen_transferencia');                
        }

        return $this->redirectToRoute('almacen_transferencia');

    }


    /**
     * Procesar data desde el server para DataTables
     *
     * @Route("/datatable", name="almacen_datatable")
     * @Method({"GET", "POST"})
     * 
     */
    public function dataTableAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $result = $this->container->get('AppBundle\Util\Util')->getProductoStockXLocalDT('v_producto_stock_x_local',$empresa);

        $result = self::convert_from_latin1_to_utf8_recursively($result);

        $response = new JsonResponse($result);

        return $response;
     

    }

    /**
     * 
     *
     * @Route("/datatable/stock/valorizado", name="almacen_stock_valorizado")
     * @Method({"GET", "POST"})
     * 
     */
    public function dataTableStockValorizadoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $result = $this->container->get('AppBundle\Util\Util')->getProductoStockValorizadoXLocalDT('v_producto_stock_valorizado_x_local',$empresa);

        $result = self::convert_from_latin1_to_utf8_recursively($result);

        $response = new JsonResponse($result);

        return $response;
     

    }

    private function convert_from_latin1_to_utf8_recursively($dat)
    {
      if (is_string($dat)) {
         return utf8_encode($dat);
      } elseif (is_array($dat)) {
         $ret = [];
         foreach ($dat as $i => $d) $ret[ $i ] = self::convert_from_latin1_to_utf8_recursively($d);

         return $ret;
      } elseif (is_object($dat)) {
         foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

         return $dat;
      } else {
         return $dat;
      }
    }

}


