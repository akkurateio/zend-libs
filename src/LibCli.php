<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur les environnements CLI  
 */
class LibCli {
    /**
     * Vérifie si l'exécution est faite depuis un environnement CLI
     * @return boolean
     */
    public static function isCli() {
        if (PHP_SAPI == "cli") {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Renvoi les arguments passés en ligne de commande sous forme de tableau
     * @return array
     */
    public static function getArgs() {
        $optLong = "--";
        $optShort = "-";

        $argv = $_SERVER['argv'];
        unset($argv[0]);

        $env = array();
        foreach ($argv as $arg) {
            // Traitement pour l'option longue
            if (strpos($arg, $optLong) !== false) {
                $arg = preg_replace("/" . $optLong . "/", "", $arg, 1);
                //$arg = str_replace($optLong, '', $arg);
                $argX = explode("=", $arg);
                $env[$argX[0]] = preg_replace("/" . $argX[0] . "=/", "", $arg, 1);
            }
            // Traitement pour l'option courte
            else if (strpos($arg, $optShort) !== false) {
                //On considere qu'une option courte peut ne pas avoir le signe =
                //$arg = str_replace($optShort, '', $arg);
                $arg = preg_replace("/" . $optShort . "/", "", $arg, 1);
                $arg = preg_replace("[=]", "", $arg, 1);
                $key = substr($arg, 0, 1);
                $value = substr($arg, 1, strlen($arg));
                $env[$key] = $value;
            }
        }
        return $env;
    }
}