<?php

/*
  请求解析，所有操作的统一入口
  主要的目的：解析出muxer基本信息,className和classMethod
 */
require_once dirname(__FILE__) . '/../config/PathConfig.inc.php';

class TyRequest {

    var $reqs = null;
    var $resps = null; //返回数据
    var $ip = "";
    var $errCode = 0;
    var $errMsg = '';
    var $plat = 0;
    var $muxer = null;
    var $appid = null;      //应用ID
    var $devId = null;      //设备ID
    var $merId = null;      //商户ID
    var $sign = null;       //参数校验
    var $token = null;      //用户TOKEN
    var $time = null;       //请求时间
    var $userInfo = null;   //用户信息
    var $appInfo = null;    //应用信息
    var $userInfoExt = null; //用户扩展信息

    public function __construct() {
        $this->reqs = $_REQUEST;
        //$userInfo = $this->get_user_info();
        //$userExt = $this->get_user_info_ext();
    }

    /**
     * 获取TYTOKEN
     * @return type
     */
    public function get_token() {
        if (isset($this->token) && $this->token != NULL) {
            return $this->token;
        }
        if (isset($this->reqs['token']) && $this->reqs['token'] != null) {
            $this->token = $this->reqs['token'];
            return $this->token;
        }
        else {
            //缺少必要参数tytoken，提示错误信息
//            include_once PathConfig::getConfigPath() . 'ErrorConfig.inc.php';
//            include_once PathConfig::getConfigPath() . 'enum/EnumCommonError.inc.php';
//            return ErrorConfig::getErrorInfo(EnumCommonError::TokenIsNull);
            return null;
        }
    }

    /**
     * 获取签名
     * @return type
     */
    public function get_sign() {
        if (isset($this->sign) && $this->sign != NULL) {
            return $this->sign;
        }
        if (isset($this->reqs['sign']) && $this->reqs['sign'] != null) {
            $this->sign = $this->reqs['sign'];
            return $this->sign;
        } else {
            //缺少必要参数Sign，提示错误信息
            include_once PathConfig::getConfigPath() . 'ErrorConfig.inc.php';
            include_once PathConfig::getConfigPath() . '/enum/EnumCommonError.inc.php';
            return ErrorConfig::getErrorInfo(EnumCommonError::SignError);
        }
    }

    /**
     * 获取time
     * @return type
     */
    public function get_time() {
        if (isset($this->time) && $this->time != NULL) {
            return $this->time;
        }
        if (isset($this->reqs['time']) && $this->reqs['time'] != null) {
            $this->time = $this->reqs['time'];
            return $this->time;
        } else {
            //缺少必要参数time，提示错误信息
            include_once PathConfig::getConfigPath() . 'ErrorConfig.inc.php';
            include_once PathConfig::getConfigPath() . '/enum/EnumCommonError.inc.php';
            return ErrorConfig::getErrorInfo(EnumCommonError::TimeIsNull);
        }
    }

    /**
     * 获取APPID
     * @return type
     */
    public function get_appid() {
        if (isset($this->appId) && $this->appId != NULL) {
            return $this->appId;
        }
        if (isset($this->reqs['appid']) && $this->reqs['appid'] != null) {
            $this->appId = $this->reqs['appid'];
            return $this->appId;
        } else {
            //缺少必要参数appId，提示错误信息
            include_once PathConfig::getConfigPath() . 'ErrorConfig.inc.php';
            include_once PathConfig::getConfigPath() . '/enum/EnumCommonError.inc.php';
            return ErrorConfig::getErrorInfo(EnumCommonError::AppIdIsNull);
        }
    }

    /**
     * 获取DEVID
     * @return type
     */
    public function get_devid() {
        if (isset($this->devId) && $this->devId != NULL) {
            return $this->devId;
        }
        if (isset($this->reqs['devid']) && $this->reqs['devid'] != null) {
            $this->devId = $this->reqs['devid'];
            return $this->devId;
        } else {
            //缺少必要参数appId，提示错误信息
            include_once PathConfig::getConfigPath() . 'ErrorConfig.inc.php';
            include_once PathConfig::getConfigPath() . '/enum/EnumCommonError.inc.php';
            return ErrorConfig::getErrorInfo(EnumCommonError::AppIdIsNull);
        }
    }

    public function get_channelId() {
        if (isset($this->channelId) && $this->channelId != NULL) {
            return $this->channelId;
        }
        if (isset($this->reqs['channelId']) && $this->reqs['channelId'] != null) {
            $this->channelId = $this->reqs['channelId'];
            return $this->channelId;
        } else {
            return null;
        }
    }

    /**
     *  获取用户信息
     * @return type
     */
    public function get_user_info() {

        if (isset($this->userInfo) && $this->userInfo != NULL) {
            return $this->userInfo;
        }
        //通过 url 参数中的token值
        $token = $this->get_token();
        include_once PathConfig::getConfigPath() . 'ErrorConfig.inc.php';
        include_once PathConfig::getConfigPath() . '/enum/EnumCommonError.inc.php';
        if (empty($token)) {
            //缺少必要参数tytoken，提示错误信息
//            return ErrorConfig::getErrorInfo(EnumCommonError::TokenIsNull);
            return '';
        }
        include_once PathConfig::getLibsPath() . 'session/TYuserInfo.php';
        $this->userInfo = TYuserInfo::getInfo($token);
        return $this->userInfo;
    }

    public function get_app_info() {
        if (isset($this->appInfo) && $this->appInfo != NULL) {
            return $this->appInfo;
        }
        //通过 url 参数中的AppId值
        $appid = $this->get_appid();
        include_once PathConfig::getConfigPath() . 'ErrorConfig.inc.php';
        include_once PathConfig::getConfigPath() . '/enum/EnumCommonError.inc.php';

        if (empty($appid)) {
            //缺少必要参数appId，提示错误信息
            return ErrorConfig::getErrorInfo(EnumCommonError::AppIdIsNull);
        }
        include_once PathConfig::getLibsPath() . 'session/TYappInfo.php';
        $this->appInfo = TYappInfo::getInfo($appid);
        if (empty($this->appInfo)) {
            $this->appInfo = TYappInfo::setInfo($appid);
            if (empty($this->appInfo)) {
                //缺少应用信息，提示错误信息
                return ErrorConfig::getErrorInfo(EnumCommonError::AppInfoIsNull);
            }
        }
        return $this->appInfo;
    }

    /**
     * 获取用户扩展信息
     */
    public function get_user_info_ext() {
        if (isset($this->userInfoExt) && $this->userInfoExt != NULL) {
            return $this->userInfoExt;
        }
        $token = $this->get_token();
        include_once PathConfig::getConfigPath() . 'ErrorConfig.inc.php';
        include_once PathConfig::getConfigPath() . '/enum/EnumCommonError.inc.php';
        if (empty($token)) {
            //缺少必要参数appId，提示错误信息
//            return ErrorConfig::getErrorInfo(EnumCommonError::TokenIsNull);
            return '';
        }

        include_once PathConfig::getLibsPath() . 'session/TYuserInfoExt.php';
        $this->userInfoExt = TYuserInfoExt::getInfo($token);
        return $this->userInfoExt;
    }

    /**
     * 获取用户IP
     * @return type
     */
    public function get_ip() {
        include_once PathConfig::getLibsPath() . 'utility/Ip.php';
        return Ip::get_real_ip();
    }

    /**
     * 设置用户信息缓存
     * @param type $userInfo
     * @return type
     */
    public function set_user_info($userInfo) {
        $token = $this->get_token();
        include_once PathConfig::getConfigPath() . 'ErrorConfig.inc.php';
        include_once PathConfig::getConfigPath() . '/enum/EnumCommonError.inc.php';
        if (empty($token)) {
            //缺少必要参数tytoken，提示错误信息
            return ErrorConfig::getErrorInfo(EnumCommonError::TokenIsNull);
        }
        include_once PathConfig::getLibsPath() . 'session/TYuserInfo.php';
        $this->userInfo = TYuserInfo::setInfo($token, $userInfo);
        return TRUE;
    }

    /**
     * 设置用户扩展信息缓存
     * @param type $userInfoExt
     */
    public function set_user_info_ext() {
        $token = $this->get_token();
        include_once PathConfig::getConfigPath() . 'ErrorConfig.inc.php';
        include_once PathConfig::getConfigPath() . '/enum/EnumCommonError.inc.php';
        if (empty($token)) {
            //缺少必要参数tytoken，提示错误信息
            return ErrorConfig::getErrorInfo(EnumCommonError::TokenIsNull);
        }
        $this->userInfoExt->terminal = $this->reqs['terminal'];
        $this->userInfoExt->resolution = $this->reqs['resolution'];
        $this->userInfoExt->imsiid = $this->reqs['imsiid'];
        $this->userInfoExt->os = $this->reqs['os'];
        $this->userInfoExt->ua = $this->reqs['ua'];
        $this->userInfoExt->netType = $this->reqs['netType'];
        $this->userInfoExt->ver = $this->reqs['ver'];
        $this->userInfoExt->imeiid = $this->reqs['imeiid'];
        $this->userInfoExt->pushid = $this->reqs['pushid'];
        $this->userInfoExt->platformType = $this->reqs['platformType'];
        $this->userInfoExt->promotionChannel = $this->reqs['promotionChannel'];
        $this->userInfoExt->playerVer = $this->reqs['playerVer'];
        include_once PathConfig::getLibsPath() . 'session/TYuserInfoExt.php';

        TYuserInfoExt::setInfo($token, $this->userInfoExt);
        return TRUE;
    }

    public function set_resp_info($val) {

    }

    /**
     * 设置注册短信验证码信息
     */
    public function set_register_msg($phone, $message) {
        include_once PathConfig::getLibsPath() . 'session/TYMessageInfo.php';
        $this->registerMsg = TYMessageInfo::setRegisterMsg($phone, $message);
        return TRUE;
    }

    /**
     * 获取注册短信验证码信息
     * @param
     */
    public function get_register_msg($phone) {
        if (isset($this->registerMsg) && $this->registerMsg != NULL) {
            return $this->registerMsg;
        }
        include_once PathConfig::getLibsPath() . 'session/TYMessageInfo.php';
        $this->registerMsg = TYMessageInfo::getRegisterMsg($phone);
        return $this->registerMsg;
    }

    /**
     * 设置短信验证码信息
     */
    public function set_common_msg($phone, $message) {
        $token = $this->get_token();
        if (empty($token)) {
            //缺少必要参数tytoken，提示错误信息
            //throw new Exception('TokenIsNull');
        }
        include_once PathConfig::getLibsPath() . 'session/TYMessageInfo.php';
        $this->commonMsg = TYMessageInfo::setCommonMsg($token, $phone, $message);
        return TRUE;
    }

    /**
     * 获取短信验证码信息
     * @param
     */
    public function get_common_msg($phone) {
        if (isset($this->commonMsg) && $this->commonMsg != NULL) {
            return $this->commonMsg;
        }

        $token = $this->get_token();
        include_once PathConfig::getConfigPath() . 'ErrorConfig.inc.php';
        include_once PathConfig::getConfigPath() . '/enum/EnumCommonError.inc.php';
        if (empty($token)) {
            //缺少必要参数tytoken，提示错误信息
            return ErrorConfig::getErrorInfo(EnumCommonError::TokenIsNull);
        }
        include_once PathConfig::getLibsPath() . 'session/TYMessageInfo.php';
        $this->commonMsg = TYMessageInfo::getCommonMsg($token, $phone);
        return $this->commonMsg;
    }

}

