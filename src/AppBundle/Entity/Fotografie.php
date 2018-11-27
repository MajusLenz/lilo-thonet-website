<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="fotografie")
 */
class Fotografie {

    public function __construct() {
        $this->archivierungen = new ArrayCollection();
        $this->erzeuger = new ArrayCollection();
    }


    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Ort", inversedBy="fotografien")
     * @ORM\JoinColumn(name="ort_id", referencedColumnName="id")
     */
    private $ort;

    /**
     * @ORM\ManyToMany(targetEntity="Erzeuger", inversedBy="fotografien")
     * @ORM\JoinTable(name="fotografie_erzeuger")
     */
    private $erzeuger;

    /**
     * @ORM\ManyToMany(targetEntity="Archivierung", mappedBy="fotografien")
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
     * Set ort
     *
     * @param \AppBundle\Entity\Ort $ort
     *
     * @return Fotografie
     */
    public function setOrt(\AppBundle\Entity\Ort $ort = null)
    {
        $this->ort = $ort;

        return $this;
    }

    /**
     * Get ort
     *
     * @return \AppBundle\Entity\Ort
     */
    public function getOrt()
    {
        return $this->ort;
    }

    /**
     * Add erzeuger
     *
     * @param \AppBundle\Entity\Erzeuger $erzeuger
     *
     * @return Fotografie
     */
    public function addErzeuger(\AppBundle\Entity\Erzeuger $erzeuger)
    {
        $this->erzeuger[] = $erzeuger;

        return $this;
    }

    /**
     * Remove erzeuger
     *
     * @param \AppBundle\Entity\Erzeuger $erzeuger
     */
    public function removeErzeuger(\AppBundle\Entity\Erzeuger $erzeuger)
    {
        $this->erzeuger->removeElement($erzeuger);
    }

    /**
     * Get erzeuger
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getErzeuger()
    {
        return $this->erzeuger;
    }

    /**
     * Add archivierungen
     *
     * @param \AppBundle\Entity\Archivierung $archivierungen
     *
     * @return Fotografie
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
