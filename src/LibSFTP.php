<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur les connexions sFTP   
 */
class LibSftp {

    private $connection;
    private $sftp;

    /**
     * Permet de créer la connexion à une serveur SFTP
     * 
     * @param string $host - Machine hôte
     * @param int $port - Port de connexion
     */
    public function __construct($host, $port=22) {
        $this->connection = ssh2_connect($host, $port);
        if (!$this->connection)
            throw new \Exception("Could not connect to $host on port $port.");
    }

    /**
     * Permet de fermer la connexion au serveur SFTP
     */
    public function __destruct() {
        ssh2_exec($this->connection, "exit");
    }

    /**
     * Permet d'ouvrir une connexion authentifiée sur le serveur SFTP
     * 
     * @param string $username - Login de connexion
     * @param string $password - Password de connexion
     * @return resource - La ressource effective connectée au serveur SFTP
     */
    public function login($username, $password) {
        if (!ssh2_auth_password($this->connection, $username, $password))
            throw new \Exception("Could not authenticate with username $username " .
                    "and password $password.");

        $this->sftp = ssh2_sftp($this->connection);
        if (!$this->sftp)
            throw new \Exception("Could not initialize SFTP subsystem.");
        else
            return $this->sftp;
    }

    /**
     *  Fonction permettant de récupérer le mtime de dernière modification du 
     * fichier distant
     * 
     * @param string $filepath
     * @return int 
     */
    public function getLastModif($filepath) {
        $result = ssh2_sftp_stat($this->sftp, $filepath);
        return $result[9];
    }
    
    /**
     * Fonction permettant de récupérer les caractéristiques d'un fichier distant
     * 
     * @param string $filepath
     * @return array 
     * 
     * @see stat
     * @link http://www.php.net/manual/fr/function.stat.php
     */
    public function getStat($filepath) {
        return ssh2_sftp_stat($this->sftp, $filepath);
    }

    /**
     *  Fonction permettant de récupérer la taille en octet du fichier distant
     * 
     * @param string $filepath
     * @return int 
     */
    public function getFileSize($filepath) {
        $result = ssh2_sftp_stat($this->sftp, $filepath);
        return $result[7];
    }

    /**
     * Fonction permettant de supprimer un fichier distant
     * 
     * @param string $filepath
     * @return bool 
     */
    public function deleteFile($filepath) {
        return ssh2_sftp_unlink($this->sftp, $filepath);
    }

    /**
     * Fonction permettant d'uploader un fichier local vers un serveur distant
     * $localDir/$nameFile => $remoteDir/$nameFile
     * 
     * @param string $localDir - Path du répertoire local
     * @param string $nameFile - Nom du fichier local
     * @param string $remoteDir - Path du répertoire distant
     * 
     */
    public function putFile($localDir, $nameFile, $remoteDir) {
        $streamFile = 'ssh2.sftp://' . $this->sftp . $remoteDir . '/' . $nameFile;

        $localFile = $localDir . '/' . $nameFile;
        if (file_exists($localFile)) {
            $contents = file_get_contents($localFile);
            if ($contents === false) {
                throw new \Exception('Impossible d\'envoyer ' . $nameFile);
            }
            $return = file_put_contents($streamFile, $contents);
            if ($return === false) {
                throw new \Exception('Impossible de d\'écrire ' . $streamFile);
            }
        } else {
            throw new \Exception('Fichier inexistant');
        }
    }
    
    /**
     * Fonction permettant d'uploader un fichier local vers un serveur distant
     * $locaPathfile => $remotePathfile
     * 
     * @param string $locaPathfile - Pathfile du fichier local
     * @param string $remotePathfile - Pathfile du fichier distant
     * 
     */
    public function putPathfile($localPathfile, $remotePathfile) {
        $streamFile = 'ssh2.sftp://' . $this->sftp . $remotePathfile;

        if (file_exists($localPathfile)) {
            $contents = file_get_contents($localPathfile);
            if ($contents === false) {
                throw new \Exception('Impossible d\'envoyer ' . $localPathfile);
            }
            $return = file_put_contents($streamFile, $contents);
            if ($return === false) {
                throw new \Exception('Impossible de d\'écrire ' . $remotePathfile);
            }
        } else {
            throw new \Exception('Fichier inexistant');
        }
    }
    
    // PERFORMANCE TROP MAUVAISE
    /**
     * Fonction permettant d'uploader un fichier local vers un serveur distant
     * $locaPathfile => $remotePathfile
     * 
     * @param string $locaPathfile - Pathfile du fichier local
     * @param string $remotePathfile - Pathfile du fichier distant
     * 
     */
//    public function putPathfile($localPathfile, $remotePathfile) {
//        return ssh2_scp_send($this->connection, $localPathfile ,$remotePathfile);
//    }

    
    /**
     * Fonction permettant de lister le contenu d'un répertoire
     * 
     * @param string $remotePath
     * @return array 
     */
    public function scanFilesystem($remotePath) {
        $entries = array();
        $handle = @opendir('ssh2.sftp://' . $this->sftp . $remotePath);
        
        if (!$handle){
            throw new \Exception('Impossible d\'ouvrir le répertoire distant !');
        }
        
        // List all the files
        while (false !== ($file = readdir($handle))) {
            if ((substr("$file", 0, 1) != ".")) {
                $entries[] = $file;
            }
        }
        closedir($handle);
        return $entries;
    }

    /**
     * Fonction permettant de récupérer un fichier distant
     * 
     * @param string $remoteDir - Path du répertoire distant
     * @param string $nameFile - Nom du fichier
     * @param string $localDir - Path du répertoire local
     */
    public function getFile($remoteDir, $nameFile, $localDir) {

        $streamFile = 'ssh2.sftp://' . $this->sftp . $remoteDir . '/' . $nameFile;

        $localFile = $localDir . '/' . $nameFile;
        if (file_exists($streamFile)) {
            $contents = file_get_contents($streamFile);
            if ($contents === false) {
                throw new \Exception('Impossible de récupérer ' . $nameFile);
            }
            $return = file_put_contents($localFile, $contents);
            if ($return === false) {
                throw new \Exception('Impossible de d\'écrire ' . $localFile);
            }
        } else {
            throw new \Exception('Fichier inexistant');
        }
    }
    
    /**
     * Fonction permettant de récupérer un fichier distant
     * 
     * @param string $remotePathfile - Pathfile du fichier distant
     * @param string $localPathfile - Pathfile du fichier local
     */
    public function getPathfile($remotePathfile, $localPathfile) {

        $streamFile = 'ssh2.sftp://' . $this->sftp . $remotePathfile;

        if (file_exists($streamFile)) {
            $contents = file_get_contents($streamFile);
            if ($contents === false) {
                throw new \Exception('Impossible de récupérer ' . $remotePathfile);
            }
            $return = file_put_contents($localPathfile, $contents);
            if ($return === false) {
                throw new \Exception('Impossible de d\'écrire ' . $localPathfile);
            }
        } else {
            throw new \Exception('Fichier inexistant');
        }
    }
    
    /**
     * Fonction permettant de renommer un fichier distant
     * 
     * @param string $from - From pathfile
     * @param string $to - To pathfile
     */
    public function rename($from, $to){
        ssh2_sftp_rename($this->sftp, $from, $to);
    }

//    public function mv($old, $new) {
//        try {
//            $old = $this->fpath($old);
//            $new = $this->fpath($new);
//        } catch (Exception $e) {
//            throw $e;
//        }
//        debug('Tentative de deplacement du fichier \'' . $old . '\' vers \'' . $new . "'");
//        $old_handle = fopen("ssh2.sftp://" . $this->url . $old, 'r');
//        if (!$old_handle)
//            throw new Exception("Impossible d'ouvrir en lecture le fichier  '" . $old . "' sur le serveur distant");
//        $new_handle = fopen("ssh2.sftp://" . $this->url . $new, 'w');
//        if (!$new_handle)
//            throw new Exception("Impossible d'ouvrir en ecriture le fichier  '" . $new . "' sur le serveur distant");
//        $size = $this->getfilesize($old);
//        $read = 0;
//        $wrote = 0;
//        while ($read < $size && ($buffer = fread($old_handle, $size - $read))) {
//            $tmp_size = strlen($buffer);
//            $read += $tmp_size;
//            $tmp_wrote = 0;
//            while ($tmp_wrote < $tmp_size) {
//                $tmp = fwrite($new_handle, substr($buffer, $tmp_wrote));
//                if (!$tmp)
//                    break;
//                $tmp_wrote += $tmp;
//            }
//            $wrote += $tmp_size;
//        }
//        debug("taille du fichier = " . $size . " , " . $read . " octets lu, " . $wrote . " octets ecrit");
//        fclose($new_handle);
//        fclose($old_handle);
//        if ($size == $read && $read == $wrote)
//            unlink("ssh2.sftp://" . $this->url . $old);
//        else
//            throw new Exception("Erreur pendant le deplacement du fichier " . $old . " en " . $old . "\ntaille du fichier = " . $size . " , " . $read . " octets lu, " . $wrote . " octets ecrit\n");
//    }

}
