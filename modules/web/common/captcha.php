<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 2016/1/20
 * Time: 16:36
 */
namespace web\common;
use Gregwar\Captcha\CaptchaBuilder;
class Captcha{

    private $builder = null;
    public function __construct(){

        $this->builder = CaptchaBuilder::create();

        $code = $this->builder->getPhrase();
        $this->builder->setIgnoreAllEffects(true);
        $session = new Session();
        $session->setCode($code);
        $this->builder->build(100, 34);
    }
    public function out(){
        header('Content-type: image/jpeg');
        $this->builder->output();
    }

    public function inline(){
        return $this->builder->inline();
    }
}