<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/7 0007
 * Time: 上午 11:10
 */
namespace web\controller\sms;

use model\logic\User;
use model\logic\Code;
use web\common\Controller;
use web\common\SMS;

class Index extends Controller{

    private $_sendCode;
    private $_senario;
    private $_phone;
    private $_effectiveTime = 5;    //有效时间 （分钟）
    private $_templateID    = 78372;//短信模板ID
    private $_limitNum      = 5;    //每日发送次数上限

    public function __construct(){

        $this->generateCode();

    }
    /**
     * 发送验证码
     * @return int 0成功 2失败
     */
    private function sendCode(){
        $sms    = new SMS();
        $result = $sms->sendTemplateSMS($this->_phone,array($this->_sendCode,$this->_effectiveTime),$this->_templateID);
        $status = 2;
        if($result != NULL && $result->statusCode==0) {
            $status = 0;
        }
        return $status;
    }

    /**
     * 生成验证码
     * @param int $length
     * @return int
     */
    private function generateCode($length = 4) {
        $this->_sendCode = rand(pow(10,($length-1)), pow(10,$length)-1);
    }

    /**
     * 发送之后
     */
    private function sendCodeAfter(){
        $code_model = new Code();
        $code_model->add($this->_phone, $this->_sendCode,$this->_senario);
    }

    /**
     * 发送验证码每日上限
     * @return boolean
     */
    private function codeLimitPerDay(){
        $flag       = true;
        $code_model = new Code();
        $limit      = $code_model->codeLimitPerDay($this->_phone);
        if($limit   >= $this->_limitNum){
            $flag   = false;
        }
        return $flag;
    }

    /**
     * 发送前检验
     * @return int 0未注册 1已注册 2已达每日上限
     */
    private function sendCodeBefore(){
        $status = 2;
        if($this->codeLimitPerDay()){
            $user_model = new User();
            $result = $user_model->check($this->_phone);
            if(!$result){
                $status = 0;
            }else{
                $status = 1;
            }
        }
        return $status;
    }

    /**
     * 注册时获取验证码
     * 0获取成功 1未注册 2已达每日上限
     */
    public function sendRegister(){
        $this->_phone   = $_POST['phone'];
        $this->_senario = "Register";
        $this->_templateID = 81310;//注册模板
        $status         = $this->sendCodeBefore();
        $status         = ($status == 0) ? $this->sendCode() : $status;
        $status         == 0 && $this->sendCodeAfter();
        $request        = \web\common\Request::instance();
        $request->jsonOut($status);
    }

    /**
     * 找回密码时
     * 0获取成功 1未注册 2已达每日上限
     */
    public function sendRetrieve(){
        $this->_phone   = $_POST['phone'];
        $this->_senario = "Retrieve";
        $this->_templateID = 81310;//找回密码模板
        $status         = $this->sendCodeBefore();
        $status         = ($status == 1) ? $this->sendCode() : (($status == 0) ? 1 : $status) ;
        ($status == 0) && $this->sendCodeAfter();
        $request        = \web\common\Request::instance();
        $request->jsonOut($status);
    }

    /**
     * 核对验证码
     * 0成功 1验证码错误 2超时
     */
    public function verifySMS(){
        $status     = 2;
        $time       = time();
        $phone      = $_POST['phone'];
        $code       = $_POST['code'];
        $code_model = new Code();
        $res        = $code_model->lastCode($phone);
        $created_at = $res['created_at'];
        if(($time - $created_at) < ($this->_effectiveTime * 60)){
            $saveCode   = $res['code'];
            $status     = 1;
            if($code == $saveCode){
                $status = 0;
            }
        }
        $request    = \web\common\Request::instance();
        $request->jsonOut($status);
    }



}