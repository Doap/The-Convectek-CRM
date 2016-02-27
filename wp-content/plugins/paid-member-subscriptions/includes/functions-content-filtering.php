<?php

    function pms_get_restriction_content_message( $type ){

        $settings = get_option('pms_settings');
        $message = '';

        if( $type == 'logged_out' ){
            $message = isset( $settings['messages']['logged_out']) ? $settings['messages']['logged_out'] : __( 'You do not have access to this content. You need to create an account.', 'pmstext' );

        }
        elseif(  $type == 'non_members' ){
            $message = isset( $settings['messages']['non_members']) ? $settings['messages']['non_members'] : __( 'You do not have access to this content. You need the proper subscription.', 'pmstext' );
        }

        return $message;
    }

    function pms_process_restriction_content_message( $type, $user_ID ){
        $message = pms_get_restriction_content_message( $type );
        $user_info = get_userdata( $user_ID );
        $message = PMS_Merge_Tags::pms_process_merge_tags( $message, $user_info, '' );
        return $message;
    }


    /*
     * Hijack the content when restrictions are set on a single post
     *
     */
    function pms_filter_content( $content ) {

        global $post, $user_ID, $pms_already_filtered;

        /* show for administrators, this propagets to the global content restriction addon */
        if( current_user_can( 'manage_options' ) ){
            $pms_already_filtered = true;
            return $content;
        }

        // Get subscription plans that have access to this post
        $user_status = get_post_meta( $post->ID, 'pms-content-restrict-user-status', true );
        $post_subscription_plans = get_post_meta( $post->ID, 'pms-content-restrict-subscription-plan' );

        if( empty( $user_status ) ){
            return $content;
        }
        else if( $user_status = 'loggedin' ){
            if( is_user_logged_in() ) {
                if (!empty($post_subscription_plans)) {

                    // Verify if the user is a member and check active subscriptions
                    $member = pms_get_member($user_ID);
                    $user_subscription_plans = $member->get_subscriptions();

                    foreach ($post_subscription_plans as $post_subscription_plan) {
                        if (!empty($user_subscription_plans)) {
                            foreach ($user_subscription_plans as $user_subscription_plan) {
                                if ($post_subscription_plan == $user_subscription_plan['subscription_plan_id'] && ( $user_subscription_plan['status'] == 'active' || $user_subscription_plan['status'] == 'canceled' ) && time() <= strtotime( $user_subscription_plan['expiration_date'] ) ) {
                                    $pms_already_filtered = true;
                                    return $content;
                                }
                            }
                        }
                    }

                    $message = pms_process_restriction_content_message('non_members', $user_ID);
                    $pms_already_filtered = true;
                    return do_shortcode(apply_filters('pms_restriction_message_non_members', $message, $content, $post, $user_ID));
                } else {
                    $pms_already_filtered = true;
                    return $content;
                }
            }
            else{
                // If user is not logged in prompt the correct message
                $message = pms_process_restriction_content_message('logged_out', $user_ID);
                $pms_already_filtered = true;
                return do_shortcode(apply_filters('pms_restriction_message_logged_out', $message, $content, $post, $user_ID));
            }
        }

        return $content;

    }
    add_filter( 'the_content', 'pms_filter_content', 990 );


    /*
     * Hijack the content when a member wants to upgrade to a higher subscription plan
     *
     */
    function pms_member_upgrade_subscription( $content ) {

        // Do nothing if we cannot validate the nonce
        if( !isset( $_REQUEST['pmstkn'] ) || !( wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_member_nonce' ) || wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_upgrade_subscription' ) ) )
            return $content;

        $user_id = pms_get_current_user_id();
        $member  = pms_get_member( $user_id );

        // Do nothing if the user is not a member
        if( !$member->is_member() )
            return $content;

        if( !isset( $_REQUEST['pms-action'] ) || ($_REQUEST['pms-action'] != 'upgrade_subscription') || !isset( $_REQUEST['subscription_plan'] ) )
            return $content;

        if( !in_array( trim( $_REQUEST['subscription_plan'] ), $member->get_subscriptions_ids() ) )
            return $content;

        // If we don't have any upgrades available, return the content
        $subscription_plan_upgrades = pms_get_subscription_plan_upgrades( trim( $_REQUEST['subscription_plan'] ) );
        if( empty( $subscription_plan_upgrades ) )
            return $content;

        $subscription_plan = pms_get_subscription_plan( trim( $_REQUEST['subscription_plan'] ) );

        // Output form
        $output= '<form action="" method="POST" class="pms-form">';

            // Do actions at the top of the form
            ob_start();

            do_action('pms_upgrade_subscription_form_top');

            $output .= ob_get_contents();
            ob_end_clean();

            // Output tagline
            $output .= apply_filters( 'pms_upgrade_subscription_before_form', '<p>' . sprintf( __( 'Upgrade %s to:', 'paid-member-subscriptions' ), $subscription_plan->name ) . '</p>', $subscription_plan, $member );

            // Output subscription plans
            $output .= pms_output_subscription_plans( $subscription_plan_upgrades );

            // Used to output the Billing Information and Credit Card form
            ob_start();

            do_action('pms_upgrade_subscription_form_bottom');

            $output .= ob_get_contents();

            ob_end_clean();

            // Output nonce field
            $output .= wp_nonce_field( 'pms_upgrade_subscription', 'pmstkn' );

            // Output submit button
            $output .= '<input type="submit" name="pms_upgrade_subscription" value="' . apply_filters( 'pms_upgrade_subscription_button_value', __( 'Upgrade Subscription', 'paid-member-subscriptions' ) ) . '" />';
            $output .= '<input type="submit" name="pms_redirect_back" value="' . apply_filters( 'pms_upgrade_subscription_go_back_button_value', __( 'Go back', 'paid-member-subscriptions' ) ) . '" />';

        $output .= '</form>';

        return apply_filters( 'pms_the_content_member_upgrade_subscription', $output, $content, $user_id, $subscription_plan );

    }
    add_filter( 'the_content', 'pms_member_upgrade_subscription', 998 );


    /*
     * Hijack the content when a member wants to renew a subscription plan
     *
     */
    function pms_member_renew_subscription( $content ) {

        // Verify nonce
        if( !isset( $_REQUEST['pmstkn'] ) || !( wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_member_nonce' ) || wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_renew_subscription' ) ) )
            return $content;

        $user_id = pms_get_current_user_id();
        $member  = pms_get_member( $user_id );

        // Do nothing if the user is not a member
        if( !$member->is_member() )
            return $content;

        if( !isset( $_REQUEST['pms-action'] ) || ($_REQUEST['pms-action'] != 'renew_subscription') || !isset( $_REQUEST['subscription_plan'] ) )
            return $content;

        if( !in_array( trim( $_REQUEST['subscription_plan'] ), $member->get_subscriptions_ids() ) )
            return $content;

        // Get subscription plan and member subscription
        $subscription_plan       = pms_get_subscription_plan( trim( $_REQUEST['subscription_plan'] ) );
        $member_subscription     = $member->get_subscription( $subscription_plan->id );

        // If member subscription is not in renewal period,
        // return the default content
        $renewal_display_time = apply_filters( 'pms_output_subscription_plan_action_renewal_time', 15 );
        if( strtotime( $member_subscription['expiration_date'] ) - time() > $renewal_display_time * 86400 )
            return $content;


        if( $subscription_plan->duration !== 0 ) {

            if( time() > strtotime( $member_subscription['expiration_date'] ) )
                $renew_expiration_date = date( 'j F, Y', strtotime( '+' . $subscription_plan->duration . ' ' . $subscription_plan->duration_unit, time() ) );
            else
                $renew_expiration_date = date( 'j F, Y', strtotime( pms_sanitize_date($member_subscription['expiration_date']) . '+' . $subscription_plan->duration . ' ' . $subscription_plan->duration_unit ) );

        } else
            $renew_expiration_date = date( 'j F, Y', $subscription_plan->get_expiration_date( true ) );

        // Output form
        $output = '<form action="" method="POST" class="pms-form">';

            // Do Actions at the top of the form
            ob_start();

            do_action('pms_renew_subscription_form_top');

            $output .= ob_get_contents();
            ob_end_clean();


            // Output tagline
            $output .= apply_filters( 'pms_renew_subscription_before_form', '<p>' . sprintf( __( 'Renew %s subscription. The subscription will be active until %s', 'paid-member-subscriptions' ), $subscription_plan->name, $renew_expiration_date ) . '</p>', $subscription_plan, $member );

            // Hidden subscription plan field
            $output .= '<input type="hidden" name="subscription_plans" value="' . esc_attr( $subscription_plan->id ) . '" />';

            // Output the payment gateways
            $output .= pms_get_output_payment_gateways();

            // Used to output the Billing Information and Credit Card form
            ob_start();

            do_action('pms_renew_subscription_form_bottom');

            $output .= ob_get_contents();
            ob_end_clean();

            // Output nonce field
            $output .= wp_nonce_field( 'pms_renew_subscription', 'pmstkn' );

            // Output submit button
            $output .= '<input type="submit" name="pms_renew_subscription" value="' . apply_filters( 'pms_renew_subscription_button_value', __( 'Renew Subscription', 'paid-member-subscriptions' ) ) . '" />';
            $output .= '<input type="submit" name="pms_redirect_back" value="' . apply_filters( 'pms_renew_subscription_go_back_button_value', __( 'Go back', 'paid-member-subscriptions' ) ) . '" />';

        $output .= '</form>';

        return apply_filters( 'pms_the_content_member_renew_subscription', $output, $content, $user_id, $subscription_plan );

    }
    add_filter( 'the_content', 'pms_member_renew_subscription', 998 );



    /*
     * Hijack the content when a member wants to cancel a subscription
     *
     */
    function pms_member_cancel_subscription( $content ) {

        // Verify nonce
        if( !isset( $_REQUEST['pmstkn'] ) || !wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_member_nonce' ) )
            return $content;

        $user_id = pms_get_current_user_id();
        $member  = pms_get_member( $user_id );

        // Do nothing if the user is not a member
        if( !$member->is_member() )
            return $content;

        if( !isset( $_REQUEST['pms-action'] ) || ($_REQUEST['pms-action'] != 'cancel_subscription') || !isset( $_REQUEST['subscription_plan'] ) )
            return $content;

        if( !in_array( trim( $_REQUEST['subscription_plan'] ), $member->get_subscriptions_ids() ) )
            return $content;

        // Output form
        $output = '<form action="" method="POST" class="pms-form">';

            // Get subscription plan
            $subscription_plan = pms_get_subscription_plan( trim( $_REQUEST['subscription_plan'] ) );

            $output .= apply_filters( 'pms_cancel_subscription_confirmation_message', '<p>' . sprintf( __( 'Are you sure you want to cancel your %1$s subscription? This action will remove the subscription.', 'paid-member-subscriptions' ) . '</p>', $subscription_plan->name ), $subscription_plan );

            // Hidden subscription plan field
            $output .= '<input type="hidden" name="subscription_plans" value="' . esc_attr( $subscription_plan->id ) . '" />';

            // Output nonce field
            $output .= wp_nonce_field( 'pms_cancel_subscription', 'pmstkn' );

            // Output submit button
            $output .= '<input type="submit" name="pms_confirm_cancel_subscription" value="' . apply_filters( 'pms_cancel_subscription_button_value', __( 'Confirm', 'paid-member-subscriptions' ) ) . '" />';
            $output .= '<input type="submit" name="pms_redirect_back" value="' . apply_filters( 'pms_cancel_subscription_go_back_button_value', __( 'Go back', 'paid-member-subscriptions' ) ) . '" />';


        $output .= '</form>';

        return $output;

    }
    add_filter( 'the_content', 'pms_member_cancel_subscription', 998 );


    /*
     * Hijack the content when a member wants to retry a payment for a pending subscription
     *
     */
    function pms_member_retry_payment_subscription( $content ) {

        // Verify nonce
        if( !isset( $_REQUEST['pmstkn'] ) || !( wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_member_nonce' ) || wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_retry_payment_subscription' ) ) )
            return $content;

        $user_id = pms_get_current_user_id();
        $member  = pms_get_member( $user_id );

        // Do nothing if the user is not a member
        if( !$member->is_member() )
            return $content;

        if( !isset( $_REQUEST['pms-action'] ) || ($_REQUEST['pms-action'] != 'retry_payment_subscription') || !isset( $_REQUEST['subscription_plan'] ) )
            return $content;

        if( !in_array( trim( $_REQUEST['subscription_plan'] ), $member->get_subscriptions_ids() ) )
            return $content;

        // Return if the subscription is not pending
        $member_subscription = $member->get_subscription( trim( $_REQUEST['subscription_plan'] ) );

        if( $member_subscription['status'] != 'pending' )
            return $content;


        // Output form
        $output = '<form action="" method="POST" class="pms-form">';

        // Do Actions at the top of the form
        ob_start();

        do_action('pms_retry_payment_form_top');

        $output .= ob_get_contents();
        ob_end_clean();


        // Get subscription plan
        $subscription_plan = pms_get_subscription_plan( trim( $_REQUEST['subscription_plan'] ) );

        $output .= apply_filters( 'pms_retry_payment_subscription_confirmation_message', '<p>' . sprintf( __( 'Your %s subscription is still pending. Do you wish to retry the payment?', 'paid-member-subscriptions' ) . '</p>', $subscription_plan->name ), $subscription_plan );

        // Hidden subscription plan field
        $output .= '<input type="hidden" name="subscription_plans" value="' . esc_attr( $subscription_plan->id ) . '" />';

        // Output the payment gateways
        $output .= pms_get_output_payment_gateways();

        // Used to output the Billing Information and Credit Card form
        ob_start();

        do_action('pms_retry_payment_form_bottom');

        $output .= ob_get_contents();
        ob_end_clean();


        // Output nonce field
        $output .= wp_nonce_field( 'pms_retry_payment_subscription', 'pmstkn' );

        // Output submit button
        $output .= '<input type="submit" name="pms_confirm_retry_payment_subscription" value="' . apply_filters( 'pms_retry_payment_subscription_button_value', __( 'Retry payment', 'paid-member-subscriptions' ) ) . '" />';
        $output .= '<input type="submit" name="pms_redirect_back" value="' . apply_filters( 'pms_retry_payment_subscription_go_back_button_value', __( 'Go back', 'paid-member-subscriptions' ) ) . '" />';

        $output .= '</form>';

        return $output;

    }
    add_filter( 'the_content', 'pms_member_retry_payment_subscription', 998 );