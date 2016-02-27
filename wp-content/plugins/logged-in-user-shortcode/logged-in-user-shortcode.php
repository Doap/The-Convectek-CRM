<?php
/*
Plugin Name: Logged in User Shortcode
Plugin URI: http://wpforchurch.com/plugins/logged-in-user-shortcode
Description: Allows you to easily display different content to users who are logged in and those who are logged out of your site.
Version: 0.1
Author: Jack Lamb
Author URI: http://wpforchurch.com/
License: GPL2
*/

// Security check to see if someone is accessing this file directly
if(preg_match("#^logged-in-user-shortcode.php#", basename($_SERVER['PHP_SELF']))) exit();

// [loggedin]content[/loggedin] returns content only to logged in users
function wpfc_logged_in( $atts, $content = null ) {
	if (is_user_logged_in() )
      {
      	return do_shortcode($content);
      }
}

add_shortcode('loggedin', 'wpfc_logged_in');

// [loggedout]content[/loggedout] returns content only to logged out users
function wpfc_logged_out( $atts, $content = null ) {
	if (is_user_logged_in() )
      {}
    else return do_shortcode($content);
}

add_shortcode('loggedout', 'wpfc_logged_out');
?>
