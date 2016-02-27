<?php
global $bbpscwl_selfdeclared_plugins;
$bbpscwl_selfdeclared_plugins[] = sseo_get_shortcode_whitelist();

function sseo_get_shortcode_whitelist() {
    $plugin_name = 'Eyes Only: User Access Shortcode';
    $plugin_author = 'Thom Stark';
    $shortcodes = array('eyesonly');
    return array('name'=>$plugin_name,'tag'=>'eyes-only-user-access-shortcode','author'=>$plugin_author,'shortcodes'=>$shortcodes);
}
