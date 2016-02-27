<?php

if(get_option('ws_login_logo_title')){
function ws_login_logo_title() {
		return get_option('ws_login_logo_title');
}
add_filter('login_headertitle', 'ws_login_logo_title');
}


if(get_option('ws_login_link')){
function ws_login_link() {
		return get_option('ws_login_link');
}
add_filter('login_headerurl', 'ws_login_link');
}


if(get_option('ws_login_desable_shake')==1){
function ws_login_desable_shake() {
remove_action('login_head', 'wp_shake_js', 12);
}
add_action('login_head', 'ws_login_desable_shake');
}

if(get_option('ws_login_change_login_redirect')){
	function ws_login_change_login_redirect( $redirect_to, $request, $user )
	{
		global $user;
		if( isset( $user->roles ) && is_array( $user->roles ) ) {
			if( in_array( "administrator", $user->roles ) ) {
			return $redirect_to;
			} else {
			return get_option('ws_login_change_login_redirect');
			}
		}
		else
		{
			return $redirect_to;
		}
	}
	add_filter("login_redirect", "ws_login_change_login_redirect", 10, 3);
}


if(get_option('ws_login_set_remember_me')==1){
	function ws_login_set_remember_me() {
		add_filter( 'login_footer', 'ws_rememberme_checked' );
	}
	add_action( 'init', 'ws_login_set_remember_me' );
	function ws_rememberme_checked() {
		echo "<script>document.getElementById('rememberme').checked = true;</script>";
	}
}













