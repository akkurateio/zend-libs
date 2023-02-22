<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur les tableaux   
 */
class LibArray {

    /**
     * Conversion d'un tableau en objet
     * @param array $array
     * @return \stdClass|boolean
     */
    public static function arrayToObject($array) {
        if (!is_array($array)) {
            return $array;
        }

        $object = new \stdClass();
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $name => $value) {
                $name = strtolower(trim($name));
                if (($name == 0) || (!empty($name))) {
                    $object->$name = self::arrayToObject($value);
                }
            }
            return $object;
        } else {
            return FALSE;
        }
    }

    /**
     * Suppression des clés ayant une valeur null
     * @param array $array
     * @return array
     */
    public static function cleanNullIndexes($array) {
        if (!empty($array)) {
            $keys = array_keys(current($array));
            foreach ($array as $arr) {
                // On restreint les clefs aux seules clefs ayant systématiquement une valeur null
                $keys = array_intersect($keys, array_keys($arr, null));
            }

            // On supprime alors les clefs en question
            foreach ($keys as $key) {
                foreach ($array as $id => $arr) {
                    unset($array[$id][$key]);
                }
            }
        }

        return $array;
    }

    /**
     * recursive search for key in nested array, also search in objects!!
     * returns: array with "values" for the searched "key"
     * @param mixed $key
     * @param array $array
     * @return array
     */
    public static function searchKeyValues($key, $array) {
        if (is_object($array))
            $array = (array) $array;

        // search for the key
        $result = array();
        foreach ($array as $k => $value) {
            if (is_array($value) || is_object($value)) {
                $r = self::searchKeyValues($key, $value);
                if (!is_null($r))
                    array_push($result, $r);
            }
        }

        if (array_key_exists($key, $array))
            array_push($result, $array[$key]);


        if (count($result) > 0) {
            // resolve nested arrays
            $result_plain = array();
            foreach ($result as $k => $value) {
                if (is_array($value))
                    $result_plain = array_merge($result_plain, $value);
                else
                    array_push($result_plain, $value);
            }
            return $result_plain;
        }
        return NULL;
    }

    /**
     * Génére la structure d'un CSV à partir d'un tableau de données
     * @param array $datas
     * @return string
     */
    public static function arrayToCsv($datas, $separator = ";", $delimiter = '"') {
        //Construction du csv
        $first = true;
        $entete = "";
        $csv = "";
        foreach ($datas as $data) {
            foreach ($data as $key => $ligne) {
                if ($first == true) {
                    $entete .= $delimiter . str_replace($delimiter, '\\' . $delimiter, $key) . $delimiter . $separator;
                }
                $csv .= $delimiter . str_replace($delimiter, '\\' . $delimiter, $ligne) . $delimiter . $separator;
            }
            $csv .= "\n";

            if ($first == true) {
                $first = false;
                $entete .= "\n";
            }
        }
        unset($datas);

        $output = $entete . $csv;

        return $output;
    }

    /**
     * Removes duplicate values from a multidimensionnal array
     * @see http://php.net/manual/en/function.array-unique.php
     * @param array $array <p>
     * The input array.
     * </p>
     * @return array the filtered array.
     */
    public static function array_unique_multidim($array) {
        return array_map("unserialize", array_unique(array_map("serialize", $array)));
    }

    /**
     * Retourne l'élément ayant pour valeur value et comme clé index
     * @param array $array
     * @param string $index
     * @param string $value
     * @param string $child
     * @return mixed
     */
    public static function objArraySearch($array, $index, $value, $child = "") {
        foreach ($array as $arrayInf) {
            if (!empty($child)) {
                if ($arrayInf[$child][$index] == $value) {
                    return $arrayInf;
                }
            } else {
                if (is_object($arrayInf)) {
                    if ($arrayInf->$index == $value) {
                        return $arrayInf;
                    }
                } else {
                    if ($arrayInf[$index] == $value) {
                        return $arrayInf;
                    }
                }
            }
        }
        return null;
    }

    public static function xmlToArray($xml, $options = array()) {
        $defaults = array(
            'namespaceSeparator' => ':', //you may want this to be something other than a colon
            'attributePrefix' => '@', //to distinguish between attributes and nodes with the same name
            'alwaysArray' => array(), //array of xml tag names which should always become arrays
            'autoArray' => true, //only create arrays for tags which appear more than once
            'textContent' => '$', //key used for the text content of elements
            'autoText' => true, //skip textContent key if node has no attributes or child nodes
            'keySearch' => false, //optional search and replace on tag and attribute names
            'keyReplace' => false       //replace values for above search values (as passed to str_replace())
        );
        $options = array_merge($defaults, $options);
        $namespaces = $xml->getDocNamespaces();
        $namespaces[''] = null; //add base (empty) namespace
        //get attributes from all namespaces
        $attributesArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
                //replace characters in attribute name
                if ($options['keySearch'])
                    $attributeName = str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
                $attributeKey = $options['attributePrefix']
                        . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                        . $attributeName;
                $attributesArray[$attributeKey] = (string) $attribute;
            }
        }

        //get child nodes from all namespaces
        $tagsArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->children($namespace) as $childXml) {
                //recurse into child nodes
                $childArray = self::xmlToArray($childXml, $options);
                list($childTagName, $childProperties) = each($childArray);

                //replace characters in tag name
                if ($options['keySearch'])
                    $childTagName = str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
                //add namespace prefix, if any
                if ($prefix)
                    $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;

                if (!isset($tagsArray[$childTagName])) {
                    //only entry with this key
                    //test if tags of this type should always be arrays, no matter the element count
                    $tagsArray[$childTagName] = in_array($childTagName, $options['alwaysArray']) || !$options['autoArray'] ? array($childProperties) : $childProperties;
                } elseif (
                        is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName]) === range(0, count($tagsArray[$childTagName]) - 1)
                ) {
                    //key already exists and is integer indexed array
                    $tagsArray[$childTagName][] = $childProperties;
                } else {
                    //key exists so convert to integer indexed array with previous value in position 0
                    $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
                }
            }
        }

        //get text content of node
        $textContentArray = array();
        $plainText = trim((string) $xml);
        if ($plainText !== '')
            $textContentArray[$options['textContent']] = $plainText;

        //stick it all together
        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '') ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        //return node as array
        return array(
            $xml->getName() => $propertiesArray
        );
    }

}
