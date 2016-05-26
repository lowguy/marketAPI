<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/1/8
 * Time: 14:18
 */

namespace model\logic;

use model\database\Table;

class Role{

    public function getRolesByUserID($id){
        $table = new Table('market_user');
        $filter = " WHERE user_id = ? ORDER BY role_id";
        $roles = $table->lists($filter, array($id),array('role_id'));
        return $roles;
    }

}