/* =========================================================
 * admin_script.js
 * =========================================================
 * Started by Solwin theme
 * ========================================================= */

jQuery(document).ready(function() {
    
    jQuery('#txtUsername').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            jQuery('#filter_action').trigger('click');
        }
    });
    //Datepicker
    jQuery("#frmdate").datetimepicker({
        //dateFormat: 'dd-mm-yy',
        minDate: 0,
        changeMonth: true,
        changeYear: true,
        timeFormat: 'HH:mm:ss',
        onClose: function (selectedDate) {
            jQuery("#todate").datetimepicker("option", "minDate", selectedDate);
        }
    });
    //jQuery("#frmdate").datetimepicker();
    jQuery("#todate").datetimepicker({
        //dateFormat: 'dd-mm-yy',
        minDate: 0,
        changeMonth: true,
        changeYear: true,
        timeFormat: 'HH:mm:ss',
        onClose: function (selectedDate) {
            jQuery("#frmdate").datepicker("option", "maxDate", selectedDate);
        }
    });
    jQuery('#display_status').change(function(){
       if(jQuery(this).val() == 'roles') {
           jQuery('.role_records').show();
           jQuery('.users_records, .filter_div').hide();
       }
       else {
           jQuery('.role_records').hide();
           jQuery('.users_records, .filter_div').show();
       }
    });
    jQuery('#chkapply').click(function(){
       var from = jQuery('#txtSunFrom').val();
       var to = jQuery('#txtSunTo').val();
       jQuery('#txtMonFrom').val(from);
       jQuery('#txtMonTo').val(to);
       jQuery('#txtTueFrom').val(from);
       jQuery('#txtTueTo').val(to);
       jQuery('#txtWedFrom').val(from);
       jQuery('#txtWedTo').val(to);
       jQuery('#txtThuFrom').val(from);
       jQuery('#txtThuTo').val(to);
       jQuery('#txtFriFrom').val(from);
       jQuery('#txtFriTo').val(to);
       jQuery('#txtSatFrom').val(from);
       jQuery('#txtSatTo').val(to);
    });
    jQuery('.view_block_data').click(function(event){
        event.preventDefault();
        jQuery(this).closest('tr').next('tr').slideToggle();
    });
    //Solve searching issue for role and text field
    jQuery('#srole').on('focus', function(){
        jQuery('#txtUsername').val('');
    });
    jQuery('#txtUsername').on('focus', function(){
        jQuery('#srole').val('');
    });
    //Datepicker
    jQuery('.view_block_data_all').click(function(event){
        event.preventDefault();
        jQuery(this).closest('tr').next('tr').slideToggle();
    });
});

/**
 * 
 * @description change user function
 */
function changeUserBy() {
    var cmbUserBy = jQuery('#cmbUserBy').val();
    jQuery('.user-records').hide();
    jQuery('#' + cmbUserBy).show();
    jQuery('#hidden_cmbUserBy').val(cmbUserBy);
    var btnVal = jQuery('#sbt-block').val();
    var is_update = 0;
    if (btnVal.toLowerCase().indexOf("update") < 0) {
        is_update = 1;
        var new_btnval = btnVal.replace("User", "Role");
        var new_btnval1 = btnVal.replace("Role", "User");
    }
    if(cmbUserBy == 'role') {
        jQuery('.filter_div').hide();
        if ( is_update == 1 ) {
            jQuery('#sbt-block').val(new_btnval);
        }
    }
    else {
        jQuery('.filter_div').show();
        if ( is_update == 1 ) {
            jQuery('#sbt-block').val(new_btnval1);
        }
    }
}

/**
 * 
 * @description click function for search user
 */
function searchUser() {
    jQuery('#filter_action').trigger('click');
}

jQuery(window).load(function(){
   jQuery('#subscribe_thickbox').trigger('click');
   jQuery("#TB_closeWindowButton").click(function() {
        jQuery.post(ajaxurl,
        {
            'action': 'close_tab'
        });
   });
});