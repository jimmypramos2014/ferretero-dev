<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Empresa;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Producto;
use AppBundle\Entity\ProductoXLocal;
use AppBundle\Entity\EmpresaLocal;
use AppBundle\Entity\Caja;
use AppBundle\Entity\Cliente;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * Empresa controller.
 *
 * @Route("empresa")
 */
class EmpresaController extends Controller
{
    /**
     * Lists all empresa entities.
     *
     * @Route("/", name="empresa_index")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        //Creamos la carpeta donde se guardaran los formatos de venta
        // $fileSystem = new Filesystem();
        // // $fileSystem->mkdir('formatos/'.$empresa->getId());

        // dump($fileSystem);
        // die();



        $empresas = $em->getRepository('AppBundle:Empresa')->findBy(array('estado'=>true));

        return $this->render('empresa/index.html.twig', array(
            'empresas' => $empresas,
            'titulo'   => 'Lista de empresas',
        ));
    }

    /**
     * Creates a new empresa entity.
     *
     * @Route("/new", name="empresa_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function newAction(Request $request)
    {

        $fecha = new \DateTime();
        $empresa = new Empresa();
        $form = $this->createForm('AppBundle\Form\EmpresaType', $empresa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em   = $this->getDoctrine()->getManager();

            $session        = $request->getSession();
            $usuario        = $session->get('usuario');


            $conn = $this->get('database_connection');
            
            $empresa->setProformaFormato('A4');
            $empresa->setProformaOrientacion('Landscape');
            $empresa->setPrefijoCodigoProducto('PROD-');
            $empresa->setGuiaremisionAncho('210');
            $empresa->setGuiaremisionLargo('297');
            $empresa->setEstadoPago(true);
            $em->persist($empresa);
            $em->flush();

            //Insertamos valores en la tabla producto_unidad_categoria
            $sql  = "INSERT INTO producto_unidad_categoria (codigo,nombre,descripcion,empresa_id) ";
            $sql .= " SELECT codigo,nombre,descripcion,? FROM producto_unidad_categoria_base ";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(1, $empresa->getId());
            $stmt->execute();


            //Insertamos valores en la tabla producto_unidad
            $sql  = "INSERT INTO producto_unidad (codigo,nombre,descripcion,abreviatura,categoria_id,empresa_id,tipo,estado,ratio) ";
            $sql .= " SELECT codigo,nombre,descripcion,abreviatura,categoria_id,?,tipo,estado,ratio FROM producto_unidad_base ";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(1, $empresa->getId());
            $stmt->execute();

            //Insertamos valores en la tabla proveedor_servicio
            $sql  = "INSERT INTO proveedor_servicio (codigo,nombre,descripcion,empresa_id) ";
            $sql .= " SELECT codigo,nombre,descripcion,? FROM proveedor_servicio_base ";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(1, $empresa->getId());
            $stmt->execute();

            //Insertamos valores en la tabla comprobante_x_empresa
            $sql  = "INSERT INTO comprobante_x_empresa (comprobante_id,estado,empresa_id) ";
            $sql .= " SELECT id,1,? FROM comprobante ";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(1, $empresa->getId());
            $stmt->execute();
            

            //Generamos registros en la tabla producto_x_local, replicando los productos
            // $productos = $em->getRepository('AppBundle:Producto')->findBy(array('empresa'=>$empresa->getId()));

            // if(count($productos) == 0 ){

            //     $productosbase = $em->getRepository('AppBundle:ProductoBase')->findAll();

            //     foreach($productosbase as $productobase){

            //         $productoObj = new Producto();
            //         $productoObj->setCodigo($productobase->getCodigo());
            //         $productoObj->setNombre($productobase->getNombre());
            //         $productoObj->setMarca($productobase->getMarca());
            //         $productoObj->setCategoria($productobase->getCategoria());
            //         $productoObj->setEmpresa($empresa);
            //         $productoObj->setEstado(true);

            //         $productoUnidad = $em->getRepository('AppBundle:ProductoUnidad')->find(1);
            //         $productoObj->setUnidadCompra($productoUnidad);
            //         $productoObj->setUnidadVenta($productoUnidad);

            //         $em->persist($productoObj);

            //     }

            //     $em->flush();

            //     $productos = $em->getRepository('AppBundle:Producto')->findBy(array('empresa'=>$empresa));

            // }

            //Creamos un local general por defecto
            $localObj = new EmpresaLocal();
            $localObj->setCodigo('001');
            $localObj->setNombre('Local General');
            $localObj->setEstado(true);
            $localObj->setPrefijoVoucher('PG1');
            $localObj->setSerieBoleta('BBB1');
            $localObj->setSerieFactura('FFF1');
            $localObj->setSerieGuiaremision('TPP1');
            $localObj->setEmpresa($empresa);
            $localObj->setImagenProductoDefault('producto_default.png');
            $localObj->setImagenCategoriaDefault('categoria_default.png');
            $localObj->setFacturacionElectronica(false);
            $localObj->setVentaNegativo(false);

            $em->persist($localObj);  

            //Creamos un caja general por defecto
            $caja = new Caja();
            $caja->setLocal($localObj);
            $caja->setNombre('Caja general');
            $caja->setEstado(true);
            $caja->setCondicion('cerrado');

            $em->persist($caja);

            //Creamos un cliente x defecto
            $clienteObj = new Cliente();
            $clienteObj->setRazonSocial('---');
            $clienteObj->setRuc('---');
            $clienteObj->setEstado(true);
            $clienteObj->setLocal($localObj);
            $clienteObj->setCodigo('000000');
            $tipoDocumento = $em->getRepository('AppBundle:TipoDocumento')->find(1);
            $clienteObj->setTipoDocumento($tipoDocumento);

            $em->persist($clienteObj);              



            // Guardamos los productos para el local creado
            // foreach($productos as $producto){

            //     $productoXLocal = new ProductoXLocal();
            //     $productoXLocal->setLocal($localObj);
            //     $productoXLocal->setProducto($producto);
            //     $productoXLocal->setStock(0);
            //     $em->persist($productoXLocal);

            // }



            try {

                $em->flush();

                //Insertamos valores en la tabla documento
                $sql  = "INSERT INTO documento (codigo,nombre,descripcion,empresa_id,empresa_local_id) ";
                $sql .= " SELECT codigo,nombre,descripcion,?,? FROM documento_base ";

                $stmt = $conn->prepare($sql);
                $stmt->bindValue(1, $empresa->getId());
                $stmt->bindValue(2, $localObj->getId());
                $stmt->execute();

                //Generamos registros en la tabla componente_x_documento,
                $documentos = $em->getRepository('AppBundle:Documento')->findBy(array('local'=>$localObj->getId()));

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

                //Creamos la carpeta donde se guardaran los formatos de venta
                $fileSystem = new Filesystem();
                $fileSystem->mkdir('formatos/'.$empresa->getId());
                $fileSystem->mkdir('uploads/files/'.$empresa->getId().'/'.$localObj->getId(),0777);

                $fileSystem->copy('uploads/files/recibo.pdf', 'uploads/files/'.$empresa->getId().'/'.$localObj->getId().'/recibo.pdf');

                $this->addFlash("success", "El registro fue guardado exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
                
            }            

            return $this->redirectToRoute('empresa_index');
        }

        return $this->render('empresa/new.html.twig', array(
            'empresa' => $empresa,
            'form' => $form->createView(),
            'titulo'   => 'Registrar empresa',
        ));
    }


    /**
     * Displays a form to edit an existing empresa entity.
     *
     * @Route("/{id}/edit", name="empresa_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function editAction(Request $request, Empresa $empresa)
    {
        $editForm = $this->createForm('AppBundle\Form\EmpresaType', $empresa);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $em = $this->getDoctrine()->getManager();

            try {
                $em->flush();
                $this->addFlash("success", "El registro fue guardado exitosamente.");
                
            } catch (\Exception $e) {

                $this->addFlash("danger", $e." Ocurrió un error inesperado, el registro no se guardó.");
                
            }   

            return $this->redirectToRoute('empresa_index');
        }

        return $this->render('empresa/edit.html.twig', array(
            'empresa' => $empresa,
            'edit_form' => $editForm->createView(),
            'titulo' => 'Editar empresa',
        ));
    }

    /**
     * Deletes a empresa entity.
     *
     * @Route("/{id}/delete", name="empresa_delete")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN') ")
     */
    public function deleteAction(Request $request, Empresa $empresa)
    {
        $em = $this->getDoctrine()->getManager();

        $empresa->setEstado(false);
        $em->persist($empresa);

        try{

            $em->flush();

            $this->addFlash("success", "El registro fue eliminado exitosamente.");

        }catch(\Exception $e){

            $this->addFlash("danger", "Ocurrió un error inesperado, el registro no pudo ser eliminado.");
        }
        return $this->redirectToRoute('empresa_index');

    }


}
