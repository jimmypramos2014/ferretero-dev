<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PedidoVentaDetalle
 *
 * @ORM\Table(name="pedido_venta_detalle")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PedidoVentaDetalleRepository")
 */
class PedidoVentaDetalle
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
     * @ORM\Column(name="cantidad_pedida", type="float", nullable=true)
     */
    private $cantidadPedida;

    /**
     * @var float
     *
     * @ORM\Column(name="cantidad_entregada", type="float", nullable=true)
     */
    private $cantidadEntregada;

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
     * @ORM\ManyToOne(targetEntity="PedidoVenta", inversedBy="pedidoVentaDetalle", cascade={"persist"})
     * @ORM\JoinColumn(name="pedido_venta_id", referencedColumnName="id")
     * 
     */
    protected $pedidoVenta;

    /**
     * @ORM\ManyToOne(targetEntity="ProductoXLocal", inversedBy="pedidoVentaDetalle", cascade={"persist"})
     * @ORM\JoinColumn(name="producto_x_local_id", referencedColumnName="id")
     * 
     */
    protected $productoXLocal;
    
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
     * Set cantidadPedida
     *
     * @param float $cantidadPedida
     *
     * @return PedidoVentaDetalle
     */
    public function setCantidadPedida($cantidadPedida)
    {
        $this->cantidadPedida = $cantidadPedida;

        return $this;
    }

    /**
     * Get cantidadPedida
     *
     * @return float
     */
    public function getCantidadPedida()
    {
        return $this->cantidadPedida;
    }

    /**
     * Set cantidadEntregada
     *
     * @param float $cantidadEntregada
     *
     * @return PedidoVentaDetalle
     */
    public function setCantidadEntregada($cantidadEntregada)
    {
        $this->cantidadEntregada = $cantidadEntregada;

        return $this;
    }

    /**
     * Get cantidadEntregada
     *
     * @return float
     */
    public function getCantidadEntregada()
    {
        return $this->cantidadEntregada;
    }

    /**
     * Set precio
     *
     * @param float $precio
     *
     * @return PedidoVentaDetalle
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
     * @return PedidoVentaDetalle
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
     * @return PedidoVentaDetalle
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
     * Set pedidoVenta
     *
     * @param \AppBundle\Entity\PedidoVenta $pedidoVenta
     *
     * @return PedidoVentaDetalle
     */
    public function setPedidoVenta(\AppBundle\Entity\PedidoVenta $pedidoVenta = null)
    {
        $this->pedidoVenta = $pedidoVenta;

        return $this;
    }

    /**
     * Get pedidoVenta
     *
     * @return \AppBundle\Entity\PedidoVenta
     */
    public function getPedidoVenta()
    {
        return $this->pedidoVenta;
    }

    /**
     * Set productoXLocal
     *
     * @param \AppBundle\Entity\ProductoXLocal $productoXLocal
     *
     * @return PedidoVentaDetalle
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
}
