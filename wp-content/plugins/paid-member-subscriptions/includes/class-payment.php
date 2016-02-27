<?php
/*
 * Payment class stores and handles data about a certain payment
 *
 */

Class PMS_Payment {

    public $id;

    public $user_id;

    public $status;

    public $date;

    public $amount;

    public $subscription_id;

    public $ip_address;

    public function __construct( $id = 0 ) {

        // Return if no id provided
        if( $id == 0 ) {
            $this->id = 0;
            return;
        }

        // Get payment data from the db
        $data = $this->get_data( $id );

        // Return if data is not in the db
        if( is_null($data) ) {
            $this->id = 0;
            return;
        }

        // Populate the data
        $this->id              = $id;
        $this->user_id         = ( isset( $data['user_id'] ) ? $data['user_id'] : '' );
        $this->status          = ( isset( $data['status'] ) ? $data['status'] : '' );
        $this->date            = ( isset( $data['date'] ) ? $data['date'] : '' );
        $this->subscription_id = ( isset( $data['subscription_plan_id'] ) ? $data['subscription_plan_id'] : '' );
        $this->amount          = ( isset( $data['amount'] ) ? $data['amount'] : '' );
        $this->type            = ( isset( $data['type'] ) ? $data['type'] : '' );
        $this->transaction_id  = ( isset( $data['transaction_id'] ) ? $data['transaction_id'] : '' );
        $this->profile_id      = ( isset( $data['profile_id'] ) ? $data['profile_id'] : '' );
        $this->ip_address      = ( isset( $data['ip_address'] ) ? $data['ip_address'] : '' );
        $this->logs            = ( isset( $data['logs'] ) ? json_decode( $data['logs'], ARRAY_A ) : '' );

    }


    /*
     * Retrieve the row data for a given id
     *
     * @param int $id   - the id of the payment we wish to get
     *
     * @return array
     *
     */
    public function get_data( $id ) {

        global $wpdb;

        $result = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}pms_payments WHERE id = {$id}", ARRAY_A );

        return $result;

    }


    /*
     * Method to add a new payment in the database
     *
     * @param int $user_id
     * @param string $status
     * @param datetime $date
     * @param int $amount
     * @param array $subscription_plan_ids
     *
     * @return mixed    - int $payment_id or false if the row could not be added
     *
     */
    public function add( $user_id, $status, $date, $amount, $subscription_plan_id ) {

        global $wpdb;

        $insert_result = $wpdb->insert( $wpdb->prefix . 'pms_payments', array( 'user_id' => $user_id, 'status' => $status, 'date' => $date, 'amount' => $amount, 'subscription_plan_id' => $subscription_plan_id, 'ip_address' => pms_get_user_ip_address() ) );

        if( $insert_result ) {
            $payment_id = $wpdb->insert_id;

            $this->id = $payment_id;

            return $payment_id;
        }

        return false;

    }


    /*
     * Add a log entry to the payment
     *
     * @param string $type      - the type of the log
     * @param string $message   - a human readable message
     * @param array $data       - an array of data saved from the payment gateway
     *
     * @return bool
     *
     */
    public function add_log_entry( $type = '', $message = '', $data = array() ) {

        global $wpdb;

        if( empty($type) || empty($message) || empty($data) )
            return false;

        $payment_logs = $wpdb->get_var( "SELECT logs FROM {$wpdb->prefix}pms_payments WHERE id LIKE {$this->id}" );

        if( $payment_logs == null )
            $payment_logs = array();
        else
            $payment_logs = json_decode( $payment_logs );

        $payment_logs[] = array(
            'type'      => $type,
            'message'   => $message,
            'data'      => $data
        );

        $update_result = $wpdb->update( $wpdb->prefix . 'pms_payments', array( 'logs' => json_encode($payment_logs) ), array( 'id' => $this->id ) );

        if( $update_result!== false )
            $update_result = true;

        return $update_result;

    }


    /*
     * Method to update the status of the payment
     *
     * @param string $status    - the new status
     *
     * @return bool
     *
     */
    public function update_status( $status ) {

        global $wpdb;

        $update_result = $wpdb->update( $wpdb->prefix . 'pms_payments', array( 'status' => $status ), array( 'id' => $this->id ) );

        // Can return 0 if no rows are affected
        if( $update_result !== false )
            $update_result = true;

        return $update_result;

    }


    /*
     * Method to update the type of the payment
     *
     * @param string $type    - the new status
     *
     * @return bool
     *
     */
    public function update_type( $type ) {

        global $wpdb;

        $update_result = $wpdb->update( $wpdb->prefix . 'pms_payments', array( 'type' => $type ), array( 'id' => $this->id ) );

        // Can return 0 if no rows are affected
        if( $update_result !== false )
            $update_result = true;

        return $update_result;

    }


    /*
     * Method to update the profile id of the payment
     *
     * @param string $profile_id    - the new status
     *
     * @return bool
     *
     */
    public function update_profile_id( $profile_id ) {

        global $wpdb;

        $update_result = $wpdb->update( $wpdb->prefix . 'pms_payments', array( 'profile_id' => $profile_id ), array( 'id' => $this->id ) );

        // Can return 0 if no rows are affected
        if( $update_result !== false )
            $update_result = true;

        return $update_result;

    }


    /*
     * Method to update any data of the payment
     *
     * @param array $data    - the new data
     *
     * @return bool
     *
     */
    public function update( $data = array() ) {

        global $wpdb;

        $update_result = $wpdb->update( $wpdb->prefix . 'pms_payments', $data, array( 'id' => $this->id ) );

        // Can return 0 if no rows are affected
        if( $update_result !== false )
            $update_result = true;

        return $update_result;

    }


    /*
     * Check to see if payment is saved in the db
     *
     */
    public function is_valid() {

        if( empty($this->id) )
            return false;
        else
            return true;

    }


}