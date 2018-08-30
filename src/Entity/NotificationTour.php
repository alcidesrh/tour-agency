<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="App\Repository\NotificationTourRepository")
 */
class NotificationTour implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Notification", inversedBy="notifications", cascade={"persist"})
     * @ORM\JoinColumn(name="notification_id", referencedColumnName="id", onDelete="CASCADE" , nullable=true)
     */
    private $notification;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LSTour", inversedBy="notifications")
     * @ORM\JoinColumn(name="tour_ls_id", onDelete="CASCADE")
     */
    private $tour;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\HTCTour", inversedBy="notifications")
     * @ORM\JoinColumn(name="tour_htc_id", onDelete="CASCADE")
     */
    private $htcTour;

    /**
     * @ORM\Column(type="integer")
     */
    private $days;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $done;

    public function __construct(Notification $notification, $days = null)
    {
        $this->notification = $notification;
        $this->days = $days;
        $this->done = false;
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
     * @return mixed
     */
    public function getDone()
    {
        return $this->done;
    }

    /**
     * @param mixed $done
     */
    public function setDone($done = null): void
    {
        $this->done = is_null($done)?!$this->done:$done;
    }


    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'days' => $this->days,
            'icon' => $this->notification->getIcon()->getName(),
            'notification' => $this->notification->getId(),
            'done' => $this->done,
            'name' => $this->notification->getName()
        ];
    }


}
