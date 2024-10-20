<?php
if (!defined('ABSPATH')) {die;} // Cannot access directly.
//启用 session
session_start();

//获取后台配置
$qqConfig = _cao('oauth_qq');

$_appid = trim($qqConfig['appid']);
$_appkey = trim($qqConfig['appkey']);


$qqOAuth  = new \Yurun\OAuthLogin\QQ\OAuth2($_appid, $_appkey, $qqConfig['backurl']);
if ($qqConfig['agent']) {
    $qqOAuth->loginAgentUrl = esc_url(home_url('/oauth/qqagent'));
}

$url                        = $qqOAuth->getAuthUrl();
$_SESSION['YURUN_QQ_STATE'] = $qqOAuth->state;
$rurl = (empty($_REQUEST["rurl"])) ? home_url('/user') : $_REQUEST["rurl"] ;
$_SESSION['oauth_rurl']  = $rurl;
header('location:' . $url);
