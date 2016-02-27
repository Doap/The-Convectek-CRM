(function() {
   if(sseo_mce_config.version === 'new'){
    tinymce.PluginManager.add('sseomodal', function( editor, url ) {
        editor.addButton( 'sseomodal', {
            title: sseo_mce_config.tb_title,
            icon: 'icon sseo-eyesonly-icon',
            onclick: function() {
             var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 640 < width ) ? 640 : width;
						W = W;
						H = H;
						tb_show( 'Eyes Only: User Access Shortcode', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=sseomodal-form' );
            }
        });
    });
   }
	else {
	tinymce.create('tinymce.plugins.sseomodal', {
		createControl : function(id, controlManager) {
			if (id == 'sseomodal') {
				var button = controlManager.createButton('sseomodal', {
					title: sseo_mce_config.tb_title, 
					image: sseo_mce_config.button_img,
					onclick : function() {
						var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 640 < width ) ? 640 : width;
						W = W;
						H = H;
						tb_show( 'Eyes Only: User Access Shortcode', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=sseomodal-form' );
					}
				});
				return button;
			}
			return null;
		}
	});
	tinymce.PluginManager.add('sseomodal', tinymce.plugins.sseomodal);
	}
})();
jQuery(function($)
{
    var table;
    var data_alt = {
        action: 'sseo_tinymce_shortcode',
        security: sseo_mce_config.ajax_nonce
    };
    $.post( 
        sseo_mce_config.ajax_url, 
        data_alt,                   
        function( response )
        {
            if( 'error' == response  )
            {
                $('<div id="sseomodal-form"><h1 style="color:#c00;padding:100px 0;width:100%;text-align:center">Ajax error</h1></div>')
                    .appendTo('body').hide();
            }
            else
            {
                form = $(response);
                table = form.find('table');
                form.appendTo('body').hide();
                form.find('#sseomodal-submit').click(sseo_submit_shortcode);
            }
        }
    );
	$(document).on('change','#sseomodal-table select[name="sseo_external_param"]', function() {
		$('#sseomodal-table div.column-extra > select').hide();
		$('#sseomodal-table div.column-extra #shortcode_'+$(this).val()).show();
	});
	$(document).on('change', '#sseomodal-table select.sseo_level_type', function() {
		if ( navigator.userAgent.toLowerCase().indexOf('firefox') > -1 ) {
			$('#sseomodal-level option').hide();
			$('#sseomodal-level option.' + $(this).val() ).show();
		} else {
			$('#sseomodal-level option.' + $(this).val() ).prependTo( $('#sseomodal-level') );
		}
	});
    function sseo_submit_shortcode()
    {
    var nesting = jQuery('#sseomodal-nesting').val();
	var sclevel = nesting == '' ? 'eyesonly' : nesting;
	var level = jQuery('#sseomodal-level').val();
    var username = jQuery('#sseomodal-username').val();
    var logged = jQuery('#sseomodal-logged').val();
    var hide = jQuery('#sseomodal-hide').val();
    var sseo_shortcode_start = '['+sclevel;
    var sseo_shortcode_hide = hide === '' ? '' : ' hide="'+hide+'"';
    var sseo_shortcode_logged = logged === '' ? '' : ' logged="'+logged+'"';          
    var sseo_shortcode_level = level === null ? '' : ' level="'+level.join(", ")+'"';
    var sseo_shortcode_username = username === null ? '' : ' username="'+username.join(", ")+'"';
	var selected_content = tinyMCE.activeEditor.selection.getContent();
	if ( ! selected_content )
		selected_content = ' Content Here ';
	else {
		selected_content = selected_content.replace(/\[[ ]*eyesonly[^\]]*\]/gi,'');
		selected_content = selected_content.replace(/\[[ ]*\/eyesonly\]/gi,'');
	} 
	var sseo_shortcode_end = ']' + selected_content + '[/'+sclevel+']';
    var shortcode = sseo_shortcode_start+sseo_shortcode_level+sseo_shortcode_username+sseo_shortcode_logged+sseo_shortcode_hide;
	$('#sseomodal-table div.column-extra > select').each( function(e) {
		if ( $(this).val() )
			shortcode = shortcode + ' ' + $(this).attr('class') + '="' + $(this).val().join(", ")+'"';
	});
	shortcode = shortcode + sseo_shortcode_end;
        tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
		jQuery('#sseomodal_level_type, #sseomodal-level, #sseomodal-username, #sseomodal-logged, #sseomodal-hide, #sseo_external_param, #shortcode_rs_group, #shortcode_pp_group').prop('value','');
		jQuery('#sseomodal_level_type option, #sseomodal-level option, #sseomodal-nesting option, #sseomodal-username option, #sseomodal-logged option, #sseomodal-hide option, #sseo_external_param option, #shortcode_rs_group option, #shortcode_pp_group option').prop('selected',false);
        tb_remove();
    }
});		