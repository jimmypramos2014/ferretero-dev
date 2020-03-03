<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * TipoCambio
 *
 * @ORM\Table(name="tipo_cambio")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TipoCambioRepository")
 * @UniqueEntity(fields={"fecha","empresa"}, message="Ya existe un valor para esta fecha.")
 */
class TipoCambio
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
     * @var \Date
     *
     * @ORM\Column(name="fecha", type="date", nullable=true)
     * @Assert\NotBlank(message="Este valor es requerido")
     */
    private $fecha;


    /**
     * @ORM\ManyToOne(targetEntity="Empresa", inversedBy="tipoCambio", cascade={"persist"})
     * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id")
     * 
     */
    protected $empresa;

    /**
     * @var float
     *
     * @ORM\Column(name="compra", type="float", nullable=true)
     * @Assert\NotBlank(message="Este valor es requerido")
     */
    private $compra;


    /**
     * @var float
     *
     * @ORM\Column(name="venta", type="float", nullable=true)
     * @Assert\NotBlank(message="Este valor es requerido")
     */
    private $venta;


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
     * @return TipoCambio
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
     * Set compra
     *
     * @param float $compra
     *
     * @return TipoCambio
     */
    public function setCompra($compra)
    {
        $this->compra = $compra;

        return $this;
    }

    /**
     * Get compra
     *
     * @return float
     */
    public function getCompra()
    {
        return $this->compra;
    }

    /**
     * Set venta
     *
     * @param float $venta
     *
     * @return TipoCambio
     */
    public function setVenta($venta)
    {
        $this->venta = $venta;

        return $this;
    }

    /**
     * Get venta
     *
     * @return float
     */
    public function getVenta()
    {
        return $this->venta;
    }

    /**
     * Set empresa
     *
     * @param \AppBundle\Entity\Empresa $empresa
     *
     * @return TipoCambio
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
}
