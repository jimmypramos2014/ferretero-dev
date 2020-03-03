<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DetalleCompraAnulada
 *
 * @ORM\Table(name="detalle_compra_anulada")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DetalleCompraAnuladaRepository")
 */
class DetalleCompraAnulada
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
     * @ORM\Column(name="cantidad", type="float", nullable=true)
     */
    private $cantidad;

    /**
     * @ORM\ManyToOne(targetEntity="Compra", inversedBy="detalleCompraAnulada", cascade={"persist"})
     * @ORM\JoinColumn(name="compra_id", referencedColumnName="id")
     * 
     */
    protected $compra;

    /**
     * @ORM\ManyToOne(targetEntity="ProductoXLocal", inversedBy="detalleCompraAnulada", cascade={"persist"})
     * @ORM\JoinColumn(name="producto_x_local_id", referencedColumnName="id")
     * 
     */
    protected $productoXLocal;
    
    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="detalleCompraAnulada", cascade={"persist"})
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * 
     */
    protected $usuario;

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
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return DetalleCompraAnulada
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
     * Set cantidad
     *
     * @param float $cantidad
     *
     * @return DetalleCompraAnulada
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
     * Set compra
     *
     * @param \AppBundle\Entity\Compra $compra
     *
     * @return DetalleCompraAnulada
     */
    public function setCompra(\AppBundle\Entity\Compra $compra = null)
    {
        $this->compra = $compra;

        return $this;
    }

    /**
     * Get compra
     *
     * @return \AppBundle\Entity\Compra
     */
    public function getCompra()
    {
        return $this->compra;
    }

    /**
     * Set productoXLocal
     *
     * @param \AppBundle\Entity\ProductoXLocal $productoXLocal
     *
     * @return DetalleCompraAnulada
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
     * @return DetalleCompraAnulada
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
}
