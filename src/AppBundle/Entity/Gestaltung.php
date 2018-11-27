<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="gestaltung")
 */
class Gestaltung {

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
     * @ORM\ManyToOne(targetEntity="Ort", inversedBy="gestaltungen")
     * @ORM\JoinColumn(name="ort_id", referencedColumnName="id")
     */
    private $ort;

    /**
     * @ORM\ManyToMany(targetEntity="Erzeuger", inversedBy="gestaltungen")
     * @ORM\JoinTable(name="gestaltung_erzeuger")
     */
    private $erzeuger;

    /**
     * @ORM\ManyToMany(targetEntity="Archivierung", mappedBy="gestaltungen")
     */
    private $archivierungen;



}
