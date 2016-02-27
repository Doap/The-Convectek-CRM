<?php
define( 'SSEO_PLUGIN_URL',  plugins_url( '', SSEO_FILE ) ); 
/*  
function ss_eyes_only_merge_defaults(){
	if ( ! $stored = get_option('ss_eyes_only_options') ) {
		update_option('ss_eyes_only_options', ss_eyes_only_get_defaults() ); 
	} else {
		update_option( 'ss_eyes_only_options', array_merge( ss_eyes_only_get_defaults(), $stored ) ); 
	}
}
*/
// REQUIRE WORDPRESS 3.3
function requires_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( SSEO_FILE );
	$plugin_data = get_plugin_data( SSEO_FILE, false );
	if ( version_compare($wp_version, "3.3", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );}}}
add_action( 'admin_init', 'requires_wordpress_version' );
// INCLUDES
function eyes_only_style() {
	wp_register_style( 'sseo-style', plugins_url('/css/sseo-style.css', __FILE__) );
	wp_enqueue_style( 'sseo-style' ); 
}
add_action( 'admin_enqueue_scripts', 'eyes_only_style' );
include dirname(__FILE__).'/includes/sseo-shortcode-generator.php'; 
// TINY MCE MODAL
add_action('admin_head','sseo_add_button');
function sseo_add_button(){  
  global $pagenow;
    if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) )
        return;
	$sseo_options = get_option('ss_eyes_only_options');
	$sseo_modal_access = $sseo_options['ss_eyes_only_modal_access'];
	$pos = $sseo_options['ss_eyes_only_tmce_rows'];
	if(current_user_can($sseo_modal_access)){
		add_filter('mce_external_plugins', 'sseo_add_plugin');  
		add_filter('mce_buttons'.$pos, 'sseo_register_button');
	}
}
function sseo_register_button($buttons) { 
	array_push($buttons, "sseomodal");  
	return $buttons;
}
function sseo_add_plugin($plugin_array){  
   $plugin_array['sseomodal'] = plugins_url( '/sseo-modal.js', __FILE__ );   
   return $plugin_array;
}  
foreach( array('post.php','post-new.php') as $hook )
    add_action( "admin_head-$hook", 'admin_head_js_vars' );
/**
 * Localize Script
 */
function admin_head_js_vars() {
    $img = plugins_url( '/images/tmcesseo.png', __FILE__ );
	global $wp_version;
	if ($wp_version >= 3.9) $version = 'new';
	else $version = 'old';
        ?>
<!-- TinyMCE Shortcode Plugin -->
<script type='text/javascript'>
    var sseo_mce_config = {
        'tb_title': '<?php _e('Eyes Only'); ?>',
        'button_img': '<?php echo $img; ?>',
	    'version': '<?php echo $version; ?>',				
        'ajax_url': '<?php echo admin_url( 'admin-ajax.php')?>',
        'ajax_nonce': '<?php echo wp_create_nonce( '_nonce_tinymce_shortcode' ); ?>' 
    };
</script>
<style>
i.sseo-eyesonly-icon {
	background-image: url('<?php echo $img; ?>');
}
</style>
<!-- TinyMCE Shortcode Plugin -->
        <?php
}
add_action( 'wp_ajax_sseo_tinymce_shortcode', 'sseo_tinymce_shortcode' );
function sseo_tinymce_shortcode() {
    $do_check = check_ajax_referer( '_nonce_tinymce_shortcode', 'security', false ); 
    if( !$do_check )
        echo 'error';
    else
        include_once dirname(__FILE__).'/sseo-modal.php';
    exit();
}
// OPTIONS PAGE
add_action('admin_init', 'ss_eyes_only_init' );
add_action('admin_menu', 'ss_eyes_only_add_options_page');
add_filter( 'plugin_action_links', 'ss_eyes_only_plugin_action_links', 10, 2 );
function ss_eyes_only_get_defaults(){
	return array(	"ss_eyes_only_metabox_access" => "edit_pages",
					"ss_eyes_only_modal_access" => "edit_pages",
					"ss_eyes_only_tmce_rows" => "",
					"ss_eyes_only_admin_override" => "on",
					"chk_default_options_db" => "",
					"ss_eyes_only_strict_role_matching", false,
					"ss_eyes_only_bclass_prefix" => "eyesonly-",
					);
}
function ss_eyes_only_add_defaults(){
	$tmp = get_option('ss_eyes_only_options');
    if( ! is_array($tmp) || ! empty($tmp['chk_default_options_db']) ){
		delete_option('ss_eyes_only_options'); 
		update_option('ss_eyes_only_options', ss_eyes_only_get_defaults() ); 
	}
}
function ss_eyes_only_init(){
	register_setting( 'ss_eyes_only_plugin_options', 'ss_eyes_only_options', 'ss_eyes_only_validate_options' ); 
}
function ss_eyes_only_add_options_page() {
	$opt_page = add_options_page('Eyes Only &#8594; Options', 'Eyes Only Options', 'manage_options', __FILE__, 'ss_eyes_only_render_form'); 
}
function ss_eyes_only_render_form() {	?>
<div class="sseo-plugins"><center>
<a href="http://wordpress.org/plugins/eyes-only-user-access-shortcode/" target="_blank"><img src="<?php echo SSEO_PLUGIN_URL;?>/images/eyesonly_banner.png" border=0 /></a><br /><br />
<img src="<?php echo SSEO_PLUGIN_URL;?>/images/other_plugins.png" />
<a href="http://wordpress.org/plugins/file-away/" target="_blank"><img src="<?php echo SSEO_PLUGIN_URL;?>/images/fileaway_banner.png" border=0 style="margin-left:-20px" /></a>
<a href="http://wordpress.org/plugins/browser-body-classes-with-shortcodes/" target="_blank"><img src="<?php echo SSEO_PLUGIN_URL;?>/images/bbc_banner.png" border=0 style="margin-left:13px" /></a>
<a href="http://wordpress.org/plugins/formidable-customizations/" target="_blank"><img src="<?php echo SSEO_PLUGIN_URL;?>/images/fc_banner.png" border=0 style="margin-left:13px; margin-top:5px; width:285px;" /></a>
<a href="http://wordpress.org/plugins/formidable-email-shortcodes/" target="_blank"><img src="<?php echo SSEO_PLUGIN_URL;?>/images/fes_banner.png" border=0 style="margin-left:18px; width:290px;" /></a><br /><br /><br />
<a href="http://agapetry.net/" target="_blank"><img src="<?php echo SSEO_PLUGIN_URL;?>/images/other_plugins_by_kevin.png" /></a>
<a href="http://wordpress.org/plugins/capability-manager-enhanced/" target="_blank"><img src="<?php echo SSEO_PLUGIN_URL;?>/images/cme_banner.png" border=0 style="margin-left:13px" /></a><a href="http://presspermit.com/" target="_blank"><img src="<?php echo SSEO_PLUGIN_URL;?>/images/ppc_banner.png" border=0 style="margin-left:13px" /></a>
<div class="sseo-support-review"><a href="http://wordpress.org/support/plugin/eyes-only-user-access-shortcode" target="_blank" class="sseo-selectIt" style="margin-right:20px;">get support</a><a href="http://wordpress.org/support/view/plugin-reviews/eyes-only-user-access-shortcode" target="_blank" class="sseo-selectIt">leave a review</a></div></center></div>
<div class="wrap sseo-options-wrap">
<div class="icon32" id="icon-options-general"><br></div>
<h2 class="eyesonly-header">Eyes Only &#8594; <i>Options</i></h2>
<form method="post" action="options.php">
<?php settings_fields('ss_eyes_only_plugin_options'); ?>
<?php $options = get_option('ss_eyes_only_options'); ?>
<br /><br /><br /><table class="form-table sseo-form-table">
<tr><th scope="row"><h4 class="sseo-options-headings">Meta Box Access</h4></th>
<td><?php
	$user = new WP_User( 1 );
	$capslist = $user->allcaps;
	echo '<select name="ss_eyes_only_options[ss_eyes_only_metabox_access]">';
	echo '<option value="administrator" '.selected('administrator', $options['ss_eyes_only_metabox_access']).'>Administrators Only</option><option value="ss_fake_cap_nobody" '.selected('ss_fake_cap_nobody', $options['ss_eyes_only_metabox_access']).'>Disable for Everyone</option>';
	foreach($capslist as $cap=>$caps){
		if($cap !== 'administrator' && $cap !== 'level_0' && $cap !== 'level_1' && $cap !== 'level_2' && $cap !== 'level_3' && $cap !== 'level_4' && $cap !== 'level_5' && $cap !== 'level_6' && $cap !== 'level_7' && $cap !== 'level_8' && $cap !== 'level_9' && $cap !== 'level_10'){ 
			echo '<option value="'.$cap.'" '.selected($cap, $options['ss_eyes_only_metabox_access']).'>'.$cap.'</option>';}}
	echo '</select>';
?><br />
<div class="sseo-option-description">By user capability, choose who can see the shortcode metabox, or disable it completely. Default: edit_pages</div>
</td></tr>
<tr><th scope="row"><h4 class="sseo-options-headings">Shortcode Button Access</h4></th>
<td><?php
	$user = new WP_User( 1 );
	$capslist = $user->allcaps;
	echo '<select name="ss_eyes_only_options[ss_eyes_only_modal_access]">';
	echo '<option value="administrator" '.selected('administrator', $options['ss_eyes_only_modal_access']).'>Administrators Only</option><option value="ss_fake_cap_nobody" '.selected('ss_fake_cap_nobody', $options['ss_eyes_only_modal_access']).'>Disable for Everyone</option>';
	foreach($capslist as $cap=>$caps){
		if($cap !== 'administrator' && $cap !== 'level_0' && $cap !== 'level_1' && $cap !== 'level_2' && $cap !== 'level_3' && $cap !== 'level_4' && $cap !== 'level_5' && $cap !== 'level_6' && $cap !== 'level_7' && $cap !== 'level_8' && $cap !== 'level_9' && $cap !== 'level_10'){ 
			echo '<option value="'.$cap.'" '.selected($cap, $options['ss_eyes_only_modal_access']).'>'.$cap.'</option>';}}
	echo '</select>';
?><br />
<div class="sseo-option-description">By user capability, choose who can see the shortcode button, or disable it completely. Default: edit_pages</div>
</td></tr>
<tr><th scope="row"><h4 class="sseo-options-headings">Shortcode Button Position</h4></th>
<td><select name="ss_eyes_only_options[ss_eyes_only_tmce_rows]">
<option value="" <?php selected('', $options['ss_eyes_only_tmce_rows']) ?> />First Row</option>
<option value="_2" <?php selected('_2', $options['ss_eyes_only_tmce_rows']) ?> />Second Row</option>
<option value="_3" <?php selected('_3', $options['ss_eyes_only_tmce_rows']) ?> />Third Row</option>
<option value="_4" <?php selected('_4', $options['ss_eyes_only_tmce_rows']) ?> />Fourth Row</option>
</select>
<br />
<div class="sseo-option-description">Choose the position of the shortcode button on the TinyMCE panel. Default: First Row</div>
</td></tr>
<tr><th scope="row"><h4 class="sseo-options-headings">Administrator Override</h4></th>
<td><input id="admin-override-checkbox" name="ss_eyes_only_options[ss_eyes_only_admin_override]" type="checkbox" value="on" <?php if (isset($options['ss_eyes_only_admin_override'])) { checked('on', $options['ss_eyes_only_admin_override']); } ?> /> <label id="admin-override-label" for="admin-override-checkbox">Turn On or Off</label><br />
<div class="sseo-option-description">Prevents content being hidden from Administrators. Default: On</div>
</td></tr><script>jQuery("#admin-override-checkbox").click(function() { jQuery("#admin-override-label").text(this.checked ? "On" : "Off"); });</script>
<tr><th scope="row"><h4 class="sseo-options-headings">Strict Role Matching</h4></th>
<td><input id="strict-role-matching-checkbox" name="ss_eyes_only_options[ss_eyes_only_strict_role_matching]" type="checkbox" value="on" <?php if (isset($options['ss_eyes_only_strict_role_matching'])) { checked('on', $options['ss_eyes_only_strict_role_matching']); } ?> /> <label id="strict-role-matching-label" for="strict-role-matching-checkbox">Turn On or Off</label><br />
<div class="sseo-option-description">Off: &quot;Author&quot;, for example, includes all users with Author capabilities. On: just users with the Author role. Default: Off</div>
</td></tr><script>jQuery("#strict-role-matching-checkbox").click(function() { jQuery("#strict-role-matching-label").text(this.checked ? "On" : "Off"); });</script>
<tr><th scope="row"><h4 class="sseo-options-headings">Body Classes Prefix</h4></th>
<td><input type="text" name="ss_eyes_only_options[ss_eyes_only_bclass_prefix]" style="width:200px" value="<?php echo $options['ss_eyes_only_bclass_prefix']; ?>" />
<br />
<div class="sseo-option-description">Choose the prefix for your logged-in user-role and logged-in username body classes. Default:  eyesonly-</div>
</td></tr>
<tr valign="top"><th scope="row"><h4 class="sseo-options-headings">Database Options</h4></th>
<td><label><input name="ss_eyes_only_options[chk_default_options_db]" type="checkbox" value="1" <?php if (isset($options['chk_default_options_db'])) { checked('1', $options['chk_default_options_db']); } ?> /> Restore defaults on plugin deactivation / reactivation</label>
<br /><div class="sseo-option-description">Check this if you'd like to reset the above settings upon plugin reactivation</div>
</td></tr></table><br /><br />
<p class="submit"><input type="submit" class="sseo-selectIt" style="margin-left:70px!important;" value="<?php _e('Save Changes') ?>" /></p></form></div>
<?php	}
function ss_eyes_only_validate_options($input) {
	return $input;
}
function ss_eyes_only_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( SSEO_FILE ) ) {
		$ss_eyes_only_links = '<a href="'.get_admin_url().'options-general.php?page=' . SSEO_FOLDER . '/sseo-admin.php">'.__('Options').'</a>';
	array_unshift( $links, $ss_eyes_only_links ); }	return $links; }
