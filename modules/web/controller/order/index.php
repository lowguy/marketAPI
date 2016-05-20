<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/11 0011
 * Time: 上午 9:43
 */
namespace web\controller\order;

use model\logic\Market;
use model\logic\Order;
use model\logic\Product;
use model\logic\User;
use web\common\Request;
use web\common\Controller;
use web\common\Session;

class Index extends Controller
{
    private $_user_id;//用户id
    private $_code = 100;//服务器繁忙
    private $_data = array();

    /**
     * 1未登录 0 登录
     */
    private function isLogin(){
        $this->_code    = 1;
        $user           = new User();
        $this->_user_id = $user->isLogin();
        $this->_user_id && $this->_code = 0;
    }

    /**
     * @param $market_id
     * 3市场信息错误 0正常
     */
    private function market($market_id){
        $this->_code    = 3;
        $market         = new Market();
        $res            = $market->market($market_id);
        !empty($res) && $this->_code = 0;
    }

    /**
     * @param $market_id
     * @param $goods
     * 2库存不足 0 正常
     */
    private function products($market_id,$goods){
        $product= new Product();
        $this->_code = $product->checkStock($market_id,$goods);
    }

    /**
     * @param $market_id
     * @param $goods
     * 0正常 1未登录 2库存不足 3市场信息错误
     */
    private function beforeAdd($market_id,$goods){
        $this->isLogin();
        $this->_code == 0 && $this->market($market_id);
        $this->_code == 0 && $this->products($market_id,$goods);
    }

    /**
     * 订单列表
     */
    public function index(){
        $request    = Request::instance();
        $request->isPOST()&&$this->orderList();
        $request->jsonOut($this->_code,$this->_data);
    }

    private function orderList(){
        $data      = array();
        $this->isLogin();
        if($this->_code == 0){
            $status= intval($_POST['status']) ? intval($_POST['status']) : 1 ;
            $page  = intval($_POST['page']);
            $page  = $page ? $page : 1;
            $size  = 5;
            $order = new Order();
            $data  = $order->orderList($this->_user_id,$status,$page,$size);
            foreach($data as $k => $v){
                $res                = $order->detailById($v['order_id']);
                $data[$k]['goods']  = $res;
            }
        }
        $request   = Request::instance();
        $request->jsonOut($this->_code,$data);
    }

    /**
     * 新增订单
     */
    public function add(){
        $request    = Request::instance();
        $request->isPOST() && $this->doOrder();
        $request->jsonOut($this->_code,$this->_data);
    }

    private function doOrder(){
        $order_no   = 0;
        $market_id  = intval($_POST['id']);
        $goods      = $_POST['goods'];
        $address    = $_POST['address'];
        $goods      = json_decode($goods,true);
        $address    = json_decode($address,true);
        $this->beforeAdd($market_id,$goods);
        if($this->_code == 0){
            $order  = new Order();
            $data   = $order->add($this->_user_id,$market_id,$goods,$address);

            $order_no = $data['code'] == 0 ? $data['order_no'] : 0 ;
            $this->_code = $data['code'];

        }
        $request    = Request::instance();
        $request->jsonOut($this->_code,$order_no);
    }

    /**
     * 订单详情
     */
    public function detail(){
        $request   = Request::instance();
        if($request->isPOST()){
            $this->isLogin();
            $order_no   = $_POST['order_no'];
            $order      = new Order();
            $res        = $order->detail($this->_user_id,$order_no);
            if($res['order']){
                $this->_code = 0 ;
                $this->_data = $res;
            }
        }
        $request->jsonOut($this->_code,$this->_data);
    }

    /**
     * 确认订单
     */
    public function confirm(){
        $code      = 100;
        $request   = Request::instance();
        if($request->isPOST()){
            $code       = 1;
            $session    = new Session();
            $session->start();
            $user_id  = $session->getUserID();
            if($user_id){
                $code     = 2;
                $roles    = $session->getUserRole();
                if(in_array(101,$roles)){
                    $order_no = $_POST['order_no'];
                    $order    = new Order();
                    $code     =  $order->confirm($user_id,$order_no);
                }
            }
        }
        $request->jsonOut($code);
    }

    /**
     * 评价订单
     */
    public function evaluate(){
        $code      = 100;
        $request   = Request::instance();
        if($request->isPOST()){
            $code       = 1;
            $session    = new Session();
            $session->start();
            $user_id  = $session->getUserID();
            if($user_id){
                $order_no = $_POST['order_no'];
                $deliver  = $_POST['score'];
                $order    = new Order();
                $code     =  $order->evaluate($user_id,$order_no,$deliver);
            }
        }
        $request->jsonOut($code);
    }


}