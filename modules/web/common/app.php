<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/1/8
 * Time: 11:00
 */
namespace  web\common;

class App{

    static private  $instance = null;


    private function __construct(){

    }

    /**
     * 获取实例
     * @return App
     */
    static public  function instance(){
        if(self::$instance == null){
            self::$instance = new App();
        }
        return self::$instance;
    }

    /**
     * 注册autoload
     */
    private function autoload(){
        spl_autoload_register(function($class){
            $file = strtolower($class) . '.php';
            $file = 'modules' . DIRECTORY_SEPARATOR . $file;
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
            require_once $file;
        }, true);
    }

    /**
     * 启动
     */
    public function run(){
        $this->autoload();

        Router::dispath();
    }
}
