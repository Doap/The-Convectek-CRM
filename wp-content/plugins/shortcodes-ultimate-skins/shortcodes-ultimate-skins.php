<?php
/*
  Plugin Name: Shortcodes Ultimate: Additional Skins
  Plugin URI: http://gndev.info/shortcodes-ultimate/skins/
  Version: 1.3.4
  Author: Vladimir Anokhin
  Author URI: http://gndev.info/
  Description: Extra set of skins for Shortcodes Ultimate
  Text Domain: sus
  Domain Path: /lang
  License: license.txt
 */

define( 'SUS_PLUGIN_FILE', __FILE__ );
define( 'SUS_PLUGIN_VERSION', '1.3.4' );

require_once 'inc/autoupdate-client.php';
require_once 'inc/skins.php';

new AutoUpdate_Client( SUS_PLUGIN_FILE, SUS_PLUGIN_VERSION, 'http://gndev.info/' );
