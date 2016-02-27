jQuery(document).ready(function(){
   jQuery('#ws_login_form_border_color,#ws_login_background_color,#ws_login_form_bg,#ws_login_form_label_color,#ws_login_form_submit_bg,#ws_login_form_submit_border_color,#ws_login_form_submit_hover_border_color,#ws_login_form_submit_hover_bg,#ws_login_form_textbox_bg,#ws_login_form_textbox_border_color,#ws_login_form_textbox_hover_bg,#ws_login_form_textbox_hover_border_color,#ws_login_link_color,#ws_login_link_hover_color').wpColorPicker();
   
      var uploadID = ''; // setup the var in a global scope
   var original_send_to_editor = window.send_to_editor;
   
   jQuery('#ws_logo_upload_image_button').click(function() {
	 uploadID = jQuery(this).prev('input'); // set the uploadID variable to the value of the input before the upload button
	 formfield = jQuery('#ws_login_form_logo').attr('name');
	 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	 uploadBAR(); // Call if needed
	 return false;
	});
	 
	
	 jQuery('#ws_upload_background_image_button').click(function() {
	 uploadID = jQuery(this).prev('input'); // set the uploadID variable to the value of the input before the upload button
	 formfield = jQuery('#ws_login_background_image').attr('name');
	 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	 uploadBAR(); // Call if needed
	 return false;
	});
	 
	
   window.send_to_editor = function(html) {
	 imgurl = jQuery('img',html).attr('src');
	 uploadID.val(imgurl); /*assign the value of the image src to the input*/
     tb_remove();
     window.send_to_editor = original_send_to_editor;//restore old send to editor function
	}
	
   
});



