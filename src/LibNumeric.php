<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur les tableaux   
 */
class LibNumeric {
    /**
     * Fonction permettant de vérifier si la chaine fournie est une chaine 
     * numérique (int, float, double, etc). 
     * La chaine d'entrée peut etre en format FR (,)ou EN (.)
     * 
     * @param string $str
     * @return boolean
     */
    public static function checkNumeric($str) {
        $str = trim($str);
        $str = str_replace(',', '.', $str);

        if (is_numeric($str)) {
            return true;
        }
        return false;
    }
    
    /**
     * Convertit un nombre arabe en chinois
     * @param int $int
     */
    public static function convertChinese($int) {
        $numbers = array(
            0 => 0,
            1 => '一',
            2 => '二',
            3 => '三',
            4 => '四',
            5 => '五',
            6 => '六',
            7 => '七',
            8 => '八',
            9 => '九'
        );
        
        return $numbers[$int];
    }
    
    /**
     * Retourne un float aléatoire
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function randomFloat($min = 0, $max = 1) {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}