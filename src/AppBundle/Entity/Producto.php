<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Producto
 *
 * @ORM\Table(name="producto")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductoRepository")
 */
class Producto
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
     * @ORM\Column(name="nombre", type="string", length=200)
     * @Assert\NotBlank(message = "Este valor es requerido")
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=32, nullable=true)
     * @Assert\NotBlank(message = "Este valor es requerido")
     */
    private $codigo;

    /**
     * @var float
     *
     * @ORM\Column(name="precio_unitario", type="float", nullable=true)
     */
    private $precioUnitario;

    /**
     * @var float
     *
     * @ORM\Column(name="precio_cantidad", type="float", nullable=true)
     */
    private $precioCantidad;

    /**
     * @var float
     *
     * @ORM\Column(name="precio_compra", type="float", nullable=true)
     */
    private $precioCompra;

    /**
     * @var string
     *
     * 
     * @Assert\File(maxSize="1M",mimeTypesMessage = "Este formato no es válido, solo se permiten jpeg,png o gif",mimeTypes={ "image/jpeg","image/png","image/gif" })
     *     
     * @ORM\Column(name="imagen", type="string", length=100, nullable=true)
     */
    private $imagen;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo_barra", type="string", length=100, nullable=true)
     */
    private $codigoBarra;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\ManyToOne(targetEntity="Empresa", inversedBy="producto", cascade={"persist"})
     * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id")
     * 
     */
    protected $empresa;

    /**
     * @ORM\ManyToOne(targetEntity="ProductoCategoria", inversedBy="producto", cascade={"persist"})
     * @ORM\JoinColumn(name="categoria_id", referencedColumnName="id")
     * @Assert\NotBlank(message = "Este valor es requerido")
     * 
     */
    protected $categoria;

    /**
     * @ORM\ManyToOne(targetEntity="ProductoMarca", inversedBy="producto", cascade={"persist"})
     * @ORM\JoinColumn(name="marca_id", referencedColumnName="id")
     * @Assert\NotBlank(message = "Este valor es requerido")
     * 
     */
    protected $marca;

    /**
     * @var boolean
     *
     * @ORM\Column(name="estado", type="boolean", nullable=true,options={"default":1, "comment":"Estado del registro"})
     */
    private $estado;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_expiracion", type="datetime", nullable=true)
     */
    private $fecha_expiracion;

     /**
     * @ORM\OneToMany(targetEntity="ProductoXLocal", mappedBy="producto" , cascade={"remove"})
     */
    protected $productoXLocal;


    /**
     * @ORM\ManyToOne(targetEntity="ProductoUnidad", inversedBy="producto", cascade={"persist"})
     * @ORM\JoinColumn(name="unidad_compra_id", referencedColumnName="id")
     * @Assert\NotBlank(message = "Este valor es requerido")
     */
    protected $unidadCompra;

    /**
     * @ORM\ManyToOne(targetEntity="ProductoUnidad", inversedBy="producto", cascade={"persist"})
     * @ORM\JoinColumn(name="unidad_venta_id", referencedColumnName="id")
     * @Assert\NotBlank(message = "Este valor es requerido")
     */
    protected $unidadVenta;

    /**
     * @var float
     *
     * @ORM\Column(name="peso", type="float", nullable=true)
     */
    private $peso;

    /**
     * @ORM\ManyToOne(targetEntity="Moneda", inversedBy="producto", cascade={"persist"})
     * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id")
     * 
     */
    protected $moneda;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=100, nullable=true)
     */
    private $tipo;

    /**
     * @ORM\OneToMany(targetEntity="ProductoXPrecio", mappedBy="producto" ,cascade={"persist","remove"})
     */
    protected $productoXPrecio;

    /**
     * @var float
     *
     * @ORM\Column(name="precio_ponderado", type="float", nullable=true)
     */
    private $precioPonderado;

    /**
     * @var string
     *
     * @ORM\Column(name="posicion", type="string", length=100, nullable=true)
     */
    private $posicion;
 
    /**
     * @ORM\ManyToOne(targetEntity="ProductoCodigoSunat", inversedBy="producto", cascade={"persist"})
     * @ORM\JoinColumn(name="codigo_sunat_id", referencedColumnName="id")
     * 
     */
    protected $codigoSunat;    

    public function __toString()
    {
        return $this->getNombre();
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productoXLocal = new \Doctrine\Common\Collections\ArrayCollection();
        $this->productoXPrecio = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Producto
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
     * Set codigo
     *
     * @param string $codigo
     *
     * @return Producto
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
     * Set precioUnitario
     *
     * @param float $precioUnitario
     *
     * @return Producto
     */
    public function setPrecioUnitario($precioUnitario)
    {
        $this->precioUnitario = $precioUnitario;

        return $this;
    }

    /**
     * Get precioUnitario
     *
     * @return float
     */
    public function getPrecioUnitario()
    {
        return $this->precioUnitario;
    }

    /**
     * Set precioCantidad
     *
     * @param float $precioCantidad
     *
     * @return Producto
     */
    public function setPrecioCantidad($precioCantidad)
    {
        $this->precioCantidad = $precioCantidad;

        return $this;
    }

    /**
     * Get precioCantidad
     *
     * @return float
     */
    public function getPrecioCantidad()
    {
        return $this->precioCantidad;
    }

    /**
     * Set precioCompra
     *
     * @param float $precioCompra
     *
     * @return Producto
     */
    public function setPrecioCompra($precioCompra)
    {
        $this->precioCompra = $precioCompra;

        return $this;
    }

    /**
     * Get precioCompra
     *
     * @return float
     */
    public function getPrecioCompra()
    {
        return $this->precioCompra;
    }

    /**
     * Set imagen
     *
     * @param string $imagen
     *
     * @return Producto
     */
    public function setImagen($imagen)
    {
        $this->imagen = $imagen;

        return $this;
    }

    /**
     * Get imagen
     *
     * @return string
     */
    public function getImagen()
    {
        return $this->imagen;
    }

    /**
     * Set codigoBarra
     *
     * @param string $codigoBarra
     *
     * @return Producto
     */
    public function setCodigoBarra($codigoBarra)
    {
        $this->codigoBarra = $codigoBarra;

        return $this;
    }

    /**
     * Get codigoBarra
     *
     * @return string
     */
    public function getCodigoBarra()
    {
        return $this->codigoBarra;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return Producto
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set estado
     *
     * @param boolean $estado
     *
     * @return Producto
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
     * Set fechaExpiracion
     *
     * @param \DateTime $fechaExpiracion
     *
     * @return Producto
     */
    public function setFechaExpiracion($fechaExpiracion)
    {
        $this->fecha_expiracion = $fechaExpiracion;

        return $this;
    }

    /**
     * Get fechaExpiracion
     *
     * @return \DateTime
     */
    public function getFechaExpiracion()
    {
        return $this->fecha_expiracion;
    }

    /**
     * Set peso
     *
     * @param float $peso
     *
     * @return Producto
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
     * Set tipo
     *
     * @param string $tipo
     *
     * @return Producto
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
     * Set empresa
     *
     * @param \AppBundle\Entity\Empresa $empresa
     *
     * @return Producto
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
     * Set categoria
     *
     * @param \AppBundle\Entity\ProductoCategoria $categoria
     *
     * @return Producto
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
     * @return Producto
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
     * Add productoXLocal
     *
     * @param \AppBundle\Entity\ProductoXLocal $productoXLocal
     *
     * @return Producto
     */
    public function addProductoXLocal(\AppBundle\Entity\ProductoXLocal $productoXLocal)
    {
        $this->productoXLocal[] = $productoXLocal;

        return $this;
    }

    /**
     * Remove productoXLocal
     *
     * @param \AppBundle\Entity\ProductoXLocal $productoXLocal
     */
    public function removeProductoXLocal(\AppBundle\Entity\ProductoXLocal $productoXLocal)
    {
        $this->productoXLocal->removeElement($productoXLocal);
    }

    /**
     * Get productoXLocal
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductoXLocal()
    {
        return $this->productoXLocal;
    }

    /**
     * Set unidadCompra
     *
     * @param \AppBundle\Entity\ProductoUnidad $unidadCompra
     *
     * @return Producto
     */
    public function setUnidadCompra(\AppBundle\Entity\ProductoUnidad $unidadCompra = null)
    {
        $this->unidadCompra = $unidadCompra;

        return $this;
    }

    /**
     * Get unidadCompra
     *
     * @return \AppBundle\Entity\ProductoUnidad
     */
    public function getUnidadCompra()
    {
        return $this->unidadCompra;
    }

    /**
     * Set unidadVenta
     *
     * @param \AppBundle\Entity\ProductoUnidad $unidadVenta
     *
     * @return Producto
     */
    public function setUnidadVenta(\AppBundle\Entity\ProductoUnidad $unidadVenta = null)
    {
        $this->unidadVenta = $unidadVenta;

        return $this;
    }

    /**
     * Get unidadVenta
     *
     * @return \AppBundle\Entity\ProductoUnidad
     */
    public function getUnidadVenta()
    {
        return $this->unidadVenta;
    }

    /**
     * Set moneda
     *
     * @param \AppBundle\Entity\Moneda $moneda
     *
     * @return Producto
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
     * Add productoXPrecio
     *
     * @param \AppBundle\Entity\ProductoXPrecio $productoXPrecio
     *
     * @return Producto
     */
    public function addProductoXPrecio(\AppBundle\Entity\ProductoXPrecio $productoXPrecio)
    {
        // for a many-to-one association:
        $productoXPrecio->setProducto($this);
        $this->productoXPrecio->add($productoXPrecio);   

        return $this;
    }

    /**
     * Remove productoXPrecio
     *
     * @param \AppBundle\Entity\ProductoXPrecio $productoXPrecio
     */
    public function removeProductoXPrecio(\AppBundle\Entity\ProductoXPrecio $productoXPrecio)
    {
        $this->productoXPrecio->removeElement($productoXPrecio);
    }

    /**
     * Get productoXPrecio
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductoXPrecio()
    {
        return $this->productoXPrecio;
    }

    /**
     * Set precioPonderado
     *
     * @param float $precioPonderado
     *
     * @return Producto
     */
    public function setPrecioPonderado($precioPonderado)
    {
        $this->precioPonderado = $precioPonderado;

        return $this;
    }

    /**
     * Get precioPonderado
     *
     * @return float
     */
    public function getPrecioPonderado()
    {
        return $this->precioPonderado;
    }

    /**
     * Set posicion
     *
     * @param string $posicion
     *
     * @return Producto
     */
    public function setPosicion($posicion)
    {
        $this->posicion = $posicion;

        return $this;
    }

    /**
     * Get posicion
     *
     * @return string
     */
    public function getPosicion()
    {
        return $this->posicion;
    }

    /**
     * Set codigoSunat
     *
     * @param \AppBundle\Entity\ProductoCodigoSunat $codigoSunat
     *
     * @return Producto
     */
    public function setCodigoSunat(\AppBundle\Entity\ProductoCodigoSunat $codigoSunat = null)
    {
        $this->codigoSunat = $codigoSunat;

        return $this;
    }

    /**
     * Get codigoSunat
     *
     * @return \AppBundle\Entity\ProductoCodigoSunat
     */
    public function getCodigoSunat()
    {
        return $this->codigoSunat;
    }
}
