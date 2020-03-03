<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ComponenteXDocumento;
use AppBundle\Entity\Documento;
use AppBundle\Entity\EmpresaLocal;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Componentexdocumento controller.
 *
 * @Route("componentexdocumento")
 */
class ComponenteXDocumentoController extends Controller
{
    /**
     * Lists all componenteXDocumento entities.
     *
     * @Route("/{id}", name="componentexdocumento_index")
     * @Method("GET")
     */
    public function indexAction(Request $request,EmpresaLocal $empresaLocal)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $empresaLocal->getId();

        $documentos = $em->getRepository('AppBundle:Documento')->findBy(array('local'=>$local));

        return $this->render('componentexdocumento/index.html.twig', array(
            'documentos' => $documentos,
            'titulo' => 'Documentos',
            'local' => $local,
        ));
    }

    /**
     * Creates a new componenteXDocumento entity.
     *
     * @Route("/new", name="componentexdocumento_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $componenteXDocumento = new Componentexdocumento();
        $form = $this->createForm('AppBundle\Form\ComponenteXDocumentoType', $componenteXDocumento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($componenteXDocumento);
            $em->flush();

            return $this->redirectToRoute('componentexdocumento_show', array('id' => $componenteXDocumento->getId()));
        }

        return $this->render('componentexdocumento/new.html.twig', array(
            'componenteXDocumento' => $componenteXDocumento,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a componenteXDocumento entity.
     *
     * @Route("/{id}", name="componentexdocumento_show")
     * @Method("GET")
     */
    public function showAction(ComponenteXDocumento $componenteXDocumento)
    {
        $deleteForm = $this->createDeleteForm($componenteXDocumento);

        return $this->render('componentexdocumento/show.html.twig', array(
            'componenteXDocumento' => $componenteXDocumento,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing componenteXDocumento entity.
     *
     * @Route("/{id}/edit", name="componentexdocumento_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Documento $documento)
    {

        $em = $this->getDoctrine()->getManager();


        $coordenadas = $em->getRepository('AppBundle:ComponenteXDocumento')->findBy(array('documento'=>$documento));

        if($request->request->get('guardar'))
        {

            foreach($coordenadas as $coordenada)
            {
                $posicionX = $request->request->get('posicionx_'.$coordenada->getId());
                $posicionY = $request->request->get('posiciony_'.$coordenada->getId());

                $coordenada->setPosicionX($posicionY);
                $coordenada->setPosicionY($posicionX);

                $em->persist($coordenada);

            }

            try {

                $em->flush();
                $this->addFlash("success", "El registro fue editado exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");       
                
            }

            return $this->redirectToRoute('componentexdocumento_edit',array('id'=>$documento->getId()));

        }


        return $this->render('componentexdocumento/edit.html.twig', array(
            'coordenadas' => $coordenadas,
            'documento'   => $documento,
            'local'       => $documento->getLocal()->getId(),
            'titulo'      => 'Editar coordenadas',
        ));




        // $editForm = $this->createForm('AppBundle\Form\ComponenteXDocumentoType', $componenteXDocumento);
        // $editForm->handleRequest($request);

        // if ($editForm->isSubmitted() && $editForm->isValid()) {
        //     $this->getDoctrine()->getManager()->flush();

        //     return $this->redirectToRoute('componentexdocumento_edit', array('id' => $componenteXDocumento->getId()));
        // }

        // return $this->render('componentexdocumento/edit.html.twig', array(
        //     'componenteXDocumento' => $componenteXDocumento,
        //     'edit_form' => $editForm->createView(),

        // ));
    }

    /**
     * Deletes a componenteXDocumento entity.
     *
     * @Route("/{id}", name="componentexdocumento_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ComponenteXDocumento $componenteXDocumento)
    {
        $form = $this->createDeleteForm($componenteXDocumento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($componenteXDocumento);
            $em->flush();
        }

        return $this->redirectToRoute('componentexdocumento_index');
    }

    /**
     * Creates a form to delete a componenteXDocumento entity.
     *
     * @param ComponenteXDocumento $componenteXDocumento The componenteXDocumento entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ComponenteXDocumento $componenteXDocumento)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('componentexdocumento_delete', array('id' => $componenteXDocumento->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
