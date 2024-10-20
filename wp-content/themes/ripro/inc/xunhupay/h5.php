<?php 
header('Content-type:text/html; Charset=utf-8');
ob_start();
require_once dirname(__FILE__) . "../../../../../../wp-load.php";
ob_end_clean();
require_once get_template_directory() . '/inc/class/xunhupay.class.php';
$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
$recent_url=dirname($http_type.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]);
$XHpayConfig = _cao('xunhupay_wx');

if (empty($XHpayConfig['mchid'])) {
	exit;
}       

        //get pay data
	    global $wpdb, $order_table_name,$current_user;
        $order_log_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$order_table_name} WHERE order_trade_no = %s AND status = 0 ", $_GET['out_trade_no']));
        
        if( empty($order_log_data) || $order_log_data->user_id != $current_user->ID ){
            exit('订单异常');
        }
        
        $data=array(
            'mchid'     	=> $XHpayConfig['mchid'],
            'out_trade_no'	=> $order_log_data->order_trade_no,
            'type'  		=> 'wechat',
            'total_fee' 	=> $order_log_data->order_price*100,
            'body'  		=> $_GET['title'],
            'notify_url'	=> get_template_directory_uri() . '/shop/xunhupay/notify3.php',
            'trade_type'	=> 'WAP',
            'wap_url'		=> $http_type.$_SERVER['SERVER_NAME'],
            'wap_name'		=> '迅虎网络',
            'nonce_str' 	=> str_shuffle(time())
        );
        
        
        
        $hashkey =$XHpayConfig['private_key'];
		$data['sign']     = XH_Payment_Api::generate_xh_hash_new($data,$hashkey);
		try {
				if($_GET['type']=='1'){
					$redirect_url=get_template_directory_uri() . '/shop/xunhupay/return2.php';
				}else{
					$redirect_url=get_template_directory_uri() . '/shop/xunhupay/return2.php?num='.$data['out_trade_no'];
				}
				$url              = $XHpayConfig['url_do'].'/pay/payment';
				$response     = XH_Payment_Api::http_post_json($url, json_encode($data));
				/**
				 * 支付回调数据
				 * @var array(
				 *      order_id,//支付系统订单ID
				 *      url//支付跳转地址
				 *  )
				 */
				$result       = $response?json_decode($response,true):null;
				if(!$result){
		            throw new Exception('Internal server error',500);
		        }
				if($result['return_code']!='SUCCESS'){
		          	throw new Exception($result['err_msg'],$result['err_code']);
		        }
		        $sign       	  = XH_Payment_Api::generate_xh_hash_new($result,$hashkey);
		    	if(!isset( $result['sign'])|| $sign!=$result['sign']){
		            throw new Exception('Invalid sign!',40029);
		        }
				$pay_url =$result['mweb_url'];
			?>
				<html>
				<head>
				<meta charset="UTF-8">
				<title>收银台付款</title>
				<meta id="viewport" name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
				<meta name="format-detection" content="telephone=no">
				<link rel="stylesheet" href="style.css">
				</head>
				<body ontouchstart="" class="bggrey">
				<div class="xh-title"><img src="https://api.xunhupay.com/content/images/wechat-s.png" alt="" style="vertical-align: middle"> 微信支付收银台</div>
					<div class="xhpay ">
					   <img class="logo" alt="" src="img_14.png">
				
						<span class="price"><?php echo $_GET['total_fee']?></span>
					</div>
					<div class="xhpaybt">
						<a href="<?php echo $pay_url;?>" class="xunhu-btn xunhu-btn-green" >唤醒微信支付</a>
					</div>
					<div class="xhpaybt">
						<a href="<?php echo $redirect_url;?>" class="xunhu-btn xunhu-btn-border-green" >取消支付</a>
					</div>
					<div class="xhtext" align="center">支付完成后，如需售后服务请联系客服</div>
					<div class="xhfooter" align="center">迅虎网络提供技术支持</div>
					<script type="text/javascript" src="<?php echo get_template_directory_uri() ?>/assets/js/jquery-2.2.4.min.js"></script>

					<script type="text/javascript">
					 (function($){
							window.view={
								query:function () {
									$.ajax({
										type: "POST",
										url: "<?php echo get_template_directory_uri().'/inc/xunhupay/query.php?out_trade_no='.$data['out_trade_no'] ?>",
										timeout:6000,
										cache:false,
										dataType:'text',
										success:function(e){
										if (e && e.indexOf('complete')!==-1) {
												location.href ="<?php echo $redirect_url ?>";
												return;
											}
											setTimeout(function(){window.view.query();}, 2000);
										},
										error:function(){
											 setTimeout(function(){window.view.query();}, 2000);
										}
									});
								}
							};								
							  window.view.query();								
						})(jQuery);
						</script>
				</body>
				</html>
			<?php
				exit;
		} catch (Exception $e) {
			echo "errcode:{$e->getCode()},errmsg:{$e->getMessage()}";exit;
			//TODO:处理支付调用异常的情况
		}