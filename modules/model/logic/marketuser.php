<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/22 0022
 * Time: 下午 3:28
 */

namespace model\logic;


use model\database\Table;

class MarketUser
{
    /**
     * @param $market_id
     * @param $user_id
     * @param $role_id
     * @return array|null
     */
    private function info($market_id,$user_id,$role_id=null){
        $table  = new Table('market_user');
        $filter = " WHERE market_id = ? AND user_id = ?";
        $params = array($market_id,$user_id);
        if($role_id){
            $filter .= " AND role_id = ?";
            $params = array_merge($params,array($role_id));
        }
        return $table->get($filter,$params);
    }

    /**
     * @param $market_id
     * @param $user_id
     * @param $data
     * @param $role
     * @return int|mixed
     */
    private function modify($market_id,$user_id,$data,$role){
        $code = 0;
        $table  = new Table('market_user');
        $pdo    = $table->getConnection();
        try{
            $pdo->beginTransaction();
            $table->edit($data," WHERE market_id = ? AND user_id = ? ",array($market_id,$user_id));
            if($role == 100){
                $sql = "UPDATE market_product SET ";
                foreach ($data as $key => $value) {
                    $fields[] = $key . '=' .$value;
                }
                $sql .= \implode($fields, ',');
                $sql .= "  WHERE market_id = ? AND user_id = ?";
                $statement = $pdo->prepare($sql);
                $statement->execute(array($market_id,$user_id));
            }
            $pdo->commit();
        }catch (\Exception $e){
            $code = $e->getCode();
        }
        return $code;
    }

    /**
     * @param $market_id
     * @param $user_id
     * @param $role_id
     * @return int
     */
    public function open($market_id,$user_id,$role_id){
        $result = $this->info($market_id,$user_id,$role_id);
        return $result ? $result['open'] : -1 ;
    }

    /**
     * @param $market_id
     * @param $user_id
     * @param $status
     * @param $role
     * @return int|mixed
     */
    public function setOpenStatus($market_id,$user_id,$status,$role){
        return $this->modify($market_id,$user_id,array('open'=>$status),$role);
    }

    /**
     * @param $market_id
     * @param $user_id
     * @return array|int
     */
    public function time($market_id,$user_id){
        $result = $this->info($market_id,$user_id);
        return $result ? array('start'=>$result['start_time'],'close'=>$result['close_time']) : array();
    }

    /**
     * @param $market_id
     * @param $user_id
     * @param $start
     * @param $close
     * @param $role
     * @return int|mixed
     */
    public function setTime($market_id,$user_id,$start,$close,$role){
        return $this->modify($market_id,$user_id,array('start_time'=>$start,'close_time'=>$close),$role);
    }

    /**
     * 市场人员信息
     * @param $market_id
     * @param $user_id
     * @return array
     */
    public function marketUserInfo($market_id,$user_id){
        return $this->info($market_id,$user_id);
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
}