<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PedidoVentaEntrega
 *
 * @ORM\Table(name="pedido_venta_entrega")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PedidoVentaEntregaRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class PedidoVentaEntrega
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
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=true)
     */
    private $fecha;

    /**
     * @var string
     *
     * @ORM\Column(name="observacion", type="string", length=100, nullable=true)
     */
    private $observacion;

    /**
     * @var bool
     *
     * @ORM\Column(name="estado", type="boolean", nullable=true)
     */
    private $estado;
    
    /**
     * 
     * @ORM\ManyToMany(targetEntity="GuiaRemision", mappedBy="pedidoVentaEntrega")
     */
    private $guiaRemision;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario", inversedBy="pedidoVentaEntrega", cascade={"persist"})
     * @ORM\JoinColumn(name="usuario_creacion", referencedColumnName="id")
     */
    private $usuarioCreacion;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario", inversedBy="pedidoVentaEntrega", cascade={"persist"})
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
     * Constructor
     */
    public function __construct()
    {
        $this->guiaRemision = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return PedidoVentaEntrega
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
     * Set observacion
     *
     * @param string $observacion
     *
     * @return PedidoVentaEntrega
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;

        return $this;
    }

    /**
     * Get observacion
     *
     * @return string
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set estado
     *
     * @param boolean $estado
     *
     * @return PedidoVentaEntrega
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return boolean
     */
    public function getEstado()
    {
        return $this->estado;
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
     * Add guiaRemision
     *
     * @param \AppBundle\Entity\GuiaRemision $guiaRemision
     *
     * @return PedidoVentaEntrega
     */
    public function addGuiaRemision(\AppBundle\Entity\GuiaRemision $guiaRemision)
    {
        $this->guiaRemision[] = $guiaRemision;

        return $this;
    }

    /**
     * Remove guiaRemision
     *
     * @param \AppBundle\Entity\GuiaRemision $guiaRemision
     */
    public function removeGuiaRemision(\AppBundle\Entity\GuiaRemision $guiaRemision)
    {
        $this->guiaRemision->removeElement($guiaRemision);
    }

    /**
     * Get guiaRemision
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGuiaRemision()
    {
        return $this->guiaRemision;
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
