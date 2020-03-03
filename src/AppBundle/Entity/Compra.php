<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Compra
 *
 * @ORM\Table(name="compra")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CompraRepository")
 */
class Compra
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
     * @var string
     *
     * 
     * @Assert\File(maxSize="1M",mimeTypesMessage = "Este formato no es válido, solo se permiten jpeg,png o gif",mimeTypes={ "image/jpeg","image/png","image/gif" })
     *     
     * @ORM\Column(name="documento", type="string", length=100, nullable=true)
     */
    private $documento;

    /**
     * @ORM\ManyToOne(targetEntity="Empleado", inversedBy="compra", cascade={"persist"})
     * @ORM\JoinColumn(name="empleado_id", referencedColumnName="id")
     * 
     */
    protected $empleado;

     /**
     * @ORM\OneToMany(targetEntity="DetalleCompra", mappedBy="compra" , cascade={"remove","persist"})
     */
    protected $detalleCompra;

     /**
     * @ORM\OneToMany(targetEntity="CompraFormaPago", mappedBy="compra" , cascade={"remove","persist"})
     */
    protected $compraFormaPago;

     /**
     * @ORM\OneToMany(targetEntity="FacturaCompra", mappedBy="compra" , cascade={"remove"})
     */
    protected $facturaCompra;

    public function __toString()
    {
        return $this->getId().'';
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->detalleCompra = new \Doctrine\Common\Collections\ArrayCollection();
        $this->compraFormaPago = new \Doctrine\Common\Collections\ArrayCollection();
        $this->facturaCompra = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Compra
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
     * @return Compra
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
     * Set documento
     *
     * @param string $documento
     *
     * @return Compra
     */
    public function setDocumento($documento)
    {
        $this->documento = $documento;

        return $this;
    }

    /**
     * Get documento
     *
     * @return string
     */
    public function getDocumento()
    {
        return $this->documento;
    }

    /**
     * Set empleado
     *
     * @param \AppBundle\Entity\Empleado $empleado
     *
     * @return Compra
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
     * Add detalleCompra
     *
     * @param \AppBundle\Entity\DetalleCompra $detalleCompra
     *
     * @return Compra
     */
    public function addDetalleCompra(\AppBundle\Entity\DetalleCompra $detalleCompra)
    {
        $this->detalleCompra[] = $detalleCompra;

        return $this;
    }

    /**
     * Remove detalleCompra
     *
     * @param \AppBundle\Entity\DetalleCompra $detalleCompra
     */
    public function removeDetalleCompra(\AppBundle\Entity\DetalleCompra $detalleCompra)
    {
        $this->detalleCompra->removeElement($detalleCompra);
    }

    /**
     * Get detalleCompra
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDetalleCompra()
    {
        return $this->detalleCompra;
    }

    /**
     * Add compraFormaPago
     *
     * @param \AppBundle\Entity\CompraFormaPago $compraFormaPago
     *
     * @return Compra
     */
    public function addCompraFormaPago(\AppBundle\Entity\CompraFormaPago $compraFormaPago)
    {
        $this->compraFormaPago[] = $compraFormaPago;

        return $this;
    }

    /**
     * Remove compraFormaPago
     *
     * @param \AppBundle\Entity\CompraFormaPago $compraFormaPago
     */
    public function removeCompraFormaPago(\AppBundle\Entity\CompraFormaPago $compraFormaPago)
    {
        $this->compraFormaPago->removeElement($compraFormaPago);
    }

    /**
     * Get compraFormaPago
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCompraFormaPago()
    {
        return $this->compraFormaPago;
    }

    /**
     * Add facturaCompra
     *
     * @param \AppBundle\Entity\FacturaCompra $facturaCompra
     *
     * @return Compra
     */
    public function addFacturaCompra(\AppBundle\Entity\FacturaCompra $facturaCompra)
    {
        $this->facturaCompra[] = $facturaCompra;

        return $this;
    }

    /**
     * Remove facturaCompra
     *
     * @param \AppBundle\Entity\FacturaCompra $facturaCompra
     */
    public function removeFacturaCompra(\AppBundle\Entity\FacturaCompra $facturaCompra)
    {
        $this->facturaCompra->removeElement($facturaCompra);
    }

    /**
     * Get facturaCompra
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFacturaCompra()
    {
        return $this->facturaCompra;
    }
}
