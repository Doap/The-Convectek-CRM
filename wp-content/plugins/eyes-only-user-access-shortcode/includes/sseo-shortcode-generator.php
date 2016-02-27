<?php 

// THE METABOX 
add_action( 'add_meta_boxes', 'eyesonly_meta_box_add' );  

// Begin Metabox Add
function eyesonly_meta_box_add() {  
	$sseo_options = get_option('ss_eyes_only_options');
	$sseo_metabox_access = $sseo_options['ss_eyes_only_metabox_access'];
	if (current_user_can($sseo_metabox_access)) {
		$types = array( 'post', 'page');
		foreach( $types as $type ) {
			add_meta_box( 'eyesonly-mb', 'Eyes Only', 'eyesonly_meta_box_cb', $type, 'normal', 'high' ); 
		}
	}

// Begin Metabox
if(!function_exists('eyesonly_meta_box_cb')):
function eyesonly_meta_box_cb() {  
?>
<script type="text/javascript">
/* Close Meta Box by Default */
	jQuery("#eyesonly-mb.postbox").addClass("closed");

jQuery(document).ready(function($) {
	$('a.sseo-selectIt, #eyesonly-mb h3.sseo-metaslide').click( function() {
		$('#sseo_create_table select').prop('value','');
		$('#sseo_create_table select option').prop('selected',false);
	});
	
	$(document).on('change','#sseo_create_table select[name="sseo_external_param"]', function() {
		$('#sseo_create_table div.column-extra > select').hide();
		$('#sseo_create_table div.column-extra #shortcode_'+$(this).val()).show();
	});
	
	$(document).on('change','#sseo_create_table div.column-extra > select', function() {
		generate_shortcode();
	});
	
	$(document).on('change', '#sseo_create_table select.sseo_level_type', function() {
		if ( navigator.userAgent.toLowerCase().indexOf('firefox') > -1 ) {
			$('#shortcode_levels option').hide();
			$('#shortcode_levels option.' + $(this).val() ).show();
		}
		else {
			$('#shortcode_levels option.' + $(this).val() ).prependTo( $('#shortcode_levels') );			
		}
	});
});
/* Smooth Toogle Transition for Metabox */	
jQuery("#eyesonly-mb h3.hndle").addClass("sseo-metaslide");
jQuery("#eyesonly-mb div.inside").addClass("sseo-metaslide-child");
    jQuery(document).ready(function($) {
        $("h3.sseo-metaslide").click(function(){
            $(this).toggleClass("active");
            $(this).next("div.sseo-metaslide-child").stop('true','true').slideToggle(600);
        });
    });
	
/* Shortcode Generator */
    	function generate_shortcode()
		{
	var level = jQuery('#shortcode_levels').val();
    var username = jQuery('#shortcode_usernames').val();
    var hide = jQuery('#shortcode_hidden').val();
    var logged = jQuery('#shortcode_logged_status').val();
	var nesting = jQuery('#shortcode_nesting').val();	
	var sclevel = nesting == '' ? 'eyesonly' : nesting;
    var shortcode_start = nesting = '['+sclevel;
    var shortcode_hide = hide === '' ? '' : ' hide="'+hide+'"';
    var shortcode_logged = logged === '' ? '' : ' logged="'+logged+'"';          
    var shortcode_level = level === null ? '' : ' level="'+level.join(", ")+'"';
    var shortcode_username = username === null ? '' : ' username="'+username.join(", ")+'"';
    var shortcode_end = '] Content Here [/'+sclevel+']';
	var shortcode_extras = '';
	
	jQuery('#sseo_create_table div.column-extra > select').each( function(e) {
		if ( jQuery(this).val() )
			shortcode_extras += ' ' + jQuery(this).attr('class') + '="' + jQuery(this).val().join(", ")+'"';
	});
	
	if(level != null || username != null || logged != '' || shortcode_extras){
	  var shortcode = shortcode_start+shortcode_level+shortcode_username+shortcode_logged+shortcode_extras+shortcode_hide+shortcode_end;
	
	  jQuery("#sseo-shortcode").fadeOut("fast", function(){
	  jQuery('#sseo-shortcode').text(shortcode).fadeIn('slow');});
			}
			else
			{
	  jQuery('#sseo-shortcode').fadeOut('slow');
  	  jQuery('#sseo-shortcode').text('');}
	}

/* Select-It Function */
   function selectElementContents(el) {
        var body = document.body, range, sel;
        if (document.createRange && window.getSelection) {
            range = document.createRange();
            sel = window.getSelection();
            sel.removeAllRanges();
            try {
                range.selectNodeContents(el);
                sel.addRange(range);
            } catch (e) {
                range.selectNode(el);
                sel.addRange(range);
            }
        } else if (body.createTextRange) {
            range = body.createTextRange();
            range.moveToElementText(el);
            range.select();
        }
    }
</script>

<!-- Shortcode Generator Form -->
<table id="sseo_create_table"><tr><td>
<div class="eyesonly-column0"><?php if(current_user_can('manage_options')){ ?><a href="options-general.php?page=<?php echo SSEO_FOLDER;?>/sseo-admin.php" target="_blank" class="sseo-selectIt sseo-config-options">Configure Options</a><?php } ?><br />
Use Ctrl+Click to Select Multiple (or to Deselect). When you've finished building your code, hit the 'Select It' button, then just copy and paste it into your Visual Editor. You're ready to share your secrets!
</div>

<tr><td>
<div class="eyesonly-column eyesonly-column1">
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
	echo '<select multiple style="height:140px; min-width:175px;" id="shortcode_levels" placeholder="Roles and Capabilities" onchange="generate_shortcode();">';
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
</div>

<div class="eyesonly-column eyesonly-column2">
<h4>By Username</h4>
<?php
	global $blog_id;
	echo '<select multiple style="height:140px; min-width:135px; margin-top:6px!important;" id="shortcode_usernames" placeholder="Usernames" onchange="generate_shortcode();">';
    $blogusers = get_users('blog_id='.$blog_id.'&orderby=nicename');
    foreach ($blogusers as $user) {echo '<option value="'.$user->user_login.'">'.$user->user_login.'</option>';}
	echo '</select>';
?>
</div>

<?php 
global $sseo_external_params;
if ( $sseo_external_params ) { 
?>

<div class="eyesonly-column eyesonly-column1 column-extra">

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
			<select multiple style="height:140px; width:200px;<?php if( ! $first_param ) echo 'display:none;'; ?>" id="shortcode_<?php echo $param_key;?>" name="<?php echo $param_key;?>" class="<?php echo $param_key;?>"> <?php // 140 Height to fit with aesthetic. 200 width is fine. I've moved your selectbox before the Select It button column if your _pp_sseo_modal_ui function exists.?>
				<?php foreach ( $param_items as $item_id => $item_label ) :?><option value="<?php echo $item_id;?>"><?php echo $item_label;?></option><?php endforeach;?>
			</select>
		<?php 
			$first_param = false;
		endif; 
		?>
	<?php endforeach;?>
</div>

<div class="eyesonly-column eyesonly-column2">
<h4>By Logged Status</h4>
<select style="min-width:115px;" id="shortcode_logged_status" placeholder="Logged Status" onchange="generate_shortcode();">
<option value=""></option>
<option value="in">Logged In</option>
<option value="out">Logged Out</option>
</select>
<br />

<h4>Show / Hide</h4>
<select style="min-width:115px;" id="shortcode_hidden" placeholder="Hide It" onchange="generate_shortcode();">
<option value="">Show To</option>
<option value="yes">Hide From</option>
</select>
<br />

<h4>Nesting Level</h4>
<select style="min-width:115px;" id="shortcode_nesting" placeholder="Nesting" onchange="generate_shortcode();">
<option value="">eyesonly</option>
<option value="eyesonlier">eyesonlier</option>
<option value="eyesonliest">eyesonliest</option>
</select>
<br /><br />

<a onclick="selectElementContents( document.getElementById('sseo-shortcode') );" class="sseo-selectIt" style="margin-top:7px;">Select It</a>
</div>

<?php } else { ?>

<div class="eyesonly-column eyesonly-column3">
<h4>By Logged Status</h4>
<select style="min-width:115px;" id="shortcode_logged_status" placeholder="Logged Status" onchange="generate_shortcode();">
<option value=""></option>
<option value="in">Logged In</option>
<option value="out">Logged Out</option>
</select>
<br />

<h4>Show / Hide</h4>
<select style="min-width:115px;" id="shortcode_hidden" placeholder="Hide It" onchange="generate_shortcode();">
<option value="">Show To</option>
<option value="yes">Hide From</option>
</select>
<br />

<h4>Nesting Level</h4>
<select style="min-width:115px;" id="shortcode_nesting" placeholder="Nesting" onchange="generate_shortcode();">
<option value="">eyesonly</option>
<option value="eyesonlier">eyesonlier</option>
<option value="eyesonliest">eyesonliest</option>
</select>
<br /><br />

<a onclick="selectElementContents( document.getElementById('sseo-shortcode') );" class="sseo-selectIt" style="margin-top:7px;">Select It</a>
</div>


<?php } ?>


</td></tr>
</table>

<div id="sseo-shortcode"></div>

<?php 
} //End Metabox
endif;
} //End Metabox Add