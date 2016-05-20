<?php
/**
 * Created by PhpStorm.
 * User: LegendFox
 * Date: 2016/5/5 0005
 * Time: 下午 1:56
 */

namespace model\logic;


use model\database\Table;

class Balance
{
    public function score($user_id,$score){
        $code = 0;
        $card = new Card();
        $card_info = $card->getDefault($user_id);
        $amount     = $score/10;
        if($amount){
            $table  = new Table('balance_apply');
            $pdo    = $table->getConnection();
            try{
                $pdo->beginTransaction();
                $data   = array(
                    'user_id'=>$user_id,
                    'card_id'=>$card_info['card_id'],
                    'type'   => 1,
                    'amount' =>$amount,
                    'created_at'=>time()
                );
                $table->add($data);
                $sql = "UPDATE user SET score = score - $score WHERE user_id = ?";
                $statement = $pdo->prepare($sql);
                $statement->execute(array($user_id));
                $pdo->commit();
            }catch (\Exception $e){
                $code = $e->getCode();
            }
        }else{
            $code = 2;
        }

        return  $code;
    }

    public function balance($user_id,$score){
        $code      = 0;
        $card      = new Card();
        $card_info = $card->getDefault($user_id);
        $amount    = $score;
        if($amount){
            $table  = new Table('balance_apply');
            $pdo    = $table->getConnection();
            try{
                $pdo->beginTransaction();
                $data   = array(
                    'user_id'=>$user_id,
                    'card_id'=>$card_info['card_id'],
                    'type'   => 2,
                    'amount' =>$amount,
                    'created_at'=>time()
                );
                $table->add($data);
                $sql = "UPDATE user SET money = money - $amount WHERE user_id = ?";
                $statement = $pdo->prepare($sql);
                $statement->execute(array($user_id));
                $pdo->commit();
            }catch (\Exception $e){
                $code = $e->getCode();
            }
        }else{
            $code = 2;
        }

        return  $code;
    }

    public function scoreLists($user_id,$page){
        $page    = $page ? $page : 1;
        $size    = 5;
        $table   = new Table('balance_apply');
        $filter  = " LEFT JOIN card ON balance_apply.card_id = card.card_id WHERE balance_apply.user_id = ? ORDER BY balance_apply.status ASC,balance_apply.created_at DESC";
        $start   = $size * ($page -1);
        $filter .= " LIMIT $start, $size";
        $params  = array($user_id);
        $fields  = array(
            'balance_apply.status',
            'balance_apply.amount',
            'balance_apply.comment',
            'balance_apply.created_at',
            'balance_apply.confirmed_at',
            'balance_apply.type',
            'card.card_id',
            'card.name',
            'card.account',
            'card.phone',
            'card.bank',
            'card.type as c_type'
        );
       return $table->lists($filter,$params,$fields);
    }


}