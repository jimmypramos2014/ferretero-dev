<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Venta
 *
 * @ORM\Table(name="venta")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VentaRepository")
 */
class Venta
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
     * @var float
     *
     * @ORM\Column(name="total", type="float", nullable=true)
     */
    private $total;

    /**
     * @ORM\ManyToOne(targetEntity="Empleado", inversedBy="venta", cascade={"persist"})
     * @ORM\JoinColumn(name="empleado_id", referencedColumnName="id")
     * 
     */
    protected $empleado;

     /**
     * @ORM\OneToMany(targetEntity="DetalleVenta", mappedBy="venta" , cascade={"remove","persist"})
     */
    protected $detalleVenta;

    /**
     * @var boolean
     *
     * @ORM\Column(name="estado", type="boolean", options={"default":1, "comment":"Estado del registro"})
     */
    private $estado;
   
    /**
     * @var string
     *
     * @ORM\Column(name="motivo_anulacion", type="string", length=100, nullable=true)
     */
    private $motivo_anulacion;

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="venta", cascade={"persist"})
     * @ORM\JoinColumn(name="usuario_anulacion_id", referencedColumnName="id")
     * 
     */
    protected $usuario_anulacion;

     /**
     * @ORM\OneToMany(targetEntity="VentaFormaPago", mappedBy="venta" , cascade={"remove","persist"})
     */
    protected $ventaFormaPago;

     /**
     * @ORM\OneToMany(targetEntity="FacturaVenta", mappedBy="venta" , cascade={"remove"})
     */
    protected $facturaVenta;

    /**
     * @var boolean
     *
     * @ORM\Column(name="condicion", type="boolean",nullable=true)
     */
    private $condicion;
 
     /**
     * @var string
     *
     * @ORM\Column(name="ticket_anulacion", type="string", length=32, nullable=true)
     */
    private $ticketAnulacion;

    public function __toString()
    {
        return '';
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->detalleVenta = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ventaFormaPago = new \Doctrine\Common\Collections\ArrayCollection();
        $this->facturaVenta = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Venta
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
     * Set total
     *
     * @param float $total
     *
     * @return Venta
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set estado
     *
     * @param boolean $estado
     *
     * @return Venta
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
     * Set motivoAnulacion
     *
     * @param string $motivoAnulacion
     *
     * @return Venta
     */
    public function setMotivoAnulacion($motivoAnulacion)
    {
        $this->motivo_anulacion = $motivoAnulacion;

        return $this;
    }

    /**
     * Get motivoAnulacion
     *
     * @return string
     */
    public function getMotivoAnulacion()
    {
        return $this->motivo_anulacion;
    }

    /**
     * Set condicion
     *
     * @param boolean $condicion
     *
     * @return Venta
     */
    public function setCondicion($condicion)
    {
        $this->condicion = $condicion;

        return $this;
    }

    /**
     * Get condicion
     *
     * @return boolean
     */
    public function getCondicion()
    {
        return $this->condicion;
    }

    /**
     * Set empleado
     *
     * @param \AppBundle\Entity\Empleado $empleado
     *
     * @return Venta
     */
    public function setEmpleado(\AppBundle\Entity\Empleado $empleado = null)
    {
        $this->empleado = $empleado;

        return $this;
    }

    /**
     * Get empleado
     *
     * @return \AppBundle\Entity\Empleado
     */
    public function getEmpleado()
    {
        return $this->empleado;
    }

    /**
     * Add detalleVentum
     *
     * @param \AppBundle\Entity\DetalleVenta $detalleVentum
     *
     * @return Venta
     */
    public function addDetalleVentum(\AppBundle\Entity\DetalleVenta $detalleVentum)
    {
        $this->detalleVenta[] = $detalleVentum;

        return $this;
    }

    /**
     * Remove detalleVentum
     *
     * @param \AppBundle\Entity\DetalleVenta $detalleVentum
     */
    public function removeDetalleVentum(\AppBundle\Entity\DetalleVenta $detalleVentum)
    {
        $this->detalleVenta->removeElement($detalleVentum);
    }

    /**
     * Get detalleVenta
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDetalleVenta()
    {
        return $this->detalleVenta;
    }

    /**
     * Set usuarioAnulacion
     *
     * @param \AppBundle\Entity\Usuario $usuarioAnulacion
     *
     * @return Venta
     */
    public function setUsuarioAnulacion(\AppBundle\Entity\Usuario $usuarioAnulacion = null)
    {
        $this->usuario_anulacion = $usuarioAnulacion;

        return $this;
    }

    /**
     * Get usuarioAnulacion
     *
     * @return \AppBundle\Entity\Usuario
     */
    public function getUsuarioAnulacion()
    {
        return $this->usuario_anulacion;
    }

    /**
     * Add ventaFormaPago
     *
     * @param \AppBundle\Entity\VentaFormaPago $ventaFormaPago
     *
     * @return Venta
     */
    public function addVentaFormaPago(\AppBundle\Entity\VentaFormaPago $ventaFormaPago)
    {
        $this->ventaFormaPago[] = $ventaFormaPago;

        return $this;
    }

    /**
     * Remove ventaFormaPago
     *
     * @param \AppBundle\Entity\VentaFormaPago $ventaFormaPago
     */
    public function removeVentaFormaPago(\AppBundle\Entity\VentaFormaPago $ventaFormaPago)
    {
        $this->ventaFormaPago->removeElement($ventaFormaPago);
    }

    /**
     * Get ventaFormaPago
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVentaFormaPago()
    {
        return $this->ventaFormaPago;
    }

    /**
     * Add facturaVentum
     *
     * @param \AppBundle\Entity\FacturaVenta $facturaVentum
     *
     * @return Venta
     */
    public function addFacturaVentum(\AppBundle\Entity\FacturaVenta $facturaVentum)
    {
        $this->facturaVenta[] = $facturaVentum;

        return $this;
    }

    /**
     * Remove facturaVentum
     *
     * @param \AppBundle\Entity\FacturaVenta $facturaVentum
     */
    public function removeFacturaVentum(\AppBundle\Entity\FacturaVenta $facturaVentum)
    {
        $this->facturaVenta->removeElement($facturaVentum);
    }

    /**
     * Get facturaVenta
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFacturaVenta()
    {
        return $this->facturaVenta;
    }

    /**
     * Set ticketAnulacion
     *
     * @param string $ticketAnulacion
     *
     * @return Venta
     */
    public function setTicketAnulacion($ticketAnulacion)
    {
        $this->ticketAnulacion = $ticketAnulacion;

        return $this;
    }

    /**
     * Get ticketAnulacion
     *
     * @return string
     */
    public function getTicketAnulacion()
    {
        return $this->ticketAnulacion;
    }
}
