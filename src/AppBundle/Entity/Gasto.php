<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Gasto
 *
 * @ORM\Table(name="gasto")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GastoRepository")
 */
class Gasto
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
     * @ORM\Column(name="monto", type="float", nullable=true)
     */
    private $monto;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="date", nullable=true)
     */
    private $fecha;

    /**
     * @var bool
     *
     * @ORM\Column(name="estado", type="boolean", nullable=true)
     */
    private $estado;
    
    /**
     * @var string
     *
     * @ORM\Column(name="numero_documento", type="string", length=32, nullable=true)
     */
    private $numeroDocumento;

    /**
     * @ORM\ManyToOne(targetEntity="Empresa", inversedBy="gasto", cascade={"persist"})
     * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id")
     * 
     */
    protected $empresa;

    /**
     * @ORM\ManyToOne(targetEntity="Proveedor", inversedBy="gasto", cascade={"persist"})
     * @ORM\JoinColumn(name="proveedor_id", referencedColumnName="id")
     * 
     */
    protected $proveedor;

    /**
     * @ORM\ManyToOne(targetEntity="ProveedorServicio", inversedBy="gasto", cascade={"persist"})
     * @ORM\JoinColumn(name="proveedor_servicio_id", referencedColumnName="id")
     * 
     */
    protected $servicio;

    /**
     * @ORM\ManyToOne(targetEntity="EmpresaLocal", inversedBy="gasto", cascade={"persist"})
     * @ORM\JoinColumn(name="empresa_local_id", referencedColumnName="id")
     * 
     */
    protected $local;

    /**
     * @var bool
     *
     * @ORM\Column(name="condicion", type="boolean", nullable=true)
     */
    private $condicion;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=32, nullable=true)
     */
    private $tipo;

    /**
     * @var string
     *
     * @ORM\Column(name="concepto", type="string", length=100, nullable=true)
     */
    private $concepto;

    /**
     * @var string
     *
     * @ORM\Column(name="beneficiario", type="string", length=100, nullable=true)
     */
    private $beneficiario;

    /**
     * @var string
     *
     *   
     * @ORM\Column(name="archivo", type="string", length=100, nullable=true)
     */
    private $archivo;

    /**
     * @var string
     *
     * @ORM\Column(name="observacion", type="string", length=255, nullable=true)
     */
    private $observacion;

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
     * Set monto
     *
     * @param float $monto
     *
     * @return Gasto
     */
    public function setMonto($monto)
    {
        $this->monto = $monto;

        return $this;
    }

    /**
     * Get monto
     *
     * @return float
     */
    public function getMonto()
    {
        return $this->monto;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return Gasto
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
     * Set empresa
     *
     * @param \AppBundle\Entity\Empresa $empresa
     *
     * @return Gasto
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

    /**
     * Set proveedor
     *
     * @param \AppBundle\Entity\Proveedor $proveedor
     *
     * @return Gasto
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
     * Set servicio
     *
     * @param \AppBundle\Entity\ProveedorServicio $servicio
     *
     * @return Gasto
     */
    public function setServicio(\AppBundle\Entity\ProveedorServicio $servicio = null)
    {
        $this->servicio = $servicio;

        return $this;
    }

    /**
     * Get servicio
     *
     * @return \AppBundle\Entity\ProveedorServicio
     */
    public function getServicio()
    {
        return $this->servicio;
    }

    /**
     * Set estado
     *
     * @param boolean $estado
     *
     * @return Gasto
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
     * Set local
     *
     * @param \AppBundle\Entity\EmpresaLocal $local
     *
     * @return Gasto
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
     * Set numeroDocumento
     *
     * @param string $numeroDocumento
     *
     * @return Gasto
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = $numeroDocumento;

        return $this;
    }

    /**
     * Get numeroDocumento
     *
     * @return string
     */
    public function getNumeroDocumento()
    {
        return $this->numeroDocumento;
    }

    /**
     * Set condicion
     *
     * @param boolean $condicion
     *
     * @return Gasto
     */
    public function setCondicion($condicion)
    {
        $this->condicion = $condicion;

        return $this;
    }

    /**
     * Get condicion
     *
     * @return boolean
     */
    public function getCondicion()
    {
        return $this->condicion;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     *
     * @return Gasto
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
     * Set concepto
     *
     * @param string $concepto
     *
     * @return Gasto
     */
    public function setConcepto($concepto)
    {
        $this->concepto = $concepto;

        return $this;
    }

    /**
     * Get concepto
     *
     * @return string
     */
    public function getConcepto()
    {
        return $this->concepto;
    }

    /**
     * Set beneficiario
     *
     * @param string $beneficiario
     *
     * @return Gasto
     */
    public function setBeneficiario($beneficiario)
    {
        $this->beneficiario = $beneficiario;

        return $this;
    }

    /**
     * Get beneficiario
     *
     * @return string
     */
    public function getBeneficiario()
    {
        return $this->beneficiario;
    }

    /**
     * Set archivo
     *
     * @param string $archivo
     *
     * @return Gasto
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
     * Set observacion
     *
     * @param string $observacion
     *
     * @return Gasto
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
}
