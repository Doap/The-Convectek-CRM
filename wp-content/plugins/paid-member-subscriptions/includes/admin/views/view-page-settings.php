<?php
/*
 * HTML Output for the settings page
 */

?>

<div class="wrap pms-wrap">

    <h2><?php echo $this->page_title; ?></h2>

    <?php settings_errors( 'general' ); ?>

    <form method="POST" action="options.php">

        <?php
            settings_fields( $this->settings_slug );

            // Get active tab
            $active_tab = ( isset( $_REQUEST['nav_tab'] ) ? trim( $_REQUEST['nav_tab'] ) : 'general' );
        ?>


        <h3 class="nav-tab-wrapper">
            <?php
                foreach( $this->get_tabs() as $tab_slug => $tab_name ) {
                    echo '<a href="' . $tab_slug . '" class="nav-tab ' . ( $active_tab == $tab_slug ? 'nav-tab-active' : '' ) . '">' . $tab_name . '</a>';
                }
            ?>
        </h3>


        <div class="pms-tab-wrapper">

            <!-- General Settings Tab -->
            <div id="pms-settings-general" class="pms-tab <?php echo ( $active_tab == 'general' ? 'tab-active' : '' ); ?>">

                <?php do_action( $this->menu_slug . '_tab_general_before_content', $this->options ); ?>

                <div class="pms-form-field-wrapper">
                    <label class="pms-form-field-label" for="use-pms-css"><?php echo __( 'Load CSS' , 'pmstxt' ) ?></label>

                    <p class="description"><input type="checkbox" id="use-pms-css" name="pms_settings[general][use_pms_css]" value="1" <?php echo ( isset( $this->options['general']['use_pms_css'] ) ? 'checked' : '' ); ?> /><?php echo __( 'Use Paid Member Subscriptions\'s own CSS in the front-end.', 'pmstxt' ); ?></p>
                </div>

                <div class="pms-form-field-wrapper">
                    <label class="pms-form-field-label" for="register-success-page"><?php echo __( 'Register Success Page', 'pmstxt' ) ?></label>

                    <select id="register-success-page" name="pms_settings[general][register_success_page]" class="widefat">
                        <option value="-1"><?php echo __( 'Choose...', 'paid-member-subscriptions' ) ?></option>

                        <?php
                        foreach( get_pages() as $page ) {
                            echo '<option value="' . $page->ID . '"' . ( isset( $this->options['general']['register_success_page'] ) ? selected( $this->options['general']['register_success_page'], $page->ID, false ) : '') . '>' . $page->post_title . ' ( ID: ' . $page->ID . ')' . '</option>';
                        }
                        ?>
                    </select>

                    <p class="description"><?php echo __( 'Select the page where you wish to redirect your newly registered members.', 'pmstxt' ); ?></p>
                </div>

                <?php do_action( $this->menu_slug . '_tab_general_after_content', $this->options ); ?>

            </div><!-- End of Messages Tab -->


            <!-- Payments Tab -->
            <div id="pms-settings-payments" class="pms-tab <?php echo ( $active_tab == 'payments' ? 'tab-active' : '' ); ?>">

                <?php do_action( $this->menu_slug . '_tab_payments_before_content', $this->options ); ?>


                <div id="payments-general">

                    <h3><?php echo __( 'General', 'paid-member-subscriptions' ); ?></h3>

                    <div class="pms-form-field-wrapper">
                        <label class="pms-form-field-label" for="payment-currency"><?php echo __( 'Currency', 'paid-member-subscriptions' ) ?></label>

                        <select id="payment-currency" name="pms_settings[payments][currency]">
                            <?php
                            foreach( pms_get_currencies() as $currency_code => $currency ) {
                                echo '<option value="' . $currency_code . '"' . ( isset( $this->options['payments']['currency'] ) ? selected( $this->options['payments']['currency'], $currency_code, false ) : '') . '>' . $currency . '</option>';
                            }
                            ?>
                        </select>

                        <p class="description"><?php echo __( 'Select your currency. Please note that some payment gateways can have currency restrictions.', 'paid-member-subscriptions' ); ?></p>
                    </div>

                    <div class="pms-form-field-wrapper">
                        <label class="pms-form-field-label" for="payment-test-mode"><?php echo __( 'Test Mode', 'paid-member-subscriptions' ) ?></label>

                        <p class="description"><input type="checkbox" id="payment-test-mode" name="pms_settings[payments][test_mode]" value="1" <?php echo ( isset( $this->options['payments']['test_mode'] ) ? 'checked' : '' ); ?> /><?php echo __( 'By checking this option you will be able to use Paid Member Subscriptions with test accounts from your payment processors.', 'paid-member-subscriptions' ); ?></p>
                    </div>

                    <?php
                    $payment_gateways = pms_get_payment_gateways();

                    if( count( $payment_gateways ) > 1 ) {

                        // Checkboxes to select active Payment Gateways
                        echo '<div class="pms-form-field-wrapper pms-form-field-active-payment-gateways">';
                            echo '<label class="pms-form-field-label">' . __( 'Active Payment Gateways', 'paid-member-subscriptions' ) . '</label>';

                            foreach( $payment_gateways as $payment_gateway_slug => $payment_gateways_details ) {
                                echo '<label>';
                                echo '<input type="checkbox" name="pms_settings[payments][active_pay_gates][]" value="' . $payment_gateway_slug . '" ' . ( in_array( $payment_gateway_slug, $this->options['payments']['active_pay_gates'] ) ? 'checked="checked"' : '' ) . '/>';
                                echo $payment_gateways_details['display_name_admin'];
                                echo '</label><br />';
                            }
                        echo '</div>';

                        // Select the default active Payment Gateway
                        echo '<div class="pms-form-field-wrapper">';

                            echo '<label class="pms-form-field-label" for="default-payment-gateway">' . __( 'Default Payment Gateway', 'paid-member-subscriptions' ) . '</label>';

                            echo '<select id="default-payment-gateway" name="pms_settings[payments][default_payment_gateway]">';
                                foreach( $payment_gateways as $payment_gateway_slug => $payment_gateways_details ) {
                                    echo '<option value="' . $payment_gateway_slug . '" ' . selected( $this->options['payments']['default_payment_gateway'], $payment_gateway_slug, false ) . '>' . $payment_gateways_details['display_name_admin'] . '</option>';
                                }
                            echo '</select>';

                        echo '</div>';

                    }

                    ?>

                    <?php do_action( $this->menu_slug . '_payment_general_after_content', $this->options ); ?>

                </div>


                <div id="pms-settings-payment-gateways">

                    <h3><?php echo __( 'Payment Gateways', 'paid-member-subscriptions' ); ?></h3>

                    <?php do_action( $this->menu_slug . '_payment_gateways_content', $this->options ); ?>

                    <?php do_action( $this->menu_slug . '_payment_gateways_after_content', $this->options ); ?>

                </div>

                <?php do_action( $this->menu_slug . '_tab_payments_after_content', $this->options ); ?>

            </div><!-- End of Payments Tab -->


            <!-- Messages Tab -->
            <div id="pms-settings-messages" class="pms-tab <?php echo ( $active_tab == 'messages' ? 'tab-active' : '' ); ?>">

                <?php do_action( $this->menu_slug . '_tab_messages_before_content', $this->options ); ?>

                <h3><?php _e( 'Messages for logged-out users', 'paid-member-subscriptions' ); ?></h3>
                <?php wp_editor( pms_get_restriction_content_message( 'logged_out' ), 'messages_logged_out', array( 'textarea_name' => $this->settings_slug . '[messages][logged_out]', 'editor_height' => 250 ) ); ?>

                <h3><?php _e( 'Messages for logged-in non-member users', 'paid-member-subscriptions' ); ?></h3>
                <?php wp_editor( pms_get_restriction_content_message( 'non_members' ), 'messages_non_members', array( 'textarea_name' => $this->settings_slug . '[messages][non_members]', 'editor_height' => 250 ) ); ?>


                <?php do_action( $this->menu_slug . '_tab_messages_after_content', $this->options ); ?>

            </div><!-- End of Messages Tab -->


            <!-- Emails Tab -->
            <div id="pms-settings-emails" class="pms-tab <?php echo ( $active_tab == 'emails' ? 'tab-active' : '' ); ?>">

                <?php do_action( $this->menu_slug . '_tab_emails_before_content', $this->options ); ?>

	            <?php $available_merge_tags = PMS_Merge_Tags::get_merge_tags(); ?>
	            <div id="pms-available-tags">
		            <h3><?php _e('Available Tags', 'pmstext') ?></h3>
		            <?php foreach( $available_merge_tags as $available_merge_tag ):?>
			            <input readonly="" spellcheck="false" type="text" class="pms-tag input" value="{{<?php echo $available_merge_tag; ?>}}">
		            <?php endforeach; ?>
				</div>

                <?php $email_general_options = PMS_Emails::get_email_general_options(); ?>
                <h3><?php _e( 'General Email Options', 'paid-member-subscriptions' ); ?></h3>
                <div class="pms-form-field-wrapper">
                    <label class="pms-form-field-label" for="email-from-name"><?php echo __( 'From Name', 'paid-member-subscriptions' ) ?></label>
                    <input type="text" id="email-from-name" class="widefat" name="<?php echo $this->settings_slug ?>[emails][email-from-name]" value="<?php echo ( isset($this->options['emails']['email-from-name']) ? $this->options['emails']['email-from-name'] : $email_general_options['email-from-name'] ) ?>">
                </div>
                <div class="pms-form-field-wrapper">
                    <label class="pms-form-field-label" for="email-from-email"><?php echo __( 'From Email', 'paid-member-subscriptions' ) ?></label>
                    <input type="text" id="email-from-email" class="widefat" name="<?php echo $this->settings_slug ?>[emails][email-from-email]" value="<?php echo ( isset($this->options['emails']['email-from-email']) ? $this->options['emails']['email-from-email'] : $email_general_options['email-from-email'] ) ?>">
                </div>


                <?php $email_actions = PMS_Emails::get_email_actions(); ?>
                <?php $email_headers = PMS_Emails::get_email_headers(); ?>
                <?php $email_subjects = PMS_Emails::get_email_subjects(); ?>
                <?php $email_content = PMS_Emails::get_email_content(); ?>

                <h3><?php echo $email_headers['register']; ?></h3>
	            <?php $wppb_addonOptions = get_option('wppb_module_settings'); ?>
	            <?php if( !( defined( 'PROFILE_BUILDER' ) && PROFILE_BUILDER == 'Profile Builder Pro' && !empty( $wppb_addonOptions['wppb_emailCustomizer'] ) && $wppb_addonOptions['wppb_emailCustomizer'] == 'show' ) ){ ?>
		            <div class="pms-form-field-wrapper">
	                    <label class="pms-form-field-label" for="email-register-subject"><?php echo __( 'Subject', 'paid-member-subscriptions' ) ?></label>
	                    <input type="text" id="email-register-subject" class="widefat" name="<?php echo $this->settings_slug ?>[emails][register_sub_subject]" value="<?php echo ( isset($this->options['emails']['register_sub_subject']) ? $this->options['emails']['register_sub_subject'] : $email_subjects['register'] ) ?>">
	                </div>

	                <div class="pms-form-field-wrapper">
	                    <label class="pms-form-field-label" for="emails_register_sub"><?php echo __( 'Content', 'paid-member-subscriptions' ) ?></label>
	                    <?php wp_editor( ( isset($this->options['emails']['register_sub']) ? $this->options['emails']['register_sub'] : $email_content['register_sub'] ), 'emails_register_sub', array( 'textarea_name' => $this->settings_slug . '[emails][register_sub]', 'editor_height' => 250 ) ); ?>
	                </div>
	            <?php }else{ ?>
		            <?php printf( __( 'You can customize this email with Profile Builder Email Customizer %1$shere%2$s', 'pmstext' ), '<a href="'. admin_url( 'admin.php?page=user-email-customizer' ) .'">', '</a>' )?>
	            <?php } ?>

	            <?php if( ( $key = array_search( 'register', $email_actions)) !== false) unset($email_actions[$key]); ?>
	            <?php foreach( $email_actions as $action ): ?>
		            <h3><?php echo $email_headers[$action]; ?></h3>
		            <div class="pms-form-field-wrapper">
			            <label class="pms-form-field-label" for="email-<?php echo $action ?>-sub-subject"><?php echo __( 'Subject', 'paid-member-subscriptions' ) ?></label>
			            <input type="text" id="email-<?php echo $action ?>-sub-subject" class="widefat" name="<?php echo $this->settings_slug ?>[emails][<?php echo $action ?>_sub_subject]" value="<?php echo ( isset($this->options['emails'][$action.'_sub_subject']) ? $this->options['emails'][$action.'_sub_subject'] : $email_subjects[$action] ) ?>">
		            </div>

		            <div class="pms-form-field-wrapper">
			            <label class="pms-form-field-label" for="emails-<?php echo $action ?>-sub"><?php echo __( 'Content', 'paid-member-subscriptions' ) ?></label>
			            <?php wp_editor( ( isset($this->options['emails'][$action.'_sub']) ? $this->options['emails'][$action.'_sub'] : $email_content[$action] ), 'emails-'. $action .'-sub', array( 'textarea_name' => $this->settings_slug . '[emails]['.$action.'_sub]', 'editor_height' => 250 ) ); ?>
		            </div>
	            <?php endforeach; ?>

                <?php do_action( $this->menu_slug . '_tab_emails_after_content', $this->options ); ?>

            </div><!-- End of Emails Tab -->


            <?php do_action( $this->menu_slug . '_after_tabs', $this->options ); ?>

        </div>

        <?php submit_button( __( 'Save Settings', 'paid-member-subscriptions' ) ); ?>

    </form>

</div>