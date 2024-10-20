<?php
$type = 'tag';
$terms = get_the_tags();
if (!$terms) {
  $terms = get_the_category();
  $type = 'category';
}
if ( $terms && _cao( 'disable_related_posts') == 1 ) : 
  $args = array(
    'orderby' => 'rand',
    'post__not_in' => array( get_the_ID() ),
    'posts_per_page' => _cao('related_posts_num','4'),
  );
  $term_ids = array();
  foreach ( $terms as $term ) {
    $term_ids[] = $term->term_id;
  }
  switch ( $type ) {
    case 'tag' :
      $args['tag__in'] = $term_ids;
      break;
    case 'category' :
      $args['category__in'] = $term_ids;
      break;
  }
  ///////////S CACHE ////////////////
  if (CaoCache::is()) {
      $_the_cache_key = 'ripro_related_posts_'.get_the_ID();
      $_the_cache_data = CaoCache::get($_the_cache_key);
      if(false === $_the_cache_data ){
          $_the_cache_data = new WP_Query( $args ); //缓存数据
          CaoCache::set($_the_cache_key,$_the_cache_data);
      }
      $related_posts = $_the_cache_data;
  }else{
      $related_posts = new WP_Query( $args ); //缓存数据
  }
  ///////////S CACHE ////////////////
  
  if ( $related_posts->have_posts() ) :
    $rs = _cao('related_posts_style','grid');
    // 判断风格
    if ($rs=='grid') { ?>
      <!-- # 标准网格模式... -->
      <div class="related-posts-grid">
        <h4 class="u-border-title">相关推荐</h4>
        <div class="row">
         <?php while ( $related_posts->have_posts() ) : $related_posts->the_post(); ?>
            <div class="col-6 col-sm-3 col-md-3 mt-10 mb-10">
              <article class="post">
                <?php cao_entry_media( array( 'layout' => 'rect_300' ) ); ?>
                <div class="entry-wrapper">
                  <?php cao_entry_header( array( 'tag' => 'h4') ); ?>
                </div>
              </article>
            </div>
          <?php endwhile; ?>
        </div>
      </div>
    <?php }elseif ($rs=='list') { ?>
      <!-- # 纯标题列表模式... -->
    <?php }elseif ($rs=='fullgrid') { ?>
      <!-- # 全宽底部网格模式... -->
      <div class="bottom-area bgcolor-fff">
        <div class="container">
          <div class="related-posts">
            <h3 class="u-border-title">相关推荐</h3>
            <div class="row">
              <?php while ( $related_posts->have_posts() ) : $related_posts->the_post(); ?>
                <div class="col-lg-6">
                  <article class="post">
                    <?php cao_entry_media( array( 'layout' => 'rect_300' ) ); ?>
                    <div class="entry-wrapper">
                      <?php cao_entry_header( array( 'tag' => 'h4' ,'author'=>true) ); ?>
                      <div class="entry-excerpt u-text-format">
                        <?php echo _get_excerpt($limit = 55, $after = '...'); ?>
                      </div>
                      <?php get_template_part( 'parts/entry-footer' ); ?>
                    </div>
                  </article>
                </div>
              <?php endwhile; ?>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>

  <?php
  endif;
  wp_reset_postdata();
endif;