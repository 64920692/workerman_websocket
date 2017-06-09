<?php
namespace TYSX\Config;
use TYSX\Config\IniConfig;
class IConfig {

    private static $_instance = null;
    private static $_confdata = null;

    private function __construct() {

    }

    private function __clone() {

    }

    private static function getObject($type) {
        if (self::$_instance === null || !isset(self::$_instance[$type])) {
            $confobj = new IniConfig();
            self::$_instance[$type] = $confobj;
        }
        return self::$_instance[$type];
    }

    public static function getConfig($sec, $type) {
        if (self::$_confdata == null || !isset(self::$_confdata[$type])) {
            $filePath = \sys_config::getConfigPath();
            $filename = $filePath . $type . ".ini";
            $obj = self::getObject($type);
            $data = $obj->getConfig($filename);
            self::$_confdata[$type] = $data;
        }
        return self::$_confdata[$type][$sec];
    }

}