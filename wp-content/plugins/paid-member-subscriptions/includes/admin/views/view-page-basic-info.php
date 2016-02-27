<?php
/*
 * HTML Output for the basic information page
 */
?>

<div class="wrap pms-wrap pms-info-wrap">
    <div class="pms-badge "></div>
    <h1><?php printf( __( '<strong>Paid Member Subscriptions </strong> <small>v.</small>%s', 'paid-member-subscriptions' ), PMS_VERSION ); ?></h1>
    <p class="pms-info-text"><?php printf( __( 'Accept payments, create subscription plans and restrict content on your website.', 'paid-member-subscriptions' ) ); ?></p>
    <hr />
    <h2 class="pms-callout"><?php _e( 'Membership Made Easy', 'paid-member-subscriptions' ); ?></h2>
    <div class="pms-row pms-3-col">
        <div>
            <h3><?php _e( 'Register', 'paid-member-subscriptions'  ); ?></h3>
            <p><?php printf( __( 'Add basic registration forms where members can sign-up for a subscription plan using the %s shortcode.', 'paid-member-subscriptions' ), '<strong class="nowrap">[pms-register]</strong>' ); ?></p>
        </div>
        <div>
            <h3><?php _e( 'Account', 'paid-member-subscriptions' ); ?></h3>
            <p><?php printf( __( 'Allow members to edit their account information and manage their subscription plans using the %s shortcode.', 'paid-member-subscriptions' ), '<strong class="nowrap">[pms-account]</strong>' ); ?></p>
        </div>
        <div>
            <h3><?php _e( 'Restrict Content', 'pmstxt' ); ?></h3>
            <p><?php printf( __( 'Restrict content using the %s shortcode or directly from individual posts and pages.', 'paid-member-subscriptions' ), '<br/><strong class="nowrap">[pms-restrict subscription_plans="9,10"]</strong> &nbsp;&nbsp;&nbsp; <em>Special content for members subscribed to the subscription plans that have the ID 9 and 10!</em><strong class="nowrap">[/pms-restrict]</strong><br/>' ); ?></p>
        </div>
        <div>
            <h3><?php _e( 'Subscriptions', 'paid-member-subscriptions' ); ?></h3>
            <p><?php printf( __( 'Output subscription plans form anywhere and allow users to sign up for new subscriptions using %s shortcode.', 'paid-member-subscriptions' ), '<strong class="nowrap">[pms-subscriptions]</strong>' ); ?></p>
        </div>
        <div>
            <h3><?php _e( 'Login', 'paid-member-subscriptions' ); ?></h3>
            <p><?php printf( __( 'Allow members to login using %s shortcode.', 'paid-member-subscriptions' ), '<strong class="nowrap">[pms-login]</strong>' ); ?></p>
        </div>
        <div>
            <h3><?php _e( 'Recover Password', 'paid-member-subscriptions' ); ?></h3>
            <p><?php printf( __( 'Add a recover password form for your members using %s shortcode.', 'paid-member-subscriptions' ), '<strong class="nowrap">[pms-recover-password]</strong>' ); ?></p>
        </div>
    </div>

    <hr/>
    <div>
        <h3><?php _e( 'Membership Modules', 'paid-member-subscriptions' );?></h3>
        <p><?php _e( 'Powerful sections that help you manage your members.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <div class="pms-row pms-2-col">
        <div>
            <div class="pms-row pms-2-col">
                <div>
                    <h3><?php _e( 'Subscription Plans', 'paid-member-subscriptions' ); ?></h3>
                    <p><?php _e( 'Create hierarchical subscription plans allowing your members to upgrade/downgrade from an existing subscription. Shortcode based, offering many options to customize your subscriptions listing.', 'paid-member-subscriptions' ); ?></p>
                </div>
                <div>
                    <h3><?php _e( 'Members', 'paid-member-subscriptions' ); ?></h3>
                    <p><?php _e( 'Overview of all your members and their subscription plans. Easily add/remove members or edit their subscription details. ', 'paid-member-subscriptions' ); ?></p>
                </div>

            </div>
            <div class="pms-row pms-2-col">
                <div>
                    <h3><?php _e( 'Payments', 'paid-member-subscriptions' ); ?></h3>
                    <p><?php _e( 'Keep track of all member payments, payment statuses or purchased subscription plans.', 'paid-member-subscriptions' ); ?></p>
                </div>
                <div>
                    <h3><?php _e( 'Settings', 'paid-member-subscriptions' ); ?></h3>
                    <p><?php _e( 'Set the payment gateway used to accept payments, select messages seen by users when accessing a restricted content page or customize default member emails. Everything is just a few clicks away. ', 'paid-member-subscriptions' ); ?></p>
                </div>
            </div>
        </div>

        <div class="">
            <img src="<?php echo PMS_PLUGIN_DIR_URL; ?>assets/images/pms_members_multiple.png" alt="Paid Member Subscriptions Members Page" />
        </div>
    </div>
    <hr/>

    <div>
        <h3><?php _e( 'Featured Add-ons', 'paid-member-subscriptions' );?></h3>
        <p><?php _e( 'Get more functionality by using dedicated Add-ons and tailor Paid Member Subscriptions to your project needs.', 'paid-member-subscriptions' ); ?></p>
        <p><a href="admin.php?page=pms-addons-page" class="button-primary"><?php _e( 'Browse all Add-ons', 'paid-member-subscriptions' ); ?></a></p>
    </div>
    <div class="pms-row pms-3-col pms-addons">
        <div>
            <h3><?php _e( 'Recurring Payments - PayPal Standard', 'paid-member-subscriptions' ); ?></h3>
            <img src="<?php echo PMS_PLUGIN_DIR_URL; ?>assets/images/banner-recurring-payments.png" alt="Recurring Payments PayPal Standard" class="pms-addon-image" />
            <p><?php _e( 'Accept recurring payments from your members through PayPal Standard.', 'paid-member-subscriptions' ); ?></p>
        </div>
        <div>
            <h3><?php _e( 'Global Content Restriction', 'paid-member-subscriptions' ); ?></h3>
            <img src="<?php echo PMS_PLUGIN_DIR_URL; ?>assets/images/banner-global-content-restriction.png" alt="Global Content Restriction" class="pms-addon-image" />
            <p><?php _e( 'Easy way to add global content restriction rules to subscription plans, based on post type, taxonomy and terms.', 'paid-member-subscriptions' ); ?></p>
        </div>
        <div>
            <h3><?php _e( 'Discount Codes', 'paid-member-subscriptions' ); ?></h3>
            <img src="<?php echo PMS_PLUGIN_DIR_URL; ?>assets/images/banner-discount-codes.png" alt="Discount Codes" class="pms-addon-image" />
            <p><?php _e( 'Friction-less discount code creation for running promotions, making price reductions or simply rewarding your users.', 'paid-member-subscriptions' ); ?></p>
        </div>
        <div>
            <h3><?php _e( 'Multiple Subscriptions / User', 'paid-member-subscriptions' ); ?></h3>
            <img src="<?php echo PMS_PLUGIN_DIR_URL; ?>assets/images/banner-multiple-subscriptions.png" alt="Multiple Subscriptions per User" class="pms-addon-image" />
            <p><?php _e( 'Setup multiple subscription level blocks and allow members to sign up for more than one subscription plan (one per block).', 'paid-member-subscriptions' ); ?></p>
        </div>
        <div>
            <h3><?php _e( 'Navigation Menu Filtering', 'paid-member-subscriptions' ); ?></h3>
            <img src="<?php echo PMS_PLUGIN_DIR_URL; ?>assets/images/banner-nav-menu-filtering.png" alt="Navigation Menu Filtering" class="pms-addon-image" />
            <p><?php _e( 'Dynamically display menu items based on logged-in status as well as selected subscription plans.', 'paid-member-subscriptions' ); ?></p>
        </div>
    </div>
    <div class="pms-row">
        <p><a href="admin.php?page=pms-addons-page" class="button-primary"><?php _e( 'Browse all Add-ons', 'paid-member-subscriptions' ); ?></a></p>
    </div>
    <hr/>
    <p><?php printf( __( '<i>Paid Member Subscriptions comes with an %1$s extensive documentation%2$s to assist you.</i>', 'paid-member-subscriptions' ),'<a href=" http://www.cozmoslabs.com/docs/paid-member-subscriptions/">','</a>' ); ?></p>
</div>