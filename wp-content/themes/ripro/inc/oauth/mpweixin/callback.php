<?php
if (!defined('ABSPATH')) {die;} // Cannot access directly.
session_start();
$wxConfig = _cao('oauth_mpweixin');
$CaoMpWeixin = new CaoMpWeixin($wxConfig);
ob_clean();//刷新缓存空格回车防止验证失败
//是否微信服务器参数验证
if (!$CaoMpWeixin->checkSignature($_GET) && empty($_GET['code'])) {exit;}
//验证服务器
if (!empty($_GET['echostr'])) {
    if ($CaoMpWeixin->checkSignature($_GET)) {echo $_GET['echostr'];exit;}
}
$userInfo=array();
//手机跳转登录
if(!empty($_GET['code']) && $_GET['state'] == 'STATE'){
    $is_pc_sanc = false;
    $userInfo = $CaoMpWeixin->getUserInfo($_GET);
}else{
    // 推送消息 MsgType event 
    $postStr = $CaoMpWeixin->getXmlData();
    // file_put_contents(__DIR__ . '/scene_id.txt', var_export($postStr, true));
    if ($postStr['MsgType'] == 'event') {
        if($postStr['is_first']==0){
            $scene_id = substr($postStr['scene_id'],8);
        }else{
            $scene_id = $postStr['scene_id'];
        }
        
        //修复非扫码事件重复通知问题
        if (empty($scene_id) || !$scene_id) {
            echo '';exit;
        }
        
        
        $AccessToken = $CaoMpWeixin->getAccessToken();
        $userInfo = $CaoMpWeixin->getUserInfo('',$AccessToken,$postStr['openid']);
        if(!empty($userInfo) && $userInfo['openid']){
            $is_pc_sanc = true;
            //写入数据库
            global $wpdb, $mpwx_log_table_name;
            $wpdb->update(
                $mpwx_log_table_name,
                array('openid' =>$userInfo['openid']),
                array('scene_id' => $scene_id),
                array('%s'),
                array('%s')
            );
            if (true) {
                $content   = $wxConfig['mp_msg'];
                $data = '{ "touser" : "'.$userInfo['openid'].'","msgtype" : "text","text" : {"content" : "'.$content.'"}}';
                $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$AccessToken;
                curlPost($url,$data);
            }
            // file_put_contents(__DIR__ . '/scene_id.txt', var_export($scene_id, true));
        }
    } elseif ($postStr['MsgType']=='text') {
        $AccessToken = $CaoMpWeixin->getAccessToken();
        // 搜索文章
        $args = array('s' => $postStr['Content'],'posts_per_page' => 5);
        $array_posts = array ();
        $data = new WP_Query($args);
        $posts = '';
        while ( $data->have_posts() ) : $data->the_post();
            $posts .= '\n'.get_the_title().'\n';
            $posts .= get_permalink().'\n';
        endwhile;
        if ($posts) {
            $resmsg = '为您搜索到以下内容： \n'.$posts;
        }else{
            $resmsg = '抱歉，没有搜索到相关内容';
        }

        $data = '{ "touser" : "'.$postStr['openid'].'","msgtype" : "text","text" : {"content" : "'.$resmsg.'"}}';
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$AccessToken;
        $result = curlPost($url,$data);
        echo '';exit;
    }
}
if (!empty($userInfo['errcode'])) {
    cao_wp_die('获取用户信息失败',$userInfo['errmsg']);exit;
}

if (!$userInfo) {
    cao_wp_die('获取用户信息失败','获取用户信息失败 CODE 2');exit;
}


// 处理本地业务逻辑
if ($userInfo) {
    $_prefix          = 'mpweixin';
    $_openid_meta_key = 'open_' . $_prefix . '_openid';
    // 初始化信息
    $metaInfo = array(
        'openid' => $userInfo['openid'],
        'name'   => $userInfo['nickname'],
        'bind'   => 1,
        'avatar' => $userInfo['headimgurl'],
    );

    global $wpdb, $current_user;

    // 查询meta
    $user_exist = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key=%s AND meta_value=%s", $_openid_meta_key, $metaInfo['openid']));
    // 如果当前用户已登录，而$user_exist存在，即该开放平台账号连接被其他用户占用了，不能再重复绑定了
    $current_user_id = get_current_user_id();
    if ($current_user_id != 0 && isset($user_exist) && $current_user_id != $user_exist) {
        cao_wp_die('绑定失败','绑定失败，可能之前已有其他账号绑定，请先登录其他账户解绑。');
    }

    if (isset($user_exist) && (int) $user_exist > 0) {
        // 该开放平台账号已连接过WP系统，再次使用它直接登录
        if(!$is_pc_sanc){
            $user_exist = (int) $user_exist;
            wp_set_current_user($user_exist);
            wp_set_auth_cookie($user_exist);
            $user = get_user_by('id', $user_exist);
            do_action('wp_login', $user->user_login, $user); // 保证挂载的action执行
            wp_safe_redirect($_SESSION['oauth_rurl']);exit;
        }
        echo '';exit;
    } elseif ($current_user_id && !$is_pc_sanc) {
        // Open 连接未被占用且当前已登录了本地账号, 那么直接绑定信息到该账号 case: 从个人资料设置中点击了绑定社交账号等操作
        update_user_meta($current_user_id, 'open_' . $_prefix . '_openid', $metaInfo['openid']);
        update_user_meta($current_user_id, 'open_' . $_prefix . '_bind', $metaInfo['bind']);
        update_user_meta($current_user_id, 'open_' . $_prefix . '_name', $metaInfo['name']);
        update_user_meta($current_user_id, 'open_weixin_avatar', $metaInfo['avatar']);
        wp_safe_redirect($_SESSION['oauth_rurl']);
        echo '';exit;
    } else {
        // 该开放平台账号未连接过WP系统，使用它登录并分配和绑定一个WP本地新用户
        $login_name = "u" . mt_rand(1000, 9999) . mt_rand(1000, 9999);
        $user_pass  = wp_create_nonce(rand(10, 1000));
        $nickname   = $metaInfo['name'];
        $userdata   = array(
            'user_login'   => $login_name,
            'user_email'   => $login_name.'_mail@no.com',
            'display_name' => $nickname,
            'nickname'     => $nickname,
            'user_pass'    => $user_pass,
            'role'         => get_option('default_role'),
            'first_name'   => $nickname,
        );
        $user_id = wp_insert_user($userdata);
        if (is_wp_error($user_id)) {
            echo $user_id->get_error_message();
        } else {
            // 更新用户字段
            update_user_meta($user_id, 'open_' . $_prefix . '_openid', $metaInfo['openid']);
            update_user_meta($user_id, 'open_' . $_prefix . '_bind', $metaInfo['bind']);
            update_user_meta($user_id, 'open_' . $_prefix . '_name', $metaInfo['name']);
            update_user_meta($user_id, 'open_weixin_avatar', $metaInfo['avatar']);
            update_user_meta($user_id, 'user_avatar_type','weixin');
            if(!$is_pc_sanc){
                //登录
                wp_set_auth_cookie($user_id, true, false);
                $user = get_user_by('id', $user_id);
                do_action('wp_login', $user->user_login, $user); // 保证挂载的action执行
                wp_safe_redirect($_SESSION['oauth_rurl']);
            }
            
        }
        echo '';exit;
    }
}
echo '';exit;