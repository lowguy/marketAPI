<?php
/**
 * 积分支付
 * User: LegendFox
 * Date: 2016/5/4 0004
 * Time: 上午 9:47
 */

namespace web\controller\pay;


use model\logic\Order;
use model\logic\User;
use web\common\Controller;
use web\common\Request;
use web\common\Session;

class Integral extends Controller
{
    public function index(){
        $code    = 100;
        $request = Request::instance();
        if($request->isPOST()){
            $code = $this->pay();
        }
        $request->jsonOut($code);
    }

    private function pay(){
        $code = 1;
        $session = new Session();
        $session->start();
        $user_id = $session->getUserID();
        if($user_id){
            $code      = 2;
            $order_no  = $_POST['order_no'];
            $order     = new Order();
            $orderInfo = $order->getOrderByNo($user_id,$order_no,1);
            $amount    = ceil($orderInfo['amount']*10);
            if($amount){
                $code   = 3;
                $user   = new User();
                $score  = $user->score($user_id);
                if($score >= $amount){
                    $pay  = $order->pay($order_no,null,$orderInfo['amount'],3);
                    $score= $user->updateScore($user_id,(0-$amount));
                    if($pay && $score){
                        $this->logResult($order_no,$amount);
                        $code = 0;
                    }
                }
            }

        }
        return $code;
    }
    /**
     * 写日志，方便测试
     * 注意：服务器需要开通fopen配置
     * @param $order_no
     * @param $total_fee
     */
    private function logResult($order_no,$total_fee) {
        $file = date('Y-m-d')."pay.log";
        $fp = fopen($file,"a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"【积分支付】 订单 $order_no 使用积分 $total_fee \n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}