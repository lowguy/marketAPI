<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 2016-03-28
 * Time: 9:26
 */
namespace web\controller\customer;
use model\logic\Category;
use model\logic\Product;
use web\common\controller;
class Mall extends Controller{

    /**
     * 商城商品接口
     * /customer/mall
     */
    public function index(){

        $top_category_id = $_GET['category_id'];//顶级分类ID
        $top_category_id = intval($top_category_id);

        $market_id =  $_GET['id'];//市场ID
        $market_id = intval($market_id);

        $result = array(
            'current'=>0,//当前请求的一级分类的ID， 如果不存在， 则默认给一个
            'all'=>array(),//所有的顶级分类
            'goods'=>array(),//当前分类的所有商品
        );

        $category = new Category();
        $result['all'] = $category->categories($market_id);
        $top_category_id = $top_category_id && in_array($top_category_id,$result['all']) ? $top_category_id : $category->current_id($market_id);
        $result['current'] = $top_category_id;

        $product = new Product();
        $result['goods'] = $product->mall($market_id,$top_category_id);

        $request = \web\common\Request::instance();
        $request->jsonOut(0,$result);
    }

    /**
     * 活动专区
     */
    public function activity(){
        $market     = intval($_GET['id']);
        $activity   = intval($_GET['activity']);

        $activity   = $activity ?: 1;
        $category = new Category();
        $categories = $category->categories($market);;
        $product    = new Product();
        $result     = $product->products($market,-1,$categories,$activity);
        $data       = $result ?: array();

        $request = \web\common\Request::instance();
        $request->jsonOut(0,$data);
    }
}