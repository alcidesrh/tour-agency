<?php

namespace App\Entity;

use App\Entity\AbstractClasses\NameAbstract;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class File extends NameAbstract implements \JsonSerializable
{
    const FILE_PATH = "files/";
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */

    private $createDate;

    /**
     * @var string
     *
     * @ORM\Column(type="boolean")
     */

    private $temporal;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */

    private $path;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LSTour")
     * @ORM\JoinColumn(name="tour_ls_id", referencedColumnName="id", nullable=true)
     */
    private $tour;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\HTCTour")
     * @ORM\JoinColumn(name="tour_htc_id", referencedColumnName="id", nullable=true)
     */
    private $tourHtc;

    public function __construct()
    {
        $this->createDate = new \DateTime();
        $this->temporal = true;
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
    public function getCreateDate(): \DateTime
    {
        return $this->createDate;
    }

    /**
     * @param \DateTime $createDate
     */
    public function setCreateDate( \DateTime $createDate ): void
    {
        $this->createDate = $createDate;
    }

    /**
     * @return string
     */
    public function getTemporal(): string
    {
        return $this->temporal;
    }

    /**
     * @param string $temporal
     */
    public function setTemporal( bool $temporal ): void
    {
        $this->temporal = $temporal;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath( string $path ): void
    {
        $this->path = $path;
    }

    /**
     * @ORM\PreRemove()
     */
    public function removeFile()
    {
        $fs = new Filesystem();
        $fs->remove( self::FILE_PATH . $this->path );
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
    public function getTourHtc()
    {
        return $this->tourHtc;
    }

    /**
     * @param mixed $tourHtc
     */
    public function setTourHtc( $tourHtc ): void
    {
        $this->tourHtc = $tourHtc;
    }

    public function pathToDownload()
    {
        return self::FILE_PATH . $this->path;
    }

    public function jsonSerialize(): array
    {
        return [ 'id' => $this->id, 'name' => $this->name, 'path' => $this->path ];
    }
}
