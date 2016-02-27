<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*
    Class: ChildThemeConfiguratorCSS
    Plugin URI: http://www.childthemeconfigurator.com/
    Description: Handles all CSS input, output, parsing, normalization and storage
    Version: 1.7.9.1
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2014-2015 Lilaea Media
*/
class ChildThemeConfiguratorCSS {
    // data dictionaries
    var $dict_query;        // @media queries and 'base'
    var $dict_sel;          // selectors  
    var $dict_qs;           // query/selector lookup
    var $dict_rule;         // css rules
    var $dict_val;          // css values
    var $dict_seq;          // child load order (priority)
    // hierarchies
    var $sel_ndx;           // query => selector hierarchy
    var $val_ndx;           // selector => rule => value hierarchy
    // key counters
    var $qskey;             // counter for dict_qs
    var $querykey;          // counter for dict_query
    var $selkey;            // counter for dict_sel
    var $rulekey;           // counter for dict_rule
    var $valkey;            // counter for dict_val
    // miscellaneous properties
    var $imports;           // @import rules
    var $styles;            // temporary update cache
    var $child;             // child theme slug
    var $parnt;             // parent theme slug
    var $configtype;        // legacy plugin slug
    var $addl_css;          // parent additional stylesheets
    var $recent;            // history of edited styles
    var $enqueue;           // load parent css method (enqueue, import, none)
    var $converted;         // @imports coverted to <link>?
    var $nowarn;            // ignore stylesheet handling warnings
    var $child_name;        // child theme name
    var $child_author;      // child theme author
    var $child_authoruri;   // child theme author website
    var $child_themeuri;    // child theme website
    var $child_descr;       // child theme description
    var $child_tags;        // child theme tags
    var $child_version;     // stylesheet version
    var $max_sel;
    var $temparray;
    var $vendorrule       = array(
        'box\-sizing',
        'font\-smoothing',
        'border(\-(top|right|bottom|left))*\-radius',
        'box\-shadow',
        'transition',
        'transition\-property',
        'transition\-duration',
        'transition\-timing\-function',
        'transition\-delay',
        'hyphens',
        'transform',
        'columns',
        'column\-gap',
        'column\-count',
    );
    var $configvars = array(
        'addl_css',
        // the enqueue flag prevents the transition from 1.5.4 
        // from breaking the stylesheet by forcing the user to regenerate
        // the config data before updating the stylesheet. Otherwise,
        // removing the @import for the parent stylesheet will cause
        // the parent core styles to be missing.
        'enqueue', 
        'max_sel',
        'imports',
        'child_version',
        'child_author',
        'child_name',
        'child_themeuri',
        'child_authoruri',
        'child_descr',
        'child_tags',
        'parnt',
        'child',
        'configtype', // legacy support
        'valkey',
        'rulekey',
        'qskey',
        'selkey',
        'querykey',
        'recent',
        'converted',
        'nowarn',
    );
    var $dicts = array(
        'dict_qs',
        'dict_sel',
        'dict_query',
        'dict_rule',
        'dict_val',
        'dict_seq',
        'sel_ndx',
        'val_ndx',
    );
    
    function __construct() {
        // scalars
        $this->querykey         = 0;
        $this->selkey           = 0;
        $this->qskey            = 0;
        $this->rulekey          = 0;
        $this->valkey           = 0;
        $this->child            = '';
        $this->parnt            = '';
        $this->configtype       = 'theme'; // legacy support
        $this->child_name       = '';
        $this->child_author     = 'Child Theme Configurator';
        $this->child_themeuri   = '';
        $this->child_authoruri  = '';
        $this->child_descr      = '';
        $this->child_tags       = '';
        $this->child_version    = '1.0';
        $this->max_sel          = 0;

        // multi-dim arrays
        $this->dict_qs          = array();
        $this->dict_sel         = array();
        $this->dict_query       = array();
        $this->dict_rule        = array();
        $this->dict_val         = array();
        $this->dict_seq         = array();
        $this->sel_ndx          = array();
        $this->val_ndx          = array();
        $this->addl_css         = array();
        $this->recent           = array();
        $this->imports          = array( 'child' => array(), 'parnt' => array() );
    }
    
    // helper function to globalize ctc object
    function ctc() {
        return ChildThemeConfigurator::ctc();
    }
    
    // loads current ctc config data into local memory
    function load_config() {
        $option = CHLD_THM_CFG_OPTIONS . apply_filters( 'chld_thm_cfg_option', '' );
        //echo 'loading option: ' . $option . LF;
        if ( ( $configarray = get_site_option( $option . '_configvars' ) ) && count( $configarray ) ):
            foreach ( $this->configvars as $configkey ):
                if ( isset( $configarray[ $configkey ] ) )
                    $this->{$configkey} = $configarray[ $configkey ];
            endforeach;
            $this->ctc()->debug( 'configvars: ' . print_r( $configarray, TRUE ), __FUNCTION__ );
            foreach ( $this->dicts as $configkey ):
                if ( ( $configarray = get_site_option( $option . '_' . $configkey ) ) && count( $configarray ) )
                    $this->{$configkey} = $configarray;
            endforeach;
        else:
            return FALSE;
        endif;
    }
    
    // writes ctc config data to options api
    function save_config( $override = NULL ) {
        global $wpdb;
        if ( isset( $override ) ) $option = $override;
        else $option = apply_filters( 'chld_thm_cfg_option', '' );
        $option = CHLD_THM_CFG_OPTIONS . $option;
        //echo 'saving option: ' . $option . LF;
        $configarray = array();
        foreach ( $this->configvars as $configkey )
            $configarray[ $configkey ] = $this->{$configkey};
        $this->ctc()->debug( 'configvars: ' . print_r( $configarray, TRUE ), __FUNCTION__ );
        if ( is_multisite() ):
            update_site_option( $option . '_configvars', $configarray ); 
        else:
            // do not autoload ( passing false above only works if value changes
            update_option( $option . '_configvars', $configarray, FALSE ); 
        endif;
        foreach ( $this->dicts as $configkey ):
            if ( is_multisite() ):
                update_site_option( $option . '_' . $configkey, $this->{$configkey} ); 
            else:
                // do not autoload ( passing false above only works if value changes
                update_option( $option . '_' . $configkey, $this->{$configkey}, FALSE );
            endif;
        endforeach;
    }
    
    /**
     * get_prop
     * Getter interface (data sliced different ways depending on objname )
     */
    function get_prop( $objname, $params = NULL ) {
        switch ( $objname ):
            case 'imports':
                return $this->obj_to_utf8( is_array( $this->imports[ 'child' ] ) ? 
                    ( current( $this->imports[ 'child' ] ) == 1 ? 
                        array_keys( $this->imports[ 'child' ] ) : 
                            array_keys( array_flip( $this->imports[ 'child' ] ) ) ) : 
                                array() );
            case 'queries':
                return $this->obj_to_utf8( $this->denorm_sel_ndx() );
            case 'selectors':
                return empty( $params[ 'key' ] ) ? 
                    array() : $this->obj_to_utf8( $this->denorm_sel_ndx( $params[ 'key' ] ) );
            case 'rule_val':
                return empty( $params[ 'key' ] ) ? array() : $this->denorm_rule_val( $params[ 'key' ] );
            case 'val_qry':
                if ( isset( $params[ 'rule' ] ) && isset( $this->dict_rule[ $params[ 'rule' ] ] ) ):
                    return empty( $params[ 'key' ] ) ? 
                        array() : $this->denorm_val_query( $params[ 'key' ], $params[ 'rule' ] );
                endif;
            case 'qsid':
                return empty( $params[ 'key' ] ) ? 
                    array() : $this->obj_to_utf8( $this->denorm_sel_val( $params[ 'key' ] ) );
            case 'rules':
                ksort( $this->dict_rule );
                return $this->obj_to_utf8( $this->dict_rule );;
            case 'child':
                return $this->child;
            case 'parnt':
                return $this->parnt;
            case 'configtype': // legacy plugin extension support
                return $this->configtype;
            case 'addl_css':
                return isset( $this->addl_css ) ? $this->addl_css : array();
            case 'child_name':
                return $this->child_name;
            case 'author':
                return $this->child_author;
            case 'themeuri':
                return isset( $this->child_themeuri ) ? $this->child_themeuri : FALSE;
            case 'authoruri':
                return isset( $this->child_authoruri ) ? $this->child_authoruri : FALSE;
            case 'descr':
                return isset( $this->child_descr ) ? $this->child_descr : FALSE;
            case 'tags':
                return isset( $this->child_tags ) ? $this->child_tags : FALSE;
            case 'version':
                return $this->child_version;
            case 'preview':
                $this->styles = '';
                if ( empty( $params[ 'key' ] ) || 'child' == $params[ 'key' ] ):
                    $this->read_stylesheet( 'child' );
                else:
                    if ( isset( $this->addl_css ) ):
                        foreach ( $this->addl_css as $file ):
                            $this->styles .= '/*** BEGIN ' . $file . ' ***/' . LF;
                            $this->read_stylesheet( 'parnt', $file );
                            $this->styles .= '/*** END ' . $file . ' ***/' . LF;
                        endforeach;
                    endif;
                    list ( $template, $file ) = apply_filters( 'chld_thm_cfg_parent_preview_args', array( 'parnt', 'style.css' ) );
                    if ( $this->ctc()->is_theme() || $this->ctc()->is_legacy() ):
                        $this->styles .= '/*** BEGIN ' . $file . ' ***/' . LF;
                        $this->read_stylesheet( $template, $file );
                        $this->styles .= '/*** END ' . $file . ' ***/' . LF;
                    endif;
                endif;
                $this->normalize_css();
                return $this->styles;
                break;
            default:
                return $this->obj_to_utf8( apply_filters( 'chld_thm_get_prop', NULL, $objname, $params ) );
        endswitch;
        return FALSE;
    }

    /**
     * set_prop
     * Setter interface (scalar values only)
     */
    function set_prop( $prop, $value ) {
        if ( is_null( $this->{ $prop } ) || is_scalar( $this->{ $prop } ) )
            $this->{ $prop } = $value;
        else return FALSE;
    }
    
    // formats css string for accurate parsing
    function normalize_css() {
        if ( preg_match( "/(\}[\w\#\.]|; *\})/", $this->styles ) ):                     // prettify compressed CSS
            $this->styles = preg_replace( "/\*\/\s*/s", "*/\n",     $this->styles );    // end comment
            $this->styles = preg_replace( "/\{\s*/s", " {\n    ",   $this->styles );    // open brace
            $this->styles = preg_replace( "/;\s*/s", ";\n    ",     $this->styles );    // semicolon
            $this->styles = preg_replace( "/\s*\}\s*/s", "\n}\n",   $this->styles );    // close brace
        endif;
    }
    
    // creates header comments for stylesheet
    function get_css_header() {
        $parnt = $this->get_prop( 'parnt' );
        return '/*' . LF
            . 'Theme Name: ' . $this->get_prop( 'child_name' ) . LF
            . ( ( $attr = $this->get_prop( 'themeuri' ) ) ? 'Theme URI: ' . $attr . LF : '' )
            . 'Template: ' . $parnt . LF
            . 'Author: ' . $this->get_prop( 'author' ) . LF
            . ( ( $attr = $this->get_prop( 'authoruri' ) ) ? 'Author URI: ' . $attr . LF : '' )
            . ( ( $attr = $this->get_prop( 'descr' ) ) ? 'Description: ' . $attr . LF : '' )
            . ( ( $attr = $this->get_prop( 'tags' ) ) ? 'Tags: ' . $attr . LF : '' )
            . 'Version: ' . $this->get_prop( 'version' ) . '.' . time() . LF
            . 'Updated: ' . current_time( 'mysql' ) . LF
            . '*/' . LF . LF . '@charset "UTF-8";' . LF . LF
            . ( 'import' == $this->enqueue ? '@import url(\'../' . $parnt . '/style.css\');' . LF : '' );
    }
    
    // formats file path for child theme file
    function get_child_target( $file = 'style.css' ) {
        return trailingslashit( get_theme_root() ) . trailingslashit( $this->get_prop( 'child' ) ) . $file;
    }
    
    // formats file path for parent theme file
    function get_parent_source( $file = 'style.css' ) {
        return trailingslashit( get_theme_root() ) . trailingslashit( $this->get_prop( 'parnt' ) ) . $file;
    }
    
    /**
     * get_dict_id
     * lookup function retrieves normalized id from string input
     * automatically adds to dictionary if it does not exist
     * incrementing key value for dictionary
     */
    function get_dict_id( $dict, $value ) {
        $property = 'dict_' . $dict;
        $key = $dict . 'key';
        if ( !isset( $this->{ $property }[ $value ] ) )
            // add value to index
            $this->{ $property }[ $value ] = ++$this->{ $key };
        return $this->{ $property }[ $value ];
    }
    
    /**
     * get_qsid
     * query/selector id is the combination of two dictionary values
     * also throttles parsing if memory limit is reached
     */
    function get_qsid( $query, $sel ) {
        $q = $this->get_dict_id( 'query', $query );
        $s = $this->get_dict_id( 'sel', $sel );
        if ( !isset( $this->sel_ndx[ $q ][ $s ] ) ):
            // stop parsing if limit is reached to prevent out of memory on serialize
            if ( $this->qskey >= $this->ctc()->sel_limit ):
                $this->max_sel = 1;
                $this->ctc()->debug( 'Maximum num selectors reached ( limit: ' . $this->ctc()->sel_limit . ' )', __FUNCTION__ );
                return FALSE;
            endif;
            // increment key number
            $this->sel_ndx[ $q ][ $s ] = ++$this->qskey;
            $this->dict_qs[ $this->qskey ][ 's' ] = $s;
            $this->dict_qs[ $this->qskey ][ 'q' ] = $q;
            // update sequence for this selector if this is a later instance to keep cascade priority
            if ( !isset( $this->dict_seq[ $this->qskey ] ) )
                $this->dict_seq[ $this->qskey ] = $this->qskey;
            
        endif;
        return $this->sel_ndx[ $q ][ $s ];
    }
    
    /**
     * update_arrays
     * accepts CSS properties as raw strings and normilizes into 
     * CTC object arrays, creating update cache in the process.
     * ( Update cache is returned to UI via AJAX to refresh page )
     * This has been refactored in v1.7.5 to accommodate multiple values per property.
     * @param   $template   parnt or child
     * @param   $query      media query 
     * @param   $sel        selector
     * @param   $rule       property (rule)
     * @param   $value      individual value ( property has array of values )
     * @param   $important  important flag for value
     * @param   $rulevalid  unique id of value for property
     * @param   $reset      clear current values to prevent multiple values from being generated from Raw CSS post input data
     * @return  $qsid       query/selector id for this entry
     */
    function update_arrays( 
        $template, 
        $query, 
        $sel, 
        $rule       = NULL, 
        $value      = NULL, 
        $important  = 0, 
        $rulevalid  = NULL, 
        $reset      = FALSE 
        ) {
        if ( $this->max_sel ) return;
        if ( FALSE === strpos( $query, '@' ) ):
            $query = 'base';
        endif;
        // normalize selector styling
        $sel = implode( ', ', preg_split( '#\s*,\s*#s', trim( $sel ) ) );
        if ( !( $qsid = $this->get_qsid( $query, $sel ) ) ) return;
        
        // set data and value
        if ( $rule ):
            // get ids and quit if max is reached ( get_qsid handles )
            $ruleid = $this->get_dict_id( 'rule', $rule );
            $valid  = $this->get_dict_id( 'val', $value );
            
            /**
             * v1.7.5
             * modify existing data sructure to allow multiple property values
             */
            
            // create empty array if reset is TRUE 
            // OR IF ruleval array does not exist
            if ( $reset || !isset( $this->val_ndx[ $qsid ][ $ruleid ][ $template ] ) 
                || !is_array( $this->val_ndx[ $qsid ][ $ruleid ][ $template ] ) )
                    $this->val_ndx[ $qsid ][ $ruleid ][ $template ] = array();
            $this->convert_ruleval_array( $this->val_ndx[ $qsid ][ $ruleid ] );
            // rulevalid passed
            if ( isset( $rulevalid ) ):
                $this->unset_rule_value( $this->val_ndx[ $qsid ][ $ruleid ][ $template ], $rulevalid );
                // value empty?
                if ( '' === $value ):
                // value exist?
                elseif ( $id = $this->rule_value_exists( $this->val_ndx[ $qsid ][ $ruleid ][ $template ], $valid ) ):
                    $this->unset_rule_value( $this->val_ndx[ $qsid ][ $ruleid ][ $template ], $id );
                    $this->update_rule_value( $this->val_ndx[ $qsid ][ $ruleid ][ $template ], $rulevalid, $valid, $important );
                // update new value
                else:
                    $this->update_rule_value( $this->val_ndx[ $qsid ][ $ruleid ][ $template ], $rulevalid, $valid, $important );
                endif;
            // rulevalid not passed
            else:
                // value exist?
                if ( $id = $this->rule_value_exists( $this->val_ndx[ $qsid ][ $ruleid ][ $template ], $valid ) ):
                    $this->unset_rule_value( $this->val_ndx[ $qsid ][ $ruleid ][ $template ], $id );
                    $this->update_rule_value( $this->val_ndx[ $qsid ][ $ruleid ][ $template ], $id, $valid, $important );
                // get new id and update new value
                else:
                    $id = $this->get_rule_value_id( $this->val_ndx[ $qsid ][ $ruleid ][ $template ] );
                    $this->update_rule_value( $this->val_ndx[ $qsid ][ $ruleid ][ $template ], $id, $valid, $important );
                endif;
            endif;
            // return query selector id   
            return $qsid;
        endif;
    }
    
    /* 
     * rule_value_exists
     * Determine if a value already exists for a property
     * and return its id
     */
    function rule_value_exists( &$arr, $valid ) {
        foreach ( $arr as $valarr ):
            if ( isset( $valarr[ 0 ] ) && isset( $valarr[ 2 ] ) && $valid == $valarr[ 0 ] ):
                return $valarr[ 2 ];
            endif;
        endforeach;
        return FALSE;
    }
    
    /* 
     * get_rule_value_id
     * Generate a new rulevalid by iterating existing ids
     * and returning the next in sequence
     */
    function get_rule_value_id( &$arr ) {
        $newid = 1;
        foreach ( $arr as $valarr )
            if ( isset( $valarr[ 2 ] ) && $valarr[ 2 ] >= $newid ) $newid = $valarr[ 2 ] + 1;
        return $newid;
    }
    
    /* 
     * update_rule_value
     * Generate a new value subarray
     */
    function update_rule_value( &$arr, $id, $valid, $important ) {
        $arr[] = array(
            $valid,
            $important,
            $id,
        );
    }

    /* 
     * unset_rule_value
     * Delete (splice) old value subarray from values 
     */
    function unset_rule_value( &$arr, $id ) {
        $index = 0;
        foreach ( $arr as $valarr ):
            if ( $id == $valarr[ 2 ] ):
                //echo 'found ' . $valarr[ 2 ] . '(index ' . $index . " )\n";
                array_splice( $arr, $index, 1 );
                break;
            endif;
            ++$index;
        endforeach;
    }
    
    /* 
     * prune_if_empty
     * Automatically cleans up hierarchies when no values exist 
     * in either parent or child for a given selector.
     */
    function prune_if_empty( $qsid ) {
        $empty = $this->get_dict_id( 'val', '' );
        foreach ( $this->val_ndx[ $qsid ] as $ruleid => $arr ):
            foreach ( array( 'child', 'parnt' ) as $template ):
                if ( isset( $arr[ $template ] ) ):
                    // v1.7.5: don't prune until converted to multi value format
                    if ( !is_array( $arr[ $template ] ) ) return FALSE; 
                    // otherwise check each value, if not empty return false
                    foreach ( $arr[ $template ] as $valarr ) 
                        if ( $empty != $valarr[ 0 ] ) return FALSE;
                endif;
            endforeach;
        endforeach;
        // no values, prune from sel index, val index and qs dict data ( keep other dictionary records )
        unset( $this->sel_ndx[ $this->dict_qs[ $qsid ][ 'q' ] ][ $this->dict_qs[ $qsid ][ 's' ] ] );
        unset( $this->val_ndx[ $qsid ] );
        unset( $this->dict_qs[ $qsid ] );
        unset( $this->dict_seq[ $qsid ] );
    }
    
    /**
     * recurse_directory
     * searches filesystem for valid files based on parameters and returns array of filepaths.
     * Core WP recurse function is not used because we require logic specific to CTC.
     */
    function recurse_directory( $rootdir, $ext = 'css', $all = FALSE ) {
        // make sure we are only recursing theme and plugin files
        if ( !$this->is_file_ok( $rootdir, 'search' ) ) 
            return array(); 
        $files = array();
        $dirs = array( $rootdir );
        $loops = 0;
        if ( 'img' == $ext )
            $ext = '(' . implode( '|', array_keys( $this->ctc()->imgmimes ) ) . ')';
        while( count( $dirs ) && $loops < CHLD_THM_CFG_MAX_RECURSE_LOOPS ): // failsafe valve
            $loops++;
            $dir = array_shift( $dirs );
            if ( $handle = opendir( $dir ) ):
                while ( FALSE !== ( $file = readdir( $handle ) ) ):
                    if ( preg_match( "/^\./", $file ) ) continue;
                    $filepath  = trailingslashit( $dir ) . $file;
                    if ( is_dir( $filepath ) ):
                        array_unshift( $dirs, $filepath );
                        if ( $all ):
                            $files[] = $filepath; 
                        endif;
                    elseif ( is_file( $filepath ) && ( $all || preg_match( "/\.".$ext."$/i", $filepath ) ) ):
                        $files[] = $filepath;
                    endif;
                endwhile;
                closedir( $handle );
            endif;
        endwhile;
        return $files;
    }
    
    /**
     * parse_post_data
     * Parse user form input into separate properties and pass to update_arrays
     * FIXME - this function has grown too monolithic - refactor and componentize
     */
    function parse_post_data() {
        $this->cache_updates = TRUE;
        if ( isset( $_POST[ 'ctc_new_selectors' ] ) ):
            $this->styles = $this->parse_css_input( LF . $_POST[ 'ctc_new_selectors' ] );
            $this->parse_css( 'child', 
                isset( $_POST[ 'ctc_sel_ovrd_query' ] ) ? trim( $_POST[ 'ctc_sel_ovrd_query' ] ) : NULL, 
                FALSE, 
                '', 
                TRUE
            );
        elseif ( isset( $_POST[ 'ctc_child_imports' ] ) ):
            $this->imports[ 'child' ] = array();
            $this->styles = $this->parse_css_input( $_POST[ 'ctc_child_imports' ] );
            $this->parse_css( 'child' );
        elseif ( isset( $_POST[ 'ctc_configtype' ] ) ):
            ob_start();
            do_action( 'chld_thm_cfg_get_stylesheets' );
            $this->ctc()->updates[] = array(
                'obj'   => 'stylesheets',
                'key'   => '',
                'data'  => ob_get_contents(),
            );
            ob_end_clean();
            ob_start();
            do_action( 'chld_thm_cfg_get_backups' );
            $this->ctc()->updates[] = array(
                'obj'   => 'backups',
                'key'   => '',
                'data'  => ob_get_contents(),
            );
            ob_end_clean();
            return;
        else:
            $newselector = isset( $_POST[ 'ctc_rewrite_selector' ] ) ? 
                $this->sanitize( $this->parse_css_input( $_POST[ 'ctc_rewrite_selector' ] ) ) : NULL;
            $newqsid = NULL;
            // set the custom sequence value
            foreach ( preg_grep( '#^ctc_ovrd_child_seq_#', array_keys( $_POST ) ) as $post_key ):
                if ( preg_match( '#^ctc_ovrd_child_seq_(\d+)$#', $post_key, $matches ) ):
                    $qsid = $matches[ 1 ];
                    $this->dict_seq[ $qsid ] = intval( $_POST[ $post_key ] );
                endif;
            endforeach;
            $parts = array();
            foreach ( preg_grep( '#^ctc_(ovrd|\d+)_child#', array_keys( $_POST ) ) as $post_key ):
                if ( preg_match( '#^ctc_(ovrd|\d+)_child_([\w\-]+?)_(\d+?)_(\d+?)(_(.+))?$#', $post_key, $matches ) ):
                    $valid      = $matches[ 1 ];
                    $rule       = $matches[ 2 ];
                    if ( NULL == $rule || !isset( $this->dict_rule[ $rule ] ) ) continue;
                    $ruleid     = $this->dict_rule[ $rule ];
                    $qsid       = $matches[ 3 ];
                    $rulevalid  = $matches[ 4 ];
                    $value      = $this->normalize_color( $this->sanitize( $this->parse_css_input( $_POST[ $post_key ] ) ) );
                    $important  = $this->is_important( $value );
                    if ( !empty( $_POST[ 'ctc_' . $valid . '_child_' . $rule . '_i_' . $qsid . '_' . $rulevalid ] ) ) $important = 1;
                    $selarr = $this->denorm_query_sel( $qsid );
                    if ( !empty( $matches[ 6 ] ) ):
                        $parts[ $qsid ][ $rule ][ 'values' ][ $rulevalid ][ $matches[ 6 ] ] = $value;
                        $parts[ $qsid ][ $rule ][ 'values' ][ $rulevalid ][ 'important' ]   = $important;
                        $parts[ $qsid ][ $rule ][ 'query' ]     = $selarr[ 'query' ];
                        $parts[ $qsid ][ $rule ][ 'selector' ]  = $selarr[ 'selector' ];
                    else:
                        if ( $newselector && $newselector != $selarr[ 'selector' ] ):
                            // If this is a renamed selector, add new selector to array 
                            $newqsid = $this->update_arrays( 
                                'child',
                                $selarr[ 'query' ],
                                $newselector,
                                $rule,
                                trim( $value ),
                                $important,
                                $rulevalid
                            );
                            // clear the original selector's child value:
                            $this->update_arrays(
                                'child',
                                $selarr[ 'query' ],
                                $selarr[ 'selector' ],
                                $rule,
                                '',
                                0,
                                $rulevalid
                            );
                            $this->dict_seq[ $newqsid ] = $this->dict_seq[ $qsid ];
                        else:
                            // otherwise, just update with the new values:
                            $this->update_arrays( 
                                'child', 
                                $selarr[ 'query' ], 
                                $selarr[ 'selector' ], 
                                $rule, 
                                trim( $value ), 
                                $important, 
                                $rulevalid
                            );
                        endif;
                    endif;
                endif;
            endforeach;
            /** 
             * Inputs for border and background-image are broken into multiple "rule parts"
             * With the addition of multiple property values in v1.7.5, the parts loop 
             * has been modified to segment the parts into rulevalids under a new 'values' array. 
             * The important flag has also been moved into the parts array.
             */
            foreach ( $parts as $qsid => $rules ):
                foreach ( $rules as $rule => $rule_arr ):
                    // new 'values' array to segment parts into rulevalids
                    foreach ( $rule_arr[ 'values' ] as $rulevalid => $rule_part ):
                        if ( 'background' == $rule ):
                            $value = $rule_part[ 'background_url' ];
                        elseif ( 'background-image' == $rule ):
                            if ( empty( $rule_part[ 'background_url' ] ) ):
                                if ( empty( $rule_part[ 'background_color2' ] ) ):
                                    $value = '';
                                else:
                                    if ( empty( $rule_part[ 'background_origin' ] ) )
                                        $rule_part[ 'background_origin' ] = 'top';
                                    if ( empty( $rule_part[ 'background_color1' ] ) )
                                        $rule_part[ 'background_color1' ] = $rule_part[ 'background_color2' ];
                                    $value = implode( ':', array(
                                        $rule_part[ 'background_origin' ], 
                                        $rule_part[ 'background_color1' ], '0%', 
                                        $rule_part[ 'background_color2' ], '100%'
                                    ) );
                                endif;
                            else:
                                $value = $rule_part[ 'background_url' ];
                            endif;
                        elseif ( preg_match( '#^border(\-(top|right|bottom|left))?$#', $rule ) ):
                            if ( empty( $rule_part[ 'border_width' ] ) && !empty( $rule_part[ 'border_color' ] ) )
                                $rule_part[ 'border_width' ] = '1px';
                            if ( empty( $rule_part[ 'border_style' ] ) && !empty( $rule_part[ 'border_color' ] ) )
                                $rule_part[ 'border_style' ] = 'solid';
                            $value = implode( ' ', array(
                                $rule_part[ 'border_width' ], 
                                $rule_part[ 'border_style' ], 
                                $rule_part[ 'border_color' ]
                            ) );
                        else:
                            $value = '';
                        endif;
                        if ( $newselector && $newselector != $rule_arr[ 'selector' ] ):
                            // If this is a renamed selector, add new selector to array 
                            $newqsid = $this->update_arrays( 
                                'child',
                                $rule_arr[ 'query' ],
                                $newselector,
                                $rule,
                                trim( $value ),
                                $rule_part[ 'important' ],
                                $rulevalid
                            );  
                            // clear the original selector's child value:
                            $this->update_arrays( 
                                'child',
                                $rule_arr[ 'query' ],
                                $rule_arr[ 'selector' ],
                                $rule,
                                '',
                                0,
                                $rulevalid
                            );
                        else:
                            // otherwise, just update with the new values:
                            $this->update_arrays( 
                                'child', 
                                $rule_arr[ 'query' ],
                                $rule_arr[ 'selector' ], 
                                $rule,
                                trim( $value ),
                                $rule_part[ 'important' ],
                                $rulevalid
                            );
                        endif;
                    endforeach;
                endforeach;
            endforeach;
            // if this is a renamed selector, update sequence dict
            if ( $newqsid ):
                if ( !isset( $this->dict_seq[ $newqsid ] ) )
                    $this->dict_seq[ $newqsid ] = $this->dict_seq[ $qsid ];
            endif;
            // remove if all values have been cleared
            $this->prune_if_empty( $qsid );
            $qsid = $newqsid ? $newqsid : $qsid;
            // return updated qsid to browser to update form
            if ( $this->ctc()->cache_updates ):
                $this->ctc()->updates[] = array(
                    'obj'   => 'qsid',
                    'key'   => $qsid,
                    'data'  => $this->obj_to_utf8( $this->denorm_sel_val( $qsid ) ),
                );
            endif;
            do_action( 'chld_thm_cfg_update_qsid', $qsid );                
        endif;

        // update enqueue function if imports have not been converted or new imports passed
        if ( isset( $_POST[ 'ctc_child_imports' ] ) || empty( $this->converted ) )
            add_action( 'chld_thm_cfg_addl_files',   array( $this->ctc(), 'enqueue_parent_css' ), 15, 2 );
    }
    
    /**
     * parse_css_input
     * Normalize raw user CSS input so that the parser can read it.
     */
    function parse_css_input( $styles ) {
        return $this->repl_octal( stripslashes( $this->esc_octal( $styles ) ) );
    }
    
    // strips non printables and potential commands
    function sanitize( $styles ) {
        return sanitize_text_field( preg_replace( '/[^[:print:]]|\{.*/', '', $styles ) );
    }
    
    // escapes octal values in input to allow for specific ascii strings in content rule
    function esc_octal( $styles ){
        return preg_replace( "#(['\"])\\\\([0-9a-f]{4})(['\"])#i", "$1##bs##$2$3", $styles );
    }
    
    // unescapes octal values for writing specific ascii strings in content rule
    function repl_octal( $styles ) {
        return str_replace( "##bs##", "\\", $styles );
    }
    
    /**
     * parse_css_file
     * reads stylesheet to get WordPress meta data and passes rest to parse_css 
     */
    function parse_css_file( $template, $file = 'style.css', $cfgtemplate = FALSE ) {
        if ( '' == $file ) $file = 'style.css';
        // have we run out of memory?
        if ( $this->max_sel ):
            $this->ctc()->debug( 'Insufficient memory to parse file.', __FUNCTION__ );
            return FALSE;
        endif;
        // turn off caching when parsing files to reduce memory usage
        $this->ctc()->cache_updates = FALSE;
        $this->styles = ''; // reset styles
        $this->read_stylesheet( $template, $file );
        // get theme name
        $regex = '#Theme Name:\s*(.+?)\n#i';
        preg_match( $regex, $this->styles, $matches );
        $child_name = $this->get_prop( 'child_name' );
        if ( !empty( $matches[ 1 ] ) && 'child' == $template && empty( $child_name ) ) $this->set_prop( 'child_name', $matches[ 1 ] );
        $this->parse_css( 
            $cfgtemplate ? $cfgtemplate : $template, 
            NULL, 
            TRUE, 
            $this->ctc()->normalize_path( dirname( $file ) )
        );
    }

    // loads raw css file into local memory
    function read_stylesheet( $template = 'child', $file = 'style.css' ) {
        
        // these conditions support revert/restore option in 1.6.0+
        if ( 'all' == $file ) return;
        elseif ( '' == $file ) $file = 'style.css';
        // end revert/restore conditions
        
        $source = $this->get_prop( $template );
        if ( empty( $source ) || !is_scalar( $source ) ) return FALSE;
        $themedir = trailingslashit( get_theme_root() ) . $source;
        $stylesheet = apply_filters( 'chld_thm_cfg_' . $template, trailingslashit( $themedir ) 
            . $file , ( $this->ctc()->is_legacy() ? $this : $file ) ); // support for plugins extension < 2.0

        // read stylesheet
        
        if ( $stylesheet_verified = $this->is_file_ok( $stylesheet, 'read' ) ):
            // make sure we have space to parse
            if ( filesize( $stylesheet_verified ) * 3 > $this->ctc()->get_free_memory() ):
                $this->max_sel = 1;
                $this->ctc()->debug( 'Insufficient memory to read file', __FUNCTION__ );
                return;
            endif;
            $this->styles .= @file_get_contents( $stylesheet_verified ) . "\n";
            //echo 'count after get contents: ' . strlen( $this->styles ) . LF;
        else:
            //echo 'not ok!' . LF;
        endif;
    }

    /**
     * parse_css
     * Accepts raw CSS as text and parses into individual properties.
     * FIXME - this function has grown too monolithic - refactor and componentize
     */
    function parse_css( $template, $basequery = NULL, $parse_imports = TRUE, $relpath = '', $reset = FALSE ) {
        if ( FALSE === strpos( $basequery, '@' ) ):
            $basequery = 'base';
        endif;
        $ruleset = array();
        // ignore commented code
        $this->styles = preg_replace( '#\/\*.*?\*\/#s', '', $this->styles );
        // space braces to ensure correct matching
        $this->styles = preg_replace( '#([\{\}])\s*#', "$1\n", $this->styles );
        // get all imports
        if ( $parse_imports ):
            
            $regex = '#(\@import\s+url\(.+?\));#';
            preg_match_all( $regex, $this->styles, $matches );
            foreach ( preg_grep( '#' . $this->get_prop( 'parnt' ) . '\/style\.css#', $matches[ 1 ], PREG_GREP_INVERT ) as $import ):
                $import = preg_replace( "#^.*?url\(([^\)]+?)\).*#", "$1", $import );
                $import = preg_replace( "#[\'\"]#", '', $import );
                $import = '@import url(' . trim( $import ) . ')';
                $this->imports[ $template ][ $import ] = 1;
            endforeach;
            if ( $this->ctc()->cache_updates ):
                $this->ctc()->updates[] = array(
                    'obj'  => 'imports',
                    'data' => array_keys( $this->imports[ $template ] ),
                );
            endif;
        endif;
        // break into @ segments
        foreach ( array(
            '#(\@media[^\{]+?)\{(\s*?)\}#', // get any placehoder (empty) media queries
            '#(\@media[^\{]+?)\{(.*?\})?\s*?\}#s', // get all other media queries
        ) as $regex ): // (((?!\@media).) backreference too memory intensive - rolled back in v 1.4.8.1
            preg_match_all( $regex, $this->styles, $matches );
            foreach ( $matches[ 1 ] as $segment ):
                $segment = $this->normalize_query( $segment );
                $ruleset[ $segment ] = array_shift( $matches[ 2 ] ) 
                    . ( isset( $ruleset[ $segment ] ) ?
                        $ruleset[ $segment ] : '' );
            endforeach;
            // stripping rulesets leaves base styles
            $this->styles = preg_replace( $regex, '', $this->styles );
        endforeach;
        $ruleset[ $basequery ] = $this->styles;
        $qsid = NULL;
        foreach ( $ruleset as $query => $segment ):
            // make sure there is a newline before the first selector
            $segment = LF . $segment;
            // make sure there is semicolon before closing brace
            $segment = preg_replace( '#(\})#', ";$1", $segment );
            // parses selectors and corresponding rules
            $regex = '#\n\s*([\[\.\#\:\w][\w\-\s\(\)\[\]\'\^\*\.\#\+:,"=>]+?)\s*\{(.*?)\}#s';  //
            preg_match_all( $regex, $segment, $matches );
            foreach( $matches[ 1 ] as $sel ):
                $stuff  = array_shift( $matches[ 2 ] );
                $this->update_arrays(
                    $template,
                    $query,
                    $sel
                );
                // handle base64 data
                $stuff = preg_replace( '#data:([^;]+?);([^\)]+?)\)#s', "data:$1%%semi%%$2)", $stuff );
                // rule semaphore makes sure rules are only reset the first time they appear
                $resetrule = array(); 
                foreach ( explode( ';', $stuff ) as $ruleval ):
                    if ( FALSE === strpos( $ruleval, ':' ) ) continue;
                    list( $rule, $value ) = explode( ':', $ruleval, 2 );
                    $rule   = trim( $rule );
                    $rule   = preg_replace_callback( "/[^\w\-]/", array( $this, 'to_ascii' ), $rule );
                    // handle base64 data
                    $value  = trim( str_replace( '%%semi%%', ';', $value ) );
                    
                    $rules = $values = array();
                    // save important flag
                    $important = $this->is_important( $value );
                    // normalize color
                    $value = $this->normalize_color( $value );
                    // normalize font
                    if ( 'font' == $rule ):
                        $this->normalize_font( $value, $rules, $values );
                    // normalize background
                    elseif( 'background' == $rule ):
                        $this->normalize_background( $value, $rules, $values );
                    // normalize margin/padding
                    elseif ( 'margin' == $rule || 'padding' == $rule ):
                        $this->normalize_margin_padding( $rule, $value, $rules, $values );
                    else:
                        $rules[]    = $rule;
                        $values[]   = $value;
                    endif;
                    foreach ( $rules as $rule ):
                        $value = trim( array_shift( $values ) );
                        // normalize zero values
                        $value = preg_replace( '#\b0(px|r?em)#', '0', $value );
                        // normalize gradients
                        if ( FALSE !== strpos( $value, 'gradient' ) ):
                            if ( FALSE !== strpos( $rule, 'filter' ) ):
                                // treat as background-image, we'll add filter rule later
                                $rule = 'background-image';
                                continue; 
                            endif;
                            if ( FALSE !== strpos( $value, 'webkit-gradient' ) ) continue; // bail on legacy webkit, we'll add it later
                            $value = $this->encode_gradient( $value );
                        endif;
                        // normalize common vendor prefixes
                        $rule = preg_replace( '#(\-(o|ms|moz|webkit)\-)?(' . implode( '|', $this->vendorrule ) . ')#', "$3", $rule );
                        if ( 'parnt' == $template && 'background-image' == $rule && strstr( $value, 'url(' ) )
                            $value = $this->convert_rel_url( $value, $relpath );
                        // by default, set semaphore true to allow multiple values
                        if ( !$reset ) $resetrule[ $rule ] = TRUE; 
                        
                        $qsid = $this->update_arrays( 
                            $template, 
                            $query, 
                            $sel, 
                            $rule, 
                            $value, 
                            $important, 
                            NULL, // no rulevalid is passed when parsing from css (vs post input data)
                            empty( $resetrule[ $rule ] ) // if rule semaphore is TRUE, reset will be FALSE
                        );
                        $resetrule[ $rule ] = TRUE; // set rule semaphore so if same rule occurs again, it is not reset
                    endforeach;
                endforeach;
            endforeach;
        endforeach;
        // if this is a raw css update pass the last selector back to the browser to update the form
        if ( $this->ctc()->cache_updates && $qsid ):
            $this->ctc()->updates[] = array(
                'obj'   => 'qsid',
                'key'   => $qsid,
                'data'  => $this->obj_to_utf8( $this->denorm_sel_val( $qsid ) ),
            );
            do_action( 'chld_thm_cfg_update_qsid', $qsid );                
        endif;
    }

    // converts relative path to absolute path for preview
    function convert_rel_url( $value, $relpath, $url = TRUE  ) {
        $path       = preg_replace( '%url\([\'" ]*(.+?)[\'" ]*\)%', "$1", $value );
        if ( preg_match( '%(https?:)?//%', $path ) ) return $value;
        $pathparts  = explode( '/', $path );
        $fileparts  = explode( '/', $relpath );
        $newparts   = array();
        while ( $pathpart = array_shift( $pathparts ) ):
            if ( '..' == $pathpart )
                array_pop( $fileparts );
            else array_push( $newparts, sanitize_text_field( $pathpart ) );
        endwhile;
        $newvalue = ( $url ? 'url(' : '' )
            . ( $fileparts ? trailingslashit( implode( '/', $fileparts ) ) : '' ) 
            . implode( '/', $newparts ) . ( $url ? ')' : '' );
        $this->ctc()->debug( 'converted ' . $value . ' to ' . $newvalue . ' with ' . $relpath, __FUNCTION__ );
        return $newvalue;
    }
    
    /**
     * write_css
     * converts normalized CSS object data into stylesheet.
     * Preserves selector sequence and !important flags of parent stylesheet.
     * @media query blocks are sorted using internal heuristics (see sort_queries)
     * New selectors are appended to the end of each media query block.
     * FIXME - this function has grown too monolithic - refactor and componentize
     */
    function write_css( $backup = FALSE ) {
        // write new stylesheet
        $output = apply_filters( 'chld_thm_cfg_css_header', $this->get_css_header(), $this );
        // turn the dictionaries into indexes (value => id into id => value):
        $rulearr = array_flip( $this->dict_rule );
        $valarr  = array_flip( $this->dict_val );
        $selarr  = array_flip( $this->dict_sel );
        
        foreach ( $this->sort_queries() as $query => $sort_order ):
            $has_selector = 0;
            $sel_output   = '';
            $selectors = $this->sel_ndx[ $this->dict_query[ $query ] ];
            uasort( $selectors, array( $this, 'cmp_seq' ) );
            if ( 'base' != $query ) $sel_output .=  $query . ' {' . LF;
            foreach ( $selectors as $selid => $qsid ):
                if ( !empty( $this->val_ndx[ $qsid ] ) ):
                    $sel            = $selarr[ $selid ];
                    $shorthand      = array();
                    $rule_output    = array();
                    foreach ( $this->val_ndx[ $qsid ] as $ruleid => $temparr ):
                        // normalize values for backward compatability
                        $this->convert_ruleval_array( $temparr );
                        if ( isset( $temparr[ 'child' ] ) && 
                            ( !isset( $temparr[ 'parnt' ] ) || $temparr[ 'parnt' ] != $temparr[ 'child' ] ) ):
                            foreach ( $temparr[ 'child' ] as $rulevalarr ):
                                $this->add_vendor_rules( 
                                    $rule_output,
                                    $shorthand,
                                    $rulearr[ $ruleid ],
                                    $valarr[ $rulevalarr[ 0 ] ],
                                    $rulevalarr[ 1 ],
                                    $rulevalarr[ 2 ]
                                );
                            endforeach;
                        /**
                         * for testing
                        else:
                            foreach ( $temparr[ 'parnt' ] as $rulevalarr ):
                                $this->add_vendor_rules( 
                                    $rule_output,
                                    $shorthand,
                                    $rulearr[ $ruleid ],
                                    $valarr[ $rulevalarr[ 0 ] ],
                                    $rulevalarr[ 1 ],
                                    $rulevalarr[ 2 ]
                                );
                            endforeach;
                          */
                        endif;
                    endforeach;
                    /** FIXME ** need better way to sort rules and multiple values ***/
                    $this->encode_shorthand( $shorthand, $rule_output );
                    if ( count( $rule_output ) ):
                        // show load order -- removed in v.1.7.6 by popular demand
                        //$sel_output .= isset( $this->dict_seq[ $qsid ] )?'/*' . $this->dict_seq[ $qsid ] . '*/' . LF:''; 
                        $sel_output .= $sel . ' {' . LF . $this->stringify_rule_output( $rule_output ) . '}' . LF; 
                        $has_selector = 1;
                    endif;
                endif;
            endforeach;
            if ( 'base' != $query ) $sel_output .= '}' . LF;
            if ( $has_selector ) $output .= $sel_output;
        endforeach;
        $stylesheet = apply_filters( 'chld_thm_cfg_target', $this->get_child_target(), $this );
        //echo 'writing stylesheet: ' . $stylesheet . LF;
        //echo //print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true) . LF;
        if ( $stylesheet_verified = $this->is_file_ok( $stylesheet, 'write' ) ):
            global $wp_filesystem; // this was initialized earlier;
            // backup current stylesheet
            if ( $backup && is_file( $stylesheet_verified ) ):
                $timestamp  = date( 'YmdHis', current_time( 'timestamp' ) );
                $bakfile    = preg_replace( "/\.css$/", '', $stylesheet_verified ) . '-' . $timestamp . '.css';
                // don't write new stylesheet if backup fails
                if ( !$wp_filesystem->copy( 
                    $this->ctc()->fspath( $stylesheet_verified ), 
                    $this->ctc()->fspath( $bakfile ) ) ) return FALSE;
            endif;
            // write new stylesheet:
            // try direct write first, then wp_filesystem write
            // stylesheet must already exist and be writable by web server
            if ( $this->ctc()->is_ajax && is_writable( $stylesheet_verified ) ):
                if ( FALSE === @file_put_contents( $stylesheet_verified, $output ) ): 
                    $this->debug( 'Ajax write failed.', __FUNCTION__ );
                    return FALSE;
                endif;
            elseif ( FALSE === $wp_filesystem->put_contents( $this->ctc()->fspath( $stylesheet_verified ), $output ) ):
                $this->debug( 'Filesystem write failed.', __FUNCTION__ );
                return FALSE;
            endif;
            return TRUE;
        endif;   
        return FALSE;
    }
    
    function stringify_rule_output( &$rule_output ) {
        $output = '';
        asort( $rule_output );
        //print_r( $rule_output );
        foreach ( $rule_output as $rule => $sortstr )
            $output .= '    ' . $rule . ";\n";
        return $output;
    }
    
    function sortstr( $rule, $rulevalid ) {
        return substr( "0000" . $this->get_dict_id( 'rule', $rule ), -4) . substr( "00" . $rulevalid, -2 );
    }

    /**
     * encode_shorthand
     * converts CTC long syntax into CSS shorthand
     * v1.7.5 refactored for multiple values per property
     * FIXME - somehow condense all these foreach loops?
     */
    function encode_shorthand( $shorthand, &$rule_output ) {
        //if ( $shorthand ) print_r( $shorthand );
        foreach ( $shorthand as $property => $sides ):
            if ( isset( $sides[ 'top' ] ) ):
                foreach ( $sides[ 'top' ] as $tval => $tarr ):
                    if ( isset( $sides[ 'right' ] ) ):
                        $currseq = $tarr[ 1 ];
                        foreach ( $sides[ 'right' ] as $rval => $rarr ):
                            // value must exist from side and priority must match all sides
                            if ( isset( $sides[ 'bottom' ] ) && $tarr[ 0 ] == $rarr[ 0 ] ):
                                if ( $rarr[ 1 ] > $currseq ) $currseq = $rarr[ 1 ];
                                foreach ( $sides[ 'bottom' ] as $bval => $barr ):
                                    if ( isset( $sides[ 'left' ] ) && $tarr[ 0 ] == $barr[ 0 ] ):
                                        // use highest sort sequence of all sides
                                        if ( $barr[ 1 ] > $currseq ) $currseq = $barr[ 1 ];
                                        foreach ( $sides[ 'left' ] as $lval => $larr ):
                                            if ( $tarr[ 0 ] != $larr[ 0 ] ) continue;
                                            if ( $larr[ 1 ] > $currseq ) $currseq = $larr[ 1 ];

                                            $combo = array(
                                                $tval,
                                                $rval,
                                                $bval,
                                                $lval,
                                            );
                                            // echo 'combo before: ' . print_r( $combo, TRUE ) . LF;
                                            // remove from shorthand array
                                            unset( $shorthand[ $property ][ 'top' ][ $tval ] );
                                            unset( $shorthand[ $property ][ 'right' ][ $rval ] );
                                            unset( $shorthand[ $property ][ 'bottom' ][ $bval ] );
                                            unset( $shorthand[ $property ][ 'left' ][ $lval ] );
                                            
                                            // combine into shorthand syntax
                                            if ( $lval === $rval ):
                                                //echo 'left same as right, popping left' . LF;
                                                array_pop( $combo );
                                                if ( $bval === $tval ):
                                                    //echo 'bottom same as top, popping bottom' . LF;
                                                    array_pop( $combo );
                                                    if ( $rval === $tval ): // && $bval === $tval ):
                                                        //echo 'right same as top, popping right' . LF;
                                                        array_pop( $combo );
                                                    endif;
                                                endif;
                                            endif;
                                            //echo 'combo after: ' . print_r( $combo, TRUE ) . LF;
                                            // set rule
                                            $rule_output[ $property . ': ' . implode( ' ', $combo ) . ( $tarr[ 0 ] ? ' !important' : '' ) ] = $this->sortstr( $property, $currseq );
                                            // reset sort sequence
                                            $currseq = 0;
                                        endforeach;
                                    endif;
                                endforeach;
                            endif;
                        endforeach;
                    endif;
                endforeach;
            endif;
        endforeach;
        // add remaining rules
        foreach ( $shorthand as $property => $sides ):
            foreach ( $sides as $side => $values ):
                $rule = $property . '-' . $side;
                foreach ( $values as $val => $valarr ):
                    // set rule
                    $rule_output[ $rule . ': ' . $val . ( $valarr[ 0 ] ? ' !important' : '' ) ] = $this->sortstr( $rule, $valarr[ 1 ] );
                endforeach;
            endforeach;
        endforeach;
    }
    
    /**
     * add_vendor_rules
     * Applies vendor prefixes to rules/values and separates out shorthand properties .
     * These are based on commonly used practices and not all vendor prefixes are supported.
     * TODO: verify this logic against vendor and W3C documentation
     */
    function add_vendor_rules( &$rule_output, &$shorthand, $rule, $value, $important, $rulevalid ) {
        if ( '' === trim( $value ) ) return;
        if ( 'filter' == $rule && ( FALSE !== strpos( $value, 'progid:DXImageTransform.Microsoft.Gradient' ) ) ) return;
        $importantstr = $important ? ' !important' : '';
        if ( preg_match( "/^(margin|padding)\-(top|right|bottom|left)$/", $rule, $matches ) ):
            $shorthand[ $matches[ 1 ] ][ $matches[ 2 ] ][ $value ] = array(
                $important,
                $rulevalid,
                );
            return;
        elseif ( preg_match( '/^(' . implode( '|', $this->vendorrule ) . ')$/', $rule ) ):
            foreach( array( 'moz', 'webkit', 'o' ) as $prefix ):
                $rule_output[ '-' . $prefix . '-' . $rule . ': ' . $value . $importantstr ] = $this->sortstr( $rule, $rulevalid++ );
            endforeach;
            $rule_output[ $rule . ': ' . $value . $importantstr ] = $this->sortstr( $rule, $rulevalid );
        elseif ( 'background-image' == $rule ):
            // gradient?
            
            if ( $gradient = $this->decode_gradient( $value ) ):
                // standard gradient
                foreach( array( 'moz', 'webkit', 'o', 'ms' ) as $prefix ):
                    $rule_output[ 'background-image: -' . $prefix . '-' . 'linear-gradient(' . $gradient[ 'origin' ] . ', ' 
                        . $gradient[ 'color1' ] . ', ' . $gradient[ 'color2' ] . ')' . $importantstr ] = $this->sortstr( $rule, $rulevalid++ );
                endforeach;
                // W3C standard gradient
                // rotate origin 90 degrees
                if ( preg_match( '/(\d+)deg/', $gradient[ 'origin' ], $matches ) ):
                    $org = ( 90 - $matches[ 1 ] ) . 'deg';
                else: 
                    foreach ( preg_split( "/\s+/", $gradient[ 'origin' ] ) as $dir ):
                        $dir = strtolower( $dir );
                        $dirs[] = ( 'top' == $dir ? 'bottom' : 
                            ( 'bottom' == $dir ? 'top' : 
                                ( 'left' == $dir ? 'right' : 
                                    ( 'right' == $dir ? 'left' : $dir ) ) ) );
                    endforeach;
                    $org = 'to ' . implode( ' ', $dirs );
                endif;
                $rule_output[ 'background-image: linear-gradient(' . $org . ', ' 
                    . $gradient[ 'color1' ] . ', ' . $gradient[ 'color2' ] . ')' . $importantstr ] = $this->sortstr( $rule, $rulevalid );
                
                // legacy webkit gradient - we'll add if there is demand
                // '-webkit-gradient(linear,' .$origin . ', ' . $color1 . ', '. $color2 . ')';
                
                /** 
                 * MS filter gradient - DEPRECATED in v1.7.5
                 * $type = ( in_array( $gradient[ 'origin' ], array( 'left', 'right', '0deg', '180deg' ) ) ? 1 : 0 );
                 * $color1 = preg_replace( "/^#/", '#00', $gradient[ 'color1' ] );
                 * $rule_output[ 'filter: progid:DXImageTransform.Microsoft.Gradient(GradientType=' . $type . ', StartColorStr="' 
                 *    . strtoupper( $color1 ) . '", EndColorStr="' . strtoupper( $gradient[ 'color2' ] ) . '")' 
                 *    . $importantstr ] = $this->sortstr( $rule, $rulevalid );
                 */
            else:
                // url or other value
                $rule_output[ $rule . ': ' . $value . $importantstr ] = $this->sortstr( $rule, $rulevalid );
            endif;
        else:
            $rule = preg_replace_callback( "/\d+/", array( $this, 'from_ascii' ), $rule );
            $rule_output[ $rule . ': ' . $value . $importantstr ] = $this->sortstr( $rule, $rulevalid );
        endif;
    }

    /**
     * normalize_background
     * parses background shorthand value and returns
     * normalized rule/value pairs for each property
     */
    function normalize_background( $value, &$rules, &$values ) {
        if ( FALSE !== strpos( $value, 'gradient' ) ):
            // only supporting linear syntax
            if ( preg_match( '#(linear\-|Microsoft\.)#', $value ) ):
                $values[] = $value;
                $rules[] = 'background-image';
            else:
                // don't try to normalize non-linear gradients
                $values[] = $value;
                $rules[] = 'background';
            endif;
        else:
            $regexes = array(
                'image'         => 'url *\\([^)]+?\\)|none',
                'attachment'    => 'scroll|fixed|local',
                'clip'          => '(padding|border|content)\\-box',
                'repeat'        => '(no\\-)?repeat(\\-(x|y))?|round|space',
                'size'          => 'cover|contain|auto',
                'position'      => 'top|bottom|left|right|center|\b0 +0\b|(\b0 +)?[\\-\\d.]+(px|%)( +0\b)?',
                'color'         => '\\#[a-fA-F0-9]{3,6}|(hsl|rgb)a? *\\([^)]+?\\)|[a-z]+'                
            );
            //echo '<pre><code>' . "\n";
            //echo '<strong>' . $value . '</strong>' . "\n";
            foreach ( $regexes as $property => $regex ):
                $this->temparray = array();
                //echo $property . ': ' . $regex . "\n";
                $value = preg_replace_callback( "/(" . $regex . ")/", array( $this, 'background_callback' ), $value );
                if ( count( $this->temparray ) ):
                    $rules[] = 'background-' . $property;
                    $values[] = implode( ' ', $this->temparray );
                    //echo '<strong>result: ' . implode( ' ', $this->temparray ) . "</strong>\n";
                endif;
            endforeach;
            //echo '</code></pre>' . "\n";
        endif;
    }
    
    function background_callback( $matches ) {
        $this->temparray[] = $matches[ 1 ];
    }

    /**
     * normalize_font
     * parses font shorthand value and returns
     * normalized rule/value pairs for each property
     */
    function normalize_font( $value, &$rules, &$values ) {
        $regex = '#^((\d+|bold|normal) )?((italic|normal) )?(([\d\.]+(px|r?em|%))[\/ ])?(([\d\.]+(px|r?em|%)?) )?(.+)$#is';
        preg_match( $regex, $value, $parts );
        if ( !empty( $parts[ 2 ] ) ):
            $rules[]    = 'font-weight';
            $values[]   = $parts[ 2 ];
        endif;
        if ( !empty( $parts[ 4 ] ) ):
            $rules[]    = 'font-style';
            $values[]   = $parts[ 4 ];
        endif;      
        if ( !empty( $parts[ 6 ] ) ):
            $rules[]    = 'font-size';
            $values[]   = $parts[ 6 ];
        endif;
        if ( !empty( $parts[ 9 ] ) ):
            $rules[]    = 'line-height';
            $values[]   = $parts[ 9 ];
        endif;
        if ( !empty( $parts[ 11 ] ) ):
            $rules[]    = 'font-family';
            $values[]   = $parts[ 11 ];
        endif;
    }

    /**
     * normalize_margin_padding
     * parses margin or padding shorthand value and returns
     * normalized rule/value pairs for each property
     */
    function normalize_margin_padding( $rule, $value, &$rules, &$values ) {
        $parts = preg_split( "/ +/", trim( $value ) );
        if ( !isset( $parts[ 1 ] ) ) $parts[ 1 ] = $parts[ 0 ];
        if ( !isset( $parts[ 2 ] ) ) $parts[ 2 ] = $parts[ 0 ];
        if ( !isset( $parts[ 3 ] ) ) $parts[ 3 ] = $parts[ 1 ];
        $rules[ 0 ]   = $rule . '-top';
        $values[ 0 ]  = $parts[ 0 ];
        $rules[ 1 ]   = $rule . '-right';
        $values[ 1 ]  = $parts[ 1 ];
        $rules[ 2 ]   = $rule . '-bottom';
        $values[ 2 ]  = $parts[ 2 ];
        $rules[ 3 ]   = $rule . '-left';
        $values[ 3 ]  = $parts[ 3 ];
    }

    /**
     * encode_gradient
     * Normalize linear gradients from a bazillion formats into standard CTC syntax.
     * This has been refactored in v1.7.5 to accommodate new spectrum color picker color "names."
     * Currently only supports two-color linear gradients with no inner stops.
     * TODO: legacy webkit? more gradients? 
     */
    function encode_gradient( $value ) {
        // don't try this at home, kids
        $regex = '#gradient[^\)]*?\((((to )?(top|bottom|left|right)?( (top|bottom|left|right))?|\d+deg),)?([^\w\#\)]*[\'"]?(\#\w{3,8}|rgba?\([\d., ]+?\)|hsla?\([\d%., ]+?\)|[a-z]+)( [\d.]+%)?)([^\w\#\)]*[\'"]?(\#\w{3,8}|rgba?\([\d., ]+?\)|hsla?\([\d%., ]+?\)|[a-z]+)( [\d.]+%)?)([^\w\)]*gradienttype=[\'"]?(\d)[\'"]?)?[^\w\)]*\)#i';
        $param = $parts = array();
        preg_match( $regex, $value, $parts );
        if ( empty( $parts[ 14 ] ) ):
            if ( empty( $parts[ 2 ] ) ):
                $param[ 0 ] = 'top';
            elseif ( 'to ' == $parts[ 3 ] ):
            
                $param[ 0 ] = ( 'top' == $parts[ 4 ] ? 'bottom' :
                    ( 'left' == $parts[ 4 ] ? 'right' : 
                        ( 'right' == $parts[ 4 ] ? 'left' : 
                            'top' ) ) ) ;
            else: 
                $param[ 0 ] = trim( $parts[ 2 ] );
            endif;
            if ( empty( $parts[ 9 ] ) ):
                $param[ 2 ] = '0%';
            else:
                $param[ 2 ] = trim( $parts[ 9 ] );
            endif;
            if ( empty( $parts[ 12 ] ) ):
                $param[ 4 ] = '100%';
            else:
                $param[ 4 ] = trim( $parts[ 12 ] );
            endif;
        elseif( '0' == $parts[ 14 ] ):
            $param[ 0 ] = 'top';
            $param[ 2 ] = '0%';
            $param[ 4 ] = '100%';
        elseif ( '1' == $parts[ 14 ] ): 
            $param[ 0 ] = 'left';
            $param[ 2 ] = '0%';
            $param[ 4 ] = '100%';
        endif;
        if ( isset( $parts[ 8 ] ) && isset( $parts[ 11 ] ) ):
            $param[ 1 ] = $parts[ 8 ];
            $param[ 3 ] = $parts[ 11 ];
            ksort( $param );
            return implode( ':', $param );
        else:
            return $value;
        endif;
    }

    /**
     * decode_border
     * De-normalize CTC border syntax into individual properties.
     */
    function decode_border( $value ) {
        $parts = preg_split( '#\s+#', $value, 3 );
        if ( 1 == count( $parts ) ):
            $parts[ 0 ] = $value;
            $parts[ 1 ] = $parts[ 2 ] = '';
        endif;
        return array(
            'width' => empty( $parts[ 0 ] ) ? '' : $parts[ 0 ],
            'style' => empty( $parts[ 1 ] ) ? '' : $parts[ 1 ],
            'color' => empty( $parts[ 2 ] ) ? '' : $parts[ 2 ],
        );
    }

    /**
     * decode_gradient
     * Decode CTC gradient syntax into individual properties.
     */
    function decode_gradient( $value ) {
        $parts = explode( ':', $value, 5 );
        if ( !preg_match( '#(url|none)#i', $value ) && 5 == count( $parts ) ):        
            return array(
                'origin' => empty( $parts[ 0 ] ) ? '' : $parts[ 0 ],
                'color1' => empty( $parts[ 1 ] ) ? '' : $parts[ 1 ],
                'stop1'  => empty( $parts[ 2 ] ) ? '' : $parts[ 2 ],
                'color2' => empty( $parts[ 3 ] ) ? '' : $parts[ 3 ],
                'stop2'  => empty( $parts[ 4 ] ) ? '' : $parts[ 4 ],
            );
        endif;
        return FALSE;
    }

    /**
     * denorm_rule_val
     * Return array of unique values corresponding to specific rule
     * FIXME: only return child if no parent value exists
     */    
    function denorm_rule_val( $ruleid ) {
        $rule_sel_arr = array();
        $val_arr = array_flip( $this->dict_val );
        foreach ( $this->val_ndx as $qsid => $rules ):
            if ( !isset( $rules[ $ruleid ] ) ) continue;
            $this->convert_ruleval_array( $rules[ $ruleid ] );
            foreach ( array( 'parnt', 'child' ) as $template ):
                if ( isset( $rules[ $ruleid ][ $template ] ) ):
                    foreach ( $rules[ $ruleid ][ $template ] as $rulevalarr ):
                        $rule_sel_arr[ $rulevalarr[ 0 ] ] = $val_arr[ $rulevalarr[ 0 ] ];
                    endforeach;
                endif;
            endforeach;
        endforeach;
        return $rule_sel_arr;
    }

    /**
     * denorm_val_query
     * Return array of queries, selectors, rules, and values corresponding to
     * specific rule/value combo grouped by query, selector
     * FIXME: only return child values corresponding to specific rulevalid of matching parent value
     */    
    function denorm_val_query( $valid, $rule ) {
        $value_query_arr = array();
        if( $thisruleid = $this->get_dict_id( 'rule', $rule ) ):
        foreach ( $this->val_ndx as $qsid => $rules ):
            foreach ( $rules as $ruleid => $values ):
                if ( $ruleid != $thisruleid ) continue;
                $this->convert_ruleval_array( $values );
                foreach ( array( 'parnt', 'child' ) as $template ):
                    if ( isset( $values[ $template ] ) ):
                        foreach ( $values[ $template ] as $rulevalarr ):
                            if ( $rulevalarr[ 0 ] != $valid ) continue;
                            $selarr = $this->denorm_query_sel( $qsid );
                            $value_query_arr[ $rule ][ $selarr[ 'query' ] ][ $qsid ] = $this->denorm_sel_val( $qsid );
                        endforeach;
                    endif;
                endforeach;
            endforeach;
        endforeach;
        endif;
        return $value_query_arr;
    }

    /**
     * denorm_query_sel
     * Return id, query and selector values of a specific qsid (query-selector ID)
     */    
    function denorm_query_sel( $qsid ) {
        if ( !isset( $this->dict_qs[ $qsid ] ) ) return array();
        $queryarr                   = array_flip( $this->dict_query );
        $selarr                     = array_flip( $this->dict_sel );
        $this->dict_seq[ $qsid ]    = isset( $this->dict_seq[ $qsid ] ) ? $this->dict_seq[ $qsid ] : $qsid;
        return array(
            'id'        => $qsid,
            'query'     => $queryarr[ $this->dict_qs[ $qsid ][ 'q' ] ],
            'selector'  => $selarr[ $this->dict_qs[ $qsid ][ 's' ] ],
            'seq'       => $this->dict_seq[ $qsid ],
        );
    }

    /**
     * denorm_sel_val
     * Return array of rules, and values matching specific qsid (query-selector ID)
     * grouped by query, selector
     */    
    function denorm_sel_val( $qsid ) {
        $selarr = $this->denorm_query_sel( $qsid );
        $valarr = array_flip( $this->dict_val );
        $rulearr = array_flip( $this->dict_rule );
        if ( isset( $this->val_ndx[ $qsid ] ) && is_array( $this->val_ndx[ $qsid ] ) ):
            foreach ( $this->val_ndx[ $qsid ] as $ruleid => $values ):
                // convert old value to new format
                $this->convert_ruleval_array( $values );
                foreach ( array( 'parnt', 'child' ) as $template ):
                    if ( isset( $values[ $template ] ) ):
                        foreach ( $values[ $template ] as $rulevalarr ):
                            $selarr[ 'value' ][ $rulearr[ $ruleid ] ][ $template ][] = array(
                                $valarr[ $rulevalarr[ 0 ] ],
                                $rulevalarr[ 1 ],
                                isset( $rulevalarr[ 2 ] ) ? $rulevalarr[ 2 ] : 1,
                            );
                        endforeach;
                    endif;
                endforeach;
            endforeach;
        endif;
        return $selarr;
    }

    /**
     * convert and/or normalize rule/value index 
     * to support multiple values per property ( rule )
     * allows backward compatility with < v1.7.5
     */
    function convert_ruleval_array( &$arr ) {
        foreach ( array( 'parnt', 'child' ) as $template ):
            // skip if empty array
            if ( !isset( $arr[ $template ] ) ) continue;
            // check if using original data structure ( value is scalar )
            if ( ! is_array( $arr[ $template ] ) ):
                /**
                 * create new array to replace old scalar value
                 * value structure is
                 * [0] => value
                 * [1] => important
                 * [2] => priority
                 */
                $temparr = array( array( $arr[ $template ], $arr[ 'i_' . $template ], 0, 1 ) );
                $arr[ $template ] = $temparr;
            endif;
            $newarr = array();
            // iterate each value and enforce array structure
            foreach ( $arr[ $template ] as $rulevalid => $rulevalarr ):
                // skip if empty array
                if ( empty ( $rulevalarr ) ) continue;
                // 
                if ( ! is_array( $rulevalarr ) ):
                    // important flag moves to individual value array
                    $important = isset( $arr[ 'i_' . $template ] ) ? $arr[ 'i_' . $template ] : 0;
                    unset( $arr[ 'i_' . $template ] ); 
                    $val = (int) $rulevalarr;
                    $rulevalarr = array( $val, $important, $rulevalid );
                elseif ( !isset( $rulevalarr[ 2 ] ) ):
                    $rulevalarr[ 2 ] = $rulevalid;
                endif;
                $newarr[] = $rulevalarr;
            endforeach;
            $arr[ $template ] = $newarr;
        endforeach;
    }
    
    /**
     * denorm_sel_ndx
     * Return denormalized array containing query and selector heirarchy
     */    
    function denorm_sel_ndx( $query = NULL ) {
        $sel_ndx_norm = array();
        $queryarr = array_flip( $this->dict_query );
        $selarr = array_flip( $this->dict_sel );
        foreach( $this->sel_ndx as $queryid => $sel ):
            foreach( $sel as $selid => $qsid ):
                $sel_ndx_norm[ $queryarr[ $queryid ] ][ $selarr[ $selid ] ] = $qsid;
            endforeach;
        endforeach;
        return empty( $query ) ? array_keys( $sel_ndx_norm ) : $this->sort_selectors( $sel_ndx_norm[ $query ] );
    }
    
    /**
     * is_important
     * Strip important flag from value reference and return boolean
     * Updating two values at once
     */
    function is_important( &$value ) {
        $important = 0;
        $value = trim( str_ireplace( '!important', '', $value, $important ) );
        return $important;
    }
    
    /**
     * sort_queries
     * De-normalize query data and return array sorted as follows:
     * base
     * @media max-width queries in descending order
     * other @media queries in no particular order
     * @media min-width queries in ascending order
     */
    function sort_queries() {
        $queries = array();
        $queryarr = array_flip( $this->dict_query );
        foreach ( array_keys( $this->sel_ndx ) as $queryid ):
            $query = $queryarr[ $queryid ];
            if ( 'base' == $query ):
                $queries[ 'base' ] = -999999;
                continue;
            endif;
            if ( preg_match( "/((min|max)(\-device)?\-width)\s*:\s*(\d+)/", $query, $matches ) ):
                $queries[ $query ] = 'min-width' == $matches[ 1 ] ? $matches[ 4 ] : -$matches[ 4 ];
            else:
                $queries[ $query ] = $queryid - 10000;
            endif;
        endforeach;
        asort( $queries );
        return $queries;
    }
    
    function sort_selectors( $selarr ) {
        uksort( $selarr, array( $this, 'cmp_sel' ) );
        return $selarr;
    }
    
    function cmp_sel( $a, $b ) {
        $cmpa = preg_replace( "/\W/", '', $a );
        $cmpb = preg_replace( "/\W/", '', $b );
        if ( $cmpa == $cmpb ) return 0;
        return ( $cmpa < $cmpb ) ? -1 : 1;
    }
    
    // sort selectors based on dict_seq if exists, otherwise qsid
    function cmp_seq( $a, $b ) {
        $cmpa = isset( $this->dict_seq[ $a ] ) ? $this->dict_seq[ $a ] : $a;
        $cmpb = isset( $this->dict_seq[ $b ] ) ? $this->dict_seq[ $b ] : $b;
        if ( $cmpa == $cmpb ) return 0;
        return ( $cmpa < $cmpb ) ? -1 : 1;
    }

    /**
     * obj_to_utf8
     * sets object data to UTF8
     * flattens to array
     * and stringifies NULLs
     */
    function obj_to_utf8( $data ) {
        if ( is_object( $data ) )
            $data = get_object_vars( $data );
        if ( is_array( $data ) )
            return array_map( array( &$this, __FUNCTION__ ), $data );
        else
            return is_null( $data ) ? '' : utf8_encode( $data );
    }
    
    // convert ascii character into decimal value 
    function to_ascii( $matches ) {
        return ord( $matches[ 0 ] );
    }
    
    // convert decimal value into ascii character
    function from_ascii( $matches ) {
        return chr( $matches[ 0 ] );
    }
    
    /**
     * is_file_ok
     * verify file exists and is in valid location
     */
    function is_file_ok( $stylesheet, $permission = 'read' ) {
        // remove any ../ manipulations
        $stylesheet = $this->ctc()->normalize_path( preg_replace( "%\.\./%", '/', $stylesheet ) );
        $this->ctc()->debug( 'checking file: ' . $stylesheet, __FUNCTION__ );
        if ( 'read' == $permission && !is_file( $stylesheet ) ):
            $this->ctc()->debug( 'read: no file!', __FUNCTION__ );
            return FALSE;
        elseif ( 'write' == $permission && !is_dir( dirname( $stylesheet ) ) ):
            $this->ctc()->debug( 'write: no dir!', __FUNCTION__ );
            return FALSE;
        elseif ( 'search' == $permission && !is_dir( $stylesheet ) ):
            $this->ctc()->debug( 'search: no dir!', __FUNCTION__ );
            return FALSE;
        endif;
        // check if in themes dir;
        $regex = '%^' . preg_quote( $this->ctc()->normalize_path( get_theme_root() ) ) . '%';
        $this->ctc()->debug( 'theme regex: ' . $regex, __FUNCTION__ );
        if ( preg_match( $regex, $stylesheet ) ): 
            $this->ctc()->debug( $stylesheet . ' ok!', __FUNCTION__ );
            return $stylesheet;
        endif;
        // check if in plugins dir
        $regex = '%^' . preg_quote( $this->ctc()->normalize_path( WP_PLUGIN_DIR ) ) . '%';
        $this->ctc()->debug( 'plugin regex: ' . $regex, __FUNCTION__ );
        if ( preg_match( $regex, $stylesheet ) ):
            $this->ctc()->debug( $stylesheet . ' ok!', __FUNCTION__ );
            return $stylesheet;
        endif;
        $this->ctc()->debug( $stylesheet . ' is not in wp folders!', __FUNCTION__ );
        return FALSE;
    }
    
    /**
     * normalize_color
     * Sets hex string to lowercase and shortens to 3 char format if possible
     */
    function normalize_color( $value ) {
        $value = preg_replace_callback( "/#([0-9A-F]{3}([0-9A-F]{3})?)/i", array( $this, 'tolower' ), $value );
        $value = preg_replace( "/#([0-9A-F])\\1([0-9A-F])\\2([0-9A-F])\\3/i", "#$1$2$3", $value );
        return $value;
    }
    
    function normalize_query( $value ) {
        // space after :
        $value = str_replace( ':', ': ', trim( $value ) );
        // remove multiple whitespace
        $value = preg_replace( "/\s+/s", ' ', $value );
        // remove space after (
        $value = str_replace( '( ', '(', $value );
        // remove space before )
        $value = str_replace( ' )', ')', $value );
        return $value;
    }
    
    // callback for normalize_color regex
    function tolower( $matches ) {
        return '#' . strtolower( $matches[ 1 ] );
    }
}