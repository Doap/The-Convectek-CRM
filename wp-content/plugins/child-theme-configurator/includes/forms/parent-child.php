<?php  
if ( !defined( 'ABSPATH' ) ) exit;
// Parent/Child Panel
?>

<div id="parent_child_options_panel" class="ctc-option-panel<?php echo 'parent_child_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>">
  <form id="ctc_load_form" method="post" action=""><!-- ?page=<?php echo CHLD_THM_CFG_MENU; ?>"-->
    <?php 
    wp_nonce_field( 'ctc_update' ); 
    //if ( '' == $hidechild ) 
    do_action( 'chld_thm_cfg_controls', $this->ctc() );
    $disabled       = $this->ctc()->is_legacy() && !$this->ctc()->is_theme() ? ' disabled ' : '';
    $disabledclass  = $this->ctc()->is_legacy() && !$this->ctc()->is_theme() ? ' ctc-disabled ' : '';
?>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>" id="input_row_parnt">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Parent Theme', 'child-theme-configurator' ); ?>
        </strong> </div>
      <div class="ctc-input-cell">
        <?php $this->render_theme_menu( 'parnt', $this->ctc()->get_current_parent() ); ?>
      </div>
    </div>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>" id="input_row_child">
      <div class="ctc-input-cell ctc-section-toggle" id="ctc_theme_attributes"> <strong>
        <?php _e( 'Child Theme', 'child-theme-configurator' ); ?>
        </strong>
        <?php _e( '(click to edit additional fields)', 'child-theme-configurator' ); ?>
        </div>
      <div class="ctc-input-cell">
        <input class="ctc-radio ctc-themeonly" id="ctc_child_type_new" name="ctc_child_type" type="radio" value="new" 
            <?php echo ( !empty( $hidechild ) ? 'checked' : '' ); ?>
            <?php echo $hidechild . ' ' . $disabled;?> />
        <label for="ctc_child_type_new">
          <?php _e( 'Create New Child Theme', 'child-theme-configurator' ); ?>
        </label>
      </div>
      <div class="ctc-input-cell">
        <input class="ctc-radio ctc-themeonly" id="ctc_child_type_existing" name="ctc_child_type"  type="radio" value="existing" 
            <?php echo ( empty( $hidechild ) ? 'checked' : '' ); ?>
            <?php echo $hidechild . ' ' . $disabled; ?> />
        &nbsp;
        <label for="ctc_child_type_existing" <?php echo $hidechild;?>>
          <?php _e( 'Use Existing Child Theme', 'child-theme-configurator' ); ?>
        </label>
      </div>
      <div class="ctc-input-cell" style="clear:both"> <strong>&nbsp;</strong> </div>
      <div class="ctc-input-cell" >
        <input class="ctc_text ctc-themeonly" id="ctc_child_template" name="ctc_child_template" type="text" placeholder="<?php _e( 'New Child Theme Slug', 'child-theme-configurator' ); ?>" autocomplete="off" <?php echo $disabled; ?> />
      </div>
      <?php if ( '' == $hidechild ): ?>
      <div class="ctc-input-cell">
        <?php $this->render_theme_menu( 'child', $this->ctc()->get_current_child() ); ?>
      </div>
      <?php endif; ?>
    </div>
<div class="ctc-section-toggle-content" id="ctc_theme_attributes_content">
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>" id="input_row_child_name">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Child Theme Name', 'child-theme-configurator' ); ?>
        </strong> </div>
  <div class="ctc-input-cell-wide">
        <input class="ctc_text ctc-themeonly" id="ctc_child_name" name="ctc_child_name"  type="text" 
                value="<?php echo esc_attr( $css->get_prop( 'child_name' ) ); ?>" placeholder="<?php _e( 'Theme Name', 'child-theme-configurator' ); ?>" autocomplete="off" <?php echo $disabled; ?> /> </div></div>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>" id="input_row_child_website">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Theme Website', 'child-theme-configurator' ); ?>
        </strong> </div>
  <div class="ctc-input-cell-wide">
        <input class="ctc_text ctc-themeonly" id="ctc_child_themeuri" name="ctc_child_themeuri"  type="text" 
                value="<?php echo esc_attr( $css->get_prop( 'themeuri' ) ); ?>" placeholder="<?php _e( 'Theme Website', 'child-theme-configurator' ); ?>" autocomplete="off" <?php echo $disabled; ?> /> </div></div>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>" id="input_row_child_author">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Author', 'child-theme-configurator' ); ?>
        </strong> </div>
      <div class="ctc-input-cell-wide">
        <input class="ctc_text" id="ctc_child_author" name="ctc_child_author" type="text" 
                value="<?php echo esc_attr( $css->get_prop( 'author' ) ); ?>" placeholder="<?php _e( 'Author', 'child-theme-configurator' ); ?>" autocomplete="off" />
      </div></div>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>" id="input_row_child_authoruri">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Author Website', 'child-theme-configurator' ); ?>
        </strong> </div>
  <div class="ctc-input-cell-wide">
        <input class="ctc_text ctc-themeonly" id="ctc_child_authoruri" name="ctc_child_authoruri"  type="text" 
                value="<?php echo esc_attr( $css->get_prop( 'authoruri' ) ); ?>" placeholder="<?php _e( 'Author Website', 'child-theme-configurator' ); ?>" autocomplete="off" <?php echo $disabled; ?> /> </div></div>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>" id="input_row_child_descr">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Theme Description', 'child-theme-configurator' ); ?>
        </strong> </div>
  <div class="ctc-input-cell-wide">
        <textarea class="ctc_text ctc-themeonly" id="ctc_child_descr" name="ctc_child_descr" placeholder="<?php _e( 'Description', 'child-theme-configurator' ); ?>" autocomplete="off" <?php echo $disabled; ?> ><?php echo esc_textarea( $css->get_prop( 'descr' ) ); ?></textarea> </div></div>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>" id="input_row_child_tags">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Theme Tags', 'child-theme-configurator' ); ?>
        </strong> </div>
  <div class="ctc-input-cell-wide">
        <textarea class="ctc_text ctc-themeonly" id="ctc_child_tags" name="ctc_child_tags" placeholder="<?php _e( 'Tags', 'child-theme-configurator' ); ?>" autocomplete="off" <?php echo $disabled; ?> ><?php echo esc_textarea( $css->get_prop( 'tags' ) ); ?></textarea></div></div>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>" id="input_row_child_version">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Version', 'child-theme-configurator' ); ?>
        </strong> </div>
      <div class="ctc-input-cell">
        <input class="ctc_text" id="ctc_child_version" name="ctc_child_version" type="text" 
                value="<?php echo esc_attr( $css->get_prop( 'version' ) ); ?>" placeholder="<?php _e( 'Version', 'child-theme-configurator' ); ?>" autocomplete="off" />
      </div>
    </div>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>" id="input_row_duplicate_theme" <?php echo $hidechild;?>>
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Duplicate Existing Child Theme', 'child-theme-configurator' ); ?>
        </strong> </div>
      <div class="ctc-input-cell">
        <input class="ctc_checkbox ctc-themeonly" id="ctc_duplicate_theme" name="ctc_duplicate_theme" type="checkbox" 
                value="1" <?php echo $disabled; ?> />
        <input class="ctc_text" id="ctc_duplicate_theme_slug" name="ctc_duplicate_theme_slug" type="text" 
                value="" placeholder="<?php _e( 'Duplicate Theme Slug', 'child-theme-configurator' ); ?>" autocomplete="off" />
      </div>
      <div class="ctc-input-cell howto"> <strong>
        <?php _e( 'NOTE:', 'child-theme-configurator' ); ?>
        </strong>
        <?php _e( 'This will copy all child theme files and apply changes to new version.', 'child-theme-configurator' ); ?>
      </div>
    </div>
  <div class="ctc-input-row clearfix" id="input_row_debug">
      <div class="ctc-input-cell">
        <strong>
          <?php _e( 'Debug', 'chld_thm_cfg_plugins' ); ?>
        </strong>
      </div>
      <div class="ctc-input-cell">
        <input class="ctc_checkbox" id="ctc_is_debug" name="ctc_is_debug"  type="checkbox" 
            value="1" <?php echo checked( $this->ctc()->is_debug, 1 ); ?> autocomplete="off" />
      </div>
      <div class="ctc-input-cell howto">
          <?php _e( 'Check the box to enable debugging output.', 'chld_thm_cfg_plugins' ); ?>
      </div>
  </div>
    </div>
    <?php $parent_handling = ( isset( $css->enqueue ) ? $css->enqueue : 'enqueue' ); ?>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>">
      <div class="ctc-input-cell ctc-section-toggle" id="ctc_stylesheet_handling">
        <strong><?php _e( 'Stylesheet handling', 'child-theme-configurator' ); ?></strong>
        <?php _e( '(click to view options)', 'child-theme-configurator' ); ?>
      </div><?php if ( empty( $css->nowarn ) && count( $this->warnings ) && in_array( $css->enqueue, array( 'none', 'enqueue' ) ) ):?>
<div class="ctc-input-cell-wide update-nag"><strong><?php _e( 'This theme may not apply child theme styles correctly with the current settings:', 'child-theme-configurator' ); ?></strong><ul class="smaller">
        <?php foreach ( $this->warnings as $warning ) echo '<li>' . $warning  . '</li>' . LF; ?>
        </ul><span class="alignright"><label class="smaller"><input type="checkbox" name="ctc_nowarn" value="1" /><?php _e( "Don't show again.", 'child-theme-configurator'); ?></label> &nbsp; <a href="#" class="ctc-section-toggle" id="ctc_stylesheet_handling2"><?php _e( 'View options', 'child-theme-configurator'); ?></a></span></div>
<?php endif; ?>
<div class="ctc-section-toggle-content clear" id="ctc_stylesheet_handling_content">
      <div class="ctc-input-cell clear"><a href="<?php echo CHLD_THM_CFG_DOCS_URL; ?>/how-to-use/#stylesheet_handling" target="_blank"><?php _e( 'Which option should I use?', 'child-theme-configurator' ); ?></a></div>
      <div class="ctc-input-cell">
        <label>
          <input class="ctc_radio ctc-themeonly" id="ctc_parent_enqueue_none" name="ctc_parent_enqueue" type="radio" 
                value="none" <?php checked( 'none', $parent_handling ); ?> <?php echo $disabled; ?> />
          <?php _e( 'None (handled by theme)', 'child-theme-configurator' ); ?>
        </label>
      </div>
      <div class="ctc-input-cell howto sep">
        <?php _e( 'Select this option if all stylesheets are correctly enqueued for child themes. If you find that styles are not being applied correctly, use a different option.', 'child-theme-configurator' ); ?>
      </div>
      <div class="ctc-input-cell clear">&nbsp;</div>
      <div class="ctc-input-cell">
        <label>
          <input class="ctc_radio ctc-themeonly" id="ctc_parent_enqueue_enqueue" name="ctc_parent_enqueue" type="radio" 
                value="enqueue" <?php checked( 'enqueue', $parent_handling ); ?> <?php echo $disabled; ?> />
          <?php _e( 'Enqueue parent stylesheet (default)', 'child-theme-configurator' ); ?>
        </label>
        </strong> </div>
      <div class="ctc-input-cell howto sep"><?php _e( "Select this option if the theme enqueues the active stylesheet but has no special handling for child themes. Start with this option if unsure.", 'child-theme-configurator' ); ?>
</div>
      <div class="ctc-input-cell clear">&nbsp;</div>
      <div class="ctc-input-cell">
        <label>
          <input class="ctc_radio ctc-themeonly" id="ctc_parent_enqueue_child" name="ctc_parent_enqueue" type="radio" 
                value="child" <?php checked( 'child', $parent_handling ); ?> <?php echo $disabled; ?> />
          <?php _e( 'Enqueue child stylesheet', 'child-theme-configurator' ); ?>
        </label>
        </strong> </div>
      <div class="ctc-input-cell howto sep"><?php _e( 'Select this option if the theme enqueues the parent stylesheet but does not enqueue the child stylesheet at all. This can happen if <code>get_template()</code> or <code>get_template_directory_uri()</code> is used to link the stylesheet.', 'child-theme-configurator' ); ?>
</div>
      <div class="ctc-input-cell clear"><?php if ( count( $this->warnings ) ): ?><div class="update-nag">
        <strong><?php _e( 'Recommended for this theme:', 'child-theme-configurator' ); ?></strong></div>
        <?php endif; ?>&nbsp;</div>
      <div class="ctc-input-cell">
        <label>
          <input class="ctc_radio ctc-themeonly" id="ctc_parent_enqueue_both" name="ctc_parent_enqueue" type="radio" 
                value="both" <?php checked( 'both', $parent_handling ); ?> <?php echo $disabled; ?> />
          <?php _e( 'Enqueue both parent and child stylesheets', 'child-theme-configurator' ); ?>
        </label>
        </strong> </div>
      <div class="ctc-input-cell howto sep"><?php _e( 'Select this option if stylesheet link tags are hard-coded into the header template (common in older themes). This enables the child stylesheet to override the parent stylesheet without using <code>@import</code>.', 'child-theme-configurator' ); ?>
</div><?php do_action( 'chld_thm_cfg_enqueue_options' ); ?>
      <div class="ctc-input-cell clear">&nbsp;</div>
      <div class="ctc-input-cell">
        <label>
          <input class="ctc_radio ctc-themeonly" id="ctc_parent_enqueue_import" name="ctc_parent_enqueue" type="radio" 
                value="import" <?php checked( 'import', $parent_handling ); ?> <?php echo $disabled; ?> />
          <?php _e( '<code>@import</code> parent stylesheet', 'child-theme-configurator' ); ?>
        </label>
        </strong> </div>
      <div class="ctc-input-cell howto"><?php _e( "This option imports the parent stylesheet from the child stylesheet. This enables the child stylesheet to override the parent stylesheet, but using <code>@import</code> is no longer recommended.", 'child-theme-configurator' ); ?>
</div>
    </div></div><?php if ( ! is_multisite() || ! empty( $this->ctc()->themes[ 'parnt' ][ $this->ctc()->get_current_parent() ][ 'allowed' ] ) ): ?>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>">
      <div class="ctc-input-cell"> <strong><label for="ctc_parent_mods">
        <?php _e( 'Copy Parent Theme Menus, Widgets and other Customizer Options', 'child-theme-configurator' ); ?>
        </label></strong> </div>
      <div class="ctc-input-cell">
        <input class="ctc_checkbox ctc-themeonly" id="ctc_parent_mods" name="ctc_parent_mods" type="checkbox" 
                value="1" <?php echo $disabled; ?> />
      </div>
      <div class="ctc-input-cell howto"> <strong>
        <?php _e( 'NOTE:', 'child-theme-configurator' ); ?>
        </strong>
        <?php _e( 'This will overwrite child theme options you may have already set.', 'child-theme-configurator' ); ?>
      </div>
    </div><?php endif; ?>
    <?php if ( '' == $hidechild ): ?>
    <div class="ctc-input-row clearfix">
      <div class="ctc-input-cell"> <strong><label for="ctc_backup">
        <?php _e( 'Backup current stylesheet', 'child-theme-configurator' ); ?>
        </label></strong> </div>
      <div class="ctc-input-cell">
        <input class="ctc_checkbox" id="ctc_backup" name="ctc_backup" type="checkbox" 
                value="1" />
      </div>
      <div class="ctc-input-cell howto"> <strong>
        <?php _e( 'NOTE:', 'child-theme-configurator' ); ?>
        </strong>
        <?php _e( 'This creates a copy of the current stylesheet before applying changes. You can remove old backup files using the Files tab.', 'child-theme-configurator' ); ?>
      </div>
    </div>
    <div class="ctc-input-row clearfix">
      <div class="ctc-input-cell ctc-section-toggle" id="ctc_revert_css"> <strong>
        <?php _e( 'Reset/Restore from backup', 'child-theme-configurator' ); ?>
        </strong> </div>
      <div class="ctc-input-cell-wide ctc-section-toggle-content" id="ctc_revert_css_content">
        <label>
          <input class="ctc_checkbox" id="ctc_revert_none" name="ctc_revert" type="radio" 
                value="" checked="" />
          <?php _e( 'Leave unchanged', 'child-theme-configurator' );?>
        </label>
        <br/>
        <label>
          <input class="ctc_checkbox" id="ctc_revert_all" name="ctc_revert" type="radio" 
                value="all" />
          <?php _e( 'Reset all', 'child-theme-configurator' );?>
        </label>
        <div id="ctc_backup_files"><?php
    foreach ( $this->ctc()->get_files( $css->get_prop( 'child' ), 'backup' ) as $backup => $label ): ?>
          <label>
            <input class="ctc_checkbox" id="ctc_revert_<?php echo $backup; ?>" name="ctc_revert" type="radio" 
                value="<?php echo $backup; ?>" />
            <?php echo __( 'Restore backup from', 'child-theme-configurator' ) . ' ' . $label; ?></label>
          <br/>
          <?php endforeach; ?>
          </div>
      </div>
    </div>
    <?php endif; ?>
    <div class="ctc-input-row clearfix" id="ctc_stylesheet_files">
      <?php
// Additional stylesheets
$stylesheets = $this->ctc()->get_files( $this->ctc()->get_current_parent(), 'stylesheet' );
if ( count( $stylesheets ) ):?>
<div class="ctc-input-cell ctc-section-toggle" id="ctc_additional_css_files"> <strong>
  <?php _e( 'Parse additional stylesheets', 'child-theme-configurator' ); ?>
  </strong> </div>
<div class="ctc-input-cell-wide ctc-section-toggle-content" id="ctc_additional_css_files_content">
  <p style="margin-top:0" class="howto">
    <?php _e( 'Stylesheets that are currently being loaded by the parent theme are automatically selected below (except for Bootstrap stylesheets which add a large amount data to the configuration). To further reduce overhead, select only the additional stylesheets you wish to customize.', 'child-theme-configurator' ); ?>
  </p>
  <ul>
<?php foreach ( $stylesheets as $stylesheet ): ?>
    <li>
      <label>
        <input class="ctc_checkbox" name="ctc_additional_css[]" type="checkbox" 
                value="<?php echo $stylesheet; ?>" />
        <?php echo esc_attr( $stylesheet ); ?></label>
    </li>
<?php endforeach; ?>
  </ul>
</div><?php 
endif; ?>
    </div>
    <div class="ctc-input-row clearfix">
<?php if ( '' == $hidechild && !$enqueueset ): ?>
      <div class="ctc-input-cell"> <strong>&nbsp;</strong> </div>
      <div class="ctc-input-cell-wide">
      <div class="update-nag">
        <strong><?php _e( 'Please read before you click:', 'child-theme-configurator' ); ?></strong>
        <p><?php _e( 'This plugin makes significant modifications to your child theme, to include changing CSS, removing comments and adding php functions.', 'child-theme-configurator' ); ?>
        <?php _e( 'If you are using an existing Child Theme,', 'child-theme-configurator' ); ?> <a href="<?php echo CHLD_THM_CFG_DOCS_URL; ?>/how-to-use/#duplicating-existing-child-themes" target="_blank"><?php _e( 'please consider using the Duplicate Child Theme option', 'child-theme-configurator' ); ?></a> <?php _e( 'before proceeding.', 'child-theme-configurator' ); ?></p>
      </div><p>&nbsp;</p></div>
<?php endif; ?>
      <div class="ctc-input-cell"> <strong>&nbsp;</strong> </div>
      <div class="ctc-input-cell">
        <input class="ctc_submit button button-primary" id="ctc_load_styles" name="ctc_load_styles"  type="submit" 
                value="<?php _e( 'Generate/Rebuild Child Theme Files', 'child-theme-configurator' ); ?>" disabled />
      </div>
    </div>
  </form>
<div id="ctc_debug_container"><?php do_action( 'chld_thm_cfg_print_debug' ); ?></div>
</div>
