<?php

namespace AppBundle\Controller;

use AppBundle\Entity\FacturaVenta;
use AppBundle\Entity\Usuario;
use AppBundle\Entity\FacturaVentaNubefactError;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * Facturaventum controller.
 *
 * @Route("facturaventa")
 */
class FacturaVentaController extends Controller
{
    const RUTA  = 'https://api.nubefact.com/api/v1/4c9c2b7e-af22-493f-ac90-259e4c8553b3';
    const TOKEN = '27a2a514ea1b459d930707cd9c14d409889a28efcc204354ac8131b39118d4f4';

    /**
     * Lists all facturaVentum entities.
     *
     * @Route("/", name="facturaventa_index")
     * @Method("GET")
     */
    public function indexAction(UserPasswordEncoderInterface $encoder)
    {
        $em = $this->getDoctrine()->getManager();

        $user = new Usuario();
        $plainPassword = '27a2a514ea1b459d930707cd9c14d409889a28efcc204354ac8131b39118d4f4';
        $encoded = $encoder->encodePassword($user, $plainPassword);

        //$user->setPassword($encoded);

        //$valid = $encoder->isPasswordValid($user,$encoded);

        $facturaVentas = $em->getRepository('AppBundle:FacturaVenta')->findAll();

        return $this->render('facturaventa/index.html.twig', array(
            'facturaVentas' => $facturaVentas,
            'titulo'=>''
        ));
    }

    /**
     * Creates a new facturaVentum entity.
     *
     * @Route("/new", name="facturaventa_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $facturaVentum = new Facturaventum();
        $form = $this->createForm('AppBundle\Form\FacturaVentaType', $facturaVentum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($facturaVentum);
            $em->flush();

            return $this->redirectToRoute('facturaventa_show', array('id' => $facturaVentum->getId()));
        }

        return $this->render('facturaventa/new.html.twig', array(
            'facturaVentum' => $facturaVentum,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a facturaVenta entity.
     *
     * @Route("/{id}", name="facturaventa_show")
     * @Method({"POST","GET"})
     */
    public function showAction(Request $request,FacturaVenta $facturaVenta)
    {
        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $host           = $request->getHost();

        $localObj  = $em->getRepository('AppBundle:EmpresaLocal')->find($local);
        $empresaObj  = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $formato_ferretero = $empresaObj->getFormatoFerretero();

        if( ($facturaVenta->getDocumento() == 'boleta' || $facturaVenta->getDocumento() == 'factura') && $facturaVenta->getVenta()->getVentaFormaPago()[0]->getFormaPago()->getCodigo() != '5'){

            $leer_respuesta = array();
            if($localObj->getUrlFacturacion() != '' && $localObj->getTokenFacturacion() != '' && $localObj->getFacturacionElectronica() != false)
            {

                $data_json = $this->container->get('AppBundle\Util\Util')->generarArchivoJson($facturaVenta,$local);
                $respuesta = $this->container->get('AppBundle\Util\Util')->enviarArchivoJson($data_json,$localObj);
                $leer_respuesta = json_decode($respuesta, true);
              


                if (isset($leer_respuesta['errors'])) {


                    if(!$formato_ferretero)
                    {


                        foreach($facturaVenta->getVenta()->getDetalleVenta() as $detalle)
                        {
                            if($detalle->getProductoXLocal()){

                                $productoXLocal = $detalle->getProductoXLocal();
                                $cantidad       = $detalle->getCantidad();
                                $this->container->get('AppBundle\Util\Util')->aumentarAlmacen($productoXLocal->getId(),$cantidad);

                            }

                        }

                        $transferencia = $em->getRepository('AppBundle:Transferencia')->findOneBy(array('facturaVenta'=>$facturaVenta));
                        $cajaAperturaDetalle = $em->getRepository('AppBundle:CajaAperturaDetalle')->findOneBy(array('identificador'=>$facturaVenta->getId()));

                        $em->remove($cajaAperturaDetalle);
                        $em->remove($transferencia);                
                        $em->remove($facturaVenta);
                        
                        $nferror  = $em->getRepository('AppBundle:NubefactError')->findOneBy(array('codigo'=>$leer_respuesta['codigo']));
                        $msj_error = ($nferror) ? $nferror->getDescripcion().' '.$leer_respuesta['errors'] : 'Error no identificado';

              
                        try {

                            $em->flush();

                            $this->addFlash("danger", $msj_error);
                                       
                        } catch (Exception $e) {
                            //Mostramos los errores si los hay
                            $this->addFlash("danger", $e." La factura no pudo ser generada en SUNAT,volver a intentarlo. Si el problema persiste contactar con el administrador del sistema.");



                            $response = new Response($e);

                            return $response;

                        }    

                        return $this->redirectToRoute('detalleventa_puntoventa');

                    }
                    else
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


                }
                else
                {
                    if(!$formato_ferretero)
                    {

                        if($localObj->getUrlFacturacion() != '' && $localObj->getTokenFacturacion() != '' && $localObj->getFacturacionElectronica() != false)
                        {

                            if($leer_respuesta['aceptada_por_sunat'] != 'true'){

                                $enlace_de_pdf = ($leer_respuesta['enlace']) ? $leer_respuesta['enlace'].'.pdf' : $leer_respuesta['enlace_del_pdf'];

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
                                    $facturaVenta->setEnlacepdf($enlace_de_pdf);
                                }

                            }

                            //Fin de consulta
                            $facturaVenta->setCodigoError($leer_respuesta['sunat_responsecode']);
                            $facturaVenta->setMensajeError($leer_respuesta['sunat_description']);
                            $facturaVenta->setEnlaceXml($leer_respuesta['enlace_del_xml']);
                            $facturaVenta->setEnlaceCdr($leer_respuesta['enlace_del_cdr']);
                                                
                            $em->persist($facturaVenta);

                            

                            try {

                                $em->flush();


                                //Get the file
                                $content = file_get_contents($enlace_de_pdf);
                                file_put_contents("uploads/files/".$empresa."/".$facturaVenta->getLocal()->getId()."/recibo.pdf",$content);

                                if($facturaVenta->getDocumento() == 'factura'  && $facturaVenta->getFacturaEnviada() == true)
                                {
                                    if($facturaVenta->getCliente()->getEmail()){

                                        $email = $facturaVenta->getCliente()->getEmail();

                                        $this->enviarFactura($email,$leer_respuesta['enlace_del_pdf'],$empresaObj->getCorreoRemitente(),$facturaVenta);

                                    }
                                    
                                }                            

                                                        
                                
                            } catch (\Exception $e) {

                                $this->addFlash("danger"," Ocurrió un error inesperado, Imprimir la factura desde la lista de ventas.");

                                $response = new Response($e);
                                return $response;
                                
                            }


                            return $this->redirect($this->generateUrl('detalleventa_puntoventa',array('rutaPdf'=>'uploads/files/'.$empresa.'/'.$facturaVenta->getLocal()->getId().'/recibo.pdf')));



                        }
                        else
                        {

                            return $this->redirectToRoute('facturaventa_generapdf',array('id'=>$facturaVenta->getId()));

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

                        //$facturaVenta->setEnlacepdf($enlace_de_pdf);

                        //Si no se genera el PDF, volvemos a consultar el documento en nubefact
                        // if($enlace_de_pdf == '.pdf' || is_null($enlace_de_pdf) || $enlace_de_pdf == '')
                        // {
                        //     $url_facturacion_electronica   = $facturaVenta->getLocal()->getUrlFacturacion();
                        //     $token_facturacion_electronica = $facturaVenta->getLocal()->getTokenFacturacion();

                        //     $data_json_reenvio      = $this->container->get('AppBundle\Util\Util')->generarConsultaArchivoJson($facturaVenta);
                        //     $respuesta_reenvio      = $this->container->get('AppBundle\Util\Util')->enviarConsultaArchivoJson($data_json_reenvio,$url_facturacion_electronica,$token_facturacion_electronica);
                        //     $leer_respuesta_reenvio = json_decode($respuesta_reenvio, true);


                        //     if(!isset($leer_respuesta_reenvio['errors']))
                        //     {
                        //         $enlace_de_pdf = ($leer_respuesta_reenvio['enlace_del_pdf']) ? $leer_respuesta_reenvio['enlace_del_pdf'] : $leer_respuesta_reenvio['enlace'].'.pdf';
                                
                        //     }

                        // }
                        //Fin de consulta

                        $facturaVenta->setEnlacepdf($enlace_de_pdf);
                        $em->persist($facturaVenta);


                        try {

                            $em->flush();

                            if($facturaVenta->getDocumento() == 'factura'  && $facturaVenta->getFacturaEnviada() == true)
                            {
                                if($facturaVenta->getCliente()->getEmail()){

                                    $email = $facturaVenta->getCliente()->getEmail();
                                    $enlace_documento = ($facturaVenta->getEnlacePdfFerretero()) ? $facturaVenta->getEnlacePdfFerretero() : $facturaVenta->getEnlacepdf();
                                    $this->enviarFactura($email,$enlace_documento,$empresaObj->getCorreoRemitente(),$facturaVenta);

                                }
                                

                            }                        
                            
                        } catch (\Exception $e) {

                            $this->addFlash("danger"," Ocurrió un error inesperado, Imprimir la factura desde la lista de ventas.");

                            $response = new Response($e);
                            return $response;
                            
                        }

                        return $this->redirectToRoute('facturaventa_generapdf_electronico',array('id'=>$facturaVenta->getId()));              

                    }

                }


                return $this->redirectToRoute('facturaventa_generapdf_electronico',array('id'=>$facturaVenta->getId()));

            }
            else
            {

                return $this->redirectToRoute('facturaventa_generapdf',array('id'=>$facturaVenta->getId()));

            }


        }
        else
        {
            return $this->redirectToRoute('facturaventa_generapdf',array('id'=>$facturaVenta->getId()));

        }


        return $this->redirectToRoute('detalleventa_puntoventa');



    }


    public function showGuiaAction(Request $request,$facturaId=null)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $host           = $request->getHost();

        $facturaVenta = null;
        if($facturaId){
            $facturaVenta   = $em->getRepository('AppBundle:FacturaVenta')->find((int)$facturaId);
        }
        
        $localObj   = $em->getRepository('AppBundle:EmpresaLocal')->find($local);


        return $this->render('facturaventa/showGuia.html.twig', array(
            'facturaVenta' => $facturaVenta,
            //'localObj' => $localObj,
        ));


    }



    /**
     * Genera documento PDF de venta , factura,boleta,ticket o recibo.
     *
     * @Route("/{id}/generapdf", name="facturaventa_generapdf")
     * @Method({"GET", "POST"})
     */
    public function generaPDFAction(Request $request, FacturaVenta $facturaVenta)
    {

        $em = $this->getDoctrine()->getManager();
        setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish');

        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $host           = $request->getHost();

        $localObj  = $em->getRepository('AppBundle:EmpresaLocal')->find($local);


        if($facturaVenta->getVenta()->getVentaFormaPago()[0]->getFormaPago()->getCodigo() == '5'){


            $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
            $dql .= " JOIN cxd.documento d";
            $dql .= " JOIN d.local l";
            $dql .= " WHERE  l.id =:local  AND d.codigo = '07' AND cxd.estado = 1  ";

            $query = $em->createQuery($dql);

            $query->setParameter('local',$local);         
     
            $componentesXDocumento =  $query->getResult();

            $html = $this->render('facturaventa/showReciboingreso.html.twig', array(
                    'facturaVenta' => $facturaVenta,
                    'localObj'     => $localObj,
                    'componentesXDocumento' => $componentesXDocumento,
                    'host' => $host
                ))->getContent();

            // $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

            $f = 'reciboingreso_'.$facturaVenta->getTicket();

            $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f . '.pdf',array('header-html'=> null,'footer-html'=> null,'page-height' => 210,'page-width'  => 58,'margin-right' => 2,'margin-left' => 2,'margin-top' => 5,'margin-bottom' => 0));

            return $this->redirect($this->generateUrl('detalleventa_puntoventa',array('rutaPdf'=>'uploads/files/'.$empresa.'/'.$f . '.pdf')));


        }else{

            if($facturaVenta->getDocumento() == 'boleta'){

                $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
                $dql .= " JOIN cxd.documento d";
                $dql .= " JOIN d.local l";
                $dql .= " WHERE  l.id =:local  AND d.codigo = '03' AND cxd.estado = 1  ";

                $query = $em->createQuery($dql);

                $query->setParameter('local',$local);         
         
                $componentesXDocumento =  $query->getResult();

                $html = $this->render('facturaventa/showBoleta.html.twig', array(
                        'facturaVenta' => $facturaVenta,
                        'localObj'     => $localObj,
                        'componentesXDocumento' => $componentesXDocumento,
                        'host' => $host
                    ))->getContent();

                // $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

                $f = $this->generateUniqueFileName().'.pdf';

                $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f . '.pdf',array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 3,'margin-bottom' => 3));

                $ruta_pdf = $request->getSchemeAndHttpHost().'/uploads/files/'.$empresa.'/'.$f;
                $facturaVenta->setEnlacePdfFerretero($ruta_pdf);
                $em->persist($facturaVenta);

                try {

                    $em->flush();
                    
                } catch (\Exception $e) {

                    return $e;
                    
                }

                return $this->redirect($this->generateUrl('detalleventa_puntoventa',array('rutaPdf'=>'uploads/files/'.$empresa.'/'.$f)));

            }elseif($facturaVenta->getDocumento() == 'factura'){

                $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
                $dql .= " JOIN cxd.documento d";
                $dql .= " JOIN d.local l";
                $dql .= " WHERE  l.id =:local  AND d.codigo = '01'  AND cxd.estado = 1 ";

                $query = $em->createQuery($dql);

                $query->setParameter('local',$local);         
         
                $componentesXDocumento =  $query->getResult();

                $cuentasBancarias = $em->getRepository('AppBundle:CuentaBanco')->findBy(array('estado'=>true,'empresa'=>$empresa),array('banco'=>'ASC'));

                $html = $this->render('facturaventa/showFactura.html.twig', array(
                        'facturaVenta' => $facturaVenta,
                        'localObj'     => $localObj,
                        'cuentasBancarias' => $cuentasBancarias,
                        'componentesXDocumento' => $componentesXDocumento,
                        'host' => $host
                    ))->getContent();

                // $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0)); 

                $f = $this->generateUniqueFileName().'.pdf';              


                $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f . '.pdf',array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 3,'margin-bottom' => 3));

                //$this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$f . '.pdf',array('header-html'=> null,'footer-html'=> null,'page-height' => 170,'page-width'  => 210,'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

                $ruta_pdf = $request->getSchemeAndHttpHost().'/uploads/files/'.$empresa.'/'.$f;
                $facturaVenta->setEnlacePdfFerretero($ruta_pdf);
                $em->persist($facturaVenta);

                try {

                    $em->flush();
                    
                } catch (\Exception $e) {

                    return $e;
                    
                }


                return $this->redirect($this->generateUrl('detalleventa_puntoventa',array('rutaPdf'=>'uploads/files/'.$empresa.'/'.$f)));


            }else{

                switch ($localObj->getNotaventaFormato()) {
                    case 'A4':

                        $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
                        $dql .= " JOIN cxd.documento d";
                        $dql .= " JOIN d.local l";
                        $dql .= " WHERE  l.id =:local  AND d.codigo = '03' AND cxd.estado = 1  ";

                        $query = $em->createQuery($dql);

                        $query->setParameter('local',$local);         
                 
                        $componentesXDocumento =  $query->getResult();

                        $html = $this->render('facturaventa/showGuiaA4.html.twig', array(
                                'facturaVenta' => $facturaVenta,
                                'localObj'     => $localObj,
                                'componentesXDocumento' => $componentesXDocumento,
                                'host' => $host
                            ))->getContent();


                        $f = $this->generateUniqueFileName().'.pdf';

                        $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f,array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 3,'margin-bottom' => 3));

                        $ruta_pdf = $request->getSchemeAndHttpHost().'/uploads/files/'.$empresa.'/'.$f;
                        $facturaVenta->setEnlacePdfFerretero($ruta_pdf);
                        $em->persist($facturaVenta);

                        try {

                            $em->flush();
                            
                        } catch (\Exception $e) {

                            return $e;
                            
                        }

                        return $this->redirect($this->generateUrl('detalleventa_puntoventa',array('rutaPdf'=>'uploads/files/'.$empresa.'/'.$f)));

                        break;
                    
                    default:

                        $html = $this->render('facturaventa/showGuia.html.twig', array(
                                'facturaVenta' => $facturaVenta,
                                'localObj'     => $localObj,
                                'host' => $host
                            ))->getContent();

                        $f = $this->generateUniqueFileName().'.pdf';


                        $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f,array('header-html'=> null,'footer-html'=> null,'page-height' =>  210,'page-width' => 80,'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

                        $ruta_pdf = $request->getSchemeAndHttpHost().'/uploads/files/'.$empresa.'/'.$f;
                        $facturaVenta->setEnlacePdfFerretero($ruta_pdf);
                        $em->persist($facturaVenta);

                        try {

                            $em->flush();
                            
                        } catch (\Exception $e) {

                            return $e;
                            
                        }


                        return $this->redirect($this->generateUrl('detalleventa_puntoventa',array('rutaPdf'=>'uploads/files/'.$empresa.'/'.$f)));

                        //return $this->redirect($this->generateUrl('detalleventa_puntoventa',array('guiaHtml'=>'si','facturaId'=>$facturaVenta->getId())));

                        break;

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
     * Genera documento PDF de venta cuando es facturacion electronnica, factura,boleta.
     *
     * @Route("/{id}/generapdfelectronico", name="facturaventa_generapdf_electronico")
     * @Method({"GET", "POST"})
     */
    public function generaPDFElectronicoAction(Request $request, FacturaVenta $facturaVenta)
    {

        $em = $this->getDoctrine()->getManager();
        setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish');

        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $host           = $request->getHost();

        $localObj  = $em->getRepository('AppBundle:EmpresaLocal')->find($local);


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


                    $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f,array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 8,'margin-top' => 3,'margin-bottom' => 3));

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


            $facturaVenta->setEnlacePdfFerretero($ruta_pdf);

            $em->persist($facturaVenta);

            try {

                $em->flush();
                
            } catch (\Exception $e) {

                return $e;
                
            }

            return $this->redirect($this->generateUrl('detalleventa_puntoventa',array('rutaPdf'=>'uploads/files/'.$empresa.'/'.$f)));

        }elseif($facturaVenta->getDocumento() == 'factura'){


            $f = $this->generateUniqueFileName().'.pdf';
            $ruta_pdf = $request->getSchemeAndHttpHost().'/uploads/files/'.$empresa.'/'.$f;    

            switch ($localObj->getFacturaFormato()) {
                case 'A4':

                    $html = $this->render('facturaventa/facturaElectronicaA4.html.twig', array(
                            'facturaVenta' => $facturaVenta,
                            'localObj'     => $localObj,
                            'host' => $host
                        ))->getContent();


                    $this->get('knp_snappy.pdf')->generateFromHtml($html, 'uploads/files/'.$empresa.'/'.$f,array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 10,'margin-top' => 3,'margin-bottom' => 3));

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


            $facturaVenta->setEnlacePdfFerretero($ruta_pdf);

            $em->persist($facturaVenta);

            try {

                $em->flush();
                
            } catch (\Exception $e) {

                return $e;
                
            }

            return $this->redirect($this->generateUrl('detalleventa_puntoventa',array('rutaPdf'=>'uploads/files/'.$empresa.'/'.$f)));


        }


        return new \Symfony\Component\HttpFoundation\Response(
                $pdf, 200, array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'inline; filename="'.$f.'"',
                     
        ));

    }


    /**
     * Genera documento PDF de venta desde nubefact: factura,boleta,ticket,nota de credito.
     *
     * @Route("/{id}/{enlacepdf}/generapdfnubefact", name="facturaventa_generapdf_nubefact")
     * @Method({"GET", "POST"})
     */
    public function generaPDFNubefactAction(Request $request, FacturaVenta $facturaVenta,$enlacepdf)
    {

        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        return $this->redirect($this->generateUrl('detalleventa_puntoventa',array('rutaPdf'=>$enlacepdf)));

        //return $this->redirectToRoute('facturaventa_edit', array('id' => $facturaVenta->getId()));
    }

    /**
     * Displays a form to edit an existing facturaVentum entity.
     *
     * @Route("/{id}/edit", name="facturaventa_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, FacturaVenta $facturaVentum)
    {
        $editForm = $this->createForm('AppBundle\Form\FacturaVentaType', $facturaVentum);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('facturaventa_edit', array('id' => $facturaVentum->getId()));
        }

        return $this->render('facturaventa/edit.html.twig', array(
            'facturaVentum' => $facturaVentum,
            'edit_form' => $editForm->createView(),
        ));
    }


    private function generarArchivoJson(FacturaVenta $facturaVenta,$local)
    {
        $em = $this->getDoctrine()->getManager();

        $detalleVenta = array();
        $i=0;
        $total_gravada = 0;
        $total_igv = 0;
        $total = 0;
        foreach($facturaVenta->getVenta()->getDetalleVenta() as $detalle){

            $cantidadLinea      = $detalle->getCantidad();
            $valor_unitario     = ($cantidadLinea > 0) ? ($detalle->getSubtotal()/$cantidadLinea)/1.18 : 0;//($detalle->getSubtotal()/$detalle->getCantidad())/1.18;
            $subt               = $valor_unitario*$cantidadLinea;
            
            $precio_unitario    = $valor_unitario + $valor_unitario*0.18;
            $totalLinea         = $cantidadLinea*$precio_unitario;//$detalle->getSubtotal();
            $igv                = $subt*0.18;


            $descripcion = ($detalle->getDescripcion())? $detalle->getProductoXLocal()->getProducto()->getNombre().' . '.$detalle->getDescripcion():$detalle->getProductoXLocal()->getProducto()->getNombre();
            

            $detalleVenta[$i]['unidad_de_medida'] = "NIU";
            $detalleVenta[$i]['codigo'] = "".$detalle->getProductoXLocal()->getProducto()->getCodigo()."";
            $detalleVenta[$i]['descripcion'] = "".$descripcion."";
            $detalleVenta[$i]['cantidad'] = "".$detalle->getCantidad()."";
            $detalleVenta[$i]['valor_unitario'] = "".$valor_unitario."";
            $detalleVenta[$i]['precio_unitario'] = "".$precio_unitario."";
            $detalleVenta[$i]['descuento'] = "";
            $detalleVenta[$i]['subtotal'] = "".$subt."";
            $detalleVenta[$i]['tipo_de_igv'] = "1";
            $detalleVenta[$i]['igv'] = "".$igv."";
            $detalleVenta[$i]['total'] = "".$totalLinea."";
            $detalleVenta[$i]['anticipo_regularizacion'] = "false";
            $detalleVenta[$i]['anticipo_documento_serie'] = "";
            $detalleVenta[$i]['anticipo_documento_numero'] = "";

            $i++;
            $total_igv  = $total_igv + $igv;
            $total      = $total + $detalle->getSubtotal();
            $total_gravada = $total_gravada + $subt;
        }



        $partesticket = explode("-", $facturaVenta->getTicket());//$this->generarNumero($local);//
        $numero = $partesticket[1];

        $localObj = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        switch ($facturaVenta->getDocumento()) {
            case 'factura':
                $serie = $localObj->getSerieFactura();
                $tipo_de_comprobante = 1;
                $cliente_tipo_de_documento = 6;
                break;
            case 'boleta':
                $serie = $localObj->getSerieBoleta();
                $cliente_tipo_de_documento = 1;
                $tipo_de_comprobante = 2;
                break;            
            default:
                $serie = '';
                $tipo_de_comprobante = '';
                break;
        }

        $total_gravada = $total/1.18;
        $total_igv = $total - $total_gravada;

        if($facturaVenta->getCliente()->getRuc() == '---'){

            $cliente_tipo_de_documento = '-';

        }

        $detraccion = 'false';
        if($facturaVenta->getDetraccion()){

            $detraccion = 'true';

        }

        $condicion_de_pago = '';
        $ventaFormaPago = $facturaVenta->getVenta()->getVentaFormaPago()[0];

        if($ventaFormaPago){

            $condicion_de_pago = strtoupper($ventaFormaPago->getFormaPago()->getNombre());

            if($ventaFormaPago->getFormaPago()->getCodigo() == '2'){
                $condicion_de_pago .= ' A '.$ventaFormaPago->getNumeroDias().' DIAS';
            }
        }



        $data = array(
            "operacion"                         => "generar_comprobante",
            "tipo_de_comprobante"               => "".$tipo_de_comprobante."",
            "serie"                             => "".$serie."",
            "numero"                            => $numero,
            "sunat_transaction"                 => "1",
            "cliente_tipo_de_documento"         => "".$cliente_tipo_de_documento."",
            "cliente_numero_de_documento"       => "".$facturaVenta->getCliente()->getRuc()."",
            "cliente_denominacion"              => "".$facturaVenta->getCliente()->getRazonSocial()."",
            "cliente_direccion"                 => "".$facturaVenta->getCliente()->getDireccion()."",
            "cliente_email"                     => "",
            "cliente_email_1"                   => "",
            "cliente_email_2"                   => "",
            "fecha_de_emision"                  => date('d-m-Y'),
            "fecha_de_vencimiento"              => "",
            "moneda"                            => "1",
            "tipo_de_cambio"                    => "",
            "porcentaje_de_igv"                 => "18.00",
            "descuento_global"                  => "",
            "descuento_global"                  => "",
            "total_descuento"                   => "",
            "total_anticipo"                    => "",
            "total_gravada"                     => "".round($total_gravada,2)."",
            "total_inafecta"                    => "",
            "total_exonerada"                   => "",
            "total_igv"                         => "".round($total_igv,2)."",
            "total_gratuita"                    => "",
            "total_otros_cargos"                => "",
            "total"                             => "".round($total,2)."",
            "percepcion_tipo"                   => "",
            "percepcion_base_imponible"         => "",
            "total_percepcion"                  => "",
            "total_incluido_percepcion"         => "",
            "detraccion"                        => "".$detraccion."",
            "observaciones"                     => "",
            "documento_que_se_modifica_tipo"    => "",
            "documento_que_se_modifica_serie"   => "",
            "documento_que_se_modifica_numero"  => "",
            "tipo_de_nota_de_credito"           => "",
            "tipo_de_nota_de_debito"            => "",
            "enviar_automaticamente_a_la_sunat" => "true",
            "enviar_automaticamente_al_cliente" => "false",
            "codigo_unico"                      => "",
            "condiciones_de_pago"               => "".$condicion_de_pago."",
            "medio_de_pago"                     => "",
            "placa_vehiculo"                    => "",
            "orden_compra_servicio"             => "",
            "tabla_personalizada_codigo"        => "",
            "formato_de_pdf"                    => "",
            "items" => $detalleVenta
        );
            
        // dump($data);
        // die();

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



    private function generarNumero($local)
    {
        $em = $this->getDoctrine()->getManager();

        $ultimo_id = $em->createQueryBuilder()
            ->select('MAX(fv.id)')
            ->from('AppBundle:FacturaVenta', 'fv')
            ->leftJoin('fv.venta', 'v')
            ->leftJoin('v.empleado', 'e')
            ->leftJoin('e.local', 'l')
            ->where('l.id=:local')
            ->setParameter('local', $local)
            ->getQuery()
            ->getSingleScalarResult();


        return $ultimo_id;

    }

    private function enviarFactura($email = '',$enlace = '',$correo_remitente = '',$facturaVenta = null)
    {
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
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }    

}
