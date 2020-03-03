<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DetalleProformaEntrega
 *
 * @ORM\Table(name="detalle_proforma_entrega")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DetalleProformaEntregaRepository")
 */
class DetalleProformaEntrega
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
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=true)
     */
    private $fecha;

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="detalleProformaEntrega", cascade={"persist"})
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * 
     */
    protected $usuario;

    /**
     * @ORM\ManyToOne(targetEntity="DetalleProforma", inversedBy="detalleProformaEntrega", cascade={"persist"})
     * @ORM\JoinColumn(name="detalle_proforma_id", referencedColumnName="id")
     * 
     */
    protected $detalleProforma;

    /**
     * @var string
     *
     * @ORM\Column(name="identificador", type="string", nullable=true)
     */
    private $identificador;


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
     * Set cantidad
     *
     * @param float $cantidad
     *
     * @return DetalleProformaEntrega
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
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return DetalleProformaEntrega
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
     * Set usuario
     *
     * @param \AppBundle\Entity\Usuario $usuario
     *
     * @return DetalleProformaEntrega
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
     * Set detalleProforma
     *
     * @param \AppBundle\Entity\DetalleProforma $detalleProforma
     *
     * @return DetalleProformaEntrega
     */
    public function setDetalleProforma(\AppBundle\Entity\DetalleProforma $detalleProforma = null)
    {
        $this->detalleProforma = $detalleProforma;

        return $this;
    }

    /**
     * Get detalleProforma
     *
     * @return \AppBundle\Entity\DetalleProforma
     */
    public function getDetalleProforma()
    {
        return $this->detalleProforma;
    }

    /**
     * Set identificador
     *
     * @param string $identificador
     *
     * @return DetalleProformaEntrega
     */
    public function setIdentificador($identificador)
    {
        $this->identificador = $identificador;

        return $this;
    }

    /**
     * Get identificador
     *
     * @return string
     */
    public function getIdentificador()
    {
        return $this->identificador;
    }
}
