<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VentaFormaPago
 *
 * @ORM\Table(name="venta_forma_pago")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VentaFormaPagoRepository")
 */
class VentaFormaPago
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
     * @var float
     *
     * @ORM\Column(name="cantidad", type="float", nullable=true)
     */
    private $cantidad;

    /**
     * @ORM\ManyToOne(targetEntity="Moneda", inversedBy="ventaFormaPago", cascade={"persist"})
     * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id")
     * 
     */
    protected $moneda;

    /**
     * @ORM\ManyToOne(targetEntity="FormaPago", inversedBy="ventaFormaPago", cascade={"persist"})
     * @ORM\JoinColumn(name="forma_pago_id", referencedColumnName="id")
     * 
     */
    protected $formaPago;


    /**
     * @ORM\ManyToOne(targetEntity="Venta", inversedBy="ventaFormaPago", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="venta_id", referencedColumnName="id")
     * 
     */
    protected $venta;

    /**
     * @var int
     *
     * @ORM\Column(name="numero_dias", type="integer", nullable=true)
     */
    private $numeroDias;


    /**
     * @var float
     *
     * @ORM\Column(name="monto_a_cuenta", type="float", nullable=true)
     */
    private $montoACuenta;

    /**
     * @var string
     *
     * @ORM\Column(name="condicion", type="string", length=10, nullable=true)
     */
    private $condicion;

    /**
     * @var float
     *
     * @ORM\Column(name="igv", type="float", nullable=true)
     */
    private $igv;

    /**
     * @var float
     *
     * @ORM\Column(name="valor_tipo_cambio", type="float", nullable=true)
     */
    private $valorTipoCambio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_pago_credito", type="datetime", nullable=true)
     */
    private $fechaPagoCredito;

    /**
     * @var float
     *
     * @ORM\Column(name="monto_entregado", type="float", nullable=true)
     */
    private $montoEntregado;

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
     * Set cantidad
     *
     * @param float $cantidad
     *
     * @return VentaFormaPago
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
     * Set moneda
     *
     * @param \AppBundle\Entity\Moneda $moneda
     *
     * @return VentaFormaPago
     */
    public function setMoneda(\AppBundle\Entity\Moneda $moneda = null)
    {
        $this->moneda = $moneda;

        return $this;
    }

    /**
     * Get moneda
     *
     * @return \AppBundle\Entity\Moneda
     */
    public function getMoneda()
    {
        return $this->moneda;
    }

    /**
     * Set formaPago
     *
     * @param \AppBundle\Entity\FormaPago $formaPago
     *
     * @return VentaFormaPago
     */
    public function setFormaPago(\AppBundle\Entity\FormaPago $formaPago = null)
    {
        $this->formaPago = $formaPago;

        return $this;
    }

    /**
     * Get formaPago
     *
     * @return \AppBundle\Entity\FormaPago
     */
    public function getFormaPago()
    {
        return $this->formaPago;
    }

    /**
     * Set venta
     *
     * @param \AppBundle\Entity\Venta $venta
     *
     * @return VentaFormaPago
     */
    public function setVenta(\AppBundle\Entity\Venta $venta = null)
    {
        $this->venta = $venta;

        return $this;
    }

    /**
     * Get venta
     *
     * @return \AppBundle\Entity\Venta
     */
    public function getVenta()
    {
        return $this->venta;
    }

    /**
     * Set numeroDias
     *
     * @param integer $numeroDias
     *
     * @return VentaFormaPago
     */
    public function setNumeroDias($numeroDias)
    {
        $this->numeroDias = $numeroDias;

        return $this;
    }

    /**
     * Get numeroDias
     *
     * @return integer
     */
    public function getNumeroDias()
    {
        return $this->numeroDias;
    }

    /**
     * Set montoACuenta
     *
     * @param float $montoACuenta
     *
     * @return VentaFormaPago
     */
    public function setMontoACuenta($montoACuenta)
    {
        $this->montoACuenta = $montoACuenta;

        return $this;
    }

    /**
     * Get montoACuenta
     *
     * @return float
     */
    public function getMontoACuenta()
    {
        return $this->montoACuenta;
    }

    /**
     * Set condicion
     *
     * @param string $condicion
     *
     * @return VentaFormaPago
     */
    public function setCondicion($condicion)
    {
        $this->condicion = $condicion;

        return $this;
    }

    /**
     * Get condicion
     *
     * @return string
     */
    public function getCondicion()
    {
        return $this->condicion;
    }

    /**
     * Set igv
     *
     * @param float $igv
     *
     * @return VentaFormaPago
     */
    public function setIgv($igv)
    {
        $this->igv = $igv;

        return $this;
    }

    /**
     * Get igv
     *
     * @return float
     */
    public function getIgv()
    {
        return $this->igv;
    }

    /**
     * Set valorTipoCambio
     *
     * @param float $valorTipoCambio
     *
     * @return VentaFormaPago
     */
    public function setValorTipoCambio($valorTipoCambio)
    {
        $this->valorTipoCambio = $valorTipoCambio;

        return $this;
    }

    /**
     * Get valorTipoCambio
     *
     * @return float
     */
    public function getValorTipoCambio()
    {
        return $this->valorTipoCambio;
    }

    /**
     * Set fechaPagoCredito
     *
     * @param \DateTime $fechaPagoCredito
     *
     * @return VentaFormaPago
     */
    public function setFechaPagoCredito($fechaPagoCredito)
    {
        $this->fechaPagoCredito = $fechaPagoCredito;

        return $this;
    }

    /**
     * Get fechaPagoCredito
     *
     * @return \DateTime
     */
    public function getFechaPagoCredito()
    {
        return $this->fechaPagoCredito;
    }

    /**
     * Set montoEntregado
     *
     * @param float $montoEntregado
     *
     * @return VentaFormaPago
     */
    public function setMontoEntregado($montoEntregado)
    {
        $this->montoEntregado = $montoEntregado;

        return $this;
    }

    /**
     * Get montoEntregado
     *
     * @return float
     */
    public function getMontoEntregado()
    {
        return $this->montoEntregado;
    }
}
