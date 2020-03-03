<?php

namespace AppBundle\Controller;

use AppBundle\Entity\PedidoVenta;
use AppBundle\Entity\GuiaRemision;
use AppBundle\Entity\Transferencia;
use AppBundle\Entity\TransferenciaXProducto;
use AppBundle\Entity\FacturaVenta;
use AppBundle\Entity\Venta;
use AppBundle\Entity\DetalleVenta;
use AppBundle\Entity\VentaFormaPago;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Pedidoventum controller.
 *
 * @Route("pedidoventa")
 */
class PedidoVentaController extends Controller
{
    /**
     * Lists all pedidoVentum entities.
     *
     * @Route("/", name="pedidoventa_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $caja           = $session->get('caja');
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $pedidoVentas = $em->getRepository('AppBundle:PedidoVenta')->findBy(array('local'=>$local,'despachado'=>false));

        return $this->render('pedidoventa/index.html.twig', array(
            'pedidoVentas' => $pedidoVentas,
            'titulo' => 'Lista de pedidos'
        ));
    }

    /**
     * Creates a new pedidoVentum entity.
     *
     * @Route("/new", name="pedidoventa_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $caja           = $session->get('caja');
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $fecha = new \DateTime();

        $categorias      = $this->container->get('AppBundle\Util\Util')->obtenerCategorias($local);
        $formClientePv   = $this->createForm('AppBundle\Form\ClientePvType',null);
        $productoXLocals = $this->container->get('AppBundle\Util\Util')->productosMasVendidosXLocal($local,null);
        $tipoCambio      = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$fecha));

        $pedidoVenta = new PedidoVenta();
        $form = $this->createForm('AppBundle\Form\PedidoVentaType', $pedidoVenta,array('empresa'=>$empresa,'local'=>$local,'caja'=>$caja));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $pedidos  = $em->getRepository('AppBundle:PedidoVenta')->findBy(array('local'=>$form->getData()->getCaja()->getLocal()));

            $numero_pedido = count($pedidos) + 1;
            $numero_pedido = str_pad($numero_pedido, 7, "0", STR_PAD_LEFT);

            $hora = date('H');
            $minuto = date('i');
            $segundo = date('s');

            $intervalo_adicional = 'PT'.$hora.'H'.$minuto.'M'.$segundo.'S';
            $pedidoVenta->getFecha()->add(new \DateInterval($intervalo_adicional));

            $cliente_id     = $request->request->get('cliente_select');
            $cliente        = $em->getRepository('AppBundle:Cliente')->find($cliente_id);

            $pedidoVenta->setCliente($cliente);            
            $pedidoVenta->setLocal($form->getData()->getCaja()->getLocal());
            $pedidoVenta->setDespachado(false);
            $pedidoVenta->setNumeroPedido($numero_pedido);

            //Registramos el tipo de cambio si fue seleccionada como moneda el dolar
            $valor_tipo_cambio = null;
            if($pedidoVenta->getMoneda()->getId() == 2)
            {
                $fecha = $pedidoVenta->getFecha();

                $tipoCambioObj       = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$fecha));
                $valor_tipo_cambio   = ($tipoCambioObj)? $tipoCambioObj->getVenta() : '';

                if($valor_tipo_cambio == '')
                {
                    $this->addFlash("danger", " No se pudo registrar el pedido . El tipo de cambio para la fecha seleccionada no existe. Fecha seleccionada: ".$fecha->format('d/m/Y'));
                    return $this->redirectToRoute('pedidoventa_new');
                }
                
            }

            $pedidoVenta->setValorTipoCambio($valor_tipo_cambio);

            $em->persist($pedidoVenta);

            try {

                $em->flush();
                $this->addFlash("success", "El pedido fue registrado exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
            }
            
            return $this->redirectToRoute('pedidoventa_edit', array('id' => $pedidoVenta->getId()));

            //return $this->redirectToRoute('pedidoventa_index');
        }

        return $this->render('pedidoventa/new.html.twig', array(
            'pedidoVenta'       => $pedidoVenta,
            'form'              => $form->createView(),
            'titulo'            => 'Registrar pedido',
            'categorias'        => $categorias,
            'formClientePv'     => $formClientePv->createView(),
            'productoXLocals'   => $productoXLocals,
            'tipoCambio'        => $tipoCambio,
        ));
    }

    /**
     * Finds and displays a pedidoVentum entity.
     *
     * @Route("/{id}", name="pedidoventa_show")
     * @Method("GET")
     */
    public function showAction(PedidoVenta $pedidoVentum)
    {
        $deleteForm = $this->createDeleteForm($pedidoVentum);

        return $this->render('pedidoventa/show.html.twig', array(
            'pedidoVentum' => $pedidoVentum,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing pedidoVentum entity.
     *
     * @Route("/{id}/edit", name="pedidoventa_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, PedidoVenta $pedidoVenta)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $caja           = $session->get('caja');
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);
        $fecha = new \DateTime();


        $originalTags = new ArrayCollection();

        // Creamos un array collection original del detalle
        foreach ($pedidoVenta->getPedidoVentaDetalle() as $tag) {
            $originalTags->add($tag);
        }

        $categorias     = $this->container->get('AppBundle\Util\Util')->obtenerCategorias($local);
        $formClientePv  = $this->createForm('AppBundle\Form\ClientePvType',null);
        $productoXLocals = $this->container->get('AppBundle\Util\Util')->productosMasVendidosXLocal($local,null);
        $tipoCambio      = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$fecha));

        $editForm = $this->createForm('AppBundle\Form\PedidoVentaType', $pedidoVenta,array('empresa'=>$empresa,'local'=>$local,'caja'=>$caja));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $hora       = date('H');
            $minuto     = date('i');
            $segundo    = date('s');

            $intervalo_adicional = 'PT'.$hora.'H'.$minuto.'M'.$segundo.'S';
            $pedidoVenta->getFecha()->add(new \DateInterval($intervalo_adicional));

            $cliente_id     = $request->request->get('cliente_select');
            $cliente        = $em->getRepository('AppBundle:Cliente')->find($cliente_id);

            $pedidoVenta->setCliente($cliente);

            //Registramos el tipo de cambio si fue seleccionada como moneda el dolar
            $valor_tipo_cambio = null;
            if($pedidoVenta->getMoneda()->getId() == 2)
            {
                $fecha = $pedidoVenta->getFecha();

                $tipoCambioObj       = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$fecha));
                $valor_tipo_cambio   = ($tipoCambioObj)? $tipoCambioObj->getVenta() : '';

                if($valor_tipo_cambio == '')
                {
                    $this->addFlash("danger", " No se pudieron registrar los cambios . El tipo de cambio para la fecha seleccionada no existe. Fecha seleccionada: ".$fecha->format('d/m/Y'));
                    return $this->redirectToRoute('pedidoventa_edit', array('id' => $pedidoVenta->getId()));
                }

            }

            $pedidoVenta->setValorTipoCambio($valor_tipo_cambio);


            // eliminamos los items del detalle q fueran necearios
            foreach ($originalTags as $tag) {
                if (false === $pedidoVenta->getPedidoVentaDetalle()->contains($tag)) {
                    $em->remove($tag);
                }
            }

            if($request->request->get('btn_opcion') == 'Generar Factura')
            {
                $pedidoVenta->setDespachado(true);
            }            

            $em->persist($pedidoVenta);

            //Guardamos en factura_venta en caso sea una venta.
            if($request->request->get('btn_opcion') == 'Generar Factura')
            {

                $fecha = $pedidoVenta->getFecha();
                $empleado = $em->getRepository('AppBundle:Empleado')->findOneBy(array('usuario'=>$session->get('usuario')));

                //Guardamos en la tabla venta
                $venta = new Venta();
                $venta->setEmpleado($empleado);
                $venta->setFecha($fecha);
                $venta->setEstado(true);
                $venta->setCondicion(false);
                $em->persist($venta);


                //Guardamos en la tabla factura_venta
                $facturaVenta = new FacturaVenta();

                $tipoVenta = $em->getRepository('AppBundle:FacturaVentaTipo')->find(2);
                $facturaVenta->setTipo($tipoVenta);

                $empresa_mismo_prefijo_multilocal = ($empresaObj->getPermitirMismoPrefijoMultilocal()) ? $empresa : '';

                $ultimoIdFacturaVenta = $this->container->get('AppBundle\Util\Util')->generarNumeroDocumento('factura_venta',$pedidoVenta->getCaja()->getLocal()->getId(),$pedidoVenta->getDocumento(),$empresa_mismo_prefijo_multilocal);

                $ultimoIdFacturaVenta++;

                switch ($editForm->getData()->getDocumento()) {
                    case 'factura':
                        $voucher = ($pedidoVenta->getCaja()->getLocal()->getSerieFactura()) ? $pedidoVenta->getCaja()->getLocal()->getSerieFactura().'-'.$ultimoIdFacturaVenta : $ultimoIdFacturaVenta;
                        $facturaVenta->setSerie($pedidoVenta->getCaja()->getLocal()->getSerieFactura());
                        break;
                    case 'boleta':
                        $voucher = ($pedidoVenta->getCaja()->getLocal()->getSerieBoleta()) ? $pedidoVenta->getCaja()->getLocal()->getSerieBoleta().'-'.$ultimoIdFacturaVenta : $ultimoIdFacturaVenta;
                        $facturaVenta->setSerie($pedidoVenta->getCaja()->getLocal()->getSerieBoleta());
                        break;
                    case 'guia':
                        $voucher = ($pedidoVenta->getCaja()->getLocal()->getPrefijoVoucher()) ? $pedidoVenta->getCaja()->getLocal()->getPrefijoVoucher().'-'.$ultimoIdFacturaVenta : $ultimoIdFacturaVenta;
                        $facturaVenta->setSerie($pedidoVenta->getCaja()->getLocal()->getPrefijoVoucher());
                        break;                            
                    default:
                        $voucher = ($pedidoVenta->getCaja()->getLocal()->getPrefijoVoucher()) ? $pedidoVenta->getCaja()->getLocal()->getPrefijoVoucher().'-'.$ultimoIdFacturaVenta : $ultimoIdFacturaVenta;
                        $facturaVenta->setSerie($pedidoVenta->getCaja()->getLocal()->getPrefijoVoucher());
                        break;
                }

                $facturaVenta->setNumero($ultimoIdFacturaVenta);
                $facturaVenta->setCliente($cliente);
                $facturaVenta->setVenta($venta);
                $facturaVenta->setFecha($fecha);
                $facturaVenta->setDocumento($editForm->getData()->getDocumento());
                $facturaVenta->setIncluirIgv($pedidoVenta->getIncluirIgv());
                $facturaVenta->setTicket($voucher);              


                $transferencias = $em->getRepository('AppBundle:Transferencia')->findBy(array('pedidoVenta'=>$pedidoVenta->getId(),'documento'=>'despacho','estado'=>true));
                $motivoTraslado = $em->getRepository('AppBundle:MotivoTraslado')->findOneBy(array('codigo'=>'01'));

                $guia_remision = array();
                foreach($transferencias as $transferencia)
                {
                    $guia_remision[] = $transferencia->getNumeroDocumento();                    
                    $transferencia->setMotivoTraslado($motivoTraslado);
                    $transferencia->setFacturaVenta($facturaVenta);
                    $em->persist($transferencia);                    
                }

                $facturaVenta->setNumeroGuiaremision(implode(',',$guia_remision));
                $facturaVenta->setLocal($editForm->getData()->getCaja()->getLocal());
                $facturaVenta->setCaja($editForm->getData()->getCaja());
                //$facturaVenta->setFacturaEnviada($enviarFactura);
                $facturaVenta->setDetraccion(false);
                $facturaVenta->setEnviadoSunat(false);
                $facturaVenta->setEmisionElectronica(true);

                $em->persist($facturaVenta);



                //Guardamos el detalle de la venta en la tabla detalle_venta
                $productos_array = array();
                $total = 0;
                $j=0;
                foreach($pedidoVenta->getPedidoVentaDetalle() as $detalle)
                {
                    if($detalle->getProductoXLocal())
                    {
                        //Guardamos el detalle de Venta
                        $detalleVenta = new DetalleVenta();
                        $detalleVenta->setVenta($venta);
                        $detalleVenta->setProductoXLocal($detalle->getProductoXLocal());
                        $detalleVenta->setCantidad($detalle->getCantidadPedida());
                        $detalleVenta->setPrecio($detalle->getPrecio());
                        $detalleVenta->setSubtotal($detalle->getSubtotal());
                        //$detalleVenta->setDescripcion($descripcion);
                        $em->persist($detalleVenta);

                        //Armamos el array q se va enviar a nubefact
                        $tipo           = $detalle->getProductoXLocal()->getProducto()->getTipo();
                        $codigo         = $detalle->getProductoXLocal()->getProducto()->getCodigo();
                        $descripcion    = $detalle->getProductoXLocal()->getProducto()->getNombre();
                        $cantidad       = $detalle->getCantidadPedida();
                        $precio         = $detalle->getPrecio();
                        $subtotal       = $detalle->getSubtotal();
                        $tipoImpuesto   = 1;//($detalle->getTipoImpuesto()) ? $detalle->getTipoImpuesto()->getId() : 
                        $impuestoValor  = 0.18;//($detalle->getTipoImpuesto()) ? $detalle->getTipoImpuesto()->getValor() : 
                        $codigoProductoSunat  = $detalle->getProductoXLocal()->getProducto()->getCodigoSunat()->getCodigo();   
                        
                        $productos_array[$j]['tipo'] = $tipo;
                        $productos_array[$j]['codigo'] = $codigo;
                        $productos_array[$j]['descripcion'] = $descripcion;
                        $productos_array[$j]['cantidad'] = $cantidad;
                        $productos_array[$j]['precio'] = $precio;
                        $productos_array[$j]['subtotal'] = $subtotal;
                        $productos_array[$j]['tipoImpuesto'] = $tipoImpuesto;
                        $productos_array[$j]['impuestoValor'] = $impuestoValor;
                        $productos_array[$j]['codigoProductoSunat'] = $codigoProductoSunat;

                        $total = $total + $detalle->getSubtotal();
                        $j++;                        

                    }
                 
                }

                //Guardamos en la tabla venta_forma_pago
                $ventaformaPago = new VentaFormaPago();        
                $ventaformaPago->setFormaPago($pedidoVenta->getFormaPago());
                $ventaformaPago->setVenta($venta);
                $ventaformaPago->setCantidad($total);
                $igv = ($pedidoVenta->getIncluirIgv()) ?  0 : $pedidoVenta->getImpuesto() ;
                $ventaformaPago->setIgv($igv);
                $ventaformaPago->setMoneda($pedidoVenta->getMoneda());
                $ventaformaPago->setMontoACuenta($pedidoVenta->getMontoACuenta());
                $ventaformaPago->setNumeroDias($pedidoVenta->getDiasCredito());

                $valor_tipo_cambio = ($pedidoVenta->getValorTipoCambio()) ? $pedidoVenta->getValorTipoCambio() : null;

                $ventaformaPago->setValorTipoCambio($valor_tipo_cambio);

                $em->persist($ventaformaPago);


                //Enviamos la venta a nubefact
                $respuesta = $this->container->get('AppBundle\Util\Util')->enviarVenta($facturaVenta,$pedidoVenta,$productos_array);
                $respuesta = json_decode($respuesta, true);


                if(isset($respuesta['errors']))
                {
                    $this->addFlash("danger"," Ocurrió un error, el registro no fue enviado.".$respuesta['errors']);
                    return $this->redirectToRoute('pedidoventa_edit', array('id' => $pedidoVenta->getId()));
                }
                else
                {

                    $enlace_del_pdf = ($respuesta['enlace_del_pdf']) ? $respuesta['enlace_del_pdf'] : $respuesta['enlace'].'.pdf';
                    $enlace_del_cdr = ($respuesta['enlace_del_cdr']) ? $respuesta['enlace_del_cdr'] : $respuesta['enlace'].'.cdr';
                    $enlace_del_xml = ($respuesta['enlace_del_xml']) ? $respuesta['enlace_del_xml'] : $respuesta['enlace'].'.xml';
                    $facturaVenta->setEnlacepdf($enlace_del_pdf);
                    $facturaVenta->setEnlaceCdr($enlace_del_cdr);
                    $facturaVenta->setEnlaceXml($enlace_del_xml);
                    $facturaVenta->setEnviadoSunat(true);
                    $em->persist($facturaVenta);
                }         

            }            

            try {

                $em->flush();

                // Actualizamos el stock y registramos los movimientos en caja
                if($request->request->get('btn_opcion') == 'Generar Factura')
                {

                    $forma_pago = $pedidoVenta->getFormaPago()->getCodigo();
                    $monto_a_cuenta = $pedidoVenta->getMontoACuenta();

                    switch ($forma_pago) 
                    {
                        case '1'://Contado
                            $monto_en_caja = $total;
                            break;
                        case '2'://Crédito
                            $monto_en_caja = 0;
                            break;                                                            
                        case '3'://Tarjeta de credito
                            $monto_en_caja = $total;
                            break;
                        case '4'://Nota de credito
                            $monto_en_caja = 0;
                            break;
                        case '5'://A cuenta
                            $monto_en_caja = $monto_a_cuenta;
                            break;
                        case '6'://Cheque
                            $monto_en_caja = $total;
                            break;                                                            
                        case '7'://Letra
                            $monto_en_caja = $total;
                            break;
                        case '8'://Depósito Transferencia
                            $monto_en_caja = $total;
                            break;                                                                         
                        default://por defecto
                            $monto_en_caja = $total;
                            break;
                    }    

                              
                    //Obtenermos el id de la caja apertura
                    $caja_apertura_id = $session->get('caja_apertura');

                    if(!$session->get('caja_apertura')){

                        $dql = "SELECT ca FROM AppBundle:CajaApertura ca ";
                        $dql .= " JOIN ca.caja c";
                        $dql .= " WHERE  c.id =:caja_id  AND ca.estado = 1 ";

                        $query = $em->createQuery($dql);

                        $query->setParameter('caja_id',$pedidoVenta->getCaja()->getId());
                 
                        $cajaApertura = $query->getSingleResult();

                        $caja_apertura_id = $cajaApertura->getId();

                        $session->set('caja_apertura',$caja_apertura_id);

                    }

                    $this->container->get('AppBundle\Util\Util')->registrarCajaAperturaDetalle($caja_apertura_id,$total,'venta');

                    // if($facturaVenta->getLocal()->getCajaybanco()){
                    //     //Guardamos pago de transaccion
                    //     $this->container->get('AppBundle\Util\UtilCajaBanco')->guardarPagoTransaccion($facturaVenta,$monto_en_caja,'v');
                    // }

                    $this->addFlash("success", "La venta fue realizada exitosamente. Ver documento en la lista de ventas. Factura Nro. : ".$facturaVenta->getTicket());

                    return $this->redirectToRoute('pedidoventa_index');

                }


                $this->addFlash("success", "El pedido fue editado exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
            }


            return $this->redirectToRoute('pedidoventa_edit', array('id' => $pedidoVenta->getId()));
        }

        return $this->render('pedidoventa/edit.html.twig', array(
            'pedidoVenta'       => $pedidoVenta,
            'edit_form'         => $editForm->createView(),
            'titulo'            => 'Editar pedido',
            'categorias'        => $categorias,
            'formClientePv'     => $formClientePv->createView(),
            'productoXLocals'   => $productoXLocals,
            'tipoCambio'        => $tipoCambio,          
        ));
    }

    /**
     * Pantalla para el despacho de productos
     *
     * @Route("/{id}/despachar", name="pedidoventa_despachar")
     * @Method({"GET", "POST"})
     */
    public function despacharAction(Request $request, PedidoVenta $pedidoVenta)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $caja           = $session->get('caja');
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $despachos = $em->getRepository('AppBundle:Transferencia')->findBy(array('pedidoVenta'=>$pedidoVenta,'documento'=>'despacho','estado'=>true));

        $fecha = new \DateTime();

        $guiaRemision = new GuiaRemision();
        $form = $this->createForm('AppBundle\Form\GuiaRemisionType', $guiaRemision,array('empresa'=>$empresa));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {

            $serie = ($pedidoVenta->getLocal()->getSerieGuiaremision()) ? $pedidoVenta->getLocal()->getSerieGuiaremision() : null;
            $empresa_mismo_prefijo_multilocal = ($pedidoVenta->getLocal()->getEmpresa()->getPermitirMismoPrefijoMultilocal()) ? $empresa : '';
            $numero = (int)$this->container->get('AppBundle\Util\Util')->generarNumeroGuiaremision('guia_remision',$pedidoVenta->getLocal()->getId(),$empresa_mismo_prefijo_multilocal);
            $numero = $numero + 1;

            //Guardamos el objeto Transferencia
            $transferencia  = new Transferencia();
            $transferencia->setLocalInicio($pedidoVenta->getLocal());
            $transferencia->setLocalFin(null);
            $transferencia->setFecha($fecha);
            
            $transferencia->setEmpresa($pedidoVenta->getLocal()->getEmpresa());
            $transferencia->setDocumento('despacho');
            $usuario    = $em->getRepository('AppBundle:Usuario')->find($session->get('usuario'));
            $transferencia->setUsuario($usuario);

            $numero_guiaremision = ($serie) ? $serie.'-'.$numero : 'GUIA-'.$numero;

            $transferencia->setNumeroDocumento($numero_guiaremision);
           
            $motivoTraslado  = $em->getRepository('AppBundle:MotivoTraslado')->findOneBy(array('codigo'=>'12'));
            $transferencia->setMotivoTraslado($motivoTraslado);
            $transferencia->setEstado(true);
            $transferencia->setPedidoVenta($pedidoVenta);
            $em->persist($transferencia);

            $productos_array = array();
            $j=0;
            foreach($pedidoVenta->getPedidoVentaDetalle() as $detalle)
            {

                if($request->request->get('select_'.$detalle->getId()) == 'on')
                {
                    $cantidad_entregada = $request->request->get('cantidad_entregada_'.$detalle->getId());
                    $cantidad_entregada_actual = ($detalle->getCantidadEntregada()) ? $detalle->getCantidadEntregada(): 0;

                    $cantidad_entregada_nueva = $cantidad_entregada_actual + $cantidad_entregada;

                    $detalle->setCantidadEntregada($cantidad_entregada_nueva);
                    $em->persist($detalle);

                    //Agregamos los items en el detalle de transferencia
                    $transferenciaXProducto = new TransferenciaXProducto();
                    $transferenciaXProducto->setProductoXLocal($detalle->getProductoXLocal());
                    $transferenciaXProducto->setCantidad($cantidad_entregada);
                    $transferenciaXProducto->setTransferencia($transferencia);
                    $em->persist($transferenciaXProducto);

                    //Armamos el array q se va enviar a nubefact
                    $tipo           = $detalle->getProductoXLocal()->getProducto()->getTipo();
                    $codigo         = $detalle->getProductoXLocal()->getProducto()->getCodigo();
                    $descripcion    = $detalle->getProductoXLocal()->getProducto()->getNombre();
                    $cantidad       = $cantidad_entregada;

                    $productos_array[$j]['id'] = $detalle->getProductoXLocal()->getId();
                    $productos_array[$j]['tipo'] = $tipo;
                    $productos_array[$j]['codigo'] = $codigo;
                    $productos_array[$j]['descripcion'] = $descripcion;
                    $productos_array[$j]['cantidad'] = $cantidad;

                    $j++;                                           
                }
            }

            $hora       = date('H');
            $minuto     = date('i');
            $segundo    = date('s');

            $intervalo_adicional = 'PT'.$hora.'H'.$minuto.'M'.$segundo.'S';
            $guiaRemision->getFechaEmision()->add(new \DateInterval($intervalo_adicional));
            $guiaRemision->setCliente($pedidoVenta->getCliente());
            $guiaRemision->setSerie($serie);
            $guiaRemision->setNumero($numero);
            $guiaRemision->setLocal($pedidoVenta->getLocal());
            $guiaRemision->setEstado(true);
            $guiaRemision->addTransferencia($transferencia);

            $em->persist($guiaRemision);

            //Enviamos la guia de remision a nubefact
            $respuesta = $this->container->get('AppBundle\Util\Util')->enviarGuiaRemision($guiaRemision,$productos_array);            
            $respuesta = json_decode($respuesta, true);


            if(isset($respuesta['errors']))
            {
                $this->addFlash("danger"," Ocurrió un error, el registro no fue enviado.".$respuesta['errors']);
                return $this->redirectToRoute('pedidoventa_despachar', array('id' => $pedidoVenta->getId()));
            }
            else
            {
                $guiaRemision->setEnlacePdf($respuesta['enlace_del_pdf']);
                $guiaRemision->setEnlaceCdr($respuesta['enlace_del_cdr']);
                $guiaRemision->setEnlaceXml($respuesta['enlace_del_xml']);
            }

            $em->persist($guiaRemision);

            try {

                $em->flush();

                //Realizamos movimiento de almacen
                foreach ($productos_array as $key => $value) 
                {
                    $producto = $value['id'];
                    $cantidad = $value['cantidad'];
                    $this->container->get('AppBundle\Util\Util')->disminuirAlmacen($producto,$cantidad);
                }


                $this->addFlash("success", "El despacho fue realizado exitosamente.");            
                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro fue enviado pero no se guardó.");
            }

            return $this->redirectToRoute('pedidoventa_despachar', array('id' => $pedidoVenta->getId()));
        }

        return $this->render('pedidoventa/despachar.html.twig', array(
            'pedidoVenta'       => $pedidoVenta,
            'form'              => $form->createView(),
            'titulo'            => 'Despachar productos',
            'despachos'         => $despachos
       
        ));
    }


    /**
     * Proceso para la reversion de un pedido
     *
     * @Route("/{id}/revertir", name="pedidoventa_revertir")
     * @Method({"GET", "POST"})
     */
    public function revertirAction(Request $request, PedidoVenta $pedidoVenta)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $caja           = $session->get('caja');
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        foreach($pedidoVenta->getPedidoVentaDetalle() as $detalle)
        {

            $producto = $detalle->getProductoXLocal()->getId();
            $cantidad = $detalle->getCantidadEntregada();
            $this->container->get('AppBundle\Util\Util')->aumentarAlmacen($producto,$cantidad);

            $detalle->setCantidadEntregada(0);
            $em->persist($detalle);


        }

        foreach($pedidoVenta->getTransferencia() as $transferencia)
        {

            $transferencia->setEstado(false);
            $em->persist($transferencia);                

        }

        try {
            
            $em->flush();
            $this->addFlash("success", "El pedido fue revertido exitosamente.");

        } catch (\Exception $e) {

           $this->addFlash("danger", " Ocurrió un error inesperado, el registro no pudo ser revertido."); 
            
        }        
        

        return $this->redirectToRoute('pedidoventa_index');

    }



    /**
     * Deletes a pedidoVentum entity.
     *
     * @Route("/{id}", name="pedidoventa_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, PedidoVenta $pedidoVentum)
    {
        $form = $this->createDeleteForm($pedidoVentum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($pedidoVentum);
            $em->flush();
        }

        return $this->redirectToRoute('pedidoventa_index');
    }

    /**
     * Creates a form to delete a pedidoVentum entity.
     *
     * @param PedidoVenta $pedidoVentum The pedidoVentum entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(PedidoVenta $pedidoVentum)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('pedidoventa_delete', array('id' => $pedidoVentum->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
