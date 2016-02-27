<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

    class ChildThemeConfiguratorPreview {
        /**
         * Replaces core function to start preview theme output buffer.
         */
        static function preview_theme() {
            // are we previewing?
            if ( ! isset( $_GET[ 'template' ] ) || !wp_verify_nonce( $_GET['preview_ctc'] ) )
                return;
            // can user preview?
            if ( !current_user_can( 'switch_themes' ) )
                return;
            // hide admin bar in preview
            if ( isset( $_GET[ 'preview_iframe' ] ) )
                show_admin_bar( false );
            // sanitize template param
            $_GET[ 'template' ] = preg_replace( '|[^a-z0-9_./-]|i', '', $_GET[ 'template' ] );
            // check for manipulations
            if ( validate_file( $_GET[ 'template' ] ) )
                return;
            // replace future get_template calls with preview template
            add_filter( 'template', 'ChildThemeConfiguratorPreview::preview_theme_template_filter' );
        
            if ( isset( $_GET[ 'stylesheet' ] ) ):
                // sanitize stylesheet param
                $_GET['stylesheet'] = preg_replace( '|[^a-z0-9_./-]|i', '', $_GET['stylesheet'] );
                // check for manipulations
                if ( validate_file( $_GET['stylesheet'] ) )
                    return;
                // replace future get_stylesheet calls with preview stylesheet
                add_filter( 'stylesheet', 'ChildThemeConfiguratorPreview::preview_theme_stylesheet_filter' );
            endif;
            // swap out theme mods with preview theme mods
            add_filter( 'pre_option_theme_mods_' . get_option( 'stylesheet' ), 
                'ChildThemeConfiguratorPreview::preview_mods' );
        }
        
        /**
         * Retrieves child theme mods for preview
         */        
        static function preview_mods() { 
            if ( ! isset( $_GET[ 'stylesheet' ] ) || get_option( 'stylesheet' ) == $_GET[ 'stylesheet' ] ) return false;
            return get_option( 'theme_mods_' . preg_replace('|[^a-z0-9_./-]|i', '', $_GET['stylesheet']) );
        }
        
        /**
         * Function to modify the current template when previewing a theme
         *
         * @return string
         */
        static function preview_theme_template_filter() {
            return ( isset($_GET['template']) && current_user_can( 'switch_themes' ) ) ? $_GET['template'] : '';
        }
        
        /**
         * Function to modify the current stylesheet when previewing a theme
         *
         * @return string
         */
        static function preview_theme_stylesheet_filter() {
            return ( isset( $_GET['stylesheet'] ) && current_user_can( 'switch_themes' ) ) ? $_GET['stylesheet'] : '';
        }
    }
    
    // replace core preview function with CTCP function for quick preview
    remove_action( 'setup_theme', 'preview_theme' );
    add_action( 'setup_theme', 'ChildThemeConfiguratorPreview::preview_theme' );

   
