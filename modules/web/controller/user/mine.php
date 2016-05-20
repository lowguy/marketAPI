<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 2016-03-28
 * Time: 9:25
 */

namespace web\controller\user;

use model\logic\Order;
use web\common\Session;
use model\logic\User;
use web\common\Controller;
use web\common\Request;

class Mine extends Controller{

    public function index(){
        $data = array(
            'order'=>array(0,0,0),
            'fans'=>0
        );
        $request = Request::instance();
        if($request->isPOST()){
            $current = $this->auth();
            if($current){
                $data['fans'] = $this->number($current);
                $data['order'] = $this->order($current);
            }
        }
        $request->jsonOut(0, $data);
    }

    private function order($current){
        $data  = array(0,0,0);
        $order = new Order();
        $res   = $order->orderNum($current);
        if($res){
            foreach($res as $k => $v){
                if(1 == $v['status']){
                    $data[0] += $v['num'];
                }else if(in_array($v['status'],array(2,3))){
                    $data[1] += $v['num'];
                }else if(in_array($v['status'],array(4))){
                    $data[2] += $v['num'];
                }
            }
        }
        return $data;
    }
    private function number($current){
        $number = 0;
        $user = new User();
        $num  = $user->listLowUserNumber($current, 3);
        if($num){
            $number = $num;
        }
        return $number;
    }

    public function wallet(){
        $data = array(
            'score'=>0,
            'frozen'=>0,
            'money'=>0.00
        );

        $session    = new Session();
        $session->start();
        $userID       = $session->getUserID();
	    if($userID){
            $userModel = new User();
            $user = $userModel->getByID($userID);
            $data['score'] = $user['score'];
            $data['frozen'] = $user['frozen_score'];
            $data['money'] = $user['money'];
        }

        $request    = \web\common\Request::instance();
        $request->jsonOut(0,$data);

    }

    /**
     * @return int
     */
    private function auth()
    {
        $user = new User();
        $current = $user->isLogin();
        return $current;
    }

}
