<?php
// Exit if accessed directly.
defined('ABSPATH') || exit;
global $wpdb, $order_table_name,$paylog_table_name,$coupon_table_name,$balance_log_table_name,$ref_log_table_name,$down_log_table_name;
// Authentication
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
function query_day($star,$end){
	global $wpdb,$order_table_name;
	$sql = $wpdb->get_var("SELECT SUM(order_price) FROM $order_table_name WHERE create_time > {$star} AND create_time < {$end}");
	$sql = ($sql) ? sprintf("%.2f",$sql) : 0 ;
	$sql_ok = $wpdb->get_var("SELECT SUM(order_price) FROM $order_table_name WHERE create_time > {$star} AND create_time < {$end} AND status=1");
	$sql_ok = ($sql_ok) ? sprintf("%.2f",$sql_ok) : 0 ;
	return array('sum' => $sql,'sum_ok' => $sql_ok,'sum_no' => round(($sql-$sql_ok),2));
}
// 时间安排
$arr_itme = [
	['name' => '今日','time' => RiPLus_Time::today(),],
	['name' => '本月','time' => RiPLus_Time::month(),],
	['name' => '今年','time' => RiPLus_Time::year(),],
];

?>

<!-- 主页面 -->
<div class="wrap">

	<h1 class="wp-heading-inline">商城统计/总览</h1>
    <hr class="wp-header-end">
    <br/>
	<div class="layui-row layui-col-space15">  

		<div class="layui-col-md9">
			<div class="layui-card">
		        <div class="layui-card-header">收入统计</div>
		        <div class="layui-card-body">
					<div class="layui-row layui-col-space15">
						<?php foreach ($arr_itme as $key => $item) { 
							// 获取今日总订单
							$_time = $item['time'];
							$_count = $wpdb->get_var("SELECT COUNT(id) FROM $order_table_name WHERE create_time > {$_time[0]} AND create_time < {$_time[1]}");
							$_count = ($_count) ? $_count : 0 ;
							$_count_ok = $wpdb->get_var("SELECT COUNT(id) FROM $order_table_name WHERE create_time > {$_time[0]} AND create_time < {$_time[1]} AND status=1");
							$_count_ok = ($_count_ok) ? $_count_ok : 0 ;

							$_sum = $wpdb->get_var("SELECT SUM(order_price) FROM $order_table_name WHERE create_time > {$_time[0]} AND create_time < {$_time[1]}");
							$_sum = ($_sum) ? $_sum : 0 ;
							$_sum_ok = $wpdb->get_var("SELECT SUM(order_price) FROM $order_table_name WHERE create_time > {$_time[0]} AND create_time < {$_time[1]} AND status=1");
							$_sum_ok = ($_sum_ok) ? $_sum_ok : 0 ;
							?>
							<div class="layui-col-sm6 layui-col-md4 layui-bg-gray">
								<div class="layui-card">
						          <div class="layui-card-header">
						            <span class="layui-badge-dot layui-bg-orange"></span> <?php echo $item['name'];?>已付款</span>
						            <span class="layui-badge layui-bg-danger "><?php echo $_count_ok;?> 条</span> 
						            <span class="layui-badge-rim layuiadmin-badge">付款率 <?php echo $retVal = ($_count_ok==0 || $_count==0) ? 0 : sprintf("%.2f",$_count_ok/$_count*100) ;?>%</span>
						          </div>
						          <div class="layui-card-body layuiadmin-card-list">
						            <p class="layuiadmin-big-font">￥<?php echo $_sum_ok;?></p>
						            <p>订单总数 <span class="layui-badge-rim"><?php echo $_count;?>条</span>
						            	
						             	<span class="layuiadmin-span-color"><span class="layui-badge-dot"></span> 订单总额 <span class="layui-badge-rim">￥<?php echo $_sum;?></span></span> </p>
						          </div>
						        </div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>

			<div class="layui-card">
				<div class="layui-card-header">本月销售统计图(近30天)</div>
				<div class="layui-card-body">
					<div class="layui-row">
		              <div class="layui-col-sm12">
		                  <div id="conversionsChart" style="width: auto;height:520px;"></div>
		              </div>
		            </div>
				</div>
			</div>

		</div>

		<div class="layui-col-md3">
			<div class="layui-card">
		        <div class="layui-card-header">便捷导航</div>
		        <div class="layui-card-body">
		          <div class="layui-btn-container">
		          	<?php 
		          	$menu_btn = [
				        ['menu'=>'cao_admin','name'=>esc_html__('商城总览','cao')],
				        ['menu'=>'cao_admin_change_log','name'=>esc_html__(_cao('site_money_ua').'充值记录','cao')],
				        ['menu'=>'cao_admin_pay_log','name'=>esc_html__('资源订单','cao')],
				        ['menu'=>'cao_admin_down_log','name'=>esc_html__('下载记录','cao')],
				        ['menu'=>'cao_admin_cdk_log','name'=>esc_html__('卡密管理','cao')],
				        ['menu'=>'cao_admin_user_log','name'=>esc_html__('会员统计','cao')],
				        ['menu'=>'cao_admin_balance_log','name'=>esc_html__('余额记录','cao')],
				        ['menu'=>'cao_admin_aff_log','name'=>esc_html__('佣金管理','cao')],
				        ['menu'=>'cao_admin_price_log','name'=>esc_html__('付费资源管理','cao')],
				        ['menu'=>'wp_clean_up_page','name'=>esc_html__('数据库优化','cao')],
				    ];
		          	foreach ($menu_btn as $btn) {
		          		echo '<a class="layui-btn layui-btn-sm" href="'.admin_url('/admin.php?page='.$btn['menu']).'">'.$btn['name'].'</a>';
		          	}?>
		            
		          </div>        
		        </div>
		    </div>

		    <div class="layui-card">
		        <div class="layui-card-header">其他数据</div>
		        <div class="layui-card-body">
		          	<div class="layui-carousel layadmin-carousel layadmin-backlog" lay-anim="" lay-indicator="inside" lay-arrow="none" style="width: 100%;">
	                  <div carousel-item="">
	                    <ul class="layui-row layui-col-space10 layui-this">
	                      <li class="layui-col-xs6">
	                        <a lay-href="app/content/comment.html" class="layadmin-backlog-body">
	                          <h3>文章总数</h3>
	                          <p><?php $count_posts = wp_count_posts(); echo $published_posts =$count_posts->publish;?></p>
	                        </a>
	                      </li>
	                      <li class="layui-col-xs6">
	                        <a lay-href="app/content/comment.html" class="layadmin-backlog-body">
	                          <h3>资源文章数</h3>
	                          <?php $sqls = $wpdb->get_var($wpdb->prepare("SELECT COUNT(post_id) FROM $wpdb->postmeta WHERE meta_key=%s AND meta_value=%s", 'cao_status',1));
	                          ?>
	                          <p><?php echo $sqls ? $sqls : '0' ?></p>
	                        </a>
	                      </li>

	                      <li class="layui-col-xs6">
	                        <a lay-href="app/forum/list.html" class="layadmin-backlog-body">
	                          <h3>用户总数</h3>
	                          <p><?php $users = $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->users"); echo $users; ?></p>
	                        </a>
	                      </li>
	                      <li class="layui-col-xs6">
	                        <a lay-href="template/goodslist.html" class="layadmin-backlog-body">
	                          <h3><?php echo _cao('site_vip_name')?>会员总数</h3>
	                          <?php // 查询meta
	    						$user_vip = $wpdb->get_var($wpdb->prepare("SELECT COUNT(user_id) FROM $wpdb->usermeta WHERE meta_key=%s AND meta_value=%s", 'cao_user_type', 'vip'));
	    						?>
	                          <p><?php echo $user_vip ? $user_vip : '0'; ?></p>
	                        </a>
	                      </li>
	                      <li class="layui-col-xs6">
	                        <a lay-href="template/goodslist.html" class="layadmin-backlog-body">
	                          <h3>总余额池 / <?php echo _cao('site_money_ua')?></h3>
	                          <?php // 查询meta
	    						$sqls = $wpdb->get_var($wpdb->prepare("SELECT SUM(meta_value) FROM $wpdb->usermeta WHERE meta_key=%s", 'cao_balance'));
	    						?>
	                          <p><?php echo $sqls ? sprintf('%0.2f', $sqls) : '0' ?></p>
	                        </a>
	                      </li>
	                      <li class="layui-col-xs6">
	                        <a lay-href="template/goodslist.html" class="layadmin-backlog-body">
	                          <h3>累计佣金池 / ￥</h3>
	                          <?php // 查询meta
	    						$sqls = $wpdb->get_var($wpdb->prepare("SELECT SUM(meta_value) FROM $wpdb->usermeta WHERE meta_key=%s", 'cao_total_bonus'));
	    						?>
	                          <p><?php echo $sqls ? sprintf('%0.2f', $sqls) : '0' ?></p>
	                        </a>
	                      </li>
	                    </ul>
	                  </div>
		    		</div>
		    	</div>
		    </div>
			
		    <div class="layui-card">
		        <div class="layui-card-header">最新动态</div>
		        <div class="layui-card-body">
		        	<?php
					//////// 构造SQL START ////////
					$sql = "SELECT * FROM {$balance_log_table_name}";
					$sql .= ' WHERE 1=1';
					$sql .= ' ORDER BY time DESC';
					$sql .= ' LIMIT 5';
					$result = $wpdb->get_results($sql);
					//////// 构造SQL END ////////
					?>
					<dl class="layuiadmin-card-status">
					<?php 
					if($result) {
						foreach($result as $item){
							$userss = get_user_by('id',$item->user_id);
							$user_loginname = ($userss->user_login) ? $userss->user_login : '游客' ;?>
			            	<dd><div><p><?php echo $user_loginname ?> ： <?php echo $item->note?></p><span><?php echo date('Y-m-d H:i:s',$item->time)?></span> </div></dd>
			        <?php }} ?>
            		</dl>
		        </div>
		    </div>

		</div>


	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/echarts@5.0.0/dist/echarts.min.js"></script>
<script type="text/javascript">
<?php $day=[];
// 获取30天时间
for ($i=0; $i < 30; $i++) { 
	$_day = 30-$i;
	$time=mktime(0, 0, 0, date('m'), date('d') - $_day, date('Y'));
	$day[$i] = date('Y-m-d',$time);
}
echo "var time_arr =". json_encode($day).";";
$__day=[];
// 获取30天时间
for ($i=0; $i < 30; $i++) { 
	$_d = 30-$i;
	$time=mktime(0, 0, 0, date('m'), date('d') - $_d, date('Y'));
	$__day[$i] = $time;
}
$__sum_data = [];
$__sum_ok_data = [];
$__sum_no_data = [];
foreach ($__day as $k => $time) {
	$end=$time+24*60*60;
	$query = query_day($time,$end);
	$__sum_data[$k] = $query['sum'];
	$__sum_ok_data[$k] = $query['sum_ok'];
	$__sum_no_data[$k] = $query['sum_no'];
}
echo "var __sum_data =". json_encode($__sum_data).";";
echo "var __sum_ok_data =". json_encode($__sum_ok_data).";";
echo "var __sum_no_data =". json_encode($__sum_no_data).";";
?>

var myChart = echarts.init(document.getElementById('conversionsChart'));
var option = {
    tooltip: {
        trigger: 'axis',
        axisPointer: {            // 坐标轴指示器，坐标轴触发有效
            type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
        }
    },
    legend: {
        data: ['总订单','已付款', '未付款', ]
    },
    grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
    },
    xAxis: [
        {
            type: 'category',
            data: time_arr
        }
    ],
    yAxis: [
        {
            type: 'value'
        }
    ],
    series: [

        {
            name: '总订单',
            type: 'bar',
            data: __sum_data
        },
        {
            name: '已付款',
            type: 'bar',
            data: __sum_ok_data
        },
        {
            name: '未付款',
            type: 'bar',
            data: __sum_no_data
        },
        
    ]
};
// 使用刚指定的配置项和数据显示图表。
myChart.setOption(option);

</script>