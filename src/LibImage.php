<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur la manipulation des dates   
 */
class LibImage {

    /**
     * Convert base64 string to image file
     * @param string $base64_string
     * @param string $output_file
     * @return file
     */
    public static function base64ToImage($str, $destination, $filename) {

        $response = new \stdClass();
        $arr = explode(',', $str);
        $arr2 = explode('/', $arr[0]);
        $arr3 = explode(';', $arr2[1]);
        try {
            $file = $filename . '.' . $arr3[0];
            file_put_contents($destination . $file, base64_decode($arr[1]));
            $response->status = true;
            $response->file = $file;
        } catch (Exception $e) {
            $response->status = false;
            $response->message = $e->getMessage();
        }
        return $response;
    }

    /**
     * Convert image file to base64 string
     */
    public static function imageToBase64($path) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }

    /**
     * Check if base64 string is an image
     * @param string $base64
     * @return boolean
     */
    public static function isBase64Image($base64) {

        $arr = explode(',', $base64);
        $arr2 = explode('/', $arr[0]);
        $arr3 = explode(';', $arr2[1]);

        $img = imagecreatefromstring(base64_decode($arr[1]));
        if (!$img) {
            return false;
        }

        imagepng($img, PRIVATE_FOLDER . DS . 'tmp' . DS . 'checkbase64.png');
        $info = getimagesize(PRIVATE_FOLDER . DS . 'tmp' . DS . 'checkbase64.png');
        unlink(PRIVATE_FOLDER . DS . 'tmp' . DS . 'checkbase64.png');

        if ($info[0] > 0 && $info[1] > 0 && $info['mime']) {
            return true;
        }

        return false;
    }

    /**
     * Get extension from base64 image
     * @param string $base64
     * @return string
     */
    public static function getExtensionOfBase64Image($base64) {
        $arr = explode(',', $base64);
        $arr2 = explode('/', $arr[0]);
        $arr3 = explode(';', $arr2[1]);
        return $arr3[0];
    }

    /**
     * Lance l'optmisation des images JPEG et PNG (les librairies suivantes sont obligatoires : jpegoptim & optipng)
     * @param string $log chemin de fichier de logs
     */
    public static function optimize($log = null) {
        //Exécution du script
        $cmd = 'cd ' . PRIVATE_FOLDER . DS . 'install' . DS . '; ./image-optimizer.sh &>';
        $cmd .= (!empty($log)) ? $log : '&1';
        exec($cmd, $a, $b);
        return array($a, $b);
    }

}
