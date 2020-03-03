<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GuiaRemisionDetalle
 *
 * @ORM\Table(name="guia_remision_detalle")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GuiaRemisionDetalleRepository")
 */
class GuiaRemisionDetalle
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
     * @var string
     *
     * @ORM\Column(name="detalle", type="string", length=100, nullable=true)
     */
    private $detalle;

    /**
     * @ORM\ManyToOne(targetEntity="ProductoXLocal", inversedBy="guiaRemisionDetalle", cascade={"persist"})
     * @ORM\JoinColumn(name="producto_x_local_id", referencedColumnName="id")
     * 
     */
    protected $productoXLocal;

    /**
     * @ORM\ManyToOne(targetEntity="GuiaRemision", inversedBy="guiaRemisionDetalle", cascade={"persist"})
     * @ORM\JoinColumn(name="guia_remision_id", referencedColumnName="id")
     * 
     */
    protected $guiaRemision;

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
     * @return GuiaRemisionDetalle
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
     * Set detalle
     *
     * @param string $detalle
     *
     * @return GuiaRemisionDetalle
     */
    public function setDetalle($detalle)
    {
        $this->detalle = $detalle;

        return $this;
    }

    /**
     * Get detalle
     *
     * @return string
     */
    public function getDetalle()
    {
        return $this->detalle;
    }

    /**
     * Set productoXLocal
     *
     * @param \AppBundle\Entity\ProductoXLocal $productoXLocal
     *
     * @return GuiaRemisionDetalle
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
     * Set guiaRemision
     *
     * @param \AppBundle\Entity\GuiaRemision $guiaRemision
     *
     * @return GuiaRemisionDetalle
     */
    public function setGuiaRemision(\AppBundle\Entity\GuiaRemision $guiaRemision = null)
    {
        $this->guiaRemision = $guiaRemision;

        return $this;
    }

    /**
     * Get guiaRemision
     *
     * @return \AppBundle\Entity\GuiaRemision
     */
    public function getGuiaRemision()
    {
        return $this->guiaRemision;
    }
}
