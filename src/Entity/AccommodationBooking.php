<?php

namespace App\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity()
 */
class AccommodationBooking implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Accommodation
     * @ORM\ManyToOne(targetEntity="App\Entity\Accommodation", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="accommodation_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $accommodation;

    /**
     * @var Destination
     * @ORM\ManyToOne(targetEntity="App\Entity\Destination", cascade={"persist"})
     * @ORM\JoinColumn(name="destination_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $destination;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $orderNumber;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $days;

    /**
     *@var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateIn;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Booking", cascade={"persist"})
     * @ORM\JoinColumn(name="booking_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $booking;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Booking", inversedBy="accommodationsExt", cascade={"persist"})
     * @ORM\JoinColumn(name="booking_ext_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $bookingExt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $canceled;

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
    public function getAccommodation()
    {
        return $this->accommodation;
    }

    /**
     * @param mixed $accommodation
     */
    public function setAccommodation( $accommodation ): void
    {
        $this->accommodation = $accommodation;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param mixed $destination
     */
    public function setDestination( $destination ): void
    {
        $this->destination = $destination;
    }

    /**
     * @return mixed
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param mixed $order
     */
    public function setOrderNumber( $orderNumber ): void
    {
        $this->orderNumber = $orderNumber;
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
     * @return \DateTime
     */
    public function getDateIn()
    {
        return $this->dateIn;
    }

    /**
     * @param \DateTime $dateIn
     */
    public function setDateIn( \DateTime $dateIn ): void
    {
        $this->dateIn = $dateIn;
    }

    /**
     * @return mixed
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * @param mixed $booking
     */
    public function setBooking( $booking ): void
    {
        $this->booking = $booking;
    }

    /**
     * @return mixed
     */
    public function getBookingExt()
    {
        return $this->bookingExt;
    }

    /**
     * @param mixed $booking
     */
    public function setBookingExt( $booking ): void
    {
        $this->bookingExt = $booking;
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

    public function getBefore(){
        return $this->dateIn < $this->bookingExt->getTour()->getStartDate();
    }

    public function jsonSerialize(): array
    {
        $return = [
            'accommodation' => $this->accommodation?$this->accommodation->getName():null,
            'order' => $this->orderNumber,
            'destination' => ($this->destination)?$this->destination->getId():'',
            'destinationName' => ($this->destination)?$this->destination->getName():'',
            'days' => $this->days,
            'dateIn' => ($this->dateIn)?$this->dateIn->format('Y-m-d'):'',
            'id' => $this->id
        ];
        if($this->bookingExt){
            $tour = $this->bookingExt->getTour();
            $return['before'] = ($this->dateIn < $tour->getStartDate())?true:false;
        }
        return $return;
    }

}
