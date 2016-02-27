<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*
    Plugin Name: Child Theme Configurator
    Plugin URI: http://www.childthemeconfigurator.com
    Description: Create child themes and customize styles, templates and functions. Enqueues stylesheets and web fonts. Handles rgba, vendor-prefixes and more.
    Version: 1.7.9.1
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2014-2015 Lilaea Media
*/

    defined( 'CHLD_THM_CFG_DIR' ) or 
    define( 'CHLD_THM_CFG_DIR',                 dirname( __FILE__ ) );
    defined( 'CHLD_THM_CFG_URL' ) or 
    define( 'CHLD_THM_CFG_URL',                 plugin_dir_url( __FILE__ ) );
    defined( 'CHLD_THM_CFG_OPTIONS' ) or 
    define( 'CHLD_THM_CFG_OPTIONS', 'chld_thm_cfg_options' );
    
    if ( is_admin() ) 
        include_once( dirname( __FILE__ ) . '/includes/class-ctc.php' );
        
    if ( isset( $_GET['preview_ctc'] ) )
        include_once( dirname( __FILE__ ) . '/includes/class-ctc-preview.php' );
        
    add_filter( 'style_loader_src', 'chld_thm_cfg_plugins_version', 10, 2 );
    
    function chld_thm_cfg_plugins_version( $src, $handle ) {
        if ( strstr( $src, get_stylesheet() ) )
            $src = preg_replace( "/ver=(.*?)(\&|$)/", 'ver=' . wp_get_theme()->Version . "$2", $src );
        return $src;
    }

    register_uninstall_hook( __FILE__, 'chld_thm_cfg_uninstall' );

    function chld_thm_cfg_uninstall() {
        delete_option( CHLD_THM_CFG_OPTIONS );
        delete_option( CHLD_THM_CFG_OPTIONS . '_configvars' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_dict_qs' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_dict_sel' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_dict_query' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_dict_rule' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_dict_val' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_dict_seq' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_sel_ndx' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_val_ndx' );
    }
   
