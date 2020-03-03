<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransferenciaXTransporte
 *
 * @ORM\Table(name="transferencia_x_transporte")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TransferenciaXTransporteRepository")
 */
class TransferenciaXTransporte
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
     * @ORM\Column(name="punto_partida", type="string", length=100, nullable=true)
     */
    private $puntoPartida;

    /**
     * @var string
     *
     * @ORM\Column(name="punto_llegada", type="string", length=100, nullable=true)
     */
    private $puntoLlegada;

    /**
     * @var string
     *
     * @ORM\Column(name="remitente", type="string", length=100, nullable=true)
     */
    private $remitente;

    /**
     * @var string
     *
     * @ORM\Column(name="destinatario", type="string", length=100, nullable=true)
     */
    private $destinatario;

    /**
     * @var string
     *
     * @ORM\Column(name="ruc_remitente", type="string", length=11, nullable=true)
     */
    private $rucRemitente;

    /**
     * @var string
     *
     * @ORM\Column(name="ruc_destinatario", type="string", length=11, nullable=true)
     */
    private $rucDestinatario;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_traslado", type="date", nullable=true)
     */
    private $fechaTraslado;

    /**
     * @var float
     *
     * @ORM\Column(name="costo_minimo", type="float", nullable=true)
     */
    private $costoMinimo;

    /**
     * @var string
     *
     * @ORM\Column(name="marca", type="string", length=100, nullable=true)
     */
    private $marca;

    /**
     * @var string
     *
     * @ORM\Column(name="placa", type="string", length=32, nullable=true)
     */
    private $placa;

    /**
     * @var string
     *
     * @ORM\Column(name="constancia_inscripcion", type="string", length=32, nullable=true)
     */
    private $constanciaInscripcion;

    /**
     * @var string
     *
     * @ORM\Column(name="licencia_conducir", type="string", length=32, nullable=true)
     */
    private $licenciaConducir;

    /**
     * @ORM\ManyToOne(targetEntity="Transporte", inversedBy="transferenciaXTransporte", cascade={"persist"})
     * @ORM\JoinColumn(name="transporte_id", referencedColumnName="id")
     * 
     */
    protected $transporte;

    /**
     * @ORM\OneToOne(targetEntity="Transferencia", inversedBy="transferenciaXTransporte", cascade={"persist"})
     * @ORM\JoinColumn(name="transferencia_id", referencedColumnName="id")
     * 
     */
    protected $transferencia;
 

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
     * Set puntoPartida
     *
     * @param string $puntoPartida
     *
     * @return TransferenciaXTransporte
     */
    public function setPuntoPartida($puntoPartida)
    {
        $this->puntoPartida = $puntoPartida;

        return $this;
    }

    /**
     * Get puntoPartida
     *
     * @return string
     */
    public function getPuntoPartida()
    {
        return $this->puntoPartida;
    }

    /**
     * Set puntoLlegada
     *
     * @param string $puntoLlegada
     *
     * @return TransferenciaXTransporte
     */
    public function setPuntoLlegada($puntoLlegada)
    {
        $this->puntoLlegada = $puntoLlegada;

        return $this;
    }

    /**
     * Get puntoLlegada
     *
     * @return string
     */
    public function getPuntoLlegada()
    {
        return $this->puntoLlegada;
    }

    /**
     * Set remitente
     *
     * @param string $remitente
     *
     * @return TransferenciaXTransporte
     */
    public function setRemitente($remitente)
    {
        $this->remitente = $remitente;

        return $this;
    }

    /**
     * Get remitente
     *
     * @return string
     */
    public function getRemitente()
    {
        return $this->remitente;
    }

    /**
     * Set destinatario
     *
     * @param string $destinatario
     *
     * @return TransferenciaXTransporte
     */
    public function setDestinatario($destinatario)
    {
        $this->destinatario = $destinatario;

        return $this;
    }

    /**
     * Get destinatario
     *
     * @return string
     */
    public function getDestinatario()
    {
        return $this->destinatario;
    }

    /**
     * Set rucRemitente
     *
     * @param string $rucRemitente
     *
     * @return TransferenciaXTransporte
     */
    public function setRucRemitente($rucRemitente)
    {
        $this->rucRemitente = $rucRemitente;

        return $this;
    }

    /**
     * Get rucRemitente
     *
     * @return string
     */
    public function getRucRemitente()
    {
        return $this->rucRemitente;
    }

    /**
     * Set rucDestinatario
     *
     * @param string $rucDestinatario
     *
     * @return TransferenciaXTransporte
     */
    public function setRucDestinatario($rucDestinatario)
    {
        $this->rucDestinatario = $rucDestinatario;

        return $this;
    }

    /**
     * Get rucDestinatario
     *
     * @return string
     */
    public function getRucDestinatario()
    {
        return $this->rucDestinatario;
    }

    /**
     * Set fechaTraslado
     *
     * @param \DateTime $fechaTraslado
     *
     * @return TransferenciaXTransporte
     */
    public function setFechaTraslado($fechaTraslado)
    {
        $this->fechaTraslado = $fechaTraslado;

        return $this;
    }

    /**
     * Get fechaTraslado
     *
     * @return \DateTime
     */
    public function getFechaTraslado()
    {
        return $this->fechaTraslado;
    }

    /**
     * Set costoMinimo
     *
     * @param float $costoMinimo
     *
     * @return TransferenciaXTransporte
     */
    public function setCostoMinimo($costoMinimo)
    {
        $this->costoMinimo = $costoMinimo;

        return $this;
    }

    /**
     * Get costoMinimo
     *
     * @return float
     */
    public function getCostoMinimo()
    {
        return $this->costoMinimo;
    }

    /**
     * Set marca
     *
     * @param string $marca
     *
     * @return TransferenciaXTransporte
     */
    public function setMarca($marca)
    {
        $this->marca = $marca;

        return $this;
    }

    /**
     * Get marca
     *
     * @return string
     */
    public function getMarca()
    {
        return $this->marca;
    }

    /**
     * Set placa
     *
     * @param string $placa
     *
     * @return TransferenciaXTransporte
     */
    public function setPlaca($placa)
    {
        $this->placa = $placa;

        return $this;
    }

    /**
     * Get placa
     *
     * @return string
     */
    public function getPlaca()
    {
        return $this->placa;
    }

    /**
     * Set constanciaInscripcion
     *
     * @param string $constanciaInscripcion
     *
     * @return TransferenciaXTransporte
     */
    public function setConstanciaInscripcion($constanciaInscripcion)
    {
        $this->constanciaInscripcion = $constanciaInscripcion;

        return $this;
    }

    /**
     * Get constanciaInscripcion
     *
     * @return string
     */
    public function getConstanciaInscripcion()
    {
        return $this->constanciaInscripcion;
    }

    /**
     * Set licenciaConducir
     *
     * @param string $licenciaConducir
     *
     * @return TransferenciaXTransporte
     */
    public function setLicenciaConducir($licenciaConducir)
    {
        $this->licenciaConducir = $licenciaConducir;

        return $this;
    }

    /**
     * Get licenciaConducir
     *
     * @return string
     */
    public function getLicenciaConducir()
    {
        return $this->licenciaConducir;
    }

    /**
     * Set transporte
     *
     * @param \AppBundle\Entity\Transporte $transporte
     *
     * @return TransferenciaXTransporte
     */
    public function setTransporte(\AppBundle\Entity\Transporte $transporte = null)
    {
        $this->transporte = $transporte;

        return $this;
    }

    /**
     * Get transporte
     *
     * @return \AppBundle\Entity\Transporte
     */
    public function getTransporte()
    {
        return $this->transporte;
    }

    /**
     * Set transferencia
     *
     * @param \AppBundle\Entity\Transferencia $transferencia
     *
     * @return TransferenciaXTransporte
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
}
