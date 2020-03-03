<?php
namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="usuario")
 */
class Usuario extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
    *  @Assert\NotBlank(message = "Este valor es requerido")
    *  @Assert\Length(
    *      min = 6,
    *      max = 12,
    *      minMessage = "Mínimo {{ limit }} caracteres",
    *      maxMessage = "Máximo {{ limit }} caracteres"
    *  )
    *  @Assert\Regex(
    *     pattern="/\d/",
    *     match=true,
    *     message="Debe contener al menos un dígito"
    *  )    
    */
    protected $plainPassword;

    /**
    *  @Assert\NotBlank(message = "Este valor es requerido")
    *
    */
    protected $username;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

}
