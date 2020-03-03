<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CajaCierre
 *
 * @ORM\Table(name="caja_cierre")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CajaCierreRepository")
 */
class CajaCierre
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
     * @ORM\Column(name="total_deposito", type="float", nullable=true)
     */
    private $totalDeposito;

    /**
     * @var float
     *
     * @ORM\Column(name="total_transferencia", type="float", nullable=true)
     */
    private $totalTransferencia;

    /**
     * @var float
     *
     * @ORM\Column(name="total_cheque", type="float", nullable=true)
     */
    private $totalCheque;

    /**
     * @var float
     *
     * @ORM\Column(name="total_efectivo", type="float", nullable=true)
     */
    private $totalEfectivo;

    /**
     * @var float
     *
     * @ORM\Column(name="total_recaudado", type="float", nullable=true)
     */
    private $totalRecaudado;

    /**
     * @var bool
     *
     * @ORM\Column(name="estado", type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity="CajaApertura", inversedBy="cajaCierre", cascade={"persist"})
     * @ORM\JoinColumn(name="caja_apertura_id", referencedColumnName="id")
     */
    private $cajaApertura;

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="cajaCierre", cascade={"persist"})
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     */
    private $usuario;
   
    /**
     * @var float
     *
     * @ORM\Column(name="total_dejada", type="float", nullable=true)
     */
    private $totalDejada;

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
     * @return CajaCierre
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
     * Set totalDeposito
     *
     * @param float $totalDeposito
     *
     * @return CajaCierre
     */
    public function setTotalDeposito($totalDeposito)
    {
        $this->totalDeposito = $totalDeposito;

        return $this;
    }

    /**
     * Get totalDeposito
     *
     * @return float
     */
    public function getTotalDeposito()
    {
        return $this->totalDeposito;
    }

    /**
     * Set totalTransferencia
     *
     * @param float $totalTransferencia
     *
     * @return CajaCierre
     */
    public function setTotalTransferencia($totalTransferencia)
    {
        $this->totalTransferencia = $totalTransferencia;

        return $this;
    }

    /**
     * Get totalTransferencia
     *
     * @return float
     */
    public function getTotalTransferencia()
    {
        return $this->totalTransferencia;
    }

    /**
     * Set totalCheque
     *
     * @param float $totalCheque
     *
     * @return CajaCierre
     */
    public function setTotalCheque($totalCheque)
    {
        $this->totalCheque = $totalCheque;

        return $this;
    }

    /**
     * Get totalCheque
     *
     * @return float
     */
    public function getTotalCheque()
    {
        return $this->totalCheque;
    }

    /**
     * Set totalEfectivo
     *
     * @param float $totalEfectivo
     *
     * @return CajaCierre
     */
    public function setTotalEfectivo($totalEfectivo)
    {
        $this->totalEfectivo = $totalEfectivo;

        return $this;
    }

    /**
     * Get totalEfectivo
     *
     * @return float
     */
    public function getTotalEfectivo()
    {
        return $this->totalEfectivo;
    }

    /**
     * Set totalRecaudado
     *
     * @param float $totalRecaudado
     *
     * @return CajaCierre
     */
    public function setTotalRecaudado($totalRecaudado)
    {
        $this->totalRecaudado = $totalRecaudado;

        return $this;
    }

    /**
     * Get totalRecaudado
     *
     * @return float
     */
    public function getTotalRecaudado()
    {
        return $this->totalRecaudado;
    }

    /**
     * Set estado
     *
     * @param boolean $estado
     *
     * @return CajaCierre
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
     * Set cajaApertura
     *
     * @param \AppBundle\Entity\CajaApertura $cajaApertura
     *
     * @return CajaCierre
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
     * @return CajaCierre
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
     * Set totalDejada
     *
     * @param float $totalDejada
     *
     * @return CajaCierre
     */
    public function setTotalDejada($totalDejada)
    {
        $this->totalDejada = $totalDejada;

        return $this;
    }

    /**
     * Get totalDejada
     *
     * @return float
     */
    public function getTotalDejada()
    {
        return $this->totalDejada;
    }
}
