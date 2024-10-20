<?php

header('Content-type:text/html; Charset=utf-8');
ob_start();
require_once dirname(__FILE__) . "../../../../../../wp-load.php";
ob_end_clean();
require_once get_template_directory() . '/inc/class/xunhupay.class.php';
$XHpayConfig = _cao('xunhupay_wx');

if (empty($XHpayConfig['mchid'])) {
    exit;
}

if ($_GET) {
    try {
        $data = array(
            'mchid'        => $XHpayConfig['mchid'],
            'out_trade_no' => $_GET['out_trade_no'],
            'nonce_str'    => str_shuffle(time()),
        );
        $hashkey      = $XHpayConfig['private_key'];
        $url          = $XHpayConfig['url_do'] . '/pay/query';
        $data['sign'] = XH_Payment_Api::generate_xh_hash_new($data, $hashkey);
        $response     = XH_Payment_Api::http_post_json($url, json_encode($data));
        $result       = $response ? json_decode($response, true) : null;
        echo $result['status'];exit;
    } catch (Exception $e) {
        exit;
    }
}