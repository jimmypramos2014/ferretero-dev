<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response; 
use AppBundle\Entity\Cliente;
use AppBundle\Entity\Proveedor;
use AppBundle\Barcode\Barcode;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use AppBundle\Entity\TransferenciaXTransporte;
use AppBundle\Entity\DetalleCompraAnulada;
use AppBundle\Entity\DetalleProformaEntregaDatosEnvio;
use AppBundle\Entity\DetalleVentaEntregaDatosEnvio;

use AppBundle\Entity\FacturaVenta;
use AppBundle\Entity\Venta;
use AppBundle\Entity\DetalleVenta;
use AppBundle\Entity\VentaFormaPago;

/**
 * ServicioAjax controller.
 *
 */
class ServicioAjaxController extends Controller
{


    public function __constructor()
    {
    }

    /**
     * @Route("/obtenercodigoproducto",name="obtenercodigoproducto")
     */
    public function obtenerCodigoProducto(Request $request){

        $em = $this->getDoctrine()->getManager();

        $producto = $request->request->get("producto");

        //$producto = '2800';

        if($producto != ''){

	        $productoObj = $em->getRepository('AppBundle:ProductoXLocal')->find($producto);

	        $data['codigo']     = $productoObj->getProducto()->getCodigo();
	        $data['stock'] 		= $productoObj->getStock();
	        $data['precio'] 	= $productoObj->getProducto()->getPrecioCompra();
            $data['unidad']     = ($productoObj->getProducto()->getUnidadCompra())?$productoObj->getProducto()->getUnidadCompra()->getNombre():'';

        }else{

	        $data['codigo']     = '';
	        $data['stock'] 		= '';
	        $data['precio'] 	= '';
            $data['unidad']     = '';

        }

        return $this->json($data);

    }

    /**
     * @Route("/registrarclientenuevo",name="registrar_cliente_nuevo")
     */
    public function registrarClienteNuevo(Request $request){

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $nrazonsocial   = $request->request->get("nrazonsocial");
        $dniruc         = $request->request->get("dniruc");
        $direccion      = $request->request->get("direccion");
        $distrito       = $request->request->get("distrito");
        $tipodoc        = $request->request->get("tipodocumento");
        $clienteid      = $request->request->get("clienteid");
        $email          = $request->request->get("email");
        $telefono       = $request->request->get("telefono");
        $tipo           = $request->request->get("tipo");

        if($clienteid != '')
        {
            $cliente = $em->getRepository('AppBundle:Cliente')->find($clienteid);

        }
        else
        {

            $clienteEliminado = $em->getRepository('AppBundle:Cliente')->findBy(array('local'=>$local,'estado'=>false,'ruc'=>$dniruc));
            $clienteExistente = $em->getRepository('AppBundle:Cliente')->findBy(array('local'=>$local,'estado'=>true,'ruc'=>$dniruc));

            if(count($clienteEliminado) > 0 ){

                foreach($clienteEliminado as $cl)
                {
                    if($cl->getRuc() != '-')
                    {

                        $data['cliente']    = '';
                        $data['text'] = '';
                        $data['cliente_id']         =  '';
                        $data['id']           =  '';
                        $data['mensaje']      =  'El Cliente que está intentando registrar ya se encuentra registrado en el Sistema pero con estado eliminado. Para habilitarlo de nuevo debe ir a la lista de Clientes buscarlo y presionar el icono de habilitar, con esto podrá utilizarlo en el Sistema.';

                        return $this->json($data);             
                    }
                }  


            }

            if(count($clienteExistente) > 0 )
            {

                foreach($clienteExistente as $cl)
                {

                    if($cl->getRuc() != '-')
                    {

                        $data['cliente']      = '';
                        $data['text']         = '';
                        $data['cliente_id']   = '';
                        $data['id']           = '';                    
                        $data['mensaje']      = 'El Cliente que está intentando registrar ya se encuentra registrado en el Sistema.';

                        return $this->json($data);                   
                    }   

                }


            }

            $cliente = new Cliente();
        }

        
        $cliente->setRazonSocial($nrazonsocial);
        $cliente->setRuc($dniruc);
        $cliente->setEstado(true);
        $cliente->setCodigo(mt_rand(1000,999999));
        $localObj = $em->getRepository('AppBundle:EmpresaLocal')->find($local);
        $cliente->setLocal($localObj);
        $cliente->setDireccion($direccion);
        $cliente->setEmail($email);
        $cliente->setTelefono($telefono);
        $cliente->setTipo($tipo);

        $distritoObj = ($distrito != '') ? $em->getRepository('AppBundle:Distrito')->find($distrito) : null;
        $cliente->setDistrito($distritoObj);

        $tipDocObj = ($tipodoc != '') ? $em->getRepository('AppBundle:TipoDocumento')->find($tipodoc) : null;
        $cliente->setTipoDocumento($tipDocObj);        
        $em->persist($cliente);


        try {

            $em->flush();

            $data['cliente']    = '['.$cliente->getRuc().'] '.$cliente->getRazonSocial();
            $data['cliente_id'] = $cliente->getId();
            $data['text']       =  '['.$cliente->getRuc().'] '.$cliente->getRazonSocial();
            $data['id']         =  $cliente->getId();
            $data['mensaje']         =  '';
            
        } catch (\Exception $e) {
            return $e;
        }

        return $this->json($data);

    }

    /**
     * @Route("/registrar/datos/transporte",name="registrar_datos_transporte")
     * @Method({"GET", "POST"})
     */
    public function registrarDatosTransporte(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $response = '';

        if($request->isXmlHttpRequest()){

            $transferencia_id = $request->request->get('appbundle_transferenciaxtransporte')['transferencia_id'];

            $transferenciaXTransporte = $em->getRepository('AppBundle:TransferenciaXTransporte')->findOneBy(array('transferencia'=>$transferencia_id));

            if(!$transferenciaXTransporte){
                $transferenciaXTransporte = new TransferenciaXTransporte();
            }

            
            $form = $this->createForm('AppBundle\Form\TransferenciaXTransporteType', $transferenciaXTransporte,array('empresa'=>$empresa));
            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) {


                $transferenciaObj = $em->getRepository('AppBundle:Transferencia')->find($transferencia_id);
                $transferenciaXTransporte->setTransferencia($transferenciaObj);
                $em->persist($transferenciaXTransporte);
                $em->flush();


                $response = new JsonResponse();
                $response->setStatusCode(200);
                $response->setData(array(
                    'response'  => 'success',
                    'empresatransporte'  => 'hello',
                ));
                
            }
            
        }

        return $response;

    }

    /**
     * @Route("/registrar/datos/entregaparcial/transporte",name="registrar_datos_entrega_parcial_transporte")
     * @Method({"GET", "POST"})
     */
    public function registrarDatosProformaEntregaParcialTransporte(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $response = '';

        if($request->isXmlHttpRequest()){

            $identificador = $request->request->get('appbundle_detalleventaentregadatosenvio')['identificador'];

            $detalleVentaEntregaDatosEnvio = $em->getRepository('AppBundle:DetalleVentaEntregaDatosEnvio')->findOneBy(array('identificador'=>$identificador));

            if(!$detalleVentaEntregaDatosEnvio){
                $detalleVentaEntregaDatosEnvio = new DetalleVentaEntregaDatosEnvio();
            }


            
            $form = $this->createForm('AppBundle\Form\DetalleVentaEntregaDatosEnvioType', $detalleVentaEntregaDatosEnvio,array('empresa'=>$empresa));
            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) {


                $detalleVentaEntregaDatosEnvio->setIdentificador($identificador);
                $em->persist($detalleVentaEntregaDatosEnvio);
                $em->flush();


                $response = new JsonResponse();
                $response->setStatusCode(200);
                $response->setData(array(
                    'response'  => 'success',
                    'empresatransporte'  => 'hello',
                ));
                
            }
            
        }

        return $response;

    }


    /**
     * @Route("/obtener/datos/transporte",name="obtener_datos_transporte")
     * @Method({"GET", "POST"})
     */
    public function obtenerDatosTransporte(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $data = array();
        $transferencia              = $request->request->get('transferencia');
        $transferenciaXTransporte   = $em->getRepository('AppBundle:TransferenciaXTransporte')->findOneBy(array('transferencia'=>$transferencia));
        $transferenciaObj           = $em->getRepository('AppBundle:Transferencia')->find($transferencia);

        $numeroDocumento = $transferenciaObj->getNumeroDocumento();

        $facturaVenta = null;
        if($numeroDocumento){

            $dql = "SELECT fv FROM AppBundle:FacturaVenta fv ";
            $dql .= " JOIN fv.local l";
            $dql .= " WHERE  fv.tipo = 2 ";

            if($local != ''){
                $dql .= " AND l.id =:local  ";
            }

            if($numeroDocumento != ''){
                $dql .= " AND fv.ticket =:numeroDocumento  ";
            }

            $query = $em->createQuery($dql);

            if($local != ''){
                $query->setParameter('local',$local);         
            }
     
            if($numeroDocumento != ''){
                $query->setParameter('numeroDocumento',$numeroDocumento);         
            }

            $facturaVenta =  $query->getOneOrNullResult();   

        }
        $rucRemitente       = '';
        $remitente          = '';
        $puntoPartida       = '';
        $rucDestinatario    = '';
        $destinatario       = '';
        $puntoLlegada       = '';

        if($facturaVenta){

            $rucRemitente       = ($facturaVenta->getLocal()->getEmpresa()->getRuc())? $facturaVenta->getLocal()->getEmpresa()->getRuc(): '';
            $remitente          = ($facturaVenta->getLocal()->getEmpresa()->getNombre())? $facturaVenta->getLocal()->getEmpresa()->getNombre(): '';
            $puntoPartida       = ($facturaVenta->getLocal()->getDireccion())? $facturaVenta->getLocal()->getDireccion(): '';
            $rucDestinatario    = ($facturaVenta->getCliente()->getRuc())? $facturaVenta->getCliente()->getRuc(): '';
            $destinatario       = ($facturaVenta->getCliente()->getRazonSocial())? $facturaVenta->getCliente()->getRazonSocial() : '';
            $puntoLlegada       = ($facturaVenta->getCliente()->getDireccion())? $facturaVenta->getCliente()->getDireccion() : '';

        }


        $data['transporte'] = '';
        $data['licenciaConducir'] = '';
        $data['constanciaInscripcion'] = '';
        $data['placa'] = '';
        $data['marca'] = '';
        $data['costoMinimo'] = '';
        $data['fechaTraslado'] = '';
        $data['rucDestinatario'] = $rucDestinatario;
        $data['rucRemitente'] = $rucRemitente;
        $data['destinatario'] = $destinatario;
        $data['remitente'] = $remitente;
        $data['puntoLlegada'] = $puntoLlegada;
        $data['puntoPartida'] = $puntoPartida;

        if($transferenciaXTransporte){


            $data['transporte'] = $transferenciaXTransporte->getTransporte()->getId();
            $data['licenciaConducir'] = $transferenciaXTransporte->getLicenciaConducir();
            $data['constanciaInscripcion'] = $transferenciaXTransporte->getConstanciaInscripcion();
            $data['placa'] = $transferenciaXTransporte->getPlaca();
            $data['marca'] = $transferenciaXTransporte->getMarca();
            $data['costoMinimo'] = $transferenciaXTransporte->getCostoMinimo();
            $data['fechaTraslado'] = $transferenciaXTransporte->getFechaTraslado()->format('d/m/Y');
            $data['rucDestinatario'] = $transferenciaXTransporte->getRucDestinatario();
            $data['rucRemitente'] = $transferenciaXTransporte->getRucRemitente();
            $data['destinatario'] = $transferenciaXTransporte->getDestinatario();
            $data['remitente'] = $transferenciaXTransporte->getRemitente();
            $data['puntoLlegada'] = $transferenciaXTransporte->getPuntoLlegada();
            $data['puntoPartida'] = $transferenciaXTransporte->getPuntoPartida();
        }

        return $this->json($data);

    }

    /**
     * @Route("/obtener/datos/entregaparcial/transporte",name="obtener_datos_entrega_parcial_transporte")
     * @Method({"GET", "POST"})
     */
    public function obtenerDatosProformaEntregaParcialTransporte(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $data = array();
        $identificador = $request->request->get('identificador');
        $detalleProformaEntregaDatosEnvio = $em->getRepository('AppBundle:DetalleVentaEntregaDatosEnvio')->findOneBy(array('identificador'=>$identificador));

        $data['transporte'] = '';
        $data['licenciaConducir'] = '';
        $data['constanciaInscripcion'] = '';
        $data['placa'] = '';
        $data['marca'] = '';
        $data['costoMinimo'] = '';
        $data['fechaTraslado'] = '';
        $data['rucDestinatario'] = '';
        $data['rucRemitente'] = '';
        $data['destinatario'] = '';
        $data['remitente'] = '';
        $data['puntoLlegada'] = '';
        $data['puntoPartida'] = '';

        if($detalleProformaEntregaDatosEnvio){

            $data['transporte'] = ($detalleProformaEntregaDatosEnvio->getTransporte())? $detalleProformaEntregaDatosEnvio->getTransporte()->getId():'';
            $data['licenciaConducir'] = $detalleProformaEntregaDatosEnvio->getLicenciaConducir();
            $data['constanciaInscripcion'] = $detalleProformaEntregaDatosEnvio->getConstanciaInscripcion();
            $data['placa'] = $detalleProformaEntregaDatosEnvio->getPlaca();
            $data['marca'] = $detalleProformaEntregaDatosEnvio->getMarca();
            $data['costoMinimo'] = $detalleProformaEntregaDatosEnvio->getCostoMinimo();
            $data['fechaTraslado'] = ($detalleProformaEntregaDatosEnvio->getFechaTraslado())?$detalleProformaEntregaDatosEnvio->getFechaTraslado()->format('d/m/Y'):'';
            $data['rucDestinatario'] = $detalleProformaEntregaDatosEnvio->getRucDestinatario();
            $data['rucRemitente'] = $detalleProformaEntregaDatosEnvio->getRucRemitente();
            $data['destinatario'] = $detalleProformaEntregaDatosEnvio->getDestinatario();
            $data['remitente'] = $detalleProformaEntregaDatosEnvio->getRemitente();
            $data['puntoLlegada'] = $detalleProformaEntregaDatosEnvio->getPuntoLlegada();
            $data['puntoPartida'] = $detalleProformaEntregaDatosEnvio->getPuntoPartida();
        }

        return $this->json($data);

    }


    /**
     * @Route("/obtener/entrega/datos/envio",name="obtener_entrega_datos_envio")
     * @Method({"GET", "POST"})
     */
    public function obtenerEntregaDatosEnvio(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $data = array();
        $identificador = $request->request->get('identificador');
        $entidad       = $request->request->get('entidad');

        $datosenvio = $em->getRepository('AppBundle:'.$entidad)->findOneBy(array('identificador'=>$identificador));

        $data['cliente'] = '';
        $data['direccion'] = '';


        if($datosenvio){

            $data['cliente'] = $datosenvio->getCliente();
            $data['direccion'] = $datosenvio->getDireccion();
        }

        return $this->json($data);

    }

    /**
     * @Route("/registrar/entrega/datos/envio",name="registrar_entrega_datos_envio")
     * @Method({"GET", "POST"})
     */
    public function registrarEntregaDatosEnvio(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $cliente        = $request->request->get('cliente');
        $direccion      = $request->request->get('direccion');
        $identificador  = $request->request->get('identificador');
        $entidad        = $request->request->get('entidad');

        $data = array();

        $datoenvio = $em->getRepository('AppBundle:'.$entidad)->findOneBy(array('identificador'=>$identificador));

        $datoenvio->setCliente($cliente);
        $datoenvio->setDireccion($direccion);
        $em->persist($datoenvio);

        try {

            $em->flush();

            $data['exito'] = true;

        } catch (\Exception $e) {

            $data['exito'] = false;
            
        }


        return $this->json($data);

    }


    /**
     * @Route("/anularventa",name="anular_venta")
     */
    public function anularVenta(Request $request){

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $numticket      = $request->request->get("numticket");
        $motivo         = $request->request->get("motivo");

        $facturaVenta = $em->getRepository('AppBundle:FacturaVenta')->findOneBy(array('ticket'=>$numticket));

        $data = array();

        if($facturaVenta){

            $facturaVenta->getVenta()->setEstado(false);
            $facturaVenta->getVenta()->setMotivoAnulacion($motivo);
            $usuario = $em->getRepository('AppBundle:Usuario')->find($session->get('usuario'));
            $facturaVenta->getVenta()->setUsuarioAnulacion($usuario);
            $em->persist($facturaVenta);

            try {

                $em->flush();

                $data['estado'] = 'exito';
                
            } catch (\Exception $e) {

                $data['estado'] = '';

            }

        }


        return $this->json($data);

    }

    /**
     * @Route("/generar/codigo/barra",name="generar_codigo_barra")
     */
    public function generarCodigoBarra(Request $request){

        $codigobarra    = $request->request->get("codigobarra");

        $format = 'png';
        $symbology = 'upc-e';
        $data = $codigobarra;
        $options = array('sf'=>2,'ms'=>'s','md'=>0.8);

        $generator = new Barcode();

        /* Output directly to standard output. */
        $generator->output_image($format, $symbology, $data, $options);
        /* Create bitmap image. */
        $image = $generator->render_image($symbology, $data, $options);
        imagepng($image);
        imagedestroy($image);

        /* Generate SVG markup. */
        $svg = $generator->render_svg($symbology, $data, $options);


        //return $this->json($svg);
        return $svg;

    }

    /**
     * @Route("/buscarproductos",name="buscar_productos")
     */
    public function buscarProductos(Request $request){

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $textobuscar    = $request->request->get("textobuscar");
        $categoria      = $request->request->get("categoria");
        $modo           = $request->request->get("modo");
        $moneda         = (int)$request->request->get("moneda");
        $tipo_cliente   = $request->request->get("tipo_cliente");

        $fecha = ($request->request->get('fecha') == '') ? new \DateTime() : date_create_from_format('d/m/Y', $request->request->get('fecha'));

        $tipoCambioObj = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$fecha));
        $tipo_cambio = ($tipoCambioObj)? $tipoCambioObj->getVenta() : null;

        if($tipo_cliente == 'mayorista')
        {

            if($moneda == 2)
            {

                $data = array();
                if($tipo_cambio == null)
                {

                    $data['error'] = 'No existe el tipo de cambio para la fecha seleccionada. Registre el valor y vuelva a intentarlo.';
                    return $this->json($data);

                }

                $productoXLocals = $this->container->get('AppBundle\Util\Util')->productosPrecioEnDolaresMayorista($local,$textobuscar,$categoria,$tipo_cambio);

            }
            else
            {
                $productoXLocals = $this->container->get('AppBundle\Util\Util')->productosPrecioMayorista($local,$textobuscar,$categoria,$tipo_cambio);
            }


        }
        else
        {

            if($moneda == 2)
            {

                $data = array();
                if($tipo_cambio == null)
                {

                    $data['error'] = 'No existe el tipo de cambio para la fecha seleccionada. Registre el valor y vuelva a intentarlo.';
                    return $this->json($data);

                }

                $productoXLocals = $this->container->get('AppBundle\Util\Util')->productosPrecioEnDolares($local,$textobuscar,$categoria,$tipo_cambio);
            }
            else
            {
                $productoXLocals = $this->container->get('AppBundle\Util\Util')->productosMasVendidosXLocal($local,$textobuscar,$categoria,$tipo_cambio);
            }

        }
      

        return $this->render('servicioajax/buscarproductos.html.twig', array(
            'productoXLocals'   => $productoXLocals,
            'textobuscar'       => $textobuscar,
            'modo'              => $modo
        ));   
     

    }

    /**
    * @Route("/provinciadesderegion",name="obtener_provincia_desde_region")
    */
    public function getProvinciaDesdeRegion(Request $request){
      
        $em = $this->getDoctrine()->getManager();

        $autorizado  = $request->request->get("autorizado");
        $region      = $request->request->get("region");

        if (strtolower($autorizado) != 'si') {
            throw new AccessDeniedException('No tienes autorización para acceder a esta dirección');
        }

        $dql = "SELECT p FROM AppBundle:Provincia p";

        if ($region !='') {

            $dql .= " WHERE p.departamento =:region";
        }

        $dql .= " ORDER BY p.nombre ASC";

        $resp = $em->createQuery($dql);


        if ($region !='') {
            $resp->setParameter('region', $region);
        }


        return $this->render('servicioajax/listadoCbo.html.twig', array(
            'listado'   => "provinciadesderegion",
            'lista_provincia' => $resp->getResult()

        ));    

    }


    /**
    * @Route("/distritodesdeprovincia",name="obtener_distrito_desde_provincia")
    */
    public function getDistritoDesdeProvincia(Request $request){
      
        $em = $this->getDoctrine()->getManager();

        $autorizado     = $request->request->get("autorizado");
        $provincia      = $request->request->get("provincia");


        if (strtolower($autorizado) != 'si') {
            throw new AccessDeniedException('No tienes autorización para acceder a esta dirección');
        }

        $dql = "SELECT d FROM AppBundle:Distrito d";

        if ($provincia !='') {

            $dql .= " WHERE d.provincia =:provincia";
        }

        $dql .= " ORDER BY d.nombre ASC";

        $resp = $em->createQuery($dql);

        if ($provincia !='') {
            $resp->setParameter('provincia', $provincia);
        }

        return $this->render('servicioajax/listadoCbo.html.twig', array(
            'listado'   => "distritodesdeprovincia",
            'lista_distrito' => $resp->getResult()

        ));    

    }

    /**
    * @Route("/localdesdeempresa",name="obtener_local_desde_empresa")
    */
    public function getLocalDesdeEmpresa(Request $request){
      
        $em = $this->getDoctrine()->getManager();

        $autorizado     = $request->request->get("autorizado");
        $empresa        = $request->request->get("empresa");


        if (strtolower($autorizado) != 'si') {
            throw new AccessDeniedException('No tienes autorización para acceder a esta dirección');
        }

        $dql = "SELECT d FROM AppBundle:EmpresaLocal d";

        if ($empresa !='') {

            $dql .= " WHERE d.empresa =:empresa";
        }

        $dql .= " ORDER BY d.nombre ASC";

        $resp = $em->createQuery($dql);

        if ($empresa !='') {
            $resp->setParameter('empresa', $empresa);
        }

        return $this->render('servicioajax/listadoCbo.html.twig', array(
            'listado'   => "localdesdeempresa",
            'lista_local' => $resp->getResult()

        ));    

    }

    /**
    * @Route("/cajadesdelocal",name="obtener_caja_desde_local")
    */
    public function getCajaDesdeLocal(Request $request){
      
        $em = $this->getDoctrine()->getManager();

        $autorizado     = $request->request->get("autorizado");
        $local        = $request->request->get("local");


        if (strtolower($autorizado) != 'si') {
            throw new AccessDeniedException('No tienes autorización para acceder a esta dirección');
        }

        $dql = "SELECT d FROM AppBundle:Caja d  ";
        $dql .= " JOIN d.local l  WHERE d.estado = 1 ";

        if ($local != '') {

            $dql .= " AND  l.id =:local";
        }

        $dql .= " ORDER BY d.nombre ASC";

        $resp = $em->createQuery($dql);

        if ($local !='') {
            $resp->setParameter('local', $local);
        }

        return $this->render('servicioajax/listadoCbo.html.twig', array(
            'listado'   => "cajadesdelocal",
            'lista_caja' => $resp->getResult()

        ));    

    }

    /**
    * @Route("/obtenercondicioncaja",name="obtener_condicion_caja")
    */
    public function obtenerCondicionCaja(Request $request){
      
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $usuario        = $session->get('usuario');

        $caja        = $request->request->get("caja");
        $cajaObj     = $em->getRepository('AppBundle:Caja')->find($caja);
        $usuarioObj  = $em->getRepository('AppBundle:Usuario')->find($usuario);

        $data = array();
        
        if($cajaObj){

            $condicion = $cajaObj->getCondicion();

            switch ($condicion) {
                case 'cerrado':
                    
                    $data['rpta'] = '<div class="bg-danger text-white">La caja está cerrada. Comunicar al administrador. Si usted es administrador seleccione la opción "Caja" del menú principal, ubique la caja y proceda con la apertura.</div>';
                    $data['condicion'] = 'cerrado';
                    break;
                case 'abierto':

                    // $ultimoUsuario = ($cajaObj->getUsuarioModificacion())?$cajaObj->getUsuarioModificacion()->getUsername():$cajaObj->getUsuarioCreacion()->getUsername();
                    // $data['rpta'] = '<div class="bg-danger text-white">La caja ya fue abierta y está siendo usada por el usuario '.$ultimoUsuario.'</div>';

                    // $data['condicion'] = 'abierto';
                    // if($ultimoUsuario == $usuarioObj->getUsername()){
                    //     $data['rpta'] = '';                        
                    //     $data['condicion'] = 'cerrado';
                    // }
                    
                    $data['rpta'] = '<div class="bg-success text-white">La caja está abierta.Puede continuar.</div>';
                    $data['condicion'] = 'abierto';                    
                    break;
                                
                default:
                    $data['rpta'] = '';
                    $data['condicion'] = 'cerrado';
                    break;
            }

        }

        return $this->json($data);   

    }

    /**
    * @Route("/obtenerdetalleventa",name="obtener_detalle_venta")
    */
    public function obtenerDetalleVenta(Request $request){
      
        $em = $this->getDoctrine()->getManager();

        //$autorizado     = $request->request->get("autorizado");
        $factura        = $request->request->get("factura");


        // if (strtolower($autorizado) != 'si') {
        //     throw new AccessDeniedException('No tienes autorización para acceder a esta dirección');
        // }

        $facturaVenta = $em->getRepository('AppBundle:FacturaVenta')->find($factura);
        $detallesVenta = $facturaVenta->getVenta()->getDetalleVenta();

        return $this->render('servicioajax/detalleventa.html.twig', array(
            'detallesVenta' => $detallesVenta
        ));    

    }

    /**
    * @Route("/procesarpagoacuenta",name="procesar_pago_acuenta")
    */
    public function procesarPagoAcuenta(Request $request){
      
        $em = $this->getDoctrine()->getManager();

        $factura        = $request->request->get("factura");

        $facturaVenta = $em->getRepository('AppBundle:FacturaVenta')->find($factura);

        $ventaFormaPago = ($facturaVenta->getVenta()->getVentaFormaPago())?$facturaVenta->getVenta()->getVentaFormaPago()[0]:null;

        return $this->render('servicioajax/pagoAcuenta.html.twig', array(
            'ventaFormaPago' => $ventaFormaPago,
            'facturaVenta'   => $facturaVenta
        ));

    }


    /**
    * @Route("/unidadcompradesdeunidadventa",name="obtener_unidad_compra")
    */
    public function obtenerUnidadCompra(Request $request){
      
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $empresa        = $session->get('empresa');

        $autorizado     = $request->request->get("autorizado");
        $unventa        = $request->request->get("unventa");

        $productoUnidad = $em->getRepository('AppBundle:ProductoUnidad')->find($unventa);

        if (strtolower($autorizado) != 'si') {
            throw new AccessDeniedException('No tienes autorización para acceder a esta dirección');
        }

        $respuesta = null;

        if($productoUnidad->getCategoria()){

            $dql = "SELECT u FROM AppBundle:ProductoUnidad u";
            $dql .= " JOIN u.categoria c";
            $dql .= " WHERE c.id =:categoria ";
            $dql .= " AND u.empresa =:empresa ";
            $dql .= " ORDER BY u.nombre ASC";

            $resp = $em->createQuery($dql);

            $resp->setParameter('categoria', $productoUnidad->getCategoria()->getId());
            $resp->setParameter('empresa', $empresa);

            $respuesta = $resp->getResult();

        }


        return $this->render('servicioajax/listadoCbo.html.twig', array(
            'listado'       => "unidadcompradesdeunidadventa",
            'lista_unidad'  => $respuesta,
            'unidad_id'     => $unventa
        ));    

    }

    /**
     * @Route("/obtenerproductos",name="obtenerproductos")
     */
    public function obtenerProductos(Request $request){

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');

        //$local = 1;

        $q = $request->query->get("q");
        $local = ($request->query->get("local") != '') ? $request->query->get("local") : $local;

        $data = array();


        $dql = "SELECT pxl FROM AppBundle:ProductoXLocal pxl ";
        $dql .= " JOIN pxl.local l";
        $dql .= " JOIN pxl.producto p";
        $dql .= " WHERE  pxl.estado <> 0  ";

        if($local != ''){
            $dql .= " AND l.id =:local  ";
        }

        if($q != ''){
            $dql .= " AND  ( p.nombre LIKE :q  ";
            $dql .= "  OR  p.codigo LIKE :q  ) ";
        }

        $dql .= " ORDER BY p.nombre ";

        $query = $em->createQuery($dql);

        if($local != ''){
            $query->setParameter('local',$local);         
        }
 
         if($q != ''){
            $query->setParameter('q','%'.$q.'%');         
        }

        $productos =  $query->getResult();    

        $data['total_count'] = count($productos);

        $i = 0;
        foreach($productos as $producto){

            $data['results'][$i]['id']         = $producto->getId();
            $data['results'][$i]['text']       = '['.$producto->getProducto()->getCodigo().'] '.$producto->getProducto()->getNombre().' - '.$producto->getProducto()->getMarca()->getNombre();


            $i++;
        };



        return $this->json($data);

    }

    /**
     * @Route("/obtenerempresas",name="obtenerempresas")
     */
    public function obtenerEmpresas(Request $request){

        $conn = $this->get('database_connection');

        $session        = $request->getSession();
        $local          = $session->get('local');

        $q = $request->query->get("q");

        $data = array();


        $sql = "SELECT ruc,razon_social FROM sunat_empresa";

        $sql .=" WHERE ruc != '' ";

        if($q != ''){
            $sql .= " AND ruc LIKE ? ";
        }

        $stmt = $conn->prepare($sql);
        
        if($q != ''){
            $stmt->bindValue(1, '%'.$q.'%');
        }          
        $stmt->execute();
        $empresas = $stmt->fetchAll();


        $data['total_count'] = count($empresas);

        $i = 0;
        foreach($empresas as $empresa){

            $data['results'][$i]['id']         = $empresa['ruc'];
            $data['results'][$i]['text']       = $empresa['ruc'];


            $i++;
        };



        return $this->json($data);

    }


    public function obtenerDatosEmpresaAction(Request $request){

        $ruc   = $request->query->get("ruc");
        $token = $request->query->get("token");

        // $ruc   = '10400518730';
        // $token = '5JFQh5toA0sWhj-YV3NXmt4dzE4JvJjwgYzzPJtYHgA';

        //$ruta = "http://consultaruc.intidev2.com/api/ruc";
        $ruta = "http://ws.consultaruc.info/api/ruc";

        $data = array(
            "token" => $token,
            "ruc"   => $ruc
        );
            
        $data_json = json_encode($data);

        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_URL, $ruta);
        \curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            )
        );
        \curl_setopt($ch, CURLOPT_POST, 1);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        \curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta  = \curl_exec($ch);
        \curl_close($ch);


        $leer_respuesta = json_decode($respuesta, true);



        return $this->json($leer_respuesta);


    }

    /**
     * @Route("/obtenerclientes",name="obtenerclientes")
     */
    public function obtenerClientes(Request $request){

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $q = $request->query->get("q");

        $data = array();


        $dql = "SELECT c FROM AppBundle:Cliente c ";
        $dql .= " JOIN c.local l";
        $dql .= " JOIN l.empresa e";
        $dql .= " WHERE  c.estado <> 0  ";

        if($empresa != ''){
            $dql .= " AND e.id =:empresa  ";
        }

        if($q != ''){
            $dql .= " AND  ( c.razonSocial LIKE :q  ";
            $dql .= "  OR  c.ruc LIKE :q  ) ";
        }

        $dql .= " ORDER BY c.razonSocial ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
         if($q != ''){
            $query->setParameter('q','%'.$q.'%');         
        }

        $clientes =  $query->getResult();    

        $data['total_count'] = count($clientes);

        $i = 0;
        foreach($clientes as $cliente){

            $data['results'][$i]['id']         = $cliente->getId();
            $data['results'][$i]['text']       = '['.$cliente->getRuc().'] '.$cliente->getRazonSocial();
            $data['results'][$i]['tipo']       = $cliente->getTipo();


            $i++;
        };



        return $this->json($data);

    }

    /**
     * @Route("/obtenercliente",name="obtenercliente")
     */
    public function obtenerCliente(Request $request){

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $data = array();

        $cliente = $em->getRepository('AppBundle:Cliente')->find($id);

        $data['id'] = '';
        $data['tipo_documento'] = '';
        $data['numero_documento'] = '';
        $data['direccion'] = '';
        $data['razon_social'] = '';
        $data['distrito'] = '';
        $data['email'] = '';
        $data['tipo'] = '';
        $data['telefono'] = '';


        if($cliente){

            $data['id'] = $cliente->getId();
            $data['tipo_documento'] = ($cliente->getTipoDocumento()) ? $cliente->getTipoDocumento()->getId():'';
            $data['direccion'] = ($cliente->getDireccion())? $cliente->getDireccion():'';
            $data['numero_documento'] = ($cliente->getRuc())? $cliente->getRuc():'';
            $data['razon_social'] = ($cliente->getRazonSocial())? $cliente->getRazonSocial() : '';
            $data['distrito'] = ($cliente->getDistrito())? $cliente->getDistrito()->getId() :'' ;
            $data['email'] = ($cliente->getEmail())? $cliente->getEmail() : '' ;
            $data['tipo'] = ($cliente->getTipo())? $cliente->getTipo() : '' ;
            $data['telefono'] = ($cliente->getTelefono())? $cliente->getTelefono() : '' ;


        }


        return $this->json($data);

    }
    

    /**
     * @Route("/obtenerstockproducto",name="obtenerstockproducto")
     */
    public function obtenerStockProducto(Request $request){

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $data = array();

        $productoXLocal = $em->getRepository('AppBundle:ProductoXLocal')->find((int)$id);

        $data['id'] = $id;
        $data['stock'] = 0;
        $data['nombre'] = '';


        if($productoXLocal){

            $data['id'] = $productoXLocal->getId();
            $data['stock'] = ($productoXLocal->getStock())? $productoXLocal->getStock() : 0 ;
            $data['nombre'] = ($productoXLocal->getProducto())? $productoXLocal->getProducto()->getNombre() : '' ;

        }


        return $this->json($data);

    }


    /**
     * @Route("/obtener/cliente/default",name="obtener_cliente_default")
     */
    public function obtenerClienteDefault(Request $request){

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $data = array();

        $dql = "SELECT c FROM AppBundle:Cliente c ";
        $dql .= " JOIN c.local l";
        $dql .= " WHERE  c.codigo = '000000'  ";

        if($local != ''){
            $dql .= " AND l.id =:local  ";
        }

        $query = $em->createQuery($dql);

        if($local != ''){
            $query->setParameter('local',$local);         
        }

        $clienteDefault =  $query->getResult();

        $data['total_count'] = count($clienteDefault);

        $i = 0;
        foreach($clienteDefault as $cliente){

            $data['results']['id']         = $cliente->getId();
            $data['results']['text']       = '['.$cliente->getRuc().'] '.$cliente->getRazonSocial();


            $i++;
        };



        return $this->json($data);

    }


    /**
     * @Route("/obtenerproveedores",name="obtenerproveedores")
     */
    public function obtenerProveedores(Request $request){

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $q = $request->query->get("q");
        //$tipo = $request->query->get("tipo");

        $data = array();


        $dql = "SELECT p FROM AppBundle:Proveedor p ";
        $dql .= " JOIN p.empresa e";
        //$dql .= " JOIN p.proveedorXTipo t";
        $dql .= " WHERE  p.estado <> 0  ";

        if($empresa != ''){
            $dql .= " AND e.id =:empresa  ";
        }

        if($q != ''){
            $dql .= " AND  ( p.nombre LIKE :q  ";
            $dql .= "  OR  p.ruc LIKE :q  ) ";
        }

        // if($tipo == 'servicio'){
        //     $dql .= " AND  t.id = 1 ";
        // }

        $dql .= " ORDER BY p.nombre ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
         if($q != ''){
            $query->setParameter('q','%'.$q.'%');         
        }

        $proveedores =  $query->getResult();    

        $data['total_count'] = count($proveedores);

        $i = 0;
        foreach($proveedores as $proveedor){

            $data['results'][$i]['id']         = $proveedor->getId();
            $data['results'][$i]['text']       = '['.$proveedor->getRuc().'] '.$proveedor->getNombre();


            $i++;
        };



        return $this->json($data);

    }

    /**
     * @Route("/obtenerproveedor",name="obtenerproveedor")
     */
    public function obtenerProveedor(Request $request){

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get("id");

        $data = array();

        $proveedor = $em->getRepository('AppBundle:Proveedor')->find($id);

        $data['id'] = '';
        $data['nombre'] = '';
        $data['ruc'] = '';
        $data['telefono'] = '';
        $data['razon_social'] = '';
        $data['distrito'] = '';
        $data['condicion'] = '';
        $data['direccion'] = '';


        if($proveedor){

            $data['id'] = $proveedor->getId();
            $data['nombre'] = ($proveedor->getNombre()) ? $proveedor->getNombre():'';
            $data['ruc'] = ($proveedor->getRuc())? $proveedor->getRuc():'';
            $data['telefono'] = ($proveedor->getTelefono())? $proveedor->getTelefono():'';
            $data['direccion'] = ($proveedor->getDireccion())? $proveedor->getDireccion() : '';
            $data['distrito'] = ($proveedor->getDistrito())? $proveedor->getDistrito()->getId() :'' ;
            $data['condicion'] = ($proveedor->getCondicion())? $proveedor->getCondicion() :'' ;


        }


        return $this->json($data);

    }


    /**
     * @Route("/registrarproveedornuevo",name="registrar_proveedor_nuevo")
     */
    public function registrarProveedorNuevo(Request $request){

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $codigo = $this->generarCodigo($empresaObj);

        $nombre   = $request->request->get("nombre");
        $ruc      = $request->request->get("ruc");
        $telefono = $request->request->get("telefono");
        $direccion = $request->request->get("direccion");
        $distrito = $request->request->get("distrito");
        $condicion = $request->request->get("condicion");
        $id       = $request->request->get("id");
        $tipo       = $request->request->get("tipo");

        if($id != '')
        {
            $proveedor = $em->getRepository('AppBundle:Proveedor')->find($id);

        }
        else
        {
            $proveedorEliminado = $em->getRepository('AppBundle:Proveedor')->findBy(array('empresa'=>$empresa,'estado'=>false,'ruc'=>$ruc));
            $proveedorExistente = $em->getRepository('AppBundle:Proveedor')->findBy(array('empresa'=>$empresa,'estado'=>true,'ruc'=>$ruc));

            if(count($proveedorEliminado) > 0 ){

                $data['proveedor']    = '';
                $data['proveedor_id'] = '';
                $data['text']         =  '';
                $data['id']           =  '';
                $data['mensaje']      =  'El Proveedor que está intentando registrar ya se encuentra registrado en el Sistema pero con estado eliminado. Para habilitarlo de nuevo debe ir a la lista de Proveedores buscarlo y presionar el icono de habilitar, con esto podrá utilizarlo en el Sistema.';

                return $this->json($data);
            }

            if(count($proveedorExistente) > 0 ){

                $data['proveedor']    = '';
                $data['proveedor_id'] = '';
                $data['text']         =  '';
                $data['id']           =  '';
                $data['mensaje']      =  'El Proveedor que está intentando registrar ya se encuentra registrado en el Sistema.';

                return $this->json($data);
            }

            $proveedor = new Proveedor();

            if($tipo == 'servicio'){
                $provXTipo = $em->getRepository('AppBundle:ProveedorTipo')->find(1);
                $proveedor->addProveedorXTipo($provXTipo);
            }else{

                $provXTipo = $em->getRepository('AppBundle:ProveedorTipo')->find(2);
                $proveedor->addProveedorXTipo($provXTipo);
            }
                    
            $proveedor->setCodigo($codigo);
        }
        
        $proveedor->setNombre($nombre);
        $proveedor->setRuc($ruc);
        $proveedor->setEstado(true);
        $proveedor->setDireccion($direccion);
        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);
        $proveedor->setEmpresa($empresaObj);
        $proveedor->setTelefono($telefono);
        $distritoObj = $em->getRepository('AppBundle:Distrito')->find($distrito);
        $proveedor->setDistrito($distritoObj);
        $proveedor->setCondicion($condicion);



 
        $em->persist($proveedor);


        try {

            $em->flush();

            $data['proveedor']    = '['.$proveedor->getRuc().'] '.$proveedor->getNombre();
            $data['proveedor_id'] = $proveedor->getId();
            $data['text']       =  '['.$proveedor->getRuc().'] '.$proveedor->getNombre();
            $data['id']         =  $proveedor->getId();
            $data['mensaje']         =  '';
            
        } catch (\Exception $e) {
            return $e;
        }

        return $this->json($data);

    }

    public function consultarEstadoBoletaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $conn = $this->get('database_connection');
        $data = array();

        $sql = "SELECT fv.id,fv.ticket,fv.fecha,fv.documento,fv.empresa_local_id FROM factura_venta fv
                    INNER JOIN venta v ON fv.venta_id = v.id
                    WHERE v.estado = 1 AND DATE_FORMAT(fv.fecha, '%Y-%m-%d') = SUBDATE(CURDATE(),1)";

        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $ventas = $stmt->fetchAll();

        $j=0;
        foreach($ventas as $i=>$venta){

            $local      = $em->getRepository('AppBundle:EmpresaLocal')->find($venta['empresa_local_id']);
            $factura    = $em->getRepository('AppBundle:FacturaVenta')->find($venta['id']);
            $url        = $local->getUrlFacturacion();
            $token      = $local->getTokenFacturacion();

            if($url != '' && $token != ''){

                $data_json = $this->generarArchivoJson($factura,$local);
                $respuesta = $this->enviarArchivoJson($data_json,$local);
                $leer_respuesta = json_decode($respuesta, true);

                if (!isset($leer_respuesta['errors'])) 
                {

                    if(!$leer_respuesta['aceptada_por_sunat']){

                        $data[$j]['serie'] = $leer_respuesta['serie'];
                        $data[$j]['numero'] = $leer_respuesta['numero'];
                        $data[$j]['ticket'] = $venta['ticket'];
                        $data[$j]['documento'] = $venta['documento'];
                        $data[$j]['local'] = $venta['empresa_local_id'];
                        $data[$j]['factura_id'] = $venta['id'];
                    }
                    
                }

            }

        }
        

        return $this->json($data);


    }

    private function generarArchivoJson($facturaVenta,$localObj)
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
            "operacion"                         => "consultar_comprobante",
            "tipo_de_comprobante"               => "".$tipo_de_comprobante."",
            "serie"                             => "".$serie."",
            "numero"                            => $numero,
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
    * @Route("/anulardetallecompra",name="anular_detalle_compra")
    */
    public function anularDetalleCompra(Request $request){
      
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $usuario        = $session->get('usuario');

        $usuarioObj = $em->getRepository('AppBundle:Usuario')->find($usuario);       

        $autorizado             = $request->request->get("autorizado");
        $cantidad_anular        = $request->request->get("cantidad_anular");
        $detalle_compra_id      = $request->request->get("detalle_compra_id");

        $fecha = new \DateTime();

        if (strtolower($autorizado) != 'si') {
            throw new AccessDeniedException('No tienes autorización para acceder a esta dirección');
        }

        $detalleCompra = $em->getRepository('AppBundle:DetalleCompra')->find($detalle_compra_id);
        $compra = $detalleCompra->getCompra();

        $cantidad = $detalleCompra->getCantidad();
        $subtotal = $detalleCompra->getSubtotal();

        $precio = ($cantidad != 0) ? $subtotal/$cantidad : 0;

        $cantidad_nueva = ($cantidad_anular <= $cantidad) ? $cantidad - $cantidad_anular : $cantidad_anular;
        $subtotal_nueva = $precio*$cantidad_nueva;

        $detalleCompra->setCantidad($cantidad_nueva);
        $detalleCompra->setSubtotal($subtotal_nueva);
        $em->persist($detalleCompra);
        $em->flush();

        //Registramos en la tabla detalle_venta_anulada
        $detalleCompraAnulada = new DetalleCompraAnulada();
        $detalleCompraAnulada->setFecha($fecha);
        $detalleCompraAnulada->setCantidad($cantidad_anular);
        $detalleCompraAnulada->setProductoXLocal($detalleCompra->getProductoXLocal());
        $detalleCompraAnulada->setCompra($compra);
        $detalleCompraAnulada->setUsuario($usuarioObj);
        $em->persist($detalleCompraAnulada);

        //Actualizamos cantidad en tabla ventaFormaPago
        $detallesCompra = $em->getRepository('AppBundle:DetalleCompra')->findBy(array('compra'=>$compra->getId()));
        $facturaCompra = $compra->getFacturaCompra()[0];

        $total = 0;
        foreach($detallesCompra as $detalle){
            $total = $total + $detalle->getSubtotal();
        }

        $compra->getCompraFormaPago()[0]->setCantidad($total);
        $em->persist($compra);

        $dql = "SELECT txp FROM AppBundle:TransferenciaXProducto txp ";
        $dql .= " JOIN txp.transferencia t";
        $dql .= " JOIN txp.productoXLocal pxl";
        $dql .= " WHERE t.numeroDocumento =:numero  ";
        $dql .= " AND pxl.id =:producto  ";

        $query = $em->createQuery($dql);
        $query->setParameter('numero',$facturaCompra->getTicket());
        $query->setParameter('producto',$detalleCompra->getProductoXLocal()->getId());         

        $transferenciaXProducto =  $query->getOneOrNullResult();

        if($transferenciaXProducto){

            $transferenciaXProducto->setCantidad($cantidad_nueva);
            $em->persist($transferenciaXProducto);

        }  

        try {

            //Actualizamos el almacen stock del producto
            $this->container->get('AppBundle\Util\Util')->disminuirAlmacen($detalleCompra->getProductoXLocal()->getId(),$cantidad_anular);

            $em->flush();

            $data['exito'] = 'La compra fue anulada exitosamente.';
            $data['error'] = null;
            
        } catch (\Exception $e) {
            $data['error'] = $e;
        }

        return $this->json($data);

    }


    private function generarCodigo($empresa)
    {
        $em = $this->getDoctrine()->getManager();
        $codigo = '';

        $proveedores = $em->getRepository('AppBundle:Proveedor')->findBy(array('empresa'=>$empresa));

        if(count($proveedores) == 0)
        {
            $codigo = 'PROV000001';

        }
        else
        {
            $contador = count($proveedores) + 1;
            $cantidadDigito = strlen($contador);
            $numCerosMaximo = 6;
            $codigo = (string)$contador;

            for($i = $cantidadDigito; $i < $numCerosMaximo; $i++){
                $codigo = "0".$codigo;
            }

            $codigo = 'PROV'.$codigo;
        }

        return $codigo;

    }



    /**
     * @Route("/registrar/datos/proforma",name="registrar_datos_proforma")
     * @Method({"GET", "POST"})
     */
    public function registrarDatosProforma(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

       
        $session        = $request->getSession();
        $usuario        = $session->get('usuario');
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $caja           = $session->get('caja');

        $hora = date('H:i');

        $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);        

        $data     = json_decode($request->request->get('data'));
        $cliente  = $request->request->get('cliente');
        $empleado = $request->request->get('empleado');
        $validez_oferta = $request->request->get('validezOferta');
        $plazo_entrega = $request->request->get('plazoEntrega');
        $empleado_cotiza = $request->request->get('empleadoCotiza');
        $correo_empleado_cotiza = $request->request->get('correoEmpleadoCotiza');
        $telefono_cliente = $request->request->get('telefonoCliente');
        $incluir_igv = $request->request->get('incluirIgv');
        $forma_pago = $request->request->get('formaPago');
        $documento = $request->request->get('documento');
        $moneda = $request->request->get('moneda');
        $fecha = ($request->request->get('fecha') == '') ? new \DateTime() : date_create_from_format('d/m/Y H:i', $request->request->get('fecha').' '.$hora);


        $numero = $this->container->get('AppBundle\Util\Util')->generarNumeroProforma('factura_venta',$local);
        $numero = str_pad($numero + 1, 7, '0', STR_PAD_LEFT);
        $numero = $localObj->getCodigo().'-'.$numero;

        //Guardamos el objeto Venta
        $ventaObj = new Venta();
        $ventaObj->setEmpleado(null);
        $ventaObj->setFecha($fecha);
        $ventaObj->setEstado(true);
        $ventaObj->setCondicion(false);
        $empleadoObj = $em->getRepository('AppBundle:Empleado')->find($empleado);
        $ventaObj->setEmpleado($empleadoObj);
        $em->persist($ventaObj);


        //Guardamos el objeto FacturaVenta
        $facturaventaObj = new FacturaVenta();

        if($empleado_cotiza == ''){
            $empleado_cotiza = strtoupper($empleadoObj->getNombres().' '.$empleadoObj->getApellidoPaterno().' '.$empleadoObj->getApellidoMaterno());
            $correo_empleado_cotiza = $empleadoObj->getEmail();
        }

        $clienteObj     = $em->getRepository('AppBundle:Cliente')->find($cliente);

        if($telefono_cliente == ''){
            $telefono_cliente = ($clienteObj->getTelefono())? $clienteObj->getTelefono(): '';
        }
        
        $facturaventaObj->setCliente($clienteObj);
        $facturaventaObj->setVenta($ventaObj);
        $facturaventaObj->setFecha($fecha);
        $facturaventaObj->setNumeroGuiaremision(null);
        $facturaventaObj->setLocal($localObj);
        $cajaObj = ($caja) ? $em->getRepository('AppBundle:Caja')->find($caja) : null;
        $facturaventaObj->setCaja($cajaObj);

        $tipoVenta = $em->getRepository('AppBundle:FacturaVentaTipo')->find(1);
        $facturaventaObj->setTipo($tipoVenta);
        $facturaventaObj->setNumeroProforma($numero);
        $facturaventaObj->setFacturaEnviada(false);
        $facturaventaObj->setDetraccion(false);
        $facturaventaObj->setDocumento($documento);

        $facturaventaObj->setPlazoEntrega($plazo_entrega);
        $facturaventaObj->setValidezOferta($validez_oferta);
        $facturaventaObj->setEmpleadoCotiza($empleado_cotiza);
        $facturaventaObj->setCorreoEmpleadoCotiza($correo_empleado_cotiza);
        $facturaventaObj->setTelefonoCliente($telefono_cliente);

        $facturaventaObj->setEnviadoSunat(false);

        $incluir_igv == 'SI';
        if($incluir_igv == 'NO'){
            $facturaventaObj->setIncluirIgv(false);
        }else{
            $facturaventaObj->setIncluirIgv(true);
        }

        $em->persist($facturaventaObj);

        $pago_total = 0;
        foreach($data as $i=>$prod){

            $subtotal = $prod->cantidad * $prod->punitario;

            //Guardamos el detalle de Venta
            $detalleVentaObj = new DetalleVenta();
            $detalleVentaObj->setVenta($ventaObj);
            $productoXLocal  = $em->getRepository('AppBundle:ProductoXLocal')->find($prod->productoid);
            $detalleVentaObj->setProductoXLocal($productoXLocal);
            $detalleVentaObj->setCantidad($prod->cantidad);
            $detalleVentaObj->setPrecio($prod->punitario);
            $detalleVentaObj->setSubtotal($subtotal);
            $detalleVentaObj->setDescripcion($prod->descripcion);
            $em->persist($detalleVentaObj);

            $pago_total += $subtotal;

        }

        //Guardamos el objeto VentaFormaPago
        $ventaformapagoObj = new VentaFormaPago();

        $formaPagoObj = null;
        if($forma_pago != ''){
            $formaPagoObj = $em->getRepository('AppBundle:FormaPago')->find($forma_pago);
        }
                
        $ventaformapagoObj->setFormaPago($formaPagoObj);
        $ventaformapagoObj->setVenta($ventaObj);
        $ventaformapagoObj->setCantidad($pago_total);
        $ventaformapagoObj->setNumeroDias(null);
        
        $monedaObj = $em->getRepository('AppBundle:Moneda')->find($moneda);
        $ventaformapagoObj->setMoneda($monedaObj);

        if($moneda == 2){

            $tipoCambio = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$fecha));
            $valorTipoCambio = ($tipoCambio) ? $tipoCambio->getVenta() : null;

            $ventaformapagoObj->setValorTipoCambio($valorTipoCambio);


        }

        $em->persist($ventaformapagoObj);        


        try {

            $em->flush();
            $data['factura_id'] = $facturaventaObj->getId();
            
        } catch (\Exception $e) {

            $data['factura_id'] = '';
        }


        return $this->json($data);

    }

    /**
     * @Route("/obtenerfactura",name="obtener_factura")
     */
    public function obtenerFactura(Request $request){

        $conn = $this->get('database_connection');

        $session        = $request->getSession();
        $local          = $session->get('local');

        $id = $request->query->get("id");

        $data = array();

        if($id != ''){

            $sql = "SELECT fc.id,fc.compra_id,DATE_FORMAT(fc.fecha,'%d/%m/%Y %H:%i') as fecha,fc.numero_documento,UPPER(fc.documento) as documento,cfp.cantidad FROM factura_compra fc
                    INNER JOIN compra c ON fc.compra_id = c.id
                    INNER JOIN compra_forma_pago cfp ON cfp.compra_id = c.id
                    WHERE fc.id = ?";

            $stmt = $conn->prepare($sql);        
            $stmt->bindValue(1, $id);
            $stmt->execute();
            $facturas = $stmt->fetchAll();


            $i = 0;
            foreach($facturas as $factura){

                $data['id']                 = $factura['id'];
                $data['compra_id']          = $factura['compra_id'];
                $data['fecha']              = $factura['fecha'];
                $data['numero_documento']   = $factura['numero_documento'];
                $data['documento']          = $factura['documento'];
                $data['cantidad']          = $factura['cantidad'];

                $i++;
            };

        }


        return $this->json($data);

    }


    /**
     * @Route("/obtenervalortipocambio",name="obtenervalortipocambio")
     */
    public function obtenerValorTipoCambio(Request $request){

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $empresa        = $session->get('empresa');

        $data = array();

        $fecha = ($request->request->get("fecha") != '')? \DateTime::createFromFormat('d/m/Y', $request->request->get("fecha")) : new \DateTime();

        $tipoCambio = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$fecha));

        $data['valor'] = '';

        if($tipoCambio){

            $data['valor'] = $tipoCambio->getVenta();

        }


        return $this->json($data);

    }

    /**
     * @Route("/obtenerprecioxrango",name="obtenerprecioxrango")
     */
    public function obtenerPrecioXRango(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $conn = $this->get('database_connection');

        $producto = $request->request->get("producto");
        $cantidad = $request->request->get("cantidad");

        $productoXLocal = $em->getRepository('AppBundle:ProductoXLocal')->find($producto);
        $producto_id    = $productoXLocal->getProducto()->getId();
        $precio_normal  = $productoXLocal->getProducto()->getPrecioUnitario();

        $data = array();

        $sql = "SELECT p.id,p.nombre,pxp.rango_inicial,pxp.rango_final,pxp.precio FROM producto_x_precio pxp ";
        $sql .= " INNER JOIN producto p ON p.id = pxp.producto_id ";
        $sql .=" WHERE p.id = ? ";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1,$producto_id);     
        $stmt->execute();
        $preciosXRango = $stmt->fetchAll();

        if(count($preciosXRango) > 0)
        {

            foreach($preciosXRango as $i=>$precioXRango)
            {
                $rango_inicial = $precioXRango['rango_inicial'];
                $rango_final = $precioXRango['rango_final'];
                $precio = $precioXRango['precio'];

                if($cantidad >= $rango_inicial && $cantidad <= $rango_final)
                {
                    $data['precio'] = $precio;
                    break;
                }
            }

        }

        if(count($data) == 0)
        {
            //$data['precio'] = $precio_normal;
        }

        return $this->json($data);

    }

    /**
    * @Route("/facturadesdecliente",name="obtener_factura_desde_cliente")
    */
    public function getFacturaDesdeCliente(Request $request){
      
        $em = $this->getDoctrine()->getManager();

        $autorizado     = $request->request->get("autorizado");
        $cliente        = $request->request->get("cliente");


        if (strtolower($autorizado) != 'si') {
            throw new AccessDeniedException('No tienes autorización para acceder a esta dirección');
        }

        $data = array();

        $dql = "SELECT d FROM AppBundle:FacturaVenta d";

        if ($cliente !='') {

            $dql .= " WHERE d.cliente =:cliente";
        }

        $dql .= " ORDER BY d.fecha ASC";

        $resp = $em->createQuery($dql);

        if ($cliente !='') {
            $resp->setParameter('cliente', $cliente);
        }

        $facturas = $resp->getResult();

        foreach($facturas as $factura)
        {
            $data[] = $factura->getId();

        }

        return $this->json($data);

    }
   
    /**
    * @Route("/productodesdelocal",name="obtener_producto_desde_local")
    */
    public function getProductoDesdeLocal(Request $request){
      
        $em = $this->getDoctrine()->getManager();

        $autorizado   = $request->request->get("autorizado");
        $local        = $request->request->get("local");


        if (strtolower($autorizado) != 'si') {
            throw new AccessDeniedException('No tienes autorización para acceder a esta dirección');
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
            $resp->setParameter('local', $local);
        }

        return $this->render('servicioajax/listadoCbo.html.twig', array(
            'listado'   => "productodesdelocal",
            'lista_producto' => $resp->getResult()

        ));    

    }    


    /**
     * @Route("/obtenercodigosunat",name="obtenercodigosunat")
     */
    public function obtenerCodigoSunat(Request $request){

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $q = $request->query->get("q");

        $data = array();


        $dql = "SELECT c FROM AppBundle:ProductoCodigoSunat c ";

        if($q != ''){
            $dql .= " WHERE  ( c.descripcion LIKE :q  ";
            $dql .= "  OR  c.codigo LIKE :q  ) ";
        }

        $dql .= " ORDER BY c.codigo ";

        $query = $em->createQuery($dql);


         if($q != ''){
            $query->setParameter('q','%'.$q.'%');         
        }

        $codigosSunat =  $query->getResult();    

        $data['total_count'] = count($codigosSunat);

        $i = 0;
        foreach($codigosSunat as $codigoSunat){

            $data['results'][$i]['id']         = $codigoSunat->getId();
            $data['results'][$i]['text']       = '['.$codigoSunat->getCodigo().'] '.$codigoSunat->getDescripcion();


            $i++;
        };



        return $this->json($data);

    }    

        
}