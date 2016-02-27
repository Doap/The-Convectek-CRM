<?php
/*
 * Plugin Name: Custom Login Logo
 * Plugin URI: http://www.dreamsonline.net/wordpress-plugins/custom-login-logo/
 * Description: Helps customize WordPress for your clients by hiding non essential wp-admin components and by adding support for custom login logo and favicon for website and admin pages.
 * Version: 1.0.2
 * Author: Dreams Online Themes
 * Author URI: http://www.dreamsonline.net/wordpress-themes/
 * Author Email: hello@dreamsmedia.in
 *
 * @package WordPress
 * @subpackage DOT_CLL
 * @author Harish
 * @since 1.0
 *
 * License:

  Copyright 2013 "Custom Login Logo WordPress Plugin" (hello@dreamsmedia.in)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'DOT_CLL' ) ) {


	class DOT_CLL {

		/*--------------------------------------------*
		 * Constructor
		 *--------------------------------------------*/

		/**
		 * Initializes the plugin by setting localization, filters, and administration functions.
		 */
		function __construct() {

			// Load text domain
			add_action( 'init', array( $this, 'load_localisation' ), 0 );

			// Adding Plugin Menu
			add_action( 'admin_menu', array( &$this, 'dot_cll_menu' ) );

			 // Load our custom assets.
        	add_action( 'admin_enqueue_scripts', array( &$this, 'dot_cll_assets' ) );

			// Register Settings
			add_action( 'admin_init', array( &$this, 'dot_cll_settings' ) );


			// Change Login header URL
			add_filter( 'login_headerurl', array( &$this, 'dot_cll_login_headerurl' ) );

			// Change Login header Title
			add_filter( 'login_headertitle', array( &$this, 'dot_cll_login_headertitle' ) );

			// Change the default Login page Logo
			add_action( 'login_head', array( &$this, 'dot_cll_login_logo' ) );


		} // end constructor


		/*--------------------------------------------*
		 * Localisation | Public | 1.0 | Return : void
		 *--------------------------------------------*/

		public function load_localisation ()
		{
			load_plugin_textdomain( 'dot_cll', false, basename( dirname( __FILE__ ) ) . '/languages' );

		} // End load_localisation()

		/**
		 * Defines constants for the plugin.
		 */
		function constants() {
			define( 'DOT_CLL_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
		}

		/*--------------------------------------------*
		 * Admin Menu
		 *--------------------------------------------*/

		function dot_cll_menu()
		{
			$page_title = __('Custom Login Logo', 'dot_cll');
			$menu_title = __('Custom Login Logo', 'dot_cll');
			$capability = 'manage_options';
			$menu_slug = 'dot_cll';
			$function =  array( &$this, 'dot_cll_menu_contents');
			add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);

		}	//dot_cll_menu

		/*--------------------------------------------*
		 * Load Necessary JavaScript Files
		 *--------------------------------------------*/

		function dot_cll_assets() {
		    if (isset($_GET['page']) && $_GET['page'] == 'dot_cll') {

    			wp_enqueue_style( 'thickbox' ); // Stylesheet used by Thickbox
   				wp_enqueue_script( 'thickbox' );
    			wp_enqueue_script( 'media-upload' );

		        wp_register_script('dot_cll_admin', plugins_url( '/js/dot_cll_admin.js' , __FILE__ ), array( 'thickbox', 'media-upload' ));
		        wp_enqueue_script('dot_cll_admin');
		    }
		} //dot_cll_assets

		/*--------------------------------------------*
		 * Settings & Settings Page
		 *--------------------------------------------*/

		public function dot_cll_settings() {

			// Settings
			register_setting( 'dot_cll_settings', 'dot_cll_settings', array(&$this, 'settings_validate') );

			// Logo Settings
			add_settings_section( 'login_logo', __( 'Login Logo Settings', 'dot_cll' ), array( &$this, 'section_login_logo' ), 'dot_cll_settings' );

			add_settings_field( 'login_logo_url', __( 'Upload Login Logo', 'dot_cll' ), array( &$this, 'section_login_logo_url' ), 'dot_cll_settings', 'login_logo' );

			add_settings_field( 'login_logo_height', __( 'Set Logo Height', 'dot_cll' ), array( &$this, 'section_login_logo_height' ), 'dot_cll_settings', 'login_logo' );


		}	//dot_cll_settings


		/*--------------------------------------------*
		 * Settings & Settings Page
		 * dot_cll_menu_contents
		 *--------------------------------------------*/

		public function dot_cll_menu_contents() {
		?>
			<div class="wrap">
				<!--<div id="icon-freshdesk-32" class="icon32"><br></div>-->
				<div id="icon-options-general" class="icon32"><br></div>
				<h2><?php _e('Custom Login Logo Settings', 'dot_cll'); ?></h2>

				<form method="post" action="options.php">
					<?php //wp_nonce_field('update-options'); ?>
					<?php settings_fields('dot_cll_settings'); ?>
					<?php do_settings_sections('dot_cll_settings'); ?>
					<p class="submit">
						<input name="Submit" type="submit" class="button-primary" value="<?php _e('Save Changes', 'dot_cll'); ?>" />
					</p>
				</form>
			</div>

		<?php
		}	//dot_cll_menu_contents


		function section_login_logo() 	{


		}

		function section_login_logo_url() 	{
		    $options = get_option( 'dot_cll_settings' );
		    ?>
		    <span class='upload'>
		        <input type='text' id='dot_cll_settings[login_logo_url]' class='regular-text text-upload' name='dot_cll_settings[login_logo_url]' value='<?php echo esc_url( $options["login_logo_url"] ); ?>'/>
		        <input type='button' class='button button-upload' value='Upload an image'/></br>
		        <img style='max-width: 300px; display: block;' src='<?php echo esc_url( $options["login_logo_url"] ); ?>' class='preview-upload' />
		    </span>
		    <?php
		}

		function section_login_logo_height() 	{
		    $options = get_option( 'dot_cll_settings' );

		    ?>
		        <input type='text' id='dot_cll_settings[login_logo_height]' class='text' name='dot_cll_settings[login_logo_height]' value='<?php echo sanitize_text_field($options["login_logo_height"]); ?>'/> px
		    <?php
		}


		/*--------------------------------------------*
		 * Settings Validation
		 *--------------------------------------------*/

		function settings_validate($input) {

			return $input;
		}


		/*--------------------------------------------*
		 * Plugin Functions
		 *--------------------------------------------*/

		function dot_cll_login_logo() {

			$options = get_option( 'dot_cll_settings' );
			//if( !isset($options['login_logo_url']) ) $options['login_logo_url'] = '0';
			//if( !isset($options['login_logo_url_height']) ) $options['login_logo_url_height'] = 'auto';

			if( $options['login_logo_url'] != "" ) {
				echo '<style type="text/css">
	        	h1 a { background-image:url('.esc_url( $options["login_logo_url"] ).') !important; 	height:'.sanitize_text_field( $options["login_logo_height"] ).'px !important; background-size: auto auto !important; width: auto !important;}
	        		</style>';
	    	}


		}


		function dot_cll_login_headertitle( $title ) {
			return get_bloginfo( 'name' );
		}

		function dot_cll_login_headerurl( $url ) {
			return home_url();
		}

	} // End Class


	// Initiation call of plugin
	$dot_cll = new DOT_CLL(__FILE__);

}



?>