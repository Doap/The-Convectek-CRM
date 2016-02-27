<?php
add_filter('body_class','sseo_body_classes');
function sseo_body_classes($classes){
	$sseo_options = get_option('ss_eyes_only_options');
	$prefix = (empty($sseo_options['ss_eyes_only_bclass_prefix']) ? 'eyesonly-' : $sseo_options['ss_eyes_only_bclass_prefix']);
	$current_user = wp_get_current_user();
	$sseo_username = str_replace ( ' ', '', strtolower ( $current_user->user_login ) );
	global $wp_roles; $all_roles = $wp_roles->roles; $editable_roles = apply_filters('editable_roles', $all_roles); 
	if (is_user_logged_in()) {
		$classes[] = $prefix.$sseo_username;
		foreach($editable_roles as $role=>$theroles) {
			if (current_user_can($role)) {
				$classes[] = $prefix.$role;
			}
		}
	}
    return $classes;
}