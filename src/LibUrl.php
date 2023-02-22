<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur les url   
 */
class LibUrl {

    private static $_content = null;
    private static $_contentUrl = null;

    /**
     * Check if the variable $url is a valid URL
     * @param string $url
     * @return boolean
     */
    public static function isValid($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Récupération de toutes les URL inclues dans une string
     * @param type $string
     * @return array
     */
    public static function getUrlsFromString($string) {
        $urlList = array();
        preg_match_all('/((https?:\/\/)(www\.)?|(www\.))[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/i', $string, $urlList);

        if (isset($urlList[0]) && !empty($urlList[0])) {
            return $urlList[0];
        } else {
            return array();
        }
    }

    /**
     * Récupération de toutes les balises meta à partir d'une url
     * @param type $url
     * @return array
     */
    public static function getMetaFromUrl($url, $removeCache = 0) {
        //Vérification si les infos sont en cache
        $cacheUrl = Subvitamine\Libs\LibString::keepAlphaNumeric($url);
        $cache = Zend_Registry::get('cache');
        $data = $cache->load($cacheUrl);

        if (empty($data) || $removeCache == 1) {
            //Récupération du contenu de l'url
            self::$_contentUrl = @file_get_contents($url);

            //récupération des données
            $title = null;
            $desc = null;
            $images = array();
            $keywords = array();
            if (!empty(self::$_contentUrl)) {
                //Récupération des tags og
                $ogTags = self::_getOgTags();

                //Récupération du titre
                if (!empty($ogTags['og:title'])) {
                    $title = $ogTags['og:title'];
                } else {
                    $title = self::_getTitle();
                }

                //Récupération de la description
                if (!empty($ogTags['og:description'])) {
                    $desc = $ogTags['og:description'];
                } else {
                    $desc = self::_getDescription($url);
                }

                //Récupération de limage
                if (!empty($ogTags['og:image'])) {
                    $images = $ogTags['og:image'];
                } else {
                    $images = self::_getImage($url);
                }

                //Récupération des keywords
                $keywords = self::_getKeywords($url);
            }

            //Vérification si utf8
            if (!preg_match('!!u', $title)) {
                $title = utf8_encode($title);
            }
            if (!preg_match('!!u', $desc)) {
                $desc = utf8_encode($desc);
            }

            $data = array(
                'url' => $url,
                'title' => $title,
                'description' => $desc,
                'images' => $images,
                'keywords' => $keywords
            );

            //Sauvegarde des données en cache
            $cache->save($data, $cacheUrl, array(CACHE_LINK_DATAS), CACHE_LINK_LIFETIME);
        }

        return $data;
    }

    /**
     * Récupération des tags og
     * @return array
     */
    private static function _getOgTags() {
        //Récupération des tags
        $matches = null;
        preg_match_all('~<\s*meta\s+property="(og:[^"]+)"\s+content="([^"]*)~i', self::$_contentUrl, $matches);

        //Mise en forme des tags
        $ogtags = array();
        for ($i = 0; $i < count($matches[1]); $i++) {
            $ogtags[$matches[1][$i]] = $matches[2][$i];
        }

        return $ogtags;
    }

    /**
     * Récupération du titre
     * @return string
     */
    private static function _getTitle() {
        $match = null;
        preg_match('/<title>([^>]*)<\/title>/si', self::$_contentUrl, $match);

        $title = null;
        if (is_array($match) && count($match) > 0) {
            $title = strip_tags($match[1]);
        }

        return $title;
    }

    /**
     * Récupération de la description
     * @return string
     */
    private static function _getDescription($url) {
        $tags = get_meta_tags($url);

        $desc = null;
        if (isset($tags['description'])) {
            $desc = $tags['description'];
        }

        return $desc;
    }

    /**
     * Récupération de l'image
     * @return array
     */
    private static function _getImage($url) {
        //Récupération de la première image
        $matches = null;
        preg_match_all('/<\s*img[^\>]*src\s*=\s*["\']([^"\'\s>]*)/i', self::$_contentUrl, $matches);

        $images = array();
        if (!empty($matches) && isset($matches[1])) {
            foreach ($matches[1] as $img) {
                //Vérification du lien de l'image pour mise en forme
                if (preg_match('/^\//', $img)) {
                    //Le lien de l'image commence par /
                    if (substr($url, -1) == '/') {
                        $img = rtrim($url, '/') . $img;
                    } else {
                        $img = $url . $img;
                    }
                } else if (!preg_match('/^http/', $img)) {
                    //Le lien de l'image ne commence pas par http
                    if (preg_match('/^\//', $img)) {
                        //Le lien de l'image commence par /
                        if (substr($url, -1) == '/') {
                            //L'url se termine par /
                            $img = rtrim($url, '/') . $img;
                        } else {
                            $img = $url . $img;
                        }
                    } else {
                        //Le lien de l'image ne commence pas par /
                        if (substr($url, -1) == '/') {
                            //L'url se termine par /
                            $img = $url . $img;
                        } else {
                            $img = $url . '/' . $img;
                        }
                    }
                }

                $images[] = $img;
            }
        }

        return $images;
    }

    /**
     * Récupération des keywords
     * @return array
     */
    private static function _getKeywords($url) {
        $tags = get_meta_tags($url);

        $keywords = array();
        if (isset($tags['keywords'])) {
            $keywordsList = $tags['keywords'];

            //Vérification si utf8
            if (!preg_match('!!u', $keywordsList)) {
                $keywordsList = utf8_encode($keywordsList);
            }

            $keywords = array_filter(array_map('trim', explode(',', $keywordsList)));
        }

        return $keywords;
    }

}
