<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="ort")
 */
class Ort
{

    public function __construct()
    {
        $this->archivierungen = new ArrayCollection();
        $this->gestaltungen = new ArrayCollection();
        $this->drucke = new ArrayCollection();
        $this->fotografien = new ArrayCollection();
    }


    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $stadt;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $land;


    /**
     * @ORM\OneToMany(targetEntity="Archivierung", mappedBy="entstehungsort")
     */
    private $archivierungen;

    /**
     * @ORM\OneToMany(targetEntity="Gestaltung", mappedBy="ort")
     */
    private $gestaltungen;

    /**
     * @ORM\OneToMany(targetEntity="Druck", mappedBy="ort")
     */
    private $drucke;

    /**
     * @ORM\OneToMany(targetEntity="Fotografie", mappedBy="ort")
     */
    private $fotografien;



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
     * Set stadt
     *
     * @param string $stadt
     *
     * @return Ort
     */
    public function setStadt($stadt)
    {
        $this->stadt = $stadt;

        return $this;
    }

    /**
     * Get stadt
     *
     * @return string
     */
    public function getStadt()
    {
        return $this->stadt;
    }

    /**
     * Set land
     *
     * @param string $land
     *
     * @return Ort
     */
    public function setLand($land)
    {
        $this->land = $land;

        return $this;
    }

    /**
     * Get land
     *
     * @return string
     */
    public function getLand()
    {
        return $this->land;
    }

    /**
     * Add archivierungen
     *
     * @param \AppBundle\Entity\Archivierung $archivierungen
     *
     * @return Ort
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

    /**
     * Add gestaltungen
     *
     * @param \AppBundle\Entity\Gestaltung $gestaltungen
     *
     * @return Ort
     */
    public function addGestaltungen(\AppBundle\Entity\Gestaltung $gestaltungen)
    {
        $this->gestaltungen[] = $gestaltungen;

        return $this;
    }

    /**
     * Remove gestaltungen
     *
     * @param \AppBundle\Entity\Gestaltung $gestaltungen
     */
    public function removeGestaltungen(\AppBundle\Entity\Gestaltung $gestaltungen)
    {
        $this->gestaltungen->removeElement($gestaltungen);
    }

    /**
     * Get gestaltungen
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGestaltungen()
    {
        return $this->gestaltungen;
    }

    /**
     * Add drucke
     *
     * @param \AppBundle\Entity\Druck $drucke
     *
     * @return Ort
     */
    public function addDrucke(\AppBundle\Entity\Druck $drucke)
    {
        $this->drucke[] = $drucke;

        return $this;
    }

    /**
     * Remove drucke
     *
     * @param \AppBundle\Entity\Druck $drucke
     */
    public function removeDrucke(\AppBundle\Entity\Druck $drucke)
    {
        $this->drucke->removeElement($drucke);
    }

    /**
     * Get drucke
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDrucke()
    {
        return $this->drucke;
    }

    /**
     * Add fotografien
     *
     * @param \AppBundle\Entity\Fotografie $fotografien
     *
     * @return Ort
     */
    public function addFotografien(\AppBundle\Entity\Fotografie $fotografien)
    {
        $this->fotografien[] = $fotografien;

        return $this;
    }

    /**
     * Remove fotografien
     *
     * @param \AppBundle\Entity\Fotografie $fotografien
     */
    public function removeFotografien(\AppBundle\Entity\Fotografie $fotografien)
    {
        $this->fotografien->removeElement($fotografien);
    }

    /**
     * Get fotografien
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFotografien()
    {
        return $this->fotografien;
    }
}
