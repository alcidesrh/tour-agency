<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\AbstractClasses\NameAbstract;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity()
 */
class CodeTourOneDay extends NameAbstract
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
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $daysShow;

    /**
     * @param mixed $id
     */
    public function setId( $id ): void
    {
        $this->id = $id;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDaysShow()
    {
        return $this->daysShow ?? 7;
    }

    /**
     * @param mixed $daysShow
     */
    public function setDaysShow( $daysShow ): void
    {
        $this->daysShow = $daysShow;
    }


}
