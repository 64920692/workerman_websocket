<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22
 * Time: 14:50
 */
class PathConfig{
    //获取lib文件夹目录
    public static function getLibsPath(){
        return dirname(__file__).'/../Lib/';
    }
    //获取config文件夹目录
    public static function getConfigPath(){
        return dirname(__file__).'/../Config/';
    }
}