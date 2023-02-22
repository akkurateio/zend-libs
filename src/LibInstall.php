<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant la possibilité de déployer l'application
 * By Julien Herrera For Subvitamine(tm)
 */
class LibInstall {

    //Paramètres du script
    protected static $_options = array(
        'all' => 'a',
        'bower' => 'b',
        'composer' => 'c',
        'database' => 'd',
        'folder' => 'f',
        'git' => 'g'
    );

    /**
     * Déploie l'aplication selon une liste d'option
     * @param string $environment
     * @param array $listOptions (array('all'=> 1, 'bower' => 1, 'composer' => 1, 'database' => 1, 'folder' => 1, 'git' => 1))
     * @param string $log chemin de fichier de logs
     */
    public static function deploy(string $environment, array $listOptions = array('all' => 1), $log = null) {
        if (empty($listOptions)) {
            return false;
        } else {
            //Construction des options
            $options = '';
            foreach ($listOptions as $key => $opt) {
                if ($opt && array_key_exists($key, self::$_options)) {
                    $options .= self::$_options[$key];
                }
            }

            if (empty($options)) {
                return false;
            } else {
                //Exécution du script
                $cmd = 'cd ' . PRIVATE_FOLDER . DS . 'install' . DS . '; ./install.sh -e' . $environment . ' -i' . $options . ' &>';
                $cmd .= (!empty($log)) ? $log : '&1';
                exec($cmd, $a, $b);
                return array($a, $b);
            }
        }
    }

}
