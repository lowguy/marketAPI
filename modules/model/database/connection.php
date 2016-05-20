<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 2016/1/8
 * Time: 14:27
 */
namespace model\database;

class Connection{

    private static $pdo = array();

    private function __construct(){

    }

    public static function instance($dsn, $user, $password){
        $key = array($dsn, $user,$password);
        $key = implode(';', $key);

        if(!array_key_exists($key, self::$pdo)){
            $pdo = new \PDO($dsn, $user, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$pdo[$key] = $pdo;
        }

        return self::$pdo[$key];
    }
}