<?php
/*  
 Plugin Name: WS Custom Login 
 Plugin URI: https://wordpress.org/plugins/ws-custom-login/ 
 Description: WS Custom Login provides you to easy way to customize the appearance of the Wordpress wp-login.php page with many style options. 
 Version: 1.0        
 Author: Web Shouter           
 Author URI: http://www.webshouter.net/                          
 License: GPL3                                                                                             
 */                                                                                                                                                                                                                         
                                                                                                                                                         
//define variable                             
define('ws_blog_name',get_bloginfo('name'));                        
define('ws_site_url',get_site_url());               
define('ws_plugin_url',plugins_url( '/', __FILE__ ));            
                                   
//add admin menu                                                                                                                                         
function ws_custom_login_add_menu(){            
    add_menu_page('WS Custom Login', 'WS Custom Login', 'manage_options', 'ws-custom-login', 'ws_custom_login_settings_page',plugins_url( 'images/menu_icon.png', __FILE__ ) );
	add_action( 'admin_init', 'ws_custom_login_settings' );    
}   
          
function ws_custom_login_settings() {                   
	          
	//register settings 
	register_setting( 'ws-diaplay-settings-group', 'ws_login_on_or_off');
	register_setting( 'ws-diaplay-settings-group', 'ws_login_hide_lost_password');
	register_setting( 'ws-diaplay-settings-group', 'ws_login_hide_logo');
	register_setting( 'ws-diaplay-settings-group', 'ws_login_hide_back_to');
	register_setting( 'ws-diaplay-settings-group', 'ws_login_hide_error_msg'); 
	register_setting( 'ws-diaplay-settings-group', 'ws_login_desable_shake');
	register_setting( 'ws-diaplay-settings-group', 'ws_login_change_login_redirect');
	register_setting( 'ws-diaplay-settings-group', 'ws_login_set_remember_me');
    register_setting( 'ws-diaplay-settings-group', 'ws_login_background_color');
	register_setting( 'ws-diaplay-settings-group', 'ws_login_background_image');
	register_setting( 'ws-diaplay-settings-group', 'ws_login_link_color');
	register_setting( 'ws-diaplay-settings-group', 'ws_login_link_hover_color');   
	register_setting( 'ws-logo-settings-group', 'ws_login_logo_title');
	register_setting( 'ws-logo-settings-group', 'ws_login_link' );
	register_setting( 'ws-logo-settings-group', 'ws_login_form_logo' );
	register_setting( 'ws-logo-settings-group', 'ws_login_form_logo_width' );
	register_setting( 'ws-logo-settings-group', 'ws_login_form_logo_height' );  
	register_setting( 'ws-form-button-group', 'ws_login_form_submit_bg' );
	register_setting( 'ws-form-button-group', 'ws_login_form_submit_border_width' );
	register_setting( 'ws-form-button-group', 'ws_login_form_submit_border_color' );
	register_setting( 'ws-form-button-group', 'ws_login_form_submit_border_radius' );
	register_setting( 'ws-form-button-group', 'ws_login_form_submit_hover_bg' );
	register_setting( 'ws-form-button-group', 'ws_login_form_submit_hover_border_width' );
	register_setting( 'ws-form-button-group', 'ws_login_form_submit_hover_border_color' );
	register_setting( 'ws-form-textbox-group', 'ws_login_form_textbox_bg' );
	register_setting( 'ws-form-textbox-group', 'ws_login_form_textbox_border_width' );
	register_setting( 'ws-form-textbox-group', 'ws_login_form_textbox_border_color' );
	register_setting( 'ws-form-textbox-group', 'ws_login_form_textbox_border_radius' );
	register_setting( 'ws-form-textbox-group', 'ws_login_form_textbox_hover_bg' );
	register_setting( 'ws-form-textbox-group', 'ws_login_form_textbox_hover_border_width' );
	register_setting( 'ws-form-textbox-group', 'ws_login_form_textbox_hover_border_color' );
	register_setting( 'ws-form-settings-group', 'ws_login_form_bg' );
	register_setting( 'ws-form-settings-group', 'ws_login_form_border_size' );
	register_setting( 'ws-form-settings-group', 'ws_login_form_border_color' );
	register_setting( 'ws-form-settings-group', 'ws_login_form_border_radius' );
	register_setting( 'ws-form-settings-group', 'ws_login_form_margin_top' );
	register_setting( 'ws-form-settings-group', 'ws_login_form_label_size' );
	register_setting( 'ws-form-settings-group', 'ws_login_form_label_color' );
	register_setting( 'ws-form-settings-group', 'ws_login_form_label_bold' );
}

function ws_custom_login_activate() {
	
     // add default setting values on activation
    add_option( 'ws_login_on_or_off', 1, '', 1 );
	add_option( 'ws_login_hide_logo', 0, '', 0 );
	add_option( 'ws_login_hide_error_msg', 0, '', 0 );
	add_option( 'ws_login_background_color', '#F26103', '', '#F26103' );
	add_option( 'ws_login_background_image', '', '', '' );
	add_option( 'ws_login_link_color', '#ffffff', '', '#ffffff' );
	add_option( 'ws_login_link_hover_color', '#ffffff', '', '#ffffff' );
	add_option( 'ws_login_form_margin_top', '6', '', '6');
	add_option( 'ws_login_logo_title', ws_blog_name, '', ws_blog_name  );
	add_option( 'ws_login_link', ws_site_url, '',  ws_site_url );
	add_option( 'ws_login_form_bg', '#EA5E03', '', '#EA5E03' );
	add_option( 'ws_login_hide_lost_password', 0, '', 0 );
	add_option( 'ws_login_hide_back_to', 0, '', 0 );
	add_option( 'ws_login_desable_shake', 0, '', 0 );
	add_option( 'ws_login_change_login_redirect', ws_site_url, '', ws_site_url );
	add_option( 'ws_login_set_remember_me', 1, '', 1 );
	add_option( 'ws_login_form_border_size', 2, '', 2 );
	add_option( 'ws_login_form_border_color', '#ffffff', '', '#ffffff');
	add_option( 'ws_login_form_border_radius', 0, '', 0 );
	add_option( 'ws_login_form_label_size', 14, '', 14 );
	add_option( 'ws_login_form_label_color', '#ffffff', '', '#ffffff' );
	add_option( 'ws_login_form_label_bold', 0, '', 0 );
	add_option( 'ws_login_form_logo', ws_plugin_url.'images/logo.png', '', ws_plugin_url.'images/logo.png' );
	add_option( 'ws_login_form_logo_width', 84, '', 84 );
	add_option( 'ws_login_form_logo_height', 84, '', 84 );
	add_option( 'ws_login_form_submit_bg', '#404040', '', '#404040' );
	add_option( 'ws_login_form_submit_border_width', 1, '', 1 );
	add_option( 'ws_login_form_submit_border_color', '#404040', '', '#0080B0' );
	add_option( 'ws_login_form_submit_hover_bg', '#404040', '', '#029CD5' );
	add_option( 'ws_login_form_submit_hover_border_width', 1, '', 1 );
	add_option( 'ws_login_form_submit_hover_border_color', '#404040', '', '#404040' );
	add_option( 'ws_login_form_textbox_bg', '#F26103', '', '#F26103' );
	add_option( 'ws_login_form_textbox_border_width', 1, '', 1 );
	add_option( 'ws_login_form_textbox_border_color', '#ffffff', '', '#ffffff' );
	add_option( 'ws_login_form_textbox_hover_bg', '#F26103', '', '#F26103' );
	add_option( 'ws_login_form_textbox_hover_border_width', 1, '', 1 );
	add_option( 'ws_login_form_textbox_hover_border_color', '#ffffff', '', '#ffffff' );
	add_option( 'ws_login_form_submit_border_radius', 0, '', 0 );
	add_option( 'ws_login_form_textbox_border_radius', 0, '', 0 );
	
}
function ws_custom_login_deactivate() { //delete setting and values on deactivation
    delete_option( 'ws_login_on_or_off');
	delete_option( 'ws_login_hide_logo');
	delete_option( 'ws_login_hide_error_msg');
    delete_option( 'ws_login_background_color');
	delete_option( 'ws_login_background_image');
	delete_option( 'ws_login_link_color');
	delete_option( 'ws_login_link_hover_color');
	delete_option( 'ws_login_form_margin_top');
	delete_option( 'ws_login_logo_title');
	delete_option( 'ws_login_link');
	delete_option( 'ws_login_form_bg');
    delete_option( 'ws_login_hide_lost_password');
	delete_option( 'ws_login_hide_back_to');
	delete_option( 'ws_login_desable_shake');
	delete_option( 'ws_login_change_login_redirect');
	delete_option( 'ws_login_set_remember_me');
	delete_option( 'ws_login_form_border_size');
	delete_option( 'ws_login_form_border_color');
	delete_option( 'ws_login_form_border_radius');
	delete_option( 'ws_login_form_label_size');
	delete_option( 'ws_login_form_label_color');
	delete_option( 'ws_login_form_label_bold');
	delete_option( 'ws_login_form_logo');
	delete_option( 'ws_login_form_logo_width');
	delete_option( 'ws_login_form_logo_height');
	delete_option( 'ws_login_form_submit_bg');
	delete_option( 'ws_login_form_submit_border_width');
	delete_option( 'ws_login_form_submit_border_color');
	delete_option( 'ws_login_form_submit_hover_bg');
	delete_option( 'ws_login_form_submit_hover_border_width');
	delete_option( 'ws_login_form_submit_hover_border_color');
	delete_option( 'ws_login_form_textbox_bg');
	delete_option( 'ws_login_form_textbox_border_width');
	delete_option( 'ws_login_form_textbox_border_color');
	delete_option( 'ws_login_form_textbox_hover_bg');
	delete_option( 'ws_login_form_textbox_hover_border_width');
	delete_option( 'ws_login_form_textbox_hover_border_color');
	delete_option( 'ws_login_form_submit_border_radius');
	delete_option( 'ws_login_form_textbox_border_radius');
}
 /* Add scripts to head */
function ws_custom_login_scripts() { 
	wp_enqueue_script('jquery');
    wp_enqueue_script( array('jquery') );
}
function ws_custom_login_add_color_picker( $hook_suffix ) { //add colorpicker to options page
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
	if($_GET['page']=='ws-custom-login' AND is_admin())
	wp_enqueue_script( 'wp-color-picker-scripts', plugins_url( 'js/scripts.js', __FILE__ ), array( 'jquery', 'wp-color-picker','media-upload','thickbox' ), false, true );
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style('thickbox');
}

if (get_option('ws_login_on_or_off')==1) {
	
	function ws_add_dynamic_style() {
		echo '<link rel="stylesheet" type="text/css" href="' . plugins_url( 'includes/dynamic-style.php', __FILE__ ) . '" />';
	}

	add_action('login_head', 'ws_add_dynamic_style');
	include('includes/add-options.php');
}

register_activation_hook( __FILE__, 'ws_custom_login_activate' ); //register activation hook
register_deactivation_hook( __FILE__, 'ws_custom_login_deactivate' ); //register deactivation hook 
add_action('admin_menu', 'ws_custom_login_add_menu'); //add settings admin menu
add_action('wp_enqueue_scripts', 'ws_custom_login_scripts'); //add custom scrollbar scripts 
add_action( 'admin_enqueue_scripts', 'ws_custom_login_add_color_picker' );//add color picker js to admin


function ws_custom_login_settings_page(){
	 
 ?>

<div class='wrap'> 
	<h1><?php _e('WS Custom Login'); ?><a style="text-decoration:none;" href="http://www.webshouter.net/" target="_blank"><span style="color: rgba(10, 154, 62, 1);"> (Upgrade to Pro Version)</span></a></h1>
	
	<p class="description"><?php _e('WS Custom Login provides you to easy way to customize the appearance of the Wordpress wp-login.php page with many style options. <a href="https://profiles.wordpress.org/webshouter" target="_blank"> click here for more plugins</a> .'); ?></p>
	<?php include('includes/social-media.php'); ?>
	<?php
	$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'display_settings';
	if(isset($_GET['tab'])) $active_tab = $_GET['tab'];
	?>
	<h2 class="nav-tab-wrapper">
		<a href="?page=ws-custom-login&amp;tab=display_settings" class="nav-tab <?php echo $active_tab == 'display_settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Display Settings', 'ws-custom-login'); ?></a>
		<a href="?page=ws-custom-login&amp;tab=logo_settings" class="nav-tab <?php echo $active_tab == 'logo_settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Logo Settings', 'ws-custom-login'); ?></a>
		<a href="?page=ws-custom-login&amp;tab=form_settings" class="nav-tab <?php echo $active_tab == 'form_settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Form Settings', 'ws-custom-login'); ?></a>
		<a href="?page=ws-custom-login&amp;tab=button_settings" class="nav-tab <?php echo $active_tab == 'button_settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Button Settings', 'ws-custom-login'); ?></a>
		<a href="?page=ws-custom-login&amp;tab=textbox_settings" class="nav-tab <?php echo $active_tab == 'textbox_settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Textbox Settings', 'ws-custom-login'); ?></a>
		<a href="?page=ws-custom-login&amp;tab=donate_now" class="nav-tab <?php echo $active_tab == 'donate_now' ? 'nav-tab-active' : ''; ?>"><?php _e('Donate', 'ws-custom-login'); ?></a>
	</h2>
	
		
		<?php if($active_tab == 'display_settings') { ?>
		
		 <form method="post" action="options.php">
     	<?php settings_errors(); ?>
		<?php settings_fields('ws-diaplay-settings-group'); ?>
		<?php do_settings_sections('ws-diaplay-settings-group'); ?>
		
		<div id="poststuff" class="ui-sortable meta-box-sortables">
			<div class="postbox">
			<h3><?php _e('Display Settings', 'ws-custom-login'); ?></h3>
			<div class="inside">
				<p><?php _e('WS Custom Login provides you to easy way to customize the appearance of the Wordpress wp-login.php page with many style options.', 'ws-custom-login'); ?></p>
				
			<table class="form-table">
				<tr>
					<th scope="row"><label for="ws_login_on_or_off"><?php _e('On/Off plugin');?></label></th>
					<td valign="top"><input name="ws_login_on_or_off" type="checkbox" value= '1' <?php checked( 1,  get_option('ws_login_on_or_off') ); ?>  />
						<p class="description"><?php _e('Turn on or Turn off plugin'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_hide_logo"><?php _e('Hide Logo');?></label></th>
					<td valign="top"><input name="ws_login_hide_logo" type="checkbox" value= '1' <?php checked( 1,  get_option('ws_login_hide_logo') ); ?>  />
						<p class="description"><?php _e('Check to hide logo'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_hide_lost_password"><?php _e('Hide Lost Password Link');?></label></th>
					<td valign="top"><input name="ws_login_hide_lost_password" type="checkbox" value= '1' <?php checked( 1,  get_option('ws_login_hide_lost_password') ); ?>  />
						<p class="description"><?php _e('Check to remove the lost password link'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_hide_back_to"><?php _e('Hide Back to Link');?></label></th>
					<td valign="top"><input name="ws_login_hide_back_to" type="checkbox" value= '1' <?php checked( 1,  get_option('ws_login_hide_back_to') ); ?>  />
						<p class="description"><?php _e('Check to remove the back to link'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_hide_error_msg"><?php _e('Hide Error Message');?></label></th>
					<td valign="top"><input name="ws_login_hide_error_msg" type="checkbox" value= '1' <?php checked( 1,  get_option('ws_login_hide_error_msg') ); ?>  />
						<p class="description"><?php _e('Check to hide login form error message'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_desable_shake"><?php _e('Disable Shake Effect ');?></label></th>
					<td valign="top"><input name="ws_login_desable_shake" type="checkbox" value= '1' <?php checked( 1,  get_option('ws_login_desable_shake') ); ?>  />
						<p class="description"><?php _e('Enable or Disable form shake effect '); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_set_remember_me"><?php _e('Remember Me ');?></label></th>
					<td valign="top"><input name="ws_login_set_remember_me" type="checkbox" value= '1' <?php checked( 1,  get_option('ws_login_set_remember_me') ); ?>  />
						<p class="description"><?php _e('The “Remember Me” checkbox is unchecked by default. '); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_change_login_redirect"><?php _e('Change Logo Redirect'); ?></label></th>
					<td>
						<input type="text" name="ws_login_change_login_redirect" class="regular-text"  id="ws_login_change_login_redirect" value="<?php echo get_option('ws_login_change_login_redirect'); ?>" />
						<p class="description"><?php _e('When you login to WordPress you’re immediately taken to the dashboard. You can easily change this to redirect users to your homepage instead.'); ?></p>
					</td>
				</tr>
				
				<tr valign="top">
					  <th scope="row"><label for="ws_login_background_image"><?php _e('Background Image'); ?></label></th>
					  <td>
						  <input id="ws_login_background_image" type="text" name="ws_login_background_image" value="<?php echo get_option('ws_login_background_image') ?>" size="50" />
		                  <input class="ws-upload-button button" type="button" id="ws_upload_background_image_button" value="Upload Image" />
					      <p class='description'><?php _e('Upload or Select background image') ;?></p>
					 </td>
				 </tr>
				
		       <tr>
					<th scope="row"><label for="ws_login_background_color"><?php _e('Background Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_background_color"  id="ws_login_background_color" value="<?php echo stripslashes(get_option('ws_login_background_color')); ?>"  />
						<p class="description"><?php _e('Change your body background color. Default color is #F26103'); ?></p>
					</td>
				</tr>
				
				 <tr>
					<th scope="row"><label for="ws_login_link_color"><?php _e('Link Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_link_color"  id="ws_login_link_color" value="<?php echo stripslashes(get_option('ws_login_link_color')); ?>"  />
						<p class="description"><?php _e('Change lost your password or back to color. Default color is #ffffff'); ?></p>
					</td>
				</tr>
				
				 <tr>
					<th scope="row"><label for="ws_login_link_hover_color"><?php _e('Link Hover Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_link_hover_color"  id="ws_login_link_hover_color" value="<?php echo stripslashes(get_option('ws_login_link_hover_color')); ?>"  />
						<p class="description"><?php _e('Change lost your password or back to hover color. Default color is #5BD0FC'); ?></p>
					</td>
				</tr>
				
				<tr valign="top" align="left">
					<td class="frm_wp_heading"><?php submit_button(); ?></td>
				</tr>
				
				</table>
				
			</div>
			</div>
		</div>
		</form>
		<?php } ?>
		
		
		
		<?php if($active_tab == 'logo_settings') { ?>
		
		 <form method="post" action="options.php">
     	<?php settings_errors(); ?>
		<?php settings_fields('ws-logo-settings-group'); ?>
		<?php do_settings_sections('ws-logo-settings-group'); ?>
		
		<div id="poststuff" class="ui-sortable meta-box-sortables">
			<div class="postbox">
			<h3><?php _e('Logo Settings', 'ws-custom-login'); ?></h3>
			<div class="inside">
				<p><?php _e('WS Custom Login provides you to easy way to customize the appearance of the Wordpress wp-login.php page with many style options.', 'ws-custom-login'); ?></p>
				
			<table class="form-table">
			
				<tr valign="top">
					  <th scope="row"><label for="ws_login_form_logo"><?php _e('Logo Image'); ?></label></th>
					  <td>
						  <input id="ws_login_form_logo" type="text" name="ws_login_form_logo" value="<?php echo get_option('ws_login_form_logo') ?>" size="50" />
		                  <input class="ws-upload-button button" type="button" id="ws_logo_upload_image_button" value="Upload Image" />
						  <p class='description'><?php _e('Upload or Select Logo Image') ;?></p>
					 </td>
				 </tr>
				 
				<tr>
					<th scope="row"><label for="ws_login_form_logo_width"><?php _e('Logo Width'); ?></label></th>
				<td valign="top">
					<input name="ws_login_form_logo_width" type="number" step="0" max="900" id="ws_login_form_logo_width" value="<?php echo get_option('ws_login_form_logo_width'); ?>" class="small-text"> px
					<p class="description"><?php _e('Set login form logo width'); ?></p>
				</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_logo_height"><?php _e('Logo Height'); ?></label></th>
				<td valign="top">
					<input name="ws_login_form_logo_height" type="number" step="0" max="500" id="ws_login_form_logo_height" value="<?php echo get_option('ws_login_form_logo_height'); ?>" class="small-text"> px
					<p class="description"><?php _e('Set login form logo height'); ?></p>
				</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_logo_title"><?php _e('Logo Title'); ?></label></th>
					<td>
						<input type="text" name="ws_login_logo_title" class="regular-text"  id="ws_login_logo_title" value="<?php echo get_option('ws_login_logo_title'); ?>" />
						<p class="description"><?php _e('Enter login form logo title.'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_link"><?php _e('Logo Link'); ?></label></th>
					<td>
						<input type="text" name="ws_login_link" class="regular-text"  id="ws_login_link" value="<?php echo get_option('ws_login_link'); ?>" />
						<p class="description"><?php _e('Enter login form logo link.'); ?></p>
					</td>
				</tr>
				
				<tr valign="top" align="left">
					<td class="frm_wp_heading"><?php submit_button(); ?></td>
				</tr>
		
			</table>
				
			</div>
			</div>
		</div>
		</form>
		<?php } ?>
		
		
		<?php if($active_tab == 'form_settings') { ?>
		
		 <form method="post" action="options.php">
     	<?php settings_errors(); ?>
		<?php settings_fields('ws-form-settings-group'); ?>
		<?php do_settings_sections('ws-form-settings-group'); ?>
		
		<div id="poststuff" class="ui-sortable meta-box-sortables">
			<div class="postbox">
			<h3><?php _e('Form Settings', 'ws-custom-login'); ?></h3>
			<div class="inside">
				<p><?php _e('WS Custom Login provides you to easy way to customize the appearance of the Wordpress wp-login.php page with many style options.', 'ws-custom-login'); ?></p>
				
			<table class="form-table">
			
				<tr>
					<th scope="row"><label for="ws_login_form_margin_top"><?php _e('Margin Top'); ?></label></th>
				<td valign="top">
					<input name="ws_login_form_margin_top" type="number" step="0" max="500" id="ws_login_form_margin_top" value="<?php echo get_option('ws_login_form_margin_top'); ?>" class="small-text"> %
					<p class="description"><?php _e('Set login form margin from top'); ?></p>
				</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_label_size"><?php _e('Label Font Size'); ?></label></th>
				<td valign="top">
					<input name="ws_login_form_label_size" type="number" step="0" max="30" id="ws_login_form_label_size" value="<?php echo get_option('ws_login_form_label_size'); ?>" class="small-text"> px
					<p class="description"><?php _e('Set login form label font size'); ?></p>
				</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_label_color"><?php _e('Label Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_form_label_color"  id="ws_login_form_label_color" value="<?php echo stripslashes(get_option('ws_login_form_label_color')); ?>"  />
						<p class="description"><?php _e('Change login form label color. Default color is #ffffff'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_label_bold"><?php _e('Label Bold ');?></label></th>
					<td valign="top"><input name="ws_login_form_label_bold" type="checkbox" value= '1' <?php checked( 1,  get_option('ws_login_form_label_bold') ); ?>  />
						<p class="description"><?php _e('Enable or Disable form label bold'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_bg"><?php _e('Background Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_form_bg"  id="ws_login_form_bg" value="<?php echo stripslashes(get_option('ws_login_form_bg')); ?>"  />
						<p class="description"><?php _e('Change login form background color. Default color is #F26103'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_border_size"><?php _e('Border Width'); ?></label></th>
				<td valign="top">
					<input name="ws_login_form_border_size" type="number" step="0" max="50" id="ws_login_form_border_size" value="<?php echo get_option('ws_login_form_border_size'); ?>" class="small-text"> px
					<p class="description"><?php _e('Change login form border width. Default border width is 2px'); ?></p>
				</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_border_color"><?php _e('Border Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_form_border_color"  id="ws_login_form_border_color" value="<?php echo stripslashes(get_option('ws_login_form_border_color')); ?>"  />
						<p class="description"><?php _e('Change login form background color. Default color is #5BD0FC'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_border_radius"><?php _e('Border Radius'); ?></label></th>
				<td valign="top">
					<input name="ws_login_form_border_radius" type="number" step="0" max="50" id="ws_login_form_border_radius" value="<?php echo get_option('ws_login_form_border_radius'); ?>" class="small-text"> px
					<p class="description"><?php _e('Change login form border radius. Default border radius is 10px'); ?></p>
				</td>
				</tr>
				
				<tr valign="top" align="left">
					<td class="frm_wp_heading"><?php submit_button(); ?></td>
				</tr>
		
			</table>
				
			</div>
			</div>
		</div>
		</form>
		<?php } ?>
		
		<?php if($active_tab == 'button_settings') { ?>
		
		 <form method="post" action="options.php">
     	<?php settings_errors(); ?>
		<?php settings_fields('ws-form-button-group'); ?>
		<?php do_settings_sections('ws-form-button-group'); ?>
		<div id="poststuff" class="ui-sortable meta-box-sortables">
			<div class="postbox">
			<h3><?php _e('Button Settings', 'ws-custom-login'); ?></h3>
			<div class="inside">
				<p><?php _e('WS Custom Login provides you to easy way to customize the appearance of the Wordpress wp-login.php page with many style options.', 'ws-custom-login'); ?></p>
				
			<table class="form-table">
		
				<tr>
					<th scope="row"><label for="ws_login_form_submit_bg"><?php _e('Background Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_form_submit_bg"  id="ws_login_form_submit_bg" value="<?php echo stripslashes(get_option('ws_login_form_submit_bg')); ?>"  />
						<p class="description"><?php _e('Change login form submit button background color. Default color is #F26103'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_submit_border_width"><?php _e('Border Width'); ?></label></th>
				<td valign="top">
					<input name="ws_login_form_submit_border_width" type="number" step="0" max="50" id="ws_login_form_submit_border_width" value="<?php echo get_option('ws_login_form_submit_border_width'); ?>" class="small-text"> px
					<p class="description"><?php _e('Change login form submit button border width. Default border radius is 1px'); ?></p>
				</td>
				</tr>	
				
				<tr>
					<th scope="row"><label for="ws_login_form_submit_border_color"><?php _e('Border Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_form_submit_border_color"  id="ws_login_form_submit_border_color" value="<?php echo stripslashes(get_option('ws_login_form_submit_border_color')); ?>"  />
						<p class="description"><?php _e('Change login form submit button border color. Default color is #5BD0FC'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_submit_border_radius"><?php _e('Border Radius'); ?></label></th>
				<td valign="top">
					<input name="ws_login_form_submit_border_radius" type="number" step="0" max="50" id="ws_login_form_submit_border_radius" value="<?php echo get_option('ws_login_form_submit_border_radius'); ?>" class="small-text"> px
					<p class="description"><?php _e('Change login form submit button border radius. Default border radius is 10px'); ?></p>
				</td>
				</tr>
				
				
				<tr>
					<th scope="row"><label for="ws_login_form_submit_hover_bg"><?php _e('Hover Background Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_form_submit_hover_bg"  id="ws_login_form_submit_hover_bg" value="<?php echo stripslashes(get_option('ws_login_form_submit_hover_bg')); ?>"  />
						<p class="description"><?php _e('Change login form submit button hover background color. Default color is #029CD5'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_submit_hover_border_width"><?php _e('Hover Border Width'); ?></label></th>
				<td valign="top">
					<input name="ws_login_form_submit_hover_border_width" type="number" step="0" max="50" id="ws_login_form_submit_hover_border_width" value="<?php echo get_option('ws_login_form_submit_hover_border_width'); ?>" class="small-text"> px
					<p class="description"><?php _e('Change login form submit button hover border width. Default border radius is 1px'); ?></p>
				</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_submit_hover_border_color"><?php _e('Hover Border Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_form_submit_hover_border_color"  id="ws_login_form_submit_hover_border_color" value="<?php echo stripslashes(get_option('ws_login_form_submit_hover_border_color')); ?>"  />
						<p class="description"><?php _e('Change login form submit button hover border color. Default color is #0080B0'); ?></p>
					</td>
				</tr>
				
				<tr valign="top" align="left">
					<td class="frm_wp_heading"><?php submit_button(); ?></td>
				</tr>
				
			</table>
				
			</div>
			</div>
		</div>
		</form>
		<?php } ?>
		
		<?php if($active_tab == 'textbox_settings') { ?>
		
		 <form method="post" action="options.php">
     	<?php settings_errors(); ?>
		<?php settings_fields('ws-form-textbox-group'); ?>
		<?php do_settings_sections('ws-form-textbox-group'); ?>
		<div id="poststuff" class="ui-sortable meta-box-sortables">
			<div class="postbox">
			<h3><?php _e('Textbox Settings', 'ws-custom-login'); ?></h3>
			<div class="inside">
				<p><?php _e('WS Custom Login provides you to easy way to customize the appearance of the Wordpress wp-login.php page with many style options.', 'ws-custom-login'); ?></p>
				
			<table class="form-table">
		
				<tr>
					<th scope="row"><label for="ws_login_form_textbox_bg"><?php _e('Background Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_form_textbox_bg"  id="ws_login_form_textbox_bg" value="<?php echo stripslashes(get_option('ws_login_form_textbox_bg')); ?>"  />
						<p class="description"><?php _e('Change login form textbox background color. Default color is #F26103'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_textbox_border_width"><?php _e('Border Width'); ?></label></th>
				<td valign="top">
					<input name="ws_login_form_textbox_border_width" type="number" step="0" max="50" id="ws_login_form_textbox_border_width" value="<?php echo get_option('ws_login_form_textbox_border_width'); ?>" class="small-text"> px
					<p class="description"><?php _e('Change login form textbox border width. Default border radius is 1px'); ?></p>
				</td>
				</tr>	
				
				<tr>
					<th scope="row"><label for="ws_login_form_textbox_border_color"><?php _e('Border Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_form_textbox_border_color"  id="ws_login_form_textbox_border_color" value="<?php echo stripslashes(get_option('ws_login_form_textbox_border_color')); ?>"  />
						<p class="description"><?php _e('Change login form textbox border color. Default color is #5BD0FC'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_textbox_border_radius"><?php _e('Border Radius'); ?></label></th>
				<td valign="top">
					<input name="ws_login_form_textbox_border_radius" type="number" step="0" max="50" id="ws_login_form_textbox_border_radius" value="<?php echo get_option('ws_login_form_textbox_border_radius'); ?>" class="small-text"> px
					<p class="description"><?php _e('Change login form textbox border radius. Default border radius is 10px'); ?></p>
				</td>
				</tr>
				
				
				<tr>
					<th scope="row"><label for="ws_login_form_textbox_hover_bg"><?php _e('Hover Background Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_form_textbox_hover_bg"  id="ws_login_form_textbox_hover_bg" value="<?php echo stripslashes(get_option('ws_login_form_textbox_hover_bg')); ?>"  />
						<p class="description"><?php _e('Change login form textbox hover background color. Default color is #029CD5'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_textbox_hover_border_width"><?php _e('Hover Border Width'); ?></label></th>
				<td valign="top">
					<input name="ws_login_form_textbox_hover_border_width" type="number" step="0" max="50" id="ws_login_form_textbox_hover_border_width" value="<?php echo get_option('ws_login_form_textbox_hover_border_width'); ?>" class="small-text"> px
					<p class="description"><?php _e('Change login form textbox hover border width. Default border radius is 1px'); ?></p>
				</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ws_login_form_textbox_hover_border_color"><?php _e('Hover Border Color'); ?></label></th>
					<td valign="top">
						<input type="text" name="ws_login_form_textbox_hover_border_color"  id="ws_login_form_textbox_hover_border_color" value="<?php echo stripslashes(get_option('ws_login_form_textbox_hover_border_color')); ?>"  />
						<p class="description"><?php _e('Change login form  textbox hover border color. Default color is #0080B0'); ?></p>
					</td>
				</tr>
				
				<tr valign="top" align="left">
					<td class="frm_wp_heading"><?php submit_button(); ?></td>
				</tr>
		
			</table>
				
			</div>
			</div>
		</div>
		</form>
		<?php } ?>
		
		
		
		<?php if($active_tab == 'donate_now') { ?>
			
			
		<div id="poststuff" class="ui-sortable meta-box-sortables">
		<div class="postbox">
		<h3><?php _e('Donate Now', 'ws-custom-login'); ?></h3>
		<div class="inside">
			
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="T59LYJEC5HAHC">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
			
        </div>
        </div>
        </div>
			
		<?php } ?>

	
	
	</div>

<?php

 }
