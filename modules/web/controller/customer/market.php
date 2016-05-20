<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/14 0014
 * Time: 下午 2:03
 */

namespace web\controller\customer;

use web\common\Controller;
use web\common\Request;

class Market extends Controller
{
    private $_code  = 100;
    private $_data  = null;
    public function index(){
        $market_id   = intval($_POST['id']);
        $this->market($market_id);
        if($this->_code == 0){
            $market     = new \model\logic\Market();
            $this->_data= $market->getBoundary($market_id);
        }
        $request = Request::instance();
        $request->jsonOut($this->_code,$this->_data);
    }
    /**
     * @param $market_id
     * 3市场信息错误 0正常
     */
    private function market($market_id){
        $this->_code    = 3;
        $market         = new \model\logic\Market();;
        $res            = $market->market($market_id);
        !empty($res) && $this->_code = 0;
    }
}