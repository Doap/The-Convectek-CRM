<?php
/*
 * HTML output for content restriction meta-box
 */
?>
<div class="pms-meta-box-field-wrapper">
    <?php $user_status = get_post_meta( $post->ID, 'pms-content-restrict-user-status', true ); ?>
    <label class="pms-meta-box-field-label"><?php _e( 'Display To', 'paid-member-subscriptions' ); ?></label>
    <label class="pms-meta-box-checkbox-label" for="pms-content-restrict-everyone">
         <input type="radio" id="pms-content-restrict-everyone" value="everyone" <?php if( empty( $user_status ) ) echo 'checked="checked"'; ?> name="pms-content-restrict-user-status">
         <?php _e( 'Everyone', 'paid-member-subscriptions' ); ?>
    </label>
    <label class="pms-meta-box-checkbox-label" for="pms-content-restrict-loggedin">
        <input type="radio" id="pms-content-restrict-loggedin" value="loggedin" <?php if( !empty( $user_status ) && $user_status == 'loggedin' ) echo 'checked="checked"'; ?> name="pms-content-restrict-user-status">
        <?php _e( 'Logged In Users ', 'paid-member-subscriptions' ); ?>
    </label>
</div>
<div class="pms-meta-box-field-wrapper">
    <label class="pms-meta-box-field-label"><?php _e( 'Subscription Plans', 'paid-member-subscriptions' ); ?></label>

    <?php
    $subscription_plans = pms_get_subscription_plans();
    $selected_subscription_plans = get_post_meta( $post->ID, 'pms-content-restrict-subscription-plan' );
    ?>

    <?php if( !empty( $subscription_plans ) ): foreach( $subscription_plans as $subscription_plan ): ?>

        <label class="pms-meta-box-checkbox-label" for="pms-content-restrict-subscription-plan-<?php echo $subscription_plan->id ?>">
            <input type="checkbox" value="<?php echo $subscription_plan->id; ?>" <?php if( in_array( $subscription_plan->id, $selected_subscription_plans ) ) echo 'checked="checked"'; ?> name="pms-content-restrict-subscription-plan[]" id="pms-content-restrict-subscription-plan-<?php echo $subscription_plan->id ?>">
            <?php echo $subscription_plan->name; ?>
        </label>

    <?php endforeach; ?>
        <p class="description"><?php echo __( 'Members subscribed to these Subscription plan(s) will be able to view this page.', 'paid-member-subscriptions' ); ?></p>
    <?php else: ?>
        <p class="description"><?php printf( __( 'You do not have any active Subscription Plans yet. Please create them <a href="%s">here</a>', 'paid-member-subscriptions' ), admin_url( 'edit.php?post_type=pms-subscription' ) ); ?></p>
    <?php endif; ?>

</div>