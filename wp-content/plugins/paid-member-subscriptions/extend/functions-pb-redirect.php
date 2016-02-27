<?php
/*
 * PB redirects
 */

    function pms_pb_payment_redirect_link() {

        if( !isset( $_GET['pmstkn'] ) || !wp_verify_nonce( $_GET['pmstkn'], 'pms_payment_redirect_link') )
            return;

        if (empty($_GET['pms_payment_id']))
            return;

        $payment_id = $_GET['pms_payment_id'];

        $redirect_to    = get_transient( 'pms_pb_pp_redirect_' . $payment_id );
        $redirect_back  = get_transient( 'pms_pb_pp_redirect_back_' . $payment_id );

        $redirect_to_base = explode( '?', $redirect_to );
        $redirect_to_base = $redirect_to_base[0];

        $redirect_to_parts = explode( '&', $redirect_to );
        $redirect_to_args = '';

        $current_part = 1;
        foreach( $redirect_to_parts as $redirect_to_part ) {

            if( strpos( $redirect_to_part, 'return' ) === 0 ) {
                $redirect_to_part = 'return=' . $redirect_back;
            }

            $redirect_to_args .=  $redirect_to_part . ( $current_part != count( $redirect_to_parts ) ? '&' : '' );

            $current_part++;
        }

        $redirect_to_base .= $redirect_to_args;

        header( 'Location:' . $redirect_to_base );
        exit;

    }
    add_action('init', 'pms_pb_payment_redirect_link');


    function pms_pb_before_paypal_redirect( $paypal_link, $gateway_object, $settings ) {

        if( !isset( $gateway_object->payment_id ) )
            return;

        set_transient( 'pms_pb_pp_redirect_' . $gateway_object->payment_id, $paypal_link, DAY_IN_SECONDS );

        // save payment ID as global to use when getting the redirect back link (on 'wppb_register_redirect')
        global $payment_data_id;
        $payment_data_id = $gateway_object->payment_id;

    }
    add_action( 'pms_before_paypal_redirect', 'pms_pb_before_paypal_redirect', 99, 3 );


    function pms_pb_register_redirect_link( $redirect_link ){

        global $payment_data_id;

        if( !isset( $payment_data_id ) )
            return $redirect_link;

        $link ='';
        $redirect_link_parts = explode("'", $redirect_link);

        if ( strpos($redirect_link, '<script>') !== false ) { // happens when login after register is true

            $link = $redirect_link_parts[1];

        } else {

            foreach ($redirect_link_parts as $part) {

                if ( strpos( $part, 'http') !== false ) {

                    $parts = explode( '"', $part );

                    foreach( $parts as $small_part ) {
                        if( strpos( $small_part, 'http' ) === 0 ) {
                            $link = $small_part;
                            break 2;
                        }
                    }
                }
            }

        }

        if ( empty( $redirect_link ) || !empty($link) ) {

            // save in transient
            set_transient('pms_pb_pp_redirect_back_' . $payment_data_id, $link, DAY_IN_SECONDS );

            $redirect_link = '<p class="redirect_message">' . sprintf( __( 'You will soon be redirected to complete the payment. %1$s', 'paid-member-subscriptions' ), '<meta http-equiv="Refresh" content="5;url=' . wp_nonce_url( add_query_arg( array( 'pms_payment_id' => $payment_data_id ), pms_get_current_page_url() ), 'pms_payment_redirect_link', 'pmstkn' ) .  '" />' ) . '</p>';

            return $redirect_link;
        }

        return $redirect_link;

    }
    add_filter( 'wppb_register_redirect', 'pms_pb_register_redirect_link', 100 );