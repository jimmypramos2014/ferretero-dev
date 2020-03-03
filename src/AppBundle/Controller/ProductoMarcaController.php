<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProductoMarca;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * ProductoMarca controller.
 *
 * @Route("productomarca")
 */
class ProductoMarcaController extends Controller
{
    /**
     * Lists all productoMarca entities.
     *
     * @Route("/", name="productomarca_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $productoMarcas = $em->getRepository('AppBundle:ProductoMarca')->findBy(array('empresa'=>$empresa));

        return $this->render('productomarca/index.html.twig', array(
            'productoMarcas' => $productoMarcas,
            'titulo' => 'Lista de marcas',
        ));
    }

    /**
     * Creates a new productoMarca entity.
     *
     * @Route("/new", name="productomarca_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $productoMarca = new ProductoMarca();
        $form = $this->createForm('AppBundle\Form\ProductoMarcaType', $productoMarca);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($productoMarca);

            try {
                $em->flush();
            } catch (\Exception $e) {
                return $e;
            }
            

            return $this->redirectToRoute('productomarca_index');
        }

        return $this->render('productomarca/new.html.twig', array(
            'productoMarca' => $productoMarca,
            'form' => $form->createView(),
            'titulo' => 'Registrar marca',
        ));
    }


    /**
     * Displays a form to edit an existing productoMarca entity.
     *
     * @Route("/{id}/edit", name="productomarca_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ProductoMarca $productoMarca)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');


        $editForm = $this->createForm('AppBundle\Form\ProductoMarcaType', $productoMarca);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {


            try {
                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");
            }

            return $this->redirectToRoute('productomarca_index');
        }

        return $this->render('productomarca/edit.html.twig', array(
            'productoMarca' => $productoMarca,
            'edit_form' => $editForm->createView(),
            'titulo' => 'Registrar marca',
        ));
    }

    /**
     * Deletes a productoMarca entity.
     *
     * @Route("/{id}/delete", name="productomarca_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, ProductoMarca $productoMarca)
    {
        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //     $em = $this->getDoctrine()->getManager();
        //     $em->remove($productoCategorium);
        //     $em->flush();
        // }

        return $this->redirectToRoute('productomarca_index');
    }


}
