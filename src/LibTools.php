<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions diverses  
 */
class LibTools
{

    private static $_dontParse = array('.', '..', 'Icon', '.DS_Store');
        
        
    /**
     * Renvoi l'architecture complète d'un répertoire
     * @param string $path
     * @return array
     */
    public static function getDirectoryFiles($dir)
    {
        $items = scandir($dir);

        foreach ($items as $key => $item) {
            if (in_array(trim($item), self::$_dontParse)) {
                unset($items[$key]);
            } else {

                $tmp = explode('.', $item);

                $items[$key] = (object) array(
                            'path' => $dir . DS . $item,
                            'filename' => $item,
                            'key' => $tmp[0],
                            'data' => file_get_contents($dir . DS . $item),
                );
            }
        }

        return $items;
    }
    

    /**
     * Renvoi l'architecture complète d'un répertoire
     * @param string $path
     * @return array
     */
    public static function getDirectoryArchi($path)
    {
        $openDir = opendir($path);

        $archi = array();
        while (($entry = readdir($openDir)) !== false)
        {
            if (is_dir($path . DIRECTORY_SEPARATOR . $entry) && $entry != '.' && $entry != '..')
            {
                $archi[$entry] = self::getArchi($path . DIRECTORY_SEPARATOR . $entry);
            }
            elseif (($entry != '.') && ($entry != '..'))
            {
                $archi[] = $entry;
            }
        }
        closedir($openDir);

        return $archi;
    }

    /**
     * Converts bytes into human readable file size.
     * @param string $bytes 
     * @return string human readable file size
     */
    public static function FileSizeConvert($bytes)
    {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach ($arBytes as $arItem)
        {
            if ($bytes >= $arItem["VALUE"])
            {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", ",", strval(round($result, 2))) . " " . $arItem["UNIT"];
                break;
            }
        }
        return $result;
    }
}
