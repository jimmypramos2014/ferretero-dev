<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Proveedor;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Proveedor controller.
 *
 * @Route("proveedor")
 */
class ProveedorController extends Controller
{
    /**
     * Lists all proveedor entities.
     *
     * @Route("/", name="proveedor_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR')")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa          = $session->get('empresa');

        $dql = "SELECT p FROM AppBundle:Proveedor p ";
        $dql .= " JOIN p.empresa e";
        $dql .= " WHERE  p.id > 0  ";

        if($empresa != ''){
            $dql .= " AND e.id =:empresa  ";
        }

        $dql .= " ORDER BY p.nombre ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
        $proveedors =  $query->getResult();    


        return $this->render('proveedor/index.html.twig', array(
            'proveedors' => $proveedors,
            'titulo'     => 'Lista de proveedores',
        ));
    }

    /**
     * Creates a new Proveedor entity.
     *
     * @Route("/new", name="proveedor_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR')")
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa          = $session->get('empresa');

        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $codigo = $this->generarCodigo($empresaObj);

        $proveedor = new Proveedor();
        $form = $this->createForm('AppBundle\Form\ProveedorType', $proveedor,array('empresa'=>$empresaObj,'codigo'=>$codigo));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($proveedor);

            try {

                $em->flush();

                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");                
            }

            return $this->redirectToRoute('proveedor_index');
        }

        return $this->render('proveedor/new.html.twig', array(
            'proveedor' => $proveedor,
            'form' => $form->createView(),
            'titulo'    => 'Registrar proveedor'
        ));
    }


    /**
     * Displays a form to edit an existing proveedor entity.
     *
     * @Route("/{id}/edit", name="proveedor_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function editAction(Request $request, Proveedor $proveedor)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa          = $session->get('empresa');

        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $editForm = $this->createForm('AppBundle\Form\ProveedorType', $proveedor,array('empresa'=>$empresaObj));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            
            $em->persist($proveedor);

            try {

                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {
                
                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");                
            }

            

            return $this->redirectToRoute('proveedor_index');        }

        return $this->render('proveedor/edit.html.twig', array(
            'proveedor' => $proveedor,
            'edit_form' => $editForm->createView(),
            'titulo'    => 'Editar proveedor',
        ));
    }

    /**
     * Deletes a proveedor entity.
     *
     * @Route("/{id}/delete", name="proveedor_delete")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function deleteAction(Request $request, Proveedor $proveedor)
    {
        $em = $this->getDoctrine()->getManager();

        $proveedor->setEstado(false);
        $em->persist($proveedor);

        try{

            $em->flush();

            $this->addFlash("success", "El registro fue eliminado exitosamente.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser eliminado.");
        }
        return $this->redirectToRoute('proveedor_index');
    }


    /**
     * Deletes a proveedor entity.
     *
     * @Route("/{id}/habilitar", name="proveedor_habilitar")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function habilitarAction(Request $request, Proveedor $proveedor)
    {
        $em = $this->getDoctrine()->getManager();

        $proveedor->setEstado(true);
        $em->persist($proveedor);

        try{

            $em->flush();

            $this->addFlash("success", "El registro fue habilitado exitosamente.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser habilitado.");
        }
        return $this->redirectToRoute('proveedor_index');
    }


    private function generarCodigo($empresa)
    {
        $em = $this->getDoctrine()->getManager();
        $codigo = '';

        $proveedores = $em->getRepository('AppBundle:Proveedor')->findBy(array('empresa'=>$empresa));

        if(count($proveedores) == 0)
        {
            $codigo = 'PROV000001';

        }
        else
        {
            $contador = count($proveedores) + 1;
            $cantidadDigito = strlen($contador);
            $numCerosMaximo = 6;
            $codigo = (string)$contador;

            for($i = $cantidadDigito; $i < $numCerosMaximo; $i++){
                $codigo = "0".$codigo;
            }

            $codigo = 'PROV'.$codigo;
        }

        return $codigo;

    }
}
