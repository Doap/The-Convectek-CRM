<?php
/*

**************************************************************************

Plugin Name:  Logout Redirect
Plugin URI:   http://www.arefly.com/logout-redirect/
Description:  Redirect to a link after logout. 登出後跳轉至特定鏈接
Version:      1.0.2
Author:       Arefly
Author URI:   http://www.arefly.com/
Text Domain:  logout-redirect
Domain Path:  /lang/

**************************************************************************

	Copyright 2014  Arefly  (email : eflyjason@gmail.com)

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

**************************************************************************/

define("LOGOUT_REDIRECT_PLUGIN_URL", plugin_dir_url( __FILE__ ));
define("LOGOUT_REDIRECT_FULL_DIR", plugin_dir_path( __FILE__ ));
define("LOGOUT_REDIRECT_TEXT_DOMAIN", "logout-redirect");

/* Plugin Localize */
function logout_redirect_load_plugin_textdomain() {
	load_plugin_textdomain(LOGOUT_REDIRECT_TEXT_DOMAIN, false, dirname(plugin_basename( __FILE__ )).'/lang/');
}
add_action('plugins_loaded', 'logout_redirect_load_plugin_textdomain');

include_once LOGOUT_REDIRECT_FULL_DIR."options.php";

/* Add Links to Plugins Management Page */
function logout_redirect_action_links($links){
	$links[] = '<a href="'.get_admin_url(null, 'options-general.php?page='.LOGOUT_REDIRECT_TEXT_DOMAIN.'-options').'">'.__("Settings", LOGOUT_REDIRECT_TEXT_DOMAIN).'</a>';
	return $links;
}
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'logout_redirect_action_links');

function logout_redirect_cur_url() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

function logout_redirect($logouturl){
	if(get_option("logout_redirect_type") == "customise"){
		$url = get_option("logout_redirect_customise_url");
	}else{
		$url = logout_redirect_cur_url();
	}
	return $logouturl.'&redirect_to='.$url;
}
add_filter('logout_url', 'logout_redirect', 10, 2);
