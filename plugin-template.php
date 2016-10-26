<?php
/**
 * plugin-template.php
 *
 * Copyright (c) 2016 @todo
 * 
 * This code is released under the GNU General Public License Version 3.
 * The following additional terms apply to all files as per section
 * "7. Additional Terms." See COPYRIGHT.txt and LICENSE.txt.
 * 
 * @author itthinx
 * @package plugin-template
 * @since 1.0.0
 *
 * Plugin Name: Plugin Template
 * Plugin URI: http://www.itthinx.com/plugins/plugin-template/
 * Description: A template for WordPress plugins.
 * Version: 1.0.0
 * Author: itthinx
 * Author URI: http://www.itthinx.com
 * Text Domain: plugin-template
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PLUGIN_TEMPLATE_PLUGIN_VERSION', '1.0.0' );
define( 'PLUGIN_TEMPLATE_PLUGIN', 'plugin-template' );
define( 'PLUGIN_TEMPLATE_FILE', __FILE__ );
define( 'PLUGIN_TEMPLATE_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_TEMPLATE_CORE_LIB', PLUGIN_TEMPLATE_CORE_DIR . 'core' );
define( 'PLUGIN_TEMPLATE_ADMIN_LIB', PLUGIN_TEMPLATE_CORE_DIR . 'admin' );
define( 'PLUGIN_TEMPLATE_VIEWS_LIB', PLUGIN_TEMPLATE_CORE_DIR . 'views' );
define( 'PLUGIN_TEMPLATE_PLUGIN_URL', plugins_url( 'plugin-template' ) );
require_once PLUGIN_TEMPLATE_CORE_LIB . '/class-plugin-template.php';
