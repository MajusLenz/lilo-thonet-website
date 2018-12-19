<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="archivierung")
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
        $this->referenzen = new ArrayCollection();
        $this->referenziertVon = new ArrayCollection();
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

    /**
     * @ORM\ManyToMany(targetEntity="Information", inversedBy="archivierungen", cascade={"persist"})
     * @ORM\JoinTable(name="archivierung_information")
     */
    private $infos;

    /**
     * @ORM\ManyToMany(targetEntity="Jahr", inversedBy="archivierungen", cascade={"persist"})
     * @ORM\JoinTable(name="archivierung_jahr")
     */
    private $jahre;


    ///// VERKNUEPFUNG AUF ANDERE ARCHIVIERUNGEN:

    /**
     * @ORM\ManyToMany(targetEntity="Archivierung", mappedBy="referenzen")
     */
    private $referenziertVon;

    /**
     * @ORM\ManyToMany(targetEntity="Archivierung", inversedBy="referenziertVon")
     * @ORM\JoinTable(name="referenz",
     *      joinColumns={@ORM\JoinColumn(name="von_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="auf_id", referencedColumnName="id")}
     *      )
     */
    private $referenzen;


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
        $allInfos = $this->getInfos();

        if(!$infoName)
            foreach ($allInfos as $info) {
                $this->removeInfo($info);
            }
        else
            foreach ($allInfos as $info) {
                if($info->getName() === $infoName)
                    $this->removeInfo($info);
            }
    }

    /**
     * Get infos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInfos()
    {
        return $this->infos;
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

    /**
     * Add referenziertVon
     *
     * @param \AppBundle\Entity\Archivierung $referenziertVon
     *
     * @return Archivierung
     */
    public function addReferenziertVon(\AppBundle\Entity\Archivierung $referenziertVon)
    {
        $this->referenziertVon[] = $referenziertVon;

        return $this;
    }

    /**
     * Remove referenziertVon
     *
     * @param \AppBundle\Entity\Archivierung $referenziertVon
     */
    public function removeReferenziertVon(\AppBundle\Entity\Archivierung $referenziertVon)
    {
        $this->referenziertVon->removeElement($referenziertVon);
    }

    /**
     * Get referenziertVon
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferenziertVon()
    {
        return $this->referenziertVon;
    }

    /**
     * Add referenzen
     *
     * @param \AppBundle\Entity\Archivierung $referenzen
     *
     * @return Archivierung
     */
    public function addReferenzen(\AppBundle\Entity\Archivierung $referenzen)
    {
        $this->removeReferenzen($referenzen);
        $this->referenzen[] = $referenzen;

        return $this;
    }

    /**
     * Remove referenzen
     *
     * @param \AppBundle\Entity\Archivierung $referenzen
     */
    public function removeReferenzen(\AppBundle\Entity\Archivierung $referenzen)
    {
        $this->referenzen->removeElement($referenzen);
    }

    /**
     * Get referenzen
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferenzen()
    {
        return $this->referenzen;
    }
}
