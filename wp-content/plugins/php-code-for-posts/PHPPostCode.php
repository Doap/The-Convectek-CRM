<?php
/*
** Plugin Name: PHP Code for posts
** Description: Insert and execute PHP code in WordPress content. This plugin also enabled shortcodes for text widgets.
** Version: 1.3.0
** Author: The Missing Code
*/

//Global variable for Class, useful for accessing class functions as well as a global variable store
global $PHPPC;
$PHPPC = "";

//Ensure the class only registers once to prevent a fatal error
if( ! class_exists("PHPPC") ){

	class PHPPC{

		static $plugin_table_name = "phppc_functions";
		static $plugin_table_version = "1";
		static $plugin_option_name = "phppc_options";
		static $plugin_menu_slug = "phppc_menu";
		static $plugin_post = "Fji93FE0pf45";
		static $plugin_shortcode = "php";
		var $_vars = array();

		/**
		* Class Constructor, registers filters, actions and shortcodes
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		*/
		function __construct(){
			add_action( "admin_menu" , array( __CLASS__ , "register_admin_menu" ) );
			add_shortcode( self::$plugin_shortcode, array(__CLASS__, "handle_shortcode" ) );
			add_action( 'wp_ajax_phppc_code', array(__CLASS__, "ajax_callback") );
			$option = self::get_option();
			add_filter( "widget_text", "do_shortcode", 5, 1);
			if( $option["content_filter"] == "1"){
				add_filter( "the_content", array(__CLASS__, "handle_extra_shortcode") , 0 ,1 );
			}
			if( $option["sidebar_filter"] == "1"){
				add_filter( "widget_text", array(__CLASS__, "handle_extra_shortcode") ,0 ,1 );
			}
		}

		/**
		* Process plugin additional shortcode locations (inline tags and sidebars)
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		* @param string $content String containing shortcode / PHP tags
		* @return string eval'ed PHP result
		*/
		static function handle_extra_shortcode( $content ){
			$content = str_ireplace( "[php]", "<?php ", str_ireplace("[/php]", " ?>", $content) );
			ob_start();
			eval("?>".$content);
			return ob_get_clean();
		}

		/**
		* Process the plugin's shortcode when it is called and return the results
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		* @param array $atts shortcode attributes passed
		* @return string eval'ed PHP result
		*/
		function handle_shortcode( $atts ){
			$snippet = "";
			$default_args = array("snippet" => 0, "param" => "");
			extract( shortcode_atts( $default_args, $atts));
			if( !empty($snippet) ){
				$snippet = self::get_single_snippet( $snippet );
				if( sizeof( $snippet )){
					$snippetPrefix = '?>';
					if ($param != ""){
						$snippetPrefix = '$_parameters = array(); parse_str(htmlspecialchars_decode("'.$param.'"), $_parameters);' . $snippetPrefix;
						print_r( parse_str($param));
					}
					ob_start();
					eval( $snippetPrefix.$snippet->code );
					return ob_get_clean();
				}
			}
		}

		/**
		* Read the plugin's options from the database, showing default options if none found
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		* @return array plugin's options
		*/
		function get_option(){
			$default = array(
				"table_version" => "0",
				"complete_deactivation" => "0",
				"content_filter" => "1",
				"sidebar_filter" => "1",
				"enable_richeditor" => "1",
			);
			$option = get_option( self::$plugin_option_name, $default );
			return $option;
		}

		/**
		* Update the plugin options in the database
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		* @param array $new_value array of new options values to add to the plugin options
		* @return bool if the option has been updated
		*/
		function update_option( $new_value ){
			return update_option( self::$plugin_option_name, $new_value );
		}

		/**
		* Action called when the plugin is activated though the admin plugin screen.
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		*/
		function activation_hook(){
			$options = self::get_option();
			self::check_table( $options["table_version"] );
		}

		/**
		* Action called when the plugin is deleted though the admin plugin screen.
		*
		* @global wpdb $wpdb
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		*/
		function uninstall_hook(){
			$options = self::get_option();
			if( $options["complete_deactivation"] == "1"){
				global $wpdb;
				delete_option( self::$plugin_option_name );
				$wpdb->get_results("DROP TABLE `".$wpdb->prefix.self::$plugin_table_name."`");
			}
		}

		/**
		* Check the plugin's db version against the installed option version
		* If difference, call the upgrade function
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		* @param string $table_version table version for comparason with class plugin version
		*/
		function check_table( $table_version ){
			if( $table_version !== self::$plugin_table_version ){
				self::upgrade_table();
			}
		}

		/**
		* Update the plugin table if required
		*
		* @global wpdb $wpdb
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		*/
		function upgrade_table(){
			global $wpdb;
			$table = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix.self::$plugin_table_name."(
				id int NOT NULL AUTO_INCREMENT,
				name varchar(256) NOT NULL DEFAULT 'Untitled Function',
				description text,
				code longtext NOT NULL,
				PRIMARY KEY(id)
			);";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $table );
			self::complete_table_upgrade();
		}

		/**
		* Once the table has been updated, change the table version number in plugin option
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		* @return bool if the upgrade has been done successfully
		*/
		function complete_table_upgrade(){
			$option = self::get_option();
			$option["table_version"] = self::$plugin_table_version;
			return self::update_option( $option );
		}

		/**
		* Adds the administation style sheet to the document header for this page
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		*/
		function register_stylesheet(){
			$options = self::get_option();
			wp_register_style( 'phppcstyles', plugins_url( 'style.css', __FILE__ ) );
			wp_enqueue_style( 'phppcstyles' );
			if ($options['enable_richeditor'] == 1) {
				wp_register_style( 'phppcCodemirror', plugins_url( 'Codemirror/lib/codemirror.css', __FILE__ ) );
				wp_enqueue_style( 'phppcCodemirror' );
			}
		}

		/**
		* Adds additional scritps to the document header for this page
		* @since 1.2.0
		* @author The Missing code
		* @package WordPress
		**/
		function register_script() {
			$options = self::get_option();
			wp_register_script( 'phppcscript', plugins_url( 'PHPPostCode.js', __FILE__ ), array('jquery') );
			wp_enqueue_script( 'phppcscript' );
			if ($options['enable_richeditor'] == 1) {
				wp_register_script( 'Codemirror_lang_clike', plugins_url( 'Codemirror/lang/clike.js', __FILE__ ) );
				wp_register_script( 'Codemirror_lang_css', plugins_url( 'Codemirror/lang/css.js', __FILE__ ) );
				wp_register_script( 'Codemirror_lang_htmlmixed', plugins_url( 'Codemirror/lang/htmlmixed.js', __FILE__ ) );
				wp_register_script( 'Codemirror_lang_javascript', plugins_url( 'Codemirror/lang/javascript.js', __FILE__ ) );
				wp_register_script( 'Codemirror_lang_php', plugins_url( 'Codemirror/lang/php.js', __FILE__ ) );
				wp_register_script( 'Codemirror_lang_xml', plugins_url( 'Codemirror/lang/xml.js', __FILE__ ) );
				wp_register_script( 'Codemirror_addon_matchbrackets', plugins_url( 'Codemirror/addon/matchbrackets.js', __FILE__ ) );
				wp_register_script( 'Codemirror', plugins_url( 'Codemirror/lib/codemirror.js', __FILE__ ) );
				wp_enqueue_script( 'Codemirror' );
				wp_enqueue_script( 'Codemirror_lang_clike' );
				wp_enqueue_script( 'Codemirror_lang_css' );
				wp_enqueue_script( 'Codemirror_lang_htmlmixed' );
				wp_enqueue_script( 'Codemirror_lang_javascript' );
				wp_enqueue_script( 'Codemirror_lang_php' );
				wp_enqueue_script( 'Codemirror_lang_xml' );
				wp_enqueue_script( 'Codemirror_addon_matchbrackets' );
			}
		}

		/**
		* Registers the administation menu in the WordPress system
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		*/
		function register_admin_menu(){
			$page = add_menu_page(
				"PHP Code for Posts and Pages",
				"PHP Code",
				"manage_options",
				self::$plugin_menu_slug,
				array( __CLASS__, "do_admin_menu" ),
				plugin_dir_url( __FILE__ ) . "/php-icon.png",
				null
			);
			add_action( 'admin_print_styles-' . $page, array( __CLASS__, "register_stylesheet") );
			add_action( 'admin_print_scripts-' . $page, array( __CLASS__, "register_script") );
		}

		/**
		* Output the basic administation menu
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		*/
		function do_admin_menu(){
			self::check_plugin_table_exists();
			?>
			 <div class='wrap'>
				<h2>PHP Code for Posts and Pages</h2>
				<?php
				$action = ( isset( $_GET["action"] ) ) ? $_GET["action"] : "";
				$item = ( isset( $_GET["item"] ) ) ? $_GET["item"] : "";
				$actioncode = ( isset( $_GET["actioncode"] ) ) ? $_GET["actioncode"] : "";
				self::check_post_vars();

				?>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" class="donate">
					<h3>Support The Plugin</h3>
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="SFQZ3KDJ4LQBA">
					<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal â€“ The safer, easier way to pay online.">
					<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
				</form>

				<?php
				if( $action == "add" ){
					 self::snippet_new_form();
				}
				elseif( $action == "edit" && wp_create_nonce( $item.$action ) == $actioncode ){
					 self::snippet_edit_form( $item );

				}
				else{
					 self::do_admin_menu_main();
				}
			?>
			</div>
			<?php
		}

		/**
		* Check the content of the GET and POST variables and subsequently choses an action to perform
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		*/
		function check_post_vars(){
			if( isset( $_REQUEST[self::$plugin_post] ) ){
				$post = $_REQUEST[self::$plugin_post];
				if( isset( $post["action"] ) && isset( $post["actioncode"] ) ){
					if( wp_verify_nonce( $post["actioncode"], $post["item"].$post["action"] ) ){
						switch ( $post["action"] ):
							case "add":
								self::add_new_snippet( $post );
								break;
							case "delete":
								self::delete_single_snippet( $post );
								break;
							case "bulkdelete":
								self::bulk_delete_snippets( $post );
								break;
							case "update":
								self::update_snippet( $post );
								break;
							case "updateoptions":
								self::update_plugin_option( $post );
								break;
						endswitch;
					}
				}
			}
		}

		/**
		* Update a single snippet in the database
		*
		* @global wpdb $wpdb
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		* @param array $postvs an array of post variables containing new snippet name, description code and id.
		*/
		function update_snippet( $postvs, $ajax = false ){
			global $wpdb;
			$update =  $wpdb->update(
				$wpdb->prefix.self::$plugin_table_name,
				array(
					"name" => $postvs["name"],
					"description" => $postvs["description"],
					"code" => base64_encode( stripslashes( $postvs["code"] ) )
				),
				array(
					"id"=>$postvs["item"]
				),
				array(
					"%s",
					"%s",
					"%s"
				),
				array("%d")
			);

			if ($ajax) {
				return $update;
			}

			if( $update ){
				echo '<div class="updated settings-error" id="setting-error-settings_updated">';
				echo '<p><strong>Code Snippet <em>'.$postvs["name"].'(id:'.$postvs["item"].')</em> has been updated</strong></p>';
				echo '</div>';
			}
			else {
				echo '<div class="error settings-error" id="setting-error-settings_updated">';
				echo '<p><strong>The update operation has failed :( - It could be that there is nothing to update?</strong></p>';
				echo '</div>';
		   }
		}

		/**
		* Deletes multiple snippets from the database
		*
		* @global wpdb $wpdb
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		* @param array $postvs an array of post variables including the key "delete" which contains an array of ids to remove
		*/
		function bulk_delete_snippets( $postvs ){
			global $wpdb;
			if( isset( $postvs["delete"] ) ){
				$count = sizeof( $postvs["delete"] );
				$snippets = implode( ",", array_keys( $postvs["delete"] ) );
				if( preg_match( "/^([0-9, ])*$/", $snippets ) ){
					$sql = "DELETE FROM `".$wpdb->prefix.self::$plugin_table_name."` WHERE `id` IN (".esc_sql($snippets).")";
					$wpdb->get_results($sql);
					echo '<div class="updated settings-error" id="setting-error-settings_updated"><p><strong>'.$count.'</strong> Snippets have been deleted</p></div>';
				}
				else{
					echo '<div class="error settings-error" id="setting-error-settings_updated"><p><strong>Could not delete snippets, please try again.</strong></p></div>';
				}
			}
			else{
				echo '<div class="error settings-error" id="setting-error-settings_updated"><p><strong>Are You Mad?!?! Nothing Selected!!!. Go back and select some snippets to delete before clicking again!</strong></p></div>';
			}
		}

		/**
		* Deletes a single snippet from the database
		*
		* @global wpdb $wpdb
		* @since 1.1
		* @author The Missing Code
		* @package WordPress
		* @param array $postvs an array of post variables including the key "delete" which contains an the id to remove
		*/
		function delete_single_snippet( $postvs ){
			global $wpdb;
			if( isset( $postvs["item"] ) ){
				$sql = "DELETE FROM `".$wpdb->prefix.self::$plugin_table_name."` WHERE `id` =".esc_sql($postvs["item"]);
				$wpdb->get_results($sql);
				echo '<div class="updated settings-error" id="setting-error-settings_updated"><p>The snippet has been deleted</p></div>';
			}
			else{
				echo '<div class="error settings-error" id="setting-error-settings_updated"><p><strong>Are You Mad?!?! Nothing Selected!!!. Go back and select some snippets to delete before clicking again!</strong></p></div>';
			}
		}

		/**
		* Add new code snippet into the database
		*
		* @global wpdb $wpdb
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		* @param array $postvs an array of post variables from the new snippet form, with details of what to add to the DB
		*/
		function add_new_snippet( $postvs ){
			global $wpdb;
			$insertArray = array();
			if( isset( $postvs["name"] )){
				$insertArray["name"] = $postvs["name"];
			}
			if( isset( $postvs["description"] ) ){
				$insertArray["description"] = $postvs["description"];
			}
			if( isset( $postvs["code"] ) ){
				if($postvs["code"] != ""){
					$insertArray["code"] = base64_encode( stripslashes( $postvs["code"] ) );
					if ( $wpdb->insert( $wpdb->prefix . self::$plugin_table_name, $insertArray, "%s" ) ){
						echo '<div class="updated settings-error" id="setting-error-settings_updated"><p><strong>The new code snippet has been saved.</strong> Use the shortcode <code>['.self::$plugin_shortcode.' snippet='.$wpdb->insert_id.']</code> to insert this snippet into a post or page.</p></div>';
					}
				}
				else{
					echo '<div class="error settings-error" id="setting-error-settings_updated"><p><strong>There was an error in adding the snippet, try again. :)</strong></p></div>';
				}
			}
		}

		/**
		* Form to add a single snippet to the system
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		*/
		function snippet_new_form(){
			$option = self::get_option();
			?>
			<h3>Add some new code</h3>
			<form action="?page=<?php echo self::$plugin_menu_slug ?>" method='post'>
				<input type='hidden' name='<?php echo self::$plugin_post;?>[action]' value='add' />
				<input type='hidden' name='<?php echo self::$plugin_post;?>[item]' value='' />
				<?php wp_nonce_field( "add", self::$plugin_post."[actioncode]" )?>
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="<?php echo self::$plugin_post;?>[name]">Give your code a name</label></th>
							<td><input type="text" maxlength="256" name="<?php echo self::$plugin_post;?>[name]" id="name" class="widefat" placeholder="Your code snippet's name" /></td>
						</tr>
						<tr>
							<th><label for="<?php echo self::$plugin_post;?>[description]">Write a short description<br />about your code</label></th>
							<td><textarea name="<?php echo self::$plugin_post;?>[description]" id="description" class="widefat" placeholder="Your code description"></textarea></td>
						</tr>
						<tr>
							<th><label for="<?php echo self::$plugin_post;?>[code]">Now add your code</label><br /><em>Remember to open your PHP code with <code>&lt;?php</code></em></th>
							<td><textarea name="<?php echo self::$plugin_post;?>[code]" id="code" class="widefat field-code" rows="10" placeholder="Your code in here" required="required">&lt;?php
//your code here!
?&gt;</textarea>
							<?php if ($option['enable_richeditor'] == 1) { echo '<em class="codemirrorfootnote">Code Highlighting By <a href="http://codemirror.net/" target="_blank">Codemirror</a></em>';}?></td>
						</tr>
						<tr>
							<td colspan="2" align="center"><input type="submit" class="button-primary" value="Save this code" /> <a href="?page=<?php echo self::$plugin_menu_slug; ?>" class='button-secondary'>Go Back</a></td>
						</tr>
					</tbody>
				</table>
			</form>
			<?php

			if ($option['enable_richeditor'] == 1) {
				?>
				<script>
			      var editor = CodeMirror.fromTextArea(document.getElementById('code'), {
			        lineNumbers: true,
			        matchBrackets: true,
			        mode: "application/x-httpd-php",
			        indentUnit: 4,
			        indentWithTabs: true,
			        enterMode: "keep",
			        tabMode: "shift"
			      });
			    </script>
				<?php
			}
		}

		/**
		* Read a single code snippet from the database
		*
		* @global wpdb $wpdb
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		* @param int $id the id of the code snippet to read from the database
		* @return array snippet details
		*/
		function get_single_snippet( $id = 0 ){
			global $wpdb;
			$snippet = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `".$wpdb->prefix.self::$plugin_table_name."` WHERE `id` = %d", $id ) );
			if(sizeof($snippet) >0 )
				$snippet->code = base64_decode($snippet->code);
			return $snippet;
		}

		/**
		* Update the main plugin options inside the database
		*
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		* @param array $postvs an array of $_POST variables with options
		*/
		function update_plugin_option( $postvs = array() ){
			$opt = self::get_option();
			$opt["complete_deactivation"]= isset( $postvs["complete_deactivation"] ) ? "1" : "0";
			$opt["content_filter"]= isset( $postvs["content_filter"] ) ? "1" : "0";
			$opt["sidebar_filter"]= isset( $postvs["sidebar_filter"] ) ? "1" : "0";
			$opt["enable_richeditor"] = isset( $postvs["enable_richeditor"] ) ? "1" : "0";
			if( self::update_option( $opt ) ){
				echo '<div class="updated settings-error" id="setting-error-settings_updated">';
				echo '<p><strong>Plugin Options Updated</strong></p>';
				echo '</div>';
			} else {
				echo '<div class="error settings-error" id="setting-error-settings_updated">';
				echo '<p><strong>Plugin Option Update Failed - It could be that there is nothing to update?</strong></p>';
				echo '</div>';
			}
		}

		/**
		* Displays the form to edit a single code snippet in the system
		*
		* @global wpdb $wpdb
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		* @param int $sid the snippet id to edit
		*/
		function snippet_edit_form( $sid = 0 ){
			$snippet = self::get_single_snippet( $sid );
			$option = self::get_option();
			?>
			<h3>Edit Existing Code</h3>
			<form action="?page=<?php echo self::$plugin_menu_slug ?>" method='post' id='codeform'>
				<input type='hidden' name='<?php echo self::$plugin_post;?>[action]' value='update' />
				<input type='hidden' name='<?php echo self::$plugin_post;?>[item]' value='<?php echo esc_attr($sid);?>' />
				<?php wp_nonce_field( $sid."update", self::$plugin_post."[actioncode]" )?>
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="<?php echo self::$plugin_post;?>[name]">Change your code's name</label></th>
							<td><input type="text" maxlength="256" name="<?php echo self::$plugin_post;?>[name]" id="name" class="widefat" placeholder="Your code snippet's name" value="<?php echo esc_attr($snippet->name)?>" /></td>
						</tr>
						<tr>
							<th><label for="<?php echo self::$plugin_post;?>[description]">Change the short description<br />about your code</label></th>
							<td><textarea name="<?php echo self::$plugin_post;?>[description]" id="description" class="widefat" placeholder="Your code description"><?php echo esc_textarea($snippet->description)?></textarea></td>
						</tr>
						<tr>
							<th><label for="<?php echo self::$plugin_post;?>[code]">Change the actual code</label><br /><em>Remember to open your PHP code with <code>&lt;?php</code></em></th>
							<td><textarea name="<?php echo self::$plugin_post;?>[code]" id="code" class="widefat field-code" rows="10" placeholder="Your code in here" required="required"><?php echo esc_attr($snippet->code)?></textarea>
								<?php if ($option['enable_richeditor'] == 1) { echo '<em class="codemirrorfootnote">Code Highlighting By <a href="http://codemirror.net/" target="_blank">Codemirror</a></em>';}?> </td>
						</tr>
						<tr>
							<td colspan="2" align="center"><input type="submit" class="button-primary" value="Update this code" id='updatebtn' data-failed='Update Failed' data-updating='Updating...' data-updated='Updated!' /> <a href="?page=<?php echo self::$plugin_menu_slug ?>" id='closebtn' class='button-secondary'>Close</a></td>
						</tr>
					</tbody>
				</table>
			</form>
			<?php

			if ($option['enable_richeditor'] == 1) {
				?>
				<script>
			      var editor = CodeMirror.fromTextArea(document.getElementById("code"), {
			        lineNumbers: true,
			        matchBrackets: true,
			        mode: "application/x-httpd-php",
			        indentUnit: 4,
			        indentWithTabs: true,
			        enterMode: "keep",
			        tabMode: "shift"
			      });
			    </script>
				<?php
			}
		}

		/**
		* Creates the main content of the administation page
		*
		* @global wpdb $wpdb
		* @since 1.0
		* @author The Missing Code
		* @package WordPress
		*/
		function do_admin_menu_main(){
			global $wpdb;
			$snippets = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix.self::$plugin_table_name."` ORDER BY `id`");
	?>
			<h3>Plugin Options</h3>
			<div class='clearall'></div>
			<?php $option = self::get_option();?>
			<form action="?page=<?php echo self::$plugin_menu_slug?>" method="post">
				<input type='hidden' name='<?php echo self::$plugin_post;?>[action]' value='updateoptions' />
				<input type='hidden' name='<?php echo self::$plugin_post;?>[item]' value='' />
				<?php wp_nonce_field( "updateoptions", self::$plugin_post."[actioncode]" )?>
				<p class='formlabel'><input type='checkbox' class='mr22' value="1" <?php checked( $option["complete_deactivation"], 1 );?>name="<?php echo self::$plugin_post?>[complete_deactivation]"  id="<?php echo self::$plugin_post?>[complete_deactivation]"/> <label for="<?php echo self::$plugin_option_name?>[complete_deactivation]">On uninstall, remove all options and tables?</label></p>
				<p class='formlabel'><input type='checkbox' class='mr22' value="1" <?php checked( $option["content_filter"], 1 );?>name="<?php echo self::$plugin_post?>[content_filter]" id="<?php echo self::$plugin_post?>[content_filter]"/> <label for="<?php echo self::$plugin_option_name?>[content_filter]">Parse in-line [<?php echo self::$plugin_shortcode;?>] shortcode tags inside post content <small>(HTML editor only)</small></label></p>
				<p class='formlabel'><input type='checkbox' class='mr22' value="1" <?php checked( $option["sidebar_filter"], 1 );?> name="<?php echo self::$plugin_post?>[sidebar_filter]" id="<?php echo self::$plugin_post?>[sidebar_filter]"/> <label for="<?php echo self::$plugin_option_name?>[sidebar_filter]">Parse in-line [<?php echo self::$plugin_shortcode;?>] shortcode tags inside sidebar text widgets</label></p>
				<p class='formlabel'><input type='checkbox' class='mr22' value="1" <?php checked( $option["enable_richeditor"], 1 );?> name="<?php echo self::$plugin_post?>[enable_richeditor]" id="<?php echo self::$plugin_post?>[enable_richeditor]"/> <label for="<?php echo self::$plugin_option_name?>[enable_richeditor]">Enable rich editor for code snippets (powered by Codemirror)</label></p>
				<p class='clearall'><input type='submit' class='button-primary' value="Save Options" /></p>
			</form>
			<br />
			<br />
			<br />
			<h2>Saved Code <a class="add-new-h2" href="?page=<?php echo self::$plugin_menu_slug;?>&action=add">Add New</a></h2>

			<form action="?page=<?php echo self::$plugin_menu_slug?>" method="post" onsubmit="return confirm('\tWait!\nIf you delete these items, you won\'t get them back!\nDo you wish to continue?');">
			<input type='hidden' name='<?php echo self::$plugin_post;?>[action]' value='bulkdelete' />
			<input type='hidden' name='<?php echo self::$plugin_post;?>[item]' value='' />
				<?php wp_nonce_field( "bulkdelete", self::$plugin_post."[actioncode]" )?>
				<table cellspacing="0" class="wp-list-table widefat fixed posts">
					<thead>
						<tr>
							<th class="manage-column column-cb check-column" scope="col">
								<input type="checkbox">
							</th>
							<th class="manage-column" scope="col" colspan="2">
								Name
							</th>
							<th class="manage-column" scope="col" colspan="3">
								Description
							</th>
							<th class="manage-column" scope="col">
								Shortcode
							</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th class="manage-column column-cb check-column" scope="col">
								<input type="checkbox">
							</th>
							<th class="manage-column" scope="col" colspan="2">
								Name
							</th>
							<th class="manage-column" scope="col" colspan="3">
								Description
							</th>
							<th class="manage-column" scope="col">
								Shortcode
							</th>
						</tr>
					</tfoot>
					<tbody id="the-list">
						<?php if(sizeof($snippets) == 0):?>
						<tr class="no-items">
							<td colspan="9" class="colspanchange">No code found.</td>
						</tr>
						<?php else: foreach($snippets as $index => $snippet): ?>
						<tr<?php if($index%2){echo " class='alternate'";}?>>
							<th class="check-column" scope="row">
								<input type="checkbox" name="<?php echo self::$plugin_post;?>[delete][<?php echo $snippet->id?>]" />
							</th>
							<td colspan="2" valign="middle">
								<?php echo esc_html($snippet->name);?>
								<div class="row-actions">
									<span class="edit">
										<a href="?page=<?php echo self::$plugin_menu_slug ?>&action=edit&item=<?php echo $snippet->id ?>&actioncode=<?php echo wp_create_nonce($snippet->id."edit")?>">Edit</a>
									</span> |
									<span class="trash">
										<a href="?page=<?php echo self::$plugin_menu_slug ?>&<?php echo self::$plugin_post?>[action]=delete&<?php echo self::$plugin_post?>[item]=<?php echo $snippet->id ?>&<?php echo self::$plugin_post?>[actioncode]=<?php echo wp_create_nonce($snippet->id."delete") ?>" onclick="return confirm('\tWait!\nIf you delete this item, you won\'t get it back!\nDo you wish to continue?')">Delete</a>
									</span>
								</div>
							</td>
							<td colspan="3" valign="middle"><?php echo esc_html($snippet->description)?></td>
							<td valign="middle"><code>[<?php echo self::$plugin_shortcode?> snippet=<?php echo $snippet->id?>]</code></td>
						</tr>
						<?php endforeach;endif; ?>
					</tbody>
				</table>
				<p><input type="submit" class="button-secondary" value="Deleted Selected Snippets" /></p>
			</form>
	<?php
		   }

		/*
		* set variables into the _vars array
		*
		* @since 1.1
		* @author The Missing Code
		*/
		function set_variable($key, $value) {
			$this->_vars[$key] = $value;
		}

		/**
		 * get variable from the _vars array
		 *
		 * @since 1.1
		 * @author The Missing Code
		**/
		function get_variable($key) {
			return $this->_vars[$key];
		}

		/**
		 * checks if the table exists for the plugin, if it doesn't, create it
		 *
		 * @since 1.1.3
		 * @author The Missing Code
		 */
		private static function check_plugin_table_exists() {
			global $wpdb;
			$result = $wpdb->get_results("SHOW TABLES LIKE '". $wpdb->prefix . self::$plugin_table_name . "'");
			if (empty($result)) {
				echo '<div class="error"><p><strong>The plugin could not find the required table. Attempting to recreate the table. If you continue to get this message, seek support.</strong></p></div>';
				self::upgrade_table();
			}
		}

		/**
		 * used for ajax saving of code snippets (hopefully!)
		 *
		 * @since 1.2.0
		 * @author The Missing Code
		**/
		public static function ajax_callback(){
			//because of the structure used to post, it would appear check_ajax_referer's second parameter fails
			$post = $_POST[self::$plugin_post];
			if (wp_verify_nonce( $post["actioncode"], $post["item"].$post["action"] ) ){
				switch ( $post["action"] ):
				//not supporting Add yet, simply because add and edit are 2 different forms with different nonces.
				//it needs to be re-thought out first.
				//maybe a future release depending on how well the ajax update goes down.
					case "update":
						$response = self::update_snippet( $post, true );
						break;
				endswitch;
			}
			echo $response;
			die;
		}
	  }
	// End of the class

	/**
	 * Initial registration of the class on WordPress initialization.
	 * Populates the PHPPC global.
	 *
	 * @global PHPPC $PHPPC
	 * @since 1.0
	 * @author The Missing Code
	 * @package WordPress
	 */
	function register_phppc(){
		global $PHPPC;
		$PHPPC = new PHPPC();
	}
	add_action( "init" , "register_phppc" );
	register_activation_hook( __FILE__ , array( "PHPPC" , "activation_hook" ) );
	register_uninstall_hook( __FILE__, array( "PHPPC", "uninstall_hook" ) );
 }
 //end if, end plugin
