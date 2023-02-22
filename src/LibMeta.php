<?php

namespace Subvitamine\Libs;

use \Subvitamine\Libs\LibMeta AS Meta;

/**
 * Classe offrant un pannel de fonctions sur les balises meta   
 */
class LibMeta {
    /**
     * Liste des metas et og de base
     */
    // title => C'est le titre de votre page. Champs requis.
    // description => C'est la description de votre page. Champs requis.
    // keywords => C'est les mots clés de votre page.
    // og:title => C'est le titre de votre page pour l'Open Graph (par exemple Facebook). Champs requis.
    // og:description => C'est une description courte de votre page. Une ou deux phrases, pas plus de 300 caractères.
    // og:image => C'est l'URL de l'image qui représente votre page dans l'Open Graph. Champs requis.
    // og:site_name => C'est le nom du site
    // og:url => C'est l'URL "canonique", c'est à dire celle de référence, de votre page. Champs requis.
    // og:type => C'est le type de votre page (website, article, video, music...). Champs requis.
    // og:video => Si vous avez une vidéo dans votre page, c'est ici qu'il faut placer son URL.
    // og:locale => C'est la langue de votre page (en_US, fr_FR...).

    /**
     * Set metas page of item (article, rubric, authors...)
     * @param Zend\View $view
     * @param object $item
     * @return null
     */
    public static function setItemMetas($view, $item, $model = null, $type = null) {

        /**
         * BASIC META
         */
        Meta::setMetaTitle($view, $item);
        Meta::setMetaRobots($view, $item);
        Meta::setMetaKeywords($view, $item);
        Meta::setMetaDescription($view, $item);

        /**
         * OG META 
         */
        Meta::setOgTitle($view, $item);
        Meta::setOgDescription($view, $item);
        Meta::setOgImage($view, $item, $model);
        Meta::setOgSitename($view);
        Meta::setOgUrl($view);
        Meta::setOgType($view, $type);
    }

    /**
     * Set custom metas page
     * @param Zend\View $view
     * @param array $metas
     * @return null
     */
    public static function setCustomMetas($view, $metas) {
        if (!empty($metas['title'])) {
            Meta::setMetaTitle($view, null, $metas['title']);
        }
        if (!empty($metas['robots'])) {
            Meta::setMetaRobots($view, null, $metas['robots']);
        }
        if (!empty($metas['description'])) {
            Meta::setMetaDescription($view, null, $metas['description']);
        }
        if (!empty($metas['keywords'])) {
            Meta::setMetaKeywords($view, null, $metas['keywords']);
        }
        if (!empty($metas['ogTitle'])) {
            Meta::setOgTitle($view, null, $metas['ogTitle']);
        }
        if (!empty($metas['ogDescription'])) {
            Meta::setOgDescription($view, null, $metas['ogDescription']);
        }
        if (!empty($metas['ogImage'])) {
            Meta::setOgImage($view, null, null, $metas['ogImage']);
        }
        if (!empty($metas['ogType'])) {
            Meta::setOgType($view, $metas['ogType']);
        }
        if (!empty($metas['ogSitename'])) {
            Meta::setOgSitename($view, $metas['ogSitename']);
        }
        if (!empty($metas['ogUrl'])) {
            Meta::setOgUrl($view, $metas['ogUrl']);
        }
    }

    /**
     * Set meta title
     * @param Zend\View $view
     * @param object $item
     */
    public static function setMetaTitle($view, $item = null, $title = null) {
        $metaTitle = $item->metaTitle ?? null;
        
        if (!empty($title)) {
            $view->headTitle()->prepend($title);
        } elseif (!empty($item) && !empty($metaTitle)) {
            $view->headTitle()->prepend($metaTitle);
        } elseif (!empty($item) && empty($metaTitle)) {
            $view->headTitle()->prepend($item->title);
        }
    }

    /**
     * Set meta title
     * @param Zend\View $view
     * @param object $item
     */
    public static function setMetaRobots($view, $item = null, $isIndexable = false) {
        if ($isIndexable || (!empty($item) && $item->isIndexable == true)) {
            $view->headMeta()->setName('robots', 'index, follow');
        }
    }

    /**
     * Set meta description
     * @param Zend\View $view
     * @param object $item
     */
    public static function setMetaDescription($view, $item = null, $description = null) {
        $metaDesc = $item->metaDesc ?? null;
        
        if (!empty($description)) {
            $view->headMeta()->setName('description', $description);
        } elseif (!empty($item && !empty($metaDesc))) {
            $view->headMeta()->setName('description', str_replace(PHP_EOL, "", $metaDesc));
        } elseif (!empty($item && empty($metaDesc))) {
            $view->headMeta()->setName('description', str_replace(PHP_EOL, "", $item->overview));
        }
    }

    /**
     * Set meta keywords
     * @param Zend\View $view
     * @param object $item
     */
    public static function setMetaKeywords($view, $item = null, $keywords = null) {
        $metaKeywords = $item->metaKeywords ?? null;
        
        if (!empty($keywords)) {
            $view->headMeta()->setName('keywords', $keywords);
        } else if (!empty($metaKeywords)) {
            if(is_array($metaKeywords)) {
                $keywords = array();
                foreach ($metaKeywords as $item) {
                    $keywords[] = $item->text;
                }
                $view->headMeta()->setName('keywords', implode(',', $keywords));
            }
            else {
                $view->headMeta()->setName('keywords', $metaKeywords);
            }
        }
    }

    /**
     * Set og title
     * @param Zend\View $view
     * @param object $item
     */
    public static function setOgTitle($view, $item = null, $title = null) {
        $ogTitle = $item->ogTitle ?? null;
        $metaTitle = $item->metaTitle ?? null;
        
        if (!empty($title)) {
            $ogTitle = $title;
        } elseif (!empty($item) && !empty($ogTitle)) {
            $ogTitle = $ogTitle;
        } elseif (!empty($item) && empty($ogTitle) && !empty($metaTitle)) {
            $ogTitle = $metaTitle;
        } elseif (!empty($item) && empty($ogTitle) && empty($metaTitle)) {
            $ogTitle = $item->title;
        }
        $view->headMeta()->setProperty('og:title', $ogTitle);
    }

    /**
     * Set og description
     * @param Zend\View $view
     * @param object $item
     */
    public static function setOgDescription($view, $item = null, $description = null) {
        $ogDesc = $item->ogDesc ?? null;
        $metaDesc = $item->metaDesc ?? null;
        
        if (!empty($description)) {
            $ogDescription = $description;
        } elseif (!empty($item) && !empty($ogDesc)) {
            $ogDescription = str_replace(PHP_EOL, "", $ogDesc);
        } elseif (!empty($item) && empty($ogDesc) && !empty($metaDesc)) {
            $ogDescription = str_replace(PHP_EOL, "", $metaDesc);
        } elseif (!empty($item) && empty($ogDesc) && empty($metaDesc)) {
            $ogDescription = str_replace(PHP_EOL, "", $item->overview);
        }
        $view->headMeta()->setProperty('og:description', $ogDescription);
    }

    /**
     * Set og image
     * @param Zend\View $view
     * @param object $item
     */
    public static function setOgImage($view, $item = null, $model = null, $imgUrl = null) {
        if (!empty($imgUrl)) {
            // custom img
            $view->headMeta()->setProperty('og:image', $imgUrl);
        } elseif (!empty($item) && !empty($model)) {
            // item img
            $routes = \Zend_Registry::get('Zend_Routes');
            $apiUrl = \Subvitamine\Libs\LibServer::get_protocole() . $routes->routes->api->route . DS . 'image' . DS . $model . DS . $item->id . DS;
            if (!empty($item->ogImage)) {
                $view->headMeta()->setProperty('og:image', $apiUrl . $item->ogImage. DS . 'og');
            } elseif (!empty($item->pictureMobile)) {
                $view->headMeta()->setProperty('og:image', $apiUrl . $item->pictureMobile . DS . 'og');
            } elseif (!empty($item->picture)) {
                $view->headMeta()->setProperty('og:image', $apiUrl . $item->picture . DS . 'og');
            }
        }
    }

    /**
     * Set og sitename
     * @param Zend\View $view
     */
    public static function setOgSitename($view, $sitename = null) {
        if (!empty($sitename)) {
            $view->headMeta()->setProperty('og:site_name', $sitename);
        } elseif(defined('GENERAL_OWNER')) {
            $view->headMeta()->setProperty('og:site_name', GENERAL_OWNER);
        } elseif(defined('GENERAL_NAME')){
            $view->headMeta()->setProperty('og:site_name', GENERAL_NAME);
        }
    }

    /**
     * Set og url
     * @param Zend\View $view
     */
    public static function setOgUrl($view, $url = null) {
        if (!empty($url)) {
            $view->headMeta()->setProperty('og:url', $url);
        } else {
            $view->headMeta()->setProperty('og:url', \Subvitamine\Libs\LibServer::get_protocole() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        }
    }

    /**
     * Set og type
     * @param Zend\View $view
     */
    public static function setOgType($view, $type = null) {
        if (!empty($type)) {
            $view->headMeta()->setProperty('og:type', $type);
        }
    }

}
