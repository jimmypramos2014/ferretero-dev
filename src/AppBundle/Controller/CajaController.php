<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Caja;
use AppBundle\Entity\CajaApertura;
use AppBundle\Entity\CajaCierre;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Caja controller.
 *
 * @Route("caja")
 */
class CajaController extends Controller
{
    /**
     * Lists all caja entities.
     *
     * @Route("/", name="caja_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');


        $dql = "SELECT c FROM AppBundle:Caja c ";
        $dql .= " JOIN c.local l";
        $dql .= " JOIN l.empresa e";
        $dql .= " WHERE  c.estado = 1  ";

        if($empresa != ''){
            $dql .= " AND e.id =:empresa  ";
        }

        
        if($session->get('rol') == 'Vendedor' || $session->get('rol') == 'Almacenero' ){
            //$dql .= " AND c.id =:caja  ";
            $dql .= " AND l.id =:local  ";
        }

        $dql .= " ORDER BY l.nombre,c.nombre ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
         if($session->get('rol') == 'Vendedor' || $session->get('rol') == 'Almacenero' ){
            //$query->setParameter('caja',$session->get('caja'));
            $query->setParameter('local',$session->get('local'));
        }

        $cajas =  $query->getResult();    


        return $this->render('caja/index.html.twig', array(
            'cajas' => $cajas,
            'titulo'=> 'Lista de cajas'
        ));
    }

    /**
     * Creates a new caja entity.
     *
     * @Route("/new", name="caja_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $caja = new Caja();
        $form = $this->createForm('AppBundle\Form\CajaType', $caja,array('empresa'=>$empresa));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $caja->setCondicion('cerrado');
            $em->persist($caja);

            try {

                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");  
                
            }
            

            return $this->redirectToRoute('caja_index');
        }

        return $this->render('caja/new.html.twig', array(
            'caja' => $caja,
            'form' => $form->createView(),
            'titulo'=> 'Registrar caja'
        ));
    }

    /**
     * Displays a form to edit an existing caja entity.
     *
     * @Route("/{id}/edit", name="caja_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Caja $caja)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $editForm = $this->createForm('AppBundle\Form\CajaType', $caja,array('empresa'=>$empresa));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            try {

                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");  
                
            }
            

            return $this->redirectToRoute('caja_index');
        }

        return $this->render('caja/edit.html.twig', array(
            'caja' => $caja,
            'edit_form' => $editForm->createView(),
            'titulo'    => 'Editar caja'
        ));
    }

    /**
     * Deletes a caja entity.
     *
     * @Route("/{id}/delete", name="caja_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, Caja $caja)
    {

        $em = $this->getDoctrine()->getManager();

        $caja->setEstado(false);
        $em->persist($caja);

        try{

            $em->flush();

            $this->addFlash("success", "El registro fue eliminado exitosamente.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser eliminado.");
        }
        
        return $this->redirectToRoute('caja_index');
    }

    /**
     * Apertura de caja.
     *
     * @Route("/{id}/apertura", name="caja_apertura")
     * @Method({"GET", "POST"})
     */
    public function aperturaAction(Request $request, Caja $caja)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $fecha = new \DateTime();

        $montoAnterior = ($caja->getMontoAnterior()) ? $caja->getMontoAnterior() : 0;

        $cajaApertura = new CajaApertura();
        $form = $this->createForm('AppBundle\Form\CajaAperturaType',$cajaApertura,array('caja'=>$caja->getId()) );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $cajaAperturaAbierto  = $em->getRepository('AppBundle:CajaApertura')->findBy(array('estado'=>true,'caja'=>$caja));

            if(count($cajaAperturaAbierto) == 0)
            {

                $cajaApertura->setFecha($fecha);
                $cajaApertura->getCaja()->setCondicion('abierto');
                $em->persist($cajaApertura);

                try {

                    $em->flush();

                    $session->set('caja_apertura',$cajaApertura->getId());
                    $session->set('caja',$caja->getId());

                    $this->addFlash("success", "La caja fue aperturada exitosamente.");
                    
                } catch (\Exception $e) {

                    $this->addFlash("danger", $e." Ocurrió un error inesperado, la apertura no se realizó.");  
                    
                }

            }    

            return $this->redirectToRoute('caja_index');
        }

        return $this->render('caja/apertura.html.twig', array(
            'caja' => $caja,
            'form' => $form->createView(),
            'montoAnterior' => $montoAnterior,
            'titulo'    => 'Apertura de caja'
        ));
    }

    /**
     * Cierre de caja.
     *
     * @Route("/{id}/cierre", name="caja_cierre")
     * @Method({"GET", "POST"})
     */
    public function cierreAction(Request $request, Caja $caja)
    {

        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $fecha = new \DateTime();

        $dql = "SELECT ca FROM AppBundle:CajaApertura ca ";
        $dql .= " JOIN ca.caja c";
        $dql .= " WHERE  c.id =:caja_id  AND ca.estado = 1 ";
        $query = $em->createQuery($dql);
        $query->setParameter('caja_id',$caja->getId());
        $cajaApertura = $query->getOneOrNullResult();

        if(!$cajaApertura){

            $this->addFlash("danger", " No ha seleccionado ninguna caja. Diríjase al punto de venta y seleccione la caja.");  
            return $this->redirectToRoute('caja_index');

        }

        $total_venta = 0;
        foreach($cajaApertura->getCajaAperturaDetalle() as $detalle){

            $cantidad    = $detalle->getCantidad();

            if($detalle->getIdentificador())
            {
                $fid = $detalle->getIdentificador();
                $facturaVenta = $em->getRepository('AppBundle:FacturaVenta')->find($fid);
                if($facturaVenta->getVenta()->getEstado())
                {
                    $total_venta = $cantidad + $total_venta;
                }
                

            }
            else
            {
                $total_venta = $cantidad + $total_venta;

            }


        }

        $monto_anterior = ($caja->getMontoAnterior()) ? $caja->getMontoAnterior() : 0;
        $total_recaudado = $total_venta + $cajaApertura->getMontoApertura();//+ $monto_anterior;

        $cajaCierre = new CajaCierre();
        $form = $this->createForm('AppBundle\Form\CajaCierreType',$cajaCierre,array('caja_apertura_id'=>$cajaApertura->getId(),'total_recaudado'=>$total_recaudado));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $cajaCierre->setFecha($fecha);
            $cajaCierre->getCajaApertura()->getCaja()->setCondicion('cerrado');
            $cajaCierre->getCajaApertura()->setEstado(false);
            $cajaCierre->getCajaApertura()->getCaja()->setMontoAnterior($form->getData()->getTotalDejada());
            $em->persist($cajaCierre);

            try {

                $em->flush();

                $session->set('caja_apertura',null);
                $session->set('caja',null);
                $this->addFlash("success", "La caja fue cerrada exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");  
                
            }
            

            return $this->redirectToRoute('caja_index');
        }

        return $this->render('caja/cierre.html.twig', array(
            'caja' => $caja,
            'cajaApertura' => $cajaApertura,
            'form' => $form->createView(),
            'titulo'    => 'Cierre de caja'
        ));
    }



}
