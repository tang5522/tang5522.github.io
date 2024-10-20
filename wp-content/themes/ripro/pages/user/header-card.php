<?php 
global $current_user;
$CaoUser = new CaoUser($current_user->ID);
$Reflog = new Reflog($current_user->ID);
$vip_status = $CaoUser->vip_status();
$cao_vip_downum = $CaoUser->cao_vip_downum($current_user->ID,$vip_status);
$_c = false;
if (is_boosvip_status($current_user->ID) && _cao('is_boosvip_down_num')) {
    $_c = true;
}elseif ($vip_status && !is_boosvip_status($current_user->ID) && _cao('is_vip_down_num')) {
    $_c = true;
}elseif(!$vip_status && _cao('is_novip_down_num')) {
    $_c = true;
}

?>
<div class="card-box">
    <div class="row">
        <div class="col-md-4 col-sm-6">
            <div class="author-info mcolorbg4">
                <small><?php echo _cao('site_money_ua');?></small>
                <p>当前余额</p>
                <h3><?php echo $CaoUser->get_balance();?></h3>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="author-info pcolorbg">
                <small><?php echo _cao('site_money_ua');?></small>
                <p>已消费</p>
                <h3><?php echo $CaoUser->get_consumed_balance();?></h3>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="author-info scolorbg">
                <small>RMB</small>
                <p>佣金</p>
                <h3><?php echo $Reflog->get_total_bonus();?></h3>
            </div>
        </div>
        <?php if($_c): ?>
        <div class="col-md-4 col-sm-6">
            <div class="author-info mcolorbg2">
                <small>今日/次</small>
                <p>可下载</p>
                <h3><?php echo $cao_vip_downum['today_count_num'];?></h3>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="author-info pcolorbg2">
                <small>今日/次</small>
                <p>已下载</p>
                <h3><?php echo $cao_vip_downum['today_down_num'];?></h3>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="author-info scolorbg2">
                <small>今日/次</small>
                <p>剩余下载</p>
                <h3><?php echo $cao_vip_downum['over_down_num'];?></h3>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>