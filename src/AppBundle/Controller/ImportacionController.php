<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\ProductoMarca;
use AppBundle\Entity\ProductoCategoria;
use AppBundle\Entity\Producto;
use AppBundle\Entity\ProductoXLocal;
use AppBundle\Entity\Cliente;
use AppBundle\Entity\Proveedor;
use AppBundle\Entity\Empresa;
use AppBundle\Entity\EmpresaLocal;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Importacion controller.
 *
 * @Route("importacion")
 */
class ImportacionController extends Controller
{

    /**
     * Permite subir el archivo para la importación de egresados
     *
     * @Route("/subir/csv", name="importacion_subir_csv")
     * @Method({"GET", "POST"})
     */
    public function subirCsvAction(Request $request)
    {
        $em    = $this->getDoctrine()->getManager();

        $this->actualizarCarreras();     

        $form = $this->createFormBuilder()
                    ->add('attachment', 'file',array( 'label' => 'Archivo','required'=>true))
                    ->getForm();
       
        $data['form']                       = $form->createView(); 
        $data['titulo']                     = 'Importar Egresados - Subir Archivo';


        return $this->render('AppBundle:Egresado:subirCsv.html.twig',$data);

    }

    /**
     * Lists all detalleVentum entities.
     *
     * @Route("/{id}", name="importacion_index")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function indexAction(Request $request,EmpresaLocal $local)
    {

      $em = $this->getDoctrine()->getManager();

      $session        = $request->getSession();

      $empresa = $local->getEmpresa();

      $form = $this->createFormBuilder()
              ->add('attachment',FileType::class,array( 'label' => false,'required'=>true))
              ->getForm();


      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) 
      {
      		$file = $form['attachment']->getData();

      		if($file->guessExtension() != 'xlsx')
      		{
      			$this->addFlash("danger", " El archivo no tiene extensión XLSX. Suba el archivo con el formato correcto.");
						return $this->redirectToRoute('importacion_index',array('id'=>$local->getId()));
      		}

      		$fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

          // Movemos el archivo al directorio uploads/files
          try {

              $file->move(
                  $this->getParameter('archivos_directorio'),
                  $fileName
              );

          } catch (FileException $e) {

              $this->addFlash("danger", $e." Ocurrió un error inesperado, el archivo no fue movido.");
              return $this->redirectToRoute('importacion_index',array('id'=>$local->getId()));
          }

      		$tmpfname = $this->getParameter('archivos_directorio').'/'.$fileName;

      		//dump($tmpfname);
      		//die();

					$objPHPExcel = \PHPExcel_IOFactory::load($tmpfname);

					$i = 0;
					$errores = array();
					foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {

						//Importamos los productos
						if($i == 0){

					    $highestRow         = $worksheet->getHighestRow();
					    $highestColumn      = $worksheet->getHighestColumn();
					    $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

					    for ($row = 2; $row <= $highestRow; ++ $row) {

			            $codigo 					= $worksheet->getCellByColumnAndRow(0, $row)->getValue();
			            $producto_nombre 	= $worksheet->getCellByColumnAndRow(1, $row)->getValue();
			            $marca_nombre 		= $worksheet->getCellByColumnAndRow(2, $row)->getValue();
			            $categoria_nombre = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
			            $precio_venta 		= $worksheet->getCellByColumnAndRow(4, $row)->getValue();
			            $precio_costo 		= $worksheet->getCellByColumnAndRow(5, $row)->getValue();
			            $precio_x_mayor 	= $worksheet->getCellByColumnAndRow(6, $row)->getValue();
			            $stock 						= $worksheet->getCellByColumnAndRow(7, $row)->getValue();
			            $unidad_venta 		= $worksheet->getCellByColumnAndRow(8, $row)->getValue();
			            $unidad_compra 		= $worksheet->getCellByColumnAndRow(9, $row)->getValue();

			            $categoria = null;
			            $marca = null;

			            if($marca_nombre != '')
			            {
					            $marca = $em->getRepository('AppBundle:ProductoMarca')->findOneBy(array('empresa'=>$empresa,'nombre'=>$marca_nombre));

				            	if(!$marca){

												  $marca = new ProductoMarca();
												  $marca->setNombre($marca_nombre);
												  $marca->setEmpresa($empresa);
												  $marca->setEstado(true);
												  $em->persist($marca);

				            	}		

			            }
			            else
			            {
			            		$errores[] = 'Productos - Error en la línea '.$row.' .Nombre de marca vacío.';

			            }

			            if($categoria_nombre != '')
			            {
				            	$categoria = $em->getRepository('AppBundle:ProductoCategoria')->findOneBy(array('empresa'=>$empresa,'nombre'=>$categoria_nombre));

				            	if(!$categoria){

												  $categoria = new ProductoCategoria();
												  $categoria->setNombre($categoria_nombre);
												  $categoria->setEmpresa($empresa);
												  $categoria->setEstado(true);
												  $em->persist($categoria);

				            	}

			            }
			            else
			            {
			            		$errores[] = 'Productos - Error en la línea '.$row.' .Nombre de categoría vacío.';

			            }


			            if($unidad_venta != '')
			            {
					            $uv = $em->getRepository('AppBundle:ProductoUnidad')->findOneBy(array('empresa'=>$empresa,'nombre'=>$unidad_venta));

				            	if(!$uv){

												  $marca = new ProductoMarca();
												  $marca->setNombre($marca_nombre);
												  $marca->setEmpresa($empresa);
												  $marca->setEstado(true);
												  $em->persist($marca);

				            	}		

			            }
			            else
			            {
			            		$errores[] = 'Productos - Error en la línea '.$row.' .Nombre de marca vacío.';

			            }


			            if($codigo != '' && $categoria != null && $marca != null)
			            {

					            $producto = $em->getRepository('AppBundle:Producto')->findOneBy(array('empresa'=>$empresa,'codigo'=>$codigo));

				            	if(!$producto){

												  $producto = new Producto();
												  $producto->setCodigo($codigo);
												  $producto->setNombre($producto_nombre);
												  $producto->setEmpresa($empresa);
												  $producto->setEstado(true);
												  $producto->setMarca($marca);
												  $producto->setCategoria($categoria);
												  $producto->setPrecioUnitario($precio_venta);
												  $producto->setPrecioCompra($precio_costo);
												  $producto->setPrecioCantidad($precio_x_mayor);
												  $producto->setEstado(true);

												  $codigoSunat = $em->getRepository('AppBundle:ProductoCodigoSunat')->find(15584);
												  $producto->setCodigoSunat($codigoSunat);

												  //$unidad = $em->getRepository('AppBundle:ProductoUnidad')->findOneBy(array('empresa'=>$empresa,'codigo'=>'1'));
												  $unidadVenta  = ($unidad_venta != '') ? $em->getRepository('AppBundle:ProductoUnidad')->findOneBy(array('empresa'=>$empresa,'nombre'=>$unidad_venta)) : $em->getRepository('AppBundle:ProductoUnidad')->findOneBy(array('empresa'=>$empresa,'codigo'=>'1'));
												  $unidadCompra = ($unidad_compra != '') ? $em->getRepository('AppBundle:ProductoUnidad')->findOneBy(array('empresa'=>$empresa,'nombre'=>$unidad_compra)) : $em->getRepository('AppBundle:ProductoUnidad')->findOneBy(array('empresa'=>$empresa,'codigo'=>'1'));

												  if(!$unidadVenta)
												  {												  	
														$unidadVenta  = $em->getRepository('AppBundle:ProductoUnidad')->findOneBy(array('empresa'=>$empresa,'codigo'=>'1'));											  													  	
												  }


												  if(!$unidadCompra)
												  {												  	
														$unidadCompra  = $em->getRepository('AppBundle:ProductoUnidad')->findOneBy(array('empresa'=>$empresa,'codigo'=>'1'));											  													  	
												  }

												  $producto->setUnidadVenta($unidadVenta);
												  $producto->setUnidadCompra($unidadCompra);
												  $producto->setTipo('producto');
												  $em->persist($producto);

												  $productoXLocal = new ProductoXLocal();
												  $productoXLocal->setLocal($local);
												  $productoXLocal->setProducto($producto);
												  $productoXLocal->setStock($stock);
												  $productoXLocal->setEstado(true);
												  $em->persist($productoXLocal);

				            	}
				            	else
				            	{
				            			$productoXLocal = $em->getRepository('AppBundle:ProductoXLocal')->findOneBy(array('producto'=>$producto,'local'=>$local));

												  if(!$productoXLocal)
												  {

													  $productoXLocal = new ProductoXLocal();
													  $productoXLocal->setLocal($local);
													  $productoXLocal->setProducto($producto);
													  $productoXLocal->setStock($stock);
													  $productoXLocal->setEstado(true);
													  $em->persist($productoXLocal);

												  }
												  else
												  {
												  	$productoXLocal->setStock($stock);
												  	$em->persist($productoXLocal);

												  }

				            	}

			            }
			            else
			            {
			            		$errores[] = 'Productos - Error en la línea '.$row.' .Código,categoría y/o marca de producto vacío.';

			            }


		            	try {

		            		$em->flush();
		            		
		            	} catch (\Exception $e) {

			              $this->addFlash("danger", $e." Hubo un error al importar los productos.");
			              return $this->redirectToRoute('importacion_index',array('id'=>$local->getId()));		            		
		            		
		            	}

				      }

					  }

					  //Importamos los clientes
						if($i == 1){

					    $highestRow         = $worksheet->getHighestRow();
					    $highestColumn      = $worksheet->getHighestColumn();
					    $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);


					    for ($row = 2; $row <= $highestRow; ++ $row) {

			            $cliente_nombre  	= $worksheet->getCellByColumnAndRow(0, $row)->getValue();
			            $tipo_documento 	= $worksheet->getCellByColumnAndRow(1, $row)->getValue();
			            $numero_documento	= $worksheet->getCellByColumnAndRow(2, $row)->getValue();
			            $direccion        = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
			            $distrito      		= $worksheet->getCellByColumnAndRow(4, $row)->getValue();
			            $telefono 		    = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
			            $email 	          = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
			            
			            if($numero_documento != '')
			            {

				            $cliente = $em->getRepository('AppBundle:Cliente')->findOneBy(array('local'=>$local,'ruc'=>$numero_documento));

			            	if(!$cliente){

											  $cliente = new Cliente();
											  $cliente->setRazonSocial($cliente_nombre);
											  $cliente->setRuc($numero_documento);
											  $cliente->setLocal($local);
											  $cliente->setEstado(true);
											  $cliente->setDireccion($direccion);
											  $cliente->setTelefono($telefono);
											  $cliente->setEmail($email);

											  $tipoDocumento = $em->getRepository('AppBundle:TipoDocumento')->findOneBy(array('nombre'=>$tipo_documento));

											  if($tipoDocumento){
											  	$cliente->setTipoDocumento($tipoDocumento);
											  }else{
											  	$tipoDocumento = $em->getRepository('AppBundle:TipoDocumento')->findOneBy(array('nombre'=>'DNI'));
											  	$cliente->setTipoDocumento($tipoDocumento);

											  }
											  
											  $em->persist($cliente);

			            	}		

			            }
			            else
			            {
			            	$errores[] = 'Clientes - Error en la línea '.$row.' .Número de documento vacío.';
			            }

		            	try {

		            		$em->flush();
		            		
		            	} catch (\Exception $e) {

			              $this->addFlash("danger", $e." Hubo un error al importar los clientes.");
			              return $this->redirectToRoute('importacion_index',array('id'=>$local->getId()));		  
		            		
		            	}

				      }

					  }

					  //Importamos los proveedores
						if($i == 2){

					    $highestRow         = $worksheet->getHighestRow();
					    $highestColumn      = $worksheet->getHighestColumn();
					    $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);


					    for ($row = 2; $row <= $highestRow; ++ $row) {

			            $proveedor_nombre = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
			            $ruc 							= $worksheet->getCellByColumnAndRow(1, $row)->getValue();
			            $direccion        = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
			            $distrito      		= $worksheet->getCellByColumnAndRow(3, $row)->getValue();
			            $telefono 		    = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
			            $email 	          = $worksheet->getCellByColumnAndRow(5, $row)->getValue();

			            if($ruc != '')
			            {

					            $proveedor = $em->getRepository('AppBundle:Proveedor')->findOneBy(array('empresa'=>$empresa,'ruc'=>$ruc));

				            	if(!$proveedor){

				            			$cod = $row - 1;

				            			$codigo_proveedor = str_pad($cod, 6, "0", STR_PAD_LEFT);

												  $proveedor = new Proveedor();
												  $proveedor->setCodigo('PROV'.$codigo_proveedor);
												  $proveedor->setEstado(true);
												  $proveedor->setRuc($ruc);
												  $proveedor->setNombre($proveedor_nombre);								  
												  $proveedor->setEmpresa($empresa);								  
												  $proveedor->setDireccion($direccion);
												  $proveedor->setTelefono($telefono);
												  $proveedor->setEmail($email);

												  if($distrito != ''){

													  $distritoObj = $em->getRepository('AppBundle:Distrito')->findOneBy(array('nombre'=>$distrito));

													  if($distritoObj)
													  {
													  	$proveedor->setDistrito($distritoObj);
													  }			

												  }						  

												  $em->persist($proveedor);

				            	}		


			            }
			            else
			            {
			            	$errores[] = 'Proveedores - Error en la línea '.$row.' .Número de documento vacío.';
			            }			            


		            	try {

		            		$em->flush();
		            		
		            	} catch (\Exception $e) {


			              $this->addFlash("danger", $e." Hubo un error al importar los proveedores.");
			              return $this->redirectToRoute('importacion_index',array('id'=>$local->getId()));		  
		            		
		            	}

				      }

					  }

					  $i++;

					}

					foreach($errores as $er=>$error)
					{
							$this->addFlash("warning", $error);
					}
					
					$this->addFlash("success", "La información fue importada exitosamente.");
					return $this->redirectToRoute('importacion_index',array('id'=>$local->getId()));


      }


      return $this->render('importacion/index.html.twig', array(
          'local' => $local,
          'form' 		=> $form->createView(),
          'titulo' 	=> 'Importar productos,clientes y proveedores',
      ));



    }	


    /**
     * Lists all detalleVentum entities.
     *
     * @Route("/stock/inicial", name="importacion_stock_inicial")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function stockInicialAction(Request $request)
    {

      $em = $this->getDoctrine()->getManager();

      $session        = $request->getSession();
      $local          = 2;//$session->get('local');
      $empresa        = $session->get('empresa');

      $empresaObj = $em->getRepository('AppBundle:Empresa')->find($empresa);
      $localObj = $em->getRepository('AppBundle:EmpresaLocal')->find($local);

			$tmpfname = "docs/ipesa.xlsx";

			$objPHPExcel = \PHPExcel_IOFactory::load($tmpfname);

			$i = 0;
			foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {

				//Importamos los productos
				if($i == 0){

			    $highestRow         = $worksheet->getHighestRow();
			    $highestColumn      = $worksheet->getHighestColumn();
			    $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

			    for ($row = 2; $row <= $highestRow; ++ $row) {

	            $codigo 					= $worksheet->getCellByColumnAndRow(0, $row)->getValue();
	            $stock 	          = $worksheet->getCellByColumnAndRow(1, $row)->getValue();


	            $producto = $em->getRepository('AppBundle:Producto')->findOneBy(array('empresa'=>$empresa,'codigo'=>$codigo));
	            $producto_id = $producto->getId();

	            if($producto)
	            {
	            	$productoXLocal = $em->getRepository('AppBundle:ProductoXLocal')->findOneBy(array('producto'=>$producto_id,'local'=>$local));

	            	if($productoXLocal)
	            	{
	            		$productoXLocal->setStockInicial($stock);
	            		$em->persist($productoXLocal);
	            	}
	            	

	            }

		      }


        	try {

        		$em->flush();
        		
        	} catch (\Exception $e) {

        		return new Response('Hubo un error al importar los productos.'.$e);  
        		
        	}		      

			  }

			  $i++;

			}



			return new Response('Se ingresaron los productos,cliente y proveedores.');  




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
