<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Barcode\ExporData;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sunat controller.
 *
 * @Route("sunat")
 */
class SunatController extends Controller
{

    /**
     * Muestra el  FORMATO 13.1
     *
     * @Route("/formato131", name="sunat_formato131")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO')  ")
     */
    public function formato131Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');


        $param = $request->request->get('appbundle_sunat_f121');

        $ano_select  = $param['ano'];
        $mes_select  = $param['mes'];
        $local_selec = ($param['local']) ? $param['local'] : $local;

        $formSunatF121    = $this->createForm('AppBundle\Form\SunatF121Type', null,array('local'=>$local_selec,'empresa'=>$empresa));

        $files_to_zip = array();
        $generado = false;


        if($request->request->get('generar')){

            $empresaLocalObj  = $em->getRepository('AppBundle:EmpresaLocal')->find($local_selec);
            $ruc_empresa = $empresaLocalObj->getEmpresa()->getRuc();
            $nombre_empresa = $empresaLocalObj->getEmpresa()->getNombre();
            $nombre_local = $empresaLocalObj->getCodigo().' '.$empresaLocalObj->getNombre();            

            $productosConMovimientoEnElMes = $this->container->get('AppBundle\Util\Util')->obtenerProductosConMovimientoEnElMes($local_selec,$ano_select,$mes_select);

            //Creamos la carpeta donde se guardaran los formatos
            $fileSystem = new Filesystem();

            try {
                
                $fileSystem->mkdir('uploads/sunat/'.$empresa.'/'.$local_selec.'/f131/'.$mes_select.'-'.$ano_select);

            } catch (IOExceptionInterface $exception) {

                $this->addFlash("danger", "Ocurrió un error inesperado al crear el directorio en ".$exception->getPath());
                return new Response($e);
            }

            ini_set('memory_limit','1600M');
            ini_set('max_execution_time', 3000);


            $cantidad_productos_movimiento = count($productosConMovimientoEnElMes);

            if($cantidad_productos_movimiento <= 50 )
            {

                $i = 0;
                
                foreach($productosConMovimientoEnElMes as $producto)
                {                              
                    
                    try {

                        $ruta = $this->generarXlsInventarioPermanenteValorizado($empresa,$local_selec,$ano_select,$mes_select,$producto['id'],$ruc_empresa,$nombre_empresa,$nombre_local);

                        $files_to_zip[] = $ruta;
                        
                    } catch (\Exception $e) {

                        $this->addFlash("danger", "Ocurrió un error inesperado. Vuelva a intentarlo.");
                        return new Response($e);
                        
                    }

                    $i++;

                }

            }
            elseif($cantidad_productos_movimiento > 50)
            {
                $array_particionado = array_chunk($productosConMovimientoEnElMes, 50);



                foreach($array_particionado as $array_p)
                {

                    $i = 0;                
                    foreach($array_p as $producto)
                    {

                        try {

                            $ruta = $this->generarXlsInventarioPermanenteValorizado($empresa,$local_selec,$ano_select,$mes_select,$producto['id'],$ruc_empresa,$nombre_empresa,$nombre_local);

                            $files_to_zip[] = $ruta;
                            
                        } catch (\Exception $e) {

                            $this->addFlash("danger", "Ocurrió un error inesperado. Vuelva a intentarlo.");
                            return new Response($e);
                            
                        }


                        $i++;

                    }

                }

            }


            $zip = new \ZipArchive();
            $ruta_zip = 'uploads/sunat/'.$empresa.'/'.$local_selec.'/f131/'.$mes_select.'-'.$ano_select.'.zip';
            $zip->open($ruta_zip, \ZipArchive::CREATE);



            foreach($files_to_zip as $z=>$val){

                if($val != ''){
                    $zip->addFile('uploads/sunat/'.$empresa.'/'.$local_selec.'/f131/'.$mes_select.'-'.$ano_select.'/'.$val,$val);
                }                

            }
            
             
            $zip->close();

            if($fileSystem->exists('uploads/sunat/'.$empresa.'/'.$local_selec.'/f131/'.$mes_select.'-'.$ano_select.'.zip'))
            {
                $generado = true;
                $this->addFlash("success", "Se generaron los documentos exitosamente. Local: ".$nombre_local." .Mes: ".$mes_select." .Año: ".$ano_select);                
            }
            else
            {

                $this->addFlash("warning", "No existe información para los datos seleccionados.");                   
            }


        }


        return $this->render('sunat/formato131.html.twig', array(
            'titulo'     => 'FORMATO 13.1: REGISTRO DE INVENTARIO PERMANENTE VALORIZADO - DETALLE DEL INVENTARIO VALORIZADO',
            //'sunatf131'  => $sunatf131,
            'formSunatF121' => $formSunatF121->createView(),
            'ano'     => $ano_select,
            'mes'     => $mes_select,
            'generado'=> $generado,
            'empresa' => $empresa,
            'local'   => $local_selec
        ));


    }

    /**
     * Muestra el  FORMATO 12.1
     *
     * @Route("/formato121", name="sunat_formato121")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ALMACENERO') ")
     */
    public function formato121Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session        = $request->getSession();
        $local          = $session->get('local');
        $empresa        = $session->get('empresa');
       
        $param = $request->request->get('appbundle_sunat_f121');

        $ano_select = $param['ano'];
        $mes_select = $param['mes'];
        $local_selec = ($param['local']) ? $param['local'] : $local;

        $formSunatF121    = $this->createForm('AppBundle\Form\SunatF121Type', null,array('local'=>$local_selec,'empresa'=>$empresa));

        $files_to_zip = array();
        $generado = false;

        if($request->request->get('generar')){

            //dump($local_selec);
            //die();

            $empresaLocalObj  = $em->getRepository('AppBundle:EmpresaLocal')->find($local_selec);
            $ruc_empresa = $empresaLocalObj->getEmpresa()->getRuc();
            $nombre_empresa = $empresaLocalObj->getEmpresa()->getNombre();
            $nombre_local = $empresaLocalObj->getCodigo().' '.$empresaLocalObj->getNombre();            

            $productosConMovimientoEnElMes = $this->container->get('AppBundle\Util\Util')->obtenerProductosConMovimientoEnElMes($local_selec,$ano_select,$mes_select);

            //Creamos la carpeta donde se guardaran los formatos
            $fileSystem = new Filesystem();

            try {
                
                $fileSystem->mkdir('uploads/sunat/'.$empresa.'/'.$local_selec.'/f121/'.$mes_select.'-'.$ano_select);

            } catch (IOExceptionInterface $exception) {

                $this->addFlash("danger", "Ocurrió un error inesperado al crear el directorio en ".$exception->getPath());
                return $e;
            }

            ini_set('memory_limit','1600M');
            ini_set('max_execution_time', 3000);

            $i = 0;
            
            foreach($productosConMovimientoEnElMes as $producto)
            {                              
                
                try {

                    $ruta = $this->generarXlsInventarioPermanenteUnidadesFisicas($empresa,$local_selec,$ano_select,$mes_select,$producto['id'],$ruc_empresa,$nombre_empresa,$nombre_local);

                    $files_to_zip[] = $ruta;
                    
                } catch (\Exception $e) {

                    $this->addFlash("danger", "Ocurrió un error inesperado. Vuelva a intentarlo.");
                    return $e;
                    
                }

                $i++;

            }

            $zip = new \ZipArchive();
            $ruta_zip = 'uploads/sunat/'.$empresa.'/'.$local_selec.'/f121/'.$mes_select.'-'.$ano_select.'.zip';
            $zip->open($ruta_zip, \ZipArchive::CREATE);

            foreach($files_to_zip as $z=>$val){

                if($val != ''){
                    $zip->addFile('uploads/sunat/'.$empresa.'/'.$local_selec.'/f121/'.$mes_select.'-'.$ano_select.'/'.$val,$val);
                }                

            }
            
             
            $zip->close();

            if($fileSystem->exists('uploads/sunat/'.$empresa.'/'.$local_selec.'/f121/'.$mes_select.'-'.$ano_select.'.zip'))
            {
                $generado = true;
                $this->addFlash("success", "Se generaron los documentos exitosamente. Local: ".$nombre_local." .Mes: ".$mes_select." .Año: ".$ano_select);                
            }
            else
            {

                $this->addFlash("warning", "No existe información para los datos seleccionados.");                   
            }


        }

        return $this->render('sunat/formato121.html.twig', array(
            'titulo'        => 'FORMATO 12.1: REGISTRO DEL INVENTARIO PERMANENTE EN UNIDADES FÍSICAS - DETALLE DEL INVENTARIO PERMANENTE EN UNIDADES FÍSICAS',
            'formSunatF121' => $formSunatF121->createView(),
            'ano'     => $ano_select,
            'mes'     => $mes_select,
            'generado'=> $generado,
            'empresa' => $empresa,
            'local'   => $local_selec
        ));
    }


    private function generarXlsInventarioPermanenteUnidadesFisicas($empresa,$local,$ano,$mes,$producto=null,$ruc_empresa='',$nombre_empresa='',$nombre_local='')
    {
        $em = $this->getDoctrine()->getManager();
        $ruta = '';

        $sunatf121 = ($producto) ? $this->container->get('AppBundle\Util\Util')->obtenerRegistrosSunatf121($local,$producto,$ano,$mes) : null;

        $saldo_inicial = ($producto) ? $this->container->get('AppBundle\Util\Util')->obtenerSaldoInicialProducto($local,$producto,$ano,$mes) : 0;

        //dump($saldo_inicial);
        //die();        

        $productoXLocalObj  = $em->getRepository('AppBundle:ProductoXLocal')->find($producto);
        $codigo_producto = $productoXLocalObj->getProducto()->getCodigo();
        $descripcion_producto = $productoXLocalObj->getProducto()->getNombre();

        if($sunatf121){

            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()
                ->setCreator("Ferretero")
                ->setLastModifiedBy("Ferretero")
                ->setTitle("Inventario permanente en unidades físicas")
                ->setSubject("Template excel")
                ->setDescription("Detalle del inventario permanente en unidades físicas")
                ->setKeywords("inventario permanente unidades físicas");

            $active_sheet = $objPHPExcel->getActiveSheet();
            $objPHPExcel->setActiveSheetIndex(0);

            //Armamos la estructura del documento e ingresamos data
            $fila = 1;
            $col = 0;


            $col_sig = $col + 13;
            $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
            $active_sheet->setCellValueByColumnAndRow($col, $fila, 'FORMATO 12.1: REGISTRO DEL INVENTARIO PERMANENTE EN UNIDADES FÍSICAS - DETALLE DEL INVENTARIO PERMANENTE EN UNIDADES FÍSICAS');

            $fila++;

            $col_sig = $col + 4;
            $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
            $active_sheet->setCellValueByColumnAndRow($col, $fila, 'PERIODO');
            $nombre_mes = $this->container->get('AppBundle\Twig\AppExtension')->nombreMes($mes);
            $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, $nombre_mes.' '.$ano);


            $fila++;
            $col = 0;

            $col_sig = $col + 4;
            $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
            $active_sheet->mergeCellsByColumnAndRow($col_sig+1, $fila, $col_sig+2, $fila);
            $active_sheet->setCellValueByColumnAndRow($col, $fila, 'RUC');
            $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, ' '.$ruc_empresa);

            $fila++;
            $col = 0;

            $col_sig = $col + 4;
            $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
            $active_sheet->setCellValueByColumnAndRow($col, $fila, 'APELLIDOS Y NOMBRES,DENOMINACIÓN O RAZÓN SOCIAL');
            $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, strtoupper($nombre_empresa));

            $fila++;
            $col = 0;

            $col_sig = $col + 4;
            $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
            $active_sheet->setCellValueByColumnAndRow($col, $fila, 'ESTABLECIMIENTO');
            $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, $nombre_local);

            $fila++;
            $col = 0;

            $col_sig = $col + 4;
            $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
            $active_sheet->setCellValueByColumnAndRow($col, $fila, 'CÓDIGO DE LA EXISTENCIA');
            $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, $codigo_producto);

            $fila++;
            $col = 0;

            $col_sig = $col + 4;
            $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
            $active_sheet->setCellValueByColumnAndRow($col, $fila, 'TIPO');
            $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, '01 MERCADERÍAS');

            $fila++;
            $col = 0;

            $col_sig = $col + 4;
            $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
            $active_sheet->setCellValueByColumnAndRow($col, $fila, 'DESCRIPCIÓN');
            $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, $descripcion_producto);

            $fila++;
            $col = 0;

            $col_sig = $col + 4;
            $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
            $active_sheet->setCellValueByColumnAndRow($col, $fila, 'CÓDIGO DE LA UNIDAD DE MEDIDA');
            $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, '07');

            $fila++;
            $fila++;
            $col = 0;

            $col_sig = $col + 3;
            $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
            $active_sheet->setCellValueByColumnAndRow($col, $fila, 'DOCUMENTO DE TRASLADO,COMPROBANTE DE PAGO,DOCUMENTO INTERNO O SIMILAR');

            $active_sheet->mergeCellsByColumnAndRow($col_sig+1, $fila, $col_sig+1, $fila+1);
            $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, 'TIPO DE OPERACIÓN');

            $active_sheet->mergeCellsByColumnAndRow($col_sig+2, $fila, $col_sig+2, $fila+1);
            $active_sheet->setCellValueByColumnAndRow($col_sig+2, $fila, 'ENTRADAS');

            $active_sheet->mergeCellsByColumnAndRow($col_sig+3, $fila, $col_sig+3, $fila+1);
            $active_sheet->setCellValueByColumnAndRow($col_sig+3, $fila, 'SALIDAS');

            $active_sheet->mergeCellsByColumnAndRow($col_sig+4, $fila, $col_sig+4, $fila+1);
            $active_sheet->setCellValueByColumnAndRow($col_sig+4, $fila, 'SALDO FINAL');

            $fila++;
            $col = 0;

            $active_sheet->setCellValueByColumnAndRow($col, $fila, 'FECHA');
            $active_sheet->setCellValueByColumnAndRow($col+1, $fila, 'TIPO');
            $active_sheet->setCellValueByColumnAndRow($col+2, $fila, 'SERIE');
            $active_sheet->setCellValueByColumnAndRow($col+3, $fila, 'NÚMERO');

            $fila++;
            $col = 0;

            $active_sheet->setCellValueByColumnAndRow($col+3, $fila, 'SALDO INICIAL');
            $active_sheet->setCellValueByColumnAndRow($col+5, $fila, $saldo_inicial);
            $active_sheet->setCellValueByColumnAndRow($col+7, $fila, $saldo_inicial);

            $fila++;
            $col = 0;
            $saldo = 0;
            $jj = 0;
            foreach($sunatf121 as $j=>$f121)
            {

                if(is_string($f121['fecha']))
                {
                    $d = \DateTime::createFromFormat("Y-m-d H:i:s",$f121['fecha']);
                    $d = $d->format("d/m/Y");
                }
                else
                {
                    $d = $f121['fecha']->format("d/m/Y");
                }

                $active_sheet->setCellValueByColumnAndRow($col, $fila, $d);
                $active_sheet->setCellValueByColumnAndRow($col+1, $fila, $f121['tipo']);
                $active_sheet->setCellValueByColumnAndRow($col+2, $fila, $f121['serie']);
                $active_sheet->setCellValueByColumnAndRow($col+3, $fila, $f121['numero']);
                $active_sheet->setCellValueByColumnAndRow($col+4, $fila, $f121['operacion']);
                $active_sheet->setCellValueByColumnAndRow($col+5, $fila, $f121['entrada']);
                $active_sheet->setCellValueByColumnAndRow($col+6, $fila, $f121['salida']);


                if($f121['entrada'] > 0)
                {
                    $saldo = $f121['entrada'] + $saldo;
                }

                if($f121['salida'] > 0)
                {
                    $saldo = $saldo - $f121['salida'];
                }

                if($jj == 0){
                    $saldo = $saldo + $saldo_inicial;
                }

                $active_sheet->setCellValueByColumnAndRow($col+7, $fila, $saldo);

                $fila++;
                $jj++;

            }

            //$objPHPExcel->setActiveSheetIndex(0);
            //$objPHPExcel->getActiveSheet()->SetCellValue('A1', "12");

            $writer = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
            $ruta = 'uploads/sunat/'.$empresa.'/'.$local.'/f121/'.$mes.'-'.$ano.'/'.$producto.'.xls';
            $writer->save($ruta);

            $objPHPExcel->disconnectWorksheets();
            $objPHPExcel->garbageCollect();
            unset($objPHPExcel);
            $writer = null;
            $active_sheet = null;        
        }


        return $producto.'.xls';
    }


    private function generarXlsInventarioPermanenteValorizado($empresa,$local,$ano,$mes,$producto=null,$ruc_empresa='',$nombre_empresa='',$nombre_local='')
    {
        $em = $this->getDoctrine()->getManager();
        $ruta = '';

        $sunatf121 = ($producto) ? $this->container->get('AppBundle\Util\Util')->obtenerRegistrosSunatf131($local,$producto,$ano,$mes) : null;
        $saldo_inicial = ($producto) ? $this->container->get('AppBundle\Util\Util')->obtenerSaldoInicialProducto($local,$producto,$ano,$mes) : 0;

        $productoXLocalObj  = $em->getRepository('AppBundle:ProductoXLocal')->find($producto);
        $codigo_producto = $productoXLocalObj->getProducto()->getCodigo();
        $descripcion_producto = $productoXLocalObj->getProducto()->getNombre();

        if($sunatf121)
        {
            if(count($sunatf121) != 0)
            {


                $objPHPExcel = new \PHPExcel();
                $objPHPExcel->getProperties()
                    ->setCreator("Ferretero")
                    ->setLastModifiedBy("Ferretero")
                    ->setTitle("Inventario permanente valorizado")
                    ->setSubject("Template excel")
                    ->setDescription("Detalle del inventario permanente valorizado")
                    ->setKeywords("inventario permanente valorizado");

                $active_sheet = $objPHPExcel->getActiveSheet();
                $objPHPExcel->setActiveSheetIndex(0);

                //Armamos la estructura del documento e ingresamos data
                $fila = 1;
                $col = 0;


                $col_sig = $col + 13;
                $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
                $active_sheet->setCellValueByColumnAndRow($col, $fila, 'FORMATO 12.1: REGISTRO DEL INVENTARIO PERMANENTE VALORIZADO - DETALLE DEL INVENTARIO PERMANENTE VALORIZADO');

                $fila++;

                $col_sig = $col + 4;
                $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
                $active_sheet->setCellValueByColumnAndRow($col, $fila, 'PERIODO');
                $nombre_mes = $this->container->get('AppBundle\Twig\AppExtension')->nombreMes($mes);
                $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, $nombre_mes.' '.$ano);


                $fila++;
                $col = 0;

                $col_sig = $col + 4;
                $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
                $active_sheet->mergeCellsByColumnAndRow($col_sig+1, $fila, $col_sig+2, $fila);
                $active_sheet->setCellValueByColumnAndRow($col, $fila, 'RUC');
                $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, ' '.$ruc_empresa);

                $fila++;
                $col = 0;

                $col_sig = $col + 4;
                $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
                $active_sheet->setCellValueByColumnAndRow($col, $fila, 'APELLIDOS Y NOMBRES,DENOMINACIÓN O RAZÓN SOCIAL');
                $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, strtoupper($nombre_empresa));

                $fila++;
                $col = 0;

                $col_sig = $col + 4;
                $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
                $active_sheet->setCellValueByColumnAndRow($col, $fila, 'ESTABLECIMIENTO');
                $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, $nombre_local);

                $fila++;
                $col = 0;

                $col_sig = $col + 4;
                $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
                $active_sheet->setCellValueByColumnAndRow($col, $fila, 'CÓDIGO DE LA EXISTENCIA');
                $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, $codigo_producto);

                $fila++;
                $col = 0;

                $col_sig = $col + 4;
                $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
                $active_sheet->setCellValueByColumnAndRow($col, $fila, 'TIPO');
                $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, '01 MERCADERÍAS');

                $fila++;
                $col = 0;

                $col_sig = $col + 4;
                $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
                $active_sheet->setCellValueByColumnAndRow($col, $fila, 'DESCRIPCIÓN');
                $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, $descripcion_producto);

                $fila++;
                $col = 0;

                $col_sig = $col + 4;
                $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
                $active_sheet->setCellValueByColumnAndRow($col, $fila, 'CÓDIGO DE LA UNIDAD DE MEDIDA');
                $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, '07');

                $fila++;
                $col = 0;

                $col_sig = $col + 4;
                $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
                $active_sheet->setCellValueByColumnAndRow($col, $fila, 'MÉTODO DE VALUACIÓN');
                $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, 'Promedio');

                $fila++;
                $fila++;
                $col = 0;

                $col_sig = $col + 3;
                $active_sheet->mergeCellsByColumnAndRow($col, $fila, $col_sig, $fila);
                $active_sheet->setCellValueByColumnAndRow($col, $fila, 'DOCUMENTO DE TRASLADO,COMPROBANTE DE PAGO,DOCUMENTO INTERNO O SIMILAR');

                $active_sheet->mergeCellsByColumnAndRow($col_sig+1, $fila, $col_sig+1, $fila+1);
                $active_sheet->setCellValueByColumnAndRow($col_sig+1, $fila, 'TIPO DE OPERACIÓN');

                $active_sheet->mergeCellsByColumnAndRow($col_sig+2, $fila, $col_sig+4, $fila);
                $active_sheet->setCellValueByColumnAndRow($col_sig+2, $fila, 'ENTRADAS');

                $active_sheet->mergeCellsByColumnAndRow($col_sig+5, $fila, $col_sig+7, $fila);
                $active_sheet->setCellValueByColumnAndRow($col_sig+5, $fila, 'SALIDAS');

                $active_sheet->mergeCellsByColumnAndRow($col_sig+8, $fila, $col_sig+10, $fila);
                $active_sheet->setCellValueByColumnAndRow($col_sig+8, $fila, 'SALDO FINAL');

                $fila++;
                $col = 0;

                $active_sheet->setCellValueByColumnAndRow($col, $fila, 'FECHA');
                $active_sheet->setCellValueByColumnAndRow($col+1, $fila, 'TIPO');
                $active_sheet->setCellValueByColumnAndRow($col+2, $fila, 'SERIE');
                $active_sheet->setCellValueByColumnAndRow($col+3, $fila, 'NÚMERO');

                $active_sheet->setCellValueByColumnAndRow($col+5, $fila, 'CANTIDAD');
                $active_sheet->setCellValueByColumnAndRow($col+6, $fila, 'COSTO UNITARIO');
                $active_sheet->setCellValueByColumnAndRow($col+7, $fila, 'COSTO TOTAL');

                $active_sheet->setCellValueByColumnAndRow($col+8, $fila, 'CANTIDAD');
                $active_sheet->setCellValueByColumnAndRow($col+9, $fila, 'COSTO UNITARIO');
                $active_sheet->setCellValueByColumnAndRow($col+10, $fila, 'COSTO TOTAL');

                $active_sheet->setCellValueByColumnAndRow($col+11, $fila, 'CANTIDAD');
                $active_sheet->setCellValueByColumnAndRow($col+12, $fila, 'COSTO UNITARIO');
                $active_sheet->setCellValueByColumnAndRow($col+13, $fila, 'COSTO TOTAL');

                $fila++;
                $col = 0;

                $active_sheet->setCellValueByColumnAndRow($col+3, $fila, 'SALDO INICIAL');
                $active_sheet->setCellValueByColumnAndRow($col+5, $fila, $saldo_inicial);
                $precio_unitario = $productoXLocalObj->getProducto()->getPrecioUnitario();
                $active_sheet->setCellValueByColumnAndRow($col+6, $fila, ''.$precio_unitario.'');
                $costo_total_inicial = $saldo_inicial * $precio_unitario;
                $active_sheet->setCellValueByColumnAndRow($col+7, $fila, ''.$costo_total_inicial.'');


                $active_sheet->setCellValueByColumnAndRow($col+11, $fila, $saldo_inicial);
                $active_sheet->setCellValueByColumnAndRow($col+12, $fila, ''.$precio_unitario.'');
                $active_sheet->setCellValueByColumnAndRow($col+13, $fila, ''.$costo_total_inicial.'');

                $fila++;
                $col = 0;
                $saldo = 0;
                $jj = 0;
                $cantidadTotal = 0;
                foreach($sunatf121 as $j=>$f121)
                {
                    
                    if(is_string($f121['fecha']))
                    {
                        $d = \DateTime::createFromFormat("Y-m-d H:i:s",$f121['fecha']);
                        $d = $d->format("d/m/Y");
                    }
                    else
                    {
                        $d = $f121['fecha']->format("d/m/Y");
                    }
                    

                    $active_sheet->setCellValueByColumnAndRow($col, $fila, $d);
                    $active_sheet->setCellValueByColumnAndRow($col+1, $fila, $f121['tipo']);
                    $active_sheet->setCellValueByColumnAndRow($col+2, $fila, $f121['serie']);
                    $active_sheet->setCellValueByColumnAndRow($col+3, $fila, $f121['numero']);
                    $active_sheet->setCellValueByColumnAndRow($col+4, $fila, $f121['operacion']);

                    if($f121['entrada']){

                        $active_sheet->setCellValueByColumnAndRow($col+5, $fila, $f121['cantidad']);
                        $active_sheet->setCellValueByColumnAndRow($col+6, $fila, $f121['costoUnitario']);
                        $costo_total = $f121['costoUnitario'] * $f121['cantidad'];
                        $active_sheet->setCellValueByColumnAndRow($col+7, $fila, $costo_total);

                        $costoUnitarioTotal = $f121['costoUnitario'];
                        $cantidadTotal = $f121['cantidad'] + $cantidadTotal;

                    }

                    if($f121['salida']){

                        $active_sheet->setCellValueByColumnAndRow($col+8, $fila, $f121['cantidad']);
                        $active_sheet->setCellValueByColumnAndRow($col+9, $fila, $f121['costoUnitario']);
                        $costo_total = $f121['costoUnitario'] * $f121['cantidad'];
                        $active_sheet->setCellValueByColumnAndRow($col+10, $fila, $costo_total);

                        $costoUnitarioTotal = $f121['costoUnitario'];
                        $cantidadTotal = $cantidadTotal - $f121['cantidad'];                    
                    
                    }

                    if($jj == 0)
                    {
                        $cantidadTotal = $cantidadTotal + $saldo_inicial;
                        $active_sheet->setCellValueByColumnAndRow($col+11, $fila,$cantidadTotal);
                    }
                    else
                    {
                        $active_sheet->setCellValueByColumnAndRow($col+11, $fila, $cantidadTotal);
                    }

                    $active_sheet->setCellValueByColumnAndRow($col+12, $fila, $costoUnitarioTotal);
                    $costo_saldo_final = $cantidadTotal * $costoUnitarioTotal;
                    $active_sheet->setCellValueByColumnAndRow($col+13, $fila, $costo_saldo_final);



                    $fila++;
                    $jj++;

                }

                //$objPHPExcel->setActiveSheetIndex(0);
                //$objPHPExcel->getActiveSheet()->SetCellValue('A1', "12");

                $writer = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
                $ruta = 'uploads/sunat/'.$empresa.'/'.$local.'/f131/'.$mes.'-'.$ano.'/'.$producto.'.xls';
                $writer->save($ruta);

                $objPHPExcel->disconnectWorksheets();
                $objPHPExcel->garbageCollect();
                unset($objPHPExcel);
                $writer = null;
                $active_sheet = null;              

            }



        }


        return $producto.'.xls';
    }




}
