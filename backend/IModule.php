<?php
/**
 * Created by PhpStorm.
 * User: Johannes Bauer
 */

/**
 * Vorlage für alle Module der Website
 * Interface IModule
 */
interface IModule
{
    /**
     * Gibt den generierten HTML Code des Moduls zurück
     * @return mixed
     */
    public function render();
}