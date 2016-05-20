<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/5/5 0005
 * Time: 下午 2:50
 */

namespace web\controller\user;


use model\logic\Balance;
use model\logic\Card;
use model\logic\User;
use web\common\Controller;
use web\common\Request;
use web\common\Session;

class Score extends Controller
{
    public function info(){
        $code       = 100;
        $data       = array();
        $request    = Request::instance();
        if($request->isPOST()){
            $code    = 1;
            $session = new Session();
            $session->start();
            $user_id = $session->getUserID();
            if($user_id){
                $code = 0;
                $card = new Card();
                $data['card']  = $card->getDefault($user_id);
                $user = new User();
                $type = $_POST['type'];
                $user_info  = (0 == $type) ? $user->score($user_id) : $user->balance($user_id);
                if(0 == $type){
                    $score      = $user_info['score']%10;
                    $data['amount']     = ($user_info['score'] - $score)/10;
                    $data['score']      = $user_info['score'] - $score;
                }else{
                    $data['amount']     = intval($user_info['money']);
                    $data['score']      = intval($user_info['money']);
                }

            }
        }
        $request->jsonOut($code,$data);
    }

    /**
     * 兑现
     */
    public function apply(){
        $code       = 100;
        $request    = Request::instance();
        if($request->isPOST()){
            $code    = 1;
            $session = new Session();
            $session->start();
            $user_id = $session->getUserID();
            if($user_id){
                $type     = $_POST['type'];
                $score    = $_POST['score'];
                $user     = new User();
                $balance  = new Balance();
                if(0 == $type){
                    $userInfo = $user->score($user_id);
                    $code = $userInfo['score'] >= $score ? $balance->score($user_id,$score) : 3;
                }else{
                    $userInfo = $user->balance($user_id);
                    $code = $userInfo['money'] >= $score ? $balance->balance($user_id,$score) : 3;
                }
            }
        }
        $request->jsonOut($code);
    }

    public function lists(){
        $code       = 100;
        $data       = array();
        $request    = Request::instance();
        if($request->isPOST()){
            $code    = 1;
            $session = new Session();
            $session->start();
            $user_id = $session->getUserID();
            if($user_id){
                $code = 0;
                $page = intval($_POST['page']);
                $balance = new Balance();
                $data  = $balance->scoreLists($user_id,$page);
                foreach($data as $k => $v){
                    if($v['c_type']==1){
                        $data[$k]['account'] = preg_replace('/\d/', '*', $v['account'], 12);
                    }elseif($v['c_type']==2){
                        $data[$k]['account'] = $this->halfReplace($v['account']);
                    }
                    $data[$k]['score'] = ($v['type'] == 1) ? intval($v['amount'] * 10) : intval($v['amount']);
                }
            }
        }
        $request->jsonOut($code,$data);
    }

    private function halfReplace($str){
        $len = intval(strlen($str)/2);
        return substr_replace($str,str_repeat('*',$len),floor(($len)/2),$len);
    }
}