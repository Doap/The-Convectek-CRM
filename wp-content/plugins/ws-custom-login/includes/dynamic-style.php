<?php header("Content-type: text/css"); ?>
<?php include '../../../../wp-load.php'; ?>
	body{
		  background: <?php echo get_option('ws_login_background_color'); ?>!important;
		}
		<?php if(get_option('ws_login_background_image')): ?>
			body {
			  background: url(<?php echo get_option('ws_login_background_image'); ?>) no-repeat center center fixed!important;
			-moz-background-size: cover!important;
			-webkit-background-size: cover!important;
			-o-background-size: cover!important;
			background-size: cover!important;
		}
		<?php endif; ?>
		.login form{
			 background: <?php echo get_option('ws_login_form_bg'); ?>!important;
			 border-width: <?php echo get_option('ws_login_form_border_size').'px'; ?>!important;
			 border-color: <?php echo get_option('ws_login_form_border_color'); ?>!important;
			 border-style: solid;
			 border-radius: <?php echo get_option('ws_login_form_border_radius').'px'; ?>!important;
		}
		#login {
			  width: 320px;
			  padding: 12% 0 0;
			  margin: auto;
		}
		.login label {
			  color: <?php echo get_option('ws_login_form_label_color');?>;
			  font-size: <?php echo get_option('ws_login_form_label_size');?>px;
			  <?php if(get_option('ws_login_form_label_bold')==1): ?>
			 	 font-weight: bold;
			  <?php endif; ?>
		}
		
		<?php if(get_option('ws_login_form_logo')): ?>
		.login h1 a {
			  background-image: url(<?php echo get_option('ws_login_form_logo');?>)!important;
			  -webkit-background-size: auto!important;
			  background-size: auto!important;
			  background-position: center top;
			  background-repeat: no-repeat;
			  color: #999;
			  height: <?php echo get_option('ws_login_form_logo_height');?>px!important;
			  width: <?php echo get_option('ws_login_form_logo_width');?>px!important;
		}
		<?php endif; ?>
		
		.login input[type=password], .login input[type=text]{
			  box-shadow: none!important;
			  outline: none!important;
			  border: <?php echo get_option('ws_login_form_textbox_border_width');?>px solid <?php echo get_option('ws_login_form_textbox_border_color');?>!important;
			  margin-top: 14px;
			  background: <?php echo get_option('ws_login_form_textbox_bg');?>!important;
			  color: #ffffff;
			  padding: 8px 11px;
			  height: auto!important;
			  font-size: 15px;
			    transition:all 0.5s ease-in-out;
			  -webkit-transition:all 0.5s ease-in-out;
			  -moz-transition:all 0.5s ease-in-out;
			  border-radius:<?php echo get_option('ws_login_form_textbox_border_radius');?>px!important;
			  -webkit-border-radius:<?php echo get_option('ws_login_form_textbox_border_radius');?>px!important;
			  -moz-border-radius:<?php echo get_option('ws_login_form_textbox_border_radius');?>px!important;
		}
		.login input[type=password]:focus, .login input[type=text]:focus{
			  box-shadow: none!important;
			  outline: none!important;
			  border: <?php echo get_option('ws_login_form_textbox_hover_border_width');?>px solid <?php echo get_option('ws_login_form_textbox_hover_border_color');?>!important;
			  background: <?php echo get_option('ws_login_form_textbox_hover_bg');?>!important;
		}
		.login .button-primary {
			  background: <?php echo get_option('ws_login_form_submit_bg');?>!important;
			  border: <?php echo get_option('ws_login_form_submit_border_width');?>px solid <?php echo get_option('ws_login_form_submit_border_color');?>!important;
			  box-shadow: none!important;
			  padding: 2px 30px!important;
			  height: auto!important;
			  transition:all 0.4s ease-in-out;
			  -webkit-transition:all 0.4s ease-in-out;
			  -moz-transition:all 0.4s ease-in-out;
			   border-radius:<?php echo get_option('ws_login_form_submit_border_radius');?>px!important;
			  -webkit-border-radius:<?php echo get_option('ws_login_form_submit_border_radius');?>px!important;
			  -moz-border-radius:<?php echo get_option('ws_login_form_submit_border_radius');?>px!important;
		}
		.login .button-primary:hover {
			  background: <?php echo get_option('ws_login_form_submit_hover_bg');?>!important;
			  border: <?php echo get_option('ws_login_form_submit_hover_border_width');?>px solid <?php echo get_option('ws_login_form_submit_hover_border_color');?>!important;
			  box-shadow: none!important;
			  padding: 2px 30px!important;
			  height: auto!important;
		}
		
		
		.login #backtoblog a:hover, .login #nav a:hover, .login h1 a:hover{
			  color: <?php echo get_option('ws_login_link_hover_color');?>;
		}
		.login #backtoblog a, .login #nav a {
			  text-decoration: none;
			  color: <?php echo get_option('ws_login_link_color');?>;
		}
		
		
		<?php if(get_option('ws_login_hide_error_msg')==1): ?>
		#login_error, .login .message {
			 display: none!important;
		}
		<?php endif; ?>
		
		<?php if(get_option('ws_login_hide_lost_password')==1): ?>
		p#nav {
			 display: none!important;
		}
		<?php endif; ?>
		
		<?php if(get_option('ws_login_hide_back_to')==1): ?>
		p#backtoblog {
			 display: none!important;
		}
		<?php endif; ?>
		
		<?php if(get_option('ws_login_hide_logo')==1): ?>
		.login h1{
		 	display: none!important;
		}
		#login {
		 	 padding: 12% 0 0!important;
		}
		<?php endif; ?>
		
		#login {
		 	 padding: <?php echo get_option('ws_login_form_margin_top');?>% 0 0!important;
		}