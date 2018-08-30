<?php

namespace App\Entity;

use App\Entity\AbstractClasses\NameAbstract;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
/**
 * @ApiResource(attributes={"order"={"id": "DESC"}})
 * @ORM\Entity()
 */
class Extention implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     *@var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $type;

    /**
     *@var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Booking", cascade={"persist"})
     * @ORM\JoinColumn(name="booking_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $booking;

    /**
     *@var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $description;

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
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate( \DateTime $date ): void
    {
        $this->date = $date;
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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription( string $description ): void
    {
        $this->description = $description;
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



    public function jsonSerialize(): array
    {
        $return = [
            'id' => $this->id,
            'dateIn' => $this->date->format('Y-m-d'),
        ];
        if( $tour = $this->tour ?? $this->htcTour ){
            $return['type'] = 'extra_tour';
            $return['tour'] = $tour;
        }
        else{
            $return['type'] = 'extra_info';
            $return['description'] = $this->description;
        }
        return $return;
    }

}
