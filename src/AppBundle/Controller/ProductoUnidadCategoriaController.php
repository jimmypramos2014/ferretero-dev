<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProductoUnidadCategoria;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Productounidadcategorium controller.
 *
 * @Route("productounidadcategoria")
 */
class ProductoUnidadCategoriaController extends Controller
{
    /**
     * Lists all productoUnidadCategorium entities.
     *
     * @Route("/", name="productounidadcategoria_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $productoUnidadCategorias = $em->getRepository('AppBundle:ProductoUnidadCategoria')->findAll();

        return $this->render('productounidadcategoria/index.html.twig', array(
            'productoUnidadCategorias' => $productoUnidadCategorias,
        ));
    }

    /**
     * Creates a new productoUnidadCategorium entity.
     *
     * @Route("/new", name="productounidadcategoria_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $productoUnidadCategorium = new ProductoUnidadCategoria();
        $form = $this->createForm('AppBundle\Form\ProductoUnidadCategoriaType', $productoUnidadCategorium,array('empresa'=>$empresaObj));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($productoUnidadCategorium);

            try {

                $em->flush();
                $respuesta['mensaje']   = 'El registro fue guardado exitosamente.';
                $respuesta['opcion']    = '<option value="'.$productoUnidadCategorium->getId().'" selected>'.$productoUnidadCategorium->getNombre().'</option>';

                //$this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {
                $respuesta['mensaje']   = 'Ocurrió un error inesperado, el registro no se guardó.';
                //$this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");                
            }

            $response = new JsonResponse();
            $response->setData($respuesta);

            return $response;

            //return $this->redirectToRoute('productounidadcategoria_index');
        }

        return $this->render('productounidadcategoria/new.html.twig', array(
            'productoUnidadCategorium' => $productoUnidadCategorium,
            'form' => $form->createView(),
            'titulo' => 'Registro de categoría'
        ));
    }

    /**
     * Finds and displays a productoUnidadCategorium entity.
     *
     * @Route("/{id}", name="productounidadcategoria_show")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function showAction(ProductoUnidadCategoria $productoUnidadCategorium)
    {
        $deleteForm = $this->createDeleteForm($productoUnidadCategorium);

        return $this->render('productounidadcategoria/show.html.twig', array(
            'productoUnidadCategorium' => $productoUnidadCategorium,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing productoUnidadCategorium entity.
     *
     * @Route("/{id}/edit", name="productounidadcategoria_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function editAction(Request $request, ProductoUnidadCategoria $productoUnidadCategorium)
    {
        $deleteForm = $this->createDeleteForm($productoUnidadCategorium);
        $editForm = $this->createForm('AppBundle\Form\ProductoUnidadCategoriaType', $productoUnidadCategorium);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('productounidadcategoria_edit', array('id' => $productoUnidadCategorium->getId()));
        }

        return $this->render('productounidadcategoria/edit.html.twig', array(
            'productoUnidadCategorium' => $productoUnidadCategorium,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a productoUnidadCategorium entity.
     *
     * @Route("/{id}", name="productounidadcategoria_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function deleteAction(Request $request, ProductoUnidadCategoria $productoUnidadCategorium)
    {
        $form = $this->createDeleteForm($productoUnidadCategorium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($productoUnidadCategorium);
            $em->flush();
        }

        return $this->redirectToRoute('productounidadcategoria_index');
    }

    /**
     * Creates a form to delete a productoUnidadCategorium entity.
     *
     * @param ProductoUnidadCategoria $productoUnidadCategorium The productoUnidadCategorium entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ProductoUnidadCategoria $productoUnidadCategorium)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('productounidadcategoria_delete', array('id' => $productoUnidadCategorium->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
