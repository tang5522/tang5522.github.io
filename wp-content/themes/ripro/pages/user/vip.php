<?php
// 开通会员
global $current_user;
$CaoUser = new CaoUser($current_user->ID);
$post_id = cao_get_page_by_slug('user');
$create_nonce = wp_create_nonce('caopay-' . $post_id);
?>

<div class="col-xs-12 col-sm-12 col-md-9">
	
	<form class="mb-0">
		<?php if (_cao('is_userpage_vip_head')) {
			get_template_part( 'pages/user/header-card');
		}?>
        
        <div class="form-box">
            <div class="row">
                <div class="col-md-12">
                    <div class="charge">
                        <div class="modules__title">
                            <h4><i class="fa fa-diamond"></i> <?php echo $CaoUser->vip_name().'用户 · 特权到期时间：'.$CaoUser->vip_end_time() ?> · 选择套餐购买或续费</h4>
                        </div>
                        <div class="pt-30">
                            <div class="payvip-box">
					            <div class="row">
					            	<?php
					            	$vip_pay_setting = _cao('vip-pay-setting');
					            	foreach ($vip_pay_setting as $key => $item) {
					            		echo '<div class="col-md-4 col-sm-4">';
					            		echo '<div class="vip-info" data-id="'.$key.'" data-price="'.$item['price'].'" style="background:'.$item['color'].';">';
					            		echo '<span class="vipc"><i class="fa fa-diamond"></i> '._cao('site_vip_name').'</span>';
					            		if ($item['daynum'] == 9999) {
					            			echo '<small style="color:'.$item['color'].';">终身永久</small>';
					            		}else{
					            			if ($item['daynum']==30) {
					            				$daynum_str = '1个月';
					            			}elseif ($item['daynum']==60) {
					            				$daynum_str = '2个月';
					            			}elseif ($item['daynum']==90) {
					            				$daynum_str = '3个月';
					            			}elseif ($item['daynum']==180) {
					            				$daynum_str = '6个月';
					            			}elseif ($item['daynum']==365) {
					            				$daynum_str = '一年';
					            			}else{
					            				$daynum_str = $item['daynum'].'天';
					            			}
					            			echo '<small style="color:'.$item['color'].';">'.$daynum_str.'</small>';
					            		}
					            		echo '<p>购买价格</p>';
					            		echo '<h3>'.$item['price']._cao('site_money_ua').'</h3>';
					            		echo $item['desc'];
					            		echo '</div></div>';
					            	}
					                ?>
					            </div>
					        </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12" style=" text-align: center; ">
	            	<input type="hidden" name="pay_id" value="">
	            	<?php echo '<button type="button" class="click-pay click-payvip btn btn--danger" data-postid="' . $post_id . '" data-postvid="0" data-nonce="' . $create_nonce . '" data-price="0">立即开通</button>';?>
            	</div>

            </div>

        </div>
    </form>
</div>