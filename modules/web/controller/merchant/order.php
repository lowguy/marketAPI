<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/5/16 0016
 * Time: 上午 10:29
 */

namespace web\controller\merchant;

use model\logic\User;
use web\common\Controller;
use web\common\Request;

class Order extends Controller
{
    public function statistics(){
        $res = array(
            'order'=>array(),
            'amount'=>array()
        );
        $code       = 1;
        $user_id    = $this->auth();
        if($user_id){
            $code = 0;
            $market     = intval($_POST['id']);
            $year       = intval($_POST['year']);
            $month      = intval($_POST['month']);
            $monthly    = intval($_POST['monthly']);
            $page       = intval($_POST['page']);
            $page       = $page ? $page : 1;
            $day        = intval($_POST['day']);
            $order      = new \model\logic\Order();
            if(1 == $monthly){
                $m_res = $order->statistics($market,$user_id,$year,$month);
                $res['amount']['monthly'] = $m_res['amount'];
            }

            $d_res = $order->statistics($market,$user_id,$year,$month,$day,$page);
            if(1 == $page){
                $res['amount']['daily'] = $d_res['amount'];
            }
            $res['order'] = $d_res['order'];
        }
        $request = Request::instance();
        $request->jsonOut($code,$res);
    }

    /**
     * 2 商户信息错误 1未登录 0 登录
     */
    private function auth(){
        $user    = new User();
        return $user->isLogin();
    }
}