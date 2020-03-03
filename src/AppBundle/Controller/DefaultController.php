<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\TipoCambio;
use AppBundle\Entity\CajaApertura;
use AppBundle\Entity\FacturaVentaNubefactError;
use AppBundle\NumeroALetras\NumeroALetras;
use AppBundle\Entity\FacturaVenta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class DefaultController extends Controller
{
    /**
     * @Route("/dashboard", name="dashboard")
     * @Security("has_role('ROLE_ADMIN')")
     *
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $local_param    = '';

        //Filtro local
        $options = array('empresa'=>$empresa);
        $form = $this->createFormBuilder($options)
            ->add('local', EntityType::class,array(
                'class'     => 'AppBundle:EmpresaLocal',
                'attr'      => array('class' => 'form-control mb-2 mr-sm-2'),
                'label'     => 'Local',
                'label_attr'=> array('class'=>'sr-only'),
                'required'  => false,
                'placeholder'  => 'Todos los locales',
                'query_builder' => function(EntityRepository $er) use ($options)
                {
                    $qb = $er->createQueryBuilder('el');
                    $qb->leftJoin('el.empresa','e');
                    $qb->where('el.estado = 1');

                    if($options['empresa'] != ''){
                        $qb->andWhere('e.id ='.$options['empresa']);
                    }
                    
                    return $qb;
                }                   
                ))
            ->getForm();

        //var_dump($request->request->get('form')['local']);
        //die();

        if(null !== $request->request->get('form')['local'] &&  $request->request->get('form')['local'] != ''){
            $local_param    = $request->request->get('form')['local'];
        }

        $ventas_x_dia       = array();
        $compras_x_dia      = array();
        $gastos_x_dia       = array();
        $ganancias_x_dia    = array();
        $dias               = array();

        for($i = 30;$i>=0;$i--){
            $ventas_x_dia[]  = ($this->container->get('AppBundle\Util\Util')->obtenerVentaXDia($empresa,$i,$local_param))?(float)$this->container->get('AppBundle\Util\Util')->obtenerVentaXDia($empresa,$i,$local_param):0;
            $compras_x_dia[] = ($this->container->get('AppBundle\Util\Util')->obtenerCompraXDia($empresa,$i,$local_param))?(float)$this->container->get('AppBundle\Util\Util')->obtenerCompraXDia($empresa,$i,$local_param):0;
            $gastos_x_dia[]  = ($this->container->get('AppBundle\Util\Util')->obtenerGastoXDia($empresa,$i,$local_param))?(float)$this->container->get('AppBundle\Util\Util')->obtenerGastoXDia($empresa,$i,$local_param):0;

            $ganancias_x_dia[] = end($ventas_x_dia) - (end($compras_x_dia) + end($gastos_x_dia));

            $dias[] = date('d/m/Y', strtotime('today - '.$i.' days'));
        }

        //dump($ventas_x_dia);
        //die();

        $ventas_x_mes       = array();
        $compras_x_mes      = array();
        $gastos_x_mes       = array();
        $ganancias_x_mes    = array();
        $meses              = array();

        for($j = 12;$j>=0;$j--){
            $ventas_x_mes[]  = ($this->container->get('AppBundle\Util\Util')->obtenerVentaXMes($empresa,$j,$local_param))?(float)$this->container->get('AppBundle\Util\Util')->obtenerVentaXMes($empresa,$j,$local_param):0;
            $compras_x_mes[] = ($this->container->get('AppBundle\Util\Util')->obtenerCompraXMes($empresa,$j,$local_param))?(float)$this->container->get('AppBundle\Util\Util')->obtenerCompraXMes($empresa,$j,$local_param):0;
            $gastos_x_mes[]  = ($this->container->get('AppBundle\Util\Util')->obtenerGastoXMes($empresa,$j,$local_param))?(float)$this->container->get('AppBundle\Util\Util')->obtenerGastoXMes($empresa,$j,$local_param):0;

            $ganancias_x_mes[] = end($ventas_x_mes) - (end($compras_x_mes) + end($gastos_x_mes));

            //$meses[] = date('m/Y', strtotime('today - '.$j.' month'));
            $meses[] = date("m/Y", strtotime( date( 'Y-m-01' )." -$j months"));
        }

        //Facturas no enviadas

        $dql = "SELECT fv FROM AppBundle:FacturaVenta fv ";
        $dql .= " JOIN fv.venta v";
        $dql .= " JOIN fv.local l";
        $dql .= " JOIN l.empresa em";
        $dql .= " WHERE  v.estado = 1 AND fv.tipo = 2 AND fv.enviadoSunat = 0  AND fv.documento IN ('factura') ";
        $dql .= " AND  l.facturacionElectronica = 1 AND fv.emisionElectronica = 1 AND em.id =:empresa ";
        $dql .= " ORDER BY fv.fecha DESC ";

        $query = $em->createQuery($dql);

        $query->setParameter('empresa',$empresa);

        $facturas_no_enviadas =  $query->getResult();

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'titulo' => 'Bienvenido',
            'ventas_x_dia' => implode(",", $ventas_x_dia),
            'compras_x_dia' => implode(",", $compras_x_dia),
            'gastos_x_dia' => implode(",", $gastos_x_dia),
            'ganancias_x_dia' => implode(",", $ganancias_x_dia),
            'dias' => $dias,
            'ventas_x_mes' => implode(",", $ventas_x_mes),
            'compras_x_mes' => implode(",", $compras_x_mes),
            'gastos_x_mes' => implode(",", $gastos_x_mes),
            'ganancias_x_mes' => implode(",", $ganancias_x_mes),
            'meses' => $meses,            
            'empresa' => $empresa,
            'form'   => $form->createView(),
            'local_param' => $local_param,
            'facturas_no_enviadas'  => $facturas_no_enviadas

        ]);


    }

    /**
     * @Route("/seleccionar/local", name="seleccionar_local")
     * @Security("has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO') or has_role('ROLE_ADMIN') ")
     */
    public function seleccionarLocalAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $caja_apertura  = $session->get('caja_apertura');

        $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        $form = $this->createForm('AppBundle\Form\SeleccionarLocalType', null,array('empresa'=>$empresa,'local'=>$local));

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $formLocal = $form->getData()['local'];
            $formCaja  = $form->getData()['caja'];

            
            $session->set('local',$formLocal->getId());
            $session->set('caja',$formCaja->getId());


            $this->addFlash("success", "El local y caja fue seleccionado exitosamente.");


            switch ($session->get('rol')) {
                case 'Administrador':

                    $estado_caja = $this->container->get('AppBundle\Util\Util')->verificaEstadoCaja($session->get('caja'));
                    if(!$estado_caja){
                        return $this->redirectToRoute('aperturar_caja');
                    }
                    return $this->redirectToRoute('detalleventa_puntoventa');
                    break;
                case 'Almacenero':
                    // $estado_caja = $this->container->get('AppBundle\Util\Util')->verificaEstadoCaja($session->get('caja'));
                    // if(!$estado_caja){
                    //     return $this->redirectToRoute('aperturar_caja');
                    // }                
                    return $this->redirectToRoute('productoxlocal_index');
                    break;
                case 'Vendedor':
                    // $estado_caja = $this->container->get('AppBundle\Util\Util')->verificaEstadoCaja($session->get('caja'));
                    // if(!$estado_caja){
                    //     return $this->redirectToRoute('aperturar_caja');
                    // }                
                    return $this->redirectToRoute('almacen_productosxlocal');
                    break;                                            
                default:
                    // $estado_caja = $this->container->get('AppBundle\Util\Util')->verificaEstadoCaja($session->get('caja'));
                    // if(!$estado_caja){
                    //     return $this->redirectToRoute('aperturar_caja');
                    // }                
                    return $this->redirectToRoute('almacen_productosxlocal');
                    break;
            }            

        }

        return $this->render('default/seleccionarlocal.html.twig', [
            'titulo' => '',
            'form'   => $form->createView(),
        ]);


    }

    /**
     * @Route("/aperturar/caja", name="aperturar_caja")
     * @Security("has_role('ROLE_VENDEDOR') or has_role('ROLE_ALMACENERO') ")
     */
    public function aperturarCajaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $caja           = $session->get('caja');

        $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);
        $cajaObj        = $em->getRepository('AppBundle:Caja')->find($caja);

        $fecha = new \DateTime();

        $montoAnterior = ($cajaObj->getMontoAnterior()) ? $cajaObj->getMontoAnterior() : 0;

        $cajaApertura = new CajaApertura();
        $form = $this->createForm('AppBundle\Form\CajaAperturaType',$cajaApertura,array('caja'=>$caja));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $cajaApertura->setFecha($fecha);
            $cajaApertura->getCaja()->setCondicion('abierto');
            $em->persist($cajaApertura);

            try {

                $em->flush();

                $session->set('caja_apertura',$cajaApertura->getId());

                $this->addFlash("success", "La caja fue aperturada exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, la apertura no se realizó.");  
                
            }


            switch ($session->get('rol')) {
                case 'Administrador':
                    return $this->redirectToRoute('detalleventa_puntoventa');
                    break;
                case 'Almacenero':
                    return $this->redirectToRoute('productoxlocal_index');
                    break;
                case 'Vendedor':
                    return $this->redirectToRoute('almacen_productosxlocal');
                    break;                                            
                default:
                    return $this->redirectToRoute('almacen_productosxlocal');
                    break;
            } 

                      

        }

        return $this->render('default/aperturarcaja.html.twig', [
            'caja' => $cajaObj,
            'form' => $form->createView(),
            'montoAnterior' => $montoAnterior,
            'titulo'    => ''
        ]);


    }


    public function imprimirTicketAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $factura_id = $request->query->get('factura_id');

        $facturaVenta  = $em->getRepository('AppBundle:FacturaVenta')->find($factura_id);

        $i = 0;
        foreach($facturaVenta->getVenta()->getDetalleVenta() as $detalleVenta){

            if($i == 0){
                $localObj = $detalleVenta->getProductoXLocal()->getLocal();
                break;
            }
            

            $i++;
        }

        $ticket_html = '';

        $ticket_html .= '<style type="text/css">';
     
        $ticket_html .= '
            body, td, th {
                font-size: 12px;
                font-family: Calibri;
            }  
            table {
                display: table;
                border-collapse: separate;
                border-spacing: 1px;
                border-color: grey;                
            }

            .col-4 {
                -webkit-box-flex: 0;
                -ms-flex: 0 0 15%;
                flex: 0 0 15%;
                max-width: 15%;
            }

            .table {
                width: 100%;
                max-width: 100%;
                margin-bottom: 1rem;
                background-color: transparent;
            }

            .h3, h3 {
                font-size: 1.75rem;
                font-family: inherit;
                font-weight: 500;
                line-height: 1.2;
                color: inherit;                
            }

            .h6, h6 {
                font-size: 1rem;
                font-family: inherit;
                font-weight: 500;
                line-height: 1.2;
                color: inherit;                    
            }            

            .text-center {
                text-align: center !important;
            }';            
        $ticket_html .= '</style>';

        $ticket_html .= '<div class="col-4 ">';
        $ticket_html .= '    <table class="table" border="0" width="40">';
        $ticket_html .= '        <tr class="h3 text-center" align="center"><td colspan="4" ><h3>'.$localObj->getNombre().'</h3></td></tr>';
        $ticket_html .= '        <tr height="10" class="text-center" align="center"><td colspan="4">'.$localObj->getDireccion().'</td></tr>';
        $ticket_html .= '        <tr height="10" class="text-center"><td colspan="4"><center>Tel: '.$localObj->getTelefono().'</center></td></tr>';
        $ticket_html .= '        <tr><td colspan="4"><center>RUC : '.$localObj->getEmpresa()->getRuc().'</center></td></tr>';

        $ticket_html .= '        <tr height="25"><th>TICKET:</th><td>'.$facturaVenta->getTicket().'</td><th>  FECHA:</th><td>'.$facturaVenta->getFecha()->format('d/m/Y H:i:s').'</td></tr>';
        $ticket_html .= '        <tr height="25"><td>CLIENTE:</td><td colspan="3">'.$facturaVenta->getClienteNombre().'</td></tr>';
        $ticket_html .= '        <tr height="25"><td>CAJERO:</td><td colspan="3">'.$facturaVenta->getVenta()->getEmpleado().'</td></tr>';
        $ticket_html .= '        <tr>';
        $ticket_html .= '            <td colspan="4" >';
        $ticket_html .= '                =================================================';
        $ticket_html .= '                <table class="table table-striped">';
        $ticket_html .= '                    <thead>';
        $ticket_html .= '                        <tr height="25" class="text-center">';
        $ticket_html .= '                            <th>Producto</th>';
        $ticket_html .= '                            <th>Descripción</th>';
        $ticket_html .= '                            <th>Cantidad</th>';
        $ticket_html .= '                            <th>Importe</th>';
        $ticket_html .= '                        </tr>';
        $ticket_html .= '                    </thead>';
        $ticket_html .= '                    <tbody>';
                                $total = 0;
                                $impuesto = 0;
                                foreach($facturaVenta->getVenta()->getDetalleVenta() as $detalle){

        $ticket_html .= '                            <tr class="text-center">';
        $ticket_html .= '                                <td>'.$detalle->getProductoXLocal()->getProducto()->getNombre().'</td>';
                                                         $descripcion = '';
                                                         if ($detalle->getDescripcion()){
                                                            $descripcion = $detalle->getDescripcion();
                                                         }
                                                        
        $ticket_html .= '                                <td>'.$descripcion.'</td>';
        $ticket_html .= '                                <td>'.$detalle->getCantidad().'</td>';
        $ticket_html .= '                                <td>'.$detalle->getSubtotal().'</td>';
        $ticket_html .= '                            </tr>';   
                                    $total = $total + $detalle->getSubtotal();
                                    $impuesto = $impuesto +  0.18*$detalle->getSubtotal()/1.18;
                                }
        $ticket_html .= '                    </tbody>';
        $ticket_html .= '                    <tfoot>';

        // $ticket_html .= '                        <tr height="25">';
        // $ticket_html .= '                            <th></th>';
        // $ticket_html .= '                            <th></th>';
        // $ticket_html .= '                            <th>Sub Total</th>';
        // $ticket_html .= '                            <th>'.number_format($total).'</th>' ;                          
        // $ticket_html .= '                        </tr>';
        $ticket_html .= '                        <tr height="25">';
        $ticket_html .= '                            <th></th>';
        $ticket_html .= '                            <th></th>';
        $ticket_html .= '                            <th>IGV</th>';
        $ticket_html .= '                            <th>'.number_format($impuesto).'</th>' ;                           
        $ticket_html .= '                        </tr>' ;
        $ticket_html .= '                        <tr height="25">';
        $ticket_html .= '                            <th></th>';
        $ticket_html .= '                            <th ></th>';
        $ticket_html .= '                            <th>TOTAL</th>';
        $ticket_html .= '                            <th>'.number_format($total).'</th>';                           
        $ticket_html .= '                        </tr>';
        $ticket_html .= '                    </tfoot>';
                         
        $ticket_html .= '                </table>';
        $ticket_html .= '                =================================================';
        $ticket_html .= '            </td>';
        $ticket_html .= '        </tr>';

        $ticket_html .= '    </table>';
        // $ticket_html .= '</div>';

        foreach($facturaVenta->getVenta()->getVentaFormaPago() as $ventaFormaPago){

            if($ventaFormaPago->getFormaPago()->getId() == 5){

                $montoACuenta   = ($ventaFormaPago->getMontoACuenta())?$ventaFormaPago->getMontoACuenta():0;
                $pagoTotal      = ($ventaFormaPago->getCantidad())?$ventaFormaPago->getCantidad():0;;
                $saldo          = $pagoTotal - $montoACuenta;

                $ticket_html .= '    <p class="text-center ">';
                $ticket_html .= '        A CUENTA: '.$montoACuenta.' ---  SALDO: '.$saldo;
                $ticket_html .= '    </p>';

            }


        }



        // $ticket_html .= '<div class="col-4 ">';
            
        $ticket_html .= '    <p class="text-center ">';
        $ticket_html .= '        **GRACIAS POR SU COMPRA**';
        $ticket_html .= '    </p>';
        $ticket_html .= '    <p class="text-center ">';
        $ticket_html .= '        Este es un comprobante interno sin valor legal,por favor canjearlo por una boleta o factura';
        $ticket_html .= '    </p>';

        $ticket_html .= '</div>';

        $response = new Response(
            $ticket_html,
            Response::HTTP_OK,
            array('content-type' => 'text/html')
        );

        return $response;


    }


    public function establecerTipoDeCambioAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $empresas     = $em->getRepository('AppBundle:Empresa')->findBy(array('estado'=>true));

        $fecha = new \Datetime();

        $sUrl = "http://www.sunat.gob.pe/cl-at-ittipcam/tcS01Alias";
        $sContent = file_get_contents($sUrl);
        $doc = new \DOMDocument();

        // set error level
        $internalErrors = libxml_use_internal_errors(true);

        $doc->loadHTML($sContent);

        // Restore error level
        libxml_use_internal_errors($internalErrors);        


        $xpath = new \DOMXPath($doc);
        $tablaTC = $xpath->query("//table[@class='class=\"form-table\"']"); //obtenemos la tabla TC
        $filas = [];

        foreach($tablaTC as $fila){
            $filas = $fila->getElementsByTagName("tr"); //obtiene todas las tr de la tabla de TC
        }

        $tcs = array(); //array de tcs, por dia como clave

        foreach($filas as $fila){//recorremos cada tr
            $tds = [];
            $tds = $fila->getElementsByTagName("td");
            $i = 0;
            $j = 0;
            $arr = [];
            $dia = "";
            foreach($tds as $td){//recorremos cada td
                if($j == 3){
                    $j = 0;
                    $arr = [];
                }
                if($j == 0){
                    $dia = trim(preg_replace("/[\r\n]+/", " ", $td->nodeValue));
                    $tcs[$dia] = [];
                }
                if($j > 0 && $j < 3){
                    $tcs[$dia][] = trim(preg_replace("/[\r\n]+/", " ", $td->nodeValue));
                }
                $j++;
            }
        }

        foreach($empresas as $empresa){

            $tipoCambio = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('fecha'=>$fecha,'empresa'=>$empresa));

            if(!$tipoCambio){
                $tipoCambio = new TipoCambio();
            }


            $j = 1;
            foreach($tcs as $i=>$tc){

                if($j == count($tcs)){                

                    $tipoCambio->setEmpresa($empresa);
                    $tipoCambio->setFecha($fecha);
                    $tipoCambio->setCompra($tc[0]);
                    $tipoCambio->setVenta($tc[1]);
                    $em->persist($tipoCambio);

                    try {

                        $em->flush();

                        

                    } catch (\Exception $e) {

                        return $e;
                    }
                    

                }

                $j++;

            }

        }


        return new Response('Se actualizó el tipo de cambio exitosamente.');        


    }


    public function consultarDocumentosErrorAction(Request $request)
    {
        $em     = $this->getDoctrine()->getManager();
        $conn   = $this->get('database_connection');

        //Vaciamos la tabla factura_venta_nubefact_error
        $sql  = " TRUNCATE TABLE factura_venta_nubefact_error ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        //Consultamos los documentos
        $sql = "SELECT fv.id FROM factura_venta fv
                INNER JOIN empresa_local el ON fv.empresa_local_id = el.id
                INNER JOIN empresa e ON e.id = el.empresa_id                
                WHERE fv.tipo_id = 2 AND fv.documento IN ('boleta','factura') AND el.facturacion_electronica = 1 "; 
                //AND MONTH(fv.fecha) = 5 AND YEAR(fv.fecha) = 2019  AND fv.id = 6253
                //WHERE (fv.validar_nubefact = 0 OR fv.validar_nubefact IS NULL )
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $facturasVenta = $stmt->fetchAll();


        foreach($facturasVenta as $i=>$value)
        {
            $facturaVenta = $em->getRepository('AppBundle:FacturaVenta')->find($value['id']);

            $url   = $facturaVenta->getLocal()->getUrlFacturacion();
            $token = $facturaVenta->getLocal()->getTokenFacturacion();

            if($facturaVenta->getVenta()->getEstado())
            {


                $data_json      = $this->container->get('AppBundle\Util\Util')->generarConsultaArchivoJson($facturaVenta);
                $respuesta      = $this->container->get('AppBundle\Util\Util')->enviarConsultaArchivoJson($data_json,$url,$token);
                $leer_respuesta = json_decode($respuesta, true);

                // dump($leer_respuesta);
                // die();

                if (isset($leer_respuesta['errors']))
                {

                    $facturaVentaNubefactError = new FacturaVentaNubefactError();
                    $facturaVentaNubefactError->setFacturaVenta($facturaVenta);
                    $facturaVentaNubefactError->setError($leer_respuesta['errors']);
                    $em->persist($facturaVentaNubefactError);

                }
                else
                {

                    $facturaVentaNubefactError = $em->getRepository('AppBundle:FacturaVentaNubefactError')->findOneBy(array('facturaVenta'=>$facturaVenta));

                    if($leer_respuesta['sunat_responsecode'] != '0' && $leer_respuesta['sunat_responsecode'] != NULL )
                    {


                        if(!$facturaVentaNubefactError){
                            $facturaVentaNubefactError = new FacturaVentaNubefactError();
                        }
                        
                        $facturaVentaNubefactError->setFacturaVenta($facturaVenta);
                        $facturaVentaNubefactError->setTipoDeComprobante($leer_respuesta['tipo_de_comprobante']);
                        $facturaVentaNubefactError->setSerie($leer_respuesta['serie']);
                        $facturaVentaNubefactError->setNumero($leer_respuesta['numero']);
                        $facturaVentaNubefactError->setAceptadaPorSunat($leer_respuesta['aceptada_por_sunat']);
                        $facturaVentaNubefactError->setSunatDescription($leer_respuesta['sunat_description']);
                        $facturaVentaNubefactError->setSunatResponsecode($leer_respuesta['sunat_responsecode']);
                        $facturaVentaNubefactError->setEnlaceDelPdf($leer_respuesta['enlace_del_pdf']);
                        $facturaVentaNubefactError->setEnlaceDelXml($leer_respuesta['enlace_del_xml']);
                        $facturaVentaNubefactError->setEnlaceDelCdr($leer_respuesta['enlace_del_cdr']);
                        $em->persist($facturaVentaNubefactError);

                    }
                    else
                    {
                        if($facturaVentaNubefactError){
                            $em->remove($facturaVentaNubefactError);
                        }
                        

                        $facturaVenta->setValidarNubefact(true);
                        $em->persist($facturaVenta);
                    }

                }

                try {

                    $em->flush();
                    
                } catch (\Exception $e) 
                {
                    return new Response($e);
                }

            }
            else
            {


                $data_json      = $this->container->get('AppBundle\Util\Util')->generarConsultaAnulacionArchivoJson($facturaVenta);
                $respuesta      = $this->container->get('AppBundle\Util\Util')->enviarConsultaAnulacionArchivoJson($data_json,$url,$token);
                $leer_respuesta = json_decode($respuesta, true);

                if (isset($leer_respuesta['errors']))
                {

                    $facturaVentaNubefactError = new FacturaVentaNubefactError();
                    $facturaVentaNubefactError->setFacturaVenta($facturaVenta);
                    $facturaVentaNubefactError->setError($leer_respuesta['errors']);
                    $em->persist($facturaVentaNubefactError);

                }
                else
                {

                    $facturaVentaNubefactError = $em->getRepository('AppBundle:FacturaVentaNubefactError')->findOneBy(array('facturaVenta'=>$facturaVenta));

                    if($leer_respuesta['sunat_responsecode'] != '0' && $leer_respuesta['sunat_responsecode'] != NULL )
                    {


                        if(!$facturaVentaNubefactError){
                            $facturaVentaNubefactError = new FacturaVentaNubefactError();
                        }
                        
                        $facturaVentaNubefactError->setFacturaVenta($facturaVenta);
                        // $facturaVentaNubefactError->setTipoDeComprobante($leer_respuesta['tipo_de_comprobante']);
                        $facturaVentaNubefactError->setSunatTicketNumero($leer_respuesta['sunat_ticket_numero']);
                        $facturaVentaNubefactError->setNumero($leer_respuesta['numero']);
                        $facturaVentaNubefactError->setAceptadaPorSunat($leer_respuesta['aceptada_por_sunat']);
                        $facturaVentaNubefactError->setSunatDescription($leer_respuesta['sunat_description']);
                        $facturaVentaNubefactError->setSunatResponsecode($leer_respuesta['sunat_responsecode']);
                        $facturaVentaNubefactError->setEnlaceDelPdf($leer_respuesta['enlace_del_pdf']);
                        $facturaVentaNubefactError->setEnlaceDelXml($leer_respuesta['enlace_del_xml']);
                        $facturaVentaNubefactError->setEnlaceDelCdr($leer_respuesta['enlace_del_cdr']);
                        $em->persist($facturaVentaNubefactError);

                    }
                    else
                    {
                        if($facturaVentaNubefactError){
                            $em->remove($facturaVentaNubefactError);
                        }
                        

                        $facturaVenta->setValidarNubefact(true);
                        $em->persist($facturaVenta);
                    }

                }

                try {

                    $em->flush();
                    
                } catch (\Exception $e) 
                {
                    return new Response($e);
                }



            }


        }   


        return new Response('Se identificaron los documentos con error. Ver Tabla : factura_venta_nubefact_error');        


    }


    public function consultarDocumentosNoEnviadosAction(Request $request)
    {
        $em     = $this->getDoctrine()->getManager();

        $dql = " SELECT fv FROM AppBundle:FacturaVenta fv ";
        $dql .= " JOIN fv.venta v";
        $dql .= " JOIN v.empleado e";
        $dql .= " JOIN e.local l";
        $dql .= " JOIN l.empresa em";
        $dql .= " WHERE  v.estado = 1 AND fv.tipo = 2 AND fv.enviadoSunat = 0  AND fv.documento IN ('factura','boleta') ";
        $dql .= " AND  l.facturacionElectronica = 1 ";
        $dql .= " ORDER BY fv.fecha DESC ";

        $query = $em->createQuery($dql);

        $facturas =  $query->getResult();

        if(count($facturas) > 0 )
        {
            $this->enviarFactura();
        }

        return new Response('Se envio un correo de alerta.');     
    }

    private function enviarFactura()
    {
        $em = $this->getDoctrine()->getManager();

        $mensaje = 'ALERTA!! .Existen documentos no enviados con posible error.';
        $correo_remitente = 'soporte@intimedia.net';

        $message = \Swift_Message::newInstance()
                ->setSubject('ALERTA')
                ->setFrom($correo_remitente)
                ->setTo('jpena@intimedia.net')
                ->setContentType('text/html')
                ->setBody($mensaje)
        ;

        $this->get('mailer')->send($message);

        $message = \Swift_Message::newInstance()
                ->setSubject('ALERTA')
                ->setFrom($correo_remitente)
                ->setTo('jsilva@intimedia.net')
                ->setContentType('text/html')
                ->setBody($mensaje)
        ;

        $this->get('mailer')->send($message);
            
        return true;

    }


    public function actualizarDatosFacturaAction(Request $request)
    {
        $em     = $this->getDoctrine()->getManager();
        $conn   = $this->get('database_connection');

        $_username = "jpena";
        $_password = "intimedia09$";

        // Retrieve the security encoder of symfony
        //$factory = $this->get('security.encoder_factory');

        $user_manager = $this->get('fos_user.user_manager');
        $user = $user_manager->findUserByUsername($_username);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));

        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);             

        //Consultamos los documentos
        // $sql = "SELECT fv.id FROM factura_venta fv
        //         INNER JOIN empresa_local el ON fv.empresa_local_id = el.id
        //         INNER JOIN empresa e ON e.id = el.empresa_id                
        //         WHERE fv.ticket = 'BPP1-63' AND fv.tipo_id = 2 AND fv.documento IN ('boleta','factura') AND el.facturacion_electronica = 1 AND fv.emision_electronica = 1 AND fv.enviado_sunat = 0  ";//fv.ticket = 'BPP1-167' AND MONTH(fv.fecha) = 7 AND  fv.enlace_xml IS NULL fv.id = 12977 AND 

        // $stmt = $conn->prepare($sql);
        // $stmt->execute();
        // $facturasVenta = $stmt->fetchAll();


        $dql = "SELECT fv FROM AppBundle:FacturaVenta fv ";
        $dql .= " JOIN fv.local l";
        $dql .= " JOIN l.empresa e";
        $dql .= " WHERE fv.tipo = 2 AND fv.documento IN ('boleta','factura') ";
        $dql .= " AND l.facturacionElectronica = 1 AND fv.emisionElectronica = 1 AND fv.enviadoSunat = 0 ";


        $query = $em->createQuery($dql);

        $facturasVenta =  $query->getResult();   


        foreach($facturasVenta as $facturaVenta)
        {
            //$facturaVenta = $em->getRepository('AppBundle:FacturaVenta')->findOneBy(array('id'=>(int)$value['id']));

            $url   = $facturaVenta->getLocal()->getUrlFacturacion();
            $token = $facturaVenta->getLocal()->getTokenFacturacion();

            if(!$facturaVenta->getVenta())
            {
                continue;
            }

            if($facturaVenta->getVenta()->getEstado())
            {

                $data_json      = $this->container->get('AppBundle\Util\Util')->generarConsultaArchivoJson($facturaVenta);
                $respuesta      = $this->container->get('AppBundle\Util\Util')->enviarConsultaArchivoJson($data_json,$url,$token);
                $leer_respuesta = json_decode($respuesta, true);



                if(!isset($leer_respuesta['errors']))
                {

                    $facturaVenta->setCodigoError($leer_respuesta['sunat_responsecode']);
                    $facturaVenta->setMensajeError($leer_respuesta['sunat_description']);
                    $facturaVenta->setEnlaceXml($leer_respuesta['enlace_del_xml']);
                    $facturaVenta->setEnlaceCdr($leer_respuesta['enlace_del_cdr']);

                    $facturaVenta->setEnviadoSunat($leer_respuesta['aceptada_por_sunat']);

                    $em->persist($facturaVenta);

                    try {

                        $em->flush();

                    } catch (\Exception $e) {

                        // dump($e);
                        // die();

                        // return new Response($e);
                        
                    }

                }

            }
            else
            {

                $data_json      = $this->container->get('AppBundle\Util\Util')->generarConsultaAnulacionArchivoJson($facturaVenta);

                $respuesta      = $this->container->get('AppBundle\Util\Util')->enviarConsultaAnulacionArchivoJson($data_json,$url,$token);

                $leer_respuesta = json_decode($respuesta, true);

                //dump($leer_respuesta);
                //die();

                if(!isset($leer_respuesta['errors']))
                {
                    $facturaVenta->setEnviadoSunat($leer_respuesta['aceptada_por_sunat']);
                    $facturaVenta->setCodigoError($leer_respuesta['sunat_responsecode']);
                    $facturaVenta->setMensajeError($leer_respuesta['sunat_description']);
                    $facturaVenta->setEnlaceXml($leer_respuesta['enlace_del_xml']);
                    $facturaVenta->setEnlaceCdr($leer_respuesta['enlace_del_cdr']);

                    $em->persist($facturaVenta);

                    try {

                        $em->flush();

                    } catch (\Exception $e) {
                        
                    }

                }
                else
                {

                    $data_json      = $this->generarAnulacion($facturaVenta);
                    $respuesta      = $this->enviarAnulacion($data_json,$url,$token);
                    $leer_respuesta = json_decode($respuesta, true);

                    if(!isset($leer_respuesta['errors']))
                    {
                        $facturaVenta->setEnviadoSunat($leer_respuesta['aceptada_por_sunat']);
                        $facturaVenta->setCodigoError($leer_respuesta['sunat_responsecode']);
                        $facturaVenta->setMensajeError($leer_respuesta['sunat_description']);
                        $facturaVenta->setEnlaceXml($leer_respuesta['enlace_del_xml']);
                        $facturaVenta->setEnlaceCdr($leer_respuesta['enlace_del_cdr']);

                        $em->persist($facturaVenta);

                        try {

                            $em->flush();

                        } catch (\Exception $e) {
                            
                        }

                    }


                }

            }

        }   

        return new Response('Se actualizó la informacion de las facturas.');        

    }

    /**
     * Procedemos a reenviar documento a nubefact
     *
     * @Route("/{id}/reenviar/documento/nubefact", name="reenviar_documento_nubefact")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function reenviarDocumentoNubefactAction(Request $request,FacturaVenta $facturaVenta)
    {
        $em = $this->getDoctrine()->getManager();

        $consultar_comprobante = $this->consultarComprobante($facturaVenta);
        $respuesta = $this->enviarComprobante($consultar_comprobante,$facturaVenta->getLocal()->getUrlFacturacion(),$facturaVenta->getLocal()->getTokenFacturacion());
        $leer_respuesta = json_decode($respuesta, true);

        if(isset($leer_respuesta['errors']))
        {
            if($leer_respuesta['codigo'] == 24)
            {
                //El documento no existe, procedemos a reenviarlo a NUBEFACT
                $data_json = $this->container->get('AppBundle\Util\Util')->generarArchivoJson($facturaVenta,$facturaVenta->getLocal()->getId());
                $respuesta = $this->container->get('AppBundle\Util\Util')->enviarArchivoJson($data_json,$facturaVenta->getLocal());
                $leer_respuesta = json_decode($respuesta, true);

                if(isset($leer_respuesta['errors']))
                {

                    $facturaVenta->setCodigoError($leer_respuesta['codigo']);
                    $facturaVenta->setMensajeError($leer_respuesta['errors']);                        

                    $em->persist($facturaVenta);

                    $this->addFlash("danger", $leer_respuesta['errors']);
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
                            $facturaVenta->setEnviadoSunat(true);
                        }

                        
                        $facturaVenta->setEnlacepdf($enlace_de_pdf);


                        $this->addFlash("warning","No se pudo reenviar el documento. Codigo de Error : ".$leer_respuesta['sunat_responsecode']);

                    }
                    else
                    {
                        $enlace_de_pdf = $leer_respuesta['enlace_del_pdf'];

                        $facturaVenta->setEnviadoSunat(true);
                        $facturaVenta->setEnlacepdf($enlace_de_pdf);

                        $this->addFlash("success", "El documento fue reenviado correctamente.");

                    }

                    $facturaVenta->setEnlaceXml($leer_respuesta['enlace_del_xml']);
                    $facturaVenta->setEnlaceCdr($leer_respuesta['enlace_del_cdr']);                        

                    $em->persist($facturaVenta);

                }

                 
            }
            else
            {

                $facturaVenta->setCodigoError($leer_respuesta['codigo']);
                $facturaVenta->setMensajeError($leer_respuesta['errors']);                        

                $em->persist($facturaVenta);

                $this->addFlash("danger", $leer_respuesta['errors']);
            }

            try {
                $em->flush();
            } catch (\Exception $e) {
                $this->addFlash("danger", "Hubo un error, no se pudo terminar el proceso.");
            }                   
            
        }
        else
        {

            if($leer_respuesta['sunat_responsecode'] == '0')
            {

                $facturaVenta->setEnlaceXml($leer_respuesta['enlace_del_xml']);
                $facturaVenta->setEnlaceCdr($leer_respuesta['enlace_del_cdr']);                   
                $facturaVenta->setEnviadoSunat(true);
                $em->persist($facturaVenta);

                $this->addFlash("success", "El documento fue reenviado correctamente : ".$facturaVenta->getTicket());
            }
            else
            {
                $facturaVenta->setEnlaceXml($leer_respuesta['enlace_del_xml']);
                $facturaVenta->setEnlaceCdr($leer_respuesta['enlace_del_cdr']);        
                $facturaVenta->setCodigoError($leer_respuesta['sunat_responsecode']);
                $facturaVenta->setMensajeError($leer_respuesta['sunat_description']);                        

                $em->persist($facturaVenta);

                $this->addFlash("danger", "Codigo de error : ".$leer_respuesta['sunat_responsecode']." .Descripcion : ".$leer_respuesta['sunat_description']);
            }        

            try {
                $em->flush();
                
            } catch (\Exception $e) {
                $this->addFlash("danger", "Hubo un error, no se pudo terminar el proceso.");
            }
                
        }



        return $this->redirectToRoute('dashboard');       

    }

    private function consultarComprobante($facturaVenta)
    {
        $em = $this->getDoctrine()->getManager();


        $partesticket = explode("-", $facturaVenta->getTicket());
        $numero = $partesticket[1];


        switch ($facturaVenta->getDocumento()) {
            case 'factura':
                $serie = $facturaVenta->getLocal()->getSerieFactura();
                $tipo_de_comprobante = 1;
                break;
            case 'boleta':
                $serie = $facturaVenta->getLocal()->getSerieBoleta();
                $tipo_de_comprobante = 2;
                break;            
            default:
                $serie = '';
                $tipo_de_comprobante = '';
                break;
        }


        $data = array(
            "operacion"                         => "consultar_comprobante",
            "tipo_de_comprobante"               => "".$tipo_de_comprobante."",
            "serie"                             => "".$serie."",
            "numero"                            => $numero,
        );
            

        $data_json = json_encode($data);

        return $data_json;
    }

    private function enviarComprobante($data_json,$url,$token)
    {

        //Invocamos el servicio de NUBEFACT
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Token token="'.$token.'"',
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

    private function generarAnulacion($facturaVenta,$msj='ERROR DEL SISTEMA')
    {
        $em = $this->getDoctrine()->getManager();

        $partesticket = explode("-", $facturaVenta->getTicket());
        $numero = $partesticket[1];


        switch ($facturaVenta->getDocumento()) {
            case 'factura':
                $serie = $facturaVenta->getLocal()->getSerieFactura();
                $tipo_de_comprobante = 1;
                break;
            case 'boleta':
                $serie = $facturaVenta->getLocal()->getSerieBoleta();
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


    private function enviarAnulacion($data_json,$url,$token)
    {

        //Invocamos el servicio de NUBEFACT
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Token token="'.$token.'"',
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


    public function actualizarPrecioCostoDetalleVentaAction(Request $request)
    {
        $em     = $this->getDoctrine()->getManager();
        $conn   = $this->get('database_connection');

        $_username = "jpena";
        $_password = "intimedia09$";

        // Retrieve the security encoder of symfony
        //$factory = $this->get('security.encoder_factory');

        $user_manager = $this->get('fos_user.user_manager');
        $user = $user_manager->findUserByUsername($_username);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));

        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);


        //Actualizamos precio costo en detalle_venta

        //$detalles = $em->getRepository('AppBundle:DetalleVenta')->findAll();

        $dql = "SELECT dv FROM AppBundle:DetalleVenta dv ";
        $dql .= " JOIN dv.venta v";
        $dql .= " JOIN v.facturaVenta fv";
        $dql .= " JOIN fv.local l";
        $dql .= " JOIN l.empresa e";
        $dql .= " WHERE  e.id = 25 AND v.estado = 1 ";

        $query = $em->createQuery($dql);

        $detalles =  $query->getResult();

        //dump($detalles);
        //die();


        foreach($detalles as $detalle)
        {
            if($detalle->getProductoXLocal())
            {
                $precio_costo = $this->container->get('AppBundle\Util\Util')->obtenerPrecioCosto($detalle->getProductoXLocal()->getId(),$detalle->getCantidad());
                $detalle->setPrecioCosto($precio_costo);
                $em->persist($detalle);

                try {
                    
                    $em->flush();

                } catch (\Exception $e) {

                    continue;
                    
                }                               
            }

        }

        return new Response('Se actualizó la informacion del precio costo.');        

    }

    public function actualizarStockAction()
    {
        $em     = $this->getDoctrine()->getManager();
        $conn   = $this->get('database_connection');



        // $dql = "SELECT pxl FROM AppBundle:ProductoXLocal pxl ";
        // $dql .= " JOIN pxl.producto p";
        // $dql .= " JOIN pxl.local l";
        // $dql .= " JOIN l.empresa e";
        // $dql .= " WHERE e.id = 1 AND p.estado = 1 ";

        // $query = $em->createQuery($dql);

        // $productos =  $query->getResult();

        $dql = "SELECT p FROM AppBundle:Producto p ";
        $dql .= " JOIN p.empresa e";
        $dql .= " WHERE e.id = 1 AND p.estado = 1 ";

        $query = $em->createQuery($dql);

        $productos =  $query->getResult();

        foreach($productos as $producto)
        {

            $productosXLocal = $producto->getProductoXLocal();

            foreach($productosXLocal as $productoXLocal)
            {

                $producto_x_local_id = $productoXLocal->getId();
                $local_id = $productoXLocal->getLocal()->getId();

                //ventas
                $sql = "SELECT SUM(txp.cantidad) as total FROM transferencia_x_producto txp
                        INNER JOIN transferencia t ON txp.transferencia_id = t.id
                        WHERE t.empresa_id = 1 AND estado = 1 AND motivo_traslado_id = 1 AND txp.producto_x_local_id = ? "; 

                $stmt = $conn->prepare($sql);
                $stmt->bindValue(1, $producto_x_local_id);
                $stmt->execute();
                $venta_total = $stmt->fetchColumn();


                //compras
                $sql = "SELECT SUM(txp.cantidad) as total FROM transferencia_x_producto txp
                        INNER JOIN transferencia t ON txp.transferencia_id = t.id
                        WHERE t.empresa_id = 1 AND estado = 1 AND motivo_traslado_id = 2 AND txp.producto_x_local_id = ? "; 

                $stmt = $conn->prepare($sql);
                $stmt->bindValue(1, $producto_x_local_id);
                $stmt->execute();
                $compra_total = $stmt->fetchColumn();


                //transferencias salidas
                $sql = "SELECT SUM(txp.cantidad) as total FROM transferencia_x_producto txp
                        INNER JOIN transferencia t ON txp.transferencia_id = t.id
                        WHERE t.empresa_id = 1 AND estado = 1 AND motivo_traslado_id = 11 AND txp.producto_x_local_id = ? AND t.local_inicio = ? "; 

                $stmt = $conn->prepare($sql);
                $stmt->bindValue(1, $producto_x_local_id);
                $stmt->bindValue(2, $local_id);
                $stmt->execute();
                $transferencia_salida_total = $stmt->fetchColumn();

                //retiros
                $sql = "SELECT SUM(txp.cantidad) as total FROM transferencia_x_producto txp
                        INNER JOIN transferencia t ON txp.transferencia_id = t.id
                        WHERE t.empresa_id = 1 AND estado = 1 AND motivo_traslado_id = 12 AND txp.producto_x_local_id = ? "; 

                $stmt = $conn->prepare($sql);
                $stmt->bindValue(1, $producto_x_local_id);
                $stmt->execute();
                $retiro_total = $stmt->fetchColumn();


                


            }


            foreach($productosXLocal as $productoXLocal)
            {

                $producto_x_local_id = $productoXLocal->getId();
                $local_id = $productoXLocal->getLocal()->getId();

                $producto_x_local_destino = $em->getRepository('AppBundle:ProductoXLocal')->findOneBy(array('local'=>$local_id,'producto'=>$producto->getProducto() ));

                //transferencias entradas
                $sql = "SELECT SUM(txp.cantidad) as total FROM transferencia_x_producto txp
                        INNER JOIN transferencia t ON txp.transferencia_id = t.id
                        WHERE t.empresa_id = 1 AND estado = 1 AND motivo_traslado_id = 11 AND txp.producto_x_local_id = ? AND t.local_fin = ? "; 

                $stmt = $conn->prepare($sql);
                $stmt->bindValue(1, $producto_x_local_id);
                $stmt->bindValue(2, $local_id);
                $stmt->execute();
                $transferencia_entrada_total = $stmt->fetchColumn();




            }















            





            $stock = ($compra_total + $transferencia_entrada_total ) - ($venta_total + $transferencia_salida_total + $retiro_total);

            $producto->setStock($stock);

            $em->persist($producto);

        }

        try {

            $em->flush();
            
        } catch (\Exception $e) {

            return new Response('Hubo un error.');

        }

        return new Response('Se actualizó la informacion del stock.');    


    }


}
