<?php
/*
Plugin Name: User Blocker
Description: Block users except admin.
Author: Solwin Infotech
Version: 1.0
Text Domain: user-blocker
Author URI: http://www.solwininfotech.com/
Plugin URI: http://www.solwininfotech.com/
*/
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if (!function_exists ('add_action')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}
add_action('admin_menu', 'block_user_plugin_setup');

//Add style/script
add_action('init', 'enqueueStyleScript');
function enqueueStyleScript() {
  wp_enqueue_script('jquery');
  wp_register_script('timepicker-js', plugins_url('script/jquery.timepicker.js', __FILE__));
  wp_enqueue_script('timepicker-js');
  wp_register_script('jquerydate-js', plugins_url('script/bootstrap-datepicker.js', __FILE__));
  wp_enqueue_script('jquerydate-js');
  wp_register_script('datepair-js', plugins_url('script/datepair.js', __FILE__));
  wp_enqueue_script('datepair-js');
  wp_register_script('jquery-timepicker-js', plugins_url('script/jquery.timepicker.js', __FILE__));
  wp_enqueue_script('jquery-timepicker-js');
  wp_register_script('admin_script', plugins_url('script/admin_script.js', __FILE__));
  wp_enqueue_script('admin_script');
  wp_register_style('timepicker-css', plugins_url('css/jquery.timepicker.css', __FILE__));
  wp_enqueue_style('timepicker-css');
  wp_register_style('admin_style', plugins_url('css/admin_style.css', __FILE__));
  wp_enqueue_style('admin_style');
}
function block_user_plugin_setup() {
    add_menu_page('User Blocker', esc_html__('User Blocker', 'user-blocker'), 'manage_options', 'block_user', 'block_user_page', 'dashicons-admin-users');
    $block_date_page = add_submenu_page('','Block User Date Wise', esc_html__('Date Wise Block User', 'user-blocker'), 'manage_options', 'block_user_date', 'block_user_date_page', 'dashicons-admin-users');
    $block_permanent = add_submenu_page('','Block User Permanent', esc_html__('Permanently Block User', 'user-blocker'), 'manage_options', 'block_user_permenant', 'block_user_permenant_page', 'dashicons-admin-users');
    add_submenu_page('block_user', 'Blocked User list', esc_html__('Blocked User list', 'user-blocker'), 'manage_options', 'blocked_user_list', 'block_user_list_page', 'dashicons-admin-users');
    $list_user_date = add_submenu_page('', 'Date Wise Blocked User list', esc_html__('Date Wise Blocked User list', 'user-blocker'), 'manage_options', 'datewise_blocked_user_list', 'datewise_block_user_list_page', 'dashicons-admin-users');
    $list_user_permanent = add_submenu_page('', 'Permanent Blocked User list', esc_html__('Permanent Blocked User list', 'user-blocker'), 'manage_options', 'permanent_blocked_user_list', 'permanent_block_user_list_page', 'dashicons-admin-users');
    $list_user_all = add_submenu_page('', 'All Type Blocked User list', esc_html__('All Type Blocked User list', 'user-blocker'), 'manage_options', 'all_type_blocked_user_list', 'all_type_block_user_list_page', 'dashicons-admin-users');
    // Enqueue script in submenu page to fix the current menu indicator
    add_action( "admin_footer-$block_date_page", function()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                jQuery('#toplevel_page_block_user, #toplevel_page_block_user > a')
                    .removeClass('wp-not-current-submenu')
                    .addClass('wp-has-current-submenu');
                jQuery('#toplevel_page_block_user .wp-submenu-wrap li.wp-first-item')
                    .addClass('current');
            });
        </script>
        <?php
    });
    add_action( "admin_footer-$block_permanent", function()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                jQuery('#toplevel_page_block_user, #toplevel_page_block_user > a')
                    .removeClass('wp-not-current-submenu')
                    .addClass('wp-has-current-submenu');
                jQuery('#toplevel_page_block_user .wp-submenu-wrap li')
                    .addClass('current');
                jQuery('#toplevel_page_block_user .wp-submenu-wrap li:first-child')
                    .removeClass('current');
                jQuery('#toplevel_page_block_user .wp-submenu-wrap li:last-child')
                    .removeClass('current');
            });
        </script>
        <?php
    });
    add_action( "admin_footer-$list_user_date", function()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                jQuery('#toplevel_page_block_user, #toplevel_page_block_user > a')
                    .removeClass('wp-not-current-submenu')
                    .addClass('wp-has-current-submenu');
                jQuery('#toplevel_page_block_user .wp-submenu-wrap li:last-child')
                    .addClass('current');
            });
        </script>
        <?php
    });
    add_action( "admin_footer-$list_user_permanent", function()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                jQuery('#toplevel_page_block_user, #toplevel_page_block_user > a')
                    .removeClass('wp-not-current-submenu')
                    .addClass('wp-has-current-submenu');
                jQuery('#toplevel_page_block_user .wp-submenu-wrap li:last-child')
                    .addClass('current');
            });
        </script>
        <?php
    });
    add_action( "admin_footer-$list_user_all", function()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                jQuery('#toplevel_page_block_user, #toplevel_page_block_user > a')
                    .removeClass('wp-not-current-submenu')
                    .addClass('wp-has-current-submenu');
                jQuery('#toplevel_page_block_user .wp-submenu-wrap li:last-child')
                    .addClass('current');
            });
        </script>
        <?php
    });
}


function block_user_list_page() {
    global $wpdb;
    $txtUsername = '';
    $role = '';
    $srole = '';
    $msg_class = '';
    $msg = '';
    $total_pages = '';
    $next_page = '';
    $prev_page = '';
    $search_arg = '';
    $records_per_page = 10;
    $paged = 1;
    $orderby = 'user_login';
    $order = 'ASC';
    if( isset($_GET['msg']) && $_GET['msg'] != '' ) {
        $msg = $_GET['msg'];
    }
    if( isset($_GET['msg_class']) && $_GET['msg_class'] != '' ) {
        $msg_class = $_GET['msg_class'];
    }
    
    if(isset($_GET['paged']))
        $paged = $_GET['paged'];
    if(!is_numeric($paged))
        $paged = 1;
    if( isset( $_REQUEST['filter_action'] ) ) {
        if( $_REQUEST['filter_action'] == 'Search' ) {
            $paged = 1;
        }
    }
    if( isset($_GET['orderby']) && $_GET['orderby'] != '' )
        $orderby = $_GET['orderby'];
    if( isset($_GET['order']) && $_GET['order'] != '' )
        $order = $_GET['order'];
    
    $offset = ($paged-1)*$records_per_page;
    
    //Only for roles
    global $wp_roles;
    $get_roles = $wp_roles->roles;
    //Reset users
    
    if(isset($_GET['reset']) && $_GET['reset'] == '1')
    {
        if(isset($_GET['username']) && $_GET['username'] != '') {
            $r_username = $_GET['username'];
            $user_data = new WP_User( $r_username );
            if( get_userdata($r_username) != false ) {
                delete_user_meta($r_username, 'block_day');
                delete_user_meta($r_username, 'block_msg_day');
                $msg_class = 'updated';
                $msg = $user_data->user_login . '\'s blocking time is successfully reset.';
            }
            else {
                $msg_class = 'error';
                $msg = 'Invalid user for reset blocking time.';
            }
        }
        if(isset($_GET['role']) && $_GET['role'] != '')
        {
            $reset_roles = get_users(array('role'=>$_GET['role']));
            if(!empty($reset_roles))
            {
                foreach($reset_roles as $single_reset_role)
                {
                    $own_value = get_user_meta($single_reset_role->ID,'block_day',true);
                    $role_value = get_option($_GET['role'].'_block_day');
                    $own_msg = get_user_meta($single_reset_role->ID,'block_msg_day',true);
                    $role_msg = get_option($_GET['role'].'_block_msg_day');
                    if($own_value == $role_value && $own_msg == $role_msg)
                    {
                        delete_user_meta($single_reset_role->ID,'block_day');
                        delete_user_meta($single_reset_role->ID, 'block_msg_day');
                    }
                }
            }
            delete_option($_GET['role'].'_block_day');
            delete_option($_GET['role'].'_block_msg_day');
            $msg_class = 'updated';
            $msg = $_GET['role'] . '\'s blocking time is successfully reset.';
        }
    }
    if(isset($_GET['txtUsername']) && trim($_GET['txtUsername'])!='') {
        $txtUsername = trim($_GET['txtUsername']);
        $filter_ary['search'] = '*'.esc_attr($txtUsername).'*';
        $filter_ary['search_columns'] = array(
            'user_login',
            'user_nicename',
            'user_email',
            'display_name'
        );
    }
    if( $txtUsername == '' ) {
        if(isset($_GET['role']) && $_GET['role'] != '' && !isset($_GET['reset'])) {
            $filter_ary['role'] = $_GET['role'];
            $srole = $_GET['role'];
        }
    }
    //end
    
    if( (isset($_GET['display']) && $_GET['display'] == 'roles') || (isset($_GET['role']) && $_GET['role'] != '' && isset($_GET['reset']) && $_GET['reset'] == '1') || (isset($_GET['role_edited']) && $_GET['role_edited'] != '' && isset($_GET['msg']) && $_GET['msg'] != '')) {
        $display = "roles";
    }
    else {
        $display = "users";
    }
    add_filter( 'pre_user_query', 'sort_by_member_number' );
    $meta_query_array[] = array('relation' => 'AND');
    $meta_query_array[] = array('key'=>'block_day');
    $meta_query_array[] = array(
        array(
        'relation' => 'OR',
        array(
        'key' => 'is_active',
        'compare' => 'NOT EXISTS'
        ),
        array(
        'key' => 'is_active',
        'value' => 'n',
        'compare' => '!='
            )
        )
    );
    $filter_ary['orderby'] = $orderby;
    $filter_ary['order'] = $order;
    $filter_ary['meta_query'] = $meta_query_array;
    //Query for counting results
    $get_users_u1 = new WP_User_Query($filter_ary);    
    $total_items = $get_users_u1->total_users;
    $total_pages = ceil($total_items/$records_per_page);
    $next_page = (int)$paged+1;
    if($next_page > $total_pages)
            $next_page = $total_pages;
    $filter_ary['number'] = $records_per_page;
    $filter_ary['offset'] = $offset;

    $prev_page = (int)$paged-1;
    if($prev_page < 1)
            $prev_page = 1;
    
    $sr_no = 1;
    if( isset( $paged ) && $paged > 1 ) {
        $sr_no = ( $records_per_page * ( $paged -1 ) + 1);
    }
    //Main query
    $get_users_u = new WP_User_Query($filter_ary);
    remove_filter( 'pre_user_query', 'sort_by_member_number' );
    $get_users = $get_users_u->get_results();
    
    ?>
    <div class="wrap" id="blocked-list">
        <h2 class="ublocker-page-title"><?php _e( 'Blocked User list', 'user-blocker') ?></h2> 
        <?php
            //Display success/error messages
            if( $msg != '' ) { ?>
                <div class="ublocker-notice <?php echo $msg_class; ?>">
                    <p><?php echo $msg; ?></p>
                </div>
        <?php } ?>
        <div class="tab_parent_parent"><div class="tab_parent"><ul><li><a href="?page=blocked_user_list" class="current">Blocked User List By Time</a></li><li><a href="?page=datewise_blocked_user_list">Blocked User List By Date</a></li><li><a href="?page=permanent_blocked_user_list">Blocked User List Permanently</a></li><li><a href="?page=all_type_blocked_user_list">Blocked User List</a></li></ul></div></div>
        <div class="cover_form">
        <div class="search_box">
            <div class="tablenav top">                
                <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                    <div class="actions">
                        <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                        <select name="display" id="display_status">
                            <option value="users" <?php echo selected($display,'users') ?> >Users</option>
                            <option value="roles" <?php echo selected($display,'roles') ?>>Roles</option>
                        </select>
                        <?php //Pagination -top ?>
                        <div class="tablenav-pages" <?php if((int)$total_pages <= 1) { echo 'style="display: none;"'; } ?>>
                            <span class="displaying-num"><?php echo $total_items; ?> items</span>
                            <span class="pagination-links">
                                <a class="first-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=blocked_user_list&paged=1&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the first page">&laquo;</a>
                                <a class="prev-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=blocked_user_list&paged='.$prev_page.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the previous page">&lsaquo;</a>
                                <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page">
                                        of
                                        <span class="total-pages"><?php echo $total_pages; ?></span>
                                </span>
                                <a class="next-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=blocked_user_list&paged='.$next_page.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the next page">&rsaquo;</a>
                                <a class="last-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=blocked_user_list&paged='.$total_pages.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the last page">&raquo;</a>
                            </span>
                            <input style="display: none;" id="sbtPages" class="button" type="submit" value="sbtPages" name="filter_action">
                            </div><!-- .tablenav-pages -->
                    </div>
                    <div class="actions">
                        <div class="filter_div" <?php if($display == 'roles') echo 'style="display: none"'; ?>>
                            <label><strong><?php _e( 'Select Role: ', 'user-blocker') ?></strong></label>
                            <select id="srole" name="role" onchange="searchUser();">
                                <option value="">All Roles</option>
                                <?php if($get_roles) 
                                {
                                    foreach($get_roles as $key=>$value) {
                                        if( $key == 'administrator' )
                                            continue;
                                        ?>
                                        <option <?php echo selected($key, $srole); ?> value="<?php echo $key; ?>"><?php echo ucfirst($value['name']); ?></option>
                                        <?php
                                     }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="actions">
                        <div class="filter_div" <?php if($display == 'roles') echo 'style="display: none"'; ?>>
                            <input type="hidden" value="blocked_user_list" name="page" />
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="username or email or first name" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="Search" name="filter_action">
                            <a class="button" href="<?php echo '?page=blocked_user_list'; ?>" style="margin-left: 10px;">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <table class="widefat post fixed role_records" <?php if($display == 'roles') echo 'style="display: table"'; ?>>
            <thead>
                <tr>
                    <th class="th-role">Role</th>
                    <th class="th-time">Sunday</th>
                    <th class="th-time">Monday</th>
                    <th class="th-time">Tuesday</th>
                    <th class="th-time">Wednesday</th>
                    <th class="th-time">Thursday</th>
                    <th class="th-time">Friday</th>
                    <th class="th-time">Saturday</th>
                    <th style="text-align:center">Message</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="th-role">Role</th>
                    <th class="th-time">Sunday</th>
                    <th class="th-time">Monday</th>
                    <th class="th-time">Tuesday</th>
                    <th class="th-time">Wednesday</th>
                    <th class="th-time">Thursday</th>
                    <th class="th-time">Friday</th>
                    <th class="th-time">Saturday</th>
                    <th style="text-align:center">Message</th>
                </tr>
            </tfoot>
            <tbody>
                <?php
                $no_data = 1;
                if($get_roles) {
                    $k = 1;
                   foreach($get_roles as $key=>$value) {
                        $block_day = get_option($key.'_block_day');
                        $block_permenant = get_option($key.'_block_permenant');
                        if( $k%2 == 0 )
                            $alt_class = 'alt';
                        else
                            $alt_class = '';
                        if( ($key == 'administrator') || ( $block_day == '' ) || ($block_permenant != ''))                            
                            continue;
                        $no_data = 0;
                       ?>
                        <tr class="<?php echo $alt_class; ?>">
                            <td class="user-role"><?php echo $value['name']; ?>
                                <div class="row-actions">
                                    <span class="trash"><a title="Reset this item" href="?page=blocked_user_list&reset=1&role=<?php echo $key; ?>">Reset</a></span>
                                </div>
                            </td>
                            <td>
                                <?php
                                $block_day = get_option($key . '_block_day');
                                if(isset($block_day) && !empty($block_day) && $block_day!='') {
                                    if (array_key_exists('sunday',$block_day)) {
                                        $from_time = $block_day['sunday']['from'];
                                        $to_time = $block_day['sunday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($block_day)) {
                                    if (array_key_exists('monday',$block_day)) {
                                        $from_time = $block_day['monday']['from'];
                                        $to_time = $block_day['monday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($block_day)) {
                                    if (array_key_exists('tuesday',$block_day)) {
                                        $from_time = $block_day['tuesday']['from'];
                                        $to_time = $block_day['tuesday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($block_day)) {
                                    if (array_key_exists('wednesday',$block_day)) {
                                        $from_time = $block_day['wednesday']['from'];
                                        $to_time = $block_day['wednesday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($block_day)) {
                                    if (array_key_exists('thursday',$block_day)) {
                                        $from_time = $block_day['thursday']['from'];
                                        $to_time = $block_day['thursday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($block_day)) {
                                    if (array_key_exists('friday',$block_day)) {
                                        $from_time = $block_day['friday']['from'];
                                        $to_time = $block_day['friday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($block_day)) {
                                    if (array_key_exists('saturday',$block_day)) {
                                        $from_time = $block_day['saturday']['from'];
                                        $to_time = $block_day['saturday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td style="text-align:center">
                                <?php
                                    $block_msg_day = get_option($key.'_block_msg_day');
                                    echo disp_msg($block_msg_day);
                                ?>
                            </td>
                        </tr>
                <?php
                        $k++;
                    }
                    if($no_data == 1)
                    {
                         ?>
                        <tr><td colspan="9" style="text-align:center"><?php echo 'No records found.'; ?></td></tr>
                      <?php
                    }
                 }
                 else
                 {
                     ?>
                        <tr><td colspan="9" style="text-align:center"><?php echo 'No records found.'; ?></td></tr>
                      <?php
                 }
                ?>
            </tbody>
        </table>
        <table class="widefat post fixed users_records" <?php if($display == 'roles') echo 'style="display:none"'; ?>>
            <thead>
                <tr>
                    <th class="sr-no">S.N.</th>
                    <?php
                    $linkOrder = 'ASC';
                    if( isset($order) ) {
                        if( $order == 'ASC' ) {
                            $linkOrder = 'DESC';
                        }
                        else if( $order == 'DESC' ) {
                            $linkOrder = 'ASC';
                        }
                    }
                    ?>
                    <th class="th-username sortable <?php echo strtolower($order); ?>">
                        <a href="?page=blocked_user_list&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                            <span>Username</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-role">Role</th>
                    <th class="th-time">Sunday</th>
                    <th class="th-time">Monday</th>
                    <th class="th-time">Tuesday</th>
                    <th class="th-time">Wednesday</th>
                    <th class="th-time">Thursday</th>
                    <th class="th-time">Friday</th>
                    <th class="th-time">Saturday</th>
                    <th style="text-align:center">Message</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="sr-no">S.N.</th>
                    <?php
                    $linkOrder = 'ASC';
                    if( isset($order) ) {
                        if( $order == 'ASC' ) {
                            $linkOrder = 'DESC';
                        }
                        else if( $order == 'DESC' ) {
                            $linkOrder = 'ASC';
                        }
                    }
                    ?>
                    <th class="th-username sortable <?php echo strtolower($order); ?>">
                        <a href="?page=blocked_user_list&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                            <span>Username</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-role">Role</th>
                    <th class="th-time">Sunday</th>
                    <th class="th-time">Monday</th>
                    <th class="th-time">Tuesday</th>
                    <th class="th-time">Wednesday</th>
                    <th class="th-time">Thursday</th>
                    <th class="th-time">Friday</th>
                    <th class="th-time">Saturday</th>
                    <th style="text-align:center">Message</th>
                </tr>
            </tfoot>
            <tbody>
                <?php
                if($get_users) {
                       foreach($get_users as $user) {
                            if( $sr_no%2 == 0 )
                                $alt_class = 'alt';
                            else
                                $alt_class = '';
                       ?>
                        <tr class="<?php echo $alt_class; ?>">
                            <td align="center"><?php echo $sr_no; ?></td>
                            <td><?php echo $user->user_login; ?>
                                <div class="row-actions">
                                    <span class="trash"><a title="Reset this item" href="?page=blocked_user_list&reset=1&paged=<?php echo $paged; ?>&username=<?php echo $user->ID; ?>&role=<?php echo $srole; ?>&txtUsername=<?php echo $txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>">Reset</a></span>
                                </div>
                            </td>
                            <td class="user-role"><?php echo ucfirst( str_replace('_', ' ', $user->roles[0]) ); ?></td>
                            <td>
                                <?php
                                $block_day = get_user_meta($user->ID,'block_day',true);
                                if( $block_day == '' || $block_day == '0' ) {
                                    $block_day = get_option($user->roles[0] . '_block_day');
                                }
                                if(!empty($block_day)) {
                                    if (array_key_exists('sunday',$block_day)) {
                                        $from_time = $block_day['sunday']['from'];
                                        $to_time = $block_day['sunday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($block_day)) {
                                    if (array_key_exists('monday',$block_day)) {
                                        $from_time = $block_day['monday']['from'];
                                        $to_time = $block_day['monday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($block_day)) {
                                    if (array_key_exists('tuesday',$block_day)) {
                                        $from_time = $block_day['tuesday']['from'];
                                        $to_time = $block_day['tuesday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($block_day)) {
                                    if (array_key_exists('wednesday',$block_day)) {
                                        $from_time = $block_day['wednesday']['from'];
                                        $to_time = $block_day['wednesday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($block_day)) {
                                    if (array_key_exists('thursday',$block_day)) {
                                        $from_time = $block_day['thursday']['from'];
                                        $to_time = $block_day['thursday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($block_day)) {
                                    if (array_key_exists('friday',$block_day)) {
                                        $from_time = $block_day['friday']['from'];
                                        $to_time = $block_day['friday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($block_day)) {
                                    if (array_key_exists('saturday',$block_day)) {
                                        $from_time = $block_day['saturday']['from'];
                                        $to_time = $block_day['saturday']['to'];
                                        if( $from_time == '' ) {
                                            echo 'not set';
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo ' to '.timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td style="text-align:center">
                                <?php
                                    $block_msg_day = get_user_meta($user->ID, 'block_msg_day', true);
                                    echo disp_msg($block_msg_day);
                                ?>
                            </td>
                        </tr>
                <?php
                        $sr_no++;
                    }
                 }
                 else {
                     echo '<tr><td colspan="11" style="text-align:center">No records found.</td></tr>';
                 }
                ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php
}

function datewise_block_user_list_page() {
    global $wpdb;
    $txtUsername = '';
    $role = '';
    $srole = '';
    $msg_class = '';
    $msg = '';
    $total_pages = '';
    $next_page = '';
    $prev_page = '';
    $search_arg = '';
    $records_per_page = 10;
    $paged = 1;
    $orderby = 'user_login';
    $order = 'ASC';
    if( isset($_GET['msg']) && $_GET['msg'] != '' ) {
        $msg = $_GET['msg'];
    }
    if( isset($_GET['msg_class']) && $_GET['msg_class'] != '' ) {
        $msg_class = $_GET['msg_class'];
    }
    
    if(isset($_GET['paged']))
        $paged = $_GET['paged'];
    if(!is_numeric($paged))
        $paged = 1;
    if( isset( $_REQUEST['filter_action'] ) ) {
        if( $_REQUEST['filter_action'] == 'Search' ) {
            $paged = 1;
        }
    }
    if( isset($_GET['orderby']) && $_GET['orderby'] != '' )
        $orderby = $_GET['orderby'];
    if( isset($_GET['order']) && $_GET['order'] != '' )
        $order = $_GET['order'];
    
    $offset = ($paged-1)*$records_per_page;
    
    //Only for roles
    global $wp_roles;
    $get_roles = $wp_roles->roles;
    //Reset users
    
    if(isset($_GET['reset']) && $_GET['reset'] == '1')
    {
        if(isset($_GET['username']) && $_GET['username'] != '') {
            $r_username = $_GET['username'];
            $user_data = new WP_User( $r_username );
            if( get_userdata($r_username) != false ) {
                delete_user_meta($r_username, 'block_date');
                delete_user_meta($r_username, 'block_msg_date');
                $msg_class = 'updated';
                $msg = $user_data->user_login . '\'s blocking time is successfully reset.';
            }
            else {
                $msg_class = 'error';
                $msg = 'Invalid user for reset blocking time.';
            }
        }
        if(isset($_GET['role']) && $_GET['role'] != '')
        {
            $reset_roles = get_users(array('role'=>$_GET['role']));
            if(!empty($reset_roles))
            {
                foreach($reset_roles as $single_reset_role)
                {
                    $own_value = get_user_meta($single_reset_role->ID,'block_date',true);
                    $role_value = get_option($_GET['role'].'_block_date');
                    if($own_value == $role_value)
                    {
                        delete_user_meta($single_reset_role->ID,'block_date');
                        delete_user_meta($single_reset_role->ID,'block_msg_date');
                    }
                }
            }
            delete_option($_GET['role'].'_block_date');
            delete_option($_GET['role'].'_block_msg_date');
        }
    }
    if(isset($_GET['txtUsername']) && trim($_GET['txtUsername'])!='') {
        $txtUsername = trim($_GET['txtUsername']);
        $filter_ary['search'] = '*'.esc_attr($txtUsername).'*';
        $filter_ary['search_columns'] = array(
            'user_login',
            'user_nicename',
            'user_email',
            'display_name'
        );
    }
    if( $txtUsername == '' ) {
        if(isset($_GET['role']) && $_GET['role'] != '' && !isset($_GET['reset'])) {
            $filter_ary['role'] = $_GET['role'];
            $srole = $_GET['role'];
        }
    }
    //end
    
    if( (isset($_GET['display']) && $_GET['display'] == 'roles') || (isset($_GET['role']) && $_GET['role'] != '' && isset($_GET['reset']) && $_GET['reset'] == '1') || (isset($_GET['role_edited']) && $_GET['role_edited'] != '' && isset($_GET['msg']) && $_GET['msg'] != '')) {
        $display = "roles";
    }
    else {
        $display = "users";
    }
    add_filter( 'pre_user_query', 'sort_by_member_number' );
    $meta_query_array[] = array('relation' => 'AND');
    $meta_query_array[] = array('key'=>'block_date');
    $meta_query_array[] = array(
        array(
        'relation' => 'OR',
        array(
        'key' => 'is_active',
        'compare' => 'NOT EXISTS'
        ),
        array(
        'key' => 'is_active',
        'value' => 'n',
        'compare' => '!='
            )
        )
    );
    $filter_ary['orderby'] = $orderby;
    $filter_ary['order'] = $order;
    $filter_ary['meta_query'] = $meta_query_array;
    //Query for counting results
    $get_users_u1 = new WP_User_Query($filter_ary);
    $total_items = $get_users_u1->total_users;
    $total_pages = ceil($total_items/$records_per_page);
		
    $next_page = (int)$paged+1;
    if($next_page > $total_pages)
            $next_page = $total_pages;
    $filter_ary['number'] = $records_per_page;
    $filter_ary['offset'] = $offset;

    $prev_page = (int)$paged-1;
    if($prev_page < 1)
            $prev_page = 1;
    
    $sr_no = 1;
    if( isset( $paged ) && $paged > 1 ) {
        $sr_no = ( $records_per_page * ( $paged -1 ) + 1);
    }
    //Main query
    $get_users_u = new WP_User_Query($filter_ary);
    remove_filter( 'pre_user_query', 'sort_by_member_number' );
    $get_users = $get_users_u->get_results();
    
    ?>
    <div class="wrap" id="blocked-list">
        <h2 class="ublocker-page-title"><?php _e( 'Date Wise Blocked User list', 'user-blocker') ?></h2> 
        <?php
            //Display success/error messages
            if( $msg != '' ) { ?>
                <div class="ublocker-notice <?php echo $msg_class; ?>">
                    <p><?php echo $msg; ?></p>
                </div>
        <?php } ?>
        <div class="tab_parent_parent"><div class="tab_parent"><ul><li><a href="?page=blocked_user_list">Blocked User List By Time</a></li><li><a class="current" href="?page=datewise_blocked_user_list">Blocked User List By Date</a></li><li><a href="?page=permanent_blocked_user_list">Blocked User List Permanently</a></li><li><a href="?page=all_type_blocked_user_list">Blocked User List</a></li></ul></div></div>
        <div class="cover_form">
        <div class="search_box">
            <div class="tablenav top">                
                <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                    <div class="actions">
                        <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                        <select name="display" id="display_status">
                            <option value="users" <?php echo selected($display,'users') ?> >Users</option>
                            <option value="roles" <?php echo selected($display,'roles') ?>>Roles</option>
                        </select>
                        <?php //Pagination -top ?>
                        <div class="tablenav-pages" <?php if((int)$total_pages <= 1) { echo 'style="display: none;"'; } ?>>
                            <span class="displaying-num"><?php echo $total_items; ?> items</span>
                            <span class="pagination-links">
                                <a class="first-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=datewise_blocked_user_list&paged=1&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the first page">&laquo;</a>
                                <a class="prev-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=datewise_blocked_user_list&paged='.$prev_page.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the previous page">&lsaquo;</a>
                                <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page">
                                        of
                                        <span class="total-pages"><?php echo $total_pages; ?></span>
                                </span>
                                <a class="next-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=datewise_blocked_user_list&paged='.$next_page.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the next page">&rsaquo;</a>
                                <a class="last-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=datewise_blocked_user_list&paged='.$total_pages.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the last page">&raquo;</a>
                            </span>
                            <input style="display: none;" id="sbtPages" class="button" type="submit" value="sbtPages" name="filter_action">
                        </div><!-- .tablenav-pages -->
                    </div>
                    <div class="actions">
                        <div class="filter_div" <?php if($display == 'roles') echo 'style="display: none"'; ?>>
                            <label><strong><?php _e( 'Select Role: ', 'user-blocker') ?></strong></label>
                            <select id="srole" name="role" onchange="searchUser();">
                                <option value="">All Roles</option>
                                <?php if($get_roles) 
                                {
                                    foreach($get_roles as $key=>$value) {
                                        if( $key == 'administrator' )
                                            continue;
                                        ?>
                                        <option <?php echo selected($key, $srole); ?> value="<?php echo $key; ?>"><?php echo ucfirst($value['name']); ?></option>
                                        <?php
                                     }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="actions">
                        <div class="filter_div" <?php if($display == 'roles') echo 'style="display: none"'; ?>>
                            <input type="hidden" value="datewise_blocked_user_list" name="page" />
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="username or email or first name" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="Search" name="filter_action">
                            <a class="button" href="<?php echo '?page=datewise_blocked_user_list'; ?>" style="margin-left: 10px;">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <table class="widefat post fixed role_records" <?php if($display == 'roles') echo 'style="display: table"'; ?>>
            <thead>
                <tr>
                    <th class="th-role">Role</th>
                    <th class="blk-date">Block Date</th>
                    <th style="text-align:center">Message</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="th-role">Role</th>
                    <th class="blk-date">Block Date</th>
                    <th style="text-align:center">Message</th>
                </tr>
            </tfoot>
            <tbody>
                <?php
                $no_data = 1;
                if($get_roles) {
                    $k = 1;
                   foreach($get_roles as $key=>$value) {
                        $block_date = get_option($key.'_block_date');
                        $block_permenant = get_option($key.'_block_permenant');
                        if( $k%2 == 0 )
                            $alt_class = 'alt';
                        else
                            $alt_class = '';
                        if( $key == 'administrator' || $block_date=='' || $block_permenant!='')                            
                            continue;
                        $no_data = 0;
                       ?>
                        <tr class="<?php echo $alt_class; ?>">
                            <td class="user-role"><?php echo $value['name']; ?>
                                <div class="row-actions">
                                    <span class="trash"><a title="Reset this item" href="?page=datewise_blocked_user_list&reset=1&role=<?php echo $key; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>">Reset</a></span>
                                </div>
                            </td>
                            <td>
                                <?php
                                if(!empty($block_date) && isset($block_date) && $block_date!='') {
                                    if (array_key_exists('frmdate',$block_date) && array_key_exists('todate',$block_date)) {
                                        $frmdate = $block_date['frmdate'];
                                        $todate = $block_date['todate'];                                        
                                        if( $frmdate != '' && $todate != '' ) {
                                            echo $frmdate.' to '.$todate;
                                        }
                                    }
                                }
                                ?>
                            </td>
                            <td style="text-align:center">
                                <?php
                                    $block_msg_date = get_option($key.'_block_msg_date');
                                    echo disp_msg($block_msg_date);
                                ?>
                            </td>
                        </tr>
                <?php
                        $k++;
                    }
                    if($no_data == 1)
                    {
                         ?>
                        <tr><td colspan="3" style="text-align:center"><?php echo 'No records found.'; ?></td></tr>
                      <?php
                    }
                 }
                 else
                 {
                     ?>
                        <tr><td colspan="3" style="text-align:center"><?php echo 'No records found.'; ?></td></tr>
                      <?php
                 }
                ?>
            </tbody>
        </table>
        <table class="widefat post fixed users_records" <?php if($display == 'roles') echo 'style="display:none"'; ?>>
            <thead>
                <tr>
                    <th class="sr-no">S.N.</th>
                    <?php
                    $linkOrder = 'ASC';
                    if( isset($order) ) {
                        if( $order == 'ASC' ) {
                            $linkOrder = 'DESC';
                        }
                        else if( $order == 'DESC' ) {
                            $linkOrder = 'ASC';
                        }
                    }
                    ?>
                    <th class="th-username sortable <?php echo strtolower($order); ?>">
                        <a href="?page=datewise_blocked_user_list&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Username</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-name sortable <?php echo strtolower($order); ?>">
                        <a href="?page=datewise_blocked_user_list&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Name</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-email sortable <?php echo strtolower($order); ?>">
                        <a href="?page=datewise_blocked_user_list&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Email</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-role">Role</th>
                    <th class="th-time">Block Date</th>
                    <th style="text-align:center">Message</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="sr-no">S.N.</th>
                    <?php
                    $linkOrder = 'ASC';
                    if( isset($order) ) {
                        if( $order == 'ASC' ) {
                            $linkOrder = 'DESC';
                        }
                        else if( $order == 'DESC' ) {
                            $linkOrder = 'ASC';
                        }
                    }
                    ?>
                    <th class="th-username sortable <?php echo strtolower($order); ?>">
                        <a href="?page=datewise_blocked_user_list&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Username</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-name sortable <?php echo strtolower($order); ?>">
                        <a href="?page=datewise_blocked_user_list&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Name</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-email sortable <?php echo strtolower($order); ?>">
                        <a href="?page=datewise_blocked_user_list&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Email</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-role">Role</th>
                    <th class="th-time">Block Date</th>
                    <th style="text-align:center">Message</th>
                </tr>
            </tfoot>
            <tbody>
                <?php
                if($get_users) {
                       foreach($get_users as $user) {
                            if( $sr_no%2 == 0 )
                                $alt_class = 'alt';
                            else
                                $alt_class = '';
                       ?>
                        <tr class="<?php echo $alt_class; ?>">
                            <td align="center"><?php echo $sr_no; ?></td>
                            <td><?php echo $user->user_login; ?>
                                <div class="row-actions">
                                    <span class="trash"><a title="Reset this item" href="?page=datewise_blocked_user_list&reset=1&paged=<?php echo $paged; ?>&username=<?php echo $user->ID; ?>&role=<?php echo $srole; ?>&txtUsername=<?php echo $txtUsername; ?>">Reset</a></span>
                                </div>
                            </td>
                            <td><?php echo $user->display_name; ?></td>
                            <td><?php echo $user->user_email; ?></td>
                            <td class="user-role"><?php echo ucfirst( str_replace('_', ' ', $user->roles[0]) ); ?></td>
                            <td>
                                <?php
                                $block_date = get_user_meta($user->ID,'block_date',true);
                                if(!empty($block_date)) {
                                    if (array_key_exists('frmdate',$block_date) && array_key_exists('todate',$block_date)) {
                                        $frmdate = $block_date['frmdate'];
                                        $todate = $block_date['todate'];                                        
                                        if( $frmdate != '' && $todate != '' ) {
                                            echo $frmdate.' to '.$todate;
                                        }
                                    }
                                }
                                ?>
                            </td>
                            <td style="text-align:center">
                                <?php
                                    $block_msg_date = get_user_meta($user->ID,'block_msg_date',true);
                                    echo disp_msg($block_msg_date);
                                ?>
                            </td>
                        </tr>
                <?php
                        $sr_no++;
                    }
                 }
                 else {
                     echo '<tr><td colspan="7" style="text-align:center">No records found.</td></tr>';
                 }
                ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php
}


function permanent_block_user_list_page() {
    global $wpdb;
    $txtUsername = '';
    $role = '';
    $srole = '';
    $msg_class = '';
    $msg = '';
    $total_pages = '';
    $next_page = '';
    $prev_page = '';
    $search_arg = '';
    $records_per_page = 10;
    $paged = 1;
    $orderby = 'user_login';
    $order = 'ASC';
    if( isset($_GET['msg']) && $_GET['msg'] != '' ) {
        $msg = $_GET['msg'];
    }
    if( isset($_GET['msg_class']) && $_GET['msg_class'] != '' ) {
        $msg_class = $_GET['msg_class'];
    }
    
    if(isset($_GET['paged']))
        $paged = $_GET['paged'];
    if(!is_numeric($paged))
        $paged = 1;
    if( isset( $_REQUEST['filter_action'] ) ) {
        if( $_REQUEST['filter_action'] == 'Search' ) {
            $paged = 1;
        }
    }
    if( isset($_GET['orderby']) && $_GET['orderby'] != '' )
        $orderby = $_GET['orderby'];
    if( isset($_GET['order']) && $_GET['order'] != '' )
        $order = $_GET['order'];
    
    $offset = ($paged-1)*$records_per_page;
    
    //Only for roles
    global $wp_roles;
    $get_roles = $wp_roles->roles;
    //Reset users
    
    if(isset($_GET['reset']) && $_GET['reset'] == '1')
    {
        if(isset($_GET['username']) && $_GET['username'] != '') {
            $r_username = $_GET['username'];
            $user_data = new WP_User( $r_username );
            if( get_userdata($r_username) != false ) {
                delete_user_meta($r_username, 'is_active');
                delete_user_meta($r_username,'block_msg_permenant');
                $msg_class = 'updated';
                $msg = $user_data->user_login . '\'s blocking time is successfully reset.';
            }
            else {
                $msg_class = 'error';
                $msg = 'Invalid user for reset blocking time.';
            }
        }
        if(isset($_GET['role']) && $_GET['role'] != '')
        {
            $reset_roles = get_users(array('role'=>$_GET['role']));
            if(!empty($reset_roles))
            {
                foreach($reset_roles as $single_reset_role)
                {
                    $own_value = get_user_meta($single_reset_role->ID,'is_active',true);
                    $role_value = get_option($_GET['role'].'_is_active');
                    if($own_value == $role_value)
                    {
                        delete_user_meta($single_reset_role->ID,'is_active');
                        delete_user_meta($single_reset_role->ID,'block_msg_permenant');
                    }
                }
            }
            delete_option($_GET['role'].'_is_active');
            delete_option($_GET['role'].'_block_msg_permenant');
            $msg_class = 'updated';
            $msg = $_GET['role'] . '\'s blocking time is successfully reset.';
        }
    }
    if(isset($_GET['txtUsername']) && trim($_GET['txtUsername'])!='') {
        $txtUsername = trim($_GET['txtUsername']);
        $filter_ary['search'] = '*'.esc_attr($txtUsername).'*';
        $filter_ary['search_columns'] = array(
            'user_login',
            'user_nicename',
            'user_email',
            'display_name'
        );
    }
    if( $txtUsername == '' ) {
        if(isset($_GET['role']) && $_GET['role'] != '' && !isset($_GET['reset'])) {
            $filter_ary['role'] = $_GET['role'];
            $srole = $_GET['role'];
        }
    }
    //end
    
    if( (isset($_GET['display']) && $_GET['display'] == 'roles') || (isset($_GET['role']) && $_GET['role'] != '' && isset($_GET['reset']) && $_GET['reset'] == '1') || (isset($_GET['role_edited']) && $_GET['role_edited'] != '' && isset($_GET['msg']) && $_GET['msg'] != '')) {
        $display = "roles";
    }
    else {
        $display = "users";
    }
    $filter_ary['orderby'] = $orderby;
    $filter_ary['order'] = $order;
    $meta_query_array[] = array(
        'key'=>'is_active',
        'value' => 'n',
        'compare' => '=');
    $filter_ary['meta_query'] = $meta_query_array;
    //Query for counting results
    $get_users_u1 = new WP_User_Query($filter_ary);
    $total_items = $get_users_u1->total_users;
    $total_pages = ceil($total_items/$records_per_page);
		
    $next_page = (int)$paged+1;
    if($next_page > $total_pages)
            $next_page = $total_pages;
    $filter_ary['number'] = $records_per_page;
    $filter_ary['offset'] = $offset;

    $prev_page = (int)$paged-1;
    if($prev_page < 1)
            $prev_page = 1;
    
    $sr_no = 1;
    if( isset( $paged ) && $paged > 1 ) {
        $sr_no = ( $records_per_page * ( $paged -1 ) + 1);
    }
    //Main query
    $get_users_u = new WP_User_Query($filter_ary);
    $get_users = $get_users_u->get_results();
    
    ?>
    <div class="wrap" id="blocked-list">
        <h2 class="ublocker-page-title"><?php _e( 'Permanently Blocked User list', 'user-blocker') ?></h2> 
        <?php
            //Display success/error messages
            if( $msg != '' ) { ?>
                <div class="ublocker-notice <?php echo $msg_class; ?>">
                    <p><?php echo $msg; ?></p>
                </div>
        <?php } ?>
        <div class="tab_parent_parent"><div class="tab_parent"><ul><li><a href="?page=blocked_user_list">Blocked User List By Time</a></li><li><a href="?page=datewise_blocked_user_list">Blocked User List By Date</a></li><li><a class="current" href="?page=permanent_blocked_user_list">Blocked User List Permanently</a></li><li><a href="?page=all_type_blocked_user_list">Blocked User List</a></li></ul></div></div>
        <div class="cover_form">
        <div class="search_box">
            <div class="tablenav top">                
                <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                    <div class="actions">
                        <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                        <select name="display" id="display_status">
                            <option value="users" <?php echo selected($display,'users') ?> >Users</option>
                            <option value="roles" <?php echo selected($display,'roles') ?>>Roles</option>
                        </select>
                        <?php //Pagination -top ?>
                        <div class="tablenav-pages" <?php if((int)$total_pages <= 1) { echo 'style="display: none;"'; } ?>>
                            <span class="displaying-num"><?php echo $total_items; ?> items</span>
                            <span class="pagination-links">
                                <a class="first-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=permanent_blocked_user_list&paged=1&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the first page">&laquo;</a>
                                <a class="prev-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=permanent_blocked_user_list&paged='.$prev_page.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the previous page">&lsaquo;</a>
                                <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page">
                                        of
                                        <span class="total-pages"><?php echo $total_pages; ?></span>
                                </span>
                                <a class="next-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=permanent_blocked_user_list&paged='.$next_page.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the next page">&rsaquo;</a>
                                <a class="last-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=permanent_blocked_user_list&paged='.$total_pages.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the last page">&raquo;</a>
                            </span>
                            <input style="display: none;" id="sbtPages" class="button" type="submit" value="sbtPages" name="filter_action">
                        </div><!-- .tablenav-pages -->
                    </div>
                    <div class="actions">
                        <div class="filter_div" <?php if($display == 'roles') echo 'style="display: none"'; ?>>
                            <label><strong><?php _e( 'Select Role: ', 'user-blocker') ?></strong></label>
                            <select id="srole" name="role" onchange="searchUser();">
                                <option value="">All Roles</option>
                                <?php if($get_roles) 
                                {
                                    foreach($get_roles as $key=>$value) {
                                        if( $key == 'administrator' )
                                            continue;
                                        ?>
                                        <option <?php echo selected($key, $srole); ?> value="<?php echo $key; ?>"><?php echo ucfirst($value['name']); ?></option>
                                        <?php
                                     }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="actions">
                        <div class="filter_div" <?php if($display == 'roles') echo 'style="display: none"'; ?>>
                            <input type="hidden" value="permanent_blocked_user_list" name="page" />
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="username or email or first name" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="Search" name="filter_action">
                            <a class="button" href="<?php echo '?page=permanent_blocked_user_list'; ?>" style="margin-left: 10px;">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <table class="widefat post fixed role_records" <?php if($display == 'roles') echo 'style="display: table"'; ?>>
            <thead>
                <tr>
                    <th class="th-role">Role</th>
                    <th style="text-align:center">Message</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="th-role">Role</th>
                    <th style="text-align:center">Message</th>
                </tr>
            </tfoot>
            <tbody>
                <?php
                $no_data = 1;
                if($get_roles) {
                    $k = 1;
                   foreach($get_roles as $key=>$value) {
                        $block_permenant = get_option($key.'_is_active');
                        if( $k%2 == 0 )
                            $alt_class = 'alt';
                        else
                            $alt_class = '';
                        if( $key == 'administrator' || $block_permenant!='n')                            
                            continue;
                        $no_data = 0;
                       ?>
                        <tr class="<?php echo $alt_class; ?>">
                            <td class="user-role"><?php echo $value['name']; ?>
                                <div class="row-actions">
                                    <span class="trash"><a title="Reset this item" href="?page=permanent_blocked_user_list&reset=1&role=<?php echo $key; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>">Reset</a></span>
                                </div>
                            </td>
                            <td style="text-align:center">
                                <?php
                                   $block_msg_permenant = get_option($key.'_block_msg_permenant');
                                   echo disp_msg($block_msg_permenant);
                                ?>
                            </td>
                        </tr>
                <?php
                        $k++;
                    }
                    if($no_data == 1)
                    {
                         ?>
                        <tr><td colspan="2" style="text-align:center"><?php echo 'No records found.'; ?></td></tr>
                      <?php
                    }
                 }
                 else
                 {
                     ?>
                        <tr><td colspan="2" style="text-align:center"><?php echo 'No records found.'; ?></td></tr>
                      <?php
                 }
                ?>
            </tbody>
        </table>
        <table class="widefat post fixed users_records" <?php if($display == 'roles') echo 'style="display:none"'; ?>>
            <thead>
                <tr>
                    <th class="sr-no">S.N.</th>
                    <?php
                    $linkOrder = 'ASC';
                    if( isset($order) ) {
                        if( $order == 'ASC' ) {
                            $linkOrder = 'DESC';
                        }
                        else if( $order == 'DESC' ) {
                            $linkOrder = 'ASC';
                        }
                    }
                    ?>
                    <th class="th-username sortable <?php echo strtolower($order); ?>">
                        <a href="?page=permanent_blocked_user_list&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Username</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-name sortable <?php echo strtolower($order); ?>">
                        <a href="?page=permanent_blocked_user_list&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Name</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-email sortable <?php echo strtolower($order); ?>">
                        <a href="?page=permanent_blocked_user_list&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Email</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-role">Role</th>
                    <th style="text-align:center">Message</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="sr-no">S.N.</th>
                    <?php
                    $linkOrder = 'ASC';
                    if( isset($order) ) {
                        if( $order == 'ASC' ) {
                            $linkOrder = 'DESC';
                        }
                        else if( $order == 'DESC' ) {
                            $linkOrder = 'ASC';
                        }
                    }
                    ?>
                    <th class="th-username sortable <?php echo strtolower($order); ?>">
                        <a href="?page=permanent_blocked_user_list&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Username</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-name sortable <?php echo strtolower($order); ?>">
                        <a href="?page=permanent_blocked_user_list&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Name</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-email sortable <?php echo strtolower($order); ?>">
                        <a href="?page=permanent_blocked_user_list&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Email</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-time">Role</th>
                    <th style="text-align:center">Message</th>
                </tr>
            </tfoot>
            <tbody>
                <?php
                if($get_users) {
                       foreach($get_users as $user) {
                            if( $sr_no%2 == 0 )
                                $alt_class = 'alt';
                            else
                                $alt_class = '';
                       ?>
                        <tr class="<?php echo $alt_class; ?>">
                            <td align="center"><?php echo $sr_no; ?></td>
                            <td><?php echo $user->user_login; ?>
                                <div class="row-actions">
                                    <span class="trash"><a title="Reset this item" href="?page=permanent_blocked_user_list&reset=1&paged=<?php echo $paged; ?>&username=<?php echo $user->ID; ?>&role=<?php echo $srole; ?>&txtUsername=<?php echo $txtUsername; ?>">Reset</a></span>
                                </div>
                            </td>
                            <td><?php echo $user->display_name; ?></td>
                            <td><?php echo $user->user_email; ?></td>
                            <td class="user-role"><?php echo ucfirst( str_replace('_', ' ', $user->roles[0]) ); ?></td>
                            <td style="text-align:center">
                                <?php
                                    $block_msg_permenant = get_user_meta($user->ID,'block_msg_permenant',true);
                                    echo disp_msg($block_msg_permenant);
                                ?>
                            </td>
                        </tr>
                <?php
                        $sr_no++;
                    }
                 }
                 else {
                     echo '<tr><td colspan="6" style="text-align:center">No records found.</td></tr>';
                 }
                ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php
}

function all_type_block_user_list_page() {
    global $wpdb;
    $txtUsername = '';
    $role = '';
    $srole = '';
    $msg_class = '';
    $msg = '';
    $total_pages = '';
    $next_page = '';
    $prev_page = '';
    $search_arg = '';
    $records_per_page = 10;
    $paged = 1;
    $orderby = 'user_login';
    $order = 'ASC';
    if( isset($_GET['msg']) && $_GET['msg'] != '' ) {
        $msg = $_GET['msg'];
    }
    if( isset($_GET['msg_class']) && $_GET['msg_class'] != '' ) {
        $msg_class = $_GET['msg_class'];
    }
    
    if(isset($_GET['paged']))
        $paged = $_GET['paged'];
    if(!is_numeric($paged))
        $paged = 1;
    if( isset( $_REQUEST['filter_action'] ) ) {
        if( $_REQUEST['filter_action'] == 'Search' ) {
            $paged = 1;
        }
    }
    if( isset($_GET['orderby']) && $_GET['orderby'] != '' )
        $orderby = $_GET['orderby'];
    if( isset($_GET['order']) && $_GET['order'] != '' )
        $order = $_GET['order'];
    
    $offset = ($paged-1)*$records_per_page;
    
    //Only for roles
    global $wp_roles;
    $get_roles = $wp_roles->roles;
    //Reset users
    
    if(isset($_GET['reset']) && $_GET['reset'] == '1')
    {
        if(isset($_GET['username']) && $_GET['username'] != '') {
            $r_username = $_GET['username'];
            $user_data = new WP_User( $r_username );
            if( get_userdata($r_username) != false ) {
                delete_user_meta($r_username, 'block_day');
                delete_user_meta($r_username, 'block_msg_date');
                delete_user_meta($r_username, 'block_date');
                delete_user_meta($r_username, 'block_msg_date');
                delete_user_meta($r_username, 'is_active');
                delete_user_meta($r_username, 'block_msg_permenant');
                $msg_class = 'updated';
                $msg = $user_data->user_login . '\'s blocking is successfully reset.';
            }
            else {
                $msg_class = 'error';
                $msg = 'Invalid user for reset blocking.';
            }
        }
        if(isset($_GET['role']) && $_GET['role'] != '')
        {
            $reset_roles = get_users(array('role'=>$_GET['role']));
            if(!empty($reset_roles))
            {
                foreach($reset_roles as $single_reset_role)
                {
                    //Permenant block data
                    $own_value = get_user_meta($single_reset_role->ID,'is_active',true);
                    $role_value = get_option($_GET['role'].'_is_active');
                    $own_value_msg = get_user_meta($single_reset_role->ID,'block_msg_permenant',true);
                    $role_value_msg = get_option($_GET['role'].'_block_msg_permenant');
                    if( ($own_value == $role_value) && ($own_value_msg == $role_value_msg) )
                    {
                        delete_user_meta($single_reset_role->ID,'is_active');
                        delete_user_meta($single_reset_role->ID,'block_msg_permenant');
                    }
                    
                    //Date wise block data
                    $own_value_date = get_user_meta($single_reset_role->ID,'block_date',true);
                    $role_value_date = get_option($_GET['role'].'_block_date');
                    $own_value_date_msg = get_user_meta($single_reset_role->ID,'block_msg_date',true);
                    $role_value_date_msg = get_option($_GET['role'].'_block_msg_date');
                    if( ($own_value_date == $role_value_date) && ($own_value_date_msg == $role_value_date_msg) )
                    {
                        delete_user_meta($single_reset_role->ID,'block_date');
                        delete_user_meta($single_reset_role->ID,'block_msg_date');
                    }
                    
                    //Day wise block data
                    $own_value_day = get_user_meta($single_reset_role->ID,'block_day',true);
                    $role_value_day = get_option($_GET['role'].'_block_day');
                    $own_value_day_msg = get_user_meta($single_reset_role->ID,'block_msg_day',true);
                    $role_value_day_msg = get_option($_GET['role'].'_block_msg_day');
                    if( ($own_value_day == $role_value_day) && ($own_value_day_msg == $role_value_day_msg) )
                    {
                        delete_user_meta($single_reset_role->ID,'block_day');
                        delete_user_meta($single_reset_role->ID,'block_msg_day');
                    }
                }
            }
            delete_option($_GET['role'].'_is_active');
            delete_option($_GET['role'].'_block_date');
            delete_option($_GET['role'].'_block_day');
            $msg_class = 'updated';
            $msg = $_GET['role'] . '\'s blocking is successfully reset.';
        }
    }
    if(isset($_GET['txtUsername']) && trim($_GET['txtUsername'])!='') {
        $txtUsername = trim($_GET['txtUsername']);
        $filter_ary['search'] = '*'.esc_attr($txtUsername).'*';
        $filter_ary['search_columns'] = array(
            'user_login',
            'user_nicename',
            'user_email',
            'display_name'
        );
    }
    if( $txtUsername == '' ) {
        if(isset($_GET['role']) && $_GET['role'] != '' && !isset($_GET['reset'])) {
            $filter_ary['role'] = $_GET['role'];
            $srole = $_GET['role'];
        }
    }
    //end
    
    if( (isset($_GET['display']) && $_GET['display'] == 'roles') || (isset($_GET['role']) && $_GET['role'] != '' && isset($_GET['reset']) && $_GET['reset'] == '1') || (isset($_GET['role_edited']) && $_GET['role_edited'] != '' && isset($_GET['msg']) && $_GET['msg'] != '')) {
        $display = "roles";
    }
    else {
        $display = "users";
    }
    $filter_ary['orderby'] = $orderby;
    $filter_ary['order'] = $order;
    $meta_query_array[] = array(
        'relation'=> 'OR',
        array(
        'key'=>'block_date',
        'compare' => 'EXISTS'),
        array(
        'key'=>'is_active',
        'value' => 'n',
        'compare' => '='),
        
        array(
        'key'=>'block_day',
        'compare' => 'EXISTS')
            );
    $filter_ary['meta_query'] = $meta_query_array;
    add_filter( 'pre_user_query', 'sort_by_member_number' );
    //Query for counting results
    $get_users_u1 = new WP_User_Query($filter_ary);
    $total_items = $get_users_u1->total_users;
    $total_pages = ceil($total_items/$records_per_page);
		
    $next_page = (int)$paged+1;
    if($next_page > $total_pages)
            $next_page = $total_pages;
    $filter_ary['number'] = $records_per_page;
    $filter_ary['offset'] = $offset;

    $prev_page = (int)$paged-1;
    if($prev_page < 1)
            $prev_page = 1;
    
    $sr_no = 1;
    if( isset( $paged ) && $paged > 1 ) {
        $sr_no = ( $records_per_page * ( $paged -1 ) + 1);
    }
    //Main query
    $get_users_u = new WP_User_Query($filter_ary);
    remove_filter( 'pre_user_query', 'sort_by_member_number' );
    $get_users = $get_users_u->get_results();
    
    ?>
    <div class="wrap" id="blocked-list">
        <h2 class="ublocker-page-title"><?php _e( 'Blocked User list', 'user-blocker') ?></h2> 
        <?php
            //Display success/error messages
            if( $msg != '' ) { ?>
                <div class="ublocker-notice <?php echo $msg_class; ?>">
                    <p><?php echo $msg; ?></p>
                </div>
        <?php } ?>
        <div class="tab_parent_parent"><div class="tab_parent"><ul><li><a href="?page=blocked_user_list">Blocked User List By Time</a></li><li><a href="?page=datewise_blocked_user_list">Blocked User List By Date</a></li><li><a href="?page=permanent_blocked_user_list">Blocked User List Permanently</a></li><li><a class='current' href="?page=all_type_blocked_user_list">Blocked User List</a></li></ul></div></div>
        <div class="cover_form">
        <div class="search_box">
            <div class="tablenav top">                
                <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                    <div class="actions">
                        <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                        <select name="display" id="display_status">
                            <option value="users" <?php echo selected($display,'users') ?> >Users</option>
                            <option value="roles" <?php echo selected($display,'roles') ?>>Roles</option>
                        </select>
                        <?php //Pagination -top ?>
                        <div class="tablenav-pages" <?php if((int)$total_pages <= 1) { echo 'style="display: none;"'; } ?>>
                            <span class="displaying-num"><?php echo $total_items; ?> items</span>
                            <span class="pagination-links">
                                <a class="first-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=all_type_blocked_user_list&paged=1&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the first page">&laquo;</a>
                                <a class="prev-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=all_type_blocked_user_list&paged='.$prev_page.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the previous page">&lsaquo;</a>
                                <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page">
                                        of
                                        <span class="total-pages"><?php echo $total_pages; ?></span>
                                </span>
                                <a class="next-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=all_type_blocked_user_list&paged='.$next_page.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the next page">&rsaquo;</a>
                                <a class="last-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=all_type_blocked_user_list&paged='.$total_pages.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the last page">&raquo;</a>
                            </span>
                            <input style="display: none;" id="sbtPages" class="button" type="submit" value="sbtPages" name="filter_action">
                        </div><!-- .tablenav-pages -->
                    </div>
                    <div class="actions">
                        <div class="filter_div" <?php if($display == 'roles') echo 'style="display: none"'; ?>>
                            <label><strong><?php _e( 'Select Role: ', 'user-blocker') ?></strong></label>
                            <select id="srole" name="role" onchange="searchUser();">
                                <option value="">All Roles</option>
                                <?php if($get_roles) 
                                {
                                    foreach($get_roles as $key=>$value) {
                                        $block_day = get_option($key.'_block_day');
                                        $block_date = get_option($key.'_block_date');
                                        $is_active = get_option($key.'_is_active');
                                        if( $key == 'administrator' || ($is_active!='n' && $block_date=='' && $block_day==''))
                                            continue;
                                        ?>
                                        <option <?php echo selected($key, $srole); ?> value="<?php echo $key; ?>"><?php echo ucfirst($value['name']); ?></option>
                                        <?php
                                     }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="actions">
                        <div class="filter_div" <?php if($display == 'roles') echo 'style="display: none"'; ?>>
                            <input type="hidden" value="all_type_blocked_user_list" name="page" />
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="username or email or first name" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="Search" name="filter_action">
                            <a class="button" href="<?php echo '?page=all_type_blocked_user_list'; ?>" style="margin-left: 10px;">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <table class="widefat post fixed role_records" <?php if($display == 'roles') echo 'style="display: table"'; ?>>
            <thead>
                <tr>
                    <th class="th-role">Role</th>
                    <th style="text-align:center">Message</th>
                    <th class="th-username">Block Data</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="th-role">Role</th>
                    <th style="text-align:center">Message</th>
                    <th class="th-username">Block Data</th>
                </tr>
            </tfoot>
            <tbody>
                <?php
                $no_data = 1;
                if($get_roles) {
                    $k = 1;
                   foreach($get_roles as $key=>$value) {
                        $block_day = get_option($key.'_block_day');
                        $block_date = get_option($key.'_block_date');
                        $is_active = get_option($key.'_is_active');
                        if( $key == 'administrator' || ($is_active!='n' && $block_date=='' && $block_day==''))                            
                            continue;
                        if( $k%2 == 0 )
                            $alt_class = 'alt';
                        else
                            $alt_class = '';
                        $no_data = 0;
                       ?>
                        <tr class="<?php echo $alt_class; ?>">
                            <td class="user-role"><?php echo $value['name']; ?>
                                <div class="row-actions">
                                    <span class="trash"><a title="Reset this item" href="?page=all_type_blocked_user_list&reset=1&role=<?php echo $key; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>">Reset</a></span>
                                </div>
                            </td>
                            <td style="text-align:center">
                                <?php
                                   all_block_data_msg_role($key);
                                ?>
                            </td>
                            <td>
                                <?php
                                all_block_data_view_role($key);
                                ?>
                            </td>
                        </tr>
                <?php
                        echo all_block_data_table_role($key); 
                        $k++;
                    }
                    if($no_data == 1)
                    {
                         ?>
                        <tr><td colspan="3" style="text-align:center"><?php echo 'No records found.'; ?></td></tr>
                      <?php
                    }
                 }
                 else
                 {
                     ?>
                        <tr><td colspan="3" style="text-align:center"><?php echo 'No records found.'; ?></td></tr>
                      <?php
                 }
                ?>
            </tbody>
        </table>
        <table class="widefat post fixed users_records" <?php if($display == 'roles') echo 'style="display:none"'; ?>>
            <thead>
                <tr>
                    <th class="sr-no">S.N.</th>
                    <?php
                    $linkOrder = 'ASC';
                    if( isset($order) ) {
                        if( $order == 'ASC' ) {
                            $linkOrder = 'DESC';
                        }
                        else if( $order == 'DESC' ) {
                            $linkOrder = 'ASC';
                        }
                    }
                    ?>
                    <th class="th-username sortable <?php echo strtolower($order); ?>">
                        <a href="?page=all_type_blocked_user_list&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Username</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-name sortable <?php echo strtolower($order); ?>">
                        <a href="?page=all_type_blocked_user_list&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Name</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-email sortable <?php echo strtolower($order); ?>">
                        <a href="?page=all_type_blocked_user_list&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Email</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-username">Role</th>
                    <th style="text-align:center">Message</th>
                    <th class="th-username aligntextcenter">Block Data</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="sr-no">S.N.</th>
                    <?php
                    $linkOrder = 'ASC';
                    if( isset($order) ) {
                        if( $order == 'ASC' ) {
                            $linkOrder = 'DESC';
                        }
                        else if( $order == 'DESC' ) {
                            $linkOrder = 'ASC';
                        }
                    }
                    ?>
                    <th class="th-username sortable <?php echo strtolower($order); ?>">
                        <a href="?page=all_type_blocked_user_list&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Username</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-name sortable <?php echo strtolower($order); ?>">
                        <a href="?page=all_type_blocked_user_list&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Name</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-email sortable <?php echo strtolower($order); ?>">
                        <a href="?page=all_type_blocked_user_list&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span>Email</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-username">Role</th>
                    <th style="text-align:center">Message</th>
                    <th class="th-username aligntextcenter">Block Data</th>
                </tr>
            </tfoot>
            <tbody>
                <?php
                if($get_users) {
                       foreach($get_users as $user) {
                            if( $sr_no%2 == 0 )
                                $alt_class = 'alt';
                            else
                                $alt_class = '';
                       ?>
                        <tr class="<?php echo $alt_class; ?>">
                            <td align="center"><?php echo $sr_no; ?></td>
                            <td><?php echo $user->user_login; ?>
                                <div class="row-actions">
                                    <span class="trash"><a title="Reset this item" href="?page=all_type_blocked_user_list&reset=1&paged=<?php echo $paged; ?>&username=<?php echo $user->ID; ?>&role=<?php echo $srole; ?>&txtUsername=<?php echo $txtUsername; ?>">Reset</a></span>
                                </div>
                            </td>
                            <td><?php echo $user->display_name; ?></td>
                            <td><?php echo $user->user_email; ?></td>
                            <td class="user-role"><?php echo ucfirst( str_replace('_', ' ', $user->roles[0]) ); ?></td>
                            <td style="text-align:center">
                                <?php
                                    echo all_block_data_msg($user->ID);
                                ?>
                            </td>
                            <td class="aligntextcenter">
                                <?php
                                all_block_data_view($user->ID);
                                ?>
                            </td>
                        </tr>
                        <?php echo all_block_data_table($user->ID); 
                        $sr_no++;
                    }
                 }
                 else {
                     echo '<tr><td colspan="7" style="text-align:center">No records found.</td></tr>';
                 }
                ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php
}


function block_user_page() {
        ?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery('#week-sun .time').timepicker({
            'showDuration': true,
            step: 15,
            'timeFormat': 'h:i A'
        });
        var sun_time = document.getElementById('week-sun');
        var sun_time_pair = new Datepair(sun_time);

        jQuery('#week-mon .time').timepicker({
            'showDuration': true,
            step: 15,
            'timeFormat': 'h:i A'
        });
        var sun_time = document.getElementById('week-mon');
        var sun_time_pair = new Datepair(sun_time);

        jQuery('#week-tue .time').timepicker({
            'showDuration': true,
            step: 15,
            'timeFormat': 'h:i A'
        });
        var sun_time = document.getElementById('week-tue');
        var sun_time_pair = new Datepair(sun_time);

        jQuery('#week-wed .time').timepicker({
            'showDuration': true,
            step: 15,
            'timeFormat': 'h:i A'
        });
        var sun_time = document.getElementById('week-wed');
        var sun_time_pair = new Datepair(sun_time);

        jQuery('#week-thu .time').timepicker({
            'showDuration': true,
            step: 15,
            'timeFormat': 'h:i A'
        });
        var sun_time = document.getElementById('week-thu');
        var sun_time_pair = new Datepair(sun_time);

        jQuery('#week-fri .time').timepicker({
            'showDuration': true,
            step: 15,
            'timeFormat': 'h:i A'
        });
        var sun_time = document.getElementById('week-fri');
        var sun_time_pair = new Datepair(sun_time);

        jQuery('#week-sat .time').timepicker({
            'showDuration': true,
            step: 15,
            'timeFormat': 'h:i A'
        });
        var sun_time = document.getElementById('week-sat');
        var sun_time_pair = new Datepair(sun_time);
    });
</script>
    
<?php
    global $wpdb;
    $default_msg = 'You are temporary blocked.';
    $sr_no = 1;
    $records_per_page = 10;
    $msg_class = '';
    $msg = '';
    $option_name = array();
    $block_time_array = array();
    $reocrd_id = array();
    $btn_name = 'sbtSaveTime';
    $btnVal = 'Block User';
    $total_pages = '';
    $next_page = '';
    $prev_page = '';
    $search_arg = '';
    $paged = 1;
    $orderby = 'user_login';
    $order = 'ASC';
    $display_users = 0;
    $is_display_role = 0;
    $username = '';
    $srole= '';
    $role = '';
    if(get_data('paged') != '') {
        $display_users = 1;
        $paged = get_data('paged',1);
    }
    if(!is_numeric($paged))
        $paged = 1;
    if( isset( $_REQUEST['filter_action'] ) ) {
        if( $_REQUEST['filter_action'] == 'Search' ) {
            $paged = 1;
        }
    }
    if( isset($_GET['orderby']) && $_GET['orderby'] != '' )
        $orderby = $_GET['orderby'];
    if( isset($_GET['order']) && $_GET['order'] != '' )
        $order = $_GET['order'];
    
    $offset = ($paged-1)*$records_per_page;
    $txtSunFrom = $txtSunTo = $txtMonFrom = $txtMonTo = $txtTueFrom = $txtTueTo = $txtWedFrom = $txtWedTo = $txtThuFrom = $txtThuTo = $txtThuTo = $txtFriFrom = $txtFriTo = $txtSatFrom = $txtSatTo = '';
    $cmbUserBy = '';
    $block_msg = '';
    global $wp_roles;
    $get_roles = $wp_roles->roles;
    $username= '';
    if( get_data('role') != '' ) {
        $reocrd_id = array( get_data('role') );
        $role = get_data('role');
        $btn_name = 'editTime';
        $btnVal = 'Update Blocked User';
        $is_display_role = 1;
        if( $GLOBALS['wp_roles']->is_role( get_data('role') ) ) {
            $time_detail = get_option( get_data('role') . '_block_day' );
            if( $time_detail != '' ) {
                if( array_key_exists('sunday', $time_detail) ) {
                    $txtSunFrom = $time_detail['sunday']['from'];
                    $txtSunTo = $time_detail['sunday']['to'];
                }
                if( array_key_exists('monday', $time_detail) ) {
                    $txtMonFrom = $time_detail['monday']['from'];
                    $txtMonTo = $time_detail['monday']['to'];
                }
                if( array_key_exists('tuesday', $time_detail) ) {
                    $txtTueFrom = $time_detail['tuesday']['from'];
                    $txtTueTo = $time_detail['tuesday']['to'];
                }
                if( array_key_exists('wednesday', $time_detail) ) {
                    $txtWedFrom = $time_detail['wednesday']['from'];
                    $txtWedTo = $time_detail['wednesday']['to'];
                }
                if( array_key_exists('thursday', $time_detail) ) {
                    $txtThuFrom = $time_detail['thursday']['from'];
                    $txtThuTo = $time_detail['thursday']['to'];
                }
                if( array_key_exists('friday', $time_detail) ) {
                    $txtFriFrom = $time_detail['friday']['from'];
                    $txtFriTo = $time_detail['friday']['to'];
                }
                if( array_key_exists('saturday', $time_detail) ) {
                    $txtSatFrom = $time_detail['saturday']['from'];
                    $txtSatTo = $time_detail['saturday']['to'];
                }
            }
            $block_msg_day = get_option(get_data('role') . '_block_msg_day');
            $curr_edit_msg = 'Update for role: ' .$GLOBALS['wp_roles']->roles[get_data('role')]['name'];
        }
        else {
            $msg_class = 'error';
            $msg = 'Role ' . get_data('role') . ' is not exist.';
        }
    }
    if( get_data('username') != '' ) {
        $reocrd_id = array( get_data('username') );
        $username = get_data('username');
        $btn_name = 'editTime';
        $btnVal = 'Update Blocked User';
        if( get_userdata(get_data('username')) != false ) {
            $time_detail = get_user_meta( get_data('username'), 'block_day', true );
            if( $time_detail != '' ) {
                if( array_key_exists('sunday', $time_detail) ) {
                    $txtSunFrom = $time_detail['sunday']['from'];
                    $txtSunTo = $time_detail['sunday']['to'];
                }
                if( array_key_exists('monday', $time_detail) ) {
                    $txtMonFrom = $time_detail['monday']['from'];
                    $txtMonTo = $time_detail['monday']['to'];
                }
                if( array_key_exists('tuesday', $time_detail) ) {
                    $txtTueFrom = $time_detail['tuesday']['from'];
                    $txtTueTo = $time_detail['tuesday']['to'];
                }
                if( array_key_exists('wednesday', $time_detail) ) {
                    $txtWedFrom = $time_detail['wednesday']['from'];
                    $txtWedTo = $time_detail['wednesday']['to'];
                }
                if( array_key_exists('thursday', $time_detail) ) {
                    $txtThuFrom = $time_detail['thursday']['from'];
                    $txtThuTo = $time_detail['thursday']['to'];
                }
                if( array_key_exists('friday', $time_detail) ) {
                    $txtFriFrom = $time_detail['friday']['from'];
                    $txtFriTo = $time_detail['friday']['to'];
                }
                if( array_key_exists('saturday', $time_detail) ) {
                    $txtSatFrom = $time_detail['saturday']['from'];
                    $txtSatTo = $time_detail['saturday']['to'];
                }
                if( array_key_exists('block_msg', $time_detail) ) {
                    $block_msg = $time_detail['block_msg'];
                }
            }
            $block_msg_day = get_user_meta(get_data('username') , 'block_msg_day', true);
            $user_data = new WP_User( get_data('username') );
            $curr_edit_msg = 'Update for user with username: ' .$user_data->user_login;
        }
        else {
            $msg_class = 'error';
            $msg = 'User with ' . get_data('username') . ' userid is not exist.';
        }
    }
    
    
    if( isset($_POST['sbtSaveTime']) ) {
        //Check if username is selected in dd
        if( isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'role' ) {
            $is_display_role = 1;
        }
        if( isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'username' ) {
            $display_users = 1;
        }
        $txtSunFrom = trim($_POST['txtSunFrom']);
        $txtSunTo = trim($_POST['txtSunTo']);
        $txtMonFrom = trim($_POST['txtMonFrom']);
        $txtMonTo = trim($_POST['txtMonTo']);
        $txtTueFrom = trim($_POST['txtTueFrom']);
        $txtTueTo = trim($_POST['txtTueTo']);
        $txtWedFrom = trim($_POST['txtWedFrom']);
        $txtWedTo = trim($_POST['txtWedTo']);
        $txtThuFrom = trim($_POST['txtThuFrom']);
        $txtThuTo = trim($_POST['txtThuTo']);
        $txtFriFrom = trim($_POST['txtFriFrom']);
        $txtFriTo = trim($_POST['txtFriTo']);
        $txtSatFrom = trim($_POST['txtSatFrom']);
        $txtSatTo = trim($_POST['txtSatTo']);
        $block_msg_day = trim($_POST['block_msg_day']);
        if( $txtSunFrom != '' || $txtMonFrom != '' || $txtTueFrom != '' || $txtWedFrom != '' || $txtThuFrom != '' || $txtFriFrom != '' || $txtSatFrom != '' ) {
            //validate time
            $invalid_time = 1;
            if( $_POST['txtSunFrom'] != '' ) {
                $invalid_time = validate_time($_POST['txtSunFrom']);
                if( $invalid_time == 0 )
                    $txtSunFrom = '';
            }
            if( $_POST['txtSunTo'] != '' ) {
                $invalid_time = validate_time($_POST['txtSunTo']);
                if( $invalid_time == 0 )
                    $txtSunTo = '';
            }
            if( $_POST['txtMonFrom'] != '' ) {
                $invalid_time = validate_time($_POST['txtMonFrom']);
                if( $invalid_time == 0 )
                    $txtMonFrom = '';
            }
            if( $_POST['txtMonTo'] != '' ) {
                $invalid_time = validate_time($_POST['txtMonTo']);
                if( $invalid_time == 0 )
                    $txtMonTo = '';
            }
            if( $_POST['txtTueFrom'] != '' ) {
                $invalid_time = validate_time($_POST['txtTueFrom']);
                if( $invalid_time == 0 )
                    $txtTueFrom = '';
            }
            if( $_POST['txtTueTo'] != '' ) {
                $invalid_time = validate_time($_POST['txtTueTo']);
                if( $invalid_time == 0 )
                    $txtTueTo = '';
            }
            if( $_POST['txtWedFrom'] != '' ) {
                $invalid_time = validate_time($_POST['txtWedFrom']);
                if( $invalid_time == 0 )
                    $txtWedFrom = '';
            }
            if( $_POST['txtWedTo'] != '' ) {
                $invalid_time = validate_time($_POST['txtWedTo']);
                if( $invalid_time == 0 )
                    $txtWedTo = '';
            }
            if( $_POST['txtThuFrom'] != '' ) {
                $invalid_time = validate_time($_POST['txtThuFrom']);
                if( $invalid_time == 0 )
                    $txtThuFrom = '';
            }
            if( $_POST['txtThuTo'] != '' ) {
                $invalid_time = validate_time($_POST['txtThuTo']);
                if( $invalid_time == 0 )
                    $txtThuTo = '';
            }
            if( $_POST['txtFriFrom'] != '' ) {
                $invalid_time = validate_time($_POST['txtFriFrom']);
                if( $invalid_time == 0 )
                    $txtFriFrom = '';
            }
            if( $_POST['txtFriTo'] != '' ) {
                $invalid_time = validate_time($_POST['txtFriTo']);
                if( $invalid_time == 0 )
                    $txtFriTo = '';
            }
            if( $_POST['txtSatFrom'] != '' ) {
                $invalid_time = validate_time($_POST['txtSatFrom']);
                if( $invalid_time == 0 )
                    $txtSatFrom = '';
            }
            if( $_POST['txtSatTo'] != '' ) {
                $invalid_time = validate_time($_POST['txtSatTo']);
                if( $invalid_time == 0 )
                    $txtSatTo = '';
            }
            if( $invalid_time == 1 ) {
                $add_time = 1;
                $txtSunFrom = timeToTwentyfourHour($txtSunFrom);
                $txtSunTo = timeToTwentyfourHour($txtSunTo);
                $txtMonFrom = timeToTwentyfourHour($txtMonFrom);
                $txtMonTo = timeToTwentyfourHour($txtMonTo);
                $txtTueFrom = timeToTwentyfourHour($txtTueFrom);
                $txtTueTo = timeToTwentyfourHour($txtTueTo);
                $txtWedFrom = timeToTwentyfourHour($txtWedFrom);
                $txtWedTo = timeToTwentyfourHour($txtWedTo);
                $txtThuFrom = timeToTwentyfourHour($txtThuFrom);
                $txtThuTo = timeToTwentyfourHour($txtThuTo);
                $txtFriFrom = timeToTwentyfourHour($txtFriFrom);
                $txtFriTo = timeToTwentyfourHour($txtFriTo);
                $txtSatFrom = timeToTwentyfourHour($txtSatFrom);
                $txtSatTo = timeToTwentyfourHour($txtSatTo);
                //Check if start time is set for end time
                if( $txtSunTo != '' && $txtSunFrom == '' ) {
                    $add_time = 0;
                }
                if( $txtMonTo != '' && $txtMonFrom == '' ) {
                    $add_time = 0;
                }
                if( $txtTueTo != '' && $txtTueFrom == '' ) {
                    $add_time = 0;
                }
                if( $txtWedTo != '' && $txtWedFrom == '' ) {
                    $add_time = 0;
                }
                if( $txtThuTo != '' && $txtThuFrom == '' ) {
                    $add_time = 0;
                }
                if( $txtFriTo != '' && $txtFriFrom == '' ) {
                    $add_time = 0;
                }
                if( $txtSatTo != '' && $txtSatFrom == '' ) {
                    $add_time = 0;
                }
                if( isset($add_time) && $add_time == 1 ) {
                    $block_time_array['sunday'] = array(
                                            'from' => $txtSunFrom,
                                            'to'   => $txtSunTo
                                        );
                    $block_time_array['monday'] = array(
                                            'from' => $txtMonFrom,
                                            'to'   => $txtMonTo
                                        );
                    $block_time_array['tuesday'] = array(
                                            'from' => $txtTueFrom,
                                            'to'   => $txtTueTo
                                        );
                    $block_time_array['wednesday'] = array(
                                            'from' => $txtWedFrom,
                                            'to'   => $txtWedTo
                                        );
                    $block_time_array['thursday'] = array(
                                            'from' => $txtThuFrom,
                                            'to'   => $txtThuTo
                                        );
                    $block_time_array['friday'] = array(
                                            'from' => $txtFriFrom,
                                            'to'   => $txtFriTo
                                        );
                    $block_time_array['saturday'] = array(
                                            'from' => $txtSatFrom,
                                            'to'   => $txtSatTo
                                        );
                    if( (get_data('role') != '') || (get_data('username') != '') ) {
                        //get Blocking Time
                        if( (get_data('role') != '' && $GLOBALS['wp_roles']->is_role( get_data('role') ) ) || (get_data('username')!='' && get_userdata(get_data('username')) != false ) ) {
                            //echo 'invalid';
                            if(get_data('role') != '') {
                                $old_block_day = get_option(get_data('role') . '_block_day');
                                $old_block_msg_day = get_option(get_data('role') . '_block_msg_day');
                                update_option(get_data('role') . '_block_day', $block_time_array);
                                $block_msg_day = $default_msg;
                                if(trim( $_POST['block_msg_day'] ) != '')
                                {
                                    $block_msg_day = trim( $_POST['block_msg_day'] );
                                }
                                update_option(get_data('role') . '_block_msg_day', $block_msg_day);
                                $role_name = str_replace('_', ' ', get_data('role'));
                                //Update all users of this role
                                block_role_users_day( get_data('role'),$old_block_day, $block_time_array,$old_block_msg_day, $block_msg_day );
                                //Update all users of this role end
                                $msg_class = 'updated';
                                $msg = 'Blocking time for ' . $role_name . ' is successfully updated.';
                                $txtSunFrom = $txtSunTo = $txtMonFrom = $txtMonTo = $txtTueFrom = $txtTueTo = $txtWedFrom = $txtWedTo = $txtThuFrom = $txtThuTo = $txtThuTo = $txtFriFrom = $txtFriTo = $txtSatFrom = $txtSatTo = '';
                                $cmbUserBy = '';
                                $block_msg_day = '';
                                $role = '';
                                $reocrd_id = array();
                            }
                            if(get_data('username') != '') {
                                update_user_meta(get_data('username'), 'block_day', $block_time_array);
                                $block_msg_day = $default_msg;
                                if(trim( $_POST['block_msg_day'] ) != '')
                                {
                                    $block_msg_day = trim( $_POST['block_msg_day'] );
                                }
                                update_user_meta(get_data('username'), 'block_msg_day', $block_msg_day );
                                $user_info = get_userdata(get_data('username'));
                                $role_name = $user_info->user_login;
                                $msg_class = 'updated';
                                $msg = 'Blocking time for ' . $role_name . ' is successfully updated.';
                                $txtSunFrom = $txtSunTo = $txtMonFrom = $txtMonTo = $txtTueFrom = $txtTueTo = $txtWedFrom = $txtWedTo = $txtThuFrom = $txtThuTo = $txtThuTo = $txtFriFrom = $txtFriTo = $txtSatFrom = $txtSatTo = '';
                                $cmbUserBy = '';
                                $block_msg_day = '';
                                $username = '';
                                $reocrd_id = array();
                            }
                        }
                        $curr_edit_msg = '';
                        $btnVal = 'Block User';
                    }
                    else {
                        $reocrd_id = array();
                        $cmbUserBy = $_POST['cmbUserBy'];
                        //$block_time_array['block_msg'] = $block_msg;
                        //Check user by value
                        if( $cmbUserBy == 'role' ) {
                            //If user by is role
                            if( isset($_POST['chkUserRole']) ) {
                                $reocrd_id = $_POST['chkUserRole'];


                                if(trim( $_POST['block_msg_day'] ) != '')
                                {
                                    $block_msg_day = trim( $_POST['block_msg_day'] );
                                }
                                while (list ($key,$val) = @each ($reocrd_id)) {
                                    $block_msg_day = $default_msg;
                                    $old_block_day = get_option($val . '_block_day');
                                    $old_block_msg_day = get_option($val . '_block_msg_day');
                                    update_option($val . '_block_day', $block_time_array);
                                    update_option($val . '_block_msg_day',$block_msg_day);
                                    $role_name = str_replace('_', ' ', get_data('role'));
                                    //Update all users of this role
                                    block_role_users_day( $val,$old_block_day, $block_time_array,$old_block_msg_day, $block_msg_day );
                                    //Update all users of this role end
                                    $msg_class = 'updated';
                                    $msg = 'Role wise time blocking is successfully added.';
                                    $txtSunFrom = $txtSunTo = $txtMonFrom = $txtMonTo = $txtTueFrom = $txtTueTo = $txtWedFrom = $txtWedTo = $txtThuFrom = $txtThuTo = $txtThuTo = $txtFriFrom = $txtFriTo = $txtSatFrom = $txtSatTo = '';
                                    $cmbUserBy = '';
                                    $block_msg_day = '';
                                }
                            }
                            else {
                                $msg_class = 'error';
                                $msg = 'Please select atleast one role.';
                                $block_msg_day = trim( $_POST['block_msg_day'] );
                            }
                        }
                        elseif ( $cmbUserBy == 'username' ) {
                            //If user by is username
                            if( isset($_POST['chkUserUsername']) ) {
                                $reocrd_id = $_POST['chkUserUsername'];
                                $block_msg_day = $default_msg;
                                if(trim( $_POST['block_msg_day'] ) != '')
                                {
                                    $block_msg_day = trim( $_POST['block_msg_day'] );
                                }
                                while (list ($key,$val) = @each ($reocrd_id)) {
                                    update_user_meta($val, 'block_day', $block_time_array);
                                    update_user_meta($val, 'block_msg_day', $block_msg_day);
                                }
                                $msg_class = 'updated';
                                $msg = 'Username wise time blocking is successfully added.';
                                $txtSunFrom = $txtSunTo = $txtMonFrom = $txtMonTo = $txtTueFrom = $txtTueTo = $txtWedFrom = $txtWedTo = $txtThuFrom = $txtThuTo = $txtThuTo = $txtFriFrom = $txtFriTo = $txtSatFrom = $txtSatTo = '';
                                $cmbUserBy = '';
                                $block_msg_day = '';
                            }
                            else {
                                $msg_class = 'error';
                                $msg = 'Please select atleast one username.';
                                $block_msg_day = trim( $_POST['block_msg_day'] );
                            }
                        }
                        $btnVal = 'Block User';
                        $reocrd_id = array();
                    }

                }
                else {
                    $msg_class = 'error';
                    $msg = 'Please add from time for respected to time.';
                    $get_cmb_val = $_POST['cmbUserBy'];
                    if( $get_cmb_val == 'role' ) {
                        if( isset( $_POST['chkUserRole'] ) ) {
                            $reocrd_id = $_POST['chkUserRole'];
                        }
                    }
                    else if( $get_cmb_val == 'username' ) {
                        if( isset( $_POST['chkUserUsername'] ) ) {
                            $reocrd_id = $_POST['chkUserUsername'];
                        }
                    }
                }
            }
            else {
                $msg_class = 'error';
                $msg = 'Please enter valid time format.';
                $get_cmb_val = $_POST['cmbUserBy'];
                if( $get_cmb_val == 'role' ) {
                    if( isset( $_POST['chkUserRole'] ) ) {
                        $reocrd_id = $_POST['chkUserRole'];
                    }
                }
                else if( $get_cmb_val == 'username' ) {
                    if( isset( $_POST['chkUserUsername'] ) ) {
                        $reocrd_id = $_POST['chkUserUsername'];
                    }
                }
            }
        }   //Check if time is not blank
        else {
            $msg_class = 'error';
            $msg = 'Time can\'t be blank.';
            $get_cmb_val = $_POST['cmbUserBy'];
            if( $get_cmb_val == 'role' ) {
                if( isset( $_POST['chkUserRole'] ) ) {
                    $reocrd_id = $_POST['chkUserRole'];
                }
            }
            else if( $get_cmb_val == 'username' ) {
                if( isset( $_POST['chkUserUsername'] ) ) {
                    $reocrd_id = $_POST['chkUserUsername'];
                }
            }
        }
    }
    $user_query = get_users( array( 'role' => 'administrator' ) );
    $admin_id = wp_list_pluck( $user_query, 'ID' );
    $inactive_users = get_users( array(  'meta_query' => array(
	'relation' => 'AND',
		array(
			'key' => 'wp_capabilities',
			'value' => '',
			'compare' => '!=',
		),
		array(
			'key' => 'is_active',
			'value' => 'n',
			'compare' => '=',
		)
	) ) );
    $inactive_id = wp_list_pluck( $inactive_users, 'ID' );
    $exclude_id = array_unique( array_merge($admin_id, $inactive_id) );
    $users_filter = array( 'exclude' => $exclude_id );
    //Start searching
    if(get_data('txtUsername') != '') {
        $display_users = 1;
        $txtUsername = trim(get_data('txtUsername'));
        $users_filter['search'] = '*'.esc_attr($txtUsername).'*';
        $users_filter['search_columns'] = array(
            'user_login',
            'user_nicename',
            'user_email',
            'display_name'
        );
    }
    if( $txtUsername == '' ) {
        if(get_data('srole') != '') {
            $display_users = 1;
            $users_filter['role'] = get_data('srole');
            $srole = get_data('srole');
        }
    }
    //end
    if(get_data('username')!='')
    {
        $display_users = 1;
    }
    if( $is_display_role == 1 ) {
        $display_users = 0;
    }
    //if order and order by set, display users
    if( isset($_GET['orderby']) && $_GET['orderby'] != '' && isset($_GET['order']) && $_GET['order'] != ''  ) {
        $display_users = 1;
    }
    //Select usermode on reset searching
    if( isset($_GET['resetsearch']) && $_GET['resetsearch'] == '1' ) {
        $display_users = 1;
    }
    if( $display_users == 1 ) {
        $cmbUserBy = 'username';
    }
    //end
    $users_filter['orderby'] = $orderby;
    $users_filter['order'] = $order;
    $get_users_u1 = new WP_User_Query($users_filter);
    $total_items = $get_users_u1->total_users;
    $total_pages = ceil($total_items/$records_per_page);
		
    $next_page = (int)$paged+1;
    if($next_page > $total_pages)
            $next_page = $total_pages;
    $users_filter['number'] = $records_per_page;
    $users_filter['offset'] = $offset;

    $prev_page = (int)$paged-1;
    if($prev_page < 1)
            $prev_page = 1;
    
    $sr_no = 1;
    if( isset( $paged ) && $paged > 1 ) {
        $sr_no = ( $records_per_page * ( $paged -1 ) + 1);
    }
    $get_users_u = new WP_User_Query($users_filter);
    $get_users = $get_users_u->get_results();
    if(isset($_GET['msg']) && $_GET['msg'] != '') {
        $msg = $_GET['msg'];
    }
    if(isset($_GET['msg_class']) && $_GET['msg_class'] != '') {
        $msg_class = $_GET['msg_class'];
    }
    ?>
    <div class="wrap">
        <?php
        //Display success/error messages
            if( $msg != '' ) { ?>
                <div class="ublocker-notice <?php echo $msg_class; ?>">
                    <p><?php echo $msg; ?></p>
                </div>
        <?php } ?>
            <h2 class="ublocker-page-title"><?php _e( 'Block Users By Time', 'user-blocker') ?></h2>
            <div class="tab_parent_parent"><div class="tab_parent"><ul><li><a class="current" href="?page=block_user">Block User By Time</a></li><li><a href="?page=block_user_date">Block User By Date</a></li><li><a href="?page=block_user_permenant">Block User Permanent</a></li></ul></div></div>
            <div class="cover_form">
            <?php
            //Visible only if not set in edit mode
            //if( true ) {
            ?>
            
            <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                <div class="tablenav top">
                    <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                    <select name="cmbUserBy" id="cmbUserBy" onchange="changeUserBy();">
                        <option <?php echo selected($cmbUserBy, 'role'); ?> value="role" selected="selected">Role</option>
                        <option <?php echo selected($cmbUserBy, 'username'); ?> value="username">Username</option>
                    </select>
                    <?php //Pagination -top ?>
                    <div class="filter_div" style="float: right; <?php if( $display_users == 1 ) { echo 'display: block;'; } else { echo 'display: none;'; } ?>">
                        <div class="tablenav-pages" <?php if((int)$total_pages <= 1) { echo 'style="display: none;"'; } ?>>
                            <span class="displaying-num"><?php echo $total_items; ?> items</span>
                            <span class="pagination-links">
                                <a class="first-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=block_user&paged=1&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the first page">&laquo;</a>
                                <a class="prev-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=block_user&paged='.$prev_page.'&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the previous page">&lsaquo;</a>
                                <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page">
                                        of
                                        <span class="total-pages"><?php echo $total_pages; ?></span>
                                </span>
                                <a class="next-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=block_user&paged='.$next_page.'&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the next page">&rsaquo;</a>
                                <a class="last-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=block_user&paged='.$total_pages.'&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the last page">&raquo;</a>
                            </span>
                            <input style="display: none;" id="sbtPages" class="button" type="submit" value="sbtPages" name="filter_action">
                        </div><!-- .tablenav-pages -->
                    </div>
                </div>
                <div class="search_box">
                    <div class="actions">
                        <div class="filter_div" <?php if( $display_users == 1 ) { echo 'style="display: block;"'; } else echo 'style="display: none;"'; ?>>
                            <label><strong><?php _e( 'Select Role: ', 'user-blocker') ?></strong></label>
                            <select id="srole" name="srole" onchange="searchUser();">
                                <option value="">All Roles</option>
                                <?php if($get_roles) 
                                {
                                    foreach($get_roles as $key=>$value) {
                                        if( $key == 'administrator')
                                            continue;
                                        ?>
                                        <option <?php echo selected($key, $srole); ?> value="<?php echo $key; ?>"><?php echo ucfirst($value['name']); ?></option>
                                        <?php
                                     }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="actions">
                        <div class="filter_div" <?php if( $display_users == 1 ) { echo 'style="display: block;"'; } else echo 'style="display: none;"'; ?>>
                            <input type="hidden" value="block_user" name="page" />
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="username or email or first name" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="Search" name="filter_action">
                            <a class="button" href="<?php echo '?page=block_user&resetsearch=1'; ?>" style="margin-left: 10px;">Reset</a>
                        </div>
                    </div>
                </div>
            </form>
            <?php //} ?>
            <?php //Role Records ?>
            <form method="post" action="?page=block_user" id="frmBlockUser">
                <input id="hidden_cmbUserBy" type="hidden" name="cmbUserBy" value='<?php if( isset( $cmbUserBy ) && $cmbUserBy != '' ) echo $cmbUserBy; else echo 'role'; ?>'/>
                <input type="hidden" name="paged" value="<?php echo $paged; ?>"/>
                <input type="hidden" name="role" value="<?php echo $role; ?>" />
                <input type="hidden" name="srole" value="<?php echo $srole; ?>" />
                <input type="hidden" name="username" value="<?php echo $username; ?>" />
                <input type="hidden" name="txtUsername" value="<?php echo $txtUsername; ?>" />
                    <?php //if( true ) { ?>
            <table id="role" class="widefat post fixed user-records" <?php if( $display_users == 1 ) echo 'style="display: none;width: 100%;"'; else echo 'style="width: 100%;"'; ?>>
                <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <th class="user-role">Role</th>
                        <th class="th-time aligntextcenter">Block Time</th>
                        <th class="blk-msg aligntextcenter">Block Message</th>
                        <th class="tbl-action aligntextcenter">Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <th class="user-role">Role</th>
                        <th class="th-time aligntextcenter">Block Time</th>
                        <th class="blk-msg aligntextcenter">Block Message</th>
                        <th class="tbl-action aligntextcenter">Action</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                    $chkUserRole = array();
                    $is_checked = '';
                    if( isset($reocrd_id) && count($reocrd_id) > 0) {
                        $chkUserRole = $reocrd_id;
                    }
                    if($get_roles) {
                       foreach($get_roles as $key=>$value) {
                            if( $sr_no%2 == 0 )
                                $alt_class = 'alt';
                            else
                                $alt_class = '';
                            if( $key == 'administrator' || get_option($key.'_is_active')=='n')
                               continue;
                            if(in_array($key, $chkUserRole) ) {
                                $is_checked = 'checked="checked"';
                            }
                            else {
                                $is_checked = '';
                            }
                           ?>
                            <tr class="<?php echo $alt_class; ?>">
                                <td class="check-column"><input <?php echo $is_checked; ?> type="checkbox" value="<?php echo $key; ?>" name="chkUserRole[]" /></td>
                                <td class="user-role"><?php echo $value['name']; ?></td>
                                <td class="aligntextcenter">
                                    <?php
                                    $exists_block_day = '';
                                    $block_day = get_option($key.'_block_day');
                                    if(!empty($block_day)) {
                                        $exists_block_day = 'y'; ?>
                                        <a href='' class="view_block_data" data-href="view_block_data_<?php echo $sr_no; ?>" ><img src="<?php echo plugins_url(); ?>/user-blocker/images/view.png" alt="view" /></a>
                                    <?php } ?>
                                </td>
                                <td class="aligntextcenter">
                                    <?php echo disp_msg( get_option($key.'_block_msg_day') ); ?>
                                </td>
                                <td class="aligntextcenter"><a href="?page=block_user&role=<?php echo $key; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>"><img src="<?php echo plugins_url(); ?>/user-blocker/images/edit.png" alt="edit" /></a></td>
                            </tr>
                            <?php
                            if( $exists_block_day == 'y' ) { ?>
                                <tr class="view_block_data_tr" id="view_block_data_<?php echo $sr_no; ?>">
                                    <td colspan="5">
                                        <table class="view_block_table form-table tbl-timing">
                                            <thead>
                                                <tr>
                                                    <th align="center">Sunday</th>
                                                    <th align="center">Monday</th>
                                                    <th align="center">Tuesday</th>
                                                    <th align="center">Wednesday</th>
                                                    <th align="center">Thursday</th>
                                                    <th align="center">Friday</th>
                                                    <th align="center">Saturday</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td align="center">
                                                        <?php
                                                        if (array_key_exists('sunday',$block_day)) {
                                                            $from_time = $block_day['sunday']['from'];
                                                            $to_time = $block_day['sunday']['to'];
                                                            if( $from_time == '' ) {
                                                                echo 'not set';
                                                            }
                                                            else {
                                                                echo timeToTwelveHour($from_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo ' to '.timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo 'not set';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if (array_key_exists('monday',$block_day)) {
                                                            $from_time = $block_day['monday']['from'];
                                                            $to_time = $block_day['monday']['to'];
                                                            if( $from_time == '' ) {
                                                                echo 'not set';
                                                            }
                                                            else {
                                                                echo timeToTwelveHour($from_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo ' to '.timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo 'not set';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if (array_key_exists('tuesday',$block_day)) {
                                                            $from_time = $block_day['tuesday']['from'];
                                                            $to_time = $block_day['tuesday']['to'];
                                                            if( $from_time == '' ) {
                                                                echo 'not set';
                                                            }
                                                            else {
                                                                echo timeToTwelveHour($from_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo ' to '.timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo 'not set';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if (array_key_exists('wednesday',$block_day)) {
                                                            $from_time = $block_day['wednesday']['from'];
                                                            $to_time = $block_day['wednesday']['to'];
                                                            if( $from_time == '' ) {
                                                                echo 'not set';
                                                            }
                                                            else {
                                                                echo timeToTwelveHour($from_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo ' to '.timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo 'not set';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if (array_key_exists('thursday',$block_day)) {
                                                            $from_time = $block_day['thursday']['from'];
                                                            $to_time = $block_day['thursday']['to'];
                                                            if( $from_time == '' ) {
                                                                echo 'not set';
                                                            }
                                                            else {
                                                                echo timeToTwelveHour($from_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo ' to '.timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo 'not set';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if (array_key_exists('friday',$block_day)) {
                                                            $from_time = $block_day['friday']['from'];
                                                            $to_time = $block_day['friday']['to'];
                                                            if( $from_time == '' ) {
                                                                echo 'not set';
                                                            }
                                                            else {
                                                                echo timeToTwelveHour($from_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo ' to '.timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo 'not set';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if (array_key_exists('saturday',$block_day)) {
                                                            $from_time = $block_day['saturday']['from'];
                                                            $to_time = $block_day['saturday']['to'];
                                                            if( $from_time == '' ) {
                                                                echo 'not set';
                                                            }
                                                            else {
                                                                echo timeToTwelveHour($from_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo ' to '.timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo 'not set';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            <?php }
                            $sr_no++;
                        }
                     }
                     ?>
                </tbody>
            </table>
                <?php
            $chkUserUsername = array();
            $is_checked = '';
            if( isset($_POST['chkUserUsername']) && count($_POST['chkUserUsername']) > 0) {
                $chkUserUsername = $_POST['chkUserUsername'];
            }
            ?>
            <table id="username" class="widefat post fixed user-records" <?php if($display_users == 1 ) echo 'style="display: table;"'; else echo 'style="display: none;"'; ?>>
                <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <?php
                        $linkOrder = 'ASC';
                        if( isset($order) ) {
                            if( $order == 'ASC' ) {
                                $linkOrder = 'DESC';
                            }
                            else if( $order == 'DESC' ) {
                                $linkOrder = 'ASC';
                            }
                        }
                        ?>
                        <th class="th-username sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Username</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-name sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Name</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-email sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Email</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-role">Role</th>
                        <th class="th-time aligntextcenter">Block Time</th>
                        <th class="blk-msg aligntextcenter">Block Message</th>
                        <th class="tbl-action aligntextcenter">Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <?php
                        $linkOrder = 'ASC';
                        if( isset($order) ) {
                            if( $order == 'ASC' ) {
                                $linkOrder = 'DESC';
                            }
                            else if( $order == 'DESC' ) {
                                $linkOrder = 'ASC';
                            }
                        }
                        ?>
                        <th class="th-username sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Username</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-name sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Name</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-email sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Email</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-role">Role</th>
                        <th class="th-time aligntextcenter">Block Time</th>
                        <th class="blk-msg aligntextcenter">Block Message</th>
                        <th class="tbl-action">Action</th>
                    </tr>
                </tfoot>
                <tbody>
            <?php
            $chkUserRole = array();
            $is_checked = '';
            if( isset($reocrd_id) && count($reocrd_id) > 0) {
                $chkUserRole = $reocrd_id;
            }
            if($get_users) {
                $d = 1;
                foreach($get_users as $user) {
                   if( $d%2 == 0 )
                       $alt_class = 'alt';
                   else
                       $alt_class = '';
                    if(in_array($user->ID, $chkUserRole) ) {
                        $is_checked = 'checked="checked"';
                    }
                    else {
                        $is_checked = '';
                    }
                   ?>
                        <tr class="<?php echo $alt_class; ?>">
                            <td class="check-column"><input <?php echo $is_checked; ?> type="checkbox" value="<?php echo $user->ID; ?>" name="chkUserUsername[]" /></td>
                            <td><?php echo $user->user_login; ?></td>
                            <td><?php echo $user->display_name; ?></td>
                            <td><?php echo $user->user_email; ?></td>
                            <td><?php echo ucfirst( str_replace('_', ' ', $user->roles[0]) ); ?></td>
                            <td class="aligntextcenter">
                                <?php
                                $exists_block_day = '';
                                $block_day = get_user_meta($user->ID,'block_day',true);
                                if(!empty($block_day)) {
                                    $exists_block_day = 'y'; ?>
                                    <a href='' class="view_block_data" data-href="view_block_data_<?php echo $d; ?>" ><img src="<?php echo plugins_url(); ?>/user-blocker/images/view.png" alt="view" /></a>
                                <?php } ?>
                            </td>
                            <td class="aligntextcenter">
                                <?php echo disp_msg( get_user_meta($user->ID,'block_msg_day',true) ); ?>
                            </td>
                            <td class="aligntextcenter"><a href="?page=block_user&username=<?php echo $user->ID; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>"><img src="<?php echo plugins_url(); ?>/user-blocker/images/edit.png" alt="edit" /></a></td>
                        </tr>
                        <?php
                        if( $exists_block_day == 'y' ) { ?>
                            <tr class="view_block_data_tr" id="view_block_data_<?php echo $d; ?>">
                                <td colspan="8">
                                    <table class="view_block_table form-table tbl-timing">
                                        <thead>
                                            <tr>
                                                <th align="center">Sunday</th>
                                                <th align="center">Monday</th>
                                                <th align="center">Tuesday</th>
                                                <th align="center">Wednesday</th>
                                                <th align="center">Thursday</th>
                                                <th align="center">Friday</th>
                                                <th align="center">Saturday</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td align="center">
                                                    <?php
                                                    if (array_key_exists('sunday',$block_day)) {
                                                        $from_time = $block_day['sunday']['from'];
                                                        $to_time = $block_day['sunday']['to'];
                                                        if( $from_time == '' ) {
                                                            echo 'not set';
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                            echo ' to '.timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                        echo 'not set';
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php
                                                    if (array_key_exists('monday',$block_day)) {
                                                        $from_time = $block_day['monday']['from'];
                                                        $to_time = $block_day['monday']['to'];
                                                        if( $from_time == '' ) {
                                                            echo 'not set';
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                            echo ' to '.timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                        echo 'not set';
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php
                                                    if (array_key_exists('tuesday',$block_day)) {
                                                        $from_time = $block_day['tuesday']['from'];
                                                        $to_time = $block_day['tuesday']['to'];
                                                        if( $from_time == '' ) {
                                                            echo 'not set';
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                            echo ' to '.timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                        echo 'not set';
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php
                                                    if (array_key_exists('wednesday',$block_day)) {
                                                        $from_time = $block_day['wednesday']['from'];
                                                        $to_time = $block_day['wednesday']['to'];
                                                        if( $from_time == '' ) {
                                                            echo 'not set';
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                            echo ' to '.timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                        echo 'not set';
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php
                                                    if (array_key_exists('thursday',$block_day)) {
                                                        $from_time = $block_day['thursday']['from'];
                                                        $to_time = $block_day['thursday']['to'];
                                                        if( $from_time == '' ) {
                                                            echo 'not set';
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                            echo ' to '.timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                        echo 'not set';
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php
                                                    if (array_key_exists('friday',$block_day)) {
                                                        $from_time = $block_day['friday']['from'];
                                                        $to_time = $block_day['friday']['to'];
                                                        if( $from_time == '' ) {
                                                            echo 'not set';
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                            echo ' to '.timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                        echo 'not set';
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php
                                                    if (array_key_exists('saturday',$block_day)) {
                                                        $from_time = $block_day['saturday']['from'];
                                                        $to_time = $block_day['saturday']['to'];
                                                        if( $from_time == '' ) {
                                                            echo 'not set';
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                            echo ' to '.timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                        echo 'not set';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        <?php } ?>
                <?php
                        $d++;
                        $sr_no++;
                    }
                }//End $get_users
                else {
                    echo '<tr><td colspan="8" align="center">No records found.</td></tr>';
                }
            ?>
                </tbody>
            </table>
            <?php
              //}
            $role_name = '';
            if( isset($_GET['role']) && $_GET['role'] != '' ) {
                if( $GLOBALS['wp_roles']->is_role( $_GET['role'] ) ) {
                    $role_name = ' For <span style="text-transform: capitalize;">' . str_replace('_', ' ', $_GET['role']) . '</span>';
                }
            }
            if( isset($_GET['username']) && $_GET['username'] != '' ) {
                if( get_userdata($_GET['username']) != false ) {
                    $user_info = get_userdata($_GET['username']);
                    $role_name = ' For ' . $user_info->user_login;
                }
            }
            ?>
            <?php // Time List ?>
            <table class="form-table tbl-timing">
                <tr class="tr_head">
                    <td style="border: 0;" colspan="20">
                        <h3 class="block_msg_title">Block Time <?php if( isset($curr_edit_msg) && $curr_edit_msg != '' ) echo '<span>' . $curr_edit_msg . '</span>'; ?></h3>
                    </td>
                </tr>
                <tr>
                    <th class="week-lbl">Sunday<input type="button" class="button primary-button" id="chkapply" value="Apply to all"/></th>
                    <th class="week-lbl">Monday</th>
                    <th class="week-lbl">Tuesday</th>
                    <th class="week-lbl">Wednesday</th>
                    <th class="week-lbl">Thursday</th>
                    <th class="week-lbl">Friday</th>
                    <th class="week-lbl">Saturday</th>
                </tr>
                <tr>
                    <td class="week-time" id="week-sun" align="center">
                        <input tabindex="1" value="<?php echo timeToTwelveHour($txtSunFrom); ?>" class="time start time-field" type="text" name="txtSunFrom" id="txtSunFrom" />
                        <span>&nbsp;to&nbsp;</span>
                        <input tabindex="2" value="<?php echo timeToTwelveHour($txtSunTo); ?>" class="time end time-field" type="text" name="txtSunTo" id="txtSunTo" />
<!--                        <input type="checkbox" class="chkapply" id="chkapply" />-->
                    </td>
                    <td class="week-time" id="week-mon" align="center">
                        <input tabindex="3" value="<?php echo timeToTwelveHour($txtMonFrom); ?>" class="time start time-field" type="text" name="txtMonFrom" id="txtMonFrom" />
                        <span>&nbsp;to&nbsp;</span>
                        <input tabindex="4" value="<?php echo timeToTwelveHour($txtMonTo); ?>" class="time end time-field" type="text" name="txtMonTo" id="txtMonTo" />
                    </td>
                    <td class="week-time" id="week-tue" align="center">
                        <input tabindex="5" value="<?php echo timeToTwelveHour($txtTueFrom); ?>" class="time start time-field" type="text" name="txtTueFrom" id="txtTueFrom" />
                        <span>&nbsp;to&nbsp;</span>
                        <input tabindex="6" value="<?php echo timeToTwelveHour($txtTueTo); ?>" class="time end time-field" type="text" name="txtTueTo" id="txtTueTo" />
                    </td>
                    <td class="week-time" id="week-wed" align="center">
                        <input tabindex="7" value="<?php echo timeToTwelveHour($txtWedFrom); ?>" class="time start time-field" type="text" name="txtWedFrom" id="txtWedFrom" />
                        <span>&nbsp;to&nbsp;</span>
                        <input tabindex="8" value="<?php echo timeToTwelveHour($txtWedTo); ?>" class="time end time-field" type="text" name="txtWedTo" id="txtWedTo" />
                    </td>
                    <td class="week-time" id="week-thu" align="center">
                        <input tabindex="9" value="<?php echo timeToTwelveHour($txtThuFrom); ?>" class="time start time-field" type="text" name="txtThuFrom" id="txtThuFrom" />
                        <span>&nbsp;to&nbsp;</span>
                        <input tabindex="10" value="<?php echo timeToTwelveHour($txtThuTo); ?>" class="time end time-field" type="text" name="txtThuTo" id="txtThuTo" />
                    </td>
                    <td class="week-time" id="week-fri" align="center">
                        <input tabindex="11" value="<?php echo timeToTwelveHour($txtFriFrom); ?>" class="time start time-field" type="text" name="txtFriFrom" id="txtFriFrom" />
                        <span>&nbsp;to&nbsp;</span>
                        <input tabindex="12" value="<?php echo timeToTwelveHour($txtFriTo); ?>" class="time end time-field" type="text" name="txtFriTo" id="txtFriTo" />
                    </td>
                    <td class="week-time" id="week-sat" align="center">
                        <input tabindex="13" value="<?php echo timeToTwelveHour($txtSatFrom); ?>" class="time start time-field" type="text" name="txtSatFrom" id="txtSatFrom" />
                        <span>&nbsp;to&nbsp;</span>
                        <input tabindex="14" value="<?php echo timeToTwelveHour($txtSatTo); ?>" class="time end time-field" type="text" name="txtSatTo" id="txtSatTo" />
                    </td>
                </tr>               
            </table>
            
            <h3 class="block_msg_title">Block Message</h3>
            <div class="block_msg_div">
                <div class="block_msg_left">
                    <textarea style="width:500px;height: 45px" name="block_msg_day"><?php echo stripslashes($block_msg_day); ?></textarea>
                </div>
                <div class="block_msg_note_div">
                    Note: If you will not type message, default message will be '<?php echo $default_msg; ?>'.
                </div>
            </div>
            <?php
            if( $cmbUserBy == 'role' || $cmbUserBy == '' ) {
                $btnVal = str_replace('User', 'Role', $btnVal);
            }
            ?>
            <input id="sbt-block" style="margin: 20px 0 0 0;clear: both;float: left" class="button button-primary" type="submit" name="sbtSaveTime" value="<?php echo $btnVal; ?>">
            <?php if( isset( $btnVal ) && ( $btnVal == 'Update Blocked User' || $btnVal == 'Update Blocked Role' ) ) { ?>
            <a style="margin: 20px 0 0 10px;float: left;" href="<?php echo '?page=block_user'; ?>" class="button button-primary">Cancel</a>
            <?php } ?>
            </form>
            </div>
    </div>
<?php
}

function block_user_date_page() {
    global $wpdb;
    $default_msg = 'You are temporary blocked.';
    $sr_no = 1;
    $records_per_page = 10;
    $msg_class = '';
    $msg = '';
    $curr_edit_msg = '';
    $btnVal = 'Block User';
    $reocrd_id = array();
    $paged = 1;
    $orderby = 'user_login';
    $order = 'ASC';
    $display_users = 0;
    $is_display_role = 0;
    $username = '';
    $srole= '';
    $role = '';
    if(get_data('paged') != '') {
        $display_users = 1;
        $paged = get_data('paged',1);
    }
    if(!is_numeric($paged))
        $paged = 1;
    if( isset( $_REQUEST['filter_action'] ) ) {
        if( $_REQUEST['filter_action'] == 'Search' ) {
            $paged = 1;
        }
    }
    
    if( isset($_GET['orderby']) && $_GET['orderby'] != '' )
        $orderby = $_GET['orderby'];
    if( isset($_GET['order']) && $_GET['order'] != '' )
        $order = $_GET['order'];
    $offset = ($paged-1)*$records_per_page;
    $option_name = array();
    $block_msg_date = '';
    global $wp_roles;
    $get_roles = $wp_roles->roles;
    $frmdate = '';
    $todate = '';
    if( get_data('role') != '' ) {
        $reocrd_id = array( get_data('role') );
        $role = get_data('role');
        $btn_name = 'editTime';
        $btnVal = 'Update Blocked User';
        $is_display_role = 1;
        if( $GLOBALS['wp_roles']->is_role( get_data('role') ) ) {
            $block_date = get_option( get_data('role') . '_block_date' );
            $frmdate = $block_date['frmdate'];
            $todate = $block_date['todate'];
            $block_msg_date = get_option(get_data('role') . '_block_msg_date');
            $curr_edit_msg = 'Update for role: ' .$GLOBALS['wp_roles']->roles[get_data('role')]['name'];
        }
        else {
            $msg_class = 'error';
            $msg = 'Role ' . get_data('role') . ' is not exist.';
        }
    }
    if( get_data('username') != '' ) {
        $reocrd_id = array( get_data('username') );
        $username = get_data('username');
        $btn_name = 'editTime';
        $btnVal = 'Update Blocked User';
        if( get_userdata(get_data('username')) != false ) {
            $block_date = get_user_meta( get_data('username'), 'block_date', true );
            if($block_date != '' && !empty($block_date))
            {
                $frmdate = $block_date['frmdate'];
                $todate = $block_date['todate'];
            }
            $block_msg_date = get_user_meta( get_data('username'), 'block_msg_date', true );
            $user_data = new WP_User( get_data('username') );
            $curr_edit_msg = 'Update for user with username: ' .$user_data->user_login;
        }
        else {
            $msg_class = 'error';
            $msg = 'User with ' . get_data('username') . ' userid is not exist.';
        }
    }
    if(isset($_POST['sbtSaveDate'])) {
        $frmdate = $_POST['frmdate'];
        $todate = $_POST['todate'];
        //Check if username is selected in dd
        if( isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'role' ) {
            $is_display_role = 1;
        }
        if( isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'username' ) {
            $display_users = 1;
        }
        if( $frmdate != '' && $todate != '' && ( strtotime($frmdate) <= strtotime($todate) ) ) {
            //Validation for fromdate to todate
            if((get_data('role')!= '') || (get_data('username') != '') ) {
                //Edit record in date wise blocking
                if(get_data('role')!= '') {
                    $block_date['frmdate'] = $_POST['frmdate'];
                    $block_date['todate'] = $_POST['todate'];
                    $old_block_date = get_option(get_data('role') . '_block_date');
                    $old_block_msg_date = get_option(get_data('role') . '_block_msg_date');
                    update_option(get_data('role') . '_block_date',$block_date );
                    $block_msg_date = $default_msg;
                    if(trim( $_POST['block_msg_date'] ) != '')
                    {
                        $block_msg_date = trim( $_POST['block_msg_date'] );
                    }
                    update_option(get_data('role') . '_block_msg_date', $block_msg_date);
                    //Update all users of this role
                    block_role_users_date( get_data('role'),$old_block_date, $block_date,$old_block_msg_date, $block_msg_date );
                    //Update all users of this role end
                    $role_name = str_replace('_', ' ', get_data('role'));
                    $msg_class = 'updated';
                    $msg = $GLOBALS['wp_roles']->roles[get_data('role')]['name'] . '\'s date wise blocking have been updated successfully';
                    $frmdate = $todate = $block_msg_date = '';
                    $role = '';
                    $reocrd_id = array();
                }
                else if(get_data('username') != '')
                {
                    $block_date['frmdate'] = $_POST['frmdate'];
                    $block_date['todate'] = $_POST['todate'];
                    $block_msg_date = $default_msg;
                    if(trim( $_POST['block_msg_date'] ) != '')
                    {
                        $block_msg_date = trim( $_POST['block_msg_date'] );
                    }
                    update_user_meta(get_data('username'), 'block_date', $block_date);
                    update_user_meta(get_data('username'), 'block_msg_date', $block_msg_date);
                    $user_info = get_userdata(get_data('username'));
                    $role_name = $user_info->user_login;
                    $msg_class = 'updated';
                    $msg = $role_name . '\'s date wise blocking have been updated successfully';
                    $frmdate = $todate = $block_msg_date = '';
                    $username = '';
                    $reocrd_id = array();
                }
                $curr_edit_msg = '';
                $btnVal = 'Block User';
            }
            else
            {
                //Add record in date wise blocking
                if(isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'role')
                {
                    if( isset($_POST['chkUserRole']) ) {
                        $reocrd_id = $_POST['chkUserRole'];
                        $block_msg_date = $default_msg;
                        if(trim( $_POST['block_msg_date'] ) != '')
                        {
                            $block_msg_date = trim( $_POST['block_msg_date'] );
                        }
                        while (list ($key,$val) = @each ($reocrd_id)) {
                            $block_date['frmdate'] = $_POST['frmdate'];
                            $block_date['todate'] = $_POST['todate'];
                            $old_block_date = get_option($val . '_block_date');
                            $old_block_msg_date = get_option($val . '_block_msg_date');
                            update_option($val . '_block_date',$block_date);
                            update_option($val . '_block_msg_date',$block_msg_date );
                            //Update all users of this role
                            block_role_users_date( $val,$old_block_date, $block_date,$old_block_msg_date, $block_msg_date );
                            //Update all users of this role end                  
                        }
                        $msg_class = 'updated';
                        $msg = 'Selected roles have beeen blocked succeefully.';
                        $frmdate = $todate = $block_msg_date = '';
                    }
                    else {
                        $block_msg_date = trim( $_POST['block_msg_date'] );
                        $msg_class = 'error';
                        $msg = 'Please select atleast one role.';
                    }

                }
                else if(isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'username')
                {
                    if( isset($_POST['chkUserUsername']) ) {
                        $reocrd_id = $_POST['chkUserUsername'];
                        
                        if(trim( $_POST['block_msg_date'] ) != '')
                        {
                            $block_msg_date = trim( $_POST['block_msg_date'] );
                        }
                        while (list ($key,$val) = @each ($reocrd_id)) {
                            $block_msg_date = $default_msg;
                            $block_date['frmdate'] = $_POST['frmdate'];
                            $block_date['todate'] = $_POST['todate'];
                            update_user_meta($val, 'block_date', $block_date);
                            update_user_meta($val, 'block_msg_date', $block_msg_date);
                        }
                        $msg_class = 'updated';
                        $msg = 'Selected users have beeen blocked succeefully.';
                        $frmdate = $todate = $block_msg_date = '';
                    }
                    else {
                        $block_msg_date = trim( $_POST['block_msg_date'] );
                        $msg_class = 'error';
                        $msg = 'Please select atleast one username.';
                    }
                }
                $btnVal = 'Block User';
                $reocrd_id = array();
            }   //database update for add and edit end
        }
        else {
            $msg_class = 'error';
            $msg = 'Please enter valid block date.';
            $block_msg_date = trim( $_POST['block_msg_date'] );
            $get_cmb_val = $_POST['cmbUserBy'];
            if( $get_cmb_val == 'role' ) {
                if( isset( $_POST['chkUserRole'] ) ) {
                    $reocrd_id = $_POST['chkUserRole'];
                }
            }
            else if( $get_cmb_val == 'username' ) {
                if( isset( $_POST['chkUserUsername'] ) ) {
                    $reocrd_id = $_POST['chkUserUsername'];
                }
            }
        }
    }
    
    $user_query = get_users( array( 'role' => 'administrator' ) );
    $admin_id = wp_list_pluck( $user_query, 'ID' );
    $inactive_users = get_users( array(  'meta_query' => array(
	'relation' => 'AND',
		array(
			'key' => 'wp_capabilities',
			'value' => '',
			'compare' => '!=',
		),
		array(
			'key' => 'is_active',
			'value' => 'n',
			'compare' => '=',
		)
	) ) );
    $inactive_id = wp_list_pluck( $inactive_users, 'ID' );
    $exclude_id = array_unique( array_merge($admin_id, $inactive_id) );
    $users_filter = array( 'exclude' => $exclude_id );
    //Start searching
    if(get_data('txtUsername') != '') {
        $display_users = 1;
        $txtUsername = get_data('txtUsername');
        $users_filter['search'] = '*'.esc_attr($txtUsername).'*';
        $users_filter['search_columns'] = array(
            'user_login',
            'user_nicename',
            'user_email',
            'display_name'
        );
    }
    if( $txtUsername == '' ) {
        if(get_data('srole') != '') {
            $display_users = 1;
            $users_filter['role'] = get_data('srole');
            $srole = get_data('srole');
        }
    }
    if(get_data('username')!='')
    {
        $display_users = 1;
    }
    if( $is_display_role == 1 ) {
        $display_users = 0;
    }
    //if order and order by set, display users
    if( isset($_GET['orderby']) && $_GET['orderby'] != '' && isset($_GET['order']) && $_GET['order'] != ''  ) {
        $display_users = 1;
    }
    //Select usermode on reset searching
    if( isset($_GET['resetsearch']) && $_GET['resetsearch'] == '1' ) {
        $display_users = 1;
    }
    if( $display_users == 1 ) {
        $cmbUserBy = 'username';
    }
    //end
    //Query to get total users
    $users_filter['orderby'] = $orderby;
    $users_filter['order'] = $order;
    $get_users_u1 = new WP_User_Query($users_filter);
    $total_items = $get_users_u1->total_users;
    $total_pages = ceil($total_items/$records_per_page);
		
    $next_page = (int)$paged+1;
    if($next_page > $total_pages)
            $next_page = $total_pages;
    $users_filter['number'] = $records_per_page;
    $users_filter['offset'] = $offset;

    $prev_page = (int)$paged-1;
    if($prev_page < 1)
            $prev_page = 1;
    
    $sr_no = 1;
    if( isset( $paged ) && $paged > 1 ) {
        $sr_no = ( $records_per_page * ( $paged -1 ) + 1);
    }
    //Main Query to display users
    $get_users_u = new WP_User_Query($users_filter);
    $get_users = $get_users_u->get_results();
    if(isset($_GET['msg']) && $_GET['msg'] != '') {
        $msg = $_GET['msg'];
    }
    if(isset($_GET['msg_class']) && $_GET['msg_class'] != '') {
        $msg_class = $_GET['msg_class'];
    }
    ?>
    <div class="wrap">
        <?php
        //Display success/error messages
            if( $msg != '' ) { ?>
                <div class="ublocker-notice <?php echo $msg_class; ?>">
                    <p><?php echo $msg; ?></p>
                </div>
        <?php }  ?>
            <h2 class="ublocker-page-title"><?php _e( 'Block Users By Date', 'user-blocker') ?></h2>
            <div class="tab_parent_parent"><div class="tab_parent"><ul><li><a href="?page=block_user">Block User By Time</a></li><li><a class="current" href="?page=block_user_date">Block User By Date</a></li><li><a href="?page=block_user_permenant">Block User Permanent</a></li></ul></div></div>
            <div class="cover_form">
            <?php
            //Visible only if not set in edit mode
         //   if( true ) {
            ?>
            
            <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                <div class="tablenav top">
                    <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                    <select name="cmbUserBy" id="cmbUserBy" onchange="changeUserBy();">
                        <option <?php echo selected($cmbUserBy, 'role'); ?> value="role" selected="selected">Role</option>
                        <option <?php echo selected($cmbUserBy, 'username'); ?> value="username">Username</option>
                    </select>
                    <?php //Pagination -top ?>
                    <div class="filter_div" style="float: right; <?php if( $display_users == 1 ) { echo 'display: block;'; } else { echo 'display: none;'; } ?>">
                        <div class="tablenav-pages" <?php if((int)$total_pages <= 1) { echo 'style="display: none;"'; } ?>>
                            <span class="displaying-num"><?php echo $total_items; ?> items</span>
                            <span class="pagination-links">
                                <a class="first-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=block_user_date&paged=1&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the first page">&laquo;</a>
                                <a class="prev-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=block_user_date&paged='.$prev_page.'&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the previous page">&lsaquo;</a>
                                <span class="paging-input">
                                        <input class="current-page" id="current_page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page">
                                        of
                                        <span class="total-pages"><?php echo $total_pages; ?></span>
                                </span>
                                <a class="next-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=block_user_date&paged='.$next_page.'&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the next page">&rsaquo;</a>
                                <a class="last-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=block_user_date&paged='.$total_pages.'&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the last page">&raquo;</a>
                            </span>
                            <input style="display: none;" id="sbtPages" class="button" type="submit" value="sbtPages" name="filter_action">
                        </div><!-- .tablenav-pages -->
                    </div>
                </div>
                <div class="search_box">
                    <div class="actions">
                        <div class="filter_div" <?php if( $display_users == 1 ) { echo 'style="display: block;"'; } else echo 'style="display: none;"'; ?>>
                            <label><strong><?php _e( 'Select Role: ', 'user-blocker') ?></strong></label>
                            <select id="srole" name="srole" onchange="searchUser();">
                                <option value="">All Roles</option>
                                <?php if($get_roles) 
                                {
                                    foreach($get_roles as $key=>$value) {
                                        if( $key == 'administrator')
                                            continue;
                                        ?>
                                        <option <?php echo selected($key, $srole); ?> value="<?php echo $key; ?>"><?php echo ucfirst($value['name']); ?></option>
                                        <?php
                                     }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="actions">
                        <div class="filter_div" <?php if( $display_users == 1 ) { echo 'style="display: block;"'; } else echo 'style="display: none;"'; ?>>
                            <input type="hidden" value="block_user_date" name="page" />
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="username or email or first name" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="Search" name="filter_action">
                            <a class="button" href="<?php echo '?page=block_user_date&resetsearch=1'; ?>" style="margin-left: 10px;">Reset</a>
                        </div>
                    </div>
                </div>
            </form>
            <?php //} ?>
            <?php //Role Records ?>
            <form method="post" action="?page=block_user_date" id="frmBlockUser">
                <input type="hidden" id='hidden_cmbUserBy' name="cmbUserBy" value='<?php if( isset( $cmbUserBy ) && $cmbUserBy != '' ) echo $cmbUserBy; else echo 'role'; ?>'/>
                <input type="hidden" name="paged" value="<?php echo $paged; ?>"/>
                <input type="hidden" name="srole" value="<?php echo $srole; ?>" />
                <input type="hidden" name="role" value="<?php echo $role; ?>" />
                <input type="hidden" name="username" value="<?php echo $username; ?>" />
                <input type="hidden" name="txtUsername" value="<?php echo $txtUsername; ?>" />
            <?php //if( !isset($_GET['role']) && !isset($_GET['username']) ) { ?>
            <table id="role" class="widefat post fixed user-records" <?php if( $display_users == 1 ) echo 'style="display: none;width: 100%;"'; else echo 'style="width: 100%;"'; ?>>
                <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <th class="user-role">Role</th>
                        <th class="blk-date">Block Date</th>
                        <th class="blk-msg aligntextcenter">Block Message</th>
                        <th class="tbl-action">Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <th class="user-role">Role</th>
                        <th class="blk-date">Block Date</th>
                        <th class="blk-msg aligntextcenter">Block Message</th>
                        <th class="tbl-action">Action</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                    $chkUserRole = array();
                    $is_checked = '';
                    if( isset($reocrd_id) && count($reocrd_id) > 0) {
                        $chkUserRole = $reocrd_id;
                    }
                    if($get_roles) {
                       $p_txtUsername = $_GET['txtUsername'];
                       $p_srole = $_GET['srole'];
                       $p_paged = $_GET['paged'];
                       foreach($get_roles as $key=>$value) {
                            if( $sr_no%2 == 0 )
                                $alt_class = 'alt';
                            else
                                $alt_class = '';
                            if( $key == 'administrator' || get_option($key.'_is_active')=='n')
                               continue;
                            if(in_array($key, $chkUserRole) ) {
                                $is_checked = 'checked="checked"';
                            }
                            else {
                                $is_checked = '';
                            }
                           ?>
                            <tr class="<?php echo $alt_class; ?>">
                                <td class="check-column"><input <?php echo $is_checked; ?> type="checkbox" value="<?php echo $key; ?>" name="chkUserRole[]" /></td>
                                <td><?php echo $value['name']; ?></td>
                                <td>
                                <?php
                                    $block_date = get_option($key.'_block_date');
                                    if($block_date != '' && !empty($block_date))
                                    {
                                        $frmdate1 = $block_date['frmdate'];
                                        $todate1 = $block_date['todate'];                                    
                                        echo $frmdate1.' to '.$todate1;
                                    }
                                    else {
                                        echo 'not set';
                                    }
                                    ?>
                                </td>
                                <td class="aligntextcenter">
                                    <?php echo disp_msg( get_option($key.'_block_msg_date') ); ?>
                                </td>
                                <td class="aligntextcenter"><a href="?page=block_user_date&role=<?php echo $key; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>"><img src="<?php echo plugins_url(); ?>/user-blocker/images/edit.png" alt="edit" /></a></td>
                            </tr>
                    <?php
                            $sr_no++;
                        }
                     }
                     else {
                         echo '<tr><td colspan="5"  align="center">No records found.</td></tr>';
                     }
                     ?>
                </tbody>
            </table>
            <?php
            $chkUserUsername = array();
            $is_checked = '';
            if( isset($_POST['chkUserUsername']) && count($_POST['chkUserUsername']) > 0) {
                $chkUserUsername = $_POST['chkUserUsername'];
            }
            ?>
            <table id="username" class="widefat post fixed user-records" <?php if($display_users == 1 ) echo 'style="display: table;"'; else echo 'style="display: none;"'; ?>>
                <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <?php
                        $linkOrder = 'ASC';
                        if( isset($order) ) {
                            if( $order == 'ASC' ) {
                                $linkOrder = 'DESC';
                            }
                            else if( $order == 'DESC' ) {
                                $linkOrder = 'ASC';
                            }
                        }
                        ?>
                        <th class="th-username sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_date&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Username</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-name sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_date&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Name</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-email sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_date&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Email</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-role">Role</th>
                        <th class="blk-date">Block Date</th>
                        <th class="blk-msg aligntextcenter">Block Message</th>
                        <th class="tbl-action">Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <?php
                        $linkOrder = 'ASC';
                        if( isset($order) ) {
                            if( $order == 'ASC' ) {
                                $linkOrder = 'DESC';
                            }
                            else if( $order == 'DESC' ) {
                                $linkOrder = 'ASC';
                            }
                        }
                        ?>
                        <th class="th-username sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_date&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Username</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-name sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_date&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Name</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-email sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_date&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Email</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-role">Role</th>
                        <th class="blk-date">Block Date</th>
                        <th class="blk-msg aligntextcenter">Block Message</th>
                        <th class="tbl-action">Action</th>
                    </tr>
                </tfoot>
                <tbody>
                <?php
                $chkUserRole = array();
                $is_checked = '';
                if( isset($reocrd_id) && count($reocrd_id) > 0) {
                    $chkUserRole = $reocrd_id;
                }
                if($get_users) {
                    $p_txtUsername = $_GET['txtUsername'];
                    $p_srole = $_GET['srole'];
                    $p_paged = $_GET['paged'];
                    $d = 1;
                    foreach($get_users as $user) {
                        if( $d%2 == 0 )
                            $alt_class = 'alt';
                        else
                           $alt_class = '';
                        if(in_array($user->ID, $chkUserRole) ) {
                            $is_checked = 'checked="checked"';
                        }
                        else {
                            $is_checked = '';
                        }
                       ?>
                        <tr class="<?php echo $alt_class; ?>">
                            <td class="check-column"><input <?php echo $is_checked; ?> type="checkbox" value="<?php echo $user->ID; ?>" name="chkUserUsername[]" /></td>
                            <td><?php echo $user->user_login; ?></td>
                            <td><?php echo $user->display_name; ?></td>
                            <td><?php echo $user->user_email; ?></td>
                            <td><?php echo ucfirst( str_replace('_', ' ', $user->roles[0]) ); ?></td>
                            <td>
                                <?php
                                $block_date = get_user_meta($user->ID,'block_date',true);
                                if($block_date != '')
                                {
                                    $frmdate1 = $block_date['frmdate'];
                                    $todate1 = $block_date['todate'];
                                    echo $frmdate1.' to '.$todate1;
                                }
                                else {
                                    echo 'not set';
                                }
                                ?>
                            </td>
                            <td class="aligntextcenter">
                                <?php echo disp_msg( get_user_meta($user->ID,'block_msg_date',true) ); ?>
                            </td>
                            <td class="aligntextcenter"><a href="?page=block_user_date&username=<?php echo $user->ID; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>"><img src="<?php echo plugins_url(); ?>/user-blocker/images/edit.png" alt="edit" /></a></td>
                        </tr>
                    <?php
                        $d++;
                    }
                }//End $get_users
                else {
                    echo '<tr><td colspan="8"  align="center">No records found.</td></tr>';
                }
                ?>
                </tbody>
            </table>
            <h3 class="block_msg_title">Block Date <?php if( isset($curr_edit_msg) && $curr_edit_msg != '' ) echo '<span>' . $curr_edit_msg . '</span>'; ?></h3>
            <?php if( isset( $btnVal ) && $btnVal == 'Update Blocked User' ) {
               $block_day = get_user_meta($_GET['username'], 'block_day', true);
               if( $block_day != '' && $block_day != 0 ) {
                    echo '<div style="width: 990px; clear: both;">';
                    echo '<span style="display: block; padding: 5px 0;">This user is blocked for below time:</span>';
                    echo '<div class="day-table">';
                    display_block_time( 'sunday', $block_day );
                    display_block_time( 'monday', $block_day );
                    display_block_time( 'tuesday', $block_day );
                    display_block_time( 'wednesday', $block_day );
                    display_block_time( 'thursday', $block_day );
                    display_block_time( 'friday', $block_day );
                    display_block_time( 'saturday', $block_day );
                    echo '</div>';
                    echo '</div>';
               }
            } ?>
            <div class="block_msg_div">
                <table class="form-table tbl-timing">
                    <tbody>
                        <tr>
                            <td style="padding: 15px;">From <input type="text" name="frmdate" value="<?php echo $frmdate; ?>" id="frmdate" /> To <input type="text" name="todate" value="<?php echo $todate; ?>" id="todate" /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <h3 class="block_msg_title">Block Message</h3>
            <div class="block_msg_div">
                <div class="block_msg_left">
                    <textarea style="width:500px;height: 45px" name="block_msg_date"><?php echo stripslashes( $block_msg_date ); ?></textarea>
                </div>
                <div class="block_msg_note_div">
                    Note: If you will not type message, default message will be 'You are temporary blocked.'.                 
                </div>
            </div>
            <?php
            if( $cmbUserBy == 'role' || $cmbUserBy == '' ) {
                $btnVal = str_replace('User', 'Role', $btnVal);
            }
            ?>
            <input id="sbt-block" style="margin: 20px 0 0 0;clear: both;float: left;" class="button button-primary" type="submit" name="sbtSaveDate" value="<?php echo $btnVal; ?>">
            <?php if( isset( $btnVal ) && $btnVal == 'Update Blocked User' ) { ?>
            <a style="margin: 20px 0 0 10px;float: left;" href="<?php echo '?page=block_user_date'; ?>" class="button button-primary">Cancel</a>
            <?php } ?>
            </form>
        </div>
    </div>
<?php
}

function block_user_permenant_page() {
    global $wpdb;
    $sr_no = 1;
    $records_per_page = 10;
    $msg_class = '';
    $msg = '';
    $curr_edit_msg = '';
    $btnVal = 'Block User';
    $option_name = array();
    $block_time_array = array();
    $reocrd_id = array();
    $is_active = 1;
    $block_msg_permenant = '';
    $block_msg = '';
    $paged = 1;
    $orderby = 'user_login';
    $order = 'ASC';
    $display_users = 0;
    $is_display_role= 0;
    $srole = '';
    $role = '';
    $username = '';
    $default_msg = 'You are permanently Blocked.';
    if(get_data('paged') != '') {
        $display_users = 1;
        $paged = get_data('paged',1);
    }
    if(!is_numeric($paged))
        $paged = 1;
    if( isset( $_REQUEST['filter_action'] ) ) {
        if( $_REQUEST['filter_action'] == 'Search' ) {
            $paged = 1;
        }
    }
    if( isset($_GET['orderby']) && $_GET['orderby'] != '' )
        $orderby = $_GET['orderby'];
    if( isset($_GET['order']) && $_GET['order'] != '' )
        $order = $_GET['order'];
    
    $offset = ($paged-1)*$records_per_page;
    global $wp_roles;
    $get_roles = $wp_roles->roles;
    if( get_data('role') != '' ) {
        $reocrd_id = array( get_data('role') );
        $role = get_data('role');
        $btn_name = 'editTime';
        $btnVal = 'Update Blocked User';
        $is_display_role = 1;
        if( $GLOBALS['wp_roles']->is_role( get_data('role') ) ) {
            $is_active = get_option( get_data('role') . '_is_active' );
            $block_msg_permenant = get_option(get_data('role') . '_block_msg_permenant');
            $curr_edit_msg = 'Update for role: ' .$GLOBALS['wp_roles']->roles[get_data('role')]['name'];
        }
        else {
            $msg_class = 'error';
            $msg = 'Role ' . get_data('role') . ' is not exist.';
        }
    }
    if( get_data('username') != '' ) {
        $reocrd_id = array( get_data('username') );
        $username = get_data('username');
        $btn_name = 'editTime';
        $btnVal = 'Update Blocked User';
        if( get_userdata(get_data('username')) != false ) {
            $is_active = get_user_meta( get_data('username'), 'is_active', true );
            $block_msg_permenant = get_user_meta( get_data('username'), 'block_msg_permenant', true );
            $user_data = new WP_User( get_data('username') );
            if( $is_active == '' ) {              
                $is_active = get_option($user_data->roles[0] . '_is_active');                
            }
            if($block_msg_permenant == '')
            {
                $block_msg_permenant = get_option($user_data->roles[0] . '_block_msg_permenant');
            }
            $curr_edit_msg = 'Update for user with username: ' .$user_data->user_login;
        }
        else {
            $msg_class = 'error';
            $msg = 'User with ' . get_data('username') . ' userid is not exist.';
        }
    }
    if(isset($_POST['sbtSaveStatus']))
    {
        //Check if username is selected in dd
        if( isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'role' ) {
            $is_display_role = 1;
        }
        if( isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'username' ) {
            $display_users = 1;
        }
        
        if((get_data('role') != '') || (get_data('username')) )
        {
            if(get_data('role') != '')
            {
                $old_block_msg_permenant = get_option(get_data('role') . '_block_msg_permenant');
                update_option(get_data('role') . '_is_active', 'n');
                $block_msg_permenant = $default_msg;
                if(trim( $_POST['block_msg_permenant'] ) != '')
                {
                    $block_msg_permenant = trim( $_POST['block_msg_permenant'] );
                }
                update_option(get_data('role') . '_block_msg_permenant', $block_msg_permenant);
                //Update all users of this role
                block_role_users_permenant( get_data('role'), 'n',$old_block_msg_permenant, $block_msg_permenant);
                //Update all users of this role end
                $role_name = str_replace('_', ' ', get_data('role'));
                $msg_class = 'updated';
                $msg = $GLOBALS['wp_roles']->roles[get_data('role')]['name'] . '\'s permanent blocking has been updated successfully';
                $role = '';
                $block_msg_permenant = '';
                $reocrd_id = array();
            }
            else if(get_data('username') != '')
            {
                update_user_meta(get_data('username'), 'is_active', 'n');
                $block_msg_permenant = $default_msg;
                if(trim( $_POST['block_msg_permenant'] ) != '')
                {
                    $block_msg_permenant = trim( $_POST['block_msg_permenant'] );
                }
                update_user_meta(get_data('username'), 'block_msg_permenant', $block_msg_permenant);
                $user_info = get_userdata(get_data('username'));
                $role_name = $user_info->user_login;
                $msg_class = 'updated';
                $msg = $role_name . '\'s permanent blocking has been updated successfully';
                $username = '';
                $block_msg_permenant = '';
                $reocrd_id = array();
            }
            $curr_edit_msg = '';
        }
        else
        {
            if(isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'role')
            {
                if( isset($_POST['chkUserRole']) ) {
                    $reocrd_id = $_POST['chkUserRole'];
                    if(trim( $_POST['block_msg_permenant'] ) != '')
                    {
                        $block_msg_permenant = trim( $_POST['block_msg_permenant'] );
                    }
                    while (list ($key,$val) = @each ($reocrd_id)) {
                        $block_msg_permenant = $default_msg;
                        $old_block_msg_permenant = get_option($val . '_block_msg_permenant');
                        update_option( $val . '_is_active', 'n');
                        update_option($val . '_block_msg_permenant', $block_msg_permenant);
                        //Update all users of this role
                        block_role_users_permenant( $val, 'n',$old_block_msg_permenant, $block_msg_permenant);
                        //Update all users of this role end                  
                    }
                    $msg_class = 'updated';
                    $msg = 'Selected roles have beeen blocked succeefully.';
                    $role = '';
                    $block_msg_permenant = '';
                }
                else {
                    $msg_class = 'error';
                    $msg = 'Please select atleast one role.';
                    $block_msg_permenant = trim( $_POST['block_msg_permenant'] );
                    $get_cmb_val = $_POST['cmbUserBy'];
                    if( $get_cmb_val == 'role' ) {
                        if( isset( $_POST['chkUserRole'] ) ) {
                            $reocrd_id = $_POST['chkUserRole'];
                        }
                    }
                    else if( $get_cmb_val == 'username' ) {
                        if( isset( $_POST['chkUserUsername'] ) ) {
                            $reocrd_id = $_POST['chkUserUsername'];
                        }
                    }
                }
                
            }
            else if(isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'username')
            {
                if( isset($_POST['chkUserUsername']) ) {
                    $reocrd_id = $_POST['chkUserUsername'];
                    $block_msg_permenant = $default_msg;
                    if(trim( $_POST['block_msg_permenant'] ) != '')
                    {
                        $block_msg_permenant = trim( $_POST['block_msg_permenant'] );
                    }
                    while (list ($key,$val) = @each ($reocrd_id)) {
                        update_user_meta($val, 'is_active', 'n');
                        update_user_meta($val, 'block_msg_permenant', $block_msg_permenant);
                    }
                    $msg_class = 'updated';
                    $msg = 'Selected users have beeen blocked succeefully.';
                    $username = '';
                    $block_msg_permenant = '';
                }
                else {
                    $msg_class = 'error';
                    $msg = 'Please select atleast one username.';
                    $block_msg_permenant = trim( $_POST['block_msg_permenant'] );
                    $get_cmb_val = $_POST['cmbUserBy'];
                    if( $get_cmb_val == 'role' ) {
                        if( isset( $_POST['chkUserRole'] ) ) {
                            $reocrd_id = $_POST['chkUserRole'];
                        }
                    }
                    else if( $get_cmb_val == 'username' ) {
                        if( isset( $_POST['chkUserUsername'] ) ) {
                            $reocrd_id = $_POST['chkUserUsername'];
                        }
                    }
                }
            }
        }
        $btnVal = 'Block User';
        $reocrd_id = array();
    }
    
    $user_query = get_users( array( 'role' => 'administrator' ) );
    $admin_id = wp_list_pluck( $user_query, 'ID' );
    $users_filter = array( 'exclude' => $admin_id );
    //Start searching
    if(get_data('txtUsername')!='') {
        $display_users = 1;
        $txtUsername = get_data('txtUsername');
        $users_filter['search'] = '*'.esc_attr($txtUsername).'*';
        $users_filter['search_columns'] = array(
            'user_login',
            'user_nicename',
            'user_email',
            'display_name'
        );
    }
    if( $txtUsername == '' ) {
        if(get_data('srole') != '') {
            $display_users = 1;
            $users_filter['role'] = get_data('srole');
            $srole = $_GET['srole'];
        }
    }
    if(get_data('username')!='')
    {
        $display_users = 1;
    }
    if( $is_display_role == 1 ) {
        $display_users = 0;
    }
    //if order and order by set, display users
    if( isset($_GET['orderby']) && $_GET['orderby'] != '' && isset($_GET['order']) && $_GET['order'] != ''  ) {
        $display_users = 1;
    }
    //Select usermode on reset searching
    if( isset($_GET['resetsearch']) && $_GET['resetsearch'] == '1' ) {
        $display_users = 1;
    }
    if( $display_users == 1 ) {
        $cmbUserBy = 'username';
    }
    //end
    $users_filter['orderby'] = $orderby;
    $users_filter['order'] = $order;
    $get_users_u1 = new WP_User_Query($users_filter);
    $total_items = $get_users_u1->total_users;
    $total_pages = ceil($total_items/$records_per_page);
		
    $next_page = (int)$paged+1;
    if($next_page > $total_pages)
            $next_page = $total_pages;
    $users_filter['number'] = $records_per_page;
    $users_filter['offset'] = $offset;

    $prev_page = (int)$paged-1;
    if($prev_page < 1)
            $prev_page = 1;
    
    $sr_no = 1;
    if( isset( $paged ) && $paged > 1 ) {
        $sr_no = ( $records_per_page * ( $paged -1 ) + 1);
    }
    $get_users_u = new WP_User_Query($users_filter);
    $get_users = $get_users_u->get_results();
    if(isset($_GET['msg']) && $_GET['msg'] != '') {
        $msg = $_GET['msg'];
    }
    if(isset($_GET['msg_class']) && $_GET['msg_class'] != '') {
        $msg_class = $_GET['msg_class'];
    }
    ?>
    <div class="wrap">
        <?php
        //Display success/error messages
            if( $msg != '' ) { ?>
                <div class="ublocker-notice <?php echo $msg_class; ?>">
                    <p><?php echo $msg; ?></p>
                </div>
        <?php }  ?>
            <h2 class="ublocker-page-title"><?php _e( 'Block Users Permanently', 'user-blocker') ?></h2>
            <div class="tab_parent_parent"><div class="tab_parent"><ul><li><a href="?page=block_user">Block User By Time</a></li><li><a href="?page=block_user_date">Block User By Date</a></li><li><a class="current" href="?page=block_user_permenant">Block User Permanent</a></li></ul></div></div>
            <div class="cover_form">
            <?php
            //Visible only if not set in edit mode
            //if( true ) {
            ?>
            
            <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                <div class="tablenav top">
                    <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                    <select name="cmbUserBy" id="cmbUserBy" onchange="changeUserBy();">
                        <option <?php echo selected($cmbUserBy, 'role'); ?> value="role" selected="selected">Role</option>
                        <option <?php echo selected($cmbUserBy, 'username'); ?> value="username">Username</option>
                    </select>
                    <?php //Pagination -top ?>
                    <div class="filter_div" style="float: right; <?php if( $display_users == 1 ) { echo 'display: block;'; } else { echo 'display: none;'; } ?>">
                        <div class="tablenav-pages" <?php if((int)$total_pages <= 1) { echo 'style="display: none;"'; } ?>>
                            <span class="displaying-num"><?php echo $total_items; ?> items</span>
                            <span class="pagination-links">
                                <a class="first-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=block_user_permenant&paged=1&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the first page">&laquo;</a>
                                <a class="prev-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=block_user_permenant&paged='.$prev_page.'&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the previous page">&lsaquo;</a>
                                <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page">
                                        of
                                        <span class="total-pages"><?php echo $total_pages; ?></span>
                                </span>
                                <a class="next-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=block_user_permenant&paged='.$next_page.'&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the next page">&rsaquo;</a>
                                <a class="last-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=block_user_permenant&paged='.$total_pages.'&srole='.$srole.'&txtUsername='.$txtUsername; ?>" title="Go to the last page">&raquo;</a>
                            </span>
                            <input style="display: none;" id="sbtPages" class="button" type="submit" value="sbtPages" name="filter_action">
                        </div><!-- .tablenav-pages -->
                    </div>
                </div>
                <div class="search_box">
                    <div class="actions">
                        <div class="filter_div" <?php if( $display_users == 1 ) { echo 'style="display: block;"'; } else echo 'style="display: none;"'; ?>>
                            <label><strong><?php _e( 'Select Role: ', 'user-blocker') ?></strong></label>
                            <select id="srole" name="srole" onchange="searchUser();">
                                <option value="">All Roles</option>
                                <?php if($get_roles) 
                                {
                                    foreach($get_roles as $key=>$value) {
                                        if( $key == 'administrator' )
                                            continue;
                                        ?>
                                        <option <?php echo selected($key, $srole); ?> value="<?php echo $key; ?>"><?php echo ucfirst($value['name']); ?></option>
                                        <?php
                                     }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="actions">
                        <div class="filter_div" <?php if( $display_users == 1 ) { echo 'style="display: block;"'; } else echo 'style="display: none;"'; ?>>
                            <input type="hidden" value="block_user_permenant" name="page" />
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="username or email or first name" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="Search" name="filter_action">
                            <a class="button" href="<?php echo '?page=block_user_permenant&resetsearch=1'; ?>" style="margin-left: 10px;">Reset</a>
                        </div>
                    </div>
                </div>
            </form>
            <?php //Role Records ?>
            <form method="post" action="?page=block_user_permenant" id="frmBlockUser">
                <input type="hidden" id='hidden_cmbUserBy' name="cmbUserBy" value='<?php if( isset( $cmbUserBy ) && $cmbUserBy != '' ) echo $cmbUserBy; else echo 'role'; ?>'/>
                <input type="hidden" name="paged" value="<?php echo $paged; ?>"/>
                <input type="hidden" name="role" value="<?php echo $role; ?>"/>
                <input type="hidden" name="srole" value="<?php echo $srole; ?>" />
                <input type="hidden" name="username" value="<?php echo $username; ?>" />
                <input type="hidden" name="txtUsername" value="<?php echo $txtUsername; ?>" />
                    <?php if( true ) { ?>
            <table id="role" class="widefat post fixed user-records" <?php if( (isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'username') || $display_users == 1 ) echo 'style="display: none;width: 100%;"'; else echo 'style="width: 100%;"'; ?>>
                <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <th class="user-role">Role</th>
                        <th class="tbl-action">Status</th>
                        <th class="blk-msg aligntextcenter">Block Message</th>
                        <th class="tbl-action">Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <th class="user-role">Role</th>
                        <th class="tbl-action">Status</th>
                        <th class="blk-msg aligntextcenter">Block Message</th>
                        <th class="tbl-action">Action</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                    $chkUserRole = array();
                    $is_checked = '';
                    if( isset($reocrd_id) && count($reocrd_id) > 0 ) {
                        $chkUserRole = $reocrd_id;
                    }
                    if($get_roles) {
                       $p_txtUsername = $_GET['txtUsername'];
                       $p_srole = $_GET['srole'];
                       $p_paged = $_GET['paged'];
                       foreach($get_roles as $key=>$value) {
                            if( $sr_no%2 == 0 )
                                $alt_class = 'alt';
                            else
                                $alt_class = '';
                            if( $key == 'administrator' )
                               continue;
                            if(in_array($key, $chkUserRole) ) {
                                $is_checked = 'checked="checked"';
                            }
                            else {
                                $is_checked = '';
                            }
                           ?>
                            <tr class="<?php echo $alt_class; ?>">
                                <td class="check-column"><input <?php echo $is_checked; ?> type="checkbox" value="<?php echo $key; ?>" name="chkUserRole[]" /></td>
                                <td><?php echo $value['name']; ?></td>
                                <td class="aligntextcenter">
                                    <?php
                                    if( get_option($key.'_is_active') == 'n' )
                                    {
                                        ?>
                                    <img src="<?php echo plugins_url(); ?>/user-blocker/images/inactive.png" alt="inactive" />
                                    <?php
                                    }
                                    else
                                    {
                                        ?>
                                    <img src="<?php echo plugins_url(); ?>/user-blocker/images/active.png" alt="active" />
                                        <?php
                                    }
                                    ?>
                                </td>
                                <td class="aligntextcenter">
                                    <?php echo disp_msg( get_option($key.'_block_msg_permenant') ); ?>
                                </td>
                                <td class="aligntextcenter"><a href="?page=block_user_permenant&role=<?php echo $key; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>"><img src="<?php echo plugins_url(); ?>/user-blocker/images/edit.png" alt="edit" /></a></td>
                            </tr>
                    <?php
                            $sr_no++;
                        }
                     }
                     ?>
                </tbody>
            </table>
                <?php
            $chkUserUsername = array();
            $is_checked = '';
            if( isset($_POST['chkUserUsername']) && count($_POST['chkUserUsername']) > 0) {
                $chkUserUsername = $_POST['chkUserUsername'];
            }
            ?>
            <table id="username" class="widefat post fixed user-records" <?php if( (isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'username') || $display_users == 1 ) echo 'style="display: table;"'; else echo 'style="display: none;"'; ?>>
                <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <?php
                        $linkOrder = 'ASC';
                        if( isset($order) ) {
                            if( $order == 'ASC' ) {
                                $linkOrder = 'DESC';
                            }
                            else if( $order == 'DESC' ) {
                                $linkOrder = 'ASC';
                            }
                        }
                        ?>
                        <th class="th-username sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_permenant&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Username</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-name sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_permenant&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Name</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-email sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_permenant&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Email</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-role">Role</th>
                        <th class="blk-msg aligntextcenter">Block Message</th>
                        <th class="tbl-action">Status</th>
                        <th class="tbl-action">Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <?php
                        $linkOrder = 'ASC';
                        if( isset($order) ) {
                            if( $order == 'ASC' ) {
                                $linkOrder = 'DESC';
                            }
                            else if( $order == 'DESC' ) {
                                $linkOrder = 'ASC';
                            }
                        }
                        ?>
                        <th class="th-username sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_permenant&orderby=user_login&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Username</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-name sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_permenant&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Name</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-email sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_permenant&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span>Email</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-role">Role</th>
                        <th class="blk-msg aligntextcenter">Block Message</th>
                        <th class="tbl-action">Status</th>
                        <th class="tbl-action">Action</th>
                    </tr>
                </tfoot>
                <tbody>
            <?php
            $chkUserRole = array();
            $is_checked = '';
            if( isset($reocrd_id) && count($reocrd_id) > 0) {
                $chkUserRole = $reocrd_id;
            }
            if($get_users) {
                $d = 1;
               foreach($get_users as $user) {
                    $p_txtUsername = $_GET['txtUsername'];
                    $p_srole = $_GET['srole'];
                    $p_paged = $_GET['paged'];
                    if( $d%2 == 0 )
                        $alt_class = 'alt';
                    else
                        $alt_class = '';
                    if(in_array($user->ID, $chkUserRole) ) {
                        $is_checked = 'checked="checked"';
                    }
                    else {
                        $is_checked = '';
                    }
                   ?>
                        <tr class="<?php echo $alt_class; ?>">
                            <td class="check-column"><input <?php echo $is_checked; ?> type="checkbox" value="<?php echo $user->ID; ?>" name="chkUserUsername[]" /></td>
                            <td><?php echo $user->user_login; ?></td>
                            <td><?php echo $user->display_name; ?></td>
                            <td><?php echo $user->user_email; ?></td>
                            <td><?php echo ucfirst( str_replace('_', ' ', $user->roles[0]) ); ?></td>
                            <td class="aligntextcenter">
                                <?php echo disp_msg( get_user_meta($user->ID, 'block_msg_permenant', true) ); ?>
                            </td>
                            <td class="aligntextcenter">
                                <?php
                                if(get_user_meta($user->ID,'is_active',true) == 'n')
                                {
                                    ?>
                                <img src="<?php echo plugins_url(); ?>/user-blocker/images/inactive.png" alt="inactive" />
                                <?php
                                }
                                else
                                {
                                    ?>
                                <img src="<?php echo plugins_url(); ?>/user-blocker/images/active.png" alt="active" />
                                    <?php
                                }
                                ?>
                            </td>
                            <td class="aligntextcenter"><a href="?page=block_user_permenant&username=<?php echo $user->ID; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>"><img src="<?php echo plugins_url(); ?>/user-blocker/images/edit.png" alt="edit" /></a></td>
                        </tr>
                        
                <?php
                        $d++;
                    }
                    ?>
                    <?php
                }//End $get_users
                else {
                    echo '<tr><td colspan="8"  align="center">No records found.</td></tr>';
                } ?>
                        </tbody>
            </table>
                <?php
              }
            $role_name = '';
            if( isset($_GET['role']) && $_GET['role'] != '' ) {
                if( $GLOBALS['wp_roles']->is_role( $_GET['role'] ) ) {
                    $role_name = ' For <span style="text-transform: capitalize;">' . str_replace('_', ' ', $_GET['role']) . '</span>';
                }
            }
            if( isset($_GET['username']) && $_GET['username'] != '' ) {
                if( get_userdata($_GET['username']) != false ) {
                    $user_info = get_userdata($_GET['username']);
                    //$block_msg_permenant = $user_info->block_msg_permenant;
                    $role_name = ' For ' . $user_info->user_login;
                }
            }
            ?>            
            <h3 class="block_msg_title">Block Message <?php if( isset($curr_edit_msg) && $curr_edit_msg != '' ) echo '<span>' . $curr_edit_msg . '</span>'; ?></h3>
            <div class="block_msg_div">
                <div class="block_msg_left">
                    <textarea style="width:500px;height: 45px" name="block_msg_permenant"><?php echo stripslashes( $block_msg_permenant ); ?></textarea>
                </div>
                <div class="block_msg_note_div">
                    Note:If you will not type message, default message will be '<?php echo $default_msg; ?>'.
                </div>
            </div>
            <?php
            if( $cmbUserBy == 'role' || $cmbUserBy == '' ) {
                $btnVal = str_replace('User', 'Role', $btnVal);
            }
            ?>
            <input id="sbt-block" style="margin: 20px 0 0 0;clear: both;float: left" class="button button-primary" type="submit" name="sbtSaveStatus" value="<?php echo $btnVal; ?>">
            <?php if( isset( $btnVal ) && $btnVal == 'Update Blocked User' ) { ?>
            <a style="margin: 20px 0 0 10px;float: left;" href="<?php echo '?page=block_user_permenant'; ?>" class="button button-primary">Cancel</a>
            <?php } ?>
        </form>
    </div>
    <div class="ajax-loader"></div>
</div>
<?php
}


//Admin login
add_filter( 'authenticate', 'myplugin_auth_signon',30, 3 );
function myplugin_auth_signon( $user, $username, $password ) {
    if(!is_wp_error($user))
    {
        $user = get_userdatabylogin($username);
        date_default_timezone_set("Asia/Kolkata");
        $user_id = $user->ID;
        $is_active = get_user_meta($user_id,'is_active',true);
        $block_day = get_user_meta($user_id,'block_day',true);
        $block_date = get_user_meta($user_id,'block_date',true);
        if($is_active == 'n')
        {
            $block_msg_permenant = get_user_meta($user_id,'block_msg_permenant',true);
            return new WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>:'.$block_msg_permenant, 'wp-members' ) );
        }
        else
        {
            $error_msg = '';            
            if(!empty($block_day) && $block_day!=0 && $block_day!='')
            {
                $full_date = getdate();
                $current_day = strtolower($full_date['weekday']);        
                $current_time = time();
                if (array_key_exists($current_day,$block_day))
                {
                    $from_time = $sfrmtime = $block_day[$current_day]['from'];
                    $to_time = $stotime = $block_day[$current_day]['to'];
                    $from_time = strtotime($from_time);
                    $to_time = strtotime($to_time);
                    if ($current_time >= $from_time && $current_time <= $to_time)
                    {
                       $block_msg_day = get_user_meta($user_id,'block_msg_day',true);
//                       $block_msg_day = str_replace('[frmtime]', $sfrmtime, $block_msg_day);
//                       $block_msg_day = str_replace('[totime]', $stotime, $block_msg_day);
//                       $block_msg_day = str_replace('[today]', $current_day, $block_msg_day);
                       $error_msg = $block_msg_day;
                    }
                }
            }
            if($block_date !=0 && $block_date !='' && !empty($block_date))
            {
                $frmdate = $sfrmdate = $block_date['frmdate'];
                $todate = $stodate = $block_date['todate'];
                $frmdate = strtotime($frmdate);
                $todate = strtotime($todate);
                $current_date = date('m/d/Y');
                $current_date = strtotime($current_date);                
                if($current_date >= $frmdate && $current_date <= $todate)
                {
                    $block_msg_date = get_user_meta($user_id,'block_msg_date',true);
//                    $block_msg_date = str_replace('[frmdate]', $sfrmdate, $block_msg_date);
//                    $block_msg_date = str_replace('[todate]', $stodate, $block_msg_date);
                    if($error_msg == '')
                           $error_msg = $block_msg_date;
                       else
                           $error_msg .= ', '.$block_msg_date;
                }
            }
            if($error_msg != '')
            {
                return new WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>:'.$error_msg, 'wp-members' ) );
            }
        }
    }
    return $user;
}
add_action( 'user_register', 'user_blocking_when_register', 10, 1 );
function user_blocking_when_register( $user_id ) {
    $user_id;
    $user_info = get_userdata($user_id);
      $user_role = $user_info->roles[0];
      $permenant_block = get_option($user_role.'_is_active');      
      if($permenant_block == 'n')
      {
          update_user_meta($user_id,'is_active','n');
          $block_msg_permenant = get_option($user_role.'_block_msg_permenant');
          update_user_meta($user_id,'block_msg_permenant',$block_msg_permenant);
      }
      else
      {
          $day_wise_block = get_option($user_role.'_block_day');
          $date_wise_block = get_option($user_role.'_block_date');
          $day_wise_block_msg = get_option($user_role.'_block_msg_day');
          $date_wise_block_msg = get_option($user_role.'_block_msg_date');
          if($day_wise_block!=0 && $day_wise_block!='')
          {
              update_user_meta($user_id,'block_day',$day_wise_block);
              update_user_meta($user_id,'block_msg_day',$day_wise_block_msg);
          }
          if($date_wise_block!=0 && $date_wise_block != '')
          {
              update_user_meta($user_id,'block_date',$date_wise_block);
              update_user_meta($user_id,'block_msg_date',$date_wise_block_msg);
          }
      }
}
function timeToTwentyfourHour($time) {
    if( $time != '' ) {
        $time = date('H:i:s', strtotime($time));
    }
    return $time;
}
function timeToTwelveHour($time) {
    if( $time != '' ) {
        $time = date('h:i A', strtotime($time));
    }
    return $time;
}
function validate_time($time) {
    $splitBySpace = explode(" ", $time);
    $firstPart = $splitBySpace[0];
    $secondPart = $splitBySpace[1];
    if( $secondPart == 'AM' || $secondPart == 'PM' ) {
        $timeIntSplit = explode(":", $firstPart);
        if( strlen($timeIntSplit[0]) == 2 && strlen($timeIntSplit[1]) == 2 ) {
            $timeFirst = intval($timeIntSplit[0]);
            $timeSecond = intval($timeIntSplit[1]);
            if( $timeSecond >= 0 || $timeSecond < 60 ) {
                if( $timeSecond >= 1 || $timeSecond < 13 ) {
                    return 1;
                }
                else {
                    return 0;
                }
            }
            else {
                return 0;
            }
        }
        else {
            return 0;
        }
    }
    else {
        return 0;
    }
}
add_action('init','get_plugins_url');
function get_plugins_url()
{
    wp_enqueue_script('jquery-ui-datepicker');
    wp_register_style('jqueryUI', plugins_url().'/user-blocker/css/jquery-ui.css');
    wp_enqueue_style('jqueryUI');
    ?>
    <script type="text/javascript">
    var plugin_url = '<?php echo plugins_url(); ?>';   
    </script>
    <?php
}
function disp_msg( $msg ) {
    $msg = stripslashes( nl2br($msg) );
    return $msg;
}
function display_block_time( $day, $block_day ) {
    if ( is_array($block_day) ) {
        if (array_key_exists( $day, $block_day )) {
            $from_time = $block_day[$day]['from'];
            $to_time = $block_day[$day]['to'];
            if( $from_time != '' && $to_time != '' ) {
                echo '<div class="days">';
                echo '<span>' . strtoupper($day) . '</span>';
                echo '<span>' . timeToTwelveHour($from_time) . ' to ' .timeToTwelveHour($to_time) . '</span>';
                echo '</div>';
            }
        }
    }
}
//Day wise blockin
function block_role_users_day( $role, $old_block_day, $block_day,$old_block_msg_day, $block_msg_day ) {
    //Update all users of this role
    $role_usr_qry = get_users( array( 'role' => $role ) );
    $curr_role_usr = wp_list_pluck( $role_usr_qry, 'ID' );
    if( count($curr_role_usr) > 0 ) {
        foreach( $curr_role_usr as $u_id ) {
            $own_block_day = get_user_meta( $u_id, 'block_day', true );
            $own_block_msg_day = get_user_meta( $u_id, 'block_msg_day', true );
            
            if((empty($own_block_day) || ($own_block_day == $old_block_day)) && ( empty($own_block_msg_day) || $old_block_msg_day == $own_block_msg_day)) {
                
                //Not update already date wise blocked users
                $is_active = get_user_meta( $u_id, 'is_active', true );
                if( $is_active != 'n' ) {
                    update_user_meta( $u_id, 'block_day', $block_day );
                    update_user_meta( $u_id, 'block_msg_day', $block_msg_day );
                }
            }
        }
    }
}
//Date wise blockin
function block_role_users_date( $role, $old_block_date, $block_date, $old_block_msg_date, $block_msg_date ) {
    //Update all users of this role
    $role_usr_qry = get_users( array( 'role' => $role ) );
    $curr_role_usr = wp_list_pluck( $role_usr_qry, 'ID' );
    if( count($curr_role_usr) > 0 ) {
        foreach( $curr_role_usr as $u_id ) {
            $own_block_date = get_user_meta( $u_id, 'block_date', true );
            $own_block_msg_date = get_user_meta( $u_id, 'block_msg_date', true );
            if((empty($own_block_date) || ($own_block_date == $old_block_date)) && ( empty($own_block_msg_date) || $old_block_msg_date == $own_block_msg_date)) {
                //Not update already date wise blocked users
                $is_active = get_user_meta( $u_id, 'is_active', true );
                if( $is_active != 'n' ) {
                    update_user_meta( $u_id, 'block_date', $block_date );
                    update_user_meta( $u_id, 'block_msg_date', $block_msg_date );
                }
            }
        }
    }
}
//Permenant wise blockin
function block_role_users_permenant( $role, $is_active,$old_block_msg_permenant, $block_msg_permenant ) {
    //Update all users of this role
    $role_usr_qry = get_users( array( 'role' => $role ) );
    $curr_role_usr = wp_list_pluck( $role_usr_qry, 'ID' );
    if( count($curr_role_usr) > 0 ) {
        foreach( $curr_role_usr as $u_id ) {
            $is_active_a = get_user_meta( $u_id, 'is_active', true );
            $own_block_msg_permenant = get_user_meta( $u_id, 'block_msg_permenant', true );
            if( (isset( $is_active_a ) && $is_active_a == '') || $own_block_msg_permenant==$old_block_msg_permenant) {
                //Not update already date wise blocked users
                update_user_meta( $u_id, 'is_active', $is_active );
                update_user_meta( $u_id, 'block_msg_permenant', $block_msg_permenant );
            }
        }
    }
    
}

/*adding group by in get user query*/
function sort_by_member_number( $vars ) {
    $vars->query_orderby = 'group by user_login '.$vars->query_orderby;
}

function all_block_data_view($user_id)
{
    $is_active = get_user_meta($user_id,'is_active',true);
    $block_day = get_user_meta($user_id,'block_day',true);
    $block_date = get_user_meta($user_id,'block_date',true);
    if($is_active == 'n')
    {
        ?>
        <img src="<?php echo plugins_url().'/user-blocker/images/inactive.png'; ?>" title="Permanently Blocked" />
        <?php
    }
    else
    {
        ?>
        <a data-href='<?php echo $user_id; ?>' href='' class="view_block_data"><img src="<?php echo plugins_url().'/user-blocker/images/view.png'; ?>" title="View Block Date Time" /></a>
        <?php
    }
}

function all_block_data_view_role($key)
{
    $is_active = get_option($key.'_is_active');
    $block_day = get_option($key.'_block_day');
    $block_date = get_option($key.'_block_date');
    if($is_active == 'n')
    {
        ?>
        <img src="<?php echo plugins_url().'/user-blocker/images/inactive.png'; ?>" title="Permanently Blocked" />
        <?php
    }
    else
    {
        ?>
        <a href='' class="view_block_data"><img src="<?php echo plugins_url().'/user-blocker/images/view.png'; ?>" title="View Block Date Time" /></a>
        <?php
    }
}

function all_block_data_table($user_id)
{
    $is_active = get_user_meta($user_id,'is_active',true);
    $block_day = get_user_meta($user_id,'block_day',true);
    $block_date = get_user_meta($user_id,'block_date',true);
    if($is_active != 'n')
    {
    ?>
        <tr id='view_block_day_tr_<?php echo $user_id; ?>' class="view_block_data_tr">
        <td colspan="7" class='date_detail_row'>
            <table class="view_block_table form-table tbl-timing">
                <tbody>
                    <?php
                    if(isset($block_day) && !empty($block_day) && $block_day!='') {
                    ?>
                    <tr><td colspan='7'><label>Blocked Day Detail</label></td></tr>
                    <tr>
                        <th align="center">Sunday</th>
                        <th align="center">Monday</th>
                        <th align="center">Tuesday</th>
                        <th align="center">Wednesday</th>
                        <th align="center">Thursday</th>
                        <th align="center">Friday</th>
                        <th align="center">Saturday</th>
                    </tr>
                    <tr>
                       <td align="center"><?php get_time_record('sunday',$block_day); ?></td>
                       <td align="center"><?php get_time_record('monday',$block_day); ?></td>
                       <td align="center"><?php get_time_record('tuesday',$block_day); ?></td>
                       <td align="center"><?php get_time_record('wednesday',$block_day); ?></td>
                       <td align="center"><?php get_time_record('thursday',$block_day); ?></td>
                       <td align="center"><?php get_time_record('friday',$block_day); ?></td>
                       <td align="center"><?php get_time_record('saturday',$block_day); ?></td>
                    </tr>
                    <?php
                    }
                    if(isset($block_date) && !empty($block_date) && $block_date!='') {
                        ?>
                    <tr><td class="" colspan='7'><label>Blocked Date Detail:</label> <?php echo $block_date['frmdate'].' to '.$block_date['todate']; ?></td></tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </td>
    </tr>
    <?php
    }
}

function all_block_data_table_role($key)
{
    $is_active = get_option($key.'_is_active');
    $block_day = get_option($key.'_block_day');
    $block_date = get_option($key.'_block_date');
    if($is_active != 'n')
    {
    ?>
        <tr id='view_block_day_tr_<?php echo $user_id; ?>' class="view_block_data_tr">
        <td colspan="3" class='date_detail_row'>
            <table class="view_block_table form-table tbl-timing">
                <tbody>
                    <?php
                    if(isset($block_day) && !empty($block_day) && $block_day!='') {
                    ?>
                    <tr><td colspan='7'><label>Blocked Day Detail</label></td></tr>
                    <tr>
                        <th align="center">Sunday</th>
                        <th align="center">Monday</th>
                        <th align="center">Tuesday</th>
                        <th align="center">Wednesday</th>
                        <th align="center">Thursday</th>
                        <th align="center">Friday</th>
                        <th align="center">Saturday</th>
                    </tr>
                    <tr>
                       <td align="center"><?php get_time_record('sunday',$block_day); ?></td>
                       <td align="center"><?php get_time_record('monday',$block_day); ?></td>
                       <td align="center"><?php get_time_record('tuesday',$block_day); ?></td>
                       <td align="center"><?php get_time_record('wednesday',$block_day); ?></td>
                       <td align="center"><?php get_time_record('thursday',$block_day); ?></td>
                       <td align="center"><?php get_time_record('friday',$block_day); ?></td>
                       <td align="center"><?php get_time_record('saturday',$block_day); ?></td>
                    </tr>
                    <?php
                    }
                    if(isset($block_date) && !empty($block_date) && $block_date!='') {
                        ?>
                    <tr><td class="" colspan='7'><label>Blocked Date Detail:</label> <?php echo $block_date['frmdate'].' to '.$block_date['todate']; ?></td></tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </td>
    </tr>
    <?php
    }
}

function all_block_data_msg($user_id)
{
    $is_active = get_user_meta($user_id,'is_active',true);
    $block_day = get_user_meta($user_id,'block_day',true);
    $block_date = get_user_meta($user_id,'block_date',true);
    if($is_active == 'n')
    {
        echo disp_msg(get_user_meta( $user_id,'block_msg_permenant',true) );
    }
    else
    {
        if(isset($block_day) && !empty($block_day) && $block_day!='') {
            echo disp_msg( get_user_meta($user_id,'block_msg_day',true) );
        }
        if(isset($block_date) && !empty($block_date) && $block_date!='') {
            echo disp_msg( get_user_meta($user_id,'block_msg_date',true) );
        }
    }
}
function all_block_data_msg_role($key)
{
    $is_active = get_option($key.'_is_active');
    $block_day = get_option($key.'_block_day');
    $block_date = get_option($key.'_block_date');
    if($is_active == 'n')
    {
        echo disp_msg( get_option($key.'_block_msg_permenant') );
    }
    else
    {
        if(isset($block_day) && !empty($block_day) && $block_day!='') {
            echo disp_msg( get_option($key.'_block_msg_day') );
        }
        if(isset($block_date) && !empty($block_date) && $block_date!='') {
            echo disp_msg( get_option($key.'block_msg_date') );
        }
    }
}
function get_time_record($day,$block_day)
{
    if (array_key_exists($day,$block_day)) {
        $from_time = $block_day[$day]['from'];
        $to_time = $block_day[$day]['to'];
        if( $from_time == '' ) {
            echo 'not set';
        }
        else {
            echo timeToTwelveHour($from_time);
        }
        if( $from_time != '' && $to_time != '' ) {
            echo ' to '.timeToTwelveHour($to_time);
        }
    }
    else {
        echo 'not set';
    }
}
function get_data( $data,$default_val = '' ) {
    $return_val = '';
    if( $data != '' ) {
        if( isset( $_GET[$data] ) && $_GET[$data]!='')
            $return_val = $_GET[$data];
        else if( isset( $_POST[$data] ) && $_POST[$data]!='')
            $return_val = $_POST[$data];
        else
            $return_val = $default_val;
    }
    return $return_val;
}