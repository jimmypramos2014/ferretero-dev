<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProductoUnidad;
use AppBundle\Entity\ProductoUnidadCategoria;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Productounidad controller.
 *
 * @Route("productounidad")
 */
class ProductoUnidadController extends Controller
{
    /**
     * Lists all productoUnidad entities.
     *
     * @Route("/", name="productounidad_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');


        $dql = "SELECT pu FROM AppBundle:ProductoUnidad pu ";
        $dql .= " JOIN pu.empresa e";
        $dql .= " JOIN pu.categoria c";
        $dql .= " WHERE  pu.estado = 1  ";

        if($empresa != ''){
            $dql .= " AND e.id =:empresa  ";
        }

        $dql .= " ORDER BY c.nombre,pu.nombre ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
        $productoUnidads =  $query->getResult();    

        return $this->render('productounidad/index.html.twig', array(
            'productoUnidads' => $productoUnidads,
            'titulo' => 'Lista de Unidades'
        ));
    }

    /**
     * Creates a new productoUnidad entity.
     *
     * @Route("/new", name="productounidad_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $productoUnidadCategorium = new ProductoUnidadCategoria();
        $formCategoria = $this->createForm('AppBundle\Form\ProductoUnidadCategoriaType', $productoUnidadCategorium,array('empresa'=>$empresaObj));


        $productoUnidad = new ProductoUnidad();
        $form = $this->createForm('AppBundle\Form\ProductoUnidadType', $productoUnidad,array('empresa'=>$empresaObj));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($productoUnidad);

            try {

                $em->flush();

                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {

                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");                
            }

            return $this->redirectToRoute('productounidad_index');
        }

        return $this->render('productounidad/new.html.twig', array(
            'productoUnidad' => $productoUnidad,
            'form' => $form->createView(),
            'formCategoria' => $formCategoria->createView(),
            'titulo' => 'Registrar unidad'
        ));
    }

    /**
     * Finds and displays a productoUnidad entity.
     *
     * @Route("/{id}", name="productounidad_show")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function showAction(ProductoUnidad $productoUnidad)
    {
        $deleteForm = $this->createDeleteForm($productoUnidad);

        return $this->render('productounidad/show.html.twig', array(
            'productoUnidad' => $productoUnidad,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing productoUnidad entity.
     *
     * @Route("/{id}/edit", name="productounidad_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function editAction(Request $request, ProductoUnidad $productoUnidad)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $productoUnidadCategorium = new ProductoUnidadCategoria();
        $formCategoria = $this->createForm('AppBundle\Form\ProductoUnidadCategoriaType', $productoUnidadCategorium,array('empresa'=>$empresaObj));

        $editForm = $this->createForm('AppBundle\Form\ProductoUnidadType', $productoUnidad,array('empresa'=>$empresaObj));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $em = $this->getDoctrine()->getManager();

            try {

                $em->flush();

                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {

                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");                
            }

            return $this->redirectToRoute('productounidad_index');
        }

        return $this->render('productounidad/edit.html.twig', array(
            'productoUnidad' => $productoUnidad,
            'edit_form' => $editForm->createView(),
            'formCategoria' => $formCategoria->createView(),
            'titulo' => 'Editar unidad',
        ));
    }

    /**
     * Deletes a productoUnidad entity.
     *
     * @Route("/{id}/delete", name="productounidad_delete")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function deleteAction(Request $request, ProductoUnidad $productoUnidad)
    {
        $em = $this->getDoctrine()->getManager();

        $productoUnidad->setEstado(false);
        $em->persist($productoUnidad);

        try{

            $em->flush();

            $this->addFlash("success", "El registro fue eliminado exitosamente.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser eliminado.");
        }
        return $this->redirectToRoute('productounidad_index');
    }

    /**
     * Creates a form to delete a productoUnidad entity.
     *
     * @param ProductoUnidad $productoUnidad The productoUnidad entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ProductoUnidad $productoUnidad)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('productounidad_delete', array('id' => $productoUnidad->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
