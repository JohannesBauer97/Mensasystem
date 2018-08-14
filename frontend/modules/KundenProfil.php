<?php
/**
 * Created by PhpStorm.
 * User: Natalia Ortega
 */

class KundenProfil implements IModule
{
    /**
     * @var TemplateSystem
     */
    private $tmp;
    /**
     * @var Connection
     */
    private $db;

    /**
     * Gibt den HTML Code für das Kunden Profil zurück
     * @return mixed|string
     */
    public function render(){
        JSController::getInstance()->loadJSFile("kunde.profil.js");
        $this->tmp = new TemplateSystem();
        $this->db = new Connection();
        $this->tmp->load("content/kunde.profil/Profil.html");
        $this->setUserData();
        $this->tmp->setVar("Umsatze", $this->getUmsatzTable());

        return $this->tmp->Template;
    }

    /**
     * Setzt im Template die in der Datenbank hinterlegten Profildaten
     */
    private function setUserData()
    {
        $auth = Auth::getInstance();
        $user = $auth->getUser()["username"];
        $result = $this->db->Query("SELECT * FROM `tbl_user` WHERE `login`='$user'");

        if ($result == null)
            return;

        $user = $result[0];
        $kontostand = number_format($user["kontostand"], "2", ",", ".") . "€";
        $this->tmp->setVar("Vorname", $user["vorname"]);
        $this->tmp->setVar("Name", $user["nachname"]);
        $this->tmp->setVar("Login", $user["login"]);
        $this->tmp->setVar("Kontostand", $kontostand);
    }

    /**
     * Gibt alle Umsätze des Users zurück
     * @return string HTML Code
     */
    private function getUmsatzTable()
    {
        $auth = Auth::getInstance()->getUser()["username"];
        $userID = $this->db->Query("SELECT `id` FROM `tbl_user` WHERE `login`='$auth'")[0]["id"];
        $umsatzSQL = "SELECT 
                        tbl_umsatz.id,
                        tbl_umsatz.price,
                        tbl_artikel.title,
                        tbl_umsatzArt.title AS 'umsatzArt',
                        tbl_speiseplan.timestamp
                    FROM
                        tbl_umsatz
                            LEFT JOIN
                        tbl_umsatzArt ON tbl_umsatz.umsatzArt = tbl_umsatzArt.id
                            LEFT JOIN
                        tbl_artikel ON tbl_umsatz.artikel = tbl_artikel.id
                            LEFT JOIN
                        tbl_speiseplan ON tbl_umsatz.speiseplan = tbl_speiseplan.id
                    WHERE
                        tbl_umsatz.user = $userID";
        $umsatze = $this->db->Query($umsatzSQL);

        if ($umsatze == null) {
            return "Du hast noch keine Buchungen zu verzeichnen!";
        }

        $output = "";

        foreach ($umsatze as $umsatz) {
            $date = date("d.m.Y", strtotime($umsatz["timestamp"]));
            $preis = number_format($umsatz["price"], 2, ",", ".") . "€";
            $output .= "<tr>
                        <td>{$umsatz["id"]}</td>
                        <td>$date</td>
                        <td>{$umsatz["umsatzArt"]}</td>
                        <td>{$umsatz["title"]}</td>
                        <td>$preis</td>
                    </tr>";
        }

        return $output;
    }

}