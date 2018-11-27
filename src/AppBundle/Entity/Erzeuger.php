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


}
