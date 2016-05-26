<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/1/8
 * Time: 13:40
 */
namespace web\common;

class Router{

    public static function dispath(){
        $request = Request::instance();

        $module = $request->getModule();
        $controller = $request->getController();
        $action = $request->getAction();

        $path = array(
            'web',
            'controller',
            $module
        );
        $namespace = implode('\\', $path);
        $class = $namespace . '\\' . $controller;

        $controller = new $class();
        $controller->$action();

    }
}