<?php

namespace App\Entity\AbstractClasses;

use App\Entity\Booking;
use App\Entity\Notification;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 */
abstract class TourAbstract extends CodeColorAbstract
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $type;
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Agent")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $agent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TourOffice")
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $office;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Guide")
     * @ORM\JoinColumn(name="guide_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $guide;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $canceled;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $ext;


    /**
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $sent;

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription( string $description )
    {
        $this->description = $description;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt( \DateTime $createdAt )
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }


    /**
     * @param \DateTime $startDate
     */
    public function setStartDate( \DateTime $startDate )
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate( \DateTime $endDate )
    {
        $this->endDate = $endDate;
    }


    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType( string $type ): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param mixed $agent
     */
    public function setAgent( $agent ): void
    {
        $this->agent = $agent;
    }

    /**
     * @return mixed
     */
    public function getOffice()
    {
        return $this->office;
    }

    /**
     * @param mixed $office
     */
    public function setOffice( $office ): void
    {
        $this->office = $office;
    }


    /**
     * @return mixed
     */
    public function getGuide()
    {
        return $this->guide;
    }

    /**
     * @param mixed $guide
     */
    public function setGuide( $guide ): void
    {
        $this->guide = $guide;
    }



    /**
     * @return boolean
     */
    public function isPrivate(): bool
    {
        return $this->type == 'private'?true:false;
    }

    /**
     * @return mixed
     */
    public function isCanceled()
    {
        return $this->canceled;
    }

    public function getCanceled(){
        return $this->canceled;
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
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * @param mixed $ext
     */
    public function setExt( $ext ): void
    {
        $this->ext = $ext;
    }

}
