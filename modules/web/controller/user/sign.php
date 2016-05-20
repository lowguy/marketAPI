<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 2016/1/25
 * Time: 10:31
 */
namespace web\controller\user;

use model\logic\Code;
use web\common\Controller;
use model\logic\User;
use model\logic\Role;
use web\common\Session;
use web\common\SMS;

class Sign extends Controller{

    /**
     * 登录
     */
    private function login(){

        $status     = 1;
        $data       = 0;

        $phone      = $_POST['phone'];

        $password   = $_POST['password'];
        $device     = $_POST['device'];
        $platform   = $_POST['platform'];

        $user       = new User();
        $result     = $user->check($phone);
        if($result){
            if($result['password'] == md5($password)){
                $status = 0;
                $role   = new Role();
                $roles  = $role->getRolesByUserID($result['user_id']);
                $data = array($result['user_id'],array_column($roles, 'role_id'));
                $session    = new \web\common\Session();
                \session_start();
                $session->reID();
                $session->setUser(array('user_id'=>$result['user_id'],'phone'=>$result['phone']));
                $session->setUserRole(array_column($roles,'role_id'));
                $session->setTag();
                $user->updateDevice($result['user_id'],$device,$platform);
            }


        }

        $request    = \web\common\Request::instance();
        $request->jsonOut($status,$data);

    }

    /**
     * 用户登录, POST/GET
     * URI:/user/sign/in
     */
    public function in(){
        $request    = \web\common\Request::instance();
        if($request->isPOST()){
            $this->login();
        }
    }

    /**
     * 用户登出, POST/GET
     * URI:/user/sign/out
     */
    public function out(){
        $code       = 0;
        $session    = new \web\common\Session();
        $session->destroy();
        $request    = \web\common\Request::instance();
        $request->jsonOut($code);
    }

    /**
     * @param $phone
     * @param $code
     * @return int 0正常 1验证码错误  2超时
     */
    private function beforeAct($phone,$code){
        $status     = 2;
        $time       = time();
        $effective  = 5;
        $code_model = new Code();
        $result     = $code_model->lastCode($phone);
        $saveCode   = $result['code'];
        $created_at = $result['created_at'];
        if(($time - $created_at) < ($effective * 60)) {
            $status = $code == $saveCode ? 0 : 1;
        }
        return $status;
    }
    /**
     * 注册
     * 0成功 1验证码错误 2超时 3SQL错误
     */
    public function register(){
        $data       = 0;
        $phone      = $_POST['phone'];
        $password   = $_POST['password'];
        $device     = $_POST['device'];
        $code       = $_POST['code'];
        $status     = $this->beforeAct($phone,$code);
        if($status == 0){
            $user_model = new User();
            $res        = $user_model->add($phone,$password,$device);
            $status = 3;
            if($res['code']==0){
                $status = 0;
                $data   = $res['user_id'];
            }
        }
        $request    = \web\common\Request::instance();
        $request->jsonOut($status,$data);
    }

    /**
     * 找回密码
     * 0成功 1验证码错误 2超时 3SQL错误
     */
    public function retrievePwd(){
        $data = 0;
        $phone      = $_POST['phone'];
        $password   = $_POST['password'];
        $device     = $_POST['device'];
        $code       = $_POST['code'];
        $status     = $this->beforeAct($phone,$code);
        if($status == 0){
            $user_model = new User();
            $data = $user_model->editPwd($phone, $password, $device);
            $status = 3;
            if ($data) {
                $status = 0;
            }
        }
        $request    = \web\common\Request::instance();
        $request->jsonOut($status,$data);
    }

    /**
     * 修改密码
     */
    public function editPwd(){
        $status = 1;
        $data = array(
            'id'=>0,
            'phone'=>0
        );
        $oldpassword= $_POST['oldpassword'];
        $password   = $_POST['password'];
        $session    = new Session();
        $session->start();
        $user    = $session->getUser();
        $phone      = $user['phone'];
        $user_model       = new User();
        $result   = $user_model->check($phone);
        if($result){
            $status = 2;
            if($result['password'] == md5($oldpassword)){
                $data['id'] = $user_model->editPwd($phone,$password);
                $data['phone'] = $phone;
                $status = 0;
            }
        }
        $request    = \web\common\Request::instance();
        $request->jsonOut($status,$data);
    }

}