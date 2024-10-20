<?php
if (!defined('ABSPATH')) {die;} // Cannot access directly.


/**
 * 下载地址加密flush shangche
 *
 */
header("Content-type:text/html;character=utf-8");
global $current_user;

$post_id = !empty($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$ref = !empty($_GET['ref']) ? (int)$_GET['ref'] : 0;

if (!$post_id && !$ref) {
    cao_wp_die('URL参数错误','地址错误或者URL参数错误');
}

// 开始下载处理
if (isset($post_id) && empty($ref)):
    $uid = $current_user->ID;
    $RiProPayAuth = new RiProPayAuth($uid,$post_id);

    $cao_is_post_free = $RiProPayAuth->cao_is_post_free();
    if (!is_user_logged_in() && !_cao('is_ripro_nologin_pay','1')) {
        cao_wp_die('请登录下载','请登录后下载资源包');
    }
    if ($cao_is_post_free && !is_user_logged_in() && !_cao('is_ripro_free_no_login')) {
        cao_wp_die('请登录下载','免费资源请登录后进行下载');
    }
    
    // 判断是否有权限下载
    $CaoUser = new CaoUser($uid);
    $PostPay = new PostPay($uid, $post_id);
    $_downurl     = get_post_meta($post_id, 'cao_downurl', true);
    $home_url=esc_url(home_url());
    // 本地文件做处理
    if(strpos($_downurl,$home_url) !== false){ 
    	$parse_url = parse_url($_downurl);
    	$_downurl  =$parse_url['path'];
	}

    if ($PostPay->isPayPost() || $cao_is_post_free) {
    	if(!is_user_logged_in() && _cao('is_ripro_nologin_pay','1')){
            $before_paynum = get_post_meta($post_id, 'cao_paynum', true);
            update_post_meta($post_id, 'cao_paynum', (int) $before_paynum + 1);
            $PostPay->add_down_log();
			$flush = _download_file($_downurl);
            exit();
		}
        // 判断会员类型 判断下载次数
        $vip_status = $CaoUser->vip_status();
        $this_vip_downum = $CaoUser->cao_vip_downum($uid,$vip_status);

        // var_dump($this_vip_downum);die;
        if ($this_vip_downum['is_down'] || $PostPay->isPayPost() ) {
            if (_cao('is_all_down_num','0') && !$this_vip_downum['is_down']) {
                cao_wp_die('下载次数超出限制','今日下载次数已用：'.$this_vip_downum['today_down_num'].'次,剩余下载次数：'.$this_vip_downum['over_down_num']);exit();
            }
            $is_add_down_log = false;
            //没有真实购买 但是使用免费权限下载 将计算下载次数
            if (!$PostPay->isPayPost() && $cao_is_post_free) {

                update_user_meta($uid, 'cao_vip_downum', $this_vip_downum['today_down_num'] + 1); //更新+1
                $is_add_down_log = $PostPay->add_down_log();
                 
                // 更新完成 更新资源销售数量 输出成功信息
                $before_paynum = get_post_meta($post_id, 'cao_paynum', true);
                update_post_meta($post_id, 'cao_paynum', (int) $before_paynum + 1);
            }
            if (_cao('is_all_down_num','0') && !$is_add_down_log) {
                $PostPay->add_down_log();
            }
            # // 开始下载缓冲...
            $flush = _download_file($_downurl);
            exit();
        } else {
            cao_wp_die('下载次数超出限制','今日下载次数已用：'.$this_vip_downum['today_down_num'].'次,剩余下载次数：'.$this_vip_downum['over_down_num']);
            exit;
        }
    	
    }else{
    	cao_wp_die('非法下载','您没有购买此资源或下载权限错误');
    }
endif;

// 开始推广地址处理
if (isset($ref) && empty($post_id)):
    $from_user_id = $ref;
    $_SESSION['cao_from_user_id'] = $from_user_id;
    header("Location:" . home_url());
    exit();
endif;
// 结束推广地址处理


cao_wp_die('地址错误或者URL参数错误','地址错误或者URL参数错误');
