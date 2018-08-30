<?php

namespace App\Entity;

use App\Entity\AbstractClasses\TourTemplateAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
/**
 * @ApiResource()
 *@ORM\Entity(repositoryClass="App\Repository\LSTourTemplateRepository")
 *@ORM\Table(name="ls_tour_template")
 *@ORM\AssociationOverrides({
 *      @ORM\AssociationOverride(name="notifications",
 *          joinTable=@ORM\JoinTable(
 *              name="ls_tour_template_notification",
 *              joinColumns=@ORM\JoinColumn(name="ls_tour_template_id"),
 *          )
 *      )
 * })
 */
class LSTourTemplate extends TourTemplateAbstract
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $days;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $day;

    /**
     * @var DestinationTour[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="App\Entity\DestinationTour",
     *      mappedBy="tourTemplate",
     *      cascade={"persist", "remove"})
     */
    private $destinations;

    /**
     *
     * @var NotificationTemplate[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\NotificationTemplate", mappedBy="tour" , cascade={"persist", "remove"})
     */


    private $notifications;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->destinations = new ArrayCollection();
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
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param mixed $day
     */
    public function setDay( $day ): void
    {
        $this->day = $day;
    }

    public function addDestination( DestinationTour $destination ): void
    {
        if ( !$this->destinations->contains( $destination ) ) {
            $this->destinations->add( $destination );
        }
    }

    public function removeDestination( DestinationTour $destination ): void
    {
        $this->destinations->removeElement( $destination );
    }

    public function getDestinations()
    {
        return $this->destinations;
    }

    /**
     * @return NotificationTemplate[]|ArrayCollection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param NotificationTemplate[]|ArrayCollection $notifications
     */
    public function setNotifications( $notifications ): void
    {
        $this->notifications = $notifications;
    }



}
