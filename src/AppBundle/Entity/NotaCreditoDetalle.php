<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotaCreditoDetalle
 *
 * @ORM\Table(name="nota_credito_detalle")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NotaCreditoDetalleRepository")
 */
class NotaCreditoDetalle
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
     * @var float
     *
     * @ORM\Column(name="precio", type="float", nullable=true)
     */
    private $precio;

    /**
     * @var float
     *
     * @ORM\Column(name="impuesto", type="float", nullable=true)
     */
    private $impuesto;

    /**
     * @var float
     *
     * @ORM\Column(name="subtotal", type="float", nullable=true)
     */
    private $subtotal;

    /**
     * @var float
     *
     * @ORM\Column(name="descuento", type="float", nullable=true)
     */
    private $descuento;

    /**
     * @ORM\ManyToOne(targetEntity="NotaCredito", inversedBy="notaCreditoDetalle", cascade={"persist"})
     * @ORM\JoinColumn(name="nota_credito_id", referencedColumnName="id")
     * 
     */
    protected $notaCredito;

    /**
     * @ORM\ManyToOne(targetEntity="ProductoXLocal", inversedBy="notaCreditoDetalle", cascade={"persist"})
     * @ORM\JoinColumn(name="producto_x_local_id", referencedColumnName="id")
     * 
     */
    protected $productoXLocal;
    
    /**
     * @ORM\ManyToOne(targetEntity="TipoImpuesto", inversedBy="notaCreditoDetalle", cascade={"persist"})
     * @ORM\JoinColumn(name="tipo_impuesto_id", referencedColumnName="id")
     * 
     */
    protected $tipoImpuesto;

    /**
     * Get id
     *
     * @return int
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
     * @return NotaCreditoDetalle
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
     * Set precio
     *
     * @param float $precio
     *
     * @return NotaCreditoDetalle
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;

        return $this;
    }

    /**
     * Get precio
     *
     * @return float
     */
    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * Set impuesto
     *
     * @param float $impuesto
     *
     * @return NotaCreditoDetalle
     */
    public function setImpuesto($impuesto)
    {
        $this->impuesto = $impuesto;

        return $this;
    }

    /**
     * Get impuesto
     *
     * @return float
     */
    public function getImpuesto()
    {
        return $this->impuesto;
    }

    /**
     * Set subtotal
     *
     * @param float $subtotal
     *
     * @return NotaCreditoDetalle
     */
    public function setSubtotal($subtotal)
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    /**
     * Get subtotal
     *
     * @return float
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * Set descuento
     *
     * @param float $descuento
     *
     * @return NotaCreditoDetalle
     */
    public function setDescuento($descuento)
    {
        $this->descuento = $descuento;

        return $this;
    }

    /**
     * Get descuento
     *
     * @return float
     */
    public function getDescuento()
    {
        return $this->descuento;
    }

    /**
     * Set notaCredito
     *
     * @param \AppBundle\Entity\NotaCredito $notaCredito
     *
     * @return NotaCreditoDetalle
     */
    public function setNotaCredito(\AppBundle\Entity\NotaCredito $notaCredito = null)
    {
        $this->notaCredito = $notaCredito;

        return $this;
    }

    /**
     * Get notaCredito
     *
     * @return \AppBundle\Entity\NotaCredito
     */
    public function getNotaCredito()
    {
        return $this->notaCredito;
    }

    /**
     * Set productoXLocal
     *
     * @param \AppBundle\Entity\ProductoXLocal $productoXLocal
     *
     * @return NotaCreditoDetalle
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
     * Set tipoImpuesto
     *
     * @param \AppBundle\Entity\TipoImpuesto $tipoImpuesto
     *
     * @return NotaCreditoDetalle
     */
    public function setTipoImpuesto(\AppBundle\Entity\TipoImpuesto $tipoImpuesto = null)
    {
        $this->tipoImpuesto = $tipoImpuesto;

        return $this;
    }

    /**
     * Get tipoImpuesto
     *
     * @return \AppBundle\Entity\TipoImpuesto
     */
    public function getTipoImpuesto()
    {
        return $this->tipoImpuesto;
    }
}
