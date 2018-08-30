<?php

namespace App\Entity;

use App\Entity\AbstractClasses\NameAbstract;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource()
 * @ORM\Entity()
 */
class TransferReciptive extends NameAbstract
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
    private $paxMin;

    /**
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $paxMax;

    /**
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;

    /**
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;

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
    public function getPaxMin()
    {
        return $this->paxMin;
    }

    /**
     * @param mixed $paxMin
     */
    public function setPaxMin( $paxMin ): void
    {
        $this->paxMin = $paxMin;
    }

    /**
     * @return mixed
     */
    public function getPaxMax()
    {
        return $this->paxMax;
    }

    /**
     * @param mixed $paxMax
     */
    public function setPaxMax( $paxMax ): void
    {
        $this->paxMax = $paxMax;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone( $phone ): void
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail( $email ): void
    {
        $this->email = $email;
    }


}
