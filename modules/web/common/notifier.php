<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/21 0021
 * Time: 上午 10:44
 */

namespace web\common;

class Notifier{

    private $push = null;

    public static $MESSAGE_NEW_ORDER = 1;
    public function __construct(){
        $this->push = new \jpush('15c99a4c4e80f59e0efd97db', '7ee853e15ed0e5a09f0186d4');
    }

    /**
     * @param $user
     * @param $msg
     * @param $pairs
     * @param $type
     */
    public function sendToUser($user, $msg, $pairs, $type){
        $pairs['type']  =  $type;
        $user['IOS'] && $this->sendToIOSUSer($user['IOS'],$msg,$pairs);
        $user['Android'] && $this->sendToAndroidUSer($user['Android'],$msg,$pairs);
    }

    /**
     * @param $user
     * @param $msg
     * @param $pairs
     */
    private function sendToIOSUSer($user, $msg, $pairs){
        $this->push->push()
            ->setPlatform(array('ios'))
            ->addAlias($user)
            ->addIosNotification($msg, 'iOS sound', \jpush::DISABLE_BADGE, true, 'iOS category', $pairs)
            ->setOptions(100000, 86400, null, TRUE)
            ->send();
    }

    /**
     * @param $user
     * @param $msg
     * @param $pairs
     */
    private function sendToAndroidUSer($user, $msg, $pairs){
        $this->push->push()
            ->setPlatform(array('android'))
            ->addAlias($user)
            ->setMessage($msg,'小小家生活助手','String',$pairs)
            ->send();
    }

    /**
     * @param $msg
     * @param $pairs
     * @param $type
     */
    public function sendToAll($msg, $pairs, $type){
        $paris['type']  = $type;
        $this->push->push()
            ->setPlatform(array('android'))
            ->addAllAudience()
            ->addIosNotification($msg, 'iOS sound', \jpush::DISABLE_BADGE, true, 'iOS category', $pairs)
            ->addAndroidNotification($msg,null, null, $pairs)
            ->setOptions(100000, 86400, null, TRUE)
            ->send();
    }

}