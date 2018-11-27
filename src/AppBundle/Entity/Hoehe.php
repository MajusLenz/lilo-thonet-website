<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="hoehe")
 */
class Hoehe
{

    public function __construct() {
        $this->archivierungen = new ArrayCollection();
    }


    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", unique=true, nullable=false)
     */
    private $wert;


    /**
     * @ORM\OneToMany(targetEntity="Archivierung", mappedBy="hoehe")
     */
    private $archivierungen;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set wert
     *
     * @param integer $wert
     *
     * @return Hoehe
     */
    public function setWert($wert)
    {
        $this->wert = $wert;

        return $this;
    }

    /**
     * Get wert
     *
     * @return integer
     */
    public function getWert()
    {
        return $this->wert;
    }

    /**
     * Add archivierungen
     *
     * @param \AppBundle\Entity\Archivierung $archivierungen
     *
     * @return Hoehe
     */
    public function addArchivierungen(\AppBundle\Entity\Archivierung $archivierungen)
    {
        $this->archivierungen[] = $archivierungen;

        return $this;
    }

    /**
     * Remove archivierungen
     *
     * @param \AppBundle\Entity\Archivierung $archivierungen
     */
    public function removeArchivierungen(\AppBundle\Entity\Archivierung $archivierungen)
    {
        $this->archivierungen->removeElement($archivierungen);
    }

    /**
     * Get archivierungen
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArchivierungen()
    {
        return $this->archivierungen;
    }
}
