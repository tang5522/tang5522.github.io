<?php
if (!defined('ABSPATH')) {die;} // Cannot access directly.
//启用 session
session_start();

$wxConfig = _cao('oauth_weixin');
$wxOAuth  = new \Yurun\OAuthLogin\Weixin\OAuth2($wxConfig['appid'], $wxConfig['appkey']);

if (false) {
    $url = $wxOAuth->getWeixinAuthUrl($wxConfig['backurl']);
} else {
    $url = $wxOAuth->getAuthUrl($wxConfig['backurl']);
}
$_SESSION['YURUN_WEIXIN_STATE'] = $wxOAuth->state;
$rurl                           = (empty($_REQUEST["rurl"])) ? home_url('/user') : $_REQUEST["rurl"];
$_SESSION['oauth_rurl']         = $rurl;
header('location:' . $url);
