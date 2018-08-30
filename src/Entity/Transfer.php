<?php

namespace App\Entity;

use App\Entity\AbstractClasses\TransferAbstract;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
/**
 * @ApiResource()
 * @ORM\Entity()
 */
class Transfer extends TransferAbstract
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $daysShow;

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
