<?php
/**
 * 商户产品信息
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/19 0019
 * Time: 下午 6:44
 */
namespace web\controller\merchant;

use model\logic\Category;
use model\logic\MarketUser;
use model\logic\User;
use web\common\Request;

class Product
{
    private $_user_id;
    private $_market_id;
    private $_code = 100;
    private $_data = array();

    public function index(){
        $request    = Request::instance();
        if($request->isPOST()){

            $this->_market_id  = intval($_POST['id']);
            $current           = intval($_POST['current']);

            $this->auth();
            if($this->_code == 0){
                $this->_data = array(
                    'all'=>array(),
                    'current'=>0,
                    'goods'=>array()
                );
                $goods       = $this->products($this->_market_id);
                if($goods){
                    $data         = array();
                    $this->_code  = 0;

                    $categories = $this->categories($goods);
                    $all        = $this->topLevel($categories);
                    $current    = $current ? $current : intval($all[0]['id']);
                    $lowLevel   = $this->lowLevel($this->_market_id ,$current);
                    $goods      = $this->resetGoods($goods,$lowLevel);

                    $data['all']= array_column($all,'id');
                    $data['current']    = $current;
                    $data['goods']      = $goods;
                    $this->_data= $data;
                }
            }
        }
        $request->jsonOut($this->_code,$this->_data);
    }

    public function stockChange(){
        $request    = Request::instance();
        if($request->isPOST()) {
            $this->_market_id  = intval($_POST['id']);
            $stock             = intval($_POST['stock']);
            $product_id        = intval($_POST['pid']);
            $this->auth();
            if($this->_code == 0){
                $this->_code = 2;
                $product    = new \model\logic\Product();
                $res        = $product->stockChange($this->_market_id,$this->_user_id,$stock,$product_id);
                if($res){
                    $this->_code = 0;
                }
            }
        }
        $request->jsonOut($this->_code,$this->_data);
    }

    private function products($market_id){
        $model      = new \model\logic\Product();
        $products   = $model->getProductByUserId($market_id,$this->_user_id);
        return empty($products) ? array() : $products;
    }

    private function categories($goods){
        return array_unique(array_column($goods,7));
    }

    private function topLevel($categories){
        $category   = new Category();
        $all        = $category->getTopLevelByChild($categories);
        return $all;
    }

    private function lowLevel($market_id,$current){
        $category   = new Category();
        $all        = $category->categories($market_id,$current);
        return $all;
    }

    private function resetGoods($goods,$lowLevel){
        $data  = array();
        foreach($goods as $k => $v){
            if(in_array($v[7],$lowLevel)){
                array_push($data,$v);
            }
        }
        return $data;
    }

    /**
     * 2 商户信息错误 1未登录 0 登录
     */
    private function auth(){
        $user           = new User();
        $this->_user_id = $user->isLogin();
        $this->_code = $this->_user_id ? 0 : 1;
    }

}