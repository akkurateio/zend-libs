<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur GIT
 */
class LibGit {
    
    public static function getHash(){
        $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));
        return $commitHash;
    }
    
    public static function getDate(){
        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
        return $commitDate->format('Y-m-d H:m:s');
    }

}
