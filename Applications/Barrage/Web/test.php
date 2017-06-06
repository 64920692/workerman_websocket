<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/10
 * Time: 22:48
 */

$message_data ='{"type":"push","info":"{\"aaa\":\"bbb\"}"}';

$message_data = json_decode($message_data,true);
print_r($message_data);