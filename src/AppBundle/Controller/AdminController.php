<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Archivierung;
use AppBundle\Entity\ArchivierungsUpload;
use AppBundle\Entity\Information;
use AppBundle\Entity\Jahr;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class AdminController extends Controller
{
    /**
     * @Route("/admin/", name="admin")
     */
    public function adminAction(Request $request)
    {
        /*
        $this   // SQL-DEBUGGER
            ->get('doctrine')
            ->getConnection()
            ->getConfiguration()
            ->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
        */

        $upload = new ArchivierungsUpload();

        $form = $this->createFormBuilder($upload)
            ->add('csv', FileType::class, array('label' => 'Archivierungsliste im CSV-Format:'))
            ->add('save', SubmitType::class, array('label' => 'Hochladen'))
            ->getForm();


        // Weil count() in php7 anders funktioniert als in php5.6, muss hier ein Error ignoriert werden. (Sehr dirty!)
        try {
            $form->handleRequest($request);
        }catch(\Exception $e){}


        if ($form->isSubmitted() && $form->isValid()) {  // wegen dem try-catch-Hack oben, wird dieses if leider immer TRUE!

            $fs = new Filesystem();
            $em = $this->getDoctrine()->getManager();
            $neueBilderOrdner = $this->getParameter('neue_bilder_ordner');
            $gespeicherteBilderOrdner = $this->getParameter('gespeicherte_bilder_ordner');

            $file = $upload->getCsv();
            $errorList = array();

            $data = $this->csv_to_array($file, "~");


            // Die Maximale Laufzeit des Scripts auf 3 min erhöhen
            set_time_limit(180);


            // Jede Zeile der Tabelle bearbeiten
            foreach($data as $rowCount => $row) {
                $zeilenNr = $rowCount + 2;
                $isEdit = false;
                $delete = "DELETE";

                $archivierungsID = trim($row["Archivierungs-ID"]);
                unset($row["Archivierungs-ID"]);

                //bestehende Archivierung bearbeiten
                if($archivierungsID) {
                    if( !is_numeric($archivierungsID) ) {
                        array_push(
                            $errorList,
                            "Zeile $zeilenNr: Bearbeiten der Archivierung nicht möglich. ArchivierungID "
                            . $archivierungsID . " ist keine Zahl! Zeile übersprungen!"
                        );

                        continue; // gesamte Zeile überspringen
                    }

                    $archivierung = $em->getRepository('AppBundle:Archivierung')->find($archivierungsID);

                    // Fehler wenn Archivierungs-ID zu keiner Archivierung führt
                    if($archivierung === null) {
                        array_push(
                            $errorList,
                            "Zeile $zeilenNr: Bearbeiten der Archivierung nicht möglich. Archivierung mit ID "
                            . $archivierungsID . " nicht gefunden! Zeile übersprungen!"
                        );

                        continue; // gesamte Zeile überspringen
                    }
                    $archivierung->setLetzteBearbeitung(new DateTime("now"));
                    $isEdit = true;
                }

                //oder neue archivierung erstellen
                else{
                    $archivierung = new Archivierung();
                    $archivierung->setArchivierungsArt("Grafik");
                }


                $dateiname = trim(utf8_encode($row["Dateiname"]));
                unset($row["Dateiname"]);

                // Fehler wenn Dateiname == DELETE
                if($dateiname === $delete) {
                    array_push(
                        $errorList,
                        "Zeile $zeilenNr: Pflichtfeld 'Dateiname' darf nicht durch $delete gelöscht werden, "
                        . "da es nie leer sein darf! Zeile übersprungen!"
                    );

                    $em->detach($archivierung);
                    continue; // gesamte Zeile überspringen
                }

                // Dateinamen für neue Archivierung
                if($isEdit === false) {

                    // Fehler wenn Dateiname nicht vorhanden
                    if(empty($dateiname)) {
                        array_push(
                            $errorList,
                            "Zeile $zeilenNr: Pflichtfeld 'Dateiname' ist leer! Zeile übersprungen!"
                        );

                        $em->detach($archivierung);
                        continue; // gesamte Zeile überspringen
                    }

                    // Fehler wenn Dateiname nicht einzigartig
                    $doppelgaenger = $em->getRepository('AppBundle:Archivierung')->findOneByDateiname($dateiname);
                    if($doppelgaenger !== null){
                        array_push(
                            $errorList,
                            "Zeile $zeilenNr: Dateiname '" . $dateiname
                            . "' ist bereits in der Datenbank enthalten! Zeile übersprungen!"
                        );

                        $em->detach($archivierung);
                        continue; // gesamte Zeile überspringen
                    }

                    $archivierung->setDateiname($dateiname);
                }

                // vorhandenen Dateinamen bearbeiten
                else{
                    // wenn was drin steht
                    if( !empty($dateiname) ) {

                        // Fehler wenn Dateiname nicht einzigartig
                        $doppelgaenger = $em->getRepository('AppBundle:Archivierung')->findOneByDateiname($dateiname);
                        if ($doppelgaenger !== null) {
                            array_push(
                                $errorList,
                                "Zeile $zeilenNr: Dateiname '" . $dateiname
                                . "' ist bereits in der Datenbank enthalten! Zeile übersprungen!"
                            );

                            $em->detach($archivierung);
                            continue; // gesamte Zeile überspringen
                        }

                        $archivierung->setDateiname($dateiname);
                    }
                }


                $dateinameAlt = trim(utf8_encode($row["Dateiname (alt)"]));
                unset($row["Dateiname (alt)"]);

                if($dateinameAlt) {
                    // DateinameAlt für neue Archivierung
                    if($isEdit === false) {
                        $archivierung->setDateinameAlt($dateinameAlt);
                    }
                    // vorhandenen DateinameAlt bearbeiten
                    else{
                        if($dateinameAlt === $delete) {
                            $archivierung->setDateinameAlt(null);
                        }
                        else{
                            $archivierung->setDateinameAlt($dateinameAlt);
                        }
                    }
                }


                $jahre = trim(utf8_encode($row["Jahr"]));
                unset($row["Jahr"]);

                if($jahre) {

                    // Bei Bearbeitung erst ALLE Jahre entfernen
                    if($isEdit) {
                        $archivierung->removeAllJahre();
                    }

                    // wenn nur gelöscht werden sollte
                    if($isEdit && $jahre === $delete) {
                        // fertig
                    }

                    // ansonsten noch neue jahre hinzufügen
                    else {
                        $minJahr = 0;
                        $maxJahr = 0;
                        $jahreArray = explode(";", $jahre);

                        // Fehler wenn Jahre in falschem Format angegeben
                        if (count($jahreArray) > 2) {
                            array_push(
                                $errorList,
                                "Zeile $zeilenNr: Jahr " . $jahre
                                . " hat das falsche Format! Erlaubt: ZAHL oder ZAHL-ZAHL. Zeile übersprungen!"
                            );

                            $em->detach($archivierung);
                            continue; // gesamte Zeile überspringen
                        }

                        // nur ein Jahr
                        if (count($jahreArray) === 1) {
                            $minJahr = trim($jahreArray[0]);
                            $maxJahr = $minJahr;
                        }
                        // mehrere Jahre
                        elseif (count($jahreArray) === 2) {
                            $minJahr = trim($jahreArray[0]);
                            $maxJahr = trim($jahreArray[1]);
                        }

                        // min und max tauschen falls falschherum
                        if ($minJahr > $maxJahr) {
                            $temp = $maxJahr;
                            $maxJahr = $minJahr;
                            $minJahr = $temp;
                        }

                        // Fehler wenn eines der Jahre keine Zahl ist
                        if (!is_numeric($minJahr) || !is_numeric($maxJahr)) {
                            array_push(
                                $errorList,
                                "Zeile $zeilenNr: Jahr " . $jahre
                                . " enthält eine ungültiige Zahl! Erlaubt sind nur glatte Jahreszahlen. Zeile übersprungen!"
                            );

                            $em->detach($archivierung);
                            continue; // gesamte Zeile überspringen
                        }

                        for ($i = $minJahr; $i <= $maxJahr; $i++) {
                            $jahrObjekt = $em->getRepository('AppBundle:Jahr')->findOneByWert($i);
                            if ($jahrObjekt === null) {
                                $jahrObjekt = new Jahr(intval($i));
                            }
                            $archivierung->addJahre($jahrObjekt);
                        }
                    }
                }

                // Alle weiteren Attribute werden in der Entity "Information" gespeichert:
                foreach($row as $key => $values) {
                    $keyString = trim(utf8_encode($key));
                    $valuesString = trim(utf8_encode($values));

                    if($keyString && $valuesString) {

                        // Bei Bearbeitung erst ALLE Infos dieser Info-Kategorie entfernen
                        if($isEdit) {
                            $archivierung->removeAllInfos($keyString);
                        }

                        // wenn nur gelöscht werden sollte
                        if($isEdit && $valuesString === $delete) {
                            // fertig
                        }

                        // ansonsten noch neue Infos hinzufügen
                        else {
                            $valuesArray = explode(";", $valuesString);

                            foreach ($valuesArray as $value) {
                                $value = trim($value);

                                if ($value) {
                                    $info = $em->getRepository('AppBundle:Information')->findOneBy(array(
                                        'name' => $keyString,
                                        'wert' => $value)
                                    );

                                    if ($info === null) {
                                        $info = new Information();
                                        $info->setName($keyString);
                                        $info->setWert($value);
                                    }
                                    $archivierung->addInfo($info);
                                }
                            }
                        }
                    }
                }

                // wenn im Dateinamen was drin steht, neue Bilder speichern:
                if(!empty($dateiname)) {

                    $finder = new Finder();
                    $finder->files()->in($neueBilderOrdner)->name($dateiname);
                    $anzahlBilder = $finder->count();

                    // Fehler wenn nicht alle drei neuen Bilder gefunden werden können
                    if ($anzahlBilder !== 3) {
                        array_push(
                            $errorList,
                            "Zeile $zeilenNr: Es können nicht alle 3 passenden Bilder zu " . $dateiname
                            . " in den Unterverzeichnissen vom Ordner 'neue_bilder' gefuden werden. Zeile übersprungen!"
                        );

                        $em->detach($archivierung);
                        continue; // gesamte Zeile überspringen
                    }

                    // Wenn Bearbeitung, dann erst alle alten Bilder der Archivierung löschen
                    if ($isEdit) {
                        $dateiHashAlt = $archivierung->getDateiHash();
                        $ersteZweiBuchstabenAlt = substr($dateiHashAlt, 0, 2);
                        $hashPfadAlt = $gespeicherteBilderOrdner . "/" . $ersteZweiBuchstabenAlt;
                        $hashRestAlt = substr($dateiHashAlt, 2);

                        $pfadKomplettAlt = $hashPfadAlt . "/" . $hashRestAlt;
                        try {
                            $fs->remove(array($pfadKomplettAlt . "_g.png", $pfadKomplettAlt . "_m.png", $pfadKomplettAlt . "_k.png"));
                        } catch (IOExceptionInterface $exception) {
                            array_push(
                                $errorList,
                                "Zeile $zeilenNr: Es ist ein unerwarteter Fehler beim Bearbeiten der Archivierung mit dem Dateinamen " . $dateiname
                                . " aufgetreten: Das Löschen der alten Bilddateien ist fehlgeschlagen. Zeile übersprungen! --- " . $exception->getPath()
                            );

                            $em->detach($archivierung);
                            continue; // gesamte Zeile überspringen
                        }
                    }

                    // Neue Bilder speichern
                    $dateiHash = md5($dateiname);
                    $ersteZweiBuchstaben = substr($dateiHash, 0, 2);
                    $hashPfad = $gespeicherteBilderOrdner . "/" . $ersteZweiBuchstaben;
                    $hashRest = substr($dateiHash, 2);

                    foreach ($finder as $bild) {

                        if ($fs->exists($hashPfad) === false) {
                            try {
                                $fs->mkdir($hashPfad);
                            } catch (IOExceptionInterface $exception) {
                                array_push(
                                    $errorList,
                                    "Zeile $zeilenNr: Es ist ein unerwarteter Fehler bei Erstellen des Ordners " . $hashPfad
                                    . " aufgetreten. Zeile übersprungen! --- " . $exception->getPath()
                                );

                                $em->detach($archivierung);
                                continue 2; // gesamte Zeile überspringen
                            }
                        }

                        $bildGroesse = $bild->getRelativePath();
                        $bildPfad = $bild->getRealPath();
                        $groesseEndung = "_FEHLER";

                        if ($bildGroesse === "Archiv_gross") {
                            $groesseEndung = "_g";
                        } elseif ($bildGroesse === "Archiv_mittel") {
                            $groesseEndung = "_m";
                        } elseif ($bildGroesse === "Archiv_klein") {
                            $groesseEndung = "_k";
                        }

                        try {
                            $fs->copy($bildPfad, $hashPfad . "/" . $hashRest . $groesseEndung . ".png", true);
                        } catch (IOExceptionInterface $exception) {
                            array_push(
                                $errorList,
                                "Zeile $zeilenNr: Es ist ein unerwarteter Fehler bei Erstellen der Datei "
                                . $bildPfad, $hashPfad . "/" . $hashRest . $groesseEndung . ".png"
                                . " aufgetreten. Zeile übersprungen! --- " . $exception->getPath()
                            );

                            $em->detach($archivierung);
                            continue 2; // gesamte Zeile überspringen
                        }
                    }

                    $archivierung->setDateiHash($dateiHash);
                }

                // Archivierung speichern
                $em->persist($archivierung);
                $em->flush();
            }

            return $this->uploadForward($request, $errorList);
        }

        return $this->render('admin/admin.html.twig', array(
            'form' => $form->createView(),
        ));
    }



    /**
     * generiert View nach hochladen einer csv-datei.
     */
    public function uploadForward(Request $request, $errorList)
    {
        if(empty($errorList)) {
            $errorList = array("Es gab keine Fehler. Alles wurde erfolgreich hochgeladen bzw. bearbeitet!");
        }
        return $this->render('admin/upload_result.html.twig', array("errorList" => $errorList));
    }



    /**
     * Thanks to jaywilliams: http://gist.github.com/385876
     */
    private function csv_to_array($filename='', $delimiter=',')
    {
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 10000, $delimiter)) !== FALSE)
            {
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

}
