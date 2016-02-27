<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*
    Class: Child_Theme_Configurator
    Plugin URI: http://www.childthemeconfigurator.com/
    Description: Main Controller Class
    Version: 1.7.9.1
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2014-2015 Lilaea Media
*/
class ChildThemeConfiguratorAdmin {

    // state
    var $is_ajax;
    var $is_get;
    var $is_post;
    var $skip_form;
    var $fs;

    var $fs_prompt;
    var $fs_method;
    var $uploadsubdir;
    var $menuName; // backward compatibility with plugin extension
    var $cache_updates  = TRUE;
    var $debug          = '';
    var $is_debug       = 0;
    var $swatch_text    = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
    var $max_sel;
    var $sel_limit;
    // state arrays
    var $themes         = array();
    var $errors         = array();
    var $files          = array();
    var $updates        = array();
    var $memory         = array();
    // objects
    var $css;
    var $ui;
    // config arrays
    var $postarrays     = array(
            'ctc_img',
            'ctc_file_parnt',
            'ctc_file_child',
            'ctc_additional_css',
        );
    var $configfields   = array(
            'theme_parnt', 
            'child_type', 
            'theme_child', 
            'child_template', 
            'child_name',
            'child_themeuri',
            'child_author',
            'child_authoruri',
            'child_descr',
            'child_tags',
            'child_version',
            'configtype', // backward compatability
            'nowarn',
        );
    var $actionfields   = array(
            'load_styles',
            'parnt_templates_submit',
            'child_templates_submit',
            'image_submit',
            'theme_image_submit',
            'theme_screenshot_submit',
            'export_child_zip',
            'reset_permission',
            'templates_writable_submit',
            'set_writable',
            'upgrade',
        );
    var $imgmimes       = array(
        	'jpg|jpeg|jpe'  => 'image/jpeg',
	        'gif'           => 'image/gif',
	        'png'           => 'image/png',
        );
    var $excludes       = array(
            'inc',
            'core',
            'lang',
            'css',
            'js',
            'lib',
            'theme',
            'options',
        );
    
    function __construct() {
        $this->menuName     = CHLD_THM_CFG_MENU; // backward compatability for plugins extension
        $this->is_post      = ( 'POST' == $_SERVER[ 'REQUEST_METHOD' ] );
        $this->is_get       = ( 'GET' == $_SERVER[ 'REQUEST_METHOD' ] );
        $this->is_debug     = get_option( CHLD_THM_CFG_OPTIONS . '_debug' );
        if ( $this->is_debug && ( $this->debug = get_site_transient( CHLD_THM_CFG_OPTIONS . '_debug' ) ) )
            delete_site_transient( CHLD_THM_CFG_OPTIONS . '_debug' );
        // sel_limit is now calculated based on free memory to prevent out of memory on serialization
        $bytes_free         = $this->get_free_memory();
        $this->sel_limit    = ( int ) ( $bytes_free / CHLD_THM_CFG_BPSEL );
        $this->debug( 'Free memory: ' . $bytes_free . ' max selectors: ' . $this->sel_limit, __FUNCTION__ );
        //$this->set_benchmark( 'before', 'execute', 'program' );
    }

    function enqueue_scripts() {
        wp_enqueue_style( 'chld-thm-cfg-admin', CHLD_THM_CFG_URL . 'css/chld-thm-cfg.min.css', array(), '1.7.9.1' );
        
        // we need to use local jQuery UI Widget/Menu/Selectmenu 1.11.2 because selectmenu is not included in < 1.11.2
        // this will be updated in a later release to use WP Core scripts when it is widely adopted
        if ( !wp_script_is( 'jquery-ui-selectmenu', 'registered' ) ): // selectmenu.min.js
            wp_enqueue_script( 'jquery-ui-selectmenu', CHLD_THM_CFG_URL . 'js/selectmenu.min.js', 
                array( 'jquery','jquery-ui-core','jquery-ui-position' ), FALSE, TRUE );
        endif;
        wp_enqueue_script( 'ctc-spectrum', CHLD_THM_CFG_URL . 'js/spectrum.min.js', array( 'jquery' ), FALSE, TRUE );
        wp_enqueue_script( 'ctc-thm-cfg-ctcgrad', CHLD_THM_CFG_URL . 'js/ctcgrad.min.js', array( 'jquery' ), FALSE, TRUE );
        wp_enqueue_script( 'chld-thm-cfg-admin', CHLD_THM_CFG_URL . 'js/chld-thm-cfg.min.js',
            array(
                'jquery-ui-autocomplete', 
                'jquery-ui-selectmenu',   
                'ctc-spectrum',
                'ctc-thm-cfg-ctcgrad'
            ), FALSE, TRUE );
        $localize_array = apply_filters( 'chld_thm_cfg_localize_script', array(
            'ssl'               => is_ssl(),
            'homeurl'           => get_home_url() . '?preview_ctc=' . wp_create_nonce(),
            'ajaxurl'           => admin_url( 'admin-ajax.php' ),
            'theme_uri'         => get_theme_root_uri(),
            'page'              => CHLD_THM_CFG_MENU,
            'themes'            => $this->themes,
            'source'            => apply_filters( 'chld_thm_cfg_source_uri', get_theme_root_uri() . '/' 
                                    . $this->css->get_prop( 'parnt' ) . '/style.css', $this->css ),
            'target'            => apply_filters( 'chld_thm_cfg_target_uri', get_theme_root_uri() . '/' 
                                    . $this->css->get_prop( 'child' ) . '/style.css', $this->css ),
            'parnt'             => $this->css->get_prop( 'parnt' ),
            'child'             => $this->css->get_prop( 'child' ),
            'addl_css'          => $this->css->get_prop( 'addl_css' ),
            'imports'           => $this->css->get_prop( 'imports' ),
            'is_debug'          => $this->is_debug,
            '_background_url_txt'       => __( 'URL/None',                                                  'child-theme-configurator' ),
            '_background_origin_txt'    => __( 'Origin',                                                    'child-theme-configurator' ),
            '_background_color1_txt'    => __( 'Color 1',                                                   'child-theme-configurator' ),
            '_background_color2_txt'    => __( 'Color 2',                                                   'child-theme-configurator' ),
            '_border_width_txt'         => __( 'Width/None',                                                'child-theme-configurator' ),
            '_border_style_txt'         => __( 'Style',                                                     'child-theme-configurator' ),
            '_border_color_txt'         => __( 'Color',                                                     'child-theme-configurator' ),
            'swatch_txt'        => $this->swatch_text,
            'load_txt'          => __( 'Are you sure? This will replace your current settings.',            'child-theme-configurator' ),
            'important_txt'     => __( '<span style="font-size:10px">!</span>',                             'child-theme-configurator' ),
            'selector_txt'      => __( 'Selectors',                                                         'child-theme-configurator' ),
            'close_txt'         => __( 'Close',                                                             'child-theme-configurator' ),
            'edit_txt'          => __( 'Edit Selector',                                                     'child-theme-configurator' ),
            'cancel_txt'        => __( 'Cancel',                                                            'child-theme-configurator' ),
            'rename_txt'        => __( 'Rename',                                                            'child-theme-configurator' ),
            'css_fail_txt'      => __( 'The stylesheet cannot be displayed.',                               'child-theme-configurator' ),
            'child_only_txt'    => __( '(Child Only)',                                                      'child-theme-configurator' ),
            'inval_theme_txt'   => __( 'Please enter a valid Child Theme.',                                 'child-theme-configurator' ),
            'inval_name_txt'    => __( 'Please enter a valid Child Theme name.',                            'child-theme-configurator' ),
            'theme_exists_txt'  => __( '<strong>%s</strong> exists. Please enter a different Child Theme',  'child-theme-configurator' ),
            'js_txt'            => __( 'The page could not be loaded correctly.',
                                                                                                            'child-theme-configurator' ),
            'jquery_txt'        => __( 'Conflicting or out-of-date jQuery libraries were loaded by another plugin:',
                                                                                                            'child-theme-configurator' ),
            'plugin_txt'        => __( 'Deactivating or replacing plugins may resolve this issue.',         'child-theme-configurator' ),
            'contact_txt'       => sprintf( __( '%sWhy am I seeing this?%s',
                                                                                                            'child-theme-configurator' ),
                '<a target="_blank" href="' . CHLD_THM_CFG_DOCS_URL . '/how-to-use/#script_dep">',
                '</a>' ),
            'nosels_txt'        => __( 'No Styles Available. Try "Parse Additional Stylesheets."',                                            'child-theme-configurator' ),
        ) );
        wp_localize_script(
            'chld-thm-cfg-admin', 
            'ctcAjax', 
            apply_filters( 'chld_thm_cfg_localize_array', $localize_array )
        );
    }
    
    /**
     * initialize configurator
     */
    function ctc_page_init () {
        // get all available themes
        $this->get_themes();
        // load config data and validate
        $this->load_config();
        // perform any checks prior to processing config data
        do_action( 'chld_thm_cfg_preprocess' );
        // process any additional forms
        do_action( 'chld_thm_cfg_forms', $this );  // hook for custom forms
        // process main post data
        $this->process_post();
        // initialize UI
        include_once( CHLD_THM_CFG_DIR . '/includes/class-ctc-ui.php' );
        $this->ui = new ChildThemeConfiguratorUI();
        // initialize help
        $this->ui->render_help_content();
        // load styles and scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99 );
        // load web fonts for this theme
        $this->load_imports();
	}
    
    function render() {
        $this->ui->render();
    }

    function get_themes() {
        // create cache of theme info
        $this->themes = array( 'child' => array(), 'parnt' => array() );
        foreach ( wp_get_themes() as $theme ):
            // organize into parent and child themes
            $group      = $theme->parent() ? 'child' : 'parnt';
            // get the theme slug
            $slug       = $theme->get_stylesheet();
            // get the theme slug
            $version    = $theme->get( 'Version' );
            // strip auto-generated timestamp from CTC child theme version
            if ( 'child' == $group ) $version = preg_replace("/\.\d{6}\d+$/", '', $version );
            // add theme to themes array
            $this->themes[ $group ][ $slug ] = array(
                'Template'      => $theme->get( 'Template' ),
                'Name'          => $theme->get( 'Name' ),
                'ThemeURI'      => $theme->get( 'ThemeURI' ),
                'Author'        => $theme->get( 'Author' ),
                'AuthorURI'     => $theme->get( 'AuthorURI' ),
                'Descr'         => $theme->get( 'Description' ),
                'Tags'          => $theme->get( 'Tags' ),
                'Version'       => $version,
                'screenshot'    => $theme->get_screenshot(),
                'allowed'       => $theme->is_allowed(),
            );
        endforeach;
    }

    function validate_post( $action = 'ctc_update', $noncefield = '_wpnonce', $cap = 'install_themes' ) {
        // security: request must be post, user must have permission, referrer must be local and nonce must match
        return ( $this->is_post 
            && current_user_can( $cap ) // ( 'edit_themes' )
            && ( $this->is_ajax ? check_ajax_referer( $action, $noncefield, FALSE ) : 
                check_admin_referer( $action, $noncefield, FALSE ) ) );
    }
    
    function load_config() {
        include_once( CHLD_THM_CFG_DIR . '/includes/class-ctc-css.php' );
        $this->css = new ChildThemeConfiguratorCSS();
        if ( FALSE !== $this->css->load_config() ):
            // if themes do not exist reinitialize
            if ( ! $this->check_theme_exists( $this->css->get_prop( 'child' ) )
                || ! $this->check_theme_exists( $this->css->get_prop( 'parnt' ) ) ):
                add_action( 'admin_notices', array( $this, 'config_notice' ) ); 	
                $this->css = new ChildThemeConfiguratorCSS();
                $this->css->enqueue = 'enqueue';
            endif;
        else:
            // this is a fresh install
            $this->css->enqueue = 'enqueue';
        endif;
        do_action( 'chld_thm_cfg_load' );
        if ( $this->is_get ):
            if ( $this->css->get_prop( 'child' ) ):
                // get filesystem credentials if available
                $this->verify_creds();
                $stylesheet = apply_filters( 'chld_thm_cfg_target', $this->css->get_child_target( 'style.css' ), $this->css );
                // check file permissions
                if ( !is_writable( $stylesheet ) && !$this->fs ):
	                add_action( 'admin_notices', array( $this, 'writable_notice' ) );
                endif;
                // enqueue flag will be null for existing install < 1.6.0
                if ( !isset( $this->css->enqueue ) ):
                    add_action( 'admin_notices', array( $this, 'enqueue_notice' ) ); 	
                endif;
            endif;
                // check if max selectors reached
            if ( isset( $this->css->max_sel ) && $this->css->max_sel ):
                $this->debug( 'Max selectors exceeded.', __FUNCTION__ );
                //$this->errors[] = __( 'Maximum number of styles exceeded.', 'child-theme-configurator' );
                add_filter( 'chld_thm_cfg_update_msg', array( $this, 'max_styles_notice' ), 20 );
            endif;
            // check if file ownership is messed up from old version or other plugin
            // by comparing owner of plugin to owner of child theme:
            if ( fileowner( $this->css->get_child_target( '' ) ) != fileowner( CHLD_THM_CFG_DIR ) ):
	            add_action( 'admin_notices', array( $this, 'owner_notice' ) ); 
            endif;
        endif;	
    }
    
    function cache_debug() {
        $this->updates[] = array(
            'obj'   => 'debug',
            'key'   => '',
            'data'  => $this->print_debug( TRUE ),
        );
    }
    /**
     * ajax callback for saving form data 
     */
    function ajax_save_postdata( $action = 'ctc_update' ) {
        $this->is_ajax = TRUE;
        
        // security check
        if ( $this->validate_post( $action ) ):
            if ( 'ctc_plugin' == $action ) do_action( 'chld_thm_cfg_pluginmode' );
            $this->verify_creds(); // initialize filesystem access
            // get configuration data from options API
            if ( FALSE !== $this->load_config() ): // sanity check: only update if config data exists
                if ( isset( $_POST[ 'ctc_is_debug' ] ) ):
                    // toggle debug
                    $this->toggle_debug();
                else:
                    $this->css->parse_post_data(); // parse any passed values
                    // if child theme config has been set up, save new data
                    // return recent edits and selected stylesheets as cache updates
                    if ( $this->css->get_prop( 'child' ) ):
                        // hook for add'l plugin files and subdirectories
                        do_action( 'chld_thm_cfg_addl_files', $this );
                        $this->css->write_css();
                        // add any additional updates to pass back to browser
                        do_action( 'chld_thm_cfg_cache_updates' );
                        /*
                        $this->updates[] = array(
                            'obj'   => 'addl_css',
                            'key'   => '',
                            'data'  => $this->css->get_prop( 'addl_css' ),
                        );
                        */
                    endif;
                    
                    // update config data in options API
                    $this->css->save_config();
                endif;
            endif;
        endif;
        $result = $this->css->obj_to_utf8( $this->updates );
        // send all updates back to browser to update cache
        die( json_encode( $result ) );
    }
    
    /**
     * ajax callback to query config data 
     */
    function ajax_query_css( $action = 'ctc_update' ) {
        $this->is_ajax = TRUE;
        if ( $this->validate_post( $action ) ):
            if ( 'ctc_plugin' == $action ) do_action( 'chld_thm_cfg_pluginmode' );
            $this->load_config();
            $regex = "/^ctc_query_/";
            foreach( preg_grep( $regex, array_keys( $_POST ) ) as $key ):
                $name = preg_replace( $regex, '', $key );
                $param[ $name ] = sanitize_text_field( $_POST[ $key ] );
            endforeach;
            if ( !empty( $param[ 'obj' ] ) ):
                // add any additional updates to pass back to browser
                $this->updates[] = array(
                    'key'   => isset( $param[ 'key' ] ) ? $param[ 'key' ] : '',
                    'obj'   => $param[ 'obj' ],
                    'data'  => $this->css->get_prop( $param[ 'obj' ], $param ),
                );
                do_action( 'chld_thm_cfg_cache_updates' );
                die( json_encode( $this->updates ) );
            endif;
        endif;
        die( 0 );
    }
    
    /***
     * Handles processing for all form submissions.
     * Older versions ( < 1.6.0 ) smelled like spaghetti so we moved conditions 
     * to switch statement with the main setup logic in a separate function.
     */
    function process_post() {
        // make sure this is a post
        if ( $this->is_post ):
            // see if a valid action was passed
            foreach ( $this->actionfields as $field ):
                if ( in_array( 'ctc_' . $field, array_keys( $_POST ) ) ):
                    $actionfield = $field;
                    break;
                endif;
            endforeach;
            if ( empty( $actionfield ) ) return FALSE;
            
            // make sure post passes security checkpoint        
            $this->errors = array();
            if ( $this->validate_post( apply_filters( 'chld_thm_cfg_action', 'ctc_update' ) ) ):
                // zip export does not require filesystem access so check that first
                if ( 'export_child_zip' == $actionfield ):
                    $this->export_zip();
                    // if we get here the zip failed
                    $this->errors[] = __( 'Zip file creation failed.', 'child-theme-configurator' );
                // all other actions require filesystem access
                else:
                    // handle uploaded file before checking filesystem
                    if ( 'theme_image_submit' == $actionfield && isset( $_FILES[ 'ctc_theme_image' ] ) ):
                        $this->handle_file_upload( 'ctc_theme_image', $this->imgmimes );          
                    elseif ( 'theme_screenshot_submit' == $actionfield && isset( $_FILES[ 'ctc_theme_screenshot' ] ) ):
                        $this->handle_file_upload( 'ctc_theme_screenshot', $this->imgmimes );
                    endif;
                    // now we need to check filesystem access 
                    $args = preg_grep( "/nonce/", array_keys( $_POST ), PREG_GREP_INVERT );
                    $this->verify_creds( $args );
                    if ( $this->fs ):
                        $msg = FALSE;
                        // we have filesystem access so proceed with specific actions
                        switch( $actionfield ):
                            case 'load_styles':
                                // main child theme setup function
                                $msg = $this->setup_child_theme();
                                break;
                            
                            case 'parnt_templates_submit':
                                // copy parent templates to child
                                if ( isset( $_POST[ 'ctc_file_parnt' ] ) ):
                                    foreach ( $_POST[ 'ctc_file_parnt' ] as $file ):
                                        $this->copy_parent_file( sanitize_text_field( $file ) );
                                    endforeach;
                                    $msg = '8&tab=file_options';
                                endif;
                                break;
                                
                            case 'child_templates_submit':
                                // delete child theme files
                                if ( isset( $_POST[ 'ctc_file_child' ] ) ):
                                    if ( in_array( 'functions', $_POST[ 'ctc_file_child' ] ) ):
                                        $this->errors[] = 
                                            __( 'The Functions file is required and cannot be deleted.', 
                                                'child-theme-configurator' );
                                    else:
                                        foreach ( $_POST[ 'ctc_file_child' ] as $file ):
                                            $this->delete_child_file( sanitize_text_field( $file ), 
                                                ( preg_match( "/^style|ctc\-plugins/", $file ) ? 'css' : 'php' ) );
                                        endforeach;
                                        $msg = '8&tab=file_options';
                                    endif;
                                endif;
                                break;
                                
                            case 'image_submit':
                                // delete child theme images
                                if ( isset( $_POST[ 'ctc_img' ] ) ):
                                    foreach ( $_POST[ 'ctc_img' ] as $file ):
                                        $this->delete_child_file( 'images/' . sanitize_text_field( $file ), 'img' );
                                    endforeach;
                                    $msg = '8&tab=file_options';
                                endif;
                                break;
                                
                            case 'templates_writable_submit':
                                // make specific files writable ( systems not running suExec )
                                if ( isset( $_POST[ 'ctc_file_child' ] ) ):
                                    foreach ( $_POST[ 'ctc_file_child' ] as $file ):
                                        $this->set_writable( sanitize_text_field( $file ), 
                                            ( 0 === strpos( $file, 'style' ) ? 'css' : 'php' ) );
                                    endforeach;
                                    $msg = '8&tab=file_options';
                                endif;
                                break;
                                
                            case 'set_writable':
                                // make child theme style.css and functions.php writable ( systems not running suExec )
                                $this->set_writable(); // no argument defaults to style.css
                                $this->set_writable( 'functions' );
                                $msg = '8&tab=file_options';
                                break;
                            
                            case 'reset_permission':
                                // make child theme read-only ( systems not running suExec )
                                $this->unset_writable();
                                $msg = '8&tab=file_options';
                                break;
                            
                            case 'theme_image_submit':
                                // move uploaded child theme images (now we have filesystem access)
                                if ( isset( $_POST[ 'movefile' ] ) ):
                                    $this->move_file_upload( 'images' );
                                    $msg = '8&tab=file_options';
                                endif;
                                break;
                            
                            case 'theme_screenshot_submit':
                                // move uploaded child theme screenshot (now we have filesystem access)
                                if ( isset( $_POST[ 'movefile' ] ) ):
                                    // remove old screenshot
                                    foreach( array_keys( $this->imgmimes ) as $extreg ): 
                                        foreach ( explode( '|', $extreg ) as $ext ):
                                            $this->delete_child_file( 'screenshot', $ext );
                                        endforeach; 
                                    endforeach;
                                    $this->move_file_upload( '' );
                                    $msg = '8&tab=file_options';
                                endif;
                                break;
                            default:
                                // assume we are on the files tab so just redirect there
                                $msg = '8&tab=file_options';
                        endswitch;
                    endif; // end filesystem condition
                endif; // end zip export condition
                if ( empty( $this->errors ) && empty( $this->fs_prompt ) )
                    // no errors so we redirect with confirmation message
                    $this->update_redirect( $msg );
                // otherwise fail gracefully
                $msg = NULL;
                return FALSE;
            endif; // end post validation condition
            // if you end up here you are not welcome
            $msg = NULL;
            $this->errors[] = __( 'You do not have permission to configure child themes.', 'child-theme-configurator' );
        endif; // end request method condition
        return FALSE;
    }
    
    function toggle_debug() {
        $debug = '';
        if ( $_POST[ 'ctc_is_debug' ] ):
            $this->is_debug = 1;
            $debug = $this->print_debug( TRUE );
        else:
            $this->is_debug = 0;
        endif;
        update_option( CHLD_THM_CFG_OPTIONS . '_debug', $this->is_debug );
        $this->updates[] = array(
            'obj'   => 'debug',
            'key'   => '',
            'data'  => $debug,
        );
    }
    
    /***
     * Handle the creation or update of a child theme
     */
    function setup_child_theme() {
        // sanitize and extract config fields into local vars
        foreach ( $this->configfields as $configfield ):
            
            $varparts = explode( '_', $configfield );
            $varname = end( $varparts );
            ${$varname} = empty( $_POST[ 'ctc_' . $configfield ] ) ? '' : 
                preg_replace( "/\s+/s", ' ', sanitize_text_field( $_POST[ 'ctc_' . $configfield ] ) );
            $this->debug( 'Extracting var ' . $varname . ' from ctc_' . $configfield . ' value: ' . ${$varname} , __FUNCTION__ );
        endforeach;
        
        // legacy plugin extension needs parent/child values but this version disables the inputs
        // so get we them from current css object
        if ( !$this->is_theme( $configtype ) && $this->is_legacy() ):
            $parnt  = $this->css->get_prop( 'parnt' );
            $child  = $this->css->get_prop( 'child' );
            $name   = $this->css->get_prop( 'child_name' );
        endif;        
        
        // validate parent and child theme inputs
        if ( $parnt ):
            if ( ! $this->check_theme_exists( $parnt ) ):
                $this->errors[] = sprintf( 
                    __( '%s does not exist. Please select a valid Parent Theme.', 
                        'child-theme-configurator' ), $parnt );
            endif;
        else:
            $this->errors[] = __( 'Please select a valid Parent Theme.', 'child-theme-configurator' );
        endif;
        if ( 'existing' == $type && empty( $child ) ):
            $this->errors[] = __( 'Please enter a valid Child Theme directory.', 'child-theme-configurator' );
        endif;
        if ( empty( $name ) ):
            $name = ucfirst( $child );
        endif;
        // if this is a shiny brand new child theme certain rules apply
        if ( 'new' == $type ):
            if ( empty( $template ) && empty( $name ) ):
                $this->errors[] = __( 'Please enter a valid Child Theme template name.', 'child-theme-configurator' );
            else:
                $child = preg_replace( "%[^\w\-]%", '', empty( $template ) ? $name : $template );
                if ( $this->check_theme_exists( $child ) ):
                    $this->errors[] = sprintf( 
                        __( '<strong>%s</strong> exists. Please enter a different Child Theme template name.', 
                            'child-theme-configurator' ), $child );
                endif;
            endif;
        endif;
        
        // clone existing child theme
        if ( 'existing' == $type && isset( $_POST[ 'ctc_duplicate_theme' ] ) ):
            $clone = strtolower( preg_replace( "%[^\w\-]%", '', sanitize_text_field( $_POST[ 'ctc_duplicate_theme_slug' ] ) ) ); 
            if ( empty( $clone ) ):
                $this->errors[] = __( 'Please enter a valid Child Theme template name.', 'child-theme-configurator' );
            else:
                if ( $this->check_theme_exists( $clone ) ):
                    $this->errors[] = sprintf( 
                        __( '<strong>%s</strong> exists. Please enter a different Child Theme template name.', 
                            'child-theme-configurator' ), $clone );
                else:
                    $this->clone_child_theme( $child, $clone );
                    if ( empty( $this->errors ) ):
                        $this->copy_theme_mods( $child, $clone );
                        $child = $clone;
                    endif;
                endif;
            endif;
        endif;
        
        if ( FALSE === $this->verify_child_dir( $child ) ):
            $this->errors[] = __( 'Your theme directories are not writable.', 'child-theme-configurator' );
            add_action( 'admin_notices', array( $this, 'writable_notice' ) ); 	
        endif;
        
        // if no errors so far, we are good to create child theme
        if ( empty( $this->errors ) ):
            // save imports in case this is a rebuild
            $imports            = $this->css->imports;
            $nowarn             = ( $nowarn || $this->css->nowarn ) ? 1 : 0;
            // reset everything else
            $this->css          = new ChildThemeConfiguratorCSS();
            // restore imports if this is a rebuild
            $this->css->imports = $imports;
            $this->css->nowarn  = $nowarn;
            // parse parent stylesheet if theme or legacy plugin extension 
            if ( $this->is_theme( $configtype ) || $this->is_legacy() ):
                add_action( 'chld_thm_cfg_parse_stylesheets', array( &$this, 'parse_parent_stylesheet' ) );
                if ( is_multisite() )
                    add_action( 'chld_thm_cfg_addl_options', array( &$this, 'network_enable' ) );
            endif;
            
            add_action( 'chld_thm_cfg_parse_stylesheets', array( &$this, 'parse_additional_stylesheets' ) );
            add_action( 'chld_thm_cfg_parse_stylesheets', array( &$this, 'parse_child_stylesheet' ) );

            // function to support wp_filesystem requirements
            if ( $this->is_theme( $configtype ) ):
                // is theme means this is not a plugin stylesheet config
                add_action( 'chld_thm_cfg_addl_files', array( &$this, 'add_base_files' ), 10, 2 );
                add_action( 'chld_thm_cfg_addl_files', array( &$this, 'copy_screenshot' ), 10, 2 );
                add_action( 'chld_thm_cfg_addl_files', array( &$this, 'enqueue_parent_css' ), 15, 2 );
            elseif( $this->is_legacy() && has_action( 'chld_thm_cfg_addl_files' ) ):
                // backwards compatability for plugins extension < 2.0.0 (before pro)
                // action exists so we have to hijack it to use new filesystem checks
                remove_all_actions( 'chld_thm_cfg_addl_files' );
                add_action( 'chld_thm_cfg_addl_files', array( &$this, 'write_addl_files' ), 10, 2 );
                $this->css->set_prop( 'configtype', $configtype );
            endif;
    
            // update with new parameters
            $this->css->set_prop( 'parnt', $parnt );
            $this->css->set_prop( 'child', $child );
            $this->css->set_prop( 'child_name', $name );
            $this->css->set_prop( 'child_author', $author );
            $this->css->set_prop( 'child_themeuri', $themeuri );
            $this->css->set_prop( 'child_authoruri', $authoruri );
            $this->css->set_prop( 'child_descr', $descr );
            $this->css->set_prop( 'child_tags', $tags );
            $this->css->set_prop( 'child_version', strlen( $version ) ? $version : '1.0' );
            
            $this->css->set_prop( 'nowarn', $nowarn );
            // set stylesheet handling option
            if ( isset( $_POST[ 'ctc_parent_enqueue' ] ) )
                $this->css->set_prop( 'enqueue', sanitize_text_field( $_POST[ 'ctc_parent_enqueue' ] ) );
            elseif ( !$this->is_theme( $configtype ) )
                $this->css->set_prop( 'enqueue', 'enqueue' );

            // plugin hooks for additional stylesheet handling options
            do_action( 'chld_thm_cfg_stylesheet_handling' );
            do_action( 'chld_thm_cfg_existing_theme' );
            // plugin hook to parse additional or non-standard files
            do_action( 'chld_thm_cfg_parse_stylesheets' );

            // copy menus, widgets and other customizer options from parent to child if selected
            if ( isset( $_POST[ 'ctc_parent_mods' ] ) ) // && empty( $_POST[ 'ctc_duplicate_theme' ] ) )
                $this->copy_theme_mods( $parnt, $child );

            // run code generation function in read-only mode to add existing external stylesheet links to config data
            $this->enqueue_parent_css( $this->css, TRUE );
            // hook for add'l plugin files and subdirectories. Must run after stylesheets are parsed to apply latest options
            do_action( 'chld_thm_cfg_addl_files', $this );
            // do not continue if errors 
            if ( empty ( $this->errors ) ):
                // set flag to skip import link conversion on ajax save
                $this->css->converted = 1;
    
                // try to write new stylsheet. If it fails send alert.
                if ( FALSE === $this->css->write_css( isset( $_POST[ 'ctc_backup' ] ) ) ):
                    $this->debug( 'failed to write', __FUNCTION__ );
                    $this->errors[] = __( 'Your stylesheet is not writable.', 'child-theme-configurator' );
                    add_action( 'admin_notices', array( $this, 'writable_notice' ) ); 	
                    return FALSE;
                endif; 
                
                // save new object to WP options table
                $this->css->save_config();
                
                // plugin hook for additional child theme setup functions
                do_action( 'chld_thm_cfg_addl_options', $this );
                
                // return message id 1, which says new child theme created successfully;
                return 1;
            endif;
        endif;
        return FALSE;
    }

    function load_imports() {
        // allows fonts and other externals to be previewed
        // loads early not to conflict with admin stylesheets
        if ( $imports = $this->css->get_prop( 'imports' ) ):
            $ext = 0;
            foreach ( $imports as $import ):
                $this->convert_import_to_enqueue( $import, ++$ext, TRUE );
            endforeach;
        endif;
    }

    /*
     * TODO: this is a stub for future use
     */
    function sanitize_options( $input ) {
        return $input;
    }
    
    /**
     * remove slashes and non-alphas from stylesheet name
     */
    function sanitize_slug( $slug ) {
        return preg_replace( "/[^\w\-]/", '', $slug );
    }
    
    function update_redirect( $msg = 1 ) {
        if ( empty( $this->is_ajax ) ):
            if ( $this->is_debug )
                set_site_transient( CHLD_THM_CFG_OPTIONS . '_debug', $this->debug, 3600 );
            $ctcpage = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
            $screen = get_current_screen()->id;
            wp_safe_redirect(
                ( strstr( $screen, '-network' ) ? network_admin_url( 'themes.php' ) : admin_url( 'tools.php' ) ) 
                    . '?page=' . $ctcpage . ( $msg ? '&updated=' . $msg : '' ) );
            die();
        endif;
    }
    
    function verify_child_dir( $path ) {
        $this->debug( 'Verifying child dir: ' . $path, __FUNCTION__ );
        if ( !$this->fs ): 
            $this->debug( 'No filesystem access.', __FUNCTION__ );
            return FALSE; // return if no filesystem access
        endif;
        global $wp_filesystem;
        $themedir = $wp_filesystem->find_folder( get_theme_root() );
        if ( ! $wp_filesystem->is_writable( $themedir ) ):
            $this->debug( 'Directory not writable: ' . $themedir, __FUNCTION__ );
            return FALSE;
        endif;
        $childparts = explode( '/', $this->normalize_path( $path ) );
        while ( count( $childparts ) ):
            $subdir = array_shift( $childparts );
            if ( empty( $subdir ) ) continue;
            $themedir = trailingslashit( $themedir ) . $subdir;
            if ( ! $wp_filesystem->is_dir( $themedir ) ):
                if ( ! $wp_filesystem->mkdir( $themedir, FS_CHMOD_DIR ) ):
                $this->debug( 'Could not make directory: ' . $themedir, __FUNCTION__ );
                    return FALSE;
                endif;
            elseif ( ! $wp_filesystem->is_writable( $themedir ) ):
                $this->debug( 'Directory not writable: ' . $themedir, __FUNCTION__ );
                return FALSE;
            endif;
        endwhile;
        $this->debug( 'Child dir verified: ' . $themedir, __FUNCTION__ );
        return TRUE;
    }
    
    function add_base_files( $obj ){
        // add functions.php file
        $contents = "<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
";
        $this->write_child_file( 'functions.php', $contents );
        $this->write_child_file( 'style.css', $this->css->get_css_header() );
    }
    
    // parses @import syntax and converts to wp_enqueue_style statement
    function convert_import_to_enqueue( $import, $count, $execute = FALSE ) {
        $relpath    = $this->css->get_prop( 'child' );
        $import     = preg_replace( "#^.*?url\(([^\)]+?)\).*#", "$1", $import );
        $import     = preg_replace( "#[\'\"]#", '', $import );
        $path       = $this->css->convert_rel_url( trim( $import ), $relpath , FALSE );
        $abs        = preg_match( '%(https?:)?//%', $path );
        if ( $execute )
            wp_enqueue_style( 'chld_thm_cfg_ext' . $count,  $abs ? $path : trailingslashit( get_theme_root_uri() ) . $path );
        else
            return "wp_enqueue_style( 'chld_thm_cfg_ext" . $count . "', " 
                . ( $abs ? "'" . $path . "'" : "trailingslashit( get_theme_root_uri() ) . '" . $path . "'" ) . ' );';
    }
    
    // converts enqueued path into @import statement for config settings
    function convert_enqueue_to_import( $path ) {
        if ( preg_match( '%(https?:)?//%', $path ) ):
            $this->css->imports[ 'child' ]['@import url(' . $path . ')'] = 1;
            return;
        endif;
        $regex  = '#^' . preg_quote( trailingslashit( $this->css->get_prop( 'child' ) ) ) . '#';
        $path   = preg_replace( $regex, '', $path, -1, $count );
        if ( $count ): 
            $this->css->imports[ 'child' ]['@import url(' . $path . ')'] = 1;
            return;
        endif;
        $parent = trailingslashit( $this->css->get_prop( 'parnt' ) );
        $regex  = '#^' . preg_quote( $parent ) . '#';
        $path   = preg_replace( $regex, '../' . $parent, $path, -1, $count );
        if ( $count )
            $this->css->imports[ 'child' ]['@import url(' . $path . ')'] = 1;
    }
    
    /**
     * Generates wp_enqueue_script code block for child theme functions file
     * Enqueues parent and/or child stylesheet depending on value of 'enqueue' setting.
     * If external imports are present, it enqueues them as well.
     */
    function enqueue_parent_code(){
        $imports    = $this->css->get_prop( 'imports' );
        $enqueues   = array();
        $code       = '';
        // enqueue parent stylesheet 
        if ( 'enqueue' == $this->css->enqueue ||  'both' == $this->css->enqueue )
            $enqueues[] = "        wp_enqueue_style( 'chld_thm_cfg_parent', trailingslashit( get_template_directory_uri() ) . 'style.css' );"; 
        // enqueue external stylesheets (previously used @import in the stylesheet)
        if ( !empty( $imports ) ):
            $ext = 0;
            foreach ( $imports as $import ):
                $ext++;
                $enqueues[] = '        ' . $this->convert_import_to_enqueue( $import, $ext ); 
            endforeach;
        endif;
        if ( count( $enqueues ) ):
            $code = "// AUTO GENERATED - Do not modify or remove comment markers above or below:
";
            $code .= "
        
if ( !function_exists( 'chld_thm_cfg_parent_css' ) ):
    function chld_thm_cfg_parent_css() {
";
            $code .= implode( "\n", $enqueues );
            $code .= "
    }
endif;
add_action( 'wp_enqueue_scripts', 'chld_thm_cfg_parent_css' );
";
        endif;
        // enqueue child stylesheet. This feature was added to avoid using @import to load parent stylesheet when links are hard-coded into header.php
        if ( 'child' == $this->css->enqueue || 'both' == $this->css->enqueue ): 
            $code .= "
if ( !function_exists( 'chld_thm_cfg_child_css' ) ):
    function chld_thm_cfg_child_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', get_stylesheet_uri() ); 
    }
endif;
add_action( 'wp_enqueue_scripts', 'chld_thm_cfg_child_css', 999 );
";
        endif;
        return explode( "\n", apply_filters( 'chld_thm_cfg_enqueue_code_filter', $code ) );
    }
    
    // updates function file with wp_enqueue_script code block. If getexternals flag is passed function is run in read-only mode
    function enqueue_parent_css( $obj, $getexternals = FALSE ) {
        $marker     = 'ENQUEUE PARENT ACTION';
        $insertion  =  $this->enqueue_parent_code();
        if ( $filename   = $this->css->is_file_ok( $this->css->get_child_target( 'functions.php' ), 'write' ) )
            $this->insert_with_markers( $filename, $marker, $insertion, $getexternals );
    }
    
    /**
     * Update functions file with wp_enqueue_style code block. Runs in read-only mode if getexternals is passed.
     * This function uses the same method as the WP core function that updates .htaccess 
     * we would have used WP's insert_with_markers function, 
     * but it does not use wp_filesystem API.
     */
    function insert_with_markers( $filename, $marker, $insertion, $getexternals = FALSE ) {
        if ( count( $this->errors ) ):
            $this->debug( 'Errors detected, returning', __FUNCTION__ );
            return FALSE;
        endif;
        // first check if this is an ajax update
        if ( $this->is_ajax && is_readable( $filename ) && is_writable( $filename ) ):
            // ok to proceed
            $this->debug( 'Ajax update, bypassing wp filesystem.', __FUNCTION__ );
            $markerdata = explode( "\n", @file_get_contents( $filename ) );
        elseif ( !$this->fs ): 
            $this->debug( 'No filesystem access.', __FUNCTION__ );
            return FALSE; // return if no filesystem access
        else:
            global $wp_filesystem;
            if( !$wp_filesystem->exists( $this->fspath( $filename ) ) ):
                // make sure file exists with php header
                $this->debug( 'No functions file, creating...', __FUNCTION__ );
                $this->add_base_files( $this );
		    endif;
            // get_contents_array returns extra linefeeds so just split it ourself
            $markerdata = explode( "\n", $wp_filesystem->get_contents( $this->fspath( $filename ) ) );
        endif;
        $newfile = '';
        $externals  = array();
        $phpopen    = 0;
        $in_comment = 0;
		$foundit = FALSE;
        $lasttoken  = '';
		if ( $markerdata ):
			$state = TRUE;
			foreach ( $markerdata as $n => $markerline ) {
                // update open state
                $openstars = 0;
                $closestars = 0;
                // remove double slash comment to end of line
                $str = preg_replace( "/\/\/.*$/", '', $markerline );
                preg_match_all("/(<\?|\?>|\*\/|\/\*)/", $str, $matches );
                if ( $matches ):
                    foreach ( $matches[1] as $token ): 
                        $lasttoken = $token;
                        if ( '/*' == $token ):
                            $in_comment = 1;
                        elseif ( '*/' == $token ):
                            $in_comment = 0;
                        elseif ( '<?' == $token && !$in_comment ):
                            $phpopen = 1;
                        elseif ( '?>' == $token && !$in_comment ):
                            $phpopen = 0;
                        endif;
                    endforeach;
                endif;
				if ( strpos( $markerline, '// BEGIN ' . $marker ) !== FALSE )
					$state = FALSE;
				if ( $state ):
					if ( $n + 1 < count( $markerdata ) )
						$newfile .= "{$markerline}\n";
					else
						$newfile .= "{$markerline}";
                elseif ( $getexternals ):
                    // look for existing external stylesheets and add to imports config data
                    if ( preg_match( "/wp_enqueue_style.+?'chld_thm_cfg_ext\d+'.+?'(.+?)'/", $markerline, $matches ) )
                        $this->convert_enqueue_to_import( $matches[ 1 ] );
				endif;
				if ( strpos( $markerline, '// END ' . $marker ) !== FALSE ):
					$newfile .= "// BEGIN {$marker}\n";
					if ( is_array( $insertion ) )
						foreach ( $insertion as $insertline )
							$newfile .= "{$insertline}\n";
					$newfile .= "// END {$marker}\n";
					$state = TRUE;
					$foundit = TRUE;
				endif;
			}
        else:
            $this->debug( 'Could not parse functions file', __FUNCTION__ );
            return FALSE;
        endif;
		if ( $foundit ):
            $this->debug( 'Found marker, replaced inline', __FUNCTION__ );
        else:
            // verify there is no PHP close tag at end of file
            if ( ! $phpopen ):
                $this->debug( 'PHP not open', __FUNCTION__ );
                $this->errors[] = __( 'A closing PHP tag was detected in Child theme functions file so "Parent Stylesheet Handling" option was not configured. Closing PHP at the end of the file is discouraged as it can cause premature HTTP headers. Please edit <code>functions.php</code> to remove the final <code>?&gt;</code> tag and click "Generate/Rebuild Child Theme Files" again.', 'child-theme-configurator' );
                return FALSE;
                //$newfile .= '<?php' . LF;
            endif;
			$newfile .= "\n// BEGIN {$marker}\n";
			foreach ( $insertion as $insertline )
				$newfile .= "{$insertline}\n";
			$newfile .= "// END {$marker}\n";
        endif;
        // only write file when getexternals is false
        if ( ! $getexternals ):
            $this->debug( 'Writing new functions file...', __FUNCTION__ );
            if ( $this->is_ajax && is_writable( $filename ) ):
                if ( FALSE === @file_put_contents( $filename, $newfile ) ): 
                    $this->debug( 'Ajax write failed.', __FUNCTION__ );
                    return FALSE;
                endif;
            elseif ( FALSE === $wp_filesystem->put_contents( $this->fspath( $filename ), $newfile ) ):
                $this->debug( 'Filesystem write failed.', __FUNCTION__ );
                return FALSE;
            endif;
            $this->css->converted = 1;
        endif;
    }
    
    // creates/updates file via filesystem API
    function write_child_file( $file, $contents ) {
        if ( !$this->fs ): 
            $this->debug( 'No filesystem access.', __FUNCTION__ );
            return FALSE; // return if no filesystem access
        endif;
        global $wp_filesystem;
        if ( $file = $this->css->is_file_ok( $this->css->get_child_target( $file ), 'write' ) ):
            if ( !$wp_filesystem->exists( $this->fspath( $file ) ) ):
            $this->debug( 'Writing to filesystem: ' . $file, __FUNCTION__ );
                if ( FALSE === $wp_filesystem->put_contents( $this->fspath( $file ), $contents ) ):
                    $this->debug( 'Filesystem write failed.', __FUNCTION__ );
                    return FALSE; 
                endif;
            else:
                $this->debug( 'File exists.', __FUNCTION__ );
                return FALSE;
            endif;
        else:
            $this->debug( 'No directory.', __FUNCTION__ );
            return FALSE;
        endif;
        $this->debug( 'Filesystem write successful.', __FUNCTION__ );
    }
    
    function copy_screenshot( $obj ) {
        // always copy screenshot
        $this->copy_parent_file( 'screenshot' ); 
    }
    
    function copy_parent_file( $file, $ext = 'php' ) {
        if ( !$this->fs ): 
            $this->debug( 'No filesystem access.', __FUNCTION__ );
            return FALSE; // return if no filesystem access
        endif;
        global $wp_filesystem;
        $parent_file = NULL;
        if ( 'screenshot' == $file ):
            foreach ( array_keys( $this->imgmimes ) as $extreg ): 
                foreach( explode( '|', $extreg ) as $ext ):
                    if ( $parent_file = $this->css->is_file_ok( $this->css->get_parent_source( 'screenshot.' . $ext ) ) ) break;
                endforeach; 
                if ( $parent_file ):
                    $parent_file = $this->fspath( $parent_file );
                    break;
                endif;
            endforeach;
        else:
            $parent_file = $this->fspath( $this->css->is_file_ok( $this->css->get_parent_source( $file . '.' . $ext ) ) );
        endif;
        // get child theme + file + ext ( passing empty string and full child path to theme_basename )
        $child_file = $this->css->get_child_target( $file . '.' . $ext );
        // return true if file already exists
        if ( $wp_filesystem->exists( $this->fspath( $child_file ) ) ) return TRUE;
        $child_dir = dirname( $this->theme_basename( '', $child_file ) );
        $this->debug( 'Verifying child dir... ', __FUNCTION__ );
        if ( $parent_file // sanity check
            && $child_file // sanity check
                && $this->verify_child_dir( $child_dir ) //create child subdir if necessary
                    && $wp_filesystem->copy( $parent_file, $this->fspath( $child_file ), FS_CHMOD_FILE ) ) return TRUE;
        $this->errors[] = __( 'Could not copy file:' . $parent_file, 'child-theme-configurator' );
    }
    
    function delete_child_file( $file, $ext = 'php' ) {
        if ( !$this->fs ): 
            $this->debug( 'No filesystem access.', __FUNCTION__ );
            return FALSE; // return if no filesystem access
        endif;
        global $wp_filesystem;
        // verify file is in child theme and exists before removing.
        $file = ( 'img' == $ext ? $file : $file . '.' . $ext );
        if ( $child_file  = $this->css->is_file_ok( $this->css->get_child_target( $file ), 'write' ) ):
            if ( $wp_filesystem->exists( $this->fspath( $child_file ) ) ):
                if ( $wp_filesystem->delete( $this->fspath( $child_file ) ) ):
                    return TRUE;
                else:
                    $this->errors[] = __( 'Could not delete file.', 'child-theme-configurator' );
                    $this->debug( 'Could not delete file', __FUNCTION__ );
                endif;
            endif;
        endif;
    }
    
    function get_files( $theme, $type = 'template' ) {
        if ( !isset( $this->files[ $theme ] ) ):
            $this->files[ $theme ] = array();
            $imgext = '(' . implode( '|', array_keys( $this->imgmimes ) ) . ')';
            foreach ( $this->css->recurse_directory(
                trailingslashit( get_theme_root() ) . $theme, '', TRUE ) as $file ):
                $file = $this->theme_basename( $theme, $file );
                if ( preg_match( "/^style\-(\d+)\.css$/", $file, $matches ) ):
                    $date = date_i18n( 'D, j M Y g:i A', strtotime( $matches[ 1 ] ) );
                    $this->files[ $theme ][ 'backup' ][ $file ] = $date;
                elseif ( preg_match( "/^ctc\-plugins\-(\d+)\.css$/", $file, $matches ) ):
                    $date = date_i18n( 'D, j M Y g:i A', strtotime( $matches[ 1 ] ) );
                    $this->files[ $theme ][ 'pluginbackup' ][ $file ] = $date;
                elseif ( preg_match( "/\.php$/", $file ) ):
                    $this->files[ $theme ][ 'template' ][] = $file;
                elseif ( preg_match( "/\.css$/", $file ) && 'style.css' != $file ):
                    $this->files[ $theme ][ 'stylesheet' ][] = $file;
                elseif ( preg_match( "/^images\/.+?\." . $imgext . "$/", $file ) ):
                    $this->files[ $theme ][ 'img' ][] = $file;
                endif;
            endforeach;
        endif;
        $types = explode(",", $type);
        $files = array();
        foreach ( $types as $type )
            if ( isset( $this->files[ $theme ][ $type ] ) )
                $files = array_merge( $this->files[ $theme ][ $type ], $files );
        return $files;
    }
        
    function theme_basename( $theme, $file ) {
        $file = $this->normalize_path( $file );
        // if no theme passed, returns theme + file
        $themedir = trailingslashit( $this->normalize_path( get_theme_root() ) ) . ( '' == $theme ? '' : trailingslashit( $theme ) );
        $this->debug( 'Themedir: ' . $themedir . ' File: ' . $file , __FUNCTION__ );
        return preg_replace( '%^' . preg_quote( $themedir ) . '%', '', $file );
    }
    
    function uploads_basename( $file ) {
        $file = $this->normalize_path( $file );
        $uplarr = wp_upload_dir();
        $upldir = trailingslashit( $this->normalize_path( $uplarr[ 'basedir' ] ) );
        return preg_replace( '%^' . preg_quote( $upldir ) . '%', '', $file );
    }
    
    function uploads_fullpath( $file ) {
        $file = $this->normalize_path( $file );
        $uplarr = wp_upload_dir();
        $upldir = trailingslashit( $this->normalize_path( $uplarr[ 'basedir' ] ) );
        return $upldir . $file;
    }
    
    function serialize_postarrays() {
        foreach ( $this->postarrays as $field )
            if ( isset( $_POST[ $field ] ) && is_array( $_POST[ $field ] ) )
                $_POST[ $field ] = implode( "%%", $_POST[ $field ] );
    }
    
    function unserialize_postarrays() {
        foreach ( $this->postarrays as $field )
            if ( isset( $_POST[ $field ] ) && !is_array( $_POST[ $field ] ) )
                $_POST[ $field ] = explode( "%%", $_POST[ $field ] );
    }
    
    function set_writable( $file = NULL ) {

        $file = isset( $file ) ? $this->css->get_child_target( $file . '.php' ) : 
            apply_filters( 'chld_thm_cfg_target', $this->css->get_child_target(), $this->css );
        if ( $this->fs ): // filesystem access
            global $wp_filesystem;
            if ( $file && $wp_filesystem->chmod( $this->fspath( $file ), 0666 ) ) return;
        endif;
        $this->errors[] = __( 'Could not set write permissions.', 'child-theme-configurator' );
        add_action( 'admin_notices', array( $this, 'writable_notice' ) ); 	
        return FALSE;
    }
    
    function clone_child_theme( $child, $clone ) {
        if ( !$this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        // set child theme if not set for get_child_target to use new child theme as source
        $this->css->set_prop( 'child', $child );

        $dir        = untrailingslashit( $this->css->get_child_target( '' ) );
        $themedir   = trailingslashit( get_theme_root() );
        $fsthemedir = $this->fspath( $themedir );
        $files = $this->css->recurse_directory( $dir, NULL, TRUE );
        $errors = array();
        foreach ( $files as $file ):
            $childfile  = $this->theme_basename( $child, $this->normalize_path( $file ) );
            $newfile    = trailingslashit( $clone ) . $childfile;
            $childpath  = $fsthemedir . trailingslashit( $child ) . $childfile;
            $newpath    = $fsthemedir . $newfile;
            $this->debug( 'Verifying child dir... ', __FUNCTION__ );
            if ( $this->verify_child_dir( is_dir( $file ) ? $newfile : dirname( $newfile ) ) ):
                if ( is_file( $file ) && !@$wp_filesystem->copy( $childpath, $newpath ) ):
                    $errors[] = 'could not copy ' . $newpath;
                endif;
            else:
                $errors[] = 'invalid dir: ' . $newfile;
            endif;
        endforeach;
    }

    function unset_writable() {
        if ( !$this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        $dir        = untrailingslashit( $this->css->get_child_target( '' ) );
        $child      = $this->theme_basename( '', $dir );
        $newchild   = untrailingslashit( $child ) . '-new';
        $themedir   = trailingslashit( get_theme_root() );
        $fsthemedir = $this->fspath( $themedir );
        // is child theme owned by user? 
        if ( fileowner( $dir ) == fileowner( $themedir ) ):
            $copy   = FALSE;
            $wp_filesystem->chmod( $dir );
            // recursive chmod ( as user )
            // WP_Filesystem RECURSIVE CHMOD IS FLAWED! IT SETS ALL CHILDREN TO PERM OF OUTERMOST DIR
            //if ( $wp_filesystem->chmod( $this->fspath( $dir ), FALSE, TRUE ) ):
            //endif;
        else:
            $copy   = TRUE;
        endif;
        // n -> copy entire folder ( as user )
        $files = $this->css->recurse_directory( $dir, NULL, TRUE );
        $errors = array();
        foreach ( $files as $file ):
            $childfile  = $this->theme_basename( $child, $this->normalize_path( $file ) );
            $newfile    = trailingslashit( $newchild ) . $childfile;
            $childpath  = $fsthemedir . trailingslashit( $child ) . $childfile;
            $newpath    = $fsthemedir . $newfile;
            if ( $copy ):
                $this->debug( 'Verifying child dir... ', __FUNCTION__ );
                if ( $this->verify_child_dir( is_dir( $file ) ? $newfile : dirname( $newfile ) ) ):
                    if ( is_file( $file ) && !$wp_filesystem->copy( $childpath, $newpath ) ):
                        $errors[] = 'could not copy ' . $newpath;
                    endif;
                else:
                    $errors[] = 'invalid dir: ' . $newfile;
                endif;
            else:
                $wp_filesystem->chmod( $this->fspath( $file ) );
            endif;
        endforeach;
        if ( $copy ):
            // verify copy ( as webserver )
            $newfiles = $this->css->recurse_directory( trailingslashit( $themedir ) . $newchild, NULL, TRUE );
            $deleteddirs = $deletedfiles = 0;
            if ( count( $newfiles ) == count( $files ) ):
                // rename old ( as webserver )
                if ( !$wp_filesystem->exists( trailingslashit( $fsthemedir ) . $child . '-old' ) )
                    $wp_filesystem->move( trailingslashit( $fsthemedir ) . $child, trailingslashit( $fsthemedir ) . $child . '-old' );
                // rename new ( as user )
                if ( !$wp_filesystem->exists( trailingslashit( $fsthemedir ) . $child ) )
                    $wp_filesystem->move( trailingslashit( $fsthemedir ) . $newchild, trailingslashit( $fsthemedir ) . $child );
                // remove old files ( as webserver )
                $oldfiles = $this->css->recurse_directory( trailingslashit( $themedir ) . $child . '-old', NULL, TRUE );
                array_unshift( $oldfiles, trailingslashit( $themedir ) . $child . '-old' );
                foreach ( array_reverse( $oldfiles ) as $file ):
                    if ( $wp_filesystem->delete( $this->fspath( $file ) ) 
                        || ( is_dir( $file ) && @rmdir( $file ) ) 
                            || ( is_file( $file ) && @unlink( $file ) ) ):
                        $deletedfiles++;
                    endif;
                endforeach;
                if ( $deletedfiles != count( $oldfiles ) ):
                    $errors[] = 'deleted: ' . $deletedfiles . ' != ' . count( $oldfiles ) . ' files';
                endif;
            else:
                $errors[] = 'newfiles != files';
            endif;
        endif;
        if ( count( $errors ) ):
            $this->errors[] = __( 'There were errors while resetting permissions.', 'child-theme-configurator' ) ;
            add_action( 'admin_notices', array( $this, 'writable_notice' ) ); 	
        endif;
    }
    
    function set_skip_form() {
        $this->skip_form = TRUE;
    }
    
    function handle_file_upload( $field, $childdir = NULL, $mimes = NULL ){
        $uploadedfile = $_FILES[ $field ];
        $upload_overrides = array( 
            'test_form' => FALSE,
            'mimes' => ( is_array( $mimes ) ? $mimes : NULL )
        );
        if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
        $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
        if ( isset( $movefile[ 'error' ] ) ):
            $this->errors[] = $movefile[ 'error' ];
            return FALSE;
        endif;
        $_POST[ 'movefile' ] = $this->uploads_basename( $movefile[ 'file' ] );        
    }
    
    function move_file_upload( $subdir = 'images' ) {
        if ( !$this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        $source_file = sanitize_text_field( $_POST[ 'movefile' ] );
        $target_file = ( '' == $subdir ? 
            preg_replace( "%^.+(\.\w+)$%", "screenshot$1", basename( $source_file ) ) : 
                trailingslashit( $subdir ) . basename( $source_file ) );
        $this->debug( 'Verifying child dir... ', __FUNCTION__ );
        if ( FALSE !== $this->verify_child_dir( trailingslashit( $this->css->get_prop( 'child' ) ) . $subdir ) ):
            $source_path = $this->fspath( $this->uploads_fullpath( $source_file ) );
            if ( $target_path = $this->css->is_file_ok( $this->css->get_child_target( $target_file ), 'write' ) ):
                $target_path = $this->fspath( $target_path );
                if ( $wp_filesystem->exists( $source_path ) ):
                    if ( $wp_filesystem->move( $source_path, $target_path ) ) return TRUE;
                endif;
            endif;
        endif;
        
        $this->errors[] = __( 'Could not upload file.', 'child-theme-configurator' );        
    }
    
    function export_zip() {
        if ( ( $child = $this->css->get_prop( 'child' ) ) 
            && ( $dir = $this->css->is_file_ok( dirname( $this->css->get_child_target() ), 'search' ) )
            && ( $version = preg_replace( "%[^\w\.\-]%", '', $this->css->get_prop( 'version' ) ) ) ):
            // use php system upload dir to store temp files so that we can use pclzip
            $tmpdir = ini_get( 'upload_tmp_dir' ) ? ini_get( 'upload_tmp_dir' ) : sys_get_temp_dir();
            $file = trailingslashit( $tmpdir ) . $child . '-' . $version . '.zip';
            mbstring_binary_safe_encoding();

            require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

            $archive = new PclZip( $file );
            if ( $archive->create( $dir, PCLZIP_OPT_REMOVE_PATH, dirname( $dir ) ) == 0 ) return FALSE;
        	reset_mbstring_encoding();
            header( 'Content-Description: File Transfer' );
            header( 'Content-Type: application/octet-stream' );
            header( 'Content-Length: ' . filesize( $file ) );
            header( 'Content-Disposition: attachment; filename=' . basename( $file ) );
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate' );
            header( 'Pragma: public' );
            readfile( $file );
            unlink( $file );
            die();
        endif;
    }
        
    /*
     *
     */
    function verify_creds( $args = array() ) {
        $this->fs_prompt = $this->fs = FALSE;
        //fs prompt does not support arrays as post data - serialize arrays
        $this->serialize_postarrays();
        // generate callback url
        $ctcpage = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
        $url = is_multisite() ?  network_admin_url( 'themes.php?page=' . $ctcpage ) :
            admin_url( 'tools.php?page=' . $ctcpage );
        $nonce_url = wp_nonce_url( $url, apply_filters( 'chld_thm_cfg_action', 'ctc_update' ), '_wpnonce' );
        // buffer output so we can process prior to http header
        ob_start();
        if ( $creds = request_filesystem_credentials( $nonce_url, '', FALSE, FALSE, $args ) ):
            // check filesystem permission if direct or ftp creds exist
            if ( WP_Filesystem( $creds ) )
                // login ok
                $this->fs = TRUE;
            else
                // incorrect credentials, get form with error flag
                $creds = request_filesystem_credentials( $nonce_url, '', TRUE, FALSE, $args );
        else:
            // no credentials, initialize unpriveledged filesystem object
            WP_Filesystem();
        endif;
        // if form was generated, store it
        $this->fs_prompt = ob_get_contents();
        // now we can read/write if fs is TRUE otherwise fs_prompt will contain form
        ob_end_clean();
         //fs prompt does not support arrays as post data - unserialize arrays
        $this->unserialize_postarrays();
   }
    
    /*
     * convert 'direct' filepath into wp_filesystem filepath
     */
    function fspath( $file ){
        if ( ! $this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        if ( is_dir( $file ) ):
            $dir = $file;
            $base = '';
        else:
            $dir = dirname( $file );
            $base = basename( $file );
        endif;
        $fsdir = $wp_filesystem->find_folder( $dir );
        return trailingslashit( $fsdir ) . $base;
    }
    
    function writable_notice() {
?>    <div class="update-nag" style="display:block">
        <div class="ctc-section-toggle" id="ctc_perm_options"><?php _e( 'The child theme is in read-only mode and Child Theme Configurator cannot apply changes. Click to see options', 'child-theme-configurator' ); ?></div><div class="ctc-section-toggle-content" id="ctc_perm_options_content"><p><ol><?php
        $ctcpage = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
        if ( 'WIN' != substr( strtoupper( PHP_OS ), 0, 3 ) ):
            _e( '<li>Temporarily set write permissions by clicking the button below. When you are finished editing, revert to read-only by clicking "Make read-only" under the "Files" tab.</li>', 'child-theme-configurator' );
?><form action="?page=<?php echo $ctcpage; ?>" method="post">
    <?php wp_nonce_field( apply_filters( 'chld_thm_cfg_action', 'ctc_update' ) ); ?>
<input name="ctc_set_writable" class="button" type="submit" value="<?php _e( 'Make files writable', 'child-theme-configurator' ); ?>"/></form><?php   endif;
        _e( '<li><a target="_blank"  href="http://codex.wordpress.org/Editing_wp-config.php#WordPress_Upgrade_Constants" title="Editin wp-config.php">Add your FTP/SSH credentials to the WordPress config file</a>.</li>', 'child-theme-configurator' );
        if ( isset( $_SERVER[ 'SERVER_SOFTWARE' ] ) && preg_match( '%iis%i',$_SERVER[ 'SERVER_SOFTWARE' ] ) )
            _e( '<li><a target="_blank" href="http://technet.microsoft.com/en-us/library/cc771170" title="Setting Application Pool Identity">Assign WordPress to an application pool that has write permissions</a> (Windows IIS systems).</li>', 'child-theme-configurator' );
        _e( '<li><a target="_blank" href="http://codex.wordpress.org/Changing_File_Permissions" title="Changing File Permissions">Set write permissions on the server manually</a> (not recommended).</li>', 'child-theme-configurator' );
        if ( 'WIN' != substr( strtoupper( PHP_OS ), 0, 3 ) ):
            _e( '<li>Run PHP under Apache with suEXEC (contact your web host).</li>', 'child-theme-configurator' );
        endif; ?>
        </ol></p></div>
</div>
    <?php
    }
    
    function owner_notice() {
        $ctcpage = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
    ?>
    <div class="update-nag">
        <p><?php _e( 'This Child Theme is not owned by your website account. It may have been created by a prior version of this plugin or by another program. Moving forward, it must be owned by your website account to make changes. Child Theme Configurator will attempt to correct this when you click the button below.', 'child-theme-configurator' ) ?></p>
<form action="?page=<?php echo $ctcpage; ?>" method="post">
    <?php wp_nonce_field( apply_filters( 'chld_thm_cfg_action', 'ctc_update' ) ); ?>
<input name="ctc_reset_permission" class="button" type="submit" value="<?php _e( 'Correct Child Theme Permissions', 'child-theme-configurator' ); ?>"/></form>    </div>
    <?php
    }

    function enqueue_notice() {
    ?>
    <div class="update-nag">
        <p><?php _e( 'Child Theme Configurator needs to update its interal data. Please set your preferences below and click "Generate Child Theme Files" to update your configuration.', 'child-theme-configurator' ) ?></p>
    </div>
    <?php
    }

    function max_styles_notice( $msg ) {
        return $msg . ' ' .  sprintf( __( '<strong>However, some styles could not be parsed due to memory limits.</strong> Try deselecting "Additional Stylesheets" below and click "Generate/Rebuild Child Theme Files". %sWhy am I seeing this?%s', 'child-theme-configurator' ), 
                '<a target="_blank" href="' . LILAEAMEDIA_URL . '/child-theme-configurator#php_memory">',
                '</a>' );
        return $msg;
    }

    function config_notice() {
    ?>
    <div class="update-nag">
        <p><?php _e( 'Child Theme Configurator did not detect any configuration data because a previously configured Child Theme has been removed. Please set your preferences below and click "Generate Child Theme Files".', 'child-theme-configurator' ) ?></p>
    </div>
    <?php
    }

    // back compatibility function for legacy plugins extension
    function write_addl_files( $obj ) {
        global $chld_thm_cfg_plugins;
        if ( !is_object( $chld_thm_cfg_plugins ) || !$this->fs ) return FALSE;
        $configtype = $this->css->get_prop( 'configtype' );
        //echo $configtype . LF;
        if ( 'theme' == $configtype || !( $def = $chld_thm_cfg_plugins->defs->get_def( $configtype ) ) ) return FALSE;
        $child = trailingslashit( $this->css->get_prop( 'child' ) );
        if ( isset( $def[ 'addl' ] ) && is_array( $def[ 'addl' ] ) && count( $def[ 'addl' ] ) ):
            foreach ( $def[ 'addl' ] as $path => $type ):
            
                // sanitize the crap out of the target data -- it will be used to create paths
                $path = $this->normalize_path( preg_replace( "%[^\w\\//\-]%", '', sanitize_text_field( $child . $path ) ) );
                $this->debug( 'Verifying child dir... ', __FUNCTION__ );
                if ( ( 'dir' == $type && FALSE === $this->verify_child_dir( $path ) )
                    || ( 'dir' != $type && FALSE === $this->write_child_file( $path, '' ) ) ):
                    //$this->errors[] = __( 'Your theme directories are not writable.', 'chld_thm_cfg_plugins' );
                endif;
            endforeach;
        endif;
        // write main def file
        if ( isset( $def[ 'target' ] ) ):
            $path = $this->normalize_path( preg_replace( "%[^\w\\//\-\.]%", '', sanitize_text_field( $def[ 'target' ] ) ) ); //$child . 
            if ( FALSE === $this->write_child_file( $path, '' ) ):
                //echo "invalid path: " . $path . ' ' . ' was: ' . $def[ 'target' ] . LF;
                //$this->errors[] = __( 'Your stylesheet is not writable.', 'chld_thm_cfg_plugins' );
                return FALSE;
            endif;
        endif;        
    }
    
    // backwards compatability < WP 3.9
    function normalize_path( $path ) {
	    $path = str_replace( '\\', '/', $path );
	    $path = preg_replace( '|/+|','/', $path );
	    return $path;
    }

    // case insensitive theme search    
    function check_theme_exists( $theme ) {
        $search_array = array_map( 'strtolower', array_keys( wp_get_themes() ) );
        return in_array( strtolower( $theme ), $search_array );
    }
    
    // helper functions to support legacy plugin extension
    function is_legacy() {
        return defined('CHLD_THM_CFG_PLUGINS_VERSION') 
            && version_compare( CHLD_THM_CFG_PLUGINS_VERSION, '2.0.0', '<' );
    }
    
    /* not using plugin mode */
    function is_theme( $configtype = '' ) {
        // if filter returns a value, we are using plugin mode
        // otherwise if configtype has a value and it is not a theme then we are in legacy plugin mode
        $pluginmode = apply_filters( 'chld_thm_cfg_action', NULL );
        if ( $pluginmode || ( !empty( $configtype ) && 'theme' != $configtype ) ):
            return FALSE;
        endif;
        if ( $this->is_legacy()
            && is_object( $this->css ) 
                && ( $configtype = $this->css->get_prop( 'configtype' ) ) 
                    && !empty( $configtype ) && 'theme' != $configtype ):
            return FALSE;
        endif;
        return TRUE;
    }
    
    /* returns parent theme either from existing config or passed as post var */
    function get_current_parent() {
        if ( isset( $_GET[ 'ctc_parent' ] ) && ( $parent = sanitize_text_field( $_GET[ 'ctc_parent' ] ) ) )
            return $parent;
        elseif ( $parent = $this->css->get_prop( 'parnt' ) )
            return $parent;
        else return get_template();
    }
    
    /* returns child theme either from existing config or passed as post var */
    function get_current_child() {
        if ( isset( $_GET[ 'ctc_child' ] ) && ( $child = sanitize_text_field( $_GET[ 'ctc_child' ] ) ) )
            return $child;
        elseif ( $child = $this->css->get_prop( 'child' ) )
            return $child;
        else return get_stylesheet();
    }
    /* debug backtrace with extraneous steps (missing class, function or line) removed */
    function backtrace_summary() {
        $bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
        $thisstep = array_shift( $bt );
        foreach ( $bt as $ndx => $step )
            if ( isset( $step[ 'class' ] ) && isset( $step[ 'function' ] ) && isset( $step[ 'line' ] ) )
                echo $ndx . ': ' . $step[ 'class' ] . ' ' . $step[ 'function' ] . ' ' . $step[ 'line' ] . LF;
    }

    function debug( $msg = NULL, $fn = NULL) {
        $this->debug .= ( isset( $fn ) ? $fn . ': ' : '' ) . ( isset( $msg ) ? $msg . LF : '' );
    }
    
    function set_benchmark( $step, $key, $label ) {
        $this->memory[ $key ][ $label ][ $step ][ 'memory' ]    = memory_get_usage();
        $this->memory[ $key ][ $label ][ $step ][ 'peak' ]      = memory_get_peak_usage();
        
        $this->memory[ $key ][ $label ][ $step ][ 'selectors' ] = isset( $this->css ) ? $this->css->qskey : 0;
    }
    
    function calc_memory_usage() {
        //$this->debug( print_r( $this->memory, TRUE ), __FUNCTION__ );
        
        $results = array();
        foreach ( $this->memory as $key => $labels ):
            if ( !is_array( $labels ) ) continue;
            foreach ( $labels as $label => $steps ):
                //if ( isset( $steps[ 'before' ] ) && isset( $steps[ 'after ' ] ) ):
                    $results[] = $key . '|' . $label . '|'
                    . ( $steps[ 'after' ][ 'memory' ] - $steps[ 'before' ][ 'memory' ] ) . '|'
                    . ( $steps[ 'after' ][ 'peak' ] - $steps[ 'before' ][ 'peak' ] ) . '|'
                    . ( $steps[ 'after' ][ 'peak' ] - $steps[ 'before' ][ 'memory' ] ) . '|'
                    . $steps[ 'after' ][ 'selectors' ] . '|'
                    . ( $steps[ 'after' ][ 'selectors' ] - $steps[ 'before' ][ 'selectors' ] );
                //endif;
            endforeach;
        endforeach;
        $this->debug( "\n" . implode( "\n", $results ), __FUNCTION__ );
    }
    
    function get_free_memory() {
        $limit = (int) ini_get('memory_limit') * 1024 * 1024;
        $used = memory_get_usage();
        return $limit - $used;
    }
    
    function print_debug( $noecho = FALSE ) {
        $debug = '<textarea style="width:100%;height:200px">' . LF . $this->debug . LF . '</textarea>' . LF;
        if ( $noecho ) return $debug;
        echo $debug;
    }
    
    function parse_parent_stylesheet() {
        $this->css->parse_css_file( 'parnt' );
    }
    
    function parse_child_stylesheet() {
        // get revert/backup 
        $revert = isset( $_POST[ 'ctc_revert' ] ) ? sanitize_text_field( $_POST[ 'ctc_revert' ] ) : '';
        // parse child stylesheet, backup or skip ( to reset )
        $this->css->parse_css_file( 'child', $revert );
    }
    
    function parse_additional_stylesheets() {
        // parse additional stylesheets
        if ( isset( $_POST[ 'ctc_additional_css' ] ) && is_array( $_POST[ 'ctc_additional_css' ] ) ):
            $this->css->addl_css = array();
            foreach ( $_POST[ 'ctc_additional_css' ] as $file ):
                $file = sanitize_text_field( $file );
                $this->css->parse_css_file( 'parnt', $file );
                $this->css->addl_css[] = $file;
            endforeach;
        endif;
    }
    
    function copy_theme_mods( $from, $to ) {
        
        // we can copy settings from parent to child even if neither is currently active
        // so we need cases for active parent, active child or neither
        
        // get active theme
        $active_theme = get_stylesheet();
        $this->debug( 'from: ' . $from . ' to: ' . $to . ' active: ' . $active_theme, __FUNCTION__ );
        // create temp array from parent settings
        $child_mods = get_option( 'theme_mods_' . $from );
        if ( $active_theme == $from ):
            $this->debug( 'from is active, using active widgets', __FUNCTION__ );
            // if parent theme is active, get widgets from active sidebars_widgets array
            $child_widgets = retrieve_widgets();
        else:
            $this->debug( 'from not active, using theme mods widgets', __FUNCTION__ );
            // otherwise get widgets from parent theme mods
            $child_widgets = $child_mods[ 'sidebars_widgets' ][ 'data' ];
        endif;
        if ( $active_theme == $to ):
            $this->debug( 'to active, setting active widgets', __FUNCTION__ );
            // if child theme is active, remove widgets from temp array
            unset( $child_mods[ 'sidebars_widgets' ] );
            // copy widgets to active sidebars_widgets array
            wp_set_sidebars_widgets( $child_widgets );
        else:
            $this->debug( 'child not active, saving widgets in theme mods', __FUNCTION__ );
            // otherwise copy widgets to temp array with time stamp
            $child_mods[ 'sidebars_widgets' ][ 'data' ] = $child_widgets;
            $child_mods[ 'sidebars_widgets' ][ 'time' ] = time();
        endif;
        $this->debug( 'saving child theme mods:' . LF . print_r( $child_mods, TRUE ), __FUNCTION__ );
        // copy temp array to child mods
        update_option( 'theme_mods_' . $to, $child_mods );
    }
    
    function network_enable() {
        if ( $child = $this->css->get_prop( 'child' ) ):
            $allowed_themes = get_site_option( 'allowedthemes' );
            $allowed_themes[ $child ] = true;
            update_site_option( 'allowedthemes', $allowed_themes );
        endif;
    }
}
