<?php
/*
Plugin Name: Aleph
Plugin URI: http://alumnos.dcc.uchile.cl/~egraells/
Description: Aleph implements profiles, paginated and customizable user lists.
Author: Eduardo Graells
Version: 0.8.1
Author URI: http://alumnos.dcc.uchile.cl/~egraells/
Donate link: https://www.amazon.com/gp/registry/wishlist/1Q7WE8X1H7QHV

This plugin is licensed under the terms of the General Public License, Version 3. Please see the file license.txt.

*/

define('ALEPH_PATH', PLUGINDIR. '/' .plugin_basename(dirname(__FILE__)));
define('ALEPH_URL', WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)));

require_once('base-classes/UserQuery.php');

require_once('lib/template-tags.php');
require_once('lib/aleph-user-tags.php');
require_once('lib/aleph-link-tags.php');
require_once('lib/widgets.php');
require_once('lib/core.php');

register_activation_hook(__FILE__, 'aleph_activation');
register_deactivation_hook(__FILE__, 'aleph_deactivation');

add_action('init', 'aleph_settings', 25);

if (is_admin()) {
	add_action('init', 'aleph_initialize', 30);
} else {
	add_action('parse_request', 'aleph_detect_custom_vars');
	add_action('init', 'aleph_hook_custom_vars');	
}

add_filter('rewrite_rules_array', 'aleph_filter_rewrite_rules');
add_action('wp_footer', 'aleph_credits');


?>