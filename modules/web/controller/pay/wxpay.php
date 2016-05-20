<?php
/**
 * 微信支付
 * User: LegendFox
 * Date: 2016/5/3 0003
 * Time: 上午 11:48
 */

namespace web\controller\pay;

use model\logic\Order;
use web\common\Controller;

class Wxpay extends Controller
{
    public function notify(){
        $xmlObj = simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA']);
        $result_code = $xmlObj->result_code;
        $trace_type  = $xmlObj->trade_type;
        $total_fee   = $xmlObj->total_fee;
        $transaction_id = $xmlObj->transaction_id;
        $out_trade_no = $xmlObj->out_trade_no;
        if($trace_type == "APP" && $result_code == 'SUCCESS') {
            $this->logResult($out_trade_no,$total_fee,$transaction_id);
            $order  = new Order();
            $flag   = $order->pay($out_trade_no,$transaction_id,$total_fee,2);
            if($flag > 0){
                echo "<xml>
                  <return_code><![CDATA[SUCCESS]]></return_code>
                  <return_msg><![CDATA[OK]]></return_msg>
              </xml>";
            }
        }else if($result_code == 'FAIL'){
            $this->errorLogResult($xmlObj->err_code,$xmlObj->err_code_des);
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
        fwrite($fp,"【微信支付】 订单 $order_no 实际支付 $total_fee 交易流水号 $trade_no \n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 写日志，方便测试
     * 注意：服务器需要开通fopen配置
     * @param $code
     * @param $error_msg
     */
    private function errorLogResult($code,$error_msg) {
        $file = date('Y-m-d')."error_pay.log";
        $fp = fopen($file,"a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"【微信支付】 错误码： $code 错误信息： $error_msg  \n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

}