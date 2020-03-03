<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FacturaVentaNubefactError
 *
 * @ORM\Table(name="factura_venta_nubefact_error")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FacturaVentaNubefactErrorRepository")
 */
class FacturaVentaNubefactError
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
     * @var int
     *
     * @ORM\Column(name="tipo_de_comprobante", type="integer", nullable=true)
     */
    private $tipoDeComprobante;

    /**
     * @var string
     *
     * @ORM\Column(name="serie", type="string", length=4, nullable=true)
     */
    private $serie;

    /**
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=32, nullable=true)
     */
    private $numero;

    /**
     * @var bool
     *
     * @ORM\Column(name="aceptada_por_sunat", type="boolean", nullable=true)
     */
    private $aceptadaPorSunat;

    /**
     * @var string
     *
     * @ORM\Column(name="sunat_description", type="string", length=100, nullable=true)
     */
    private $sunatDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="sunat_responsecode", type="string", length=4, nullable=true)
     */
    private $sunatResponsecode;

    /**
     * @var string
     *
     * @ORM\Column(name="enlace_del_pdf", type="string", length=255, nullable=true)
     */
    private $enlaceDelPdf;

    /**
     * @var string
     *
     * @ORM\Column(name="enlace_del_xml", type="string", length=255, nullable=true)
     */
    private $enlaceDelXml;

    /**
     * @var string
     *
     * @ORM\Column(name="enlace_del_cdr", type="string", length=255, nullable=true)
     */
    private $enlaceDelCdr;

    /**
     * @var string
     *
     * @ORM\Column(name="error", type="string", length=255, nullable=true)
     */
    private $error;

    /**
     * @ORM\ManyToOne(targetEntity="FacturaVenta", inversedBy="facturaVentaNubefactError", cascade={"persist"})
     * @ORM\JoinColumn(name="factura_venta_id", referencedColumnName="id")
     * 
     */
    protected $facturaVenta;

    /**
     * @var string
     *
     * @ORM\Column(name="sunat_ticket_numero", type="string", length=255, nullable=true)
     */
    private $sunatTicketNumero;

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
     * Set tipoDeComprobante
     *
     * @param integer $tipoDeComprobante
     *
     * @return FacturaVentaNubefactError
     */
    public function setTipoDeComprobante($tipoDeComprobante)
    {
        $this->tipoDeComprobante = $tipoDeComprobante;

        return $this;
    }

    /**
     * Get tipoDeComprobante
     *
     * @return integer
     */
    public function getTipoDeComprobante()
    {
        return $this->tipoDeComprobante;
    }

    /**
     * Set serie
     *
     * @param string $serie
     *
     * @return FacturaVentaNubefactError
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
     * @param string $numero
     *
     * @return FacturaVentaNubefactError
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
     * Set aceptadaPorSunat
     *
     * @param boolean $aceptadaPorSunat
     *
     * @return FacturaVentaNubefactError
     */
    public function setAceptadaPorSunat($aceptadaPorSunat)
    {
        $this->aceptadaPorSunat = $aceptadaPorSunat;

        return $this;
    }

    /**
     * Get aceptadaPorSunat
     *
     * @return boolean
     */
    public function getAceptadaPorSunat()
    {
        return $this->aceptadaPorSunat;
    }

    /**
     * Set sunatDescription
     *
     * @param string $sunatDescription
     *
     * @return FacturaVentaNubefactError
     */
    public function setSunatDescription($sunatDescription)
    {
        $this->sunatDescription = $sunatDescription;

        return $this;
    }

    /**
     * Get sunatDescription
     *
     * @return string
     */
    public function getSunatDescription()
    {
        return $this->sunatDescription;
    }

    /**
     * Set sunatResponsecode
     *
     * @param string $sunatResponsecode
     *
     * @return FacturaVentaNubefactError
     */
    public function setSunatResponsecode($sunatResponsecode)
    {
        $this->sunatResponsecode = $sunatResponsecode;

        return $this;
    }

    /**
     * Get sunatResponsecode
     *
     * @return string
     */
    public function getSunatResponsecode()
    {
        return $this->sunatResponsecode;
    }

    /**
     * Set enlaceDelPdf
     *
     * @param string $enlaceDelPdf
     *
     * @return FacturaVentaNubefactError
     */
    public function setEnlaceDelPdf($enlaceDelPdf)
    {
        $this->enlaceDelPdf = $enlaceDelPdf;

        return $this;
    }

    /**
     * Get enlaceDelPdf
     *
     * @return string
     */
    public function getEnlaceDelPdf()
    {
        return $this->enlaceDelPdf;
    }

    /**
     * Set enlaceDelXml
     *
     * @param string $enlaceDelXml
     *
     * @return FacturaVentaNubefactError
     */
    public function setEnlaceDelXml($enlaceDelXml)
    {
        $this->enlaceDelXml = $enlaceDelXml;

        return $this;
    }

    /**
     * Get enlaceDelXml
     *
     * @return string
     */
    public function getEnlaceDelXml()
    {
        return $this->enlaceDelXml;
    }

    /**
     * Set enlaceDelCdr
     *
     * @param string $enlaceDelCdr
     *
     * @return FacturaVentaNubefactError
     */
    public function setEnlaceDelCdr($enlaceDelCdr)
    {
        $this->enlaceDelCdr = $enlaceDelCdr;

        return $this;
    }

    /**
     * Get enlaceDelCdr
     *
     * @return string
     */
    public function getEnlaceDelCdr()
    {
        return $this->enlaceDelCdr;
    }

    /**
     * Set facturaVenta
     *
     * @param \AppBundle\Entity\FacturaVenta $facturaVenta
     *
     * @return FacturaVentaNubefactError
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

    /**
     * Set error
     *
     * @param string $error
     *
     * @return FacturaVentaNubefactError
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set sunatTicketNumero
     *
     * @param string $sunatTicketNumero
     *
     * @return FacturaVentaNubefactError
     */
    public function setSunatTicketNumero($sunatTicketNumero)
    {
        $this->sunatTicketNumero = $sunatTicketNumero;

        return $this;
    }

    /**
     * Get sunatTicketNumero
     *
     * @return string
     */
    public function getSunatTicketNumero()
    {
        return $this->sunatTicketNumero;
    }
}
