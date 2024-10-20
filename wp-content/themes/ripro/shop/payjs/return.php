<?php
/**

/
 * 作者唯一QQ：200933220 （油条）



 */

/**
 * 微信同步回调
 */

header('Content-type:text/html; Charset=utf-8');
date_default_timezone_set('Asia/Shanghai');
ob_start();
require_once dirname(__FILE__) . "../../../../../../wp-load.php";
ob_end_clean();
if (!_cao('is_payjs')) {
    wp_safe_redirect(home_url());exit;
}