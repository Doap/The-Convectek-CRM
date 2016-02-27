<?php
/**
 * Used by Ajax
 */
?>

<div id="sseomodal-form">
<div style="float:right;"><?php if(current_user_can('manage_options')){ ?><a href="options-general.php?page=<?php echo SSEO_FOLDER;?>/sseo-admin.php" target="_blank" class="sseo-selectIt sseo-config-options-modal">Configure Options</a><?php } ?>
</div><div class="sseo-modal-instructions">ctrl+click for multiple or to deselect</div>
<img src="<?php echo plugins_url( 'images/eyesonly_banner.png', SSEO_FILE ); ?>" style="margin-left:5px; margin-right: 20px; position:relative; top:5px">

<table id="sseomodal-table" class="form-table">

<tr><td valign=top colspan=10>
<h4>Nesting Level</h4>
<select style="min-width:115px;" id="sseomodal-nesting" name="logged">
<option value="">eyesonly</option>
<option value="eyesonlier">eyesonlier</option>
<option value="eyesonliest">eyesonliest</option>
</select>
</td></tr>


<tr><td valign=top>
<h4>By <select class="sseo_level_type">
<option value="wp_role">Role</option>
<?php
global $wp_roles;
$admin_role = $wp_roles->get_role( 'administrator' );
$capslist = $admin_role->capabilities;

$post_types = get_post_types( array( 'public' => true ), 'object' );

$cap_types = array();
$cap_count = array();

$post_caps = (array) $post_types['post']->cap;

foreach( array_keys( $post_types ) as $post_type ) {
	$cap_count[$post_type] = 0;
	foreach( $post_types[$post_type]->cap as $cap_name ) {
		if ( ( 'post' != $post_type ) && in_array( $cap_name, $post_caps ) )
			continue;
	
		if ( ! isset($cap_types[$cap_name]) )
			$cap_types[$cap_name] = array();
		
		$cap_types[$cap_name][]= "{$post_type}_cap";
		$cap_count[$post_type]++;
		
		if ( ! isset($capslist[$cap_name] ) )
		$capslist[$cap_name] = true;
	}
}

ksort($capslist);

$types_alpha = array();
foreach( $post_types as $post_type => $type_obj ) {
	$types_alpha[$post_type] = $type_obj->labels->singular_name;
}
asort( $types_alpha );
foreach( $types_alpha as $post_type => $type_label ) :
	if ( empty($cap_count[$post_type]) )
		continue;
?>
<option value="<?php echo "{$post_type}_cap";?>"><?php printf( "%s Access", $type_label );?></option>
<?php endforeach;?>
<option value="general">Other</option>
</select>

</h4>
<?php
	$current_user = wp_get_current_user();
	global $wp_roles;
	$all_roles = $wp_roles->roles; 
	$editable_roles = apply_filters('editable_roles', $all_roles); 
	echo '<select multiple style="height:140px; min-width:135px;" id="sseomodal-level" placeholder="Roles and Capabilities" onchange="generate_shortcode();">';
	foreach($editable_roles as $role=>$theroles){echo '<option value="'.$role.'" class="wp_role">'.$wp_roles->role_names[$role].'</option>';}
	if(current_user_can('manage_options')){
		$style = ( strpos( strtolower($_SERVER['HTTP_USER_AGENT']), 'firefox' ) ) ? ' style="display:none"' : '';

		foreach($capslist as $cap=>$caps){
			$class = ( isset($cap_types[$cap]) ) ? 'class="' . implode(" ", $cap_types[$cap]) . '"' : 'class="general"';
		
			if($cap !== 'administrator' && $cap !== 'level_0' && $cap !== 'level_1' && $cap !== 'level_2' && $cap !== 'level_3' && $cap !== 'level_4' && $cap !== 'level_5' && $cap !== 'level_6' && $cap !== 'level_7' && $cap !== 'level_8' && $cap !== 'level_9' && $cap !== 'level_10'){ 
				echo '<option value="'.$cap.'"' . $class . $style . '>'.$cap.'</option>';}}
	}
	echo '</select>';
?>
</td>

<td valign=top>
<h4>By Username</h4>
<?php
	global $blog_id;
	echo '<select style="height:140px; min-width:135px;" id="sseomodal-username" class="require_one" name="username" multiple>';
    $blogusers = get_users('blog_id='.$blog_id.'&orderby=nicename');
    foreach ($blogusers as $user) {echo '<option value="'.$user->user_login.'">'.$user->user_login.'</option>';}
	echo '</select>';
?>
</td>

<?php 
global $sseo_external_params;
if ( $sseo_external_params ) { 
?>
<td valign=top>
<div class="eyesonly-column column-extra">

<?php if( count($sseo_external_params) == 1 ):
	$param = reset( $sseo_external_params );
?>
	<h4><?php printf( 'By %s', $param->label );?></h4>
<?php else:?>
	<h4>
	<?php /* if ( not translated or not rtl ) */ ?>By 
	
	<select name="sseo_external_param">
	<?php foreach( $sseo_external_params as $param_key => $param ) :?>
		<option value="<?php echo $param_key;?>"><?php echo $param->label;?></option>
	<?php endforeach;?>
	</select>
	<?php /* if ( translated and rtl ) printf( __( ' By', 'pp' ), $param->label ); */?>
	</h4>
<?php endif;

	$first_param = true;
	foreach( $sseo_external_params as $param_key => $param ) :
		if ( $param_items = apply_filters( "sseo_{$param_key}_items", array() ) ) :?>
			<select multiple style="height:140px; width:200px;<?php if( ! $first_param ) echo 'display:none;'; ?>" id="shortcode_<?php echo $param_key;?>" name="<?php echo $param_key;?>" class="<?php echo $param_key;?>" onchange="generate_shortcode();"> 
				<?php foreach ( $param_items as $item_id => $item_label ) :?><option value="<?php echo $item_id;?>"><?php echo $item_label;?></option><?php endforeach;?>
			</select>
		<?php 
			$first_param = false;
		endif; 
		?>
	<?php endforeach;?>
</div>
</td>

</tr>

<tr><td valign=top>
<h4>By Logged Status</h4>
<select id="sseomodal-logged" class="require_one" name="logged">
<option value=""></option>
<option value="in">Logged In</option>
<option value="out">Logged Out</option>
</select><br />
</td>

<td valign=top>
<h4>Show / Hide</h4>
<select id="sseomodal-hide" class="optional" name="hide">
<option value="">Show To</option>
<option value="yes">Hide From</option>
</select><br /></td>

<td valign=bottom>
<input type="button" id="sseomodal-submit" class="sseo-selectIt" style="padding-left:0; padding-right:0; width:150px; margin-top:0px;" value="Insert Shortcode" name="submit" /><br>
</td>
</tr>
<?php } else { ?>

<td valign=top>
<h4>By Logged Status</h4>
<select id="sseomodal-logged" class="require_one" name="logged">
<option value=""></option>
<option value="in">Logged In</option>
<option value="out">Logged Out</option>
</select><br />

<h4 style="margin-top:15px!important;">Show / Hide</h4>
<select id="sseomodal-hide" class="optional" name="hide">
<option value="">Show To</option>
<option value="yes">Hide From</option>
</select><br /><br />

<p class="submit"><input type="button" id="sseomodal-submit" class="sseo-selectIt" style="padding-left:0; padding-right:0; width:150px; margin-top:0px;" value="Insert Shortcode" name="submit" /></p><br>
</td>
</tr>
<?php } ?>

</table><br />
</div>