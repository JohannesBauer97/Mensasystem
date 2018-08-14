<?php
/**
 * Created by PhpStorm.
 * User: Natalia Ortega
 */

class CatererUbersichtUmsatz implements IModule
{
    private $tmp;
    private $monat;
    private $jahr;
    /**
     * @var Connection
     */
    private $db;

    /**
     * Gibt den HTML Code für die Umsatzübersicht zurück
     * @return mixed|string
     */
    public function render(){
        JSController::getInstance()->loadJSFile("caterer.ubersicht.umsatze.js");
        $this->db = new Connection();

        if (!isset($_GET["year"])) {
            $_GET["year"] = date("Y");
            $this->jahr = date("Y");
        } else {
            $this->jahr = $_GET["year"];
        }

        if (!isset($_GET["month"])) {
            $_GET["month"] = date("m");
            $this->monat = date("m");
        } else {
            $this->monat = $_GET["month"];
        }

        $this->tmp = new TemplateSystem();
        $this->tmp->load("content/caterer.ubersicht/Umsatze.html");
        $this->tmp->setVar("MonatLinks", $this->getMonatLinks());
        $this->tmp->setVar("TitleDate", $this->getTitleDate());
        $this->tmp->setVar("Umsatze", $this->getMonatsUmsatz());
        $sumB = $this->getSumBuchungen();
        $sumS = $this->getSumStorno();
        $gewinn = $this->getEinnahmen($sumB, $sumS);
        $this->tmp->setVar("SumB", number_format($sumB, 2, ",", ".") . "€");
        $this->tmp->setVar("SumS", number_format($sumS, 2, ",", ".") . "€");
        $this->tmp->setVar("Gewinn", number_format($gewinn, 2, ",", ".") . "€");
        return $this->tmp->Template;
    }

    /**
     * Gibt das Datum für den Titel zurück
     * @return false|string HTML Code
     */
    private function getTitleDate()
    {
        return date("m/Y", strtotime($this->jahr . "-" . $this->monat . "-01"));
    }

    /**
     * Gibt die Linkliste für die Monatsauswahl zurück
     * @return string HTML Code
     */
    private function getMonatLinks()
    {
        $output = "";
        for ($i = -3; $i <= 3; $i++) {
            $j = $i > 0 ? "+" . $i : $i;
            $anzeigeDatum = date("m/Y", strtotime("$j month"));
            $monat = date("m", strtotime("$j month"));
            $jahr = date("Y", strtotime("$j month"));
            $output .= "<a class=\"dropdown-item\" href=\"?page=ubersicht&overview=umsatze&month=$monat&year=$jahr\">$anzeigeDatum</a>";
        }
        return $output;
    }

    /**
     * Gibt den Umsatz des ausgewählten Monats zurück
     * @return string|void HTML Code
     */
    private function getMonatsUmsatz()
    {
        $firstDayOfMonth = date("Y-m-01 00:00:00", strtotime($this->monat . "/01/" . $this->jahr));
        $lastDayOfMonth = date("Y-m-t 23:59:59", strtotime($this->monat . "/01/" . $this->jahr));
        $sql = "SELECT 
                    tbl_umsatz.id,
                    tbl_umsatz.timestamp,
                    tbl_umsatz.price,
                    tbl_artikel.title,
                    tbl_umsatzArt.title AS 'umsatzArt',
                    tbl_user.login
                FROM
                    tbl_umsatz
                        LEFT JOIN
                    tbl_umsatzArt ON tbl_umsatz.umsatzArt = tbl_umsatzArt.id
                        LEFT JOIN
                    tbl_artikel ON tbl_umsatz.artikel = tbl_artikel.id
                        LEFT JOIN
                    tbl_user ON tbl_umsatz.user = tbl_user.id
                WHERE
                    tbl_umsatz.timestamp >= '$firstDayOfMonth' AND tbl_umsatz.timestamp <= '$lastDayOfMonth'";
        $result = $this->db->Query($sql);
        if ($result == null) {
            return;
        }

        $output = "";
        foreach ($result as $umsatz) {
            $preis = number_format($umsatz["price"], "2", ",", ".") . "€";
            $date = date("d.m.Y", strtotime($umsatz["timestamp"]));

            $output .= "<tr>
                        <td>{$umsatz["id"]}</td>
                        <td>$date</td>
                        <td>{$umsatz["login"]}</td>
                        <td>{$umsatz["umsatzArt"]}</td>
                        <td>{$umsatz["title"]}</td>
                        <td>$preis</td>
                    </tr>";
        }

        return $output;
    }

    /**
     * Gibt die Summe aller Buchungen zurück
     * @return float|string
     */
    private function getSumBuchungen()
    {
        $firstDayOfMonth = date("Y-m-01 00:00:00", strtotime($this->monat . "/01/" . $this->jahr));
        $lastDayOfMonth = date("Y-m-t 23:59:59", strtotime($this->monat . "/01/" . $this->jahr));
        $sql = "SELECT
                    SUM(tbl_umsatz.price) as 'sumBuchungen'
                FROM
                    tbl_umsatz
                WHERE
                    tbl_umsatz.umsatzArt = 1
                AND
                    tbl_umsatz.timestamp >= '$firstDayOfMonth' AND tbl_umsatz.timestamp <= '$lastDayOfMonth'";
        $result = $this->db->Query($sql);
        if ($result == null) {
            return "-";
        }

        return doubleval($result[0]["sumBuchungen"]);
    }

    /**
     * Gibt die Summe aller Stornierungen zurück
     * @return float|string
     */
    private function getSumStorno()
    {
        $firstDayOfMonth = date("Y-m-01 00:00:00", strtotime($this->monat . "/01/" . $this->jahr));
        $lastDayOfMonth = date("Y-m-t 23:59:59", strtotime($this->monat . "/01/" . $this->jahr));
        $sql = "SELECT
                    SUM(tbl_umsatz.price) as 'sumStorno'
                FROM
                    tbl_umsatz
                WHERE
                    tbl_umsatz.umsatzArt = 2
                AND
                    tbl_umsatz.timestamp >= '$firstDayOfMonth' AND tbl_umsatz.timestamp <= '$lastDayOfMonth'";
        $result = $this->db->Query($sql);
        if ($result == null) {
            return "-";
        }

        return doubleval($result[0]["sumStorno"]);
    }

    /**
     * Gibt die Einnahmen zurück
     * @param $buchungen Summe aller Buchungen
     * @param $storno Summe aller Stornos
     * @return float
     */
    private function getEinnahmen($buchungen, $storno)
    {
        return doubleval($buchungen - $storno);
    }
}