<?php

$mode_catpost = _cao('mode_catpost');



foreach ($mode_catpost['catcms'] as $key => $cms) { 

	$args = array(
	    'cat'            => $cms['category'],
	    'ignore_sticky_posts' => true,
	    'post_status'         => 'publish',
	    'posts_per_page'      => $cms['count'],
	    'orderby'      => $cms['orderby'],
	);

	///////////S CACHE ////////////////
	if (CaoCache::is()) {
	    $_the_cache_key = 'ripro_home_catpost_posts_'.$args['cat'];
	    $_the_cache_data = CaoCache::get($_the_cache_key);
	    if(false === $_the_cache_data ){
	        $_the_cache_data = new WP_Query($args); //缓存数据
	        CaoCache::set($_the_cache_key,$_the_cache_data);
	    }
	    $data = $_the_cache_data;
	}else{
	    $data = new WP_Query($args); //原始输出
	}
	///////////S CACHE ////////////////
	$category = get_category( $cms['category'] ); ?>
	<div class="section pb-0">
	  <div class="container">
	  	<h3 class="section-title">
	  		<span><i class="fa fa-th"></i> <a href="<?php echo esc_url( get_category_link( $category->cat_ID ) ); ?>"><?php echo $category->cat_name; ?></a></span>
	  	</h3>
	  	<?php do_action('ripro_echo_ads', 'ad_cms_1'); ?>
		<div class="row cat-posts-wrapper">
		    <?php while ( $data->have_posts() ) : $data->the_post();
		      get_template_part( 'parts/template-parts/content',$cms['latest_layout'] );
		    endwhile; ?>
		</div>
		<?php do_action('ripro_echo_ads', 'ad_cms_2'); ?>
	  </div>
	</div>

	<?php 
	wp_reset_postdata();
}
?>