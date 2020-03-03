<?php

namespace AppBundle\Controller;

use AppBundle\Entity\DetalleVentaEntrega;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Detalleventaentrega controller.
 *
 * @Route("detalleventaentrega")
 */
class DetalleVentaEntregaController extends Controller
{
    /**
     * Lists all detalleVentaEntrega entities.
     *
     * @Route("/", name="detalleventaentrega_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $detalleVentaEntregas = $em->getRepository('AppBundle:DetalleVentaEntrega')->findAll();

        return $this->render('detalleventaentrega/index.html.twig', array(
            'detalleVentaEntregas' => $detalleVentaEntregas,
        ));
    }

    /**
     * Creates a new detalleVentaEntrega entity.
     *
     * @Route("/new", name="detalleventaentrega_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $detalleVentaEntrega = new Detalleventaentrega();
        $form = $this->createForm('AppBundle\Form\DetalleVentaEntregaType', $detalleVentaEntrega);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($detalleVentaEntrega);
            $em->flush();

            return $this->redirectToRoute('detalleventaentrega_show', array('id' => $detalleVentaEntrega->getId()));
        }

        return $this->render('detalleventaentrega/new.html.twig', array(
            'detalleVentaEntrega' => $detalleVentaEntrega,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a detalleVentaEntrega entity.
     *
     * @Route("/{id}", name="detalleventaentrega_show")
     * @Method("GET")
     */
    public function showAction(DetalleVentaEntrega $detalleVentaEntrega)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $em = $this->getDoctrine()->getManager();

        return $this->render('detalleventaentrega/show.html.twig', array(
            'detalleVentaEntrega' => $detalleVentaEntrega,
            'titulo' => 'Editar entrega',
        ));
    }

    /**
     * Displays a form to edit an existing detalleVentaEntrega entity.
     *
     * @Route("/{id}/edit", name="detalleventaentrega_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, DetalleVentaEntrega $detalleVentaEntrega)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createForm('AppBundle\Form\DetalleVentaEntregaType', $detalleVentaEntrega);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            try {

                $em->flush();
                
            } catch (\Exception $e) {
                
            }

            return $this->redirectToRoute('detalleventa_entrega', array('id' => $detalleVentaEntrega->getId()));
        }

        return $this->render('detalleventaentrega/edit.html.twig', array(
            'detalleVentaEntrega' => $detalleVentaEntrega,
            'edit_form' => $editForm->createView(),
            'titulo' => 'Editar entrega',
        ));
    }

    /**
     * Deletes a detalleVentaEntrega entity.
     *
     * @Route("/{id}", name="detalleventaentrega_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, DetalleVentaEntrega $detalleVentaEntrega)
    {
        $form = $this->createDeleteForm($detalleVentaEntrega);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($detalleVentaEntrega);
            $em->flush();
        }

        return $this->redirectToRoute('detalleventaentrega_index');
    }

    /**
     * Creates a form to delete a detalleVentaEntrega entity.
     *
     * @param DetalleVentaEntrega $detalleVentaEntrega The detalleVentaEntrega entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(DetalleVentaEntrega $detalleVentaEntrega)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('detalleventaentrega_delete', array('id' => $detalleVentaEntrega->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
