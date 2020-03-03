<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CuentaBanco
 *
 * @ORM\Table(name="cuenta_banco")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CuentaBancoRepository")
 */
class CuentaBanco
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
     * @ORM\Column(name="banco", type="string", length=100, nullable=true)
     */
    private $banco;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=32, nullable=true)
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=32, nullable=true)
     */
    private $numero;

    /**
     * @var string
     *
     * @ORM\Column(name="numero_interbancario", type="string", length=32, nullable=true)
     */
    private $numeroInterbancario;

    /**
     * @var bool
     *
     * @ORM\Column(name="estado", type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity="Moneda", inversedBy="cuentaBanco", cascade={"persist"})
     * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id")
     * 
     */
    protected $moneda;

    /**
     * @ORM\ManyToOne(targetEntity="Empresa", inversedBy="cuentaBanco", cascade={"persist"})
     * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id")
     * 
     */
    protected $empresa;

    public function __toString()
    {
        return strtoupper($this->getBanco());
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
     * Set banco
     *
     * @param string $banco
     *
     * @return CuentaBanco
     */
    public function setBanco($banco)
    {
        $this->banco = $banco;

        return $this;
    }

    /**
     * Get banco
     *
     * @return string
     */
    public function getBanco()
    {
        return $this->banco;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     *
     * @return CuentaBanco
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set numero
     *
     * @param string $numero
     *
     * @return CuentaBanco
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
     * Set numeroInterbancario
     *
     * @param string $numeroInterbancario
     *
     * @return CuentaBanco
     */
    public function setNumeroInterbancario($numeroInterbancario)
    {
        $this->numeroInterbancario = $numeroInterbancario;

        return $this;
    }

    /**
     * Get numeroInterbancario
     *
     * @return string
     */
    public function getNumeroInterbancario()
    {
        return $this->numeroInterbancario;
    }

    /**
     * Set estado
     *
     * @param boolean $estado
     *
     * @return CuentaBanco
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
     * Set moneda
     *
     * @param \AppBundle\Entity\Moneda $moneda
     *
     * @return CuentaBanco
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
     * Set empresa
     *
     * @param \AppBundle\Entity\Empresa $empresa
     *
     * @return CuentaBanco
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
