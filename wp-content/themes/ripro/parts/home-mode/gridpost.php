
<?php 
  $mode_gridpost = _cao('mode_gridpost');
  $posts_1 = ($mode_gridpost['posts_1']) ? $mode_gridpost['posts_1'] : '1' ;
  $posts_1_id = $posts_1;
  $posts_1 = array(
    'post__in' => array($posts_1),
    'posts_per_page' => 1,
  );
  $posts_4 = array(
    'cat'       => $mode_gridpost['posts_4'],
    'ignore_sticky_posts' => 0,
    'post_status'         => 'publish',
    'posts_per_page'      => 4,
    'orderby'              => $mode_gridpost['orderby'],
  );
?>

<div class="section gridpost-bg bgcolor-fff lazyload" data-bg="<?php echo esc_url( $mode_gridpost['bgimg'] ); ?>">
  <div class="container">
    <div class="module gridpost row">
      
      <div class="col-12 col-md-6 sm-flex padding-10">
        <?php 
        ///////////S CACHE ////////////////
        if (CaoCache::is()) {
            $_the_cache_key = 'ripro_home_gridpost_posts_'.$posts_1_id;
            $_the_cache_data = CaoCache::get($_the_cache_key);
            if(false === $_the_cache_data ){
                $_the_cache_data = new WP_Query( $posts_1 ); //缓存数据
                CaoCache::set($_the_cache_key,$_the_cache_data);
            }
            $posts_1 = $_the_cache_data;
        }else{
            $posts_1 =  new WP_Query( $posts_1 ); //原始输出
        }
        ///////////S CACHE ////////////////
        while ( $posts_1->have_posts() ) : $posts_1->the_post();
        ?>
        <div class="list-item list-homegrid-overlay flex-fill">
            <div class="media media-28x15 sm-flex flex-fill">
                <a<?php echo _target_blank();?> class="media-content lazyload" href="<?php echo esc_url( get_the_permalink() ); ?>" data-bg="<?php echo _get_post_thumbnail_url(); ?>">
                    <span class="overlay"></span>
                </a>
            </div>
                <div class="list-content">
                <div class="list-body">
                     <a<?php echo _target_blank();?> class="list-title" href="<?php echo esc_url( get_the_permalink() ); ?>">
                        <?php echo get_the_title(); ?>
                     </a>
                     <?php get_template_part( 'parts/entry-footer' ); ?>
                </div>
            </div>
        </div>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>

    
      <div class="col-12 col-md-6">
        <div class="list-scroll-2x">
          <div class="row">
            <?php
            ///////////S CACHE ////////////////
            if (CaoCache::is()) {
                $_the_cache_key = 'ripro_home_gridpost_posts_'.$posts_4['cat'];
                $_the_cache_data = CaoCache::get($_the_cache_key);
                if(false === $_the_cache_data ){
                    $_the_cache_data = new WP_Query( $posts_4 ); //缓存数据
                    CaoCache::set($_the_cache_key,$_the_cache_data);
                }
                $posts_4 = $_the_cache_data;
            }else{
                $posts_4 =  new WP_Query( $posts_4 ); //原始输出
            }
            ///////////S CACHE ////////////////
            while ( $posts_4->have_posts() ) : $posts_4->the_post();
            ?>
              <div class="col-6 padding-10">
                <div class="list-item list-homegrid-overlay">
                    <div class="media">
                      <a<?php echo _target_blank();?> class="media-content lazyload" href="<?php echo esc_url( get_the_permalink() ); ?>" data-bg="<?php echo _get_post_thumbnail_url(); ?>">
                            <span class="overlay"></span>
                        </a>
                    </div>
                    <div class="list-content">
                        <div class="list-body">
                            <div class="mt-auto">
                               <a<?php echo _target_blank();?> class="list-title" href="<?php echo esc_url( get_the_permalink() ); ?>">
                                  <?php echo get_the_title(); ?>
                               </a>
                               <?php get_template_part( 'parts/entry-footer' ); ?>
                            </div>
                        </div>
                    </div>
                </div>
              </div>
            <?php endwhile; wp_reset_postdata(); ?>
          </div>
        </div>
      </div>


    </div>
  </div>
</div>
