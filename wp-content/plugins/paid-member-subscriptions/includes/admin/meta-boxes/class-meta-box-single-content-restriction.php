<?php

Class PMS_Meta_Box_Content_Restriction extends PMS_Meta_Box {


    /*
     * Function to hook the output and save data methods
     *
     */
    public function init() {
        add_action( 'pms_output_content_meta_box_' . $this->post_type . '_' . $this->id, array( $this, 'output' ) );
        add_action( 'pms_save_meta_box_' . $this->post_type, array( $this, 'save_data' ) );
    }


    /*
     * Function to output the HTML for this meta-box
     *
     */
    public function output( $post ) {

        include_once 'views/view-meta-box-single-content-restriction.php';

    }


    /*
     * Function to validate the data and save it for this meta-box
     *
     */
    public function save_data( $post_id ) {
        /* first we delete the rules */
        delete_post_meta( $post_id, 'pms-content-restrict-subscription-plan' );
        if( isset( $_POST['pms-content-restrict-subscription-plan'] ) )
            foreach( $_POST['pms-content-restrict-subscription-plan'] as $subscription_id ){
                add_post_meta( $post_id, 'pms-content-restrict-subscription-plan', $subscription_id );
            }

        if( isset( $_POST['pms-content-restrict-user-status'] ) && $_POST['pms-content-restrict-user-status'] == 'loggedin' ){
            update_post_meta( $post_id, 'pms-content-restrict-user-status', 'loggedin' );
        }
        else{
            delete_post_meta(  $post_id, 'pms-content-restrict-user-status' );
        }
    }

}

// initialize the restrict content metaboxes on init.
add_action( 'init', 'pms_initialize_content_restrict_metabox' );
function pms_initialize_content_restrict_metabox(){
	$post_types = get_post_types( array( 'public' => true ) );
	if( !empty( $post_types ) ){
		foreach( $post_types as $post_type ){
			$pms_meta_box_content_restriction = new PMS_Meta_Box_Content_Restriction( 'pms_post_content_restriction', __( 'Content Restriction', 'paid-member-subscriptions' ), $post_type, 'normal' );
			$pms_meta_box_content_restriction->init();
		}
	}
}