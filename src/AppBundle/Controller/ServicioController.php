<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Servicio;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Servicio controller.
 *
 * @Route("servicio")
 */
class ServicioController extends Controller
{
    /**
     * Lists all servicio entities.
     *
     * @Route("/", name="servicio_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        //$servicios = $em->getRepository('AppBundle:Servicio')->findBy(array('local'=>$local,'estado'=>true));

        $dql = "SELECT s FROM AppBundle:Servicio s ";
        $dql .= " JOIN s.local l";
        $dql .= " JOIN l.empresa e";
        $dql .= " WHERE   s.estado = 1 AND  e.id =:empresa  ";

        $query = $em->createQuery($dql);

        $query->setParameter('empresa',$empresa);
 
        $servicios = $query->getResult();

        return $this->render('servicio/index.html.twig', array(
            'servicios' => $servicios,
            'empresa'   => $empresa,
            'titulo'    => 'Lista de servicios'
        ));

     
    }

    /**
     * Creates a new servicio entity.
     *
     * @Route("/new", name="servicio_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');

        $servicio = new Servicio();
        $form = $this->createForm('AppBundle\Form\ServicioType', $servicio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $localObj = $em->getRepository('AppBundle:Empresalocal')->find($local);

            $servicio->setLocal($localObj);
            $em->persist($servicio);

            try {

                $em->flush();

                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {

                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");                
            }

            return $this->redirectToRoute('servicio_index');
        }

        return $this->render('servicio/new.html.twig', array(
            'servicio' => $servicio,
            'form' => $form->createView(),
            'titulo'    => 'Agregar servicio'
        ));
    }

    /**
     * Finds and displays a servicio entity.
     *
     * @Route("/{id}", name="servicio_show")
     * @Method("GET")
     */
    public function showAction(Servicio $servicio)
    {
        $deleteForm = $this->createDeleteForm($servicio);

        return $this->render('servicio/show.html.twig', array(
            'servicio' => $servicio,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing servicio entity.
     *
     * @Route("/{id}/edit", name="servicio_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Servicio $servicio)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');

        $editForm = $this->createForm('AppBundle\Form\ServicioType', $servicio);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $localObj = $em->getRepository('AppBundle:Empresalocal')->find($local);

            $servicio->setLocal($localObj);
            $em->persist($servicio);

            try {

                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {
                
                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");                
            }

            

            return $this->redirectToRoute('servicio_index');        }

        return $this->render('servicio/edit.html.twig', array(
            'servicio' => $servicio,
            'edit_form' => $editForm->createView(),
            'titulo'    => 'Editar servicio',
        ));
    }

    /**
     * Deletes a servicio entity.
     *
     * @Route("/{id}/delete", name="servicio_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, Servicio $servicio)
    {
        $em = $this->getDoctrine()->getManager();

        $servicio->setEstado(false);
        $em->persist($servicio);

        try{

            $em->flush();

            $this->addFlash("success", "El registro fue eliminado exitosamente.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser eliminado.");
        }
        return $this->redirectToRoute('servicio_index');
    }

    /**
     * Creates a form to delete a servicio entity.
     *
     * @param Servicio $servicio The servicio entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Servicio $servicio)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('servicio_delete', array('id' => $servicio->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
