<?php

namespace AppBundle\Controller;

use AppBundle\Entity\TipoCambio;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tipocambio controller.
 *
 * @Route("tipocambio")
 */
class TipoCambioController extends Controller
{
    /**
     * Lists all tipoCambio entities.
     *
     * @Route("/", name="tipocambio_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $tipoCambios = $em->getRepository('AppBundle:TipoCambio')->findBy(array('empresa'=>$empresa));

        return $this->render('tipocambio/index.html.twig', array(
            'tipoCambios' => $tipoCambios,
            'titulo' => 'Lista de tipos de cambio',
        ));
    }

    /**
     * Creates a new tipoCambio entity.
     *
     * @Route("/new", name="tipocambio_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $tipoCambio = new TipoCambio();
        $form = $this->createForm('AppBundle\Form\TipoCambioType', $tipoCambio,array('empresa'=>$empresa));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($tipoCambio);

            try {

                $em->flush();

                $this->addFlash("success", "El registro fue guardado exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." El registro no fue guardado.");
                
            }
            

            return $this->redirectToRoute('tipocambio_index');
        }

        return $this->render('tipocambio/new.html.twig', array(
            'tipoCambio' => $tipoCambio,
            'form' => $form->createView(),
            'titulo' => 'Registrar tipo de cambio',
        ));
    }


    /**
     * Displays a form to edit an existing tipoCambio entity.
     *
     * @Route("/{id}/editar", name="tipocambio_editar")
     * @Method({"GET", "POST"})
     */
    public function editarAction(Request $request, TipoCambio $tipoCambio)
    {
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');


        $editForm = $this->createForm('AppBundle\Form\TipoCambioType', $tipoCambio,array('empresa'=>$empresa));
        $editForm->handleRequest($request);


        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $em = $this->getDoctrine()->getManager();            

            $em->persist($tipoCambio);

            try {

                $em->flush();

                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {

                $this->addFlash("danger", $e." El registro no fue guardado.");

            }

            return $this->redirectToRoute('tipocambio_index');
        }

        return $this->render('tipocambio/edit.html.twig', array(
            'tipoCambio' => $tipoCambio,
            'edit_form' => $editForm->createView(),
            'titulo' => 'Editar tipo de cambio',
        ));
    }


}
