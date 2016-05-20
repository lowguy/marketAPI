<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/22 0022
 * Time: 上午 11:40
 */

namespace web\controller\user;


use model\logic\MarketUser;
use model\logic\User;
use web\common\Controller;
use web\common\Request;

class Apply extends Controller
{
    private $_code = 100;
    private $_market_id;

    /**
     * 用户申请
     */
    public function index(){
        $request    = Request::instance();
        if($request->isPOST()){
            $this->_market_id  = intval($_POST['id']);
            $role_id    = intval($_POST['role']);
            $auth       = $this->auth();
            if($this->_code == 0){
                $this->apply($auth,$this->_market_id,$role_id);
            }
        }
        $request->jsonOut($this->_code);
    }

    /**
     * 兑现
     */
    public function cash(){
        $request    = Request::instance();
        if($request->isPOST()){
            $this->_market_id  = intval($_POST['id']);
            $role_id    = intval($_POST['role']);
            $auth       = $this->auth();
            if($this->_code == 0){
                $this->apply($auth,$this->_market_id,$role_id);
            }
        }
        $request->jsonOut($this->_code);
    }

    /**
     * @param $user_id
     * @param $market_id
     * @param $role_id
     */
    private function apply($user_id,$market_id,$role_id){
        $user   = new MarketUser();
        $res    = $user->apply($user_id,$market_id,$role_id);
        $this->_code    = $res ? 0 : 100;
        return;
    }

    /**
     * 0 信息合法 1 未登录  2 正在审核 3 已成功申请
     */
    private function auth(){
        $user       = new User();
        $user_id    = $user->isLogin();
        $this->_code = 1;
        if($user_id){
            $this->_code  = 0;
            $market_user = new MarketUser();
            $res    = $market_user->marketUserInfo($this->_market_id,$user_id);
            if($res){
                if($res['status'] == 0){
                    $this->_code  = 2;
                }else if($res['status'] == 1){
                    $this->_code  = 3;
                }else if($res['status'] == 2){
                    $this->_code  = 4;
                }
            }
        }
        return $user_id;
    }
}