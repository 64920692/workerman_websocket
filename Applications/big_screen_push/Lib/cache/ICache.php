<?php

include_once(PathConfig::getLibsPath() . 'config/IConfig.php');
include_once(PathConfig::getLibsPath() . 'cache/FRedis.php');
include_once 'Predis.class.php';

//本实例仅提供了redis的cache处理
class ICache {

    private static $_cacheobj = null; //缓存链接对象

    private function __construct() {

    }

    private function __clone() {

    }
    /**
     * PHP扩展读取redis 效率高
     * @param type $cachename
     * @return type
     */
    /*public static function getRedis($cachename='DEFAULT') {
        if (isset(self::$_cacheobj[$cachename]) && self::$_cacheobj[$cachename] != null) {
            return self::$_cacheobj[$cachename];
        } else {
            $conf = IConfig::getConfig($cachename, 'cache');
            $redis = new Redis();
            $redis->connect($conf->host, $conf->port);
            self::$_cacheobj[$cachename] = $redis;
            return self::$_cacheobj[$cachename];
        }
    }*/
    /**
     * 通过Predis读取缓存，老方法，效率低，本地调试使用
     * @param type $cachename
     * @return type
     */
    /*public static function getRedis($cachename='DEFAULT') {
        if (isset(self::$_cacheobj[$cachename]) && self::$_cacheobj[$cachename] != null) {
            return self::$_cacheobj[$cachename];
        } else {
            $conf = IConfig::getConfig($cachename, 'cache');
            $redis = new Predis_Client(array('host' => $conf->host, 'port' => $conf->port));
            self::$_cacheobj[$cachename] = $redis;
            return self::$_cacheobj[$cachename];
        }
    }*/

    /**
     * 通过connect pool连接缓存
     */
    public static function getRedis($cachename='DEFAULT') {
        if (isset(self::$_cacheobj[$cachename]) && self::$_cacheobj[$cachename] != null) {
            return self::$_cacheobj[$cachename];
        } else {
            $conf = IConfig::getConfig($cachename, 'cache');
//            $redis = new Predis_Client(array('host' => $conf->host, 'port' => $conf->port));
            $redis = FRedis::getInstance($conf->host, $conf->port);
            self::$_cacheobj[$cachename] = $redis;
            return self::$_cacheobj[$cachename];
        }
    }

    /**
     * 关闭redis连接
     */
    public static function closeRedis($redis, $cachename='DEFAULT') {
        if(isset($redis)) {
            $redis->close();
        }
        $redis = null;
        self::$_cacheobj[$cachename] = null;
    }

}

?>
