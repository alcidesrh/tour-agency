<?php

namespace App\Entity;

use App\Entity\AbstractClasses\TourTemplateAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
/**
 *@ApiResource()
 *@ORM\Entity(repositoryClass="App\Repository\HTCTourTemplateRepository")
 *@ORM\Table(name="htc_tour_template")
 *@ORM\AssociationOverrides({
 *      @ORM\AssociationOverride(name="notifications",
 *          joinTable=@ORM\JoinTable(
 *              name="htc_tour_template_notification",
 *              joinColumns=@ORM\JoinColumn(name="htc_tour_template_id"),
 *          )
 *      )
 * })
 */
class HTCTourTemplate extends TourTemplateAbstract
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
     * @var NotificationTemplate[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\NotificationTemplate", mappedBy="tourTemplate" , cascade={"persist", "remove"})
     */


    private $notifications;


    public function __construct()
    {
        $this->notifications = new ArrayCollection();
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
     * @return \DateTime
     */
    public function getStartTime()
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
    public function getStartPlace()
    {
        return $this->startPlace;
    }

    /**
     * @param string $startPlace
     */
    public function setStartPlace( $startPlace ): void
    {
        $this->startPlace = $startPlace;
    }

    /**
     * @return \DateTime
     */
    public function getEndTime()
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
    public function getFinishPlace()
    {
        return $this->finishPlace;
    }

    /**
     * @param string $endPlace
     */
    public function setFinishPlace( $finishPlace ): void
    {
        $this->finishPlace = $finishPlace;
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
