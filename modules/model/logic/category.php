<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/3/28 0028
 * Time: 上午 9:45
 */

namespace model\logic;


use model\database\Table;

class Category
{


    /**
     * 一级分类
     * @param $market_id 市场id
     * @param $status=1 状态
     * @return array|null
     */
    private function getMarketTopLevel($market_id,$status = 1){

        $table  = new Table('market_category');
        $filter = " LEFT JOIN category ON category.category_id = market_category.category_id WHERE market_id = ? and status = ? ORDER BY weight";
        $params = array($market_id,$status);
        $fields = array(
            'category.category_id'
        );
        $res    = $table->lists($filter,$params,$fields);

        return $res;

    }

    /**
     * 二级分类
     * @param $market_id 市场id
     * @param $category_id 一级分类id
     * @return array|null
     */
    private function getMarket2Level($market_id,$category_id){

        $table  = new Table('category');
        $filter = " WHERE category_id IN ( %s ) %s";
        $product_sql = "SELECT category_id FROM product WHERE product.product_id IN ( %s ) AND product.category_id IN ( %s )";
        $market_sql = "SELECT product_id FROM market_product WHERE market_id = ?";
        $category_sql = "SELECT end FROM category_category WHERE start = ? AND distance = ?";
        $product_sql = sprintf($product_sql,$market_sql,$category_sql);
        $order_sql = " ORDER BY category_id ASC";
        $filter = sprintf($filter,$product_sql,$order_sql);
        $params = array($market_id,$category_id,1);
        $fields = array(
            'category.category_id'
        );
        $res    = $table->lists($filter,$params,$fields);

        return $res;

    }

    /**
     * 获取分类id
     * @param $market_id
     * @param $category_id
     * @return array
     * @throws \Exception
     */
    public function categories($market_id,$category_id = null){
        $res = array();
        if(is_int($market_id)||is_int($category_id)){
            $categories = $category_id ? self::getMarket2Level($market_id,$category_id) : self::getMarketTopLevel($market_id);
            foreach($categories as $k => $v){
                array_push($res,$v['category_id']);
            }
        }
        return $res;
    }

    /**
     * 当前ID
     * @param $market_id
     * @return mixed
     */
    public function current_id($market_id){

        $categories = self::getMarketTopLevel($market_id);
        $current_id = $categories['0']['category_id'];

        return $current_id;

    }

    /**
     * @param $categories
     * @return array
     */
    public function getTopLevelByChild($categories){
        $table          = new Table('category');
        $filter         = " WHERE category_id IN ( %s ) %s";
        $category2_sql  = "SELECT start FROM category_category WHERE category_category.end IN ( %s ) AND category_category.distance =1 ";
        $ids            = implode(',',$categories);
        $category2_sql  = sprintf($category2_sql,$ids);
        $order_sql      = " ORDER BY category_id ASC";
        $filter         = sprintf($filter,$category2_sql,$order_sql);
        $fields = array(
            'category.category_id as id'
        );
        $res            = $table->lists($filter,array(),$fields);

        return $res;
    }
}