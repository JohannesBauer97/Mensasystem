<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */
require_once '../backend/Input.php';
require_once '../backend/Connection.php';
require_once '../backend/TemplateSystem.php';
require_once '../backend/Auth.php';
require_once '../backend/JSController.php';
require_once '../backend/IModule.php';

require_once './modules/Home.php';
require_once './modules/Login.php';
require_once './modules/Navbar.php';
require_once './modules/KundenBestellen.php';
require_once './modules/KundenProfil.php';
require_once './modules/CatererMOTD.php';
require_once './modules/CatererKunden.php';
require_once './modules/CatererUbersicht.php';
require_once './modules/CatererSpeiseplan.php';

class Main {
    private $input;
    private $tmpMain;
    private $db;
    private $auth;
    private $user;
    private $jsController;

    /**
     * Lädt das HTML Grundgerüst
     * Befüllt die Navbar und Content Variablen mit den Render Rückgabewerten der einzelnen Module
     */
    public function show(){
        $this->input = new Input();    
        $this->tmpMain = new TemplateSystem();
        $this->db = new Connection();
        $this->jsController = JSController::getInstance();

        if ($this->db->getConnectError() != null){
            echo "Database is currently not available!";
            exit;
        }

        $this->auth = Auth::getInstance();
        $this->user = $this->auth->getUser();
        $this->tmpMain->load("Main.html");

        //Page aufbau
        $this->jsController->loadJSFile("main.js");
        $navBar = new Navbar();
        $this->tmpMain->setVar("NavBar",$navBar->render());
        $this->tmpMain->setVar("Datum",date("d.m.Y"));
        $this->ManageContent();

        $this->tmpMain->setVar("JSImports", $this->jsController->getHTMLImportString());


        $this->tmpMain->show();
    }
    
    /**
     * Setzen der Content Variable anhand des GET Parameters page
     */
    private function ManageContent(){  
        switch ($this->input->get("page")) {
            case "home":
                $Home = new Home();
                $this->tmpMain->setVar("Content",$Home->render());
                break;
            case "login":
                $Login = new Login();
                $this->tmpMain->setVar("Content",$Login->render());
                break;
            case "bestellen":
                if(!$this->user || $this->user["rolle"] == 1){
                    goto home;
                }
                $KundenBestellen = new KundenBestellen();
                $this->tmpMain->setVar("Content",$KundenBestellen->render());
                break;
            case "profil":
                if(!$this->user || $this->user["rolle"] == 1){
                    goto home;
                }
                $KundenProfil = new KundenProfil();
                $this->tmpMain->setVar("Content",$KundenProfil->render());
                break;
            case "kunden":
                if(!$this->user || $this->user["rolle"] == 2){
                    goto home;
                }
                $CatererKunden = new CatererKunden();
                $this->tmpMain->setVar("Content",$CatererKunden->render());
                break;
            case "motd":
                if(!$this->user || $this->user["rolle"] == 2){
                    goto home;
                }
                $CatererMOTD = new CatererMOTD();
                $this->tmpMain->setVar("Content",$CatererMOTD->render());
                break;
            case "ubersicht":
                if(!$this->user || $this->user["rolle"] == 2){
                    goto home;
                }
                $CatererUbersicht = new CatererUbersicht();
                $this->tmpMain->setVar("Content",$CatererUbersicht->render());
                break;
            case "speiseplan":
                if(!$this->user || $this->user["rolle"] == 2){
                    goto home;
                }
                $CatererSpeiseplan = new CatererSpeiseplan();
                $this->tmpMain->setVar("Content",$CatererSpeiseplan->render());
                break;
            case "logout":
                $this->auth->logout();
                header("location:index.php?page=home" . "&kw=" . date("W") . "&jahr=" . date("Y"));
                break;
            default:
                home:
                $Home = new Home();
                $this->tmpMain->setVar("Content",$Home->render());
                break;
        }
    }
}