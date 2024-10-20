<?php
/**

/
 * 作者唯一QQ：200933220 （油条）



 */

/**
 * 支付宝同步回调
 */

header('Content-type:text/html; Charset=utf-8');
date_default_timezone_set('Asia/Shanghai');
ob_start();
require_once dirname(__FILE__) . "../../../../../../wp-load.php";
ob_end_clean();

if (!_cao('is_alipay')) {
    wp_safe_redirect(home_url());exit;
}


if (empty($_GET)) {
    echo '非法请求';exit();
}

// 获取后台支付宝配置
$aliPayConfig = _cao('alipay');

if (empty($aliPayConfig['md5Key'])) {
    wp_safe_redirect(home_url());exit;
}

// 公共配置
$params         = new \Yurun\PaySDK\Alipay\Params\PublicParams;
$params->md5Key = $aliPayConfig['md5Key'];

// SDK实例化，传入公共配置
$pay = new \Yurun\PaySDK\Alipay\SDK($params);

if ($pay->verifyCallback($_GET)) {
	// 验证成功 $_GET['trade_no']
	$out_trade_no = $_GET['out_trade_no'];
	$_post_id = 0;
	// 查询本地订单
	$RiProPay = new RiProPay;
    $postData = $RiProPay->get_order_info($out_trade_no);
    if ($postData) {
    	$_post_id = $postData['post_id'];
    	$RiProPay->AddPayPostCookie($postData['user_id'],$_post_id,$postData['order_trade_no']);
    }
    if ($_post_id>0) {
    	wp_safe_redirect( get_the_permalink( $_post_id ) );
    }else{
    	wp_safe_redirect(home_url('/user'));
    }
	// 验证结束

} else {
    wp_safe_redirect(home_url());
}
