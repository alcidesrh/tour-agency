<?php

namespace App\Entity;


use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\AbstractClasses\NameAbstract;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(attributes={"order"={"id": "ASC"}})
 * @ORM\Entity(repositoryClass="App\Repository\GuideRepository")
 */
class Guide extends NameAbstract
{
    const NUM_ITEMS = 10;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     */
    private $email;

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }


    public function setId( $id )
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->name;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName( string $firstName )
    {
        $this->name = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName( string $lastName )
    {
        $this->lastName = $lastName;
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
    public function setPhone( string $phone )
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail( string $email )
    {
        $this->email = $email;
    }
}
