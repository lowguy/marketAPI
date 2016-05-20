<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/5/4 0004
 * Time: 下午 3:44
 */

namespace web\controller\user;

use web\common\Controller;
use web\common\Request;
use web\common\Session;

class Card extends \web\common\Controller
{
    public function index(){
        $code       = 100;
        $request    = Request::instance();
        if($request->isPOST()){
            $code   = $this->bund();
        }
        $request->jsonOut($code);
    }

    private function bund(){
        $code    = 1;
        $session = new Session();
        $session->start();
        $user_id = $session->getUserID();
        if($user_id){
            $code      = 2;
            $name      = trim($_POST['name']);
            $account   = trim($_POST['account']);
            $bank      = trim($_POST['bank']);
            $type      = intval($_POST['type']);
            $bank      = $bank ? $bank : '支付宝';
            if($name && $account){
                $card  = new \model\logic\Card();
                $data  = $card->info($user_id,$account);
                if(empty($data)){
                    $code  = 3;
                    $res   = $card->bund($user_id,$name,$account,$bank,$type);
                    if($res){
                        $code = 0;
                    }
                }
            }
        }
        return $code;
    }

    public function lists(){
        $code       = 100;
        $data       = array();
        $request    = Request::instance();
        if($request->isPOST()){
            $res   = $this->listsCard();
            $code  = $res['code'];
            if($code == 0){
                foreach($res['data'] as $k => $v){
                    if($v['type']==1){
                        $res['data'][$k]['account'] = preg_replace('/\d/', '*', $v['account'], 12);
                    }elseif($v['type']==2){
                        $res['data'][$k]['account'] = $this->halfReplace($v['account']);
                    }
                }

                $data = empty($res['data']) ? array() : $res['data'];
            }
        }
        $request->jsonOut($code,$data);
    }

    private function halfReplace($str){
        $len = intval(strlen($str)/2);
        return substr_replace($str,str_repeat('*',$len),floor(($len)/2),$len);
    }

    private function listsCard(){
        $res    = array(
            'code' => 1,
            'data' =>array(),
        );
        $session = new Session();
        $session->start();
        $user_id = $session->getUserID();
        if($user_id){
            $res['code']    = 2;
            $card  = new \model\logic\Card();
            $res['data']   = $card->lists($user_id);
            if($res['data']){
                $res['code'] = 0;
            }
        }
        return $res;
    }

    public function delete(){
        $code       = 100;
        $request    = Request::instance();
        if($request->isPOST()){
            $code    = 1;
            $session = new Session();
            $session->start();
            $user_id = $session->getUserID();
            if($user_id){
                $card_id    = $_POST['id'];
                $card       = new \model\logic\Card();
                $res        = $card->modify($user_id,-1,$card_id);
                $code       = $res ? 0 : 2;
            }
        }
        $request->jsonOut($code);
    }

    public function status(){
        $code       = 100;
        $request    = Request::instance();
        if($request->isPOST()){
            $code    = 1;
            $session = new Session();
            $session->start();
            $user_id = $session->getUserID();
            if($user_id){
                $card_id    = $_POST['id'];
                $card       = new \model\logic\Card();
                $res        = $card->status($user_id,1,$card_id);
                $code       = $res ? 0 : 3;
            }
        }
        $request->jsonOut($code);
    }
}