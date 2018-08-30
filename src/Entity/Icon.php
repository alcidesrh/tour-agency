<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\AbstractClasses\NameAbstract;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(attributes={"order"={"id": "DESC"}})
 * @ORM\Entity()
 */
class Icon  extends NameAbstract
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId( $id )
    {
        $this->id = $id;
    }
}
