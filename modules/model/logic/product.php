<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/3/28 0028
 * Time: 上午 9:55
 */

namespace model\logic;


use model\database\Table;

class Product
{
    /**
     * 经营产品
     * @param $market_id
     * @param $fullTime
     * @param $category_id
     * @param $productIDs
     * @param $title
     * @param $user_id
     * @param $activity
     * @return array|null
     */
    private function search($market_id, $fullTime = -1, $category_id = null, $productIDs = null, $title = null,$user_id = null,$activity = null){

        $table = new Table('market_product');

        $filter = " LEFT JOIN product ON product.product_id = market_product.product_id WHERE market_product.user_id IS NOT NULL AND market_product.status = ? AND market_product.market_id = ? AND price > 0";
        $params = array(1,$market_id);

        date_default_timezone_set("PRC");
        $currentTime   = time();
        $start = mktime(0,0,0,date("m",$currentTime),date("d",$currentTime),date("Y",$currentTime));
        $mark = $currentTime - $start;

        if($fullTime > 0){
            $filter .= " AND market_product.activity =  0 AND (start < $mark AND end > $mark )";
        }

        if(!empty($category_id)){
            if(is_array($category_id)){
                $filter .= " AND product.category_id IN ( SELECT end FROM category_category WHERE start IN (" . implode(',',$category_id) .") and distance = 1)";
            }else{
                $filter .= " AND product.category_id IN ( SELECT end FROM category_category WHERE start = ? and distance = ?)";
                $params = array_merge($params,array($category_id,1));
            }
        }

        if(!empty($productIDs)){
            $filter .= " AND market_product.product_id IN ( $productIDs )";
        }

        if(!empty($title)){
            $filter .= " AND product.title LIKE  ? ";
            $params = array_merge($params,array('%'.$title.'%'));
        }

        if(!empty($user_id)){
            $filter .= " AND market_product.user_id =  ? ";
            $params = array_merge($params,array($user_id));
        }

        if(!empty($activity)){
            $filter .= " AND market_product.activity =  ? AND market_product.stock > ? AND market_product.open = ? AND market_product.start_time < ? AND market_product.close_time > ?";
            $params = array_merge($params,array($activity,0,1,$mark,$mark));
        }

        $filter .= " ORDER BY ";
        if($fullTime > 0){
            $filter .= "(end - start) ASC,";
        }

        $filter .= "stock ASC,sales DESC";
        //只能拿8个
        if($fullTime > 0) {
            $filter .= " LIMIT 0,16";
        }

        $fields = array(
            'product.product_id AS id',
            'product.title',
            'product.slogan',
            'product.category_id',
            'market_product.price',
            'market_product.inprice',
            'market_product.market_id',
            'market_product.user_id',
            'market_product.stock',
            'market_product.sales',
            'market_product.start',
            'market_product.end',
            'market_product.updated_at',
            'market_product.open',
            'market_product.start_time',
            'market_product.close_time',
            'market_product.activity',
            'market_product.discount',
            "IF(market_product.open = 1 AND market_product.status  = 1 AND market_product.start_time < $mark AND market_product.close_time > $mark,1,0) AS status"
        );

        $res    = $table->lists($filter,$params,$fields);
        return $res;

    }

    private function prodcutsInfo($market, $fullTime, $cid, $productIDs, $title,$uid,$activity){
        $products = array();
        $res      = self::search($market, $fullTime, $cid, $productIDs, $title,$uid,$activity);
        while(list($k, $v) = each($res)){
            $products[$k] = array(
                intval($v['id']),
                floatval($v['price']),
                intval($v['stock']),
                intval($v['sales']),
                intval($v['status']),
                intval($v['activity']),
                floatval($v['discount']),
                intval($v['category_id']));
        }
        return $products;
    }

     /**
     * 产品信息
     * @param $market
     * @param $fullTime
     * @param $top_category_id
     * @param $activity
     * @return array
     */
    public function products($market,$fullTime = -1,$top_category_id = null,$activity = null){
        $res = array();
        if(is_int($market)){
            $res = self::prodcutsInfo($market,$fullTime,$top_category_id,null,null,null,$activity);
        }
        return $res;
    }

    /**
     * 商城按分类获取产品
     * @param $market
     * @param $top_category_id
     * @return array
     */
    public function mall($market,$top_category_id){
        $res = array();
        if(is_int($market)){
            $res = self::prodcutsInfo($market,-1,$top_category_id);
        }
        return $res;
    }

    /**
     * 按时间段获取推介产品
     * @param $market
     * @param $categories
     * @return array
     */
    public function recommend($market,$categories){
        $res = array();
        if(is_int($market)){
            $res = self::prodcutsInfo($market,1,$categories);
        }
        return $res;
    }

    /**
     * 根据
     * @param $market
     * @param $title
     * @return array|null
     */
    public function searchByTitle($market,$title){
        $res = array();
        if($market && $title){
            $res = self::prodcutsInfo($market,-1,null,null,$title);
        }
        return $res;
    }
    /**
     * 客户端购物车产品重新获取产品信息
     * @param $market
     * @param $goods
     * @return mixed
     */
    public function cartProducts($market,$goods){
        $res = array();
        if($market && $goods){
            $res = self::prodcutsInfo($market,-1,null,implode(',',$goods));
            foreach($res as $k => $v){
                $category = $this->categoryIsMark($v['7']);
                $res[$k]['7'] = intval($category['mark']);
            }
        }
        return $res;
    }

    /**
     * 获取用户经营产品
     * @param $market_id
     * @param $user_id
     * @return array
     */
    public function getProductByUserId($market_id,$user_id){
        $res = array();
        if(is_int($market_id)){
            $products = self::search($market_id,-1,null,null,null,$user_id);
            foreach($products as $k => $v){
                $res[$k] = array(
                    intval($v['id']),
                    floatval($v['inprice']),
                    intval($v['stock']),
                    intval($v['sales']),
                    intval($v['status']),
                    intval($v['activity']),
                    floatval($v['discount']),
                    intval($v['category_id']));
            }
        }
        return $res;
    }

    /**
     * 单条产品信息
     * @param $market_id
     * @param $product_id
     * @return array|null
     */
    public function getProduct($market_id,$product_id){
        $res = array();
        if($market_id && $product_id){
            $res = self::search($market_id,-1,'',$product_id);
        }
        return $res[0];
    }



    /**
     * 检查库存
     * @param $market_id
     * @param $goods
     * @return mixed
     */
    public function checkStock($market_id,$goods){
        $status     = 0;
        foreach($goods as $k=>$v){
            $res = $this->getProduct($market_id,$v['id']);
            $goods[$k]['price'] = $res['price'];
            $goods[$k]['stock'] = $res['stock'];
            if($res['stock'] < 1 || $goods[$k]['number'] > $res['stock']){
                $status =2;
                break;
            }
        }
        return $status;
    }

    /**
     * 改变库存
     * @param $market_id
     * @param $user_id
     * @param $stock
     * @param $product_id
     * @return boolean
     */
    public function stockChange($market_id,$user_id,$stock,$product_id){
        $table  = new Table('market_product');
        $data   = array('stock'=>$stock);
        $filter = " WHERE product_id = ? AND market_id = ? AND user_id = ? ";
        $params = array($product_id,$market_id,$user_id);
        return $table->edit($data,$filter,$params);
    }

    /**
     * 根据ORDERID获取商户被下单产品
     * @param $order_id
     * @param $user_id
     * @return array
     */
    public function getProductByOrderId($order_id,$user_id){
        $table  = new Table('order_product');
        $filter = " WHERE order_id = ? AND user_id = ?";
        return $table->lists($filter,array($order_id,$user_id));
    }

    /**
     * 查看是否需要备注
     * @param $id
     * @return array
     */
    private function categoryIsMark($id){
        $table = new Table('category');
        return $table->get(" WHERE category_id = ? ",array($id),array('mark'));
    }

}