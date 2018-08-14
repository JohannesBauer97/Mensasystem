<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */

/**
 * Class CatererKunden
 * Verwaltung aller Kunden aus der Caterer Ansicht
 */
class CatererKunden implements IModule
{
    /**
     * Template der Kundenansicht des Caterers
     * @var TemplateSystem
     */
    private $tmp;
    /**
     * Datenbankverbindung
     * @var Connection
     */
    private $db;

    /**
     * Erstellt die Kundenverwaltung in der Catereransicht
     * @return string
     */
    public function render(){
        $this->tmp = new TemplateSystem();
        $this->db = new Connection();
        $this->tmp->load("content/caterer.kunden/Kunden.html");

        $this->tmp->setVar("KundenTableData",$this->createKundenTable());
        JSController::getInstance()->loadJSFile("caterer.kunden.js");
        return $this->tmp->Template;
    }

    /**
     * Generiert eine HTML Tabelle mit allen Kunden
     * @return string|void HTML Table
     */
    public static function createKundenTable(){
        $sql = "SELECT id, login, vorname, nachname, kontostand FROM mensasystem.tbl_user WHERE rolle = 2 AND active = 1;";
        $db = new Connection();
        $result = $db->Query($sql);

        if($result == null){
            return;
        }

        $output = "";

        foreach ($result as $kunde){
            $kontostand = number_format(doubleval($kunde['kontostand']),2,",",".");
            $output .= "<tr>";

            $output .= "<td>{$kunde['id']}</td>";
            $output .= "<td>{$kunde['login']}</td>";
            $output .= "<td>{$kunde['vorname']}</td>";
            $output .= "<td>{$kunde['nachname']}</td>";
            $output .= "<td>{$kontostand}€</td>";
            $output .= "<td>
                            <button onclick='kundePassReset({$kunde['id']})' type=\"button\" class=\"btn btn-light\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Passwort zurücksetzen\"><i class=\"material-icons\">security</i></button>
                            <button onclick='openKontoModal({$kunde['id']},true)' type=\"button\" class=\"btn btn-light\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Kontostand erhöhen\"><i class=\"material-icons\">arrow_upward</i></button>
                            <button onclick='openKontoModal({$kunde['id']},false)' type=\"button\" class=\"btn btn-light\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Kontostand verringern\"><i class=\"material-icons\">arrow_downward</i></button>
                            <button onclick='openStammdatenModal({$kunde['id']})' type=\"button\" class=\"btn btn-light\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Stammdaten bearbeiten\"><i class=\"material-icons\">mode_edit</i></button>
                            <button onclick='kundeLoeschen({$kunde['id']})' type=\"button\" class=\"btn btn-light\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Account löschen\"><i class=\"material-icons\">delete</i></button>
                        </td>";

            $output .= "</tr>";
        }


        return $output;
    }

    /**
     * Entfernt (deaktiviert) einen Kunden in der Datenbank
     * @param $kundenID KundenID
     * @return bool|string True bei erfolgreicher Deaktivierung
     */
    public static function deleteKunde($kundenID){

        $kundenID = intval($kundenID);

        if (!is_int($kundenID)){
            return " falsche Kunden ID!";
        }

        $db = new Connection();

        $filteredID = $db->filter($kundenID,false);

        $sql = "UPDATE tbl_user SET active = 0 WHERE id = $filteredID;";
        $result = $db->Query($sql);

        if($result){
            return true;
        }
        return false;
    }

    /**
     * Fügt einen Kunden der Datenbank hinzu
     * @param $vorname Vorname
     * @param $nachname Nachname
     * @param $accountname Accountname (Login Name)
     * @param $kontostand Startbetrag des Kontostands
     * @return array|string Array mit Passwort & Accountname oder Error Meldung
     */
    public static function addKunde($vorname, $nachname, $accountname, $kontostand){
        if(empty($vorname) || empty($nachname) || empty($accountname) || intval($kontostand) < 0){
            return json_encode(array("error" => "Ungültige Eingabe"));;
        }

        $db = new Connection();
        $vorname = $db->filter($vorname);
        $nachname = $db->filter($nachname);
        $accountname = $db->filter($accountname);
        $kontostand = intval($kontostand);
        $password_decrypted = self::generateRandomString(8);
        $password_encrypted = hash('sha256',$password_decrypted);

        $sql = "INSERT INTO `tbl_user`(`login`, `vorname`, `nachname`, `rolle`, `passwort`, `kontostand`) VALUES ('$accountname','$vorname','$nachname',2,'$password_encrypted',$kontostand);";
        $result = $db->Query($sql);
        if($result){
            return json_encode(array("password" => $password_decrypted, "accountname" => $accountname));
        }else{
            return json_encode(array("error" => $db->getLastError()));
        }
        return $result;
    }

    /**
     * Generiert ein zufälligen String 0-9 a-Z
     * @param int $length Länge des Strings
     * @return string Zufällig generierter String
     */
    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Generiert Kontostand Edit Modal
     * @param $kundenID KundenID
     * @param $aufladen True aufladen, False entladen
     * @return string|void HTML Code
     */
    public static function getkontoModal($kundenID, $aufladen){
        $kundenID = intval($kundenID);
        $aufladen = filter_var($aufladen, FILTER_VALIDATE_BOOLEAN);
        if(!is_bool($aufladen)){
            return "2. Parameter kein Bool!";
        }
        if(!isset($kundenID) || empty($kundenID) || !is_integer(intval($kundenID))){
            return "$kundenID Falsche KundenID!";
        }

        $kontoAction = null;
        $kontoAction1 = null;
        $aufladen ? $kontoAction = "aufladen" : $kontoAction = "entladen";
        $aufladen ? $kontoAction1 = "Aufladen" : $kontoAction1 = "Entladen";

        $db = new Connection();
        $kunde = $db->Query("SELECT * FROM `tbl_user` WHERE `id` = $kundenID AND `active` = 1 LIMIT 1")[0];

        $actionFunction = null;
        $aufladen ? $actionFunction = "kundeAufladen($kundenID);" : $actionFunction = "kundeEntladen($kundenID);";

        $tmp = new TemplateSystem();
        $tmp->load("content/caterer.kunden/KontoModal.html");
        $tmp->setVar("KontoAction", $kontoAction);
        $tmp->setVar("KontoAction1", $kontoAction1);
        $tmp->setVar("Accountname", $kunde["login"]);
        $tmp->setVar("actionFunction", $actionFunction);
        $tmp->setVar("Kontostand", number_format(doubleval($kunde["kontostand"]),2,',','.'));
        return $tmp->show();
    }

    /**
     * Kontostand eines Kunden erhöhen
     * @param $kundenID KundenID
     * @param $betrag Betrag
     * @return array|string Error String wenn Eingabe fehlerhaft oder Array mit Ergebnis
     */
    public static function kundeAufladen($kundenID, $betrag){
        $kundenID = intval($kundenID);
        $betrag = doubleval(str_replace(",",".", $betrag));

        if($betrag <= 0 ||$kundenID <= 0){
            return "Error: Betrag/KundenID muss über 0 sein!";
        }

        $db = new Connection();
        $success = $db->Query("UPDATE `tbl_user` SET `kontostand`=ROUND(`kontostand`+$betrag, 2) WHERE `active`=1 AND `id`=$kundenID");
        $kontostand = $db->Query("SELECT `kontostand` FROM `tbl_user` WHERE `id` = $kundenID AND `active` = 1 LIMIT 1");
        return json_encode(array($success,$kontostand[0]["kontostand"]));
    }

    /**
     * Kontostand eines Kunden verringern
     * @param $kundenID KundenID
     * @param $betrag Betrag
     * @return string|array Error String wenn Eingabe fehlerhaft oder Array mit Ergebnis
     */
    public static function kundeEntladen($kundenID, $betrag){
        $kundenID = intval($kundenID);
        $betrag = doubleval(str_replace(",",".", $betrag));

        if($betrag <= 0 ||$kundenID <= 0){
            return "Error: Betrag/KundenID muss über 0 sein!";
        }

        $db = new Connection();
        $kontostand = $db->Query("SELECT `kontostand` FROM `tbl_user` WHERE `id` = $kundenID AND `active` = 1 LIMIT 1");

        if(($kontostand[0]["kontostand"]-$betrag) < 0){
            return "Error: Der Kontostand darf nicht negativ werden!";
        }

        $success = $db->Query("UPDATE `tbl_user` SET `kontostand`=ROUND(`kontostand`-$betrag,2) WHERE `active`=1 AND `id`=$kundenID");
        $kontostand = $db->Query("SELECT `kontostand` FROM `tbl_user` WHERE `id` = $kundenID AND `active` = 1 LIMIT 1");

        return json_encode(array($success,$kontostand[0]["kontostand"]));
    }

    /**
     * Liefert HTML für Passwort Reset Modal
     * @param $kundenID KundenID
     * @return string|void Fehlermeldung oder Modal HTML Code
     */
    public static function getPassResetModal($kundenID){
        if(!isset($kundenID) || empty($kundenID) || !is_integer(intval($kundenID))){
            return "$kundenID Falsche KundenID!";
        }
        $kundenID = intval($kundenID);
        $db = new Connection();
        $kunde = $db->Query("SELECT * FROM `tbl_user` WHERE `id` = $kundenID AND `active` = 1 LIMIT 1");
        if(is_bool($kunde) || count($kunde) <= 0){
            return "Kunde nicht gefunden!";
        }


        $tmp = new TemplateSystem();
        $tmp->load("content/caterer.kunden/PassResetModal.html");
        $tmp->setVar("Accountname", $kunde[0]["login"]);
        $tmp->setVar("resetFunction", "resetPass({$kunde[0]["id"]})");
        return $tmp->show();
    }

    /**
     * Setzt ein neues generiertes Passwort für einen Kunden
     * @param $kundenID KundenID
     * @return null|string Gibt das neue Passwort oder NULL zurück
     */
    public static function passReset($kundenID){
        $kundenID = intval($kundenID);
        if(!isset($kundenID) || empty($kundenID) || !is_integer(intval($kundenID))){
            return "$kundenID Falsche KundenID!";
        }

        $newPW = self::generateRandomString();
        $pwdhash = hash("sha256",$newPW);
        $db = new Connection();
        $success = $db->Query("UPDATE `tbl_user` SET `passwort`='$pwdhash' WHERE `id`=$kundenID AND `active`=1");
        if($success){
            return $newPW;
        }else{
            return null;
        }
    }

    /**
     * Erstellt Stammdaten Modal HTML
     * @param $kundenID
     * @return string|void HTML
     */
    public static function getStammdatenModal($kundenID){
        $kundenID = intval($kundenID);
        if($kundenID <= 0){
            return "Error: Falsche KundenID! ($kundenID)";
        }

        $db = new Connection();
        $result = $db->Query("SELECT * FROM `tbl_user` WHERE `id`=$kundenID AND `active`=1");

        if(is_bool($result) || count($result) <= 0)
            return "Error: User konnte nicht gefunden werden!";

        $kunde = $result[0];

        $tmpl = new TemplateSystem();
        $tmpl->load("content/caterer.kunden/StammdatenModal.html");
        $tmpl->setVar("ID",$kunde["id"]);
        $tmpl->setVar("Accountname",$kunde["login"]);
        $tmpl->setVar("Vorname",$kunde["vorname"]);
        $tmpl->setVar("Nachname",$kunde["nachname"]);
        return $tmpl->show();
    }

    /**
     * Ändert die Stammdaten eines Kunden
     * @param $kundenID KundenID
     * @param $vorname neuer Vorname
     * @param $nachname neuer Nachname
     * @return string|bool Error string oder Ergebnis bool
     */
    public static function saveStammdaten($kundenID, $vorname, $nachname){
        $kundenID = intval($kundenID);
        if(!isset($vorname) || !isset($nachname) || empty($vorname) || empty($nachname)){
            return "Error: Vor-/Nachname dürfen nicht leer sein!";
        }

        if($kundenID <= 0)
            return "Error: Fehlerhafte KundenID! ($kundenID)";


        $db = new Connection();
        $vorname = $db->filter($vorname,true);
        $nachname = $db->filter($nachname, true);
        $success = $db->Query("UPDATE `tbl_user` SET `vorname`='$vorname',`nachname`='$nachname' WHERE `id`=$kundenID AND `active`=1");
        return $success;
    }
}