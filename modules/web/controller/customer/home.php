<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 2016-03-28
 * Time: 9:25
 */
namespace web\controller\customer;

use web\common\controller;
use model\logic\Product;
use model\logic\Category;

class Home extends Controller{

    /**
     * 买家APP首页接口
     * /customer/home
     */
    public function index(){

        $market = intval($_GET['id']);

        $result = array(
            'categories'=>array(),//一级分类的ID
            'recommend'=>array()//推荐商品的ID和单价
        );

        $category = new Category();
        $categories = $category->categories($market);
        $result['categories'] = $categories;
        $product = new Product();
        $result['recommend'] = $product->recommend($market,$categories);

        $request = \web\common\Request::instance();

        $request->jsonOut(0,$result);

    }


}