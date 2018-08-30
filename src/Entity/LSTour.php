<?php

namespace App\Entity;

use App\Entity\AbstractClasses\TourAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\LSTourRepository")
 */
class LSTour extends TourAbstract implements \JsonSerializable
{
    /**
     * @ORM\Id;
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Comment[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="App\Entity\DestinationTour",
     *      mappedBy="tour",
     *      orphanRemoval=true,
     *      cascade={"persist", "remove"})
     */
    private $destinations;

    /**
     * @var Comment[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="Booking",
     *      mappedBy="tour",
     *      orphanRemoval=true,
     *      cascade={"persist", "remove"})
     */

    private $bookings;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $days;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dayWeek;

    /**
     * @var Comment[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="App\Entity\File",
     *      mappedBy="tour",
     *      orphanRemoval=true,
     *      cascade={"persist", "remove"})
     */
    private $attachments;

    /**
     * @var LSTourTemplate
     * @ORM\ManyToOne(targetEntity="App\Entity\LSTourTemplate")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $template;

    /**
     *
     * @var NotificationTour[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\NotificationTour", mappedBy="tour" , cascade={"persist", "remove"})
     */
    private $notifications;



    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->destinations = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

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

    public function addDestination( DestinationTour ...$destinations ): void
    {
        foreach ( $destinations as $destination ) {
            if ( !$this->destinations->contains( $destination ) ) {
                $this->destinations->add( $destination );
            }
        }
    }

    public function removeDestinations( DestinationTour $destination ): void
    {
        $this->destinations->removeElement( $destination );
    }

    public function getDestinations()
    {
        return $this->destinations;
    }
    /**
     * @param mixed $bookings
     */
    public function setBookings( $bookings )
    {
        $this->bookings = $bookings;
    }

    public function addBooking( Booking ...$bookings ): void
    {
        foreach ( $bookings as $booking ) {
            if ( !$this->bookings->contains( $booking ) ) {
                $this->bookings->add( $booking );
            }
        }
    }

    public function removeBooking( Booking $booking ): void
    {
        $this->bookings->removeElement( $booking );
    }

    public function getBookings()
    {
        return $this->bookings;
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
     * @return mixed
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @param mixed $days
     */
    public function setDays( $days ): void
    {
        $this->days = $days;
    }

    /**
     * @return mixed
     */
    public function getDayWeek()
    {
        return $this->dayWeek;
    }

    /**
     * @param mixed $dayWeek
     */
    public function setDayWeek( $dayWeek ): void
    {
        $this->dayWeek = $dayWeek;
    }


    public function addAttached( File ...$attachments ): void
    {
        foreach ( $attachments as $attached ) {
            if ( !$this->attachments->contains( $attached ) ) {
                $this->attachments->add( $attached );
            }
        }
    }

    public function removeAttached( File $attached ): void
    {
        $this->attachments->removeElement( $attached );
    }

    public function getAttachment()
    {
        return $this->attachments;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     */
    public function setTemplate( $template ): void
    {
        $this->template = $template;
    }

    public function getColor(): string
    {
        return $this->color;
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
        return [ 'id' => $this->id, 'name' => $this->name, 'color' => $this->getColor(), 'code' =>$this->code, 'date' => $this->startDate->format('Y-m-d'), 'days' => ($this->endDate && $this->startDate)?$this->startDate->diff($this->endDate)->d:$this->days??0, 'ls' => true, 'guide' => $this->guide?$this->guide->getId():false ];
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
            'days' => $this->days,
            'day' => $this->dayWeek,
            'startDate' => ( $this->startDate ) ? $this->startDate->format( 'Y-m-d' ) : $this->startDate,
            'endDate' => ( $this->endDate ) ? $this->endDate->format( 'Y-m-d' ) : $this->endDate,
            'agent' => $this->agent?$this->agent->getName():false,
            'bookings' => $this->bookings->toArray(),
            'notifications' => $this->notifications->toArray(),
            'destinations' => $this->destinations->toArray(),
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
