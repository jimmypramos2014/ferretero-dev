<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ComponenteXDocumento
 *
 * @ORM\Table(name="componente_x_documento")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ComponenteXDocumentoRepository")
 */
class ComponenteXDocumento
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
     * @ORM\Column(name="posicion_x", type="string", length=32, nullable=true)
     */
    private $posicionX;

    /**
     * @var string
     *
     * @ORM\Column(name="posicion_y", type="string", length=32, nullable=true)
     */
    private $posicionY;

    /**
     * @var bool
     *
     * @ORM\Column(name="estado", type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity="Documento", inversedBy="componenteXDocumento", cascade={"persist"})
     * @ORM\JoinColumn(name="documento_id", referencedColumnName="id")
     * 
     */
    protected $documento;


    /**
     * @ORM\ManyToOne(targetEntity="Componente", inversedBy="componenteXDocumento", cascade={"persist"})
     * @ORM\JoinColumn(name="componente_id", referencedColumnName="id")
     * 
     */
    protected $componente;



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
     * Set posicionX
     *
     * @param string $posicionX
     *
     * @return ComponenteXDocumento
     */
    public function setPosicionX($posicionX)
    {
        $this->posicionX = $posicionX;

        return $this;
    }

    /**
     * Get posicionX
     *
     * @return string
     */
    public function getPosicionX()
    {
        return $this->posicionX;
    }

    /**
     * Set posicionY
     *
     * @param string $posicionY
     *
     * @return ComponenteXDocumento
     */
    public function setPosicionY($posicionY)
    {
        $this->posicionY = $posicionY;

        return $this;
    }

    /**
     * Get posicionY
     *
     * @return string
     */
    public function getPosicionY()
    {
        return $this->posicionY;
    }

    /**
     * Set estado
     *
     * @param boolean $estado
     *
     * @return ComponenteXDocumento
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
     * Set documento
     *
     * @param \AppBundle\Entity\Documento $documento
     *
     * @return ComponenteXDocumento
     */
    public function setDocumento(\AppBundle\Entity\Documento $documento = null)
    {
        $this->documento = $documento;

        return $this;
    }

    /**
     * Get documento
     *
     * @return \AppBundle\Entity\Documento
     */
    public function getDocumento()
    {
        return $this->documento;
    }

    /**
     * Set componente
     *
     * @param \AppBundle\Entity\Componente $componente
     *
     * @return ComponenteXDocumento
     */
    public function setComponente(\AppBundle\Entity\Componente $componente = null)
    {
        $this->componente = $componente;

        return $this;
    }

    /**
     * Get componente
     *
     * @return \AppBundle\Entity\Componente
     */
    public function getComponente()
    {
        return $this->componente;
    }
}
