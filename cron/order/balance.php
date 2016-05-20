<?php
/**
 * Created by PhpStorm.
 * User: zhanghui
 * Date: 16/5/3
 * Time: 17:45
 */
require_once dirname(__FILE__) . '/../task.php';
use \model\database\Table;
class Balance extends Task{
    public function run(){
        $ordertTable = new Table('`order`');
        $userTable = new Table('user');
        $userModel = new \model\logic\User();
        $pdo = $ordertTable->getConnection();
        $filter = "WHERE balance_status = 0 AND status > 1";
        $orders = $ordertTable->lists($filter);
        foreach ($orders as $order){
            try{
                $pdo->beginTransaction();
                $data = array(
                    'balance_status'=>1
                );
                $filter = "WHERE order_id = ?";
                $ordertTable->edit($data, $filter, array($order['order_id']));
                /**
                 * 1, 分配积分
                 */
                if($order['payment'] != 0){//线上支付才可以获得积分
                    $topUser = $userModel->listTopUser($order['user_id'], 3);
                    $topUser = array_column($topUser, 'start');
                    $topUser[] = $order['user_id'];
                    $score = $order['amount'] * 100;
                    $score = \floor($score);
                    $sql = "UPDATE user SET frozen_score = frozen_score + $score WHERE user_id IN (%s)";
                    $sql = sprintf($sql, implode(',', $topUser));
                    $pdo->query($sql);
                }
                /**
                 * 2, 商家结算
                 */
                $pdo->commit();
            }
            catch (Exception $e){
                print_r($e);
            }

        }
    }
}

$task = new Balance();
$task->run();