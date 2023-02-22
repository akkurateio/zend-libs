<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions utilitaires sur les requêtes SQL   
 */
class LibSql {

    /**
     * Permet de construire une requête SQL à partir d'un tableau de données
     * 
     * @param string $tableName
     * @param array $data
     * @return string
     */
    public static function createInsertQuery($tableName, $data = array()) {
        if (empty($data)) {
            return null;
        }
        $fields = "";
        $values = "";
        $i = 1;
        foreach ($data as $field => $value) {
            $fields .= "`" . $field . "`";
            $fields .= ($i < count($data)) ? ", " : "";
            $values .= "'" . str_replace("'", "''", $value) . "'";
            $values .= ($i < count($data)) ? ", " : "";
            $i++;
        }
        return "INSERT INTO " . $tableName . " (" . $fields . ") VALUES (" . $values . ");\n";
    }

}
