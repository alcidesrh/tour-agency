<?php

namespace App\Entity\AbstractClasses;

use App\Entity\NotificationTemplate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 */
abstract class TourTemplateAbstract extends CodeColorAbstract
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;


    /**
     *
     * @var boolean
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $type;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription( $description )
    {
        $this->description = $description;
    }

    /**
     * @return boolean
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param boolean $private
     */
    public function setType( $type )
    {
        $this->type = $type;
    }

}
