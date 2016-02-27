<?php

// WP_List_Table is not loaded automatically in the plugins section
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/*
 * Extent WP default list table for our custom payments section
 *
 */
Class PMS_Payments_List_Table extends WP_List_Table {

    public $items_per_page;

    public $data;

    public $views_count = array();

    /*
     * Constructor function
     *
     */
    public function __construct() {

        parent::__construct( array(
            'singular'  => 'payment',
            'plural'    => 'payments',
            'ajax'      => false
        ));

        // Set table data
        $this->set_table_data();

        // Set items per page
        $items_per_page = get_user_meta( get_current_user_id(), 'pms_payments_per_page', true );

        if( empty( $items_per_page ) ) {
            $screen     = get_current_screen();
            $per_page   = $screen->get_option('per_page');
            $items_per_page = $per_page['default'];
        }

        $this->items_per_page = $items_per_page;

    }

    /*
     * Overwrites the parent class.
     * Define the columns for the payments
     *
     * @return array
     *
     */
    public function get_columns() {

        $columns = array(
            'id'             => __( 'ID', 'paid-member-subscriptions' ),
            'username'       => __( 'User', 'paid-member-subscriptions' ),
            'subscriptions'  => __( 'Subscription', 'paid-member-subscriptions' ),
            'amount'         => __( 'Amount', 'paid-member-subscriptions' ),
            'date'           => __( 'Date / Time', 'paid-member-subscriptions' ),
            'type'           => __( 'Type', 'paid-member-subscriptions' ),
            'transaction_id' => __( 'Transaction ID', 'paid-member-subscriptions' ),
            'status'         => __( 'Status', 'paid-member-subscriptions' ),
        );

        return apply_filters( 'pms_payments_list_table_columns', $columns );

    }


    /*
     * Overwrites the parent class.
     * Define which columns to hide
     *
     * @return array
     *
     */
    public function get_hidden_columns() {

        return array();

    }


    /*
     * Overwrites the parent class.
     * Define which columns are sortable
     *
     * @return array
     *
     */
    public function get_sortable_columns() {

        return array(
            'username'  => array( 'username', false ),
            'status'    => array( 'status', false )
        );

    }


    /*
     * Returns the possible views for the members list table
     *
     */
    protected function get_views() {

        return apply_filters( 'pms_payments_list_table_get_views', array(
            'all'       => '<a href="' . remove_query_arg( array( 'pms-view', 'paged' ) ) . '" ' . ( !isset( $_GET['pms-view'] ) ? 'class="current"' : '' ) . '>All <span class="count">(' . ( isset( $this->views_count['all'] ) ? $this->views_count['all'] : '' ) . ')</span></a>',
            'completed' => '<a href="' . add_query_arg( array( 'pms-view' => 'completed', 'paged' => 1 ) ) . '" ' . ( isset( $_GET['pms-view'] ) &&$_GET['pms-view'] == 'completed' ? 'class="current"' : '' ) . '>Completed <span class="count">(' . ( isset( $this->views_count['completed'] ) ? $this->views_count['completed'] : '' ) . ')</span></a>',
            'pending'   => '<a href="' . add_query_arg( array( 'pms-view' => 'pending', 'paged' => 1 ) ) . '" ' . ( isset( $_GET['pms-view'] ) &&$_GET['pms-view'] == 'pending' ? 'class="current"' : '' ) . '>Pending <span class="count">(' . ( isset( $this->views_count['pending'] ) ? $this->views_count['pending'] : '' ) . ')</span></a>'
        ));

    }


    /*
     * Sets the table data
     *
     * @return array
     *
     */
    public function set_table_data() {

        $data = array();

        $args = array();

        // If it's a search query send search parameter through $args
        if ( !empty($_REQUEST['s']) ) {
            $args = array(
                'order'                => 'ASC',
                'orderby'              => '',
                'search'               => $_REQUEST['s']
            );
        }

        // Get the payments
        $payments = pms_get_payments($args);

        // Set views count array to 0, we use this to display the count
        // next to the views links (all, active, expired, etc)
        $views = $this->get_views();
        foreach( $views as $view_slug => $view_link) {
            $this->views_count[$view_slug] = 0;
        }

        $selected_view = ( isset( $_GET['pms-view'] ) ? trim( $_GET['pms-view'] ) : '' );

        foreach(  $payments as $payment ) {

            // Increment the number of items for each status and for the total
            $this->views_count[ $payment->status ]++;
            $this->views_count['all']++;

            if( !empty($selected_view) && $payment->status != $selected_view )
                continue;

            $user = get_user_by( 'id', $payment->user_id );

            if( $user )
                $username = $user->data->user_login;
            else
                $username = __( 'User no longer exists', 'paid-member-subscriptions' );

            $data[] = array(
                'id'            => $payment->id,
                'username'      => $username,
                'subscription'  => $payment->subscription_id,
                'amount'        => $payment->amount,
                'date'          => date( 'F d, Y H:i:s', strtotime($payment->date) ),
                'type'          => pms_get_payment_type_name( $payment->type ),
                'transaction_id'=> $payment->transaction_id,
                'status'        => $payment->status,
                'ip_address'    => $payment->ip_address
            );
        }

        $this->data = $data;

    }


    /*
     * Populates the items for the table
     *
     * @param array $item           - data for the current row
     *
     * @return string
     *
     */
    public function prepare_items() {

        $columns        = $this->get_columns();
        $hidden_columns = $this->get_hidden_columns();
        $sortable       = $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden_columns, $sortable );

        $data = $this->data;
        usort( $data, array( $this, 'sort_data' ) );

        $paged = ( isset( $_GET['paged'] ) ? $_GET['paged'] : 1 );

        $this->set_pagination_args( array(
            'total_items' => count( $data ),
            'per_page'    => $this->items_per_page
        ));

        $data = array_slice( $data, $this->items_per_page * ( $paged-1 ), $this->items_per_page );

        $this->items = $data;

    }


    /*
     * Sorts the data by the variables in GET
     *
     */
    public function sort_data( $a, $b ) {

        // Set defaults
        $orderby = 'id';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }

        $result = strnatcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;

    }


    /*
     * Return data that will be displayed in each column
     *
     * @param array $item           - data for the current row
     * @param string $column_name   - name of the current column
     *
     * @return string
     *
     */
    public function column_default( $item, $column_name ) {

        return $item[ $column_name ];

    }


    /*
     * Return data that will be displayed in the username column
     *
     * @param array $item   - data of the current row
     *
     * @return string
     *
     */
    public function column_username( $item ) {

        if( empty( $item['ip_address'] ) )
            return $item['username'];


        $output = '<span class="pms-has-bubble">';

            $output .= $item['username'];

            $output .= '<div class="pms-bubble">';
                $output .= '<div><span class="alignleft">' . __( 'IP Address', 'paid-member-subscriptions' ) . '</span><span class="alignright">' . $item['ip_address'] . '</span></div>';
            $output .= '</div>';
        $output .= '</span>';

        return $output;

    }


    /*
     * Return data that will be displayed in the subscriptions column
     *
     * @param array $item   - data of the current row
     *
     * @return string
     *
     */
    public function column_subscriptions( $item ) {

        $subscription_plan = pms_get_subscription_plan( $item['subscription'] );
        $output = '<span class="pms-payment-list-subscription">' . $subscription_plan->name . '</span>';

        return $output;

    }


    /*
     * Return data that will be displayed in the status column
     *
     * @param array $item   - data of the current row
     *
     * @return string
     *
     */
    public function column_status( $item ) {

        $payment_statuses = pms_get_payment_statuses();

        $output = apply_filters( 'pms_list_table_' . $this->_args['plural'] . '_show_status_dot', '<span class="pms-status-dot ' . $item['status'] . '"></span>' );

        $output .= ( isset( $payment_statuses[ $item['status'] ] ) ? $payment_statuses[ $item['status'] ] : $item['status'] );

        return $output;

    }


    /*
     * Display if no items are found
     *
     */
    public function no_items() {

        echo __( 'No payments found', 'paid-member-subscriptions' );

    }

}