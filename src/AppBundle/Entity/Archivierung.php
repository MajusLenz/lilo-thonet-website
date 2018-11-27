<?php

namespace AppBundle\Entity;

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
        $this->kategorien = new ArrayCollection();
        $this->jahre = new ArrayCollection();
        $this->hinweise = new ArrayCollection();
        $this->schriften = new ArrayCollection();
        $this->farben = new ArrayCollection();
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
     * @ORM\Column(type="string", length=100)
     */
    private $dateiname;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $titel;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $dateinameAlt;

    /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=false)
     */
    private $dateiHash;


    ///// ASSOZIATIONEN:

    /**
     * @ORM\ManyToOne(targetEntity="Breite", inversedBy="archivierungen")
     * @ORM\JoinColumn(name="breite_id", referencedColumnName="id")
     */
    private $breite;

    /**
     * @ORM\ManyToOne(targetEntity="Hoehe", inversedBy="archivierungen")
     * @ORM\JoinColumn(name="hoehe_id", referencedColumnName="id")
     */
    private $hoehe;

    /**
     * @ORM\ManyToOne(targetEntity="Herkunftsarchiv", inversedBy="archivierungen")
     * @ORM\JoinColumn(name="herkunftsarchiv_id", referencedColumnName="id")
     */
    private $herkunftsarchiv;

    /**
     * @ORM\ManyToOne(targetEntity="Firma", inversedBy="archivierungen")
     * @ORM\JoinColumn(name="firma_id", referencedColumnName="id")
     */
    private $firma;

    /**
     * @ORM\ManyToOne(targetEntity="Ort", inversedBy="archivierungen")
     * @ORM\JoinColumn(name="ort_id", referencedColumnName="id")
     */
    private $entstehungsort;


    /**
     * @ORM\ManyToMany(targetEntity="Kategorie", inversedBy="archivierungen")
     * @ORM\JoinTable(name="archivierung_kategorie")
     */
    private $kategorien;

    /**
     * @ORM\ManyToMany(targetEntity="Farbe", inversedBy="archivierungen")
     * @ORM\JoinTable(name="archivierung_farbe")
     */
    private $farben;

    /**
     * @ORM\ManyToMany(targetEntity="Schrift", inversedBy="archivierungen")
     * @ORM\JoinTable(name="archivierung_schrift")
     */
    private $schriften;

    /**
     * @ORM\ManyToMany(targetEntity="Hinweis", inversedBy="archivierungen")
     * @ORM\JoinTable(name="archivierung_hinweis")
     */
    private $hinweise;

    /**
     * @ORM\ManyToMany(targetEntity="Jahr", inversedBy="archivierungen")
     * @ORM\JoinTable(name="archivierung_jahr")
     */
    private $jahre;

    /**
     * @ORM\ManyToMany(targetEntity="Gestaltung", inversedBy="archivierungen")
     * @ORM\JoinTable(name="archivierung_gestaltung")
     */
    private $gestaltungen;

    /**
     * @ORM\ManyToMany(targetEntity="Druck", inversedBy="archivierungen")
     * @ORM\JoinTable(name="archivierung_druck")
     */
    private $drucke;

    /**
     * @ORM\ManyToMany(targetEntity="Fotografie", inversedBy="archivierungen")
     * @ORM\JoinTable(name="archivierung_fotografie")
     */
    private $fotografien;


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
     * Set titel
     *
     * @param string $titel
     *
     * @return Archivierung
     */
    public function setTitel($titel)
    {
        $this->titel = $titel;

        return $this;
    }

    /**
     * Get titel
     *
     * @return string
     */
    public function getTitel()
    {
        return $this->titel;
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
     * Set breite
     *
     * @param \AppBundle\Entity\Breite $breite
     *
     * @return Archivierung
     */
    public function setBreite(\AppBundle\Entity\Breite $breite = null)
    {
        $this->breite = $breite;

        return $this;
    }

    /**
     * Get breite
     *
     * @return \AppBundle\Entity\Breite
     */
    public function getBreite()
    {
        return $this->breite;
    }

    /**
     * Set hoehe
     *
     * @param \AppBundle\Entity\Hoehe $hoehe
     *
     * @return Archivierung
     */
    public function setHoehe(\AppBundle\Entity\Hoehe $hoehe = null)
    {
        $this->hoehe = $hoehe;

        return $this;
    }

    /**
     * Get hoehe
     *
     * @return \AppBundle\Entity\Hoehe
     */
    public function getHoehe()
    {
        return $this->hoehe;
    }

    /**
     * Set herkunftsarchiv
     *
     * @param \AppBundle\Entity\Herkunftsarchiv $herkunftsarchiv
     *
     * @return Archivierung
     */
    public function setHerkunftsarchiv(\AppBundle\Entity\Herkunftsarchiv $herkunftsarchiv = null)
    {
        $this->herkunftsarchiv = $herkunftsarchiv;

        return $this;
    }

    /**
     * Get herkunftsarchiv
     *
     * @return \AppBundle\Entity\Herkunftsarchiv
     */
    public function getHerkunftsarchiv()
    {
        return $this->herkunftsarchiv;
    }

    /**
     * Set firma
     *
     * @param \AppBundle\Entity\Firma $firma
     *
     * @return Archivierung
     */
    public function setFirma(\AppBundle\Entity\Firma $firma = null)
    {
        $this->firma = $firma;

        return $this;
    }

    /**
     * Get firma
     *
     * @return \AppBundle\Entity\Firma
     */
    public function getFirma()
    {
        return $this->firma;
    }

    /**
     * Set entstehungsort
     *
     * @param \AppBundle\Entity\Ort $entstehungsort
     *
     * @return Archivierung
     */
    public function setEntstehungsort(\AppBundle\Entity\Ort $entstehungsort = null)
    {
        $this->entstehungsort = $entstehungsort;

        return $this;
    }

    /**
     * Get entstehungsort
     *
     * @return \AppBundle\Entity\Ort
     */
    public function getEntstehungsort()
    {
        return $this->entstehungsort;
    }

    /**
     * Add kategorien
     *
     * @param \AppBundle\Entity\Kategorie $kategorien
     *
     * @return Archivierung
     */
    public function addKategorien(\AppBundle\Entity\Kategorie $kategorien)
    {
        $this->kategorien[] = $kategorien;

        return $this;
    }

    /**
     * Remove kategorien
     *
     * @param \AppBundle\Entity\Kategorie $kategorien
     */
    public function removeKategorien(\AppBundle\Entity\Kategorie $kategorien)
    {
        $this->kategorien->removeElement($kategorien);
    }

    /**
     * Get kategorien
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getKategorien()
    {
        return $this->kategorien;
    }

    /**
     * Add farben
     *
     * @param \AppBundle\Entity\Farbe $farben
     *
     * @return Archivierung
     */
    public function addFarben(\AppBundle\Entity\Farbe $farben)
    {
        $this->farben[] = $farben;

        return $this;
    }

    /**
     * Remove farben
     *
     * @param \AppBundle\Entity\Farbe $farben
     */
    public function removeFarben(\AppBundle\Entity\Farbe $farben)
    {
        $this->farben->removeElement($farben);
    }

    /**
     * Get farben
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFarben()
    {
        return $this->farben;
    }

    /**
     * Add schriften
     *
     * @param \AppBundle\Entity\Schrift $schriften
     *
     * @return Archivierung
     */
    public function addSchriften(\AppBundle\Entity\Schrift $schriften)
    {
        $this->schriften[] = $schriften;

        return $this;
    }

    /**
     * Remove schriften
     *
     * @param \AppBundle\Entity\Schrift $schriften
     */
    public function removeSchriften(\AppBundle\Entity\Schrift $schriften)
    {
        $this->schriften->removeElement($schriften);
    }

    /**
     * Get schriften
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSchriften()
    {
        return $this->schriften;
    }

    /**
     * Add hinweise
     *
     * @param \AppBundle\Entity\Hinweis $hinweise
     *
     * @return Archivierung
     */
    public function addHinweise(\AppBundle\Entity\Hinweis $hinweise)
    {
        $this->hinweise[] = $hinweise;

        return $this;
    }

    /**
     * Remove hinweise
     *
     * @param \AppBundle\Entity\Hinweis $hinweise
     */
    public function removeHinweise(\AppBundle\Entity\Hinweis $hinweise)
    {
        $this->hinweise->removeElement($hinweise);
    }

    /**
     * Get hinweise
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHinweise()
    {
        return $this->hinweise;
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
     * Get jahre
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJahre()
    {
        return $this->jahre;
    }

    /**
     * Add gestaltungen
     *
     * @param \AppBundle\Entity\Gestaltung $gestaltungen
     *
     * @return Archivierung
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
     * @return Archivierung
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
     * @return Archivierung
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
