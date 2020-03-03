<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Administrador;
use AppBundle\Entity\Empleado;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Administrador controller.
 *
 * @Route("administrador")
 */
class AdministradorController extends Controller
{

    /**
     * Lists all administrador entities.
     *
     * @Route("/", name="administrador_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $administradors = $em->getRepository('AppBundle:Empleado')->findBy(array('puesto'=>3));

        return $this->render('administrador/index.html.twig', array(
            'administradors' => $administradors,
            'titulo' => 'Lista de Administradores',
        ));
    }

    /**
     * Creates a new administrador entity.
     *
     * @Route("/new", name="administrador_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function newAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');

        $administrador = new Empleado();
        $form = $this->createForm('AppBundle\Form\AdministradorType', $administrador);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            
            $puesto = $em->getRepository('AppBundle:Puesto')->find(3);
            $administrador->setPuesto($puesto);

            $user = $userManager->findUserByUsername($administrador->getUsuario()->getUsername());

            if($user){
                $this->addFlash("danger", "El usuario ya existe. Verifique si su nombre de usuario y/o correo electrónico ya está siendo usado.");
                return $this->redirect($this->generateUrl('administrador_index'));
            }

            $user = $userManager->findUserByEmail($administrador->getUsuario()->getEmail());

            if($user){
                $this->addFlash("danger", "El correo ya existe. Verifique si el correo electrónico ya está siendo usado.");
                return $this->redirect($this->generateUrl('administrador_index'));
            }

            if($administrador->getEmail() == ''){
                $administrador->getUsuario()->setEmail($administrador->getUsuario()->getUsername().'@mail.com');
            }else{
                $administrador->getUsuario()->setEmail($administrador->getEmail());
            }


            $roleArray = array('ROLE_ADMIN');
            
            $administrador->getUsuario()->setRoles($roleArray);
            $administrador->getUsuario()->setEnabled(true);
            //$administrador->getUsuario()->setApiToken($this->generateUniqueToken());  

            $em->persist($administrador);


            try {

                $em->flush();

                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");                
            }

            return $this->redirectToRoute('administrador_index');
        }

        return $this->render('administrador/new.html.twig', array(
            'administrador' => $administrador,
            'form' => $form->createView(),
            'titulo' => 'Registrar Administrador',
        ));
    }

    /**
     * Displays a form to edit an existing administrador entity.
     *
     * @Route("/{id}/edit", name="administrador_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function editAction(Request $request, Empleado $empleado)
    {
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->findUserByUsername($empleado->getUsuario()->getUsername());
        $plainPassword = $user->getPlainPassword();
        $username = $user->getUsername();
        $email    = $user->getEmail();

        $editForm = $this->createForm('AppBundle\Form\AdministradorType', $empleado);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $puesto = $em->getRepository('AppBundle:Puesto')->find(3);
            $empleado->setPuesto($puesto);



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


            $roleArray = array('ROLE_ADMIN');
            
            $empleado->getUsuario()->setRoles($roleArray);

            $em->persist($empleado);



            try {

                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {
                
                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");                
            }

            return $this->redirectToRoute('administrador_index');
        }

        return $this->render('administrador/edit.html.twig', array(
            'administrador' => $empleado,
            'edit_form'     => $editForm->createView(),
            'titulo'        => 'Editar Administrador',
        ));
    }


    /**
     *
     * @Route("/{id}/delete", name="administrador_delete")
     * @Method({"GET", "POST", "DELETE"})
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $userManager = $this->get('fos_user.user_manager');

        $entity = $em->getRepository('AppBundle:Empleado')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Administrador entity.');
        }        

        try{
            $entity->setEstado(false);

            $stringAleatorio = $this->container->get('AppBundle\Util\Util')->generateRandomString();
            
            $user = $userManager->findUserByUsername($entity->getDni());

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

        return $this->redirect($this->generateUrl('administrador_index'));
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
