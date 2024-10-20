<?php
if (!defined('ABSPATH')) {die;} // Cannot access directly.
//启用 session
session_start();

//获取后台配置
$weiboConfig = _cao('oauth_weibo');

$weiboOAuth = new \Yurun\OAuthLogin\Weibo\OAuth2($weiboConfig['appid'], $weiboConfig['appkey'], $weiboConfig['backurl']);

if ($weiboConfig['agent']) {
    $weiboOAuth->loginAgentUrl = esc_url(home_url('/oauth/weiboagent'));
}

// 所有为null的可不传，这里为了演示和加注释就写了
$url = $weiboOAuth->getAuthUrl();
$_SESSION['YURUN_WEIBO_STATE'] = $weiboOAuth->state;
$rurl = (empty($_REQUEST["rurl"])) ? home_url('/user') : $_REQUEST["rurl"] ;
$_SESSION['oauth_rurl']  = $rurl;
header('location:' . $url);
