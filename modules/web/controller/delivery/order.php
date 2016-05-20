<?php
/**
 * Created by PhpStorm.
 * User: zhanghui
 * Date: 16/4/27
 * Time: 16:18
 */
namespace web\controller\delivery;

use web\common\Request;
use web\common\Session;

class Order{

    public function dispatch(){
        $code = 100;
        $data = array();
        $request = \web\common\Request::instance();
        $session = new Session();
        $session->start();
        if($request->isPOST()){
            $role = $session->getUserRole();
            if(in_array(101, $role)){
                $userID = $session->getUserID();
                $orderModel = new \model\logic\Order();
                $data = $orderModel->getDeliveryOrder($userID);
                $code = 0;
            }
            else{
                $code = 1;
            }
        }
        $request->jsonOut($code, $data);
    }

    public function accept(){
        $code = 100;
        $request = \web\common\Request::instance();
        $session = new Session();
        $session->start();
        if($request->isPOST()){
            $order_id   = $_POST['id'];
            $role = $session->getUserRole();
            if(in_array(101, $role)){
                $userID = $session->getUserID();
                $orderModel = new \model\logic\Order();
                $code = $orderModel->setDeliveryOrderStatus($userID,$order_id,1);
            }
            else{
                $code = 1;
            }
        }
        $request->jsonOut($code);
    }

    public function reject(){
        $code = 100;
        $request = \web\common\Request::instance();
        $session = new Session();
        $session->start();
        if($request->isPOST()){
            $order_id   = $_POST['id'];
            $role = $session->getUserRole();
            if(in_array(101, $role)){
                $userID = $session->getUserID();
                $orderModel = new \model\logic\Order();
                $code = $orderModel->setDeliveryOrderStatus($userID,$order_id,0);
            }
            else{
                $code = 1;
            }
        }
        $request->jsonOut($code);
    }

    public function finish(){
        $code = 100;
        $request = \web\common\Request::instance();
        $session = new Session();
        $session->start();
        if($request->isPOST()){
            $order_id   = $_POST['id'];
            $role = $session->getUserRole();
            if(in_array(101, $role)){
                $userID = $session->getUserID();
                $orderModel = new \model\logic\Order();
                $code = $orderModel->setDeliveryOrderStatus($userID,$order_id,2);
            }
            else{
                $code = 1;
            }
        }
        $request->jsonOut($code);
    }

    public function pick(){
        $code = 100;
        $request = \web\common\Request::instance();
        $session = new Session();
        $session->start();
        if($request->isPOST()){
            $order_id   = intval($_POST['id']);
            $goods      = json_decode($_POST['goods'],true);
            $role = $session->getUserRole();
            if(in_array(101, $role)){
                $userID = $session->getUserID();
                $orderModel = new \model\logic\Order();
                $res = $orderModel->verifyDeliveryOrder($order_id,$userID);
                if(!empty($res)){
                    $code = $orderModel->setOrderProduct($order_id,$goods);
                }
            }
            else{
                $code = 1;
            }
        }
        $request->jsonOut($code);
    }

    public function statistics(){
        $code = 100;
        $data = array(
            'order'=>array(),
            'amount'=>array()
        );
        $request = Request::instance();
        $session = new Session();
        $session->start();
        if($request->isPOST()){
            $role = $session->getUserRole();
            if(in_array(101, $role)){
                $userID = $session->getUserID();
                $market     = intval($_POST['id']);
                $year       = intval($_POST['year']);
                $month      = intval($_POST['month']);
                $monthly    = intval($_POST['monthly']);
                $page       = intval($_POST['page']);
                $page       = $page ? $page : 1;
                $day        = intval($_POST['day']);
                $page = $page ? $page : 1;
                if($userID){
                    $code = 0;
                    $orderModel = new \model\logic\Order();
                    if(1 == $monthly){
                        $m_res = $orderModel->deliveryStatistics($userID,$market,$year,$month,null,$page);
                        $data['amount']['monthly'] = $m_res['number'];
                    }

                    $d_res = $orderModel->deliveryStatistics($userID,$market,$year,$month,$day,$page);
                    if(1 == $page){
                        $data['amount']['daily'] = $d_res['number'];
                    }
                    $data['order'] = $d_res['data'];
                }else{
                    $code = 1;
                }
            }
        }
        $request->jsonOut($code,$data);
    }
}