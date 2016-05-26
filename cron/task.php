<?php
/**
 * Created by PhpStorm.
 * User: 890
 * Date: 16/4/22
 * Time: 13:41
 */
chdir(dirname(__DIR__));
set_time_limit(0);
error_reporting(E_ALL ^ E_NOTICE);
require_once 'vendor/autoload.php';
abstract class Task{
    protected $total = 1;//cron总数
    protected $number = 0;//当前服务器编号

    public function __construct()
    {
        $this->autoload();
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

    abstract public function run();
}
