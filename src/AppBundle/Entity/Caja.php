<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Caja
 *
 * @ORM\Table(name="caja")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CajaRepository")
 * 
 */
class Caja
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
     * @ORM\Column(name="codigo", type="string", length=32, nullable=true)
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=100, nullable=true)
     */
    private $nombre;

    /**
     * @var bool
     *
     * @ORM\Column(name="estado", type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity="EmpresaLocal", inversedBy="caja", cascade={"persist"})
     * @ORM\JoinColumn(name="empresa_local_id", referencedColumnName="id")
     * 
     */
    protected $local;

    /**
     * @var string
     *
     * @ORM\Column(name="condicion", type="string", length=32, nullable=true)
     */
    private $condicion;

    /**
     * @var float
     *
     * @ORM\Column(name="monto_anterior", type="float", nullable=true)
     */
    private $montoAnterior;


    public function __toString()
    {
        return '['.$this->getLocal().'] '.$this->getNombre();
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
     * Set codigo
     *
     * @param string $codigo
     *
     * @return Caja
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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Caja
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set estado
     *
     * @param boolean $estado
     *
     * @return Caja
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
     * Set local
     *
     * @param \AppBundle\Entity\EmpresaLocal $local
     *
     * @return Caja
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
     * Set condicion
     *
     * @param string $condicion
     *
     * @return Caja
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
     * Set montoAnterior
     *
     * @param float $montoAnterior
     *
     * @return Caja
     */
    public function setMontoAnterior($montoAnterior)
    {
        $this->montoAnterior = $montoAnterior;

        return $this;
    }

    /**
     * Get montoAnterior
     *
     * @return float
     */
    public function getMontoAnterior()
    {
        return $this->montoAnterior;
    }
}
