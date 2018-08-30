<?php

namespace App\Entity;

use App\Entity\AbstractClasses\NameAbstract;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
/**
 * @ApiResource(attributes={"order"={"id": "DESC"}})
 * @ORM\Entity()
 */
class Destination extends NameAbstract
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Province", cascade={"persist"})
     * @ORM\JoinColumn(name="province_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $province;
    /**
     * @return mixed
     */
    public function getId(): int
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
    public function getProvince(): Province
    {
        return $this->province;
    }

    /**
     * @param mixed $province
     */
    public function setProvince( Province $province ): void
    {
        $this->province = $province;
    }

}
