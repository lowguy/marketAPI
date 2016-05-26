<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/1/8
 * Time: 11:11
 */
namespace web\common;

class Request{
    private $module = null;
    private $controller = null;
    private $action = null;
    private static $instance = null;

    /**
     * 获取Request单例对象
     * @return Request
     */
    public static function instance(){
        if(self::$instance == null){
            self::$instance = new Request();
        }

        return self::$instance;
    }

    /**
     * 获取请求的module
     * @return string
     */
    public function getModule(){
        return $this->module;
    }

    /**
     * 获取请求的module
     * @return string
     */
    public function getController(){
        return $this->controller;
    }

    /**
     * 获取请求的action
     * @return string
     */
    public function getAction(){
        return $this->action;
    }

    /**
     * 私有构造子
     */
    private function __construct(){

        $uri = substr($_SERVER['REQUEST_URI'], 1);
        $uri = str_replace('?' . $_SERVER['QUERY_STRING'],'' , $uri);

        $uri = explode('/', $uri);
        if(!empty($uri[0])){
            $this->module = $uri[0];
        }
        else{
            $this->module = 'customer';
        }
        if(!empty($uri[1])){
            $this->controller = $uri[1];
        }
        else{
            $this->controller = 'home';
        }
        if(!empty($uri[2])){
            $this->action = $uri[2];
        }
        else{
            $this->action = 'index';
        }
    }

    public function jsonOut($code, $data){
        $json = array(
            'code' =>$code,
            'data'=>$data
        );

        exit(json_encode($json));
    }

    public function isPOST(){

        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * 404
     */
    public function FOF(){
        header("HTTP/1.0 404 Not Found");
        exit();
    }

    /**
     * @param $module, String 模块
     * @param $controller, String 控制器
     * @param $action, String 方法
     * @param $params, Array 参数
     * @return string, URL
     */
    public function makeURL($module='', $controller='', $action='', $params=array()){
        $url = '';
        if(!empty($module)){
            $url .= '/' . $module;
        }
        if(!empty($controller)){
            $url .= '/' . $controller;
        }
        if(!empty($action)){
            $url .= '/' . $action;
        }
        if(!empty($params)){
            $url .= '?';
            $temp = array();
            foreach($params as $k => $v){
                $temp[] = $k . '=' . $v;
            }

            $url .= implode('&', $temp);
        }

        return $url;
    }

}