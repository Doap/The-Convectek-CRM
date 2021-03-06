<?php
/*
 * Merge Tags Class contains the deafult merge tags and methods how to handle them
 *
 */
Class PMS_Merge_Tags{

    public function __construct() {
        /* filters on replacing merge tags */
        add_filter( 'pms_merge_tag_subscription_name', array( $this, 'pms_tag_subscription_name' ), 10, 3 );
        add_filter( 'pms_merge_tag_display_name', array( $this, 'pms_tag_display_name' ), 10, 3 );
    }

    /**
     * Function that searches and replaces merge tags with their values
     * @param $text the text to search
     * @param $user_info used for merge tags related to the user
     * @param $subscription_plan_id used for merge tags related to the subscription plan
     * @return mixed teh text with merge tags replaced
     */
    static function pms_process_merge_tags( $text, $user_info, $subscription_plan_id ){
        $merge_tags = PMS_Merge_Tags::get_merge_tags();
        if( !empty( $merge_tags ) ){
            foreach( $merge_tags as $merge_tag ){
                $text = str_replace( '{{'.$merge_tag.'}}', apply_filters( 'pms_merge_tag_'.$merge_tag, '', $user_info, $subscription_plan_id ), $text );
            }
        }
        return $text;
    }

    /**
     * Function that returns the available merge tags
     */
    static function get_merge_tags(){
        $available_merge_tags = apply_filters( 'pms_merge_tags', array( 'display_name', 'subscription_name' ) );
        return $available_merge_tags;
    }

    /**
     * Replace the {{subscription_name}} tag
     */
    function pms_tag_subscription_name( $value, $user_info, $subscription_plan_ids ){
        /* make sure we have an array */
        if( !is_array( $subscription_plan_ids ) )
            $subscription_plan_ids = array( $subscription_plan_ids );

        $sub_plan_names = array();
        if( !empty( $subscription_plan_ids ) ){
            foreach( $subscription_plan_ids as $subscription_plan_id ){
                $sub_plan = pms_get_subscription_plan( $subscription_plan_id );
                $sub_plan_names[] = $sub_plan->name;
            }
        }


        if( !empty( $sub_plan_names ) )
            return implode( ', ', $sub_plan_names );
        else
            return $value;
    }

    /**
     * Replace the {{display_name}} tag
     */
    function pms_tag_display_name( $value, $user_info, $subscription_plan_id ){
        if( !empty( $user_info->display_name ) )
            return $user_info->display_name;
        else if( !empty( $user_info->user_login ) )
            return $user_info->user_login;
        else
            return '';
    }
}


$merge_tags = new PMS_Merge_Tags();