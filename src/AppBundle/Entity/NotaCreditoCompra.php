<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotaCreditoCompra
 *
 * @ORM\Table(name="nota_credito_compra")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NotaCreditoCompraRepository")
 */
class NotaCreditoCompra
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
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=100, nullable=true)
     */
    private $numero;

    /**
     * @var float
     *
     * @ORM\Column(name="valor", type="float", nullable=true)
     */
    private $valor;

    /**
     * @var bool
     *
     * @ORM\Column(name="estado", type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity="EmpresaLocal", inversedBy="notaCreditoCompra", cascade={"persist"})
     * @ORM\JoinColumn(name="empresa_local_id", referencedColumnName="id")
     * 
     */
    protected $local;

    /**
     * @ORM\ManyToMany(targetEntity="FacturaCompra", inversedBy="notaCreditoCompra")
     * @ORM\JoinTable(name="nota_credito_x_factura_compra")
     * 
     */
    private $facturaCompra;

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
     * @return NotaCreditoCompra
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
     * Set numero
     *
     * @param string $numero
     *
     * @return NotaCreditoCompra
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
     * Set valor
     *
     * @param float $valor
     *
     * @return NotaCreditoCompra
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return float
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set estado
     *
     * @param boolean $estado
     *
     * @return NotaCreditoCompra
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return bool
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set local
     *
     * @param \AppBundle\Entity\EmpresaLocal $local
     *
     * @return NotaCreditoCompra
     */
    public function setLocal(\AppBundle\Entity\EmpresaLocal $local = null)
    {
        $this->local = $local;

        return $this;
    }

    /**
     * Get local
     *
     * @return \AppBundle\Entity\EmpresaLocal
     */
    public function getLocal()
    {
        return $this->local;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->facturaCompra = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add facturaCompra
     *
     * @param \AppBundle\Entity\FacturaCompra $facturaCompra
     *
     * @return NotaCreditoCompra
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
