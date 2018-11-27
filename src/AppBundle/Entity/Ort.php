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
     * @ORM\Column(type="string")
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


}
