<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */

class CatererSpeiseplanArtikel implements IModule
{
    private $tmp;

    /**
     * Artikelverwaltung
     * @return mixed|string
     */
    public function render(){
        $this->tmp = new TemplateSystem();
        $this->tmp->load("content/caterer.speiseplan/Artikel.html");
        $this->tmp->setVar("ArtikelTableData", self::createArtikelTable());
        JSController::getInstance()->loadJSFile("caterer.speiseplan.artikel.js");
        return $this->tmp->Template;
    }

    /**
     * Erstellt den Table Body mit allen Artikeln
     * @return string
     */
    public static function createArtikelTable()
    {
        $db = new Connection();
        $artikel = $db->Query("SELECT * FROM `tbl_artikel` WHERE `active`=1");

        $output = "";
        foreach ($artikel as $Artikel) {

            $preis = number_format($Artikel["price"], 2, ",", ".");
            $angebot = "<input type='checkbox' disabled readonly >";
            if ($Artikel["dauerhaftesAngebot"]) {
                $angebot = "<input type='checkbox' checked disabled readonly>";
            }

            $output .= "<tr>";

            $output .= "<td>{$Artikel["id"]}</td>";
            $output .= "<td>{$Artikel["title"]}</td>";
            $output .= "<td>{$Artikel["description"]}</td>";
            $output .= "<td>{$preis}€</td>";
            $output .= "<td>$angebot</td>";
            $output .= "<td>
                            <button onclick=\"openArtikelEditModal({$Artikel['id']})\" type=\"button\" class=\"btn btn-light\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Bearbeiten\"><i class=\"material-icons\">mode_edit</i></button>
                            <button onclick=\"removeArtikel({$Artikel['id']})\" type=\"button\" class=\"btn btn-light\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Löschen\"><i class=\"material-icons\">delete</i></button>
                        </td>";

            $output .= "</tr>";
        }

        return $output;
    }

    /**
     * Fügt einen Artikel zu der Datenbank hinzu
     * @param $titel Titel
     * @param $descr Beschreibung
     * @param $preis Preis
     * @param $dauerhaftesAngebot Dauerhaft im Angebot
     * @return array|bool|null|string True bei Erfolg
     */
    public static function addArtikel($titel, $descr, $preis, $dauerhaftesAngebot)
    {
        if (empty($titel) || empty($preis)) {
            return "Error: Titel und Preis notwendig!";
        }


        $db = new Connection();
        $titel = $db->filter($titel, true);
        $descr = $db->filter($descr, true);
        $preis = doubleval(str_replace(",", ".", $preis));
        $dauerhaftesAngebot = boolval($dauerhaftesAngebot) == true ? 1 : 0;

        $sql = "INSERT INTO `tbl_artikel`(`title`, `description`, `price`, `dauerhaftesAngebot`) VALUES ('$titel','$descr',TRUNCATE($preis,2),$dauerhaftesAngebot);";

        $success = $db->Query($sql);
        if (!$success) {
            return "Datenbankfehler!";
        }
        return $success;
    }

    /**
     * Entfernt einen Artikel aus der Datenbank (active=0)
     * @param $artikelID ArtikelID
     * @return array|bool|null|string True bei Erfolg
     */
    public static function removeArtikel($artikelID)
    {
        $artikelID = intval($artikelID);
        if ($artikelID <= 0) {
            return "Error: Falsche Artikel ID!";
        }

        $db = new Connection();
        $success = $db->Query("UPDATE `tbl_artikel` SET `active`=0 WHERE `id`=$artikelID");
        return $success;
    }

    /**
     * Gibt den HTML Code für das Artikel Editieren Modal zurück
     * @param $artikelID ArtikelID
     * @return string|void HTMLCode Artikel Editieren Modal
     */
    public static function getArtikelEditModal($artikelID)
    {
        $artikelID = intval($artikelID);
        if ($artikelID <= 0) {
            return "Error: Falsche Artikel ID!";
        }

        $db = new Connection();
        $artikel = $db->Query("SELECT * FROM `tbl_artikel` WHERE `id`=$artikelID AND `active`=1 LIMIT 1")[0];
        $tmpl = new TemplateSystem();
        $tmpl->load("content/caterer.speiseplan/ArtikelEditModal.html");

        $checked = "";
        if ($artikel["dauerhaftesAngebot"] == 1) {
            $checked = "checked";
        }

        $tmpl->setVar("ID", $artikel["id"]);
        $tmpl->setVar("Titel", $artikel["title"]);
        $tmpl->setVar("Description", $artikel["description"]);
        $tmpl->setVar("Preis", number_format($artikel["price"], 2, ",", "."));
        $tmpl->setVar("Dauerhaft", $checked);

        return $tmpl->show();
    }

    /**
     * Ändert ein vorhandenen Artikel in der Datenbank
     * @param $artikelID ArtikelID
     * @param $title Titel
     * @param $descr Beschreibung
     * @param $preis Preis
     * @param $dauerhaft Dauerhaft im Angebot
     * @return int|string True bei Erfolg; String bei Fehler
     */
    public static function saveArtikel($artikelID, $title, $descr, $preis, $dauerhaft)
    {
        $artikelID = intval($artikelID);
        $preis = str_replace(",", ".", $preis);
        $preis = doubleval($preis);
        if ($artikelID <= 0) {
            return "Error: Falsche Artikel ID!";
        }
        if ($preis < 0) {
            return "Error: Der Preis darf nicht negativ sein!";
        }

        $db = new Connection();
        $title = $db->filter($title, true);
        $descr = $db->filter($descr, true);

        $dauerhaft = $dauerhaft == 1 ? 1 : 0;

        $sql = "UPDATE `tbl_artikel` SET `title`='$title',`description`='$descr',`price`=TRUNCATE($preis,2),`dauerhaftesAngebot`=$dauerhaft WHERE `id`=$artikelID AND `active`=1";
        $success = $db->Query($sql);
        return $success ? 1 : 0;
    }
}