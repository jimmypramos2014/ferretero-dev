<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransferenciaXProducto
 *
 * @ORM\Table(name="transferencia_x_producto")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TransferenciaXProductoRepository")
 */
class TransferenciaXProducto
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
     * @ORM\ManyToOne(targetEntity="ProductoXLocal", inversedBy="transferenciaXProducto", cascade={"persist"})
     * @ORM\JoinColumn(name="producto_x_local_id", referencedColumnName="id")
     * 
     */
    protected $productoXLocal;

    /**
     * @ORM\ManyToOne(targetEntity="Transferencia", inversedBy="transferenciaXProducto", cascade={"persist"})
     * @ORM\JoinColumn(name="transferencia_id", referencedColumnName="id")
     * 
     */
    protected $transferencia;
    
    /**
     * @var float
     *
     * @ORM\Column(name="precio", type="float", nullable=true)
     */
    private $precio;

    /**
     * @var float
     *
     * @ORM\Column(name="contador", type="float", nullable=true)
     */
    private $contador;
    
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
     * @return TransferenciaXProducto
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
     * Set productoXLocal
     *
     * @param \AppBundle\Entity\ProductoXLocal $productoXLocal
     *
     * @return TransferenciaXProducto
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
     * Set transferencia
     *
     * @param \AppBundle\Entity\Transferencia $transferencia
     *
     * @return TransferenciaXProducto
     */
    public function setTransferencia(\AppBundle\Entity\Transferencia $transferencia = null)
    {
        $this->transferencia = $transferencia;

        return $this;
    }

    /**
     * Get transferencia
     *
     * @return \AppBundle\Entity\Transferencia
     */
    public function getTransferencia()
    {
        return $this->transferencia;
    }

    /**
     * Set precio
     *
     * @param float $precio
     *
     * @return TransferenciaXProducto
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
     * Set contador
     *
     * @param float $contador
     *
     * @return TransferenciaXProducto
     */
    public function setContador($contador)
    {
        $this->contador = $contador;

        return $this;
    }

    /**
     * Get contador
     *
     * @return float
     */
    public function getContador()
    {
        return $this->contador;
    }
}
