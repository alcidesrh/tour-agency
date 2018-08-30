<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\AbstractClasses\NameAbstract;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(attributes={"order"={"id": "DESC"}})
 * @ORM\Entity(repositoryClass="App\Repository\DriverRepository")
 */
class Driver extends NameAbstract implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $number;

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
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone( string $phone ): void
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     */
    public function setNumber( $number ): void
    {
        $this->number = $number;
    }

    public function jsonSerialize(): array
    {
        return [ 'id' => $this->id, 'name' => $this->name, 'number' => $this->number, 'phone' => $this->phone ];
    }
}
