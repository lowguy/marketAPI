<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 2016/1/20
 * Time: 16:07
 */
namespace web\common;

class SMS{
    private $accountSid     = 'aaf98f894f402f15014f43985a9c0214';
    private $accountToken   = '792f8ba2180c45bcb10afbbdef2f4730';
    private $appId          = '8a48b55153eae5110153ee6d6f0703ff';
    private $serverIP       = 'app.cloopen.com';
    private $serverPort     = '8883';
    private $softVersion    = '2013-12-26';

    public function sendTemplateSMS($to,$datas,$tempId){
        $rest = new REST($this->serverIP,$this->serverPort,$this->softVersion);
        $rest->setAccount($this->accountSid,$this->accountToken);
        $rest->setAppId($this->appId);

        // 发送模板短信
        $result = $rest->sendTemplateSMS($to,$datas,$tempId);
        return $result;
    }
}