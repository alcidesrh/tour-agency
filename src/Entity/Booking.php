<?php

namespace App\Entity;

use App\Entity\AbstractClasses\BookingAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Booking extends BookingAbstract implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\OneToOne(targetEntity="App\Entity\TransferAirport", inversedBy="bookingIn", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="airport_transfer_in_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $airPortTransferIn;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TransferAirport", inversedBy="bookingOut", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="airport_transfer_out_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $airPortTransferOut;

    /**
     * @var Comment[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="App\Entity\AccommodationBooking",
     *      mappedBy="booking",
     *      orphanRemoval=true,
     *      cascade={"persist", "remove"})
     */


    private $accommodations;

    /**
     * @var Comment[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="App\Entity\AccommodationBooking",
     *      mappedBy="bookingExt",
     *      orphanRemoval=true,
     *      cascade={"persist", "remove"})
     */


    private $accommodationsExt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LSTour", cascade={"persist"})
     * @ORM\JoinColumn(name="tour_ls_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $tour;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\HTCTour", cascade={"persist"})
     * @ORM\JoinColumn(name="tour_htc_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $htcTour;

    /**
     * @var Comment[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="App\Entity\Extention",
     *      mappedBy="booking",
     *      orphanRemoval=true,
     *      cascade={"persist", "remove"})
     */


    private $extentions;


    public function __construct(Booking $booking = null)
    {
        $this->accommodations = new ArrayCollection();
        $this->accommodationsExt = new ArrayCollection();
        $this->extentions = new ArrayCollection();

        if($booking){
            $this->name = $booking->getName();
            $this->numberPerson = $booking->getNumberPerson();
            $this->lp = $this->getLp();
        }
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

    public function addAccommodation( AccommodationBooking ...$accommodations ): void
    {
        foreach ( $accommodations as $accommodation ) {
            if ( !$this->accommodations->contains( $accommodation ) ) {
                $this->accommodations->add( $accommodation );
            }
        }
    }

    public function getAccommodations( )
    {
        return $this->accommodations;
    }

    public function getAccommodationExt( )
    {
        return $this->accommodationsExt;
    }
    public function addAccommodationExt( AccommodationBooking ...$accommodations ): void
    {
        foreach ( $accommodations as $accommodation ) {
            if ( !$this->accommodationsExt->contains( $accommodation ) ) {
                $this->accommodationsExt->add( $accommodation );
            }
        }
    }

    /**
     * @return mixed
     */
    public function getAirPortTransferIn()
    {
        return $this->airPortTransferIn;
    }

    /**
     * @param mixed $airPortTransferIn
     */
    public function setAirPortTransferIn( $airPortTransferIn ): void
    {
        $this->airPortTransferIn = $airPortTransferIn;
    }

    /**
     * @return mixed
     */
    public function getAirPortTransferOut()
    {
        return $this->airPortTransferOut;
    }

    /**
     * @param mixed $airPortTransferOut
     */
    public function setAirPortTransferOut( $airPortTransferOut ): void
    {
        $this->airPortTransferOut = $airPortTransferOut;
    }

    public function getTourObject(){
        return $this->tour ?? $this->htcTour;
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
    public function getHtcTour()
    {
        return $this->htcTour;
    }

    /**
     * @param mixed $htcTour
     */
    public function setHtcTour( $htcTour ): void
    {
        $this->htcTour = $htcTour;
    }

    /**
     * @return Comment[]|ArrayCollection
     */
    public function getExtentions()
    {
        return $this->extentions;
    }

    /**
     * @param Comment[]|ArrayCollection $extention
     */
    public function setExtentions( $extentions ): void
    {
        $this->extentions = $extentions;
    }



    /**
     * @param mixed $canceled
     */
    public function setCanceled( $canceled ): void
    {
        $this->canceled = $canceled;
        if($this->airPortTransferOut)
        $this->airPortTransferOut->setCanceled($canceled);
        if($this->airPortTransferIn)
        $this->airPortTransferIn->setCanceled($canceled);
    }

    /**
     * @ORM\PreRemove()
     */
    public function removeAccommodationsBooking(LifecycleEventArgs $eventArgs){
        if(empty($this->accommodations))
            return false;
        $em = $eventArgs->getObjectManager();
        foreach ($this->accommodations as $accommodation)
            $em->remove($accommodation);
        foreach ($this->accommodationsExt as $accommodation)
            $em->remove($accommodation);
        if($this->airPortTransferOut)
            $em->remove($this->airPortTransferOut);
        if($this->airPortTransferIn)
            $em->remove($this->airPortTransferIn);
        $em->flush();
    }

    public function jsonSerialize(): array
    {
        return [ 'id' => $this->id,
            'name' => $this->name,
            'persons' => $this->numberPerson,
            'lp' => $this->lp,
            'sleepRequirement' => $this->sleepingRequirement,
            'transferIn' => $this->airPortTransferIn,
            'transferOut' => $this->airPortTransferOut,
            'accommodations' => $this->accommodations->toArray(),
            'canceled' => $this->canceled,
            'extentions' => $this->accommodationsExt?$this->accommodationsExt->toArray():[],
            'otherExtentions' => $this->extentions->toArray() ?? []
        ];
    }


}
