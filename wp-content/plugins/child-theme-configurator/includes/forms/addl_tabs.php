<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
if ( !defined( 'CHLD_THM_CFG_PLUGINS_VERSION' ) ):
?>
<a id="get_pro" href="?page=<?php echo CHLD_THM_CFG_MENU; ?>&amp;tab=get_pro" 
                    class="nav-tab<?php echo 'get_pro' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $hidechild; ?>>
<?php _e( 'Upgrade', 'chld_thm_cfg_plugins' ); ?>
</a>
<?php
endif;