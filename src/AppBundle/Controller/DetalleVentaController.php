<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Venta;
use AppBundle\Entity\Cliente;
use AppBundle\Entity\DetalleVenta;
use AppBundle\Entity\FacturaVenta;
use AppBundle\Entity\VentaFormaPago;
use AppBundle\Entity\Transferencia;
use AppBundle\Entity\TransferenciaXProducto;
use AppBundle\Entity\Proforma;
use AppBundle\Entity\DetalleProforma;
use AppBundle\Entity\DetalleVentaEntrega;
use AppBundle\Entity\DetalleVentaEntregaDatosEnvio;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Detalleventum controller.
 *
 * @Route("detalleventa")
 */
class DetalleVentaController extends Controller
{

    /**
     * Lists all detalleVentum entities.
     *
     * @Route("/", name="detalleventa_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');

        $localObj   = $em->getRepository('AppBundle:EmpresaLocal')->find($local);
        $limite_venta = ($localObj->getLimiteVenta())? $localObj->getLimiteVenta():0;


        $formCliente    = $this->createForm('AppBundle\Form\DetalleVentaClienteType', null);

        $productoXLocals = $em->getRepository('AppBundle:ProductoXLocal')->findBy(array('local'=>$local));

        //$productoXLocals = $this->container->get('AppBundle\Util\Util')->productosMasVendidosXLocal($local,null);         

        return $this->render('detalleventa/index.html.twig', array(
            'productoXLocals'     => $productoXLocals,
            'titulo'        => 'Registro de ventas',
            'formCliente'   => $formCliente->createView(),
            'local'         => $local,
            'limite_venta'  => $limite_venta
        ));
    }

    /**
     * Lists all detalleVentum entities.
     *
     * @Route("/lista", name="detalleventa_lista")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function listaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $empresaObj     = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $enlacePdf      = ($session->get('enlace_pdf'))? $session->get('enlace_pdf') : '';


        if(null !== $session->get('enlace_pdf') )
        {
            $session->remove('enlace_pdf');
        }


        // $dql = "SELECT fv FROM AppBundle:FacturaVenta fv ";
        // $dql .= " JOIN fv.venta v";
        // $dql .= " JOIN v.empleado e";
        // $dql .= " JOIN e.local l";
        // $dql .= " JOIN l.empresa em";
        // $dql .= " WHERE  v.estado = 1 AND fv.tipo = 2 ";

        // if($empresa != ''){
        //     $dql .= " AND em.id =:empresa  ";
        // }

        // $dql .= " ORDER BY fv.fecha DESC ";

        // $query = $em->createQuery($dql);

        // if($empresa != ''){
        //     $query->setParameter('empresa',$empresa);         
        // }
 
        // $facturas =  $query->getResult();

        $formCliente    = $this->createForm('AppBundle\Form\DetalleVentaClienteType', null,array('empresa'=>$empresa));    

        return $this->render('detalleventa/lista.html.twig', array(
            //'facturas'      => $facturas,
            'titulo'        => 'Lista de ventas',
            'local'         => $local,
            'formCliente'   => $formCliente->createView(),
            'enlacePdf'     => $enlacePdf,
            'empresaObj'    => $empresaObj
        ));
    }


    /**
     * Punto de venta
     *
     * @Route("/puntoventa", name="detalleventa_puntoventa")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')")
     */
    public function puntoVentaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $rutaPdf    = utf8_decode($request->query->get('rutaPdf'));
        $guiaHtml   = $request->query->get('guiaHtml');
        $facturaId  = $request->query->get('facturaId');

        $fecha = new \DateTime();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $localObj   = $em->getRepository('AppBundle:EmpresaLocal')->find($local);
        $limite_venta = ($localObj->getLimiteVenta())? $localObj->getLimiteVenta():0;

        $tipoCambio = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$fecha));
        $valorTipoCambio = ($tipoCambio) ? $tipoCambio->getVenta() : null;


        $formCliente    = $this->createForm('AppBundle\Form\DetalleVentaClienteType', null,array('empresa'=>$empresa));
        $productoXLocals = $this->container->get('AppBundle\Util\Util')->productosMasVendidosXLocal($local,'','',$valorTipoCambio);
        $categorias = $this->container->get('AppBundle\Util\Util')->obtenerCategorias($local);

        

        $formClientePv    = $this->createForm('AppBundle\Form\ClientePvType',null);

        return $this->render('detalleventa/puntoventa.html.twig', array(
            'titulo'            =>'Punto de Venta',
            'formCliente'       => $formCliente->createView(),
            'formClientePv'     => $formClientePv->createView(),
            'local'             => $local,
            'limite_venta'      => $limite_venta,
            'productoXLocals'   => $productoXLocals,
            'categorias'        => $categorias,
            'tipoCambio'        => $tipoCambio,
            'rutaPdf'           => $rutaPdf,
            'guiaHtml'          => $guiaHtml,
            'facturaId'         => $facturaId,
            'localObj'         => $localObj
        ));
    }       

    /**
     * Creates a new detalleVentum entity.
     *
     * @Route("/new", name="detalleventa_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')")
     */
    public function newAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $caja           = $session->get('caja');

        $cajaObj    = $em->getRepository('AppBundle:Caja')->find($caja);
        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $formCliente = $request->request->get('appbundle_detalleventa_cliente');

        // dump($request->request->get('monto_entregado_pago'));
        // die();

        $empleado_id    = $formCliente['vendedor'];
        $forma_pago_id  = $formCliente['forma_pago'];
        $numero_dias    = ($formCliente['numero_dias'])?$formCliente['numero_dias']:0;
        $estado         = $formCliente['estado'];
        $monto_acuenta  = ($formCliente['monto_acuenta'])?$formCliente['monto_acuenta']:0;

        $fecha = ($formCliente['fecha'] != '')? \DateTime::createFromFormat('d/m/Y', $formCliente['fecha']) : new \DateTime();

        $condicion               = (isset($formCliente['condicion']))?$formCliente['condicion']:false;
        $numero_guiaremision     = ($formCliente['numero_guiaremision'])?$formCliente['numero_guiaremision']:'';
        //$numero_proforma         = ($formCliente['numero_proforma'])?$formCliente['numero_proforma']:'';
        $numero_documento        = ($formCliente['numero_documento'])?$formCliente['numero_documento']:'';

        $enviarFactura           = (isset($formCliente['enviarFactura']))?$formCliente['enviarFactura']:false;
        $detraccion              = (isset($formCliente['detraccion']))?$formCliente['detraccion']:false;

        $moneda              = (isset($formCliente['moneda']))?$formCliente['moneda']:1;

        $monto_entregado = ($request->request->get('monto_entregado_pago'))? $request->request->get('monto_entregado_pago'): null;

        $formapago = $em->getRepository('AppBundle:FormaPago')->find($forma_pago_id);        

        $cliente = null;
        $localObj   = $em->getRepository('AppBundle:EmpresaLocal')->find($local);
        $limite_venta = ($localObj->getLimiteVenta())? $localObj->getLimiteVenta():0;

        $cliente_id     = $formCliente['cliente_select'];
        $cliente        = $em->getRepository('AppBundle:Cliente')->find($cliente_id);
        

        $documento      = $formCliente['documento'];
        $numero_voucher = $formCliente['numero_voucher'];

        //Guardamos el objeto Venta
        $ventaObj = new Venta();
        $empleado = $em->getRepository('AppBundle:Empleado')->find($empleado_id);
        $ventaObj->setEmpleado($empleado);
        $ventaObj->setFecha($fecha);
        $ventaObj->setEstado($estado);
        $ventaObj->setCondicion($condicion);
        $em->persist($ventaObj);

        //$ultimoIdFacturaVenta = ($em->getRepository('AppBundle:FacturaVenta')->findLastId()) ? $em->getRepository('AppBundle:FacturaVenta')->findLastId()->getId() :1;
        $empresa_mismo_prefijo_multilocal = ($empresaObj->getPermitirMismoPrefijoMultilocal()) ? $empresa : '';
        $ultimoIdFacturaVenta = $this->container->get('AppBundle\Util\Util')->generarNumeroDocumento('factura_venta',$local,$documento,$empresa_mismo_prefijo_multilocal);

        //Guardamos el objeto FacturaVenta
        $facturaventaObj = new FacturaVenta();
        
        $facturaventaObj->setCliente($cliente);
        $facturaventaObj->setVenta($ventaObj);
        $facturaventaObj->setFecha($fecha);
        $facturaventaObj->setNumeroGuiaremision($numero_guiaremision);
        $facturaventaObj->setLocal($localObj);
        $facturaventaObj->setCaja($cajaObj);
        $facturaventaObj->setFacturaEnviada($enviarFactura);
        $facturaventaObj->setDetraccion($detraccion);
        $facturaventaObj->setEnviadoSunat(true);

        if($localObj->getFacturacionElectronica())
        {
            $facturaventaObj->setEmisionElectronica(true);
        }

        $tipoVenta = $em->getRepository('AppBundle:FacturaVentaTipo')->find(2);
        $facturaventaObj->setTipo($tipoVenta);

        if($numero_voucher != ''){

            $facturaventaObj->setTicket($numero_voucher);

        }else{
            
            if($numero_documento != ''){

                $num_ticket = $numero_documento;
                $facturaventaObj->setTicket($num_ticket);

            }else{

                $ultimoIdFacturaVenta++;

                switch ($documento) {
                    case 'factura':
                        $num_ticket = ($localObj->getSerieFactura())?$localObj->getSerieFactura().'-'.$ultimoIdFacturaVenta:$ultimoIdFacturaVenta;
                        $facturaventaObj->setSerie($localObj->getSerieFactura());

                        break;
                    case 'boleta':
                        $num_ticket = ($localObj->getSerieBoleta())?$localObj->getSerieBoleta().'-'.$ultimoIdFacturaVenta:$ultimoIdFacturaVenta;
                        $facturaventaObj->setSerie($localObj->getSerieBoleta());                        

                        break;
                    case 'guia':
                        $num_ticket = ($localObj->getPrefijoVoucher())?$localObj->getPrefijoVoucher().'-'.$ultimoIdFacturaVenta:$ultimoIdFacturaVenta;
                        $facturaventaObj->setSerie($localObj->getPrefijoVoucher());

                        break;                            
                    default:
                        $num_ticket = ($localObj->getPrefijoVoucher())?$localObj->getPrefijoVoucher().'-'.$ultimoIdFacturaVenta:$ultimoIdFacturaVenta;
                        $facturaventaObj->setSerie($localObj->getPrefijoVoucher());

                        break;
                }

                $facturaventaObj->setNumero($ultimoIdFacturaVenta);
                $facturaventaObj->setTicket($num_ticket);
            }

        }
        
        $facturaventaObj->setDocumento($documento);
        $facturaventaObj->setIncluirIgv(true);
        $em->persist($facturaventaObj);

        // switch ($documento) {
        //     case 'factura':
        //         $codigo_tipo_sunat = '01';
        //         break;
        //     case 'boleta':
        //         $codigo_tipo_sunat = '03';
        //         break;
        //     case 'guia':
        //         $codigo_tipo_sunat = '12';
        //         break;                            
        //     default:
        //         $codigo_tipo_sunat = '12';
        //         break;
        // }

        //Actualizamos entidad proforma si existe una factura relacionada
        // if($formCliente['numero_proforma'] != ''){

        //     $proforma = $em->getRepository('AppBundle:Proforma')->findOneBy(array('numero'=>$formCliente['numero_proforma']));

        //     if(!$proforma){
        //         $this->addFlash("danger"," El número de proforma ingresado no existe. Ingrese el número correcto por favor.");
        //         return $this->redirectToRoute('detalleventa_puntoventa');     
        //     }

        //     $proforma->setFacturaVenta($facturaventaObj);
        //     $em->persist($proforma);

        // }

        //Guardamos el objeto Transferencia
        $transferencia  = new Transferencia();
        $transferencia->setLocalInicio($localObj);
        $transferencia->setLocalFin(null);
        $transferencia->setFecha($fecha);
        
        $transferencia->setEmpresa($empresaObj);
        //$transferencia->setDocumento($documento);
        $usuario    = $em->getRepository('AppBundle:Usuario')->find($session->get('usuario'));
        $transferencia->setUsuario($usuario);

        if($numero_voucher != ''){
            $transferencia->setNumeroDocumento($numero_voucher);

        }else{
            $transferencia->setNumeroDocumento($num_ticket);
        }
       
        $motivoTraslado  = $em->getRepository('AppBundle:MotivoTraslado')->findOneBy(array('codigo'=>'01'));
        $transferencia->setMotivoTraslado($motivoTraslado);
        $transferencia->setEstado(true);
        $transferencia->setFacturaVenta($facturaventaObj);
        $em->persist($transferencia);


        $productosXLocal  = $em->getRepository('AppBundle:ProductoXLocal')->findBy(array('local'=>$local));

        //dump($request->request);
        $pago_total = 0;
        foreach($request->request->get('productoid') as $pr=>$prodid){

            $producto_id = (int)$prodid;

            
            foreach($productosXLocal as $productoXLocal){

                if($producto_id == $productoXLocal->getId()){


                    $precio         = $request->request->get('precio_'.$producto_id);
                    $cantidad       = (double)$request->request->get('cantidad_'.$producto_id);
                    $descripcion    = $request->request->get('productodescripcion_'.$producto_id);

                    $subtotal = $cantidad*$precio;

                    //Guardamos el detalle de Venta
                    $detalleVentaObj = new DetalleVenta();
                    $detalleVentaObj->setVenta($ventaObj);
                    $detalleVentaObj->setProductoXLocal($productoXLocal);
                    $detalleVentaObj->setCantidad($cantidad);
                    $detalleVentaObj->setPrecio($precio);
                    $detalleVentaObj->setSubtotal($subtotal);
                    $detalleVentaObj->setDescripcion($descripcion);

                    $tipoImpuesto = $em->getRepository('AppBundle:TipoImpuesto')->find(1);
                    $detalleVentaObj->setTipoImpuesto($tipoImpuesto);

                    $precio_costo = $this->container->get('AppBundle\Util\Util')->obtenerPrecioCosto($productoXLocal->getId(),$cantidad);
                    $precio_costo = ($precio_costo) ? $precio_costo : $productoXLocal->getProducto()->getPrecioCompra() ;
                    $detalleVentaObj->setPrecioCosto($precio_costo);

                    $em->persist($detalleVentaObj);

                    //Guardamos el detalle de la transferencia
                    $transferenciaXProducto = new TransferenciaXProducto();
                    $transferenciaXProducto->setProductoXLocal($productoXLocal);
                    $transferenciaXProducto->setCantidad($cantidad);
                    $transferenciaXProducto->setTransferencia($transferencia);
                    $transferenciaXProducto->setPrecio($precio);
                    $em->persist($transferenciaXProducto);


                    $pago_total += $subtotal;

                    if($formapago->getCodigo() == '4'){

                        $this->container->get('AppBundle\Util\Util')->aumentarAlmacen($productoXLocal->getId(),$cantidad);
                        //$this->container->get('AppBundle\Util\Util')->registrarSunatF121($facturaventaObj,$productoXLocal,'07','05',$cantidad);

                    }elseif($formapago->getCodigo() == '5' || $condicion == true ){

                    }else{

                        $this->container->get('AppBundle\Util\Util')->disminuirAlmacen($productoXLocal->getId(),$cantidad);                       
                        //$this->container->get('AppBundle\Util\Util')->registrarSunatF121($facturaventaObj,$productoXLocal,$codigo_tipo_sunat,'01',$cantidad);
                    }
                    

                    //Guardar informacion en log_modificacion si el precio del producto vendido ha sido editado.
                    $precio_unitario = $productoXLocal->getProducto()->getPrecioUnitario();

                    if($precio_unitario != $precio){

                        $this->container->get('AppBundle\Util\Util')->registrarLog($productoXLocal,$precio,$precio_unitario,'precio',$facturaventaObj);
                    }


                }
            
            }

            
        }


        // $pago_total = 0;
        // foreach($productosXLocal as $productoXLocal){

        //     if(null !== $request->request->get('productoid_'.$productoXLocal->getId()) ){

        //         $producto_id = (int)$request->request->get('productoid_'.$productoXLocal->getId());

        //         if($producto_id){                    

        //             $precio         = $request->request->get('precio_'.$producto_id);
        //             $cantidad       = (double)$request->request->get('cantidad_'.$producto_id);
        //             $descripcion    = $request->request->get('productodescripcion_'.$producto_id);

        //             $subtotal = $cantidad*$precio;

        //             //Guardamos el detalle de Venta
        //             $detalleVentaObj = new DetalleVenta();
        //             $detalleVentaObj->setVenta($ventaObj);
        //             $detalleVentaObj->setProductoXLocal($productoXLocal);
        //             $detalleVentaObj->setCantidad($cantidad);
        //             $detalleVentaObj->setPrecio($precio);
        //             $detalleVentaObj->setSubtotal($subtotal);
        //             $detalleVentaObj->setDescripcion($descripcion);
        //             $em->persist($detalleVentaObj);

        //             //Guardamos el detalle de la transferencia
        //             $transferenciaXProducto = new TransferenciaXProducto();
        //             $transferenciaXProducto->setProductoXLocal($productoXLocal);
        //             $transferenciaXProducto->setCantidad($cantidad);
        //             $transferenciaXProducto->setTransferencia($transferencia);
        //             $em->persist($transferenciaXProducto);


        //             $pago_total += $subtotal;

        //             if($formapago->getCodigo() == '4'){

        //                 $this->container->get('AppBundle\Util\Util')->aumentarAlmacen($productoXLocal->getId(),$cantidad);
        //                 //$this->container->get('AppBundle\Util\Util')->registrarSunatF121($facturaventaObj,$productoXLocal,'07','05',$cantidad);

        //             }elseif($formapago->getCodigo() == '5' || $condicion == true ){

        //             }else{

        //                 $this->container->get('AppBundle\Util\Util')->disminuirAlmacen($productoXLocal->getId(),$cantidad);                       
        //                 //$this->container->get('AppBundle\Util\Util')->registrarSunatF121($facturaventaObj,$productoXLocal,$codigo_tipo_sunat,'01',$cantidad);
        //             }
                    

        //             //Guardar informacion en log_modificacion si el precio del producto vendido ha sido editado.
        //             $precio_unitario = $productoXLocal->getProducto()->getPrecioUnitario();

        //             if($precio_unitario != $precio){

        //                 $this->container->get('AppBundle\Util\Util')->registrarLog($productoXLocal,$precio,$precio_unitario,'precio',$facturaventaObj);
        //             }

        //         }

        //     }
        
        // }

        //Obtenermos el id de la caja apertura
        $caja_apertura_id = $session->get('caja_apertura');

        if(!$session->get('caja_apertura')){

            $dql = "SELECT ca FROM AppBundle:CajaApertura ca ";
            $dql .= " JOIN ca.caja c";
            $dql .= " WHERE  c.id =:caja_id  AND ca.estado = 1 ";

            $query = $em->createQuery($dql);

            $query->setParameter('caja_id',$session->get('caja'));
     
            $cajaApertura = $query->getSingleResult();

            $caja_apertura_id = $cajaApertura->getId();

            $session->set('caja_apertura',$caja_apertura_id);

        }


        //Guardamos el objeto VentaFormaPago
        $ventaformapagoObj = new VentaFormaPago();        
        $ventaformapagoObj->setFormaPago($formapago);
        $ventaformapagoObj->setVenta($ventaObj);
        $ventaformapagoObj->setCantidad($pago_total);
        $ventaformapagoObj->setNumeroDias($numero_dias);

        $monedaObj    = $em->getRepository('AppBundle:Moneda')->find($moneda);
        $ventaformapagoObj->setMoneda($monedaObj);

        if($formapago->getCodigo() == '5')
        {
            $ventaformapagoObj->setMontoACuenta($monto_acuenta);
            $ventaformapagoObj->setCondicion('pendiente');

            $this->container->get('AppBundle\Util\Util')->registrarCajaAperturaDetalle($caja_apertura_id,$monto_acuenta,'venta',$facturaventaObj->getId());

        }
        elseif($formapago->getCodigo() == '2')
        {

            $ventaformapagoObj->setCondicion('pendiente');
        }


        //Guardamos el valor del tipo de cambio solo si selecciona la moneda dólar
        if($monedaObj->getId() == 2){

            $tipoCambioObj       = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$fecha));
            $valor_tipo_cambio   = ($tipoCambioObj)? $tipoCambioObj->getVenta() : '';

            $ventaformapagoObj->setValorTipoCambio($valor_tipo_cambio);

        }

        $ventaformapagoObj->setMontoEntregado($monto_entregado);

        $em->persist($ventaformapagoObj);

        //Registrar en el detalle de caja.
        if($formapago->getCodigo() != '5')
        {
            $this->container->get('AppBundle\Util\Util')->registrarCajaAperturaDetalle($caja_apertura_id,$pago_total,'venta',$facturaventaObj->getId());

        }          
        

        try {

            $em->flush();

            // if($enviarFactura){
            //     $this->enviarFactura($cliente->getEmail(),$facturaVenta->getEnlacepdf());
            // }

        } catch (\Exception $e) {

            $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
            return $this->redirectToRoute('detalleventa_puntoventa');                
        }

        return $this->redirectToRoute('facturaventa_show', array('id' => $facturaventaObj->getId()));
        
    }


    /**
     * Muestra el detalle de la venta
     *
     * @Route("/{id}/entrega", name="detalleventa_entrega")
     * @Method({"GET", "POST"})
     */
    public function entregaAction(Request $request,FacturaVenta $facturaVenta)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $em = $this->getDoctrine()->getManager();

        $dql = "SELECT dve FROM AppBundle:DetalleVentaEntrega dve ";
        $dql .= " JOIN dve.detalleVenta dv";
        $dql .= " JOIN dv.venta v";
        $dql .= " WHERE  v.id =:id  ";
        $dql .= " ORDER BY dve.identificador ";

        $query = $em->createQuery($dql);

        $query->setParameter('id',$facturaVenta->getVenta()->getId());         
 
        $entregas =  $query->getResult();       


        return $this->render('detalleventa/entrega.html.twig', array(
            'facturaVenta' => $facturaVenta,
            'titulo'       => 'Entrega de productos',
            'entregas'    => $entregas
        ));
    }

    /**
     * Muestra el detalle de las entregas cuando ya estan completadas
     *
     * @Route("/{id}/entregacompleta", name="detalleventa_entrega_completa")
     * @Method({"GET", "POST"})
     */
    public function entregaCompletaAction(Request $request,FacturaVenta $facturaVenta)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $em = $this->getDoctrine()->getManager();

        $dql = "SELECT dve FROM AppBundle:DetalleVentaEntrega dve ";
        $dql .= " JOIN dve.detalleVenta dv";
        $dql .= " JOIN dv.venta v";
        $dql .= " WHERE  v.id =:id  ";
        $dql .= " ORDER BY dve.identificador ";

        $query = $em->createQuery($dql);

        $query->setParameter('id',$facturaVenta->getVenta()->getId());         
 
        $entregas =  $query->getResult();       


        return $this->render('detalleventa/entregacompleta.html.twig', array(
            'facturaVenta' => $facturaVenta,
            'titulo'       => 'Entrega de productos',
            'entregas'    => $entregas
        ));
    }

    /**
     * Procedemos al pago final de una factura al credito
     *
     * @Route("/{id}/finalizarpago", name="detalleventa_finalizar_pago")
     * @Method({"GET", "POST"})
     */
    public function finalizarPagoAction(Request $request,FacturaVenta $facturaVenta)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $fecha_pago = new \DateTime();

        $em = $this->getDoctrine()->getManager();

        $facturaVenta->getVenta()->getVentaFormaPago()[0]->setCondicion(null);
        $facturaVenta->getVenta()->getVentaFormaPago()[0]->setFechaPagoCredito($fecha_pago);
        $em->persist($facturaVenta);

        try {

            $em->flush();
            $this->addFlash("success","El pago fue realizado correctamente.");

        } catch (\Exception $e) {

            $this->addFlash("danger", $e." Ocurrió un error inesperado, el pago no se realizó.");
            
        }

        return $this->redirectToRoute('detalleventa_lista');       

    }


    /**
     * Muestra el detalle de la venta
     *
     * @Route("/entrega/procesar", name="detalleventa_entrega_procesar")
     * @Method({"GET", "POST"})
     */
    public function procesarEntregaAction(Request $request)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $usuario        = $session->get('usuario');
        $host           = $request->getHost();

        $fecha = new \DateTime();
        $timestamp = $fecha->getTimestamp();

        $em = $this->getDoctrine()->getManager();

        $localObj = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        $factura_id = $request->request->get('factura_id');

        $factura = $em->getRepository('AppBundle:FacturaVenta')->find($factura_id);

        switch ($factura->getDocumento()) {
            case 'factura':
                $codigo_tipo_sunat = '01';
                break;
            case 'boleta':
                $codigo_tipo_sunat = '03';
                break;
            case 'guia':
                $codigo_tipo_sunat = '12';
                break;                            
            default:
                $codigo_tipo_sunat = '12';
                break;
        }

        $saldoTotal = 0;
        // $detalleIdArray = array();
        $i = 0;
        foreach($factura->getVenta()->getDetalleVenta() as $detalleVenta){

            $cantidadEntregada = $request->request->get('cantidad_'.$detalleVenta->getId());
            $cantidadDetalle = $detalleVenta->getCantidad();
            $saldoParcial = $cantidadDetalle - $cantidadEntregada;

            if($request->request->get('select_'.$detalleVenta->getId()) !== null ){

                $this->container->get('AppBundle\Util\Util')->disminuirAlmacen($detalleVenta->getProductoXLocal()->getId(),$cantidadEntregada);
                //$this->container->get('AppBundle\Util\Util')->registrarSunatF121($factura,$detalleVenta->getProductoXLocal(),$codigo_tipo_sunat,'01',$cantidadEntregada);

                // $detalleIdArray[$i]['id']       = $detalleVenta->getId();
                // $detalleIdArray[$i]['cantidad'] = $cantidadEntregada;

                $cantidadDetalleEntregada = $cantidadEntregada;
                $cantidadEntregada = ($detalleVenta->getCantidadEntregada())?$detalleVenta->getCantidadEntregada() + $cantidadEntregada:$cantidadEntregada;

                $detalleVenta->setCantidadEntregada($cantidadEntregada);
                $em->persist($detalleVenta);                
            
                //Guardamos la informacion del producto entregado

                $detalleVentaEntrega = new DetalleVentaEntrega();
                $detalleVentaEntrega->setDetalleVenta($detalleVenta);
                $detalleVentaEntrega->setCantidad($cantidadDetalleEntregada);
                $detalleVentaEntrega->setFecha($fecha);
                $usuarioObj = $em->getRepository('AppBundle:Usuario')->find($usuario);
                $detalleVentaEntrega->setUsuario($usuarioObj);
                $detalleVentaEntrega->setIdentificador($timestamp);
                $em->persist($detalleVentaEntrega);

            }
            

            $saldoTotal += $cantidadDetalle - $cantidadEntregada;            
            $i++;
        }

        if($saldoTotal == 0){

            // $factura->getVenta()->setCondicion(false);
            // $em->persist($factura);
        }

        $detalleVentaEntregaDatosEnvio = new DetalleVentaEntregaDatosEnvio();
        $detalleVentaEntregaDatosEnvio->setIdentificador($timestamp);
        $detalleVentaEntregaDatosEnvio->setCliente('');
        $detalleVentaEntregaDatosEnvio->setDireccion('');
        $em->persist($detalleVentaEntregaDatosEnvio);

        try {

            $em->flush();

        } catch (\Exception $e) {

            $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
            return $this->redirectToRoute('detalleventa_entrega');                
        }


        return $this->redirectToRoute('detalleventa_entrega_pdf', array('id' => $factura->getId(),'identificador'=>$timestamp));


    }

    /**
     * Se muestra el PDF
     *
     * @Route("/{id}/entrega/{identificador}/pdf", name="detalleventa_entrega_pdf")
     * @Method({"GET", "POST"})
     */
    public function entregaPDFAction(Request $request,FacturaVenta $facturaVenta,$identificador)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $usuario        = $session->get('usuario');
        $host           = $request->getHost();

        $em = $this->getDoctrine()->getManager();

        //$detalleVentaEntrega = $em->getRepository('AppBundle:DetalleVentaEntrega')->findBy(array('identificador'=>$identificador));

        $localObj = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        $dql = "SELECT dve FROM AppBundle:DetalleVentaEntrega dve ";
        $dql .= " JOIN dve.detalleVenta dv";
        $dql .= " JOIN dv.venta v";
        $dql .= " WHERE  v.id =:id  ";
        $dql .= " AND dve.identificador =:identificador  ";

        $query = $em->createQuery($dql);

        $query->setParameter('id',$facturaVenta->getVenta()->getId());
        $query->setParameter('identificador',$identificador);        

        $detalleVentaEntrega =  $query->getResult();    


        $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
        $dql .= " JOIN cxd.documento d";
        $dql .= " JOIN d.local l";
        $dql .= " WHERE  l.id =:local  AND d.codigo = '04' AND cxd.estado = 1 ";

        $query = $em->createQuery($dql);

        $query->setParameter('local',$local);         
 
        $componentesXDocumento =  $query->getResult();

        $html = $this->render('detalleventa/guiaEntrega.html.twig', array(
                'facturaVenta' => $facturaVenta,
                'detalleVentaEntrega' => $detalleVentaEntrega,
                'componentesXDocumento' => $componentesXDocumento,
                'localObj'  => $localObj,
                'host' => $host
            ))->getContent();

        $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 8,'margin-top' => 3,'margin-bottom' => 3));


        return new \Symfony\Component\HttpFoundation\Response(
                $pdf, 200, array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'inline; filename="guiaentrega_'.$identificador.'.pdf"',
                     
        ));

    }


    /**
     * genera PDF de detalle venta entrega para imprimir
     *
     * @Route("/{id}/imprimirentrega/{identificador}/parcial", name="detalleventa_imprimir_entregaparcial")
     * @Method("GET")
     */
    public function imprimirEntregaparcialAction(Request $request,FacturaVenta $facturaVenta,$identificador)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        $dql = "SELECT dve FROM AppBundle:DetalleVentaEntrega dve ";
        $dql .= " JOIN dve.detalleVenta dv";
        $dql .= " JOIN dv.venta v";
        $dql .= " WHERE  v.id =:id  ";
        $dql .= " AND dve.identificador =:identificador  ";

        $query = $em->createQuery($dql);

        $query->setParameter('id',$facturaVenta->getVenta()->getId());
        $query->setParameter('identificador',$identificador);        

        $detalleVentaEntrega =  $query->getResult();    


        $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
        $dql .= " JOIN cxd.documento d";
        $dql .= " JOIN d.local l";
        $dql .= " WHERE  l.id =:local  AND d.codigo = '04' AND cxd.estado = 1  ";

        $query = $em->createQuery($dql);
        $query->setParameter('local',$local);         
        $componentesXDocumento =  $query->getResult();

        $html = $this->render('detalleventa/imprimirEntregaparcial.html.twig', array(
                'facturaVenta'  => $facturaVenta,
                'detalleVentaEntrega'  => $detalleVentaEntrega,
                'componentesXDocumento'  => $componentesXDocumento,
                'localObj'      => $localObj,
                'host'  => $request->getHost(),
                'identificador'  => $identificador,
            ))->getContent();

        $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

        return new \Symfony\Component\HttpFoundation\Response(
                $pdf, 200, array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'inline; filename="guiaentrega_'.$identificador.'.pdf"',                     
        ));
    }


    /**
     * Guia de entrega.
     *
     * @Route("/{id}/guia/entrega", name="detalleventa_guia_entrega")
     * @Method({"POST","GET"})
     */
    public function guiaEntregaAction(Request $request,FacturaVenta $facturaVenta)
    {
        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $host           = $request->getHost();

        $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
        $dql .= " JOIN cxd.documento d";
        $dql .= " JOIN d.local l";
        $dql .= " WHERE  l.id =:local  AND d.codigo = '03' AND cxd.estado = 1  ";

        $query = $em->createQuery($dql);

        $query->setParameter('local',$local);         
 
        $componentesXDocumento =  $query->getResult();

        $html = $this->render('detalleventa/guiaEntrega.html.twig', array(
                'facturaVenta' => $facturaVenta,
                'componentesXDocumento' => $componentesXDocumento,
                'host' => $host
            ))->getContent();

        $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));


        return new \Symfony\Component\HttpFoundation\Response(
                $pdf, 200, array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'inline; filename="guiaentrega.pdf"',
                     
        ));

    }



    /**
     * Displays a form to edit an existing detalleVentum entity.
     *
     * @Route("/{id}/edit", name="detalleventa_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, DetalleVenta $detalleVentum)
    {
        $deleteForm = $this->createDeleteForm($detalleVentum);
        $editForm = $this->createForm('AppBundle\Form\DetalleVentaType', $detalleVentum);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('detalleventa_edit', array('id' => $detalleVentum->getId()));
        }

        return $this->render('detalleventa/edit.html.twig', array(
            'detalleVentum' => $detalleVentum,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Anula una venta
     *
     * @Route("/{id}/{msj}/anular/{tipo}", name="detalleventa_anular")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function anularAction(Request $request, FacturaVenta $facturaVenta,$msj,$tipo)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $em = $this->getDoctrine()->getManager();

        $localObj = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        if( ($facturaVenta->getDocumento() == 'boleta' || $facturaVenta->getDocumento() == 'factura')  && $localObj->getFacturacionElectronica() == true )
        {

            $data_json = $this->generarArchivoJson($facturaVenta,$localObj,$msj);
            $respuesta = $this->enviarArchivoJson($data_json,$localObj);
            $leer_respuesta = json_decode($respuesta, true);

            if($tipo == '2')
            {


                $sunat_ticket_numero  = ($leer_respuesta['sunat_ticket_numero']) ? $leer_respuesta['sunat_ticket_numero'] : '';
                
                if(!isset($leer_respuesta['errors']))
                {
                    //$enlacepdf = $leer_respuesta['enlace_del_pdf'];
                    $enlacepdf = $leer_respuesta['enlace'].'.pdf';              

                    $data_json = $this->container->get('AppBundle\Util\Util')->generarArchivoJsonNotaCredito($facturaVenta,$local,$msj);
                    $respuesta = $this->container->get('AppBundle\Util\Util')->enviarArchivoJsonNotaCredito($data_json,$localObj);
                    $leer_respuesta = json_decode($respuesta, true);


                    if(isset($leer_respuesta['errors']))
                    {

                        $this->addFlash("danger", " Ocurrió un error inesperado, el registro no fue anulado.".$leer_respuesta['errors']);
                        return $this->redirectToRoute('detalleventa_lista');    

                    }

                    $enlacepdf = $leer_respuesta['enlace'].'.pdf';
                    
                    $aceptada_por_sunat   = $leer_respuesta['aceptada_por_sunat'];
                    $sunat_description    = $leer_respuesta['sunat_description'];

                    $facturaVenta->getVenta()->setEstado(false);
                    $facturaVenta->getVenta()->setMotivoAnulacion($msj);
                    $usuario = $em->getRepository('AppBundle:Usuario')->find($session->get('usuario'));

                    $facturaVenta->getVenta()->setUsuarioAnulacion($usuario);

                    $facturaVenta->getVenta()->setTicketAnulacion($sunat_ticket_numero);

                    $facturaVenta->setEnlacepdf($enlacepdf);
                    $em->persist($facturaVenta);


                    foreach($facturaVenta->getVenta()->getDetalleVenta() as $detalleVenta)
                    {
                        if($detalleVenta->getProductoXLocal())
                        {
                            $this->container->get('AppBundle\Util\Util')->aumentarAlmacen($detalleVenta->getProductoXLocal()->getId(),$detalleVenta->getCantidad());
                        }
                        

                    }

                    /*Eliminamos la transferencia generada con la venta*/
                    $transferencia = $em->getRepository('AppBundle:Transferencia')->findOneBy(array('empresa'=>$empresa,'numeroDocumento'=>$facturaVenta->getTicket(),'estado'=>true));

                    if($transferencia){
                        $transferencia->setEstado(false);
                        $em->persist($transferencia);
                    }

                    
                    try {

                        $em->flush();

                        
                    } catch (\Exception $e) {

                        $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no fue anulado.");
                        return $this->redirectToRoute('detalleventa_lista');    
                    }

                }
                else
                {
                    $this->addFlash("danger", " Ocurrió un error inesperado, el registro no fue anulado.".$leer_respuesta['errors']);
                    return $this->redirectToRoute('detalleventa_lista');
                }

                return $this->redirectToRoute('detalleventa_imprimir_notacredito_anulacion',array('id'=>$facturaVenta->getId()));

            }
            elseif($tipo == '1')
            {

                $facturaVenta->getVenta()->setEstado(false);
                $facturaVenta->getVenta()->setMotivoAnulacion($msj);
                $usuario = $em->getRepository('AppBundle:Usuario')->find($session->get('usuario'));

                $facturaVenta->getVenta()->setUsuarioAnulacion($usuario);

                $numero_ticket_anulacion = $this->container->get('AppBundle\Util\Util')->generarNumeroAnulacion($local);
                $numero_ticket_anulacion++;
                $numero_ticket_anulacion = str_pad($numero_ticket_anulacion + 1, 7, '0', STR_PAD_LEFT);
                $facturaVenta->getVenta()->setTicketAnulacion($numero_ticket_anulacion);

                $em->persist($facturaVenta);


                /*Eliminamos la transferencia generada con la venta*/
                $transferencia = $em->getRepository('AppBundle:Transferencia')->findOneBy(array('empresa'=>$empresa,'numeroDocumento'=>$facturaVenta->getTicket(),'estado'=>true));

                if($transferencia){
                    $transferencia->setEstado(false);
                    $em->persist($transferencia);
                }


                //Consultamos nuevamente el documento anulado
                // $url   = $facturaVenta->getLocal()->getUrlFacturacion();
                // $token = $facturaVenta->getLocal()->getTokenFacturacion();

                // $data_json      = $this->container->get('AppBundle\Util\Util')->generarConsultaAnulacionArchivoJson($facturaVenta);
                // $respuesta      = $this->container->get('AppBundle\Util\Util')->enviarConsultaAnulacionArchivoJson($data_json,$url,$token);
                // $leer_respuesta = json_decode($respuesta, true);                

                // if (isset($leer_respuesta['errors']))
                // {
                //     $this->addFlash("danger", $leer_respuesta['errors']." Ocurrió un error inesperado, el registro no fue anulado.");
                //     return $this->redirectToRoute('detalleventa_lista');
                // }

                foreach($facturaVenta->getVenta()->getDetalleVenta() as $detalleVenta)
                {
                    if($detalleVenta->getProductoXLocal())
                    {
                        $this->container->get('AppBundle\Util\Util')->aumentarAlmacen($detalleVenta->getProductoXLocal()->getId(),$detalleVenta->getCantidad());
                    }
                    
                }

                try {

                    $em->flush();                  
                    
                } catch (\Exception $e) {

                    $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no fue anulado.");
                    return $this->redirectToRoute('detalleventa_lista');    
                }

                return $this->redirectToRoute('detalleventa_imprimir_ticket_anulacion',array('id'=>$facturaVenta->getId()));
            }


        }
        else
        {

            $facturaVenta->getVenta()->setEstado(false);
            $facturaVenta->getVenta()->setMotivoAnulacion($msj);
            $usuario = $em->getRepository('AppBundle:Usuario')->find($session->get('usuario'));

            $facturaVenta->getVenta()->setUsuarioAnulacion($usuario);

            $numero_ticket_anulacion = $this->container->get('AppBundle\Util\Util')->generarNumeroAnulacion($local);
            $numero_ticket_anulacion++;
            $numero_ticket_anulacion = str_pad($numero_ticket_anulacion + 1, 7, '0', STR_PAD_LEFT);
            $facturaVenta->getVenta()->setTicketAnulacion($numero_ticket_anulacion);

            $em->persist($facturaVenta);


            foreach($facturaVenta->getVenta()->getDetalleVenta() as $detalleVenta){

                $this->container->get('AppBundle\Util\Util')->aumentarAlmacen($detalleVenta->getProductoXLocal()->getId(),$detalleVenta->getCantidad());

            }


            /*Eliminamos la transferencia generada con la venta*/
            $transferencia = $em->getRepository('AppBundle:Transferencia')->findOneBy(array('empresa'=>$empresa,'numeroDocumento'=>$facturaVenta->getTicket(),'estado'=>true));

            if($transferencia){
                $transferencia->setEstado(false);
                $em->persist($transferencia);
            }

            try {

                $em->flush();               

                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no fue anulado.");
                return $this->redirectToRoute('detalleventa_lista');    
                    
            }

            if($tipo == '1'){

                return $this->redirectToRoute('detalleventa_imprimir_ticket_anulacion',array('id'=>$facturaVenta->getId()));

            }elseif($tipo == '2'){

                return $this->redirectToRoute('detalleventa_imprimir_notacredito_anulacion',array('id'=>$facturaVenta->getId()));

            }
            


        }


        return $this->redirectToRoute('detalleventa_lista');
    }


    /**
     * Muestra el PDF de impresion de un ticket para el proceso de anulación
     *
     * @Route("/{id}/imprimirticketanulacion", name="detalleventa_imprimir_ticket_anulacion")
     * @Method({"POST","GET"})
     */
    public function imprimirTicketAnulacionAction(Request $request,FacturaVenta $facturaVenta)
    {

        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $http           = $request->getSchemeAndHttpHost();
        $host           = $request->getHost();

        $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        
        $html =  $this->render('detalleventa/ticketanulacion.html.twig', array(
                'facturaVenta'  => $facturaVenta,
                'localObj'      => $localObj,
                'host'          => $host
        ))->getContent();


        $f = 'ticketanulacion_'.$facturaVenta->getTicket();
        $enlace_pdf = 'uploads/files/'.$empresa.'/'.$f . '.pdf';

        $this->get('knp_snappy.pdf')->generateFromHtml($html,$enlace_pdf,array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 5,'margin-bottom' => 5));

        $session->set('enlace_pdf',$http.'/'.$enlace_pdf );

        $this->addFlash("success", "El registro fue anulado exitosamente.");

        return $this->redirect($this->generateUrl('detalleventa_lista'));


        // if($facturaVenta->getEnlacepdf()){

        //     // //Obtener el archivo
        //     // $content = file_get_contents($facturaVenta->getEnlacepdf());
        //     // //Guardar el archivo
        //     // file_put_contents("uploads/files/ticketanulacion.pdf",$content);


        //     // return $this->render('detalleventa/ticketanulacionNf.html.twig', array(
        //     //         'facturaVenta'  => $facturaVenta,
        //     //         'localObj'      => $localObj,
        //     //         'titulo'        => '',
        //     //         'rutaPdf'       => 'uploads/files/ticketanulacion.pdf',
        //     // ));

        //     $session->set('enlace_pdf',$facturaVenta->getEnlacepdf());
        //     return $this->redirect($this->generateUrl('detalleventa_lista'));
            
        // }
        // else
        // {

        //     $html =  $this->render('detalleventa/ticketanulacion.html.twig', array(
        //             'facturaVenta'  => $facturaVenta,
        //             'localObj'      => $localObj,
        //     ))->getContent();



        //     $f = 'ticketanulacion_'.$facturaVenta->getTicket();
        //     $enlace_pdf = 'uploads/files/'.$empresa.'/'.$f . '.pdf';

        //     $this->get('knp_snappy.pdf')->generateFromHtml($html,$enlace_pdf,array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

        //     $session->set('enlace_pdf',$host.'/'.$enlace_pdf );
        //     return $this->redirect($this->generateUrl('detalleventa_lista'));



        //     // $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

        //     // return new \Symfony\Component\HttpFoundation\Response(
        //     //         $pdf, 200, array(
        //     //             'Content-Type'          => 'application/pdf',
        //     //             'Content-Disposition'   => 'inline; filename="ticketanulacion_'.$facturaVenta->getId().'.pdf"',
                         
        //     // ));

        // }

    }

    /**
     * Muestra el PDF de impresion de nota credito para el proceso de anulación
     *
     * @Route("/{id}/imprimirnotacreditoanulacion", name="detalleventa_imprimir_notacredito_anulacion")
     * @Method({"POST","GET"})
     */
    public function imprimirNotacreditoAnulacionAction(Request $request,FacturaVenta $facturaVenta)
    {

        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $host           = $request->getSchemeAndHttpHost();

        $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        if($facturaVenta->getEnlacepdf()){

            //Obtener el archivo
            //$content = file_get_contents($facturaVenta->getEnlacepdf());
            //Guardar el archivo
            //file_put_contents("uploads/files/notacredito.pdf",$content);


            // return $this->render('detalleventa/notacreditoanulacionNf.html.twig', array(
            //         'facturaVenta'  => $facturaVenta,
            //         'localObj'      => $localObj,
            //         'titulo'        => '',
            //         'rutaPdf'       => 'uploads/files/notacredito.pdf',
            // ));

            $session->set('enlace_pdf',$facturaVenta->getEnlacepdf());
            $this->addFlash("success", "El registro fue anulado exitosamente.");
            return $this->redirect($this->generateUrl('detalleventa_lista'));

        }
        else
        {

            $html = $this->render('detalleventa/notacreditoanulacion.html.twig', array(
                    'facturaVenta'  => $facturaVenta,
                    'localObj'      => $localObj,
                ))->getContent();


            $f = 'ticketanulacion_'.$facturaVenta->getTicket();
            $enlace_pdf = 'uploads/files/'.$empresa.'/'.$f . '.pdf';

            $this->get('knp_snappy.pdf')->generateFromHtml($html,$enlace_pdf,array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

            $session->set('enlace_pdf',$host.'/'.$enlace_pdf);
            $this->addFlash("success", "El registro fue anulado exitosamente.");
            return $this->redirect($this->generateUrl('detalleventa_lista'));


            // $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

            // return new \Symfony\Component\HttpFoundation\Response(
            //         $pdf, 200, array(
            //             'Content-Type'          => 'application/pdf',
            //             'Content-Disposition'   => 'inline; filename="notacreditoanulacion_'.$facturaVenta->getId().'.pdf"',
                         
            // ));

        }

    }


    private function generarArchivoJson($facturaVenta,$localObj,$msj='ERROR DEL SISTEMA')
    {
        $em = $this->getDoctrine()->getManager();


        $partesticket = explode("-", $facturaVenta->getTicket());
        $numero = $partesticket[1];


        switch ($facturaVenta->getDocumento()) {
            case 'factura':
                $serie = $localObj->getSerieFactura();
                $tipo_de_comprobante = 1;
                break;
            case 'boleta':
                $serie = $localObj->getSerieBoleta();
                $tipo_de_comprobante = 2;
                break;            
            default:
                $serie = '';
                $tipo_de_comprobante = '';
                break;
        }


        $data = array(
            "operacion"                         => "generar_anulacion",
            "tipo_de_comprobante"               => "".$tipo_de_comprobante."",
            "serie"                             => "".$serie."",
            "numero"                            => $numero,
            "motivo"                            => $msj,
            "codigo_unico"                      => ""             
        );
            

        $data_json = json_encode($data);

        return $data_json;
    }


    private function enviarArchivoJson($data_json,$localObj)
    {

        //Invocamos el servicio de NUBEFACT
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$localObj->getUrlFacturacion());
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Token token="'.$localObj->getTokenFacturacion().'"',
            'Content-Type: application/json',
            )
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta  = curl_exec($ch);
        curl_close($ch);

        return $respuesta;
    }


    /**
     * Procesar pago a cuenta
     *
     * @Route("/procesarpagoacuenta", name="detalleventa_procesarpagoacuenta")
     * @Method({"GET","POST"})
     * 
     */
    public function procesarPagoAcuentaAction(Request $request)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $em = $this->getDoctrine()->getManager();

        $ventaformapagoId = $request->request->get('ventaFormaPagoId');
        $facturaventaId   = $request->request->get('facturaVentaId');

        $ventaformapagoObj = $em->getRepository('AppBundle:VentaFormaPago')->find($ventaformapagoId);
        $facturaventaObj = $em->getRepository('AppBundle:FacturaVenta')->find($facturaventaId);
        $localObj  = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        $ventaformapagoObj->setMontoACuenta(null);
        $ventaformapagoObj->setCondicion(null);
        $em->persist($ventaformapagoObj);

        
        try {
            

            //Generamos doc en nubefact
            $leer_respuesta = array();
            if($localObj->getUrlFacturacion() != '' && $localObj->getTokenFacturacion() != '' && $localObj->getFacturacionElectronica() != false)
            {
                if( $facturaventaObj->getDocumento() == 'boleta' || $facturaventaObj->getDocumento() == 'factura' )
                {

                    $data_json = $this->container->get('AppBundle\Util\Util')->generarArchivoJson($facturaventaObj,$local);
                    $respuesta = $this->container->get('AppBundle\Util\Util')->enviarArchivoJson($data_json,$localObj);
                    $leer_respuesta = json_decode($respuesta, true);

                    if(isset($leer_respuesta['errors'])){

                        $nferror  = $em->getRepository('AppBundle:NubefactError')->findOneBy(array('codigo'=>$leer_respuesta['codigo']));
                        $msj_error = ($nferror) ? $nferror->getDescripcion().' '.$leer_respuesta['errors'] : 'Error no identificado';

                        $this->addFlash("danger", "El pago no fue procesado, hubo un error en el proceso de generación del documento. ".$msj_error);

                    }else{


                        if($leer_respuesta['aceptada_por_sunat'] != 'true'){

                            $enlace_de_pdf = $leer_respuesta['enlace'].'.pdf';

                            if($facturaventaObj->getDocumento() == 'factura')
                            {
                                $facturaventaObj->setEnviadoSunat(false);
                            }
                            else
                            {
                                $facturaventaObj->setEnviadoSunat(true);
                            }

                            
                            $facturaventaObj->setEnlacepdf($enlace_de_pdf);

                        }
                        else
                        {
                            $enlace_de_pdf = $leer_respuesta['enlace_del_pdf'];

                            $facturaventaObj->setEnviadoSunat(true);
                            $facturaventaObj->setEnlacepdf($enlace_de_pdf);

                        }


                        $em->persist($facturaventaObj);

                        foreach($facturaventaObj->getVenta()->getDetalleVenta() as  $detalle){

                            if($detalle->getProductoXLocal()){

                                $this->container->get('AppBundle\Util\Util')->disminuirAlmacen($detalle->getProductoXLocal()->getId(),$detalle->getCantidad());

                            }

                        }

                        try {

                            $em->flush();
                            
                            $this->addFlash("success", "El pago fue procesado exitosamente. Numero de documento : ".$facturaventaObj->getTicket());    

                        } catch (\Exception $e) {
                            
                            $this->addFlash("danger", $e." .Error.");    

                        }

                        
                    }

                }
                else
                {

                    foreach($facturaventaObj->getVenta()->getDetalleVenta() as  $detalle){

                        if($detalle->getProductoXLocal())
                        {
                            $this->container->get('AppBundle\Util\Util')->disminuirAlmacen($detalle->getProductoXLocal()->getId(),$detalle->getCantidad());

                        }


                    }

                    try {
                      
                        $em->flush();

                        $this->addFlash("success", "El pago fue procesado exitosamente. Numero de documento : ".$facturaventaObj->getTicket());

                    } catch (\Exception $e) {

                        $this->addFlash("danger", $e." .Error.");
                        
                    }



                }

            }
            else
            {
                foreach($facturaventaObj->getVenta()->getDetalleVenta() as  $detalle){

                    if($detalle->getProductoXLocal())
                    {
                        $this->container->get('AppBundle\Util\Util')->disminuirAlmacen($detalle->getProductoXLocal()->getId(),$detalle->getCantidad());                        
                    }


                }

                try {
                 
                    $em->flush();
                    $this->addFlash("success", "El pago fue procesado exitosamente. Numero de documento : ".$facturaventaObj->getTicket());

                } catch (\Exception $e) {

                    $this->addFlash("danger", $e." .Error.");
                    
                }



            }            

            
        } catch (\Exception $e) {

            $this->addFlash("danger", $e." Ocurrió un error inesperado, el proceso se ha truncado.");
            return $this->redirectToRoute('detalleventa_lista');    
        }

        return $this->redirectToRoute('detalleventa_lista');
    }

    /**
     * Detalle de una venta
     *
     * @Route("/{id}/detalle", name="detalleventa_detalle")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function detalleAction(Request $request, FacturaVenta $facturaVenta)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $em = $this->getDoctrine()->getManager();

        $facturaVenta->getVenta()->setEstado(false);
        $facturaVenta->getVenta()->setMotivoAnulacion($msj);
        $usuario = $em->getRepository('AppBundle:Usuario')->find($session->get('usuario'));

        $facturaVenta->getVenta()->setUsuarioAnulacion($usuario);
        $em->persist($facturaVenta);

        try {

            $em->flush();
            $this->addFlash("success", "El registro fue anulado exitosamente.");

            
        } catch (\Exception $e) {

            $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no fue anulado.");
            return $this->redirectToRoute('detalleventa_lista');    
        }

        return $this->redirectToRoute('detalleventa_lista');
    }


    /**
     * Muestra el formato de impresion de proforma.
     *
     * @Route("/show/proforma", name="detalleventa_show_proforma")
     * @Method({"POST","GET"})
     */
    public function proformaAction(Request $request)
    {

        $factura_id  = $request->query->get('factura_id');

        return $this->redirectToRoute('detalleventa_imprimir_proforma',array('id'=>$factura_id));

        // $hora = date('H:i');

        // $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);        

        // //$data     = json_decode($request->query->get('data'));
        // $cliente  = $request->query->get('cliente');
        // $empleado = $request->query->get('empleado');
        // $validez_oferta = $request->query->get('validezOferta');
        // $plazo_entrega = $request->query->get('plazoEntrega');
        // $empleado_cotiza = $request->query->get('empleadoCotiza');
        // $correo_empleado_cotiza = $request->query->get('correoEmpleadoCotiza');
        // $telefono_cliente = $request->query->get('telefonoCliente');
        // $incluir_igv = $request->query->get('incluirIgv');
        // $forma_pago = $request->query->get('formaPago');
        // $documento = $request->query->get('documento');
        // $fecha = ($request->query->get('fecha') == '') ? new \DateTime() : date_create_from_format('d/m/Y H:i', $request->query->get('fecha').' '.$hora);


        // $numero = $this->container->get('AppBundle\Util\Util')->generarNumeroProforma('factura_venta',$local);
        // $numero = str_pad($numero + 1, 7, '0', STR_PAD_LEFT);
        // $numero = $localObj->getCodigo().'-'.$numero;

        // //Guardamos el objeto Venta
        // $ventaObj = new Venta();
        // $ventaObj->setEmpleado(null);
        // $ventaObj->setFecha($fecha);
        // $ventaObj->setEstado(true);
        // $ventaObj->setCondicion(false);
        // $empleadoObj = $em->getRepository('AppBundle:Empleado')->find($empleado);
        // $ventaObj->setEmpleado($empleadoObj);
        // $em->persist($ventaObj);


        // //Guardamos el objeto FacturaVenta
        // $facturaventaObj = new FacturaVenta();

        // if($empleado_cotiza == ''){
        //     $empleado_cotiza = strtoupper($empleadoObj->getNombres().' '.$empleadoObj->getApellidoPaterno().' '.$empleadoObj->getApellidoMaterno());
        //     $correo_empleado_cotiza = $empleadoObj->getEmail();
        // }

        // $clienteObj     = $em->getRepository('AppBundle:Cliente')->find($cliente);

        // if($telefono_cliente == ''){
        //     $telefono_cliente = ($clienteObj->getTelefono())? $clienteObj->getTelefono(): '';
        // }
        
        // $facturaventaObj->setCliente($clienteObj);
        // $facturaventaObj->setVenta($ventaObj);
        // $facturaventaObj->setFecha($fecha);
        // $facturaventaObj->setNumeroGuiaremision(null);
        // $facturaventaObj->setLocal($localObj);
        // $cajaObj = ($caja) ? $em->getRepository('AppBundle:Caja')->find($caja) : null;
        // $facturaventaObj->setCaja($cajaObj);

        // $tipoVenta = $em->getRepository('AppBundle:FacturaVentaTipo')->find(1);
        // $facturaventaObj->setTipo($tipoVenta);
        // $facturaventaObj->setNumeroProforma($numero);
        // $facturaventaObj->setFacturaEnviada(false);
        // $facturaventaObj->setDetraccion(false);
        // $facturaventaObj->setDocumento($documento);

        // $facturaventaObj->setPlazoEntrega($plazo_entrega);
        // $facturaventaObj->setValidezOferta($validez_oferta);
        // $facturaventaObj->setEmpleadoCotiza($empleado_cotiza);
        // $facturaventaObj->setCorreoEmpleadoCotiza($correo_empleado_cotiza);
        // $facturaventaObj->setTelefonoCliente($telefono_cliente);

        // $incluir_igv == 'SI';
        // if($incluir_igv == 'NO'){
        //     $facturaventaObj->setIncluirIgv(false);
        // }else{
        //     $facturaventaObj->setIncluirIgv(true);
        // }

        // $em->persist($facturaventaObj);

        // $pago_total = 0;
        // foreach($data as $i=>$prod){

        //     $subtotal = $prod->cantidad * $prod->punitario;

        //     //Guardamos el detalle de Venta
        //     $detalleVentaObj = new DetalleVenta();
        //     $detalleVentaObj->setVenta($ventaObj);
        //     $productoXLocal  = $em->getRepository('AppBundle:ProductoXLocal')->find($prod->productoid);
        //     $detalleVentaObj->setProductoXLocal($productoXLocal);
        //     $detalleVentaObj->setCantidad($prod->cantidad);
        //     $detalleVentaObj->setPrecio($prod->punitario);
        //     $detalleVentaObj->setSubtotal($subtotal);
        //     $detalleVentaObj->setDescripcion($prod->descripcion);
        //     $em->persist($detalleVentaObj);

        //     $pago_total += $subtotal;

        // }

        // //Guardamos el objeto VentaFormaPago
        // $ventaformapagoObj = new VentaFormaPago();

        // $formaPagoObj = null;
        // if($forma_pago != ''){
        //     $formaPagoObj = $em->getRepository('AppBundle:FormaPago')->find($forma_pago);
        // }
                
        // $ventaformapagoObj->setFormaPago($formaPagoObj);
        // $ventaformapagoObj->setVenta($ventaObj);
        // $ventaformapagoObj->setCantidad($pago_total);
        // $ventaformapagoObj->setNumeroDias(null);

        // $em->persist($ventaformapagoObj);        


        // try {

        //     $em->flush();
            
        // } catch (\Exception $e) {

        //     // dump($e);
        //     // die();
        //     return $e;
        // }

        // return $this->redirectToRoute('detalleventa_imprimir_proforma',array('id'=>$facturaventaObj->getId()));

    }

    /**
     * Muestra el PDF de impresion de la proforma
     *
     * @Route("/{id}/imprimirproforma", name="detalleventa_imprimir_proforma")
     * @Method({"POST","GET"})
     */
    public function imprimirProformaAction(Request $request,FacturaVenta $facturaVenta)
    {

        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);
        $empresaObj     = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $base_dir = $this->get('kernel')->getRootDir() . '/../web' . $request->getBasePath();


        $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
        $dql .= " JOIN cxd.documento d";
        $dql .= " JOIN d.local l";
        $dql .= " WHERE  l.id =:local  AND d.codigo = '06' AND cxd.estado = 1  ";

        $query = $em->createQuery($dql);
        $query->setParameter('local',$local);         
        $componentesXDocumento =  $query->getResult();

        $cuentasBancarias = $em->getRepository('AppBundle:CuentaBanco')->findBy(array('estado'=>true,'empresa'=>$empresa),array('banco'=>'ASC'));

        if($empresaObj->getProformaOrientacion() == 'Landscape'){

            $html = $this->render('detalleventa/proforma.html.twig', array(
                    'facturaVenta'  => $facturaVenta,
                    'componentesXDocumento'  => $componentesXDocumento,
                    'localObj'      => $localObj,
                    'host'  => $request->getHost(),
                    'cuentasBancarias' => $cuentasBancarias
                ))->getContent();

            $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> $empresaObj->getProformaFormato(),'orientation' => $empresaObj->getProformaOrientacion(),'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

            return new \Symfony\Component\HttpFoundation\Response(
                    $pdf, 200, array(
                        'Content-Type'          => 'application/pdf',
                        'Content-Disposition'   => 'inline; filename="cotizacion_'.$facturaVenta->getNumeroProforma().'.pdf"',
                         
            ));


        }
        else{

            $html = $this->render('detalleventa/proforma2.html.twig', array(
                    'facturaVenta'  => $facturaVenta,
                    'componentesXDocumento'  => $componentesXDocumento,
                    'localObj'      => $localObj,
                    'host'  => $request->getHost(),
                    'base_dir' => $base_dir,
                    'cuentasBancarias' => $cuentasBancarias
                ))->getContent();

            $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> $empresaObj->getProformaFormato(),'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

            $f = 'proforma_'.$facturaVenta->getNumeroProforma();

            return new \Symfony\Component\HttpFoundation\Response(
                    $pdf, 200, array(
                        'Content-Type'          => 'application/pdf',
                        'Content-Disposition'   => 'inline; filename="'.$f.'.pdf"',                     
            ));


        }

    }


    /**
     * Muestra el formato de impresion.
     *
     * @Route("/{id}/imprimir", name="detalleventa_imprimir")
     * @Method({"POST","GET"})
     */
    public function imprimirAction(Request $request,FacturaVenta $facturaVenta)
    {
        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $host           = $request->getHost();

        $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        if($facturaVenta->getLocal()->getFacturacionElectronica() && ($facturaVenta->getDocumento() == 'boleta' ||  $facturaVenta->getDocumento() == 'factura'))
        {
            if(!$facturaVenta->getEnlacepdf())
            {
                $ruta_imprimir_con_reenvio = $this->container->get('AppBundle\Util\Util')->imprimirConReenvio($facturaVenta->getId());

                if($ruta_imprimir_con_reenvio != null && $ruta_imprimir_con_reenvio != '.pdf')
                {
                    $pdf = file_get_contents($ruta_imprimir_con_reenvio);

                    return new Response($pdf, 200, [
                                'Content-Type' => 'application/pdf',
                                'Content-Disposition' => 'inline; filename="file.pdf"'
                    ]);                    
                }
                else
                {

                    $data_json = $this->container->get('AppBundle\Util\Util')->generarArchivoJson($facturaVenta,$local);
                    $respuesta = $this->container->get('AppBundle\Util\Util')->enviarArchivoJson($data_json,$localObj);
                    $leer_respuesta = json_decode($respuesta, true);

                    if(isset($leer_respuesta['errors']))
                    {
                        $this->addFlash("danger",$leer_respuesta['errors']);
                    }
                    else
                    {
                        $enlace_del_pdf = ($leer_respuesta['enlace']) ? $leer_respuesta['enlace'].'.pdf' : $leer_respuesta['enlace_del_pdf'];

                        $pdf = file_get_contents($enlace_del_pdf);

                        return new Response($pdf, 200, [
                                    'Content-Type' => 'application/pdf',
                                    'Content-Disposition' => 'inline; filename="file.pdf"'
                        ]);  


                        // if($facturaVenta->getVenta()->getVentaFormaPago()[0]->getFormaPago()->getCodigo() == '5' && $facturaVenta->getVenta()->getVentaFormaPago()[0]->getCondicion() == 'pendiente')
                        // {


                        //     $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
                        //     $dql .= " JOIN cxd.documento d";
                        //     $dql .= " JOIN d.local l";
                        //     $dql .= " WHERE  l.id =:local  AND d.codigo = '07' AND cxd.estado = 1  ";

                        //     $query = $em->createQuery($dql);

                        //     $query->setParameter('local',$local);         
                     
                        //     $componentesXDocumento =  $query->getResult();

                        //     $html = $this->render('detalleventa/reciboingreso.html.twig', array(
                        //             'facturaVenta' => $facturaVenta,
                        //             'localObj'     => $localObj,
                        //             'componentesXDocumento' => $componentesXDocumento,
                        //             'host' => $host
                        //         ))->getContent();

                        //     $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

                        //     $f = 'reciboingreso_'.$facturaVenta->getTicket();

                        // }
                        // else
                        // {

                        //     if($facturaVenta->getDocumento() == 'boleta')
                        //     {

                        //         $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
                        //         $dql .= " JOIN cxd.documento d";
                        //         $dql .= " JOIN d.local l";
                        //         $dql .= " WHERE  l.id =:local  AND d.codigo = '03' AND cxd.estado = 1  ";

                        //         $query = $em->createQuery($dql);

                        //         $query->setParameter('local',$local);         
                         
                        //         $componentesXDocumento =  $query->getResult();

                        //         $html = $this->render('detalleventa/boleta.html.twig', array(
                        //                 'facturaVenta' => $facturaVenta,
                        //                 'localObj'     => $localObj,
                        //                 'componentesXDocumento' => $componentesXDocumento,
                        //                 'host' => $host
                        //             ))->getContent();

                        //         $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 5,'margin-bottom' => 5));

                        //         $f = 'boleta_'.$facturaVenta->getTicket();

                        //     }
                        //     elseif($facturaVenta->getDocumento() == 'factura')
                        //     {

                        //         $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
                        //         $dql .= " JOIN cxd.documento d";
                        //         $dql .= " JOIN d.local l";
                        //         $dql .= " WHERE  l.id =:local  AND d.codigo = '01' AND cxd.estado = 1  ";

                        //         $query = $em->createQuery($dql);

                        //         $query->setParameter('local',$local);         
                         
                        //         $componentesXDocumento =  $query->getResult();

                        //         $cuentasBancarias = $em->getRepository('AppBundle:CuentaBanco')->findBy(array('estado'=>true,'empresa'=>$empresa),array('banco'=>'ASC'));

                        //         $html = $this->render('detalleventa/factura.html.twig', array(
                        //                 'facturaVenta' => $facturaVenta,
                        //                 'localObj'     => $localObj,
                        //                 'cuentasBancarias' => $cuentasBancarias,
                        //                 'componentesXDocumento' => $componentesXDocumento,
                        //                 'host' => $host
                        //             ))->getContent();

                        //         $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 5,'margin-bottom' => 5));

                        //         $f = 'factura_'.$facturaVenta->getTicket();                

                        //     }
                        //     else
                        //     {

                        //         switch ($localObj->getNotaventaFormato()) {
                        //             case 'A4':

                        //                 $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
                        //                 $dql .= " JOIN cxd.documento d";
                        //                 $dql .= " JOIN d.local l";
                        //                 $dql .= " WHERE  l.id =:local  AND d.codigo = '03' AND cxd.estado = 1  ";

                        //                 $query = $em->createQuery($dql);

                        //                 $query->setParameter('local',$local);         
                                 
                        //                 $componentesXDocumento =  $query->getResult();

                        //                 $html = $this->render('detalleventa/ticketA4.html.twig', array(
                        //                         'facturaVenta' => $facturaVenta,
                        //                         'localObj'     => $localObj,
                        //                         'componentesXDocumento' => $componentesXDocumento,
                        //                         'host' => $host
                        //                     ))->getContent();


                        //                 $f = 'notaventa_'.$facturaVenta->getTicket();

                        //                 $pdf =  $this->get('knp_snappy.pdf')->getOutputFromHtml($html,array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 5,'margin-bottom' => 5));

                        //                 break;
                                    
                        //             default:

                        //                 $html = $this->render('detalleventa/ticket.html.twig', array(
                        //                         'facturaVenta' => $facturaVenta,
                        //                         'localObj'     => $localObj,
                        //                         'host' => $host
                        //                     ))->getContent();

                        //                 $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-height' => 210,'page-width'  => 58,'margin-right' => 2,'margin-left' => 2,'margin-top' => 5,'margin-bottom' => 0));

                        //                 $f = 'ticket_'.$facturaVenta->getTicket();

                        //                 break;
                        //         }
                        //     }
                        // }


                    }



                }

            }
          
        }
        else
        {

            if($facturaVenta->getVenta()->getVentaFormaPago()[0]->getFormaPago()->getCodigo() == '5' && $facturaVenta->getVenta()->getVentaFormaPago()[0]->getCondicion() == 'pendiente')
            {


                $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
                $dql .= " JOIN cxd.documento d";
                $dql .= " JOIN d.local l";
                $dql .= " WHERE  l.id =:local  AND d.codigo = '07' AND cxd.estado = 1  ";

                $query = $em->createQuery($dql);

                $query->setParameter('local',$local);         
         
                $componentesXDocumento =  $query->getResult();

                $html = $this->render('detalleventa/reciboingreso.html.twig', array(
                        'facturaVenta' => $facturaVenta,
                        'localObj'     => $localObj,
                        'componentesXDocumento' => $componentesXDocumento,
                        'host' => $host
                    ))->getContent();

                $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-height' =>  200,'page-width' => 50,'margin-right' => 2,'margin-left' => 1,'margin-top' => 3,'margin-bottom' => 3));

                $f = 'reciboingreso_'.$facturaVenta->getTicket();

            }
            else
            {

                if($facturaVenta->getDocumento() == 'boleta')
                {

                    $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
                    $dql .= " JOIN cxd.documento d";
                    $dql .= " JOIN d.local l";
                    $dql .= " WHERE  l.id =:local  AND d.codigo = '03' AND cxd.estado = 1  ";

                    $query = $em->createQuery($dql);

                    $query->setParameter('local',$local);         
             
                    $componentesXDocumento =  $query->getResult();

                    $html = $this->render('detalleventa/boleta.html.twig', array(
                            'facturaVenta' => $facturaVenta,
                            'localObj'     => $localObj,
                            'componentesXDocumento' => $componentesXDocumento,
                            'host' => $host
                        ))->getContent();

                    $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 5,'margin-bottom' => 5));

                    $f = 'boleta_'.$facturaVenta->getTicket();

                }
                elseif($facturaVenta->getDocumento() == 'factura')
                {

                    $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
                    $dql .= " JOIN cxd.documento d";
                    $dql .= " JOIN d.local l";
                    $dql .= " WHERE  l.id =:local  AND d.codigo = '01' AND cxd.estado = 1  ";

                    $query = $em->createQuery($dql);

                    $query->setParameter('local',$local);         
             
                    $componentesXDocumento =  $query->getResult();

                    $cuentasBancarias = $em->getRepository('AppBundle:CuentaBanco')->findBy(array('estado'=>true,'empresa'=>$empresa),array('banco'=>'ASC'));

                    $html = $this->render('detalleventa/factura.html.twig', array(
                            'facturaVenta' => $facturaVenta,
                            'localObj'     => $localObj,
                            'cuentasBancarias' => $cuentasBancarias,
                            'componentesXDocumento' => $componentesXDocumento,
                            'host' => $host
                        ))->getContent();

                    $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 5,'margin-bottom' => 5));

                    $f = 'factura_'.$facturaVenta->getTicket();                

                }
                else
                {

                    switch ($localObj->getNotaventaFormato()) {
                        case 'A4':

                            $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
                            $dql .= " JOIN cxd.documento d";
                            $dql .= " JOIN d.local l";
                            $dql .= " WHERE  l.id =:local  AND d.codigo = '03' AND cxd.estado = 1  ";

                            $query = $em->createQuery($dql);

                            $query->setParameter('local',$local);         
                     
                            $componentesXDocumento =  $query->getResult();

                            $html = $this->render('detalleventa/ticketA4.html.twig', array(
                                    'facturaVenta' => $facturaVenta,
                                    'localObj'     => $localObj,
                                    'componentesXDocumento' => $componentesXDocumento,
                                    'host' => $host
                                ))->getContent();


                            $f = 'notaventa_'.$facturaVenta->getTicket();

                            $pdf =  $this->get('knp_snappy.pdf')->getOutputFromHtml($html,array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 5,'margin-bottom' => 5));

                            break;
                        
                        default:

                            $html = $this->render('detalleventa/ticket.html.twig', array(
                                    'facturaVenta' => $facturaVenta,
                                    'localObj'     => $localObj,
                                    'host' => $host
                                ))->getContent();

                            $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-height' => 210,'page-width'  => 80,'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

                            $f = 'ticket_'.$facturaVenta->getTicket();

                            break;
                    }
                }
            }

        }


        return new \Symfony\Component\HttpFoundation\Response(
                $pdf, 200, array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'inline; filename="'.$f.'.pdf"',
                     
        ));

    }


    /**
     * envia un enlace de la factura al correo electronico del cliente de la venta realizada.
     *
     * @Route("/{id}/{email}/enviar/factura", name="detalleventa_enviar_factura")
     * @Method("GET")
     */
    public function enviarFacturaAction(Request $request, FacturaVenta $facturaVenta,$email)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $empresaObj     = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $enlacepdf  = $facturaVenta->getEnlacepdf();

        try{

            if($email)
            {
                if($enlacepdf){

                    $enviar = $this->enviarFactura($email,$enlacepdf,$empresaObj->getCorreoRemitente(),$facturaVenta);

                    if($enviar)
                    {
                        if($facturaVenta->getCliente()){

                            if($facturaVenta->getCliente()->getEmail() == null){
                                $facturaVenta->getCliente()->setEmail($email);
                                $em->persist($facturaVenta);
                                $em->flush();
                            }                            
                        }


                        $this->addFlash("success", "Se ha enviado exitosamente un correo electrónico al cliente con la factura/boleta de la compra realizada.");
                    }
                    else
                    {
                        $this->addFlash("danger", "Hubo problemas en el envío del correo electrónico. Consultar al administrador del sistema.");
                    }

                }
                else
                {
                    $this->addFlash("danger", "La factura no ha sido generada electrónicamente.No se puede enviar el documento al cliente.");

                }
                
            }
            else
            {
                $this->addFlash("danger", "El cliente no tiene registrado ningún correo electrónico. No se ha enviado la factura. Corregir y volver a intentar.");
            }

            

        }catch(\Exception $e){

            $this->addFlash("danger", $e."Ocurrió un error inesperado, el correo no pudo ser enviado.");
        }

        return $this->redirectToRoute('detalleventa_lista');
    }

    private function enviarFactura($email = '',$enlace = '',$correo_remitente = '',$facturaVenta = null){

        $request        = $this->get('request_stack')->getCurrentRequest();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $em = $this->getDoctrine()->getManager();

        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);
        $nombreCorto = ($empresaObj->getNombreCorto()) ? $empresaObj->getNombreCorto() : 'FERRETERO';

        $mensaje = 'En el siguiente enlace puede descargar la FACTURA/BOLETA emitida el : '.$facturaVenta->getFecha()->format('d/m/Y').'.';

        $cliente = ($facturaVenta->getCliente()) ? $facturaVenta->getCliente()->getRazonSocial() : 'CLIENTE';


        if($correo_remitente == ''){
            $correo_remitente = 'soporte@intimedia.net';
        }

        if($email != ''){

            $message = \Swift_Message::newInstance()
                    ->setSubject($nombreCorto.' - Factura/Boleta')
                    ->setFrom($correo_remitente)
                    ->setTo($email)
                    ->setContentType('text/html')
                    ->setBody(
                    $this->renderView('detalleventa/enviarFactura.html.twig', array(
                        'enlace'     => $enlace,
                        'mensaje'    => $mensaje,
                        'empresa'    => $nombreCorto,
                        'cliente'    => $cliente
                    ))
                    )
            ;
            

            if($this->get('mailer')->send($message)){

                return true;

            }else{

                return false;

            }

            

        }

        return false;

    }


    /**
     * Lists all detalleVentum entities.
     *
     * @Route("/lista/factura/noenviada", name="detalleventa_lista_factura_noenviada")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function listaFacturaNoenviadaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();

        $dql = "SELECT fv FROM AppBundle:FacturaVenta fv ";
        $dql .= " JOIN fv.venta v";
        $dql .= " JOIN v.empleado e";
        $dql .= " JOIN e.local l";
        $dql .= " JOIN l.empresa em";
        $dql .= " WHERE  v.estado = 1 AND fv.tipo = 2 AND fv.enviadoSunat = 0  AND fv.documento IN ('factura','boleta') ";
        $dql .= " AND  l.facturacionElectronica = 1 ";
        $dql .= " ORDER BY fv.fecha DESC ";

        $query = $em->createQuery($dql);

        $facturas =  $query->getResult();

        return $this->render('detalleventa/listaFacturaNoenviada.html.twig', array(
            'facturas'      => $facturas,
            'titulo'        => 'Documentos no enviados',
        ));
    }

    /**
     * Procedemos a la actualizacion del estado de envio a la SUNAT
     *
     * @Route("/{id}/actualizar/estado/envio", name="detalleventa_actualizar_estado_envio")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function actualizarEstadoEnvioAction(Request $request,FacturaVenta $facturaVenta)
    {
        $session        = $request->getSession();

        $fecha_pago = new \DateTime();

        $em = $this->getDoctrine()->getManager();

        $facturaVenta->setEnviadoSunat(true);
        $em->persist($facturaVenta);

        try {

            $em->flush();
            $this->addFlash("success","Se actualizó la información correctamente.");

        } catch (\Exception $e) {

            $this->addFlash("danger", $e." Ocurrió un error inesperado, el pago no se realizó.");
            
        }

        return $this->redirectToRoute('detalleventa_lista_factura_noenviada');       

    }


    /**
     * Procedemos a enviar factura a nubefact
     *
     * @Route("/{id}/enviar/factura/nubefact", name="detalleventa_enviar_factura_nubefact")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function enviarFacturaNubefactAction(Request $request,FacturaVenta $facturaVenta)
    {
        $em = $this->getDoctrine()->getManager();

        $localObj = $facturaVenta->getLocal();
        $local = $localObj->getId();

        $data_json = $this->container->get('AppBundle\Util\Util')->generarArchivoJson($facturaVenta,$local);
        $respuesta = $this->container->get('AppBundle\Util\Util')->enviarArchivoJson($data_json,$localObj);
        $leer_respuesta = json_decode($respuesta, true);



        if (isset($leer_respuesta['errors'])) 
        {

            $nferror  = $em->getRepository('AppBundle:NubefactError')->findOneBy(array('codigo'=>$leer_respuesta['codigo']));
            $msj_error = ($nferror) ? $nferror->getDescripcion().' '.$leer_respuesta['errors'] : 'Error no identificado';

            $this->addFlash("danger"," Ocurrió un error inesperado, la factura no fue recibida. ".$msj_error);
        }
        else
        {
            $enlace_de_pdf = $leer_respuesta['enlace'].'.pdf';

            $facturaVenta->setEnlacepdf($enlace_de_pdf);
            $facturaVenta->setEnviadoSunat(true);

            $em->persist($facturaVenta);

            try {

                $em->flush();

                $this->addFlash("success","Se envió la información correctamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger"," Ocurrió un error inesperado. ".$e);
                
            }

            
        }


        return $this->redirectToRoute('detalleventa_lista_factura_noenviada');       

    }


    /**
     * Procesar data desde el server
     *
     * @Route("/listaventa/datatable", name="listaventa_datatable")
     * @Method({"GET", "POST"})
     * 
     */
    public function dataTableAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $result = $this->container->get('AppBundle\Util\Util')->getFacturaVentaDT('v_factura_venta',$empresa);

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
