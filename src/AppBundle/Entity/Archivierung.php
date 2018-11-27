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

}