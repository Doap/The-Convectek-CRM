<?php  
if ( !defined( 'ABSPATH' ) ) exit;
// Property/Value Panel
        $ctcpage = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
?>

<div id="rule_value_options_panel" 
        class="ctc-option-panel<?php echo 'rule_value_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?>>
  <form id="ctc_rule_value_form" method="post" action="?page=<?php echo $ctcpage; ?>">
    <?php wp_nonce_field( apply_filters( 'chld_thm_cfg_action', 'ctc_update' ) ); ?>
    <div class="ctc-input-row clearfix" id="ctc_input_row_rule_menu">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Property', 'child-theme-configurator' ); ?>
        </strong> </div>
      <div class="ctc-input-cell" id="ctc_rule_menu_selected">&nbsp;</div>
      <div id="ctc_status_rule_val"></div>
      <div class="ctc-input-cell">
        <div class="ui-widget">
          <input id="ctc_rule_menu"/>
          <div id="ctc_status_rules" style="float:right"></div>
        </div>
      </div>
    </div>
    <div class="ctc-input-row clearfix" id="ctc_input_row_rule_header" style="display:none">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Value', 'child-theme-configurator' ); ?>
        </strong> </div>
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Sample', 'child-theme-configurator' ); ?>
        </strong> </div>
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Selectors', 'child-theme-configurator' ); ?>
        </strong> </div>
    </div>
    <div class="ctc-rule-value-input-container clearfix" id="ctc_rule_value_inputs" style="display:none"> </div>
  </form>
</div>
