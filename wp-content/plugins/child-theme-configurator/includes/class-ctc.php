<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

    class ChildThemeConfigurator {
        static $instance;
        static function init() {
            
            // verify WP version support
            global $wp_version;
            if ( version_compare( $wp_version, CHLD_THM_CFG_MIN_WP_VERSION ) < 0 ):
                add_action( 'admin_notices',        'ChildthemeConfigurator::version_notice' );
                return;
            endif; 	
            // setup admin hooks
            if ( is_multisite() )
                add_action( 'network_admin_menu',   'ChildThemeConfigurator::network_admin' );
            add_action( 'admin_menu',               'ChildThemeConfigurator::admin' );
            // setup ajax actions
            add_action( 'wp_ajax_ctc_update',       'ChildThemeConfigurator::save' );
            add_action( 'wp_ajax_ctc_query',        'ChildThemeConfigurator::query' );
            // initialize languages
            add_action( 'init',                     'ChildThemeConfigurator::lang' );
        }
        static function ctc() {
            // create admin object
            global $chld_thm_cfg; /// backward compat
            if ( !isset( self::$instance ) ):
                include_once( CHLD_THM_CFG_DIR . '/includes/class-ctc-admin.php' );
                self::$instance = new ChildThemeConfiguratorAdmin( __FILE__ );
            endif;
            $chld_thm_cfg = self::$instance; // backward compat
            return self::$instance;
        }
        static function lang() {
            // initialize languages
	    	load_plugin_textdomain( 'child-theme-configurator', FALSE, basename( CHLD_THM_CFG_DIR ) . '/lang' );
        }
        static function save() {
            // ajax write
            self::ctc()->ajax_save_postdata();
        }
        static function query() {
            // ajax read
            self::ctc()->ajax_query_css();
        }        
        static function network_admin() {
            $hook = add_theme_page( 
                    __( 'Child Theme Configurator', 'child-theme-configurator' ), 
                    __( 'Child Themes', 'child-theme-configurator' ), 
                    'install_themes', 
                    CHLD_THM_CFG_MENU, 
                    'ChildThemeConfigurator::render' 
            );
            add_action( 'load-' . $hook, 'ChildThemeConfigurator::page_init' );        
        }
        static function admin() {
            $hook = add_management_page(
                    __( 'Child Theme Configurator', 'child-theme-configurator' ), 
                    __( 'Child Themes', 'child-theme-configurator' ), 
                    'install_themes', 
                    CHLD_THM_CFG_MENU, 
                    'ChildThemeConfigurator::render' 
            );
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ChildThemeConfigurator::action_links' );
            add_action( 'load-' . $hook, 'ChildThemeConfigurator::page_init' );        
        }
        static function action_links( $actions ) {
            $actions[] = '<a href="' . admin_url( 'tools.php?page=' . CHLD_THM_CFG_MENU ). '">' 
                . __( 'Child Themes', 'child-theme-configurator' ) . '</a>' . LF;
            return $actions;
        }
        static function page_init() {
            // start admin controller
            self::ctc()->ctc_page_init();
        }
        static function render() {
            // display admin page
            self::ctc()->render();
        }
        static function version_notice() {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            unset( $_GET[ 'activate' ] );
            echo '<div class="update-nag"><p>' . 
                sprintf( __( 'Child Theme Configurator requires WordPress version %s or later.', 'child-theme-configurator' ), 
                CHLD_THM_CFG_MIN_WP_VERSION ) . '</p></div>' . LF;
        }
    }
    defined( 'LF' ) or define( 'LF',            "\n" );
    defined( 'LILAEAMEDIA_URL' ) or 
    define( 'LILAEAMEDIA_URL',                  "http://www.lilaeamedia.com" );
    defined( 'CHLD_THM_CFG_DOCS_URL' ) or 
    define( 'CHLD_THM_CFG_DOCS_URL',            "http://www.childthemeconfigurator.com" );
    define( 'CHLD_THM_CFG_VERSION',             '1.7.9.1' );
    define( 'CHLD_THM_CFG_MIN_WP_VERSION',      '3.7' );
    defined( 'CHLD_THM_CFG_BPSEL' ) or 
    define( 'CHLD_THM_CFG_BPSEL',               '2500' );
    defined( 'CHLD_THM_CFG_MAX_RECURSE_LOOPS' ) or 
    define( 'CHLD_THM_CFG_MAX_RECURSE_LOOPS',   '1000' );
    defined( 'CHLD_THM_CFG_MENU' ) or 
    define( 'CHLD_THM_CFG_MENU',                'chld_thm_cfg_menu' );

    add_action( 'plugins_loaded', 'ChildThemeConfigurator::init' );
