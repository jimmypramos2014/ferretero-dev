<?php

namespace AppBundle\Controller;

use AppBundle\Entity\GuiaRemision;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Guiaremision controller.
 *
 * @Route("guiaremision")
 */
class GuiaRemisionController extends Controller
{
    /**
     * Lists all guiaRemision entities.
     *
     * @Route("/", name="guiaremision_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $caja           = $session->get('caja');
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');


        $guiaRemisions = $em->getRepository('AppBundle:GuiaRemision')->findBy(array('estado'=>true,'local'=>$local));

        return $this->render('guiaremision/index.html.twig', array(
            'guiaRemisions' => $guiaRemisions,
            'titulo' => 'Lista de guias de remisión'
        ));
    }

    /**
     * Creates a new guiaRemision entity.
     *
     * @Route("/new", name="guiaremision_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $caja           = $session->get('caja');
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $guiaRemision = new GuiaRemision();
        $form = $this->createForm('AppBundle\Form\GuiaRemisionType', $guiaRemision,array('empresa'=>$empresa));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {

            $fecha_emision = new \DateTime();

            $facturas_relacionadas = $request->request->get('appbundle_guiaremision')['facturaVenta'];

            $productos_array = array();
            $j=0;
            foreach($facturas_relacionadas as $j=>$factura_relacionada)
            {
                $facturaVenta = $em->getRepository('AppBundle:FacturaVenta')->find($factura_relacionada);
                $transferencia = $em->getRepository('AppBundle:Transferencia')->findOneBy(array('facturaVenta'=>$factura_relacionada));
                $guiaRemision->addTransferencia($transferencia);

                $localObj = $facturaVenta->getLocal();

                foreach($facturaVenta->getVenta()->getDetalleVenta() as $detalle)
                {

                    //Armamos el array q se va enviar a nubefact
                    $tipo           = $detalle->getProductoXLocal()->getProducto()->getTipo();
                    $codigo         = $detalle->getProductoXLocal()->getProducto()->getCodigo();
                    $descripcion    = $detalle->getProductoXLocal()->getProducto()->getNombre();
                    $cantidad       = $detalle->getCantidad();

                    $productos_array[$j]['tipo'] = $tipo;
                    $productos_array[$j]['codigo'] = $codigo;
                    $productos_array[$j]['descripcion'] = $descripcion;
                    $productos_array[$j]['cantidad'] = $cantidad;

                    $j++;                         

                }               


            }

            $serie = ($localObj->getSerieGuiaremision()) ? $localObj->getSerieGuiaremision() : null;
            $empresa_mismo_prefijo_multilocal = ($localObj->getEmpresa()->getPermitirMismoPrefijoMultilocal()) ? $empresa : '';
            $numero = (int)$this->container->get('AppBundle\Util\Util')->generarNumeroGuiaremision('guia_remision',$localObj->getId(),$empresa_mismo_prefijo_multilocal);
            $numero = $numero + 1;

           
            $guiaRemision->setEstado(true);
            $guiaRemision->setLocal($localObj);
            $guiaRemision->setSerie($serie);
            $guiaRemision->setNumero($numero);
            $guiaRemision->setFechaEmision($fecha_emision);


            //Enviamos la guia de remision a nubefact
            $respuesta = $this->container->get('AppBundle\Util\Util')->enviarGuiaRemision($guiaRemision,$productos_array);
            $respuesta = json_decode($respuesta, true);

            if(isset($respuesta['errors']))
            {
                $this->addFlash("danger"," Ocurrió un error, el registro no fue enviado.".$respuesta['errors']);
                return $this->redirectToRoute('guiaremision_index');
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
                
                $this->addFlash("success", "El registro fue realizado exitosamente.");
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
                
            }
            

            return $this->redirectToRoute('guiaremision_index');
        }

        return $this->render('guiaremision/new.html.twig', array(
            'guiaRemision' => $guiaRemision,
            'form' => $form->createView(),
            'titulo' => 'Registrar guía'
        ));
    }

    /**
     * Finds and displays a guiaRemision entity.
     *
     * @Route("/{id}", name="guiaremision_show")
     * @Method("GET")
     */
    public function showAction(GuiaRemision $guiaRemision)
    {
        $deleteForm = $this->createDeleteForm($guiaRemision);

        return $this->render('guiaremision/show.html.twig', array(
            'guiaRemision' => $guiaRemision,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing guiaRemision entity.
     *
     * @Route("/{id}/edit", name="guiaremision_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, GuiaRemision $guiaRemision)
    {
        $deleteForm = $this->createDeleteForm($guiaRemision);
        $editForm = $this->createForm('AppBundle\Form\GuiaRemisionType', $guiaRemision);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('guiaremision_edit', array('id' => $guiaRemision->getId()));
        }

        return $this->render('guiaremision/edit.html.twig', array(
            'guiaRemision' => $guiaRemision,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a guiaRemision entity.
     *
     * @Route("/{id}", name="guiaremision_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, GuiaRemision $guiaRemision)
    {
        $form = $this->createDeleteForm($guiaRemision);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($guiaRemision);
            $em->flush();
        }

        return $this->redirectToRoute('guiaremision_index');
    }

    /**
     * Creates a form to delete a guiaRemision entity.
     *
     * @param GuiaRemision $guiaRemision The guiaRemision entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(GuiaRemision $guiaRemision)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('guiaremision_delete', array('id' => $guiaRemision->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
