<?php
/**
 * 订单派发
 * Created by PhpStorm.
 * User: zhanghui
 * Date: 16/4/27
 * Time: 10:21
 */
require_once dirname(__FILE__) . '/../task.php';
use \model\database\Table;
class Dispatch extends Task{
    public function run()
    {
        $orderTable = new Table('`order`');
        $deliveryTable = new Table('order_delivery');
        $marketUserTable = new Table('market_user');
        $where = " WHERE status = 0 AND created_at < ?";
        $deliveryTable->delete($where, array(time() - 59));

        $where = " WHERE status = 2 ORDER BY order_id DESC";
        $orders = $orderTable->lists($where);
        foreach ($orders as $order) {
            $where = " WHERE market_id = ? AND role_id = 101 AND open = 1";
            $deliveries = $marketUserTable->lists($where, array($order['market_id']));
            $deliveryIDs = array_column($deliveries, 'user_id');
            if (!empty($deliveryIDs)) {
                $where = " WHERE user_id IN(%s) AND status <> 2";
                $where = sprintf($where, implode(',', $deliveryIDs));
                $busyUser = $deliveryTable->lists($where, array());
                $freeUser = array_diff($deliveryIDs, array_column($busyUser, 'user_id'));
                if (!empty($freeUser)) {
                    $index = array_rand($freeUser);
                    $user = $freeUser[$index];
                    $this->notify($user, $order['order_id']);

                } else {
                    $where = ' WHERE user_id IN(%s) AND status <> 2 GROUP BY user_id HAVING count(*) > 1';
                    $where = sprintf($where, implode(',', $deliveryIDs));
                    $fullUser = $deliveryTable->lists($where, array());
                    $halfUser = array_diff($deliveryIDs,array_column($fullUser, 'user_id'));
                    if(!empty($halfUser)){
                        $index = array_rand($halfUser);
                        $user = $halfUser[$index];
                        $this->notify($user, $order['order_id']);
                    }
                    else{
                        break;
                    }
                }
            }

        }
    }

    private function notify($user, $order){
        $table = new Table('order_delivery');
        $userTable = new Table('user');
        $userArray = $userTable->get(" WHERE user_id = ?", array($user));
        try{
            $data = array(
                'user_id' => $user,
                'order_id' => $order,
                'created_at' => time()
            );
            $table->add($data);
            $notifier = new \model\logic\Notifier();
            $user = array();
            $notifier->sendToUser(array($userArray['phone']), '您有新小小家的订单, 请及时处理',array('id'=>$order),\model\logic\Notifier::$MESSAGE_NEW_ORDER);
        }
        catch (Exception $e){

        }
    }
}


$task = new Dispatch();

$task->run();
