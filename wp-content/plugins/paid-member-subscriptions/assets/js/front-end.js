jQuery( function($) {


    if( window.history.replaceState ) {

        currentURL = window.location.href;

        currentURL = pms_remove_query_arg( 'pmsscscd', currentURL );
        currentURL = pms_remove_query_arg( 'pmsscsmsg', currentURL );
        currentURL = pms_remove_query_arg( 'pms_gateway_payment_action', currentURL );
        currentURL = pms_remove_query_arg( 'pms_gateway_payment_id', currentURL );

        window.history.replaceState( null, null, currentURL );
    }


    /*
     * Strips one query argument from a given URL string
     *
     */
    function pms_remove_query_arg( key, sourceURL ) {

        var rtn = sourceURL.split("?")[0],
            param,
            params_arr = [],
            queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";

        if (queryString !== "") {
            params_arr = queryString.split("&");
            for (var i = params_arr.length - 1; i >= 0; i -= 1) {
                param = params_arr[i].split("=")[0];
                if (param === key) {
                    params_arr.splice(i, 1);
                }
            }

            rtn = rtn + "?" + params_arr.join("&");

        }

        if(rtn.split("?")[1] == "") {
            rtn = rtn.split("?")[0];
        }

        return rtn;
    }

    /*
     * Hide "automatically renew subscription" checkbox for free/one time payments
     *
     */
    jQuery(document).ready(function() {

        if (jQuery('.pms-subscription-plan input[type=radio][data-duration="0"]').is(':checked')) {
            jQuery('.pms-subscription-plan-auto-renew').hide();
        }

        jQuery('.pms-subscription-plan input[type=radio]').click(function(){

            if (jQuery('.pms-subscription-plan input[type=radio][data-duration="0"]').is(':checked')) {
                jQuery('.pms-subscription-plan-auto-renew').hide();
            }
            else {
                jQuery('.pms-subscription-plan-auto-renew').show();
            }
        });

    });


    /*
     * Add field error for a given element name
     *
     */
    $.pms_add_field_error = function( error, field_name ) {

        if( error == '' || error == 'undefined' || field_name == '' || field_name == 'undefined' )
            return false;

        $field          = $('[name=' + field_name + ']');
        $field_wrapper  = $field.closest('.pms-field');

        error = '<p>' + error + '</p>';

        if( $field_wrapper.find('.pms_field-errors-wrapper').length > 0 )
            $field_wrapper.find('.pms_field-errors-wrapper').html( error );
        else
            $field_wrapper.append('<div class="pms_field-errors-wrapper pms-is-js">' + error + '</div>');

    };

    /*
     * Clear all field errors added with js
     *
     */
    $.pms_clean_field_errors = function() {

        $('.pms_field-errors-wrapper.pms-is-js').remove();

    };

});