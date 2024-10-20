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
$message = '';
if ('delete' === $RiPlusTable->current_action()) {
    $message = '<div class="updated notice notice-success is-dismissible"><p>' . sprintf(__('成功删除: %d 条记录', 'cao'), count($_REQUEST['wp_list_event'])) . '</p></div>';
}

?>

<!-- 主页面 -->
<div class="wrap">
    <h2>积分充值记录总览</h2>
    <p>如需清理订单，直接筛选出未付款订单，选择删除，应用即可</p>
    
    <?php echo $message; ?>
    <hr class="wp-header-end">
   
    <div id="post-body-content">
        <div class="meta-box-sortables ui-sortable">
            <form method="get">
                <?php $RiPlusTable->search_box('根据用户ID搜索', 'user_id'); ?>
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
            'cb'      => '<input type="checkbox" />',
            'order_trade_no'    => __( '本地订单号', 'rizhuti-v2' ),
            'user_id'    => __( '用户ID', 'rizhuti-v2' ),
            'order_name'    => __( '商品名称', 'rizhuti-v2' ),
            'order_price'    => __( '订单价格', 'rizhuti-v2' ),
            'site_price'    => __( '充值数量', 'rizhuti-v2' ),
            // 'order_type'    => __( '订单类型', 'rizhuti-v2' ),
            'create_time'    => __( '下单时间', 'rizhuti-v2' ),
            'pay_type'    => __( '支付方式', 'rizhuti-v2' ),
            'pay_time'    => __( '支付时间', 'rizhuti-v2' ),
            'pay_trade_no'    => __( '支付订单号', 'rizhuti-v2' ),
            'status'    => __( '支付状态', 'rizhuti-v2' ),
        ];

        return $columns;
    }

    public function column_default( $item, $column_name )
    {
        switch ( $column_name ) {
            case 'user_id':
                if ($author_obj = get_user_by('ID', $item['user_id'])) {
                    $u_name =$author_obj->user_login;
                }else{
                    $u_name = '游客';
                }
                return get_avatar($item['user_id'], 50).'<strong>'.$u_name.'<strong>';
            case 'order_name':
                return _cao('site_money_ua').'充值';
            case 'order_type':
                if ($item[$column_name]=='charge') {
                    return '充值';
                }elseif ($item[$column_name]=='vip') {
                    return '购买会员';
                }else{
                    return '其他';
                }
                
            case 'order_price':
                return '<span class="badge badge-hollow">￥'.$item[$column_name].'</span>';
            case 'site_price':
                $charge_rate  = (int) _cao('site_change_rate'); //充值比例
                return '<span class="badge badge-secondary">'.sprintf('%0.2f', $item['order_price'] * $charge_rate)._cao('site_money_ua').'</span>';
            case 'create_time':
                return date('Y-m-d H:i:s',$item[$column_name]);
            case 'pay_type':
                $paytypeArr=[
                    1=>['badge'=>'badge-blue','name'=>'支付宝'],
                    2=>['badge'=>'badge-primary','name'=>'微信'],
                    4=>['badge'=>'badge-primary','name'=>'PAYJS'],
                    5=>['badge'=>'badge-primary','name'=>'虎皮椒-微信'],
                    6=>['badge'=>'badge-blue','name'=>'虎皮椒-支付宝'],
                    7=>['badge'=>'badge-blue','name'=>'码支付-支付宝'],
                    8=>['badge'=>'badge-primary','name'=>'码支付-微信'],
                    9=>['badge'=>'badge-blue','name'=>'讯虎-支付宝'],
                    10=>['badge'=>'badge-primary','name'=>'讯虎-微信'],
                     11=>['badge'=>'badge-blue','name'=>'易支付-支付宝'],
                    12=>['badge'=>'badge-primary','name'=>'易支付-微信'],
                ];
                return '<span class="badge '.$paytypeArr[$item[$column_name]]['badge'].'">'.$paytypeArr[$item[$column_name]]['name'].'</span>';
            case 'pay_time':
                if (!empty($item[$column_name])) {
                    return date('Y-m-d H:i:s',$item[$column_name]);
                }else{
                    return 'N/A';
                }
                
            case 'pay_trade_no':
                if (!empty($item[$column_name])) {
                    return $item[$column_name];
                }else{
                    return 'N/A';
                }
            case 'status':
                if ($item[$column_name]==1) {
                    return '<span class="badge badge-primary">已支付</span>';
                }else{
                    return '<span class="badge badge-secondary">未支付</span>';
                }
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
            'pay_time' => array( 'pay_time', true ),
            'order_price' => array( 'order_price', true ),
        );

        return $sortable_columns;
    }

    public function display_tablenav( $which ) 
    {
        
        ?>
        
        <div class="tablenav <?php echo esc_attr( $which ); ?>">

            <div class="alignleft actions">
                <?php $this->bulk_actions(); ?>
            </div>
            <?php
            $this->extra_tablenav( $which );
            $this->pagination( $which );
            
            ?>
            <br class="clear" />
        </div>
        <?php
    }

    public function extra_tablenav( $which ) {
        global $wpdb, $testiURL, $tablename, $tablet;
        if ( $which == "top" ){
            ?>
            <div class="alignleft actions bulkactions">
            <?php
            $filter = [
                ['title'=>'未支付','id'=>'0'],
                ['title'=>'已支付','id'=>'1'],
            ];
            if( $filter ){
                ?>
                <select name="status" class="ewc-filter-status">
                    <option selected="selected" value="">支付状态</option>
                    <?php foreach( $filter as $item ){
                        $selected = '';
                        $_REQUEST['status'] = (!empty($_REQUEST['status'])) ? $_REQUEST['status'] : null ;
                        if( $_REQUEST['status'] == $item['id'] ){
                            $selected = ' selected = "selected"';   
                        }
                        echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['title'].'</option>';
                    }?>
                </select>
                <?php
            }
            $paytypeArr=[
                ['id'=>'1','name'=>'支付宝'],
                ['id'=>'2','name'=>'微信'],
                ['id'=>'4','name'=>'微信-PAYJS'],
                ['id'=>'5','name'=>'微信-虎皮椒'],
                ['id'=>'6','name'=>'支付宝-虎皮椒'],
            ];
            if ($paytypeArr) { ?>
                <select name="pay_type" class="ewc-filter-pay_type">
                    <option selected="selected" value="">支付方式</option>
                    <?php foreach( $paytypeArr as $item ){
                        $selected = '';
                        $_REQUEST['pay_type'] = (!empty($_REQUEST['pay_type'])) ? $_REQUEST['pay_type'] : 1 ;
                        if( $_REQUEST['pay_type'] == $item['id'] ){
                            $selected = ' selected = "selected"';   
                        }
                        echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['name'].'</option>';
                    }?>
                </select>
                <button type="submit" id="post-query-submit" class="button">筛选</button> 
            <?php } ?>  
            </div>
            <?php
        }
        if ( $which == "bottom" ){
        }
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
        global $wpdb, $order_table_name;

        $sql = "SELECT * FROM $order_table_name WHERE order_type='charge'";
        //根据用户查询
        if ( ! empty( $_REQUEST['s'] ) ) {
            $user_id = 0;
            if (is_numeric($_REQUEST['s'])) {
                $user_id = absint($_REQUEST['s']);
            } else {
                $author_obj = get_user_by('login', $_REQUEST['s']);
                if (!empty($author_obj)) {
                    $user_id    = $author_obj->ID;
                }
            }
            $sql .= ' AND user_id=' . esc_sql($user_id);
        }

        //状态查询
        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status']!='' ) {
            $status = absint($_REQUEST['status']);
            $sql .= ' AND status=' . esc_sql($status);
        }

        //支付方式
        if ( isset( $_REQUEST['pay_type'] ) && $_REQUEST['pay_type']!='' ) {
            $pay_type = absint($_REQUEST['pay_type']);
            $sql .= ' AND pay_type=' . esc_sql($pay_type);
        }
        
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'create_time' ;
        if ( ! empty( $orderby ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $orderby );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' DESC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        return $result;
    }

    private function table_data_count() {
        global $wpdb, $order_table_name;

        $sql = "SELECT COUNT(*) FROM $order_table_name WHERE order_type='charge'";
        //根据用户查询
        if ( ! empty( $_REQUEST['s'] ) ) {
            $user_id = 0;
            if (is_numeric($_REQUEST['s'])) {
                $user_id = absint($_REQUEST['s']);
            } else {
                $author_obj = get_user_by('login', $_REQUEST['s']);
                if (!empty($author_obj)) {
                    $user_id    = $author_obj->ID;
                }
            }
            $sql .= ' AND user_id=' . esc_sql($user_id);
        }
        //状态查询
        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status']!='' ) {
            $status = absint($_REQUEST['status']);
            $sql .= ' AND status=' . esc_sql($status);
        }
        //支付方式
        if ( isset( $_REQUEST['pay_type'] ) && $_REQUEST['pay_type']!='' ) {
            $pay_type = absint($_REQUEST['pay_type']);
            $sql .= ' AND pay_type=' . esc_sql($pay_type);
        }

        return $wpdb->get_var( $sql );
    }

    private function delete_table_data( $id ) {
        global $wpdb,$order_table_name;
        $wpdb->delete(
            "$order_table_name",
            [ 'id' => $id ,'status'=>0],
            [ '%d' ]
        );
    }

}

