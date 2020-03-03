<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Moneda;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Moneda controller.
 *
 * @Route("moneda")
 */
class MonedaController extends Controller
{
    /**
     * Lists all moneda entities.
     *
     * @Route("/", name="moneda_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $monedas = $em->getRepository('AppBundle:Moneda')->findAll();

        return $this->render('moneda/index.html.twig', array(
            'monedas' => $monedas,
        ));
    }

    /**
     * Creates a new moneda entity.
     *
     * @Route("/new", name="moneda_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $moneda = new Moneda();
        $form = $this->createForm('AppBundle\Form\MonedaType', $moneda);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($moneda);
            $em->flush();

            return $this->redirectToRoute('moneda_show', array('id' => $moneda->getId()));
        }

        return $this->render('moneda/new.html.twig', array(
            'moneda' => $moneda,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a moneda entity.
     *
     * @Route("/{id}", name="moneda_show")
     * @Method("GET")
     */
    public function showAction(Moneda $moneda)
    {
        $deleteForm = $this->createDeleteForm($moneda);

        return $this->render('moneda/show.html.twig', array(
            'moneda' => $moneda,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing moneda entity.
     *
     * @Route("/{id}/edit", name="moneda_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Moneda $moneda)
    {
        $deleteForm = $this->createDeleteForm($moneda);
        $editForm = $this->createForm('AppBundle\Form\MonedaType', $moneda);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('moneda_edit', array('id' => $moneda->getId()));
        }

        return $this->render('moneda/edit.html.twig', array(
            'moneda' => $moneda,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a moneda entity.
     *
     * @Route("/{id}", name="moneda_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Moneda $moneda)
    {
        $form = $this->createDeleteForm($moneda);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($moneda);
            $em->flush();
        }

        return $this->redirectToRoute('moneda_index');
    }

    /**
     * Creates a form to delete a moneda entity.
     *
     * @param Moneda $moneda The moneda entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Moneda $moneda)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('moneda_delete', array('id' => $moneda->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
