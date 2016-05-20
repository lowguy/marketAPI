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

class Search extends Controller{

    /**
     * 买家APP搜索
     * /customer/search
     */
    public function index(){

        $market = intval($_POST['id']);

        $title = $_POST['title'];
        $title = trim($title);
        $product = new Product();
        $result = array(
            'all'=>array(),
        );
        $res = $product->searchByTitle($market,$title);
        $result['all'] = $res;

        $request = \web\common\Request::instance();

        $request->jsonOut(0,$result);

    }


}