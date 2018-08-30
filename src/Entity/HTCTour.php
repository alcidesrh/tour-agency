<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\AbstractClasses\TourAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 *@ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\HTCTourRepository")
 */
class HTCTour extends TourAbstract implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startTime;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $startPlace;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endTime;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $finishPlace;

    /**
     *
     * @var NotificationTour[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\NotificationTour", mappedBy="htcTour", cascade={"persist", "remove"})
     */
    private $notifications;

    /**
     * @var Comment[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="Booking",
     *      mappedBy="htcTour",
     *      orphanRemoval=true,
     *      cascade={"persist", "remove"})
     */

    private $bookings;

    /**
     * @var LSTourTemplate
     * @ORM\ManyToOne(targetEntity="App\Entity\HTCTourTemplate")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $template;

    /**
     * @var Comment[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="App\Entity\File",
     *      mappedBy="tourHtc",
     *      orphanRemoval=true,
     *      cascade={"persist", "remove"})
     */
    private $attachments;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

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
     * @return NotificationTour[]|ArrayCollection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param NotificationTour[]|ArrayCollection $notifications
     */
    public function setNotifications( $notifications ): void
    {
        $this->notifications = $notifications;
    }

    /**
     * @return Comment[]|ArrayCollection
     */
    public function getBookings()
    {
        return $this->bookings;
    }

    /**
     * @param Comment[]|ArrayCollection $bookings
     */
    public function setBookings( $bookings ): void
    {
        $this->bookings = $bookings;
    }

    /**
     * @return LSTourTemplate
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param LSTourTemplate $template
     */
    public function setTemplate( $template ): void
    {
        $this->template = $template;
    }

    /**
     * @return \DateTime
     */
    public function getStartTime(): \DateTime
    {
        return $this->startTime;
    }

    /**
     * @param \DateTime $startTime
     */
    public function setStartTime( \DateTime $startTime ): void
    {
        $this->startTime = $startTime;
    }

    /**
     * @return string
     */
    public function getStartPlace(): string
    {
        return $this->startPlace;
    }

    /**
     * @param string $startPlace
     */
    public function setStartPlace( string $startPlace ): void
    {
        $this->startPlace = $startPlace;
    }

    /**
     * @return \DateTime
     */
    public function getEndTime(): \DateTime
    {
        return $this->endTime;
    }

    /**
     * @param \DateTime $endTime
     */
    public function setEndTime( \DateTime $endTime ): void
    {
        $this->endTime = $endTime;
    }

    /**
     * @return string
     */
    public function getFinishPlace(): string
    {
        return $this->finishPlace;
    }

    /**
     * @param string $finishPlace
     */
    public function setFinishPlace( string $finishPlace ): void
    {
        $this->finishPlace = $finishPlace;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getAttachment()
    {
        return $this->attachments;
    }

    /**
     * @param mixed $canceled
     */
    public function setCanceled(): void
    {
        $this->canceled = !$this->canceled;
        foreach ($this->bookings as $booking)
            $booking->setCanceled($this->canceled);
    }

    public function jsonSerialize(): array
    {
        return [ 'id' => $this->id, 'name' => $this->name, 'color' => $this->getColor(), 'notifications' => $this->notifications->toArray(), 'code' =>$this->code, 'canceled' => $this->canceled ];
    }

    public function getTourForShedule(): array
    {
        return [ 'id' => $this->id, 'name' => $this->name, 'color' => $this->getColor(), 'code' =>$this->code, 'templateCode' => $this->template?$this->getTemplate()->getCode():"", 'date' => $this->startDate->format('Y-m-d'), 'days' => 1, 'ls' => false, 'guide' => $this->guide?$this->guide->getId():false];
    }

    public function getGroupSize(){
        $persons = 0;
        foreach ($this->bookings as $booking){
            $persons += $booking->getNumberPerson();
        }
        return $persons;
    }

    public function getTour(): array
    {

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'color' => $this->getColor(),
            'type' => $this->type,
            'startDate' => ( $this->startDate ) ? $this->startDate->format( 'Y-m-d' ) : $this->startDate,
            'startTime' => $this->startTime->format('Y-m-d H:i'),
            'endTime' => $this->endTime->format('Y-m-d H:i'),
            'startPlace' => $this->startPlace,
            'finishPlace' => $this->finishPlace,
            'agent' => $this->agent?$this->agent->getName():false,
            'bookings' => $this->bookings->toArray(),
            'notifications' => $this->notifications->toArray(),
            'attachments' => $this->attachments->toArray(),
            'description' => $this->description,
            'canceled' => $this->canceled,
            'persons' => $this->getGroupSize(),
            'description' => $this->description,
            'guide' => $this->guide?$this->guide->getName():false,
            'office' => $this->office
        ];
    }

}
