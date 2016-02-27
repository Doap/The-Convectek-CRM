jQuery( function(){
    /* auto check loggedin radio if we select a plan */
    jQuery( '#pms_post_content_restriction input[name="pms-content-restrict-subscription-plan[]"]').click( function(){
        jQuery( '#pms_post_content_restriction input[value="everyone"]').prop( "checked", false );
        jQuery( '#pms_post_content_restriction input[value="loggedin"]').prop( "checked", true );
    } )
});
