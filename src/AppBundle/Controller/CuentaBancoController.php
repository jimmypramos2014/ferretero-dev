<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CuentaBanco;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Cuentabanco controller.
 *
 * @Route("cuentabanco")
 */
class CuentaBancoController extends Controller
{
    /**
     * Lists all cuentaBanco entities.
     *
     * @Route("/", name="cuentabanco_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $cuentaBancos = $em->getRepository('AppBundle:CuentaBanco')->findBy(array('estado'=>true,'empresa'=>$empresa));

        return $this->render('cuentabanco/index.html.twig', array(
            'cuentaBancos' => $cuentaBancos,
            'titulo'       => 'Cuentas bancarias'
        ));
    }

    /**
     * Creates a new cuentaBanco entity.
     *
     * @Route("/new", name="cuentabanco_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $cuentaBanco = new Cuentabanco();
        $form = $this->createForm('AppBundle\Form\CuentaBancoType', $cuentaBanco);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $cuentaBanco->setEmpresa($empresaObj);
            $em->persist($cuentaBanco);

            try {

                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", "Ocurrió un error inesperado.");
            }
            

            return $this->redirectToRoute('cuentabanco_index');
        }

        return $this->render('cuentabanco/new.html.twig', array(
            'cuentaBanco' => $cuentaBanco,
            'form' => $form->createView(),
            'titulo'   => 'Registrar cuenta',
        ));
    }

    /**
     * Finds and displays a cuentaBanco entity.
     *
     * @Route("/{id}", name="cuentabanco_show")
     * @Method("GET")
     */
    public function showAction(CuentaBanco $cuentaBanco)
    {
        $deleteForm = $this->createDeleteForm($cuentaBanco);

        return $this->render('cuentabanco/show.html.twig', array(
            'cuentaBanco' => $cuentaBanco,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing cuentaBanco entity.
     *
     * @Route("/{id}/edit", name="cuentabanco_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, CuentaBanco $cuentaBanco)
    {
        $editForm = $this->createForm('AppBundle\Form\CuentaBancoType', $cuentaBanco);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();

            try {

                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", "Ocurrió un error inesperado.");
            }
            

            return $this->redirectToRoute('cuentabanco_index');
        }

        return $this->render('cuentabanco/edit.html.twig', array(
            'cuentaBanco' => $cuentaBanco,
            'edit_form' => $editForm->createView(),
             'titulo'   => 'Editar cuenta',
        ));
    }

    /**
     * Deletes a cuentaBanco entity.
     *
     * @Route("/{id}/delete", name="cuentabanco_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, CuentaBanco $cuentaBanco)
    {
        $em = $this->getDoctrine()->getManager();

        $cuentaBanco->setEstado(false);
        $em->persist($cuentaBanco);

        try{

            $em->flush();

            $this->addFlash("success", "El registro fue eliminado exitosamente.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser eliminado.");
        }

        return $this->redirectToRoute('cuentabanco_index');
    }

    /**
     * Creates a form to delete a cuentaBanco entity.
     *
     * @param CuentaBanco $cuentaBanco The cuentaBanco entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(CuentaBanco $cuentaBanco)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('cuentabanco_delete', array('id' => $cuentaBanco->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
