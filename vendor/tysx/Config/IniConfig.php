<?php

//配置文件读取
namespace TYSX\Config;
class IniConfig {//extends IConfig {

    private $_parentFlag = ':'; //继承字符标识，仅用于section节
    private $_classFlag = '.'; //类标识

    private function parseObjectVal($o, $vks, $val, $pos, $vlen) {
        if ($pos == $vlen - 1) {
            $o->{$vks[$pos]} = $val;
        } else {
            $o->{$vks[$pos]} = $this->parseObjectVal($o->{$vks[$pos]}, $vks, $val, $pos + 1, $vlen);
        }
        return $o;
    }

    private function mergeObjectValue($o, $n) {
        if(!empty($o)){
            foreach ($o as $key => $val) {
                if (!isset($n->$key)) {
                    $n->$key = $val;
                } else {
                    if (is_object($val) && is_object($n->$key)) {
                        $n->$key = $this->mergeObjectValue($val, $n->$key);
                    }
                }
            }
        }
        return $n;
    }

    private function parseVal($val) {
        $o = new \stdClass();
        if (is_array($val)) {
            foreach ($val as $vk => $vv) {
                $vks = explode($this->_classFlag, $vk);
                switch (count($vks)) {
                    case 1:$o->$vk = $vv;
                        break;
                    default:
                        $ilen = count($vks);
                        $o->{$vks[0]} = $this->parseObjectVal($o->{$vks[0]}, $vks, $vv, 1, $ilen);
                        break;
                }
            }
            return $o;
        } else {
            return null;
        }
    }

    public function getConfig($filename) {
        if (empty($filename)) {
            //require_once 'Config/Exception.php';
            //throw new ConfigException('FilenameIsNotSet');
            return null;
        }

        $inidata = parse_ini_file($filename, true);
        $ini = null;
        if (is_array($inidata)) {
            foreach ($inidata as $key => $val) {
                $keys = explode($this->_parentFlag, $key);
                $o = $this->parseVal($val);
                switch (count($keys)) {
                    case 1:
                        $ini[$key] = $o;
                        break;
                    case 2:
                        $po = $ini[trim($keys[1])];
                        //var_dump($o);die;
                        $this->mergeObjectValue($po, $o);
                        $ini[trim($keys[0])] = $o;
                        break;
                    default:
                        $ini[$key] = $o;
                        break;
                }
            }
        }
        return $ini;
    }

}
?>