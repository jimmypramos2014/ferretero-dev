<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GuiaRemision
 *
 * @ORM\Table(name="guia_remision")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GuiaRemisionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class GuiaRemision
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
     * @ORM\Column(name="fecha_emision", type="datetime", nullable=true)
     */
    private $fechaEmision;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=32, nullable=true)
     */
    private $tipo;

    /**
     * @var string
     *
     * @ORM\Column(name="serie", type="string", length=4, nullable=true)
     */
    private $serie;

    /**
    * @var int
    *
    * @ORM\Column(name="numero", type="integer", nullable=true)
    */
    private $numero;

    /**
     * @ORM\ManyToOne(targetEntity="Cliente", inversedBy="guiaRemision", cascade={"persist"})
     * @ORM\JoinColumn(name="cliente_id", referencedColumnName="id")
     * 
     */
    protected $cliente;

    /**
     * @ORM\ManyToOne(targetEntity="EmpresaLocal", inversedBy="guiaRemision", cascade={"persist"})
     * @ORM\JoinColumn(name="empresa_local_id", referencedColumnName="id")
     * 
     */
    protected $local;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_inicio_traslado", type="datetime", nullable=true)
     */
    private $fechaInicioTraslado;

    /**
     * @var string
     *
     * @ORM\Column(name="motivo_traslado", type="string", length=32, nullable=true)
     */
    private $motivoTraslado;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo_transporte", type="string", length=32, nullable=true)
     */
    private $tipoTransporte;

    /**
     * @var float
     *
     * @ORM\Column(name="peso", type="float", nullable=true)
     */
    private $peso;

    /**
     * @var float
     *
     * @ORM\Column(name="numero_bultos", type="float", nullable=true)
     */
    private $numeroBultos;

    /**
     * @var string
     *
     * @ORM\Column(name="transportista_documento", type="string", length=32, nullable=true)
     */
    private $transportistaDocumento;

    /**
     * @var string
     *
     * @ORM\Column(name="transportista_documento_numero", type="string", length=32, nullable=true)
     */
    private $transportistaDocumentoNumero;

    /**
     * @var string
     *
     * @ORM\Column(name="transportista_denominacion", type="string", length=100, nullable=true)
     */
    private $transportistaDenominacion;

    /**
     * @var string
     *
     * @ORM\Column(name="transportista_placa", type="string", length=32, nullable=true)
     */
    private $transportistaPlaca;

    /**
     * @var string
     *
     * @ORM\Column(name="conductor_documento", type="string", length=32, nullable=true)
     */
    private $conductorDocumento;

    /**
     * @var string
     *
     * @ORM\Column(name="conductor_documento_numero", type="string", length=32, nullable=true)
     */
    private $conductorDocumentoNumero;

    /**
     * @var string
     *
     * @ORM\Column(name="conductor_nombre", type="string", length=100, nullable=true)
     */
    private $conductorNombre;

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
     * @ORM\Column(name="ubigeo_partida", type="string", length=32, nullable=true)
     */
    private $ubigeoPartida;

    /**
     * @var string
     *
     * @ORM\Column(name="ubigeo_llegada", type="string", length=32, nullable=true)
     */
    private $ubigeoLlegada;

    /**
     * @var string
     *
     * @ORM\Column(name="observacion", type="string", length=255, nullable=true)
     */
    private $observacion;

    /**
     * @var bool
     *
     * @ORM\Column(name="estado", type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @ORM\ManyToMany(targetEntity="Transferencia", inversedBy="guiaRemision")
     * @ORM\JoinTable(name="transferencia_x_guia_remision")
     * 
     */
    private $transferencia;

    /**
     * @var string
     *
     * @ORM\Column(name="enlace_pdf", type="string", length=255,nullable=true)
     * 
     */
    private $enlacePdf;

    /**
     * @var string
     *
     * @ORM\Column(name="enlace_xml", type="string", length=255,nullable=true)
     * 
     */
    private $enlaceXml;

    /**
     * @var string
     *
     * @ORM\Column(name="enlace_cdr", type="string", length=255,nullable=true)
     * 
     */
    private $enlaceCdr;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo_error", type="string", length=32,nullable=true)
     * 
     */
    private $codigoError;

    /**
     * @var string
     *
     * @ORM\Column(name="mensaje_error", type="string", length=1000,nullable=true)
     * 
     */
    private $mensajeError;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario", inversedBy="guiaRemision", cascade={"persist"})
     * @ORM\JoinColumn(name="usuario_creacion", referencedColumnName="id")
     */
    private $usuarioCreacion;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario", inversedBy="guiaRemision", cascade={"persist"})
     * @ORM\JoinColumn(name="usuario_modificacion", referencedColumnName="id")
     */
    private $usuarioModificacion;

    /** 
     * created Time/Date 
     * 
     * @var \DateTime 
     * 
     * @ORM\Column(name="fecha_creacion", type="datetime", nullable=true) 
     */  
    protected $fechaCreacion;  
  
    /** 
     * updated Time/Date 
     * 
     * @var \DateTime 
     * 
     * @ORM\Column(name="fecha_modificacion", type="datetime", nullable=true) 
     */  
    protected $fechaModificacion;  

    /**
     * @ORM\PrePersist
     */
    public function setUsuarioCreacion()
    {
        $usuario = $GLOBALS['kernel']->getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->usuarioCreacion = $usuario;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUsuarioModificacion()
    {
        $usuario = $GLOBALS['kernel']->getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->usuarioModificacion = $usuario;

    }

    /** 
     * Set FechaCreacion 
     * 
     * @ORM\PrePersist 
     */  
    public function setFechaCreacion()  
    {  
        $this->fechaCreacion = new \DateTime();  
        $this->fechaModificacion = new \DateTime();  
    }  
  
    /** 
     * Set FechaModificacion 
     * 
     * @ORM\PreUpdate 
     */  
    public function setFechaModificacion()  
    {  
        $this->fechaModificacion = new \DateTime();  
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->transferencia = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set fechaEmision
     *
     * @param \DateTime $fechaEmision
     *
     * @return GuiaRemision
     */
    public function setFechaEmision($fechaEmision)
    {
        $this->fechaEmision = $fechaEmision;

        return $this;
    }

    /**
     * Get fechaEmision
     *
     * @return \DateTime
     */
    public function getFechaEmision()
    {
        return $this->fechaEmision;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     *
     * @return GuiaRemision
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set serie
     *
     * @param string $serie
     *
     * @return GuiaRemision
     */
    public function setSerie($serie)
    {
        $this->serie = $serie;

        return $this;
    }

    /**
     * Get serie
     *
     * @return string
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * Set numero
     *
     * @param integer $numero
     *
     * @return GuiaRemision
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return integer
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set fechaInicioTraslado
     *
     * @param \DateTime $fechaInicioTraslado
     *
     * @return GuiaRemision
     */
    public function setFechaInicioTraslado($fechaInicioTraslado)
    {
        $this->fechaInicioTraslado = $fechaInicioTraslado;

        return $this;
    }

    /**
     * Get fechaInicioTraslado
     *
     * @return \DateTime
     */
    public function getFechaInicioTraslado()
    {
        return $this->fechaInicioTraslado;
    }

    /**
     * Set motivoTraslado
     *
     * @param string $motivoTraslado
     *
     * @return GuiaRemision
     */
    public function setMotivoTraslado($motivoTraslado)
    {
        $this->motivoTraslado = $motivoTraslado;

        return $this;
    }

    /**
     * Get motivoTraslado
     *
     * @return string
     */
    public function getMotivoTraslado()
    {
        return $this->motivoTraslado;
    }

    /**
     * Set tipoTransporte
     *
     * @param string $tipoTransporte
     *
     * @return GuiaRemision
     */
    public function setTipoTransporte($tipoTransporte)
    {
        $this->tipoTransporte = $tipoTransporte;

        return $this;
    }

    /**
     * Get tipoTransporte
     *
     * @return string
     */
    public function getTipoTransporte()
    {
        return $this->tipoTransporte;
    }

    /**
     * Set peso
     *
     * @param float $peso
     *
     * @return GuiaRemision
     */
    public function setPeso($peso)
    {
        $this->peso = $peso;

        return $this;
    }

    /**
     * Get peso
     *
     * @return float
     */
    public function getPeso()
    {
        return $this->peso;
    }

    /**
     * Set numeroBultos
     *
     * @param float $numeroBultos
     *
     * @return GuiaRemision
     */
    public function setNumeroBultos($numeroBultos)
    {
        $this->numeroBultos = $numeroBultos;

        return $this;
    }

    /**
     * Get numeroBultos
     *
     * @return float
     */
    public function getNumeroBultos()
    {
        return $this->numeroBultos;
    }

    /**
     * Set transportistaDocumento
     *
     * @param string $transportistaDocumento
     *
     * @return GuiaRemision
     */
    public function setTransportistaDocumento($transportistaDocumento)
    {
        $this->transportistaDocumento = $transportistaDocumento;

        return $this;
    }

    /**
     * Get transportistaDocumento
     *
     * @return string
     */
    public function getTransportistaDocumento()
    {
        return $this->transportistaDocumento;
    }

    /**
     * Set transportistaDocumentoNumero
     *
     * @param string $transportistaDocumentoNumero
     *
     * @return GuiaRemision
     */
    public function setTransportistaDocumentoNumero($transportistaDocumentoNumero)
    {
        $this->transportistaDocumentoNumero = $transportistaDocumentoNumero;

        return $this;
    }

    /**
     * Get transportistaDocumentoNumero
     *
     * @return string
     */
    public function getTransportistaDocumentoNumero()
    {
        return $this->transportistaDocumentoNumero;
    }

    /**
     * Set transportistaDenominacion
     *
     * @param string $transportistaDenominacion
     *
     * @return GuiaRemision
     */
    public function setTransportistaDenominacion($transportistaDenominacion)
    {
        $this->transportistaDenominacion = $transportistaDenominacion;

        return $this;
    }

    /**
     * Get transportistaDenominacion
     *
     * @return string
     */
    public function getTransportistaDenominacion()
    {
        return $this->transportistaDenominacion;
    }

    /**
     * Set transportistaPlaca
     *
     * @param string $transportistaPlaca
     *
     * @return GuiaRemision
     */
    public function setTransportistaPlaca($transportistaPlaca)
    {
        $this->transportistaPlaca = $transportistaPlaca;

        return $this;
    }

    /**
     * Get transportistaPlaca
     *
     * @return string
     */
    public function getTransportistaPlaca()
    {
        return $this->transportistaPlaca;
    }

    /**
     * Set conductorDocumento
     *
     * @param string $conductorDocumento
     *
     * @return GuiaRemision
     */
    public function setConductorDocumento($conductorDocumento)
    {
        $this->conductorDocumento = $conductorDocumento;

        return $this;
    }

    /**
     * Get conductorDocumento
     *
     * @return string
     */
    public function getConductorDocumento()
    {
        return $this->conductorDocumento;
    }

    /**
     * Set conductorDocumentoNumero
     *
     * @param string $conductorDocumentoNumero
     *
     * @return GuiaRemision
     */
    public function setConductorDocumentoNumero($conductorDocumentoNumero)
    {
        $this->conductorDocumentoNumero = $conductorDocumentoNumero;

        return $this;
    }

    /**
     * Get conductorDocumentoNumero
     *
     * @return string
     */
    public function getConductorDocumentoNumero()
    {
        return $this->conductorDocumentoNumero;
    }

    /**
     * Set conductorNombre
     *
     * @param string $conductorNombre
     *
     * @return GuiaRemision
     */
    public function setConductorNombre($conductorNombre)
    {
        $this->conductorNombre = $conductorNombre;

        return $this;
    }

    /**
     * Get conductorNombre
     *
     * @return string
     */
    public function getConductorNombre()
    {
        return $this->conductorNombre;
    }

    /**
     * Set puntoPartida
     *
     * @param string $puntoPartida
     *
     * @return GuiaRemision
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
     * @return GuiaRemision
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
     * Set ubigeoPartida
     *
     * @param string $ubigeoPartida
     *
     * @return GuiaRemision
     */
    public function setUbigeoPartida($ubigeoPartida)
    {
        $this->ubigeoPartida = $ubigeoPartida;

        return $this;
    }

    /**
     * Get ubigeoPartida
     *
     * @return string
     */
    public function getUbigeoPartida()
    {
        return $this->ubigeoPartida;
    }

    /**
     * Set ubigeoLlegada
     *
     * @param string $ubigeoLlegada
     *
     * @return GuiaRemision
     */
    public function setUbigeoLlegada($ubigeoLlegada)
    {
        $this->ubigeoLlegada = $ubigeoLlegada;

        return $this;
    }

    /**
     * Get ubigeoLlegada
     *
     * @return string
     */
    public function getUbigeoLlegada()
    {
        return $this->ubigeoLlegada;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     *
     * @return GuiaRemision
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;

        return $this;
    }

    /**
     * Get observacion
     *
     * @return string
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set estado
     *
     * @param boolean $estado
     *
     * @return GuiaRemision
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
     * Get fechaCreacion
     *
     * @return \DateTime
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set cliente
     *
     * @param \AppBundle\Entity\Cliente $cliente
     *
     * @return GuiaRemision
     */
    public function setCliente(\AppBundle\Entity\Cliente $cliente = null)
    {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * Get cliente
     *
     * @return \AppBundle\Entity\Cliente
     */
    public function getCliente()
    {
        return $this->cliente;
    }

    /**
     * Set local
     *
     * @param \AppBundle\Entity\EmpresaLocal $local
     *
     * @return GuiaRemision
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
     * Add transferencia
     *
     * @param \AppBundle\Entity\Transferencia $transferencia
     *
     * @return GuiaRemision
     */
    public function addTransferencia(\AppBundle\Entity\Transferencia $transferencia)
    {
        $this->transferencia[] = $transferencia;

        return $this;
    }

    /**
     * Remove transferencia
     *
     * @param \AppBundle\Entity\Transferencia $transferencia
     */
    public function removeTransferencia(\AppBundle\Entity\Transferencia $transferencia)
    {
        $this->transferencia->removeElement($transferencia);
    }

    /**
     * Get transferencia
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTransferencia()
    {
        return $this->transferencia;
    }

    /**
     * Get usuarioCreacion
     *
     * @return \AppBundle\Entity\Usuario
     */
    public function getUsuarioCreacion()
    {
        return $this->usuarioCreacion;
    }

    /**
     * Get usuarioModificacion
     *
     * @return \AppBundle\Entity\Usuario
     */
    public function getUsuarioModificacion()
    {
        return $this->usuarioModificacion;
    }



    /**
     * Set enlacePdf
     *
     * @param string $enlacePdf
     *
     * @return GuiaRemision
     */
    public function setEnlacePdf($enlacePdf)
    {
        $this->enlacePdf = $enlacePdf;

        return $this;
    }

    /**
     * Get enlacePdf
     *
     * @return string
     */
    public function getEnlacePdf()
    {
        return $this->enlacePdf;
    }

    /**
     * Set enlaceXml
     *
     * @param string $enlaceXml
     *
     * @return GuiaRemision
     */
    public function setEnlaceXml($enlaceXml)
    {
        $this->enlaceXml = $enlaceXml;

        return $this;
    }

    /**
     * Get enlaceXml
     *
     * @return string
     */
    public function getEnlaceXml()
    {
        return $this->enlaceXml;
    }

    /**
     * Set enlaceCdr
     *
     * @param string $enlaceCdr
     *
     * @return GuiaRemision
     */
    public function setEnlaceCdr($enlaceCdr)
    {
        $this->enlaceCdr = $enlaceCdr;

        return $this;
    }

    /**
     * Get enlaceCdr
     *
     * @return string
     */
    public function getEnlaceCdr()
    {
        return $this->enlaceCdr;
    }

    /**
     * Set codigoError
     *
     * @param string $codigoError
     *
     * @return GuiaRemision
     */
    public function setCodigoError($codigoError)
    {
        $this->codigoError = $codigoError;

        return $this;
    }

    /**
     * Get codigoError
     *
     * @return string
     */
    public function getCodigoError()
    {
        return $this->codigoError;
    }

    /**
     * Set mensajeError
     *
     * @param string $mensajeError
     *
     * @return GuiaRemision
     */
    public function setMensajeError($mensajeError)
    {
        $this->mensajeError = $mensajeError;

        return $this;
    }

    /**
     * Get mensajeError
     *
     * @return string
     */
    public function getMensajeError()
    {
        return $this->mensajeError;
    }

}
