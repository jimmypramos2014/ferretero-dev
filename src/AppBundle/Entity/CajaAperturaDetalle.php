<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CajaAperturaDetalle
 *
 * @ORM\Table(name="caja_apertura_detalle")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CajaAperturaDetalleRepository")
 */
class CajaAperturaDetalle
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
     * @ORM\Column(name="denominacion", type="string", length=100, nullable=true)
     */
    private $denominacion;

    /**
     * @var float
     *
     * @ORM\Column(name="cantidad", type="float", nullable=true)
     */
    private $cantidad;

    /**
     * @ORM\ManyToOne(targetEntity="CajaApertura", inversedBy="cajaAperturaDetalle", cascade={"persist"})
     * @ORM\JoinColumn(name="caja_apertura_id", referencedColumnName="id")
     */
    private $cajaApertura;

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="cajaAperturaDetalle", cascade={"persist"})
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     */
    private $usuario;
 
     /**
     * @var int
     *
     * @ORM\Column(name="identificador", type="integer", nullable=true)
     */
    private $identificador;
    

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
     * Set denominacion
     *
     * @param string $denominacion
     *
     * @return CajaAperturaDetalle
     */
    public function setDenominacion($denominacion)
    {
        $this->denominacion = $denominacion;

        return $this;
    }

    /**
     * Get denominacion
     *
     * @return string
     */
    public function getDenominacion()
    {
        return $this->denominacion;
    }

    /**
     * Set cantidad
     *
     * @param float $cantidad
     *
     * @return CajaAperturaDetalle
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
     * Set cajaApertura
     *
     * @param \AppBundle\Entity\CajaApertura $cajaApertura
     *
     * @return CajaAperturaDetalle
     */
    public function setCajaApertura(\AppBundle\Entity\CajaApertura $cajaApertura = null)
    {
        $this->cajaApertura = $cajaApertura;

        return $this;
    }

    /**
     * Get cajaApertura
     *
     * @return \AppBundle\Entity\CajaApertura
     */
    public function getCajaApertura()
    {
        return $this->cajaApertura;
    }

    /**
     * Set usuario
     *
     * @param \AppBundle\Entity\Usuario $usuario
     *
     * @return CajaAperturaDetalle
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
     * Set identificador
     *
     * @param integer $identificador
     *
     * @return CajaAperturaDetalle
     */
    public function setIdentificador($identificador)
    {
        $this->identificador = $identificador;

        return $this;
    }

    /**
     * Get identificador
     *
     * @return integer
     */
    public function getIdentificador()
    {
        return $this->identificador;
    }
}
