<?php
/**
 * 支付宝
 * User: LegendFox
 * Date: 2016/5/3 0003
 * Time: 上午 11:48
 */
namespace web\controller\pay;

use model\logic\Order;
use web\common\Controller;

class Alipay extends Controller
{
    public function notify(){
        $order_no   = $_POST['out_trade_no'];
        $total_fee  = $_POST['total_fee'];
        $trade_no   = $_POST['trade_no'];
        if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
            $this->logResult($order_no,$total_fee,$trade_no);
            $order  = new Order();
            $flag   = $order->pay($order_no,$trade_no,$total_fee,1);
            if($flag > 0){
                echo "success";
            }
        }
    }

    /**
     * 写日志，方便测试
     * 注意：服务器需要开通fopen配置
     * @param $order_no
     * @param $total_fee
     * @param $trade_no
     */
    private function logResult($order_no,$total_fee,$trade_no) {
        $file = date('Y-m-d')."pay.log";
        $fp = fopen($file,"a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"【支付宝】 订单 $order_no 实际支付 $total_fee 交易流水号 $trade_no \n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}