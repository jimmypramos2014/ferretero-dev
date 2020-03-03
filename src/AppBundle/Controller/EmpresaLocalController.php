<?php

namespace AppBundle\Controller;

use AppBundle\Entity\EmpresaLocal;
use AppBundle\Entity\ProductoXLocal;
use AppBundle\Entity\Producto;
use AppBundle\Entity\Caja;
use AppBundle\Entity\Cliente;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File\File;
use AppBundle\Imagen\ResizeImage;

/**
 * Empresalocal controller.
 *
 * @Route("empresalocal")
 */
class EmpresaLocalController extends Controller
{
    /**
     * Lists all empresaLocal entities.
     *
     * @Route("/", name="empresalocal_index")
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

        $dql = "SELECT l FROM AppBundle:EmpresaLocal l ";
        $dql .= " JOIN l.empresa em";
        $dql .= " WHERE  l.estado = 1  ";

        if($empresa != ''){
            $dql .= " AND em.id =:empresa  ";
        }

        $dql .= " ORDER BY l.nombre ";

        $query = $em->createQuery($dql);

        if($empresa != ''){
            $query->setParameter('empresa',$empresa);         
        }
 
        $empresaLocals =  $query->getResult(); 


        return $this->render('empresalocal/index.html.twig', array(
            'empresaLocals' => $empresaLocals,
            'titulo'   => 'Lista de locales',
        ));
    }

    /**
     * Creates a new empresaLocal entity.
     *
     * @Route("/new", name="empresalocal_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
        $rol            = $session->get('rol');

        $conn = $this->get('database_connection');
        $em = $this->getDoctrine()->getManager();

        //$empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);

        $param = array('empresa'=>$empresa);

        if($rol == 'Superadmin'){
            $param = array();
        }


        $empresaLocal = new Empresalocal();
        $form = $this->createForm('AppBundle\Form\EmpresaLocalType', $empresaLocal,$param);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            

            //Guardamos la imagen x default de un producto
            $fileProducto = $empresaLocal->getImagenProductoDefault();
            if($fileProducto){
                $fileNameProducto = $this->generateUniqueFileName().'.'.$fileProducto->guessExtension();

                $fileProducto->move(
                    $this->getParameter('imagenes_directorio'),
                    $fileNameProducto
                );

                $empresaLocal->setImagenProductoDefault($fileNameProducto);

                $rutaProducto = $this->getParameter('imagenes_directorio').'/'.$fileNameProducto;

                $this->redimensionarImagen($fileNameProducto);

            }else{

                $empresaLocal->setImagenProductoDefault('producto_default.png');
                
            }

            //Guardamos la imagen x default de una categoria
            $fileCategoria = $empresaLocal->getImagenCategoriaDefault();
            if($fileCategoria){
                $fileNameCategoria = $this->generateUniqueFileName().'.'.$fileCategoria->guessExtension();

                $fileCategoria->move(
                    $this->getParameter('imagenes_directorio'),
                    $fileNameCategoria
                );

                $empresaLocal->setImagenCategoriaDefault($fileNameCategoria);

                $rutaCategoria = $this->getParameter('imagenes_directorio').'/'.$fileNameCategoria;

                $this->redimensionarImagen($fileNameCategoria);

            }else{
                $empresaLocal->setImagenCategoriaDefault('categoria_default.png');
            }

            $em->persist($empresaLocal);


            if($rol == 'Superadmin'){
                $empresa = $empresaLocal->getEmpresa()->getId();
            }        

            $productos = $em->getRepository('AppBundle:Producto')->findBy(array('empresa'=>$empresa ));

            // Guardamos los productos
            foreach($productos as $producto){

                $productoXLocal = new ProductoXLocal();
                $productoXLocal->setLocal($empresaLocal);
                $productoXLocal->setProducto($producto);
                $productoXLocal->setStock(0);
                $productoXLocal->setEstado(true);
                $em->persist($productoXLocal);

            }

            //Creamos un caja general por defecto
            $caja = new Caja();
            $caja->setLocal($empresaLocal);
            $caja->setNombre('Caja 0001');
            $caja->setEstado(true);
            $caja->setCondicion('cerrado');

            $em->persist($caja);

            //Creamos un cliente por defecto
            $clienteObj = new Cliente();
            $clienteObj->setRazonSocial('---');
            $clienteObj->setRuc('---');
            $clienteObj->setEstado(true);
            $clienteObj->setLocal($empresaLocal);
            $clienteObj->setCodigo('000000');
            $tipoDocumento = $em->getRepository('AppBundle:TipoDocumento')->find(1);
            $clienteObj->setTipoDocumento($tipoDocumento);

            $em->persist($clienteObj);     


            try {

                $em->flush();

                //Insertamos valores en la tabla documento
                $sql  = "INSERT INTO documento (codigo,nombre,descripcion,empresa_id,empresa_local_id) ";
                $sql .= " SELECT codigo,nombre,descripcion,?,? FROM documento_base ";

                $stmt = $conn->prepare($sql);
                $stmt->bindValue(1, $empresaLocal->getEmpresa()->getId());
                $stmt->bindValue(2, $empresaLocal->getId());
                $stmt->execute();

                //Generamos registros en la tabla componente_x_documento,
                $documentos = $em->getRepository('AppBundle:Documento')->findBy(array('local'=>$empresaLocal->getId()));

                foreach($documentos as $documento){

                    $codigoDocumento = $documento->getCodigo();

                    //Seleccionamos la tabla componente_x_documento_base
                    $sql = " SELECT cxd.posicion_x,cxd.estado,cxd.documento_id,cxd.componente_id,cxd.posicion_y FROM componente_x_documento_base cxd 
                                INNER JOIN documento d ON cxd.documento_id = d.id WHERE d.codigo = ? ";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue(1, $codigoDocumento);
                    $stmt->execute();
                    $componentes = $stmt->fetchAll();

                    foreach($componentes as $componente)
                    {

                        //Insertamos valores en la tabla componente_x_documento
                        $sql  = "INSERT INTO componente_x_documento (posicion_x,estado,documento_id,componente_id,posicion_y) ";
                        $sql .= " VALUES (?,?,?,?,?) ";

                        $stmt = $conn->prepare($sql);
                        $stmt->bindValue(1, $componente['posicion_x']);
                        $stmt->bindValue(2, $componente['estado']);
                        $stmt->bindValue(3, $documento->getId());
                        $stmt->bindValue(4, $componente['componente_id']);
                        $stmt->bindValue(5, $componente['posicion_y']);
                        $stmt->execute();

                    }

                }


                $this->addFlash("success", "El registro fue guardado exitosamente. Asimismo fue creada una caja por defecto para este local.");

            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
                return $this->redirectToRoute('empresalocal_index');                
            }


            return $this->redirectToRoute('empresalocal_index');
        }

        return $this->render('empresalocal/new.html.twig', array(
            'empresaLocal' => $empresaLocal,
            'form' => $form->createView(),
            'titulo'   => 'Registrar local',
        ));
    }

    /**
     * Finds and displays a empresaLocal entity.
     *
     * @Route("/{id}", name="empresalocal_show")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showAction(EmpresaLocal $empresaLocal)
    {

        return $this->render('empresalocal/show.html.twig', array(
            'empresaLocal' => $empresaLocal,
        ));
    }

    /**
     * Displays a form to edit an existing empresaLocal entity.
     *
     * @Route("/{id}/edit", name="empresalocal_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, EmpresaLocal $empresaLocal)
    {
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');        
        $rol            = $session->get('rol');

        $param = array('empresa'=>$empresa);

        if($rol == 'Superadmin'){
            $param = array();
        }

        $originalFileProducto = null;

        if($empresaLocal->getImagenProductoDefault()){

            $originalFileProducto = $empresaLocal->getImagenProductoDefault();

            $empresaLocal->setImagenProductoDefault(
                new File($this->getParameter('imagenes_directorio').'/100x100/'.$empresaLocal->getImagenProductoDefault())
            );
        }

        $originalFileCategoria = null;

        if($empresaLocal->getImagenCategoriaDefault()){

            $originalFileCategoria = $empresaLocal->getImagenCategoriaDefault();

            $empresaLocal->setImagenCategoriaDefault(
                new File($this->getParameter('imagenes_directorio').'/100x100/'.$empresaLocal->getImagenCategoriaDefault())
            );
        }


        $editForm = $this->createForm('AppBundle\Form\EmpresaLocalType', $empresaLocal,$param);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $fileProducto = $empresaLocal->getImagenProductoDefault();

            if($fileProducto){

                $fileNameProducto = $this->generateUniqueFileName().'.'.$fileProducto->guessExtension();

                $fileProducto->move(
                    $this->getParameter('imagenes_directorio'),
                    $fileNameProducto
                );


                $empresaLocal->setImagenProductoDefault($fileNameProducto);

                $rutaProducto = $this->getParameter('imagenes_directorio').'/'.$fileNameProducto;

                $this->redimensionarImagen($fileNameProducto);                

            }else{

                $empresaLocal->setImagenProductoDefault($originalFileProducto);
            }


            //Guadamos imagen de categoria

            $fileCategoria = $empresaLocal->getImagenCategoriaDefault();

            if($fileCategoria){

                $fileNameCategoria = $this->generateUniqueFileName().'.'.$fileCategoria->guessExtension();

                $fileCategoria->move(
                    $this->getParameter('imagenes_directorio'),
                    $fileNameCategoria
                );


                $empresaLocal->setImagenCategoriaDefault($fileNameCategoria);

                $rutaCategoria = $this->getParameter('imagenes_directorio').'/'.$fileNameCategoria;

                $this->redimensionarImagen($fileNameCategoria);                

            }else{

                $empresaLocal->setImagenCategoriaDefault($originalFileCategoria);
            }


            try {

                $em->flush();

            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
                return $this->redirectToRoute('empresalocal_index');                
            }


            return $this->redirectToRoute('empresalocal_index');
        }

        return $this->render('empresalocal/edit.html.twig', array(
            'empresaLocal' => $empresaLocal,
            'edit_form' => $editForm->createView(),
            'titulo'   => 'Editar local',
            'originalFileProducto' => $originalFileProducto,
            'originalFileCategoria' => $originalFileCategoria,
        ));
    }

    /**
     * Deletes a empresaLocal entity.
     *
     * @Route("/{id}/delete", name="empresalocal_delete")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, EmpresaLocal $empresaLocal)
    {
        $em = $this->getDoctrine()->getManager();

        $empresaLocal->setEstado(false);
        $em->persist($empresaLocal);

        try{

            $em->flush();

            $this->addFlash("success", "El registro fue eliminado exitosamente.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser eliminado.");
        }
        return $this->redirectToRoute('empresalocal_index');
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
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

}
