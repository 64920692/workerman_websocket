<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//Redis timeout
define('REDIS_TIMEOUT', '0');
//Redis connect type
define('REDIS_CTYPE', '1');
//curl timeout
define('CURL_TIMEOUT', 4);

class sys_config {

    public static function getConfigPath() {
        return dirname(__FILE__) . '/../Config/';
    }
}

