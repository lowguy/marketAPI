<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/11 0011
 * Time: 上午 10:13
 */

namespace model\logic;

use model\database\Table;
use web\common\Session;

class Order
{
    /**
     * @param $order_id
     * @param $goods
     * @return int
     */
    public function setOrderProduct($order_id,$goods){
        if(is_int($order_id) && !is_array($goods)){
            return 100;
        }
        $table = new Table('order_product');
        $order_table = new Table('`order`');
        $pdo   = $table->getConnection();
        try{
            $pdo->beginTransaction();
            $sql = "UPDATE order_product SET status = ?, pick_at = ? WHERE order_id = ? AND product_id = ? AND status = ?";
            $code = 0;
            $flag = false;
            foreach($goods AS $key => $item){
                $statement = $pdo->prepare($sql);
                $statement->execute(array(abs($item['1']-2),time(),$order_id,$item['0'],0));
                if(0 == $item['1']){
                    $flag = true;
                }
            }
            if($flag){
                $order_table->edit(array('goods_less'=>0),' WHERE order_id = ?',array($order_id));
            }
            $pdo->commit();
        }catch (\Exception $e){
            $code = 100;
        }
        return $code;
    }
    /**
     * @param $order_id
     * @param $user_id
     * @return array
     */
    public function verifyDeliveryOrder($order_id,$user_id){
        $res = array();
        if(!is_int($order_id) && !is_int($user_id)){
            return $res;
        }
        $table  = new Table('order_delivery');
        $res    = $table->get(' WHERE user_id = ? AND order_id = ? AND status = ?',array($user_id,$order_id,1));
        return $res;
    }

    /**
     * @param $user_id
     * @return array
     */
    public function orderNum($user_id){
        $table = new Table('`order`');
        $pdo   = $table->getConnection();
        try{
            $order = "`order`";
            $sql = "SELECT status,count(*) as num FROM {$order} WHERE user_id = ? AND status IN (1,2,3,4) GROUP BY status";
            $statement = $pdo->prepare($sql);
            $statement->execute(array($user_id));
            $result =  $statement->fetchAll(\PDO::FETCH_ASSOC);
        }catch (\Exception $e){
            $result = array();
        }
        return $result;
    }
    /**
     * 获取订单产品中参见活动的产品ID
     * @param $market
     * @param $user
     * @return array
     */
    public function orderProductsActivityIDs($market,$user){
        $ids = array();
        $table = new Table('market_product');
        $orderProduct      = $this->orderProductsIDs($market,$user);
        $orderProductIDs   = empty($orderProduct) ? array() : array_unique(array_column($orderProduct,'product_id'));
        if(!empty($orderProductIDs)) {
            $filter = " WHERE market_id = ? AND activity != ? AND product_id IN (" . implode(',', $orderProductIDs) . ")";
            $result = $table->lists($filter, array($market, 0));
            $ids =  empty($orderProduct) ? array() : array_column($result, 'product_id');
        }
        return $ids;
    }

    /**
     * 获取参加活动的商品ID
     * @param $market
     * @return array
     */
    public function productsActivityIDs($market){
        $table = new Table('market_product');
        $filter = " WHERE market_id = ? AND activity != ? ";
        $result = $table->lists($filter, array($market, 0));
        $ids =  empty($result) ? array() : array_column($result, 'product_id');
        return $ids;
    }

    /**
     * 获取订单产品
     * @param $market
     * @param $user
     * @return array|null
     */
    public function orderProductsIDs($market,$user){
        $result     = array();
        $order      = $this->perDayList($market,$user);
        $orderIDs   = empty($order) ? array() : array_column($order,'order_id');
        if(!empty($order)){
            $table      = new Table('order_product');
            $filter     = " WHERE order_id IN (" .  implode(',',$orderIDs).")";
            $result     = $table->lists($filter);
        }

        return $result;
    }
    /**
     * 当天的订单列表
     * @param $market
     * @param $user
     * @return array|null
     */
    public function perDayList($market,$user){
        $table = new Table("`order`");

        date_default_timezone_set("PRC");
        $currentTime   = time();
        $start = mktime(0,0,0,date("m",$currentTime),date("d",$currentTime),date("Y",$currentTime));
        $end   = mktime(23,59,59,date("m",$currentTime),date("d",$currentTime),date("Y",$currentTime));

        $filter = " WHERE market_id = ? AND user_id = ? AND created_at between ? AND ?";
        $params = array($market,$user,$start,$end);
        return $table->lists($filter,$params);
    }
    /**
     * 确认收货
     * @param $order_no
     * @param $user_id
     * @return boolean
     */
    public function confirm($user_id,$order_no){
        $data   = array(
            'status'=>3,
            'confirmed_at'=>time()
        );
        return $this->update($user_id,$order_no,$data) ? 0 : 1;
    }

    /**
     * 评价
     * @param $order_no
     * @param $user_id
     * @param $deliver
     * @return boolean
     */
    public function evaluate($user_id,$order_no,$deliver){
        $data   = array(
            'status'=>5,
            'evaluate_deliver'=>$deliver
        );
        return $this->update($user_id,$order_no,$data) ? 0 : 1;
    }
    
    /**
     * 订单列表
     * @param $user_id
     * @param $status
     * @param $page
     * @param $size
     * @return array
     */
    public function orderList($user_id,$status,$page,$size){
        return $this->search($user_id,$status,null,$page,$size);
    }

    public function detailById($order_id){
        $table  = new Table('order_product');
        $filter = " WHERE order_id = ? ";
        return $table->lists($filter, array($order_id));
    }
    /**
     * 订单详情
     * @param $order_no
     * @param $user_id
     * @return array
     */
    public function detail($user_id,$order_no){
        $data       = array(
            'countdown'=>-1,
            'order'=>array(),
            'goods'=>array()
        );

        $order      = $this->getOrderByNo($user_id,$order_no);
        $data['order']  = !empty($order) ? $order : null;
        $time       = time();
        $expired    = $data['order']['expired'];
        if($expired > $time){
            $data['countdown'] = $expired - $time;
        }
        $goods      = !empty($order) ? $this->getOrderProducts($data['order']['order_id']) : null;
        $data['goods']  = !empty($goods) ? $goods : null;

        return $data;
    }



    /**
     * 下订单
     * @param $user_id
     * @param $market_id
     * @param $goods
     * @param $address
     * @return array
     */
    public function add($user_id,$market_id,$goods,$address){
        $result   = array(
            'code'=>0,
            'order_no'=>0
        );
        $res      = $this->beforeAdd($market_id,$goods,$address,$user_id);
        $result['code'] = $res['code'];
        $order_no = $this->buildOrderNo($user_id);
        if($order_no&&!empty($res['goods'])&& ($res['code'] == 0)){
            $result['order_no'] = $order_no;
            $point              = "Point({$address['lon']} {$address['lat']})";
            $order_table        = new Table('`order`');
            $pdo                = $order_table->getConnection();
            $point              = $pdo->quote($point);
            $format             = "PointFromText(%s)";
            $geo_address        = sprintf($format, $point);
            try{
                $pdo->beginTransaction();
                $table  = "`order`";
                $sql    = "INSERT INTO {$table} (order_no, market_id, user_id, amount, deliver, created_at,c_year,c_month,c_day, status, phone, address, geo_address)";
                $sql   .= " VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, $geo_address)";
                try{
                    $statement = $pdo->prepare($sql);
                    $statement->execute(array($order_no,$market_id,$user_id,$res['amount'],$res['deliver'],time(),date('Y'),date('n'),date('j'),1,$address['phone'],$address['address']));
                }catch(\Exception $e){
                    $result['code']     = $e->getCode();
                    $result['order_no'] = 0;
                }
                $order_id       = $order_table->lastID();
                $order_product  = new Table('order_product');
                $inamount = 0;
                foreach($res['goods'] as $k => $v){
                    $data               = array();
                    $data['order_id']   = $order_id;
                    $data['product_id'] = $v['id'];
                    $data['user_id']    = $v['user_id'];
                    $data['title']      = $v['title'];
                    $data['amount']     = $v['amount'];
                    $data['price']      = $v['price'];
                    $data['discount']   = $v['discount'];
                    $data['inprice']    = $v['inprice'];
                    $data['inamount']    = $v['inprice'] * $v['number'];
                    $inamount += $v['inprice'] * $v['number'];
                    $data['number']     = $v['number'];
                    $data['comment']    = mb_substr($v['comment'],0,40);
                    $order_product->add($data);
                    $sql = "UPDATE market_product SET sales = sales + ?,stock = stock - ? WHERE market_id = ? AND product_id = ?";
                    try{
                        $statement = $pdo->prepare($sql);
                        $statement->execute(array($v['number'],$v['number'],$market_id,$v['id']));
                    }
                    catch(\Exception $e){
                        $result['code']     = $e->getCode();
                        $result['order_no'] = 0;
                    }
                }
                $order_table->edit(array('inamount'=>$inamount),' WHERE order_id = ?',array($order_id));
                $pdo->commit();
            }catch (\Exception $e){
                $result['code']     = $e->getCode();
                $result['order_no'] = 0;
            }
        }
        return $result;
    }

    /**
     * 获取配送员订单
     * @param $user
     * @return array
     */
    public function getDeliveryOrder($user){
        $result = array();

        $table = new Table('order_delivery');
        $where = " WHERE user_id = ? AND status IN (0,1)";
        $deliveryOrder = $table->lists($where, array($user));
        $orderIDs = array_column($deliveryOrder, 'order_id');
        if(!empty($orderIDs)){
            $table = new Table('`order`');
            $fields = array(
                '*',
                'AsText(geo_address) as geo_address',
                'FROM_UNIXTIME(created_at, "%m-%d %H:%i") as created_at'
            );
            $where = " WHERE order_id IN (%s) ORDER BY status ASC, order_id DESC";
            $where = sprintf($where, implode(',', $orderIDs));

            $orders = $table->lists($where, array(), $fields);
            $where = ' WHERE order_id = ? ORDER BY user_id DESC';
            $table = new Table('order_product');
            $market_table = new Table('market_user');
            foreach($orders as $order){
                preg_match('/^POINT\((.*?)\)$/', $order['geo_address'], $matches);
                $order['geo_address'] = $matches[1];
                $order['geo_address'] = str_replace(' ', ',', $order['geo_address']);
                $products = $table->lists($where, array($order['order_id']));
                $userIDs  = array_unique(array_column($products,'user_id'));
                $filter = " LEFT JOIN user ON user.user_id = market_user.user_id WHERE market_user.user_id IN (%s) AND market_user.market_id = ?";
                $filter = sprintf($filter, implode(',', $userIDs));

                $fields = array(
                    'market_user.user_id',
                    'user.phone',
                    'market_user.address',
                    'AsText(geo_address) as geo_address'
                );
                $userInfo = $market_table->lists($filter,array($order['market_id']),$fields);
                $shop = array();
                foreach ($products as $product){
                    $shop[$product['user_id']]['goods'][] = $product;
                }
                foreach ($userInfo as $merchant){
                    preg_match('/^POINT\((.*?)\)$/', $merchant['geo_address'], $matches);
                    $merchant['geo_address'] = $matches[1];
                    $shop[$merchant['user_id']]['merchant'] = $merchant;
                }
                $result[] = array(
                    'order'=>$order,
                    'shop'=>$shop
                );
            }

        }
        return $result;
    }

    /**
     * 获取配送员订单
     * @param $user
     * @param $market
     * @param $year
     * @param $month
     * @param $day
     * @param $page
     * @return array
     */
    public function deliveryStatistics($user,$market = null,$year = null,$month = null,$day = null,$page = null){
        $result = array(
            'data'=>array(),
            'number'=>0
        );
        $table = new Table('order_delivery');
        $where = " WHERE user_id = ? AND status = ?";
        $deliveryOrder = $table->lists($where, array($user,2));
        $orderIDs = array_column($deliveryOrder, 'order_id');
        if(!empty($orderIDs)){
            $table = new Table('`order`');
            $fields = array(
                '*',
                'AsText(geo_address) as geo_address',
                'FROM_UNIXTIME(created_at, "%m-%d %H:%i") as created_at'
            );
            $where = " WHERE order_id IN (%s) ";
            $where = sprintf($where, implode(',', $orderIDs));

            if($market && $year && $month){
                $where .= " AND market_id = $market AND c_year = $year AND c_month = $month ";
            }
            if($day){
                $where .= " AND c_day = $day";
            }
            $where .= " ORDER BY status ASC, order_id DESC";

            $orders = $table->lists($where, array(), $fields);
            if($day && $orders){
                $result['number'] = count($orders);
                $where = ' WHERE order_id = ? ORDER BY user_id DESC';
                $table = new Table('order_product');
                $market_table = new Table('market_user');
                $size  = 10;
                $start = $size * ($page -1);
                $orders = array_slice($orders,$start,$size);
                foreach($orders as $order){
                    preg_match('/^POINT\((.*?)\)$/', $order['geo_address'], $matches);
                    $order['geo_address'] = $matches[1];
                    $order['geo_address'] = str_replace(' ', ',', $order['geo_address']);
                    $products = $table->lists($where, array($order['order_id']));
                    $userIDs  = array_unique(array_column($products,'user_id'));
                    $filter = " LEFT JOIN user ON user.user_id = market_user.user_id WHERE market_user.user_id IN (%s)";
                    $filter .= " AND market_user.market_id = ?";
                    $filter = sprintf($filter, implode(',', $userIDs));

                    $fields = array(
                        'market_user.user_id',
                        'user.phone',
                        'market_user.address',
                        'AsText(geo_address) as geo_address'
                    );
                    $userInfo = $market_table->lists($filter,array($order['market_id']),$fields);
                    $shop = array();
                    foreach ($products as $product){
                        $shop[$product['user_id']]['goods'][] = $product;
                    }
                    foreach ($userInfo as $merchant){
                        preg_match('/^POINT\((.*?)\)$/', $merchant['geo_address'], $matches);
                        $merchant['geo_address'] = $matches[1];
                        $shop[$merchant['user_id']]['merchant'] = $merchant;
                    }
                    $result['data'][] = array(
                        'order'=>$order,
                        'shop'=>$shop
                    );
                }
            }else{
                $result['number'] = count($orders);
            }
        }
        return $result;
    }

    /**
     * @param $user
     * @param $order
     * @param $status
     * @return int
     */
    public function setDeliveryOrderStatus($user,$order,$status){
        $code  = 0;
        $table = new Table('order_delivery');
        if($status !== 0){
            $order_table = new Table('`order`');
            $userModel = new User();
            $order_product_table = new Table('order_product');
            $pdo = $table->getConnection();
            $data = array('status'=>$status);
            try{
                $pdo->beginTransaction();
                ($status == 1) && $data = array_merge($data,array('confirmed_at'=>time()));
                $table->edit($data," WHERE user_id = ? AND order_id = ?",array($user,$order));
                $data['status'] += 2;
                ($status == 2) && $data = array_merge($data,array('confirmed_at'=>time()));
                $order_table->edit($data," WHERE order_id = ?",array($order));
                if($order_table->affectedRows() && (2 == $status)){
                    $userModel->updateMoney($user,4);
                    $order_product_table->edit(array('status'=>1),' WHERE order_id = ? AND status = ?',array($order,0));
                }
                $pdo->commit();
            }catch (\Exception $e){
                $code = $e->getCode();
            }
        }else{
            $filter = " WHERE order_id = ? AND user_id = ?";
            $params = array($order,$user);
            $code = $table->delete($filter,$params) ? 0:100;
        }
        return $code;
    }
    /**
     * @param $order_no
     * @param $trade_no
     * @param $total_fee
     * @param $payment
     * @return boolean
     */
    public function pay($order_no,$trade_no,$total_fee,$payment){
        $table  = new Table('`order`');
        $data   = array(
            'trade_no' =>$trade_no,
            'total_fee'=>$total_fee,
            'payment'  =>$payment,
            'status'   =>2,
            'paid_at' => time()
        );
        $filter = " WHERE order_no = ?";
        $params = array($order_no);
        return $table->edit($data,$filter,$params);
    }

    /**
     * @param $market_id
     * @param $goods
     * @param $address
     * @param $user_id
     * @return array
     */
    private function beforeAdd($market_id,$goods,$address,$user_id){
        $data = array(
            'code'=>0,
            'amount'=>0,
            'deliver'=>0,
            'goods'=>array()
        );
        $amount = 0;
        $code = $this->withIn($market_id,$address);
        if($code != 0){
            $data['code'] = 1;
            return $data;
        }
        $orderProductsActivityIDs = $this->orderProductsActivityIDs($market_id,$user_id);
        $product  = new Product();
        foreach($goods as $k => $v){
            $price  = 0;
            $res    = $product->getProduct($market_id,$v['id']);
            if(in_array($v['id'],$orderProductsActivityIDs)){
                $price  += $res['price'] * $v['number'];
            }else{
                $productsActivityIDs = $this->productsActivityIDs($market_id);
                if(in_array($v['id'],$productsActivityIDs)){
                    $price  += $v['number'] > 2 ? ($res['price'] * ($v['number'] - 2) + 2 * $res['discount']): $res['discount'] * $v['number'];
                }else{
                    $price  += $res['price'] * $v['number'];
                }

            }
            $goods[$k]['title']   = $res['title'];
            $goods[$k]['user_id'] = $res['user_id'];
            $goods[$k]['price']   = $res['price'];
            $goods[$k]['inprice'] = $res['inprice'];
            $goods[$k]['inamount'] = $res['inprice'] * $v['number'];
            $goods[$k]['discount'] = $res['discount'];
            $goods[$k]['amount']  = $price;
            $amount              += $price;
        }
        $deliver            = ($amount >=29) ? 0 : 4;
        $data['amount']     = $amount + $deliver;
        $data['deliver']    = $deliver;
        $data['goods']      = $goods;
        return $data;
    }

    /**
     * @param $market_id
     * @param $address
     * @return boolean
     */
    private function withIn($market_id,$address){
        $table  = new Table('market');
        $pdo    = $table->getConnection();
        $sql    = "SELECT * FROM market WHERE market_id = ? AND MBRContains(PolygonFromText(AsText(free_area)),PolygonFromText('Point({$address['lon']} {$address['lat']})')) > 0";
        try{
            $statement = $pdo->prepare($sql);
            $statement->execute(array($market_id));
            $res       = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $code = !empty($res) ? 0 : 1;
        }catch (\Exception $e){
            $code = $e->getCode();
        }
        return $code;
    }

    /**
     * @param $user_id
     * @param $order_no
     * @param $data
     * @return boolean
     */
    private function update($user_id,$order_no,$data){
        $table  = new Table('`order`');
        $filter = " WHERE user_id = ? AND order_no = ? ";
        $params = array($user_id,$order_no);
        return $table->edit($data, $filter, $params);
    }

    /**
     * @param $user_id
     * @return string
     */
    private function buildOrderNo($user_id){
        $length = 4;
        return $user_id ? date('ymdHis').rand(pow(10,($length-1)), pow(10,$length)-1) : false;
    }

    /**
     * @param $user_id
     * @param null $status
     * @param null $order_no
     * @param $page
     * @param $size
     * @return array|null
     */
    private function search($user_id,$status = null,$order_no = null,$page = 1, $size = 5){
        $table  = new Table('`order`');
        $filter = " WHERE user_id = ? ";
        $params = array($user_id);

        if($status){
            if(2 == $status){
                $filter .= " AND (status = ? OR status = ?)";
                $params = array_merge($params,array($status,3));
            }else{
                $filter .= " AND status = ? ";
                $params = array_merge($params,array($status));
            }
        }

        if($order_no){
            $filter .= " AND order_no = ? ";
            $params = array_merge($params,array($order_no));
        }

        $filter .= " ORDER BY created_at DESC";
        $start   = $size * ($page - 1);
        $filter .= " LIMIT $start, $size";
        $fields = array(
            '*',
            'AsText(geo_address) as geo_address',
            'created_at+900 as expired',
            'FROM_UNIXTIME(created_at, "%m-%d %H:%i") as created_at'
        );

        return $table->lists($filter, $params,$fields);
    }

    /**
     * @param $order_no
     * @param $user_id
     * @param $status
     * @return array
     */
    public function getOrderByNo($user_id,$order_no,$status = null){
        $res    = $this->search($user_id,$status,$order_no);
        return $res[0];
    }

    /**
     * @param $order_id
     * @param $shop
     * @return array
     */
    private function getOrderProducts($order_id,$shop = null){
        $table  = new Table('order_product');
        $filter = " WHERE order_id = ? ";
        $params = array($order_id);
        if($shop){
            $filter .= " AND user_id = ?";
            $params = array_merge($params,array($shop));
        }
        return $table->lists($filter, $params);
    }

    /**
     * @param $market
     * @param $shop
     * @param $year
     * @param $month
     * @param $day
     * @param $page
     * @return array
     */
    public function statistics($market,$shop,$year,$month,$day = null,$page){
        $res = array(
            'amount'=>0
        );
        if($day){
            $res['order'] = array();
        }
        $order_products_info = $this->orderProducts($shop);

        if(!$order_products_info){
            return $res;
        }

        $order_ids = array_unique(array_column($order_products_info,'order_id'));

        $order_info = $this->getOrderByIds($market,$order_ids,$year,$month,$day);

        if(!$order_info){
            return $res;
        }

        foreach($order_info as $k => $v){
            $products = $this->getOrderProducts($v['order_id'],$shop);
            $order_info[$k]['amount'] = floatval(0);
            foreach($products as $key => $item){
                if(2 != $item['status']){
                    $order_info[$k]['amount'] += $item['inamount'];
                }
            }

            $order_info[$k]['goods'] = $products;
        }


        $res['amount'] = array_sum(array_column($order_info,'amount'));

        $size  = 10;
        $start = $size * ($page -1);
        $order_info = array_slice($order_info,$start,$size);

        if($day){
            $res['order'] = $order_info;
        }

        return $res;
    }

    public function orderProducts($shop,$order_id=NULL){
        $table  = new Table('order_product');
        $filter = " WHERE user_id = ?";
        $params = array($shop);
        if($order_id){
            $filter .= " AND order_id = ?";
            $params = array_merge($params,array($order_id));
        }
        return $table->lists($filter,$params);
    }

    public function getOrderByIds($market,$ids,$year,$month,$day = null){
        $table = new Table('`order`');
        $filter = " WHERE market_id = ? AND status >= ? AND order_id IN (" .implode(',',$ids) .") AND c_year = ? AND c_month = ? ";
        $params = array($market,2,$year,$month);
        if($day){
            $filter .= " AND c_day = ?";
            $params = array_merge($params,array($day));
        }
        $filter .= " ORDER BY created_at DESC";

        $fields = array(
            'order_id',
            'order_no',
            'created_at',
            'c_year',
            'c_month',
            'c_day',
            'status'
        );
        return $table->lists($filter,$params,$fields);
    }


}
