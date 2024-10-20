<?php
if (is_site_shop_open()): 

$CaoCdk    = new CaoCdk();
$home_codecdk = _cao('home_codecdk');
$home_coupon = $home_codecdk['home_coupon'];
?>
<div class="section codecdk-panel lazyload" data-bg="<?php echo esc_url( $home_codecdk['bg'] ); ?>">
    <div class="container">
        <div class="row">
            <?php foreach ($home_coupon as $key => $item) : ?>
            <div class="col-lg-4 col-sm-6">
                <?php // 实例化优惠码
                    ///////////S CACHE ////////////////
                    if (CaoCache::is()) {
                        $_the_cache_key = 'ripro_home_codecdk_list_'.$item['_code'];
                        $_the_cache_data = CaoCache::get($_the_cache_key);
                        if(false === $_the_cache_data ){
                            $_the_cache_data = $CaoCdk->checkCdk($item['_code']); //缓存数据
                            CaoCache::set($_the_cache_key,$_the_cache_data);
                        }
                        $cdk_money = $_the_cache_data;
                    }else{
                        $cdk_money = $CaoCdk->checkCdk($item['_code']); //原始输出
                    }
                    ///////////S CACHE ////////////////
                
                ?>
                <div class="jq22-flex">
                    <div class="jq22-price-nub">
                        <div class="jq22-digit">
                            <h1><a href="/22171-2">免费</a></h1>
                        </div>
             
                    </div>
                    <div class="jq22-flex-box">
                        <h2><?php echo $item['_desc'];?></h2>
                        <h3><?php echo $item['_code'];?></h3>
                        <a href="/22171-2">  <span class="cop-codecdk" data-clipboard-text=>免费专区点这进入</span></a>
                    </div>
                </div>

            </div>
            <?php endforeach;?>
            
        </div>
    </div>
</div>

<?php endif; ?>