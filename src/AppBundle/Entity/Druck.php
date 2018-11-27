<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="druck")
 */
class Druck {

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
     * @ORM\ManyToOne(targetEntity="Ort", inversedBy="drucke")
     * @ORM\JoinColumn(name="ort_id", referencedColumnName="id")
     */
    private $ort;

    /**
     * @ORM\ManyToMany(targetEntity="Erzeuger", inversedBy="drucke")
     * @ORM\JoinTable(name="druck_erzeuger")
     */
    private $erzeuger;

    /**
     * @ORM\ManyToMany(targetEntity="Archivierung", mappedBy="drucke")
     */
    private $archivierungen;



}
