<?php

namespace AppBundle\Util;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Security\Core\Security;

use AppBundle\Entity\LogModificacion;
use AppBundle\Entity\SunatF121;
use AppBundle\Entity\SunatF131;

use AppBundle\Datatable\SSP;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\CajaAperturaDetalle;

class Util{

    protected $router;
    protected $security;
    protected $session;
    protected $em;
    protected $conn;
    protected $ssp;
    protected $container;
    
    public function __construct(RouterInterface $router, SessionInterface $session,EntityManagerInterface $em,Connection $connection,Security $security,SSP $ssp,ContainerInterface $container)
    {
        $this->router   = $router;
        $this->session  = $session;
        $this->em       = $em;
        $this->conn     = $connection;
        $this->security = $security;
        $this->ssp      = $ssp;
        $this->container = $container;     
    }



    public function generateRandomString($length = 8) { 
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length); 
    } 

    public function obtenerSubMenu($padre,$rol_id)
    {
        

        $sql = "SELECT * FROM menu_x_rol mxr INNER JOIN menu m
                ON (mxr.menu_id = m.id) WHERE
                mxr.estado = 1 AND mxr.rol_id = ? AND m.padre = ? ORDER BY mxr.orden";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $rol_id);
        $stmt->bindValue(2, $padre);
        $stmt->execute();
        $menus = $stmt->fetchAll();


        return $menus;
    }

    /*
    *Crear usuario
    */
    public function crearUsuario($userManager,$user,$entity,$rol_nombre='',$username='',$password='')
    {
        $random = '1234567u';//rand(100000,999999);

        $tokenGenerator = $GLOBALS['kernel']->getContainer()->get('fos_user.util.token_generator');
        
        $user->setUsername($username);
        $user->setUsernameCanonical($username);
        $user->setEmail($entity->getEmail());
        $user->setEmailCanonical($entity->getEmail());
        $user->setPlainPassword($password);
        $user->setConfirmationToken($tokenGenerator->generateToken());       

        $rolNombreArray = array($rol_nombre);
        
        $user->setRoles($rolNombreArray);
        $user->setEnabled(true);      


        try{
            $userManager->updateUser($user);
            return $user;

        }catch(\Exception $e) {

            return null;
        }            
            
       
    } 

    /*
    *Actualizar usuario
    */
    public function actualizarUsuario($userManager,$user,$entity,$rol_nombre='',$username='',$password='')
    {
        $tokenGenerator = $GLOBALS['kernel']->getContainer()->get('fos_user.util.token_generator');

        $user->setUsername($username);
        $user->setUsernameCanonical($username);
        $user->setEmail($entity->getEmail());
        $user->setEmailCanonical($entity->getEmail());
        $user->setPlainPassword($password);
        $user->setConfirmationToken($tokenGenerator->generateToken());
        
        $rolNombreArray = array($rol_nombre);

        $user->setRoles($rolNombreArray);

        try{
            $userManager->updateUser($user);
            return $user;

        }catch(\Exception $e) {

            return null;
        }            
            
       
    } 

    /*
    *Deshabilitar usuario
    */
    public function deshabilitarUsuario($userManager,$user)
    {
        $tokenGenerator = $GLOBALS['kernel']->getContainer()->get('fos_user.util.token_generator');

        $user->setEnabled(false);
        $user->setConfirmationToken($tokenGenerator->generateToken());

        
        try{
            $userManager->updateUser($user);
            return true;

        }catch(\Exception $e) {
            return false;
        }            
            
       
    } 

   

    public function disminuirAlmacen($producto_id,$cantidad=0)
    {

        $producto = $this->em->getRepository('AppBundle:ProductoXLocal')->find($producto_id);


        $ratio = 1;
        if($producto->getProducto()->getUnidadVenta())
        {
            $unidadVenta    = $producto->getProducto()->getUnidadVenta();
            $ratio          = (float)$unidadVenta->getRatio();

        }


        $stock_actual = $producto->getStock();

        $stock_nuevo = $stock_actual - ($cantidad * $ratio);

        $producto->setStock($stock_nuevo);
        $this->em->persist($producto);


        try{
            $this->em->flush();
            return true;

        }catch(\Exception $e) {

            return $e;
        }           

    }  

    public function aumentarAlmacen($producto_id,$cantidad=0)
    {
        $producto = $this->em->getRepository('AppBundle:ProductoXLocal')->find($producto_id);

        $ratio = 1;
        if($producto->getProducto()->getUnidadCompra())
        {
            $unidadCompra      = $producto->getProducto()->getUnidadCompra();

            $ratio = (float)$unidadCompra->getRatio();

        }


        $stock_actual = $producto->getStock();

        $stock_nuevo = $stock_actual + ($cantidad * $ratio);

        $producto->setStock($stock_nuevo);
        $this->em->persist($producto);

        try{
            $this->em->flush();
            return true;

        }catch(\Exception $e) {
            return false;
        }   
    }  

    public function aumentarTransferenciaAlmacen($producto_id,$cantidad=0)
    {
        $producto = $this->em->getRepository('AppBundle:ProductoXLocal')->find($producto_id);

        $ratio = 1;
        if($producto->getProducto()->getUnidadVenta())
        {
            $unidadVenta    = $producto->getProducto()->getUnidadVenta();
            $ratio          = (float)$unidadVenta->getRatio();

        }


        $stock_actual = $producto->getStock();

        $stock_nuevo = $stock_actual + ($cantidad * $ratio);

        $producto->setStock($stock_nuevo);
        $this->em->persist($producto);

        try{
            $this->em->flush();
            return true;

        }catch(\Exception $e) {
            return false;
        }   
    }  


    public function registrarLog($productoXLocal,$valor_nuevo,$valor,$tipo,$factura=null)
    {
        $usuario = $this->security->getUser();
        $fecha = new \DateTime();

        $logModificaciom = new LogModificacion();

        $logModificaciom->setValor($valor);
        $logModificaciom->setValorNuevo($valor_nuevo);
        $logModificaciom->setProductoXLocal($productoXLocal);
        $modificacionTipo = $this->em->getRepository('AppBundle:ModificacionTipo')->findOneBy(array('nombre'=>$tipo));
        $logModificaciom->setModificacionTipo($modificacionTipo);
        $logModificaciom->setUsuario($usuario);
        $logModificaciom->setFecha($fecha);
        $logModificaciom->setFacturaVenta($factura);
        $this->em->persist($logModificaciom);


        try{
            $this->em->flush();
            return true;

        }catch(\Exception $e) {
            return false;
        }   
    }  

    public function registrarCajaAperturaDetalle($caja_apertura_id,$cantidad,$denominacion='',$identificador=null)
    {
        $usuario = $this->security->getUser();
        $fecha = new \DateTime();

        $cajaAperturaDetalle = new CajaAperturaDetalle();

        $cajaApertura = $this->em->getRepository('AppBundle:CajaApertura')->find($caja_apertura_id);        
        $cajaAperturaDetalle->setCajaApertura($cajaApertura);
        $cajaAperturaDetalle->setUsuario($usuario);
        $cajaAperturaDetalle->setCantidad($cantidad);
        $cajaAperturaDetalle->setDenominacion($denominacion);
        $cajaAperturaDetalle->setIdentificador($identificador);

        $this->em->persist($cajaAperturaDetalle);


        try{
            //$this->em->flush();
            return true;

        }catch(\Exception $e) {
            return false;
        }

    }  

    public function verificaEstadoCaja($caja=null){

        if(!$caja){
            return false;
        }

        $dql = "SELECT ca FROM AppBundle:CajaApertura ca ";
        $dql .= " JOIN ca.caja c";
        $dql .= " WHERE  c.id =:caja_id  AND ca.estado = 1 ";

        $query = $this->em->createQuery($dql);

        $query->setParameter('caja_id',$caja);
 
        $cajaApertura = $query->getOneOrNullResult();

        if(!$cajaApertura){
            return false;
        }

        return true;
    }

    
    public function obtenerVentaDiariaXTrabajador($empresa)
    {
        

        $sql = "SELECT em.id AS id,
                (CASE
                    WHEN vfp.moneda_id = 1 THEN SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                    WHEN vfp.moneda_id = 2 THEN SUM(vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1))
                    ELSE SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                END) AS subtotal   
                FROM factura_venta fv
                INNER JOIN venta v ON fv.venta_id = v.id
                INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                INNER JOIN empleado em ON v.empleado_id = em.id
                INNER JOIN empresa_local el ON fv.empresa_local_id = el.id
                INNER JOIN empresa e ON el.empresa_id = e.id
                WHERE v.estado = 1 AND e.id = ? AND fv.tipo_id = 2  AND MONTH(fv.fecha) = MONTH(CURRENT_DATE()) AND YEAR(fv.fecha) = YEAR(CURRENT_DATE()) AND DAY(fv.fecha) = DAY(CURRENT_DATE())
                GROUP BY em.id ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        $stmt->execute();
        $venta_diaria_x_trabajador = $stmt->fetchAll();


        return $venta_diaria_x_trabajador;
    }

    public function obtenerVentaXTrabajador($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT em.id AS id,
                (CASE
                    WHEN vfp.moneda_id = 1 THEN SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                    WHEN vfp.moneda_id = 2 THEN SUM(vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1))
                    ELSE SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                END) AS subtotal   
                FROM factura_venta fv
                INNER JOIN venta v ON fv.venta_id = v.id
                INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                INNER JOIN empleado em ON v.empleado_id = em.id
                INNER JOIN empresa_local el ON fv.empresa_local_id = el.id
                INNER JOIN empresa e ON el.empresa_id = e.id
                WHERE v.estado = 1 AND fv.tipo_id = 2 AND e.id = ? ";

        if($fechaini == '' || $fechafin == ''){
            $sql .=" AND MONTH(fv.fecha) = MONTH(CURRENT_DATE()) AND YEAR(fv.fecha) = YEAR(CURRENT_DATE()) ";
        }elseif($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        
        $sql .=" GROUP BY em.id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $venta_x_trabajador = $stmt->fetchAll();


        return $venta_x_trabajador;
    }

    public function obtenerVentaDiariaXLocal($empresa)
    {        

        $sql = "SELECT el.id AS id,
                (CASE
                    WHEN vfp.moneda_id = 1 THEN SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                    WHEN vfp.moneda_id = 2 THEN SUM(vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1))
                    ELSE SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                END) AS subtotal   
                FROM factura_venta fv
                INNER JOIN venta v ON fv.venta_id = v.id
                INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                INNER JOIN empresa_local el ON fv.empresa_local_id = el.id
                INNER JOIN empresa e ON el.empresa_id = e.id
                WHERE v.estado = 1 AND fv.tipo_id = 2 AND e.id = ? AND MONTH(fv.fecha) = MONTH(CURRENT_DATE()) AND YEAR(fv.fecha) = YEAR(CURRENT_DATE()) AND DAY(fv.fecha) = DAY(CURRENT_DATE())
                GROUP BY el.id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        $stmt->execute();
        $venta_diaria_x_local = $stmt->fetchAll();


        return $venta_diaria_x_local;
    }

    public function obtenerVentaXLocal($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT el.id AS id,
                (CASE
                    WHEN vfp.moneda_id = 1 THEN SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                    WHEN vfp.moneda_id = 2 THEN SUM(vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1))
                    ELSE SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                END) AS subtotal           
                #SUM(vfp.cantidad + IFNULL(vfp.igv,0)) AS subtotal 
                FROM factura_venta fv
                INNER JOIN venta v ON fv.venta_id = v.id
                #INNER JOIN empleado em ON v.empleado_id = em.id
                INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                INNER JOIN empresa_local el ON fv.empresa_local_id = el.id
                INNER JOIN empresa e ON el.empresa_id = e.id
                WHERE v.estado = 1 AND e.id = ?  AND fv.tipo_id = 2 ";

        if($fechaini == '' || $fechafin == ''){
            $sql .=" AND MONTH(fv.fecha) = MONTH(CURRENT_DATE()) AND YEAR(fv.fecha) = YEAR(CURRENT_DATE()) ";
        }elseif($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        
        $sql .=" GROUP BY el.id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $venta_x_local = $stmt->fetchAll();


        return $venta_x_local;
    }

    public function obtenerProductosMasVendidos($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT pxl.id ,p.nombre,p.codigo,el.nombre as local,SUM(dv.cantidad) AS cantidad FROM detalle_venta dv
                    INNER JOIN venta v ON dv.venta_id = v.id
                    INNER JOIN factura_venta fv ON fv.venta_id = v.id
                    INNER JOIN producto_x_local pxl ON dv.producto_x_local_id = pxl.id
                    INNER JOIN producto p ON p.id = pxl.producto_id
                    INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE el.estado = 1 AND v.estado = 1 AND fv.tipo_id = 2  AND e.id = ? 
                    ";

        if($fechaini == '' || $fechafin == ''){
            $sql .=" AND MONTH(v.fecha) = MONTH(CURRENT_DATE()) AND YEAR(v.fecha) = YEAR(CURRENT_DATE()) ";
        }elseif($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(v.fecha AS DATE) BETWEEN ? AND ? ";
        }
        
        $sql .=" GROUP BY pxl.id ORDER BY cantidad DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $productos = $stmt->fetchAll();


        return $productos;
    }

    public function productosMasVendidosXLocal($local,$param='',$cat='',$tipoCambio=null)
    {       

        if($tipoCambio == null)
        {
            $tipoCambio = 1;
        }

        $sql  = " SELECT pxl.id ,p.nombre,p.codigo, ";
        $sql .= " (CASE
                    WHEN p.moneda_id = 1 THEN p.precio_unitario
                    WHEN p.moneda_id = 2 THEN p.precio_unitario * ".$tipoCambio."
                    ELSE p.precio_unitario
                    END) AS precio, "; 
        $sql .= " (CASE
                    WHEN p.moneda_id = 1 THEN p.precio_cantidad
                    WHEN p.moneda_id = 2 THEN p.precio_cantidad * ".$tipoCambio."
                    ELSE p.precio_cantidad
                    END) AS precio_x_mayor, ";
        $sql .= " p.imagen,pxl.stock,pu.abreviatura AS unidad,pm.nombre AS marca ";

        $sql .= "   FROM producto_x_local pxl 
                    INNER JOIN producto p ON p.id = pxl.producto_id
                    INNER JOIN producto_unidad pu ON pu.id = p.unidad_venta_id
                    INNER JOIN producto_categoria pc ON pc.id = p.categoria_id
                    INNER JOIN producto_marca pm ON pm.id = p.marca_id
                    INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE el.estado = 1  AND pxl.estado = 1 AND el.id = ?
                    ";


        if($param != ''){

            $sql  = " SELECT pxl.id ,p.nombre,p.codigo, ";
            $sql .= " (CASE
                        WHEN p.moneda_id = 1 THEN p.precio_unitario
                        WHEN p.moneda_id = 2 THEN p.precio_unitario * ".$tipoCambio."
                        ELSE p.precio_unitario
                        END) AS precio, "; 
            $sql .= " (CASE
                        WHEN p.moneda_id = 1 THEN p.precio_cantidad
                        WHEN p.moneda_id = 2 THEN p.precio_cantidad * ".$tipoCambio."
                        ELSE p.precio_cantidad
                        END) AS precio_x_mayor, ";
            $sql .= " p.imagen,pxl.stock,pu.abreviatura AS unidad,pm.nombre AS marca ";

            $sql .= "   FROM producto_x_local pxl 
                        INNER JOIN producto p ON p.id = pxl.producto_id
                        INNER JOIN producto_unidad pu ON pu.id = p.unidad_venta_id
                        INNER JOIN producto_categoria pc ON pc.id = p.categoria_id
                        INNER JOIN producto_marca pm ON pm.id = p.marca_id
                        INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
                        INNER JOIN empresa e ON e.id = el.empresa_id
                        WHERE el.estado = 1  AND pxl.estado = 1 AND el.id = ?
                        ";


            $partes = explode(" ", $param);

            foreach($partes as $i=>$parte){
                $sql .= " AND (pc.nombre LIKE '%".$parte."%' OR pm.nombre LIKE '%".$parte."%' OR p.nombre LIKE '%".$parte."%' OR p.codigo LIKE '%".$parte."%' OR p.codigo_barra LIKE '%".$parte."%' OR p.descripcion LIKE '%".$parte."%') ";
            }
        }

        if($cat != ''){
            $sql .= " AND pc.id = ? ";
        }

        $sql .=" GROUP BY pxl.id ";

        if($param != ''){

            $sql .=" ORDER BY pm.nombre DESC,p.nombre ASC";

        }
        else
        {
            $sql .=" ORDER BY pm.nombre DESC,p.nombre ASC LIMIT 20 ";
        }
        

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $local);
        if($cat != ''){
            $stmt->bindValue(2, $cat);
        }

        $stmt->execute();
        $productos = $stmt->fetchAll();


        return $productos;
    }

    public function productosPrecioMayorista($local,$param='',$cat='',$tipoCambio=null)
    {       

        if($tipoCambio == null)
        {
            $tipoCambio = 1;
        }

        $sql  = " SELECT pxl.id ,p.nombre,p.codigo, ";
        $sql .= " (CASE
                    WHEN p.moneda_id = 1 THEN p.precio_cantidad
                    WHEN p.moneda_id = 2 THEN p.precio_cantidad * ".$tipoCambio."
                    ELSE p.precio_cantidad
                    END) AS precio, "; 
        $sql .= " (CASE
                    WHEN p.moneda_id = 1 THEN p.precio_unitario
                    WHEN p.moneda_id = 2 THEN p.precio_unitario * ".$tipoCambio."
                    ELSE p.precio_unitario
                    END) AS precio_x_mayor, ";
        $sql .= " p.imagen,pxl.stock,pu.abreviatura AS unidad,pm.nombre AS marca ";

        $sql .= "   FROM producto_x_local pxl 
                    INNER JOIN producto p ON p.id = pxl.producto_id
                    INNER JOIN producto_unidad pu ON pu.id = p.unidad_venta_id
                    INNER JOIN producto_categoria pc ON pc.id = p.categoria_id
                    INNER JOIN producto_marca pm ON pm.id = p.marca_id
                    INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE el.estado = 1  AND pxl.estado = 1 AND el.id = ?
                    ";


        if($param != ''){

            $sql  = " SELECT pxl.id ,p.nombre,p.codigo, ";
            $sql .= " (CASE
                        WHEN p.moneda_id = 1 THEN p.precio_cantidad
                        WHEN p.moneda_id = 2 THEN p.precio_cantidad * ".$tipoCambio."
                        ELSE p.precio_cantidad
                        END) AS precio, "; 
            $sql .= " (CASE
                        WHEN p.moneda_id = 1 THEN p.precio_unitario
                        WHEN p.moneda_id = 2 THEN p.precio_unitario * ".$tipoCambio."
                        ELSE p.precio_unitario
                        END) AS precio_x_mayor, ";
            $sql .= " p.imagen,pxl.stock,pu.abreviatura AS unidad,pm.nombre AS marca ";

            $sql .= "   FROM producto_x_local pxl 
                        INNER JOIN producto p ON p.id = pxl.producto_id
                        INNER JOIN producto_unidad pu ON pu.id = p.unidad_venta_id
                        INNER JOIN producto_categoria pc ON pc.id = p.categoria_id
                        INNER JOIN producto_marca pm ON pm.id = p.marca_id
                        INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
                        INNER JOIN empresa e ON e.id = el.empresa_id
                        WHERE el.estado = 1  AND pxl.estado = 1 AND el.id = ?
                        ";


            $partes = explode(" ", $param);

            foreach($partes as $i=>$parte){
                $sql .= " AND (pc.nombre LIKE '%".$parte."%' OR pm.nombre LIKE '%".$parte."%' OR p.nombre LIKE '%".$parte."%' OR p.codigo LIKE '%".$parte."%' OR p.codigo_barra LIKE '%".$parte."%' OR p.descripcion LIKE '%".$parte."%') ";
            }
        }

        if($cat != ''){
            $sql .= " AND pc.id = ? ";
        }

        $sql .=" GROUP BY pxl.id ";

        if($param != ''){

            $sql .=" ORDER BY pm.nombre DESC,p.nombre ASC";

        }
        else
        {
            $sql .=" ORDER BY pm.nombre DESC,p.nombre ASC LIMIT 20 ";
        }
        

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $local);
        if($cat != ''){
            $stmt->bindValue(2, $cat);
        }

        $stmt->execute();
        $productos = $stmt->fetchAll();


        return $productos;
    }

    public function productosPrecioEnDolaresMayorista($local,$param='',$cat='',$tipoCambio=null)
    {       

        if($tipoCambio == null)
        {
            $tipoCambio = 1;
        }

        $sql  = " SELECT pxl.id ,p.nombre,p.codigo, ";
        $sql .= " (CASE
                    WHEN p.moneda_id = 1 THEN p.precio_cantidad /".$tipoCambio."
                    WHEN p.moneda_id = 2 THEN p.precio_cantidad 
                    ELSE p.precio_cantidad /".$tipoCambio."
                    END) AS precio, "; 
        $sql .= " (CASE
                    WHEN p.moneda_id = 1 THEN p.precio_unitario /".$tipoCambio."
                    WHEN p.moneda_id = 2 THEN p.precio_unitario
                    ELSE p.precio_unitario /".$tipoCambio."
                    END) AS precio_x_mayor, ";
        $sql .= " p.imagen,pxl.stock,pu.abreviatura AS unidad,pm.nombre AS marca ";

        $sql .= "   FROM producto_x_local pxl 
                    INNER JOIN producto p ON p.id = pxl.producto_id
                    INNER JOIN producto_unidad pu ON pu.id = p.unidad_venta_id
                    INNER JOIN producto_categoria pc ON pc.id = p.categoria_id
                    INNER JOIN producto_marca pm ON pm.id = p.marca_id
                    INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE el.estado = 1  AND pxl.estado = 1 AND el.id = ?
                    ";



        if($param != ''){

            $sql  = " SELECT pxl.id ,p.nombre,p.codigo, ";
            $sql .= " (CASE
                        WHEN p.moneda_id = 1 THEN p.precio_cantidad /".$tipoCambio."
                        WHEN p.moneda_id = 2 THEN p.precio_cantidad 
                        ELSE p.precio_cantidad /".$tipoCambio."
                        END) AS precio, "; 
            $sql .= " (CASE
                        WHEN p.moneda_id = 1 THEN p.precio_unitario /".$tipoCambio."
                        WHEN p.moneda_id = 2 THEN p.precio_unitario
                        ELSE p.precio_unitario /".$tipoCambio."
                        END) AS precio_x_mayor, ";
            $sql .= " p.imagen,pxl.stock,pu.abreviatura AS unidad,pm.nombre AS marca ";

            $sql .= "   FROM producto_x_local pxl 
                        INNER JOIN producto p ON p.id = pxl.producto_id
                        INNER JOIN producto_unidad pu ON pu.id = p.unidad_venta_id
                        INNER JOIN producto_categoria pc ON pc.id = p.categoria_id
                        INNER JOIN producto_marca pm ON pm.id = p.marca_id
                        INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
                        INNER JOIN empresa e ON e.id = el.empresa_id
                        WHERE el.estado = 1  AND pxl.estado = 1 AND el.id = ?
                        ";

            $partes = explode(" ", $param);

            foreach($partes as $i=>$parte){
                $sql .= " AND (pc.nombre LIKE '%".$parte."%' OR pm.nombre LIKE '%".$parte."%' OR p.nombre LIKE '%".$parte."%' OR p.codigo LIKE '%".$parte."%' OR p.codigo_barra LIKE '%".$parte."%' OR p.descripcion LIKE '%".$parte."%') ";
            }
        }

        if($cat != ''){
            $sql .= " AND pc.id = ? ";
        }

        $sql .=" GROUP BY pxl.id ";

        if($param != ''){

            $sql .=" ORDER BY pm.nombre DESC,p.nombre ASC";

        }
        else
        {
            $sql .=" ORDER BY pm.nombre DESC,p.nombre ASC LIMIT 20 ";
        }
        

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $local);
        if($cat != ''){
            $stmt->bindValue(2, $cat);
        }

        $stmt->execute();
        $productos = $stmt->fetchAll();


        return $productos;
    }

    public function productosPrecioEnDolares($local,$param='',$cat='',$tipoCambio=null)
    {       

        if($tipoCambio == null)
        {
            $tipoCambio = 1;
        }


        $sql  = " SELECT pxl.id ,p.nombre,p.codigo, ";
        $sql .= " (CASE
                    WHEN p.moneda_id = 1 THEN p.precio_unitario /".$tipoCambio." 
                    WHEN p.moneda_id = 2 THEN p.precio_unitario
                    ELSE p.precio_unitario /".$tipoCambio."
                    END) AS precio, "; 
        $sql .= " (CASE
                    WHEN p.moneda_id = 1 THEN p.precio_cantidad /".$tipoCambio." 
                    WHEN p.moneda_id = 2 THEN p.precio_cantidad 
                    ELSE p.precio_cantidad /".$tipoCambio."
                    END) AS precio_x_mayor, ";
        $sql .= " p.imagen,pxl.stock,pu.abreviatura AS unidad,pm.nombre AS marca ";

        $sql .= "   FROM producto_x_local pxl 
                    INNER JOIN producto p ON p.id = pxl.producto_id
                    INNER JOIN producto_unidad pu ON pu.id = p.unidad_venta_id
                    INNER JOIN producto_categoria pc ON pc.id = p.categoria_id
                    INNER JOIN producto_marca pm ON pm.id = p.marca_id
                    INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE el.estado = 1  AND pxl.estado = 1 AND el.id = ?
                    ";



        if($param != ''){

            $sql  = " SELECT pxl.id ,p.nombre,p.codigo, ";
            $sql .= " (CASE
                        WHEN p.moneda_id = 1 THEN p.precio_unitario /".$tipoCambio." 
                        WHEN p.moneda_id = 2 THEN p.precio_unitario
                        ELSE p.precio_unitario /".$tipoCambio."
                        END) AS precio, "; 
            $sql .= " (CASE
                        WHEN p.moneda_id = 1 THEN p.precio_cantidad /".$tipoCambio." 
                        WHEN p.moneda_id = 2 THEN p.precio_cantidad 
                        ELSE p.precio_cantidad /".$tipoCambio."
                        END) AS precio_x_mayor, ";
            $sql .= " p.imagen,pxl.stock,pu.abreviatura AS unidad,pm.nombre AS marca ";

            $sql .= "   FROM producto_x_local pxl 
                        INNER JOIN producto p ON p.id = pxl.producto_id
                        INNER JOIN producto_unidad pu ON pu.id = p.unidad_venta_id
                        INNER JOIN producto_categoria pc ON pc.id = p.categoria_id
                        INNER JOIN producto_marca pm ON pm.id = p.marca_id
                        INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
                        INNER JOIN empresa e ON e.id = el.empresa_id
                        WHERE el.estado = 1  AND pxl.estado = 1 AND el.id = ?
                        ";

            $partes = explode(" ", $param);

            foreach($partes as $i=>$parte){
                $sql .= " AND (pc.nombre LIKE '%".$parte."%' OR pm.nombre LIKE '%".$parte."%' OR p.nombre LIKE '%".$parte."%' OR p.codigo LIKE '%".$parte."%' OR p.codigo_barra LIKE '%".$parte."%' OR p.descripcion LIKE '%".$parte."%') ";
            }
        }

        if($cat != ''){
            $sql .= " AND pc.id = ? ";
        }

        $sql .=" GROUP BY pxl.id ";

        if($param != ''){

            $sql .=" ORDER BY pm.nombre DESC,p.nombre ASC";

        }
        else
        {
            $sql .=" ORDER BY pm.nombre DESC,p.nombre ASC LIMIT 20 ";
        }
        

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $local);
        if($cat != ''){
            $stmt->bindValue(2, $cat);
        }

        $stmt->execute();
        $productos = $stmt->fetchAll();


        return $productos;
    }



    public function ventasDiarias($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT fv.id,fv.ticket AS codigo,c.razon_social,fv.fecha,fp.nombre AS tipo,
                    CONCAT(em.apellido_paterno,' ',em.apellido_materno,' ',em.nombres) AS usuario,
                    (CASE
                        WHEN vfp.moneda_id = 1 THEN SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                        WHEN vfp.moneda_id = 2 THEN SUM(vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1))
                        ELSE SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                    END) AS monto_total,
                    el.nombre AS local, vfp.numero_dias AS dias,fv.numero_guiaremision AS 'guia_remision'   
                    FROM factura_venta fv
                    INNER JOIN venta v ON fv.venta_id = v.id
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN forma_pago fp ON vfp.forma_pago_id = fp.id
                    INNER JOIN cliente c ON fv.cliente_id = c.id
                    INNER JOIN empleado em ON em.id = v.empleado_id
                    INNER JOIN empresa_local el ON el.id = fv.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE el.estado = 1 AND v.estado = 1 AND fv.tipo_id = 2 AND e.id = ?
                    ";
                    
        if($fechaini == '' || $fechafin == ''){
            $sql .=" AND MONTH(fv.fecha) = MONTH(CURRENT_DATE()) AND YEAR(fv.fecha) = YEAR(CURRENT_DATE()) AND DAY(fv.fecha) = DAY(CURRENT_DATE())";
        }elseif($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        $sql .=" GROUP BY fv.id";
        $sql .=" ORDER BY fv.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $ventas = $stmt->fetchAll();


        return $ventas;
    }

    public function ventasAnuladas($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT fv.id,c.codigo,c.razon_social,fv.fecha,fp.nombre AS tipo,CONCAT(em.apellido_paterno,' ',em.apellido_materno,' ',em.nombres) AS usuario,
                    SUM(dv.subtotal) AS subtotal,u.username AS usuario_anulacion,el.nombre AS local,v.motivo_anulacion AS motivo,fv.ticket 
                    FROM factura_venta fv
                    INNER JOIN venta v ON fv.venta_id = v.id
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN forma_pago fp ON vfp.forma_pago_id = fp.id
                    INNER JOIN cliente c ON fv.cliente_id = c.id
                    INNER JOIN detalle_venta dv ON dv.venta_id = v.id                    
                    INNER JOIN empleado em ON em.id = v.empleado_id
                    INNER JOIN producto_x_local pxl ON dv.producto_x_local_id = pxl.id
                    INNER JOIN producto p ON p.id = pxl.producto_id
                    INNER JOIN empresa_local el ON el.id = em.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    INNER JOIN usuario u ON v.usuario_anulacion_id = u.id
                    WHERE v.estado = 0 AND el.estado = 1 AND fv.tipo_id = 2 AND  e.id = ?
                    ";

        if($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        
        $sql .=" GROUP BY fv.id ORDER BY fv.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $ventas = $stmt->fetchAll();


        return $ventas;
    }

    public function ventasContado($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT fv.id,fv.ticket AS codigo,c.razon_social,fv.fecha,fp.nombre AS tipo,
                    CONCAT(em.apellido_paterno,' ',em.apellido_materno,' ',em.nombres) AS usuario,
                    (CASE
                        WHEN vfp.moneda_id = 1 THEN SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                        WHEN vfp.moneda_id = 2 THEN SUM(vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1))
                        ELSE SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                    END) AS monto_total,                      
                    #SUM(vfp.cantidad + IFNULL(vfp.igv,0)) AS monto_total,
                    el.nombre AS local, vfp.numero_dias AS dias   
                    FROM factura_venta fv
                    INNER JOIN venta v ON fv.venta_id = v.id
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN forma_pago fp ON vfp.forma_pago_id = fp.id
                    INNER JOIN cliente c ON fv.cliente_id = c.id
                    INNER JOIN empleado em ON em.id = v.empleado_id
                    INNER JOIN empresa_local el ON el.id = fv.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE el.estado = 1 AND v.estado = 1 AND fp.codigo IN (1,6,7,8) AND fv.tipo_id = 2 AND e.id = ?
                    ";

        if($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        
        $sql .=" GROUP BY fv.id ORDER BY fv.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $ventas = $stmt->fetchAll();


        return $ventas;
    }


    public function ventasCredito($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT fv.id,fv.ticket AS codigo,c.razon_social,fv.fecha,fp.nombre AS tipo,
                    CONCAT(em.apellido_paterno,' ',em.apellido_materno,' ',em.nombres) AS usuario,
                    (CASE
                        WHEN vfp.moneda_id = 1 THEN SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                        WHEN vfp.moneda_id = 2 THEN SUM(vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1))
                        ELSE SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                    END) AS monto_total,                        
                    #SUM(vfp.cantidad + IFNULL(vfp.igv,0)) AS monto_total,
                    el.nombre AS local, vfp.numero_dias AS dias,vfp.condicion,vfp.moneda_id AS moneda   
                    FROM factura_venta fv
                    INNER JOIN venta v ON fv.venta_id = v.id
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN forma_pago fp ON vfp.forma_pago_id = fp.id
                    INNER JOIN cliente c ON fv.cliente_id = c.id
                    INNER JOIN empleado em ON em.id = v.empleado_id
                    INNER JOIN empresa_local el ON el.id = fv.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE el.estado = 1 AND v.estado = 1 AND fp.codigo = 2 AND fv.tipo_id = 2 AND e.id = ?
                    ";

        if($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        
        $sql .=" GROUP BY fv.id ORDER BY fv.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $ventas = $stmt->fetchAll();


        return $ventas;
    }

    public function ventasCreditoEnDolares($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT COUNT(fv.id) AS total
                    FROM factura_venta fv
                    INNER JOIN venta v ON fv.venta_id = v.id
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN forma_pago fp ON vfp.forma_pago_id = fp.id
                    INNER JOIN cliente c ON fv.cliente_id = c.id
                    INNER JOIN empleado em ON em.id = v.empleado_id
                    INNER JOIN empresa_local el ON el.id = fv.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE el.estado = 1 AND v.estado = 1 AND fp.codigo = 2 AND fv.tipo_id = 2 AND vfp.moneda_id = 2 AND e.id = ?
                    ";

        if($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        
        $sql .=" GROUP BY fv.id ORDER BY fv.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $total = $stmt->fetchColumn();

        return $total;
    }


    public function ventasTarjetaCredito($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT fv.id,fv.ticket AS codigo,c.razon_social,fv.fecha,fp.nombre AS tipo,
                    CONCAT(em.apellido_paterno,' ',em.apellido_materno,' ',em.nombres) AS usuario,
                    (CASE
                        WHEN vfp.moneda_id = 1 THEN SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                        WHEN vfp.moneda_id = 2 THEN SUM(vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1))
                        ELSE SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                    END) AS monto_total,   
                    el.nombre AS local, vfp.numero_dias AS dias   
                    FROM factura_venta fv
                    INNER JOIN venta v ON fv.venta_id = v.id
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN forma_pago fp ON vfp.forma_pago_id = fp.id
                    INNER JOIN cliente c ON fv.cliente_id = c.id
                    INNER JOIN empleado em ON em.id = v.empleado_id
                    INNER JOIN empresa_local el ON el.id = fv.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE el.estado = 1 AND v.estado = 1 AND fp.codigo = 3 AND fv.tipo_id = 2 AND e.id = ?
                    ";

        if($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        
        $sql .=" GROUP BY fv.id ORDER BY fv.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $ventas = $stmt->fetchAll();


        return $ventas;
    }

    public function ventasNotaCredito($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT fv.id,fv.ticket AS codigo,c.razon_social,fv.fecha,fp.nombre AS tipo,
                    CONCAT(em.apellido_paterno,' ',em.apellido_materno,' ',em.nombres) AS usuario,
                    (CASE
                        WHEN vfp.moneda_id = 1 THEN SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                        WHEN vfp.moneda_id = 2 THEN SUM(vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1))
                        ELSE SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                    END) AS monto_total,
                    el.nombre AS local, vfp.numero_dias AS dias   
                    FROM factura_venta fv
                    INNER JOIN venta v ON fv.venta_id = v.id
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN forma_pago fp ON vfp.forma_pago_id = fp.id
                    INNER JOIN cliente c ON fv.cliente_id = c.id
                    INNER JOIN empleado em ON em.id = v.empleado_id
                    INNER JOIN empresa_local el ON el.id = fv.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE el.estado = 1 AND v.estado = 1 AND fp.codigo = 4 AND fv.tipo_id = 2 AND e.id = ?
                    ";

        if($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        
        $sql .=" GROUP BY fv.id ORDER BY fv.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $ventas = $stmt->fetchAll();


        return $ventas;
    }

    public function detalleVenta($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT fv.id,fv.ticket AS codigo,c.razon_social,fv.fecha,fp.nombre AS tipo,fv.documento,CONCAT(em.apellido_paterno,' ',em.apellido_materno,' ',em.nombres) AS usuario,
                    dv.subtotal AS subtotal,el.nombre AS 'local',p.codigo AS codigo_prod,p.nombre AS nombre_prod,dv.descripcion,dv.cantidad,pu.nombre AS 'unidad_venta',fv.numero_guiaremision AS 'guia_remision',dv.cantidad_entregada,v.condicion
                    FROM detalle_venta dv
                    INNER JOIN venta v ON dv.venta_id = v.id
                    INNER JOIN factura_venta fv ON fv.venta_id = v.id   
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN forma_pago fp ON vfp.forma_pago_id = fp.id
                    INNER JOIN cliente c ON fv.cliente_id = c.id                
                    INNER JOIN empleado em ON em.id = v.empleado_id
                    INNER JOIN producto_x_local pxl ON dv.producto_x_local_id = pxl.id
                    INNER JOIN producto p ON p.id = pxl.producto_id
                    INNER JOIN producto_unidad pu ON p.unidad_venta_id = pu.id
                    INNER JOIN empresa_local el ON el.id = fv.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE 
                    v.estado = 1 AND 
                    el.estado = 1 AND 
                    fv.tipo_id = 2 AND  
                    e.id = ?
                    ";

        if($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        
        $sql .=" ORDER BY fv.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $ventas = $stmt->fetchAll();


        return $ventas;
    }

    public function obtenerCategorias($local)
    {

        $sql = "SELECT DISTINCT(pc.id),pc.nombre,pc.imagen FROM producto_x_local pxl
                INNER JOIN producto p ON pxl.producto_id = p.id
                INNER JOIN producto_categoria pc ON pc.id = p.categoria_id
                WHERE pc.estado = 1 AND pxl.empresa_local_id = ?
                    ";

        $sql .=" ORDER BY pc.nombre ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $local);
        $stmt->execute();
        $categorias = $stmt->fetchAll();

        return $categorias;

    }

    public function comprasAnulada($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT dca.id,fc.ticket AS ticket,p.nombre AS producto,dca.cantidad,dca.fecha,u.username AS usuario,el.nombre AS local
                    FROM detalle_compra_anulada dca
                    INNER JOIN compra c ON dca.compra_id = c.id
                    INNER JOIN factura_compra fc ON fc.compra_id = c.id                 
                    INNER JOIN usuario u ON u.id = dca.usuario_id
                    INNER JOIN producto_x_local pxl ON dca.producto_x_local_id = pxl.id
                    INNER JOIN producto p ON p.id = pxl.producto_id
                    INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE el.estado = 1 AND fc.estado = 1 AND e.id = ?
                    ";

        if($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(dca.fecha AS DATE) BETWEEN ? AND ? ";
        }
        
        $sql .=" ORDER BY dca.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $compras = $stmt->fetchAll();


        return $compras;
    }

    public function detalleCaja($empresa,$caja='',$fecha='')
    {            

        $sql = "SELECT el.nombre as 'local',c.nombre AS 'caja',ca.fecha AS 'fecha_apertura',cc.fecha AS 'fecha_cierre',ca.monto_apertura AS 'monto_apertura',
                    (SELECT SUM(cantidad) FROM caja_apertura_detalle WHERE caja_apertura_id = ca.id) AS 'entradas',u.username AS 'usuario_apertura',
                    us.username AS 'usuario_cierre',cc.total_recaudado AS 'total_recaudado'
                    FROM caja_cierre cc
                    INNER JOIN caja_apertura ca ON cc.caja_apertura_id = ca.id
                    INNER JOIN caja c ON ca.caja_id = c.id
                    INNER JOIN empresa_local el ON c.empresa_local_id = el.id
                    INNER JOIN empresa e ON el.empresa_id = e.id
                    INNER JOIN usuario u ON ca.usuario_id = u.id
                    INNER JOIN usuario us ON cc.usuario_id = us.id
                    WHERE el.estado = 1 AND c.estado = 1 AND e.id = ?
                    ";

        if($caja != '' && $fecha != ''){

            $fecha = date("Y-m-d", strtotime(str_replace('/', '-', $fecha) ) );

            $sql .=" AND CAST(ca.fecha AS DATE) = ? ";
            $sql .=" AND c.id = ? ";
        }

        $sql .=" ORDER BY fecha_apertura DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);

        if($caja != '' && $fecha != ''){
            $stmt->bindValue(2, $fecha);
            $stmt->bindValue(3, $caja);
        }

        $stmt->execute();
        $detalle = $stmt->fetchAll();


        return $detalle;
    }

    public function cajaAperturas($empresa,$caja='',$fecha='',$forma_pago='')
    {

        $sql = "SELECT ca.id AS 'apertura_id',c.id AS 'caja_id',c.nombre AS 'caja',ca.monto_apertura AS 'monto_apertura',ca.fecha AS 'fecha_apertura',el.nombre AS 'local'
                    FROM caja_apertura ca 
                    INNER JOIN caja c ON  ca.caja_id = c.id
                    INNER JOIN empresa_local el ON  c.empresa_local_id = el.id
                    INNER JOIN empresa e ON  el.empresa_id = e.id
                    WHERE e.id = ?                    
                    ";

        if($caja != '' && $fecha != ''){

            $fecha = date("Y-m-d", strtotime(str_replace('/', '-', $fecha) ) );

            $sql .=" AND CAST(ca.fecha AS DATE) = ? ";
            $sql .=" AND c.id = ? ";

        }else{

            $sql .= " AND DAY(ca.fecha) = DAY(CURRENT_DATE()) AND MONTH(ca.fecha) = MONTH(CURRENT_DATE()) AND YEAR(ca.fecha) = YEAR(CURRENT_DATE())";

        }

        $sql .=" ORDER BY ca.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);                    

        if($caja != '' && $fecha != ''){
            $stmt->bindValue(2, $fecha);
            $stmt->bindValue(3, $caja);
        }

        $stmt->execute();
        $aperturas = $stmt->fetchAll();

        $resultado_array = array();

        //dump($aperturas);

        //Seleccionamos las ventas por inicio de apertura
        $x = 0;
        foreach($aperturas as $i=>$apertura){

            //Verificamos si esta apertura tiene ya un cierre
            $sql = "SELECT cc.fecha AS 'fecha_cierre',cc.total_dejada 'monto_dejado',cc.total_recaudado AS 'total_recaudado' FROM caja_cierre cc 
                        INNER JOIN caja_apertura ca ON cc.caja_apertura_id = ca.id 
                        WHERE caja_apertura_id = ?                   
                        ";            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $apertura['apertura_id']);

            $stmt->execute();
            $cierre = $stmt->fetch();

            if($cierre){

                $fecha_cierre = $cierre['fecha_cierre'];

                $sql = "SELECT el.nombre AS 'local',c.nombre AS 'caja',fv.ticket AS 'ticket',fv.documento AS 'documento',
                            CONCAT(e.nombres,' ',e.apellido_paterno,' ',e.apellido_materno) AS 'empleado',
                            fv.fecha,
                            (CASE
                                WHEN vfp.moneda_id = 1 THEN vfp.cantidad + IFNULL(vfp.igv,0)
                                WHEN vfp.moneda_id = 2 THEN vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1)
                                ELSE vfp.cantidad + IFNULL(vfp.igv,0)
                            END) AS 'monto',   
                            fp.nombre AS 'forma_pago',c.monto_anterior AS 'monto_anterior',vfp.condicion,vfp.fecha_pago_credito AS 'fecha_pago_credito',IFNULL(vfp.monto_a_cuenta,0) AS monto_a_cuenta
                            FROM factura_venta fv
                            INNER JOIN caja c ON fv.caja_id = c.id
                            INNER JOIN venta v ON fv.venta_id = v.id
                            INNER JOIN empleado e ON v.empleado_id = e.id
                            INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                            INNER JOIN forma_pago fp ON vfp.forma_pago_id = fp.id
                            INNER JOIN empresa_local el ON el.id = fv.empresa_local_id
                            INNER JOIN empresa em ON el.empresa_id = em.id
                            WHERE v.estado = 1 AND fv.tipo_id = 2  AND em.id = ? 
                            ";

                $sql .= " AND (fv.fecha BETWEEN ? AND ?  OR vfp.fecha_pago_credito BETWEEN ? AND ? ) ";
                $sql .= " AND c.id = ? ";

                if($forma_pago != ''){
                    $sql .= " AND fp.id = ? ";
                }

                $sql .= " ORDER BY fv.fecha DESC,c.nombre DESC";

                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(1, $empresa);
                $stmt->bindValue(2, $apertura['fecha_apertura']);                
                $stmt->bindValue(3, $cierre['fecha_cierre']);
                $stmt->bindValue(4, $apertura['fecha_apertura']);                
                $stmt->bindValue(5, $cierre['fecha_cierre']);                
                $stmt->bindValue(6, $apertura['caja_id']);

                if($forma_pago != ''){
                    $stmt->bindValue(7, $forma_pago);
                }

                $stmt->execute();
                $detalleFacturas = $stmt->fetchAll();


            }else{

                //dump($apertura['fecha_apertura']);
                //dump($apertura['caja_id']);


                $sql = "SELECT el.nombre AS 'local',c.nombre AS 'caja',fv.ticket,fv.documento,
                            CONCAT(e.nombres,' ',e.apellido_paterno,' ',e.apellido_materno) AS 'empleado',
                            fv.fecha,
                            (CASE
                                WHEN vfp.moneda_id = 1 THEN vfp.cantidad + IFNULL(vfp.igv,0)
                                WHEN vfp.moneda_id = 2 THEN vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1)
                                ELSE vfp.cantidad + IFNULL(vfp.igv,0)
                            END) AS 'monto',   
                            fp.nombre AS 'forma_pago',c.monto_anterior AS 'monto_anterior',vfp.condicion,vfp.fecha_pago_credito AS 'fecha_pago_credito',IFNULL(vfp.monto_a_cuenta,0) AS monto_a_cuenta
                            FROM factura_venta fv
                            INNER JOIN caja c ON fv.caja_id = c.id
                            INNER JOIN venta v ON fv.venta_id = v.id
                            INNER JOIN empleado e ON v.empleado_id = e.id
                            INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                            INNER JOIN forma_pago fp ON vfp.forma_pago_id = fp.id
                            INNER JOIN empresa_local el ON el.id = fv.empresa_local_id
                            INNER JOIN empresa em ON el.empresa_id = em.id
                            WHERE v.estado = 1 AND fv.tipo_id = 2  AND em.id = ? 
                            ";

                $sql .= " AND (fv.fecha >= ?  OR  vfp.fecha_pago_credito >= ?  ) ";
                $sql .= " AND c.id = ? ";

                if($forma_pago != ''){
                    $sql .= " AND fp.id = ? ";
                }

                $sql .= " ORDER BY fv.fecha DESC,c.nombre DESC";

                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(1, $empresa);
                $stmt->bindValue(2, $apertura['fecha_apertura']);
                $stmt->bindValue(3, $apertura['fecha_apertura']);
                $stmt->bindValue(4, $apertura['caja_id']);

                if($forma_pago != ''){
                    $stmt->bindValue(5, $forma_pago);
                }

                $stmt->execute();
                $detalleFacturas = $stmt->fetchAll();

                //dump($detalleFacturas);


            }
            

            if(count($detalleFacturas) == 0 ){

                $resultado_array[$x][0]['apertura_id']     = $apertura['apertura_id'];
                $resultado_array[$x][0]['fecha_apertura']  = $apertura['fecha_apertura'];
                $resultado_array[$x][0]['monto_apertura']  = $apertura['monto_apertura'];
                $resultado_array[$x][0]['caja']            = $apertura['caja'];
                $resultado_array[$x][0]['local']           = $apertura['local'];
                $resultado_array[$x][0]['ticket']          = '';
                $resultado_array[$x][0]['documento']       = '';                
                $resultado_array[$x][0]['empleado']        = '';
                $resultado_array[$x][0]['fecha']           = '';
                $resultado_array[$x][0]['monto']           = '';
                $resultado_array[$x][0]['monto_a_cuenta']  = '';
                $resultado_array[$x][0]['forma_pago']      = '';
                $resultado_array[$x][0]['monto_anterior']  = '';
                $resultado_array[$x][0]['condicion']       = '';
                $resultado_array[$x][0]['fecha_pago_credito']  = '';

            }else{

                $z = 0;
                foreach($detalleFacturas as $j=>$detalleFactura){

                    $resultado_array[$x][$z]['apertura_id']     = $apertura['apertura_id'];
                    $resultado_array[$x][$z]['fecha_apertura']  = $apertura['fecha_apertura'];
                    $resultado_array[$x][$z]['monto_apertura']  = $apertura['monto_apertura'];
                    $resultado_array[$x][$z]['caja']            = $detalleFactura['caja'];
                    $resultado_array[$x][$z]['local']           = $detalleFactura['local'];
                    $resultado_array[$x][$z]['ticket']          = $detalleFactura['ticket'];
                    $resultado_array[$x][$z]['documento']       = $detalleFactura['documento'];
                    $resultado_array[$x][$z]['empleado']        = $detalleFactura['empleado'];
                    $resultado_array[$x][$z]['fecha']           = $detalleFactura['fecha'];
                    $resultado_array[$x][$z]['monto']           = $detalleFactura['monto'];
                    $resultado_array[$x][$z]['monto_a_cuenta']  = $detalleFactura['monto_a_cuenta'];
                    $resultado_array[$x][$z]['forma_pago']      = $detalleFactura['forma_pago'];
                    $resultado_array[$x][$z]['monto_anterior']  = $detalleFactura['monto_anterior'];
                    $resultado_array[$x][$z]['condicion']       = $detalleFactura['condicion'];
                    $resultado_array[$x][$z]['fecha_pago_credito']       = $detalleFactura['fecha_pago_credito'];

                    $z++;

                }

            }  


            $x++;
        }

        return $resultado_array;       

    }
  
      public function detalleVentaXCaja($empresa,$caja='',$fecha='')
    {            

        $sql = "SELECT el.nombre AS 'local',c.nombre AS 'caja',fv.ticket,fv.documento,
                    CONCAT(e.nombres,' ',e.apellido_paterno,' ',e.apellido_materno) AS 'empleado',
                    fv.fecha,
                    (CASE
                        WHEN vfp.moneda_id = 1 THEN SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                        WHEN vfp.moneda_id = 2 THEN SUM(vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1))
                        ELSE SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                    END) AS 'monto',
                    #vfp.cantidad AS 'monto',
                    fp.nombre AS 'forma_pago',c.monto_anterior AS 'monto_anterior',
                    (SELECT monto_apertura from caja_apertura WHERE caja_id = c.id AND estado = 1 LIMIT 1) AS 'monto_apertura',
                    (SELECT fecha from caja_apertura WHERE caja_id = c.id AND estado = 1 LIMIT 1) AS 'fecha_apertura'
                    #IF(fv.cliente_id ,SELECT razon_social FROM cliente  WHERE id = fv.cliente_id ,fv.cliente_nombre) as 'cliente'  
                    FROM factura_venta fv
                    INNER JOIN caja c ON fv.caja_id = c.id
                    INNER JOIN venta v ON fv.venta_id = v.id
                    INNER JOIN empleado e ON v.empleado_id = e.id
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN forma_pago fp ON vfp.forma_pago_id = fp.id
                    INNER JOIN empresa_local el ON el.id = fv.empresa_local_id
                    INNER JOIN empresa em ON el.empresa_id = em.id
                    WHERE v.estado = 1 AND fv.tipo_id = 2  AND em.id = ? 
                    ";

        

        if($caja != '' && $fecha != ''){

            $fecha = date("Y-m-d", strtotime(str_replace('/', '-', $fecha) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) = ? ";
            $sql .=" AND c.id = ? ";

        }else{

            $sql .= "AND DAY(fv.fecha) = DAY(CURRENT_DATE()) AND MONTH(fv.fecha) = MONTH(CURRENT_DATE()) AND YEAR(v.fecha) = YEAR(CURRENT_DATE())";

        }

        $sql .=" ORDER BY fv.fecha DESC ,c.nombre DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);

        if($caja != '' && $fecha != ''){
            $stmt->bindValue(2, $fecha);
            $stmt->bindValue(3, $caja);
        }

        $stmt->execute();
        $detalle = $stmt->fetchAll();

        if(count($detalle) == 0 ){

            $sql = "SELECT ca.monto_apertura AS 'monto_apertura',ca.fecha AS 'fecha_apertura' FROM caja_apertura ca 
                    INNER JOIN  caja c ON ca.caja_id = c.id WHERE ca.estado = 1  ";

            if($caja != '' && $fecha != ''){

                $fecha = date("Y-m-d", strtotime(str_replace('/', '-', $fecha) ) );

                $sql .=" AND CAST(fv.fecha AS DATE) = ? ";
                $sql .=" AND c.id = ? ";

            }else{

                $sql .= "AND DAY(fv.fecha) = DAY(CURRENT_DATE()) AND MONTH(fv.fecha) = MONTH(CURRENT_DATE()) AND YEAR(v.fecha) = YEAR(CURRENT_DATE())";

            }

        
        }


        return $detalle;
    }


    public function getProductoXLocalDT($table,$local)
    {         
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * Easy set variables
         */
         
        // DB table to use
        //$table = tabla;
         
        // Table's primary key
        $primaryKey = 'id';
         
        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            array( 'db' => 'id', 'dt' => 0 ),
            array( 'db' => 'codigo',  'dt' => 1 ),
            array( 'db' => 'nombre',   'dt' => 2 ),
            // array( 'db' => 'office',     'dt' => 3 ),
            // array(
            //     'db'        => 'start_date',
            //     'dt'        => 4,
            //     'formatter' => function( $d, $row ) {
            //         return date( 'jS M y', strtotime($d));
            //     }
            // ),
            array(
                'db'        => 'precio_unitario',
                'dt'        => 3,
                'formatter' => function( $d, $row ) {
                    return 'S./ '.number_format($d,2,'.','');
                }
            ),
            array(
                'db'        => 'precio_cantidad',
                'dt'        => 4,
                'formatter' => function( $d, $row ) {
                    return 'S./ '.number_format($d,2,'.','');
                }
            ),
            array(
                'db'        => 'precio_compra',
                'dt'        => 5,
                'formatter' => function( $d, $row ) {
                    return 'S./ '.number_format($d,2,'.','');
                }
            ),
            //array( 'db' => 'stock',   'dt' => 6 ),                       
        );
         
        // SQL server connection information
        $sql_details = array(
            'user' => $this->em->getConnection()->getUsername(),
            'pass' => $this->em->getConnection()->getPassword(),
            'db'   => $this->em->getConnection()->getDatabase(),
            'host' => $this->em->getConnection()->getHost()
        );
         
         
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * If you just want to use the basic configuration for DataTables with PHP
         * server-side, there is no need to edit below this line.
         */
         
        //require( 'ssp.class.php' );

        return $this->ssp->complex($_GET, $sql_details, $table, $primaryKey, $columns ,null, "local_id = ".$local);

    }

    public function getProductoStockXLocalDT($table,$empresa)
    {         
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * Easy set variables
         */
         
        // DB table to use
        //$table = tabla;
         
        // Table's primary key
        $primaryKey = 'id';
         
        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            array( 'db' => 'id', 'dt' => 0 ),
            array( 'db' => 'local',  'dt' => 1 ),
            array( 'db' => 'codigo',   'dt' => 2 ),
            array( 'db' => 'nombre',   'dt' => 3 ),
            array( 'db' => 'imagen',   'dt' => 4 ),
            // array( 'db' => 'office',     'dt' => 3 ),
            // array(
            //     'db'        => 'imagen',
            //     'dt'        => 4,
            //     'formatter' => function( $d, $row ) {
            //         $ruta_img = $this->container->getParameter('imagenes_directorio');
            //         $result = '<img src="'.$ruta_img.'\100x100\noimage.png" height="25" width="35" />';
            //         return $result;//'.$d.'"
            //     }
            // ),
            array(
                'db'        => 'precio_venta',
                'dt'        => 5,
                'formatter' => function( $d, $row ) {
                    return 'S./ '.number_format($d,2,'.','');
                }
            ),
            array(
                'db'        => 'precio_compra',
                'dt'        => 6,
                'formatter' => function( $d, $row ) {
                    return 'S./ '.number_format($d,2,'.','');
                }
            ),
            array( 'db' => 'marca',   'dt' => 7 ),
            array( 'db' => 'stock',   'dt' => 8 ),
            array( 'db' => 'descripcion',   'dt' => 9 ),
              
        );
         
        // SQL server connection information
        $sql_details = array(
            'user' => $this->em->getConnection()->getUsername(),
            'pass' => $this->em->getConnection()->getPassword(),
            'db'   => $this->em->getConnection()->getDatabase(),
            'host' => $this->em->getConnection()->getHost()
        );
         
         
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * If you just want to use the basic configuration for DataTables with PHP
         * server-side, there is no need to edit below this line.
         */
         
        //require( 'ssp.class.php' );
         
        return $this->ssp->complex( $_GET, $sql_details, $table, $primaryKey, $columns ,null, "empresa_id = ".$empresa);

    }

    public function getProductoStockValorizadoXLocalDT($table,$empresa)
    {         
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * Easy set variables
         */
         
        // DB table to use
        //$table = tabla;
         
        // Table's primary key
        $primaryKey = 'id';
         
        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            array( 'db' => 'id', 'dt' => 0 ),
            array( 'db' => 'local',  'dt' => 1 ),
            array( 'db' => 'codigo',   'dt' => 2 ),
            array( 'db' => 'nombre',   'dt' => 3 ),
            //array( 'db' => 'imagen',   'dt' => 4 ),
            // array( 'db' => 'office',     'dt' => 3 ),
            // array(
            //     'db'        => 'imagen',
            //     'dt'        => 4,
            //     'formatter' => function( $d, $row ) {
            //         $ruta_img = $this->container->getParameter('imagenes_directorio');
            //         $result = '<img src="'.$ruta_img.'\100x100\noimage.png" height="25" width="35" />';
            //         return $result;//'.$d.'"
            //     }
            // ),
            array(
                'db'        => 'precio_venta',
                'dt'        => 4,
                'formatter' => function( $d, $row ) {
                    return number_format($d,4,'.','');
                }
            ),
            array(
                'db'        => 'precio_ponderado',
                'dt'        => 5,
                'formatter' => function( $d, $row ) {
                    return number_format($d,4,'.','');
                }
            ),
            array( 'db' => 'marca',   'dt' => 6 ),
            array( 'db' => 'stock',   'dt' => 7 ),
            array(
                'db'        => 'precio_total_costo',
                'dt'        => 8,
                'formatter' => function( $d, $row ) {
                    return number_format($d,4,'.','');
                }
            ),
            array(
                'db'        => 'precio_total_venta',
                'dt'        => 9,
                'formatter' => function( $d, $row ) {
                    return number_format($d,4,'.','');
                }
            ),
            array(
                'db'        => 'utilidad',
                'dt'        => 10,
                'formatter' => function( $d, $row ) {
                    return number_format($d,4,'.','');
                }
            ),
            array( 'db' => 'categoria',   'dt' => 11 ),                        
            // array(
            //     'db'        => 'precio_compra',
            //     'dt'        => 11,
            //     'formatter' => function( $d, $row ) {
            //         return number_format($d,4,'.','');
            //     }
            // ),           
            // array( 'db' => 'precio_total_venta',   'dt' => 9 ),
            // array( 'db' => 'utilidad',   'dt' => 10 ),
              
        );
         
        // SQL server connection information
        $sql_details = array(
            'user' => $this->em->getConnection()->getUsername(),
            'pass' => $this->em->getConnection()->getPassword(),
            'db'   => $this->em->getConnection()->getDatabase(),
            'host' => $this->em->getConnection()->getHost()
        );
         
         
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * If you just want to use the basic configuration for DataTables with PHP
         * server-side, there is no need to edit below this line.
         */
         
        //require( 'ssp.class.php' );
         
        return $this->ssp->complex( $_GET, $sql_details, $table, $primaryKey, $columns ,null, "empresa_id = ".$empresa);

    }

    public function getFacturaVentaDT($table,$empresa)
    {         
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * Easy set variables
         */
         
        // DB table to use
        //$table = tabla;
         
        // Table's primary key
        $primaryKey = 'id';

        
        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            array( 'db' => 'id', 'dt' => 0 ),
            array( 
                'db' => 'local',  
                'dt' => 1,
                'formatter' => function( $d, $row ) {

                    $result = (!$row['estado'])? '<del>': '';
                    $result .= $d;
                    $result .= (!$row['estado'])? '</del>': '';

                    return $result;
                }                 
            ),
            array( 
                'db' => 'numero_documento',   
                'dt' => 2,
                'formatter' => function( $d, $row ) {

                    $result = (!$row['estado'])? '<del>': '';
                    $result .= $d;
                    $result .= (!$row['estado'])? '</del>': '';

                    return $result;
                }     
            ),
            array( 
                'db' => 'cliente',  
                'dt' => 3,
                'formatter' => function( $d, $row ) {

                    $result = (!$row['estado'])? '<del>': '';
                    $result .= $d;
                    $result .= (!$row['estado'])? '</del>': '';

                    return $result;
                }     
            ),
            array( 
                'db' => 'tipo_documento',   
                'dt' => 4,
                'formatter' => function( $d, $row ) {

                    $result = (!$row['estado'])? '<del>': '';
                    $result .= $d;
                    $result .= (!$row['estado'])? '</del>': '';

                    return $result;
                }                      
            ),
            array( 
                'db' => 'empleado',  
                'dt' => 5,
                'formatter' => function( $d, $row ) {

                    $result = (!$row['estado'])? '<del>': '';
                    $result .= $d;
                    $result .= (!$row['estado'])? '</del>': '';

                    return $result;
                }                     
             ),
            array( 
                'db' => 'fecha',   
                'dt' => 6, 
                'formatter' => function( $d, $row ) {
                    //$newDate = date("Y-m-d H:i", strtotime($d));
                    //$date = \DateTime::createFromFormat('d/m/Y H:i', $d);

                    //$date    = str_replace('/', '-', $d );
                    //$newDate = date("Y-m-d H:i", strtotime($date));

                    $result  = (!$row['estado'])? '<del>': '';
                    $result .= $d;
                    $result .= (!$row['estado'])? '</del>': '';

                    return $result;
                }
            ),
            array(
                'db'        => 'monto',
                'dt'        => 7,
                'formatter' => function( $d, $row ) {

                    $result = (!$row['estado'])? '<del>': '';
                    $result .= 'S./ '.number_format($d,2,'.','');
                    $result .= (!$row['estado'])? '</del>': '';

                    return $result;
                }
            ),            
            array( 
                'db' => 'moneda',  
                'dt' => 8,
                'formatter' => function( $d, $row ) {

                    $result = (!$row['estado'])? '<del>': '';
                    $result .= $d;
                    $result .= (!$row['estado'])? '</del>': '';

                    return $result;
                }                     
            ),
            array( 
                'db' => 'forma_pago',   
                'dt' => 9,
                'formatter' => function( $d, $row ) {

                    $result = (!$row['estado'])? '<del>': '';
                    $result .= $d;
                    $result .= (!$row['estado'])? '</del>': '';

                    return $result;
                }                
            ),
            array( 
                'db' => 'estado_pago',  
                'dt' => 10,
                'formatter' => function( $d, $row ) {

                    $estado_pago_clase = '';
                    $estado_pago = strtoupper($d);
                    if(!$d){
                        $estado_pago_clase = 'bg-success text-white';
                        $estado_pago = 'PAGADO';
                    }

                    $result = (!$row['estado'])? '<del>': '';
                    $result .= '<span class="'.$estado_pago_clase.'">'.$estado_pago.'</span>';
                    $result .= (!$row['estado'])? '</del>': '';
                
                    return $result;
                }                
            ),
            array( 
                'db' => 'condicion',   
                'dt' => 11,
                'formatter' => function( $d, $row ) {

                    $detalleVentas = $this->em->getRepository('AppBundle:DetalleVenta')->findBy(array('venta'=>$row['venta_id']));

                    $result = (!$row['estado'])? '<del>': '';

                    if($d)
                    {
                        $total_elementos = count($detalleVentas);
                        $parcial = 0;

                        foreach($detalleVentas as $detalleVenta)
                        {
                            if($detalleVenta->getCantidad() == $detalleVenta->getCantidadEntregada())
                            {
                                $parcial = $parcial + 1;
                            }

                        }

                        if($parcial == $total_elementos)
                        {
                            $result .= '<span class="bg-success text-white">ENTREGADO</span>';
                        }
                        elseif($parcial < $total_elementos && $parcial != 0 )
                        {
                            $result .= '<span class="bg-warning text-white">ENTREGADO PARCIALMENTE</span>';
                        }
                        elseif($parcial == 0)
                        {
                            $result .= '<span class="bg-danger text-white">PENDIENTE DE ENTREGA</span>';
                        }
                    }
                    else
                    {
                        $result .= '<span class="bg-success text-white">ENTREGADO</span>';
                    }
                    
                    $result .= (!$row['estado'])? '</del>': '';

                    return $result;
                }                           

            ),
            array( 
                'db' => 'enviado_sunat',  
                'dt' => 12, 
                'formatter' => function( $d, $row ) {

                    $enviado_sunat = '';
                    $enviado_sunat_clase = '';

                    if($row['emision_electronica'])
                    {

                        if($row['tipo_documento'] == 'FACTURA' || $row['tipo_documento'] == 'BOLETA')
                        {

                            if($d)
                            {

                                $enviado_sunat = 'ENVIADO';
                                $enviado_sunat_clase = 'bg-success text-white';

                            
                            }
                            else
                            {
                                // $fecha = date("Y-m-d", strtotime($row['fecha_creacion']));

                                // //$fecha =  date("Y-m-d", $row['fecha_creacion']);
                                // $date1 = new \DateTime($fecha->format('Y-m-d'));//new \DateTime($fecha);
                                // $date2 = new \DateTime("now");
                                // $diff = $date1->diff($date2);
                                // $leftDays = $diff->days

                                $datetime1 = new \DateTime();
                                $datetime2 = date_create($row['fecha_creacion']);
                                        
                                $interval = date_diff($datetime1, $datetime2);

                                $leftDays = $interval->format('%a');                                       

                                if($leftDays > 2)
                                {
                                    $enviado_sunat = 'NO-ENVIADO';
                                    $enviado_sunat_clase = 'bg-danger text-white';
                                }
                                else
                                {
                                    $enviado_sunat = 'EN-PROCESO';
                                    $enviado_sunat_clase = 'bg-warning text-white';
                                }


                            }

                        }

                    }

                    $result = (!$row['estado'])? '<del>': '';
                    $result .= '<span class="'.$enviado_sunat_clase.'">'.$enviado_sunat.'</span>';
                    $result .= (!$row['estado'])? '</del>': '';
                
                    return $result;
                }    
            ),
            array( 'db' => 'facturacion_electronica',   'dt' => 13 ),                                                            
            array( 'db' => 'venta_id',   'dt' => 14 ),
            array( 'db' => 'email',  'dt' => 15 ),
            array( 'db' => 'enlacepdf',   'dt' => 16 ),
            array( 'db' => 'enlace_xml',   'dt' => 17 ),
            array( 'db' => 'enlace_cdr',   'dt' => 18 ),
            array( 'db' => 'cliente_nombre',  'dt' => 19 ),
            array( 'db' => 'emision_electronica',  'dt' => 20 ),
            array( 'db' => 'fecha_creacion',  'dt' => 21 ),
            array( 
                'db' => 'estado',  
                'dt' => 22,
                'formatter' => function( $d, $row ) {

                    $estado = 'NO';
                    if(!$d){
                        $estado = 'SI';
                    }
                
                    return $estado;
                }                
            ),
            array( 'db' => 'enlace_pdf_ferretero',   'dt' => 23 ),
            array( 'db' => 'formato_ferretero',   'dt' => 24 ),
            array( 
                'db' => 'local_id',   
                'dt' => 25,
                'formatter' => function( $d, $row ) {

                    $enlace_del_pdf = $row['enlacepdf'];

                    if($row['tipo_documento'] == 'FACTURA' || $row['tipo_documento'] == 'BOLETA')
                    {
                        if($row['formato_ferretero'])
                        {
                            $enlace_del_pdf = $row['enlace_pdf_ferretero'];
                        }
                    }
                    else
                    {
                        $enlace_del_pdf = $row['enlace_pdf_ferretero'];
                    }


                    $result = '';
                    $rol = $this->session->get('rol');

                    if($rol == 'Administrador')
                    {
                        $result .= '<a href="javascript:anular('.$row['id'].')" class="mr-2"  data-toggle="tooltip" data-target="" title="Anular"><i class="fa fa-remove fa-lg"></i></a>';
                    }

                    $result .= '<a href="#" class="mr-2" data-toggle="modal" data-target="#detalleventa" data-factura="'.$row['id'].'"><i class="fa fa-list fa-lg" data-toggle="tooltip" title="Ver detalle"></i></a>';

                    if($enlace_del_pdf)
                    {
                        $result .= '<a href="'.$enlace_del_pdf.'" target="_blank" id="mostrar_doc" class="mr-2" ><i class="fa fa-print fa-lg"  data-toggle="tooltip" title="Imprimir"></i></a>';
                    }
                    else
                    {

                        $ruta_imprimir = $this->router->generate('detalleventa_imprimir', array('id' => $row['id']));
                        $result .= '<a href="'.$ruta_imprimir.'" target="_blank" id="mostrar_doc" class="mr-2" ><i class="fa fa-print fa-lg"  data-toggle="tooltip" title="Imprimir"></i> </a>';                        

                        // if($row['facturacion_electronica'])
                        // {
                        //     $ruta_imprimir_con_reenvio = $this->imprimirConReenvio($row['id']);
                        //     if($ruta_imprimir_con_reenvio)
                        //     {
                        //         $result .= '<a href="'.$ruta_imprimir_con_reenvio.'" target="_blank" id="mostrar_doc" class="mr-2" ><i class="fa fa-print fa-lg"  data-toggle="tooltip" title="Imprimir"></i></a>';
                        //     }
                        //     else
                        //     {
                        //         if($row['tipo_documento'] == 'FACTURA' || $row['tipo_documento'] == 'BOLETA')
                        //         {
                        //             $result .= '<a href="#" id="mostrar_doc" class="mr-2" ><i class="fa fa-print fa-lg"  data-toggle="tooltip" title="Imprimir"></i></a>';
                        //         }
                        //         else
                        //         {

                        //             $ruta_imprimir = $this->router->generate('detalleventa_imprimir', array('id' => $row['id']));
                        //             $result .= '<a href="'.$ruta_imprimir.'" target="_blank" id="mostrar_doc" class="mr-2" ><i class="fa fa-print fa-lg"  data-toggle="tooltip" title="Imprimir"></i> </a>';
                        //         }
                                
                        //     }
                            
                        // }
                        // else
                        // {
                        //     $ruta_imprimir = $this->router->generate('detalleventa_imprimir', array('id' => $row['id']));
                        //     $result .= '<a href="'.$ruta_imprimir.'" target="_blank" id="mostrar_doc" class="mr-2" ><i class="fa fa-print fa-lg"  data-toggle="tooltip" title="Imprimir"></i> </a>';

                        // }

                    }

                    if($row['forma_pago'] == 'A CUENTA' && $row['estado_pago'] == 'pendiente')
                    {
                        $result .= '<a href="#" class="mr-2" data-toggle="modal" data-target="#pagoACuenta" data-factura="'.$row['id'].'"><i class="fa fa-money fa-lg" data-toggle="tooltip" title="Pagar"></i></a>';
                    }

                    if($row['forma_pago'] == 'CREDITO' && $row['estado_pago'] == 'pendiente')
                    {
                        $ruta_finalizar_pago = $this->router->generate('detalleventa_finalizar_pago', array('id' => $row['id']));
                        $result .= '<a href="'.$ruta_finalizar_pago.'" class="confirmation mr-2"><i class="fa fa-power-off fa-lg" data-toggle="tooltip" title="Finalizar pago"></i></a>';
                    }

                    if($row['condicion'] && $rol != 'Vendedor')
                    {
                        $ruta_entrega = $this->router->generate('detalleventa_entrega', array('id' => $row['id']));
                        $result .= '<a href="'.$ruta_entrega.'" class="mr-2" ><i class="fa fa-share-square-o fa-lg" data-toggle="tooltip" title="Entrega de productos"></i></a>';
                    }
                    else
                    {
                        $ruta_entrega_completa = $this->router->generate('detalleventa_entrega_completa', array('id' => $row['id']));
                        $result .= '<a href="'.$ruta_entrega_completa.'" class="mr-2" ><i class="fa fa-eye fa-lg" data-toggle="tooltip" title="Ver entregas"></i></a>';
                    }

                    $correo = '';
                    if($row['email'])
                    {
                        if($row['email'] != '')
                        {
                            $correo = "'".$row['email']."'";
                        }
                        
                    }

                    if($row['tipo_documento'] == 'FACTURA' || $row['tipo_documento'] == 'BOLETA')
                    {
                        if($correo != ''){
                            $result .= '<a href="javascript:enviarFactura('.$row['id'].','.$correo.');" class="mr-2"  data-toggle="tooltip" data-target="" title="Enviar factura electronica al cliente"><i class="fa fa-envelope-o fa-lg"></i></a>';
                        }

                    }

                    if($row['enlace_xml'])
                    {
                        $result .= '<a href="'.$row['enlace_xml'].'" target="_blank" id="descarga_xml" class="mr-2" ><i class="fa fa-file-code-o fa-lg"  data-toggle="tooltip" title="Descargar XML"></i></a>';
                    }

                    if($row['enlace_cdr'])
                    {
                        $result .= '<a href="'.$row['enlace_cdr'].'" target="_blank" id="descarga_cdr" class="mr-2" ><i class="fa fa-file-o fa-lg"  data-toggle="tooltip" title="Descargar CDR"></i></a>';
                    }                    
                                    

                    return $result;                                    

                } 

            ),

        );
         
        // SQL server connection information
        $sql_details = array(
            'user' => $this->em->getConnection()->getUsername(),
            'pass' => $this->em->getConnection()->getPassword(),
            'db'   => $this->em->getConnection()->getDatabase(),
            'host' => $this->em->getConnection()->getHost()
        );
         
         
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * If you just want to use the basic configuration for DataTables with PHP
         * server-side, there is no need to edit below this line.
         */
         
        //require( 'ssp.class.php' );
         
        return $this->ssp->complex($_GET, $sql_details, $table, $primaryKey, $columns ,null, "empresa_id = ".$empresa);

    }

    public function imprimirConReenvio($factura_id)
    {
        $facturaVenta = $this->em->getRepository('AppBundle:FacturaVenta')->find($factura_id);

        $url   = $facturaVenta->getLocal()->getUrlFacturacion();
        $token = $facturaVenta->getLocal()->getTokenFacturacion();

        $data_json      = $this->generarConsultaArchivoJson($facturaVenta);
        $respuesta      = $this->enviarConsultaArchivoJson($data_json,$url,$token);
        $leer_respuesta = json_decode($respuesta, true);

        $enlace_del_pdf = null;

        if (!isset($leer_respuesta['errors']))
        {
            $enlace_del_pdf = ($leer_respuesta['enlace_del_pdf']) ? $leer_respuesta['enlace_del_pdf'] : $leer_respuesta['enlace'].'.pdf';
        }

        $facturaVenta->setEnlacepdf($enlace_del_pdf);
        $this->em->persist($facturaVenta);

        try {
            $this->em->flush();

        } catch (\Exception $e) {

            return $e;
        }

        return $enlace_del_pdf;

    }

    public function obtenerVentaXDia($empresa,$dia='',$local='')
    {

        $sql = "SELECT SUM(dv.subtotal)  AS total FROM detalle_venta dv
                    INNER JOIN venta v ON dv.venta_id = v.id
                    INNER JOIN factura_venta fv ON fv.venta_id = v.id
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN producto_x_local pxl ON dv.producto_x_local_id = pxl.id
                    INNER JOIN empresa_local el ON pxl.empresa_local_id = el.id
                    INNER JOIN empresa e ON el.empresa_id = e.id
                    WHERE v.estado = 1 AND fv.tipo_id = 2 AND e.id = ? ";//AND (vfp.condicion IS NULL OR vfp.condicion = 'pagado' )

        $sql .=" AND DAY(fv.fecha) = DAY(DATE_SUB(NOW(), INTERVAL ? DAY )) AND MONTH(fv.fecha) = MONTH(DATE_SUB(NOW(), INTERVAL ? DAY )) AND YEAR(fv.fecha) = YEAR(DATE_SUB(NOW(), INTERVAL ? DAY ))";

        if($local != ''){
            $sql .= " AND el.id = ? ";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        $stmt->bindValue(2, $dia);
        $stmt->bindValue(3, $dia);
        $stmt->bindValue(4, $dia);
        if($local != ''){
            $stmt->bindValue(5, $local);
        }           
        $stmt->execute();
        $venta = $stmt->fetchColumn();

        return $venta;

    }

    public function obtenerCompraXDia($empresa,$dia='',$local='')
    {

        $sql = "SELECT SUM(dc.subtotal) as total FROM detalle_compra dc
                    INNER JOIN compra c  ON dc.compra_id = c.id
                    INNER JOIN factura_compra fc ON fc.compra_id = c.id
                    INNER JOIN compra_forma_pago cfp  ON cfp.compra_id = c.id
                    INNER JOIN producto_x_local pxl ON dc.producto_x_local_id = pxl.id
                    INNER JOIN empresa_local el ON pxl.empresa_local_id = el.id
                    INNER JOIN empresa e ON el.empresa_id = e.id
                    WHERE fc.estado = 1 AND cfp.forma_pago_id <> 4 AND e.id = ?  ";//AND (cfp.condicion IS NULL OR cfp.condicion = 'pagado' ) 

        $sql .=" AND DAY(fc.fecha) = DAY(DATE_SUB(NOW(), INTERVAL ? DAY )) AND MONTH(fc.fecha) = MONTH(DATE_SUB(NOW(), INTERVAL ? DAY )) AND YEAR(fc.fecha) = YEAR(DATE_SUB(NOW(), INTERVAL ? DAY )) ";

        if($local != ''){
            $sql .= " AND el.id = ? ";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        $stmt->bindValue(2, $dia);
        $stmt->bindValue(3, $dia);
        $stmt->bindValue(4, $dia);
        if($local != ''){
            $stmt->bindValue(5, $local);
        }          
        $stmt->execute();
        $compra = $stmt->fetchColumn();

        return $compra;

    }

    public function obtenerGastoXDia($empresa,$dia='',$local='')
    {

        $sql = "SELECT SUM(g.monto) as total FROM gasto g
                    INNER JOIN empresa_local el ON g.empresa_local_id = el.id
                    INNER JOIN empresa e ON el.empresa_id = e.id
                    WHERE g.estado = 1 AND e.id = ?
                    ";

        $sql .=" AND DAY(g.fecha) = DAY(DATE_SUB(NOW(), INTERVAL ? DAY )) AND MONTH(g.fecha) = MONTH(DATE_SUB(NOW(), INTERVAL ? DAY )) AND YEAR(g.fecha) = YEAR(DATE_SUB(NOW(), INTERVAL ? DAY )) ";

        if($local != ''){
            $sql .= " AND el.id = ? ";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        $stmt->bindValue(2, $dia);
        $stmt->bindValue(3, $dia);
        $stmt->bindValue(4, $dia);
        if($local != ''){
            $stmt->bindValue(5, $local);
        }          
        $stmt->execute();
        $gasto = $stmt->fetchColumn();

        return $gasto;

    }

    /***********X mes*********/
    public function obtenerVentaXMes($empresa,$mes='',$local='')
    {

        $sql = "SELECT SUM(dv.subtotal) AS total FROM detalle_venta dv
                    INNER JOIN venta v ON dv.venta_id = v.id
                    INNER JOIN factura_venta fv ON fv.venta_id = v.id
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN producto_x_local pxl ON dv.producto_x_local_id = pxl.id
                    INNER JOIN empresa_local el ON pxl.empresa_local_id = el.id
                    INNER JOIN empresa e ON el.empresa_id = e.id
                    WHERE v.estado = 1 AND fv.tipo_id = 2 AND e.id = ? ";//AND (vfp.condicion IS NULL OR vfp.condicion = 'pagado' )

        $sql .=" AND MONTH(fv.fecha) = MONTH(DATE_SUB(NOW(), INTERVAL ? MONTH )) AND YEAR(fv.fecha) = YEAR(DATE_SUB(NOW(), INTERVAL ? MONTH ))";

        if($local != ''){
            $sql .= " AND el.id = ? ";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        $stmt->bindValue(2, $mes);
        $stmt->bindValue(3, $mes);
        if($local != ''){
            $stmt->bindValue(4, $local);
        }     

        $stmt->execute();
        $venta = $stmt->fetchColumn();

        return $venta;

    }

    public function obtenerCompraXMes($empresa,$mes='',$local='')
    {

        $sql = "SELECT SUM(dc.subtotal) as total FROM detalle_compra dc
                    INNER JOIN compra c  ON dc.compra_id = c.id
                    INNER JOIN factura_compra fc ON fc.compra_id = c.id
                    INNER JOIN compra_forma_pago cfp  ON cfp.compra_id = c.id
                    INNER JOIN producto_x_local pxl ON dc.producto_x_local_id = pxl.id
                    INNER JOIN empresa_local el ON pxl.empresa_local_id = el.id
                    INNER JOIN empresa e ON el.empresa_id = e.id
                    WHERE fc.estado = 1 AND cfp.forma_pago_id <> 4 AND e.id = ? ";//AND (cfp.condicion IS NULL OR cfp.condicion = 'pagado' )

        $sql .=" AND MONTH(fc.fecha) = MONTH(DATE_SUB(NOW(), INTERVAL ? MONTH )) AND YEAR(fc.fecha) = YEAR(DATE_SUB(NOW(), INTERVAL ? MONTH )) ";

        if($local != ''){
            $sql .= " AND el.id = ? ";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        $stmt->bindValue(2, $mes);
        $stmt->bindValue(3, $mes);
        if($local != ''){
            $stmt->bindValue(4, $local);
        }           
        $stmt->execute();
        $compra = $stmt->fetchColumn();

        return $compra;

    }

    public function obtenerGastoXMes($empresa,$mes='',$local='')
    {

        $sql = "SELECT SUM(g.monto) as total FROM gasto g
                    INNER JOIN empresa_local el ON g.empresa_local_id = el.id
                    INNER JOIN empresa e ON el.empresa_id = e.id
                    WHERE g.estado = 1 AND e.id = ?
                    ";

        $sql .=" AND MONTH(g.fecha) = MONTH(DATE_SUB(NOW(), INTERVAL ? MONTH )) AND YEAR(g.fecha) = YEAR(DATE_SUB(NOW(), INTERVAL ? MONTH )) ";

        if($local != ''){
            $sql .= " AND el.id = ? ";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        $stmt->bindValue(2, $mes);
        $stmt->bindValue(3, $mes);
        if($local != ''){
            $stmt->bindValue(4, $local);
        }          
        $stmt->execute();
        $gasto = $stmt->fetchColumn();

        return $gasto;

    }


    public function registrarSunatF121($factura,$productoXLocal,$tipo,$operacion,$cantidad,$proceso='')
    {
        switch ($operacion) {
            case '01':
                $entrada = 0;
                $salida = $cantidad;
                break;
            case '02':
                $entrada = $cantidad;
                $salida = 0;
                break;
            case '03':
                # code...
                break;
            case '04':
                # code...
                break;
            case '05':
                $entrada = $cantidad;
                $salida = 0;
                break;
            case '06':
                # code...
                break;
            case '07':
                # code...
                break;
            case '08':
                # code...
                break;
            case '09':
                # code...
                break;
            case '10':
                # code...
                break;
            case '11':

                if($proceso != ''){

                    if($proceso = 'entrada'){
                        $entrada = $cantidad;
                        $salida = 0;
                    }elseif($proceso = 'salida'){
                        $entrada = 0;
                        $salida = $cantidad;
                    }

                }

                break;
            case '12':
                # code...
                break;
            case '13':
                # code...
                break;
            case '14':
                # code...
                break;
            case '15':
                # code...
                break;
            case '16':
                # code...
                break;  
                                                                                                                                                                               
            default:
                # code...
                break;
        }



        $SunatF121 = new SunatF121();

        $tipo = $this->em->getRepository('AppBundle:SunatT10')->findOneBy(array('codigo'=>$tipo));
        $SunatF121->setTipo($tipo);
        $operacion = $this->em->getRepository('AppBundle:SunatT12')->findOneBy(array('codigo'=>$operacion));
        $SunatF121->setOperacion($operacion);
        $SunatF121->setFecha($factura->getFecha());        
        $SunatF121->setSerie('0001');
        $num_ticket = ($factura->getTicket())?$factura->getTicket():'';
        $SunatF121->setNumero($num_ticket);
        $SunatF121->setEntrada($entrada);
        $SunatF121->setSalida($salida);
        $SunatF121->setProductoXLocal($productoXLocal);
        $this->em->persist($SunatF121);


        try{
            $this->em->flush();
            return true;

        }catch(\Exception $e) {
            return false;
        }   
    }  

    public function obtenerRegistrosSunatf121($local,$producto='',$ano='',$mes='')
    {

        $localObj = $this->em->getRepository('AppBundle:EmpresaLocal')->find($local);

        $sql = "SELECT t.id,t.local_inicio,t.local_fin,t.subtotal,t.numero_documento,mt.codigo AS codigotraslado,t.fecha AS fecha,factura_compra_id,factura_venta_id
                    FROM transferencia t 
                    INNER JOIN empresa e ON t.empresa_id = e.id
                    INNER JOIN motivo_traslado mt ON t.motivo_traslado_id = mt.id  WHERE t.empresa_id = ? AND t.estado = 1 ";//AND t.estado = 1 

        if($mes == ''){

            $sql .=" AND MONTH(t.fecha) = MONTH(NOW()) AND YEAR(t.fecha) = YEAR(NOW()) ";

        }else{

            $sql .=" AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ? ";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $localObj->getEmpresa()->getId());

        if($mes != ''){

            $stmt->bindValue(2, $mes);
            $stmt->bindValue(3, $ano);

        }

        $stmt->execute();
        $transferencias = $stmt->fetchAll();

        $data = array();
        foreach($transferencias as $i=>$transferencia){

            $ticket = $transferencia['numero_documento'];
            $codigotraslado = $transferencia['codigotraslado'];

            switch ($codigotraslado) {
                case '01':

                    //Venta

                    if($transferencia['factura_venta_id'])
                    {
                        $facturaObj = $this->em->getRepository('AppBundle:FacturaVenta')->find($transferencia['factura_venta_id']);


                        if($facturaObj)
                        {

                            switch ($facturaObj->getDocumento()) {
                                case 'boleta':
                                    $tipo = '03';
                                    break;
                                case 'factura':
                                    $tipo = '01';
                                    break;                                        
                                default:
                                    $tipo = '';
                                    break;
                            }

                            $operacionObj = $this->em->getRepository('AppBundle:SunatT12')->findOneBy(array('codigo'=>$codigotraslado));

                            $operacion = $operacionObj->getCodigo();
                            $fecha = $facturaObj->getFecha();

                            //$partesTicket = explode('-',$facturaObj->getTicket());
                            $serie = $facturaObj->getSerie();//($partesTicket[0])?$partesTicket[0]:'0001';
                            $numero = $facturaObj->getNumero();//($partesTicket[1])?$partesTicket[1]:'1';

                            foreach($facturaObj->getVenta()->getDetalleVenta() as $detalleVenta)
                            {

                                if($detalleVenta->getProductoXLocal())
                                {

                                    $productoId = $detalleVenta->getProductoXLocal()->getId();

                                    if($productoId == $producto){

                                        $cantidad = $detalleVenta->getCantidad();

                                        $data[$i]['tipo'] = $tipo;
                                        $data[$i]['operacion'] = $operacion;
                                        $data[$i]['fecha'] = $fecha;
                                        $data[$i]['serie'] = $serie;
                                        $data[$i]['numero'] = $numero;
                                        $data[$i]['entrada'] = 0;
                                        $data[$i]['salida'] = (double)$cantidad;
                                        $data[$i]['producto_x_local_id'] = $productoId;

                                    }

                                }


                            }

                        }

                    }//Existe factura_venta_id

                    

                    break;
                case '02':

                    //Compra

                    if($transferencia['factura_compra_id'])
                    {

                        $facturaObj = $this->em->getRepository('AppBundle:FacturaCompra')->find($transferencia['factura_compra_id']);

                        if($facturaObj){

                            switch ($facturaObj->getDocumento()) {
                                case 'boleta':
                                    $tipo = '03';
                                    break;
                                case 'factura':
                                    $tipo = '01';
                                    break;                                        
                                default:
                                    $tipo = '12';
                                    break;
                            }

                            $operacionObj = $this->em->getRepository('AppBundle:SunatT12')->findOneBy(array('codigo'=>$codigotraslado));

                            $operacion = $operacionObj->getCodigo();
                            $fecha = $facturaObj->getFecha();

                            $serie = '0000'; 
                            $numero = $facturaObj->getNumeroDocumento();
                            
                            if (strpos($facturaObj->getNumeroDocumento(), '-') !== false) {
                                $partesTicket = explode('-',$facturaObj->getNumeroDocumento());
                                $serie = ($partesTicket[0])?$partesTicket[0]:'0001';//($facturaObj->getSerie()) ? $facturaObj->getSerie() : '0001';//
                                $numero = ($partesTicket[1])?$partesTicket[1]:'1';//($facturaObj->getNumero()) ? $facturaObj->getNumero() : '1';//
                            }

                            foreach($facturaObj->getCompra()->getDetalleCompra() as $detalleCompra)
                            {

                                if($detalleCompra->getProductoXLocal())
                                {

                                    $productoId = $detalleCompra->getProductoXLocal()->getId();

                                    if($productoId == $producto){

                                        $cantidad = $detalleCompra->getCantidad();

                                        $data[$i]['tipo'] = $tipo;
                                        $data[$i]['operacion'] = $operacion;
                                        $data[$i]['fecha'] = $fecha;
                                        $data[$i]['serie'] = $serie;
                                        $data[$i]['numero'] = $numero;
                                        $data[$i]['entrada'] = (double)$cantidad;
                                        $data[$i]['salida'] = 0;
                                        $data[$i]['producto_x_local_id'] = $productoId;

                                    }


                                }                            


                            }

                        }

                    }


                    break;
                case '11':

                    //Transferencia entre almacenes

                    $local_inicio = $transferencia['local_inicio'];
                    $local_fin    = $transferencia['local_fin'];
                    $tipo         = '12';

                    $operacionObj = $this->em->getRepository('AppBundle:SunatT12')->findOneBy(array('codigo'=>$codigotraslado));

                    $operacion = $operacionObj->getCodigo();
                    $fecha = $transferencia['fecha'];

                    $serie = '0001';
                    $numero = $ticket;

                    $transferenciaDetalle = $this->em->getRepository('AppBundle:TransferenciaXProducto')->findBy(array('transferencia'=>$transferencia['id']));

                    foreach($transferenciaDetalle as $detalle)
                    {

                        if($detalle->getProductoXLocal())
                        {

                            $productoId = $detalle->getProductoXLocal()->getId();                        

                            if($productoId == $producto){

                                $cantidad = $detalle->getCantidad();

                                $data[$i]['tipo'] = $tipo;
                                $data[$i]['operacion'] = $operacion;
                                $data[$i]['fecha'] = $fecha;
                                $data[$i]['serie'] = $serie;
                                $data[$i]['numero'] = $numero;

                                $data[$i]['salida'] = 0;
                                $data[$i]['entrada'] = 0;

                                if($local_inicio == $local ){
                                    $data[$i]['salida'] = (double)$cantidad;
                                    $data[$i]['entrada'] = 0;
                                }

                                if($local_fin == $local ){
                                    $data[$i]['entrada'] = $cantidad;
                                    $data[$i]['salida'] = 0;
                                }
                               
                                $data[$i]['producto_x_local_id'] = $productoId;

                            }

                        }

                    }            

                    break;
                                                      
                default:
                    # code...
                    break;
            }





        }



        return $data;

    }

    public function obtenerProductosConMovimientoEnElMes($local,$ano='',$mes='')
    {        

        $sql = " SELECT DISTINCT(pxl.id) AS id FROM transferencia_x_producto txp
                    INNER JOIN transferencia t ON txp.transferencia_id = t.id
                    INNER JOIN producto_x_local pxl ON pxl.id = txp.producto_x_local_id
                    INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
                    WHERE t.estado = 1 AND el.id = ? AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ?  ";//AND pxl.id = 61530


        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $local);
        $stmt->bindValue(2, $mes);
        $stmt->bindValue(3, $ano);

        $stmt->execute();
        $productos = $stmt->fetchAll();


        return $productos;

    }

    public function obtenerSaldoInicialProducto($local,$producto='',$ano='',$mes='')
    {
        $productoXLocal = $this->em->getRepository('AppBundle:ProductoXLocal')->find($producto);
        $stock = ($productoXLocal->getStock()) ? $productoXLocal->getStock(): $productoXLocal->getStockInicial();



        //Obtenemos el stock inicial

        // $fecha_inicio_mes = $ano.'-'.str_pad($mes,2,'0',STR_PAD_LEFT).'-01';

        // $sql = " SELECT 
        //             SUM(
        //             CASE 
        //                 WHEN mt.codigo = '01' THEN -1 * txp.cantidad
        //                 WHEN mt.codigo = '02' THEN txp.cantidad
        //             ELSE 
        //                 CASE
        //                     WHEN pxl.empresa_local_id = t.local_inicio THEN -1 * txp.cantidad
        //                 END
        //             END
        //             ) AS total
        //             FROM transferencia_x_producto txp
        //             INNER JOIN transferencia t ON txp.transferencia_id = t.id
        //             INNER JOIN producto_x_local pxl ON pxl.id = txp.producto_x_local_id
        //             INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
        //             INNER JOIN motivo_traslado mt ON t.motivo_traslado_id = mt.id
        //             WHERE t.estado = 1 AND el.id = ? AND t.fecha < ? AND pxl.id = ?  ";

        // $stmt = $this->conn->prepare($sql);
        // $stmt->bindValue(1, $local);
        // $stmt->bindValue(2,$fecha_inicio_mes);
        // $stmt->bindValue(3, $producto);
        
        // $stmt->execute();
        // $total_inicial = $stmt->fetchColumn();


        $sql = " SELECT t.id,t.fecha,txp.cantidad,txp.precio,t.local_inicio,t.local_fin,mt.codigo FROM transferencia_x_producto txp
                    INNER JOIN transferencia t ON txp.transferencia_id = t.id
                    INNER JOIN producto_x_local pxl ON pxl.id = txp.producto_x_local_id
                    INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
                    INNER JOIN motivo_traslado mt ON t.motivo_traslado_id = mt.id
                    WHERE t.estado = 1 AND el.id = ? AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ? AND pxl.id = ?  ";


        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $local);
        $stmt->bindValue(2, $mes);
        $stmt->bindValue(3, $ano);
        $stmt->bindValue(4, $producto);
        

        $stmt->execute();
        $transferencias = $stmt->fetchAll();

        $stock_inicial = 0;
        foreach($transferencias as $transferencia)
        {

            switch ($transferencia['codigo']) {
                case '01':
                    $stock_inicial  = $stock_inicial + $transferencia['cantidad'];
                    break;
                case '02':
                    $stock_inicial  = $stock_inicial - $transferencia['cantidad'];
                    break;
                case '11':

                    if($local == $transferencia['local_inicio']){

                        $stock_inicial  = $stock_inicial + $transferencia['cantidad'];

                    }

                    if($local == $transferencia['local_fin']){

                        $stock_inicial  = $stock_inicial - $transferencia['cantidad'];

                    }

                    break;                                     
                default:
                    # code...
                    break;
            }

        }

        $stock_inicial = $stock - $stock_inicial;


        return $stock_inicial;

    }


    public function registrarSunatF131($factura,$productoXLocal,$tipo,$operacion,$cantidad,$costo,$proceso='')
    {
        switch ($operacion) {
            case '01':
                $entrada = 0;
                $salida = $cantidad;
                break;
            case '02':
                $entrada = $cantidad;
                $salida = 0;
                break;
            case '03':
                # code...
                break;
            case '04':
                # code...
                break;
            case '05':
                $entrada = $cantidad;
                $salida = 0;
                break;
            case '06':
                # code...
                break;
            case '07':
                # code...
                break;
            case '08':
                # code...
                break;
            case '09':
                # code...
                break;
            case '10':
                # code...
                break;
            case '11':

                if($proceso != ''){

                    if($proceso = 'entrada'){
                        $entrada = $cantidad;
                        $salida = 0;
                    }elseif($proceso = 'salida'){
                        $entrada = 0;
                        $salida = $cantidad;
                    }

                }

                break;
            case '12':
                # code...
                break;
            case '13':
                # code...
                break;
            case '14':
                # code...
                break;
            case '15':
                # code...
                break;
            case '16':
                # code...
                break;  
                                                                                                                                                                               
            default:
                # code...
                break;
        }



        $SunatF131 = new SunatF131();

        $tipo = $this->em->getRepository('AppBundle:SunatT10')->findOneBy(array('codigo'=>$tipo));
        $SunatF131->setTipo($tipo);
        $operacion = $this->em->getRepository('AppBundle:SunatT12')->findOneBy(array('codigo'=>$operacion));
        $SunatF131->setOperacion($operacion);
        $SunatF131->setFecha($factura->getFecha());        
        $SunatF131->setSerie('0001');
        $num_ticket = ($factura->getTicket())?$factura->getTicket():$factura->getNumeroDocumento();
        $SunatF131->setNumero($num_ticket);
        $SunatF131->setEntrada($entrada);
        $SunatF131->setSalida($salida);
        $SunatF131->setCostoUnitario($costo);
        $SunatF131->setProductoXLocal($productoXLocal);
        $this->em->persist($SunatF131);


        try{
            $this->em->flush();
            return true;

        }catch(\Exception $e) {
            return false;
        }   
    }  


    public function obtenerRegistrosSunatf131($local,$producto='',$ano='',$mes='')
    {

        $localObj = $this->em->getRepository('AppBundle:EmpresaLocal')->find($local);

        $sql = "SELECT t.id,t.local_inicio,t.local_fin,t.subtotal,t.numero_documento,mt.codigo AS codigotraslado,t.fecha AS fecha
                    FROM transferencia t 
                    INNER JOIN empresa e ON t.empresa_id = e.id
                    INNER JOIN motivo_traslado mt ON t.motivo_traslado_id = mt.id  WHERE t.empresa_id = ? ";

        if($mes == ''){

            $sql .=" AND MONTH(t.fecha) = MONTH(NOW()) AND YEAR(t.fecha) = YEAR(NOW()) ";

        }else{

            $sql .=" AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ? ";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $localObj->getEmpresa()->getId());

        if($mes != ''){

            $stmt->bindValue(2, $mes);
            $stmt->bindValue(3, $ano);

        }

        $stmt->execute();
        $transferencias = $stmt->fetchAll();

        $data = array();
        foreach($transferencias as $i=>$transferencia){

            $ticket = $transferencia['numero_documento'];
            $codigotraslado = $transferencia['codigotraslado'];

            switch ($codigotraslado) {
                case '01':

                    //Venta

                    $facturaObj = $this->em->getRepository('AppBundle:FacturaVenta')->findOneBy(array('ticket'=>$ticket,'local'=>$local));

                    if($facturaObj){

                        if($facturaObj->getVenta()->getEstado()){


                            switch ($facturaObj->getDocumento()) {
                                case 'boleta':
                                    $tipo = '03';
                                    break;
                                case 'factura':
                                    $tipo = '01';
                                    break;                                        
                                default:
                                    $tipo = '';
                                    break;
                            }

                            $operacionObj = $this->em->getRepository('AppBundle:SunatT12')->findOneBy(array('codigo'=>$codigotraslado));

                            $operacion = $operacionObj->getCodigo();
                            $fecha = $facturaObj->getFecha();

                            $partesTicket = explode('-',$facturaObj->getTicket());

                            $serie = ($partesTicket[0])?$partesTicket[0]:'0001';
                            $numero = ($partesTicket[1])?$partesTicket[1]:'1';

                            foreach($facturaObj->getVenta()->getDetalleVenta() as $detalleVenta)
                            {

                                if($detalleVenta->getProductoXLocal())
                                {

                                    $productoId = $detalleVenta->getProductoXLocal()->getId();

                                    if($productoId == $producto){

                                        $cantidad = $detalleVenta->getCantidad();
                                        $subtotal = $detalleVenta->getSubtotal();

                                        $valorTipoCambio = 1;

                                        if($facturaObj->getVenta()->getVentaFormaPago()[0]->getMoneda()->getId() == 2 )
                                        {
                                            $valorTipoCambio = ($facturaObj->getVenta()->getVentaFormaPago()[0]->getValorTipoCambio()) ? $facturaObj->getVenta()->getVentaFormaPago()[0]->getValorTipoCambio() : 1;
                                        }


                                        $costoUnitario = ($cantidad > 0) ? ($subtotal/$cantidad) * $valorTipoCambio : 0;



                                        $data[$i]['tipo'] = $tipo;
                                        $data[$i]['operacion'] = $operacion;
                                        $data[$i]['fecha'] = $fecha;
                                        $data[$i]['serie'] = $serie;
                                        $data[$i]['numero'] = $numero;
                                        $data[$i]['entrada'] = null;
                                        $data[$i]['salida'] = $cantidad;
                                        $data[$i]['costoUnitario'] = $costoUnitario;
                                        $data[$i]['cantidad'] = $cantidad;
                                        $data[$i]['producto_x_local_id'] = $productoId;

                                    }

                                }                            

                            }

                        }

                    }

                    break;
                case '02':

                    //Compra

                    $facturaObj = $this->em->getRepository('AppBundle:FacturaCompra')->findOneBy(array('numero_documento'=>$ticket,'local'=>$local));

                    if($facturaObj){

                        if($facturaObj->getEstado()){

                            switch ($facturaObj->getDocumento()) {
                                case 'boleta':
                                    $tipo = '03';
                                    break;
                                case 'factura':
                                    $tipo = '01';
                                    break;                                        
                                default:
                                    $tipo = '12';
                                    break;
                            }

                            $operacionObj = $this->em->getRepository('AppBundle:SunatT12')->findOneBy(array('codigo'=>$codigotraslado));

                            $operacion = $operacionObj->getCodigo();
                            $fecha = $facturaObj->getFecha();


                            $serie = '0000'; 
                            $numero = $facturaObj->getNumeroDocumento();

                            if (strpos($facturaObj->getNumeroDocumento(), '-') !== false) {
                                $partesTicket = explode('-',$facturaObj->getNumeroDocumento());
                                $serie = ($partesTicket[0])?$partesTicket[0]:'0001';
                                $numero = ($partesTicket[1])?$partesTicket[1]:'1';
                            }



                            foreach($facturaObj->getCompra()->getDetalleCompra() as $detalleCompra)
                            {

                                if($detalleCompra->getProductoXLocal())
                                {

                                    $productoId = $detalleCompra->getProductoXLocal()->getId();

                                    if($productoId == $producto){

                                        $cantidad = $detalleCompra->getCantidad();
                                        $subtotal = $detalleCompra->getSubtotal();
                                        $costoUnitario = ($cantidad > 0) ? $subtotal/$cantidad : 0;

                                        $data[$i]['tipo'] = $tipo;
                                        $data[$i]['operacion'] = $operacion;
                                        $data[$i]['fecha'] = $fecha;
                                        $data[$i]['serie'] = $serie;
                                        $data[$i]['numero'] = $numero;
                                        $data[$i]['entrada'] = $cantidad;
                                        $data[$i]['salida'] = null;
                                        $data[$i]['cantidad'] = $cantidad;
                                        $data[$i]['costoUnitario'] = $costoUnitario;
                                        $data[$i]['producto_x_local_id'] = $productoId;

                                    }

                                }                            

                            }

                        }

                    }

                    break;
                case '11':

                    //Transferencia entre almacenes

                    $local_inicio = $transferencia['local_inicio'];
                    $local_fin    = $transferencia['local_fin'];
                    $tipo         = '12';

                    $operacionObj = $this->em->getRepository('AppBundle:SunatT12')->findOneBy(array('codigo'=>$codigotraslado));

                    $operacion = $operacionObj->getCodigo();
                    $fecha = $transferencia['fecha'];

                    $serie = '0001';
                    $numero = $ticket;

                    $transferenciaDetalle = $this->em->getRepository('AppBundle:TransferenciaXProducto')->findBy(array('transferencia'=>$transferencia['id']));

                    foreach($transferenciaDetalle as $detalle)
                    {
                        if($detalle->getProductoXLocal())
                        {

                            $productoId = $detalle->getProductoXLocal()->getId();

                            if($productoId == $producto){

                                $cantidad = $detalle->getCantidad();
                                $precio = $detalle->getPrecio();

                                $data[$i]['tipo'] = $tipo;
                                $data[$i]['operacion'] = $operacion;
                                $data[$i]['fecha'] = $fecha;
                                $data[$i]['serie'] = $serie;
                                $data[$i]['numero'] = $numero;

                                $data[$i]['salida'] = null;
                                $data[$i]['entrada'] = null;

                                if($local_inicio == $local ){
                                    $data[$i]['salida'] = $cantidad;
                                    $data[$i]['entrada'] = null;
                                }

                                if($local_fin == $local ){
                                    $data[$i]['entrada'] = $cantidad;
                                    $data[$i]['salida'] = null;
                                }
                                $data[$i]['cantidad'] = $cantidad;
                                $data[$i]['costoUnitario'] = $precio;
                               
                                $data[$i]['producto_x_local_id'] = $productoId;

                            }

                        }                        

                    }            

                    break;
                                                      
                default:
                    # code...
                    break;
            }





        }



        return $data;

    }

    public function generarNumeroDocumento($tabla,$local,$doc='',$empresa='')
    {

        $localObj = $this->em->getRepository('AppBundle:EmpresaLocal')->find($local);
        $serie = '';

        switch ($doc) {
            case 'factura':
                $serie = $localObj->getSerieFactura();

                break;
            case 'boleta':
                $serie = $localObj->getSerieBoleta();

                break;
            case 'guia':
                $serie = $localObj->getPrefijoVoucher();

                break;                            
            default:
                $serie = $localObj->getPrefijoVoucher();

                break;
        }

        
        $sql = "SELECT COUNT(*) as total FROM ";
        $sql .= $tabla.' f';
        $sql .= " INNER JOIN empresa_local el ON f.empresa_local_id = el.id ";
        $sql .= " INNER JOIN empresa e ON el.empresa_id = e.id ";
        $sql .= " WHERE f.tipo_id = 2  ";

        if($empresa != '')
        {
            $sql .= " AND e.id = ? ";
        }
        else
        {

            $sql .= " AND el.id = ?  ";
        }

        if($doc != '')
        {
            $sql .= "  AND f.documento = ? ";            
        }

        if($serie != '')
        {
            $sql .= "  AND f.serie = ? ";            
        }

        // if($localObj->getFacturacionElectronica())
        // {
        //     if($tabla == 'factura_venta' ){

        //         if($doc == 'factura' || $doc == 'boleta'){

        //             $sql .= "  AND f.enlacepdf IS NOT NULL ";

        //         }
                
        //     }
            
        // }

        $stmt = $this->conn->prepare($sql);

        if($empresa != '')
        {

            $stmt->bindValue(1, $empresa);

        }
        else
        {

            $stmt->bindValue(1, $local);
        }


        if($doc != '')
        {
            $stmt->bindValue(2, $doc);
        }

        if($serie != '')
        {
            $stmt->bindValue(3, $serie);
        }

        $stmt->execute();
        $total = $stmt->fetchColumn();

        return $total;
    }

    public function generarNumeroProforma($tabla,$local)
    {
        
        $sql = "SELECT COUNT(*) as total FROM ";
        $sql .= $tabla;
        $sql .= " WHERE empresa_local_id = ?  AND numero_proforma IS NOT NULL ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $local);
        $stmt->execute();
        $total = $stmt->fetchColumn();

        return $total;
    }

    public function generarNumeroAnulacion($local)
    {
        
        $sql = "SELECT COUNT(*) as total FROM factura_venta fv ";
        $sql .= " INNER JOIN venta v  ON fv.venta_id = v.id ";
        $sql .= " WHERE fv.empresa_local_id = ?  AND v.estado = 0 ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $local);
        $stmt->execute();
        $total = $stmt->fetchColumn();

        return $total;
    }

    public function generarArchivoJson($facturaVenta,$local)
    {

        $detalleVenta = array();
        $i=0;
        $total_gravada = 0;
        $total_igv = 0;
        $total = 0;
        foreach($facturaVenta->getVenta()->getDetalleVenta() as $detalle){

            if($detalle->getProductoXLocal())
            {
                $codigo_producto_sunat   = ($detalle->getProductoXLocal()->getProducto()->getCodigoSunat()) ? $detalle->getProductoXLocal()->getProducto()->getCodigoSunat()->getCodigo() : '31162800';


                if(!$facturaVenta->getIncluirIgv())
                {


                    $cantidadLinea      = $detalle->getCantidad();
                    $valor_unitario     = $detalle->getPrecio();
                    $precio_unitario    = ($cantidadLinea > 0) ? $valor_unitario * 1.18 : 0;

                    $subt               = $valor_unitario*$cantidadLinea;
                    
                    $totalLinea         = $cantidadLinea*$precio_unitario;
                    $igv                = $cantidadLinea*$valor_unitario*0.18;

                    $descripcion = ($detalle->getDescripcion())? $detalle->getProductoXLocal()->getProducto()->getNombre().' . '.$detalle->getDescripcion():$detalle->getProductoXLocal()->getProducto()->getNombre();

                    $unidad_medida = "NIU";
                    if($detalle->getProductoXLocal()->getProducto()->getTipo() == 'servicio')
                    {
                       $unidad_medida = "ZZ";     
                    }                    
                    
                    $detalleVenta[$i]['unidad_de_medida'] = "".$unidad_medida."";
                    $detalleVenta[$i]['codigo'] = "".$detalle->getProductoXLocal()->getProducto()->getCodigo()."";
                    $detalleVenta[$i]['descripcion'] = "".$descripcion."";
                    $detalleVenta[$i]['cantidad'] = "".$detalle->getCantidad()."";
                    $detalleVenta[$i]['valor_unitario'] = "".$valor_unitario."";
                    $detalleVenta[$i]['precio_unitario'] = "".$precio_unitario."";
                    $detalleVenta[$i]['descuento'] = "";
                    $detalleVenta[$i]['subtotal'] = "".$subt."";
                    $detalleVenta[$i]['tipo_de_igv'] = "1";
                    $detalleVenta[$i]['igv'] = "".$igv."";
                    $detalleVenta[$i]['total'] = "".$totalLinea."";
                    $detalleVenta[$i]['anticipo_regularizacion'] = "false";
                    $detalleVenta[$i]['anticipo_documento_serie'] = "";
                    $detalleVenta[$i]['anticipo_documento_numero'] = "";
                    $detalleVenta[$i]['codigo_producto_sunat'] = "".$codigo_producto_sunat."";

                    $i++;
                    $total_igv  = $total_igv + $igv;
                    $total      = $total + $precio_unitario*$cantidadLinea;


                }
                else
                {

                    $cantidadLinea      = $detalle->getCantidad();
                    $valor_unitario     = ($cantidadLinea > 0) ? ($detalle->getSubtotal()/$cantidadLinea)/1.18 : 0;
                    $subt               = $valor_unitario*$cantidadLinea;
                    
                    $precio_unitario    = $valor_unitario + $valor_unitario*0.18;
                    $totalLinea         = $cantidadLinea*$precio_unitario;
                    $igv                = $subt*0.18;


                    $descripcion = ($detalle->getDescripcion())? $detalle->getProductoXLocal()->getProducto()->getNombre().' . '.$detalle->getDescripcion():$detalle->getProductoXLocal()->getProducto()->getNombre();
                    

                    $unidad_medida = "NIU";
                    if($detalle->getProductoXLocal()->getProducto()->getTipo() == 'servicio')
                    {
                       $unidad_medida = "ZZ";     
                    }                    

                    $detalleVenta[$i]['unidad_de_medida'] = "".$unidad_medida."";
                    $detalleVenta[$i]['codigo'] = "".$detalle->getProductoXLocal()->getProducto()->getCodigo()."";
                    $detalleVenta[$i]['descripcion'] = "".$descripcion."";
                    $detalleVenta[$i]['cantidad'] = "".$detalle->getCantidad()."";
                    $detalleVenta[$i]['valor_unitario'] = "".$valor_unitario."";
                    $detalleVenta[$i]['precio_unitario'] = "".$precio_unitario."";
                    $detalleVenta[$i]['descuento'] = "";
                    $detalleVenta[$i]['subtotal'] = "".$subt."";
                    $detalleVenta[$i]['tipo_de_igv'] = "1";
                    $detalleVenta[$i]['igv'] = "".$igv."";
                    $detalleVenta[$i]['total'] = "".$totalLinea."";
                    $detalleVenta[$i]['anticipo_regularizacion'] = "false";
                    $detalleVenta[$i]['anticipo_documento_serie'] = "";
                    $detalleVenta[$i]['anticipo_documento_numero'] = "";
                    $detalleVenta[$i]['codigo_producto_sunat'] = "".$codigo_producto_sunat."";

                    $i++;
                    $total_igv  = $total_igv + $igv;
                    $total      = $total + $detalle->getSubtotal();

                }

            
            }




        }



        $partesticket = explode("-", $facturaVenta->getTicket());//$this->generarNumero($local);//
        $numero = $partesticket[1];

        $localObj = $this->em->getRepository('AppBundle:EmpresaLocal')->find($local);

        switch ($facturaVenta->getDocumento()) {
            case 'factura':
                $serie = $localObj->getSerieFactura();
                $tipo_de_comprobante = 1;
                $cliente_tipo_de_documento = 6;
                break;
            case 'boleta':
                $serie = $localObj->getSerieBoleta();
                $cliente_tipo_de_documento = 1;
                $tipo_de_comprobante = 2;
                break;            
            default:
                $serie = '';
                $tipo_de_comprobante = '';
                break;
        }

        $total_gravada = $total/1.18;
        $total_igv = $total - $total_gravada;

        $fecha_emision = ($facturaVenta->getFecha()) ? $facturaVenta->getFecha()->format('d-m-Y') : date('d-m-Y');

        $guiasArray = array();
        if($facturaVenta->getNumeroGuiaremision() != ''){

            $guias = explode(",", $facturaVenta->getNumeroGuiaremision());
            
            $z=0;
            foreach($guias as $h=>$guia){

                $guiasArray[$z]['guia_tipo'] = 1;
                $guiasArray[$z]['guia_serie_numero'] = $guia;

                $z++;
            }

        }

        $cliente_numero_documento = $facturaVenta->getCliente()->getRuc();

        if($cliente_tipo_de_documento == 1){

            if(strlen($facturaVenta->getCliente()->getRuc()) < 8)
            {
                $cliente_tipo_de_documento = '-';
                //$cliente_numero_documento = '---';
            }
            elseif(strlen($facturaVenta->getCliente()->getRuc()) == 8)
            {
                $cliente_tipo_de_documento = 1;
                //$cliente_numero_documento = '---';
            }
            elseif(strlen($facturaVenta->getCliente()->getRuc()) == 11)
            {
                $cliente_tipo_de_documento = 6;
                //$cliente_numero_documento = '---';
            }
            else
            {
                $cliente_tipo_de_documento = '-';
            }

        }

        if($facturaVenta->getCliente()->getRuc() == '---'){

            $cliente_tipo_de_documento = '-';

        }

        $detraccion = 'false';
        if($facturaVenta->getDetraccion()){

            $detraccion = 'true';

        }

        $condicion_de_pago = '';
        $ventaFormaPago = $facturaVenta->getVenta()->getVentaFormaPago()[0];

        if($ventaFormaPago){

            $condicion_de_pago = strtoupper($ventaFormaPago->getFormaPago()->getNombre());

            if($ventaFormaPago->getFormaPago()->getCodigo() == '2'){
                $condicion_de_pago .= ' A '.$ventaFormaPago->getNumeroDias().' DIAS';
            }
        }

        $moneda = '1';
        if($facturaVenta->getVenta()->getVentaFormaPago()[0]){

            $moneda = ($facturaVenta->getVenta()->getVentaFormaPago()[0]->getMoneda())? $facturaVenta->getVenta()->getVentaFormaPago()[0]->getMoneda()->getId():'1';

        }

        $tipo_cambio_valor = '';
        if($moneda == 2){

            $tipoCambioObj = $this->em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$facturaVenta->getLocal()->getEmpresa()->getId(),'fecha'=>$facturaVenta->getFecha()));
            $tipo_cambio_valor = $tipoCambioObj->getVenta();

        }




        $data = array(
            "operacion"                         => "generar_comprobante",
            "tipo_de_comprobante"               => "".$tipo_de_comprobante."",
            "serie"                             => "".$serie."",
            "numero"                            => $numero,
            "sunat_transaction"                 => "1",
            "cliente_tipo_de_documento"         => "".$cliente_tipo_de_documento."",
            "cliente_numero_de_documento"       => "".$cliente_numero_documento."",
            "cliente_denominacion"              => "".$facturaVenta->getCliente()->getRazonSocial()."",
            "cliente_direccion"                 => "".$facturaVenta->getCliente()->getDireccion()."",
            "cliente_email"                     => "",
            "cliente_email_1"                   => "",
            "cliente_email_2"                   => "",
            "fecha_de_emision"                  => $fecha_emision,
            "fecha_de_vencimiento"              => "",
            "moneda"                            => "".$moneda."",
            "tipo_de_cambio"                    => "".$tipo_cambio_valor."",
            "porcentaje_de_igv"                 => "18.00",
            "descuento_global"                  => "",
            "descuento_global"                  => "",
            "total_descuento"                   => "",
            "total_anticipo"                    => "",
            "total_gravada"                     => "".round($total_gravada,2)."",
            "total_inafecta"                    => "",
            "total_exonerada"                   => "",
            "total_igv"                         => "".round($total_igv,2)."",
            "total_gratuita"                    => "",
            "total_otros_cargos"                => "",
            "total"                             => "".round($total,2)."",
            "percepcion_tipo"                   => "",
            "percepcion_base_imponible"         => "",
            "total_percepcion"                  => "",
            "total_incluido_percepcion"         => "",
            "detraccion"                        => "".$detraccion."",
            "observaciones"                     => "",
            "documento_que_se_modifica_tipo"    => "",
            "documento_que_se_modifica_serie"   => "",
            "documento_que_se_modifica_numero"  => "",
            "tipo_de_nota_de_credito"           => "",
            "tipo_de_nota_de_debito"            => "",
            "enviar_automaticamente_a_la_sunat" => "true",
            "enviar_automaticamente_al_cliente" => "false",
            "codigo_unico"                      => "",
            "condiciones_de_pago"               => "".$condicion_de_pago."",
            "medio_de_pago"                     => "",
            "placa_vehiculo"                    => "",
            "orden_compra_servicio"             => "",
            "tabla_personalizada_codigo"        => "",
            "formato_de_pdf"                    => "",
            "items" => $detalleVenta
        );

        if($facturaVenta->getNumeroGuiaremision() != ''){
            $data["guias"] = $guiasArray;
        }


        $data_json = json_encode($data);

        return $data_json;


    }

    public function generarArchivoPerdidoJson($facturaVenta)
    {

        
        $detalleVenta[0]['unidad_de_medida'] = "NIU";
        $detalleVenta[0]['codigo'] = "777777";
        $detalleVenta[0]['descripcion'] = "Corrección de Sistema - Generación de correlativo faltante";
        $detalleVenta[0]['cantidad'] = "1";
        $detalleVenta[0]['valor_unitario'] = "1";
        $detalleVenta[0]['precio_unitario'] = "1";
        $detalleVenta[0]['descuento'] = "";
        $detalleVenta[0]['subtotal'] = "1";
        $detalleVenta[0]['tipo_de_igv'] = "1";
        $detalleVenta[0]['igv'] = "0.18";
        $detalleVenta[0]['total'] = "1";
        $detalleVenta[0]['anticipo_regularizacion'] = "false";
        $detalleVenta[0]['anticipo_documento_serie'] = "";
        $detalleVenta[0]['anticipo_documento_numero'] = "";

        $total = 1;


        $partesticket = explode("-", $facturaVenta->getTicket());
        $numero = $partesticket[1];

        $localObj = $facturaVenta->getLocal();

        switch ($facturaVenta->getDocumento()) {
            case 'factura':
                $serie = $localObj->getSerieFactura();
                $tipo_de_comprobante = 1;
                $cliente_tipo_de_documento = 6;
                break;
            case 'boleta':
                $serie = $localObj->getSerieBoleta();
                $cliente_tipo_de_documento = 1;
                $tipo_de_comprobante = 2;
                break;            
            default:
                $serie = '';
                $tipo_de_comprobante = '';
                break;
        }

        $total_gravada = $total/1.18;
        $total_igv = $total - $total_gravada;

        $fecha_emision = ($facturaVenta->getFecha()) ? $facturaVenta->getFecha()->format('d-m-Y') : date('d-m-Y');


        if($facturaVenta->getCliente()->getRuc() == '---'){

            $cliente_tipo_de_documento = '-';

        }

        $detraccion = 'false';
        if($facturaVenta->getDetraccion()){

            $detraccion = 'true';

        }

        $condicion_de_pago = '';
        $ventaFormaPago = $facturaVenta->getVenta()->getVentaFormaPago()[0];

        if($ventaFormaPago){

            $condicion_de_pago = strtoupper($ventaFormaPago->getFormaPago()->getNombre());

            if($ventaFormaPago->getFormaPago()->getCodigo() == '2'){
                $condicion_de_pago .= ' A '.$ventaFormaPago->getNumeroDias().' DIAS';
            }
        }

        $moneda = '1';
        if($facturaVenta->getVenta()->getVentaFormaPago()[0]){

            $moneda = ($facturaVenta->getVenta()->getVentaFormaPago()[0]->getMoneda())? $facturaVenta->getVenta()->getVentaFormaPago()[0]->getMoneda()->getId():'1';

        }

        $tipo_cambio_valor = '';
        if($moneda == 2){

            $tipoCambioObj = $this->em->getRepository('AppBundle:TipoCambio')->findOneBy(array('empresa'=>$facturaVenta->getLocal()->getEmpresa()->getId(),'fecha'=>$facturaVenta->getFecha()));
            $tipo_cambio_valor = $tipoCambioObj->getVenta();

        }


        $data = array(
            "operacion"                         => "generar_comprobante",
            "tipo_de_comprobante"               => "".$tipo_de_comprobante."",
            "serie"                             => "".$serie."",
            "numero"                            => $numero,
            "sunat_transaction"                 => "1",
            "cliente_tipo_de_documento"         => "".$cliente_tipo_de_documento."",
            "cliente_numero_de_documento"       => "".$facturaVenta->getCliente()->getRuc()."",
            "cliente_denominacion"              => "".$facturaVenta->getCliente()->getRazonSocial()."",
            "cliente_direccion"                 => "".$facturaVenta->getCliente()->getDireccion()."",
            "cliente_email"                     => "",
            "cliente_email_1"                   => "",
            "cliente_email_2"                   => "",
            "fecha_de_emision"                  => $fecha_emision,
            "fecha_de_vencimiento"              => "",
            "moneda"                            => "".$moneda."",
            "tipo_de_cambio"                    => "".$tipo_cambio_valor."",
            "porcentaje_de_igv"                 => "18.00",
            "descuento_global"                  => "",
            "descuento_global"                  => "",
            "total_descuento"                   => "",
            "total_anticipo"                    => "",
            "total_gravada"                     => "".round($total_gravada,2)."",
            "total_inafecta"                    => "",
            "total_exonerada"                   => "",
            "total_igv"                         => "".round($total_igv,2)."",
            "total_gratuita"                    => "",
            "total_otros_cargos"                => "",
            "total"                             => "".round($total,2)."",
            "percepcion_tipo"                   => "",
            "percepcion_base_imponible"         => "",
            "total_percepcion"                  => "",
            "total_incluido_percepcion"         => "",
            "detraccion"                        => "".$detraccion."",
            "observaciones"                     => "",
            "documento_que_se_modifica_tipo"    => "",
            "documento_que_se_modifica_serie"   => "",
            "documento_que_se_modifica_numero"  => "",
            "tipo_de_nota_de_credito"           => "",
            "tipo_de_nota_de_debito"            => "",
            "enviar_automaticamente_a_la_sunat" => "true",
            "enviar_automaticamente_al_cliente" => "false",
            "codigo_unico"                      => "",
            "condiciones_de_pago"               => "".$condicion_de_pago."",
            "medio_de_pago"                     => "",
            "placa_vehiculo"                    => "",
            "orden_compra_servicio"             => "",
            "tabla_personalizada_codigo"        => "",
            "formato_de_pdf"                    => "",
            "items" => $detalleVenta
        );


        $data_json = json_encode($data);

        return $data_json;


    }


    public function enviarArchivoJson($data_json,$localObj)
    {
        //Invocamos el servicio de NUBEFACT
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$localObj->getUrlFacturacion());
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Token token="'.$localObj->getTokenFacturacion().'"',
            'Content-Type: application/json',
            )
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta  = curl_exec($ch);
        curl_close($ch);

        return $respuesta;        
        
    }

    public function generarArchivoJsonNotaCredito($facturaVenta,$local,$msj)
    {

        $detalleVenta = array();
        $i=0;
        $total_gravada = 0;
        $total_igv = 0;
        $total = 0;
        foreach($facturaVenta->getVenta()->getDetalleVenta() as $detalle){

            $cantidadLinea      = $detalle->getCantidad();
            $valor_unitario     = ($cantidadLinea > 0) ? ($detalle->getSubtotal()/$cantidadLinea)/1.18 : 0;//($detalle->getSubtotal()/$detalle->getCantidad())/1.18;
            $subt               = $valor_unitario*$cantidadLinea;
            
            $precio_unitario    = $valor_unitario + $valor_unitario*0.18;
            $totalLinea         = $cantidadLinea*$precio_unitario;//$detalle->getSubtotal();
            $igv                = $subt*0.18;


            $descripcion = ($detalle->getDescripcion())? $detalle->getProductoXLocal()->getProducto()->getNombre().' . '.$detalle->getDescripcion():$detalle->getProductoXLocal()->getProducto()->getNombre();
            

            $detalleVenta[$i]['unidad_de_medida'] = "NIU";
            $detalleVenta[$i]['codigo'] = "".$detalle->getProductoXLocal()->getProducto()->getCodigo()."";
            $detalleVenta[$i]['descripcion'] = "".$descripcion."";
            $detalleVenta[$i]['cantidad'] = "".$detalle->getCantidad()."";
            $detalleVenta[$i]['valor_unitario'] = "".$valor_unitario."";
            $detalleVenta[$i]['precio_unitario'] = "".$precio_unitario."";
            $detalleVenta[$i]['descuento'] = "";
            $detalleVenta[$i]['subtotal'] = "".$subt."";
            $detalleVenta[$i]['tipo_de_igv'] = "1";
            $detalleVenta[$i]['igv'] = "".$igv."";
            $detalleVenta[$i]['total'] = "".$totalLinea."";
            $detalleVenta[$i]['anticipo_regularizacion'] = "false";
            $detalleVenta[$i]['anticipo_documento_serie'] = "";
            $detalleVenta[$i]['anticipo_documento_numero'] = "";

            $i++;
            $total_igv  = $total_igv + $igv;
            $total      = $total + $detalle->getSubtotal();
            $total_gravada = $total_gravada + $subt;
        }



        $partesticket = explode("-", $facturaVenta->getTicket());
        $numero = $partesticket[1];

        $localObj = $this->em->getRepository('AppBundle:EmpresaLocal')->find($local);

        switch ($facturaVenta->getDocumento()) {
            case 'factura':
                $serie = $localObj->getSerieFactura();
                $tipo_de_comprobante = 1;
                $cliente_tipo_de_documento = 6;
                $cliente_numero_documento = $facturaVenta->getCliente()->getRuc();
                break;
            case 'boleta':
                $serie = $localObj->getSerieBoleta();
                $cliente_tipo_de_documento = 1;
                $tipo_de_comprobante = 2;
                $cliente_numero_documento = ($facturaVenta->getCliente()->getRuc() != '---') ? $facturaVenta->getCliente()->getRuc() : '00000000';
                break;            
            default:
                $serie = '';
                $tipo_de_comprobante = '';
                $cliente_numero_documento = '';
                break;
        }

        $total_gravada = $total/1.18;
        $total_igv = $total - $total_gravada;

        $data = array(
            "operacion"                         => "generar_comprobante",
            "tipo_de_comprobante"               => 3,
            "serie"                             => "".$serie."",
            "numero"                            => $numero,
            "sunat_transaction"                 => "1",
            "cliente_tipo_de_documento"         => "".$cliente_tipo_de_documento."",
            "cliente_numero_de_documento"       => "".$cliente_numero_documento."",
            "cliente_denominacion"              => "".$facturaVenta->getCliente()->getRazonSocial()."",
            "cliente_direccion"                 => "".$facturaVenta->getCliente()->getDireccion()."",
            "cliente_email"                     => "",
            "cliente_email_1"                   => "",
            "cliente_email_2"                   => "",
            "fecha_de_emision"                  => date('d-m-Y'),
            "fecha_de_vencimiento"              => "",
            "moneda"                            => "1",
            "tipo_de_cambio"                    => "",
            "porcentaje_de_igv"                 => "18.00",
            "descuento_global"                  => "",
            "descuento_global"                  => "",
            "total_descuento"                   => "",
            "total_anticipo"                    => "",
            "total_gravada"                     => "".round($total_gravada,2)."",
            "total_inafecta"                    => "",
            "total_exonerada"                   => "",
            "total_igv"                         => "".round($total_igv,2)."",
            "total_gratuita"                    => "",
            "total_otros_cargos"                => "",
            "total"                             => "".round($total,2)."",
            "percepcion_tipo"                   => "",
            "percepcion_base_imponible"         => "",
            "total_percepcion"                  => "",
            "total_incluido_percepcion"         => "",
            "detraccion"                        => "false",
            "observaciones"                     => "".$msj."",
            "documento_que_se_modifica_tipo"    => "".$tipo_de_comprobante."",
            "documento_que_se_modifica_serie"   => "".$serie."",
            "documento_que_se_modifica_numero"  => $numero,
            "tipo_de_nota_de_credito"           => "1",
            "tipo_de_nota_de_debito"            => "",
            "enviar_automaticamente_a_la_sunat" => "true",
            "enviar_automaticamente_al_cliente" => "false",
            "codigo_unico"                      => "",
            "condiciones_de_pago"               => "",
            "medio_de_pago"                     => "",
            "placa_vehiculo"                    => "",
            "orden_compra_servicio"             => "",
            "tabla_personalizada_codigo"        => "",
            "formato_de_pdf"                    => "",
            "items" => $detalleVenta
        );


        $data_json = json_encode($data);

        return $data_json;

    }

    public function enviarArchivoJsonNotaCredito($data_json,$localObj)
    {
        //Invocamos el servicio de NUBEFACT
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$localObj->getUrlFacturacion());
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Token token="'.$localObj->getTokenFacturacion().'"',
            'Content-Type: application/json',
            )
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta  = curl_exec($ch);
        curl_close($ch);

        return $respuesta;        
        
    }

    public function generarConsultaArchivoJson($facturaVenta)
    {

        switch ($facturaVenta->getDocumento()) {
            case 'factura':
                $serie = $facturaVenta->getLocal()->getSerieFactura();
                $tipo_de_comprobante = 1;
                break;
            case 'boleta':
                $serie = $facturaVenta->getLocal()->getSerieBoleta();
                $tipo_de_comprobante = 2;
                break;            
            default:
                $serie = '';
                $tipo_de_comprobante = '';
                break;
        }


        $partesticket = explode("-", $facturaVenta->getTicket());
        $numero = $partesticket[1];

        $data = array(
            "operacion"                         => "consultar_comprobante",
            "tipo_de_comprobante"               => "".$tipo_de_comprobante."",
            "serie"                             => "".$serie."",
            "numero"                            => $numero
        );

        $data_json = json_encode($data);

        return $data_json;


    }

    public function enviarConsultaArchivoJson($data_json,$url,$token)
    {
        //Invocamos el servicio de NUBEFACT
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Token token="'.$token.'"',
            'Content-Type: application/json',
            )
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta  = curl_exec($ch);
        curl_close($ch);

        return $respuesta;        
        
    }


    public function generarConsultaAnulacionArchivoJson($facturaVenta)
    {

        switch ($facturaVenta->getDocumento()) {
            case 'factura':
                $serie = $facturaVenta->getLocal()->getSerieFactura();
                $tipo_de_comprobante = 1;
                break;
            case 'boleta':
                $serie = $facturaVenta->getLocal()->getSerieBoleta();
                $tipo_de_comprobante = 2;
                break;            
            default:
                $serie = '';
                $tipo_de_comprobante = '';
                break;
        }


        $partesticket = explode("-", $facturaVenta->getTicket());
        $numero = $partesticket[1];

        $data = array(
            "operacion"                         => "consultar_anulacion",
            "tipo_de_comprobante"               => "".$tipo_de_comprobante."",
            "serie"                             => "".$serie."",
            "numero"                            => $numero
        );

        $data_json = json_encode($data);

        return $data_json;


    }

    public function enviarConsultaAnulacionArchivoJson($data_json,$url,$token)
    {
        //Invocamos el servicio de NUBEFACT
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Token token="'.$token.'"',
            'Content-Type: application/json',
            )
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta  = curl_exec($ch);
        curl_close($ch);

        return $respuesta;        
        
    }


    public function siderperu($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT 
                    YEAR(fv.fecha) AS 'ano',MONTH(fv.fecha),c.id AS 'cliente_id',e.id AS 'empleado_id',pxl.id AS 'producto_id',
                    (CASE
                        WHEN MONTH(fv.fecha) = 1 THEN 'ENERO'
                        WHEN MONTH(fv.fecha) = 2 THEN 'FEBRERO'
                        WHEN MONTH(fv.fecha) = 3 THEN 'MARZO'
                        WHEN MONTH(fv.fecha) = 4 THEN 'ABRIL'
                        WHEN MONTH(fv.fecha) = 5 THEN 'MAYO'
                        WHEN MONTH(fv.fecha) = 6 THEN 'JUNIO'
                        WHEN MONTH(fv.fecha) = 7 THEN 'JULIO'
                        WHEN MONTH(fv.fecha) = 8 THEN 'AGOSTO'
                        WHEN MONTH(fv.fecha) = 9 THEN 'SETIEMBRE'
                        WHEN MONTH(fv.fecha) = 10 THEN 'OCTUBRE'
                        WHEN MONTH(fv.fecha) = 11 THEN 'NOVIEMBRE'
                        WHEN MONTH(fv.fecha) = 12 THEN 'DICIEMBRE'    
                        ELSE ''
                    END) AS 'mes',
                    el.nombre AS 'almacen',(CASE WHEN c.tipo = 'mayorista' THEN 'FERRETERIA' ELSE 'CLIENTE FINAL' END) AS 'tipo_canal_venta',
                    c.ruc AS 'ruc_cliente',c.razon_social AS 'razon_social_cliente',c.direccion AS 'direccion_cliente',
                    (CASE WHEN c.distrito_id IS NOT NULL THEN (SELECT nombre FROM distrito WHERE id = c.distrito_id LIMIT 1) ELSE '' END) AS 'distrito_cliente',
                    (CASE WHEN c.distrito_id IS NOT NULL THEN (SELECT dp.nombre FROM distrito dt INNER JOIN provincia pr ON dt.provincia_id = pr.id INNER JOIN departamento dp ON pr.departamento_id = dp.id WHERE dt.id = c.distrito_id LIMIT 1) ELSE '' END) AS 'departamento_cliente',
                    c.telefono,'nombre_contacto',CONCAT(e.nombres,' ',e.apellido_paterno,' ',e.apellido_materno) AS 'vendedor',el.direccion AS 'direccion_despacho',
                    (CASE WHEN el.distrito_id IS NOT NULL THEN (SELECT nombre FROM distrito WHERE id = el.distrito_id LIMIT 1) ELSE '' END) AS 'distrito_despacho',
                    (CASE WHEN el.distrito_id IS NOT NULL THEN (SELECT dp2.nombre FROM distrito dt2 INNER JOIN provincia pr2 ON dt2.provincia_id = pr2.id INNER JOIN departamento dp2 ON pr2.departamento_id = dp2.id WHERE dt2.id = el.distrito_id LIMIT 1) ELSE '' END) AS 'departamento_despacho',
                    p.nombre AS 'producto',SUM(dv.cantidad) AS 'cantidad',SUM(dv.cantidad * IFNULL(p.peso,0)) AS 'venta',
                    (CASE WHEN vfp.forma_pago_id = 2 THEN 'CREDITO' ELSE 'CONTADO' END) AS 'tipo_venta',vfp.numero_dias AS 'dias_credito'
                    FROM factura_venta fv
                    INNER JOIN venta v ON fv.venta_id = v.id
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN detalle_venta dv ON dv.venta_id = v.id
                    INNER JOIN cliente c ON fv.cliente_id = c.id
                    INNER JOIN empresa_local el ON fv.empresa_local_id = el.id
                    INNER JOIN empresa em ON em.id = el.empresa_id
                    INNER JOIN empleado e ON v.empleado_id = e.id
                    INNER JOIN producto_x_local pxl ON pxl.id = dv.producto_x_local_id
                    INNER JOIN producto p ON pxl.producto_id = p.id
                    INNER JOIN producto_marca pm ON p.marca_id = pm.id
                    WHERE em.id = ?  AND pm.nombre LIKE '%SIDER%'
                    ";//AND p.nombre LIKE '%BARRA DE CONSTRUCCION%'

        if($fechaini == '' || $fechafin == '')
        {
            $sql .=" AND MONTH(fv.fecha) = MONTH(CURRENT_DATE()) AND YEAR(fv.fecha) = YEAR(CURRENT_DATE()) AND DAY(fv.fecha) = DAY(CURRENT_DATE())";
        }
        elseif($fechaini != '' && $fechafin != '')
        {
            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        $sql .=" GROUP BY YEAR(fv.fecha), MONTH(fv.fecha),c.id,e.id,pxl.id ";
        $sql .=" ORDER BY fv.fecha DESC,c.razon_social,e.nombres,p.nombre ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $ventas = $stmt->fetchAll();


        return $ventas;
    }

    public function obtenerDocumentosConError($fechaini='',$fechafin='')
    {            

        $sql = "SELECT fv.id,fvne.serie,fvne.numero,fv.documento,fvne.aceptada_por_sunat,fvne.sunat_responsecode AS codigo,fvne.sunat_description AS descripcion_error,fvne.error,e.nombre AS empresa,el.nombre AS local,fv.fecha,fv.ticket,v.estado FROM factura_venta_nubefact_error fvne 
                INNER JOIN factura_venta fv ON fv.id = fvne.factura_venta_id
                INNER JOIN venta v ON v.id = fv.venta_id
                INNER JOIN empresa_local el ON fv.empresa_local_id = el.id
                INNER JOIN empresa e ON el.empresa_id = e.id ";

        if($fechaini == '' || $fechafin == ''){
            $sql .=" WHERE MONTH(fv.fecha) = MONTH(CURRENT_DATE()) AND YEAR(fv.fecha) = YEAR(CURRENT_DATE()) ";
        }elseif($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" WHERE CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        
        $sql .=" ORDER BY fv.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        //$stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(1, $fechaini);
            $stmt->bindValue(2, $fechafin);
        }
        $stmt->execute();
        $documentos = $stmt->fetchAll();


        return $documentos;
    }

    public function registroVentas($empresa,$fechaini='',$fechafin='')
    {            
        $sql = "SELECT 
                    fv.id,
                    v.id AS venta_id,
                    fv.fecha AS 'fecha_emision',
                    (CASE
                    WHEN fv.documento = 'boleta' THEN '03'
                    WHEN fv.documento = 'factura' THEN '01' 
                    ELSE ''
                    END) AS 'tipo',
                    SUBSTR(fv.ticket,1,4) AS 'serie',
                    SUBSTR(fv.ticket,6) AS 'numero',
                    fv.ticket,
                    (CASE
                    WHEN c.tipo_documento_id = 1 THEN '1'
                    WHEN c.tipo_documento_id = 2 THEN '6' 
                    ELSE ''
                    END) AS 'doc_entidad',
                    c.ruc,
                    c.razon_social AS 'denominacion',
                    (CASE
                    WHEN vfp.moneda_id = 1 THEN 'SOLES'
                    WHEN vfp.moneda_id = 2 THEN 'DOLARES' 
                    ELSE ''
                    END) AS 'moneda',
                    (CASE
                        WHEN vfp.moneda_id = 1 THEN SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                        WHEN vfp.moneda_id = 2 THEN SUM(vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1))
                        ELSE SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                    END) AS 'total',
                    (CASE
                        WHEN fv.detraccion = 0 THEN 'NO'
                        WHEN fv.detraccion = 1 THEN 'SI'
                        ELSE ''
                    END) AS 'detraccion',
                    (CASE
                        WHEN fv.enviado_sunat = 0 THEN '-'
                        WHEN fv.enviado_sunat = 1 THEN 'SI'
                        ELSE ''
                    END) AS 'aceptado_sunat',
                    (CASE
                        WHEN v.estado = 0 THEN 'SI'
                        WHEN v.estado = 1 THEN 'NO'
                        ELSE ''
                    END) AS 'anulado',
                    vfp.valor_tipo_cambio AS 'tipo_cambio',
                    fv.enlace_xml,fv.enlace_cdr,fv.codigo_error,fv.mensaje_error,fv.enlacepdf AS enlace_pdf
                    FROM factura_venta fv
                    INNER JOIN venta v ON fv.venta_id = v.id
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN cliente c ON fv.cliente_id = c.id
                    INNER JOIN empresa_local el ON fv.empresa_local_id = el.id
                    INNER JOIN empresa em ON em.id = el.empresa_id
                    INNER JOIN empleado e ON v.empleado_id = e.id
                    WHERE em.id = ?  AND fv.documento IN ('factura','boleta') AND fv.tipo_id = 2                 
                    ";

        if($fechaini == '' || $fechafin == '')
        {
            $sql .=" AND MONTH(fv.fecha) = MONTH(CURRENT_DATE()) AND YEAR(fv.fecha) = YEAR(CURRENT_DATE()) ";//AND DAY(fv.fecha) = DAY(CURRENT_DATE())
        }
        elseif($fechaini != '' && $fechafin != '')
        {
            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        $sql .=" GROUP BY fv.id ";
        $sql .=" ORDER BY fv.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $ventas = $stmt->fetchAll();


        return $ventas;
    }


    public function ganancias($empresa,$fechaini='',$fechafin='')
    {            
        $sql = "SELECT UPPER(p.nombre) AS 'producto',UPPER(p.codigo) AS 'codigo',
                UPPER(fv.ticket) AS 'documento',fv.fecha,p.precio_unitario,dv.precio AS 'precio_venta',
                dv.precio_costo AS 'precio_costo',dv.cantidad,
                (IFNULL(dv.precio,0)  - IFNULL(dv.precio_costo,0)) AS 'ganancia_x_unidad',
                (IFNULL(dv.precio,0) * dv.cantidad) AS 'venta_total',
                (IFNULL(dv.precio_costo,0) * dv.cantidad) AS 'compra_total',
                ((IFNULL(dv.precio,0) * dv.cantidad) - (IFNULL(dv.precio_costo,0) * dv.cantidad)) AS 'ganancia_total'
                FROM factura_venta fv
                INNER JOIN venta v ON fv.venta_id = v.id
                INNER JOIN detalle_venta dv ON dv.venta_id = v.id
                INNER JOIN producto_x_local pxl ON dv.producto_x_local_id = pxl.id
                INNER JOIN producto p ON pxl.producto_id = p.id
                WHERE fv.tipo_id = 2 AND v.estado = 1 AND p.empresa_id = ? ";

        if($fechaini == '' || $fechafin == '')
        {
            $sql .=" AND MONTH(fv.fecha) = MONTH(CURRENT_DATE()) AND YEAR(fv.fecha) = YEAR(CURRENT_DATE()) ";//AND DAY(fv.fecha) = DAY(CURRENT_DATE())
        }
        elseif($fechaini != '' && $fechafin != '')
        {
            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        //$sql .=" GROUP BY fv.id ";
        $sql .=" ORDER BY fv.fecha DESC,p.nombre ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $ventas = $stmt->fetchAll();


        return $ventas;
    }



    public function registroVentasDetalle($empresa,$fechaini='',$fechafin='')
    {            
        $sql = "SELECT 
                    fv.id,
                    fv.fecha AS 'fecha_emision',
                    (CASE
                    WHEN fv.documento = 'boleta' THEN '03'
                    WHEN fv.documento = 'factura' THEN '01' 
                    ELSE ''
                    END) AS 'tipo',
                    SUBSTR(fv.ticket,1,4) AS 'serie',
                    SUBSTR(fv.ticket,6) AS 'numero',
                    fv.ticket,
                    (CASE
                    WHEN c.tipo_documento_id = 1 THEN '1'
                    WHEN c.tipo_documento_id = 2 THEN '6' 
                    ELSE ''
                    END) AS 'doc_entidad',
                    c.ruc,
                    c.razon_social AS 'denominacion',
                    (CASE
                    WHEN vfp.moneda_id = 1 THEN 'SOLES'
                    WHEN vfp.moneda_id = 2 THEN 'DOLARES' 
                    ELSE ''
                    END) AS 'moneda',
                    (CASE
                        WHEN vfp.moneda_id = 1 THEN SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                        WHEN vfp.moneda_id = 2 THEN SUM(vfp.cantidad * IFNULL(vfp.valor_tipo_cambio,1) + IFNULL(vfp.igv,0) * IFNULL(vfp.valor_tipo_cambio,1))
                        ELSE SUM(vfp.cantidad + IFNULL(vfp.igv,0))
                    END) AS 'total',
                    (CASE
                        WHEN fv.detraccion = 0 THEN 'NO'
                        WHEN fv.detraccion = 1 THEN 'SI'
                        ELSE ''
                    END) AS 'detraccion',
                    (CASE
                        WHEN fv.enviado_sunat = 0 THEN '-'
                        WHEN fv.enviado_sunat = 1 THEN 'SI'
                        ELSE ''
                    END) AS 'aceptado_sunat',
                    (CASE
                        WHEN v.estado = 0 THEN 'SI'
                        WHEN v.estado = 1 THEN 'NO'
                        ELSE ''
                    END) AS 'anulado'
                    FROM factura_venta fv
                    INNER JOIN venta v ON fv.venta_id = v.id
                    INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                    INNER JOIN cliente c ON fv.cliente_id = c.id
                    INNER JOIN empresa_local el ON fv.empresa_local_id = el.id
                    INNER JOIN empresa em ON em.id = el.empresa_id
                    INNER JOIN empleado e ON v.empleado_id = e.id
                    WHERE em.id = ?  AND fv.documento IN ('factura','boleta')                  
                    ";

        if($fechaini == '' || $fechafin == '')
        {
            $sql .=" AND MONTH(fv.fecha) = MONTH(CURRENT_DATE()) AND YEAR(fv.fecha) = YEAR(CURRENT_DATE()) AND DAY(fv.fecha) = DAY(CURRENT_DATE())";
        }
        elseif($fechaini != '' && $fechafin != '')
        {
            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fv.fecha AS DATE) BETWEEN ? AND ? ";
        }
        $sql .=" GROUP BY fv.id ";
        $sql .=" ORDER BY fv.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $ventas = $stmt->fetchAll();


        return $ventas;
    }

    public function obtenerPrecioCosto($producto,$cantidad=0)
    {

        $cantidad_inicial = $cantidad;

        $sql = "SELECT txp.id,txp.transferencia_id,txp.producto_x_local_id,txp.cantidad,
                txp.precio,txp.contador,t.fecha FROM transferencia_x_producto txp
                INNER JOIN transferencia t ON txp.transferencia_id = t.id 
                INNER JOIN producto_x_local pxl ON txp.producto_x_local_id = pxl.id 
                WHERE t.estado = 1 AND  t.motivo_traslado_id = 2 AND pxl.id = ?   ";

        $sql .=" ORDER BY t.fecha ASC ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(1, $producto);

        $stmt->execute();
        $transferencias = $stmt->fetchAll();

        $precio_costo_array = array();
        $precio_costo = 0;

        if(count($transferencias) == 0 )
        {
            return $precio_costo;
        }        

        foreach($transferencias as $i=>$transferencia)
        {
            if($cantidad == 0)
            {
                break;
            }

            if($transferencia['contador'] == 0)
            {
                continue;
            }
            else
            {
                $contador = ($transferencia['contador']) ? $transferencia['contador'] : 0;

                if($cantidad > $contador)
                {
                    $cantidad = $cantidad - $contador;

                    $transferenciaXProducto = $this->em->getRepository('AppBundle:TransferenciaXProducto')->find($transferencia['id']);

                    $transferenciaXProducto->setContador(0);
                    $this->em->persist($transferenciaXProducto);

                    $precio_costo_array[] = $transferencia['precio'] * $contador;

                    
                }
                elseif($cantidad <= $contador)
                {
                    

                    $transferenciaXProducto = $this->em->getRepository('AppBundle:TransferenciaXProducto')->find($transferencia['id']);

                    $contador = $contador - $cantidad;

                    $transferenciaXProducto->setContador($contador);
                    $this->em->persist($transferenciaXProducto);

                    $precio_costo_array[] = $transferencia['precio'] * $cantidad;

                    $cantidad = 0;

                }
                else
                {

                }


            }

        }


        if(count($precio_costo_array) > 0)
        {
            if($cantidad_inicial > 0)
            {
                $precio_costo = array_sum($precio_costo_array)/$cantidad_inicial;
            }
            
        }
        else
        {
            $key = key(array_slice($transferencias, -1, 1, true));
            $precio_costo = $transferencias[$key]['precio'];
        }


        try {

            $this->em->flush();
            
        } catch (\Exception $e) {

            return $e.'Error en la actualizacion del contador.';
            
        }
         

        return $precio_costo;

    }


    public function ventaDetalleProducto($empresa)
    {
        $sql = "SELECT YEAR(fv.fecha) AS 'ano',MONTH(fv.fecha) AS 'mes',DATE_FORMAT(fv.fecha,'%d/%m/%Y %H:%i') AS 'fecha',fv.ticket,UPPER(fv.documento) AS 'documento',c.razon_social AS 'cliente',el.nombre AS 'local',
                UPPER(p.nombre) AS 'producto',dv.cantidad,dv.precio,dv.subtotal,dv.precio_costo,UPPER(fp.nombre) AS 'forma_pago',
                ((dv.precio - IFNULL((CASE WHEN dv.precio_costo IS NULL THEN p.precio_compra ELSE dv.precio_costo END),0)) * dv.cantidad) AS 'ganancia'
                FROM factura_venta fv
                INNER JOIN venta v ON fv.venta_id = v.id
                INNER JOIN cliente c ON fv.cliente_id = c.id
                INNER JOIN empleado em ON v.empleado_id = em.id
                INNER JOIN detalle_venta dv ON dv.venta_id = v.id
                INNER JOIN producto_x_local pxl ON dv.producto_x_local_id = pxl.id
                INNER JOIN producto p ON pxl.producto_id = p.id
                INNER JOIN venta_forma_pago vfp ON vfp.venta_id = v.id
                INNER JOIN forma_pago fp ON vfp.forma_pago_id = fp.id
                INNER JOIN empresa_local el ON fv.empresa_local_id = el.id
                INNER JOIN empresa e ON el.empresa_id = e.id
                WHERE v.estado = 1 AND fv.tipo_id = 2 AND e.id = ? 
                ORDER BY ano,mes,fecha,fv.ticket  ";


        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1,$empresa);
        
        $stmt->execute();
        $ventas = $stmt->fetchAll();

        return $ventas;

    }

    /* Funciones para envio y generacion de guias de remision*/
    public function generarNumeroGuiaremision($tabla,$local,$empresa='')
    {

        $localObj = $this->em->getRepository('AppBundle:EmpresaLocal')->find($local);
        
        $sql = "SELECT COUNT(*) as total FROM ";
        $sql .= $tabla.' f';
        $sql .= " INNER JOIN empresa_local el ON f.empresa_local_id = el.id ";
        $sql .= " INNER JOIN empresa e ON el.empresa_id = e.id ";
        $sql .= " WHERE f.id > 0  ";

        if($empresa != ''){

            $sql .= " AND e.id = ? ";

        }else{

            $sql .= " AND el.id = ?  ";
        }


        $stmt = $this->conn->prepare($sql);

        if($empresa != ''){

            $stmt->bindValue(1, $empresa);

        }else{

            $stmt->bindValue(1, $local);
        }


        $stmt->execute();
        $total = $stmt->fetchColumn();

        return $total;
    }

    public function enviarGuiaRemision($guiaRemision,$detalleProductos)
    {        

        $detalleGuia = array();

        $j=0;
        foreach($detalleProductos as $i=>$detalle)
        {

            $unidad_medida = "NIU";
            if($detalle['tipo'] == 'servicio')
            {
               $unidad_medida = "ZZ";     
            }                    

            $detalleGuia[$j]['unidad_de_medida'] = "".$unidad_medida."";
            $detalleGuia[$j]['codigo'] = "".$detalle['codigo']."";
            $detalleGuia[$j]['descripcion'] = "".$detalle['descripcion']."";
            $detalleGuia[$j]['cantidad'] = "".$detalle['cantidad']."";

            $j++;
        }

        //$cliente = $this->em->getRepository('AppBundle:Cliente')->find($guiaRemision->getCliente()->getId());

        $sql = "SELECT tc.codigo,c.ruc,c.razon_social,c.direccion,c.email FROM cliente c INNER JOIN tipo_documento tc ON c.tipo_documento_id = tc.id ";
        $sql .= " WHERE c.id = ? ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $guiaRemision->getCliente()->getId());
        $stmt->execute();
        $cliente = $stmt->fetch();

       

        $serie  = $guiaRemision->getSerie();
        $numero = $guiaRemision->getNumero();
        $cliente_tipo_de_documento = ($cliente['codigo'] == '6') ? '6': '1';
        $cliente_numero_de_documento = $cliente['ruc'];
        $cliente_denominacion = $cliente['razon_social'];
        $cliente_direccion = ($cliente['direccion']) ? $cliente['direccion'] : '';
        $cliente_email = ($this->is_valid_email($cliente['email'])) ? $cliente['email'] : '';
        $fecha_de_emision = ($guiaRemision->getFechaEmision()) ? $guiaRemision->getFechaEmision()->format('d-m-Y') : date('d-m-Y');
        $observaciones = ($guiaRemision->getObservacion()) ? $guiaRemision->getObservacion() : '';
        $motivo_de_traslado = $guiaRemision->getMotivoTraslado();
        $peso_bruto_total = $guiaRemision->getPeso();
        $numero_de_bultos = $guiaRemision->getNumeroBultos();
        $tipo_de_transporte = ($guiaRemision->getTipoTransporte() == 'privado') ? '02':'01';
        $fecha_de_inicio_de_traslado = ($guiaRemision->getFechaInicioTraslado()) ? $guiaRemision->getFechaInicioTraslado()->format('d-m-Y') : date('d-m-Y');
        $transportista_documento_tipo = ($guiaRemision->getTransportistaDocumento() == 'dni') ? '1':'6';
        $transportista_documento_numero = $guiaRemision->getTransportistaDocumentoNumero();
        $transportista_denominacion = $guiaRemision->getTransportistaDenominacion();
        $transportista_placa_numero = $guiaRemision->getTransportistaPlaca();
        $conductor_documento_tipo = ($guiaRemision->getConductorDocumento() == 'dni') ? '1':'6';
        $conductor_documento_numero = $guiaRemision->getConductorDocumentoNumero();
        $conductor_denominacion = $guiaRemision->getConductorNombre();
        $punto_de_partida_direccion = $guiaRemision->getPuntoPartida();
        $punto_de_llegada_direccion = $guiaRemision->getPuntoLlegada();
        $punto_de_partida_ubigeo = $guiaRemision->getUbigeoPartida();
        $punto_de_llegada_ubigeo = $guiaRemision->getUbigeoLlegada();


        $data = array(
            "operacion"                         => "generar_guia",
            "tipo_de_comprobante"               => "7",
            "serie"                             => "".$serie."",
            "numero"                            => "".$numero."",
            "cliente_tipo_de_documento"         => "".$cliente_tipo_de_documento."",
            "cliente_numero_de_documento"       => "".$cliente_numero_de_documento."",
            "cliente_denominacion"              => "".$cliente_denominacion."",
            "cliente_direccion"                 => "".$cliente_direccion."",
            "cliente_email"                     => "".$cliente_email."",
            "cliente_email_1"                   => "",
            "cliente_email_2"                   => "",
            "fecha_de_emision"                  => "".$fecha_de_emision."",
            "observaciones"                     => "".$observaciones."",
            "motivo_de_traslado"                => "".$motivo_de_traslado."",
            "peso_bruto_total"                  => "".$peso_bruto_total."",
            "numero_de_bultos"                  => "".$numero_de_bultos."",
            "tipo_de_transporte"                => "".$tipo_de_transporte."",
            "fecha_de_inicio_de_traslado"       => "".$fecha_de_inicio_de_traslado."",
            "transportista_documento_tipo"      => "".$transportista_documento_tipo."",
            "transportista_documento_numero"    => "".$transportista_documento_numero."",
            "transportista_denominacion"        => "".$transportista_denominacion."",
            "transportista_placa_numero"        => "".$transportista_placa_numero."",
            "conductor_documento_tipo"          => "".$conductor_documento_tipo."",
            "conductor_documento_numero"        => "".$conductor_documento_numero."",
            "conductor_denominacion"            => "".$conductor_denominacion."",
            "punto_de_partida_ubigeo"           => "".$punto_de_partida_ubigeo."",
            "punto_de_partida_direccion"        => "".$punto_de_partida_direccion."",
            "punto_de_llegada_ubigeo"           => "".$punto_de_llegada_ubigeo."",
            "punto_de_llegada_direccion"        => "".$punto_de_llegada_direccion."",
            "enviar_automaticamente_a_la_sunat" => "false",
            "enviar_automaticamente_al_cliente" => "false",
            "codigo_unico"                      => "",
            "formato_de_pdf"                    => "",
            "items" => $detalleGuia
        );



        $data_json = json_encode($data);

        $url   = $guiaRemision->getLocal()->getUrlFacturacion();
        $token = $guiaRemision->getLocal()->getTokenFacturacion();

        //dump($data_json);
        //die();

        $respuesta = $this->enviarNubefact($data_json,$url,$token);

        return $respuesta;

    }    

    private function is_valid_email($str)
    {
       return (false !== filter_var($str, FILTER_VALIDATE_EMAIL));
    }

    public function enviarVenta($facturaVenta,$pedidoVenta,$detalleProductos)
    {

        $detalleVenta = array();
        $i=0;
        $total_gravada = 0;
        $total_igv = 0;
        $total = 0;
        foreach($detalleProductos as $j=>$detalle)
        {
            $tipo_de_igv = $detalle['tipoImpuesto'];
            $igv_valor   = $detalle['impuestoValor'];
            $codigo_producto_sunat   = $detalle['codigoProductoSunat'];

            if(!$pedidoVenta->getIncluirIgv())
            {

                $cantidadLinea      = $detalle['cantidad'];
                $valor_unitario     = $detalle['precio'];
                $precio_unitario    = ($cantidadLinea > 0) ? $valor_unitario * (1 + $igv_valor) : 0;

                $subt               = $valor_unitario*$cantidadLinea;
                
                $totalLinea         = $cantidadLinea * $precio_unitario;
                $igv                = $cantidadLinea * $valor_unitario * $igv_valor;

                $descripcion        = $detalle['descripcion'];

                $unidad_medida = "NIU";
                if($detalle['tipo'] == 'servicio')
                {
                   $unidad_medida = "ZZ";     
                }                  
                
                $detalleVenta[$i]['unidad_de_medida'] = "".$unidad_medida."";
                $detalleVenta[$i]['codigo'] = "".$detalle['codigo']."";
                $detalleVenta[$i]['descripcion'] = "".$descripcion."";
                $detalleVenta[$i]['cantidad'] = "".$detalle['cantidad']."";
                $detalleVenta[$i]['valor_unitario'] = "".$valor_unitario."";
                $detalleVenta[$i]['precio_unitario'] = "".$precio_unitario."";
                $detalleVenta[$i]['descuento'] = "";
                $detalleVenta[$i]['subtotal'] = "".$subt."";
                $detalleVenta[$i]['tipo_de_igv'] = "".$tipo_de_igv."";
                $detalleVenta[$i]['igv'] = "".$igv."";
                $detalleVenta[$i]['total'] = "".$totalLinea."";
                $detalleVenta[$i]['anticipo_regularizacion'] = "false";
                $detalleVenta[$i]['anticipo_documento_serie'] = "";
                $detalleVenta[$i]['anticipo_documento_numero'] = "";
                $detalleVenta[$i]['codigo_producto_sunat'] = "".$codigo_producto_sunat."";

                $i++;
                $total_igv  = $total_igv + $igv;
                $total      = $total + $precio_unitario * $cantidadLinea;


            }
            else
            {

                $cantidadLinea      = $detalle['cantidad'];
                $valor_unitario     = ($cantidadLinea > 0) ? ($detalle['subtotal']/$cantidadLinea)/(1 + $igv_valor) : 0;
                $subt               = $valor_unitario*$cantidadLinea;
                
                $precio_unitario    = $valor_unitario + $valor_unitario * $igv_valor;
                $totalLinea         = $cantidadLinea * $precio_unitario;
                $igv                = $subt * $igv_valor;


                $descripcion        = $detalle['descripcion'];
                
                $unidad_medida = "NIU";
                if($detalle['tipo'] == 'servicio')
                {
                   $unidad_medida = "ZZ";     
                }                    

                $detalleVenta[$i]['unidad_de_medida'] = "".$unidad_medida."";
                $detalleVenta[$i]['codigo'] = "".$detalle['codigo']."";
                $detalleVenta[$i]['descripcion'] = "".$descripcion."";
                $detalleVenta[$i]['cantidad'] = "".$detalle['cantidad']."";
                $detalleVenta[$i]['valor_unitario'] = "".$valor_unitario."";
                $detalleVenta[$i]['precio_unitario'] = "".$precio_unitario."";
                $detalleVenta[$i]['descuento'] = "";
                $detalleVenta[$i]['subtotal'] = "".$subt."";
                $detalleVenta[$i]['tipo_de_igv'] = "".$tipo_de_igv."";
                $detalleVenta[$i]['igv'] = "".$igv."";
                $detalleVenta[$i]['total'] = "".$totalLinea."";
                $detalleVenta[$i]['anticipo_regularizacion'] = "false";
                $detalleVenta[$i]['anticipo_documento_serie'] = "";
                $detalleVenta[$i]['anticipo_documento_numero'] = "";
                $detalleVenta[$i]['codigo_producto_sunat'] = "".$codigo_producto_sunat."";

                $i++;
                $total_igv  = $total_igv + $igv;
                $total      = $total + $detalle['subtotal'];

            }


        }


        $partesticket = explode("-", $facturaVenta->getTicket());
        $serie  = $partesticket[0];
        $numero = $partesticket[1];

        $localObj = $facturaVenta->getLocal();

        switch ($facturaVenta->getDocumento()) {
            case 'factura':
                $tipo_de_comprobante = 1;
                $cliente_tipo_de_documento = 6;
                break;
            case 'boleta':
                $cliente_tipo_de_documento = 1;
                $tipo_de_comprobante = 2;
                break;            
            default:
                $serie = '';
                $tipo_de_comprobante = '';
                break;
        }

        $total_gravada = $total/1.18;
        $total_igv = $total - $total_gravada;

        $fecha_emision = ($facturaVenta->getFecha()) ? $facturaVenta->getFecha()->format('d-m-Y') : date('d-m-Y');

        $guiasArray = array();
        if($facturaVenta->getNumeroGuiaremision() != ''){

            $guias = explode(",", $facturaVenta->getNumeroGuiaremision());
            
            $z=0;
            foreach($guias as $h=>$guia){

                $guiasArray[$z]['guia_tipo'] = 1;
                $guiasArray[$z]['guia_serie_numero'] = $guia;

                $z++;
            }

        }

        $cliente_numero_documento = $facturaVenta->getCliente()->getRuc();

        switch ($cliente_tipo_de_documento) {
            case 1:
                # Boleta...

                if(!is_numeric($facturaVenta->getCliente()->getRuc()))
                {
                    $cliente_numero_documento = '---';
                    $cliente_tipo_de_documento = '-';

                }
                else
                {
                    if(strlen($facturaVenta->getCliente()->getRuc()) != 8)
                    {
                        $cliente_numero_documento = '---';
                        $cliente_tipo_de_documento = '-';                        
                    }                    
                }

                break;
            case 6:
                # Factura...

                if(!is_numeric($facturaVenta->getCliente()->getRuc()))
                {
                    $cliente_numero_documento = '---';
                    $cliente_tipo_de_documento = '-';

                }
                else
                {
                    if(strlen($facturaVenta->getCliente()->getRuc()) != 11)
                    {
                        $cliente_numero_documento = '---';
                        $cliente_tipo_de_documento = '-';                        
                    }                    
                }

                break;            
            default:
                # code...
                break;
        }



        $detraccion = 'false';
        // if($facturaVenta->getDetraccion()){

        //     $detraccion = 'true';

        // }


        $condicion_de_pago = ($pedidoVenta->getFormaPago()) ? strtoupper($pedidoVenta->getFormaPago()->getNombre()) : '';

        if($pedidoVenta->getDiasCredito() && $pedidoVenta->getDiasCredito() > 0 )
        {
            if($pedidoVenta->getFormaPago()->getCodigo() == '2' )
            {
                $condicion_de_pago .= ' A '.$pedidoVenta->getDiasCredito().' DIAS';
            }
            
        }

        //Definimos el tipo de moneda de la factura
        $moneda = ($pedidoVenta->getMoneda())? $pedidoVenta->getMoneda()->getId() : 1;

        $tipo_cambio_valor = '';
        if($moneda == 2){
            $tipo_cambio_valor = ($pedidoVenta->getValorTipoCambio()) ? $pedidoVenta->getValorTipoCambio() : '';
        }


        $data = array(
            "operacion"                         => "generar_comprobante",
            "tipo_de_comprobante"               => "".$tipo_de_comprobante."",
            "serie"                             => "".$serie."",
            "numero"                            => $numero,
            "sunat_transaction"                 => "1",
            "cliente_tipo_de_documento"         => "".$cliente_tipo_de_documento."",
            "cliente_numero_de_documento"       => "".$cliente_numero_documento."",
            "cliente_denominacion"              => "".$facturaVenta->getCliente()->getRazonSocial()."",
            "cliente_direccion"                 => "".$facturaVenta->getCliente()->getDireccion()."",
            "cliente_email"                     => "",
            "cliente_email_1"                   => "",
            "cliente_email_2"                   => "",
            "fecha_de_emision"                  => $fecha_emision,
            "fecha_de_vencimiento"              => "",
            "moneda"                            => "".$moneda."",
            "tipo_de_cambio"                    => "".$tipo_cambio_valor."",
            "porcentaje_de_igv"                 => "18.00",
            "descuento_global"                  => "",
            "descuento_global"                  => "",
            "total_descuento"                   => "",
            "total_anticipo"                    => "",
            "total_gravada"                     => "".round($total_gravada,2)."",
            "total_inafecta"                    => "",
            "total_exonerada"                   => "",
            "total_igv"                         => "".round($total_igv,2)."",
            "total_gratuita"                    => "",
            "total_otros_cargos"                => "",
            "total"                             => "".round($total,2)."",
            "percepcion_tipo"                   => "",
            "percepcion_base_imponible"         => "",
            "total_percepcion"                  => "",
            "total_incluido_percepcion"         => "",
            "detraccion"                        => "".$detraccion."",
            "observaciones"                     => "",
            "documento_que_se_modifica_tipo"    => "",
            "documento_que_se_modifica_serie"   => "",
            "documento_que_se_modifica_numero"  => "",
            "tipo_de_nota_de_credito"           => "",
            "tipo_de_nota_de_debito"            => "",
            "enviar_automaticamente_a_la_sunat" => "true",
            "enviar_automaticamente_al_cliente" => "false",
            "codigo_unico"                      => "",
            "condiciones_de_pago"               => "".$condicion_de_pago."",
            "medio_de_pago"                     => "",
            "placa_vehiculo"                    => "",
            "orden_compra_servicio"             => "",
            "tabla_personalizada_codigo"        => "",
            "formato_de_pdf"                    => "",
            "items" => $detalleVenta
        );

        if($facturaVenta->getNumeroGuiaremision() != ''){
            $data["guias"] = $guiasArray;
        }


        $data_json = json_encode($data);


        $url   = $facturaVenta->getLocal()->getUrlFacturacion();
        $token = $facturaVenta->getLocal()->getTokenFacturacion();

        $respuesta = $this->enviarNubefact($data_json,$url,$token);

        return $respuesta;

    }

    public function enviarNota($notaCredito,$detalleProductos)
    {

        $facturaVenta = $notaCredito->getFacturaVenta();

        $detalleVenta = array();
        $sunat_transaction = ($facturaVenta->getTipoVenta() == 'venta_interna') ? '1' : '2';
        $i=0;
        $total_gravada      = 0;
        $total_igv          = 0;
        $total              = 0;
        $total_gratuita     = 0;
        $total_descuento    = 0;
        $total_anticipo     = 0;
        $total_inafecta     = 0;
        $total_exonerada    = 0;            
        $total_otros_cargos = 0;

        foreach($detalleProductos as $j=>$detalle)
        {

            $tipo_de_igv    = $detalle['tipoImpuesto'];
            $igv_valor      = $detalle['impuestoValor'];
            $descuento      = ($detalle['descuento']) ? $detalle['descuento'] : 0;
            $cantidadLinea  = $detalle['cantidad'];
            $subtotal       = $detalle['subtotal'];
            $valor_unitario = $detalle['precio_vu'];

            //$valor_unitario = ($cantidadLinea > 0) ? ($subtotal/$cantidadLinea)/(1 + $igv_valor) : 0;            
            
            $subt               = $valor_unitario * $cantidadLinea - $descuento;            
            $precio_unitario    = ($cantidadLinea > 0) ? $detalle['precio'] : 0;
            $totalLinea         = $cantidadLinea * $precio_unitario;
            $igv                = $subt * 0.18;//$cantidadLinea * $valor_unitario * $igv_valor;


            $descripcion        = $detalle['descripcion'];
            
            $unidad_medida = "NIU";
            if($detalle['tipo'] == 'servicio')
            {
               $unidad_medida = "ZZ";     
            }                    

            $detalleVenta[$i]['unidad_de_medida'] = "".$unidad_medida."";
            $detalleVenta[$i]['codigo'] = "".$detalle['codigo']."";
            $detalleVenta[$i]['descripcion'] = "".$descripcion."";
            $detalleVenta[$i]['cantidad'] = "".$detalle['cantidad']."";
            $detalleVenta[$i]['valor_unitario'] = "".$valor_unitario."";
            $detalleVenta[$i]['precio_unitario'] = "".$precio_unitario."";
            $detalleVenta[$i]['descuento'] = "".$descuento."";
            $detalleVenta[$i]['subtotal'] = "".$subt."";
            $detalleVenta[$i]['tipo_de_igv'] = "".$tipo_de_igv."";
            $detalleVenta[$i]['igv'] = "".$igv."";
            $detalleVenta[$i]['total'] = "".$totalLinea."";
            $detalleVenta[$i]['anticipo_regularizacion'] = "false";
            $detalleVenta[$i]['anticipo_documento_serie'] = "";
            $detalleVenta[$i]['anticipo_documento_numero'] = "";

            $i++;


            switch ($tipo_de_igv) {
                case 1:
                    $total_igv       = $total_igv + $igv;
                    $total_gravada   = $total_gravada + $valor_unitario * $detalle['cantidad'];
                    $total           = $total + $subtotal + $igv;                          
                    break;
                case 2:
                    $total_gratuita  = $total_gratuita + $precio_unitario * $cantidadLinea;    
                    break;
                case 3:
                    $total_gratuita  = $total_gratuita + $precio_unitario * $cantidadLinea;    
                    break;
                case 4:
                    $total_gratuita  = $total_gratuita + $precio_unitario * $cantidadLinea;    
                    break;
                case 5:
                    $total_gratuita  = $total_gratuita + $precio_unitario * $cantidadLinea;    
                    break;
                case 6:
                    $total_gratuita  = $total_gratuita + $precio_unitario * $cantidadLinea;    
                    break;
                case 7:
                    $total_gratuita  = $total_gratuita + $precio_unitario * $cantidadLinea;    
                    break;
                case 8:
                    $total_exonerada = $total_exonerada + $precio_unitario * $cantidadLinea;
                    $total           = $total + $precio_unitario * $cantidadLinea;  
                    break;
                case 9:
                    $total_inafecta  = $total_inafecta + $precio_unitario * $cantidadLinea;
                    $total           = $total + $total_inafecta;
                    break;
                case 10:
                    $total_gratuita  = $total_gratuita + $precio_unitario * $cantidadLinea;
                    break;
                case 11:
                    $total_gratuita  = $total_gratuita + $precio_unitario * $cantidadLinea;
                    break;
                case 12:
                    $total_gratuita  = $total_gratuita + $precio_unitario * $cantidadLinea;
                    break;
                case 13:
                    $total_gratuita  = $total_gratuita + $precio_unitario * $cantidadLinea;
                    break;
                case 14:
                    $total_gratuita  = $total_gratuita + $precio_unitario * $cantidadLinea;
                    break;
                case 15:
                    $total_gratuita  = $total_gratuita + $precio_unitario * $cantidadLinea;
                    break;
                case 16:
                    $total           = $total + $precio_unitario * $cantidadLinea;
                    $total_inafecta  = $total_inafecta + $precio_unitario * $cantidadLinea;
                    break;                                                                                                                                                                     
                default:
                    $total_igv       = $total_igv + $igv;
                    $total_gravada   = $total_gravada + $valor_unitario * $detalle['cantidad'];
                    $total           = $total + $detalle['subtotal'];                               
                    break;
            }



        }


        $serie  = $notaCredito->getSerie();
        $numero = $notaCredito->getNumero();

        $localObj = $facturaVenta->getLocal();

        switch ($facturaVenta->getDocumento()) {
            case 'factura':
                $tipo_de_comprobante = 3;
                $cliente_tipo_de_documento = 6;
                $documento_que_se_modifica_tipo = 1;
                break;
            case 'boleta':
                $cliente_tipo_de_documento = 1;
                $tipo_de_comprobante = 3;
                $documento_que_se_modifica_tipo = 2;
                break;            
            default:
                $serie = '';
                $tipo_de_comprobante = '';
                $cliente_tipo_de_documento = '-';
                $documento_que_se_modifica_tipo = 2;
                break;
        }


        $fecha_emision = ($notaCredito->getFecha()) ? $notaCredito->getFecha()->format('d-m-Y') : date('d-m-Y');
        $fecha_vencimiento = ($notaCredito->getFechaVencimiento()) ? $notaCredito->getFechaVencimiento()->format('d-m-Y') : date('d-m-Y');

        $guiasArray = array();
        if($notaCredito->getNumeroGuiaremision() != ''){

            $guias = explode(",", $notaCredito->getNumeroGuiaremision());
            
            $z=0;
            foreach($guias as $h=>$guia){

                $guiasArray[$z]['guia_tipo'] = 1;
                $guiasArray[$z]['guia_serie_numero'] = $guia;

                $z++;
            }

        }

        $cliente_numero_documento = $notaCredito->getCliente()->getRuc();

        switch ($cliente_tipo_de_documento) {
            case 1:
                # Boleta...

                if(!is_numeric($notaCredito->getCliente()->getRuc()))
                {
                    $cliente_numero_documento = '---';
                    $cliente_tipo_de_documento = '-';

                }
                else
                {
                    if(strlen($notaCredito->getCliente()->getRuc()) != 8)
                    {
                        $cliente_numero_documento = '---';
                        $cliente_tipo_de_documento = '-';                        
                    }                    
                }

                break;
            case 6:
                # Factura...

                if(!is_numeric($notaCredito->getCliente()->getRuc()))
                {
                    $cliente_numero_documento = '---';
                    $cliente_tipo_de_documento = '-';

                }
                else
                {
                    if(strlen($notaCredito->getCliente()->getRuc()) != 11)
                    {
                        $cliente_numero_documento = '---';
                        $cliente_tipo_de_documento = '-';                        
                    }                    
                }

                break;            
            default:
                    $cliente_numero_documento = '---';
                    $cliente_tipo_de_documento = '-';
                break;
        }

        if($notaCredito->getCliente()->getTipoDocumento()->getId() == 3){

            $cliente_tipo_de_documento = 0;

        }

        $detraccion = 'false';
        $condicion_de_pago = ($notaCredito->getCondicionPago()) ? $notaCredito->getCondicionPago() : '';

        //Definimos el tipo de moneda de la factura
        $moneda = ($notaCredito->getMoneda())? $notaCredito->getMoneda()->getId() : 1;

        $tipo_cambio_valor = '';
        if($moneda == 2){
            $tipo_cambio_valor = ($notaCredito->getValorTipoCambio()) ? $notaCredito->getValorTipoCambio() : '';
        }

        $orden_compra = ($notaCredito->getOrdenCompra()) ? $notaCredito->getOrdenCompra() : '';

        $partesticket = explode("-", $facturaVenta->getTicket());
        $documento_que_se_modifica_serie  = $partesticket[0];
        $documento_que_se_modifica_numero = $partesticket[1];

        $tipo_de_nota_de_credito = (int)$notaCredito->getNotaCreditoTipo()->getCodigo();
        $placa_vehiculo = ($notaCredito->getPlacaVehiculo()) ? $notaCredito->getPlacaVehiculo() : '';
        $observaciones  = ($notaCredito->getObservacion()) ? $notaCredito->getObservacion() : '';

        $total_gravada      = ($total_gravada == 0) ? '' : round($total_gravada,2);
        $total_igv          = ($total_igv == 0) ? '' : round($total_igv,2);
        $total              = round($total,2);
        $total_descuento    = ($total_descuento == 0) ? '' : round($total_descuento,2);
        $total_anticipo     = ($total_anticipo == 0) ? '' : round($total_anticipo,2);
        $total_inafecta     = ($total_inafecta == 0) ? '' : round($total_inafecta,2);
        $total_exonerada    = ($total_exonerada == 0) ? '' : round($total_exonerada,2);
        $total_gratuita     = ($total_gratuita == 0) ? '' : round($total_gratuita,2);
        $total_otros_cargos = ($total_otros_cargos == 0) ? '' : round($total_otros_cargos,2);
        
        $data = array(
            "operacion"                         => "generar_comprobante",
            "tipo_de_comprobante"               => "".$tipo_de_comprobante."",
            "serie"                             => "".$serie."",
            "numero"                            => $numero,
            "sunat_transaction"                 => "".$sunat_transaction."",
            "cliente_tipo_de_documento"         => "".$cliente_tipo_de_documento."",
            "cliente_numero_de_documento"       => "".$cliente_numero_documento."",
            "cliente_denominacion"              => "".$notaCredito->getCliente()->getRazonSocial()."",
            "cliente_direccion"                 => "".$notaCredito->getCliente()->getDireccion()."",
            "cliente_email"                     => "",
            "cliente_email_1"                   => "",
            "cliente_email_2"                   => "",
            "fecha_de_emision"                  => $fecha_emision,
            "fecha_de_vencimiento"              => $fecha_vencimiento,
            "moneda"                            => "".$moneda."",
            "tipo_de_cambio"                    => "".$tipo_cambio_valor."",
            "porcentaje_de_igv"                 => "18.00",
            "descuento_global"                  => "",
            "descuento_global"                  => "",
            "total_descuento"                   => "".$total_descuento."",
            "total_anticipo"                    => "".$total_anticipo."",
            "total_gravada"                     => "".$total_gravada."",
            "total_inafecta"                    => "".$total_inafecta."",
            "total_exonerada"                   => "".$total_exonerada."",
            "total_igv"                         => "".$total_igv."",
            "total_gratuita"                    => "".$total_gratuita."",
            "total_otros_cargos"                => "".$total_otros_cargos."",
            "total"                             => "".$total."",
            "percepcion_tipo"                   => "",
            "percepcion_base_imponible"         => "",
            "total_percepcion"                  => "",
            "total_incluido_percepcion"         => "",
            "detraccion"                        => "".$detraccion."",
            "observaciones"                     => "".$observaciones."",
            "documento_que_se_modifica_tipo"    => "".$documento_que_se_modifica_tipo."",
            "documento_que_se_modifica_serie"   => "".$documento_que_se_modifica_serie."",
            "documento_que_se_modifica_numero"  => "".$documento_que_se_modifica_numero."",
            "tipo_de_nota_de_credito"           => $tipo_de_nota_de_credito,
            "tipo_de_nota_de_debito"            => "",
            "enviar_automaticamente_a_la_sunat" => "true",
            "enviar_automaticamente_al_cliente" => "false",
            "codigo_unico"                      => "",
            "condiciones_de_pago"               => "".$condicion_de_pago."",
            "medio_de_pago"                     => "",
            "placa_vehiculo"                    => "".$placa_vehiculo."",
            "orden_compra_servicio"             => "".$orden_compra."",
            "tabla_personalizada_codigo"        => "",
            "formato_de_pdf"                    => "",
            "items" => $detalleVenta
        );

        if($facturaVenta->getNumeroGuiaremision() != ''){
            $data["guias"] = $guiasArray;
        }


        $data_json = json_encode($data);


        $url   = $notaCredito->getLocal()->getUrlFacturacion();
        $token = $notaCredito->getLocal()->getTokenFacturacion();

        $respuesta = $this->enviarNubefact($data_json,$url,$token);

        return $respuesta;

    }
    
    private function enviarNubefact($data_json,$url,$token)
    {
        //Invocamos el servicio de NUBEFACT
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Token token="'.$token.'"',
            'Content-Type: application/json',
            )
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta  = curl_exec($ch);
        curl_close($ch);

        return $respuesta;        
        
    }

    public function movimientoProductos($local,$producto_id=null)
    {  

        $sql = "SELECT txp.id,pxl.id AS producto_x_local,txp.transferencia_id AS transferencia,txp.cantidad AS cantidad,txp.precio AS precio,
                    mt.nombre AS motivo_traslado,t.numero_documento,IFNULL(t.fecha_creacion,t.fecha) AS fecha,p.nombre AS producto,p.id as producto,
                    el.nombre AS local,t.local_inicio,t.local_fin,mt.codigo,pxl.stock_inicial,IFNULL(pxl.fecha_creacion,'') AS fecha_stock_inicial FROM transferencia_x_producto txp
                    INNER JOIN transferencia t ON txp.transferencia_id = t.id
                    INNER JOIN motivo_traslado mt ON mt.id = t.motivo_traslado_id
                    INNER JOIN producto_x_local pxl ON pxl.id = txp.producto_x_local_id
                    INNER JOIN producto p ON pxl.producto_id = p.id
                    INNER JOIN empresa_local el ON el.id = pxl.empresa_local_id
                    WHERE t.estado = 1 AND p.tipo = 'producto' AND pxl.id = ? ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1,$producto_id);
        
        $stmt->execute();
        $movimientos = $stmt->fetchAll();

        return $movimientos;

    }

    public function actualizarPrecioPonderadoProducto($empresa)
    {

        $productos = $this->em->getRepository('AppBundle:Producto')->findBy(array('empresa'=>$empresa,'estado'=>true));

        foreach($productos as $producto)
        {

            //Obtenemos el promedio ponderado segun las compras realizadas
            $sql = "SELECT SUM(xx.cantidad * xx.precio)/SUM(xx.cantidad) AS promedio FROM 
                    (SELECT p.id,txp.cantidad,txp.precio FROM transferencia_x_producto txp
                    INNER JOIN transferencia t ON txp.transferencia_id = t.id
                    INNER JOIN producto_x_local pxl ON txp.producto_x_local_id = pxl.id
                    INNER JOIN producto p ON pxl.producto_id = p.id
                    WHERE t.empresa_id = 1 AND t.motivo_traslado_id = 2 AND txp.precio > 0 AND p.id = ? ) xx "; 

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $producto->getId());
            $stmt->execute();
            $precio_ponderado = $stmt->fetchColumn();

            if(!$precio_ponderado)
            {
                $precio_ponderado = $producto->getPrecioCompra();
            }

            $producto->setPrecioPonderado($precio_ponderado);
            $this->em->persist($producto);

        }

        try {

            $this->em->flush();

        } catch (\Exception $e) {
            
            return 'Ocurrio un error';
        }

    }


    public function detalleCompra($empresa,$fechaini='',$fechafin='')
    {            

        $sql = "SELECT
                    fc.id,fc.numero_documento,UPPER(fc.documento) AS 'documento',el.nombre AS 'local',pr.nombre AS 'proveedor',fc.fecha,UPPER(fp.nombre) AS 'forma_pago',
                    dc.subtotal AS subtotal,p.codigo AS codigo_prod,p.nombre AS nombre_prod,dc.cantidad,dc.precio
                    FROM detalle_compra dc
                    INNER JOIN compra c ON dc.compra_id = c.id
                    INNER JOIN factura_compra fc ON fc.compra_id = c.id   
                    INNER JOIN compra_forma_pago cfp ON cfp.compra_id = c.id
                    INNER JOIN forma_pago fp ON cfp.forma_pago_id = fp.id
                    INNER JOIN proveedor pr ON fc.proveedor_id = pr.id                
                    INNER JOIN empleado em ON em.id = c.empleado_id
                    INNER JOIN producto_x_local pxl ON dc.producto_x_local_id = pxl.id
                    INNER JOIN producto p ON p.id = pxl.producto_id
                    INNER JOIN producto_unidad pu ON p.unidad_venta_id = pu.id
                    INNER JOIN empresa_local el ON el.id = fc.empresa_local_id
                    INNER JOIN empresa e ON e.id = el.empresa_id
                    WHERE 
                    fc.estado = 1 AND 
                    el.estado = 1 AND 
                    e.id = ?
                    ";

        if($fechaini != '' && $fechafin != ''){

            $fechaini = date("Y-m-d", strtotime(str_replace('/', '-', $fechaini) ) );
            $fechafin = date("Y-m-d", strtotime(str_replace('/', '-', $fechafin) ) );

            $sql .=" AND CAST(fc.fecha AS DATE) BETWEEN ? AND ? ";
        }
        
        $sql .=" ORDER BY fc.fecha DESC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $empresa);
        if($fechaini != '' && $fechafin != ''){
            $stmt->bindValue(2, $fechaini);
            $stmt->bindValue(3, $fechafin);
        }
        $stmt->execute();
        $ventas = $stmt->fetchAll();


        return $ventas;
    }


}