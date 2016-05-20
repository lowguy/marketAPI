<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 2016/1/8
 * Time: 14:24
 */
namespace model\database;

class Table extends View
{

    protected $name = null;//表名
    protected $pdo = null;//PDO对象

    /**
     * 构造函数
     * @param $name 表名， 必须
     */
    public function __construct($name)
    {

        parent::__construct($name);
    }


    /**
     * @param $data
     * @return Boolean
     */
    public function add($data)
    {

        $sql = "INSERT INTO $this->name SET ";

        $fields = array();

        foreach ($data as $key => $value) {
            $fields[] = $key . ' = ?';
        }

        $sql .= \implode($fields, ',');
        $this->statement = $this->pdo->prepare($sql);

        return $this->statement->execute(\array_values($data));
    }

    /**
     * @param $data
     * @param $filter
     * @param $params
     * @return Boolean
     */
    public function edit($data, $filter, $params)
    {
        $sql = "UPDATE $this->name SET ";
        $fields = array();
        foreach ($data as $key => $value) {
            $fields[] = $key . '=?';
        }

        $sql .= \implode($fields, ',');
        $sql .= ' ' . $filter;

        $this->statement = $this->pdo->prepare($sql);

        $values = \array_values($data);
        $values = \array_merge($values, $params);

        return $this->statement->execute($values);
    }

    /**
     * @param $filter
     * @param $params
     * @return Boolean
     */
    public function delete($filter, $params)
    {
        $sql = "DELETE FROM $this->name ";
        $sql .= ' ' . $filter;

        $this->statement = $this->pdo->prepare($sql);

        return $this->statement->execute($params);
    }

    public function lastID()
    {
        return $this->pdo->lastInsertId();
    }

}