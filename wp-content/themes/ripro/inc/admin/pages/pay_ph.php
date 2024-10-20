<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;
global $wpdb, $order_table_name,$paylog_table_name,$coupon_table_name,$balance_log_table_name,$ref_log_table_name,$down_log_table_name;
// Authentication
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
$RiPlusTable = new RiPlus_List_Table();
$RiPlusTable->prepare_items();
?>

<!-- 主页面 -->
<div class="wrap">
    <h2 >站内资源销售数量排行</h2>
    <p>（包括文章订单和开通会员订单）包括会员免费获取的资源和内容，本排行统计方式包括余额支付和在线支付，以站内币为单位统计</p>
    <hr class="wp-header-end">
   
    <div id="post-body-content">
        <div class="meta-box-sortables ui-sortable">
            <form method="get">
                <input type="hidden" name="page" value="<?php echo $_GET['page']?>">
                <?php $RiPlusTable->display(); ?>
            </form>
        </div>
    </div>
    <br class="clear">
</div>

<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class RiPlus_List_Table extends WP_List_Table
{

    public function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular'  => 'wp_list_event',
            'plural'    => 'wp_list_events',
            'ajax'      => false
        ));
    }



    public function no_items() {
      _e( '没有找到相关数据' );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $total_items  = $this->get_pagenum();


        $this->set_pagination_args( array(
            'total_items' => $this->table_data_count(),
            'per_page'    => $per_page
        ) );

        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $this->items = $this->table_data($per_page,$current_page);
        $this->process_bulk_action();
    }

    public function get_columns()
    {
       $columns = [
            'post_id'    => __( '资源ID', 'rizhuti-v2' ),
            'post_name'    => __( '资源名称', 'rizhuti-v2' ),
            'order_price'    => __( '资源单价', 'rizhuti-v2' ),
            'order_sale'    => __( '会员折扣', 'rizhuti-v2' ),
            'sum_pay_num'    => __( '实际销售数量', 'rizhuti-v2' ),
            'sum_order_amount'    => __( '实际销售总额', 'rizhuti-v2' ),
        ];

        return $columns;
    }

    public function column_default( $item, $column_name )
    {
        switch ( $column_name ) {
            case 'post_name':
                $post_data = get_post($item['post_id'], ARRAY_A);
                if (empty($post_data) || $post_data['post_status'] !='publish') {
                    return '<span class="badge badge-danger">资源已删除或在回收站</span>';
                }elseif ($post_data['post_type']=='post') {
                    return '<a target="_blank" href='.get_permalink($item['post_id']).'>'.get_the_title($item['post_id']).'</a>'; 
                }elseif ($post_data['post_name']=='user') {
                    return '<span class="badge badge-warning">开通网站会员</span>'; 
                }else{
                   return '未知文章或页面已被删除';  
                }
            case 'order_sale':
                return get_post_meta($item['post_id'], 'cao_vip_rate', true);
            case 'order_price':
                return '<b>'.get_post_meta($item['post_id'], 'cao_price', true).'</b> '._cao('site_money_ua');
            case 'sum_order_amount':
                return '<b>'.$item[ $column_name ].'</b> '._cao('site_money_ua');
            
           
            default:
              return $item[ $column_name ];
        }
    }

    public function get_hidden_columns()
    {
        return array();
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array( 'id', true ),
            'create_time' => array( 'create_time', true ),
            'order_sale' => array( 'order_sale', true ),
            'order_price' => array( 'order_price', true ),
            'order_amount' => array( 'order_amount', true ),
        );

        return $sortable_columns;
    }

    public function display_tablenav( $which ) 
    {
        
        ?>
        
        <div class="tablenav <?php echo esc_attr( $which ); ?>">

            <?php
            $this->extra_tablenav( $which );
            $this->pagination( $which );
            
            ?>
            <br class="clear" />
        </div>
        <?php
    }

    public function extra_tablenav( $which ) {
        
    }


    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],
            $item['id']
        );
    }

    public function get_bulk_actions()
    {
        $actions = array(
            'delete'    => '删除',
        );
        return $actions;
    }

    public function process_bulk_action() {

        if ('delete' === $this->current_action()) {
            $delete_ids = (!empty($_REQUEST['wp_list_event'])) ? esc_sql( $_REQUEST['wp_list_event'] ) : null ; 

            if ($delete_ids) {
                foreach ($_REQUEST['wp_list_event'] as $event) {
                    $this->delete_table_data($event);
                }
            }
            
        }

    }

    
    private function table_data($per_page = 5, $page_number = 1 )
    {
        global $wpdb, $paylog_table_name;

        $sql = "select post_id,count(post_id) as sum_pay_num,sum(order_amount) as sum_order_amount from $paylog_table_name group by post_id,status having status=1 and sum(order_amount) >=1 order by count(post_id) desc";

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        return $result;
    }

    private function table_data_count() {
        global $wpdb, $paylog_table_name;

        $count = $wpdb->get_var("select count(*) as count from (select post_id from $paylog_table_name group by post_id,status having status=1 and sum(order_amount) >=1) a;
    ");
        return $count;
    }

    private function delete_table_data( $id ) {
        return false;
    }

}

