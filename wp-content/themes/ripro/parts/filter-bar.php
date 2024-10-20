<?php 
if (!_cao('is_filter_bar')) :
$currentterm = get_queried_object();
if (!empty($currentterm)) {
    $currentterm_id  = $currentterm->term_id;
    $parent_id = $currentterm->parent;
}else{
    $currentterm_id  = 0;
    $parent_id  = 0;
}

$top_term_id = (is_category()) ? get_category_root_id($currentterm_id ) : 0 ;
$current_array = array($currentterm_id);

while($parent_id){
    $current_array[] = $parent_id;
    $parent_term = get_term($parent_id,'category');
    $parent_id = $parent_term->parent;
}

?>


<div class="filter--content">
    <form class="mb-0" method="get" action="<?php echo home_url(); ?>">
        <input type="hidden" name="s">
        <div class="form-box search-properties mb-0">
            <!-- 一级分类 -->
            <?php if (_cao('is_filter_item_cat','1')) : ?>
            <div class="filter-item">
                <?php
                $filter_cat_1=_cao('archive_filter_cat_1');
                echo '<ul class="filter-tag"><span><i class="fa fa-folder-open-o"></i> 分类筛选</span>';
                if (!empty($filter_cat_1)) {
                    foreach ($filter_cat_1 as $_cid) {
                        $item = get_term($_cid,'category');
                        if (!empty($item)) {
                            $is_current = (in_array($item->term_id,$current_array)) ? ' class="on"' : '' ;
                            echo '<li><a'.$is_current.' href="'.get_category_link($item->term_id).'">'.$item->name.'</a></li>';
                        }
                    }
                } else {
                    echo '<li>请在后台-主题设置-分类页筛选-一级主分类筛选配置和排序您的主分类筛选</li>';
                }
                echo "</ul>";
                ?>
            </div>
            <?php endif;
            
            // 二级分类
            if (_cao('is_filter_item_cat2','1')) : 
                $cat_orderby = _cao('is_filter_item_cat_orderby','id');
                $child_categories = get_terms('category', array('hide_empty' => 0,'parent' => $top_term_id,'orderby' =>$cat_orderby,'order' => 'DESC'));
                

                if ($top_term_id && !empty($child_categories)) {
                    $child2 = get_category($top_term_id);
                    $is_child3 = 0 ;//三级指针
                   echo '<div class="filter-item"><ul class="filter-tag"><span><i class="fa fa-folder-open-o"></i> '.$child2->name.'</span>';
                    foreach ($child_categories as $item) {
                        $is_current = (in_array($item->term_id,$current_array)) ? ' class="on"' : '' ;
                        if (!empty($is_current)) {
                          $is_child3 = $item->term_id;
                        }
                        echo '<li><a'.$is_current.' href="'.get_category_link($item->term_id).'">'.$item->name.'</a></li>';
                    }
                    echo '</ul></div>';
                    // 三级分类
                    $child_categories = get_terms('category', array('hide_empty' => 0,'parent' => $is_child3,'orderby' =>$cat_orderby,'order' => 'DESC'));
                    if (_cao('is_filter_item_cat3','1') && $is_child3>0  && !empty($child_categories)) {
                    $child3 = get_category($is_child3);
                      echo '<div class="filter-item"><ul class="filter-tag"><span><i class="fa fa-folder-open-o"></i> '.$child3->name.'</span>';
                      foreach ($child_categories as $item) {
                          $is_current = (in_array($item->term_id,$current_array)) ? ' class="on"' : '' ;
                          echo '<li><a'.$is_current.' href="'.get_category_link($item->term_id).'">'.$item->name.'</a></li>';
                      }
                      echo '</ul></div>';
                    }
                }
            ?>
            <?php endif; ?>

            <!-- 相关标签 -->
            <?php if (_cao('is_filter_item_tags','1')){
                $this_cat_arg = array( 'categories' => $currentterm_id);
                ///////////S CACHE ////////////////
                if (CaoCache::is()) {
                  $_the_cache_key = 'ripro_filter_item_tags_'.$currentterm_id;
                  $_the_cache_data = CaoCache::get($_the_cache_key);
                  if(false === $_the_cache_data ){
                      $_the_cache_data = _get_category_tags($this_cat_arg); //缓存数据
                      CaoCache::set($_the_cache_key,$_the_cache_data);
                  }
                  $tags = $_the_cache_data;
                }else{
                  $tags = _get_category_tags($this_cat_arg); //缓存数据
                }
                ///////////S CACHE ////////////////
                
                if(!empty($tags)) {
                    echo '<div class="filter-item">';
                    $content = '<ul class="filter-tag"><span><i class="fa fa-tags"></i> 相关标签</span>';
                      foreach ($tags as $tag) {
                        $content .= '<li><a href="'.get_tag_link($tag->term_id).'">'.$tag->name.'</a></li>';
                      }
                    $content .= "</ul>";
                    echo $content;
                    echo '</div>';
                }
            }?>
            <!-- 自定义筛选 -->
            <?php if (_cao('is_custom_post_meta_opt', '0') && _cao('custom_post_meta_opt', '0')) {
                $custom_post_meta_opt = _cao('custom_post_meta_opt', '0');
                foreach ($custom_post_meta_opt as $filter) {
                    $opt_meta_category = (array_key_exists('meta_category',$filter)) ? $filter['meta_category'] : false ;
                    if (!$opt_meta_category || in_array($currentterm_id,$opt_meta_category) ) {
                        $_meta_key = $filter['meta_ua'];
                        echo '<div class="filter-item">';
                            $is_on = !empty($_GET[$_meta_key]) ? $_GET[$_meta_key] : '';
                            $content = '<ul class="filter-tag"><span>'.$filter['meta_name'].'</span>';
                            $meta_opt_arr = array('all' => '全部');
                            $_oncssall = ($is_on == 'all') ? 'on' : '' ;
                            $content .= '<li><a href="'.add_query_arg($_meta_key,'all').'" class="'.$_oncssall.'">全部</a></li>';
                            foreach ($filter['meta_opt'] as $opt) {
                                $_oncss = ($is_on == $opt['opt_ua']) ? 'on' : '' ;
                                $content .= '<li><a href="'.add_query_arg($_meta_key,$opt['opt_ua']).'" class="'.$_oncss.'">'.$opt['opt_name'].'</a></li>';
                            }
                            $content .= "</ul>";
                            echo $content;
                        echo '</div>';
                    }
                   
                }
            }?>
            <?php if ( (_cao('is_filter_item_price', '0') || _cao('is_filter_item_order', '0')) && is_site_shop_open() ) : ?>
            <div class="filter-tab">
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <?php if (_cao('is_filter_item_price','1')) : 
                            $is_on = !empty($_GET['cao_type']) ? $_GET['cao_type'] : '';
                            $cao_vip_name = _cao('site_vip_name');
                            $content = '<ul class="filter-tag"><span><i class="fa fa-filter"></i> 价格</span>';
                            $caotype_arr = array('0' => '全部','1' => '免费','2' => '付费' ,'3' => $cao_vip_name.'免费','4' => $cao_vip_name.'优惠');
                            foreach ($caotype_arr as $key => $item) {
                                $_oncss = ($is_on == $key) ? 'on' : '' ;
                                $content .= '<li><a href="'.add_query_arg("cao_type",$key).'" class="tab '.$_oncss.'"><i></i><em>'.$item.'</em></a></li>';
                            }
                            $content .= "</ul>";
                            echo $content;
                        endif; ?>
                    </div>
                    <div class="col-12 col-sm-6">
                        <!-- 排序 -->
                        <?php if (_cao('is_filter_item_order','1')) : 
                                $is_on = !empty($_GET['order']) ? $_GET['order'] : 'date';
                                $content = '<ul class="filter-tag" style="width: 100%;"><div class="right">';
                                $order_arr = array('date' => '发布日期','modified' => '修改时间','comment_count' => '评论数量','rand' => '随机','hot' => '热度');
                                foreach ($order_arr as $key => $item) {
                                    $_oncss = ($is_on == $key) ? 'on' : '' ;
                                    $content .= '<li class="rightss"><i class="fa fa-caret-down"></i> <a href="'.add_query_arg("order",$key).'" class="'.$_oncss.'">'.$item.'</a></li>';
                                }
                                $content .= "</div></ul>";
                                echo $content;
                        endif; ?>
                        
                    </div>
                </div>
            </div>
            <?php endif;?>

            <!-- .row end -->
        </div>
        <!-- .form-box end -->
    </form>
</div>
<?php endif;?>