<?php
/**
 * Created by PhpStorm.
 * User: Alexander Ullmann
 */

class CatererUbersichtBestellungen implements IModule
{
    private $tmp;
    /**
     * @var Connection
     */
    private $db;

    /**
     * Gibt den HTML Code für die Bestellübersicht zurück
     * @return mixed|string
     */
    public function render(){
        JSController::getInstance()->loadJSFile("caterer.ubersicht.bestellungen.js");
        $this->db = new Connection();
        $this->tmp = new TemplateSystem();
        $this->tmp->load("content/caterer.ubersicht/Bestellungen.html");
        $this->tmp->setVar("Bestellungen", $this->getBestellungen());

        return $this->tmp->Template;
    }

    /**
     * Gibt die TabellenRows der Bestellungen für den aktuellen Tag zurück
     * @return string|void HTML Code
     */
    private function getBestellungen()
    {
        $heute = date("Y-m-d 00:00:00");
        $sql = "SELECT 
                    tbl_umsatz.id,
                    tbl_umsatz.price,
                    tbl_user.login,
                    tbl_artikel.title
                FROM
                    tbl_umsatz
                LEFT JOIN tbl_artikel ON tbl_artikel.id = tbl_umsatz.artikel
                LEFT JOIN tbl_user ON tbl_user.id = tbl_umsatz.user
                LEFT JOIN tbl_speiseplan ON tbl_umsatz.speiseplan = tbl_speiseplan.id
                WHERE
                    tbl_umsatz.umsatzArt = 1
                    AND
                    tbl_umsatz.active = 1
                    AND
                    tbl_speiseplan.timestamp = '$heute'";

        $result = $this->db->Query($sql);
        if ($result == null)
            return;

        $output = "";
        foreach ($result as $buchung) {
            $preis = number_format($buchung["price"], 2, ",", ".") . "€";
            $output .= "<tr>
                        <td>{$buchung["id"]}</td>
                        <td>{$buchung["login"]}</td>
                        <td>{$buchung["title"]}</td>
                        <td>$preis</td>
                    </tr>";
        }

        return $output;
    }
}