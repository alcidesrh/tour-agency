<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="NotificationTourRepository")
 */
class NotificationTemplate implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Notification")
     * @ORM\JoinColumn(name="notification_id")
     */
    private $notification;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LSTourTemplate", inversedBy="notifications", cascade={"persist"})
     * @ORM\JoinColumn(name="tour_ls_template_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $tour;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\HTCTourTemplate", inversedBy="notifications", cascade={"persist"})
     * @ORM\JoinColumn(name="tour_htc_template_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $tourTemplate;

    /**
     * @ORM\Column(type="integer")
     */
    private $days;

    public function __construct(Notification $notification, $days = null)
    {
        $this->notification = $notification;
        $this->days = $days;
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
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param mixed $notification
     */
    public function setNotification( $notification ): void
    {
        $this->notification = $notification;
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
            'notification' => $this->notification->getId()
        ];
    }


}
