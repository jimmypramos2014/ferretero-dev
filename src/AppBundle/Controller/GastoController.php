<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Gasto;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Gasto controller.
 *
 * @Route("gasto")
 */
class GastoController extends Controller
{
    /**
     * Lists all gasto entities.
     *
     * @Route("/", name="gasto_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR')")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $gastos = $em->getRepository('AppBundle:Gasto')->findBy(array('empresa'=>$empresa,'estado'=>true),array('fecha'=>'desc'));

        return $this->render('gasto/index.html.twig', array(
            'gastos' => $gastos,
            'titulo' => 'Lista de gastos'
        ));
    }

    /**
     * Creates a new gasto entity.
     *
     * @Route("/new", name="gasto_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR')")
     */
    public function newAction(Request $request)
    {        
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);
        $file       = $request->files->get('appbundle_gasto')['archivo'];        

        $gasto = new Gasto();
        $form = $this->createForm('AppBundle\Form\GastoType', $gasto,array('empresa'=>$empresaObj));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $proveedor_id = $request->request->get('proveedor_select');
            $proveedor = $em->getRepository('AppBundle:Proveedor')->find($proveedor_id);


            if($file){
                //Guardamos el archivo adjunto si existiera
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('archivos_directorio'),
                    $fileName
                );

                $gasto->setArchivo($fileName);

                $ruta = $this->getParameter('archivos_directorio').'/'.$fileName;
            }

            $gasto->setTipo('gasto');
            $gasto->setProveedor($proveedor);
            $em->persist($gasto);

            try {

                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");
                
            } catch (\Exception $e) {
                
                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
                return $this->redirectToRoute('gasto_index');                
            }
            

            return $this->redirectToRoute('gasto_index');
        }

        return $this->render('gasto/new.html.twig', array(
            'gasto' => $gasto,
            'form' => $form->createView(),
            'titulo' => 'Registrar gasto'
        ));
    }

    /**
     * Creates a new egreso.
     *
     * @Route("/new/egreso", name="gasto_egreso_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR')")
     */
    public function newEgresoAction(Request $request)
    {        
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);        

        $gasto = new Gasto();
        $form = $this->createForm('AppBundle\Form\EgresoType', $gasto,array('empresa'=>$empresaObj));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $numdoc =($em->getRepository('AppBundle:Gasto')->findLastId())?$em->getRepository('AppBundle:Gasto')->findLastId()->getId():1;
            $numdoc = str_pad($numdoc + 1, 7, '0', STR_PAD_LEFT);

            $gasto->setNumeroDocumento($numdoc);

            $gasto->setTipo('egreso');
            $em->persist($gasto);

            try {

                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");
                
            } catch (\Exception $e) {
                
                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
                return $this->redirectToRoute('gasto_index');                
            }
            

            return $this->redirectToRoute('gasto_index');
        }

        return $this->render('gasto/newEgreso.html.twig', array(
            'gasto' => $gasto,
            'form' => $form->createView(),
            'titulo' => 'Registrar egreso'
        ));
    }


    /**
     * Finds and displays a gasto entity.
     *
     * @Route("/{id}", name="gasto_show")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR')")
     */
    public function showAction(Gasto $gasto)
    {
        $deleteForm = $this->createDeleteForm($gasto);

        return $this->render('gasto/show.html.twig', array(
            'gasto' => $gasto,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing gasto entity.
     *
     * @Route("/{id}/edit", name="gasto_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR')")
     */
    public function editAction(Request $request, Gasto $gasto)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $originalFile = null;

        if($gasto->getArchivo()){

            $originalFile = $gasto->getArchivo();

            $gasto->setArchivo(
                new File($this->getParameter('archivos_directorio').'/'.$gasto->getArchivo())
            );
        }

        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);   

        $editForm = $this->createForm('AppBundle\Form\GastoType', $gasto,array('empresa'=>$empresaObj));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $file = $gasto->getArchivo();

            if($file){

                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('archivos_directorio'),
                    $fileName
                );


                $gasto->setArchivo($fileName);

                $ruta = $this->getParameter('archivos_directorio').'/'.$fileName;


            }else{

                $gasto->setArchivo($originalFile);
            }


            try {

                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");
                
            } catch (\Exception $e) {
                
                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
                return $this->redirectToRoute('gasto_index');                
            }

           return $this->redirectToRoute('gasto_index');
        }

        return $this->render('gasto/edit.html.twig', array(
            'gasto' => $gasto,
            'edit_form' => $editForm->createView(),
            'titulo' => 'Editar gasto',
            'originalFile'  => $originalFile
        ));
    }


    /**
     * Displays a form to edit an existing gasto entity.
     *
     * @Route("/{id}/edit/egreso", name="gasto_egreso_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR') ")
     */
    public function editEgresoAction(Request $request, Gasto $gasto)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);   

        $editForm = $this->createForm('AppBundle\Form\EgresoType', $gasto,array('empresa'=>$empresaObj));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            try {

                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");
                
            } catch (\Exception $e) {
                
                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
                return $this->redirectToRoute('gasto_index');                
            }

           return $this->redirectToRoute('gasto_index');
        }

        return $this->render('gasto/editEgreso.html.twig', array(
            'gasto' => $gasto,
            'edit_form' => $editForm->createView(),
            'titulo' => 'Editar egreso'
        ));
    }


    /**
     * Muestra el formato de impresion de recibo de egreso.
     *
     * @Route("/{id}/reciboegreso", name="gasto_show_reciboegreso")
     * @Method({"POST","GET"})
     */
    public function showReciboegresoAction(Request $request,Gasto $gasto)
    {

        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $localObj       = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        $dql = "SELECT cxd FROM AppBundle:ComponenteXDocumento cxd ";
        $dql .= " JOIN cxd.documento d";
        $dql .= " JOIN d.local l";
        $dql .= " WHERE  l.id =:local  AND d.codigo = '05' AND cxd.estado = 1 ";

        $query = $em->createQuery($dql);

        $query->setParameter('local',$local);         
 
        $componentesXDocumento =  $query->getResult();

        $html = $this->render('gasto/showReciboegreso.html.twig', array(
                'gasto'  => $gasto,
                'componentesXDocumento'  => $componentesXDocumento,
                'localObj'      => $localObj,
                'host'  => $request->getHost()
            ))->getContent();

        $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('header-html'=> null,'footer-html'=> null,'page-size'=> "A4",'margin-right' => 0,'margin-left' => 0,'margin-top' => 0,'margin-bottom' => 0));

        return new \Symfony\Component\HttpFoundation\Response(
                $pdf, 200, array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'inline; filename="file.pdf"',
                     
        ));

    }


    /**
     * Deletes a Gasto entity.
     *
     * @Route("/{id}/delete", name="gasto_delete")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_VENDEDOR') ")
     */
    public function deleteAction(Request $request, Gasto $gasto)
    {
        $em = $this->getDoctrine()->getManager();

        $gasto->setEstado(false);
        $em->persist($gasto);

        try{

            $em->flush();

            $this->addFlash("success", "El registro fue eliminado exitosamente.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser eliminado.");
        }
        return $this->redirectToRoute('gasto_index');
    }

    /**
     * Creates a form to delete a gasto entity.
     *
     * @param Gasto $gasto The gasto entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Gasto $gasto)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('gasto_delete', array('id' => $gasto->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }


    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }


}
