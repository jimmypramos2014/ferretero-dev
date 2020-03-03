<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FacturaCompra
 *
 * @ORM\Table(name="factura_compra")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FacturaCompraRepository")
 */
class FacturaCompra
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
     * @var bool
     *
     * @ORM\Column(name="estado", type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity="Compra", inversedBy="facturaCompra", cascade={"persist"})
     * @ORM\JoinColumn(name="compra_id", referencedColumnName="id")
     * 
     */
    protected $compra;

    /**
     * @ORM\ManyToOne(targetEntity="Proveedor", inversedBy="facturaCompra", cascade={"persist"})
     * @ORM\JoinColumn(name="proveedor_id", referencedColumnName="id")
     * 
     */
    protected $proveedor;

    /**
     * @var string
     *
     * @ORM\Column(name="ticket", type="string", length=32,nullable=true)
     * 
     */
    private $ticket;

    /**
     * @var string
     *
     * @ORM\Column(name="documento", type="string", length=32,nullable=true)
     * 
     */
    private $documento;

    /**
     * @var string
     *
     * @ORM\Column(name="numero_documento", type="string", length=32,nullable=true)
     * 
     */
    private $numero_documento;

    /**
     * @var string
     *
     * 
     * @Assert\File(maxSize="1M",mimeTypesMessage = "Este formato no es válido, solo se permiten jpeg,png o gif",mimeTypes={ "image/jpeg","image/png","image/gif","application/pdf" })
     *     
     * @ORM\Column(name="archivo", type="string", length=100, nullable=true)
     */
    private $archivo;

    /**
     * @ORM\ManyToOne(targetEntity="EmpresaLocal", inversedBy="facturaCompra", cascade={"persist"})
     * @ORM\JoinColumn(name="empresa_local_id", referencedColumnName="id")
     * 
     */
    protected $local;

    /**
     * @var string
     *
     * @ORM\Column(name="observacion", type="string", length=255,nullable=true)
     * 
     */
    private $observacion;

    /**
     * @var string
     *
     * 
     * @Assert\File(maxSize="1M",mimeTypesMessage = "Este formato no es válido, solo se permiten jpeg,png o gif",mimeTypes={ "image/jpeg","image/png","image/gif","application/pdf" })
     *     
     * @ORM\Column(name="voucher", type="string", length=100, nullable=true)
     */
    private $voucher;

    /**
     * @var string
     *
     * @ORM\Column(name="documento_relacionado_notacredito", type="string", length=100,nullable=true)
     * 
     */
    private $documento_relacionado_notacredito;

    /**
     * @var float
     *
     * @ORM\Column(name="monto_notacredito", type="float",nullable=true)
     * 
     */
    private $monto_notacredito;

    /**
     * @var string
     *
     * @ORM\Column(name="numero_notacredito", type="string", length=100,nullable=true)
     * 
     */
    private $numero_notacredito;

    public function __toString()
    {
        return $this->getNumeroDocumento();
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
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return FacturaCompra
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
     * Set estado
     *
     * @param boolean $estado
     *
     * @return FacturaCompra
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
     * Set ticket
     *
     * @param string $ticket
     *
     * @return FacturaCompra
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * Get ticket
     *
     * @return string
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Set compra
     *
     * @param \AppBundle\Entity\Compra $compra
     *
     * @return FacturaCompra
     */
    public function setCompra(\AppBundle\Entity\Compra $compra = null)
    {
        $this->compra = $compra;

        return $this;
    }

    /**
     * Get compra
     *
     * @return \AppBundle\Entity\Compra
     */
    public function getCompra()
    {
        return $this->compra;
    }

    /**
     * Set proveedor
     *
     * @param \AppBundle\Entity\Proveedor $proveedor
     *
     * @return FacturaCompra
     */
    public function setProveedor(\AppBundle\Entity\Proveedor $proveedor = null)
    {
        $this->proveedor = $proveedor;

        return $this;
    }

    /**
     * Get proveedor
     *
     * @return \AppBundle\Entity\Proveedor
     */
    public function getProveedor()
    {
        return $this->proveedor;
    }

    /**
     * Set documento
     *
     * @param string $documento
     *
     * @return FacturaCompra
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
     * Set numeroDocumento
     *
     * @param string $numeroDocumento
     *
     * @return FacturaCompra
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numero_documento = $numeroDocumento;

        return $this;
    }

    /**
     * Get numeroDocumento
     *
     * @return string
     */
    public function getNumeroDocumento()
    {
        return $this->numero_documento;
    }

    /**
     * Set archivo
     *
     * @param string $archivo
     *
     * @return FacturaCompra
     */
    public function setArchivo($archivo)
    {
        $this->archivo = $archivo;

        return $this;
    }

    /**
     * Get archivo
     *
     * @return string
     */
    public function getArchivo()
    {
        return $this->archivo;
    }

    /**
     * Set local
     *
     * @param \AppBundle\Entity\EmpresaLocal $local
     *
     * @return FacturaCompra
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
     * Set observacion
     *
     * @param string $observacion
     *
     * @return FacturaCompra
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
     * Set voucher
     *
     * @param string $voucher
     *
     * @return FacturaCompra
     */
    public function setVoucher($voucher)
    {
        $this->voucher = $voucher;

        return $this;
    }

    /**
     * Get voucher
     *
     * @return string
     */
    public function getVoucher()
    {
        return $this->voucher;
    }

    /**
     * Set documentoRelacionadoNotacredito
     *
     * @param string $documentoRelacionadoNotacredito
     *
     * @return FacturaCompra
     */
    public function setDocumentoRelacionadoNotacredito($documentoRelacionadoNotacredito)
    {
        $this->documento_relacionado_notacredito = $documentoRelacionadoNotacredito;

        return $this;
    }

    /**
     * Get documentoRelacionadoNotacredito
     *
     * @return string
     */
    public function getDocumentoRelacionadoNotacredito()
    {
        return $this->documento_relacionado_notacredito;
    }

    /**
     * Set montoNotacredito
     *
     * @param float $montoNotacredito
     *
     * @return FacturaCompra
     */
    public function setMontoNotacredito($montoNotacredito)
    {
        $this->monto_notacredito = $montoNotacredito;

        return $this;
    }

    /**
     * Get montoNotacredito
     *
     * @return float
     */
    public function getMontoNotacredito()
    {
        return $this->monto_notacredito;
    }

    /**
     * Set numeroNotacredito
     *
     * @param string $numeroNotacredito
     *
     * @return FacturaCompra
     */
    public function setNumeroNotacredito($numeroNotacredito)
    {
        $this->numero_notacredito = $numeroNotacredito;

        return $this;
    }

    /**
     * Get numeroNotacredito
     *
     * @return string
     */
    public function getNumeroNotacredito()
    {
        return $this->numero_notacredito;
    }
}
