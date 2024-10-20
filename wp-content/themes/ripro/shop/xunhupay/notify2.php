<?php
/**

/
 * 作者唯一QQ：200933220 （油条）



 */


/**
 * 支付成功异步回调接口
 *
 * 当用户支付成功后，支付平台会把订单支付信息异步请求到本接口(最多5次)
 *
 * @date 2017年3月13日
 * @copyright 重庆迅虎网络有限公司
 */

header('Content-type:text/html; Charset=utf-8');
date_default_timezone_set('Asia/Shanghai');
ob_start();
require_once dirname(__FILE__) . "../../../../../../wp-load.php";
ob_end_clean();
require_once get_template_directory() . '/inc/class/xunhupay.class.php';


if (!_cao('is_xunhualipay')) {
    wp_safe_redirect(home_url());exit;
}


/**
 * 回调数据
 * @var array(
 *       'trade_order_id'，商户网站订单ID
         'total_fee',订单支付金额
         'transaction_id',//支付平台订单ID
         'order_date',//支付时间
         'plugins',//自定义插件ID,与支付请求时一致
         'status'=>'OD'//订单状态，OD已支付，WP未支付
 *   )
 */
// 获取后台支付配置
$XHpayConfig = _cao('xunhualipay');

if (empty($XHpayConfig['appsecret'])) {
    wp_safe_redirect(home_url());exit;
}

$data = $_POST;
foreach ($data as $k=>$v){
    $data[$k] = stripslashes($v);
}
if(!isset($data['hash'])||!isset($data['trade_order_id'])){
   echo 'failed';exit;
}

//自定义插件ID,请与支付请求时一致
if(isset($data['plugins'])&&$data['plugins']!='ripro-xunhupay-v3'){
    echo 'failed';exit;
}

//APP SECRET
$appkey = $XHpayConfig['appsecret'];
$hash = XH_Payment_Api::generate_xh_hash($data,$appkey);
if($data['hash']!=$hash){
    //签名验证失败
    echo 'failed';exit;
}

if($data['status']=='OD'){
    //商户本地订单号
    $out_trade_no = $data['trade_order_id'];
    //交易号
    $trade_no = $data['transaction_id'];
    // 验证通过 获取基本信息
    //发送支付成功回调用
    $RiProPay = new RiProPay;
    $RiProPay->send_order_trade_success($out_trade_no,$trade_no,'ripropaysucc');
    echo 'success';exit();

}else{
    //处理未支付的情况
}

//以下是处理成功后输出，当支付平台接收到此消息后，将不再重复回调当前接口
echo 'success';
exit;
