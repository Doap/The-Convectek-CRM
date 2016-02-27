<?php
/*
 * Extends core PMS_Submenu_Page base class to create and add custom functionality
 * for the add-ons page in the admin section
 *
 * The Add-ons page will contain a listing of all the available add-ons for PMS,
 * allowing the user to purchase, install or activate a certain add-on.
 *
 */
Class PMS_Submenu_Page_Addons extends PMS_Submenu_Page {

    /*
     * Method that initializes the class
     *
     * */
    public function init() {

        // Hook the output method to the parent's class action for output instead of overwriting the
        // output method
        add_action( 'pms_output_content_submenu_page_' . $this->menu_slug, array( $this, 'output' ) );

        add_action( 'wp_ajax_pms_add_on_activate', array( $this, 'add_on_activate' ) );
        add_action( 'wp_ajax_pms_add_on_deactivate', array( $this, 'add_on_deactivate' ) );
        add_action( 'wp_ajax_pms_add_on_download_zip_file', array( $this, 'add_on_download_zip_file' ) );
        add_action( 'wp_ajax_pms_add_on_get_new_plugin_data', array( $this, 'add_on_get_new_plugin_data' ) );

        add_action( 'wp_ajax_pms_add_on_save_serial', array( $this, 'add_on_save_serial' ) );

    }

    /*
     * Method to output the content in the Add-ons page
     *
     * */
    public function output(){

        include_once 'views/view-page-addons.php';

    }

    /*
    * Function that returns the array of add-ons from cozmoslabs.com if it finds the file
    * If something goes wrong it returns false
    *
    * @since v.2.1.0
    */
    static function  add_ons_get_remote_content() {

        $response = wp_remote_get( 'http://www.cozmoslabs.com/wp-content/plugins/cozmoslabs-products-add-ons/paid-member-subscriptions-add-ons.json' );

        if( is_wp_error($response) ) {
            return false;
        } else {
            $json_file_contents = $response['body'];
            $pms_add_ons = json_decode( $json_file_contents, true );
        }

        if( !is_object( $pms_add_ons ) && !is_array( $pms_add_ons ) ) {
            return false;
        }

        return $pms_add_ons;
    }

    function add_on_activate(){
        check_ajax_referer( 'pms-activate-addon', 'nonce' );
        if( current_user_can( 'manage_options' ) ){
            // Setup variables from POST
            $pms_add_on_to_activate = $_POST['pms_add_on_to_activate'];
            $response = $_POST['pms_add_on_index'];

            if( !empty( $pms_add_on_to_activate ) && !is_plugin_active( $pms_add_on_to_activate )) {
                activate_plugin( $pms_add_on_to_activate );
            }

            if( !empty( $response ) )
                echo $response;
        }
        wp_die();
    }

    /*
     * Function that is triggered through Ajax to deactivate an add-on
     *
     * @since v.2.1.0
     */
    function add_on_deactivate() {
        check_ajax_referer( 'pms-activate-addon', 'nonce' );
        if( current_user_can( 'manage_options' ) ){
            // Setup variables from POST
            $pms_add_on_to_deactivate = $_POST['pms_add_on_to_deactivate'];
            $response = $_POST['pms_add_on_index'];

            if( !empty( $pms_add_on_to_deactivate ))
                deactivate_plugins( $pms_add_on_to_deactivate );

            if( !empty( $response ) )
                echo $response;
        }
        wp_die();

    }

    /*
     * Function that downloads and unzips the .zip file returned from Cozmoslabs
     *
     * @since v.2.1.0
     */
    function add_on_download_zip_file() {

        check_ajax_referer( 'pms-activate-addon', 'nonce' );

        // Set the response to success and change it later if needed
        $response = $_POST['pms_add_on_index'];
        $add_on_index = $response;

        // Setup variables from POST
        $pms_add_on_download_url = $_POST['pms_add_on_download_url'];
        $pms_add_on_zip_name = $_POST['pms_add_on_zip_name'];

        if( strpos( $pms_add_on_download_url, 'http://www.cozmoslabs.com/' ) === false )
            wp_die();


        // Get .zip file
        $remote_response = wp_remote_get( $pms_add_on_download_url );
        if( is_wp_error( $remote_response ) ) {
            $response = 'error-' . $add_on_index;
        } else {
            $file_contents = $remote_response['body'];
        }


        // Put the file in the plugins directory
        if( isset( $file_contents ) ) {
            if( file_put_contents( WP_PLUGIN_DIR . '/' . $pms_add_on_zip_name, $file_contents ) === false ) {
                $response = 'error-' . $add_on_index;
            }
        }


        // Unzip the file
        if( $response != 'error' ) {
            WP_Filesystem();
            if( unzip_file( WP_PLUGIN_DIR . '/' . $pms_add_on_zip_name , WP_PLUGIN_DIR ) ) {
                // Remove the zip file after we are all done
                unlink( WP_PLUGIN_DIR . '/' . $pms_add_on_zip_name );
            } else {
                $response = 'error-' . $add_on_index;
            }
        }

        echo $response;
        wp_die();
    }

    /*
     * Function that retrieves the data of the newly added plugin
     *
     * @since v.2.1.0
     */
    function add_on_get_new_plugin_data() {
        $pms_add_on_name = $_POST['pms_add_on_name'];

        $pms_get_all_plugins = get_plugins();
        foreach( $pms_get_all_plugins as $pms_plugin_key => $pms_plugin ) {

            if( strpos( $pms_plugin['Name'], $pms_add_on_name ) !== false && strpos( $pms_plugin['AuthorName'], 'Cozmoslabs' ) !== false ) {

                // Deactivate the add-on if it's active
                if( is_plugin_active( $pms_plugin_key )) {
                    deactivate_plugins( $pms_plugin_key );
                }

                // Return the plugin path
                echo $pms_plugin_key;
            }
        }

        wp_die();
    }

    /* ajax function to save the addon serial */
    function add_on_save_serial(){
        $pms_add_on_slug = $_POST['pms_add_on_slug'];
        $pms_add_on_unique_name = $_POST['pms_add_on_unique_name'];
        $pms_serial_value = $_POST['pms_serial_value'];
        $response = '';

        if( !empty( $pms_add_on_slug ) ){
            if( !empty( $pms_serial_value ) ) {
                update_option($pms_add_on_slug . '_serial_number', $pms_serial_value);
                $response = PMS_Submenu_Page_Addons::add_on_check_serial_number( $pms_serial_value, $pms_add_on_slug, $pms_add_on_unique_name );
            }
            else
                delete_option( $pms_add_on_slug.'_serial_number' );
        }
        die( $response );
    }


    //the function to check the validity of the serial number and save a variable in the DB; purely visual
    static function add_on_check_serial_number( $serial, $add_on_slug, $pms_add_on_unique_name ){
        $remote_url = 'http://updatemetadata.cozmoslabs.com/checkserial/?serialNumberSent='.$serial;
        if( !empty( $pms_add_on_unique_name ) )
            $remote_url = $remote_url.'&uniqueproduct='.$pms_add_on_unique_name;
        $remote_response = wp_remote_get( $remote_url );
        $response = PMS_Submenu_Page_Addons::add_on_update_serial_status( $remote_response, $add_on_slug );
        wp_clear_scheduled_hook( "check_plugin_updates-". $add_on_slug );
        return $response;
    }

    /* function to update the serial number status */
    static function add_on_update_serial_status( $response, $add_on_slug ){
        if (is_wp_error($response)) {
            update_option( 'pms_add_on_'.$add_on_slug.'_serial_status', 'serverDown'); //server down
            return 'serverDown';
        } elseif ((trim($response['body']) != 'notFound') && (trim($response['body']) != 'found') && (trim($response['body']) != 'expired') && (strpos( $response['body'], 'aboutToExpire' ) === false)) {
            update_option( 'pms_add_on_'. $add_on_slug .'_serial_status', 'serverDown'); //unknown response parameter
            update_option( 'pms_add_on_'. $add_on_slug .'_serial_number', ''); //reset the entered password, since the user will need to try again later
            return 'serverDown';

        } else {
            update_option( 'pms_add_on_'.$add_on_slug.'_serial_status', trim($response['body'])); //either found, notFound or expired
            return trim( $response['body'] );
        }
    }
}

$pms_submenu_page_addons = new PMS_Submenu_Page_Addons( 'paid-member-subscriptions', __( 'Add-ons', 'paid-member-subscriptions' ), __( 'Add-ons', 'paid-member-subscriptions' ), 'manage_options', 'pms-addons-page', 30 );
$pms_submenu_page_addons->init();