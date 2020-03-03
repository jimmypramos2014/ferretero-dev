<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DetalleVentaEntrega
 *
 * @ORM\Table(name="detalle_venta_entrega")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DetalleVentaEntregaRepository")
 */
class DetalleVentaEntrega
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
     * @var float
     *
     * @ORM\Column(name="cantidad", type="float", nullable=true)
     */
    private $cantidad;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=true)
     */
    private $fecha;

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="detalleVentaEntrega", cascade={"persist"})
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * 
     */
    protected $usuario;

    /**
     * @ORM\ManyToOne(targetEntity="DetalleVenta", inversedBy="detalleVentaEntrega", cascade={"persist"})
     * @ORM\JoinColumn(name="detalle_venta_id", referencedColumnName="id")
     * 
     */
    protected $detalleVenta;

    /**
     * @var string
     *
     * @ORM\Column(name="identificador", type="string", nullable=true)
     */
    private $identificador;




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
     * Set cantidad
     *
     * @param float $cantidad
     *
     * @return DetalleVentaEntrega
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return float
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return DetalleVentaEntrega
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
     * Set identificador
     *
     * @param string $identificador
     *
     * @return DetalleVentaEntrega
     */
    public function setIdentificador($identificador)
    {
        $this->identificador = $identificador;

        return $this;
    }

    /**
     * Get identificador
     *
     * @return string
     */
    public function getIdentificador()
    {
        return $this->identificador;
    }

    /**
     * Set usuario
     *
     * @param \AppBundle\Entity\Usuario $usuario
     *
     * @return DetalleVentaEntrega
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
     * Set detalleVenta
     *
     * @param \AppBundle\Entity\DetalleVenta $detalleVenta
     *
     * @return DetalleVentaEntrega
     */
    public function setDetalleVenta(\AppBundle\Entity\DetalleVenta $detalleVenta = null)
    {
        $this->detalleVenta = $detalleVenta;

        return $this;
    }

    /**
     * Get detalleVenta
     *
     * @return \AppBundle\Entity\DetalleVenta
     */
    public function getDetalleVenta()
    {
        return $this->detalleVenta;
    }
}
