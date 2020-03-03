<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Producto;
use AppBundle\Entity\ProductoXLocal;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use AppBundle\Barcode\Barcode;
use AppBundle\Imagen\ResizeImage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;



/**
 * Producto controller.
 *
 * @Route("producto")
 */
class ProductoController extends Controller
{
    /**
     * Lists all producto entities.
     *
     * @Route("/", name="producto_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR')")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $dql = "SELECT p FROM AppBundle:Producto p ";
        $dql .= " JOIN p.empresa e";
        $dql .= " WHERE  p.estado = 1  ";

        if($empresa != ''){
            $dql .= " AND e.id =:empresa  ";
        }

        $dql .= " ORDER BY p.nombre ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);        
        }

        $productos =  $query->getResult();

        //$this->container->get('AppBundle\Util\Util')->actualizarPrecioPonderadoProducto($empresa);   


        return $this->render('producto/index.html.twig', array(
            'productos' => $productos,
            'titulo'    => 'Lista de productos'
        ));
    }

    /**
     * Creates a new producto entity.
     *
     * @Route("/new", name="producto_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function newAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $empresaObj     = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $codigo = (string)$this->generarCodigo($empresaObj);    

        $producto = new Producto();
        $form = $this->createForm('AppBundle\Form\ProductoType', $producto,array('codigo'=>$codigo,'empresa'=>$empresa));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $codigo_sunat_id   = $request->request->get('codigo_sunat_select');
            $codigoSunat = $em->getRepository('AppBundle:ProductoCodigoSunat')->find($codigo_sunat_id);
            
            $producto->setEstado(true);
            $producto->setCodigoSunat($codigoSunat);


            $file = $producto->getImagen();

            if($file){
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('imagenes_directorio'),
                    $fileName
                );

                $producto->setImagen($fileName);
                $this->redimensionarImagen($fileName);
            }

            $producto->setEmpresa($empresaObj);
            $em->persist($producto);
            
            //$locales = $em->getRepository('AppBundle:EmpresaLocal')->findBy(array('empresa'=>$empresa));
            foreach($empresaObj->getLocales() as $loc){

                $stock = $request->request->get('stock_'.$loc->getId());
                $stock_jaba = $request->request->get('stock_jaba_'.$loc->getId());
                $stockInicial = $stock;

                $productoXLocal = new ProductoXlocal();
                $productoXLocal->setStock($stock);
                $productoXLocal->setEstado(true);
                $productoXLocal->setLocal($loc);
                $productoXLocal->setProducto($producto);
                $productoXLocal->setStockInicial($stockInicial);

                $em->persist($productoXLocal);

            }

            try {

                $em->flush();

                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {

                $this->addFlash("danger", $e."Ocurrió un error inesperado, el registro no se guardó.");                
            }

            
            return $this->redirectToRoute('producto_index');
        }

        return $this->render('producto/new.html.twig', array(
            'producto' => $producto,
            'form' => $form->createView(),
            'titulo'    => 'Registrar producto',
            'codigo'    => $codigo,
            'empresa'   => $empresaObj
        ));
    }

    /**
     * Finds and displays a producto entity.
     *
     * @Route("/{id}", name="producto_show")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR')")
     */
    public function showAction(Producto $producto)
    {

        return $this->render('producto/show.html.twig', array(
            'producto' => $producto,
        ));
    }

    /**
     * Displays a form to edit an existing producto entity.
     *
     * @Route("/{id}/edit", name="producto_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function editAction(Request $request, Producto $producto)
    {

        $em = $this->getDoctrine()->getManager();

        $originalProductoXPrecio = new ArrayCollection();
        // array con el rango de precios
        foreach ($producto->getProductoXPrecio() as $productoXPrecio) {
            $originalProductoXPrecio->add($productoXPrecio);
        }

        $originalProductoXLocal = array();
        // array con los productos x local
        $j=0;
        foreach ($producto->getProductoXLocal() as $productoXLocal) {

            $originalProductoXLocal[$j]['id'] = $productoXLocal->getId();
            $originalProductoXLocal[$j]['stock'] = $productoXLocal->getStock();

            $j++;
        }



        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $empresaObj     = $em->getRepository('AppBundle:Empresa')->find($empresa);  
     

        $originalFile = null;

        if($producto->getImagen()){

            $originalFile = $producto->getImagen();

            $producto->setImagen(
                new File($this->getParameter('imagenes_directorio').'/100x100/'.$producto->getImagen())
            );
        }

        $producto_nombre = $producto->getNombre();
        $producto_precio = $producto->getPrecioUnitario();

        $editForm = $this->createForm('AppBundle\Form\ProductoType', $producto,array('empresa'=>$empresa));

        $editForm->handleRequest($request);
        

        if ($editForm->isSubmitted() && $editForm->isValid()) 
        {

            $codigo_sunat_id   = $request->request->get('codigo_sunat_select');

            $codigoSunat = $em->getRepository('AppBundle:ProductoCodigoSunat')->find($codigo_sunat_id);
            $producto->setCodigoSunat($codigoSunat);


            // borrar los precios x rango q ya no existan
            foreach ($originalProductoXPrecio as $tag) {
                if (false === $producto->getProductoXPrecio()->contains($tag)) {
                    $em->remove($tag);
                }
            }

            $file = $producto->getImagen();

            if($file){

                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('imagenes_directorio'),
                    $fileName
                );


                $producto->setImagen($fileName);

                $ruta = $this->getParameter('imagenes_directorio').'/'.$fileName;

                $this->redimensionarImagen($fileName);                

            }else{

                $producto->setImagen($originalFile);
            }
            

            $producto->setEstado(true);
            $producto->setEmpresa($empresaObj);

            $em->persist($producto);
            



            try {

                $em->flush();


                // Comparamos el stock del producto,nombres y precio unitario en cada local, si ha sido alterado guardamos en un LOG
                foreach ($producto->getProductoXLocal() as $nuevoPXL) {

                    foreach ($originalProductoXLocal as $z=>$originalPXL) {

                        if($originalPXL['id'] == $nuevoPXL->getId())
                        {
                            if($originalPXL['stock'] != $nuevoPXL->getStock()){
                                $this->container->get('AppBundle\Util\Util')->registrarLog($nuevoPXL,$nuevoPXL->getStock(),$originalPXL['stock'],'stock');
                            }

                            if($producto_nombre != $nuevoPXL->getProducto()->getNombre())
                            {
                                $this->container->get('AppBundle\Util\Util')->registrarLog($nuevoPXL,$nuevoPXL->getProducto()->getNombre(),$producto_nombre,'nombre');
                            }

                            if($producto_precio != $nuevoPXL->getProducto()->getPrecioUnitario())
                            {
                                $this->container->get('AppBundle\Util\Util')->registrarLog($nuevoPXL,$nuevoPXL->getProducto()->getPrecioUnitario(),$producto_precio,'precio');
                            }                            

                        }

                    }

                }



                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {
                
                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");                
            }

            

            return $this->redirectToRoute('producto_index');
        }


        return $this->render('producto/edit.html.twig', array(
            'producto'  => $producto,
            'edit_form' => $editForm->createView(),
            'titulo'    => 'Editar producto',
            'originalFile' => $originalFile,
        ));
    }

    /**
     * Deletes a producto entity.
     *
     * @Route("/{id}/delete", name="producto_delete")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') or has_role('ROLE_VENDEDOR') ")
     */
    public function deleteAction(Request $request, Producto $producto)
    {
        $em = $this->getDoctrine()->getManager();

        $producto->setEstado(false);
        $em->persist($producto);

        foreach($producto->getProductoXLocal() as $item){
            $item->setEstado(false);
            $em->persist($item);
        }

        try{

            $em->flush();

            $this->addFlash("success", "El registro fue eliminado exitosamente.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser eliminado.");
        }
        
        return $this->redirectToRoute('producto_index');
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
        $resize->saveImage($this->getParameter('imagenes_directorio').'/100x100/'.$fileName);

        //Creamos la imagen en  500x500
        $resize = new ResizeImage($this->getParameter('imagenes_directorio').'/'.$fileName);
        $resize->resizeTo(500, 500, 'exact');
        $resize->saveImage($this->getParameter('imagenes_directorio').'/500x500/'.$fileName);

        unlink($this->getParameter('imagenes_directorio').'/'.$fileName);  

        return true;

    }

    private function generarCodigo($empresa)
    {
        $em = $this->getDoctrine()->getManager();
        
        $empresaObj     = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $codigo = '';

        $productos = $em->getRepository('AppBundle:Producto')->findBy(array('empresa'=>$empresa));

        if(count($productos) == 0)
        {
            $codigo = $empresaObj->getPrefijoCodigoProducto().'1';

        }
        else
        {
            $contador = count($productos) + 1;
            // $cantidadDigito = strlen($contador);
            // $numCerosMaximo = 7;
            // $codigo = (string)$contador;

            // for($i = $cantidadDigito; $i < $numCerosMaximo; $i++){
            //     $codigo = "0".$codigo;
            // }

            $codigo = $empresaObj->getPrefijoCodigoProducto().$contador;

        }

        return $codigo;

    }

}
