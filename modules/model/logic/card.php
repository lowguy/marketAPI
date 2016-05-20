<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/5/4 0004
 * Time: 下午 3:42
 */

namespace model\logic;

use model\database\Table;
class Card
{
    public function bund($user_id,$name,$account,$phone,$bank,$type){
        $table = new Table('card');
        $data  = array(
            'user_id'   =>$user_id,
            'name'      =>$name,
            'account'   =>$account,
            'phone'     =>$phone,
            'bank'      =>$bank,
            'type'      =>$type
        );
        $res     = $table->add($data);
        if($res){
            $card_id = $table->lastID();
            $this->status($user_id,1,$card_id);
        }
        return $res;
    }

    public function status($user_id,$status,$card_id){
        $table  = new Table('card');
        $table->edit(array('status'=>0), " WHERE user_id = ? AND status != ?", array($user_id,-1));
        $data   = array(
            'status'=>$status
        );
        $filter = " WHERE user_id = ? AND card_id = ?";
        $params = array($user_id,$card_id);
        return $table->edit($data, $filter, $params);
    }

    public function modify($user_id,$status,$card_id){
        $table  = new Table('card');
        $this_status    = $table->get('WHERE user_id = ? AND card_id = ?',array($user_id,$card_id),array('status'));
        if($this_status == 1){
            $list = $this->lists($user_id);
            if($list){
                $this->status($user_id,1,$list['0']['card_id']);
            }
        }
        $data   = array(
            'status'=>$status
        );
        $filter = " WHERE user_id = ? AND card_id = ?";
        $params = array($user_id,$card_id);
        return $table->edit($data, $filter, $params);
    }

    public function lists($user_id){
        $table  = new Table('card');
        $filter = " WHERE user_id = ? AND status != ? ORDER BY status DESC";
        $params = array($user_id,-1);
        return $table->lists($filter,$params);
    }

    public function info($user_id,$account){
        $table  = new Table('card');
        $filter = " WHERE user_id = ? AND account = ? ";
        $params = array($user_id,$account);
        return $table->get($filter,$params);
    }

    public function getDefault($user_id){
        $data   = array();
        $table  = new Table('card');
        $filter = " WHERE user_id = ? AND status = ?";
        $params = array($user_id,1);
        $res    = $table->get($filter,$params);
        if($res){
            if($res['type']==1){
                $res['account'] = preg_replace('/\d/', '*', $res['account'], 12);
            }elseif($res['type']==2){
                $res['account'] = $this->halfReplace($res['account']);
            }
            $data = $res;
        }
        return $data;
    }
    private function halfReplace($str){
        $len = intval(strlen($str)/2);
        return substr_replace($str,str_repeat('*',$len),floor(($len)/2),$len);
    }
}