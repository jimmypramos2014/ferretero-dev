<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProductoXLocal;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;

use AppBundle\Barcode\Barcode;
use AppBundle\Imagen\ResizeImage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Productoxlocal controller.
 *
 * @Route("productoxlocal")
 */
class ProductoXLocalController extends Controller
{
    /**
     * Lists all productoXLocal entities.
     *
     * @Route("/", name="productoxlocal_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        
        //$productoXLocals = $em->getRepository('AppBundle:ProductoXLocal')->findBy(array('local'=>$local));

        return $this->render('productoxlocal/index.html.twig', array(
            //'productoXLocals'   => $productoXLocals,
            'titulo'            => 'Lista de productos',
        ));
    }

    /**
     * Creates a new productoXLocal entity.
     *
     * @Route("/new", name="productoxlocal_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $codigo = (string)$this->generarCodigo($empresaObj);

        $productoXLocal = new Productoxlocal();
        $form = $this->createForm('AppBundle\Form\ProductoXLocalType', $productoXLocal,array('codigo'=>$codigo,'empresa'=>$empresa));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $localObj   = $em->getRepository('AppBundle:EmpresaLocal')->find($local);
            $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);

            $productoXLocal->getProducto()->setEstado(true);
            $productoXLocal->setEstado(true);
            $productoXLocal->getProducto()->setEmpresa($empresaObj);

            $file = $productoXLocal->getProducto()->getImagen();

            if($file){
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('imagenes_directorio'),
                    $fileName
                );

                $productoXLocal->getProducto()->setImagen($fileName);

                $ruta = $this->getParameter('imagenes_directorio').'/'.$fileName;

                $this->redimensionarImagen($fileName);

            }

            $productoXLocal->setLocal($localObj);
            //$productoXLocal->setEstado(true);
            
            $em->persist($productoXLocal);

            //Creamos el mismo producto con stock '0' para el resto de locales
            $locales = $em->getRepository('AppBundle:EmpresaLocal')->findBy(array('empresa'=>$empresa));
            foreach($locales as $loc){

                if($loc->getId() != $local){
                    $productoXLocalNuevo = new ProductoXlocal();
                    $productoXLocalNuevo->setStock(0);
                    $productoXLocalNuevo->setEstado(true);
                    $productoXLocalNuevo->setLocal($loc);
                    $productoXLocalNuevo->setProducto($productoXLocal->getProducto());
                    $em->persist($productoXLocalNuevo);
                }
            }
            

            try {

                $em->flush();

                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");                
            }

            
            return $this->redirectToRoute('productoxlocal_index');
        }

        return $this->render('productoxlocal/new.html.twig', array(
            'productoXLocal' => $productoXLocal,
            'form' => $form->createView(),
            'titulo'    => 'Registrar producto',
            'codigo'    => $codigo
        ));
    }



    /**
     * Displays a form to edit an existing productoXLocal entity.
     *
     * @Route("/{id}/edit", name="productoxlocal_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ProductoXLocal $productoXLocal)
    {

        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');  

        $originalFile = null;

        if($productoXLocal->getProducto()->getImagen()){

            $originalFile = $productoXLocal->getProducto()->getImagen();

            $productoXLocal->getProducto()->setImagen(
                new File($this->getParameter('imagenes_directorio').'/100x100/'.$productoXLocal->getProducto()->getImagen())
            );
        }

        $nombre_producto = $productoXLocal->getProducto()->getNombre();
        $stock_producto = $productoXLocal->getStock();


        $editForm = $this->createForm('AppBundle\Form\ProductoXLocalType', $productoXLocal,array('empresa'=>$empresa));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $productoXLocal->getProducto()->setEstado(true);
            
            $localObj   = $em->getRepository('AppBundle:EmpresaLocal')->find($local);
            $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);

            $productoXLocal->setLocal($localObj);
            $productoXLocal->getProducto()->setEmpresa($empresaObj);

            $file = $productoXLocal->getProducto()->getImagen();

            if($file){

                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('imagenes_directorio'),
                    $fileName
                );


                $productoXLocal->getProducto()->setImagen($fileName);

                $ruta = $this->getParameter('imagenes_directorio').'/'.$fileName;

                $this->redimensionarImagen($fileName);                

            }else{

                $productoXLocal->getProducto()->setImagen($originalFile);
            }

            
            $em->persist($productoXLocal);

            $nombre_producto_nuevo = $productoXLocal->getProducto()->getNombre();
            $stock_producto_nuevo  = $productoXLocal->getStock();

            if($nombre_producto != $nombre_producto_nuevo){
                $this->container->get('AppBundle\Util\Util')->registrarLog($productoXLocal,$nombre_producto_nuevo,$nombre_producto,'nombre');
            }
            
            if($stock_producto != $stock_producto_nuevo){
                $this->container->get('AppBundle\Util\Util')->registrarLog($productoXLocal,$stock_producto_nuevo,$stock_producto,'stock');
            }

            try {

                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");

            } catch (\Exception $e) {
                
                $this->addFlash("danger", "Ocurrió un error inesperado, el registro no se guardó.");                
            }

            

            return $this->redirectToRoute('productoxlocal_index');
        }

        return $this->render('productoxlocal/edit.html.twig', array(
            'productoXLocal' => $productoXLocal,
            'edit_form' => $editForm->createView(),
            'titulo'    => 'Editar producto',
            'originalFile' => $originalFile,
        ));
    }

    /**
     * Deletes a productoXLocal entity.
     *
     * @Route("/{id}/delete", name="productoxlocal_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, ProductoXLocal $productoXLocal)
    {
        $em = $this->getDoctrine()->getManager();

        $session        = $request->getSession();
        $empresa        = $session->get('empresa');  

        $locales        = $em->getRepository('AppBundle:EmpresaLocal')->findBy(array('empresa'=>$empresa));

        foreach($locales as $local){

            $localId    = $local->getId();
            $productoId = $productoXLocal->getProducto()->getId();
            $prodXLocal = $em->getRepository('AppBundle:ProductoXLocal')->findOneBy(array('local'=>$localId,'producto'=>$productoId));
            $prodXLocal->setEstado(false);
            $em->persist($prodXLocal);


        }
        
        $productoXLocal->getProducto()->setEstado(false);
        $em->persist($productoXLocal);
        
        try{

            $em->flush();

            $this->addFlash("success", "El registro fue eliminado exitosamente de todos los locales.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser eliminado.");
        }
        
        return $this->redirectToRoute('productoxlocal_index');
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

    /**
     * Imprimir el codigo de barras
     *
     * @Route("/imprimircodigo", name="producto_imprimircodigo")
     * @Method({"GET", "POST"})
     * 
     */
    public function imprimirCodigoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $codigo = $request->query->get('codigo');

        $format = 'png';
        $symbology = 'ean-128';
        $data = $codigo;
        $options = array('ms'=>'s','md'=>0.8,'h'=>120,'w'=>400);//'sf'=>2,

        $generator = new Barcode();

        $svg = $generator->render_svg($symbology, $data, $options);

        $html = $this->render('productoxlocal/imprimircodigo.html.twig', array(
                'codigo' => $codigo,
                'svg'    => $svg
            ))->getContent();



        $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('page-size'=> 'A4','images'=> true));
                
        return new \Symfony\Component\HttpFoundation\Response(
                $pdf, 200, array(
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="file.pdf"',                     
                ));
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

    /**
     * Procesar data desde el server para DataTables
     *
     * @Route("/datatable", name="producto_datatable")
     * @Method({"GET", "POST"})
     * 
     */
    public function dataTableAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');

        $result = $this->container->get('AppBundle\Util\Util')->getProductoXLocalDT('v_producto_x_local',$local);

        $result = self::convert_from_latin1_to_utf8_recursively($result);

        $response = new JsonResponse($result);

        return $response;
     

    }

    private function convert_from_latin1_to_utf8_recursively($dat)
    {
      if (is_string($dat)) {
         return utf8_encode($dat);
      } elseif (is_array($dat)) {
         $ret = [];
         foreach ($dat as $i => $d) $ret[ $i ] = self::convert_from_latin1_to_utf8_recursively($d);

         return $ret;
      } elseif (is_object($dat)) {
         foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

         return $dat;
      } else {
         return $dat;
      }
    }
}
