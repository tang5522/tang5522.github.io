<?php

if (!defined('ABSPATH')) {die;} // Cannot access directly.


/////////////////////////////////////////
/////////////////////////////////////////
/////////////////////////////////////////
/////////////////////////////////////////


function add_admin_ripro_shop_menu() {
    $index_page = 'cao_admin';
    add_menu_page(esc_html__('商城管理','cao'),esc_html__('商城管理','cao'), 'manage_options', $index_page, $index_page, 'dashicons-cart', 100);
    $menu = [
        ['menu'=>'cao_admin','name'=>esc_html__('商城总览','cao')],
        ['menu'=>'cao_admin_change_log','name'=>esc_html__(_cao('site_money_ua').'充值记录','cao')],
        ['menu'=>'cao_admin_pay_log','name'=>esc_html__('资源/会员订单','cao')],
        //['menu'=>'cao_admin_pay_ph','name'=>esc_html__('资源销售排行','cao')],
        ['menu'=>'cao_admin_down_log','name'=>esc_html__('用户下载记录查询','cao')],
        ['menu'=>'cao_admin_balance_log','name'=>esc_html__('用户余额记录查询','cao')],
        ['menu'=>'cao_admin_ref_log','name'=>esc_html__('用户佣金记录查询','cao')],
        
        ['menu'=>'cao_admin_price_log','name'=>esc_html__('文章价格批量修改','cao')],
        ['menu'=>'cao_admin_aff_log','name'=>esc_html__('网站佣金提现审核','cao')],
        ['menu'=>'cao_admin_cdk_log','name'=>esc_html__('网站卡密管理','cao')],
        ['menu'=>'cao_admin_user_log','name'=>esc_html__('网站会员管理','cao')],
        ['menu'=>'wp_clean_up_page','name'=>esc_html__('数据库优化','cao')],
    ];
    foreach ($menu as $k => $v) {
        add_submenu_page($index_page,$v['name'],$v['name'],'manage_options',$v['menu'],$v['menu']);
    }

}
if (is_site_shop_open()) {
    add_action('admin_menu', 'add_admin_ripro_shop_menu');
}

require_once get_template_directory() . '/inc/plugins/wp-clean-up/wp-clean-up.php';


function cao_admin() {
    require_once get_template_directory() . '/inc/admin/pages/index.php';
}
function cao_admin_change_log() {
    require_once get_template_directory() . '/inc/admin/pages/change_log.php';
}
function cao_admin_pay_log() {
    require_once get_template_directory() . '/inc/admin/pages/pay_log.php';
}
function cao_admin_pay_ph() {
    require_once get_template_directory() . '/inc/admin/pages/pay_ph.php';
}
function cao_admin_down_log() {
    require_once get_template_directory() . '/inc/admin/pages/down_log.php';
}
function cao_admin_cdk_log() {
    require_once get_template_directory() . '/inc/admin/pages/cdk_log.php';
}
function cao_admin_user_log() {
    require_once get_template_directory() . '/inc/admin/pages/user_log.php';
}
function cao_admin_balance_log() {
    require_once get_template_directory() . '/inc/admin/pages/balance_log.php';
}
function cao_admin_aff_log() {
    require_once get_template_directory() . '/inc/admin/pages/aff_log.php';
}
function cao_admin_ref_log() {
    require_once get_template_directory() . '/inc/admin/pages/ref_log.php';
}
function cao_admin_price_log() {
    require_once get_template_directory() . '/inc/admin/pages/price_log.php';
}




/////////////////////////////////////////
/////////////////////////////////////////
/////////////////////////////////////////
/////////////////////////////////////////



//时间查询
class RiPLus_Time
{
    
    /**
     * 返回今日开始和结束的时间戳
     *
     * @return array
     */
    public static function today()
    {
        
        return [
            mktime(0, 0, 0, date('m'), date('d'), date('Y')),
            mktime(23, 59, 59, date('m'), date('d'), date('Y'))
        ];
    }
 
    /**
     * 返回昨日开始和结束的时间戳
     *
     * @return array
     */
    public static function yesterday()
    {
        $yesterday = date('d') - 1;
        return [
            mktime(0, 0, 0, date('m'), $yesterday, date('Y')),
            mktime(23, 59, 59, date('m'), $yesterday, date('Y'))
        ];
    }
 
    /**
     * 返回本周开始和结束的时间戳
     *
     * @return array
     */
    public static function week()
    {
        $timestamp = time();
        return [
            mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y")),
            mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"))
        ];
    }
 
    /**
     * 返回上周开始和结束的时间戳
     *
     * @return array
     */
    public static function lastWeek()
    {
        $timestamp = time();
        return [
            mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y")),
            mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y"))
        ];
    }
 
    /**
     * 返回本月开始和结束的时间戳
     *
     * @return array
     */
    public static function month($everyDay = false)
    {
        return [
            mktime(0, 0, 0, date('m'), 1, date('Y')),
            mktime(23, 59, 59, date('m'), date('t'), date('Y'))
        ];
    }
 
    /**
     * 返回上个月开始和结束的时间戳
     *
     * @return array
     */
    public static function lastMonth()
    {
        $begin = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
        $end = mktime(23, 59, 59, date('m') - 1, date('t', $begin), date('Y'));
 
        return [$begin, $end];
    }
 
    /**
     * 返回今年开始和结束的时间戳
     *
     * @return array
     */
    public static function year()
    {
        return [
            mktime(0, 0, 0, 1, 1, date('Y')),
            mktime(23, 59, 59, 12, 31, date('Y'))
        ];
    }
 
    /**
     * 返回去年开始和结束的时间戳
     *
     * @return array
     */
    public static function lastYear()
    {
        $year = date('Y') - 1;
        return [
            mktime(0, 0, 0, 1, 1, $year),
            mktime(23, 59, 59, 12, 31, $year)
        ];
    }
 
    public static function dayOf()
    {
 
    }
 
    /**
     * 获取几天前零点到现在/昨日结束的时间戳
     *
     * @param int $day 天数
     * @param bool $now 返回现在或者昨天结束时间戳
     * @return array
     */
    public static function dayToNow($day = 1, $now = true)
    {
        $end = time();
        if (!$now) {
            list($foo, $end) = self::yesterday();
        }
 
        return [
            mktime(0, 0, 0, date('m'), date('d') - $day, date('Y')),
            $end
        ];
    }
 
    /**
     * 返回几天前的时间戳
     *
     * @param int $day
     * @return int
     */
    public static function daysAgo($day = 1)
    {
        $nowTime = time();
        return $nowTime - self::daysToSecond($day);
    }
 
    /**
     * 返回几天后的时间戳
     *
     * @param int $day
     * @return int
     */
    public static function daysAfter($day = 1)
    {
        $nowTime = time();
        return $nowTime + self::daysToSecond($day);
    }
 
    /**
     * 天数转换成秒数
     *
     * @param int $day
     * @return int
     */
    public static function daysToSecond($day = 1)
    {
        return $day * 86400;
    }
 
    /**
     * 周数转换成秒数
     *
     * @param int $week
     * @return int
     */
    public static function weekToSecond($week = 1)
    {
        return self::daysToSecond() * 7 * $week;
    }
 
    private static function startTimeToEndTime()
    {
 
    }
}


/////////////////////////////////////////
/////////////////////////////////////////
/////////////////////////////////////////
/////////////////////////////////////////




add_filter('manage_users_sortable_columns', 'registerdate_column_sortable');
add_filter('request', 'registerdate_column_orderby');
function registerdate_column_sortable($columns) {
    $custom = array(
        'reg_time' => 'registered',
    );
    return wp_parse_args($custom, $columns);
}
function registerdate_column_orderby($vars) {
    if (isset($vars['orderby']) && 'registerdate' == $vars['orderby']) {
        $vars = array_merge($vars, array(
            'meta_key' => 'registerdate',
            'orderby'  => 'meta_value',
        ));
    }
    return $vars;
}

add_filter('pre_get_users', 'filter_users');
add_filter('views_users', 'views_users');
function views_users($views) {
    global $wpdb;
    if (!current_user_can('edit_users')) {
        return $views;
    }
    $type     = 'vip';
    $current  = (isset($_REQUEST['vip_type']) && $_REQUEST['vip_type'] == $type) ? 'class="current"' : '';
    $meta_key = 'cao_user_type';
    $users    = get_users(array(
        'meta_query' => array(
            array(
                'key'     => $meta_key,
                'value'   => $type,
                'compare' => '==',
            ),
        ),
    ));
    $count = count($users);

    $views['vip'] = '<a href="' . admin_url('users.php') . '?vip_type=' . $type . '" ' . $current . '>' . _cao('site_vip_name') . '用户 <span class="count">（' . $count . '）</span></a>';

    $type    = 'vip';
    $current = (isset($_REQUEST['vip_type']) && $_REQUEST['vip_type'] == 'vip_pro') ? 'class="current"' : '';

    $users = get_users(array(
        'meta_query' => array(
            array(
                'key'     => $meta_key,
                'value'   => $type,
                'compare' => '==',
            ),
            array(
                'key'     => 'cao_vip_end_time',
                'value'   => '9999-09-09',
                'compare' => '==',
            ),
        ),
    ));
    $count = count($users);

    $views['vip_pro'] = '<a href="' . admin_url('users.php') . '?vip_type=vip_pro" ' . $current . '>永久' . _cao('site_vip_name') . '用户 <span class="count">（' . $count . '）</span></a>';

    return $views;
}
function filter_users($query) {
    global $pagenow, $wpdb;
    if (is_admin() && 'users.php' == $pagenow && isset($_REQUEST['vip_type']) && $_REQUEST['vip_type'] == 'vip') {
        $meta_key = '_riplus_vip_type';
        $query->set('meta_query', array(
            array(
                'key'     => 'cao_user_type',
                'value'   => 'vip',
                'compare' => '==',
            ),
        ));
    }
    if (is_admin() && 'users.php' == $pagenow && isset($_REQUEST['vip_type']) && $_REQUEST['vip_type'] == 'vip_pro') {
        $meta_key = '_riplus_vip_type';
        $query->set('meta_query', array(
            array(
                'key'     => 'cao_user_type',
                'value'   => 'vip',
                'compare' => '==',
            ),
            array(
                'key'     => 'cao_vip_end_time',
                'value'   => '9999-09-09',
                'compare' => '==',
            ),
        ));
    }
    return $query;
}

/**
 * [my_users_columns 挂钩WP后台用户列表]
 * @Author   Dadong2g
 * @DateTime 2019-05-28T12:32:52+0800
 * @param    [type]                   $columns [description]
 * @return   [type]                            [description]
 */
function my_users_columns($columns) {

    $columns['reg_time']      = __('注册时间');
    $columns['vip_type']      = __('会员类型');
    $columns['vip_balance']   = __('余额');
    $columns['user_status']   = __('账号状态');
    $columns['signup_ip']     = __('注册IP');
    $columns['last_login']    = __('上次登录');
    $columns['last_login_ip'] = __('登录IP');
    unset($columns['role']);
    unset($columns['name']);
    unset($columns['posts']);
    return $columns;
}

/**
 * [output_my_users_columns 添加用户列表自定义列]
 * @Author   Dadong2g
 * @DateTime 2019-05-28T12:32:38+0800
 * @param    [type]                   $var         [description]
 * @param    [type]                   $column_name [description]
 * @param    [type]                   $user_id     [description]
 * @return   [type]                                [description]
 */
function output_my_users_columns($var, $column_name, $user_id) {
    $CaoUser = new CaoUser($user_id);
    $user    = get_userdata($user_id);
    switch ($column_name) {
    case "vip_type":
        return $CaoUser->vip_name();
        break;
    case "vip_balance":
        return $CaoUser->get_balance();
        break;
    case "user_status":
        $is_ban = (get_user_meta($user_id, 'cao_banned', true)) ? true : false;
        if ($is_ban) {
            $str = '封号';
        } else {
            $str = '正常';
        }
        return $str;
        break;
    case "reg_time":
        return get_date_from_gmt($user->user_registered);
        break;
    case "signup_ip":
        if ($meta = get_user_meta($user->ID, 'signup_ip', true)) {
           return $meta;
        }else{
            return '';
        }
        
        break;
    case "last_login":
        if ($meta = get_user_meta($user->ID, 'last_login', true)) {
           return $meta;
        }else{
            return '';
        }
        break;

    case "last_login_ip":
        if ($meta = get_user_meta($user->ID, 'last_login_ip', true)) {
           return $meta;
        }else{
            return '';
        }
        
        break;

    }
}
add_filter('manage_users_columns', 'my_users_columns');
add_action('manage_users_custom_column', 'output_my_users_columns', 10, 3);

/**
 * [my_post_custom_columns 挂钩WP后台文章列表]
 * @Author   Dadong2g
 * @DateTime 2019-05-28T12:33:01+0800
 * @param    [type]                   $columns [description]
 * @return   [type]                            [description]
 */
function my_post_custom_columns($columns) {
    // Add a new field
    $columns['cao_price'] = __('资源价格');
    // Delete an existing field, eg. comments
    unset($columns['comments']);
    return $columns;
}
/**
 * [output_my_post_custom_columns 添加文章列表自定义列]
 * @Author   Dadong2g
 * @DateTime 2019-05-28T12:32:08+0800
 * @param    [type]                   $column_name [description]
 * @param    [type]                   $post_id     [description]
 * @return   [type]                                [description]
 */
function output_my_post_custom_columns($column_name, $post_id) {
    switch ($column_name) {
    case "cao_price":
        // Retrieve data and echo result
        $cao_price = (get_post_meta($post_id, 'cao_price', true)) ? get_post_meta($post_id, 'cao_price', true) : '—';
        echo $cao_price;
        break;
    }
}

add_filter('manage_posts_columns', 'my_post_custom_columns');
add_action('manage_posts_custom_column', 'output_my_post_custom_columns', 10, 2);
