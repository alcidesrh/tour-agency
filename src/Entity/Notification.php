<?php

namespace App\Entity;

use App\Entity\AbstractClasses\NameAbstract;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
/**
 * @ApiResource(attributes={"order"={"orderList": "ASC"}})
 * @ORM\Entity()
 */
class Notification extends NameAbstract implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $types;

    /**
     * @ORM\ManyToOne(targetEntity="Icon")
     * @ORM\JoinColumn(name="icon_id", referencedColumnName="id", onDelete="SET NULL" , nullable=true)
     */
    private $icon;

    /**
     * @ORM\Column(type="integer")
     */
    private $days;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderList;

    public function __construct()
    {
        $this->orderList = 0;
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
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param array $type
     */
    public function setTypes( array $types ): void
    {
        $this->types = $types;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     */
    public function setIcon( $icon ): void
    {
        $this->icon = $icon;
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
    public function getOrderList()
    {
        return $this->orderList;
    }

    /**
     * @param mixed $order
     */
    public function setOrderList( $orderList ): void
    {
        $this->orderList = $orderList;
    }


    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'days' => $this->days,
            'type' => $this->types,
            'icon' => $this->icon->getName(),
            'code' => $this->code,
            'order' => $this->orderList
        ];
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode( $code ): void
    {
        $this->code = $code;
    }


}
