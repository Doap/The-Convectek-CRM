<?php
/*
 * Shortcodes base class
 */

Class PMS_Shortcodes {


    public static function init() {

        $shortcodes = array(
            'pms-register'         => __CLASS__ . '::register_form',
            'pms-subscriptions'    => __CLASS__ . '::subscriptions_form',
            'pms-account'          => __CLASS__ . '::member_account',
            'pms-edit-profile'     => __CLASS__ . '::edit_profile_form',
            'pms-login'            => __CLASS__ . '::login_form',
            'pms-recover-password' => __CLASS__ . '::recover_password_form',
            'pms-restrict'         => __CLASS__ . '::restrict_content'
        );

        foreach( $shortcodes as $shortcode_tag => $shortcode_func ) {
            add_shortcode( $shortcode_tag, $shortcode_func );
        }

        // Extra filters needed in the shortcodes
        add_filter( 'login_form_bottom', array( __CLASS__, 'login_form_bottom' ), 10, 2 );
    }


    /*
     * Register form shortcode
     *
     * @param array $attr       - there are the attributes the back-end user can pass to filter the subscription plans on the
     *                          register page. Usable attributes are as follow:
     *
     * - "subscription_plans"   - a list of subscription plan ids separated by comma that the back-end user wants to display in the front-end.
     *                          - "none" for allowing users to register without selecting any of the active subscription plans
     *                          - if this attribute is not set, all active subscription plans will be returned
     * - "plans_position"       - can have the values "bottom" or "top". Where to display the subscription plans in relation to the register
     *                          fields needed
     * - "selected"             - the id of the subscription plan that should be selected by default when rendering the form
     *
     */
    public static function register_form( $atts ) {

        /*
         * Sanitize attributes
         */
        if( isset( $atts['subscription_plans'] ) )
            $atts['subscription_plans'] = array_map( 'trim', explode(',', $atts['subscription_plans'] ) );
        else
            $atts['subscription_plans'] = array();

        if( isset( $atts['plans_position'] ) )
            ( empty( $atts['plans_position'] ) ? $atts['plans_position'] = 'bottom' : trim( $atts['plans_position'] ) );
        else
            $atts['plans_position'] = 'bottom';


        if ( ( !empty($atts['subscription_plans']) ) && ( strtolower( $atts['subscription_plans'][0] ) == 'none' ) )
            pms_errors()->remove('subscription_plans');

        /*
         * Detect if all went well on a registration and display a message to the user
         */
        if( isset( $_POST['pms_register'] ) && count( pms_errors()->get_error_codes() ) == 0 ) {

            return apply_filters( 'pms_register_success_message', '<p>' . __( 'Congratulations, your account has been successfully created.', 'paid-member-subscriptions' ) . '</p>' );

        }


        /*
         * Display the register form
         */
        $users_can_register = get_option( 'users_can_register' );

        // Start catching the contents of the register form
        ob_start();

        // Display any success message that exists
        if( pms_success()->get_message( 'subscription_plans' ) ) {

            pms_display_success_messages(pms_success()->get_messages('subscription_plans'));

        }


        if( is_user_logged_in() ) {

            if( !$users_can_register ) {

                echo '<p>' . __( 'Only an administrator can add new users.', 'paid-member-subscriptions' ) . '</p>';

                if( current_user_can( apply_filters( 'pms_register_form_display_current_user_capability', 'create_users' ) ) ) {
                    include_once 'views/shortcodes/view-shortcode-register-form.php';
                }

            } else {

                echo apply_filters( 'pms_register_form_already_a_user_message', '<p>' . __( 'You already have an account.', 'paid-member-subscriptions') . '</p>', $atts );

            }


        } else {

            if( !$users_can_register ) {
                echo '<p>' . __( 'Only an administrator can add new users.', 'paid-member-subscriptions' ) . '</p>';
            } else {

                if( !pms_success()->get_message( 'subscription_plans' ) )
                    include_once 'views/shortcodes/view-shortcode-register-form.php';

            }

        }

        // Get the contents and clean the buffer
        $output = ob_get_contents();
        ob_end_clean();

        return $output;

    }


    /*
     * Shortcode to output subscription plans form and allow users to subscribe to new subscriptions
     *
     */
    public static function subscriptions_form( $atts ) {

        /*
         * Sanitize attributes
         */
        if( isset( $atts['subscription_plans'] ) )
            $atts['subscription_plans'] = array_map( 'trim', explode(',', $atts['subscription_plans'] ) );
        else
            $atts['subscription_plans'] = array();

        $atts['exclude'] = array();

        // Start catching the contents of the subscriptions form
        ob_start();

        if( is_user_logged_in() ) {

            $member = pms_get_member( pms_get_current_user_id() );

            // Exclude subscription
            if( $member->get_subscriptions_count() > 0 ) {
                foreach( $member->subscriptions as $member_subscription )
                    array_push( $atts['exclude'], $member_subscription['subscription_plan_id'] );
            }

            if( $member->is_member() ) {

                echo apply_filters( 'pms_subscriptions_form_already_a_member', do_shortcode( '[pms-account show_edit_profile="no"]' ), $atts, $member );

            } else {

                include_once 'views/shortcodes/view-shortcode-new-subscription-form.php';

            }


        } else {

            echo apply_filters( 'pms_subscriptions_form_not_logged_in_message', __( 'Only registered users can see this information.', 'paid-member-subscriptions' ) );

        }

        // Get the contents and clean the buffer
        $output = ob_get_contents();
        ob_end_clean();

        return $output;

    }


    /*
     * Member account shortcode
     *
     * @param array $atts      - there are the attributes the back-end user can pass to the account shortcode
     *                          Usable attributes are as follow:
     *
     * - "login_url"           - a url where the user should be redirected to log in. It will be displayed at the end of the text: "You must be logged in to view this information".
     *
     */
    public static function member_account( $atts ) {

        // Get atts and set them
        if( !empty( $atts['show_edit_profile'] ) && $atts['show_edit_profile'] == 'no' )
            $atts['show_edit_profile'] = false;
        else
            $atts['show_edit_profile'] = true;


        // Get current user id
        $user_id = pms_get_current_user_id();

        // If no user is found display a message and return
        if( $user_id === 0 ) {

            if ( !empty($atts['login_url']) ) {
                $atts['login_url'] = pms_add_missing_http( $atts['login_url'] );
                return apply_filters('pms_member_account_not_logged_in', '<p>' . sprintf( __('You must be logged in to view this information. %1$sLog in%2$s', 'paid-member-subscriptions'), '<a href="'.$atts["login_url"].'">' , '</a>') . '</p>', $atts);
            }
            else
                return apply_filters( 'pms_member_account_not_logged_in', '<p>' . __( 'You must be logged in to view this information.', 'paid-member-subscriptions' ) . '</p>' , $atts);

        }

        // Get member
        $member = pms_get_member( $user_id );
        $output = '';

        // Add subscription errors
        $output .= pms_display_success_messages( pms_success()->get_messages('subscription_plans'), true );

        // If not a member display a message and return
        if( !$member->is_member() ) {
            $output .= apply_filters( 'pms_member_account_not_member', '<p>' . __( 'You do not have any subscriptions attached to your account.', 'paid-member-subscriptions' ) . '</p>', $member );
        } else {

            // Add member subscription plan ids to an array
            $member_subscriptions = $member->get_subscriptions_ids();

            // Output subscription plans for current member
            $output .= pms_output_subscription_plans( $member_subscriptions, array(), $member );

        }

        // Output the edit profile
        if( $atts['show_edit_profile'] )
            $output .= do_shortcode( PMS_Shortcodes::edit_profile_form() );

        return $output;

    }


    /*
     * Member edit profile form
     *
     */
    public static function edit_profile_form() {

        // Get current user id
        $user_id = pms_get_current_user_id();

        // If no user is found display a message and return
        if( $user_id === 0 ) {
            return apply_filters( 'pms_member_edit_profile_form_not_logged_in', '<p>' . __( 'You must be logged in to view this information.', 'paid-member-subscriptions' ) . '</p>' );
        }

        // Start catching the contents of the register form
        ob_start();

        include_once 'views/shortcodes/view-shortcode-edit-profile-form.php';

        // Get the contents and clean the buffer
        $output = ob_get_contents();
        ob_end_clean();

        return $output;

    }


    /*
     * Front-end login form
     *
     * @param array $atts       - there are the attributes the back-end user can set. Usable attributes are as follow:
     *
     * - "redirect_url"         - a url where the logged in user should be redirected to. If this value is not set the user will be redirected to
     *                          the current page
     * - "lostpassword_url"     - if lostpassword_url argument is set, give the lost password error link that value and place a "Lost your password?" link below the login form
     *
     * - "register_url"         - place a "Register" link below the login form
     *
     */
    public static function login_form( $atts ) {

        $output = '';

        if( !is_user_logged_in() ) {

            // Set up arguments for
            $args = array( 'echo' => false, 'form_id' => 'pms_login' );
            ( !empty($atts['redirect_url']) ? $args['redirect'] = pms_add_missing_http( $atts['redirect_url'] ) : '' );
            ( !empty($atts['register_url']) ? $args['register'] = pms_add_missing_http( $atts['register_url'] ) : '' );
            ( !empty($atts['lostpassword_url']) ? $args['lostpassword'] = pms_add_missing_http( $atts['lostpassword_url'] ) : '' );

            // Get login error
            $login_error = ( isset( $_GET['login_error'] ) ? base64_decode( $_GET['login_error'] ) : '' );

            if( !empty($login_error) ) {
                if ( !empty($args['lostpassword']) )  // replace the lost password error link with the "lostpassword_url" value from the shortcode
                    $login_error = str_replace(site_url('/wp-login.php?action=lostpassword'), esc_url($args['lostpassword']), $login_error);

                $output .= '<p class="pms-login-error">' . $login_error . '</p>';
            }

            $output .= wp_login_form( apply_filters( 'pms_login_form_args', $args ) );

        } else {

            $user = get_userdata( get_current_user_id() );

            $logout_url = '<a href="' . wp_logout_url( pms_get_current_page_url() ) . '" title="' . __( 'Log out of this account', 'paid-member-subscriptions' ) . '">' . __( 'Log out', 'paid-member-subscriptions' ) . '</a>';

            $output .= apply_filters( 'pms_login_form_logged_in_message', '<p class="pms-alert">' . sprintf( __( 'You are currently logged in as %1$s. %2$s', 'paid-member-subscriptions' ), $user->display_name, $logout_url ) . '</p>', $user->ID, $user->display_name );

        }

        return $output;

    }

    /*
     * Add extra fields at the bottom of the login form
     *
     */
    public static function login_form_bottom( $string, $args ) {

        $string .= '<input type="hidden" name="pms_login" value="1" />';
        $string .= '<input type="hidden" name="pms_redirect" value="' . pms_get_current_page_url() . '" />';

        // Add "Register" and "Lost your password" links below the form is shortcode arguments exist
        $i = 0;
        if ( !empty($args['register']) ) {
            $string .= '<a href="' . esc_url($args['register']) . '">' . apply_filters('pms_login_register_text', __('Register', 'paid-member-subscriptions')) . '</a>';
            $i++;
        }
        if ( !empty($args['lostpassword']) ) {
            if ($i != 0) $string .= ' | ';
            $string .= '<a href="' . esc_url($args['lostpassword']) . '">' . apply_filters('pms_login_lostpass_text', __('Lost your password?', 'paid-member-subscriptions')) . '</a>';
        }

        return $string;
    }


    /**
     * Recover Password shortcode
     * @param $atts shortcode attributes
     *
     * - "redirect_url"         - a url where the user should be redirected to after successful password reset. If this value is not set the user will no redirect.
     *
     */
    public static function recover_password_form( $atts ){


        // If entered username or email is valid, display a message to the user and email confirmation link
        if( isset( $_POST['pms_username_email'] ) && count( pms_errors()->get_error_codes() ) == 0 ) {
            return apply_filters( 'pms_recover_password_confirmation_link_message', '<p>' . __( 'Please check your email for the confirmation link.', 'paid-member-subscriptions' ) . '</p>' );
        }


         // Do not display the recover password form if user is logged in, display already logged in message
        if ( is_user_logged_in() ) {

            $member = pms_get_member( get_current_user_id() );
            echo( apply_filters ('pms_recover_password_form_logged_in_message', '<p>' .  __( 'You are already logged in.', 'paid-member-subscriptions' ) . '</p>', $atts, $member) );

        } else {

            if ( isset($_GET['loginName']) && isset($_GET['key']) ) {
                // The user clicked the email confirmation link
                if ( !empty($_POST['pms_new_password']) && !empty($_POST['pms_repeat_password']) && ( count( pms_errors()->get_error_codes() ) == 0 )) {

                    // The new password form was submitted with no errors
                    echo(apply_filters('pms_recover_password_form_password_changed_message', '<p>' . __('Your password was successfully changed!', 'paid-member-subscriptions') . '</p>'));

                    if ( !empty($atts['redirect_url']) ) {// "redirect_url" shortcode parameter is set
                        $redirect_url = pms_add_missing_http( $atts['redirect_url'] );

                        $redirect_message = apply_filters( 'pms_recover_pass_redirect_message', __('You will soon be redirected automatically.', 'paid-member-subscriptions') );
                        echo '<p class="pms_redirect_message">'. $redirect_message . '</p>' . '<meta http-equiv="Refresh" content="3;url=' . $redirect_url . '" />';
                    }

                }

                else{
                    $user = get_user_by('login', $_GET['loginName']);
                    if ( is_object($user) && ($user->user_activation_key == $_GET['key']) )
                        // Display the new password form
                        include_once 'views/shortcodes/view-shortcode-new-password-form.php';
                    else
                        // Confirmation link has expired or activation key invalid
                        echo( apply_filters ('pms_recover_password_form_invalid_key_message', '<p>' .  __( 'The confirmation link has expired. Invalid key.', 'paid-member-subscriptions' ) . '</p>') );
                    }

            }
            else
                // display the standard recover password form
                include_once 'views/shortcodes/view-shortcode-recover-password-form.php';

        }

    }


    /**
     * Restrict content shortcode
     * @param $atts shortcode attributes
     *   - subscription_plans: list of subscription plans separated by comma. if it is not defined then we only check if user is logged in
     *   - messsage: the message that will be displayed instead of the content
     */
    public static function restrict_content( $atts, $content = null ){
         $args = shortcode_atts( array(
             'subscription_plans' => array(),
             'display_to'         => '',
             'message'            => __( 'You do not have permission to view this content', 'paid-member-subscriptions' )
         ), $atts );

        // Message to replace the content of checks do not match
        if( !empty( $args['message'] ) )
            $message = apply_filters( 'pms_restrict_content_message', '<p>' . $args['message'] . '</p>', $args );
        else
            $message = '';

        if( is_user_logged_in() ) {

            // Show for administrators
            if( current_user_can( 'manage_options' ) )
                return do_shortcode( $content );

            if( $args['display_to'] == 'not_logged_in' )
                return $message;


            if( !empty( $args['subscription_plans'] ) ) {
                $subscription_plans = array_map('trim', explode( ',', $args['subscription_plans'] ) );
                $member = pms_get_member( get_current_user_id() );
                $member_subscriptions = array();
                if( !empty( $member->subscriptions ) ){
                    foreach( $member->subscriptions as $subscription ){
                        if( !empty( $subscription['status'] )  && ( $subscription['status'] == 'active' || $subscription['status'] == 'canceled' ) && time() <= strtotime( $subscription['expiration_date'] ) ) {
                            $member_subscriptions[] = $subscription['subscription_plan_id'];
                        }
                    }

                    $common_subscriptions = array_intersect( $subscription_plans, $member_subscriptions );
                    if( !empty( $common_subscriptions ) ){
                        return do_shortcode( $content );
                    }
                    else{
                        return $message;
                    }
                }
            }
            else
                return do_shortcode( $content );
        } else {

            if( $args['display_to'] == 'not_logged_in' )
                return do_shortcode( $content );
            else
                return $message;

        }

    }

}