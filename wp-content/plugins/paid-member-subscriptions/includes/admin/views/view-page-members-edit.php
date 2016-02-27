<?php
/*
 * HTML output for the members admin edit member page
 */
?>

<div class="wrap">

    <h2>
        <?php echo __( 'Edit Member', 'paid-member-subscriptions' ); ?>
    </h2>

    <form id="pms-form-edit-member" class="pms-form" method="POST">

        <?php $member = pms_get_member( trim( $_REQUEST['member_id'] ) ); ?>

        <div class="pms-form-field-wrapper pms-form-field-user-name">

            <label class="pms-form-field-label"><?php echo __( 'Username', 'paid-member-subscriptions' ); ?></label>
            <input type="hidden" id="pms-member-user-id" name="pms-member-user-id" class="widefat" value="<?php echo $member->user_id; ?>" />

            <span><strong><?php echo $member->username; ?></strong></span>

        </div>


        <?php
            $members_list_table = new PMS_Member_Subscription_List_Table( $member->user_id );
            $members_list_table->prepare_items();
            $members_list_table->display();
        ?>


        <?php do_action( 'pms_member_edit_form_field' ); ?>

        <?php wp_nonce_field( 'pms_member_nonce' ); ?>

    </form>

</div>