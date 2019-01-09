<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="Archivierung")
 */
class Archivierung
{

    public function __construct()
    {
        $now = new DateTime("now");
        $this->erstelldatum = $now;
        $this->letzteBearbeitung = $now;
        $this->infos = new ArrayCollection();
        $this->jahre = new ArrayCollection();
    }

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $erstelldatum;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $letzteBearbeitung;

    /**
     * @ORM\Column(type="string", unique=true, nullable=false)
     */
    private $dateiname;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $dateinameAlt;

    /**
     * @ORM\Column(type="string", unique=true, nullable=false)
     */
    private $dateiHash;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $archivierungsArt;      // Grafik, Moebel, etc.


    ///// ASSOZIATIONEN:

    /*/**
     * @ORM\ManyToMany(targetEntity="Information", cascade={"persist"}, inversedBy="archivierung")
     * @ORM\JoinTable(name="Archivierung_Information",
     *      joinColumns={@ORM\JoinColumn(name="information_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="archivierung_id", referencedColumnName="id")}
     * )
     */
    /**
     * @ORM\ManyToMany(targetEntity="Information", inversedBy="archivierungen", cascade={"persist"})
     * @ORM\JoinTable(name="Archivierung_Information")
     */
    private $infos;

    /**
     * @ORM\ManyToMany(targetEntity="Jahr", inversedBy="archivierungen", cascade={"persist"})
     * @ORM\JoinTable(name="Archivierung_Jahr")
     */
    private $jahre;


    ///// GETTER & SETTER:


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
     * Set erstelldatum
     *
     * @param \DateTime $erstelldatum
     *
     * @return Archivierung
     */
    public function setErstelldatum($erstelldatum)
    {
        $this->erstelldatum = $erstelldatum;

        return $this;
    }

    /**
     * Get erstelldatum
     *
     * @return \DateTime
     */
    public function getErstelldatum()
    {
        return $this->erstelldatum;
    }

    /**
     * Set letzteBearbeitung
     *
     * @param \DateTime $letzteBearbeitung
     *
     * @return Archivierung
     */
    public function setLetzteBearbeitung($letzteBearbeitung)
    {
        $this->letzteBearbeitung = $letzteBearbeitung;

        return $this;
    }

    /**
     * Get letzteBearbeitung
     *
     * @return \DateTime
     */
    public function getLetzteBearbeitung()
    {
        return $this->letzteBearbeitung;
    }

    /**
     * Set dateiname
     *
     * @param string $dateiname
     *
     * @return Archivierung
     */
    public function setDateiname($dateiname)
    {
        $this->dateiname = $dateiname;

        return $this;
    }

    /**
     * Get dateiname
     *
     * @return string
     */
    public function getDateiname()
    {
        return $this->dateiname;
    }

    /**
     * Set dateinameAlt
     *
     * @param string $dateinameAlt
     *
     * @return Archivierung
     */
    public function setDateinameAlt($dateinameAlt)
    {
        $this->dateinameAlt = $dateinameAlt;

        return $this;
    }

    /**
     * Get dateinameAlt
     *
     * @return string
     */
    public function getDateinameAlt()
    {
        return $this->dateinameAlt;
    }

    /**
     * Set dateiHash
     *
     * @param string $dateiHash
     *
     * @return Archivierung
     */
    public function setDateiHash($dateiHash)
    {
        $this->dateiHash = $dateiHash;

        return $this;
    }

    /**
     * Get dateiHash
     *
     * @return string
     */
    public function getDateiHash()
    {
        return $this->dateiHash;
    }

    /**
     * Set archivierungsArt
     *
     * @param string $archivierungsArt
     *
     * @return Archivierung
     */
    public function setArchivierungsArt($archivierungsArt)
    {
        $this->archivierungsArt = $archivierungsArt;

        return $this;
    }

    /**
     * Get archivierungsArt
     *
     * @return string
     */
    public function getArchivierungsArt()
    {
        return $this->archivierungsArt;
    }

    /**
     * Add info
     *
     * @param \AppBundle\Entity\Information $info
     *
     * @return Archivierung
     */
    public function addInfo(\AppBundle\Entity\Information $info)
    {
        $this->removeInfo($info);
        $this->infos[] = $info;

        return $this;
    }

    /**
     * Remove info
     *
     * @param \AppBundle\Entity\Information $info
     */
    public function removeInfo(\AppBundle\Entity\Information $info)
    {
        $this->infos->removeElement($info);
    }

    /**
     * Remove all infos
     * @param $infoName string -> Kategorie der Information. Beispiel: "Titel". --- Wenn $infoName leer, dann lösche alle Infos.
     */
    public function removeAllInfos($infoName)
    {
        $allInfos = $this->getInfos($infoName);

        foreach ($allInfos as $info) {
            $this->removeInfo($info);
        }
    }

    /**
     * get infos
     *
     * @param $infoName
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInfos($infoName)
    {
        if(!$infoName)
            return $this->infos;

        else{
            $return = array();

            foreach ($this->infos as $info) {
                if($info->getName() === $infoName)
                    $return[] = $info;
            }

            return $return;
        }
    }

    /**
     * Add jahre
     *
     * @param \AppBundle\Entity\Jahr $jahre
     *
     * @return Archivierung
     */
    public function addJahre(\AppBundle\Entity\Jahr $jahre)
    {
        $this->removeJahre($jahre);
        $this->jahre[] = $jahre;

        return $this;
    }

    /**
     * Remove jahre
     *
     * @param \AppBundle\Entity\Jahr $jahre
     */
    public function removeJahre(\AppBundle\Entity\Jahr $jahre)
    {
        $this->jahre->removeElement($jahre);
    }

    /**
     * Remove all jahre
     */
    public function removeAllJahre()
    {
        $allJahre = $this->getJahre();

        foreach ($allJahre as $jahr) {
            $this->removeJahre($jahr);
        }
    }

    /**
     * Get jahre
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJahre()
    {
        return $this->jahre;
    }
}
