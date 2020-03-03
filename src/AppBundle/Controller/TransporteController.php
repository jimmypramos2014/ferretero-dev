<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Transporte;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Transporte controller.
 *
 * @Route("transporte")
 */
class TransporteController extends Controller
{
    /**
     * Lists all transporte entities.
     *
     * @Route("/", name="transporte_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $transportes = $em->getRepository('AppBundle:Transporte')->findAll();

        return $this->render('transporte/index.html.twig', array(
            'transportes' => $transportes,
        ));
    }

    /**
     * Creates a new transporte entity.
     *
     * @Route("/new", name="transporte_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $response = '';

        if($request->isXmlHttpRequest()){

            $transporte = new Transporte();
            $form = $this->createForm('AppBundle\Form\TransporteType', $transporte,array('empresa'=>$empresa));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $em->persist($transporte);
                $em->flush();

                $response = new JsonResponse();
                $response->setStatusCode(200);
                $response->setData(array(
                    'response'  => 'success',
                    'empresatransporte' => '<option value="'.$transporte->getId().'" selected>'.$transporte->getNombre().'</option>',
                ));
                
            }
            
        }

        return $response;
    }

    /**
     * Finds and displays a transporte entity.
     *
     * @Route("/{id}", name="transporte_show")
     * @Method("GET")
     */
    public function showAction(Transporte $transporte)
    {
        $deleteForm = $this->createDeleteForm($transporte);

        return $this->render('transporte/show.html.twig', array(
            'transporte' => $transporte,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing transporte entity.
     *
     * @Route("/{id}/edit", name="transporte_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Transporte $transporte)
    {
        $deleteForm = $this->createDeleteForm($transporte);
        $editForm = $this->createForm('AppBundle\Form\TransporteType', $transporte);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('transporte_edit', array('id' => $transporte->getId()));
        }

        return $this->render('transporte/edit.html.twig', array(
            'transporte' => $transporte,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a transporte entity.
     *
     * @Route("/{id}", name="transporte_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Transporte $transporte)
    {
        $form = $this->createDeleteForm($transporte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($transporte);
            $em->flush();
        }

        return $this->redirectToRoute('transporte_index');
    }

    /**
     * Creates a form to delete a transporte entity.
     *
     * @param Transporte $transporte The transporte entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Transporte $transporte)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('transporte_delete', array('id' => $transporte->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
