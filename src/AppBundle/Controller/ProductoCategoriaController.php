<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProductoCategoria;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use AppBundle\Imagen\ResizeImage;

/**
 * Productocategorium controller.
 *
 * @Route("productocategoria")
 */
class ProductoCategoriaController extends Controller
{
    /**
     * Lists all productoCategorium entities.
     *
     * @Route("/", name="productocategoria_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $localObj = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

        $productoCategorias = $em->getRepository('AppBundle:ProductoCategoria')->findBy(array('empresa'=>$empresa));

        return $this->render('productocategoria/index.html.twig', array(
            'productoCategorias' => $productoCategorias,
            'titulo' => 'Lista de categorías',
            'localObj' => $localObj,
        ));
    }

    /**
     * Creates a new productoCategorium entity.
     *
     * @Route("/new", name="productocategoria_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $productoCategoria = new ProductoCategoria();
        $form = $this->createForm('AppBundle\Form\ProductoCategoriaType', $productoCategoria);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $productoCategoria->getImagen();

            if($file){
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('imagenes_directorio'),
                    $fileName
                );

                $productoCategoria->setImagen($fileName);

                $this->redimensionarImagen($fileName);

            }


            $em->persist($productoCategoria);

            try {
                $em->flush();
            } catch (\Exception $e) {
                return $e;
            }
            

            return $this->redirectToRoute('productocategoria_index');
        }

        return $this->render('productocategoria/new.html.twig', array(
            'productoCategoria' => $productoCategoria,
            'form' => $form->createView(),
            'titulo' => 'Registrar categoría',
        ));
    }


    /**
     * Displays a form to edit an existing productoCategoria entity.
     *
     * @Route("/{id}/edit", name="productocategoria_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ProductoCategoria $productoCategoria)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $originalFile = null;

        if($productoCategoria->getImagen()){

            $originalFile = $productoCategoria->getImagen();

            $productoCategoria->setImagen(
                new File($this->getParameter('imagenes_directorio').'/categoria/'.$productoCategoria->getImagen())
            );
        }

        $editForm = $this->createForm('AppBundle\Form\ProductoCategoriaType', $productoCategoria);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $file = $productoCategoria->getImagen();

            if($file){

                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('imagenes_directorio'),
                    $fileName
                );


                $productoCategoria->setImagen($fileName);

                $this->redimensionarImagen($fileName);                

            }else{

                $productoCategoria->setImagen($originalFile);
            }

            try {
                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");
            }

            return $this->redirectToRoute('productocategoria_index');
        }

        return $this->render('productocategoria/edit.html.twig', array(
            'productoCategoria' => $productoCategoria,
            'edit_form' => $editForm->createView(),
            'titulo' => 'Registrar categoría',
            'originalFile' => $originalFile,
        ));
    }

    /**
     * Deletes a productoCategoria entity.
     *
     * @Route("/{id}/delete", name="productocategoria_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, ProductoCategoria $productoCategoria)
    {
        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //     $em = $this->getDoctrine()->getManager();
        //     $em->remove($productoCategorium);
        //     $em->flush();
        // }

        return $this->redirectToRoute('productocategoria_index');
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

    private function redimensionarImagen($fileName)
    {

        //Creamos el tumbnail 100x100
        $resize = new ResizeImage($this->getParameter('imagenes_directorio').'/'.$fileName);
        $resize->resizeTo(100, 100, 'exact');
        $resize->saveImage($this->getParameter('imagenes_directorio').'/categoria/'.$fileName);

        unlink($this->getParameter('imagenes_directorio').'/'.$fileName);  

        return true;

    }

}
