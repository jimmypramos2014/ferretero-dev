<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductoBase
 *
 * @ORM\Table(name="producto_base")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductoBaseRepository")
 */
class ProductoBase
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
     * @ORM\Column(name="codigo", type="string", length=32)
     * 
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=200)
     * 
     */
    private $nombre;

    /**
     * @ORM\ManyToOne(targetEntity="ProductoCategoria", inversedBy="productoBase", cascade={"persist"})
     * @ORM\JoinColumn(name="categoria_id", referencedColumnName="id")
     * 
     * 
     */
    protected $categoria;

    /**
     * @ORM\ManyToOne(targetEntity="ProductoMarca", inversedBy="productoBase", cascade={"persist"})
     * @ORM\JoinColumn(name="marca_id", referencedColumnName="id")
     * 
     * 
     */
    protected $marca;


    public function __toString()
    {
        return $this->getNombre();
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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return ProductoBase
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
     * Set categoria
     *
     * @param \AppBundle\Entity\ProductoCategoria $categoria
     *
     * @return ProductoBase
     */
    public function setCategoria(\AppBundle\Entity\ProductoCategoria $categoria = null)
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * Get categoria
     *
     * @return \AppBundle\Entity\ProductoCategoria
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Set marca
     *
     * @param \AppBundle\Entity\ProductoMarca $marca
     *
     * @return ProductoBase
     */
    public function setMarca(\AppBundle\Entity\ProductoMarca $marca = null)
    {
        $this->marca = $marca;

        return $this;
    }

    /**
     * Get marca
     *
     * @return \AppBundle\Entity\ProductoMarca
     */
    public function getMarca()
    {
        return $this->marca;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     *
     * @return ProductoBase
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
}
