<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * Proveedor
 *
 * @ORM\Table(name="proveedor",uniqueConstraints={@ORM\UniqueConstraint(name="ruc_empresa_idx", columns={"ruc", "empresa_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProveedorRepository")
 * @UniqueEntity(fields={"ruc","empresa"}, message="El Proveedor que esta intentando registrar ya se encuentra registrado en el Sistema pero con estado eliminado. Para habilitarlo de nuevo debe ir a la lista de Proveedores buscarlo y presionar el icono de habilitar, con esto podrá utilizarlo en el Sistema.")
 */
class Proveedor
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
     * @ORM\Column(name="nombre", type="string", length=100)
     * @Assert\NotBlank(message="Este valor es requerido")
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="ruc", type="string", length=11)
     * @Assert\NotBlank(message="Este valor es requerido")
     * @Assert\Length(
     *      max = 11,
     *      maxMessage = "El RUC no puede tener mas de  {{ limit }} números"
     * )     
     */
    private $ruc;

    /**
     * @var string
     *
     * @ORM\Column(name="ciudad", type="string", length=100,nullable=true)
     */
    private $ciudad;

    /**
     * @var string
     *
     * @ORM\Column(name="telefono", type="string", length=32,nullable=true)
     * 
     */
    private $telefono;

    /**
     * @var string
     *
     * @ORM\Column(name="celular", type="string", length=32,nullable=true)
     */
    private $celular;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100,nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="web", type="string", length=100,nullable=true)
     */
    private $web;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook", type="string", length=100,nullable=true)
     */
    private $facebook;

    /**
     * @var string
     *
     * @ORM\Column(name="cuenta_soles", type="string", length=32,nullable=true)
     */
    private $cuenta_soles;

    /**
     * @var string
     *
     * @ORM\Column(name="cuenta_dolares", type="string", length=32,nullable=true)
     */
    private $cuenta_dolares;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=32, nullable=true)
     * 
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion", type="string", length=255, nullable=true)
     */
    private $direccion;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\ManyToOne(targetEntity="Empresa", inversedBy="proveedor", cascade={"persist"})
     * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id")
     * 
     */
    protected $empresa;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="estado", type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @var string
     *
     * @ORM\Column(name="banco_soles", type="string", length=100,nullable=true)
     */
    private $banco_soles;

    /**
     * @var string
     *
     * @ORM\Column(name="banco_dolares", type="string", length=100,nullable=true)
     */
    private $banco_dolares;

    /**
     * @ORM\ManyToOne(targetEntity="Distrito", inversedBy="proveedor", cascade={"persist"})
     * @ORM\JoinColumn(name="distrito_id", referencedColumnName="id")
     * 
     */
    protected $distrito;

    /**
     * @ORM\ManyToMany(targetEntity="ProveedorTipo", inversedBy="tipos")
     * @ORM\JoinTable(name="proveedor_x_tipo")
     * 
     */
    private $proveedorXTipo;

    /**
     * @var string
     *
     * @ORM\Column(name="condicion", type="string",length=32, nullable=true)
     */
    private $condicion;

    
    public function __toString()
    {
        return strtoupper($this->getNombre());
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
     * @return Proveedor
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
     * Set ruc
     *
     * @param string $ruc
     *
     * @return Proveedor
     */
    public function setRuc($ruc)
    {
        $this->ruc = $ruc;

        return $this;
    }

    /**
     * Get ruc
     *
     * @return string
     */
    public function getRuc()
    {
        return $this->ruc;
    }

    /**
     * Set ciudad
     *
     * @param string $ciudad
     *
     * @return Proveedor
     */
    public function setCiudad($ciudad)
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    /**
     * Get ciudad
     *
     * @return string
     */
    public function getCiudad()
    {
        return $this->ciudad;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     *
     * @return Proveedor
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get telefono
     *
     * @return string
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set celular
     *
     * @param string $celular
     *
     * @return Proveedor
     */
    public function setCelular($celular)
    {
        $this->celular = $celular;

        return $this;
    }

    /**
     * Get celular
     *
     * @return string
     */
    public function getCelular()
    {
        return $this->celular;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Proveedor
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set web
     *
     * @param string $web
     *
     * @return Proveedor
     */
    public function setWeb($web)
    {
        $this->web = $web;

        return $this;
    }

    /**
     * Get web
     *
     * @return string
     */
    public function getWeb()
    {
        return $this->web;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     *
     * @return Proveedor
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get facebook
     *
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set cuentaSoles
     *
     * @param string $cuentaSoles
     *
     * @return Proveedor
     */
    public function setCuentaSoles($cuentaSoles)
    {
        $this->cuenta_soles = $cuentaSoles;

        return $this;
    }

    /**
     * Get cuentaSoles
     *
     * @return string
     */
    public function getCuentaSoles()
    {
        return $this->cuenta_soles;
    }

    /**
     * Set cuentaDolares
     *
     * @param string $cuentaDolares
     *
     * @return Proveedor
     */
    public function setCuentaDolares($cuentaDolares)
    {
        $this->cuenta_dolares = $cuentaDolares;

        return $this;
    }

    /**
     * Get cuentaDolares
     *
     * @return string
     */
    public function getCuentaDolares()
    {
        return $this->cuenta_dolares;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     *
     * @return Proveedor
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
     * Set direccion
     *
     * @param string $direccion
     *
     * @return Proveedor
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * Get direccion
     *
     * @return string
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return Proveedor
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
     * @return Proveedor
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
     * Set empresa
     *
     * @param \AppBundle\Entity\Empresa $empresa
     *
     * @return Proveedor
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
     * Set bancoSoles
     *
     * @param string $bancoSoles
     *
     * @return Proveedor
     */
    public function setBancoSoles($bancoSoles)
    {
        $this->banco_soles = $bancoSoles;

        return $this;
    }

    /**
     * Get bancoSoles
     *
     * @return string
     */
    public function getBancoSoles()
    {
        return $this->banco_soles;
    }

    /**
     * Set bancoDolares
     *
     * @param string $bancoDolares
     *
     * @return Proveedor
     */
    public function setBancoDolares($bancoDolares)
    {
        $this->banco_dolares = $bancoDolares;

        return $this;
    }

    /**
     * Get bancoDolares
     *
     * @return string
     */
    public function getBancoDolares()
    {
        return $this->banco_dolares;
    }

    /**
     * Set distrito
     *
     * @param \AppBundle\Entity\Distrito $distrito
     *
     * @return Proveedor
     */
    public function setDistrito(\AppBundle\Entity\Distrito $distrito = null)
    {
        $this->distrito = $distrito;

        return $this;
    }

    /**
     * Get distrito
     *
     * @return \AppBundle\Entity\Distrito
     */
    public function getDistrito()
    {
        return $this->distrito;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->proveedorXTipo = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add proveedorXTipo
     *
     * @param \AppBundle\Entity\ProveedorTipo $proveedorXTipo
     *
     * @return Proveedor
     */
    public function addProveedorXTipo(\AppBundle\Entity\ProveedorTipo $proveedorXTipo)
    {
        $this->proveedorXTipo[] = $proveedorXTipo;

        return $this;
    }

    /**
     * Remove proveedorXTipo
     *
     * @param \AppBundle\Entity\ProveedorTipo $proveedorXTipo
     */
    public function removeProveedorXTipo(\AppBundle\Entity\ProveedorTipo $proveedorXTipo)
    {
        $this->proveedorXTipo->removeElement($proveedorXTipo);
    }

    /**
     * Get proveedorXTipo
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProveedorXTipo()
    {
        return $this->proveedorXTipo;
    }


    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {

        if ($this->proveedorXTipo->count()==0) {
            $context->buildViolation('Este valor es requerido.')
                ->atPath('proveedorXTipo')
                ->addViolation();
        }
    }


    /**
     * Set condicion
     *
     * @param string $condicion
     *
     * @return Proveedor
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
}
