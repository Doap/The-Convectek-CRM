<?php
/*
 * Payment Gateways base class
 *
 */

Class PMS_Payment_Gateway {

    public $payment_id;
    public $user_id;
    public $user_email;
    public $subscription_plan;
    public $currency;
    public $amount;
    public $sign_up_amount;
    public $recurring;
    public $redirect_url;
    public $form_location;
    public $test_mode;


    public function __construct( $payment_data = array() ) {

        if( !empty( $payment_data ) ) {

            $this->payment_id        = ( isset( $payment_data['payment_id'] ) ? $payment_data['payment_id'] : 0 );
            $this->user_id           = ( isset( $payment_data['user_data']['user_id'] ) ? $payment_data['user_data']['user_id'] : 0 );
            $this->user_email        = ( isset( $payment_data['user_data']['user_email'] ) ? $payment_data['user_data']['user_email'] : '' );
            $this->subscription_plan = ( isset( $payment_data['user_data']['subscription'] ) && is_object( $payment_data['user_data']['subscription'] ) ? $payment_data['user_data']['subscription'] : '' );
            $this->currency          = ( isset( $payment_data['currency'] ) ? $payment_data['currency'] : 'USD' );
            $this->amount            = ( isset( $payment_data['amount'] ) ? $payment_data['amount'] : 0 );
            $this->sign_up_amount    = ( isset( $payment_data['sign_up_amount'] ) ? $payment_data['sign_up_amount'] : NULL );
            $this->recurring         = ( isset( $payment_data['recurring'] ) ? $payment_data['recurring'] : 0 );
            $this->redirect_url      = ( isset( $payment_data['redirect_url'] ) ? $payment_data['redirect_url'] : '' );
            $this->form_location     = ( isset( $payment_data['form_location'] ) ? $payment_data['form_location'] : '' );
            $this->test_mode         = pms_is_payment_test_mode();

        }

        $this->init();

    }

    public function init() {}

    public function process_sign_up() {}

    public function process_webhooks() {}

    public function fields() {}

}