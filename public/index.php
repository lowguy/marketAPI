<?php
/**
 * User: Monk
 * Date: 2015/10/10
 * Time: 21:51
 */


chdir(dirname(__DIR__));
require_once 'modules/web/common/app.php';
require_once 'vendor/autoload.php';
$app = \web\common\App::instance();

try{

    $app->run();
}
catch (\Exception $e){

    $log_file = date('Y-m-d') . 'run.log';
    file_put_contents($log_file, PHP_EOL . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
    file_put_contents($log_file, $e, FILE_APPEND);
    \web\common\Request::instance()->FOF();
}