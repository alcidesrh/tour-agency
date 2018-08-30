<?php

namespace App\Entity\AbstractClasses;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 */
abstract class TransferAbstract
{

    /**
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $numberPerson;
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Driver", cascade={"persist"})
     * @ORM\JoinColumn(name="drive_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $drive;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\HTCTour", cascade={"persist"})
     * @ORM\JoinColumn(name="tour_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $tour;

    /**
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $sent;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $canceled;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", cascade={"persist"})
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $company;

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
    public function setNumberPerson( $numberPerson )
    {
        $this->numberPerson = $numberPerson;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate( $date )
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getDrive()
    {
        return $this->drive;
    }

    /**
     * @param mixed $drive
     */
    public function setDrive( $drive ): void
    {
        $this->drive = $drive;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $company
     */
    public function setCompany( $company ): void
    {
        $this->company = $company;
    }

    /**
     * @return mixed
     */
    public function getTour()
    {
        return $this->tour;
    }

    /**
     * @param mixed $tour
     */
    public function setTour( $tour ): void
    {
        $this->tour = $tour;
    }

    /**
     * @return mixed
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * @param mixed $sent
     */
    public function setSent( $sent ): void
    {
        $this->sent = $sent;
    }


    /**
     * @return mixed
     */
    public function getCanceled()
    {
        return $this->canceled;
    }

    /**
     * @param mixed $canceled
     */
    public function setCanceled( $canceled ): void
    {
        $this->canceled = $canceled;
    }


}
