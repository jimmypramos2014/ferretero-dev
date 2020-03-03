<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Compra;
use AppBundle\Entity\DetalleCompra;
use AppBundle\Entity\FacturaCompra;
use AppBundle\Entity\CompraFormaPago;
use AppBundle\Entity\Transferencia;
use AppBundle\Entity\TransferenciaXProducto;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * Detallecompra controller.
 *
 * @Route("detallecompra")
 */
class DetalleCompraController extends Controller
{
    /**
     * Lists all detalleCompra entities.
     *
     * @Route("/", name="detallecompra_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR')")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $fecha = new \DateTime();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $facturaCompra = new FacturaCompra();

        $formProducto = $this->createForm('AppBundle\Form\DetalleCompraProductoType', $facturaCompra,array('local'=>$local,'empresa'=>$empresa));

        //$productoXLocals = $em->getRepository('AppBundle:ProductoXLocal')->findBy(array('local'=>$local));

        $tipoCambio = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$fecha));


        return $this->render('detallecompra/index.html.twig', array(
            //'productoXLocals'=> $productoXLocals,
            'titulo'         => 'Registrar compras',
            'tipoCambio'   => $tipoCambio,
            'formProducto'   => $formProducto->createView(),
        ));

    }

    /**
     * Creates a new detalleCompra entity.
     *
     * @Route("/new", name="detallecompra_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        
        //$fecha = new \DateTime();

        $formProducto = $request->request->get('appbundle_detallecompra_producto');
        $file         = $request->files->get('appbundle_detallecompra_producto')['archivo'];
        $fileVoucher         = $request->files->get('appbundle_detallecompra_producto')['voucher'];

        $proveedor_id       = $formProducto['proveedor_select'];
        $empresa_local_id   = $formProducto['local'];
        $documento          = $formProducto['documento'];
        $numero_documento   = $formProducto['numero_documento'];
        $moneda             = $formProducto['moneda'];
        $observacion        = $formProducto['observacion'];
        $forma_pago         = $formProducto['forma_pago'];
        $numero_dias        = ($formProducto['numero_dias'] != '')? $formProducto['numero_dias'] : 0;
        $condicion_pago     = ($formProducto['condicion'])? $formProducto['condicion'] : null;

        $documento_relacionado_notacredito        = ($formProducto['doc_rel_notacredito']) ? $formProducto['doc_rel_notacredito']: '';
        $monto_notacredito        = ($formProducto['valor_notacredito'] != '') ? $formProducto['valor_notacredito'] : 0;
        $numero_notacredito       = ($formProducto['num_notacredito'] ) ? $formProducto['num_notacredito'] : '';
        
        $fecha = \DateTime::createFromFormat('d/m/Y', $formProducto['fecha']);

        $localObj   = $em->getRepository('AppBundle:EmpresaLocal')->find($empresa_local_id);

        $ultimoIdFacturaCompra = ($em->getRepository('AppBundle:FacturaCompra')->findLastId())?$em->getRepository('AppBundle:FacturaCompra')->findLastId()->getId():1;

        $ultimoIdFacturaCompra++;
        $num_ticket = ($localObj->getPrefijoVoucher())?$localObj->getPrefijoVoucher().$ultimoIdFacturaCompra:$ultimoIdFacturaCompra;

        //Guardamos el objeto Compra
        $compraObj = new Compra();
        $empleado = $em->getRepository('AppBundle:Empleado')->find($session->get('empleado'));
        $compraObj->setEmpleado($empleado);
        $compraObj->setFecha($fecha);
        $em->persist($compraObj);

        //Guardamos el objeto FacturaCompra
        $facturacompraObj = new FacturaCompra();
        $proveedor = $em->getRepository('AppBundle:Proveedor')->find($proveedor_id);
        $facturacompraObj->setProveedor($proveedor);
        $facturacompraObj->setCompra($compraObj);
        $facturacompraObj->setFecha($fecha);
        $facturacompraObj->setTicket($num_ticket);
        $facturacompraObj->setDocumento($documento);
        $facturacompraObj->setNumeroDocumento($numero_documento);
        $facturacompraObj->setLocal($localObj);
        $facturacompraObj->setObservacion($observacion);
        $facturacompraObj->setEstado(true);

        $facturacompraObj->setDocumentoRelacionadoNotacredito($documento_relacionado_notacredito);
        $facturacompraObj->setMontoNotacredito($monto_notacredito);
        $facturacompraObj->setNumeroNotacredito($numero_notacredito);


        if($file){
            //Guardamos el archivo adjunto si existiera
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

            $file->move(
                $this->getParameter('archivos_directorio').'/'.$empresa,
                $fileName
            );

            $facturacompraObj->setArchivo($fileName);

            $ruta = $this->getParameter('archivos_directorio').'/'.$empresa.'/'.$fileName;
        }
          
        if($fileVoucher){
            //Guardamos el archivo adjunto si existiera
            $fileNameVoucher = $this->generateUniqueFileName().'.'.$fileVoucher->guessExtension();

            $fileVoucher->move(
                $this->getParameter('archivos_directorio').'/'.$empresa,
                $fileNameVoucher
            );

            $facturacompraObj->setVoucher($fileNameVoucher);

            $ruta = $this->getParameter('archivos_directorio').'/'.$empresa.'/'.$fileNameVoucher;
        }

        $em->persist($facturacompraObj);

        switch ($documento) {
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
        
        //Guardamos el objeto Transferencia
        $transferencia  = new Transferencia();
        $transferencia->setLocalInicio(null);
        $transferencia->setLocalFin($localObj);
        $transferencia->setFecha($fecha);
        $usuario    = $em->getRepository('AppBundle:Usuario')->find($session->get('usuario'));
        $transferencia->setUsuario($usuario);
        $transferencia->setNumeroDocumento($numero_documento);
        $motivoTraslado  = $em->getRepository('AppBundle:MotivoTraslado')->findOneBy(array('codigo'=>'02'));
        $transferencia->setMotivoTraslado($motivoTraslado);
        $empresaObj   = $em->getRepository('AppBundle:Empresa')->find($empresa);
        $transferencia->setEmpresa($empresaObj);
        $transferencia->setEstado(true);
        $transferencia->setFacturaCompra($facturacompraObj);
        $em->persist($transferencia);


        $productosXLocal  = $em->getRepository('AppBundle:ProductoXLocal')->findBy(array('local'=>$localObj->getId()));

        $pago_total = 0;
        foreach($productosXLocal as $productoXLocal){

            if(null !== $request->request->get('productoid_'.$productoXLocal->getId()) ){

                $producto_id = (int)$request->request->get('productoid_'.$productoXLocal->getId());

                if($producto_id){                    

                    $precio     = (double)$request->request->get('precio_'.$producto_id);
                    $cantidad   = (double)$request->request->get('cantidad_'.$producto_id);

                    $subtotal = $cantidad*$precio;

                    //Guardamos el detalle de Venta
                    $detalleCompraObj = new DetalleCompra();
                    $detalleCompraObj->setCompra($compraObj);
                    $detalleCompraObj->setProductoXLocal($productoXLocal);
                    $detalleCompraObj->setCantidad($cantidad);
                    $detalleCompraObj->setPrecio($precio);
                    $detalleCompraObj->setSubtotal($subtotal);
                    $detalleCompraObj->setProveedor($proveedor);
                    $em->persist($detalleCompraObj);

                    //Guardamos el detalle de la transferencia
                    $transferenciaXProducto = new TransferenciaXProducto();
                    $transferenciaXProducto->setProductoXLocal($productoXLocal);
                    $transferenciaXProducto->setCantidad($cantidad);
                    $transferenciaXProducto->setPrecio($precio);
                    $transferenciaXProducto->setTransferencia($transferencia);
                    $transferenciaXProducto->setContador($cantidad);
                    $em->persist($transferenciaXProducto);

                    $pago_total += $subtotal;


                    if($precio > 0){

                        //Actualizamos el precio de compra de producto. Definimos un valor ponderado, stock de lo que se compra y lo que hay en almacen
                        $unidadCompra = $productoXLocal->getProducto()->getUnidadCompra();
                        $ratio = (float)$unidadCompra->getRatio();

                        $precio_compra  = ($productoXLocal->getProducto()->getPrecioCompra()) ? $productoXLocal->getProducto()->getPrecioCompra() : 0;
                        $stock          = $productoXLocal->getStock();

                        if($stock < 0){
                            $stock = abs($stock);
                            $precio_compra = $precio;
                        }
                        
                        $stock_total = $stock + ($cantidad * $ratio);
                        if($precio_compra == 0 ){
                            $stock_total = $cantidad * $ratio;
                        }

                        $precio_ponderado = number_format(($precio_compra * $stock + $subtotal) / $stock_total,2,'.','');

                        $productoXLocal->getProducto()->setPrecioPonderado($precio_ponderado);
                        $productoXLocal->getProducto()->setPrecioCompra($precio);
                        $em->persist($productoXLocal);

                    }
                  

                    $this->container->get('AppBundle\Util\Util')->aumentarAlmacen($producto_id,$cantidad);
                    //$this->container->get('AppBundle\Util\Util')->registrarSunatF121($facturacompraObj,$productoXLocal,$codigo_tipo_sunat,'02',$cantidad);

                }

            }
        

        }


        //Guardamos el objeto CompraFormaPago
        $compraformapagoObj = new CompraFormaPago();
        $monedaObj = $em->getRepository('AppBundle:Moneda')->find($moneda);
        $compraformapagoObj->setMoneda($monedaObj);
        $compraformapagoObj->setCompra($compraObj);
        $compraformapagoObj->setCantidad($pago_total);
        $compraformapagoObj->setNumeroDias($numero_dias);

        $formaPago = $em->getRepository('AppBundle:FormaPago')->find($forma_pago);
        $compraformapagoObj->setFormaPago($formaPago);
        $compraformapagoObj->setCondicion($condicion_pago);
        $em->persist($compraformapagoObj);        


        try {

            $em->flush();
            $this->addFlash("success", "El registro fue guardado exitosamente.");

        } catch (\Exception $e) {

            $this->addFlash("danger", $e."-Ocurrió un error inesperado, el registro no se guardó.");
            return $this->redirectToRoute('detallecompra_index');                
        }

        return $this->redirectToRoute('detallecompra_index');


    }

    /**
     * Finds and displays a detalleCompra entity.
     *
     * @Route("/{id}", name="detallecompra_show")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR')")
     */
    public function showAction(DetalleCompra $detalleCompra)
    {

        return $this->render('detallecompra/show.html.twig', array(
            'detalleCompra' => $detalleCompra,
        ));
    }

    /**
     * Displays a form to edit an existing detalleCompra entity.
     *
     * @Route("/{id}/edit", name="detallecompra_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function editAction(Request $request, DetalleCompra $detalleCompra)
    {
        $editForm = $this->createForm('AppBundle\Form\DetalleCompraType', $detalleCompra);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('detallecompra_edit', array('id' => $detalleCompra->getId()));
        }

        return $this->render('detallecompra/edit.html.twig', array(
            'detalleCompra' => $detalleCompra,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a detalleCompra entity.
     *
     * @Route("/{id}", name="detallecompra_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR')")
     */
    public function deleteAction(Request $request, DetalleCompra $detalleCompra)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($detalleCompra);
            $em->flush();
        }

        return $this->redirectToRoute('detallecompra_index');
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
