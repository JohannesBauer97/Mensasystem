<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */

class AjaxController
{
    /**
     * Klassen auf die nur der Caterer zugreifen darf
     * @var array mit Klassennamen
     */
    private $CatererClasses = array(
        "CatererKunden",
        "CatererMOTD",
        //"CatererSpeiseplan",
        "CatererSpeiseplanArtikel",
        "CatererSpeiseplanVerwalten",
        //"CatererUbersicht",
        "CatererUbersichtBestellungen",
        "CatererUbersichtUmsatz"
    );

    /**
     * Klassen auf die nur der Kunde zugreifen darf
     * @var array mit Klassennamen
     */
    private $KundenClasses = array(
        "KundenBestellen",
        "KundenProfil",
    );

    /**
     * Klassen auf die alle zugreifen dürfen
     * @var array mit Klassennamen
     */
    private $PublicClasses = array(
        "Home",
        "Login",
    );

    /**
     * Erstellt ein neues AjaxController Objekt
     * Verarbeitet POST Parameter
     * Führt Methode aus
     * Gibt Rückgabewerte der Methode zurück
     */
    public function __construct()
    {
        if(!isset($_POST["class"]) || !isset($_POST["method"])){
            http_response_code(400);
            exit;
        }

        //Aufgrund von XAMPP kann relativer Path nicht verwendet werden
        //Backend Klassen werden required, damit static functions diese verwenden können
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $AuthClassPath = dirname(dirname(__DIR__)) . "\\backend\\Auth.php";
            $InputClassPath = dirname(dirname(__DIR__)) . "\\backend\\Input.php";
            $ConnectionClassPath = dirname(dirname(__DIR__)) . "\\backend\\Connection.php";
            $TmplClassPath = dirname(dirname(__DIR__)) . "\\backend\\TemplateSystem.php";
            $IModulePath = dirname(dirname(__DIR__)) . "\\backend\\IModule.php";
        } else {
            $AuthClassPath = dirname(dirname(__DIR__)) . "/backend/Auth.php";
            $InputClassPath = dirname(dirname(__DIR__)) . "/backend/Input.php";
            $ConnectionClassPath = dirname(dirname(__DIR__)) . "/backend/Connection.php";
            $TmplClassPath = dirname(dirname(__DIR__)) . "/backend/TemplateSystem.php";
            $IModulePath = dirname(dirname(__DIR__)) . "/backend/IModule.php";
        }
        require_once $AuthClassPath;
        require_once $InputClassPath;
        require_once $ConnectionClassPath;
        require_once $TmplClassPath;
        require_once $IModulePath;

        $class = $_POST["class"];
        $method = $_POST["method"];
        $params = $_POST["params"];

        $auth = Auth::getInstance();
        $user = $auth->getUser();

        if(!file_exists($class . ".php")){
            http_response_code(404);
            exit;
        }

        if((in_array($class,$this->CatererClasses) && $user["rolle"] == 1) || (in_array($class,$this->KundenClasses) && $user["rolle"] == 2)){
            require_once $class . ".php";
            echo call_user_func_array($class . "::" . $method,$params);
            return;
        }else if(in_array($class,$this->PublicClasses)){
            require_once $class . ".php";
            echo call_user_func_array($class . "::" . $method,$params);
            return;
        }

        http_response_code(401);
    }

}

$ctrl = new AjaxController();