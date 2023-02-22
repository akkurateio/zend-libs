<?php

namespace Subvitamine\Libs;

/**
 * Classe qui permet une gestion simplifiée des caches
 */
class LibCache {

    /**
     * Store data cache
     * @param string $registryKey
     * @param string $key
     * @param string | stdClass | array $values
     * @param array $tags
     */
    public static function set($registryKey, $key, $values, $tags = array(), $lifetime = null) {
        $cache = \Zend_Registry::get($registryKey);
        if (!empty($lifetime)) {
            $cache->save($values, $key, $tags, $lifetime);
        } else {
            $cache->save($values, $key, $tags);
        }
        unset($cache);
    }

    /**
     * Get data cache stored
     * @param string $registryKey
     * @param string $key
     * @return string | object | array $tags
     */
    public static function get($registryKey, $key) {
        $cache = \Zend_Registry::get($registryKey);
        $data = $cache->load($key);
        unset($cache);
        return $data;
    }

    /**
     * Remove data cache stored by key
     * @param string $registryKey
     * @param string $key
     */
    public static function removeByKey($registryKey, $key) {
        $cache = \Zend_Registry::get($registryKey);
        $cache->remove($key);
        unset($cache);
    }

    /**
     * Remove data cache stored by tags
     * @param string $registryKey
     * @param array $tags
     */
    public static function removeByTags($registryKey, $tags) {
        $cache = \Zend_Registry::get($registryKey);
        $cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);
        unset($cache);
    }

    /**
     * Remove all data cache stored
     * @param string $registryKey
     */
    public static function removeAll($registryKey) {
        $cache = \Zend_Registry::get($registryKey);
        $cache->clean(\Zend_Cache::CLEANING_MODE_ALL);
        unset($cache);
    }

}
