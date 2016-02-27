<?php

/*
  Plugin Name: Postie
  Plugin URI: http://PostiePlugin.com/
  Description: Create posts via email. Signifigantly upgrades the Post by Email features of Word Press.
  Version: 1.7.30
  Author: Wayne Allen
  Author URI: http://PostiePlugin.com/
  License: GPL2
  Text Domain: postie
 */

/*  Copyright (c) 2015  Wayne Allen  (email : wayne@allens-home.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/*
  $Id: postie.php 1352195 2016-02-16 22:37:17Z WayneAllen $
 */
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib_autolink.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "postie-functions.php");

define('POSTIE_VERSION', '1.7.30');
define("POSTIE_ROOT", dirname(__FILE__));
define("POSTIE_URL", WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)));

//register the hooks early in the page in case some method needs the result of one of them (i.e. cron_schedules)
add_action('init', 'postie_disable_kses_content', 20);
add_action('check_postie_hook', 'check_postie');
add_action('parse_request', 'postie_parse_request');
add_action('admin_init', 'postie_admin_init');
add_action('admin_menu', 'postie_admin_menu');
add_action('admin_head', 'postie_admin_head');

add_filter('whitelist_options', 'postie_whitelist');
add_filter('cron_schedules', 'postie_more_reccurences');
add_filter('query_vars', 'postie_query_vars');
add_filter("plugin_action_links_" . plugin_basename(__FILE__), 'postie_plugin_action_links');
add_filter('plugin_row_meta', 'postie_plugin_row_meta', 10, 2);

register_activation_hook(__FILE__, 'activate_postie');
register_activation_hook(__FILE__, 'postie_cron');
register_deactivation_hook(__FILE__, 'postie_decron');

if (isset($_GET["postie_read_me"])) {
    include_once(ABSPATH . "wp-admin/admin.php");
    include(ABSPATH . 'wp-admin/admin-header.php');
    postie_ShowReadMe();
    include(ABSPATH . 'wp-admin/admin-footer.php');
}
//Add Menu Configuration
if (is_admin()) {
    if (function_exists('load_plugin_textdomain')) {

        function postie_load_domain() {
            $plugin_dir = WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__));
            load_plugin_textdomain('postie', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        add_action('init', 'postie_load_domain');
    }
    postie_warnings();
}

//****************** functions *************************
function postie_admin_head() {
    ?>
    <style type="text/css">
        #adminmenu #toplevel_page_postie-settings div.wp-menu-image:before {
            content: "\f466";
        }    
    </style>
    <?php

}

function postie_plugin_row_meta($links, $file) {
    if (strpos($file, plugin_basename(__FILE__)) !== false) {
        $new_links = array(
            '<a href="http://postieplugin.com/" target="_blank">Support</a>',
            '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HPK99BJ88V4C2" target="_blank">Donate</a>'
        );

        $links = array_merge($links, $new_links);
    }

    return $links;
}

function postie_plugin_action_links($links) {
    $links[] = '<a href="admin.php?page=postie-settings">Settings</a>';
    return $links;
}

function postie_query_vars($vars) {
    $vars[] = 'postie';
    return $vars;
}

function postie_parse_request($wp) {
    if (array_key_exists('postie', $wp->query_vars)) {
        switch ($wp->query_vars['postie']) {
            case 'get-mail':
                postie_get_mail();
                die();
            case 'test-config':
                postie_test_config();
                die();
            default :
                dir('Unknown option: ' . $wp->query_vars['postie']);
        }
    }
}

function postie_admin_init() {
    wp_register_style('postie-style', plugins_url('css/style.css', __FILE__));
    register_setting('postie-settings', 'postie-settings', 'config_ValidateSettings');
}

function postie_admin_menu() {
    $page = add_menu_page('Postie', 'Postie', 'manage_options', 'postie-settings', 'postie_loadjs_options_page');
    add_action('admin_print_styles-' . $page, 'postie_admin_styles');
}

function postie_loadjs_options_page() {
    require_once POSTIE_ROOT . '/config_form.php';
}

function postie_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'postie'));
    }
    include 'config_form.php';
}

function postie_admin_styles() {
    wp_enqueue_style('postie-style');
}

/*
 * called by WP when activating the plugin
 * Note that you can't do any output during this funtion or activation
 * will fail on some systems. This means no DebugEcho, EchoInfo or DebugDump.
 */

function activate_postie() {
    static $init = false;
    $options = config_Read();

    if ($init) {
        return;
    }

    if (!$options) {
        $options = array();
    }
    $default_options = config_GetDefaults();
    $old_config = array();

    $result = config_GetOld();
    if (is_array($result)) {
        foreach ($result as $key => $val) {
            $old_config[strtolower($key)] = $val;
        }
    }

    // overlay the options on top of each other:
    // the current value of $options takes priority over the $old_config, which takes priority over the $default_options
    $options = array_merge($default_options, $old_config, $options);
    $options = config_ValidateSettings($options);
    update_option('postie-settings', $options);
    $init = true;
}

/**
 * set up actions to show relevant warnings, 
 * if mail server is not set, or if IMAP extension is not available
 */
function postie_warnings() {

    $config = config_Read();

    if ((empty($config['mail_server']) ||
            empty($config['mail_server_port']) ||
            empty($config['mail_userid']) ||
            empty($config['mail_password'])
            ) && !isset($_POST['submit'])) {

        function postie_enter_info() {
            echo "<div id='postie-info-warning' class='updated fade'><p><strong>" . __('Postie is almost ready.', 'postie') . "</strong> "
            . sprintf(__('You must <a href="%1$s">enter your email settings</a> for it to work.', 'postie'), "admin.php?page=postie-settings")
            . "</p></div> ";
        }

        add_action('admin_notices', 'postie_enter_info');
    }

    $p = strtolower($config['input_protocol']);
    if (!function_exists('imap_mime_header_decode') && ($p == 'imap' || $p == 'imap-ssl' || $p == 'pop-ssl')) {

        function postie_imap_warning() {
            echo "<div id='postie-imap-warning' class='error'><p><strong>";
            echo __('Warning: the IMAP php extension is not installed. Postie can not use IMAP, IMAP-SSL or POP-SSL without this extension.', 'postie');
            echo "</strong></p></div>";
        }

        add_action('admin_notices', 'postie_imap_warning');
    }
    if ($p == 'pop3' && $config['email_tls']) {

        function postie_tls_warning() {
            echo "<div id='postie-lst-warning' class='error'><p><strong>";
            echo __('Warning: The POP3 connector does not support TLS.', 'postie');
            echo "</strong></p></div>";
        }

        add_action('admin_notices', 'postie_tls_warning');
    }

    if (isMarkdownInstalled() && $config['prefer_text_type'] == 'html') {

        function postie_markdown_warning() {
            echo "<div id='postie-lst-warning' class='error'><p><strong>";
            _e("You currently have the Markdown plugin installed. It will cause problems if you send in HTML email. Please turn it off if you intend to send email using HTML.", 'postie');
            echo "</strong></p></div>";
        }

        add_action('admin_notices', 'postie_markdown_warning');
    }

    if (!HasIconvInstalled()) {

        function postie_iconv_warning() {
            echo "<div id='postie-lst-warning' class='error'><p><strong>";
            _e("Warning! Postie requires that iconv be enabled.", 'postie');
            echo "</strong></p></div>";
        }

        add_action('admin_notices', 'postie_iconv_warning');
    }

    $userdata = WP_User::get_data_by('login', $config['admin_username']);
    if (!$userdata) {

        function postie_adminuser_warning() {
            echo "<div id='postie-mbstring-warning' class='error'><p><strong>";
            echo __('Warning: the Admin username is not a valid WordPress login. Postie may reject emails if this is not corrected.', 'postie');
            echo "</strong></p></div>";
        }

        add_action('admin_notices', 'postie_adminuser_warning');
    }
}

function postie_disable_kses_content() {
    remove_filter('content_save_pre', 'wp_filter_post_kses');
}

function postie_whitelist($options) {
    $added = array('postie-settings' => array('postie-settings'));
    $options = add_option_whitelist($added, $options);
    return $options;
}

//don't use DebugEcho or EchoInfo here as it is not defined when called as an action
function check_postie() {
    //error_log("check_postie");
    postie_get_mail();
}

function postie_cron($interval = false) {
    //Do not echo output in filters, it seems to break some installs
    //error_log("postie_cron: setting up cron task: $interval");
    //$schedules = wp_get_schedules();
    //error_log("postie_cron\n" . print_r($schedules, true));

    if (!$interval) {
        $config = config_Read();
        $interval = $config['interval'];
        //error_log("postie_cron: setting up cron task from config: $interval");
    }
    if (!$interval || $interval == '') {
        $interval = 'hourly';
        //error_log("Postie: setting up cron task: defaulting to hourly");
    }
    if ($interval == 'manual') {
        postie_decron();
        //error_log("postie_cron: clearing cron (manual)");
    } else {
        if ($interval != wp_get_schedule('check_postie_hook')) {
            postie_decron(); //remove existing
            //try to create the new schedule with the first run in 5 minutes
            if (false === wp_schedule_event(time() + 5 * 60, $interval, 'check_postie_hook')) {
                //error_log("postie_cron: Failed to set up cron task: $interval");
            } else {
                //error_log("postie_cron: Set up cron task: $interval");
            }
        } else {
            //error_log("postie_cron: OK: $interval");
            //don't need to do anything, cron already scheduled
        }
    }
}

function postie_decron() {
    //error_log("postie_decron: clearing cron");
    wp_clear_scheduled_hook('check_postie_hook');
}

/* here we add some more cron options for how often to check for email */

function postie_more_reccurences($schedules) {
    //Do not echo output in filters, it seems to break some installs
    //error_log("postie_more_reccurences: setting cron schedules");
    $schedules['weekly'] = array('interval' => (60 * 60 * 24 * 7), 'display' => __('Once Weekly', 'postie'));
    $schedules['twiceperhour'] = array('interval' => 60 * 30, 'display' => __('Twice per hour', 'postie'));
    $schedules['tenminutes'] = array('interval' => 60 * 10, 'display' => __('Every 10 minutes', 'postie'));
    $schedules['fiveminutes'] = array('interval' => 60 * 5, 'display' => __('Every 5 minutes', 'postie'));
    $schedules['oneminute'] = array('interval' => 60 * 1, 'display' => __('Every 1 minute', 'postie'));
    $schedules['thirtyseconds'] = array('interval' => 30, 'display' => __('Every 30 seconds', 'postie'));
    $schedules['fifteenseconds'] = array('interval' => 15, 'display' => __('Every 15 seconds', 'postie'));
    return $schedules;
}
