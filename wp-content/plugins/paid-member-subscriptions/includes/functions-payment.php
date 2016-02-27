<?php

/*
 * Functions for payment things
 *
 */


    /*
     * Wrapper function to return a payment object
     *
     */
    function pms_get_payment( $id = 0 ) {
        return new PMS_Payment( $id );
    }


    /*
     * Return payments filterable by an array of arguments
     *
     * @param array $args
     *
     * @return array
     *
     */
    function pms_get_payments( $args = array() ) {

        global $wpdb;

        $defaults = array(
            'order'         => 'ASC',
            'orderby'       => '',
            'search'        => '',
            'type'          => '',
            'profile_id'    => ''
        );

        $args = wp_parse_args( $args, $defaults );

        // Start query string
        $query_string       = "SELECT pms_payments.id ";

        // Query string sections
        $query_from         = "FROM {$wpdb->prefix}pms_payments pms_payments ";
        $query_inner_join   = "INNER JOIN {$wpdb->users} users ON pms_payments.user_id = users.id ";
        $query_inner_join   = $query_inner_join . "INNER JOIN {$wpdb->posts} posts ON pms_payments.subscription_plan_id = posts.id ";
        $query_where        = "WHERE 1=%d ";

        // Add search query
        if( !empty($args['search']) ) {
            $search_term = $args['search'];
            $query_where    = $query_where . " AND " . "  pms_payments.transaction_ID LIKE '%s' OR users.user_nicename LIKE '%%%s%%' OR posts.post_title LIKE '%%%s%%'  ". " ";
        }

        // Filter by type
        if( !empty( $args['type'] ) ) {
            $type = $args['type'];
            $query_where    = $query_where . " AND " . " pms_payments.type LIKE '{$type}'";
        }

        // Filter by profile_id
        if( !empty( $args['profile_id'] ) ) {
            $profile_id = $args['profile_id'];
            $query_where    = $query_where . " AND " . " pms_payments.profile_id LIKE '{$profile_id}'";
        }

        $query_order_by = '';
        if ( !empty($args['orderby']) ) {
            $query_order_by = "ORDER BY pms_payments." . $args['orderby'] . ' ';
        }

        // Concatenate query string
        $query_string .= $query_from . $query_inner_join . $query_where . $query_order_by;


        // Return results
        if (!empty($search_term))
            $ids = $wpdb->get_results( $wpdb->prepare( $query_string, 1, $wpdb->esc_like( $search_term ) , $wpdb->esc_like( $search_term ), $wpdb->esc_like( $search_term ) ), ARRAY_N );
        else
            $ids = $wpdb->get_results( $wpdb->prepare( $query_string, 1 ), ARRAY_N );

        $payments = array();

        foreach( $ids as $key => $id ) {
            $payments[] = pms_get_payment( $id[0] );
        }

        return $payments;

    }


    /*
     * Function that returns all possible payment statuses
     *
     * @return array
     *
     */
    function pms_get_payment_statuses() {

        return apply_filters( 'pms_payment_statuses', array(
            'pending'   => __( 'Pending', 'paid-member-subscriptions' ),
            'completed' => __( 'Completed', 'paid-member-subscriptions' ),
            'rejected'  => __( 'Rejected', 'paid-member-subscriptions' )
        ));

    }


    /*
     * Returns an array with the payment types supported
     *
     * @return array
     *
     */
    function pms_get_payment_types() {

        return apply_filters( 'pms_payment_types', array(
            'web_accept_paypal_standard' => __( 'PayPal Standard - One-Time Payment', 'paid-member-subscriptions' )
        ));

    }


    /*
     * Returns true if the test mode is checked in the payments settings page
     * and false if it is not checked
     *
     * @return bool
     *
     */
    function pms_is_payment_test_mode() {

        $pms_settings = get_option('pms_settings');

        if( isset( $pms_settings['payments']['test_mode'] ) && $pms_settings['payments']['test_mode'] == 1 )
            return true;
        else
            return false;

    }


    /*
     * Returns the name of the payment type given its slug
     *
     * @param string $payment_type_slug
     *
     * @return string
     *
     */
    function pms_get_payment_type_name( $payment_type_slug ) {

        $payment_types = pms_get_payment_types();

        if( isset( $payment_types[$payment_type_slug] ) )
            return $payment_types[$payment_type_slug];
        else
            return '';

    }


    /*
     * Function that outputs the payment gateway options
     *
     * @param array $pms_settings     - the saved settings
     *
     * @return string
     *
     */
    function pms_get_output_payment_gateways( $pms_settings = array() ) {

        if( empty($pms_settings) )
            $pms_settings = get_option( 'pms_settings' );

        $output = '';

        // Output gateways only when we have active subscription plans
        $active_subscriptions = pms_get_subscription_plans();
        if ( empty($active_subscriptions) ) {
            return $output;
        }

        // If there's only one payment gateway saved
        if( count( $pms_settings['payments']['active_pay_gates'] ) == 1 ) {

            $output .= apply_filters( 'pms_output_payment_gateway_input_hidden', '<input type="hidden" name="pay_gate" value="' . ( $pms_settings['payments']['active_pay_gates'][0] != 'paypal_standard' ? $pms_settings['payments']['active_pay_gates'][0] : 'paypal_standard' ) . '" />', $pms_settings['payments']['active_pay_gates'][0] );

        } else {

            $payment_gateways = pms_get_payment_gateways();

            // Set default payment gateway
            $default_gateway  = ( !empty( $pms_settings['payments']['default_payment_gateway'] ) ? ( in_array( $pms_settings['payments']['default_payment_gateway'], $pms_settings['payments']['active_pay_gates'] ) ? $pms_settings['payments']['default_payment_gateway'] : $pms_settings['payments']['active_pay_gates'][0] ) : 'paypal_standard' );
            $default_gateway  = ( !empty( $_POST['pay_gate'] ) ? esc_attr( $_POST['pay_gate'] ) : $default_gateway );

            // Output content for all payment gateways
            $output .= '<div id="pms-paygates-wrapper">';

                $output .= apply_filters( 'pms_get_output_payment_gateways_before', '<h3>' . __( 'Select a Payment Method', 'paid-member-subscriptions' ) . '</h3>', $pms_settings );

                foreach( $pms_settings['payments']['active_pay_gates'] as $paygate_key ) {

                    $output .= '<label>';
                        $output .= apply_filters( 'pms_output_payment_gateway_input_radio', '<input type="radio" name="pay_gate" value="' . $paygate_key . '" ' . checked( $default_gateway, $paygate_key, false ) . ' />', $paygate_key );
                        $output .= '<span class="pms-paygate-name">' . $payment_gateways[$paygate_key]['display_name_user'] . '</span>';
                    $output .= '</label>';

                }
            $output .= '</div>';

        }

        return apply_filters( 'pms_get_output_payment_gateways', $output, $pms_settings );

    }

    /*
     * Function that outputs the payment gateway options after the subscription plans
     * radio buttons
     *
     * @return string
     *
     */
    function pms_output_subscription_plans_payment_gateways( $output, $include, $exclude_id_group, $member, $pms_settings ) {

        if( is_object( $member ) )
            return $output;

        $output .= pms_get_output_payment_gateways( $pms_settings );

        return $output;

    }
    add_filter( 'pms_output_subscription_plans', 'pms_output_subscription_plans_payment_gateways', 10, 5);