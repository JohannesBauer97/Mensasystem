<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */

class Login implements IModule
{
    private $tmp;
    private $auth;

    /**
     * Gibt den HTML Code für die Login Seite zurück
     * @return mixed|string
     */
    public function render()
    {
        $this->tmp = new TemplateSystem();
        $this->auth = Auth::getInstance();
        $this->tmp->load("content/Login.html");

        JSController::getInstance()->loadJSFile("login.js");
        return $this->tmp->Template;
    }

    /**
     * Wird mittels Ajax und den Login Werten aufgerufen
     * @param $username Login Name
     * @param $passwort Decrypted Passwort
     * @return bool True bei Erfolg
     */
    public static function ajaxLogin($username, $passwort){
        if(empty($username) || empty($passwort)){
            return false;
        }

        return Auth::getInstance()->login($username,$passwort);
    }

}