<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="erzeuger")
 */
class Erzeuger
{

    public function __construct()
    {
        $this->gestaltungen = new ArrayCollection();
        $this->drucke = new ArrayCollection();
        $this->fotografien= new ArrayCollection();
    }


    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true, nullable=false)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="Gestaltung", mappedBy="erzeuger")
     */
    private $gestaltungen;

    /**
     * @ORM\ManyToMany(targetEntity="Druck", mappedBy="erzeuger")
     */
    private $drucke;

    /**
     * @ORM\ManyToMany(targetEntity="Fotografie", mappedBy="erzeuger")
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
     * Set name
     *
     * @param string $name
     *
     * @return Erzeuger
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add gestaltungen
     *
     * @param \AppBundle\Entity\Gestaltung $gestaltungen
     *
     * @return Erzeuger
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
     * @return Erzeuger
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
     * @return Erzeuger
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
