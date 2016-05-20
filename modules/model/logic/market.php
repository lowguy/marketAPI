<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/4/13 0013
 * Time: 上午 9:19
 */

namespace model\logic;

use model\database\Table;

class Market
{
    private $_market_id;
    /**
     * 市场信息
     * @param $id
     * @return array
     */
    private function marketInfo($id){
        if(empty($id)){
            $id = $this->_market_id;
        }
        $market_table = new Table('market');
        $filter = "WHERE market_id = ?";
        $fields = array(
            '*',
            'AsText(free_area) as free_area',
            'AsText(boundary) as boundary'
        );
        $market = $market_table->get($filter, array($id), $fields);
        if(!empty($market)){
            $market['free_area'] =  str_replace(',', ';', $market['free_area']);
            $market['free_area'] = str_replace(' ', ',', $market['free_area']);
            preg_match('/^POLYGON\(\((.*?)\)\)$/', $market['free_area'], $matches);
            $market['free_area'] = $matches[1];

            $market['boundary'] =  str_replace(',', ';', $market['boundary']);
            $market['boundary'] = str_replace(' ', ',', $market['boundary']);
            preg_match('/^POLYGON\(\((.*?)\)\)$/', $market['boundary'], $matches);
            $market['boundary'] = $matches[1];
        }
        return $market;
    }

    /**
     * 获取配送区域
     * @param $market_id
     * @return mixed
     */
    public function getBoundary($market_id){
        $this->_market_id   = $market_id;
        $market             = $this->marketInfo();
        $res['free_area']   = $market['free_area'];
        $res['boundary']    = $market['boundary'];
        return $res;
    }

    /**
     * Market
     * @param $market_id
     * @return array
     */
    public function market($market_id){
        $this->_market_id    = $market_id;
        return $this->marketInfo();
    }
}