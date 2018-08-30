<?php

namespace App\Entity;

use App\Entity\AbstractClasses\CodeColorAbstract;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
/**
 * @ApiResource()
 * @ORM\Entity()
 */
class Company extends CodeColorAbstract
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CodeTour", cascade={"persist"})
     * @ORM\JoinColumn(name="code_ls_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $companyLS;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CodeTourOneDay", cascade={"persist"})
     * @ORM\JoinColumn(name="code_htc_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $companyHTC;
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
    public function getCompanyLS()
    {
        return $this->companyLS;
    }

    /**
     * @param mixed $companyLS
     */
    public function setCompanyLS( $companyLS ): void
    {
        $this->companyLS = $companyLS;
    }

    /**
     * @return mixed
     */
    public function getCompanyHTC()
    {
        return $this->companyHTC;
    }

    /**
     * @param mixed $companyHTC
     */
    public function setCompanyHTC( $companyHTC ): void
    {
        $this->companyHTC = $companyHTC;
    }



}
