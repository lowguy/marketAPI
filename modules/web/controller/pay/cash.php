<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/5/4 0004
 * Time: 上午 11:05
 */

namespace web\controller\pay;


use web\common\Controller;
use model\logic\Order;
use model\logic\User;
use web\common\Request;
use web\common\Session;
class Cash extends Controller
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
            $amount    = $orderInfo['amount'];
            if($amount){
                $pay  = $order->pay($order_no,null,$amount,4);
                if($pay){
                    $this->logResult($order_no,$amount);
                    $code = 0;
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
        fwrite($fp,"【货到付款】 订单 $order_no 现金支付 $total_fee \n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}