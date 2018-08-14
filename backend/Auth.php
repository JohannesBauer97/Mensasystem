<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */

require_once "Connection.php";

class Auth
{
    private static $instance = null;
    private $db;

    private function __construct()
    {
        session_start();
        $this->db = new Connection();
    }

    /**
     * Gibt ein Auth Objekt zurück
     * Singleton Pattern
     * @return Auth|null
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Auth();
        }

        return self::$instance;
    }

    /**
     * Erstellt PHPSESSION mit Username & Rolle in _SESSION Variable
     * @param $username Login name in Datenbank
     * @param $password Decrypted Passwort
     * @return bool True bei erfolgreicher Authentifizierung
     */
    public function login($username, $password)
    {
        $username = $this->db->filter($username);
        $user = $this->db->Query("SELECT * FROM tbl_user WHERE login = '$username'");
        $pwdhash = hash("sha256",$password);

        if($user[0]["passwort"] == $pwdhash){
            $_SESSION['username'] = $user[0]["login"];
            $_SESSION['rolle'] = $user[0]["rolle"];
            return true;
        }
        return false;
    }

    /**
     * @return array|bool Array mit User; False wenn kein User eingeloggt ist
     */
    public function getUser(){
        if (isset($_SESSION["username"])){
            $ret = array("username" => $_SESSION["username"],
                         "rolle" => $_SESSION["rolle"]);
            return $ret;
        }else{
            return false;
        }
    }

    /**
     * Beendet die PHP Session
     */
    public function logout()
    {
        session_destroy();
    }

}