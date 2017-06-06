<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

/**
 * 聊天主逻辑
 * 主要是处理 onMessage onClose 
 */
use \GatewayWorker\Lib\Gateway;
require_once  dirname(__FILE__) . '/Config/SystemConfig.inc.php';
require_once dirname(__FILE__) . '/Config/PathConfig.php';
include_once(PathConfig::getLibsPath() . 'cache/ICache.php');
class Events
{
   
   /**
    * 有消息时
    * @param int $client_id
    * @param mixed $message
    */
   public static function onMessage($client_id, $message)
   {
        // debug
       \Workerman\Worker::log("client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:".json_encode($_SESSION)." onMessage:".$message."\n");

      // \Workerman\Worker::log(json_encode($_SERVER)."\n");

        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if(!$message_data)
        {
            return ;
        }

        // 根据类型执行不同的业务
        switch($message_data['type'])
        {
            // 客户端回应服务端的心跳
            case 'pong':
                return;
            // 客户端登录 message格式: {type:login, name:xx, room_id:1} ，添加到客户端，广播给所有客户端xx进入聊天室
            case 'login':
                // 判断机器码
                if(!isset($message_data['terId']))
                {
                    throw new \Exception("\$message_data['terId'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }
                // 把房间号昵称放到session中
                $terId = $message_data['terId'];

                $_SESSION['terId'] = $terId;
                if($message_data['h5']){
                    //写redis
                    $_SESSION['h5'] = 1;
                    $redis = ICache::getRedis('api');
                    $redis->set("big_screen_".$terId,$client_id);
                }
                // 转播给当前房间的所有客户端，xx进入聊天室 message {type:login, client_id:xx, name:xx}
                $new_message = array('type'=>$message_data['type'], 'client_id'=>$client_id, 'time'=>date('Y-m-d H:i:s'));



                //return Gateway::sendToAll(json_encode($new_message));
                Gateway::joinGroup($client_id, $terId);
                Gateway::sendToCurrentClient(json_encode($new_message));
                return;
            case 'push':
                $terId = $_SESSION['terId'];
                return Gateway::sendToGroup($terId,$message_data['info']);
                //return Gateway::sendToAll(($message_data['info']));
        }
   }
   
   /**
    * 当客户端断开连接时
    * @param integer $client_id 客户端id
    */
   public static function onClose($client_id)
   {
       // debug
       \Workerman\Worker::log("client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n") ;
       if($_SESSION['h5']){
           $redis = ICache::getRedis('api');
           $redis->delete("big_screen_".$_SESSION['terId']);
           \Workerman\Worker::log("DelRedis:big_screen_".$_SESSION['terId']);
       }
   }

   public  static function onConnect($client_id){
       \Workerman\Worker::log("client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onConnect:''\n") ;
   }
}
