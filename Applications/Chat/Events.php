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

       //flash安全策略调用
       if ($message == "<policy-file-request/>"){
           $val = '<cross-domain-policy> 
                    <allow-access-from domain="*" /> 
                    </cross-domain-policy>\0';
           return Gateway::sendToCurrentClient($val);
       }

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
            // 客户端登录 message格式: {type:login, name:xx, liveId:1} ，
            case 'login':
                // 判断是否有直播Id
                if(!isset($message_data['liveId']))
                {
                    throw new \Exception("\$message_data['liveId'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }
                
                // 把liveId,uid放入session中
                $liveId = $message_data['liveId'];
                $uid = '';
                $_SESSION['liveId'] = $liveId;
                if(isset($message_data['uid']) && $message_data['uid'] != ''){
                    $_SESSION['uid'] = $message_data['uid'];
                    Gateway::bindUid($client_id,$message_data['uid']);
                }
                $count = Gateway::getAllClientCount();
                $new_message = array(
                    'type'=>'l_ok',
                    'client_id'=>$client_id,
                    'uid'=>$message_data['uid'],
                    'time'=>date('Y-m-d H:i:s'),
                    'count' => $count
                );
                Gateway::joinGroup($client_id, $liveId);
                Gateway::sendToCurrentClient(json_encode($new_message));
                return;
            //手机号登录后绑定UID
            case 'bindUid':
                if(!isset($message_data['uid'])){
                    throw new \Exception("\$message_data['uid'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }
                Gateway::bindUid($client_id,$message_data['uid']);
                $new_message =  array(
                    'type'=>'b_ok',
                    'client_id'=>$client_id,
                    'uid'=>$message_data['uid'],
                    'time'=>date('Y-m-d H:i:s')
                );
                Gateway::sendToCurrentClient(json_encode($new_message));
                return;
            //删除评论
            case 'delComment':
                if(!isset($message_data['commentId'])){
                    throw new \Exception("\commentId  not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }
                $liveId = $_SESSION['liveId'];
                $new_message = array(
                    'cmd'=> 'del',
                    'content' => array('commentId'=> $message_data['commentId'])
                );
                Gateway::sendToGroup($liveId,json_encode($new_message));
                return;
            //派发评论
            case 'sendComment':
                if(!isset($message_data['uid'])){
                    throw new \Exception("\ uid not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }
                $new_message = array(
                    'cmd' => 'send',
                    'content' => array('info' => $message_data['info'])
                );
                Gateway::sendToUid($message_data['uid'],json_encode($new_message));
                return;
            //发送评论
            case 'say':
                if(!isset($message_data['liveId']) || !isset($message_data['commentId'])){
                    throw new \Exception("\commentId or liveId not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }

                $liveId = $message_data['liveId'];//评论频道ID
                $commentId = $message_data['commentId'];//评论ID
                $nickName = '';//评论昵称
                $info = $message_data['info'];//评论内容（入库JSON数据)
                if(isset($message_data['nickName'])){
                    $nickName = $message_data['nickName'];
                }
                $new_message = array(
                    'cmd' => 'add',
                    'content' => array(
                            'info' => $info,
                            'commentId' => $commentId,
                            'nickName' => $nickName
                        )
                );
                Gateway::sendToGroup($liveId,json_encode($new_message));
                return;
            //评论回复
            case 'reply':
                if(!isset($message_data['commentId']) || !isset($message_data['uid'])){
                    throw new \Exception("\commentId  not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }
                $liveId = $_SESSION['liveId'];
                $uid = $message_data['uid'];
                //所有频道内用户均会接收到
                $all_message = array(
                    'cmd' => 're',
                    'content' => array('info' => array(
                        'comment' => $message_data['info'],
                        'nickName' => $message_data['nickName'],
                        'commentId' => $message_data['commentId']
                    ))
                );
                Gateway::sendToGroup($liveId,json_encode($all_message));
                //被回复的评论人可接收到
                $u_message = array(
                    'cmd' => 're_pop',
                    'content' => array('info' => array(
                        'comment' => $message_data['info'],
                        'nickName' => $message_data['nickName'])
                    )
                );
                Gateway::sendToUid($uid,json_encode($u_message));
                return;
            case 'adminLogin':
                if(!isset($message_data['liveId']) || !isset($message_data['uid']))
                {
                    throw new \Exception("\liveId or uid  not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }

                // 把liveId,uid放入session中
                $liveId = $message_data['liveId'];
                $uid = '';
                $_SESSION['liveId'] = $liveId;
                $_SESSION['uid'] = $message_data['uid'];
                $_SESSION['adminType'] = $message_data['adminType'];
                //写redis
                $redis = ICache::getRedis('api');
                $redis->hashSet("admin-".$liveId."-".$message_data['adminType'],array($message_data['uid']=> 1));
                \Workerman\Worker::log("addRedis:admin-".$liveId."-".$message_data['adminType']." value: ".$message_data['uid']);
                $new_message = array(
                    'type'=>$message_data['type'],
                    'client_id'=>$client_id,
                    'uid'=> $message_data['uid'],
                    'time'=>date('Y-m-d H:i:s')
                );
                //return Gateway::sendToAll(json_encode($new_message));
                Gateway::bindUid($client_id,$message_data['uid']);
                Gateway::joinGroup($client_id, $liveId);
                Gateway::sendToCurrentClient(json_encode($new_message));
                return;
            //管理后台控制命令
            case 'out':
                Gateway::unbindUid($client_id,$message_data['uid']);
                $new_message = array(
                    'type'=>'out_ok',
                    'client_id'=>$client_id,
                    'time'=>date('Y-m-d H:i:s')
                );
                Gateway::sendToCurrentClient(json_encode($new_message));
                return;
            case 'push':
                $liveId = $_SESSION['liveId'];
                return Gateway::sendToGroup($liveId,$message_data['info']);
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
       \Workerman\Worker::log ("client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n");
       
       // 如果是评论助理或嘉宾,则清除缓存中的数据
       if($_SESSION['adminType'] == 'assistant' || $_SESSION['adminType'] == 'host' || $_SESSION['adminType'] == 'super')
       {
            $redis = ICache::getRedis('api');
            $redis->hashDel("admin-".$_SESSION['liveId']."-".$_SESSION['adminType'],$_SESSION['uid']);
           \Workerman\Worker::log("DelRedis:admin-".$_SESSION['liveId']."-".$_SESSION['adminType']." value: ".$_SESSION['uid']);
       }
   }
  
}
