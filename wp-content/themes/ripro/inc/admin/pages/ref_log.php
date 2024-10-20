<?php
// Exit if accessed directly.
defined('ABSPATH') || exit;
global $wpdb;
// Authentication
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
?>

<?php
// 主页面PHP

$perpage = 20; // 每页数量
$paged=isset($_GET['paged']) ?intval($_GET['paged']) :1;  //当前页
$offset = $perpage*($paged-1); //偏移页
//////// 构造SQL START ////////
$sql = "SELECT * FROM {$wpdb->usermeta}";
$where = ' WHERE meta_key="cao_total_bonus"';
$where .= ' AND meta_value>0';

if ( !empty( $_GET['user_id'] ) ) {
	$author_obj = get_user_by('login', $_GET['user_id']);
	if (!empty($author_obj) && $author_obj->ID > 0) {
		$where .= ' AND user_id='.esc_sql($author_obj->ID);
	}
}

$orderlimti = ' ORDER BY meta_value+0 DESC';
$orderlimti .= ' LIMIT '.esc_sql($offset.','.$perpage);
$result = $wpdb->get_results($sql.$where.$orderlimti);
$total   = $wpdb->get_var("SELECT COUNT(user_id) FROM $wpdb->usermeta {$where}");

// var_dump($result);die;
//////// 构造SQL END ////////
?>

<!-- 主页面 -->
<div class="wrap">
	<h1 class="wp-heading-inline">用户佣金明细查询</h1>
    <hr class="wp-header-end">
	
	<form id="order-filter" method="get">
		<!-- 初始化页面input -->
		<input type="hidden" name="page" value="<?php echo $_GET['page']?>">
		<!-- 筛选 -->
		<div class="wp-filter">
		    <div class="filter-items">
		    	<div class="view-switch">
		    		<a class="view-list current"></a>
		    	</div>
		    </div>
		    <div class="search-form">
		        <span class="">只显示有佣金的用户，根据用户的ID搜索例如（admin） ，共<?php echo $total?>个项目 </span>
		        <input class="search" id="media-search-input" name="user_id" placeholder="根据用户搜索,回车确定…" type="search" value=""/>
		    </div>
		    <br class="clear">
		</div>
		<!-- 筛选END -->

		<table class="wp-list-table widefat fixed striped posts">
			<thead>
				<tr>
					<th class="column-primary">用户ID</th>	
					<th>推广人数</th>
					<th>佣金比例</th>
					<th>累计佣金</th>
					<th>可提现</th>
					<th>提现中</th>
                    <th>已提现</th>
				</tr>
			</thead>
			<tbody id="the-list">

		<?php

			if($result) {
				
				foreach($result as $item){
					$CaoUser = new CaoUser($item->user_id);
					$Reflog = new Reflog($item->user_id);
					if ($CaoUser->vip_status()) {
					    $the_ref_float = (_cao('site_vip_ref_float')*100).'%';
					}else{
					    $the_ref_float = (_cao('site_novip_ref_float')*100).'%';
					}

					echo '<tr id="order-info">';
					echo '<td class="has-row-actions column-primary">'.get_user_by('id',$item->user_id)->user_login.'<button type="button" class="toggle-row"><span class="screen-reader-text">显示详情</span></button></td>';

					echo '<td data-colname="推广人数">'.$Reflog->get_ref_num().'</td>';
                    echo '<td data-colname="佣金比例">'.$the_ref_float.'</td>';
                    echo '<td data-colname="累计佣金"><span class="badge">￥'.$Reflog->get_total_bonus().'</span></td>';
                    echo '<td data-colname="可提现"><span class="badge badge-warning">￥'.$Reflog->get_ke_bonus().'</span></td>';
                    echo '<td data-colname="提现中"><span class="badge badge-danger">￥'.$Reflog->get_ing_bonus().'</span></td>';
                    echo '<td data-colname="已提现"><span class="badge">￥'.$Reflog->get_yi_bonus().'</span></td>';

					echo "</tr>";
				}
			}
			else{
				echo '<tr><td colspan="12" align="center"><strong>没有数据</strong></td></tr>';
			}
		?>
		</tbody>
		</table>
	</form>
    <?php echo cao_admin_pagenavi($total,$perpage);?>
    <script>
            jQuery(document).ready(function($){

            });
	</script>
</div>
