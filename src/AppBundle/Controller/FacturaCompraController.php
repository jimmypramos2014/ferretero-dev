<?php

namespace AppBundle\Controller;

use AppBundle\Entity\FacturaCompra;
use AppBundle\Entity\Compra;
use AppBundle\Entity\TransferenciaXProducto;
use AppBundle\Entity\Transferencia;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Facturacompra controller.
 *
 * @Route("facturacompra")
 */
class FacturaCompraController extends Controller
{
    /**
     * Lists all facturaCompra entities.
     *
     * @Route("/", name="facturacompra_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $dql = "SELECT fc FROM AppBundle:FacturaCompra fc ";
        $dql .= " JOIN fc.compra c";
        $dql .= " JOIN c.empleado e";
        $dql .= " JOIN e.local el";
        $dql .= " JOIN el.empresa em";
        $dql .= " WHERE  fc.estado = 1  ";

        if($empresa != ''){
            $dql .= " AND em.id =:empresa  ";
        }

        $dql .= " ORDER BY fc.fecha DESC ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
        $facturaCompras =  $query->getResult();   

        //$facturaCompras = $em->getRepository('AppBundle:FacturaCompra')->findBy(array('estado'=>true),array('fecha' => 'DESC'));

        return $this->render('facturacompra/index.html.twig', array(
            'facturaCompras' => $facturaCompras,
            'titulo'         => 'Lista de compras'
        ));
    }

    /**
     * Creates a new facturaCompra entity.
     *
     * @Route("/new", name="facturacompra_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function newAction(Request $request)
    {
        $facturaCompra = new Facturacompra();
        $form = $this->createForm('AppBundle\Form\FacturaCompraType', $facturaCompra);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($facturaCompra);
            $em->flush();

            return $this->redirectToRoute('facturacompra_show', array('id' => $facturaCompra->getId()));
        }

        return $this->render('facturacompra/new.html.twig', array(
            'facturaCompra' => $facturaCompra,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a facturaCompra entity.
     *
     * @Route("/{id}", name="facturacompra_show")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function showAction(Compra $compra)
    {
        $em = $this->getDoctrine()->getManager();

        $detalleCompras = $em->getRepository('AppBundle:DetalleCompra')->findBy(array('compra'=>$compra));

        return $this->render('facturacompra/show.html.twig', array(
            'detalleCompras' => $detalleCompras,
            'titulo'         => 'Detalle de compra'
        ));
    }

    /**
     * Displays a form to edit an existing factura compra entity.
     *
     * @Route("/{id}/edit", name="facturacompra_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function editAction(Request $request, FacturaCompra $facturaCompra)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $fecha = new \DateTime();

        $originalArchivo = null;

        if($facturaCompra->getArchivo()){

            $originalArchivo = $facturaCompra->getArchivo();

            $facturaCompra->setArchivo(
                new File($this->getParameter('archivos_directorio').'/'.$empresa.'/'.$facturaCompra->getArchivo())
            );
        }

        $originalVoucher = null;

        if($facturaCompra->getVoucher()){

            $originalVoucher = $facturaCompra->getVoucher();

            $facturaCompra->setVoucher(
                new File($this->getParameter('archivos_directorio').'/'.$empresa.'/'.$facturaCompra->getVoucher())
            );
        }

        $numeroDocumentoAnterior = $facturaCompra->getNumeroDocumento();
        $detalleCompraAnterior   = $facturaCompra->getCompra()->getDetalleCompra();

        $detalleArray = array();
        $j = 0;
        foreach($detalleCompraAnterior as $detalleAnterior){

            if($detalleAnterior->getProductoXLocal()){

                $detalleArray[$j]['id'] = $detalleAnterior->getProductoXLocal()->getId();
                $detalleArray[$j]['cantidad'] = $detalleAnterior->getCantidad();

                $j++;

            }

        }
        

        $localObj   = $em->getRepository('AppBundle:EmpresaLocal')->find($local);
        $formProducto = $this->createForm('AppBundle\Form\DetalleCompraProductoType', $facturaCompra,array('local'=>$local,'empresa'=>$empresa));

        $tipoCambio = $em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$empresa,'fecha'=>$fecha));

        $editForm = $this->createForm('AppBundle\Form\FacturaCompraType', $facturaCompra,array('empresa'=>$empresa,'local'=>$local));
        $editForm->handleRequest($request);


        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $hora = date('H');
            $minuto = date('i');
            $segundo = date('s');

            $intervalo_adicional = 'PT'.$hora.'H'.$minuto.'M'.$segundo.'S';
            $editForm->getData()->getFecha()->add(new \DateInterval($intervalo_adicional));
            
            //Guardamos el archivo
            $fileArchivo = $facturaCompra->getArchivo();

            if($fileArchivo){

                $fileNameArchivo = $this->generateUniqueFileName().'.'.$fileArchivo->guessExtension();

                $fileArchivo->move(
                    $this->getParameter('archivos_directorio').'/'.$empresa,
                    $fileNameArchivo
                );


                $facturaCompra->setArchivo($fileNameArchivo);

            }else{

                $facturaCompra->setArchivo($originalArchivo);
            }

            //Guardamos el voucher de pago
            $fileVoucher = $facturaCompra->getVoucher();

            if($fileVoucher){

                $fileNameVoucher= $this->generateUniqueFileName().'.'.$fileVoucher->guessExtension();

                $fileVoucher->move(
                    $this->getParameter('archivos_directorio').'/'.$empresa,
                    $fileNameVoucher
                );


                $facturaCompra->setVoucher($fileNameVoucher);

            }else{

                $facturaCompra->setVoucher($originalVoucher);
            }


            // $dql = "SELECT t FROM AppBundle:Transferencia t ";
            // $dql .= " JOIN t.motivoTraslado mt";
            // $dql .= " JOIN t.empresa e";
            // $dql .= " WHERE  t.estado = 1  AND mt.id = 2  ";

            // if($empresa != ''){
            //     $dql .= " AND e.id =:empresa  ";
            // }

            // $dql .= " AND t.numeroDocumento =:numeroDocumento ";

            // $query = $em->createQuery($dql);

            // if($empresa != ''){
            //     $query->setParameter('empresa',$empresa);         
            // }

            // $query->setParameter('numeroDocumento',$numeroDocumentoAnterior);
     
            // $transferencia =  $query->getOneOrNullResult();

            $transferencia = $em->getRepository('AppBundle:Transferencia')->findOneBy(array('facturaCompra'=>$facturaCompra));

            if($transferencia){

                $transferencia->setNumeroDocumento($editForm->getData()->getNumeroDocumento());
                $em->persist($transferencia);

                foreach($transferencia->getTransferenciaXProducto() as $transferenciaXProducto){

                    $em->remove($transferenciaXProducto);

                }

            }


            foreach($detalleArray as $p=>$det){

                $this->container->get('AppBundle\Util\Util')->disminuirAlmacen($det['id'],$det['cantidad']);
            }


            $total = 0;
            foreach($editForm->getData()->getCompra()->getDetalleCompra() as $detalle){

                if($detalle->getProductoXLocal()){

                    $total = $total + $detalle->getSubtotal();

                    $precio   = $detalle->getPrecio();
                    $cantidad = $detalle->getCantidad();


                    if($transferencia){

                        //Guardamos el detalle de la transferencia
                        $transferenciaXProductoNuevo = new TransferenciaXProducto();
                        $transferenciaXProductoNuevo->setProductoXLocal($detalle->getProductoXLocal());
                        $transferenciaXProductoNuevo->setCantidad($cantidad);
                        $transferenciaXProductoNuevo->setTransferencia($transferencia);
                        $transferenciaXProductoNuevo->setPrecio($precio);
                        $transferenciaXProductoNuevo->setContador($cantidad);
                        $em->persist($transferenciaXProductoNuevo);
                    
                    }

                    $this->container->get('AppBundle\Util\Util')->aumentarAlmacen($detalle->getProductoXLocal()->getId(),$cantidad);

                    $detalle->getProductoXLocal()->getProducto()->setPrecioCompra($precio);
                    $detalle->setProveedor($facturaCompra->getProveedor());
                    $em->persist($detalle);   
                }

            }

            $facturaCompra->getCompra()->getCompraFormaPago()[0]->setCantidad($total);


            $em->persist($facturaCompra);


            try {

                $em->flush();
                
                $this->addFlash("success", "El registro fue editado exitosamente."); 

                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
                
            }   

            return $this->redirectToRoute('facturacompra_index');
        }

        return $this->render('facturacompra/edit.html.twig', array(
            'edit_form'         => $editForm->createView(),
            'formProducto'      => $formProducto->createView(),
            'local'             => $local,
            'tipoCambio'        => $tipoCambio,
            'facturaCompra'     => $facturaCompra,
            'originalVoucher'   => $originalVoucher,
            'originalArchivo'   => $originalArchivo,
            'titulo'            => 'Editar compra',
        ));
    }

    /**
     * Deletes a facturacompra entity.
     *
     * @Route("/{id}/delete", name="facturacompra_delete")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function deleteAction(Request $request, FacturaCompra $facturaCompra)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $facturaCompra->setEstado(false);
        $em->persist($facturaCompra);

        //disminuir almacen
        foreach($facturaCompra->getCompra()->getDetalleCompra() as $detalle){

            $this->container->get('AppBundle\Util\Util')->disminuirAlmacen($detalle->getProductoXLocal()->getId(),$detalle->getCantidad());

        }

        $transferencias = $em->getRepository('AppBundle:Transferencia')->findBy(array('numeroDocumento'=>$facturaCompra->getNumeroDocumento(),'empresa'=>$empresa));

        foreach($transferencias as $transferencia){

            $transferencia->setEstado(false);
            $transferencia->setNumeroDocumento('eliminado');
            $em->persist($transferencia);

        }        

        try{

            $em->flush();

            $this->addFlash("success", "El registro fue eliminado exitosamente.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser eliminado.");
        }
        return $this->redirectToRoute('facturacompra_index');
    }


    /**
     * Lists all facturaCompra entities.
     *
     * @Route("/lista/anular", name="facturacompra_lista")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function listaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $dql = "SELECT fc FROM AppBundle:FacturaCompra fc ";
        $dql .= " JOIN fc.compra c";
        $dql .= " JOIN c.empleado e";
        $dql .= " JOIN e.local el";
        $dql .= " JOIN el.empresa em";
        $dql .= " WHERE  fc.estado = 1  ";

        if($empresa != ''){
            $dql .= " AND em.id =:empresa  ";
        }

        $dql .= " ORDER BY fc.fecha DESC ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
        $facturaCompras =  $query->getResult();   

        return $this->render('facturacompra/lista.html.twig', array(
            'facturaCompras' => $facturaCompras,
            'titulo'         => 'Anular compra'
        ));
    }


    /**
     * Lista las Notas de credito por tus compras.
     *
     * @Route("/notacredito", name="facturacompra_notacredito")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function notacreditoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $dql = "SELECT fc FROM AppBundle:FacturaCompra fc ";
        $dql .= " JOIN fc.compra c";
        $dql .= " JOIN c.empleado e";
        $dql .= " JOIN e.local el";
        $dql .= " JOIN el.empresa em";
        $dql .= " WHERE  fc.estado = 1  ";

        if($empresa != ''){
            $dql .= " AND em.id =:empresa  ";
        }

        $dql .= " ORDER BY fc.fecha DESC ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
        $facturaCompras =  $query->getResult();   

        return $this->render('facturacompra/notacredito.html.twig', array(
            'facturaCompras' => $facturaCompras,
            'titulo'         => 'Notas de crédito'
        ));
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
