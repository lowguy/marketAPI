<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 2016-03-28
 * Time: 9:25
 */
namespace web\controller\customer;

use model\logic\Market;
use model\logic\Order;
use model\logic\Product;
use web\common\controller;
use web\common\Session;

class Cart extends Controller{

    /**
     * 买家APP购物车
     * /customer/cart
     */
    public function index(){
        $result = array(
            'goods'=>array(),//一级分类的ID
            'free_area'=>''//配送区域
        );

        $market_id  = intval($_POST['id']);
        $goods      = json_decode($_POST['goods'],true);
        $product    = new Product();
        $order      = new Order();
        $session    = new Session();
        $session->start();
        $user       = $session->getUserID();
	
        $orderProductsActivityIDs = $order->orderProductsActivityIDs($market_id,$user);
	    $cartGoods  = $product->cartProducts($market_id,$goods);
        foreach($cartGoods AS $k => $v){
            if(in_array($v['0'],$orderProductsActivityIDs)){
                $cartGoods[$k]['5'] = 0;
            }
        }
        $result['goods'] = $cartGoods;
        $market     = new Market();
        $res        = $market->getBoundary($market_id);
        $result['free_area'] = $res['free_area'];

        $request    = \web\common\Request::instance();
        $request->jsonOut(0,$result);

    }

}
