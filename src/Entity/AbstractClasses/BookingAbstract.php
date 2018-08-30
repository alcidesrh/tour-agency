<?php

namespace App\Entity\AbstractClasses;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass()
 */
abstract class BookingAbstract extends NameAbstract
{

    /**
     *
     * @ORM\Column(type="integer")
     * @Assert\Type("integer")
     */
    protected $numberPerson;

    /**
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type("integer")
     */
    protected $lp;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $sleepingRequirement;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $canceled;

    /**
     * @return mixed
     */
    public function getNumberPerson()
    {
        return $this->numberPerson;
    }

    /**
     * @param mixed $numberPerson
     */
    public function setNumberPerson( int $numberPerson )
    {
        $this->numberPerson = $numberPerson;
    }

    /**
     * @return mixed
     */
    public function getLp()
    {
        return $this->lp;
    }

    /**
     * @param mixed $lp
     */
    public function setLp( $lp )
    {
        $this->lp = $lp;
    }

    /**
     * @return string
     */
    public function getSleepingRequirement()
    {
        return $this->sleepingRequirement;
    }

    /**
     * @param string $sleepingRequirement
     */
    public function setSleepingRequirement(  $sleepingRequirement ): void
    {
        $this->sleepingRequirement = $sleepingRequirement;
    }

    /**
     * @return mixed
     */
    public function getCanceled()
    {
        return $this->canceled;
    }

}
