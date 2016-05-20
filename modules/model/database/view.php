<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 2016/1/8
 * Time: 14:24
 */
namespace model\database;

class View
{

    protected $name = null;//视图名
    protected $pdo = null;//PDO对象
    protected $statement = null;

    /**
     * 构造函数
     * @param $name 视图名称
     */
    public function __construct($name)
    {
        $statement = null;

        $this->pdo = Connection::instance('mysql:dbname=center;host=10.51.49.29;port=3306', 'php', '123456');

        $this->name = $name;
    }

    /**
     * 获取数据库连接对象
     * @return \PDO||null
     */
    public function getConnection(){
        return $this->pdo;
    }

    /**
     * @param $filter
     * @param $params
     * @param $fields_arr, 字段
     * @return null|array
     */
    public function lists($filter = null, $params = array(), $fields_arr=array('*'))
    {

        $fields = implode(',', $fields_arr);
        $sql = "SELECT $fields FROM $this->name ";
        $sql .= $filter;

        $this->statement = $this->pdo->prepare($sql);

        $this->statement->execute($params);

        $result =  $this->statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;

    }

    /**
     * @param $filter
     * @param $params
     * @param $fields_arr, 字段
     * @return null|array
     */
    public function get($filter = null, $params = null, $fields_arr=array('*'))
    {
        $result = null;
        $filter .= ' LIMIT 0, 1';
        $lists = $this->lists($filter, $params, $fields_arr);
        if (!empty($lists)) {
            $result = $lists[0];
        }

        return $result;
    }

    /**
     * @param $filter, string
     * @param $params, string
     * @param $distinct, string统计的唯一列
     * @return Integer
     */
    public function count($filter = null, $params = null, $distinct = null)
    {

        if(null != $distinct){
            $sql = "SELECT COUNT(DISTINCT $distinct) FROM $this->name ";
        }
        else{
            $sql = "SELECT COUNT(*) FROM $this->name ";
        }

        $sql .= $filter;

        $this->statement = $this->pdo->prepare($sql);
        $this->statement->execute($params);

        return $this->statement->fetchColumn();
    }


    public function affectedRows(){
        $result = 0;
        if(null != $this->statement){
            $result = $this->statement->rowCount();
        }

        return $result;
    }

}