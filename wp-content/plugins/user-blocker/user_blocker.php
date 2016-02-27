<?php
/*
Plugin Name: User Blocker
Plugin URI: https://wordpress.org/plugins/user-blocker/
Description: Block users except admin.
Author: Solwin Infotech
Version: 1.1.1
Author URI: http://www.solwininfotech.com/
Requires at least: 4.0
Tested up to: 4.4
Copyright: Solwin Infotech
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

/**
 * Add style/script
 */
add_action('init', 'enqueueStyleScript');
function enqueueStyleScript() {
    if(isset($_GET['page']) && ($_GET['page'] == 'all_type_blocked_user_list' || $_GET['page'] == 'permanent_blocked_user_list' || $_GET['page'] == 'datewise_blocked_user_list' || $_GET['page'] == 'blocked_user_list' || $_GET['page'] == 'block_user' || $_GET['page'] == 'block_user_date' || $_GET['page'] == 'block_user_permenant'))
    {
        wp_enqueue_script('jquery');
        wp_register_script('jquery-ui-js', 'http://code.jquery.com/ui/1.11.0/jquery-ui.min.js','jquery');
        wp_enqueue_script('jquery-ui-js');
        wp_register_script('timepicker-addon-js', plugins_url('script/jquery-ui-timepicker-addon.js', __FILE__),'jquery-ui-js');
        wp_enqueue_script('timepicker-addon-js');
        wp_register_script('timepicker-js', plugins_url('script/jquery.timepicker.js', __FILE__));
        wp_enqueue_script('timepicker-js');
        wp_register_script('datepair-js', plugins_url('script/datepair.js', __FILE__));
        wp_enqueue_script('datepair-js');

        wp_register_script('jquery-ui-sliderAccess', plugins_url('script/jquery-ui-sliderAccess.js', __FILE__),'jquery-ui');
        wp_enqueue_script('jquery-ui-sliderAccess');
        wp_register_script('admin_script', plugins_url('script/admin_script.js', __FILE__));
        wp_enqueue_script('admin_script');

        wp_register_style('timepicker-css', plugins_url('css/jquery.timepicker.css', __FILE__));
        wp_enqueue_style('timepicker-css');
        wp_register_style('jqueryUI', plugins_url().'/user-blocker/css/jquery-ui.css');
        wp_enqueue_style('jqueryUI');
        wp_register_style('timepicker-addon-css', plugins_url('css/jquery-ui-timepicker-addon.css', __FILE__),'jqueryUI');
        wp_enqueue_style('timepicker-addon-css');
        wp_register_style('admin_style', plugins_url('css/admin_style.css', __FILE__));
        wp_enqueue_style('admin_style');
    }
}

/**
 * Add js
 */
function block_user_plugin_setup() {
    add_menu_page( esc_html__('User Blocker', 'user-blocker'), esc_html__('User Blocker', 'user-blocker'), 'manage_options', 'block_user', 'block_user_page', 'dashicons-admin-users');
    $block_date_page = add_submenu_page('',esc_html__('Block User Date Wise', 'user-blocker'), esc_html__('Date Wise Block User', 'user-blocker'), 'manage_options', 'block_user_date', 'block_user_date_page', 'dashicons-admin-users');
    $block_permanent = add_submenu_page('',esc_html__('Block User Permanent', 'user-blocker'), esc_html__('Permanently Block User', 'user-blocker'), 'manage_options', 'block_user_permenant', 'block_user_permenant_page', 'dashicons-admin-users');
    add_submenu_page('block_user', esc_html__('Blocked User list', 'user-blocker'), esc_html__('Blocked User list', 'user-blocker'), 'manage_options', 'blocked_user_list', 'block_user_list_page', 'dashicons-admin-users');
    $list_user_date = add_submenu_page('', esc_html__('Date Wise Blocked User list', 'user-blocker'), esc_html__('Date Wise Blocked User list', 'user-blocker'), 'manage_options', 'datewise_blocked_user_list', 'datewise_block_user_list_page', 'dashicons-admin-users');
    $list_user_permanent = add_submenu_page('', esc_html__('Permanent Blocked User list', 'user-blocker'), esc_html__('Permanent Blocked User list', 'user-blocker'), 'manage_options', 'permanent_blocked_user_list', 'permanent_block_user_list_page', 'dashicons-admin-users');
    $list_user_all = add_submenu_page('',  esc_html__('All Type Blocked User list', 'user-blocker'), esc_html__('All Type Blocked User list', 'user-blocker'), 'manage_options', 'all_type_blocked_user_list', 'all_type_block_user_list_page', 'dashicons-admin-users');
    
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

/**
 * plugin text domain
 */
add_action('plugins_loaded', 'load_text_domain_user_blocker');

function load_text_domain_user_blocker() {
    load_plugin_textdomain('user-blocker', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

/**
 * Display total download of plugin
 */
if (!function_exists('get_user_blocker_total_downloads')) {

    function get_user_blocker_total_downloads() {
        // Set the arguments. For brevity of code, I will set only a few fields.        
        $plugins = $response = "";
        $args = array(
            'author' => 'solwininfotech',
            'fields' => array(
                'downloaded' => true,
                'downloadlink' => true
            )
        );
        // Make request and extract plug-in object. Action is query_plugins
        $response = wp_remote_post(
                'http://api.wordpress.org/plugins/info/1.0/',
                array(
                    'body' => array(
                        'action' => 'query_plugins',
                        'request' => serialize((object) $args)
                    )
                )
        );
        if (!is_wp_error($response)) {
            $returned_object = unserialize(wp_remote_retrieve_body($response));
            $plugins = $returned_object->plugins;
        } else {            
        }
        $current_slug = 'user-blocker';
        if ($plugins) {
            foreach ($plugins as $plugin) {
                if ($current_slug == $plugin->slug) {
                    if($plugin->downloaded) {
                        ?>
                            <span class="total-downloads">
                                <span class="download-number"><?php echo $plugin->downloaded; ?></span>
                            </span>
                        <?php
                    }
                }
            }
        }
    }
}

/**
 * Display rating of plugin
 */
$wp_version = get_bloginfo('version');
if($wp_version > 3.8){
    if (!function_exists('wp_user_blocker_star_rating')) {

        function wp_user_blocker_star_rating($args = array()) {
            $plugins = $response = "";
            $args = array(
                'author' => 'solwininfotech',
                'fields' => array(
                    'downloaded' => true,
                    'downloadlink' => true
                )
            );

            // Make request and extract plug-in object. Action is query_plugins
            $response = wp_remote_post(
                    'http://api.wordpress.org/plugins/info/1.0/',
                    array(
                        'body' => array(
                            'action' => 'query_plugins',
                            'request' => serialize((object) $args)
                        )
                    )
            );

            if (!is_wp_error($response)) {
                $returned_object = unserialize(wp_remote_retrieve_body($response));
                $plugins = $returned_object->plugins;
            }

            $current_slug = 'user-blocker';
            if ($plugins) {
                foreach ($plugins as $plugin) {
                    if ($current_slug == $plugin->slug) {
                        $rating = $plugin->rating * 5 / 100;
                        if ($rating > 0) {
                            $args = array(
                                'rating' => $rating,
                                'type' => 'rating',
                                'number' => $plugin->num_ratings,
                            );
                            wp_star_rating($args);
                        }
                    }
                }
            }
        }
    }
}

/**
 * Display html of support section at right side
 */
function display_support_section(){
    ?>
    <div class="td-admin-sidebar">
        <div class="td-help">
            <h2><?php _e('Help to improve this plugin!', 'user-blocker'); ?></h2>
            <span><?php _e('Enjoyed this plugin?', 'user-blocker' ); ?></span>
            <span><?php _e(' You can help by', 'user-blocker'); ?>
                <a href="https://wordpress.org/support/view/plugin-reviews/user-blocker/" target="_blank">
                    <?php _e(' rating this plugin on wordpress.org', 'user-blocker'); ?>
                </a>
            </span>
            <div class="td-total-download">
                <?php _e('Downloads:', 'user-blocker'); ?><?php get_user_blocker_total_downloads(); ?>
                <?php $wp_version = get_bloginfo('version'); if($wp_version > 3.8){ wp_user_blocker_star_rating(); } ?>
            </div>
        </div>
        <div class="td-support">
            <h3><?php _e('Need Support?', 'user-blocker'); ?></h3>
            <span><?php _e('Check out the', 'user-blocker') ?>
                <a href="https://wordpress.org/plugins/user-blocker/faq/" target="_blank"><?php _e('FAQs', 'user-blocker'); ?></a>
                <?php _e('and','user-blocker') ?>
                <a href="https://wordpress.org/support/plugin/user-blocker/" target="_blank"><?php _e('Support Forums', 'user-blocker') ?></a>
            </span>
        </div>
        <div class="td-support">
            <h3><?php _e('Share & Follow Us', 'user-blocker'); ?></h3>
            <!-- Twitter -->
            <div style='display:block;margin-bottom:8px;'>
                <a href="https://twitter.com/solwininfotech" class="twitter-follow-button" data-show-count="true" data-show-screen-name="true" data-dnt="true">Follow @solwininfotech</a>                    
                <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
            </div>
            <!-- Facebook -->
            <div style='display:block;margin-bottom:10px;'>
                <div id="fb-root"></div>
                <script>(function(d, s, id) {
                  var js, fjs = d.getElementsByTagName(s)[0];
                  if (d.getElementById(id)) return;
                  js = d.createElement(s); js.id = id;
                  js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.5";
                  fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));</script>
                <div class="fb-share-button" data-href="https://wordpress.org/plugins/user-blocker/" data-layout="button_count"></div>
            </div>            
            <!-- Google Plus -->
            <div style='display:block;margin-bottom:8px;'>
                <!-- Place this tag where you want the +1 button to render. -->
                <div class="g-plusone" data-href="https://wordpress.org/plugins/user-blocker/"></div>
                <!-- Place this tag after the last +1 button tag. -->
                <script type="text/javascript">
                    (function () {
                        var po = document.createElement('script');
                        po.type = 'text/javascript';
                        po.async = true;
                        po.src = 'https://apis.google.com/js/platform.js';
                        var s = document.getElementsByTagName('script')[0];
                        s.parentNode.insertBefore(po, s);
                    })();
                </script>
            </div>
            <div style='display:block;margin-bottom:8px'>
                <script src="//platform.linkedin.com/in.js" type="text/javascript"></script>
                <script type="IN/Share" data-url="https://wordpress.org/plugins/user-blocker/" data-counter="right" data-showzero="true"></script>
            </div>
        </div>
        <div class="useful_plugins">
            <h3><?php _e('Our Other Works', 'user-blocker'); ?></h3>
            <ul class="plugins_list">
                <li>
                    <span class="plugin_img">
                        <img src="http://www.solwininfotech.com/wp-content/uploads/2015/10/avartan-slider-300x300.png" />
                    </span>
                    <span>
                        <a href="http://www.solwininfotech.com/product/wordpress-plugins/avartan-slider/" target="_blank"><?php _e('Avartan Premium Slider Plugin', 'user-blocker'); ?></a>
                    </span>
                    <span class="plugins_content">
                        <?php _e('Avartan Slider is a great way to create stunning text, image and video slider for your WordPress websites.', 'user-blocker'); ?>
                    </span>
                </li>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * 
 * @global type $wpdb
 * @global type $wp_roles
 * @return html Display block user list
 */
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
        <h2 class="ublocker-page-title"><?php _e( 'Blocked User list', 'user-blocker' ) ?></h2> 
        <?php
            //Display success/error messages
            if( $msg != '' ) { ?>
                <div class="ublocker-notice <?php echo $msg_class; ?>">
                    <p><?php echo $msg; ?></p>
                </div>
        <?php } ?>
        <?php if(isset($_SESSION['success_msg'])) { ?>
            <div class="success_msg"><?php echo $_SESSION['success_msg'];
            unset($_SESSION['success_msg']);
            ?></div>
            <?php } ?>
        <div class="tab_parent_parent"><div class="tab_parent"><ul><li><a href="?page=blocked_user_list" class="current"><?php _e('Blocked User List By Time','user-blocker' ); ?></a></li><li><a href="?page=datewise_blocked_user_list"><?php _e('Blocked User List By Date','user-blocker'); ?></a></li><li><a href="?page=permanent_blocked_user_list"><?php _e('Blocked User List Permanently','user-blocker'); ?></a></li><li><a href="?page=all_type_blocked_user_list"><?php _e('Blocked User List','user-blocker' ); ?></a></li></ul></div></div>
        <div class="cover_form">
        <div class="search_box">
            <div class="tablenav top">                
                <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                    <div class="actions">
                        <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                        <select name="display" id="display_status">
                            <option value="users" <?php echo selected($display,'users') ?> ><?php _e('Users','user-blocker'); ?></option>
                            <option value="roles" <?php echo selected($display,'roles') ?>><?php _e('Roles','user-blocker'); ?></option>
                        </select>
                        <?php //Pagination -top ?>
                        <div class="tablenav-pages" <?php if((int)$total_pages <= 1) { echo 'style="display: none;"'; } ?>>
                            <span class="displaying-num"><?php echo $total_items; ?> <?php _e('items','user-blocker'); ?></span>
                            <span class="pagination-links">
                                <a class="first-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=blocked_user_list&paged=1&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the first page">&laquo;</a>
                                <a class="prev-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=blocked_user_list&paged='.$prev_page.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the previous page">&lsaquo;</a>
                                <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page">
                                        <?php _e( 'of','user-blocker' ); ?>
                                        <span class="total-pages"><?php echo $total_pages; ?></span>
                                </span>
                                <a class="next-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=blocked_user_list&paged='.$next_page.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the next page">&rsaquo;</a>
                                <a class="last-page <?php if($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=blocked_user_list&paged='.$total_pages.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the last page">&raquo;</a>
                            </span>
                            <input style="display: none;" id="sbtPages" class="button" type="submit" value="sbtPages" name="filter_action">
                            </div>
                    </div>
                    <div class="actions">
                        <div class="filter_div" <?php if($display == 'roles') echo 'style="display: none"'; ?>>
                            <label><strong><?php _e( 'Select Role: ', 'user-blocker') ?></strong></label>
                            <select id="srole" name="role" onchange="searchUser();">
                                <option value=""><?php _e( 'All Roles','user-blocker'); ?></option>
                                <?php
                                if($get_roles) 
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
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="<?php esc_attr_e('username or email or first name','user-blocker'); ?>" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="<?php esc_attr_e('Search','user-blocker'); ?>" name="filter_action">
                            <a class="button" href="<?php echo '?page=blocked_user_list'; ?>" style="margin-left: 10px;"><?php _e( 'Reset','user-blocker' ); ?></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <table class="widefat post role_records striped" <?php if($display == 'roles') echo 'style="display: table"'; ?>>
            <thead>
                <tr>
                    <th class="th-role"><?php _e( 'Role','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e('Sunday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e('Monday','user-blocker'); ?></th>
                    <th class="th-time"><?php _e('Tuesday','user-blocker'); ?></th>
                    <th class="th-time"><?php _e('Wednesday','user-blocker'); ?></th>
                    <th class="th-time"><?php _e( 'Thursday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e('Friday','user-blocker'); ?></th>
                    <th class="th-time"><?php _e('Saturday','user-blocker'); ?></th>
                    <th style="text-align:center"><?php _e('Message','user-blocker'); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="th-role"><?php _e( 'Role','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e('Sunday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e('Monday','user-blocker'); ?></th>
                    <th class="th-time"><?php _e('Tuesday','user-blocker'); ?></th>
                    <th class="th-time"><?php _e('Wednesday','user-blocker'); ?></th>
                    <th class="th-time"><?php _e( 'Thursday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e('Friday','user-blocker'); ?></th>
                    <th class="th-time"><?php _e('Saturday','user-blocker'); ?></th>
                    <th style="text-align:center"><?php _e('Message','user-blocker'); ?></th>
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
                                    <span class="trash"><a title="Reset this item" href="?page=blocked_user_list&reset=1&role=<?php echo $key; ?>"><?php _e( 'Reset','user-blocker' ); ?></a></span>
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
                                            echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker');
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
                                            echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker' );
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
                                            echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker' );
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
                                            echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                       echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker' );
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
                                            echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker' );
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
                                            echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker' );
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
                                            echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker' );
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
                        <tr><td colspan="9" style="text-align:center"><?php echo __('No records Found.','user-blocker' ); ?></td></tr>
                        <?php
                    }
                }
                else
                {
                    ?>
                    <tr><td colspan="9" style="text-align:center"><?php echo __('No records Found.','user-blocker' ); ?></td></tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <table class="widefat post fixed users_records striped" <?php if($display == 'roles') echo 'style="display:none"'; ?>>
            <thead>
                <tr>
                    <th class="sr-no"><?php _e( 'S.N.','user-blocker' ); ?></th>
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
                            <span><?php _e( 'Username','user-blocker'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-role"><?php _e( 'Role','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Sunday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Monday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Tuesday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Wednesday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Thursday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Friday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Saturday','user-blocker' ); ?></th>
                    <th style="text-align:center"><?php _e( 'Message','user-blocker' ); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="sr-no"><?php _e( 'S.N.','user-blocker' ); ?></th>
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
                            <span><?php _e( 'Username','user-blocker'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-role"><?php _e( 'Role','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Sunday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Monday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Tuesday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Wednesday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Thursday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Friday','user-blocker' ); ?></th>
                    <th class="th-time"><?php _e( 'Saturday','user-blocker' ); ?></th>
                    <th style="text-align:center"><?php _e( 'Message','user-blocker' ); ?></th>
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
                                    <span class="trash"><a title="Reset this item" href="?page=blocked_user_list&reset=1&paged=<?php echo $paged; ?>&username=<?php echo $user->ID; ?>&role=<?php echo $srole; ?>&txtUsername=<?php echo $txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>"><?php _e( 'Reset','user-blocker' ); ?></a></span>
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
                                            echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker' );
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
                                            echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker' );
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
                                            echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker' );
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
                                            echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker' );
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
                                            echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker' );
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
                                           echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker' );
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
                                            echo __( 'not set','user-blocker' );
                                        }
                                        else {
                                            echo timeToTwelveHour($from_time);
                                        }
                                        if( $from_time != '' && $to_time != '' ) {
                                            echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                        }
                                    }
                                    else {
                                        echo __( 'not set','user-blocker' );
                                    }
                                }
                                else {
                                    echo __( 'not set','user-blocker' );
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
                    ?>
                    <tr><td colspan="11" style="text-align:center"><?php _e( "No records found.","user-blocker" ); ?></td></tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        </div>        
    </div>
    <?php
}

/**
 * 
 * @global type $wpdb
 * @global type $wp_roles
 * @return html Display datewise block user list
 */
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
                $msg = __( 'Invalid user for reset blocking time.','user-blocker' );
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
        if( $msg != '' ) {
            ?>
            <div class="ublocker-notice <?php echo $msg_class; ?>">
                <p><?php echo $msg; ?></p>
            </div>
            <?php
        }
        ?>
        <div class="tab_parent_parent">
            <div class="tab_parent">
                <ul>
                    <li><a href="?page=blocked_user_list"><?php _e( 'Blocked User List By Time','user-blocker' ); ?></a></li>
                    <li><a class="current" href="?page=datewise_blocked_user_list"><?php _e( 'Blocked User List By Date','user-blocker' ); ?></a></li>
                    <li><a href="?page=permanent_blocked_user_list"><?php _e( 'Blocked User List Permanently','user-blocker'); ?></a></li>
                    <li><a href="?page=all_type_blocked_user_list"><?php _e( 'Blocked User List','user-blocker'); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="cover_form">
        <div class="search_box">
            <div class="tablenav top">                
                <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                    <div class="actions">
                        <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                        <select name="display" id="display_status">
                            <option value="users" <?php echo selected($display,'users') ?> ><?php _e( 'Users','user-blocker'); ?></option>
                            <option value="roles" <?php echo selected($display,'roles') ?>><?php _e( 'Roles','user-blocker'); ?></option>
                        </select>
                        <?php //Pagination -top ?>
                        <div class="tablenav-pages" <?php if((int)$total_pages <= 1) { echo 'style="display: none;"'; } ?>>
                            <span class="displaying-num"><?php echo $total_items; ?> items</span>
                            <span class="pagination-links">
                                <a class="first-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=datewise_blocked_user_list&paged=1&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the first page">&laquo;</a>
                                <a class="prev-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=datewise_blocked_user_list&paged='.$prev_page.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the previous page">&lsaquo;</a>
                                <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page">
                                        <?php _e( 'of','user-blocker'); ?>
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
                                <option value=""><?php _e( 'All Roles','user-blocker' ); ?></option>
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
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="<?php esc_attr_e('username or email or first name','user-blocker'); ?>" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="<?php _e( 'Search','user-blocker' ); ?>" name="filter_action">
                            <a class="button" href="<?php echo '?page=datewise_blocked_user_list'; ?>" style="margin-left: 10px;"><?php _e( 'Reset','user-blocker'); ?></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <table class="widefat post role_records striped" <?php if($display == 'roles') echo 'style="display: table"'; ?>>
            <thead>
                <tr>
                    <th class="th-role"><?php _e( 'Role','user-blocker' ); ?></th>
                    <th class="blk-date"><?php _e( 'Block Date','user-blocker' ); ?></th>
                    <th style="text-align:center"><?php _e( 'Message','user-blocker' ); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="th-role"><?php _e( 'Role','user-blocker' ); ?></th>
                    <th class="blk-date"><?php _e( 'Block Date','user-blocker' ); ?></th>
                    <th style="text-align:center"><?php _e( 'Message','user-blocker' ); ?></th>
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
                                    <span class="trash"><a title="Reset this item" href="?page=datewise_blocked_user_list&reset=1&role=<?php echo $key; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>"><?php _e( 'Reset','user-blocker' ); ?></a></span>
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
                        <tr><td colspan="3" style="text-align:center"><?php echo __('No records found.','user-blocker'); ?></td></tr>
                        <?php
                    }
                }
                else
                {
                    ?>
                    <tr><td colspan="3" style="text-align:center"><?php echo __('No records found.','user-blocker'); ?></td></tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <table class="widefat post fixed users_records striped" <?php if($display == 'roles') echo 'style="display:none"'; ?>>
            <thead>
                <tr>
                    <th class="sr-no"><?php _e( 'S.N.','user-blocker' ); ?></th>
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
                            <span><?php _e( 'Username','user-blocker'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-name sortable <?php echo strtolower($order); ?>">
                        <a href="?page=datewise_blocked_user_list&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span><?php _e( 'Name','user-blocker'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-email sortable <?php echo strtolower($order); ?>">
                        <a href="?page=datewise_blocked_user_list&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span><?php _e( 'Email','user-blocker'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-role"><?php _e( 'Role','user-blocker'); ?></th>
                    <th class="th-time"><?php _e( 'Block Date','user-blocker'); ?></th>
                    <th style="text-align:center"><?php _e( 'Message','user-blocker'); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="sr-no"><?php _e( 'S.N.','user-blocker' ); ?></th>
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
                            <span><?php _e( 'Username','user-blocker'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-name sortable <?php echo strtolower($order); ?>">
                        <a href="?page=datewise_blocked_user_list&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span><?php _e( 'Name','user-blocker'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-email sortable <?php echo strtolower($order); ?>">
                        <a href="?page=datewise_blocked_user_list&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span><?php _e( 'Email','user-blocker'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                     <th class="th-role"><?php _e( 'Role','user-blocker'); ?></th>
                    <th class="th-time"><?php _e( 'Block Date','user-blocker'); ?></th>
                    <th style="text-align:center"><?php _e( 'Message','user-blocker'); ?></th>
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
                                    <span class="trash"><a title="Reset this item" href="?page=datewise_blocked_user_list&reset=1&paged=<?php echo $paged; ?>&username=<?php echo $user->ID; ?>&role=<?php echo $srole; ?>&txtUsername=<?php echo $txtUsername; ?>"><?php _e( 'Reset','user-blocker'); ?></a></span>
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
                    ?>
                    <tr><td colspan="7" style="text-align:center">
                        <?php _e( 'No Record Found.','user-blocker'); ?>
                    </td></tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php
}

/**
 * 
 * @global type $wpdb
 * @global type $wp_roles
 * @return html Display permanent block user list
 */
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
            <?php
        }
        ?>
        <div class="tab_parent_parent">
            <div class="tab_parent">
                <ul>
                    <li><a href="?page=blocked_user_list"><?php _e( 'Blocked User List By Time','user-blocker' ); ?></a></li>
                    <li><a href="?page=datewise_blocked_user_list"><?php _e( 'Blocked User List By Date','user-blocker' ); ?></a></li>
                    <li><a class="current" href="?page=permanent_blocked_user_list"><?php _e( 'Blocked User List Permanently','user-blocker'); ?></a></li>
                    <li><a href="?page=all_type_blocked_user_list"><?php _e( 'Blocked User List','user-blocker'); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="cover_form">
        <div class="search_box">
            <div class="tablenav top">                
                <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                    <div class="actions">
                        <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                        <select name="display" id="display_status">
                            <option value="users" <?php echo selected($display,'users') ?> ><?php _e( 'Users','user-blocker' ); ?></option>
                            <option value="roles" <?php echo selected($display,'roles') ?>><?php _e( 'Roles','user-blocker' ); ?></option>
                        </select>
                        <?php //Pagination -top ?>
                        <div class="tablenav-pages" <?php if((int)$total_pages <= 1) { echo 'style="display: none;"'; } ?>>
                            <span class="displaying-num"><?php echo $total_items; ?> items</span>
                            <span class="pagination-links">
                                <a class="first-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=permanent_blocked_user_list&paged=1&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the first page">&laquo;</a>
                                <a class="prev-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=permanent_blocked_user_list&paged='.$prev_page.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the previous page">&lsaquo;</a>
                                <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page">
                                        <?php _e( 'of','user-blocker' ); ?>
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
                                <option value=""><?php _e( 'All Roles','user-blocker' ); ?></option>
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
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="<?php esc_attr_e('username or email or first name','user-blocker'); ?>" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="<?php esc_attr_e( 'Search','user-blocker' ); ?>" name="filter_action">
                            <a class="button" href="<?php echo '?page=permanent_blocked_user_list'; ?>" style="margin-left: 10px;"><?php _e( 'Reset','user-blocker' ); ?></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <table class="widefat post role_records striped" <?php if($display == 'roles') echo 'style="display: table"'; ?>>
            <thead>
                <tr>
                    <th class="th-role"><?php _e( 'Role','user-blocker' ); ?></th>
                    <th style="text-align:center"><?php _e( 'Message','user-blocker' ); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="th-role"><?php _e( 'Role','user-blocker' ); ?></th>
                    <th style="text-align:center"><?php _e( 'Message','user-blocker' ); ?></th>
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
                                    <span class="trash"><a title="Reset this item" href="?page=permanent_blocked_user_list&reset=1&role=<?php echo $key; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>"><?php _e( 'Reset','user-blocker' ); ?></a></span>
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
                        <tr><td colspan="2" style="text-align:center"><?php echo __('No records found.','user-blocker'); ?></td></tr>
                        <?php
                    }
                }
                else
                {
                    ?>
                    <tr><td colspan="2" style="text-align:center"><?php echo __('No records found.','user-blocker'); ?></td></tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <table class="widefat post fixed users_records striped" <?php if($display == 'roles') echo 'style="display:none"'; ?>>
            <thead>
                <tr>
                    <th class="sr-no"><?php _e( 'S.N.','user-blocker' ); ?></th>
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
                            <span><?php _e( 'Username','user-blocker' ); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-name sortable <?php echo strtolower($order); ?>">
                        <a href="?page=permanent_blocked_user_list&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span><?php _e( 'Name','user-blocker' ); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-email sortable <?php echo strtolower($order); ?>">
                        <a href="?page=permanent_blocked_user_list&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span><?php _e( 'Email','user-blocker' ); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-role"><?php _e( 'Role','user-blocker' ); ?></th>
                    <th style="text-align:center"><?php _e( 'Message','user-blocker' ); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="sr-no"><?php _e( 'S.N.','user-blocker' ); ?></th>
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
                            <span><?php _e( 'Username','user-blocker' ); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-name sortable <?php echo strtolower($order); ?>">
                        <a href="?page=permanent_blocked_user_list&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span><?php _e( 'Name','user-blocker' ); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-email sortable <?php echo strtolower($order); ?>">
                        <a href="?page=permanent_blocked_user_list&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span><?php _e( 'Email','user-blocker' ); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-time"><?php _e( 'Role','user-blocker' ); ?></th>
                    <th style="text-align:center"><?php _e( 'Message','user-blocker' ); ?></th>
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
                                    <span class="trash"><a title="Reset this item" href="?page=permanent_blocked_user_list&reset=1&paged=<?php echo $paged; ?>&username=<?php echo $user->ID; ?>&role=<?php echo $srole; ?>&txtUsername=<?php echo $txtUsername; ?>"><?php _e( 'Reset','user-blocker' ); ?></a></span>
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
                else { ?>
                    <tr><td colspan="6" style="text-align:center">
                        <?php _e( 'No records Found.','user-blocker' ); ?>
                    </td></tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php
}

/**
 * 
 * @global type $wpdb
 * @global type $wp_roles
 * @return html Display all type block user list
 */
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
                $msg = __( 'Invalid user for reset blocking.','user-blocker');
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
            <?php
        }
        ?>
        <div class="tab_parent_parent">
            <div class="tab_parent">
                <ul>
                    <li><a href="?page=blocked_user_list"><?php _e( 'Blocked User List By Time','user-blocker'); ?></a></li>
                    <li><a href="?page=datewise_blocked_user_list"><?php _e( 'Blocked User List By Date','user-blocker' );?></a></li>
                    <li><a href="?page=permanent_blocked_user_list"><?php _e( 'Blocked User List Permanently','user-blocker'); ?></a></li>
                    <li><a class='current' href="?page=all_type_blocked_user_list"><?php _e( 'Blocked User List','user-blocker' ); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="cover_form">
        <div class="search_box">
            <div class="tablenav top">                
                <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                    <div class="actions">
                        <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                        <select name="display" id="display_status">
                            <option value="users" <?php echo selected($display,'users') ?> ><?php _e( 'Users','user-blocker'); ?></option>
                            <option value="roles" <?php echo selected($display,'roles') ?>><?php _e( 'Roles','user-blocker'); ?></option>
                        </select>
                        <?php //Pagination -top ?>
                        <div class="tablenav-pages" <?php if((int)$total_pages <= 1) { echo 'style="display: none;"'; } ?>>
                            <span class="displaying-num"><?php echo $total_items; ?> <?php _e( 'Items','user-blocker'); ?></span>
                            <span class="pagination-links">
                                <a class="first-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=all_type_blocked_user_list&paged=1&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the first page">&laquo;</a>
                                <a class="prev-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=all_type_blocked_user_list&paged='.$prev_page.'&role='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the previous page">&lsaquo;</a>
                                <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page">
                                        <?php _e( 'of','user-blocker'); ?>
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
                                <option value=""><?php _e( 'All Roles','user-blocker'); ?></option>
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
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="<?php esc_attr_e('username or email or first name','user-blocker'); ?>" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="<?php esc_attr_e('Search','user-blocker' ); ?>" name="filter_action">
                            <a class="button" href="<?php echo '?page=all_type_blocked_user_list'; ?>" style="margin-left: 10px;"><?php esc_attr_e( 'Reset','user-blocker' ); ?></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <table class="widefat post role_records striped" <?php if($display == 'roles') echo 'style="display: table"'; ?>>
            <thead>
                <tr>
                    <th class="th-role"><?php _e( 'Role','user-blocker'); ?></th>
                    <th style="text-align:center"><?php _e( 'Message','user-blocker' ); ?></th>
                    <th class="th-username"><?php _e( 'Block Data','user-blocker' ); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="th-role"><?php _e( 'Role','user-blocker'); ?></th>
                    <th style="text-align:center"><?php _e( 'Message','user-blocker' ); ?></th>
                    <th class="th-username"><?php _e( 'Block Data','user-blocker' ); ?></th>
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
                                    <span class="trash"><a title="Reset this item" href="?page=all_type_blocked_user_list&reset=1&role=<?php echo $key; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>"><?php _e( 'Reset','user-blocker'); ?></a></span>
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
                        <tr><td colspan="3" style="text-align:center"><?php echo __('No records found.','user-blocker' ); ?></td></tr>
                        <?php
                    }
                }
                else
                {
                    ?>
                    <tr><td colspan="3" style="text-align:center"><?php echo __( 'No records found.','user-blocker' ); ?></td></tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <table class="widefat post fixed users_records striped" <?php if($display == 'roles') echo 'style="display:none"'; ?>>
            <thead>
                <tr>
                    <th class="sr-no"><?php _e( 'S.N.','user-blocker' ); ?></th>
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
                            <span><?php _e('Username','user-blocker'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-name sortable <?php echo strtolower($order); ?>">
                        <a href="?page=all_type_blocked_user_list&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span><?php _e('Name','user-blocker'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-email sortable <?php echo strtolower($order); ?>">
                        <a href="?page=all_type_blocked_user_list&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span><?php _e( 'Email','user-blocker' ); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-username"><?php _e( 'Role','user-blocker' ); ?></th>
                    <th style="text-align:center"><?php _e('Message','user-blocker'); ?></th>
                    <th class="th-username aligntextcenter"><?php _e('Block Data','user-blocker');  ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="sr-no"><?php _e('S.N.','user-blocker'); ?></th>
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
                            <span><?php _e('Username','user-blocker'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-name sortable <?php echo strtolower($order); ?>">
                        <a href="?page=all_type_blocked_user_list&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span><?php _e('Name','user-blocker'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-email sortable <?php echo strtolower($order); ?>">
                        <a href="?page=all_type_blocked_user_list&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>&paged=<?php echo $paged; ?>">
                            <span><?php _e( 'Email','user-blocker' ); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="th-username"><?php _e( 'Role','user-blocker' ); ?></th>
                    <th style="text-align:center"><?php _e('Message','user-blocker'); ?></th>
                    <th class="th-username aligntextcenter"><?php _e('Block Data','user-blocker' ); ?></th>
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
                                    <span class="trash"><a title="Reset this item" href="?page=all_type_blocked_user_list&reset=1&paged=<?php echo $paged; ?>&username=<?php echo $user->ID; ?>&role=<?php echo $srole; ?>&txtUsername=<?php echo $txtUsername; ?>"><?php _e( 'Reset','user-blocker' ); ?></a></span>
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
                    echo '<tr><td colspan="7" style="text-align:center">'. __('No records found.','user-blocker' ) .'</td></tr>';
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php
}

/**
 * 
 * @global type $wpdb
 * @global type $wp_roles
 * @retun  html Display block user page
 */
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
    $default_msg = __( 'You are temporary blocked.','user-blocker');
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
    $display_users = 1;
    $is_display_role = 0;
    $username = '';
    $srole= '';
    $role = '';
    $block_msg_day = '';
    
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
            $curr_edit_msg = __( 'Update for user with username: ','user-blocker' ) .$user_data->user_login;
        }
        else {
            $msg_class = 'error';
            $msg = __( 'User with ','user-blocker' ) . get_data('username') . __( ' userid is not exist.','user-blocker' );
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
                                $msg = __( 'Blocking time for ','user-blocker') . $role_name . __( ' is successfully updated.','user-blocker' );
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
                                $msg = __( 'Blocking time for ','user-blocker')  . $role_name . __(' is successfully updated.','user-blocker' );
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
                                    $msg = __( 'Role wise time blocking is successfully added.','user-blocker' );
                                    $txtSunFrom = $txtSunTo = $txtMonFrom = $txtMonTo = $txtTueFrom = $txtTueTo = $txtWedFrom = $txtWedTo = $txtThuFrom = $txtThuTo = $txtThuTo = $txtFriFrom = $txtFriTo = $txtSatFrom = $txtSatTo = '';
                                    $cmbUserBy = '';
                                    $block_msg_day = '';
                                }
                            }
                            else {
                                $msg_class = 'error';
                                $msg = __('Please select atleast one role.','user-blocker' );
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
                                $msg = __('Username wise time blocking is successfully added.','user-blocker');
                                $txtSunFrom = $txtSunTo = $txtMonFrom = $txtMonTo = $txtTueFrom = $txtTueTo = $txtWedFrom = $txtWedTo = $txtThuFrom = $txtThuTo = $txtThuTo = $txtFriFrom = $txtFriTo = $txtSatFrom = $txtSatTo = '';
                                $cmbUserBy = '';
                                $block_msg_day = '';
                            }
                            else {
                                $msg_class = 'error';
                                $msg = __( 'Please select atleast one username.','user-blocker' );
                                $block_msg_day = trim( $_POST['block_msg_day'] );
                            }
                        }
                        $btnVal = 'Block User';
                        $reocrd_id = array();
                    }

                }
                else {
                    $msg_class = 'error';
                    $msg = __('Please add from time for respected to time.','user-blocker');
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
                $msg = __('Please enter valid time format.','user-blocker' );
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
            $msg = __( 'Time can\'t be blank.','user-blocker');
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
    $txtUsername = '';
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
        $cmbUserBy = 'role';
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
        if( $msg != '' ) {
            ?>
            <div class="ublocker-notice <?php echo $msg_class; ?>">
                <p><?php echo $msg; ?></p>
            </div>
            <?php
        }
        ?>
            <h2 class="ublocker-page-title"><?php _e( 'Block Users By Time', 'user-blocker') ?></h2>
            <div class="tab_parent_parent"><div class="tab_parent"><ul><li><a class="current" href="?page=block_user"><?php _e( 'Block User By Time','user-blocker' ); ?></a></li><li><a href="?page=block_user_date"><?php _e( 'Block User By Date','user-blocker' ); ?></a></li><li><a href="?page=block_user_permenant"><?php _e( 'Block User Permanent','user-blocker' ); ?></a></li></ul></div></div>
            <div class="cover_form">
            <?php
            //Visible only if not set in edit mode
            //if( true ) {
            ?>
            
            <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                <div class="tablenav top">
                    <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                    <select name="cmbUserBy" id="cmbUserBy" onchange="changeUserBy();">
                        <option <?php echo selected($cmbUserBy, 'username'); ?> value="username"><?php _e('Username','user-blocker'); ?></option>
                        <option <?php echo selected($cmbUserBy, 'role'); ?> value="role" ><?php _e( 'Role','user-blocker' ); ?></option>
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
                                        <?php _e('of','user-blocker'); ?>
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
                                <option value=""><?php _e( 'All Roles','user-blocker' ); ?></option>
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
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="<?php esc_attr_e('username or email or first name','user-blocker'); ?>" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="<?php esc_attr_e('Search','user-blocker'); ?>" name="filter_action">
                            <a class="button" href="<?php echo '?page=block_user&resetsearch=1'; ?>" style="margin-left: 10px;"><?php _e('Reset','user-blocker' ); ?></a>
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
            <table id="role" class="widefat post fixed user-records striped" <?php if( $display_users == 1 ) echo 'style="display: none;width: 100%;"'; else echo 'style="width: 100%;"'; ?>>
                <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <th class="user-role"><?php _e( 'Role','user-blocker' ); ?></th>
                        <th class="th-time aligntextcenter"><?php _e('Block Time','user-blocker' ); ?></th>
                        <th class="blk-msg aligntextcenter"><?php _e('Block Message','user-blocker'); ?></th>
                        <th class="tbl-action aligntextcenter"><?php _e( 'Action','user-blocker' ); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <th class="user-role"><?php _e( 'Role','user-blocker' ); ?></th>
                        <th class="th-time aligntextcenter"><?php _e('Block Time','user-blocker' ); ?></th>
                        <th class="blk-msg aligntextcenter"><?php _e('Block Message','user-blocker'); ?></th>
                        <th class="tbl-action aligntextcenter"><?php _e( 'Action','user-blocker' ); ?></th>
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
                                                    <th align="center"><?php _e( 'Sunday','user-blocker'); ?></th>
                                                    <th align="center"><?php _e( 'Monday','user-blocker'); ?></th>
                                                    <th align="center"><?php _e( 'Tuesday','user-blocker'); ?></th>
                                                    <th align="center"><?php _e( 'Wednesday','user-blocker'); ?></th>
                                                    <th align="center"><?php _e( 'Thursday','user-blocker'); ?></th>
                                                    <th align="center"><?php _e( 'Friday','user-blocker'); ?></th>
                                                    <th align="center"><?php _e( 'Saturday','user-blocker'); ?></th>
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
                                                                echo __( 'not set','user-blocker');
                                                            }
                                                            else {
                                                                echo timeToTwelveHour($from_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo __( 'not set','user-blocker');
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if (array_key_exists('monday',$block_day)) {
                                                            $from_time = $block_day['monday']['from'];
                                                            $to_time = $block_day['monday']['to'];
                                                            if( $from_time == '' ) {
                                                                echo __( 'not set','user-blocker');
                                                            }
                                                            else {
                                                                echo timeToTwelveHour($from_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo __( 'not set','user-blocker');
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if (array_key_exists('tuesday',$block_day)) {
                                                            $from_time = $block_day['tuesday']['from'];
                                                            $to_time = $block_day['tuesday']['to'];
                                                            if( $from_time == '' ) {
                                                                echo __( 'not set','user-blocker');
                                                            }
                                                            else {
                                                                echo timeToTwelveHour($from_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo __( 'not set','user-blocker');
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if (array_key_exists('wednesday',$block_day)) {
                                                            $from_time = $block_day['wednesday']['from'];
                                                            $to_time = $block_day['wednesday']['to'];
                                                            if( $from_time == '' ) {
                                                               echo __( 'not set','user-blocker');
                                                            }
                                                            else {
                                                                echo timeToTwelveHour($from_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo __( 'not set','user-blocker');
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if (array_key_exists('thursday',$block_day)) {
                                                            $from_time = $block_day['thursday']['from'];
                                                            $to_time = $block_day['thursday']['to'];
                                                            if( $from_time == '' ) {
                                                                echo __( 'not set','user-blocker');
                                                            }
                                                            else {
                                                                echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo ' to '.timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo __( 'not set','user-blocker');
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if (array_key_exists('friday',$block_day)) {
                                                            $from_time = $block_day['friday']['from'];
                                                            $to_time = $block_day['friday']['to'];
                                                            if( $from_time == '' ) {
                                                                echo __( 'not set','user-blocker');
                                                            }
                                                            else {
                                                                echo timeToTwelveHour($from_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo __( 'not set','user-blocker');
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if (array_key_exists('saturday',$block_day)) {
                                                            $from_time = $block_day['saturday']['from'];
                                                            $to_time = $block_day['saturday']['to'];
                                                            if( $from_time == '' ) {
                                                                echo __( 'not set','user-blocker');
                                                            }
                                                            else {
                                                                echo timeToTwelveHour($from_time);
                                                            }
                                                            if( $from_time != '' && $to_time != '' ) {
                                                                echo __(' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                            }
                                                        }
                                                        else {
                                                            echo __( 'not set','user-blocker');
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
            <table id="username" class="widefat post fixed user-records striped" <?php if($display_users == 1 ) echo 'style="display: table;"'; else echo 'style="display: none;"'; ?>>
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
                                <span><?php _e( 'Username','user-blocker'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-name sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span><?php _e( 'Name','user-blocker'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-email sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span><?php _e( 'Email','user-blocker'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-role"><?php _e( 'Role','user-blocker'); ?></th>
                        <th class="th-time aligntextcenter"><?php _e( 'Block Time','user-blocker'); ?></th>
                        <th class="blk-msg aligntextcenter"><?php _e( 'Block Message','user-blocker'); ?></th>
                        <th class="tbl-action aligntextcenter"><?php _e( 'Action','user-blocker'); ?></th>
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
                                <span><?php _e( 'Username','user-blocker'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-name sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span><?php _e( 'Name','user-blocker'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-email sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span><?php _e( 'Email','user-blocker'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-role"><?php _e( 'Role','user-blocker'); ?></th>
                        <th class="th-time aligntextcenter"><?php _e( 'Block Time','user-blocker'); ?></th>
                        <th class="blk-msg aligntextcenter"><?php _e( 'Block Message','user-blocker'); ?></th>
                        <th class="tbl-action"><?php _e( 'Action','user-blocker'); ?></th>
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
                                                <th align="center"><?php _e( 'Sunday','user-blocker'); ?></th>
                                                <th align="center"><?php _e( 'Monday','user-blocker'); ?></th>
                                                <th align="center"><?php _e( 'Tuesday','user-blocker'); ?></th>
                                                <th align="center"><?php _e( 'Wednesday','user-blocker'); ?></th>
                                                <th align="center"><?php _e( 'Thursday','user-blocker'); ?></th>
                                                <th align="center"><?php _e( 'Friday','user-blocker'); ?></th>
                                                <th align="center"><?php _e( 'Saturday','user-blocker'); ?></th>
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
                                                            echo __('not set','user-blocker' );
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                            echo __( ' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                       echo __('not set','user-blocker' );
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php
                                                    if (array_key_exists('monday',$block_day)) {
                                                        $from_time = $block_day['monday']['from'];
                                                        $to_time = $block_day['monday']['to'];
                                                        if( $from_time == '' ) {
                                                            echo __('not set','user-blocker' );
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                            echo __( ' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                        echo __('not set','user-blocker' );
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php
                                                    if (array_key_exists('tuesday',$block_day)) {
                                                        $from_time = $block_day['tuesday']['from'];
                                                        $to_time = $block_day['tuesday']['to'];
                                                        if( $from_time == '' ) {
                                                            echo __('not set','user-blocker' );
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                            echo __( ' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                        echo __('not set','user-blocker' );
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php
                                                    if (array_key_exists('wednesday',$block_day)) {
                                                        $from_time = $block_day['wednesday']['from'];
                                                        $to_time = $block_day['wednesday']['to'];
                                                        if( $from_time == '' ) {
                                                            echo __('not set','user-blocker' );
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                           echo __( ' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                        echo __('not set','user-blocker' );
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php
                                                    if (array_key_exists('thursday',$block_day)) {
                                                        $from_time = $block_day['thursday']['from'];
                                                        $to_time = $block_day['thursday']['to'];
                                                        if( $from_time == '' ) {
                                                            echo __('not set','user-blocker' );
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                            echo __( ' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                        echo __('not set','user-blocker' );
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php
                                                    if (array_key_exists('friday',$block_day)) {
                                                        $from_time = $block_day['friday']['from'];
                                                        $to_time = $block_day['friday']['to'];
                                                        if( $from_time == '' ) {
                                                            echo __('not set','user-blocker' );
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                            echo __( ' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                        echo __('not set','user-blocker' );
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php
                                                    if (array_key_exists('saturday',$block_day)) {
                                                        $from_time = $block_day['saturday']['from'];
                                                        $to_time = $block_day['saturday']['to'];
                                                        if( $from_time == '' ) {
                                                            echo __('not set','user-blocker' );
                                                        }
                                                        else {
                                                            echo timeToTwelveHour($from_time);
                                                        }
                                                        if( $from_time != '' && $to_time != '' ) {
                                                            echo __( ' to ','user-blocker' ).timeToTwelveHour($to_time);
                                                        }
                                                    }
                                                    else {
                                                        echo __('not set','user-blocker' );
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
                else { ?>
                            <tr><td colspan="8" align="center">
                                    <?php _e('No records found.','user-blocker'); ?>
                                </td></tr>
               <?php }
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
                        <h3 class="block_msg_title"><?php _e( 'Block Time ','user-blocker' ); ?> <?php if( isset($curr_edit_msg) && $curr_edit_msg != '' ) echo '<span>' . $curr_edit_msg . '</span>'; ?></h3>
                    </td>
                </tr>
                <tr>
                    <th class="week-lbl"><?php _e( 'Sunday','user-blocker' ); ?></th>
                    <th class="week-lbl"><?php _e( 'Monday','user-blocker'); ?></th>
                    <th class="week-lbl"><?php _e( 'Tuesday','user-blocker' ); ?></th>
                    <th class="week-lbl"><?php _e('Wednesday','user-blocker' ); ?></th>
                    <th class="week-lbl"><?php _e('Thursday','user-blocker' ); ?></th>
                    <th class="week-lbl"><?php _e( 'Friday','user-blocker' ); ?></th>
                    <th class="week-lbl"><?php _e('Saturday','user-blocker'); ?></th>
                </tr>
                <tr>
                    <td class="week-time" id="week-sun" align="center">
                        <input tabindex="1" value="<?php echo timeToTwelveHour($txtSunFrom); ?>" class="time start time-field" type="text" name="txtSunFrom" id="txtSunFrom" />
                        <span>&nbsp;<?php _e( 'to','user-blocker' ); ?>&nbsp;</span>
                        <input tabindex="2" value="<?php echo timeToTwelveHour($txtSunTo); ?>" class="time end time-field" type="text" name="txtSunTo" id="txtSunTo" />
<!--                        <input type="checkbox" class="chkapply" id="chkapply" />-->
                    </td>
                    <td class="week-time" id="week-mon" align="center">
                        <input tabindex="3" value="<?php echo timeToTwelveHour($txtMonFrom); ?>" class="time start time-field" type="text" name="txtMonFrom" id="txtMonFrom" />
                        <span>&nbsp;<?php _e( 'to','user-blocker' ); ?>&nbsp;</span>
                        <input tabindex="4" value="<?php echo timeToTwelveHour($txtMonTo); ?>" class="time end time-field" type="text" name="txtMonTo" id="txtMonTo" />
                    </td>
                    <td class="week-time" id="week-tue" align="center">
                        <input tabindex="5" value="<?php echo timeToTwelveHour($txtTueFrom); ?>" class="time start time-field" type="text" name="txtTueFrom" id="txtTueFrom" />
                        <span>&nbsp;<?php _e( 'to','user-blocker' ); ?>&nbsp;</span>
                        <input tabindex="6" value="<?php echo timeToTwelveHour($txtTueTo); ?>" class="time end time-field" type="text" name="txtTueTo" id="txtTueTo" />
                    </td>
                    <td class="week-time" id="week-wed" align="center">
                        <input tabindex="7" value="<?php echo timeToTwelveHour($txtWedFrom); ?>" class="time start time-field" type="text" name="txtWedFrom" id="txtWedFrom" />
                        <span>&nbsp;<?php _e( 'to','user-blocker' ); ?>&nbsp;</span>
                        <input tabindex="8" value="<?php echo timeToTwelveHour($txtWedTo); ?>" class="time end time-field" type="text" name="txtWedTo" id="txtWedTo" />
                    </td>
                    <td class="week-time" id="week-thu" align="center">
                        <input tabindex="9" value="<?php echo timeToTwelveHour($txtThuFrom); ?>" class="time start time-field" type="text" name="txtThuFrom" id="txtThuFrom" />
                        <span>&nbsp;<?php _e( 'to','user-blocker' ); ?>&nbsp;</span>
                        <input tabindex="10" value="<?php echo timeToTwelveHour($txtThuTo); ?>" class="time end time-field" type="text" name="txtThuTo" id="txtThuTo" />
                    </td>
                    <td class="week-time" id="week-fri" align="center">
                        <input tabindex="11" value="<?php echo timeToTwelveHour($txtFriFrom); ?>" class="time start time-field" type="text" name="txtFriFrom" id="txtFriFrom" />
                        <span>&nbsp;<?php _e( 'to','user-blocker' ); ?>&nbsp;</span>
                        <input tabindex="12" value="<?php echo timeToTwelveHour($txtFriTo); ?>" class="time end time-field" type="text" name="txtFriTo" id="txtFriTo" />
                    </td>
                    <td class="week-time" id="week-sat" align="center">
                        <input tabindex="13" value="<?php echo timeToTwelveHour($txtSatFrom); ?>" class="time start time-field" type="text" name="txtSatFrom" id="txtSatFrom" />
                        <span>&nbsp;<?php _e( 'to','user-blocker' ); ?>&nbsp;</span>
                        <input tabindex="14" value="<?php echo timeToTwelveHour($txtSatTo); ?>" class="time end time-field" type="text" name="txtSatTo" id="txtSatTo" />
                    </td>
                </tr>               
            </table>
            <input type="button" class="button primary-button" id="chkapply" value="<?php _e( 'Apply to all','user-blocker' ); ?>"/>
            
            <h3 class="block_msg_title"><?php _e( 'Block Message','user-blocker' ); ?></h3>
            <div class="block_msg_div">
                <div class="block_msg_left">
                    <textarea style="width:500px;height: 110px" name="block_msg_day"><?php echo stripslashes($block_msg_day); ?></textarea>
                </div>
                <div class="block_msg_note_div">
                    <?php _e( 'Note: If you will not type message, default message will be ','user-blocker' ); echo "'".$default_msg."'"; ?>
                </div>
            </div>
            <?php
            if( $cmbUserBy == 'role' || $cmbUserBy == '' ) {
                $btnVal = str_replace('User', 'Role', $btnVal);
            }
            ?>
            <input id="sbt-block" style="margin: 20px 0 0 0;clear: both;float: left" class="button button-primary" type="submit" name="sbtSaveTime" value="<?php echo $btnVal; ?>">
            <?php if( isset( $btnVal ) && ( $btnVal == 'Update Blocked User' || $btnVal == 'Update Blocked Role' ) ) { ?>
            <a style="margin: 20px 0 0 10px;float: left;" href="<?php echo '?page=block_user'; ?>" class="button button-primary"><?php _e('Cancel','user-blocker'); ?></a>
            <?php } ?>
            </form>
            </div>
            <?php echo display_support_section(); ?>
    </div>
<?php
}

/**
 * 
 * @global type $wpdb
 * @global type $wp_roles
 * @return html Display block user date page
 */
function block_user_date_page() {
    global $wpdb;
    $default_msg = __('You are temporary blocked.','user-blocker');
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
    $display_users = 1;
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
                        $msg = __( 'Please select atleast one role.','user-blocker');
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
                        $msg = __( 'Selected users have beeen blocked succeefully.','user-blocker');
                        $frmdate = $todate = $block_msg_date = '';
                    }
                    else {
                        $block_msg_date = trim( $_POST['block_msg_date'] );
                        $msg_class = 'error';
                        $msg = __( 'Please select atleast one username.','user-blocker');
                    }
                }
                $btnVal = 'Block User';
                $reocrd_id = array();
            }   //database update for add and edit end
        }
        else {
            $msg_class = 'error';
            $msg = __( 'Please enter valid block date.','user-blocker');
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
    $txtUsername = '';
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
        $cmbUserBy = 'role';
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
            <div class="tab_parent_parent"><div class="tab_parent"><ul><li><a href="?page=block_user"><?php _e( 'Block User By Time','user-blocker' ); ?></a></li><li><a class="current" href="?page=block_user_date"><?php _e( 'Block User By Date','user-blocker' ); ?></a></li><li><a href="?page=block_user_permenant"><?php _e( 'Block User Permanent','user-blocker'); ?></a></li></ul></div></div>
            <div class="cover_form">
            <?php
            //Visible only if not set in edit mode
         //   if( true ) {
            ?>
            
            <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                <div class="tablenav top">
                    <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                    <select name="cmbUserBy" id="cmbUserBy" onchange="changeUserBy();">
                        <option <?php echo selected($cmbUserBy, 'username'); ?> value="username"><?php _e( 'Username', 'user-blocker') ?></option>
                        <option <?php echo selected($cmbUserBy, 'role'); ?> value="role" ><?php _e( 'Role', 'user-blocker') ?></option>
                    </select>
                    <?php //Pagination -top ?>
                    <div class="filter_div" style="float: right; <?php if( $display_users == 1 ) { echo 'display: block;'; } else { echo 'display: none;'; } ?>">
                        <div class="tablenav-pages" <?php if((int)$total_pages <= 1) { echo 'style="display: none;"'; } ?>>
                            <span class="displaying-num"><?php echo $total_items; ?> <?php _e( 'Items', 'user-blocker') ?></span>
                            <span class="pagination-links">
                                <a class="first-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=block_user_date&paged=1&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the first page">&laquo;</a>
                                <a class="prev-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=block_user_date&paged='.$prev_page.'&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the previous page">&lsaquo;</a>
                                <span class="paging-input">
                                        <input class="current-page" id="current_page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page">
                                        <?php _e( 'of', 'user-blocker') ?>
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
                                <option value=""><?php _e( 'All Roles', 'user-blocker') ?></option>
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
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="<?php esc_attr_e('username or email or first name','user-blocker'); ?>" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="<?php esc_attr_e( 'Search', 'user-blocker') ?>" name="filter_action">
                            <a class="button" href="<?php echo '?page=block_user_date&resetsearch=1'; ?>" style="margin-left: 10px;"><?php _e( 'Reset', 'user-blocker') ?></a>
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
                        <th class="user-role"><?php _e( 'Role', 'user-blocker') ?></th>
                        <th class="blk-date"><?php _e( 'Block Date', 'user-blocker') ?></th>
                        <th class="blk-msg aligntextcenter"><?php _e( 'Block Message', 'user-blocker') ?></th>
                        <th class="tbl-action"><?php _e( 'Action', 'user-blocker') ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                         <th class="user-role"><?php _e( 'Role', 'user-blocker') ?></th>
                        <th class="blk-date"><?php _e( 'Block Date', 'user-blocker') ?></th>
                        <th class="blk-msg aligntextcenter"><?php _e( 'Block Message', 'user-blocker') ?></th>
                        <th class="tbl-action"><?php _e( 'Action', 'user-blocker') ?></th>
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
                       $p_txtUsername = isset($_GET['txtUsername']) ? $_GET['txtUsername'] : '';
                       $p_srole = isset($_GET['srole']) ? $_GET['srole'] : '';
                       $p_paged = isset($_GET['paged']) ? $_GET['paged'] : '';
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
                                        echo __( 'not set','user-blocker' );
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
                         echo '<tr><td colspan="5"  align="center">'. __( 'No records found.','user-blocker' ) .'</td></tr>';
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
            <table id="username" class="widefat post fixed user-records striped" <?php if($display_users == 1 ) echo 'style="display: table;"'; else echo 'style="display: none;"'; ?>>
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
                                <span><?php _e( 'Username', 'user-blocker') ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-name sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_date&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span><?php _e( 'Name', 'user-blocker') ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-email sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_date&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span><?php _e( 'Email', 'user-blocker') ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-role"><?php _e( 'Role', 'user-blocker') ?></th>
                        <th class="blk-date"><?php _e( 'Block Date', 'user-blocker') ?></th>
                        <th class="blk-msg aligntextcenter"><?php _e( 'Block Message', 'user-blocker') ?></th>
                        <th class="tbl-action"><?php _e( 'Action', 'user-blocker') ?></th>
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
                                <span><?php _e( 'Username', 'user-blocker') ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-name sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_date&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span><?php _e( 'Name', 'user-blocker') ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-email sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_date&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span><?php _e( 'Email', 'user-blocker') ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-role"><?php _e( 'Role', 'user-blocker') ?></th>
                        <th class="blk-date"><?php _e( 'Block Date', 'user-blocker') ?></th>
                        <th class="blk-msg aligntextcenter"><?php _e( 'Block Message', 'user-blocker') ?></th>
                        <th class="tbl-action"><?php _e( 'Action', 'user-blocker') ?></th>
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
                    $p_txtUsername = isset($_GET['txtUsername']) ? $_GET['txtUsername'] : '';
                    $p_srole = isset($_GET['srole']) ? $_GET['srole'] : '';
                    $p_paged = isset($_GET['paged']) ? $_GET['paged'] : '';
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
                                    echo __( 'not set','user-blocker');
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
                else  { ?>
                    <tr><td colspan="8"  align="center">
                            <?php _e( 'No records Founds.', 'user-blocker') ?>                        
                        </td></tr>
              <?php  }
                ?>
                </tbody>
            </table>
            <h3 class="block_msg_title">Block Date <?php if( isset($curr_edit_msg) && $curr_edit_msg != '' ) echo '<span>' . $curr_edit_msg . '</span>'; ?></h3>
            <?php if( isset( $btnVal ) && $btnVal == 'Update Blocked User' ) {
               $block_day = get_user_meta($_GET['username'], 'block_day', true);
               if( $block_day != '' && $block_day != 0 ) {
                    echo '<div style="width: 990px; clear: both;">';
                    echo '<span style="display: block; padding: 5px 0;">'.__('This user is blocked for below time:','user-blocker') .'</span>';
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
            <h3 class="block_msg_title"><?php _e( 'Block Message', 'user-blocker') ?></h3>
            <div class="block_msg_div">
                <div class="block_msg_left">
                    <textarea style="width:500px;height: 110px" name="block_msg_date"><?php echo stripslashes( $block_msg_date ); ?></textarea>
                </div>
                <div class="block_msg_note_div">
                    <?php _e( "Note: If you will not type message, default message will be 'You are temporary blocked.'", 'user-blocker') ?>
                </div>
            </div>
            <?php
            if( $cmbUserBy == 'role' || $cmbUserBy == '' ) {
                $btnVal = str_replace('User', 'Role', $btnVal);
            }
            ?>
            <input id="sbt-block" style="margin: 20px 0 0 0;clear: both;float: left;" class="button button-primary" type="submit" name="sbtSaveDate" value="<?php echo $btnVal; ?>">
            <?php if( isset( $btnVal ) && $btnVal == 'Update Blocked User' ) { ?>
            <a style="margin: 20px 0 0 10px;float: left;" href="<?php echo '?page=block_user_date'; ?>" class="button button-primary"><?php _e( 'Cancel', 'user-blocker') ?></a>
            <?php } ?>
            </form>
        </div>
            <?php display_support_section(); ?>
    </div>
<?php
}

/**
 * 
 * @global type $wpdb
 * @global type $wp_roles
 * @return html Display block user permenant page
 */
function block_user_permenant_page() {
    global $wpdb;
    $sr_no = 1;
    $records_per_page = 10;
    $msg_class = '';
    $msg = '';
    $curr_edit_msg = '';
    $btnVal = __( 'Block User','user-blocker' );
    $option_name = array();
    $block_time_array = array();
    $reocrd_id = array();
    $is_active = 1;
    $block_msg_permenant = '';
    $block_msg = '';
    $paged = 1;
    $orderby = 'user_login';
    $order = 'ASC';
    $display_users = 1;
    $is_display_role= 0;
    $srole = '';
    $role = '';
    $username = '';
    $default_msg = __( 'You are permanently Blocked.','user-blocker');
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
        $btnVal = __( 'Update Blocked User','user-blocker' );
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
            $msg = __('User with ','user-blocker' ) . get_data('username') . __( ' userid is not exist.','user-blocker' );
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
                    $msg = __( 'Selected roles have beeen blocked succeefully.','user-blocker' );
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
                    $msg = __( 'Selected users have beeen blocked succeefully.','user-blocker' );
                    $username = '';
                    $block_msg_permenant = '';
                }
                else {
                    $msg_class = 'error';
                    $msg = __( 'Please select atleast one username.','user-blocker');
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
    $txtUsername = '';
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
        $cmbUserBy = 'role';
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
            <div class="tab_parent_parent"><div class="tab_parent"><ul><li><a href="?page=block_user"><?php _e( 'Block User By Time','user-blocker'); ?></a></li><li><a href="?page=block_user_date"><?php _e( 'Block User By Date','user-blocker'); ?></a></li><li><a class="current" href="?page=block_user_permenant"><?php _e( 'Block User Permanent','user-blocker'); ?></a></li></ul></div></div>
            <div class="cover_form">
            <?php
            //Visible only if not set in edit mode
            //if( true ) {
            ?>
            
            <form id="frmSearch" name="frmSearch" method="get" action="<?php echo home_url().'/wp-admin/admin.php'; ?>">
                <div class="tablenav top">
                    <label><strong><?php _e( 'Select User/Category: ', 'user-blocker') ?></strong></label>
                    <select name="cmbUserBy" id="cmbUserBy" onchange="changeUserBy();">
                        <option <?php echo selected($cmbUserBy, 'username'); ?> value="username"><?php _e( 'Username','user-blocker'); ?></option>
                        <option <?php echo selected($cmbUserBy, 'role'); ?> value="role"><?php _e( 'Role','user-blocker'); ?></option>
                    </select>
                    <?php //Pagination -top ?>
                    <div class="filter_div" style="float: right; <?php if( $display_users == 1 ) { echo 'display: block;'; } else { echo 'display: none;'; } ?>">
                        <div class="tablenav-pages" <?php if((int)$total_pages <= 1) { echo 'style="display: none;"'; } ?>>
                            <span class="displaying-num"><?php echo $total_items; ?> <?php _e( 'Items','user-blocker'); ?></span>
                            <span class="pagination-links">
                                <a class="first-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=block_user_permenant&paged=1&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the first page">&laquo;</a>
                                <a class="prev-page <?php if($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=block_user_permenant&paged='.$prev_page.'&srole='.$srole.'&txtUsername='.$txtUsername; ?>&orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" title="Go to the previous page">&lsaquo;</a>
                                <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page">
                                        <?php _e( 'of','user-blocker'); ?>
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
                                <option value=""><?php _e( 'All Roles','user-blocker'); ?></option>
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
                            <input type="text" id="txtUsername" value="<?php echo $txtUsername; ?>" placeholder="<?php esc_attr_e('username or email or first name','user-blocker'); ?>" name="txtUsername" />
                            <input id="filter_action" class="button" type="submit" value="<?php esc_attr_e( 'Search','user-blocker'); ?>" name="filter_action">
                            <a class="button" href="<?php echo '?page=block_user_permenant&resetsearch=1'; ?>" style="margin-left: 10px;"><?php _e( 'Reset','user-blocker'); ?></a>
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
            <table id="role" class="widefat post fixed user-records striped" <?php if( (isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'username') || $display_users == 1 ) echo 'style="display: none;width: 100%;"'; else echo 'style="width: 100%;"'; ?>>
                <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <th class="user-role"><?php _e( 'Role','user-blocker'); ?></th>
                        <th class="tbl-action"><?php _e( 'Status','user-blocker'); ?></th>
                        <th class="blk-msg aligntextcenter"><?php _e( 'Block Message','user-blocker'); ?></th>
                        <th class="tbl-action"><?php _e( 'Action','user-blocker'); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="check-column"><input type="checkbox" /></th>
                        <th class="user-role"><?php _e( 'Role','user-blocker'); ?></th>
                        <th class="tbl-action"><?php _e( 'Status','user-blocker'); ?></th>
                        <th class="blk-msg aligntextcenter"><?php _e( 'Block Message','user-blocker'); ?></th>
                        <th class="tbl-action"><?php _e( 'Action','user-blocker'); ?></th>
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
                       $p_txtUsername = isset($_GET['txtUsername']) ? $_GET['txtUsername'] : '';
                       $p_srole = isset($_GET['srole']) ? $_GET['srole'] : '' ;
                       $p_paged = isset($_GET['paged']) ? $_GET['paged'] : '' ;
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
            <table id="username" class="widefat post fixed user-records striped" <?php if( (isset($_POST['cmbUserBy']) && $_POST['cmbUserBy'] == 'username') || $display_users == 1 ) echo 'style="display: table;"'; else echo 'style="display: none;"'; ?>>
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
                                <span><?php _e( 'Username','user-blocker'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-name sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_permenant&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span><?php _e( 'Name','user-blocker'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-email sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_permenant&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span><?php _e( 'Email','user-blocker'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-role"><?php _e( 'Role','user-blocker'); ?></th>
                        <th class="blk-msg aligntextcenter"><?php _e( 'Block Message','user-blocker'); ?></th>
                        <th class="tbl-action"><?php _e( 'Status','user-blocker'); ?></th>
                        <th class="tbl-action"><?php _e( 'Action','user-blocker'); ?></th>
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
                                <span><?php _e( 'Username','user-blocker'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-name sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_permenant&orderby=display_name&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span><?php _e( 'Name','user-blocker'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-email sortable <?php echo strtolower($order); ?>">
                            <a href="?page=block_user_permenant&orderby=user_email&order=<?php echo $linkOrder; ?>&txtUsername=<?php echo $txtUsername; ?>&srole=<?php echo $srole; ?>">
                                <span><?php _e( 'Email','user-blocker'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th class="th-role"><?php _e( 'Role','user-blocker'); ?></th>
                        <th class="blk-msg aligntextcenter"><?php _e( 'Block Message','user-blocker'); ?></th>
                        <th class="tbl-action"><?php _e( 'Status','user-blocker'); ?></th>
                        <th class="tbl-action"><?php _e( 'Action','user-blocker'); ?></th>
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
                    $p_txtUsername = isset($_GET['txtUsername']) ? $_GET['txtUsername'] : '';
                    $p_srole = isset($_GET['srole']) ? $_GET['srole'] : '';
                    $p_paged = isset($_GET['paged']) ? $_GET['paged'] : '';
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
                else { ?>
                    <tr><td colspan="8"  align="center">
                            <?php _e('No records found.','user-blocker' ); ?>
                        </td></tr>
               <?php } ?>
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
            <h3 class="block_msg_title"><?php _e('Block Message','user-blocker'); ?> <?php if( isset($curr_edit_msg) && $curr_edit_msg != '' ) echo '<span>' . $curr_edit_msg . '</span>'; ?></h3>
            <div class="block_msg_div">
                <div class="block_msg_left">
                    <textarea style="width:500px;height: 110px" name="block_msg_permenant"><?php echo stripslashes( $block_msg_permenant ); ?></textarea>
                </div>
                <div class="block_msg_note_div">
                    <?php _e( 'Note: If you will not type message, default message will be ','user-blocker' ); echo "'".$default_msg."'"; ?>
                </div>
            </div>
            <?php
            if( $cmbUserBy == 'role' || $cmbUserBy == '' ) {
                $btnVal = str_replace('User', 'Role', $btnVal);
            }
            ?>
            <input id="sbt-block" style="margin: 20px 0 0 0;clear: both;float: left" class="button button-primary" type="submit" name="sbtSaveStatus" value="<?php echo $btnVal; ?>">
            <?php if( isset( $btnVal ) && $btnVal == 'Update Blocked User' ) { ?>
            <a style="margin: 20px 0 0 10px;float: left;" href="<?php echo '?page=block_user_permenant'; ?>" class="button button-primary"><?php _e('Cancel','user-blocker'); ?></a>
            <?php } ?>
        </form>
    </div>
            <?php display_support_section(); ?>
    <div class="ajax-loader"></div>
</div>
<?php
}

/**
 * 
 * @param type $user
 * @param type $username
 * @param type $password
 * @return \WP_Error
 */
function myplugin_auth_signon( $user, $username, $password ) {
    if(!is_wp_error($user))
    {        
        $user = get_user_by('login',$username);
        //date_default_timezone_set("Asia/Kolkata");        
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
                $current_time = current_time( 'timestamp');
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
                $frmdate = strtotime($frmdate).'</br>';
                $todate = strtotime($todate);
                $current_date = current_time( 'timestamp');
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
add_filter( 'authenticate', 'myplugin_auth_signon',30, 3 );

/**
 * 
 * @param type $user_id
 */
function user_blocking_when_register( $user_id ) {
    $user_id;
    $user_info = get_userdata($user_id);
      $user_role = $user_info->roles[0];
      $permenant_block = get_option($user_role.'_is_active');      
      if($permenant_block == 'n')
      {
       //   echo 'in';
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
     // exit();
}
add_action( 'user_register', 'user_blocking_when_register', 10, 1 );

/**
 * 
 * @param type $time
 * @return time
 */
function timeToTwentyfourHour($time) {
    if( $time != '' ) {
        $time = date('H:i:s', strtotime($time));
    }
    return $time;
}

/**
 * 
 * @param type $time
 * @return time
 */
function timeToTwelveHour($time) {
    if( $time != '' ) {
        $time = date('h:i A', strtotime($time));
    }
    return $time;
}

/**
 * 
 * @param type $time
 * @return int
 */
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
    
}

/**
 * 
 * @param type $msg
 * @return text 
 */
function disp_msg( $msg ) {
    $msg = stripslashes( nl2br($msg) );
    return $msg;
}

/**
 * 
 * @param type $day
 * @param type $block_day
 * @return html Display block time
 */
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

/**
 * 
 * @param type $role
 * @param type $old_block_day
 * @param type $block_day
 * @param type $old_block_msg_day
 * @param type $block_msg_day
 */
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

/**
 * 
 * @param type $role
 * @param type $old_block_date
 * @param type $block_date
 * @param type $old_block_msg_date
 * @param type $block_msg_date
 */
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

/**
 * 
 * @param type $role
 * @param type $is_active
 * @param type $old_block_msg_permenant
 * @param type $block_msg_permenant
 */
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

/**
 * 
 * @param type $vars
 * @return query Adding group by in get user query
 */
function sort_by_member_number( $vars ) {
    $vars->query_orderby = 'group by user_login '.$vars->query_orderby;
}

/**
 * 
 * @param type $user_id
 * @return show all block data view
 */
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

/**
 * 
 * @param type $key
 * @return type show all block data view role
 */
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

/**
 * 
 * @param type $user_id
 * @return type show all block data table
 */
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
                    <tr><td colspan='7'><label><?php _e( 'Blocked Day Detail','user-blocker' ); ?></label></td></tr>
                    <tr>
                        <th align="center"><?php _e( 'Sunday','user-blocker' ); ?></th>
                        <th align="center"><?php _e( 'Monday','user-blocker' ); ?></th>
                        <th align="center"><?php _e( 'Tuesday','user-blocker' ); ?></th>
                        <th align="center"><?php _e( 'Wednesday','user-blocker' ); ?></th>
                        <th align="center"><?php _e( 'Thursday','user-blocker' ); ?></th>
                        <th align="center"><?php _e( 'Friday','user-blocker' ); ?></th>
                        <th align="center"><?php _e( 'Saturday','user-blocker' ); ?></th>
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
                    <tr><td class="" colspan='7'><label><?php _e('Blocked Date Detail:','user-blocker' ); ?></label> <?php echo $block_date['frmdate'].' to '.$block_date['todate']; ?></td></tr>
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

/**
 * 
 * @param type $key
 * @return type show all block data table role
 */
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
                    <tr><td colspan='7'><label><?php _e( 'Blocked Day Detail','user-blocker' ); ?></label></td></tr>
                    <tr>
                       <th align="center"><?php _e( 'Sunday','user-blocker' ); ?></th>
                        <th align="center"><?php _e( 'Monday','user-blocker' ); ?></th>
                        <th align="center"><?php _e( 'Tuesday','user-blocker' ); ?></th>
                        <th align="center"><?php _e( 'Wednesday','user-blocker' ); ?></th>
                        <th align="center"><?php _e( 'Thursday','user-blocker' ); ?></th>
                        <th align="center"><?php _e( 'Friday','user-blocker' ); ?></th>
                        <th align="center"><?php _e( 'Saturday','user-blocker' ); ?></th>
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
                    <tr><td class="" colspan='7'><label><?php _e('Blocked Date Detail:','user-blocker' ); ?></label> <?php echo $block_date['frmdate'].' to '.$block_date['todate']; ?></td></tr>
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

/**
 * 
 * @param type $user_id
 * @return type Message of all block data
 */
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

/**
 * 
 * @param type $key
 * @return all block data message role
 */
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

/**
 * 
 * @param type $day
 * @param type $block_day
 * @return type Get time record
 */
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
/**
 * 
 * @param type $data
 * @param type $default_val
 * @return type Get data
 */
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


/**
 * admin scripts
 */
if (!function_exists('ublk_admin_scripts')) {
    function ublk_admin_scripts() {
        $screen = get_current_screen();
        $plugin_data = get_plugin_data( WP_PLUGIN_DIR.'/user-blocker/user_blocker.php', $markup = true, $translate = true );
        $current_version = $plugin_data['Version'];
        $old_version = get_option('ublk_version');
        if($old_version != $current_version)
        {
            update_option('is_user_subscribed_cancled', '');
            update_option('ublk_version',$current_version);
        }
        if(get_option('is_user_subscribed') != 'yes' && get_option('is_user_subscribed_cancled') != 'yes')
        {
            wp_enqueue_script( 'thickbox' );
            wp_enqueue_style( 'thickbox' );
        }
    }
}
add_action( 'admin_enqueue_scripts', 'ublk_admin_scripts' );

/**
 * 
 * @param $actions for take a action for redirection setting
 * @param $plugin_file give path of plugin file
 * @return action for setting link
 */
if(!function_exists('ublk_settings_link')) {
    function ublk_settings_link($actions, $plugin_file) {
        static $plugin;
        if (empty($plugin))
            $plugin = dirname(plugin_basename(__FILE__)) . '/user_blocker.php';
        if ($plugin_file == $plugin) {
            $settings_link = '<a href="' . admin_url('admin.php?page=block_user') . '">'.__('Settings', 'user-blocker').'</a>';
            array_unshift($actions, $settings_link);
        }
        return $actions;
    }
}
add_filter('plugin_action_links', 'ublk_settings_link', 10, 2);

/**
 * start session if not
 */
if(!function_exists('ublk_session_start')) {
    function ublk_session_start() {
        if(session_id() == '') {
            session_start();
        }
    }
}
add_action('init','ublk_session_start');
/**
 * subscribe email form
 */
if(!function_exists('ublk_subscribe_mail')) {
    function ublk_subscribe_mail() {
        $customer_email = get_option('admin_email');
        $current_user = wp_get_current_user();
        $f_name = $current_user -> user_firstname;
        $l_name = $current_user -> user_lastname;
        if(isset($_POST['sbtEmail']))
        {
            $_SESSION['success_msg'] = 'Thank you for your subscription.';
            //Email To Admin
            update_option('is_user_subscribed', 'yes');
            $customer_email = trim($_POST['txtEmail']);
            $customer_name = trim($_POST['txtName']);
            $to = 'plugins@solwininfotech.com';
            $from = get_option('admin_email');
            $headers = "MIME-Version: 1.0;\r\n";
            $headers .= "From: " . strip_tags($from) . "\r\n";
            $headers .= "Content-Type: text/html; charset: utf-8;\r\n";
            $headers .= "X-Priority: 3\r\n";
            $headers .= "X-Mailer: PHP" . phpversion() . "\r\n";
            $subject = 'New user subscribed from Plugin - User Blocker';            
            $body = '';
            ob_start();
            ?>
            <div style="background: #F5F5F5; border-width: 1px; border-style: solid; padding-bottom: 20px; margin: 0px auto; width: 750px; height: auto; border-radius: 3px 3px 3px 3px; border-color: #5C5C5C;">
                <div style="border: #FFF 1px solid; background-color: #ffffff !important; margin: 20px 20px 0;
                     height: auto; -moz-border-radius: 3px; padding-top: 15px;">
                    <div style="padding: 20px 20px 20px 20px; font-family: Arial, Helvetica, sans-serif;
                         height: auto; color: #333333; font-size: 13px;">
                        <div style="width: 100%;">
                            <strong>Dear Admin (User Blocker plugin developer)</strong>,
                            <br />
                            <br />
                            Thank you for developing useful plugin.
                            <br />
                            <br />
                            I <?php echo $customer_name; ?> want to notify you that I have installed plugin on my <a href="<?php echo home_url(); ?>">website</a>. Also I want to subscribe to your newsletter, and I do allow you to enroll me to your free newsletter subscription to get update with new products, news, offers and updates.
                            <br />
                            <br />
                            I hope this will motivate you to develop more good plugins and expecting good support form your side.
                            <br />
                            <br />
                            Following is details for newsletter subscription.
                            <br />
                            <br />
                            <div>
                                <table border='0' cellpadding='5' cellspacing='0' style="font-family: Arial, Helvetica, sans-serif; font-size: 13px;color: #333333;width: 100%;">
                                    <?php if($customer_name !='' )
                                    { ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <th style="padding: 8px 5px; text-align: left;width: 120px;">
                                            Name<span style="float:right">:</span>
                                        </th>
                                        <td style="padding: 8px 5px;">
                                            <?php echo $customer_name; ?>
                                        </td>
                                    </tr>
                                    <?php }
                                    else
                                    { ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <th style="padding: 8px 5px; text-align: left;width: 120px;">
                                            Name<span style="float:right">:</span>
                                        </th>
                                        <td style="padding: 8px 5px;">
                                            <?php echo home_url(); ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <th style="padding: 8px 5px; text-align: left;width: 120px;">
                                            Email<span style="float:right">:</span>
                                        </th>
                                        <td style="padding: 8px 5px;">
                                            <?php echo $customer_email; ?>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <th style="padding: 8px 5px; text-align: left;width: 120px;">
                                            Website<span style="float:right">:</span>
                                        </th>
                                        <td style="padding: 8px 5px;">
                                            <?php echo home_url(); ?>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <th style="padding: 8px 5px; text-align: left; width: 120px;">
                                            Date<span style="float:right">:</span>
                                        </th>
                                        <td style="padding: 8px 5px;">
                                            <?php echo date('d-M-Y  h:i  A'); ?>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <th style="padding: 8px 5px; text-align: left; width: 120px;">
                                            Plugin<span style="float:right">:</span>
                                        </th>
                                        <td style="padding: 8px 5px;">
                                            <?php echo 'User Blocker'; ?>
                                        </td>
                                    </tr>
                                </table>
                                <br /><br />
                                Again Thanks you
                                <br />
                                <br />
                                Regards
                                <br />
                                <?php echo $customer_name; ?>
                                <br />
                                <?php echo home_url(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $body = ob_get_clean();
            wp_mail($to, $subject, $body, $headers);
        }
        if(get_option('is_user_subscribed') != 'yes' && get_option('is_user_subscribed_cancled') != 'yes')
        {
        ?>
        <div id="subscribe_widget_blocker" style="display:none;">
            <div class="subscribe_widget">
            <h3>Notify to plugin developer and subscribe.</h3>
            <form class='sub_form' name="frmSubscribe" method="post" action="<?php echo admin_url().'admin.php?page=block_user'; ?>">
                <div class="sub_row"><label>Your Name: </label><input placeholder="Your Name" name="txtName" type="text" value="<?php echo $f_name.' '.$l_name; ?>" /></div>
                <div class="sub_row"><label>Email Address: </label><input placeholder="Email Address" required name="txtEmail" type="email" value="<?php echo $customer_email; ?>" /></div>
                <input class="button button-primary" type="submit" name="sbtEmail" value="Notify & Subscribe" />                
            </form>
            </div>
        </div>
        <?php
        }
        if(get_option('is_user_subscribed') != 'yes' && get_option('is_user_subscribed_cancled') != 'yes' && ($_GET['page'] == 'block_user' || $_GET['page'] == 'block_user_date' || $_GET['page'] == 'block_user_permenant' || $_GET['page'] == 'blocked_user_list' || $_GET['page'] == 'datewise_blocked_user_list' || $_GET['page'] == 'permanent_blocked_user_list' || $_GET['page'] == 'all_type_blocked_user_list'))
        {
            ?>
            <a style="display:none" href="#TB_inline?width=400&height=210&inlineId=subscribe_widget_blocker" class="thickbox" id="subscribe_thickbox"></a>            
            <?php
        }
    }
}
add_action('admin_head','ublk_subscribe_mail',10);

/**
 * user cancle subscribe
 */
if(!function_exists('wp_ajax_blocker_close_tab')) {
    function wp_ajax_blocker_close_tab() {
        update_option('is_user_subscribed_cancled', 'yes');
        exit();
    }
}
add_action('wp_ajax_close_tab','wp_ajax_blocker_close_tab');