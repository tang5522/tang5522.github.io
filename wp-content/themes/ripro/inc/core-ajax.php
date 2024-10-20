<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access directly.



//**切换暗黑风格
function tap_dark()
{
    $is_ripro_dark   = !empty($_POST['is_ripro_dark']) ? intval($_POST['is_ripro_dark']) : 0;
    $_SESSION['is_ripro_dark'] = $is_ripro_dark;
    echo $_SESSION['is_ripro_dark'];
    exit();
}
add_action('wp_ajax_tap_dark', 'tap_dark');
add_action('wp_ajax_nopriv_tap_dark', 'tap_dark');

//**切换博客模式
function blog_style()
{
    $is_blog_style   = ($_POST['is_blog_style'] == '0') ? 1 : 0;
    $_SESSION['is_blog_style'] = $is_blog_style;
    echo $_SESSION['is_blog_style'];
    exit();
}
add_action('wp_ajax_blog_style', 'blog_style');
add_action('wp_ajax_nopriv_blog_style', 'blog_style');


function get_bigger_img()
{
    //新版本海报抛弃传统的php后端生产模式，使用js前段绘图，不占用服务器资源 性能出众 参考灵感来自wpcom.cn和b2的html2canvasjs技术
    header('Content-type:application/html; Charset=utf-8');
    global $current_user;
    $post_id   = !empty($_POST['id']) ? (int)$_POST['id'] : 0;
    $post    = get_post($post_id);
    if ($current_user->ID>0) {
        // 生出带参数的推广文章链接
        $afflink = add_query_arg(array('ref' => $current_user->ID), get_the_permalink($post_id));
    } else {
        $afflink = get_the_permalink($post_id);
    }
    if (!$post) {
        exit('参数错误');
    }
    $img_u = _get_post_thumbnail_url($post_id);
    $img_t = get_template_directory_uri() . '/timthumb.php?src=' . $img_u . '&h=300&w=400&zc=1&a=c&q=80&s=1';
    // $imageInfo = getimagesize($img_t);
    // $b64 = base64_encode(file_get_contents($img_t));
    // switch ($imageInfo[2]) {           //判读图片类型
    //   case 1: $img_type = "gif";
    //       break;
    //   case 2: $img_type = "jpg";
    //       break;
    //   case 3: $img_type = "png";
    //       break;
    // }
    // $img_base64 = 'data:image/' . $img_type . ';base64,' . $b64;
    echo '<div id="poster-html" class="poster-html">';
    echo '<div class="poster-header">';
    echo '<img src="'.$img_t.'">';
    echo '<h2 class="poster-title">'.get_the_title($post_id).'</h2>';
    echo '</div>';
    echo '<div class="poster-body">';
    echo '<div class="poster-meta">';
    echo '<div class="poster-author">'.get_avatar($post->post_author).get_the_author_meta('display_name', $post->post_author).'</div>';
    echo '<div class="poster-data">'.$post->post_date.'</div>';
    echo '</div>';
    echo '<div class="poster-text">'.wp_trim_words(strip_shortcodes($post->post_content), 120, '...').'</div>';
    echo '</div>';
    echo '<div class="poster-footer">';
    echo '<div class="poster-logo">';
    echo '<img src="'._cao('poster_logo').'">';
    echo '<p>'._cao('poster_desc').'</p>';
    echo '</div>';
    echo '<div class="poster-qrcode">';
    echo '<img src="'.getQrcode($afflink).'">';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '<div class="poster-canvas"></div>';
    echo '<a class="poster-down btn" href="" download="'.get_the_title($post_id).'.png"><i class="fa fa-spinner fa-spin"></i> '.esc_html__('海报生成中', 'rizhuti-v2').'</a>';
    exit;
}

add_action('wp_ajax_nopriv_get_bigger_img', 'get_bigger_img');
add_action('wp_ajax_get_bigger_img', 'get_bigger_img');


/**
 * [ajax_getcat_post 分类文章获取]
 * @Author   Dadong2g
 * @DateTime 2020-03-05T19:56:17+0800
 * @return   [type]                   [html str]
 */
function ajax_getcat_post()
{
    global $wp_query;
    header('Content-type:application/html; Charset=utf-8');
    // $paged   = !empty($_POST['paged']) ? esc_sql($_POST['paged']) : 1;
    $cat   = !empty($_POST['cat']) ? (int)$_POST['cat'] : '';
    /////////////
    $is_cao_site_list_blog = is_cao_site_list_blog();
    if ($is_cao_site_list_blog) {
        $latest_layout = 'bloglist';
    } else {
        $latest_layout = _cao('latest_layout', 'grid');
    }
    /////////////

    $args = array(
        'cat'            => $cat,
        'post_status' => 'publish',
    );

    $data = new WP_Query($args);
    if ($data->have_posts()) {
        while ($data->have_posts()) : $data->the_post();
        get_template_part('parts/template-parts/content', $latest_layout);
        endwhile;
    } else {
        get_template_part('parts/template-parts/content', 'none');
    }
    wp_reset_postdata();
    exit();
}
add_action('wp_ajax_ajax_getcat_post', 'ajax_getcat_post');
add_action('wp_ajax_nopriv_ajax_getcat_post', 'ajax_getcat_post');



/**
 * [ajax_search AJAX搜索]
 * @Author   Dadong2g
 * @DateTime 2019-08-21T23:35:34+0800
 * @return   [type]                   [JSON Arr]
 */
function ajax_search()
{
    global $wp_query;
    header('Content-type:application/json; Charset=utf-8');
    $text   = !empty($_POST['text']) ? esc_sql($_POST['text']) : null;
    $args = array('s' => $text,'posts_per_page' => 5);
    $array_posts = array();
    $data = new WP_Query($args);
    while ($data->have_posts()) : $data->the_post();
    array_push($array_posts, array("title"=>get_the_title(),"url"=>get_permalink(),"img"=>_get_post_timthumb_src() ));
    endwhile;
    echo json_encode($array_posts);
    exit();
}
add_action('wp_ajax_ajax_search', 'ajax_search');
add_action('wp_ajax_nopriv_ajax_search', 'ajax_search');


function get_mpweixin_qr()
{
    header('Content-type:application/json; Charset=utf-8');
    $wxConfig = _cao('oauth_mpweixin');
    $CaoMpWeixin = new CaoMpWeixin($wxConfig);
    echo json_encode($CaoMpWeixin->getLoginQr());
    exit;
}
add_action('wp_ajax_get_mpweixin_qr', 'get_mpweixin_qr');
add_action('wp_ajax_nopriv_get_mpweixin_qr', 'get_mpweixin_qr');


function check_mpweixin_qr()
{
    header('Content-type:application/json; Charset=utf-8');
    $scene_id   = !empty($_POST['scene_id']) ? sanitize_text_field(wp_unslash($_POST[ 'scene_id' ])) : null;
    global $current_user;
    $current_user_id =$current_user->ID;

    // 查询数据库
    global $wpdb, $mpwx_log_table_name;
    $res = $wpdb->get_row($wpdb->prepare("SELECT * FROM $mpwx_log_table_name WHERE scene_id = %s ", esc_sql($scene_id)));
    if (($res->create_time+180)<time()) {
        echo json_encode(array('status' => 0));
        exit;
        //180秒内有效
    }
    // 查询openid
    $_prefix          = 'mpweixin';
    $_openid_meta_key = 'open_' . $_prefix . '_openid';
    $user_exist = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key=%s AND meta_value=%s", $_openid_meta_key, $res->openid));

    if (!$current_user_id && !empty($res) && $res->scene_id == $scene_id && !empty($res->openid)) {
        if (!empty($user_exist)) {
            wp_set_auth_cookie($user_exist, true, false);
            $user = get_user_by('id', $user_exist);
            do_action('wp_login', $user->user_login, $user); // 保证挂载的action执行
        }
        $status = 1;
    } else {
        $status = 0;
    }
    echo json_encode(array('status' => $status));
    exit;
}
add_action('wp_ajax_check_mpweixin_qr', 'check_mpweixin_qr');
add_action('wp_ajax_nopriv_check_mpweixin_qr', 'check_mpweixin_qr');



/**
 * [user_login 用户登录]
 * @Author   Dadong2g
 * @DateTime 2019-06-02T15:34:38+0800
 * @return   [type]                   [description]
 */
function user_login()
{
    header('Content-type:application/json; Charset=utf-8');
    $username   = !empty($_POST['username']) ? esc_sql($_POST['username']) : null;
    $password   = !empty($_POST['password']) ? esc_sql($_POST['password']) : null;
    $rememberme = !empty($_POST['rememberme']) ? esc_sql($_POST['rememberme']) : null;
    if (_cao('is_close_wplogin')) {
        echo json_encode(array('status' => '0', 'msg' => '仅开放社交账号登录'));
        exit;
    }
    $login_data                  = array();
    $login_data['user_login']    = $username;
    $login_data['user_password'] = $password;
    $login_data['remember']      = false;
    if (isset($rememberme) && $rememberme == '1') {
        $login_data['remember'] = true;
    }
    if (!$username || !$password) {
        echo json_encode(array('status' => '0', 'msg' => '请输入登录账号/密码'));
        exit;
    }
    //是否腾讯验证
    if (_cao('is_captcha_qq', '0') && @$_SESSION['is_tencentcaptcha'] == 0) {
        $_SESSION['is_tencentcaptcha'] = 0;
        echo json_encode(array('status' => '0', 'msg' => '安全验证失败'));
        exit;
    }
    $user_verify = wp_signon($login_data, false);
    if (is_wp_error($user_verify)) {
        echo json_encode(array('status' => '0', 'msg' => '你别调皮捣乱哦！'));
        exit;
    } else {
        echo json_encode(array('status' => '1', 'msg' => '登录成功'));
        exit;
    }
    exit();
}
add_action('wp_ajax_user_login', 'user_login');
add_action('wp_ajax_nopriv_user_login', 'user_login');

/**
 * [user_register 注册新用户]
 * @Author   Dadong2g
 * @DateTime 2019-06-02T15:34:30+0800
 * @return   [type]                   [description]
 */
function user_register()
{
    header('Content-type:application/json; Charset=utf-8');

    $user_name  = !empty($_POST['user_name']) ? sanitize_user($_POST['user_name']) : null;
    $user_email = !empty($_POST['user_email']) ? apply_filters('user_registration_email', $_POST['user_email']) : null;
    $user_pass  = !empty($_POST['user_pass']) ? esc_sql($_POST['user_pass']) : null;
    $user_pass2  = !empty($_POST['user_pass2']) ? esc_sql($_POST['user_pass2']) : null;
    if (!$user_name || !$user_email || !$user_pass) {
        echo json_encode(array('status' => '0', 'msg' => '注册信息错误'));
        exit;
    }
    if (_cao('is_close_wpreg')) {
        echo json_encode(array('status' => '0', 'msg' => '仅开放社交账号注册'));
        exit;
    }
    if (!validate_username($user_name)) {
        echo json_encode(array('status' => '0', 'msg' => '用户名包含无效字符'));
        exit;
    }
    if (username_exists($user_name)) {
        echo json_encode(array('status' => '0', 'msg' => '该用户名已被注册'));
        exit;
    }
    if (!is_email($user_email)) {
        echo json_encode(array('status' => '0', 'msg' => '邮箱地址错误'));
        exit;
    }
    if (email_exists($user_email)) {
        echo json_encode(array('status' => '0', 'msg' => '邮箱已经被注册'));
        exit;
    }
    if (strlen($user_pass) < 6) {
        echo json_encode(array('status' => '0', 'msg' => '密码长度不得小于6位'));
        exit;
    }
    if ($user_pass != $user_pass2) {
        echo json_encode(array('status' => '0', 'msg' => '两次输入的密码不一致'));
        exit;
    }
    // 是否需要邮箱验证
    if (_cao('is_email_reg_cap')) {
        if (empty($_POST['captcha']) || empty($_SESSION['CAO_code_captcha']) || trim(strtolower($_POST['captcha'])) != $_SESSION['CAO_code_captcha']) {
            echo json_encode(array('status' => '0', 'msg' => '验证码错误'));
            exit;
        }
        if ($_SESSION['CAO_code_captcha_email'] != $user_email) {
            echo json_encode(array('status' => '0', 'msg' => '验证码与邮箱不对应'));
            exit;
        }
    }
    //是否腾讯验证
    if (_cao('is_captcha_qq', '0') && @$_SESSION['is_tencentcaptcha'] == 0) {
        $_SESSION['is_tencentcaptcha'] = 0;
        echo json_encode(array('status' => '0', 'msg' => '安全验证失败'));
        exit;
    }
    // 验证通过
    $nweUserData = array(
        'ID'         => '',
        'user_login' => $user_name,
        'user_pass'  => $user_pass,
        'user_email' => $user_email,
        'role'       => get_option('default_role'),
    );
    $user_id = wp_insert_user($nweUserData);

    if (is_wp_error($user_id)) {
        echo json_encode(array('status' => '0', 'msg' => '注册失败，请重试'));
        exit;
    } else {
        wp_set_auth_cookie($user_id, true, false);
        wp_set_current_user($user_id);
        //发送邮件
        $message = __('注册成功！') . "\r\n\r\n";
        $message .= sprintf(__('用户名: %s'), $user_name) . "\r\n\r\n";
        //$message .= sprintf(__('密码: %s'), $user_pass) . "\r\n\r\n";

        if (_cao('is_mail_nitfy_reg')) {
            _sendMail($user_email, '注册信息', $message);
        }
        echo json_encode(array('status' => '1', 'msg' => '注册成功'));
        exit;
    }
    exit();
}
add_action('wp_ajax_user_register', 'user_register');
add_action('wp_ajax_nopriv_user_register', 'user_register');

/**
 * [sessioncode 生产验证码]
 * @Author   Dadong2g
 * @DateTime 2019-06-02T15:34:20+0800
 * @param    [type]                   $email [description]
 * @return   [type]                          [description]
 */
function sessioncode($email)
{
    $originalcode = '0,1,2,3,4,5,6,7,8,9';
    $originalcode = explode(',', $originalcode);
    $countdistrub = 10;
    $_dscode      = "";
    $counts       = 6;
    for ($j = 0; $j < $counts; $j++) {
        $dscode = $originalcode[rand(0, $countdistrub - 1)];
        $_dscode .= $dscode;
    }
    $_SESSION['CAO_code_captcha']       = strtolower($_dscode);
    $_SESSION['CAO_code_captcha_email'] = $email;
    $message                            = '验证码：' . $_dscode;
    $send_email                         = _sendMail($email, '验证码', $message);
    if ($send_email) {
        return true;
    }
    return false;
}

/**
 * [captcha_email 验证邮箱]
 * @Author   Dadong2g
 * @DateTime 2019-06-02T15:34:06+0800
 * @return   [type]                   [description]
 */
function captcha_email()
{
    header('Content-type:application/json; Charset=utf-8');
    global $wpdb;
    $user_email = !empty($_POST['user_email']) ? esc_sql($_POST['user_email']) : null;
    $user_email = apply_filters('user_registration_email', $user_email);
    $user_email = $wpdb->_escape(trim($user_email));

    if (email_exists($user_email)) {
        echo json_encode(array('status' => '0', 'msg' => '邮箱已存在'));
        exit;
    } else {
        $send_email = sessioncode($user_email);
        if ($send_email) {
            echo json_encode(array('status' => '1', 'msg' => '发送成功'));
            exit;
        } else {
            echo json_encode(array('status' => '0', 'msg' => '发送失败'));
            exit;
        }
    }
    exit();
}
add_action('wp_ajax_captcha_email', 'captcha_email');
add_action('wp_ajax_nopriv_captcha_email', 'captcha_email');

//腾讯防水墙
function tencentcaptcha()
{
    header('Content-type:application/json; Charset=utf-8');
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $cip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif (!empty($_SERVER["REMOTE_ADDR"])) {
        $cip = $_SERVER["REMOTE_ADDR"];
    } else {
        $cip = '';
    }
    preg_match("/[\d\.]{7,15}/", $cip, $cips);
    $cip = isset($cips[0]) ? $cips[0] : 'unknown';
    unset($cips);

    $AppSecretKey = _cao('captcha_qq_secretkey', '');
    $appid = !empty($_POST['appid']) ? $_POST['appid'] : null;
    $Ticket = !empty($_POST['Ticket']) ? $_POST['Ticket'] : null;
    $Randstr = !empty($_POST['Randstr']) ? $_POST['Randstr'] : null;
    $UserIP = $cip;
    $url = "https://ssl.captcha.qq.com/ticket/verify";
    $params = array(
        "aid" => $appid,
        "AppSecretKey" => $AppSecretKey,
        "Ticket" => $Ticket,
        "Randstr" => $Randstr,
        "UserIP" => $UserIP
    );
    $paramstring = http_build_query($params);
    $geturl = $url.'?'.$paramstring;
    $content = tx_http_curl($geturl);
    $result = json_decode($content, true);
    if ($result) {
        if ($result['response'] == 1) {
            $_SESSION['is_tencentcaptcha'] = 1;
            echo json_encode(array('status' => '1', 'msg' => '验证通过'));
            exit;
        } else {
            $_SESSION['is_tencentcaptcha'] = 0;
            echo json_encode(array('status' => '0', 'msg' => $result['err_msg']));
            exit;
        }
    } else {
        $_SESSION['is_tencentcaptcha'] = 0;
        echo json_encode(array('status' => '0', 'msg' => '请求失败'));
        exit;
    }
    exit();
}
add_action('wp_ajax_tencentcaptcha', 'tencentcaptcha');
add_action('wp_ajax_nopriv_tencentcaptcha', 'tencentcaptcha');

function tx_http_curl($url, $type='get', $res='json', $arr='')
{
    //1.初始化curl
    $ch = curl_init();
    //2.设置curl的参数
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($type == 'post') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
    }
    //3.采集
    $output = curl_exec($ch);
    //4.关闭
    curl_close($ch);
    if ($res=='json') {
        if (curl_error($ch)) {
            //请求失败，返回错误信息
            return curl_error($ch);
        } else {
            //请求成功，返回信息
            return $output;
        }
    }
}

/**
 * @package caozhuti
 */

/**
 * [isLoginCheck 登陆状态验证]
 * @Author   Dadong2g
 * @DateTime 2019-05-31T13:12:49+0800
 * @return   boolean                  [description]
 */
function isLoginCheck()
{
    if (!is_user_logged_in()) {
        header('Allow: POST');
        header('HTTP/1.1 503 Method Not Allowed');
        header('Content-Type: text/plain');
        exit;
    }
}



//投稿 write_post
function cao_write_post()
{
    header('Content-type:application/json; Charset=utf-8');
    global $current_user;
    $uid = $current_user->ID;
    isLoginCheck(); //检测登录
    $nonce   = !empty($_POST['nonce']) ? $_POST['nonce'] : null;
    if ($nonce && !wp_verify_nonce($nonce, 'caoclick-' . $uid)) {
        echo json_encode(array('status' => '0', 'msg' => '非法请求'));
        exit;
    }
    $edit_id = !empty($_POST['edit_id']) ? (int)sanitize_text_field(trim($_POST['edit_id'])) : 0;
    $post_title = !empty($_POST['post_title']) ? sanitize_text_field(trim($_POST['post_title'])) : '';
    $post_content = !empty($_POST['post_content']) ? trim($_POST['post_content']) : '';
    $post_excerpt = !empty($_POST['post_excerpt']) ? sanitize_text_field(trim($_POST['post_excerpt'])) : '';
    $post_cat = !empty($_POST['post_cat']) ? (int)sanitize_text_field(trim($_POST['post_cat'])) : 1;
    $cao_status = !empty($_POST['cao_status']) ? trim($_POST['cao_status']) : 0;
    $cao_status = ($cao_status == 'fee') ? 1 : 0;
    $cao_price = !empty($_POST['cao_price']) ? (int)sanitize_text_field(trim($_POST['cao_price'])) : 0;
    $cao_vip_rate = !empty($_POST['cao_vip_rate']) ? sanitize_text_field(trim($_POST['cao_vip_rate'])) : 1;
    $cao_pwd = !empty($_POST['cao_pwd']) ? sanitize_text_field(trim($_POST['cao_pwd'])) : '';
    $cao_downurl = !empty($_POST['cao_downurl']) ? esc_url(trim($_POST['cao_downurl'])) : '';
    $post_status = !empty($_POST['post_status']) ? $_POST['post_status'] : '';
    $post_status = in_array($post_status, array('publish', 'draft', 'pending')) ? $post_status : 'draft';

    if (!_cao('is_all_publish_posts') && !current_user_can('publish_posts')) {
        echo json_encode(array('status' => '0', 'msg' => '您没有权限发布或修改文章'));
        exit;
    }

    if (strlen($post_content) < 100) {
        echo json_encode(array('status' => '0', 'msg' => '文章内容最低100个字符'));
        exit;
    }
    // 如果是编辑
    if ($edit_id > 0) {
        // 插入文章
        $new_post = wp_update_post(array( //Return: The ID of the post if the post is successfully updated in the database. Otherwise returns 0
            'ID'            => $edit_id,
            'post_title'    => $post_title,
            'post_excerpt'  => $post_excerpt,
            'post_content'  => $post_content,
            'post_status'   => $post_status,
            'post_author'   => get_current_user_id(),
            'post_category' => array($post_cat)
        ));
    } else {
        // 插入文章
        $new_post = wp_insert_post(array(
            'post_title'    => $post_title,
            'post_excerpt'  => $post_excerpt,
            'post_content'  => $post_content,
            'post_status'   => $post_status,
            'post_author'   => get_current_user_id(),
            'post_category' => array($post_cat),
            'tags_input'    => ''
        ));
    }


    if ($new_post instanceof WP_Error) {
        echo json_encode(array('status' => '0', 'msg' => '网络错误，请重试或联系管理员'));
        exit;
    }

    // 如果是直接发布的 挂钩 用于后期添加
    if ($post_status == 'publish') {
        do_action('cao_immediate_to_publish', $new_post);
    }

    // 更新Meta
    $_cao_status = ($cao_status>0) ? 1 : 0 ;
    update_post_meta($new_post, 'cao_status', $_cao_status);
    update_post_meta($new_post, 'cao_price', $cao_price);
    update_post_meta($new_post, 'cao_vip_rate', $cao_vip_rate);
    update_post_meta($new_post, 'cao_pwd', $cao_pwd);
    update_post_meta($new_post, 'cao_downurl', $cao_downurl);
    update_post_meta($new_post, 'post_style', 'sidebar');

    echo json_encode(array('status' => '1', 'msg' => '提交成功，审核后公开'));
    exit;
}
add_action('wp_ajax_cao_write_post', 'cao_write_post');
add_action('wp_ajax_nopriv_cao_write_post', 'cao_write_post');



// 上传头像avatar_photo
function update_avatar_photo()
{
    if (_cao('disabled_up_ava')) {
        echo json_encode(array('status' => '0', 'msg' => '头像功能关闭'));
        exit;
    }

    header('Content-type:application/json; Charset=utf-8');
    global $current_user;
    $uid = $current_user->ID;
    isLoginCheck(); //检测登录
    $nonce   = !empty($_POST['nonce']) ? $_POST['nonce'] : null;
    $file = !empty($_FILES['file']) ? $_FILES['file'] : null;
    if ($nonce && !wp_verify_nonce($nonce, 'caoclick-' . $uid)) {
        echo json_encode(array('status' => '0', 'msg' => '非法请求'));
        exit;
    }

    $allowMime = array('image/jpg', 'image/gif', 'image/png', 'image/bmp', 'image/pjpeg', "image/jpeg");
    if (!in_array($file['type'], $allowMime)) {
        echo json_encode(array('status' => '0', 'msg' => '图片类型错误'));
        exit;
    }

    //如果扩展名是图片，就进行检测
    $this_img = @getimagesize($file['tmp_name']);//读取图片信息
    if (empty($this_img)) {
        echo json_encode(array('status' => '0', 'msg' => '图片类型错误'));
        exit;
    }
    $typearr = array(1 => 'gif', 2 => 'jpg', 3 => 'png');
    $this_type = $typearr[$this_img[2]];
    if (!in_array($this_type, $typearr)) {
        echo json_encode(array('status' => '0', 'msg' => '图片类型错误'));
        exit;
    }
    if ($file['type']=="image/gif") {
        $img=@imagecreatefromgif($file['tmp_name']);
    } elseif ($file['type']=="image/png" || $file['type']=="image/x-png") {
        $img=@imagecreatefrompng($file['tmp_name']);
    } else {
        $img=@imagecreatefromjpeg($file['tmp_name']);
    }
    if ($img==false) {
        echo json_encode(array('status' => '0', 'msg' => '图片类型错误'));
        exit;
    }

    if (is_uploaded_file($file['tmp_name']) && is_user_logged_in()) {
        $picname = $file['name'];
        $picsize = $file['size'];
        $arrType = array('image/jpg', 'image/gif', 'image/png', 'image/bmp', 'image/pjpeg', "image/jpeg");
        $userid  = $uid;
        $rand    = (rand(10, 100));
        if ($picname != "") {
            if ($picsize > 200400) {
                echo json_encode(array('status' => '0', 'msg' => '头像最大限制200KB'));
                exit;
            } elseif (!in_array($file['type'], $arrType)) {
                echo json_encode(array('status' => '0', 'msg' => '图片类型错误'));
                exit;
            } else {
                ///////////////////////
                $upload_dir = wp_upload_dir();
                $poster_dir = $upload_dir['basedir'] . '/avatar/';

                if (!is_dir($poster_dir)) {
                    wp_mkdir_p($poster_dir);
                }
                //获取文件后缀
                $_filesubstr  = substr(strrchr($file['name'], '.'), 1);
                if (!in_array($_filesubstr, $typearr)) {
                    echo json_encode(array('status' => '0', 'msg' => '图片类型错误'));
                    exit;
                }
                $filename = 'avatar-' . $userid . '.' .$_filesubstr;
                $_file     = $poster_dir . $filename;

                if (!move_uploaded_file($file['tmp_name'], $_file)) {
                    echo json_encode(array('status' => '0', 'msg' => '上传失败'));
                    exit;
                }
                //unlink($file);
                $src = $upload_dir['baseurl'] . '/avatar/' . $filename;
                // 是否开启CDN兼容
                if (_cao('disabled_wp_cdn') && _cao('_wp_cdn_domain')) {
                    $src = str_replace(_cao('_wp_cdn_domain'), esc_url(home_url('/')), $src);
                }
                error_reporting(0);
                if (is_wp_error($src)) {
                    echo json_encode(array('status' => '0', 'msg' => '上传失败'));
                    exit;
                } else {
                    update_user_meta($userid, 'user_custom_avatar', $src);
                    echo json_encode(array('status' => '1', 'msg' => '上传成功'));
                    exit;
                }
            }
        }
    }
    echo json_encode(array('status' => '0', 'msg' => '文件错误'));
    exit;
}
add_action('wp_ajax_update_avatar_photo', 'update_avatar_photo');


/**
 * [update_img 新增文件类型验证安全]
 * @Author   Dadong2g
 * @DateTime 2019-10-25T20:20:45+0800
 * @return   [type]                   [description]
 */
function update_img()
{
    header('Content-type:application/json; Charset=utf-8');
    global $current_user;
    $uid = $current_user->ID;
    isLoginCheck(); //检测登录
    $nonce   = !empty($_POST['nonce']) ? $_POST['nonce'] : null;
    $file = !empty($_FILES['file']) ? $_FILES['file'] : null;
    if ($nonce && !wp_verify_nonce($nonce, 'caoclick-' . $uid)) {
        echo json_encode(array('status' => '0', 'msg' => '非法请求'));
        exit;
    }
    $file_index = mb_strrpos($file["name"], '.'); //扩展名定位

    //图片验证
    $is_img = getimagesize($file["tmp_name"]);

    if (!$is_img && true) {
        echo json_encode(array('status' => '0', 'msg' => '上传文件类型错误'));
        exit;
    }
    //图片类型验证
    $image_type = ['image/jpg', 'image/gif', 'image/png', 'image/bmp', 'image/pjpeg', "image/jpeg", "image/webp"];
    if (!in_array($file['type'], $image_type) && true) {
        echo json_encode(array('status' => '0', 'msg' => '禁止上传非图片类型文件'));
        exit;
    }
    //图片后缀验证
    $postfix = ['.png','.jpg','.jpeg','pjpeg','gif','bmp','webp'];
    $file_postfix = strtolower(mb_substr($file["name"], $file_index));
    if (!in_array($file_postfix, $postfix) && true) {
        echo json_encode(array('status' => '0', 'msg' => '上传后缀不允许'));
        exit;
    }
    if (!empty($file)) {
        // 获取上传目录信息
        $wp_upload_dir = wp_upload_dir();
        // 将上传的图片文件移动到上传目录 md5纯命名图片
        $basename   = _new_filename($file['name']);
        $filename   = $wp_upload_dir['path'] . '/' . $basename;
        $re         = rename($file['tmp_name'], $filename);
        $attachment = array(
                'guid'           => $wp_upload_dir['url'] . '/' . $basename,
                'post_mime_type' => $file['type'],
                'post_title'     => preg_replace('/\.[^.]+$/', '', $basename),
                'post_content'   => '',
                'post_status'    => 'inherit'
        );
        $attach_id  = wp_insert_attachment($attachment, $filename);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
        wp_update_attachment_metadata($attach_id, $attach_data);
        // 返回图片地址和状态
        echo json_encode(
            array('errno' => '0',
             'data' => array(wp_get_attachment_url($attach_id))
            )
        );
        exit;
    }


    // 返回图片地址和状态
    echo json_encode(array('errno' => '1', 'data' => array()));
    exit;
}
add_action('wp_ajax_update_img', 'update_img');
add_action('wp_ajax_nopriv_update_img', 'update_img');





/**
 * [cdk_pay 卡密充值]
 * @Author   Dadong2g
 * @DateTime 2019-06-02T15:33:58+0800
 * @return   [type]                   [description]
 */
function cdk_pay()
{
    header('Content-type:application/json; Charset=utf-8');
    global $current_user;
    $uid = $current_user->ID;
    isLoginCheck(); //检测登录
    $cdkcode = !empty($_POST['cdkcode']) ? sanitize_text_field(wp_unslash($_POST[ 'cdkcode' ])) : null;
    $nonce   = !empty($_POST['nonce']) ? $_POST['nonce'] : null;
    if ($nonce && !wp_verify_nonce($nonce, 'caoclick-' . $uid)) {
        echo json_encode(array('status' => '0', 'msg' => '非法请求'));
        exit;
    }
    // 验证长度
    if ($cdkcode && strlen($cdkcode) != 12) {
        echo json_encode(array('status' => '0', 'msg' => '卡密错误'));
        exit;
    }

    // 实例化卡密
    $CaoCdk    = new CaoCdk();
    $cdk_money = sprintf('%0.2f', $cdk_money);
    $cdk_money = $CaoCdk->checkCdk($cdkcode);
    if (!$cdk_money) {
        echo json_encode(array('status' => '0', 'msg' => '卡密无效'));
        exit;
    }

    // 卡密有效 进行换算
    $CaoUser   = new CaoUser($uid);
    $old_money = $CaoUser->get_balance();
    if (!$CaoUser->update_balance($cdk_money)) {
        echo json_encode(array('status' => '0', 'msg' => '兑换失败'));
        exit;
    }
    // 充值余额成功 废弃卡密 updataCdk
    if (!$CaoCdk->updataCdk($cdkcode)) {
        echo json_encode(array('status' => '0', 'msg' => '卡密异常'));
        exit;
    }

    // 添加纪录
    if ($uid) {
        $Caolog    = new Caolog();
        $new_money = $old_money + $cdk_money;
        $note      = '卡密充值 [' . $cdkcode . '] +' . $cdk_money;
        $Caolog->addlog($uid, $old_money, $cdk_money, $new_money, 'cdk', $note);
    }

    echo json_encode(array('status' => '1', 'msg' => '卡密充值成功'));
    if (_cao('is_mail_nitfy_cdk')) {
        _sendMail($current_user->user_email, '卡密充值成功', $note);
    }
    exit;
}
add_action('wp_ajax_cdk_pay', 'cdk_pay');
add_action('wp_ajax_nopriv_cdk_pay', 'cdk_pay');


// 提现申请
function add_reflog()
{
    header('Content-type:application/json; Charset=utf-8');
    if (_cao('is_ref_to_rmb')) {
        echo json_encode(array('status' => '0', 'msg' => 'RMB提现功能未开启'));
        exit;
    }
    global $current_user;
    $uid = $current_user->ID;
    isLoginCheck(); //检测登录
    $money = !empty($_POST['money']) ? (int)$_POST['money'] : 0;
    $nonce   = !empty($_POST['nonce']) ? $_POST['nonce'] : null;
    if ($nonce && !wp_verify_nonce($nonce, 'caoclick-' . $uid)) {
        echo json_encode(array('status' => '0', 'msg' => '非法请求'));
        exit;
    }
    $site_min_tixian_num = _cao('site_min_tixian_num');
    $Reflog = new Reflog($uid);
    // 验证长度
    if ($money < $site_min_tixian_num) {
        echo json_encode(array('status' => '0', 'msg' => '提现金额最低'.$site_min_tixian_num.'元起'));
        exit;
    }

    if ($money > $Reflog->get_ke_bonus()) {
        echo json_encode(array('status' => '0', 'msg' => '可提现金额不足'));
        exit;
    }
    $note = '用户ID：'.$uid.' 申请提现';
    if ($Reflog->addlog($money, $note)) {
        echo json_encode(array('status' => '1', 'msg' => '提现申请成功，将尽快为您转账'));
        exit;
    } else {
        echo json_encode(array('status' => '0', 'msg' => '申请失败，稍后再试'));
        exit;
    }
}
add_action('wp_ajax_add_reflog', 'add_reflog');
add_action('wp_ajax_nopriv_add_reflog', 'add_reflog');



// 提现站内余额申请
function add_reflog2()
{
    header('Content-type:application/json; Charset=utf-8');
    global $current_user;
    $uid = $current_user->ID;
    isLoginCheck(); //检测登录
    $money = !empty($_POST['money']) ? (int)$_POST['money'] : 0;
    $nonce   = !empty($_POST['nonce']) ? $_POST['nonce'] : null;
    if ($nonce && !wp_verify_nonce($nonce, 'caoclick-' . $uid)) {
        echo json_encode(array('status' => '0', 'msg' => '非法请求'));
        exit;
    }
    $site_min_tixian_num = _cao('site_min_tixian_num');
    $Reflog = new Reflog($uid);
    // 验证长度
    if ($money < $site_min_tixian_num) {
        echo json_encode(array('status' => '0', 'msg' => '提现金额最低'.$site_min_tixian_num.'元起'));
        exit;
    }

    if ($money > $Reflog->get_ke_bonus()) {
        echo json_encode(array('status' => '0', 'msg' => '可提现金额不足'));
        exit;
    }
    $note = '用户ID：'.$uid.' 提现到站内余额';
    if ($Reflog->addlog($money, $note)) {
        // $money 兑换
        $charge_rate  = (int) _cao('site_change_rate'); //充值比例
        $CaoUser   = new CaoUser($uid);
        $old_money = $CaoUser->get_balance();
        $add_money = $money*$charge_rate;
        if (!$CaoUser->update_balance($add_money)) {
            echo json_encode(array('status' => '0', 'msg' => '佣金兑换失败'));
            exit;
        }
        // 兑换成功 添加纪录
        if ($uid) {
            $Caolog    = new Caolog();
            $new_money = $old_money + $add_money;
            $note      = '佣金提现兑换 [￥' . $money . '] +' . $add_money;
            $Caolog->addlog($uid, $old_money, $add_money, $new_money, 'other', $note);
        }

        echo json_encode(array('status' => '1', 'msg' => '提现成功，已经自动兑换到您的可用余额'));
        exit;
    } else {
        echo json_encode(array('status' => '0', 'msg' => '申请失败，稍后再试'));
        exit;
    }
}
add_action('wp_ajax_add_reflog2', 'add_reflog2');
add_action('wp_ajax_nopriv_add_reflog2', 'add_reflog2');


/**
 * [charge_pay 在线付款支付]
 * @Author   Dadong2g
 * @DateTime 2019-06-03T22:28:59+0800
 * @return   [type]                   [JOSN]
 */
function charge_pay()
{
    header('Content-type:application/json; Charset=utf-8');
    $ip = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1'; //客户端IP
    global $current_user;
    $uid = $current_user->ID;
    isLoginCheck(); //检测登录
    $nonce      = !empty($_POST['nonce']) ? $_POST['nonce'] : null;
    $charge_num = !empty($_POST['charge_num']) ? (int)$_POST['charge_num'] : null;
    $pay_type   = !empty($_POST['pay_type']) ? (int) $_POST['pay_type'] : null; //1支付宝；2微信
    if ($nonce && !wp_verify_nonce($nonce, 'caoclick-' . $uid)) {
        echo json_encode(array('status' => '0', 'msg' => '非法请求'));
        exit;
    }

    // 基础验证通过 验证前台表单数据 充值数量和支付方式
    $min_cahrge_num =_cao('min_cahrge_num', '1');
    $max_cahrge_num =_cao('max_cahrge_num', '1000');
    if (!$charge_num || $charge_num < 0) {
        echo json_encode(array('status' => '0', 'msg' => '请输入充值数量'));
        exit;
    }
    if ($charge_num < $min_cahrge_num) {
        echo json_encode(array('status' => '0', 'msg' => '最低充值数量限额：'.$min_cahrge_num));
        exit;
    }
    if ($charge_num > $max_cahrge_num) {
        echo json_encode(array('status' => '0', 'msg' => '最高充值数量限额：'.$max_cahrge_num));
        exit;
    }
    if (!isset($pay_type) || $pay_type == 0) {
        echo json_encode(array('status' => '0', 'msg' => '请选择支付方式'));
        exit;
    }

    // 实例化订单
    $ShopOrder = new ShopOrder();

    /////////商品属性START///////
    $charge_rate    = (int) _cao('site_change_rate'); //充值比例
    $order_price    = sprintf('%0.2f', $charge_num / $charge_rate); // 订单价格 换算人民币,保留两位小数点
    $order_trade_no = date("ymdhis") . mt_rand(100, 999) . mt_rand(100, 999) . mt_rand(100, 999); // 订单号
    if (_cao('is_ripro_diy_shop_name')) {
        $order_name = _cao('ripro_diy_shop_name_charge'); //自定义订单名称
    } else {
        $order_name = get_bloginfo('name') . '-余额充值'; //订单名称
    }

    $order_type     = 'charge'; //类型 充值
    /////////商品属性END/////////

    // 判断支付方式 1 支付宝 START
    if ($pay_type == 1) {
        // 获取后台支付宝配置
        $aliPayConfig = _cao('alipay');
        // 判断是否开启手机版跳转
        if (wp_is_mobile() && $aliPayConfig['is_mobile']) {
            // 添加订单 ShopOrder
            if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
                echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
                exit;
            }
            // 支付宝公共配置
            $params         = new \Yurun\PaySDK\Alipay\Params\PublicParams;
            $params->appID  = $aliPayConfig['pid'];
            $params->md5Key = $aliPayConfig['md5Key'];
            // SDK实例化，传入公共配置
            $pay       = new \Yurun\PaySDK\Alipay\SDK($params);
            // 支付接口
            $request    = new \Yurun\PaySDK\Alipay\Params\WapPay\Request;
            $request->notify_url    = get_template_directory_uri() . '/shop/alipay/notify.php';
            $request->return_url    = get_template_directory_uri() . '/shop/alipay/return.php'; // 支付后跳转返回地址
            $request->businessParams->seller_id    = $aliPayConfig['pid']; // 卖家支付宝用户号
            $request->businessParams->out_trade_no = $order_trade_no; // 商户订单号
            $request->businessParams->total_fee    = $order_price; // 价格
            $request->businessParams->subject      = $order_name; // 商品标题
            $request->businessParams->show_url     = get_permalink($post_id); // 用户付款中途退出返回商户网站的地址。

            $payurl = $pay->redirectExecuteUrl($request);
            $_SESSION['ali_session_order_trade_no'] = $order_trade_no;
            // type 1 = 扫码支付  2 跳转支付
            echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $payurl, 'qrcode' => '', 'msg' => $order_trade_no));
            exit;
        } elseif (!$aliPayConfig['is_pcqr']) {
            // 支付宝-电脑网站支付
            // 添加订单 ShopOrder
            if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
                echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
                exit;
            }
            // 支付宝公共配置
            $params         = new \Yurun\PaySDK\Alipay\Params\PublicParams;
            $params->appID  = $aliPayConfig['pid'];
            $params->md5Key = $aliPayConfig['md5Key'];
            // SDK实例化，传入公共配置
            $pay       = new \Yurun\PaySDK\Alipay\SDK($params);
            // 支付接口
            $request = new \Yurun\PaySDK\Alipay\Params\Pay\Request;
            $request->notify_url    = get_template_directory_uri() . '/shop/alipay/notify.php';
            $request->return_url    = get_template_directory_uri() . '/shop/alipay/return.php'; // 支付后跳转返回地址
            $request->businessParams->seller_id    = $aliPayConfig['pid']; // 卖家支付宝用户号
            $request->businessParams->out_trade_no = $order_trade_no; // 商户订单号
            $request->businessParams->total_fee    = $order_price; // 价格
            $request->businessParams->subject      = $order_name; // 商品标题
            // 跳转到支付宝页面
            $payurl = $pay->redirectExecuteUrl($request);
            // var_dump($payurl);
            // type 1 = 扫码支付  2 跳转支付
            echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $payurl, 'qrcode' => '', 'msg' => $order_trade_no));
            exit;
        } else {
            // 应用模式公共配置-当面付
            // 添加订单 ShopOrder
            if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
                echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
                exit;
            }
            // 更换公共配置文件
            $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
            $params->appID = $aliPayConfig['appid'];
            $params->appPrivateKey = $aliPayConfig['privateKey'];
            $params->appPublicKey = $aliPayConfig['publicKey'];
            // SDK实例化，传入公共配置
            $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
            // 支付接口
            $request = new \Yurun\PaySDK\AlipayApp\FTF\Params\QR\Request;
            $request->notify_url    = get_template_directory_uri() . '/shop/alipay/notify2.php'; // 支付后通知地址
            $request->businessParams->out_trade_no = $order_trade_no; // 商户订单号
            $request->businessParams->total_amount = $order_price; // 价格
            $request->businessParams->subject      = $order_name; // 商品标题

            // 调用接口
            try {
                $data = $pay->execute($request);
            } catch (Exception $e) {
                var_dump($pay->response->body());
            }
            // QR内容
            $qrimg = getQrcode($data['alipay_trade_precreate_response']['qr_code']);

            $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/alipay.png" class="qr-pay">';
            $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">支付宝扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$qrimg.'"/> </div> <div class="bottom alipay"> 请使用支付宝扫一扫<br>扫描二维码支付</br> </div> </div>';
            echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $qrimg, 'num' => $order_trade_no));
            exit;
        }
    }
    //END ALIPAY

    // 2 微信
    if ($pay_type == 2) {
        // 获取后台支付配置
        $wxPayConfig = _cao('weixinpay');
        // 公共配置
        $params = new \Yurun\PaySDK\Weixin\Params\PublicParams;
        $params->appID = $wxPayConfig['appid'];
        $params->mch_id = $wxPayConfig['mch_id'];
        $params->key = $wxPayConfig['key'];
        // SDK实例化，传入公共配置
        $pay = new \Yurun\PaySDK\Weixin\SDK($params);
        $the_openid = get_user_meta($uid, 'open_mpweixin_openid', true);
        // 判断是否开启手机版跳转
        if (wp_is_mobile() && $wxPayConfig['is_mobile'] && !is_weixin_view()) {
            // 添加订单 ShopOrder
            if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
                echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
                exit;
            }

            // 支付接口H5
            $request = new \Yurun\PaySDK\Weixin\H5\Params\Pay\Request;
            $request->body = $order_name; // 商品描述
            $request->out_trade_no = $order_trade_no; // 订单号
            $request->total_fee = $order_price*100; // 订单总金额，单位为：分
            $request->spbill_create_ip = $ip; // 客户端ip，必须传正确的用户ip，否则会报错
            $request->notify_url = get_template_directory_uri() . '/shop/weixin/notify.php'; // 异步通知地址
            $request->scene_info = new \Yurun\PaySDK\Weixin\H5\Params\SceneInfo;
            $request->scene_info->type = 'Wap'; // 可选值：IOS、Android、Wap
            // 下面参数根据type不同而不同
            $request->scene_info->wap_url = get_template_directory_uri() . '/shop/weixin/return.php';
            $request->scene_info->wap_name = get_bloginfo('name');
            // 调用接口
            $result = $pay->execute($request);
            if ($pay->checkResult()) {
                $_SESSION['wx_session_order_trade_no'] = $order_trade_no;
                echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $result['mweb_url'], 'qrcode' => 'h5', 'num' => $order_trade_no));
                exit;
            } else {
                $error_msg = $pay->getErrorCode() . ':' . $pay->getError();
                echo json_encode(array('status' => '0', 'msg' => $error_msg));
                exit;
            }
        } elseif (_cao('is_oauth_mpweixin') && $wxPayConfig['is_jsapi'] && is_weixin_view() && is_user_logged_in() && !empty($the_openid)) {
            // 添加订单 ShopOrder
            if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
                echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
                exit;
            }
            # JSAPI 模式
            $request = new \Yurun\PaySDK\Weixin\JSAPI\Params\Pay\Request;
            $request->body = $order_name; // 商品描述
            $request->out_trade_no = $order_trade_no; // 订单号
            $request->total_fee = $order_price*100; // 订单总金额，单位为：分
            $request->spbill_create_ip = $ip; // 客户端ip
            $request->notify_url = get_template_directory_uri() . '/shop/weixin/notify.php'; // 异步通知地址
            $request->openid = $the_openid; // 必须设置openid
            // 调用接口
            $result = $pay->execute($request);
            if ($pay->checkResult()) {
                $request = new \Yurun\PaySDK\Weixin\JSAPI\Params\JSParams\Request;
                $request->prepay_id = $result['prepay_id'];
                $jsapiParams = $pay->execute($request);
                // 最后需要将数据传给js，使用WeixinJSBridge进行支付
                echo json_encode(array('status' => '1', 'type' => '3', 'msg' => $jsapiParams, 'img' => '', 'num' => $order_trade_no));
                exit;
            } else {
                echo json_encode(array('status' => '0', 'msg' => '未绑定公众号登录或网络错误'));
                exit;
            }
        } else {
            // PC使用当面付返回二维码
            // 添加订单 ShopOrder
            if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
                echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
                exit;
            }
            // 支付接口 PC扫码
            $request = new \Yurun\PaySDK\Weixin\Native\Params\Pay\Request;
            $request->body = $order_name; // 商品描述
            $request->out_trade_no = $order_trade_no; // 订单号
            $request->total_fee = $order_price*100; // 订单总金额，单位为：分
            $request->spbill_create_ip = $ip; // 客户端ip
            $request->notify_url = get_template_directory_uri() . '/shop/weixin/notify.php'; // 异步通知地址
            // 调用接口
            $result = $pay->execute($request);
            $shortUrl = $result['code_url'];
            if (is_array($result) && $shortUrl) {
                // 获取成功 返回QR内容
                $qrimg = getQrcode($shortUrl);
                $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/weixin.png" class="qr-pay">';
                $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">微信扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$qrimg.'"/> </div> <div class="bottom weixinpay"> 请使用微信扫一扫<br>扫描二维码支付</br> </div> </div>';
                echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $qrimg, 'num' => $order_trade_no));
                exit;
            } else {
                echo json_encode(array('status' => '0', 'msg' => '接口网络异常'));
                exit;
            }
        }
    }

    //PAYJS
    if ($pay_type == 4) {
        require_once get_template_directory() . '/inc/class/Payjs.class.php';
        // 获取后台支付配置
        $PayJsConfig = _cao('payjs');
        // 配置通信参数
        $config = [
            'mchid' => $PayJsConfig['mchid'],   // 配置商户号
            'key'   => $PayJsConfig['key'],   // 配置通信密钥
        ];
        // 初始化 PAYJS
        $payjs = new Payjs($config);
        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }
        if (false) {
            // 手机模式因openid获取问题 暂时未开放
        } else {
            // 构造订单基础信息
            $data = [
                'body' => $order_name,                        // 订单标题
                'total_fee' => $order_price*100,                           // 订单金额
                'out_trade_no' => $order_trade_no,                   // 订单号
                'attach' => 'payjs_order_attach',            // 订单附加信息(可选参数)
                'notify_url' => get_template_directory_uri() . '/shop/payjs/notify.php',    // 异步通知地址(可选参数)
            ];
            $result = $payjs->native($data);
            // var_dump($result);die;
            if (is_array($result) && $result['return_code'] == 1) {
                $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/weixin.png" class="qr-pay">';
                $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">微信扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$result['qrcode'].'"/> </div> <div class="bottom weixinpay"> 请使用微信扫一扫<br>扫描二维码支付</br> </div> </div>';
                echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $result['qrcode'], 'num' => $order_trade_no));
                exit;
            } else {
                echo json_encode(array('status' => '0', 'msg' => 'PAYJS接口异常'));
                exit;
            }
        }

        echo json_encode(array('status' => '0', 'msg' => '请配置payjs参数'));
        exit;
    }

    //虎皮椒支付 讯虎支付 V3 微信
    if ($pay_type == 5) {
        require_once get_template_directory() . '/inc/class/xunhupay.class.php';
        // 获取后台支付配置
        $XHpayConfig = _cao('xunhupay');

        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }

        $data=array(
            'version'   => '1.1',//固定值，api 版本，目前暂时是1.1
            'lang'       => 'zh-cn', //必须的，zh-cn或en-us 或其他，根据语言显示页面
            'plugins'   => 'ripro-xunhupay-v3',//必须的，根据自己需要自定义插件ID，唯一的，匹配[a-zA-Z\d\-_]+
            'appid'     => $XHpayConfig['appid'], //必须的，APPID
            'trade_order_id'=> $order_trade_no, //必须的，网站订单ID，唯一的，匹配[a-zA-Z\d\-_]+
            'payment'   => 'wechat',//必须的，支付接口标识：wechat(微信接口)|alipay(支付宝接口)
            'type'   => 'WAP',//固定值"WAP"
            'wap_url'   => home_url(),//网站域名，必填
            'wap_name'   => home_url(),//网站域名，或者名字，必填，长度32或以内到分(测试账户只支持0.1元内付款)
	    'total_fee' => $order_price,//人民币，单位精确到分(测试账户只支持0.1元内付款)
            'title'     => $order_name, //必须的，订单标题，长度32或以内
            'time'      => time(),//必须的，当前时间戳，根据此字段判断订单请求是否已超时，防止第三方攻击服务器
            'notify_url'=>  get_template_directory_uri() . '/shop/xunhupay/notify.php', //必须的，支付成功异步回调接口
            'return_url'=> get_template_directory_uri() . '/shop/xunhupay/return.php',//必须的，支付成功后的跳转地址
            'callback_url'=> esc_url(home_url('/user?action=charge')),//必须的，支付发起地址（未支付或支付失败，系统会会跳到这个地址让用户修改支付信息）
            'modal'=>null, //可空，支付模式 ，可选值( full:返回完整的支付网页; qrcode:返回二维码; 空值:返回支付跳转链接)
            'nonce_str' => str_shuffle(time())//必须的，随机字符串，作用：1.避免服务器缓存，2.防止安全密钥被猜测出来
        );

        $hashkey =$XHpayConfig['appsecret'];
        $data['hash']     = XH_Payment_Api::generate_xh_hash($data, $hashkey);
        $url              = $XHpayConfig['url_do'];

        try {
            $response     = XH_Payment_Api::http_post($url, json_encode($data));
            /**
             * 支付回调数据
             * @var array(
             *      order_id,//支付系统订单ID
             *      url//支付跳转地址
             *  )
             */
            $result       = $response?json_decode($response, true):null;
            if (!$result) {
                throw new Exception('Internal server error', 500);
            }

            $hash         = XH_Payment_Api::generate_xh_hash($result, $hashkey);
            if (!isset($result['hash'])|| $hash!=$result['hash']) {
                throw new Exception(__('Invalid sign!', XH_Wechat_Payment), 40029);
            }

            if ($result['errcode']!=0) {
                throw new Exception($result['errmsg'], $result['errcode']);
            }
            //虎皮椒H5支付判断
            if (XH_Payment_Api::is_app_client()) {
                echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $result['url'], 'qrcode' => '', 'msg' => $order_trade_no));
                exit;
            }
            if ($XHpayConfig['is_pop_qrcode'] && !is_weixin_view()) {
                //获取二维码地址
                $RiProPay = new RiProPay;
                $pay_qrcode_url = $RiProPay->_cao_get_xunhupay_qrcode($result);
                $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/weixin.png" class="qr-pay">';
                $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">微信扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$result['url_qrcode'].'"/> </div> <div class="bottom weixinpay"> 请使用微信扫一扫<br>扫描二维码支付</br> </div> </div>';
                echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $result['url_qrcode'], 'num' => $order_trade_no));
                exit;
            } else {
                echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $result['url'], 'qrcode' => '', 'msg' => $order_trade_no));
                exit;
            }
        } catch (Exception $e) {
            echo "errcode:{$e->getCode()},errmsg:{$e->getMessage()}";
            exit;
            //TODO:处理支付调用异常的情况
        }
        exit;
    }

    //虎皮椒支付 讯虎支付 V3 支付宝
    if ($pay_type == 6) {
        require_once get_template_directory() . '/inc/class/xunhupay.class.php';
        // 获取后台支付配置
        $XHpayConfig = _cao('xunhualipay');
        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }
        $data=array(
            'version'   => '1.1',//固定值，api 版本，目前暂时是1.1
            'lang'       => 'zh-cn', //必须的，zh-cn或en-us 或其他，根据语言显示页面
            'plugins'   => 'ripro-xunhupay-v3',//必须的，根据自己需要自定义插件ID，唯一的，匹配[a-zA-Z\d\-_]+
            'appid'     => $XHpayConfig['appid'], //必须的，APPID
            'trade_order_id'=> $order_trade_no, //必须的，网站订单ID，唯一的，匹配[a-zA-Z\d\-_]+
            'payment'   => 'alipay',//必须的，支付接口标识：wechat(微信接口)|alipay(支付宝接口)
            'total_fee' => $order_price,//人民币，单位精确到分(测试账户只支持0.1元内付款)
            'title'     => $order_name, //必须的，订单标题，长度32或以内
            'time'      => time(),//必须的，当前时间戳，根据此字段判断订单请求是否已超时，防止第三方攻击服务器
            'notify_url'=>  get_template_directory_uri() . '/shop/xunhupay/notify2.php', //必须的，支付成功异步回调接口
            'return_url'=> get_template_directory_uri() . '/shop/xunhupay/return.php',//必须的，支付成功后的跳转地址
            'callback_url'=> esc_url(home_url('/user?action=charge')),//必须的，支付发起地址（未支付或支付失败，系统会会跳到这个地址让用户修改支付信息）
            'modal'=>null, //可空，支付模式 ，可选值( full:返回完整的支付网页; qrcode:返回二维码; 空值:返回支付跳转链接)
            'nonce_str' => str_shuffle(time())//必须的，随机字符串，作用：1.避免服务器缓存，2.防止安全密钥被猜测出来
        );

        $hashkey =$XHpayConfig['appsecret'];
        $data['hash']     = XH_Payment_Api::generate_xh_hash($data, $hashkey);
        $url              = $XHpayConfig['url_do'];

        try {
            $response     = XH_Payment_Api::http_post($url, json_encode($data));
            /**
             * 支付回调数据
             * @var array(
             *      order_id,//支付系统订单ID
             *      url//支付跳转地址
             *  )
             */
            $result       = $response?json_decode($response, true):null;
            if (!$result) {
                throw new Exception('Internal server error', 500);
            }

            $hash         = XH_Payment_Api::generate_xh_hash($result, $hashkey);
            if (!isset($result['hash'])|| $hash!=$result['hash']) {
                throw new Exception(__('Invalid sign!', XH_Wechat_Payment), 40029);
            }

            if ($result['errcode']!=0) {
                throw new Exception($result['errmsg'], $result['errcode']);
            }
            if ($XHpayConfig['is_pop_qrcode']) {
                //获取二维码地址
                $RiProPay = new RiProPay;
                $pay_qrcode_url = $RiProPay->_cao_get_xunhupay_qrcode($result);

                $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/alipay.png" class="qr-pay">';
                $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">支付宝扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$result['url_qrcode'].'"/> </div> <div class="bottom alipay"> 请使用支付宝扫一扫<br>扫描二维码支付</br> </div> </div>';
                echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $result['url_qrcode'], 'num' => $order_trade_no));
                exit;
            } else {
                echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $result['url'], 'qrcode' => '', 'msg' => $order_trade_no));
                exit;
            }
        } catch (Exception $e) {
            echo "errcode:{$e->getCode()},errmsg:{$e->getMessage()}";
            exit;
            //TODO:处理支付调用异常的情况
        }
        exit;
    }

    //讯虎支付 支付宝
    if ($pay_type == 9) {
        require_once get_template_directory() . '/inc/class/xunhupay.class.php';
        // 获取后台支付配置
        $XHpayConfig = _cao('xunhupay_ali');
        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }

        $data=array(
            'mchid'         => $XHpayConfig['mchid'],
            'out_trade_no'  => $order_trade_no,
            'type'          => 'alipay',
            'total_fee'     => $order_price*100,
            'body'          => $order_name,
            'notify_url'    => get_template_directory_uri() . '/shop/xunhupay/notify4.php',
            'nonce_str'     => str_shuffle(time())
        );

        $hashkey =$XHpayConfig['private_key'];
        if (XH_Payment_Api::is_app_client()) {
            $data['redirect_url']=get_template_directory_uri() . '/shop/xunhupay/return2.php';
            $data['sign']     = XH_Payment_Api::generate_xh_hash_new($data, $hashkey);
            $pay_url     = XH_Payment_Api::data_link('https://admin.xunhuweb.com/alipaycashier', $data);
            $pay_url1    = htmlspecialchars_decode($pay_url, ENT_NOQUOTES);
            echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $pay_url1, 'qrcode' => '', 'msg' => $order_trade_no));
            exit;
        }
        $data['sign']     = XH_Payment_Api::generate_xh_hash_new($data, $hashkey);
        $url              = $XHpayConfig['url_do'].'/pay/payment';

        try {
            $response     = XH_Payment_Api::http_post_json($url, json_encode($data));


            /**
             * 支付回调数据
             * @var array(
             *      order_id,//支付系统订单ID
             *      url//支付跳转地址
             *  )
             */
            $result       = $response?json_decode($response, true):null;

            if (!$result) {
                throw new Exception('Internal server error', 500);
            }

            $hash         = XH_Payment_Api::generate_xh_hash_new($result, $hashkey);
            if (!isset($result['sign'])|| $hash!=$result['sign']) {
                throw new Exception(__('Invalid sign!', XH_Wechat_Payment), 40029);
            }

            if ($result['err_code']!=0) {
                throw new Exception($result['errmsg'], $result['errcode']);
            }
            $pay_url =$result['code_url'];
            //获取二维码地址
            $pay_qrcode_url = getQrcode($pay_url);
            $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/alipay.png" class="qr-pay">';
            $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">支付宝扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$pay_qrcode_url.'"/> </div> <div class="bottom alipay"> 请使用支付宝扫一扫<br>扫描二维码支付</br> </div> </div>';
            echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $pay_qrcode_url, 'num' => $order_trade_no));
            exit;
        } catch (Exception $e) {
            echo "errcode:{$e->getCode()},errmsg:{$e->getMessage()}";
            exit;
            //TODO:处理支付调用异常的情况
        }
        exit;
    }

    //讯虎支付 微信支付
    if ($pay_type == 10) {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        require_once get_template_directory() . '/inc/class/xunhupay.class.php';
        // 获取后台支付配置
        $XHpayConfig = _cao('xunhupay_wx');
        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }

        $data=array(
            'mchid'         => $XHpayConfig['mchid'],
            'out_trade_no'  => $order_trade_no,
            'type'          => 'wechat',
            'total_fee'     => $order_price*100,
            'body'          => $order_name,
            'notify_url'    => get_template_directory_uri() . '/shop/xunhupay/notify3.php',
            'nonce_str'     => str_shuffle(time())
        );

        $hashkey =$XHpayConfig['private_key'];
        if (XH_Payment_Api::is_wechat_app()) {
            $data['redirect_url']=get_template_directory_uri() . '/shop/xunhupay/return2.php';
            $data['sign']     = XH_Payment_Api::generate_xh_hash_new($data, $hashkey);
            $pay_url = XH_Payment_Api::data_link('https://admin.xunhuweb.com/pay/cashier', $data);
            $pay_url1 = htmlspecialchars_decode($pay_url, ENT_NOQUOTES);
            echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $pay_url1, 'qrcode' => '', 'msg' => $order_trade_no));
            exit;
        }
        if (XH_Payment_Api::is_app_client()) {
            $url= get_template_directory_uri() . '/inc/xunhupay/h5.php?out_trade_no='.$order_trade_no.'&total_fee='.$order_price.'&title='.$order_name.'&type=1';
            echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $url, 'qrcode' => '', 'msg' => $order_trade_no));
            exit;
        }
        $data['sign']     = XH_Payment_Api::generate_xh_hash_new($data, $hashkey);
        $url              = $XHpayConfig['url_do'].'/pay/payment';
        try {
            $response     = XH_Payment_Api::http_post_json($url, json_encode($data));
            /**
             * 支付回调数据
             * @var array(
             *      order_id,//支付系统订单ID
             *      url//支付跳转地址
             *  )
             */
            $result       = $response?json_decode($response, true):null;

            if (!$result) {
                throw new Exception('Internal server error', 500);
            }
            $sign             = XH_Payment_Api::generate_xh_hash_new($result, $hashkey);
            if (!isset($result['sign'])|| $sign!=$result['sign']) {
                throw new Exception(__('Invalid sign!', XH_Wechat_Payment), 40029);
            }
            if ($result['return_code']!='SUCCESS') {
                throw new Exception($result['err_msg'], $result['err_code']);
            }
            $url =$result['code_url'];
            //获取二维码地址
            $pay_qrcode_url = getQrcode($url);
            $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/weixin.png" class="qr-pay">';
            $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">微信扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$pay_qrcode_url.'"/> </div> <div class="bottom weixinpay"> 请使用微信扫一扫<br>扫描二维码支付</br> </div> </div>';
            echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $pay_qrcode_url, 'num' => $order_trade_no));
            exit;
        } catch (Exception $e) {
            echo "errcode:{$e->getCode()},errmsg:{$e->getMessage()}";
            exit;
            //TODO:处理支付调用异常的情况
        }
        exit;
    }


    //码支付 codepay 微信 7 8支付宝
    if ($pay_type == 7 || $pay_type == 8) {
        // 获取后台支付配置
        $codepayConfig = _cao('codepay');
        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }

        //判断码支付支付方式
        switch ($pay_type) {
            case '7':
                $paymethod = 1; // 支付宝
                break;
            case '8':
                $paymethod = 3; // 微信
                break;
        }
        $params = array(
            "id" => $codepayConfig['mzf_appid'],
            "token" => $codepayConfig['mzf_token'],
            "pay_id" => $order_trade_no, //唯一标识
            "type" => $paymethod,//1支付宝支付 3微信支付 2QQ钱包
            "price" => $order_price,//金额
            "param" => "rimini",//自定义参数
            "notify_url"=>get_template_directory_uri() . '/shop/codepay/notify.php',//通知地址
        ); //构造需要传递的参数

        // 请求支付数据
        $query = 'id='.$params['id'].'&token='.$params['token'].'&price='.$params['price'].'&pay_id='.$params['pay_id'].'&type='.$params['type'].'&notify_url='.$params['notify_url'].'&page=4'; //创建订单所需的参数
        $urls = 'https://api.xiuxiu888.com/creat_order/creat_order?'.trim($query); //支付页面
        $result = get_url_contents($urls);
        $resultData = json_decode($result, true);

        if ($resultData && $resultData['status'] == 0) {
            if ($paymethod == 3) {
                $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/weixin.png" class="qr-pay">';
                $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">微信扫码支付 '.$resultData['money'].' 元</div> <div align="center" class="qrcode"> <img src="'.$resultData['qrcode'].'"/> </div> <div class="bottom weixinpay">请使用微信扫一扫</br><b style="font-size: 12px;color: #f10;">请在五分钟内支付指定金额</b></br><b style=" font-size: 12px; ">手机用户可保存上方二维码到手机中</b></br><b style=" font-size: 12px; ">在微信扫一扫中选择“相册”即可</b> </div> </div>';
                echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $resultData['qrcode'], 'num' => $order_trade_no));
                exit;
            } else {
                $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/alipay.png" class="qr-pay">';
                $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">支付宝扫码支付 '.$resultData['money'].' 元</div> <div align="center" class="qrcode"> <img src="'.$resultData['qrcode'].'"/> </div> <div class="bottom alipay"> 请使用支付宝扫一扫<br><b style="font-size: 12px;color: #f10;">请在五分钟内支付指定金额</b></br><b style=" font-size: 12px; ">手机用户可保存上方二维码到手机中</b></br><b style=" font-size: 12px; ">在支付宝扫一扫中选择“相册”即可</b> </div> </div>';
                echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str , 'img' => $resultData['qrcode'], 'num' => $order_trade_no));
                exit;
            }
        } else {
            echo json_encode(array('status' => '0', 'msg' => $resultData['msg']));
            exit;
        }
    }
    //易支付 支付宝11 微信 12 
    if ($pay_type == 11 || $pay_type == 12) {
        // 获取后台支付配置
        $yzf=_cao('yzf');
        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }
        //判断易支付支付方式
        switch ($pay_type) {
            case '11':
                // 获取后台支付配置
                $yzfConfig = $yzf['yzf_alipay'];
                $paymethod = 'alipay'; // 支付宝
                break;
            case '12':
                // 获取后台支付配置
                $yzfConfig = $yzf['yzf_wxpay'];
                $paymethod = 'wxpay'; // 微信
                break;
        }
        $key = $yzfConfig['yzf_key'];
        $params = array(
            "pid" => $yzfConfig['yzf_id'],
            "out_trade_no" => $order_trade_no, //唯一标识
            "notify_url"   => get_template_directory_uri() . '/shop/yzf/notify.php',
            "return_url"  => get_template_directory_uri() . '/shop/yzf/return.php', // 支付后跳转返回地址
            "name" => '商品自助购买',
            "type" => $paymethod,//alipay:支付宝,wxpay:微信支付
            "money" => $order_price,//金额
            "sign_type"   => strtoupper('MD5'),
        ); //构造需要传递的参数
        $signPars = "";
        ksort($params);
        foreach ($params as $k => $v) {
            if ($k != "sign_type") {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars = trim($signPars, '&');
        $signPars .= $key;
        $params['sign']=md5($signPars);
        $urls=$yzfConfig['yzf_url'].'submit.php?'. http_build_query($params, '', '&');
        //建立请求
        echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $urls, 'qrcode' => '', 'msg' => $order_trade_no));
        exit;
    }
}
add_action('wp_ajax_charge_pay', 'charge_pay');
add_action('wp_ajax_nopriv_charge_pay', 'charge_pay');

/**
 * [go_post_pay 支付模式购买文章]
 * @Author   Dadong2g
 * @DateTime 2020-01-15T11:41:26+0800
 * @return   [type]                   [description]
 */
function go_post_pay()
{
    header('Content-type:application/json; Charset=utf-8');
    $ip = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1'; //客户端IP
    global $current_user;
    $uid = (is_user_logged_in()) ? $current_user->ID : 0 ; //■■■■■如果没有登录，则uid = 0，否则就取全局变量$current_user中的id数值

    if ($uid>0 && !_cao('is_online_pay_status', true)) {
        echo json_encode(array('status' => '0', 'msg' => '登录用户仅限请使用余额支付'));
        exit;
    }

    // isLoginCheck(); //检测登录
    $nonce      = !empty($_POST['nonce']) ? $_POST['nonce'] : null;
    $post_id = !empty($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

    // 1支付宝官方；2微信官方 ；3 其他  ；4 PAYJS  ；5 讯虎微信  ；6 讯虎支付宝 ；7 码支付支付宝  ；8 码支付微信
    $pay_type   = !empty($_POST['pay_type']) ? (int) $_POST['pay_type'] : null;

    if ($nonce && !wp_verify_nonce($nonce, 'caopay-' . $post_id)) {
        echo json_encode(array('status' => '0', 'msg' => '非法请求'));
        exit;
    }

    if ($post_id <= 0) {
        echo json_encode(array('status' => '0', 'msg' => '购买ID参数错误'));
        exit;
    }

    // 实例化订单
    $ShopOrder = new ShopOrder();
    $CaoUser = new CaoUser($uid);
    $PostPay = new PostPay($uid, $post_id);

    if (get_post_meta($post_id, 'cao_close_novip_pay', true) && !$CaoUser->vip_status()) {
        echo json_encode(array('status' => '0', 'msg' => '该资源为会员专属资源，普通用户无购买权限！'));
        exit;
    }

    /////////商品属性START///////
    $charge_rate    = (int) _cao('site_change_rate'); //网站比例
    //获取资源文章价格等信息
    $post_price = $_post_price = get_post_meta($post_id, 'cao_price', true);
    $post_price = ($post_price) ? $post_price : '0' ;
    // 计算价格 验证会员折扣权限
    $post_vip_rate = get_post_meta($post_id, 'cao_vip_rate', true);
    $cao_is_boosvip  = get_post_meta($post_id, 'cao_is_boosvip', true);
    if ($cao_is_boosvip && is_boosvip_status($uid)) {
        $post_price    = 0;
    }
    if (_cao('is_online_pay_reta', true)) {
        $vip_status    = $CaoUser->vip_status();
        $order_vip_rate = ($vip_status) ? $post_vip_rate : 1 ;
        // 折扣信息
        if ($order_vip_rate == 0) {
            $post_price = 0;
        } elseif ($order_vip_rate == 1) {
            $post_price = $post_price;
        } elseif ($order_vip_rate > 0 && $order_vip_rate < 1) {
            $post_price = sprintf('%0.2f', $post_price*$order_vip_rate);
        } else {
            $post_price = $post_price;
        }
    } else {
        $order_vip_rate =  1 ;
        $post_price = $post_price;
    }

    $order_price    = sprintf('%0.2f', $post_price / $charge_rate); // 订单价格 换算人民币,保留两位小数点
    $order_trade_no = date("ymdhis") . mt_rand(100, 999) . mt_rand(100, 999) . mt_rand(100, 999); // 订单号
    if (_cao('is_ripro_diy_shop_name')) {
        $order_name = _cao('ripro_diy_shop_name_pay'); //自定义订单名称
    } else {
        $order_name = get_bloginfo('name') . '-资源购买'; //订单名称
    }

    $order_type     = 'other'; //类型 购买 其他

    $post_vid = (int)$_POST['post_vid'];//post_vid这个变量是会员等级数组里元素的序号

	//■■■■■■判断post_vid是否获取到值，并校验post_id是否是用户页面的post_id
    if (!isset($post_vid) && $post_id==cao_get_page_by_slug('user')) {
        echo json_encode(array('status' => '0', 'msg' => '请选择开通套餐'));
        exit;
    }

    //start
    if (isset($post_vid) && $post_id==cao_get_page_by_slug('user')) {
        # 获取后台价格设置
        $vip_pay_setting = _cao('vip-pay-setting');
        $payInfo = [];
        foreach ($vip_pay_setting as $key => $item) {
            if ($key == $post_vid) {
                $payInfo = $item;
                break;
            }
        }
        if (empty($payInfo)) {
            echo json_encode(array('status' => '0', 'msg' => '商城设置会员组设置不完善'));
            exit;
        }
		//echo "<script>console.log('".json_encode($payInfo)."')</script>";
        $post_price = $payInfo['price']; //积分价格
        $pay_daynum = $payInfo['daynum']; //开通天数
        $order_price    = sprintf('%0.2f', $post_price / $charge_rate); // 订单价格 换算人民币,保留两位小数点
        if ($pay_daynum==9999) {
            $order_name = get_bloginfo('name') . '-开通永久'._cao('site_vip_name'); //订单名称
        } else {
            $order_name = get_bloginfo('name') . '-开通'._cao('site_vip_name').'【'.$pay_daynum.'天】'; //订单名称
        }
        $_post_price = $post_price;
    }
    // end

    if ($post_price <= 0) {
        echo json_encode(array('status' => '0', 'msg' => '免费或'._cao('site_vip_name').'免费资源仅限余额支付'));
        exit;
    }

    //写入本地文章购买记录
    if (!$PostPay->add($_post_price, $order_vip_rate, $order_trade_no, $pay_type)) {
        echo json_encode(array('status' => '0', 'msg' => '添加订单异常'));
        exit;
    }

    /////////商品属性END/////////

    // 判断支付方式 1 支付宝 START
    if ($pay_type == 1) {
        // 获取后台支付宝配置
        $aliPayConfig = _cao('alipay');
        // 判断是否开启手机版跳转
        if (wp_is_mobile() && $aliPayConfig['is_mobile']) {
            // 添加订单 ShopOrder
            if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
                echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
                exit;
            }


            // 支付宝公共配置
            $params         = new \Yurun\PaySDK\Alipay\Params\PublicParams;
            $params->appID  = $aliPayConfig['pid'];
            $params->md5Key = $aliPayConfig['md5Key'];
            // SDK实例化，传入公共配置
            $pay       = new \Yurun\PaySDK\Alipay\SDK($params);
            // 支付接口
            $request    = new \Yurun\PaySDK\Alipay\Params\WapPay\Request;
            $request->notify_url    = get_template_directory_uri() . '/shop/alipay/notify.php';
            $request->return_url    = get_template_directory_uri() . '/shop/alipay/return.php'; // 支付后跳转返回地址
            $request->businessParams->seller_id    = $aliPayConfig['pid']; // 卖家支付宝用户号
            $request->businessParams->out_trade_no = $order_trade_no; // 商户订单号
            $request->businessParams->total_fee    = $order_price; // 价格
            $request->businessParams->subject      = $order_name; // 商品标题
            $request->businessParams->show_url     = get_permalink($post_id); // 用户付款中途退出返回商户网站的地址。

            $payurl = $pay->redirectExecuteUrl($request);
            $_SESSION['ali_session_order_trade_no'] = $order_trade_no;
            // type 1 = 扫码支付  2 跳转支付
            echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $payurl, 'qrcode' => '', 'msg' => $order_trade_no));
            exit;
        } elseif (!$aliPayConfig['is_pcqr']) {
            // 支付宝-电脑网站支付
            // 添加订单 ShopOrder
            if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
                echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
                exit;
            }
            // 支付宝公共配置
            $params         = new \Yurun\PaySDK\Alipay\Params\PublicParams;
            $params->appID  = $aliPayConfig['pid'];
            $params->md5Key = $aliPayConfig['md5Key'];
            // SDK实例化，传入公共配置
            $pay       = new \Yurun\PaySDK\Alipay\SDK($params);
            // 支付接口
            $request = new \Yurun\PaySDK\Alipay\Params\Pay\Request;
            $request->notify_url    = get_template_directory_uri() . '/shop/alipay/notify.php';
            $request->return_url    = get_template_directory_uri() . '/shop/alipay/return.php'; // 支付后跳转返回地址
            $request->businessParams->seller_id    = $aliPayConfig['pid']; // 卖家支付宝用户号
            $request->businessParams->out_trade_no = $order_trade_no; // 商户订单号
            $request->businessParams->total_fee    = $order_price; // 价格
            $request->businessParams->subject      = $order_name; // 商品标题
            // 跳转到支付宝页面
            $payurl = $pay->redirectExecuteUrl($request);
            // var_dump($payurl);
            // type 1 = 扫码支付  2 跳转支付
            echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $payurl, 'qrcode' => '', 'msg' => $order_trade_no));
            exit;
        } else {
            // 应用模式公共配置-当面付
            // 添加订单 ShopOrder
            if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
                echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
                exit;
            }
            // 更换公共配置文件
            $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
            $params->appID = $aliPayConfig['appid'];
            $params->appPrivateKey = $aliPayConfig['privateKey'];
            $params->appPublicKey = $aliPayConfig['publicKey'];
            // SDK实例化，传入公共配置
            $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
            // 支付接口
            $request = new \Yurun\PaySDK\AlipayApp\FTF\Params\QR\Request;
            $request->notify_url    = get_template_directory_uri() . '/shop/alipay/notify2.php'; // 支付后通知地址
            $request->businessParams->out_trade_no = $order_trade_no; // 商户订单号
            $request->businessParams->total_amount = $order_price; // 价格
            $request->businessParams->subject      = $order_name; // 商品标题

            // 调用接口
            try {
                $data = $pay->execute($request);
            } catch (Exception $e) {
                var_dump($pay->response->body());
            }
            // QR内容
            $qrimg = getQrcode($data['alipay_trade_precreate_response']['qr_code']);

            $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/alipay.png" class="qr-pay">';
            $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">支付宝扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$qrimg.'"/> </div> <div class="bottom alipay"> 请使用支付宝扫一扫<br>扫描二维码支付</br> </div> </div>';
            echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $qrimg, 'num' => $order_trade_no));
            exit;
        }
    }
    //END ALIPAY

    // 2 微信
    if ($pay_type == 2) {
        // 获取后台支付配置
        $wxPayConfig = _cao('weixinpay');
        // 公共配置
        $params = new \Yurun\PaySDK\Weixin\Params\PublicParams;
        $params->appID = $wxPayConfig['appid'];
        $params->mch_id = $wxPayConfig['mch_id'];
        $params->key = $wxPayConfig['key'];
        // SDK实例化，传入公共配置
        $pay = new \Yurun\PaySDK\Weixin\SDK($params);
        $the_openid = get_user_meta($uid, 'open_mpweixin_openid', true);
        // 判断是否开启手机版跳转 //微信当前免登陆h5跳转有问题 免登陆下不允许h5支付
        if (wp_is_mobile() && $wxPayConfig['is_mobile']  && !is_weixin_view()) {
            // 添加订单 ShopOrder
            if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
                echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
                exit;
            }
            // 支付接口H5
            $request = new \Yurun\PaySDK\Weixin\H5\Params\Pay\Request;
            $request->body = $order_name; // 商品描述
            $request->out_trade_no = $order_trade_no; // 订单号
            $request->total_fee = $order_price*100; // 订单总金额，单位为：分
            $request->spbill_create_ip = $ip; // 客户端ip，必须传正确的用户ip，否则会报错
            $request->notify_url = get_template_directory_uri() . '/shop/weixin/notify.php'; // 异步通知地址
            $request->scene_info = new \Yurun\PaySDK\Weixin\H5\Params\SceneInfo;
            $request->scene_info->type = 'Wap'; // 可选值：IOS、Android、Wap
            // 下面参数根据type不同而不同
            $request->scene_info->wap_url = get_template_directory_uri() . '/shop/weixin/return.php';
            $request->scene_info->wap_name = get_bloginfo('name');
            // 调用接口
            $result = $pay->execute($request);

            if ($pay->checkResult()) {
                $_SESSION['wx_session_order_trade_no'] = $order_trade_no;
                echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $result['mweb_url'], 'qrcode' => 'h5', 'num' => $order_trade_no));
                exit;
            } else {
                $error_msg = $pay->getErrorCode() . ':' . $pay->getError();
                echo json_encode(array('status' => '0', 'msg' => $error_msg));
                exit;
            }
        } elseif (_cao('is_oauth_mpweixin') && $wxPayConfig['is_jsapi'] && is_weixin_view() && is_user_logged_in() && !empty($the_openid)) {
            // 添加订单 ShopOrder
            if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
                echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
                exit;
            }
            # JSAPI 模式
            $request = new \Yurun\PaySDK\Weixin\JSAPI\Params\Pay\Request;
            $request->body = $order_name; // 商品描述
            $request->out_trade_no = $order_trade_no; // 订单号
            $request->total_fee = $order_price*100; // 订单总金额，单位为：分
            $request->spbill_create_ip = $ip; // 客户端ip
            $request->notify_url = get_template_directory_uri() . '/shop/weixin/notify.php'; // 异步通知地址
            $request->openid = $the_openid; // 必须设置openid
            // 调用接口
            $result = $pay->execute($request);
            if ($pay->checkResult()) {
                $request = new \Yurun\PaySDK\Weixin\JSAPI\Params\JSParams\Request;
                $request->prepay_id = $result['prepay_id'];
                $jsapiParams = $pay->execute($request);
                // 最后需要将数据传给js，使用WeixinJSBridge进行支付
                echo json_encode(array('status' => '1', 'type' => '3', 'msg' => $jsapiParams, 'img' => '', 'num' => $order_trade_no));
                exit;
            } else {
                echo json_encode(array('status' => '0', 'msg' => '未绑定公众号登录或网络错误'));
                exit;
            }
        } else {
            // PC使用当面付返回二维码
            // 添加订单 ShopOrder
            if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
                echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
                exit;
            }
            // 支付接口 PC扫码
            $request = new \Yurun\PaySDK\Weixin\Native\Params\Pay\Request;
            $request->body = $order_name; // 商品描述
            $request->out_trade_no = $order_trade_no; // 订单号
            $request->total_fee = $order_price*100; // 订单总金额，单位为：分
            $request->spbill_create_ip = $ip; // 客户端ip
            $request->notify_url = get_template_directory_uri() . '/shop/weixin/notify.php'; // 异步通知地址
            // 调用接口
            $result = $pay->execute($request);
            $shortUrl = $result['code_url'];
            if (is_array($result) && $shortUrl) {
                // 获取成功 返回QR内容
                $qrimg = getQrcode($shortUrl);
                $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/weixin.png" class="qr-pay">';
                $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">微信扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$qrimg.'"/> </div> <div class="bottom weixinpay"> 请使用微信扫一扫<br>扫描二维码支付</br> </div> </div>';
                echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $qrimg, 'num' => $order_trade_no));
                exit;
            } else {
                echo json_encode(array('status' => '0', 'msg' => '接口网络异常'));
                exit;
            }
        }
    }

    //PAYJS
    if ($pay_type == 4) {
        require_once get_template_directory() . '/inc/class/Payjs.class.php';
        // 获取后台支付配置
        $PayJsConfig = _cao('payjs');
        // 配置通信参数
        $config = [
            'mchid' => $PayJsConfig['mchid'],   // 配置商户号
            'key'   => $PayJsConfig['key'],   // 配置通信密钥
        ];
        // 初始化 PAYJS
        $payjs = new Payjs($config);
        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }
        if (false) {
            // 手机模式因openid获取问题 暂时未开放
        } else {
            // 构造订单基础信息
            $data = [
                'body' => $order_name,                        // 订单标题
                'total_fee' => $order_price*100,                           // 订单金额
                'out_trade_no' => $order_trade_no,                   // 订单号
                'attach' => 'payjs_order_attach',            // 订单附加信息(可选参数)
                'notify_url' => get_template_directory_uri() . '/shop/payjs/notify.php',    // 异步通知地址(可选参数)
            ];
            $result = $payjs->native($data);
            // var_dump($result);die;
            if (is_array($result) && $result['return_code'] == 1) {
                $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/weixin.png" class="qr-pay">';
                $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">微信扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$result['qrcode'].'"/> </div> <div class="bottom weixinpay"> 请使用微信扫一扫<br>扫描二维码支付</br> </div> </div>';
                echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $result['qrcode'], 'num' => $order_trade_no));
                exit;
            } else {
                echo json_encode(array('status' => '0', 'msg' => 'PAYJS接口异常'));
                exit;
            }
        }

        echo json_encode(array('status' => '0', 'msg' => '请配置payjs参数'));
        exit;
    }

    //虎皮椒支付 讯虎支付 V3 微信
    if ($pay_type == 5) {
        require_once get_template_directory() . '/inc/class/xunhupay.class.php';
        // 获取后台支付配置
        $XHpayConfig = _cao('xunhupay');

        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }

        $data=array(
            'version'   => '1.1',//固定值，api 版本，目前暂时是1.1
            'lang'       => 'zh-cn', //必须的，zh-cn或en-us 或其他，根据语言显示页面
            'plugins'   => 'ripro-xunhupay-v3',//必须的，根据自己需要自定义插件ID，唯一的，匹配[a-zA-Z\d\-_]+
            'appid'     => $XHpayConfig['appid'], //必须的，APPID
            'trade_order_id'=> $order_trade_no, //必须的，网站订单ID，唯一的，匹配[a-zA-Z\d\-_]+
            'payment'   => 'wechat',//必须的，支付接口标识：wechat(微信接口)|alipay(支付宝接口)
            'type'   => 'WAP',//固定值"WAP"
            'wap_url'   => home_url(),//网站域名，必填
            'wap_name'   => home_url(),//网站域名，或者名字，必填，长度32或以内
            'total_fee' => $order_price,//人民币，单位精确到分(测试账户只支持0.1元内付款)
            'title'     => $order_name, //必须的，订单标题，长度32或以内
            'time'      => time(),//必须的，当前时间戳，根据此字段判断订单请求是否已超时，防止第三方攻击服务器
            'notify_url'=>  get_template_directory_uri() . '/shop/xunhupay/notify.php', //必须的，支付成功异步回调接口
            'return_url'=> get_template_directory_uri() . '/shop/xunhupay/return.php?num='.$order_trade_no,//必须的，支付成功后的跳转地址
            'callback_url'=> esc_url(home_url('/user?action=charge')),//必须的，支付发起地址（未支付或支付失败，系统会会跳到这个地址让用户修改支付信息）
            'modal'=>null, //可空，支付模式 ，可选值( full:返回完整的支付网页; qrcode:返回二维码; 空值:返回支付跳转链接)
            'nonce_str' => str_shuffle(time())//必须的，随机字符串，作用：1.避免服务器缓存，2.防止安全密钥被猜测出来
        );

        $hashkey =$XHpayConfig['appsecret'];
        $data['hash']     = XH_Payment_Api::generate_xh_hash($data, $hashkey);
        $url              = $XHpayConfig['url_do'];

        try {
            $response     = XH_Payment_Api::http_post($url, json_encode($data));
            /**
             * 支付回调数据
             * @var array(
             *      order_id,//支付系统订单ID
             *      url//支付跳转地址
             *  )
             */
            $result       = $response?json_decode($response, true):null;
            if (!$result) {
                throw new Exception('Internal server error', 500);
            }

            $hash         = XH_Payment_Api::generate_xh_hash($result, $hashkey);
            if (!isset($result['hash'])|| $hash!=$result['hash']) {
                throw new Exception(__('Invalid sign!', XH_Wechat_Payment), 40029);
            }

            if ($result['errcode']!=0) {
                throw new Exception($result['errmsg'], $result['errcode']);
            }
            //虎皮椒H5支付判断
            if (XH_Payment_Api::is_app_client()) {
                echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $result['url'], 'qrcode' => '', 'msg' => $order_trade_no));
                exit;
            }
            if ($XHpayConfig['is_pop_qrcode'] && !is_weixin_view()) {
                //获取二维码地址
                $RiProPay = new RiProPay;
                $pay_qrcode_url = $RiProPay->_cao_get_xunhupay_qrcode($result);
                $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/weixin.png" class="qr-pay">';
                $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">微信扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$result['url_qrcode'].'"/> </div> <div class="bottom weixinpay"> 请使用微信扫一扫<br>扫描二维码支付</br> </div> </div>';
                echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $result['url_qrcode'], 'num' => $order_trade_no));
                exit;
            } else {
                echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $result['url'], 'qrcode' => '', 'msg' => $order_trade_no));
                exit;
            }
        } catch (Exception $e) {
            echo "errcode:{$e->getCode()},errmsg:{$e->getMessage()}";
            exit;
            //TODO:处理支付调用异常的情况
        }
        exit;
    }

    //讯虎支付 支付宝
    if ($pay_type == 9) {
        require_once get_template_directory() . '/inc/class/xunhupay.class.php';
        // 获取后台支付配置
        $XHpayConfig = _cao('xunhupay_ali');
        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }

        $data=array(
            'mchid'         => $XHpayConfig['mchid'],
            'out_trade_no'  => $order_trade_no,
            'type'          => 'alipay',
            'total_fee'     => $order_price*100,
            'body'          => $order_name,
            'notify_url'    => get_template_directory_uri() . '/shop/xunhupay/notify4.php',
            'nonce_str'     => str_shuffle(time())
        );

        $hashkey =$XHpayConfig['private_key'];
        if (XH_Payment_Api::is_app_client()) {
            $data['redirect_url']=get_template_directory_uri() . '/shop/xunhupay/return2.php';
            $data['sign']     = XH_Payment_Api::generate_xh_hash_new($data, $hashkey);
            $pay_url     = XH_Payment_Api::data_link('https://admin.xunhuweb.com/alipaycashier', $data);
            $pay_url1    = htmlspecialchars_decode($pay_url, ENT_NOQUOTES);
            echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $pay_url1, 'qrcode' => '', 'msg' => $order_trade_no));
            exit;
        }
        $data['sign']     = XH_Payment_Api::generate_xh_hash_new($data, $hashkey);
        $url              = $XHpayConfig['url_do'].'/pay/payment';
        try {
            $response     = XH_Payment_Api::http_post_json($url, json_encode($data));
            /**
             * 支付回调数据
             * @var array(
             *      order_id,//支付系统订单ID
             *      url//支付跳转地址
             *  )
             */
            $result       = $response?json_decode($response, true):null;

            if (!$result) {
                throw new Exception('Internal server error', 500);
            }

            $hash         = XH_Payment_Api::generate_xh_hash_new($result, $hashkey);
            if (!isset($result['sign'])|| $hash!=$result['sign']) {
                throw new Exception(__('Invalid sign!', XH_Wechat_Payment), 40029);
            }

            if ($result['err_code']!=0) {
                throw new Exception($result['errmsg'], $result['errcode']);
            }
            $pay_url =$result['code_url'];
            //获取二维码地址
            $pay_qrcode_url = getQrcode($pay_url);
            $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/alipay.png" class="qr-pay">';
            $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">支付宝扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$pay_qrcode_url.'"/> </div> <div class="bottom alipay"> 请使用支付宝扫一扫<br>扫描二维码支付</br> </div> </div>';
            echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $pay_qrcode_url, 'num' => $order_trade_no));
            exit;
        } catch (Exception $e) {
            echo "errcode:{$e->getCode()},errmsg:{$e->getMessage()}";
            exit;
            //TODO:处理支付调用异常的情况
        }
        exit;
    }

    //讯虎支付 微信支付
    if ($pay_type == 10) {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        require_once get_template_directory() . '/inc/class/xunhupay.class.php';
        // 获取后台支付配置
        $XHpayConfig = _cao('xunhupay_wx');
        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }

        $data=array(
            'mchid'         => $XHpayConfig['mchid'],
            'out_trade_no'  => $order_trade_no,
            'type'          => 'wechat',
            'total_fee'     => $order_price*100,
            'body'          => $order_name,
            'notify_url'    => get_template_directory_uri() . '/shop/xunhupay/notify3.php',
            'nonce_str'     => str_shuffle(time())
        );

        $hashkey =$XHpayConfig['private_key'];
        if (XH_Payment_Api::is_wechat_app()) {
            $data['redirect_url']=get_template_directory_uri() . '/shop/xunhupay/return2.php';
            $data['sign']     = XH_Payment_Api::generate_xh_hash_new($data, $hashkey);
            $pay_url = XH_Payment_Api::data_link('https://admin.xunhuweb.com/pay/cashier', $data);
            $pay_url1 = htmlspecialchars_decode($pay_url, ENT_NOQUOTES);
            echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $pay_url1, 'qrcode' => '', 'msg' => $order_trade_no));
            exit;
        }
        if (XH_Payment_Api::is_app_client()) {
            $url= get_template_directory_uri() . '/inc/xunhupay/h5.php?out_trade_no='.$order_trade_no.'&total_fee='.$order_price.'&title='.$order_name.'&type=2';
            echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $url, 'qrcode' => '', 'msg' => $order_trade_no));
            exit;
        }
        $data['sign']     = XH_Payment_Api::generate_xh_hash_new($data, $hashkey);
        $url              = $XHpayConfig['url_do'].'/pay/payment';
        try {
            $response     = XH_Payment_Api::http_post_json($url, json_encode($data));
            /**
             * 支付回调数据
             * @var array(
             *      order_id,//支付系统订单ID
             *      url//支付跳转地址
             *  )
             */
            $result       = $response?json_decode($response, true):null;

            if (!$result) {
                throw new Exception('Internal server error', 500);
            }
            $sign             = XH_Payment_Api::generate_xh_hash_new($result, $hashkey);
            if (!isset($result['sign'])|| $sign!=$result['sign']) {
                throw new Exception(__('Invalid sign!', XH_Wechat_Payment), 40029);
            }
            if ($result['return_code']!='SUCCESS') {
                throw new Exception($result['err_msg'], $result['err_code']);
            }
            $url =$result['code_url'];
            //获取二维码地址
            $pay_qrcode_url = getQrcode($url);
            $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/weixin.png" class="qr-pay">';
            $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">微信扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$pay_qrcode_url.'"/> </div> <div class="bottom weixinpay"> 请使用微信扫一扫<br>扫描二维码支付</br> </div> </div>';
            echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $pay_qrcode_url, 'num' => $order_trade_no));
            exit;
        } catch (Exception $e) {
            echo "errcode:{$e->getCode()},errmsg:{$e->getMessage()}";
            exit;
            //TODO:处理支付调用异常的情况
        }
        exit;
    }


    //虎皮椒支付 讯虎支付 V3 支付宝
    if ($pay_type == 6) {
        require_once get_template_directory() . '/inc/class/xunhupay.class.php';
        // 获取后台支付配置
        $XHpayConfig = _cao('xunhualipay');
        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }
        $data=array(
            'version'   => '1.1',//固定值，api 版本，目前暂时是1.1
            'lang'       => 'zh-cn', //必须的，zh-cn或en-us 或其他，根据语言显示页面
            'plugins'   => 'ripro-xunhupay-v3',//必须的，根据自己需要自定义插件ID，唯一的，匹配[a-zA-Z\d\-_]+
            'appid'     => $XHpayConfig['appid'], //必须的，APPID
            'trade_order_id'=> $order_trade_no, //必须的，网站订单ID，唯一的，匹配[a-zA-Z\d\-_]+
            'payment'   => 'alipay',//必须的，支付接口标识：wechat(微信接口)|alipay(支付宝接口)
            'total_fee' => $order_price,//人民币，单位精确到分(测试账户只支持0.1元内付款)
            'title'     => $order_name, //必须的，订单标题，长度32或以内
            'time'      => time(),//必须的，当前时间戳，根据此字段判断订单请求是否已超时，防止第三方攻击服务器
            'notify_url'=>  get_template_directory_uri() . '/shop/xunhupay/notify2.php', //必须的，支付成功异步回调接口
            'return_url'=> get_template_directory_uri() . '/shop/xunhupay/return.php?num='.$order_trade_no,//必须的，支付成功后的跳转地址
            'callback_url'=> esc_url(home_url('/user?action=charge')),//必须的，支付发起地址（未支付或支付失败，系统会会跳到这个地址让用户修改支付信息）
            'modal'=>null, //可空，支付模式 ，可选值( full:返回完整的支付网页; qrcode:返回二维码; 空值:返回支付跳转链接)
            'nonce_str' => str_shuffle(time())//必须的，随机字符串，作用：1.避免服务器缓存，2.防止安全密钥被猜测出来
        );

        $hashkey =$XHpayConfig['appsecret'];
        $data['hash']     = XH_Payment_Api::generate_xh_hash($data, $hashkey);
        $url              = $XHpayConfig['url_do'];

        try {
            $response     = XH_Payment_Api::http_post($url, json_encode($data));
            /**
             * 支付回调数据
             * @var array(
             *      order_id,//支付系统订单ID
             *      url//支付跳转地址
             *  )
             */
            $result       = $response?json_decode($response, true):null;
            if (!$result) {
                throw new Exception('Internal server error', 500);
            }

            $hash         = XH_Payment_Api::generate_xh_hash($result, $hashkey);
            if (!isset($result['hash'])|| $hash!=$result['hash']) {
                throw new Exception(__('Invalid sign!', XH_Wechat_Payment), 40029);
            }

            if ($result['errcode']!=0) {
                throw new Exception($result['errmsg'], $result['errcode']);
            }
            if ($XHpayConfig['is_pop_qrcode']) {
                //获取二维码地址
                $RiProPay = new RiProPay;
                $pay_qrcode_url = $RiProPay->_cao_get_xunhupay_qrcode($result);
                $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/alipay.png" class="qr-pay">';
                $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">支付宝扫码支付 '.$order_price.' 元</div> <div align="center" class="qrcode"> <img src="'.$pay_qrcode_url.'"/> </div> <div class="bottom alipay"> 请使用支付宝扫一扫<br>扫描二维码支付</br> </div> </div>';
                echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $pay_qrcode_url, 'num' => $order_trade_no));
                exit;
            } else {
                echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $result['url'], 'qrcode' => '', 'msg' => $order_trade_no));
                exit;
            }
        } catch (Exception $e) {
            echo "errcode:{$e->getCode()},errmsg:{$e->getMessage()}";
            exit;
            //TODO:处理支付调用异常的情况
        }
        exit;
    }

    //码支付 codepay 微信 7 8支付宝
    if ($pay_type == 7 || $pay_type == 8) {
        // 获取后台支付配置
        $codepayConfig = _cao('codepay');
        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }

        //判断码支付支付方式
        switch ($pay_type) {
            case '7':
                $paymethod = 1; // 支付宝
                break;
            case '8':
                $paymethod = 3; // 微信
                break;
        }
        $params = array(
            "id" => $codepayConfig['mzf_appid'],
            "token" => $codepayConfig['mzf_token'],
            "pay_id" => $order_trade_no, //唯一标识
            "type" => $paymethod,//1支付宝支付 3微信支付 2QQ钱包
            "price" => $order_price,//金额
            "param" => "rimini",//自定义参数
            "notify_url"=>get_template_directory_uri() . '/shop/codepay/notify.php',//通知地址
        ); //构造需要传递的参数

        // 请求支付数据
        $query = 'id='.$params['id'].'&token='.$params['token'].'&price='.$params['price'].'&pay_id='.$params['pay_id'].'&type='.$params['type'].'&notify_url='.$params['notify_url'].'&page=4'; //创建订单所需的参数
        $urls = 'https://api.xiuxiu888.com/creat_order/creat_order?'.trim($query); //支付页面
        $result = get_url_contents($urls);
        $resultData = json_decode($result, true);

        if ($resultData && $resultData['status'] == 0) {
            if ($paymethod == 3) {
                $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/weixin.png" class="qr-pay">';
                $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">微信扫码支付 '.$resultData['money'].' 元</div> <div align="center" class="qrcode"> <img src="'.$resultData['qrcode'].'"/> </div> <div class="bottom weixinpay">请使用微信扫一扫</br><b style="font-size: 12px;color: #f10;">请在五分钟内支付指定金额</b></br><b style=" font-size: 12px; ">手机用户可保存上方二维码到手机中</b></br><b style=" font-size: 12px; ">在微信扫一扫中选择“相册”即可</b> </div> </div>';
                echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str, 'img' => $resultData['qrcode'], 'num' => $order_trade_no));
                exit;
            } else {
                $iconstr = '<img src="'.get_template_directory_uri() . '/assets/icons/alipay.png" class="qr-pay">';
                $html_str = '<div class="qrcon"> <h5> '.$iconstr.' </h5> <div class="title">支付宝扫码支付 '.$resultData['money'].' 元</div> <div align="center" class="qrcode"> <img src="'.$resultData['qrcode'].'"/> </div> <div class="bottom alipay"> 请使用支付宝扫一扫<br><b style="font-size: 12px;color: #f10;">请在五分钟内支付指定金额</b></br><b style=" font-size: 12px; ">手机用户可保存上方二维码到手机中</b></br><b style=" font-size: 12px; ">在支付宝扫一扫中选择“相册”即可</b> </div> </div>';
                echo json_encode(array('status' => '1', 'type' => '1', 'msg' => $html_str , 'img' => $resultData['qrcode'], 'num' => $order_trade_no));
                exit;
            }
        } else {
            echo json_encode(array('status' => '0', 'msg' => $resultData['msg']));
            exit;
        }
    }
    //易支付 支付宝11 微信 12
    if ($pay_type == 11 || $pay_type == 12) {
        $yzf = _cao('yzf');
        // 添加订单 ShopOrder
        if (!$ShopOrder->add($uid, $order_trade_no, $order_type, $order_price, $pay_type)) {
            echo json_encode(array('status' => '0', 'msg' => '订单创建失败'));
            exit;
        }
        //判断易支付支付方式
        switch ($pay_type) {
            case '11':
                // 获取后台支付配置
                $yzfConfig = $yzf['yzf_alipay'];
                $paymethod = 'alipay'; // 支付宝
                break;
            case '12':
                // 获取后台支付配置
                $yzfConfig = $yzf['yzf_wxpay'];
                $paymethod = 'wxpay'; // 微信
                break;
        }
        $key = $yzfConfig['yzf_key'];
        $params = array(
            "pid" => $yzfConfig['yzf_id'],
            "out_trade_no" => $order_trade_no, //唯一标识
            "notify_url"   => get_template_directory_uri() . '/shop/yzf/notify.php',
            "return_url"  => get_template_directory_uri() . '/shop/yzf/return.php', // 支付后跳转返回地址
            "name" => '商品自助购买',
            "type" => $paymethod,//alipay:支付宝,wxpay:微信支付
            "money" => $order_price,//金额
            "sign_type"   => strtoupper('MD5'),
        ); //构造需要传递的参数
        $signPars = "";
        ksort($params);
        foreach ($params as $k => $v) {
            if ($k != "sign_type") {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars = trim($signPars, '&');
        $signPars .= $key;
        $params['sign']=md5($signPars);
        $urls=$yzfConfig['yzf_url'].'submit.php?'. http_build_query($params, '', '&');
        //建立请求
        echo json_encode(array('status' => '1', 'type' => '2', 'rurl' => $urls, 'qrcode' => '', 'msg' => $order_trade_no));
        exit;
    }
}
add_action('wp_ajax_go_post_pay', 'go_post_pay');
add_action('wp_ajax_nopriv_go_post_pay', 'go_post_pay');



// 检测支付状态
function check_pay()
{
    header('Content-type:application/json; Charset=utf-8');
    global $current_user;
    $uid = is_user_logged_in() ? $current_user->ID : 0;
    $post_id = !empty($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $post_vid = (int)$_POST['post_vid'];//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
	
	//echo "<script>console.log('post:".json_encode($_POST)."')</script>";
	
    $orderNum = !empty($_POST['num']) ? sanitize_text_field(wp_unslash($_POST[ 'num' ])) : null;
    $ShopOrder = new ShopOrder();
    $status = $ShopOrder->check($orderNum);
    if ($status) {
        $intstatus = 1;
        $msg = '恭喜你，支付成功';
        $RiProPay = new RiProPay;
        $RiProPay->AddPayPostCookie($uid, $post_id, $orderNum);
        //修正在线充值方式交费后没法开通vip会员的bug■■■■■■■■■功能已经集成到class/core.class7.4.php这个核心文件中■■■■www.haodaima.cc■■■■■
        // if (isset($post_vid) && $post_id==cao_get_page_by_slug('user')) {
        //     cash_pay_vip($post_vid, wp_create_nonce('caoclick-' . $current_user->ID));
        //     exit;
        // }
    } else {
        $intstatus = 0;
        $msg = '支付中';
    }
    $result = array(
        'status' => $intstatus,
        'msg' => $msg
    );
    echo json_encode($result);
    exit;
}
add_action('wp_ajax_check_pay', 'check_pay');
add_action('wp_ajax_nopriv_check_pay', 'check_pay');

//■■■■■■■■在线充值方式开通vip会员后的操作■■修正在线充值方式交费后没法开通vip会员的bug■
function cash_pay_vip($pay_id=null, $nonce=null)
{
    header('Content-type:application/json; Charset=utf-8');
    global $current_user;
    isLoginCheck(); //检测登录
    $uid     = $current_user->ID;
    // $pay_id = !empty($_POST['pay_id']) ? (int) $_POST['pay_id'] : null;
    // $nonce   = !empty($_POST['nonce']) ? $_POST['nonce'] : null;
    if ($nonce && !wp_verify_nonce($nonce, 'caoclick-'.$uid)) {
        echo json_encode(array('status' => '0', 'msg' => '非法请求'));
        exit;
    }

    // 验证通过 开始处理消费逻辑
    $CaoUser = new CaoUser($uid);

    // 获取后台价格设置
    $vip_pay_setting = _cao('vip-pay-setting');
    $payInfo = [];
    foreach ($vip_pay_setting as $key => $item) {
        if ($key == $pay_id) {
            $payInfo = $item;
            break; // 当 $value为c时，终止循环
        }
    }

    // 计算价格 验证会员折扣权限
    $pay_price = $payInfo['price'] * -1;
    $pay_daynum = $payInfo['daynum'];


    // 添加纪录
    if ($uid) {
        $Caolog    = new Caolog();
        $new_money = $old_money + $amount;
        $note      = '购买'._cao('site_vip_name') .' '. $amount;
        $Caolog->addlog($uid, $old_money, $amount, $new_money, 'other', $note);
    }

    // 扣费成功 更新会员数据
    if (!$CaoUser->update_vip_pay($pay_daynum)) {
        echo json_encode(array('status' => '0', 'msg' => '购买失败，请联系网站管理员'));
        exit;
    }

    if ($pay_daynum == 9999) {
        $success_msg = '成功开通：终身特权！ 消费：' . $payInfo['price'] . _cao('site_money_ua');
    } else {
        $success_msg = '成功开通：'.$pay_daynum.'天特权！ 消费：' . $payInfo['price'] . _cao('site_money_ua');
    }

    echo json_encode(array('status' => '1', 'msg' => $success_msg));
    if (_cao('is_mail_nitfy_vip')) {
        _sendMail($current_user->user_email, 'VIP特权开通成功', $success_msg);
    }
    exit;
}

/**
 * [add_pay_post 购买文章资源]
 * @Author   Dadong2g
 * @DateTime 2019-06-02T15:33:41+0800
 */
function add_pay_post()
{
    header('Content-type:application/json; Charset=utf-8');
    global $current_user;
    isLoginCheck(); //检测登录
    $uid     = $current_user->ID;
    $post_id = !empty($_POST['post_id']) ? (int) $_POST['post_id'] : null;
    $post_vid = (int)$_POST['post_vid'];

    if (isset($post_vid) && $post_id==cao_get_page_by_slug('user')) {
        # go to vip
        pay_vip($post_vid, wp_create_nonce('caoclick-' . $current_user->ID));
        exit;
    }

    $nonce   = !empty($_POST['nonce']) ? $_POST['nonce'] : null;
    // $create_nonce= wp_create_nonce('caopay-'.$uid);
    if ($nonce && !wp_verify_nonce($nonce, 'caopay-' . $post_id)) {
        echo json_encode(array('status' => '0', 'msg' => '非法请求'));
        exit;
    }

    if (!$post_id > 0) {
        echo json_encode(array('status' => '0', 'msg' => '资源错误'));
        exit;
    }
    // 验证通过 开始处理消费逻辑
    $PostPay = new PostPay($uid, $post_id);
    $CaoUser = new CaoUser($uid);

    if (get_post_meta($post_id, 'cao_close_novip_pay', true) && !$CaoUser->vip_status()) {
        echo json_encode(array('status' => '0', 'msg' => '该资源为会员专属资源，普通用户无购买权限。开通会员后即可正常购买获取。'));
        exit;
    }

    // 检测用户是否已经购买过 防止重复扣费
    if ($PostPay->isPayPost()) {
        echo json_encode(array('status' => '0', 'msg' => '您已经购买过'));
        exit;
    }
    // 计算价格 验证会员折扣权限
    $post_price    = get_post_meta($post_id, 'cao_price', true);
    $post_vip_rate = get_post_meta($post_id, 'cao_vip_rate', true);
    $cao_is_boosvip  = get_post_meta($post_id, 'cao_is_boosvip', true);
    if ($cao_is_boosvip && is_boosvip_status($uid)) {
        $post_price    = 0;
    }
    $vip_status    = $CaoUser->vip_status();
    if ($vip_status) {
        $order_vip_rate = $post_vip_rate;
    } else {
        $order_vip_rate = 1;
    }
    // 发起订单请求
    $order_trade_no = date("ymdhis") . mt_rand(100, 999) . mt_rand(100, 999) . mt_rand(100, 999); // 订单号
    $payInfo = $PostPay->add($post_price, $order_vip_rate, $order_trade_no, 99);
    if (!$payInfo || !is_array($payInfo)) {
        echo json_encode(array('status' => '0', 'msg' => '添加订单失败'));
        exit;
    }
    // 订单添加成功 开始扣费逻辑
    $amount    = $payInfo['order_amount'] * -1;
    $old_money = $CaoUser->get_balance();
    if (!$CaoUser->update_balance($amount)) {
        echo json_encode(array('status' => '0', 'msg' => '可用余额不足，<b><a href="'.esc_url(home_url('/user?action=charge')).'">去充值</a></b>'));
        exit;
    }
    // 添加纪录
    if ($uid) {
        $Caolog    = new Caolog();
        $new_money = $old_money + $amount;
        $note      = '站内货币购买资源 ' . $amount;
        $Caolog->addlog($uid, $old_money, $amount, $new_money, 'post', $note);
    }

    // 扣费成功 更具上面返回的订单号更新订单状态
    if (!$PostPay->update($payInfo['order_trade_no'])) {
        echo json_encode(array('status' => '0', 'msg' => '订单状态异常，请联系管理员'));
        exit;
    }
    // 更新完成 更新资源销售数量 输出成功信息
    $before_paynum = get_post_meta($post_id, 'cao_paynum', true);
    update_post_meta($post_id, 'cao_paynum', (int) $before_paynum + 1);
    // 发放佣金
    $author_id = (int)get_post($post_id)->post_author;
    if ($author_id != $uid) {
        //自己购买自己不发放
        add_post_author_bonus($author_id, $payInfo['order_amount']);
    }
    echo json_encode(array('status' => '1', 'msg' => '购买成功，扣除：' . $payInfo['order_amount'] . _cao('site_money_ua')));
    if (_cao('is_mail_nitfy_pay')) {
        _sendMail($current_user->user_email, '资源购买成功', '成功购买资源，扣除：' . $payInfo['order_amount'] . _cao('site_money_ua'));
    }
    exit;
}
add_action('wp_ajax_add_pay_post', 'add_pay_post');
add_action('wp_ajax_nopriv_add_pay_post', 'add_pay_post');


/**■■■■■■■
 * [pay_vip 购买vip会员]■■■■■■■
 ■■■■■■■*/
function pay_vip($pay_id=null, $nonce=null)
{
    header('Content-type:application/json; Charset=utf-8');
    global $current_user;
    isLoginCheck(); //检测登录
    $uid     = $current_user->ID;
    // $pay_id = !empty($_POST['pay_id']) ? (int) $_POST['pay_id'] : null;
    // $nonce   = !empty($_POST['nonce']) ? $_POST['nonce'] : null;
    if ($nonce && !wp_verify_nonce($nonce, 'caoclick-'.$uid)) {
        echo json_encode(array('status' => '0', 'msg' => '非法请求'));
        exit;
    }

    if (!is_numeric($pay_id)) {
        echo json_encode(array('status' => '0', 'msg' => '请选择开通套餐'));
        exit;
    }

    if (_cao('is_pay_vip_dashed_yec')) {
        echo json_encode(array('status' => '0', 'msg' => '本站仅限在线支付开通'));
        exit;
    }


    // 验证通过 开始处理消费逻辑
    $PostPay = new PostPay($uid, $post_id);
    $CaoUser = new CaoUser($uid);


    // 获取后台价格设置
    $vip_pay_setting = _cao('vip-pay-setting');
    $payInfo = [];
    foreach ($vip_pay_setting as $key => $item) {
        if ($key == $pay_id) {
            $payInfo = $item;
            break; // 当 $value为c时，终止循环
        }
    }
    if (empty($payInfo)) {
        echo json_encode(array('status' => '0', 'msg' => '购买信息错误'));
        exit;
    }

    // 计算价格 验证会员折扣权限
    $pay_price = $payInfo['price'] * -1;
    $pay_daynum = $payInfo['daynum'];

    if (is_boosvip_status($uid)) {
        echo json_encode(array('status' => '0', 'msg' => '您已经是终身永久，无需重复开通'));
        exit;
    }

    // 订单计算成功 开始扣费逻辑
    $amount    = $payInfo['price'] * -1;
    $old_money = $CaoUser->get_balance();
    if (!$CaoUser->update_balance($amount)) {
        echo json_encode(array('status' => '0', 'msg' => '可用余额不足'));
        exit;
    }
    // 添加纪录
    if ($uid) {
        $Caolog    = new Caolog();
        $new_money = $old_money + $amount;
        $note      = '购买'._cao('site_vip_name') .' '. $amount;
        $Caolog->addlog($uid, $old_money, $amount, $new_money, 'other', $note);
    }

    // 扣费成功 更新会员数据
    if (!$CaoUser->update_vip_pay($pay_daynum)) {
        echo json_encode(array('status' => '0', 'msg' => '购买失败，请联系网站管理员'));
        exit;
    }
    if ($pay_daynum == 9999) {
        $success_msg = '成功开通：终身特权！ 扣除：' . $payInfo['price'] . _cao('site_money_ua');
    } else {
        $success_msg = '成功开通：'.$pay_daynum.'天特权！ 扣除：' . $payInfo['price'] . _cao('site_money_ua');
    }

    echo json_encode(array('status' => '1', 'msg' => $success_msg));
    if (_cao('is_mail_nitfy_vip')) {
        _sendMail($current_user->user_email, '特权开通成功', $success_msg);
    }
    exit;
}
// add_action('wp_ajax_pay_vip', 'pay_vip');
// add_action('wp_ajax_nopriv_pay_vip', 'pay_vip');



/**
 * [userinfo AJAX保存用户基本信息]
 * @Author   Dadong2g
 * @DateTime 2019-05-31T13:12:33+0800
 * @return   [type]                   [description]
 */
function edit_user_info()
{
    global $current_user;
    isLoginCheck(); //检测登录
    $uid         = $current_user->ID;
    $nickname    = !empty($_POST['nickname']) ? wp_strip_all_tags($_POST['nickname']) : null;
    $email       = !empty($_POST['email']) ? $_POST['email'] : null;
    $avatar_type = !empty($_POST['user_avatar_type']) ? sanitize_text_field(wp_unslash($_POST[ 'user_avatar_type' ])) : 'gravatar';
    $phone       = !empty($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST[ 'phone' ])) : null;
    $qq          = !empty($_POST['qq']) ? sanitize_text_field(wp_unslash($_POST[ 'qq' ])) : null;
    $description = !empty($_POST['description']) ? $_POST['description'] : null;

    $userdata                 = array();
    $userdata['ID']           = $uid;
    $userdata['nickname']     = $nickname;
    $userdata['display_name'] = @$userdata['nickname'];


    if ($current_user->user_email != $email) {
        // 邮箱验证
        $preg_email = '/^[a-zA-Z0-9]+([-_.][a-zA-Z0-9]+)*@([a-zA-Z0-9]+[-.])+([a-z]{2,5})$/ims';
        if (preg_match($preg_email, $email)) {
            $userdata['user_email'] = esc_sql($email);
        } else {
            echo "邮箱格式错误";
            exit();
        }

        // 是否需要邮箱验证
        if (_cao('is_user_bang_email')) {
            if (empty($_POST['captcha']) || empty($_SESSION['CAO_code_captcha']) || trim(strtolower($_POST['captcha'])) != $_SESSION['CAO_code_captcha']) {
                echo "新邮箱验证码错误";
                exit();
            }
            if ($_SESSION['CAO_code_captcha_email'] != $email) {
                echo "验证码与新邮箱不对应";
                exit();
            }
        }
    }


    if (wp_update_user($userdata)) {
        if ($phone && $phone != get_user_meta($uid, 'phone', true)) {
            // 手机验证
            if (preg_match("/^1[345678]{1}\d{9}$/", $phone)) {
                update_user_meta($uid, 'phone', $phone);
            } else {
                echo "手机号码格式错误";
                exit();
            }
        }
        // is_numeric();
        if ($qq && $qq != get_user_meta($uid, 'qq', true)) {
            if (is_numeric($qq)) {
                update_user_meta($uid, 'qq', $qq);
            } else {
                echo "QQ号码格式错误";
                exit();
            }
        }
        if ($description && $description != get_user_meta($uid, 'description', true)) {
            update_user_meta($uid, 'description', $description);
        }
        if ($avatar_type) {
            update_user_meta($uid, 'user_avatar_type', $avatar_type);
        }
        echo "1";
        exit();
    } else {
        echo "修改失败";
        exit();
    }

    exit();
}

add_action('wp_ajax_edit_user_info', 'edit_user_info');
add_action('wp_ajax_nopriv_edit_user_info', 'edit_user_info');

//修改密码
function edit_repassword()
{
    global $current_user;
    isLoginCheck(); //检测登录
    $uid         = $current_user->ID;
    $password    = !empty($_POST['password']) ? wp_strip_all_tags($_POST['password']) : null;
    $new_password    = !empty($_POST['new_password']) ? wp_strip_all_tags($_POST['new_password']) : null;
    $re_password    = !empty($_POST['re_password']) ? wp_strip_all_tags($_POST['re_password']) : null;
    if (strlen($password) < 6) {
        echo "密码长度至少6位";
        exit();
    } elseif ($new_password != $re_password) {
        echo "两次输入密码不一致";
        exit();
    } else {
        $userdata['ID']        = $uid;
        $userdata['user_pass'] = $re_password;
        wp_update_user($userdata);
        echo "1";
        exit();
    }
    exit();
}
add_action('wp_ajax_edit_repassword', 'edit_repassword');
add_action('wp_ajax_nopriv_edit_repassword', 'edit_repassword');




function unset_open_oauth()
{
    global $current_user;
    isLoginCheck(); //检测登录
    $uid = $current_user->ID;
    $unsetid = !empty($_POST['unsetid']) ? (int)$_POST['unsetid'] : null;
    if ($unsetid) {
        update_user_meta($uid, 'open_'.$unsetid.'_openid', '');
        update_user_meta($uid, 'open_'.$unsetid.'_bind', 0);
        echo "1";
        exit();
    } else {
        echo "0";
        exit();
    }
}
add_action('wp_ajax_unset_open_oauth', 'unset_open_oauth');
add_action('wp_ajax_nopriv_unset_open_oauth', 'unset_open_oauth');



/**
 * [user_qiandao 签到]
 * @Author   Dadong2g
 * @DateTime 2019-09-21T12:11:40+0800
 * @return   [type]                   [description]
 */
function user_qiandao()
{
    header('Content-type:application/json; Charset=utf-8');
    global $current_user;
    $uid         = ($current_user->ID) ? $current_user->ID : 0 ;
    if ($uid == 0) {
        echo json_encode(array('status' => '0', 'msg' => '请登录后签到'));
        exit;
    }
    if (!_cao('is_qiandao', '1')) {
        echo json_encode(array('status' => '0', 'msg' => '签到功能暂未开启'));
        exit;
    }
    if (_cao_user_is_qiandao()) {
        echo json_encode(array('status' => '0', 'msg' => '今日已签到，请明日再来'));
        exit;
    } else {
        $thenTime = time();
        $qiandao_money = _cao('qiandao_to_money', '5');
        update_user_meta($uid, 'cao_qiandao_time', $thenTime);
        // 卡密有效 进行换算
        $CaoUser   = new CaoUser($uid);
        $old_money = $CaoUser->get_balance();
        if (!$CaoUser->update_balance($qiandao_money)) {
            echo json_encode(array('status' => '0', 'msg' => '签到异常，请稍后重试'));
            exit;
        }
        // 添加纪录
        if ($uid) {
            $Caolog    = new Caolog();
            $new_money = $old_money + $qiandao_money;
            $note      = '签到赠送'. $qiandao_money;
            $Caolog->addlog($uid, $old_money, $qiandao_money, $new_money, 'other', $note);
        }
        echo json_encode(array('status' => '1', 'msg' => '签到成功，赠送'.$qiandao_money._cao('site_money_ua') ));
        exit;
    }
}
add_action('wp_ajax_user_qiandao', 'user_qiandao');
add_action('wp_ajax_nopriv_user_qiandao', 'user_qiandao');



/**
 * [edit_user_qr AJAX保存收款码]
 * @Author   Dadong2g
 * @DateTime 2019-05-31T13:35:53+0800
 * @return   [type]                   [description]
 */
function edit_user_qr()
{
    global $current_user;
    isLoginCheck(); //检测登录

    $uid       = $current_user->ID;
    $qr_alipay = !empty($_POST['qr_alipay']) ? sanitize_text_field(wp_unslash($_POST[ 'qr_alipay' ])) : null;
    $qr_weixin = !empty($_POST['qr_weixin']) ? sanitize_text_field(wp_unslash($_POST[ 'qr_weixin' ])) : null;
    if ($qr_alipay && $qr_alipay != get_user_meta($uid, 'qr_alipay', true)) {
        update_user_meta($uid, 'qr_alipay', $qr_alipay);
    }
    if ($qr_weixin && $qr_weixin != get_user_meta($uid, 'qr_weixin', true)) {
        update_user_meta($uid, 'qr_weixin', $qr_weixin);
    }
    echo "1";
    exit();
}

add_action('wp_ajax_edit_user_qr', 'edit_user_qr');
add_action('wp_ajax_nopriv_edit_user_qr', 'edit_user_qr');



//收藏文章
function fav_post()
{
    header('Content-type:application/json; Charset=utf-8');
    global $current_user;
    $uid         = ($current_user->ID) ? $current_user->ID : 0 ;
    $post_id    = !empty($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

    if ($uid == 0) {
        echo json_encode(array('status' => '0', 'msg' => '请登录后再收藏'));
        exit;
    }

    if (is_get_post_fav($post_id)) {
        // 取消收藏
        _cao_del_follow_post($uid, $post_id);
        echo json_encode(array('status' => '1', 'msg' => '取消收藏成功'));
        exit;
    } else {
        //新收藏
        _cao_add_follow_post($uid, $post_id);
        echo json_encode(array('status' => '1', 'msg' => '收藏成功'));
        exit;
    }
    exit;
}
add_action('wp_ajax_fav_post', 'fav_post');
add_action('wp_ajax_nopriv_fav_post', 'fav_post');



///////////*******下载弹窗********////////////

function user_down_ajax()
{
    header('Content-type:application/json; Charset=utf-8');
    global $current_user;
    $uid = ($current_user->ID) ? $current_user->ID : 0 ;
    $post_id = !empty($_POST['post_id']) ? (int) $_POST['post_id'] : 0;

    if ($uid == 0 && !_cao('is_ripro_nologin_pay', '1')) {
        echo json_encode(array('status' => '0', 'msg' => '请登录后下载'));
        exit;
    }
    if (!$post_id) {
        echo json_encode(array('status' => '0', 'msg' => '下载参数错误，请刷新重试'));
        exit;
    }

    // 判断是否有权限下载
    $CaoUser = new CaoUser($uid);
    $PostPay = new PostPay($uid, $post_id);
    $RiProPayAuth = new RiProPayAuth($uid, $post_id);
    $cao_is_post_free = $RiProPayAuth->cao_is_post_free();
    if ($cao_is_post_free && !is_user_logged_in() && !_cao('is_ripro_free_no_login')) {
        echo json_encode(array('status' => '0', 'msg' => '免费资源请登录后下载'));
        exit;
    }

    if ($PostPay->isPayPost() || $cao_is_post_free) {

        //免登录购买用户直接下载
        if (!is_user_logged_in() && _cao('is_ripro_nologin_pay', '1')) {
            echo json_encode(array('status' => '1', 'msg' => esc_url(home_url('/go?post_id='.$post_id)) ));
            exit;
        }

        // 判断会员类型 判断下载次数
        $vip_status = $CaoUser->vip_status();
        $this_vip_downum = $CaoUser->cao_vip_downum($uid, $vip_status);
        if ($this_vip_downum['is_down'] || ($PostPay->isPayPost() && !_cao('is_all_down_num', '0'))) {
            echo json_encode(array('status' => '1', 'msg' => esc_url(home_url('/go?post_id='.$post_id)) ));
            exit;
        } else {
            $srt = (_cao('is_all_down_num', '0')) ? '可' : '免费' ;
            $_msg = '<p>今日免费下载次数已用【'.$this_vip_downum['today_down_num'].'】,剩余【'.$this_vip_downum['over_down_num'].'】</p>';
            $_msg .= '<p style=" font-size: 15px; color: #888; margin: 0; ">'._cao('site_no_vip_name').'用户每日'.$srt.'下载次数（'.($_num=(_cao('is_novip_down_num')) ? _cao('novip_down_num') : '无限').'）</p>';
            $_msg .= '<p style=" font-size: 15px; color: #888; margin: 0; ">'._cao('site_vip_name').'会员每日'.$srt.'下载次数（'.($_num=(_cao('is_vip_down_num')) ? _cao('vip_down_num') : '无限').'）</p>';
            $_msg .= '<p style=" font-size: 15px; color: #888; margin: 0; ">永久'._cao('site_vip_name').'会员每日'.$srt.'下载次数（'.($_num=(_cao('is_boosvip_down_num')) ? _cao('boosvip_down_num') : '无限').'）</p>';
            echo json_encode(array('status' => '0', 'msg' => $_msg));
            exit;
        }
    } else {
        echo json_encode(array('status' => '0', 'msg' => '您没有购买此资源或下载权限错误' ));
        exit;
    }
    echo json_encode(array('status' => '0', 'msg' => '您没有购买此资源或下载权限错误' ));
    exit;
}
add_action('wp_ajax_user_down_ajax', 'user_down_ajax');
add_action('wp_ajax_nopriv_user_down_ajax', 'user_down_ajax');
