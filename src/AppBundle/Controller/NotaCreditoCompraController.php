<?php

namespace AppBundle\Controller;

use AppBundle\Entity\NotaCreditoCompra;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Notacreditocompra controller.
 *
 * @Route("notacreditocompra")
 */
class NotaCreditoCompraController extends Controller
{
    /**
     * Lists all notaCreditoCompra entities.
     *
     * @Route("/", name="notacreditocompra_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');        

        $notaCreditoCompras = $em->getRepository('AppBundle:NotaCreditoCompra')->findBy(array('local'=>$local,'estado'=>true));

        return $this->render('notacreditocompra/index.html.twig', array(
            'notaCreditoCompras' => $notaCreditoCompras,
            'titulo' => 'Notas de crédito - Compras',
        ));
    }

    /**
     * Creates a new notaCreditoCompra entity.
     *
     * @Route("/new", name="notacreditocompra_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');      

        $notaCreditoCompra = new Notacreditocompra();
        $form = $this->createForm('AppBundle\Form\NotaCreditoCompraType', $notaCreditoCompra,array('local'=>$local));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $valor = $notaCreditoCompra->getValor();

            $total = 0;
            foreach($notaCreditoCompra->getFacturaCompra() as $factura){

                $cant = $factura->getCompra()->getCompraFormaPago()[0]->getCantidad();
                
                $total = $total + $cant;

            }

            foreach($notaCreditoCompra->getFacturaCompra() as $facturaCompra){

                $monto   = $facturaCompra->getCompra()->getCompraFormaPago()[0]->getCantidad();
                $porcentaje = $monto/$total;
                $descuento  = $monto - ($valor * $porcentaje);

                $facturaCompra->getCompra()->getCompraFormaPago()[0]->setCantidad($descuento);
                $em->persist($facturaCompra);

                $monto_descontado = $monto - $descuento;

                foreach($facturaCompra->getCompra()->getDetalleCompra() as $detalleCompra ){

                    $precio         = $detalleCompra->getPrecio();
                    $cantidad       = $detalleCompra->getCantidad();
                    $subtotal       = $detalleCompra->getSubtotal();

                    $porc           = $subtotal/$monto;
                    $nuevo_subtotal = $subtotal - ($porc * $monto_descontado);

                    $nuevo_precio   = $nuevo_subtotal/$cantidad;

                    //Actualizamos precio y subtotal
                    $detalleCompra->setPrecio($nuevo_precio);
                    $detalleCompra->setSubtotal($nuevo_subtotal);
                    $em->persist($detalleCompra);


                }

            }
            

            $em->persist($notaCreditoCompra);

            try {

                $em->flush();
                $this->addFlash("success", "El registro fue registrado exitosamente."); 
                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
                
            }
            

            return $this->redirectToRoute('notacreditocompra_index');
        }

        return $this->render('notacreditocompra/new.html.twig', array(
            'notaCreditoCompra' => $notaCreditoCompra,
            'form' => $form->createView(),
            'titulo' => 'Registrar nota de crédito',
        ));
    }


    /**
     * Displays a form to edit an existing notaCreditoCompra entity.
     *
     * @Route("/{id}/edit", name="notacreditocompra_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, NotaCreditoCompra $notaCreditoCompra)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');     

        $editForm = $this->createForm('AppBundle\Form\NotaCreditoCompraType', $notaCreditoCompra,array('local'=>$local));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            try {

                $em->flush();
                $this->addFlash("success", "El registro fue registrado exitosamente."); 

            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
                
            }

            return $this->redirectToRoute('notacreditocompra_edit', array('id' => $notaCreditoCompra->getId()));
        }

        return $this->render('notacreditocompra/edit.html.twig', array(
            'notaCreditoCompra' => $notaCreditoCompra,
            'edit_form' => $editForm->createView(),
            'titulo' => 'Editar nota de crédito',
        ));
    }

    /**
     * Deletes a notaCreditoCompra entity.
     *
     * @Route("/{id}/delete", name="notacreditocompra_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, NotaCreditoCompra $notaCreditoCompra)
    {

        $em = $this->getDoctrine()->getManager();

        $valor = $notaCreditoCompra->getValor();

        $total = 0;
        foreach($notaCreditoCompra->getFacturaCompra() as $factura){

            $cant = $factura->getCompra()->getCompraFormaPago()[0]->getCantidad();
            
            $total = $total + $cant;

        }

        foreach($notaCreditoCompra->getFacturaCompra() as $facturaCompra){

            $monto   = $facturaCompra->getCompra()->getCompraFormaPago()[0]->getCantidad();
            $porcentaje = $monto/$total;
            $aumento  = $monto + ($valor * $porcentaje);

            $facturaCompra->getCompra()->getCompraFormaPago()[0]->setCantidad($aumento);
            $em->persist($facturaCompra);

            $monto_aumentado = $aumento - $monto;

            foreach($facturaCompra->getCompra()->getDetalleCompra() as $detalleCompra ){

                $cantidad       = $detalleCompra->getCantidad();
                $subtotal       = $detalleCompra->getSubtotal();

                $porc           = $subtotal/$monto;
                $nuevo_subtotal = $subtotal + ($porc * $monto_aumentado);

                $nuevo_precio   = $nuevo_subtotal/$cantidad;

                //Actualizamos precio y subtotal
                $detalleCompra->setPrecio($nuevo_precio);
                $detalleCompra->setSubtotal($nuevo_subtotal);
                $em->persist($detalleCompra);


            }

        }



        $em->remove($notaCreditoCompra);

        try {

            $em->flush();
            $this->addFlash("success", "El registro fue eliminado exitosamente."); 
            
        } catch (\Exception $e) {

            $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se eliminó.");
            
        }
        


        return $this->redirectToRoute('notacreditocompra_index');
    }

}
