<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Empleado;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Empleado controller.
 *
 * @Route("empleado")
 */
class EmpleadoController extends Controller
{
    /**
     * Lists all empleado entities.
     *
     * @Route("/", name="empleado_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $rol            = $session->get('rol');

        if($rol == 'Superadmin'){
            $empresa = '';
        }

        $dql = "SELECT e FROM AppBundle:Empleado e ";
        $dql .= " JOIN e.local l";
        $dql .= " JOIN l.empresa em";
        $dql .= " WHERE  e.estado = 1  AND e.oculto = 0";

        if($empresa != ''){
            $dql .= " AND em.id =:empresa  ";
        }

        $dql .= " ORDER BY e.nombres ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
        $empleados =  $query->getResult();

        return $this->render('empleado/index.html.twig', array(
            'empleados' => $empleados,
            'titulo' => 'Lista de empleados',
        ));
    }
    
    /**
     * Creates a new administrador entity.
     *
     * @Route("/new", name="empleado_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $rol            = $session->get('rol');

        $param = array('empresa'=>$empresa);

        if($rol == 'Superadmin'){
            $param = array();
        }


        $empleado = new Empleado();
        $form = $this->createForm('AppBundle\Form\EmpleadoType', $empleado,$param);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
                        
            $user = $userManager->findUserByUsername($empleado->getUsuario()->getUsername());

            if($user){
                $this->addFlash("danger", "El usuario ya existe. Verifique si su nombre de usuario y/o correo electrónico ya está siendo usado.");
                return $this->redirect($this->generateUrl('empleado_index'));
            }

            $user = $userManager->findUserByEmail($empleado->getUsuario()->getEmail());

            if($user){
                $this->addFlash("danger", "El correo ya existe. Verifique si el correo electrónico ya está siendo usado.");
                return $this->redirect($this->generateUrl('empleado_index'));
            }

            if($empleado->getEmail() == ''){
                $empleado->getUsuario()->setEmail($empleado->getUsuario()->getUsername().'@mail.com');
            }else{
                $empleado->getUsuario()->setEmail($empleado->getEmail());
            }

            $role = '';
            if($empleado->getPuesto() == 'VENDEDOR'){
                $role = 'ROLE_VENDEDOR';
            }elseif($empleado->getPuesto() == 'ALMACENERO'){
                $role = 'ROLE_ALMACENERO';
            }elseif($empleado->getPuesto() == 'ADMINISTRADOR'){
                $role = 'ROLE_ADMIN';
            }

            $roleArray = array($role);
            
            $empleado->getUsuario()->setRoles($roleArray);
            $empleado->getUsuario()->setEnabled(true);
            //$empleado->getUsuario()->setApiToken($this->generateUniqueToken());

            $empleado->setOculto(false);

            $em->persist($empleado);

            try {

                $em->flush();

                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {

                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");                
            }

            return $this->redirectToRoute('empleado_index');
        }

        return $this->render('empleado/new.html.twig', array(
            'empleado' => $empleado,
            'form' => $form->createView(),
            'titulo' => 'Registrar Empleado',
        ));
    }

    /**
     * Displays a form to edit an existing empleado entity.
     *
     * @Route("/{id}/edit", name="empleado_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Empleado $empleado)
    {
        $userManager = $this->get('fos_user.user_manager');
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $rol            = $session->get('rol');

        $user = $userManager->findUserByUsername($empleado->getUsuario()->getUsername());
        $plainPassword = $user->getPlainPassword();
        $username = $user->getUsername();
        $email    = $user->getEmail();

        $param = array('empresa'=>$empresa);

        if($rol == 'Superadmin'){
            $param = array();
        }

        $editForm = $this->createForm('AppBundle\Form\EmpleadoType', $empleado,$param);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $em = $this->getDoctrine()->getManager();

            if($empleado->getUsuario()->getUsername() != $username){

                $user = $userManager->findUserByUsername($empleado->getUsuario()->getUsername());

                if($user){
                    $this->addFlash("danger", "El usuario ya existe. Verifique si su nombre de usuario ya está siendo usado.");
                    return $this->redirect($this->generateUrl('empleado_index'));
                }

            }

            if($empleado->getUsuario()->getEmail() != $email){

                $user = $userManager->findUserByEmail($empleado->getUsuario()->getEmail());

                if($user){
                    $this->addFlash("danger", "El correo ya existe. Verifique si el correo electrónico ya está siendo usado.");
                    return $this->redirect($this->generateUrl('empleado_index'));
                }

            }


            if($empleado->getEmail() == ''){
                $empleado->getUsuario()->setEmail($empleado->getUsuario()->getUsername().'@mail.com');
            }else{
                $empleado->getUsuario()->setEmail($empleado->getEmail());
            }

            if($empleado->getUsuario()->getPlainPassword() == 'abcde1'){
                $empleado->getUsuario()->setPlainPassword($plainPassword);
            }else{
                $empleado->getUsuario()->setPlainPassword($empleado->getUsuario()->getPlainPassword());
            }

            $role = '';
            if($empleado->getPuesto() == 'VENDEDOR'){
                $role = 'ROLE_VENDEDOR';
            }elseif($empleado->getPuesto() == 'ALMACENERO'){
                $role = 'ROLE_ALMACENERO';
            }elseif($empleado->getPuesto() == 'ADMINISTRADOR'){
                $role = 'ROLE_ADMIN';
            }

            $roleArray = array($role);
            
            $empleado->getUsuario()->setRoles($roleArray);

            $empleado->setOculto(false);

            $em->persist($empleado);


            try {

                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {
                
                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");                
            }

            return $this->redirectToRoute('empleado_index');
        }

        return $this->render('empleado/edit.html.twig', array(
            'empleado' => $empleado,
            'edit_form'     => $editForm->createView(),
            'titulo'        => 'Editar Empleado',
        ));
    }


    /**
     *
     * @Route("/{id}/delete", name="empleado_delete")
     * @Method({"GET", "POST", "DELETE"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $userManager = $this->get('fos_user.user_manager');

        $entity = $em->getRepository('AppBundle:Empleado')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Empleado entity.');
        }        

        try{
            $entity->setEstado(false);

            $stringAleatorio = $this->container->get('AppBundle\Util\Util')->generateRandomString();
            
            $user = $userManager->findUserByUsername($entity->getUsuario()->getUsername());

            $this->container->get('AppBundle\Util\Util')->deshabilitarUsuario($userManager,$user);

            $user->setUsername($stringAleatorio);
            $user->setUsernameCanonical($stringAleatorio);      
            $user->setEmail($stringAleatorio.'@mail.com');
            $user->setEmailCanonical($stringAleatorio.'@mail.com');    
            
            $entity->setDni($stringAleatorio);

            $em->persist($entity);
            $userManager->updateUser($user);

            $em->flush();

        } catch (\Exception $e) {
            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó."); 
        }

        $this->addFlash("success", "El registro fue eliminado exitosamente.");

        return $this->redirect($this->generateUrl('empleado_index'));
    }


    /**
     * @return string
     */
    private function generateUniqueToken()
    {
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
         
}
