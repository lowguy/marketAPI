<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/26 0026
 * Time: 上午 9:51
 */
namespace web\controller\delivery;

use model\logic\MarketUser;
use model\logic\User;
use web\common\Controller;
use web\common\Request;

class Home extends Controller
{
    private $_code      = 100;
    private $_role_id   = 101;
    private $_user_id;

    /**
     * 商铺状态
     */
    public function open(){
        $request    = Request::instance();
        $data = 0;
        if($request->isPOST()){
            $market_id  = $_POST['id'];
            $this->auth();
            if($this->_code == 0){
                $market_user   = new MarketUser();
                $open = $market_user->open($market_id,$this->_user_id,$this->_role_id);
                $data = ($open == -1) ? 0 : $open;
            }
        }
        $request->jsonOut($this->_code,$data);

    }

    /**
     * 设置状态
     */
    public function setStatus(){
        $request    = Request::instance();
        $res     = 0;
        if($request->isPOST()){
            $market_id  = $_POST['id'];
            $status     = $_POST['status'];
            $this->auth();
            if($this->_code == 0){
                $this->_code = 2;
                $market_user   = new MarketUser();
                $open = $market_user->open($market_id,$this->_user_id,$this->_role_id);
                if($open != -1){
                    $this->_code = 1;
                    $data   = $market_user->setOpenStatus($market_id,$this->_user_id,$status,$this->_role_id);
                    if($data == 0){
                        $res    = $status;
                        $this->_code = 0;
                    }
                }
            }
        }
        $request->jsonOut($this->_code,$res);
    }


    /**
     * 2 商户信息错误 1未登录 0 登录
     */
    private function auth(){
        $this->_code    = 1;
        $user           = new User();
        $this->_user_id = $user->isLogin();
        $this->_code = $this->_user_id ? 0 : 1;
    }
}