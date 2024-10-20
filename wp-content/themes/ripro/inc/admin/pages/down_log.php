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

<div class="wrap">
	<h2>下载记录统计</h2>
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
// WP_List_Table is not loaded automatically so we need to load it in our application
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
			'user_id'    => __( '用户ID', 'rizhuti-v2' ),
			'user_type'    => __( '用户级别', 'rizhuti-v2' ),
			'down_id'    => __( '下载名称', 'rizhuti-v2' ),
			'ip'    => __( '下载IP', 'rizhuti-v2' ),
            // 'note'    => __( '备注', 'rizhuti-v2' ),
            'create_time'    => __( '下载时间', 'rizhuti-v2' ),
            'today_count_num'    => __( '今日可下载', 'rizhuti-v2' ),
            'today_down_num'    => __( '已下载次数', 'rizhuti-v2' ),
            'over_down_num'    => __( '剩余次数', 'rizhuti-v2' ),
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
            case 'user_type':
            	$CaoUser = new CaoUser($item['user_id']);
            	return $CaoUser->vip_name();
            case 'down_id':
                return '<a target="_blank" href='.get_permalink($item['down_id']).'>'.get_the_title($item['down_id']).'</a>'; 
            case 'today_count_num':
            	$CaoUser = new CaoUser($item['user_id']);
            	$cao_vip_downum = $CaoUser->cao_vip_downum($item['user_id'],$CaoUser->vip_status());
            	return '<span class="badge badge-blue">'.$cao_vip_downum['today_count_num'].'次</span>';
            case 'today_down_num':
            	$CaoUser = new CaoUser($item['user_id']);
            	$cao_vip_downum = $CaoUser->cao_vip_downum($item['user_id'],$CaoUser->vip_status());
            	return '<span class="badge">'.$cao_vip_downum['today_down_num'].'次</span>';
            case 'over_down_num':
            	$CaoUser = new CaoUser($item['user_id']);
            	$cao_vip_downum = $CaoUser->cao_vip_downum($item['user_id'],$CaoUser->vip_status());
            	return '<span class="badge badge-primary">'.$cao_vip_downum['over_down_num'].'次</span>';
			
            case 'create_time':
            	return date('Y-m-d H:i:s',$item[$column_name]);

            
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
			'down_time' => array( 'down_time', false ),
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
        global $wpdb,$down_log_table_name;

		$sql = "SELECT * FROM $down_log_table_name WHERE 1=1";
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
		//排序
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
		global $wpdb,$down_log_table_name;

        $sql = "SELECT COUNT(*) FROM $down_log_table_name WHERE 1=1";
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
		return $wpdb->get_var( $sql );
	}

	private function delete_table_data( $id ) {
		global $wpdb,$down_log_table_name;
		$wpdb->delete(
			"$down_log_table_name",
			[ 'id' => $id ],
			[ '%d' ]
		);
	}

}