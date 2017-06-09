<?php
namespace TYSX\Cache;

use TYSX\Cache\FRedis;
use TYSX\Config\IConfig;
//include_once 'Predis.class.php';

//本实例仅提供了redis的cache处理
class ICache {

    private static $_cacheobj = null; //缓存链接对象

    private function __construct() {

    }

    private function __clone() {

    }
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
