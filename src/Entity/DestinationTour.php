<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity()
 */
class DestinationTour implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="integer")
     */
    private $days;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Destination", cascade={"persist"})
     * @ORM\JoinColumn(name="destination_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $destination;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LSTour", cascade={"persist"})
     * @ORM\JoinColumn(name="tour_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $tour;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LSTourTemplate", cascade={"persist"})
     * @ORM\JoinColumn(name="tour_template_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $tourTemplate;

    public function __construct(Destination $destination, $days = null)
    {
        $this->destination = $destination;
        $this->days = $days;
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
    public function getTourTemplate()
    {
        return $this->tourTemplate;
    }

    /**
     * @param mixed $tourTemplate
     */
    public function setTourTemplate( $tourTemplate ): void
    {
        $this->tourTemplate = $tourTemplate;
    }



    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'days' => $this->days,
            'destination' => $this->destination->getId(),
            'name' => $this->destination->getName()
        ];
    }

}
