<?php
  global $post;
  $post_id = $post->ID;
  $user_id = is_user_logged_in() ? wp_get_current_user()->ID : 0;
  $video_poster_meta = get_post_meta($post->ID, 'video_poster_url', true);
  $site_logo = _cao( 'site_logo');
  $cao_video = _get_post_video_status();
  $cao_video_img = ($video_poster_meta) ? $video_poster_meta : _get_post_timthumb_src();
  $cao_video_url = _get_post_video_url();
if ($cao_video && $cao_video_url): ?>
<link rel="stylesheet" href="<?php echo get_template_directory_uri() . '/assets/css/DPlayer.min.css'?>" />
<div id="ripro-mse">
  <div id="mse-video"></div>
</div>
<?php
  $cao_video_url = '';
  $CaoUser = new CaoUser($user_id);
  // INFO
  $cao_price     = get_post_meta($post_id, 'cao_price', true);
  $cao_vip_rate  = get_post_meta($post_id, 'cao_vip_rate', true);
  $cao_paynum    = get_post_meta($post_id, 'cao_paynum', true);
  $cao_is_boosvip  = get_post_meta($post_id, 'cao_is_boosvip', true);
  $cao_close_novip_pay  = get_post_meta($post_id, 'cao_close_novip_pay', true);
  $cao_is_video_free  = get_post_meta($post_id, 'cao_is_video_free', true);

  if (!$cao_is_video_free){
   //启用了付费视频
    $site_vip_name = _cao('site_vip_name');
    $site_money_ua = _cao('site_money_ua');
    if ($CaoUser->vip_status()) {
        $cao_this_am = ($cao_price * $cao_vip_rate) . $site_money_ua;
    } else {
        $cao_this_am = $cao_price . $site_money_ua;
    }

    // 优惠信息
    switch ($cao_vip_rate) {
        case 1:
            $rate_text = '付费后观看';
            break;
        case 0:
            $rate_text = $site_vip_name . '会员免费观看';
            break;
        default:
            $rate_text = $site_vip_name . '会员价 ' . ($cao_vip_rate * 10) . ' 折观看';
    }

    if ($cao_is_boosvip) {
        $rate_text = '永久'.$site_vip_name.'会员免费观看';
    }
    
    if ($cao_price == 0) {
        $rate_text = '免费观看';
    }

    $close_novip_pay_str = ($cao_close_novip_pay) ? '该视频仅限'.$site_vip_name.'会员购买。' : '' ;
    
    $RiProPayAuth = new RiProPayAuth($user_id,$post_id);
    switch ($RiProPayAuth->ThePayAuthStatus()) {
        case 11: //免登陆  已经购买过 输出OK
          $cao_video_url = _get_post_video_url();
          break;
        case 12: //免登陆  登录后查看
          if (!_cao('is_ripro_free_no_login')) {
            $do_video = '<div class="content-do-video"><div class="views">';
            $do_video .= '<span class="rate label label-warning">在线观看</span>';
            $do_video .= '<div class="login-false">当前视频登录后免费观看';
			$do_video .= '<div class="login-false">当前视频登录后免费下载';
            $do_video .= '<div class="coin"><span class="label label-warning">免费</span></div>';
            $do_video .= '</div>';
            $do_video .= '<p class="t-c">会员拥有观看和下载权限！</p>';
            $do_video .= '<div class="pc-button">';
            $do_video .= '<button type="button" class="login-btn btn btn--primary"><i class="fa fa-user"></i> 登录/注册</button>';
            $do_video .= '</div>';
            $do_video .= '</div></div>';
          }else{
            $cao_video_url = _get_post_video_url();
          }
          break;
        case 13: //免登陆 输出购买按钮信息
          $create_nonce = wp_create_nonce('caopay-' . $post_id);
          $do_video = '<div class="content-do-video"><div class="views">';
          $do_video .= '<span class="rate label label-warning"><i class="fa fa-lock"></i> ' . $rate_text . '</span>';
          $do_video .= '<div class="login-false">'.$close_novip_pay_str.'观看当前视频需要支付';
          $do_video .= '<div class="coin"><span class="label label-warning">' . $cao_this_am . '</span></div>';
          $do_video .= '</div>';
          $do_video .= '<p class="t-c">已有<span class="red">' . $cao_paynum . '</span>人支付</p>';
          $do_video .= '<div class="pc-button">';
          if ($cao_close_novip_pay && !$CaoUser->vip_status()) {
              $do_video .= '<button type="button" class="login-btn btn btn--primary"><i class="fa fa-user"></i> 仅限'.$site_vip_name.'会员购买</button>';
          }else{
              $do_video .= '<button type="button" class="click-pay btn btn--secondary" data-postid="' . $post_id . '" data-nonce="' . $create_nonce . '" data-price="' . $cao_this_am . '"><i class="fa fa-money"></i> 支付后观看</button>';
          }
          $do_video .= '</div>';
          $do_video .= '</div></div>';
          break;
        case 21: //登陆后  已经购买过 输出OK
          $cao_video_url = _get_post_video_url();
          break;
        case 22: //登陆后  输出购买按钮信息
          $create_nonce = wp_create_nonce('caopay-' . $post_id);
          $do_video = '<div class="content-do-video"><div class="views">';
          $do_video .= '<span class="rate label label-warning"><i class="fa fa-lock"></i> ' . $rate_text . '</span>';
          $do_video .= '<div class="login-false">'.$close_novip_pay_str.'观看当前视频需要支付';
          $do_video .= '<div class="coin"><span class="label label-warning">' . $cao_this_am . '</span></div>';
          $do_video .= '</div>';
          $do_video .= '<p class="t-c">已有<span class="red">' . $cao_paynum . '</span>人支付</p>';
          $do_video .= '<div class="pc-button">';
          if ($cao_close_novip_pay && !$CaoUser->vip_status()) {
            $do_video .= '<a class="btn btn--secondary" href="'.esc_url(home_url('/user?action=vip')).'" ><i class="fa fa-money"></i> 开通'.$site_vip_name.'会员</a>';
          }else{
            $do_video .= '<button type="button" class="click-pay btn btn--secondary" data-postid="' . $post_id . '" data-nonce="' . $create_nonce . '" data-price="' . $cao_this_am . '"><i class="fa fa-money"></i> 立即购买</button>';
          }
          $do_video .= '</div>';
          $do_video .= '</div></div>';
          break;
        case 31: //没有开启免登录 没有登录 输出登录后进行操作
          $do_video = '<div class="content-do-video"><div class="views">';
          $do_video .= '<span class="rate label label-warning"><i class="fa fa-lock"></i> ' . $rate_text . '</span>';
          $do_video .= '<div class="login-false">观看当前视频需要支付';
          $do_video .= '<div class="coin"><span class="label label-warning">' . $cao_this_am . '</span></div>';
          $do_video .= '</div>';
          $do_video .= '<p class="t-c">已有<span class="red">' . $cao_paynum . '</span>人支付</p>';
          $do_video .= '<div class="pc-button">';
          $do_video .= '<button type="button" class="login-btn btn btn--primary"><i class="fa fa-user"></i> 登录购买</button>';
          $do_video .= '</div>';
          $do_video .= '</div></div>';
          break;
    }
  }else{
    $cao_video_url = _get_post_video_url();
  }

?>

<script src="<?php echo get_template_directory_uri() . '/assets/js/plugins/hls.min.js'?>"></script>
<script src="<?php echo get_template_directory_uri() . '/assets/js/plugins/DPlayer.min.js'?>"></script>
<script type="text/javascript">
  const dp = new DPlayer({
      container: document.getElementById('ripro-mse'),
      logo: '<?php echo $site_logo;?>',  //LOGO
      video: {
          url: '<?php echo $cao_video_url;?>',  //视频地址
          type: 'auto', //视频类型
          pic: '<?php echo $cao_video_img;?>', //视频类型
      },
      contextmenu: [{text: '<?php echo get_bloginfo('name');?>',link: '<?php echo home_url();?>',}],
  });

  <?php if ($cao_video_url == ''): ?>
    var mask = $(".dplayer-mask")
    mask.show()
    if (!mask.hasClass('content-do-video')) {
      mask.append('<?php echo $do_video;?>');
      $(".dplayer-video-wrap").addClass("video-filter");
    }
  <?php else: ?>
    var notice = $(".dplayer-notice")
    if (notice.hasClass('dplayer-notice')) {
      notice.css("opacity","0.8"); //设置透明度
      notice.append('<i class="fa fa-unlock-alt"></i> 您已获得当前视频观看权限');
    }
    dp.on('play', function() {
      notice.css("opacity","0"); //设置透明度
    });
    dp.on('pause', function() {
      notice.css("opacity","0.8"); //设置透明度
    });
  <?php endif;?>
</script>
<?php endif;?>