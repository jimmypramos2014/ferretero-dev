<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * LogModificacion
 *
 * @ORM\Table(name="log_modificacion")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LogModificacionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class LogModificacion
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="valor", type="string", nullable=true)
     * 
     */
    private $valor;

    /**
     * @var string
     *
     * @ORM\Column(name="valor_nuevo", type="string", nullable=true)
     * 
     */
    private $valor_nuevo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=true)
     */
    private $fecha;

    /**
     * @ORM\ManyToOne(targetEntity="ModificacionTipo", inversedBy="logModificacion", cascade={"persist"})
     * @ORM\JoinColumn(name="modificacion_tipo_id", referencedColumnName="id")
     * 
     */
    protected $modificacionTipo;

    /**
     * @ORM\ManyToOne(targetEntity="ProductoXLocal", inversedBy="logModificacion", cascade={"persist"})
     * @ORM\JoinColumn(name="producto_x_local_id", referencedColumnName="id")
     * 
     */
    protected $productoXLocal;

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="logModificacion", cascade={"persist"})
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * 
     */
    protected $usuario;

    /**
     * @ORM\ManyToOne(targetEntity="FacturaVenta", inversedBy="logModificacion", cascade={"persist"})
     * @ORM\JoinColumn(name="factura_venta_id", referencedColumnName="id")
     * 
     */
    protected $facturaVenta;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario", inversedBy="caja", cascade={"persist"})
     * @ORM\JoinColumn(name="usuario_creacion", referencedColumnName="id")
     */
    private $usuarioCreacion;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario", inversedBy="caja", cascade={"persist"})
     * @ORM\JoinColumn(name="usuario_modificacion", referencedColumnName="id")
     */
    private $usuarioModificacion;

    /** 
     * created Time/Date 
     * 
     * @var \DateTime 
     * 
     * @ORM\Column(name="fecha_creacion", type="datetime", nullable=true) 
     */  
    protected $fechaCreacion;  
  
    /** 
     * updated Time/Date 
     * 
     * @var \DateTime 
     * 
     * @ORM\Column(name="fecha_modificacion", type="datetime", nullable=true) 
     */  
    protected $fechaModificacion;  

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @ORM\PrePersist
     */
    public function setUsuarioCreacion()
    {
        $usuario = $GLOBALS['kernel']->getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->usuarioCreacion = $usuario;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUsuarioModificacion()
    {
        $usuario = $GLOBALS['kernel']->getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->usuarioModificacion = $usuario;

    }

    /** 
     * Set FechaCreacion 
     * 
     * @ORM\PrePersist 
     */  
    public function setFechaCreacion()  
    {  
        $this->fechaCreacion = new \DateTime();  
        $this->fechaModificacion = new \DateTime();  
    }  
  
    /** 
     * Set FechaModificacion 
     * 
     * @ORM\PreUpdate 
     */  
    public function setFechaModificacion()  
    {  
        $this->fechaModificacion = new \DateTime();  
    }  
    
    /**
     * Set valor
     *
     * @param string $valor
     *
     * @return LogModificacion
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return string
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set valorNuevo
     *
     * @param string $valorNuevo
     *
     * @return LogModificacion
     */
    public function setValorNuevo($valorNuevo)
    {
        $this->valor_nuevo = $valorNuevo;

        return $this;
    }

    /**
     * Get valorNuevo
     *
     * @return string
     */
    public function getValorNuevo()
    {
        return $this->valor_nuevo;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return LogModificacion
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set modificacionTipo
     *
     * @param \AppBundle\Entity\ModificacionTipo $modificacionTipo
     *
     * @return LogModificacion
     */
    public function setModificacionTipo(\AppBundle\Entity\ModificacionTipo $modificacionTipo = null)
    {
        $this->modificacionTipo = $modificacionTipo;

        return $this;
    }

    /**
     * Get modificacionTipo
     *
     * @return \AppBundle\Entity\ModificacionTipo
     */
    public function getModificacionTipo()
    {
        return $this->modificacionTipo;
    }

    /**
     * Set productoXLocal
     *
     * @param \AppBundle\Entity\ProductoXLocal $productoXLocal
     *
     * @return LogModificacion
     */
    public function setProductoXLocal(\AppBundle\Entity\ProductoXLocal $productoXLocal = null)
    {
        $this->productoXLocal = $productoXLocal;

        return $this;
    }

    /**
     * Get productoXLocal
     *
     * @return \AppBundle\Entity\ProductoXLocal
     */
    public function getProductoXLocal()
    {
        return $this->productoXLocal;
    }

    /**
     * Set usuario
     *
     * @param \AppBundle\Entity\Usuario $usuario
     *
     * @return LogModificacion
     */
    public function setUsuario(\AppBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return \AppBundle\Entity\Usuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set facturaVenta
     *
     * @param \AppBundle\Entity\FacturaVenta $facturaVenta
     *
     * @return LogModificacion
     */
    public function setFacturaVenta(\AppBundle\Entity\FacturaVenta $facturaVenta = null)
    {
        $this->facturaVenta = $facturaVenta;

        return $this;
    }

    /**
     * Get facturaVenta
     *
     * @return \AppBundle\Entity\FacturaVenta
     */
    public function getFacturaVenta()
    {
        return $this->facturaVenta;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Get usuarioCreacion
     *
     * @return \AppBundle\Entity\Usuario
     */
    public function getUsuarioCreacion()
    {
        return $this->usuarioCreacion;
    }

    /**
     * Get usuarioModificacion
     *
     * @return \AppBundle\Entity\Usuario
     */
    public function getUsuarioModificacion()
    {
        return $this->usuarioModificacion;
    }
}
