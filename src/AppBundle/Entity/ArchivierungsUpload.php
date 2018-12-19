<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;


class ArchivierungsUpload
{

    /**
     * @Assert\NotBlank(message="Bitte Laden Sie die Archivierungsliste als CSV-Datei hoch.")
     * @Assert\File(
     *     mimeTypes={ "application/csv", "text/csv", "application/vnd.ms-excel", "text/plain", "text/x-csv", "application/x-csv", "text/comma-separated-values", "text/x-comma-separated-values" },
     *     maxSize = "10M",
     *     mimeTypesMessage = "Bitte laden Sie eine valide CSV-Datei hoch.")
     */
    private $csv;

    public function getCsv()
    {
        return $this->csv;
    }

    public function setCsv($csv)
    {
        $this->csv = $csv;

        return $this;
    }



}
