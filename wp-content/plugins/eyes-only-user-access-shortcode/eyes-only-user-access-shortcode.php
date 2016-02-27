<?php

/*

   Plugin Name: Eyes Only: User Access Shortcode

   Plugin URI: http://wordpress.org/plugins/eyes-only-user-access-shortcode/

   Description: Show or hide content based on usernames, user roles, capabilities, or logged-in status.

   Version: 1.8.2

   Author: Thom Stark

   Author URI: http://imdb.me/thomstark

   License: GPLv3

*/



define( 'SSEO_FOLDER', dirname( plugin_basename(__FILE__) ) );  

define( 'SSEO_FILE', __FILE__ );



define( 'SSEO_VERSION', '1.8.2' );



  

/*

//$last_version = get_option( 'sseo_version' );

//if ( ! $last_version ) {

	// version was not stored prior to 1.5.0

//	require_once( dirname(__FILE__).'/sseo-admin.php' );

//	ss_eyes_only_merge_defaults();

//	update_option( 'sseo_version', SSEO_VERSION );

//}

*/



if ( is_admin() )

	require_once( dirname(__FILE__).'/sseo-admin.php' );



global $sseo_external_params;

$sseo_external_params = array();



function sseo_register_parameter( $param_key, $label, $args = array() ) {

	global $sseo_external_params;

	$sseo_external_params[$param_key] = (object) array( 'label' => $label );

}



// BP AND BPP SAFE

// For use in combination with the plugins: "bbPress2 shortcode whitelist" and "Custom Profile Filters for BuddyPress"

function sseo_do_shortcode($content) {

	return ( function_exists('bbp_whitelist_do_shortcode') ) ? bbp_whitelist_do_shortcode($content) : do_shortcode($content);

}



add_action( 'plugins_loaded', 'eyes_only_bbpw' );

function eyes_only_bbpw() {

	if ( class_exists('bbPressShortcodeWhitelist') )

		require_once( dirname(__FILE__).'/sseo-bbpwl.php' );

}



// THE SHORTCODE

add_shortcode('eyesonly', 'sc_eyesonly');

add_shortcode('eyesonlier', 'sc_eyesonly');

add_shortcode('eyesonliest', 'sc_eyesonly');



function sc_eyesonly($atts, $content = null) {

	extract(shortcode_atts(array('username' => null, 'level' => null, 'role' => null, 'logged' => null, 'hide' => null,),$atts));

	$sseo_options = get_option('ss_eyes_only_options');



	$matched = false;

	

	$shortcode_content = sseo_do_shortcode($content);

	

	// single-pass do loop to easily skip unnecessary checks

	do {

		if( $logged ) {

			$matched = ( 'in' == $logged ) ? is_user_logged_in() : ! is_user_logged_in();

		}



		if( ! empty($sseo_options['ss_eyes_only_admin_override']) && current_user_can('administrator') ){

			if ( ! $logged || ! $matched )  // bypass won't force Administrators to see anonymous content blocks

				return $shortcode_content;

		}

		

		if ( $matched ) break;

		

		$current_user = wp_get_current_user();

		$users = preg_split("/[\s,]+/",$username);

		$levels = ( $level ? preg_split("/[\s,]+/",$level) : ( $role  ? preg_split("/[\s,]+/",$role) : array()));



		foreach($users as $name){

			if($username && $current_user->user_login === $name){

				$matched = true;

				break 2;

			}

		}



		global $wp_roles;



		foreach($levels as $value){

			if ( is_numeric($value) )

				$value = "level_{$value}";

			

			if ( ! empty( $sseo_options['ss_eyes_only_strict_role_matching'] ) && isset( $wp_roles->role_names[$value] ) ) {

				if ( in_array( $value, $current_user->roles ) ) {

					$matched = true;

					break 2;

				}

			} else {

				if( current_user_can($value) ) {

					$matched = true;

					break 2;

				}

			}

		}

	} while( false ); // end single-pass do loop

	

	if( empty($hide) ) {

		$showcontent = null;  // default to returning no content (matching determines WHETHER TO SHOW shortcode block)

		$thecontent = $shortcode_content;

	} else {

		$showcontent = $shortcode_content;  // default to returning content (matching determine WHETHER TO HIDE shortcode block)

		$thecontent = null;

	}

	

	if ( ! $matched ) { // only apply filter if not already matched

		foreach( array_keys($atts) as $key ) {

			if ( ! in_array( $key, array( 'logged', 'hide' ) ) )

				$atts[$key] = $atts[$key] ? preg_split("/[\s,]+/",$atts[$key]) : array();

		}

		$matched = apply_filters( 'eo_shortcode_matched', $matched, $atts, $shortcode_content );

	}

	

	if ( $matched ) {

		$showcontent = $thecontent;

	}

	

	return $showcontent;

}



// OPTIONS PAGE

register_activation_hook(__FILE__, 'ss_eyes_only_defaults');

register_uninstall_hook(__FILE__, 'ss_eyes_only_delete_plugin_options');



function ss_eyes_only_delete_plugin_options(){

	delete_option('ss_eyes_only_options');

}



function ss_eyes_only_defaults(){

	require_once( dirname(__FILE__).'/sseo-admin.php' );

	ss_eyes_only_add_defaults();

}





// BODY CLASSES

require_once( dirname(__FILE__).'/includes/sseo-body-classes.php' );