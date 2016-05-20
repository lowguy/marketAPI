<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/22 0022
 * Time: 下午 1:19
 */

namespace web\controller\user;


use model\logic\User;
use web\common\Controller;
use web\common\Request;

class Fans extends Controller
{
    private $_code = 100;

    /**
     * 0 正常 1 未登录
     */
    public function add()
    {

        $request = Request::instance();

        if ($request->isPOST()) {
            $user_id = intval($_POST['id']);
            $current = $this->auth();
            if ($this->_code == 0) {
                $this->_code = 100;
                $user = new User();
                $top = $user->listTopUserNumber($current, 1);
                if (0 == $top) {
                    $this->_code = $user->invite($current, $user_id);
                } else {
                    $this->_code = 2;
                }
            }
        }

        $request->jsonOut($this->_code);
    }

    public function number(){
        $data = 0;
        $request = Request::instance();
        if($request->isPOST()){
            $current = $this->auth();
            if($this->_code == 0){
                $user = new User();
                $data = $user->listLowUserNumber($current, 3);
            }
        }
        $request->jsonOut($this->_code, $data);
    }

    /**
     * @return int
     */
    private function auth()
    {
        $this->_code = 1;
        $user = new User();
        $current = $user->isLogin();
        if($current){
            $this->_code = 0;
        }
        return $current;
    }
}
