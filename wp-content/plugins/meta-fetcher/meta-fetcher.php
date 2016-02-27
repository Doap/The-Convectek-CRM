<?php
/*
Plugin Name: Meta Fetcher
Plugin Group: Shortcodes
Plugin URI: http://phplug.in/
Description: This plugin provides a simple shortcode that allows you to fetch meta information for the current <code>$post</code>. Example Usage: <code>[meta name="some_name_here" default="some default content"]</code>
Version: 0.4
Author: Eric King
Author URI: http://webdeveric.com/
*/

// This function checks to see if the argument passed indicates an affirmative.  It exists to account for various user inputs.
if ( ! function_exists('is_yes')):
    function is_yes($arg)
    {
        if (is_string($arg))
            $arg = strtolower($arg);
        return in_array($arg, array(true, 'true', 'yes', 'y', '1', 1), true);
    }
endif;

if ( ! function_exists('is_no')):
    function is_no($arg)
    {
        if (is_string($arg))
            $arg = strtolower($arg);
        return in_array($arg, array(false, 'false', 'no', 'n', '0', 0), true);
    }
endif;

function wde_meta_fetcher_shortcode($atts, $content = null, $code = '')
{
    global $post;

    extract(
        shortcode_atts(
            array(
                'name'       => '',
                'default'    => '',
                'join'       => ', ',
                'shortcode'  => true,
                'filters'    => true,
                'single'     => true,
                'json'       => false,                
            ),
            $atts
        )
    );

    if (isset($post, $post->ID) && $name != '') {

        $value = get_post_meta($post->ID, $name, is_yes($single));

        if (empty($value))
            $value = $default;

        if (is_yes($shortcode))
            $value = do_shortcode($value);

        if (is_yes($filters)) {
            $value = apply_filters('meta_fetcher_value', $value, $name);
            $value = apply_filters('meta_fetcher_' . $name, $value);
        }

        if (is_yes($json)) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0)
                $value = json_encode($value, apply_filters('meta_fetcher_json_options', 0));
            else
                $value = json_encode($value);
        }

        if (is_array($value) && ! is_no($join)) {
            $value = implode($join, $value);
        }

        return $value;

    }

    return $default;
}
add_shortcode('meta', 'wde_meta_fetcher_shortcode');
