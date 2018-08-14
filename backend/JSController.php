<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */

class JSController
{
    private static $instance = null;
    private $JSFiles = array();

    private function __construct()
    {

    }

    /**
     * JS Constroller
     * @return JSController|null
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new JSController();
        }

        return self::$instance;
    }

    /**
     * Adds JS File to the END of JSFiles array
     * @param $fileName File in js folder
     * @return bool True if file exists
     */
    public function loadJSFile($fileName)
    {
        $jsFolderPath = "";
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { //Local Dev on Windows
            $jsFolderPath = dirname(__DIR__) . "\\frontend\\js\\";
        } else {
            $jsFolderPath = dirname(__DIR__) . "/frontend/js/";
        }


        if (file_exists($jsFolderPath . "$fileName")) {
            array_push($this->JSFiles, $fileName);
            return true;
        }
        return false;
    }

    /**
     * Get the current loaded JS Files
     * @return array File Names
     */
    public function getLoadedJSFiles()
    {
        return $this->JSFiles;
    }

    /**
     * @return string HTML Import string with all JS Files loaded
     */
    public function getHTMLImportString()
    {
        $outputString = "";

        foreach ($this->JSFiles as $File) {
            $outputString .= "<script type=\"text/javascript\" src=\"js/$File\"></script>";
        }

        return $outputString;
    }
}