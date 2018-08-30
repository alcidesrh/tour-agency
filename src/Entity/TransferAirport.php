<?php

namespace App\Entity;

use App\Entity\AbstractClasses\TransferAbstract;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
/**
 * @ApiResource()
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="App\Repository\TransferAirportRepository")
 */
class TransferAirport extends TransferAbstract implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $flightData;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AccommodationBooking", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="accommodation_booking_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $accommodation;

    /**
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $inTranfer;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Booking", mappedBy="airPortTransferIn", cascade={"persist"})
     */

    private $bookingIn;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Booking", mappedBy="airPortTransferOut", cascade={"persist"})
     */

    private $bookingOut;


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
    public function getFlightData()
    {
        return $this->flightData;
    }

    /**
     * @param mixed $flightData
     */
    public function setFlightData( $flightData ): void
    {
        $this->flightData = $flightData;
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
    public function getInTranfer()
    {
        return $this->inTranfer;
    }

    /**
     * @param mixed $inTranfer
     */
    public function setInTranfer( $inTranfer ): void
    {
        $this->inTranfer = $inTranfer;
    }

    /**
     * @return mixed
     */
    public function getBookingIn()
    {
        return $this->bookingIn;
    }

    /**
     * @param mixed $bookingIn
     */
    public function setBookingIn( $bookingIn ): void
    {
        $this->bookingIn = $bookingIn;
    }

    /**
     * @return mixed
     */
    public function getBookingOut()
    {
        return $this->bookingOut;
    }

    /**
     * @param mixed $bookingOut
     */
    public function setBookingOut( $bookingOut ): void
    {
        $this->bookingOut = $bookingOut;
    }

    public function getBooking(){
        return $this->bookingIn ?? $this->bookingOut;
    }


    public function retrieve(): array {

        $booking = $this->inTranfer?$this->bookingIn:$this->bookingOut;
        if($tour = $booking->getTourObject())
        $code = $booking->getTourObject()->getCode();
        else if($this->tour){
            $code = $this->tour->getCode();
        }
        return [
            'booking' => $booking->getName(),
            'in' => $this->inTranfer,
            'accommodation' => $this->accommodation?$this->accommodation->getAccommodation()->getName():"",
            'flyData' => $this->flightData,
            'date' => $this->date->format('Y-m-d'),
            'time' => $this->date->format('H:i'),
            'driver' => $this->drive,
            'tour' => $code ?? false,
            'number' => $this->numberPerson,
            'id' => $this->id,
            'canceled' => $this->canceled,
            'sent' => $this->sent
        ];
    }
    public function getTransferForList(): array {
    return [
        'booking' => ($this->bookingIn)?$this->bookingIn->getName():$this->bookingOut->getName(),
        'in' => $this->inTranfer,
        'accommodation' => $this->accommodation && $this->accommodation->getAccommodation() && $this->accommodation->getAccommodation()->getName()?true:false,
        'flyData' => $this->flightData?true:false,
        'id' => $this->id,
        'companyColor' => $this->company?$this->company->getColor():false,
        'driver' => $this->drive?true:false,
        'sent' => $this->sent,
        'canceled' => $this->canceled
    ];
}
    public function jsonSerialize(): array
    {
        return [
            'startDate' => ($this->date)?$this->date->format('Y-m-d'):false,
            'startTime' => ($this->date)?$this->date->format('H:i'):false,
            'drive' => $this->drive,
            'accommodation' => $this->accommodation,
            'flyData' => $this->flightData,
            'canceled' => $this->canceled,
            'bookingName' => ($this->bookingIn)?$this->bookingIn->getName():$this->bookingOut->getName(),
            'in' => $this->inTranfer,
            'id' => $this->id,
            'sent' => $this->sent,
            ];
    }


}
