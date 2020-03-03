<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CajaApertura
 *
 * @ORM\Table(name="caja_apertura")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CajaAperturaRepository")
 */
class CajaApertura
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
     * @ORM\Column(name="monto_apertura", type="float", nullable=true)
     */
    private $montoApertura;

    /**
     * @var bool
     *
     * @ORM\Column(name="estado", type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity="Caja", inversedBy="cajaApertura", cascade={"persist"})
     * @ORM\JoinColumn(name="caja_id", referencedColumnName="id")
     */
    private $caja;

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="cajaApertura", cascade={"persist"})
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     */
    private $usuario;

     /**
     * @ORM\OneToMany(targetEntity="CajaAperturaDetalle", mappedBy="cajaApertura" , cascade={"remove","persist"})
     */
    protected $cajaAperturaDetalle;


    public function __toString()
    {
        return '';
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cajaAperturaDetalle = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return CajaApertura
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
     * Set montoApertura
     *
     * @param float $montoApertura
     *
     * @return CajaApertura
     */
    public function setMontoApertura($montoApertura)
    {
        $this->montoApertura = $montoApertura;

        return $this;
    }

    /**
     * Get montoApertura
     *
     * @return float
     */
    public function getMontoApertura()
    {
        return $this->montoApertura;
    }

    /**
     * Set estado
     *
     * @param boolean $estado
     *
     * @return CajaApertura
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return boolean
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set caja
     *
     * @param \AppBundle\Entity\Caja $caja
     *
     * @return CajaApertura
     */
    public function setCaja(\AppBundle\Entity\Caja $caja = null)
    {
        $this->caja = $caja;

        return $this;
    }

    /**
     * Get caja
     *
     * @return \AppBundle\Entity\Caja
     */
    public function getCaja()
    {
        return $this->caja;
    }

    /**
     * Set usuario
     *
     * @param \AppBundle\Entity\Usuario $usuario
     *
     * @return CajaApertura
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

    /**
     * Add cajaAperturaDetalle
     *
     * @param \AppBundle\Entity\CajaAperturaDetalle $cajaAperturaDetalle
     *
     * @return CajaApertura
     */
    public function addCajaAperturaDetalle(\AppBundle\Entity\CajaAperturaDetalle $cajaAperturaDetalle)
    {
        $this->cajaAperturaDetalle[] = $cajaAperturaDetalle;

        return $this;
    }

    /**
     * Remove cajaAperturaDetalle
     *
     * @param \AppBundle\Entity\CajaAperturaDetalle $cajaAperturaDetalle
     */
    public function removeCajaAperturaDetalle(\AppBundle\Entity\CajaAperturaDetalle $cajaAperturaDetalle)
    {
        $this->cajaAperturaDetalle->removeElement($cajaAperturaDetalle);
    }

    /**
     * Get cajaAperturaDetalle
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCajaAperturaDetalle()
    {
        return $this->cajaAperturaDetalle;
    }
}
