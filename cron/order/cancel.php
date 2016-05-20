<?php
/**
 * Created by PhpStorm.
 * User: zhanghui
 * Date: 16/4/22
 * Time: 13:45
 */
require_once dirname(__FILE__) . '/../task.php';
use \model\database\Table;

class Cancel extends Task
{

    /**
     * 取消订单
     * @return int 0正常 1无此订单
     */
    public function run()
    {
        $table = new Table('`order`');
        $pdo = $table->getConnection();
        $filter = " WHERE created_at < ? AND status = ? ";
        $expired = time() - 900;
        $params = array($expired, 1);
        $orders = $table->lists($filter, $params);
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $order_product = new Table('order_product');
                $res = $order_product->lists(" WHERE order_id = ?", array($order['order_id']));
                $pdo->beginTransaction();
                $data = array(
                    'status' => 0
                );
                $table->edit($data, ' WHERE order_id = ?', array($order['order_id']));

                foreach ($res as $k => $v) {
                    $sql = "UPDATE market_product SET stock = stock + ? WHERE market_id = ? AND product_id = ?";
                    $statement = $pdo->prepare($sql);
                    $statement->execute(array($v['number'], $order['market_id'], $v['product_id']));
                }
                $pdo->commit();
            }
        }
    }

}

$task = new Cancel();
$task->run();