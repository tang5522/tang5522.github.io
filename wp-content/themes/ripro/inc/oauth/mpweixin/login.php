<?php
if (!defined('ABSPATH')) {die;} // Cannot access directly.
//启用 session
session_start();

//获取后台配置
$wxConfig = _cao('oauth_mpweixin');
$CaoMpWeixin = new CaoMpWeixin($wxConfig);

$url = $CaoMpWeixin->getWxLoginUrl();
$rurl = (empty($_REQUEST["rurl"])) ? home_url('/user') : $_REQUEST["rurl"] ;
$_SESSION['oauth_rurl']  = $rurl;
header('location:' . $url);


