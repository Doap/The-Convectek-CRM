<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
/*
    Class: Child_Theme_Configurator_UI
    Plugin URI: http://www.childthemeconfigurator.com/
    Description: Handles the plugin User Interface
    Version: 1.7.9.1
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2014-2015 Lilaea Media
*/
class ChildThemeConfiguratorUI {

    var $warnings = array();

    // helper function to globalize ctc object
    function ctc() {
        return ChildThemeConfigurator::ctc();
    }

    function render() {
        $css        = $this->ctc()->css;
        $themes     = $this->ctc()->themes;
        $child      = $css->get_prop( 'child' );
        $hidechild  = ( count( $themes[ 'child' ] ) ? '' : 'style="display:none"' );
        $enqueueset = ( isset( $css->enqueue ) && $child );
        $this->ctc()->debug( 'Enqueue set: ' . ( $enqueueset ? 'TRUE' : 'FALSE' ), __FUNCTION__ );
        if ( empty( $css->nowarn ) ) $this->parent_theme_check();
        $imports    = $css->get_prop( 'imports' );
        $id         = 0;
        $this->ctc()->fs_method = get_filesystem_method();
        add_thickbox();
        add_filter( 'chld_thm_cfg_files_tab_filter',    array( $this, 'render_files_tab_options' ) );
        add_action( 'chld_thm_cfg_tabs',                array( $this, 'render_addl_tabs' ), 10, 4 );
        add_action( 'chld_thm_cfg_panels',              array( $this, 'render_addl_panels' ), 10, 4 );
        add_action( 'chld_thm_cfg_related_links',       array( $this, 'lilaea_plug' ) );
        if ( $this->ctc()->is_debug ):
            $this->ctc()->debug( 'adding new debug action...', __FUNCTION__ );
            add_action( 'chld_thm_cfg_print_debug', array( $this->ctc(), 'print_debug' ) );
        endif;
        include ( CHLD_THM_CFG_DIR . '/includes/forms/main.php' ); 
    } 

    function parent_theme_check() {
        // check header for hard-coded 
        $bad_practice_descr = array(
            //  Stylesheets should be enqueued using the <code>wp_enqueue_scripts</code> action.
            'links'     => __( 'A stylesheet link tag is hard-coded into the header template.', 'child-theme-configurator' ),
            'enqueue'   => __( '<code>wp_enqueue_style()</code> called from the header template.', 'child-theme-configurator' ),
            //  <code>wp_head()</code> should be located just before the closing <code>&lt;/head&gt;</code> tag. 
            'wphead'    => __( 'Code exists between the <code>wp_head()</code> function and the closing <code>&lt;/head&gt;</code> tag.', 'child-theme-configurator'),
        );
        $parentfile = trailingslashit( get_theme_root() ) . trailingslashit( $this->ctc()->get_current_parent() ) . 'header.php';
        $childfile  = trailingslashit( get_theme_root() ) . trailingslashit( $this->ctc()->css->get_prop( 'child' ) ) . 'header.php';
        if ( $file = ( file_exists( $childfile ) ? $childfile : ( file_exists( $parentfile ) ? $parentfile : FALSE ) ) ):
            $contents = file_get_contents( $file );
            $contents = preg_replace( "/\/\/.*?(\?>|\n)|\/\*.*?\*\/|<\!\-\-.*?\-\->/s", '', $contents );
            // check for linked stylesheets
            if ( preg_match( '/rel=[\'"]stylesheet[\'"]/is', $contents ) ) $this->warnings[] = $bad_practice_descr[ 'links' ];
            if ( preg_match( '/wp_enqueue_style/is', $contents ) ) $this->warnings[] = $bad_practice_descr[ 'enqueue' ];
            // check for code after wp_head
            if ( preg_match( '/wp_head(.*?)<\/head>/is', $contents, $matches ) ):
                $codeafter = preg_replace( "/[\(\)\?>;\s]/s", '', $matches[ 1 ] );
                if ( !empty( $codeafter ) ) $this->warnings[] = $bad_practice_descr[ 'wphead' ];
            endif; 
        endif;
    }
   
    function render_theme_menu( $template = 'child', $selected = NULL ) {
        
         ?>
        <select class="ctc-select" id="ctc_theme_<?php echo $template; ?>" name="ctc_theme_<?php echo $template; ?>" style="visibility:hidden" <?php echo $this->ctc()->is_theme() ? '' : ' disabled '; ?> ><?php
            foreach ( $this->ctc()->themes[ $template ] as $slug => $theme )
                echo '<option value="' . $slug . '"' . ( $slug == $selected ? ' selected' : '' ) . '>' 
                    . esc_attr( $theme[ 'Name' ] ) . '</option>' . LF; 
        ?>
        </select>
        <div style="display:none">
        <?php 
        foreach ( $this->ctc()->themes[ $template ] as $slug => $theme )
            include ( CHLD_THM_CFG_DIR . '/includes/forms/themepreview.php' ); ?>
        </div>
        <?php
    }
        
    function render_file_form( $template = 'parnt' ) {
        global $wp_filesystem; 
        if ( $theme = $this->ctc()->css->get_prop( $template ) ):
            $themeroot  = trailingslashit( get_theme_root() ) . trailingslashit( $theme );
            $files      = $this->ctc()->get_files( $theme );
            $counter    = 0;
            sort( $files );
            ob_start();
            foreach ( $files as $file ):
                $templatefile = preg_replace( '%\.php$%', '', $file );
                $excludes = implode( "|", ( array ) apply_filters( 'chld_thm_cfg_template_excludes', $this->ctc()->excludes ) );
                if ( 'parnt' == $template && ( preg_match( '%^(' . $excludes . ' )\w*\/%',$templatefile ) 
                    || 'functions' == basename( $templatefile ) ) ) continue; 
                include ( CHLD_THM_CFG_DIR . '/includes/forms/file.php' );            
            endforeach;
            if ( 'child' == $template && ( $backups = $this->ctc()->get_files( $theme, 'backup,pluginbackup' ) ) ):
                foreach ( $backups as $backup => $label ):
                    $templatefile = preg_replace( '%\.css$%', '', $backup );
                    include ( CHLD_THM_CFG_DIR . '/includes/forms/backup.php' );            
                endforeach;
            endif;
            $inputs = ob_get_contents();
            ob_end_clean();
            if ( $counter ):
                include ( CHLD_THM_CFG_DIR . '/includes/forms/fileform.php' );            
            endif;
        endif;
    }
    
    function render_image_form() {
         
        if ( $theme = $this->ctc()->css->get_prop( 'child' ) ):
            $themeuri   = trailingslashit( get_theme_root_uri() ) . trailingslashit( $theme );
            $files = $this->ctc()->get_files( $theme, 'img' );
            
            $counter = 0;
            sort( $files );
            ob_start();
            foreach ( $files as $file ): 
                $templatefile = preg_replace( '/^images\//', '', $file );
                include( CHLD_THM_CFG_DIR . '/includes/forms/image.php' );             
            endforeach;
            $inputs = ob_get_contents();
            ob_end_clean();
            if ( $counter ) include( CHLD_THM_CFG_DIR . '/includes/forms/images.php' );
        endif;
    }
    
    function get_theme_screenshot() {
        
        foreach ( array_keys( $this->ctc()->imgmimes ) as $extreg ): 
            foreach ( explode( '|', $extreg ) as $ext ):
                if ( $screenshot = $this->ctc()->css->is_file_ok( $this->ctc()->css->get_child_target( 'screenshot.' . $ext ) ) ):
                    $screenshot = trailingslashit( get_theme_root_uri() ) . $this->ctc()->theme_basename( '', $screenshot );
                    return $screenshot . '?' . time();
                endif;
            endforeach; 
        endforeach;
        return FALSE;
    }
    
    function settings_errors() {
        
        if ( count( $this->ctc()->errors ) ):
            echo '<div class="error"><ul>' . LF;
            foreach ( $this->ctc()->errors as $err ):
                echo '<li>' . $err . '</li>' . LF;
            endforeach;
            echo '</ul></div>' . LF;
        elseif ( isset( $_GET[ 'updated' ] ) ):
            echo '<div class="updated">' . LF;
            if ( 8 == $_GET[ 'updated' ] ):
                echo '<p>' . __( 'Child Theme files modified successfully.', 'child-theme-configurator' ) . '</p>' . LF;
            else:
                $child_theme = wp_get_theme( $this->ctc()->css->get_prop( 'child' ) );
                echo '<p>' . apply_filters( 'chld_thm_cfg_update_msg', sprintf( __( 'Child Theme <strong>%s</strong> has been generated successfully.
                ', 'child-theme-configurator' ), $child_theme->Name ), $this->ctc() ) . LF;
                if ( $this->ctc()->is_theme() ):
                echo '<strong>' . __( 'IMPORTANT:', 'child-theme-configurator' ) . LF;
                if ( is_multisite() && !$child_theme->is_allowed() ): 
                    echo 'You must <a href="' . network_admin_url( '/themes.php' ) . '" title="' . __( 'Go to Themes', 'child-theme-configurator' ) . '" class="ctc-live-preview">' . __( 'Network enable', 'child-theme-configurator' ) . '</a> ' . __( 'your child theme.', 'child-theme-configurator' );
                else: 
                    echo '<a href="' . admin_url( '/customize.php?theme=' . $this->ctc()->css->get_prop( 'child' ) ) . '" title="' . __( 'Live Preview', 'child-theme-configurator' ) . '" class="ctc-live-preview">' . __( 'Test your child theme', 'child-theme-configurator' ) . '</a> ' . __( 'before activating.', 'child-theme-configurator' );
                endif;
                echo '</strong></p>' . LF;
                endif;
             endif;
            echo '</div>' . LF;
        endif;
    }
    
    function render_help_content() {
	    global $wp_version;
	    if ( version_compare( $wp_version, '3.3' ) >= 0 ) {
	
		    $screen = get_current_screen();
                
            // load help content via output buffer so we can use plain html for updates
            // then use regex to parse for help tab parameter values
            
            $regex_sidebar = '/' . preg_quote( '<!-- BEGIN sidebar -->' ) . '(.*?)' . preg_quote( '<!-- END sidebar -->' ) . '/s';
            $regex_tab = '/' . preg_quote( '<!-- BEGIN tab -->' ) . '\s*<h\d id="(.*?)">(.*?)<\/h\d>(.*?)' . preg_quote( '<!-- END tab -->' ) . '/s';
            $locale = get_locale();
            $dir = CHLD_THM_CFG_DIR . '/includes/help/';
            $file = $dir . $locale . '.php';
            if ( !is_readable( $file ) ) $file = $dir . 'en_US.php';
            ob_start();
            include( $file );
            $help_raw = ob_get_contents();
            ob_end_clean();
            // parse raw html for tokens
            preg_match( $regex_sidebar, $help_raw, $sidebar );
            preg_match_all( $regex_tab, $help_raw, $tabs );

    		// Add help tabs
            if ( isset( $tabs[ 1 ] ) ):
                //$priority = 0;
                while( count( $tabs[ 1 ] ) ):
                    $id         = array_shift( $tabs[ 1 ] );
                    $title      = array_shift( $tabs[ 2 ] );
                    $content    = array_shift( $tabs[ 3 ] );
	    	        $screen->add_help_tab( array(
	    	    	    'id'        => $id,
    		    	    'title'     => $title,
	    		        'content'   => $content, 
                        //'priority'  => ++$priority,
                    ) );
                endwhile;
            endif;
            if ( isset( $sidebar[ 1 ] ) )
                $screen->set_help_sidebar( $sidebar[ 1 ] );

        }
    }
    
    function render_addl_tabs( $ctc, $active_tab = NULL, $hidechild = '' ) {
        include ( CHLD_THM_CFG_DIR . '/includes/forms/addl_tabs.php' );            
    }

    function render_addl_panels( $ctc, $active_tab = NULL, $hidechild = '' ) {
        include ( CHLD_THM_CFG_DIR . '/includes/forms/addl_panels.php' );            
    }

    function lilaea_plug() {
        include ( CHLD_THM_CFG_DIR . '/includes/forms/related.php' );
    }
    
    function render_files_tab_options( $output ) {
        $regex = '%<div class="ctc\-input\-cell clear">.*?(</form>).*%s';
        $output = preg_replace( $regex, "$1", $output );
        return $output;
    }
}
?>
