<?php
/* *
 * 配置文件
 * 版本：3.3
 */
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者id，以2088开头的16位纯数字
$alipay_config['partner']           ='2088221648412583';

//安全检验码，以数字和字母组成的32位字符
$aliapy_config['key']               = '8xhnxayiu4qa302wai173rjln1926tys';

//签约支付宝账号或卖家支付宝帐户
$aliapy_config['seller_email']      = 'xiaomujiangkeji@163.com';

//页面跳转同步通知页面路径
$aliapy_config['return_url']        = '';

//服务器异步通知页面路径
$aliapy_config['notify_url']        = 'http://101.200.204.155/pay/alipay/notify';

//商户的私钥（后缀是.pen）文件相对路径
$alipay_config['private_key_path']	= 'key/rsa_private_key.pem';

//支付宝公钥（后缀是.pen）文件相对路径
$alipay_config['ali_public_key_path']= 'key/alipay_public_key.pem';
//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


//签名方式 不需修改
$alipay_config['sign_type']         = strtoupper('RSA');

//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'\\cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';
?>