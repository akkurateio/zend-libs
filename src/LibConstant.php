<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur les constantes 
 */
class LibConstant {

    /**
     * Formatte la valeur d'une constante et la renvoie. Remplacera si nécessaire tout ce qui est entre des % par ce qui est dans $replace
     * @param string $constant
     * @param string | array $replace
     * @param string $delimiter
     * @return string
     */
    public static function formatConstantValue($constant, $replace = null, $delimiter = '%') {
        if(empty($replace)) {
            return $constant;
        }
        else {
            $pattern = '/('.$delimiter.'[[:alnum:]]*'.$delimiter.')/';
            return preg_replace($pattern, $replace, $constant);
        }
    }
}
