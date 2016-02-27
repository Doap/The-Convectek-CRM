<?php
function logout_redirect_register_settings() {
	add_option('logout_redirect_type', 'current');
	add_option('logout_redirect_customise_url', home_url());
	register_setting('logout_redirect_options', 'logout_redirect_type');
	register_setting('logout_redirect_options', 'logout_redirect_customise_url');
}
add_action('admin_init', 'logout_redirect_register_settings');

function logout_redirect_register_options_page() {
	add_options_page(__('Logout Redirect Options Page', LOGOUT_REDIRECT_TEXT_DOMAIN), __('Logout Redirect', LOGOUT_REDIRECT_TEXT_DOMAIN), 'manage_options', LOGOUT_REDIRECT_TEXT_DOMAIN.'-options', 'logout_redirect_options_page');
}
add_action('admin_menu', 'logout_redirect_register_options_page');

function logout_redirect_get_select_option($select_option_name, $select_option_value, $select_option_id){
	?>
	<select name="<?php echo $select_option_name; ?>" id="<?php echo $select_option_name; ?>"<?php if($select_option_name == "logout_redirect_type"){ ?> onchange="customise_url(this);"<?php } ?>>
		<?php
		for($num = 0; $num < count($select_option_id); $num++){
			$select_option_value_each = $select_option_value[$num];
			$select_option_id_each = $select_option_id[$num];
			?>
			<option value="<?php echo $select_option_id_each; ?>"<?php if (get_option($select_option_name) == $select_option_id_each){?> selected="selected"<?php } ?>>
				<?php echo $select_option_value_each; ?>
			</option>
		<?php } ?>
	</select>
	<?php
}

function logout_redirect_options_page() {
?>
<script>
function customise_url(select){
	var selected_option = select.options[select.selectedIndex].value;
	if(selected_option == "customise"){
		jQuery("#logout_redirect_customise_div").slideDown();
	}else{
		jQuery("#logout_redirect_customise_div").slideUp();
	}
}
</script>
<div class="wrap">
	<h2><?php _e("Logout Redirect Options Page", LOGOUT_REDIRECT_TEXT_DOMAIN); ?></h2>
	<form method="post" action="options.php">
		<?php settings_fields('logout_redirect_options'); ?>
		<h3><?php _e("General Options", LOGOUT_REDIRECT_TEXT_DOMAIN); ?></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="logout_redirect_type"><?php _e("Where you want to go after logout?", LOGOUT_REDIRECT_TEXT_DOMAIN); ?></label></th>
					<td>
						<?php logout_redirect_get_select_option("logout_redirect_type", array(__('Current URL', LOGOUT_REDIRECT_TEXT_DOMAIN), __('Customise URL', LOGOUT_REDIRECT_TEXT_DOMAIN)), array('current', 'customise')); ?>
						<div id="logout_redirect_customise_div"<?php if(get_option("logout_redirect_type") != "customise"){ ?> style="display: none;"<?php } ?>>
							<input type="url" name="logout_redirect_customise_url" id="logout_redirect_customise_url" value="<?php echo get_option('logout_redirect_customise_url'); ?>" size="40" />
						</div>
					</td>
				</tr>
			</table>
		<?php submit_button(); ?>
	</form>
</div>
<?php
}
?>