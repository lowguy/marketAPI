<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/6 0006
 * Time: 上午 10:47
 */

namespace model\logic;

use model\database\Table;
use web\common\Session;

class User
{
    public function updateScore($user_id,$score){
        return $this->modifyForFieldAccumulation(array('score'=>$score),' WHERE user_id = ?',array($user_id));
    }

    public function updateMoney($user_id,$money){
        return $this->modifyForFieldAccumulation(array('money'=>$money),' WHERE user_id = ?',array($user_id));
    }
    public function getByID($user_id){
        return $this->info($user_id);
    }

    public function check($phone,$status = 1){
        return $this->info(null,$phone,$status);
    }

    public function score($user_id){
        return $this->info($user_id,null,1);
    }

    public function updateDevice($user_id,$device,$platform){
        return $this->modify($user_id,null,$device,$platform);
    }

    public function balance($user_id){
        return $this->info($user_id,null,1);
    }

    /**
     * @param null $user_id
     * @param null $phone
     * @param null $status
     * @return array|null
     */
    private function info($user_id = null,$phone = null,$status = null){
        $result = array();
        if( empty($user_id) && empty($phone) && empty($status)){
            return $result;
        }

        $table = new Table('user');
        $filter = " WHERE 1=1";
        $params = array();

        if($user_id){
            $filter .= " AND user_id = ?";
            $params = array_merge($params,array($user_id));
        }
        if($phone){
            $filter .= " AND phone = ?";
            $params = array_merge($params,array($phone));
        }
        if($status){
            $filter .= " AND status = ?";
            $params = array_merge($params,array($status));
        }

        $result = $table->get($filter, $params);
        return $result;
    }

    /**
     * @param $user_id
     * @param null $password
     * @param null $device
     * @param null $platform
     * @param null $money
     */
    private function modify($user_id,$password = null,$device = null,$platform = null,$money = null){
        $table = new Table('user');
        $data  = array();

        if($password){
            $data   = array_merge($data,array('password'=>$password));
        }
        if($device){
            $data   = array_merge($data,array('device'=>$device));
        }
        if($platform){
            $data   = array_merge($data,array('platform'=>$platform));
        }
        if($money){
            $data   = array_merge($data,array('money'=>$money));
        }

        $filter = " WHERE user_id = ?";

        $params = array($user_id);

        return $table->edit($data,$filter,$params);
    }

    /**
     * @param $data
     * @param $filter
     * @param $params
     * @return bool
     */
    private function modifyForFieldAccumulation($data,$filter,$params){
        $table  = new Table('user');
        $pdo    = $table->getConnection();
        $sql = "UPDATE user SET ";
        $fields = array();
        foreach ($data as $key => $value) {
            $fields[] = "$key = $key + $value";
        }
        $sql .= \implode($fields, ',');
        $sql .= ' '.$filter;
        $statement = $pdo->prepare($sql);
        return $statement->execute($params);

    }

    /**
     * 修改密码
     * @param $phone
     * @param $password
     * @param $device
     * @return int, 0成功， 其他表示数据库错误code
     */
    public function editPwd($phone, $password, $device){
        $user_id = 0;
        $table = new Table('user');
        $data = array(
            'password'=>md5($password),
            'device'=>$device
        );
        $filter = " WHERE phone = ? AND status = ?";
        $params = array($phone,1);
        if($table->edit($data,$filter,$params)){
            $user = $this->check($phone);
            $user_id = $user['user_id'];
        }

        return $user_id;
    }

    /**
     * 添加用户
     * @param $phone
     * @param $password
     * @param $device
     * @return int, 0成功， 其他表示数据库错误code
     */
    public function add($phone, $password, $device){
        $res = array(
            'code'=>0,
            'user_id'=>null
        );
        $table = new Table('user');
        $data = array(
            'phone'=>$phone,
            'password'=>md5($password),
            'created_at'=>time(),
            'device'=>$device,
            'status'=>1
        );
        try{
            $pdo = $table->getConnection();
            $pdo->beginTransaction();
            $table->add($data);
            $user_id = $table->lastID();
            $res['user_id'] = $user_id;
            $user_table = new Table('user_user');
            $data = array(
                'start' => $user_id,
                'end' => $user_id,
                'distance' => 0
            );
            $user_table->add($data);
            $pdo->commit();
        }
        catch(\Exception $e){
            $res['code'] = $e->getCode();
        }

        return $res;
    }

    /**
     * 是否登录
     * @return null
     */
    public function isLogin(){
        $session    = new Session();
        $session->start();
        return $session->getUserID();
    }

    /**
     * 市场人员信息
     * @param $user_id
     * @return array
     */
    public function marketUserInfo($user_id){
        $table  = new Table('market_user');
        $filter = "  WHERE user_id = ? ";
        return $table->get($filter,array($user_id));
    }

    /**
     * 商户、配送申请
     * @param $user_id
     * @param $market_id
     * @param $role_id
     * @return boolean
     */
    public function apply($user_id,$market_id,$role_id){
        $table  = new Table('market_user');
        return $table->add(array('market_id'=>$market_id, 'user_id'=>$user_id, 'role_id'=>$role_id));
    }

    /**
     * 获取用户关系中,指定用户的上级用户id
     * @param $user_id,  用户ID
     * @param $distance, 距离
     */
    public function listTopUser($user_id, $distance){
        $table  = new Table('user_user');
        $filter = " WHERE end = ? AND distance BETWEEN 1 AND ? ORDER BY distance ASC";
        $result = $table->lists($filter,array($user_id, $distance));
        return $result;
    }

    /**
     * 获取用户关系中,指定用户的上级用户数量
     * @param $user_id,  用户ID
     * @param $distance, 距离
     */
    public function listTopUserNumber($user_id, $distance){
        $table  = new Table('user_user');
        $filter = " WHERE end = ? AND distance BETWEEN 1 AND ? ORDER BY distance ASC";
        $result = $table->count($filter,array($user_id, $distance));
        return $result;
    }

    public function listLowUser($user_id, $distance){
        $table  = new Table('user_user');
        $filter = " WHERE start = ? AND distance BETWEEN 1 AND ? ORDER BY distance ASC";
        $result = $table->lists($filter,array($user_id, $distance));
        return $result;
    }

    public function listLowUserNumber($user_id, $distance){
        $table  = new Table('user_user');
        $filter = " WHERE start = ? AND distance BETWEEN 1 AND ? ORDER BY distance ASC";
        $result = $table->count($filter,array($user_id, $distance));
        return $result;
    }

    /**
     * 新建用户关系
     * @param $current
     * @param $user_id
     * @return int|mixed
     */
    public function invite($current,$user_id){
        $code   = 0;
        $table  = new Table('user_user');
        $count = $table->count(" WHERE start = ? AND end = ?", array($current, $user_id));
        if($count > 0){
            $code = 2;
        }
        else{
            $pdo = $table->getConnection();
            try{
                $sql = "INSERT INTO user_user (start,end,distance) SELECT a.start,b.end,a.distance+b.distance+1 FROM ";
                $sql .=" (SELECT start, distance FROM user_user a WHERE end = ?) AS a";
                $sql .= " JOIN (SELECT end, distance FROM user_user WHERE start = ?) AS b;";
                $statement = $pdo->prepare($sql);
                $bool = $statement->execute(array($user_id,$current));
                if(!$bool) {
                    $code = 2;
                }
            }
            catch(\Exception $e){
                $code = $e->getCode();
            }
        }

        return $code;
    }







}