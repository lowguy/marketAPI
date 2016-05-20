<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/5/5 0005
 * Time: 下午 2:50
 */

namespace web\controller\user;


use model\logic\Card;
use model\logic\User;
use web\common\Controller;
use web\common\Request;
use web\common\Session;

class Balance extends Controller
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
                $user_score = $user->score($user_id);
                $score      = $user_score['score']%10;
                $data['amount']     = ($user_score['score'] - $score)/10;
                $data['score']      = $user_score['score'] - $score;
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
                $score    = $_POST['score'];
                $balance  = new \model\logic\Balance();
                $code = $balance->score($user_id,$score);
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
                    if($v['type']==1){
                        $data[$k]['account'] = preg_replace('/\d/', '*', $v['account'], 12);
                    }elseif($v['type']==2){
                        $data[$k]['account'] = $this->halfReplace($v['account']);
                    }
                    $data[$k]['score'] = intval($v['amount'] * 10);
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