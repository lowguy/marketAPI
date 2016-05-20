<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/23 0023
 * Time: 下午 1:41
 */

namespace web\controller\merchant;


use model\logic\MarketUser;
use model\logic\User;
use web\common\Controller;
use web\common\Request;

class Shop extends Controller
{
    private $_code      = 100;
    private $_role_id   = 100;
    private $_user_id;
    private $_market_id;

    public function mine(){
        $data = array(
            'status'=>0,
            'balance'=>0.00
        );
        $request    = Request::instance();
        if($request->isPOST()){
            $this->_market_id     = $_POST['id'];
            $this->auth();
            if($this->_code == 0){
                $market_user   = new MarketUser();
                $open = $market_user->open($this->_market_id,$this->_user_id,$this->_role_id);
                $data['status'] = ($open == -1) ? 0 : $open;
            }
        }
        $request->jsonOut($this->_code,$data);
    }

    /**
     * 设置状态
     */
    public function setStatus(){
        $request  = Request::instance();
        $res      = 0;
        if($request->isPOST()){
            $this->_market_id  = $_POST['id'];
            $status            = $_POST['status'];
            $this->auth();
            if($this->_code == 0){
                $this->_code = 2;
                $market_user   = new MarketUser();
                $open   = $market_user->open($this->_market_id,$this->_user_id,$this->_role_id);
                if($open != -1){
                    $this->_code = 1;
                    $data   = $market_user->setOpenStatus($this->_market_id,$this->_user_id,$status,$this->_role_id);
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
     * 商户营业时间
     */
    public function time(){
        $request    = Request::instance();
        $data = 0;
        if($request->isPOST()){
            $this->_market_id  = $_POST['id'];
            $this->auth();
            if($this->_code == 0){
                $market_user   = new MarketUser();
                $data   = $market_user->time($this->_market_id,$this->_user_id);
                if($data){
                    $this->_code = 0;
                }
            }
        }
        $request->jsonOut($this->_code,$data);
    }

    /**
     * 设置商户营业时间
     */
    public function setTime(){
        $request    = Request::instance();
        $data = '';
        if($request->isPOST()){
            $this->_market_id  = $_POST['id'];
            $start             = $_POST['start'];
            $close             = $_POST['close'];
            $this->auth();
            if($this->_code == 0){
                $this->_code = 100;
                $market_user   = new MarketUser();
                $data   = $market_user->setTime($this->_market_id,$this->_user_id,$start,$close,$this->_role_id);
                if($data){
                    $this->_code = 0;
                }
            }
        }
        $request->jsonOut($this->_code,$data);
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