<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Proforma;
use AppBundle\Entity\DetalleProformaEntrega;
use AppBundle\Entity\DetalleProformaEntregaDatosEnvio;

use AppBundle\Entity\FacturaVenta;
use AppBundle\Entity\DetalleVentaEntrega;
use AppBundle\Entity\DetalleVentaEntregaDatosEnvio;

use AppBundle\Entity\Transferencia;
use AppBundle\Entity\TransferenciaXProducto;

use AppBundle\Entity\FacturaVentaNubefactError;

/**
 * Administrador controller.
 *
 * @Route("proforma")
 */
class ProformaController extends Controller
{
    /**
     * Lists all proforma entities.
     *
     * @Route("/", name="proforma_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        //$proformas = $em->getRepository('AppBundle:Proforma')->findBy(array('empresa'=>$empresa));

        $dql = "SELECT fv FROM AppBundle:FacturaVenta fv ";
        $dql .= " JOIN fv.venta v";
        $dql .= " JOIN fv.local l";
        $dql .= " JOIN l.empresa e";
        $dql .= " WHERE  v.estado = 1  AND fv.tipo = 1 ";

        if($empresa != ''){
            $dql .= " AND e.id =:empresa  ";
        }

        $dql .= " ORDER BY fv.fecha DESC ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
        $proformas =  $query->getResult();    


        return $this->render('proforma/index.html.twig', array(
            'proformas'   => $proformas,
            'titulo'      => 'Lista de proformas',
        ));
    }

    /**
     * Creates a new Proforma entity.
     *
     * @Route("/new", name="proforma_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')")
     */
    public function newAction(Request $request)
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

        $formCliente    = $this->createForm('AppBundle\Form\DetalleVentaClienteType', null,array('empresa'=>$empresa));

        $productoXLocals = $this->container->get('AppBundle\Util\Util')->productosMasVendidosXLocal($local,null);

        $categorias = $this->container->get('AppBundle\Util\Util')->obtenerCategorias($local);

        $tipoCambio = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$fecha));

        $formClientePv    = $this->createForm('AppBundle\Form\ClientePvType',null);

        return $this->render('proforma/new.html.twig', array(
            'titulo'            =>'Registrar proforma',
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
        ));


    }


    /**
     * Displays a form to edit an existing empresa entity.
     *
     * @Route("/{id}/edit", name="proforma_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')  or has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO')")
     */
    public function editAction(Request $request, FacturaVenta $facturaVenta)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $caja           = ($session->get('caja')) ? $session->get('caja') : '';

        // $tipoCambioObj = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$facturaVenta->getFecha()));
        // $tipo_cambio = ($tipoCambioObj)? $tipoCambioObj->getVenta() : null;

        $localObj   = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        //$formCliente    = $this->createForm('AppBundle\Form\ProformaType', $proforma,array('empresa'=>$empresa));
        $formClientePv    = $this->createForm('AppBundle\Form\ClientePvType',null);

        $moneda = ($facturaVenta->getVenta()->getVentaFormaPago()[0]->getMoneda()) ? $facturaVenta->getVenta()->getVentaFormaPago()[0]->getMoneda()->getId() : 1;
        $tipo_cambio = ($facturaVenta->getVenta()->getVentaFormaPago()[0]->getValorTipoCambio()) ? $facturaVenta->getVenta()->getVentaFormaPago()[0]->getValorTipoCambio() : null;
        $tipo_cliente = ($facturaVenta->getCliente()->getTipo()) ? $facturaVenta->getCliente()->getTipo() : '';


        if($tipo_cliente == 'mayorista')
        {

            if($moneda == 2)
            {

                $productoXLocals = $this->container->get('AppBundle\Util\Util')->productosPrecioEnDolaresMayorista($local,'','',$tipo_cambio);

            }
            else
            {
                $productoXLocals = $this->container->get('AppBundle\Util\Util')->productosPrecioMayorista($local,'','',$tipo_cambio);
            }


        }
        else
        {

            if($moneda == 2)
            {

                $productoXLocals = $this->container->get('AppBundle\Util\Util')->productosPrecioEnDolares($local,'','',$tipo_cambio);
            }
            else
            {
                $productoXLocals = $this->container->get('AppBundle\Util\Util')->productosMasVendidosXLocal($local,'','',$tipo_cambio);
            }

        }

        //dump();

        //die();

        //$productoXLocals = null;


        //$productoXLocals = $this->container->get('AppBundle\Util\Util')->productosMasVendidosXLocal($local,'','',$valorTipoCambio);

        $categorias = $this->container->get('AppBundle\Util\Util')->obtenerCategorias($local);

        //$formSeleccionLocal = $this->createForm('AppBundle\Form\SeleccionarLocalType', null,array('empresa'=>$empresa,'local'=>$local));

        $editForm = $this->createForm('AppBundle\Form\FacturaVentaType', $facturaVenta,array('empresa'=>$empresa,'local'=>$local,'caja'=>$caja));


        $editForm->handleRequest($request);

        // dump($facturaVenta);
        // die();


        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $hora = date('H');
            $minuto = date('i');
            $segundo = date('s');

            $intervalo_adicional = 'PT'.$hora.'H'.$minuto.'M'.$segundo.'S';
            $editForm->getData()->getFecha()->add(new \DateInterval($intervalo_adicional));

            $empresaObj   = $em->getRepository('AppBundle:Empresa')->find($empresa);

            $formato_ferretero = $empresaObj->getFormatoFerretero();

            $voucher = $request->request->get('appbundle_facturaventa')['voucher'];

            if($request->request->get('btn_vender') == 'SI'){


                //Obtenermos el id de la caja apertura
                $caja_apertura_id = $session->get('caja_apertura');

                if(!$session->get('caja_apertura')){

                    $dql = "SELECT ca FROM AppBundle:CajaApertura ca ";
                    $dql .= " JOIN ca.caja c";
                    $dql .= " WHERE  c.id =:caja_id  AND ca.estado = 1 ";

                    $query = $em->createQuery($dql);

                    $query->setParameter('caja_id',$facturaVenta->getCaja()->getId());
             
                    $cajaApertura = $query->getOneOrNullResult();

                    if(!$cajaApertura)
                    {
                        $this->addFlash("danger", " La caja seleccionada se encuentra cerrada. Solicite al administrador abrirla. Si usted es el administrador ir a CAJAS  en el menú principal.");
                        return $this->redirectToRoute('proforma_edit',array('id'=>$facturaVenta->getId()));                    
                    }

                    $caja_apertura_id = $cajaApertura->getId();
                    $session->set('caja_apertura',$caja_apertura_id);

                }


                $tipoVenta = $em->getRepository('AppBundle:FacturaVentaTipo')->find(2);
                $facturaVenta->setTipo($tipoVenta);

                $empresa_mismo_prefijo_multilocal = ($empresaObj->getPermitirMismoPrefijoMultilocal()) ? $empresa : '';

                $ultimoIdFacturaVenta = $this->container->get('AppBundle\Util\Util')->generarNumeroDocumento('factura_venta',$local,$editForm->getData()->getDocumento(),$empresa_mismo_prefijo_multilocal);

                if($voucher == '')
                {


                    $ultimoIdFacturaVenta++;

                    switch ($editForm->getData()->getDocumento()) {
                        case 'factura':
                            $voucher = ($localObj->getSerieFactura())?$localObj->getSerieFactura().'-'.$ultimoIdFacturaVenta:$ultimoIdFacturaVenta;
                            $facturaVenta->setSerie($localObj->getSerieFactura());
                            break;
                        case 'boleta':
                            $voucher = ($localObj->getSerieBoleta())?$localObj->getSerieBoleta().'-'.$ultimoIdFacturaVenta:$ultimoIdFacturaVenta;
                            $facturaVenta->setSerie($localObj->getSerieBoleta());
                            break;
                        case 'guia':
                            $voucher = ($localObj->getPrefijoVoucher())?$localObj->getPrefijoVoucher().'-'.$ultimoIdFacturaVenta:$ultimoIdFacturaVenta;
                            $facturaVenta->setSerie($localObj->getPrefijoVoucher());
                            break;                            
                        default:
                            $voucher = ($localObj->getPrefijoVoucher())?$localObj->getPrefijoVoucher().'-'.$ultimoIdFacturaVenta:$ultimoIdFacturaVenta;
                            $facturaVenta->setSerie($localObj->getPrefijoVoucher());
                            break;
                    }
                        
                }

                $facturaVenta->setNumero($ultimoIdFacturaVenta);


                //Guardamos el objeto Transferencia
                $transferencia  = new Transferencia();
                $transferencia->setLocalInicio($localObj);
                $transferencia->setLocalFin(null);
                $transferencia->setFecha($editForm->getData()->getFecha());
                
                $transferencia->setEmpresa($empresaObj);
                $usuario    = $em->getRepository('AppBundle:Usuario')->find($session->get('usuario'));
                $transferencia->setUsuario($usuario);
                $transferencia->setNumeroDocumento($voucher);
               
                $motivoTraslado  = $em->getRepository('AppBundle:MotivoTraslado')->findOneBy(array('codigo'=>'01'));
                $transferencia->setMotivoTraslado($motivoTraslado);
                $transferencia->setEstado(true);
                $transferencia->setFacturaVenta($facturaVenta);
                $em->persist($transferencia);


            }

            $formapagoCodigo = $editForm->getData()->getVenta()->getVentaFormaPago()[0]->getFormaPago()->getCodigo();
            $pendienteDePago = $editForm->getData()->getVenta()->getCondicion();

            $total = 0;
            foreach($editForm->getData()->getVenta()->getDetalleVenta() as $detalle){

                if($detalle->getProductoXLocal()){

                    $total = $total + $detalle->getSubtotal();

                    if($request->request->get('btn_vender') == 'SI' )
                    {
                        if($detalle->getProductoXLocal()->getStock() <= 0)
                        {
                            if(!$detalle->getProductoXLocal()->getLocal()->getVentaNegativo())
                            {
                                $this->addFlash("danger", " Uno o más productos ya no tienen stock. No se puede realizar la venta. Corrija las cantidades..");
                                return $this->redirectToRoute('proforma_edit',array('id'=>$facturaVenta->getId()));     
                            }

                        }

                        $precio   = $detalle->getPrecio();
                        $cantidad = $detalle->getCantidad();

                        // Insertamos el precio de costo del producto en el momento de la venta
                        $precio_costo = $this->container->get('AppBundle\Util\Util')->obtenerPrecioCosto($detalle->getProductoXLocal()->getId(),$cantidad);
                        $precio_costo = ($precio_costo) ? $precio_costo : $detalle->getProductoXLocal()->getProducto()->getPrecioCompra();
                        $detalle->setPrecioCosto($precio_costo);
                        $em->persist($detalle);

                        //Guardamos el detalle de la transferencia
                        $transferenciaXProducto = new TransferenciaXProducto();
                        $transferenciaXProducto->setProductoXLocal($detalle->getProductoXLocal());
                        $transferenciaXProducto->setCantidad($detalle->getCantidad());
                        $transferenciaXProducto->setTransferencia($transferencia);
                        $transferenciaXProducto->setPrecio($precio);
                        $em->persist($transferenciaXProducto);
                        

                        if($formapagoCodigo == '4'){

                            $this->container->get('AppBundle\Util\Util')->aumentarAlmacen($detalle->getProductoXLocal()->getId(),$cantidad);

                        }elseif($formapagoCodigo == '5' ||  $pendienteDePago == true ){

                        }else{

                            $this->container->get('AppBundle\Util\Util')->disminuirAlmacen($detalle->getProductoXLocal()->getId(),$cantidad);                       

                        }
                        
                        //Guardar informacion en log_modificacion si el precio del producto vendido ha sido editado.
                        $precio_unitario = $detalle->getProductoXLocal()->getProducto()->getPrecioUnitario();

                        if($precio_unitario != $precio){

                            $this->container->get('AppBundle\Util\Util')->registrarLog($detalle->getProductoXLocal(),$precio,$precio_unitario,'precio',$facturaVenta);
                        }

                    }

                }

            }


            if($request->request->get('btn_vender') == 'SI'){
                if($formapagoCodigo == '5')
                {
                    $facturaVenta->getVenta()->getVentaFormaPago()[0]->setCondicion('pendiente');
                    
                }
                elseif($formapagoCodigo == '2'){

                    $facturaVenta->getVenta()->getVentaFormaPago()[0]->setCondicion('pendiente');
                }
            }

            $facturaVenta->getVenta()->getVentaFormaPago()[0]->setCantidad($total);

            $valorTipoCambio = null;
            if($facturaVenta->getVenta()->getVentaFormaPago()[0]->getMoneda()->getId() == 2){

                $tipoCambio = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$facturaVenta->getFecha()));
                $valorTipoCambio = ($tipoCambio) ? $tipoCambio->getVenta() : null;

            }


            $facturaVenta->getVenta()->getVentaFormaPago()[0]->setValorTipoCambio($valorTipoCambio);
            
            $facturaVenta->setTicket($voucher);
            
            if(!$facturaVenta->getIncluirIgv()){
                $facturaVenta->getVenta()->getVentaFormaPago()[0]->setIgv($total*0.18);
            }

            $cliente = $request->request->get('cliente_select');
            $clienteObj  = $em->getRepository('AppBundle:Cliente')->find($cliente);
            $facturaVenta->setCliente($clienteObj);
              
            if($localObj->getFacturacionElectronica())
            {
                $facturaVenta->setEmisionElectronica(true);
            }                      

            $em->persist($facturaVenta);
            $em->flush();


            if($request->request->get('btn_vender') == 'SI'){
                //Generamos documento en nubefact si esta activada la facturacion electronica
                $leer_respuesta = array();
                if($localObj->getUrlFacturacion() != '' && $localObj->getTokenFacturacion() != '' && $localObj->getFacturacionElectronica() != false)
                {

                    if( ($facturaVenta->getDocumento() == 'boleta' || $facturaVenta->getDocumento() == 'factura') && $formapagoCodigo != '5')
                    {

                        $data_json = $this->container->get('AppBundle\Util\Util')->generarArchivoJson($facturaVenta,$local);
                        $respuesta = $this->container->get('AppBundle\Util\Util')->enviarArchivoJson($data_json,$localObj);
                        $leer_respuesta = json_decode($respuesta, true);

                        if(!$formato_ferretero)
                        {


                            if(isset($leer_respuesta['errors'])){

                                $nferror  = $em->getRepository('AppBundle:NubefactError')->findOneBy(array('codigo'=>$leer_respuesta['codigo']));
                                $msj_error = ($nferror) ? $nferror->getDescripcion().' '.$leer_respuesta['errors'] : 'Error no identificado';

                                $this->addFlash("danger", "Hubo un error al generarse el documento para la SUNAT. ".$msj_error);
                                return $this->redirectToRoute('proforma_index');

                            }
                            else
                            {

                                if($leer_respuesta['aceptada_por_sunat'] != 'true'){

                                    $enlace_de_pdf = $leer_respuesta['enlace'].'.pdf';

                                    if($facturaVenta->getDocumento() == 'factura')
                                    {
                                        $facturaVenta->setEnviadoSunat(false);
                                    }
                                    else
                                    {
                                        $facturaVenta->setEnviadoSunat(false);
                                    }

                                    
                                    $facturaVenta->setEnlacepdf($enlace_de_pdf);

                                }
                                else
                                {
                                    $enlace_de_pdf = $leer_respuesta['enlace_del_pdf'];

                                    $facturaVenta->setEnviadoSunat(true);
                                    $facturaVenta->setEnlacepdf($enlace_de_pdf);

                                }


                                $facturaVenta->setCodigoError($leer_respuesta['sunat_responsecode']);
                                $facturaVenta->setMensajeError($leer_respuesta['sunat_description']);
                                $facturaVenta->setEnlaceXml($leer_respuesta['enlace_del_xml']);
                                $facturaVenta->setEnlaceCdr($leer_respuesta['enlace_del_cdr']);
                                
                                $em->persist($facturaVenta);

                            }


                        }
                        else
                        {


                            if(isset($leer_respuesta['errors']))
                            {

                                $nferror  = $em->getRepository('AppBundle:NubefactError')->findOneBy(array('codigo'=>$leer_respuesta['codigo']));
                                $msj_error = ($nferror) ? $nferror->getDescripcion() : '';  

                                $facturaVentaNubefactError = new FacturaVentaNubefactError();
                                $facturaVentaNubefactError->setFacturaVenta($facturaVenta);
                                $facturaVentaNubefactError->setError($leer_respuesta['errors']);
                                $facturaVentaNubefactError->setSunatResponsecode($leer_respuesta['codigo']);
                                $facturaVentaNubefactError->setSunatDescription($msj_error);

                                $em->persist($facturaVentaNubefactError);

                                try {

                                    $em->flush();
                                    
                                } catch (\Exception $e) {
                                    
                                    return $e;
                                }
                                                  
                            }
                            else
                            {

                                $enlace_de_pdf = '';
                                if($leer_respuesta['aceptada_por_sunat'] != 'true')
                                {

                                    $enlace_de_pdf = ($leer_respuesta['enlace']) ? $leer_respuesta['enlace'].'.pdf' : $leer_respuesta['enlace_del_pdf'];

                                    if($facturaVenta->getDocumento() == 'factura')
                                    {
                                        $facturaVenta->setEnviadoSunat(false);
                                    }
                                    else
                                    {
                                        $facturaVenta->setEnviadoSunat(true);
                                    }
                                   
                                }
                                else
                                {
                                    $enlace_de_pdf = $leer_respuesta['enlace_del_pdf'];

                                    $facturaVenta->setEnviadoSunat(true);
                                    
                                }

                                //Si no se genera el PDF, volvemos a consultar el documento en nubefact
                                if($enlace_de_pdf == '.pdf' || is_null($enlace_de_pdf) || $enlace_de_pdf == '')
                                {
                                    $url_facturacion_electronica   = $facturaVenta->getLocal()->getUrlFacturacion();
                                    $token_facturacion_electronica = $facturaVenta->getLocal()->getTokenFacturacion();

                                    $data_json_reenvio      = $this->container->get('AppBundle\Util\Util')->generarConsultaArchivoJson($facturaVenta);
                                    $respuesta_reenvio      = $this->container->get('AppBundle\Util\Util')->enviarConsultaArchivoJson($data_json_reenvio,$url_facturacion_electronica,$token_facturacion_electronica);
                                    $leer_respuesta_reenvio = json_decode($respuesta_reenvio, true);

                                    //dump($leer_respuesta_reenvio);

                                    if(!isset($leer_respuesta_reenvio['errors']))
                                    {
                                        $enlace_de_pdf = ($leer_respuesta_reenvio['enlace_del_pdf']) ? $leer_respuesta_reenvio['enlace_del_pdf'] : $leer_respuesta_reenvio['enlace'].'.pdf';
                                        
                                    }

                                }
                                //Fin de consulta

                                $facturaVenta->setCodigoError($leer_respuesta['sunat_responsecode']);
                                $facturaVenta->setMensajeError($leer_respuesta['sunat_description']);
                                $facturaVenta->setEnlaceXml($leer_respuesta['enlace_del_xml']);
                                $facturaVenta->setEnlaceCdr($leer_respuesta['enlace_del_cdr']);                                

                            
                                $ruta_pdf = $this->generarPDF($request,$facturaVenta,$localObj,$empresa);

                                $facturaVenta->setEnlacepdf($enlace_de_pdf);
                                $facturaVenta->setEnlacePdfFerretero($ruta_pdf);

                                $em->persist($facturaVenta);

                            }




                        }



                    }
                    else
                    {
                        $ruta_pdf = $this->generarPDF($request,$facturaVenta,$localObj,$empresa);

                        $facturaVenta->setEnlacePdfFerretero($ruta_pdf);                        
                        $facturaVenta->setEnviadoSunat(false);
                        $em->persist($facturaVenta);

                    }                        

                }
                else
                {
                    $facturaVenta->setEnviadoSunat(false);
                    $em->persist($facturaVenta);
                }    

                if($formapagoCodigo == '5')
                {
                    $monto_a_cuenta = $facturaVenta->getVenta()->getVentaFormaPago()[0]->getMontoACuenta();
                    $monto_en_caja = $monto_a_cuenta;
                    $this->container->get('AppBundle\Util\Util')->registrarCajaAperturaDetalle($caja_apertura_id,$monto_a_cuenta,'venta',$facturaVenta->getId());

                }

                //Registrar en el detalle de caja.
                if($formapagoCodigo != '5')
                {
                    $monto_en_caja = $total;
                    if(!$facturaVenta->getIncluirIgv()){
                        $total += $total * 0.18;
                    }                    
                    $this->container->get('AppBundle\Util\Util')->registrarCajaAperturaDetalle($caja_apertura_id,$total,'venta',$facturaVenta->getId());

                }          
                
                if($formapagoCodigo == '2')
                {
                    $monto_en_caja = 0;
                }
                if($formapagoCodigo == '4')
                {
                    $monto_en_caja = 0;
                }



            }else{

                $facturaVenta->setEnviadoSunat(false);
                $em->persist($facturaVenta);

            }




            try {
                $em->flush();

                if($request->request->get('btn_vender') == 'SI'){
                    $this->addFlash("success", "La venta fue realizada exitosamente. El registro fue movido a la Lista de ventas.");
                }else{
                    $this->addFlash("success", "El registro fue editado exitosamente."); 
                }
                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
                
            }   

            return $this->redirectToRoute('proforma_index');
        }

        return $this->render('proforma/edit.html.twig', array(
            'edit_form'         => $editForm->createView(),
            'formClientePv'     => $formClientePv->createView(),
            //'formSeleccionLocal'=> $formSeleccionLocal->createView(),
            'local'             => $local,
            'localObj'          => $localObj,
            'productoXLocals'   => $productoXLocals,
            'categorias'        => $categorias,
            'facturaVenta'      => $facturaVenta,
            'titulo'            => 'Editar proforma',
        ));
    }


    /**
     * genera PDF de proforma para imprimir
     *
     * @Route("/{id}/imprimir", name="proforma_imprimir")
     * @Method("GET")
     */
    public function imprimirAction(Request $request,FacturaVenta $facturaVenta)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);
        $empresaObj       = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $base_dir = $this->get('kernel')->getRootDir() . '/../web' . $request->getBasePath();

        $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
        $dql .= " JOIN cxd.documento d";
        $dql .= " JOIN d.local l";
        $dql .= " WHERE  l.id =:local  AND d.codigo = '06' AND cxd.estado = 1 ";

        $query = $em->createQuery($dql);
        $query->setParameter('local',$local);         
        $componentesXDocumento =  $query->getResult();

        $cuentasBancarias = $em->getRepository('AppBundle:CuentaBanco')->findBy(array('estado'=>true,'empresa'=>$empresa),array('banco'=>'ASC'));

        if($empresaObj->getProformaOrientacion() == 'Landscape'){

            $html = $this->render('proforma/imprimir.html.twig', array(
                    'facturaVenta'  => $facturaVenta,
                    'componentesXDocumento'  => $componentesXDocumento,
                    'localObj'      => $localObj,
                    'host'  => $request->getHost(),
                    'base_dir' => $base_dir,
                    'cuentasBancarias' => $cuentasBancarias
                ))->getContent();

            $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> $empresaObj->getProformaFormato(),'orientation' => $empresaObj->getProformaOrientacion(),'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

            $f = 'proforma_'.$facturaVenta->getNumeroProforma();

            return new \Symfony\Component\HttpFoundation\Response(
                    $pdf, 200, array(
                        'Content-Type'          => 'application/pdf',
                        'Content-Disposition'   => 'inline; filename="'.$f.'.pdf"',                     
            ));

        }
        else{


            $html = $this->render('proforma/imprimir2.html.twig', array(
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
     * Entrega de productos
     *
     * @Route("/{id}/entrega", name="proforma_entrega")
     * @Method({"GET", "POST"})
     */
    public function entregaAction(Request $request,FacturaVenta $facturaVenta)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $em = $this->getDoctrine()->getManager();

        $formTransporte = $this->createForm('AppBundle\Form\DetalleVentaEntregaDatosEnvioType', null,array('empresa'=>$empresa));

        $formEmpresaTransporte = $this->createForm('AppBundle\Form\TransporteType',null,array('empresa'=>$empresa));


        $dql = "SELECT dve FROM AppBundle:DetalleVentaEntrega dve ";
        $dql .= " JOIN dve.detalleVenta dv";
        $dql .= " JOIN dv.venta v";
        $dql .= " JOIN v.facturaVenta fv";
        $dql .= " WHERE  fv.id =:id  ";
        $dql .= " ORDER BY dve.identificador ";

        $query = $em->createQuery($dql);

        $query->setParameter('id',$facturaVenta->getId());         
 
        $entregas =  $query->getResult();            

        return $this->render('proforma/entrega.html.twig', array(
            'facturaVenta' => $facturaVenta,
            'entregas' => $entregas,
            'titulo'   => 'Entrega de productos',
            'formTransporte'        => $formTransporte->createView(),
            'formEmpresaTransporte' => $formEmpresaTransporte->createView(),            
        ));
    }


    /**
     * Se procesa la entrega de los productos
     *
     * @Route("/entrega/procesar", name="proforma_entrega_procesar")
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

        $facturaventa_id = $request->request->get('proforma_id');

        $facturaVenta = $em->getRepository('AppBundle:FacturaVenta')->find($facturaventa_id);

        $saldoTotal = 0;
        $i = 0;
        foreach($facturaVenta->getVenta()->getDetalleVenta() as $detalleVenta){

            if($detalleVenta->getCantidad() != $detalleVenta->getCantidadEntregada()){

                $cantidadEntregada = 0;
                $cantidadDetalle = $detalleVenta->getCantidad();
                
                if($request->request->get('select_'.$detalleVenta->getId()) !== null ){

                    $cantidadEntregada = $request->request->get('cantidad_'.$detalleVenta->getId());

                    //$this->container->get('AppBundle\Util\Util')->disminuirAlmacen($detalleVenta->getProductoXLocal()->getId(),$cantidadEntregada);

                    $cantidadDetalleEntregada = $cantidadEntregada;
                    $cantidadEntregada = ($detalleVenta->getCantidadEntregada())?$detalleVenta->getCantidadEntregada() + $cantidadEntregada : $cantidadEntregada;

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

        }

        if($saldoTotal == 0){

            $facturaVenta->getVenta()->setCondicion(true);
            $em->persist($facturaVenta);
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
            return $this->redirectToRoute('proforma_index');                
        }


        return $this->redirectToRoute('proforma_entrega_pdf', array('id' => $facturaVenta->getId(),'identificador'=>$timestamp));

    }

    /**
     * Se procesa la entrega de los productos
     *
     * @Route("/{id}/entrega/{identificador}/pdf", name="proforma_entrega_pdf")
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

        $localObj = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        $dql = "SELECT dve FROM AppBundle:DetalleVentaEntrega dve ";
        $dql .= " JOIN dve.detalleVenta dv";
        $dql .= " JOIN dv.venta v";
        $dql .= " JOIN v.facturaVenta fv";
        $dql .= " WHERE  fv.id =:id  ";
        $dql .= " AND dve.identificador =:identificador  ";

        $query = $em->createQuery($dql);

        $query->setParameter('id',$facturaVenta->getId());
        $query->setParameter('identificador',$identificador);        

        $detalleVentaEntrega =  $query->getResult();      


        $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
        $dql .= " JOIN cxd.documento d";
        $dql .= " JOIN d.local l";
        $dql .= " WHERE  l.id =:local  AND d.codigo = '04' AND cxd.estado = 1 ";

        $query = $em->createQuery($dql);

        $query->setParameter('local',$local);         
 
        $componentesXDocumento =  $query->getResult();

        $html = $this->render('proforma/guiaentrega.html.twig', array(
                'facturaVenta' => $facturaVenta,
                'detalleVentaEntrega' => $detalleVentaEntrega,
                'componentesXDocumento' => $componentesXDocumento,
                'localObj'  => $localObj,
                'host' => $host
            ))->getContent();

        $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 8,'margin-top' => 3,'margin-bottom' => 3));

        $f = 'proformaentrega_'.$facturaVenta->getNumeroProforma();

        return new \Symfony\Component\HttpFoundation\Response(
                $pdf, 200, array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'inline; filename="'.$f.'.pdf"',
                     
        ));

    }

    /**
     * genera PDF de proforma para imprimir
     *
     * @Route("/{id}/imprimirentrega/{identificador}/parcial", name="proforma_imprimir_entregaparcial")
     * @Method("GET")
     */
    public function imprimirEntregaparcialAction(Request $request,FacturaVenta $facturaVenta,$identificador)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        $detalleVentaEntrega = $em->getRepository('AppBundle:DetalleVentaEntrega')->findBy(array('identificador'=>$identificador));

        $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
        $dql .= " JOIN cxd.documento d";
        $dql .= " JOIN d.local l";
        $dql .= " WHERE  l.id =:local  AND d.codigo = '04' AND cxd.estado = 1 ";

        $query = $em->createQuery($dql);
        $query->setParameter('local',$local);         
        $componentesXDocumento =  $query->getResult();

        $html = $this->render('proforma/imprimirEntregaparcial.html.twig', array(
                'facturaVenta'  => $facturaVenta,
                'detalleVentaEntrega'=> $detalleVentaEntrega,
                'componentesXDocumento' => $componentesXDocumento,
                'localObj'              => $localObj,
                'host'                  => $request->getHost(),
                'identificador'         => $identificador,
            ))->getContent();

        $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 8,'margin-top' => 3,'margin-bottom' => 3));

        $f = 'proformaentrega_'.$identificador;

        return new \Symfony\Component\HttpFoundation\Response(
                $pdf, 200, array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'inline; filename="'.$f.'.pdf"',                     
        ));
    }

    /**
     * genera PDF de proforma para imprimir
     *
     * @Route("/{id}/imprimirguiaremision/{identificador}/parcial", name="proforma_imprimir_guiaremision_parcial")
     * @Method("GET")
     */
    public function imprimirGuiaremisionParcialAction(Request $request,FacturaVenta $facturaVenta,$identificador)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        $detalleVentaEntrega           = $em->getRepository('AppBundle:DetalleVentaEntrega')->findBy(array('identificador'=>$identificador));
        $detalleVentaEntregaDatosEnvio = $em->getRepository('AppBundle:DetalleVentaEntregaDatosEnvio')->findOneBy(array('identificador'=>$identificador));

        $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
        $dql .= " JOIN cxd.documento d";
        $dql .= " JOIN d.local l";
        $dql .= " WHERE  l.id =:local  AND d.codigo = '02' AND cxd.estado = 1 ";

        $query = $em->createQuery($dql);
        $query->setParameter('local',$local);         
        $componentesXDocumento =  $query->getResult();

        $html = $this->render('proforma/imprimirGuiaremisionParcial.html.twig', array(
                'facturaVenta'              => $facturaVenta,
                'detalleVentaEntrega'=> $detalleVentaEntrega,
                'detalleVentaEntregaDatosEnvio'=> $detalleVentaEntregaDatosEnvio,
                'componentesXDocumento' => $componentesXDocumento,
                'localObj'              => $localObj,
                'identificador'         => $identificador,
            ))->getContent();

        $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

        $f = 'guiaremision_'.$identificador;

        return new \Symfony\Component\HttpFoundation\Response(
                $pdf, 200, array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'inline; filename="'.$f.'.pdf"',                     
        ));


    }


    private function generarPDF($request,$facturaVenta,$localObj,$empresa)
    {
        $em = $this->getDoctrine()->getManager();

        $host   = $request->getHost();

        $ruta_pdf = null;

        if($facturaVenta->getDocumento() == 'boleta')
        {

            $f = $this->generateUniqueFileName().'.pdf';
            $ruta_pdf = $request->getSchemeAndHttpHost().'/uploads/files/'.$empresa.'/'.$f;

            switch ($localObj->getBoletaFormato()) {
                case 'A4':

                    $html = $this->render('facturaventa/boletaElectronicaA4.html.twig', array(
                            'facturaVenta' => $facturaVenta,
                            'localObj'     => $localObj,
                            'host' => $host
                        ))->getContent();


                    $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f,array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 3,'margin-bottom' => 3));

                break;
                case 'TICKET':

                    $html = $this->render('facturaventa/boletaElectronicaTicket.html.twig', array(
                            'facturaVenta' => $facturaVenta,
                            'localObj'     => $localObj,
                            'host' => $host
                        ))->getContent();


                    $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f,array('header-html'=> null,'footer-html'=> null,'page-height' =>  200,'page-width' => 50,'margin-right' => 2,'margin-left' => 1,'margin-top' => 3,'margin-bottom' => 3));

                break;                
            }


        }
        elseif($facturaVenta->getDocumento() == 'factura')
        {

            $f = $this->generateUniqueFileName().'.pdf';
            $ruta_pdf = $request->getSchemeAndHttpHost().'/uploads/files/'.$empresa.'/'.$f;    

            switch ($localObj->getFacturaFormato()) {
                case 'A4':

                    $html = $this->render('facturaventa/facturaElectronicaA4.html.twig', array(
                            'facturaVenta' => $facturaVenta,
                            'localObj'     => $localObj,
                            'host' => $host
                        ))->getContent();


                    $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f,array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 3,'margin-bottom' => 3));

                break;
                case 'TICKET':

                    $html = $this->render('facturaventa/facturaElectronicaTicket.html.twig', array(
                            'facturaVenta' => $facturaVenta,
                            'localObj'     => $localObj,
                            'host' => $host
                        ))->getContent();


                    $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f,array('header-html'=> null,'footer-html'=> null,'page-height' =>  200,'page-width' => 50,'margin-right' => 2,'margin-left' => 1,'margin-top' => 3,'margin-bottom' => 3));

                break;                
            }


        }
        else
        {
            $f = $this->generateUniqueFileName().'.pdf';
            $ruta_pdf = $request->getSchemeAndHttpHost().'/uploads/files/'.$empresa.'/'.$f;

            switch ($localObj->getNotaventaFormato()) {
                case 'A4':

                    $html = $this->render('facturaventa/showGuiaA4.html.twig', array(
                            'facturaVenta' => $facturaVenta,
                            'localObj'     => $localObj,
                            'host' => $host
                        ))->getContent();
                  

                    $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f,array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 3,'margin-bottom' => 3));                    

                    break;
               
                case 'TICKET':


                    $html = $this->render('facturaventa/showGuia.html.twig', array(
                            'facturaVenta' => $facturaVenta,
                            'localObj'     => $localObj,
                            'host' => $host
                        ))->getContent();


                    $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f,array('header-html'=> null,'footer-html'=> null,'page-height' =>  200,'page-width' => 50,'margin-right' => 2,'margin-left' => 1,'margin-top' => 3,'margin-bottom' => 3));               

                    break;

                default:

                    $html = $this->render('facturaventa/showGuia.html.twig', array(
                            'facturaVenta' => $facturaVenta,
                            'localObj'     => $localObj,
                            'host' => $host
                        ))->getContent();


                    $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f,array('header-html'=> null,'footer-html'=> null,'page-height' =>  200,'page-width' => 50,'margin-right' => 2,'margin-left' => 1,'margin-top' => 3,'margin-bottom' => 3));


                    break;

            }


        }        


        return $ruta_pdf;


    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }


}
