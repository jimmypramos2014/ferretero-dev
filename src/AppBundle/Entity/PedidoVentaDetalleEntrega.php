<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PedidoVentaDetalleEntrega
 *
 * @ORM\Table(name="pedido_venta_detalle_entrega")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PedidoVentaDetalleEntregaRepository")
 */
class PedidoVentaDetalleEntrega
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
     * @ORM\ManyToOne(targetEntity="PedidoVentaDetalle", inversedBy="pedidoVentaDetalleEntrega", cascade={"persist"})
     * @ORM\JoinColumn(name="pedido_venta_detalle_id", referencedColumnName="id")
     * 
     */
    protected $pedidoVentaDetalle;

    /**
     * @ORM\ManyToOne(targetEntity="PedidoVentaEntrega", inversedBy="pedidoVentaDetalleEntrega", cascade={"persist"})
     * @ORM\JoinColumn(name="pedido_venta_entrega_id", referencedColumnName="id")
     * 
     */
    protected $pedidoVentaEntrega;

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
     * @return PedidoVentaDetalleEntrega
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
     * Set pedidoVentaDetalle
     *
     * @param \AppBundle\Entity\PedidoVentaDetalle $pedidoVentaDetalle
     *
     * @return PedidoVentaDetalleEntrega
     */
    public function setPedidoVentaDetalle(\AppBundle\Entity\PedidoVentaDetalle $pedidoVentaDetalle = null)
    {
        $this->pedidoVentaDetalle = $pedidoVentaDetalle;

        return $this;
    }

    /**
     * Get pedidoVentaDetalle
     *
     * @return \AppBundle\Entity\PedidoVentaDetalle
     */
    public function getPedidoVentaDetalle()
    {
        return $this->pedidoVentaDetalle;
    }

    /**
     * Set pedidoVentaEntrega
     *
     * @param \AppBundle\Entity\PedidoVentaEntrega $pedidoVentaEntrega
     *
     * @return PedidoVentaDetalleEntrega
     */
    public function setPedidoVentaEntrega(\AppBundle\Entity\PedidoVentaEntrega $pedidoVentaEntrega = null)
    {
        $this->pedidoVentaEntrega = $pedidoVentaEntrega;

        return $this;
    }

    /**
     * Get pedidoVentaEntrega
     *
     * @return \AppBundle\Entity\PedidoVentaEntrega
     */
    public function getPedidoVentaEntrega()
    {
        return $this->pedidoVentaEntrega;
    }
}
