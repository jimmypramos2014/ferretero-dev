<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cliente;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Cliente controller.
 *
 * @Route("cliente")
 */
class ClienteController extends Controller
{
    /**
     * Lists all cliente entities.
     *
     * @Route("/", name="cliente_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO')")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');        

        //$clientes = $em->getRepository('AppBundle:Cliente')->findBy(array('estado'=>true,'local'=>$local));

        $dql = "SELECT c FROM AppBundle:Cliente c ";
        $dql .= " JOIN c.local l";
        $dql .= " JOIN l.empresa e";
        $dql .= " WHERE  c.ruc <> '---' ";

        if($empresa != ''){
            $dql .= " AND e.id =:empresa  ";
        }

        $dql .= " ORDER BY c.razonSocial ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
        $clientes =  $query->getResult();    


        return $this->render('cliente/index.html.twig', array(
            'clientes'  => $clientes,
            'titulo'    => 'Lista de clientes',
        ));
    }

    /**
     * Creates a new cliente entity.
     *
     * @Route("/new", name="cliente_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') ")
     */
    public function newAction(Request $request)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');    

        $cliente = new Cliente();
        $form = $this->createForm('AppBundle\Form\ClienteType', $cliente,array('empresa'=>$empresa));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $cliente->setCodigo(mt_rand(1000,999999));
            $em->persist($cliente);

            try {

                $em->flush();

                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {

                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");                
            }

            return $this->redirectToRoute('cliente_index');

        }

        return $this->render('cliente/new.html.twig', array(
            'cliente' => $cliente,
            'form' => $form->createView(),
            'titulo'    => 'Registrar cliente',
        ));
    }

    /**
     * Finds and displays a cliente entity.
     *
     * @Route("/{id}", name="cliente_show")
     * @Method("GET")
     */
    public function showAction(Cliente $cliente)
    {

        return $this->render('cliente/show.html.twig', array(
            'cliente' => $cliente,
        ));
    }

    /**
     * Displays a form to edit an existing cliente entity.
     *
     * @Route("/{id}/edit", name="cliente_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO')")
     */
    public function editAction(Request $request, Cliente $cliente)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');    
                
        $editForm = $this->createForm('AppBundle\Form\ClienteType', $cliente,array('empresa'=>$empresa));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $em = $this->getDoctrine()->getManager();

            try {

                $em->flush();

                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {

                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");                
            }

            return $this->redirectToRoute('cliente_index');        
        }

        return $this->render('cliente/edit.html.twig', array(
            'cliente'   => $cliente,
            'edit_form' => $editForm->createView(),
            'titulo'    => 'Editar cliente',
        ));
    }

    /**
     * Deletes a cliente entity.
     *
     * @Route("/{id}/delete", name="cliente_delete")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') ")
     */
    public function deleteAction(Request $request, Cliente $cliente)
    {
        $em = $this->getDoctrine()->getManager();

        $cliente->setEstado(false);
        $em->persist($cliente);

        try{

            $em->flush();

            $this->addFlash("success", "El registro fue eliminado exitosamente.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser eliminado.");
        }
        return $this->redirectToRoute('cliente_index');
    }

    /**
     * Habiltar a proveedor entity.
     *
     * @Route("/{id}/habilitar", name="cliente_habilitar")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO')")
     */
    public function habilitarAction(Request $request, Cliente $cliente)
    {
        $em = $this->getDoctrine()->getManager();

        $cliente->setEstado(true);
        $em->persist($cliente);

        try{

            $em->flush();

            $this->addFlash("success", "El registro fue habilitado exitosamente.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser habilitado.");
        }
        return $this->redirectToRoute('cliente_index');
    }


}
