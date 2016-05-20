<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/6 0006
 * Time: 下午 5:41
 */

namespace model\logic;


use model\database\Table;

class Code
{
    private function lists($phone){
        date_default_timezone_set('PRC');
        $time   = time();
        $start  = mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
        $table = new Table('code');
        $filter = " WHERE phone = ? AND created_at > ? ORDER BY created_at DESC";
        $params = array($phone,$start);
        $fields = array('*');
        return $table->lists($filter,$params,$fields);
    }

    public function add($phone,$code,$senario){
        $time = time();
        $table = new Table('code');
        $data = array(
            'phone'=>$phone,
            'created_at'=>$time,
            'senario'=>$senario,
            'code'=>$code
        );
        $table->add($data);
    }

    /**
     * 获取最后一次的code
     * @param $phone
     * @return mixed
     */
    public function lastCode($phone){
        $data   = array(
            'code'      => 66666,
            'created_at'=>null
        );
        $res = $this->lists($phone);
        if($res){
            $data['code'] = $res[0]['code'];
            $data['created_at'] = $res[0]['created_at'];
        }
        return $data;
    }

    /**
     * 获取每天的发送次数
     * @param $phone
     * @return int
     */
    public function codeLimitPerDay($phone){
        $res = $this->lists($phone);
        return count($res);
    }

}