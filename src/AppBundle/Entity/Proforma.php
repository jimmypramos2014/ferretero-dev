<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proforma
 *
 * @ORM\Table(name="proforma")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProformaRepository")
 */
class Proforma
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
     * @var string
     *
     * @ORM\Column(name="cliente", type="string", length=100, nullable=true)
     */
    private $cliente;

    /**
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=32, nullable=true)
     */
    private $numero;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="date", nullable=true)
     */
    private $fecha;

    /**
     * @var float
     *
     * @ORM\Column(name="total", type="float", nullable=true)
     */
    private $total;

    /**
     * @ORM\ManyToOne(targetEntity="Empresa", inversedBy="proforma", cascade={"persist"})
     * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id")
     * 
     */
    protected $empresa;

     /**
     * @ORM\OneToMany(targetEntity="DetalleProforma", mappedBy="proforma" , orphanRemoval=true,cascade={"remove","persist"})
     */
    protected $detalleProforma;

    /**
     * @var boolean
     *
     * @ORM\Column(name="condicion", type="boolean",nullable=true)
     */
    private $condicion;
    
    /**
     * @ORM\ManyToOne(targetEntity="FacturaVenta", inversedBy="proforma", cascade={"persist"})
     * @ORM\JoinColumn(name="factura_venta_id", referencedColumnName="id")
     * 
     */
    protected $facturaVenta;


    public function __toString()
    {
        return $this->getNumero();
    }

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
     * Set cliente
     *
     * @param string $cliente
     *
     * @return Proforma
     */
    public function setCliente($cliente)
    {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * Get cliente
     *
     * @return string
     */
    public function getCliente()
    {
        return $this->cliente;
    }

    /**
     * Set numero
     *
     * @param string $numero
     *
     * @return Proforma
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return string
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return Proforma
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
     * @return Proforma
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
     * Set empresa
     *
     * @param \AppBundle\Entity\Empresa $empresa
     *
     * @return Proforma
     */
    public function setEmpresa(\AppBundle\Entity\Empresa $empresa = null)
    {
        $this->empresa = $empresa;

        return $this;
    }

    /**
     * Get empresa
     *
     * @return \AppBundle\Entity\Empresa
     */
    public function getEmpresa()
    {
        return $this->empresa;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->detalleProforma = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add detalleProforma
     *
     * @param \AppBundle\Entity\DetalleProforma $detalleProforma
     *
     * @return Proforma
     */
    public function addDetalleProforma(\AppBundle\Entity\DetalleProforma $detalleProforma)
    {
        $this->detalleProforma[] = $detalleProforma;

        return $this;
    }

    /**
     * Remove detalleProforma
     *
     * @param \AppBundle\Entity\DetalleProforma $detalleProforma
     */
    public function removeDetalleProforma(\AppBundle\Entity\DetalleProforma $detalleProforma)
    {
        $this->detalleProforma->removeElement($detalleProforma);
    }

    /**
     * Get detalleProforma
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDetalleProforma()
    {
        return $this->detalleProforma;
    }

    /**
     * Set condicion
     *
     * @param boolean $condicion
     *
     * @return Proforma
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
     * Set facturaVenta
     *
     * @param \AppBundle\Entity\FacturaVenta $facturaVenta
     *
     * @return Proforma
     */
    public function setFacturaVenta(\AppBundle\Entity\FacturaVenta $facturaVenta = null)
    {
        $this->facturaVenta = $facturaVenta;

        return $this;
    }

    /**
     * Get facturaVenta
     *
     * @return \AppBundle\Entity\FacturaVenta
     */
    public function getFacturaVenta()
    {
        return $this->facturaVenta;
    }
}
