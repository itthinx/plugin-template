<?php
/**
 * class-plugin-template.php
 *
 * Copyright (c) 2016 www.itthinx.com
 *
 * @author itthinx
 * @package plugin-template
 * @since 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Template main plugin class.
 * Boots the plugin and loads essentials.
 */
class Plugin_Template {

	/**
	 * The plugin's option key.
	 * @var string
	 */
	const OPTIONS = 'plugin-template';

	/**
	 * Table prefix for this plugin's tables.
	 * @var string
	 */
	const DB_PREFIX = 'plugin_template_';

	/**
	 * This capability allows to control the plugin settings.
	 * @var string
	 */
	const MANAGE_PLUGIN_TEMPLATE = 'manage_plugin_template';

	/**
	 * Holds messages shown on the administrative back end if any.
	 * @var array of string
	 */
	private static $admin_messages = array();

	/**
	 * Used to refer to an example plugin setting (to store a yes/no option).
	 * 
	 * @var string
	 */
	const ENABLE = 'enable';

	/**
	 * Another example plugin setting (to store some text).
	 * 
	 * @var string
	 */
	const TEXT = 'text';

	/**
	 * Put hooks in place and activate.
	 */
	public static function init() {
		register_activation_hook( PLUGIN_TEMPLATE_FILE, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( PLUGIN_TEMPLATE_FILE, array( __CLASS__, 'deactivate' ) );
		//register_uninstall_hook( PLUGIN_TEMPLATE_FILE, array( __CLASS__, 'uninstall' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wp_enqueue_scripts' ) );
		require_once PLUGIN_TEMPLATE_CORE_LIB . '/class-plugin-template-service.php';
		require_once PLUGIN_TEMPLATE_VIEWS_LIB . '/class-plugin-template-shortcodes.php';
		require_once PLUGIN_TEMPLATE_VIEWS_LIB . '/class-plugin-template-widget.php';
		if ( is_admin() ) {
			require_once PLUGIN_TEMPLATE_ADMIN_LIB . '/class-plugin-template-admin.php';
		}
	}

	/**
	 * Loads translations.
	 */
	public static function wp_init() {
		// translations
		load_plugin_textdomain( 'plugin-template', null, 'plugin-template/languages' );
	}

	/**
	 * Activate plugin action.
	 * 
	 * Adds the plugin's capabilities to the administrator role, or
	 * in lack thereof, to those roles that have the activate_plugins
	 * capability.
	 * 
	 * @param boolean $network_wide
	 */
	public static function activate( $network_wide = false ) {
		global $wp_roles;
		if ( $administrator_role = $wp_roles->get_role( 'administrator' ) ) {
			$administrator_role->add_cap( self::MANAGE_PLUGIN_TEMPLATE );
		} else {
			foreach ( $wp_roles->role_objects as $role ) {
				if ($role->has_cap( 'activate_plugins' ) ) {
					$role->add_cap( self::MANAGE_PLUGIN_TEMPLATE );
				}
			}
		}
	}

	/**
	 * Deactivate plugin action.
	 * 
	 * Currently not doing anything.
	 * 
	 * @param boolean $network_wide
	 */
	public static function deactivate( $network_wide = false ) {
	}

	/**
	 * Uninstall plugin action.
	 * 
	 * Currently not used.
	 */
	public static function uninstall() {
	}

	/**
	 * Prints admin notices.
	 */
	public static function admin_notices() {
		if ( !empty( self::$admin_messages ) ) {
			foreach ( self::$admin_messages as $msg ) {
				echo $msg;
			}
		}
	}

	/**
	 * Register or enqueues scripts and styles.
	 * 
	 * Although the name of the hook suggests this is for scripts, it is used
	 * to register styles as well.
	 * 
	 * Don't just enqueue your scripts and styles on all pages, rather register
	 * them here and enqueue them where needed.
	 */
	public static function wp_enqueue_scripts() {
		wp_register_script(
			'plugin-template',
			defined( 'PLUGIN_TEMPLATE_DEBUG' ) && PLUGIN_TEMPLATE_DEBUG ?
				PLUGIN_TEMPLATE_PLUGIN_URL . '/js/plugin-template.js' :
				PLUGIN_TEMPLATE_PLUGIN_URL . '/js/plugin-template.min.js',
			array( 'jquery' ),
			PLUGIN_TEMPLATE_PLUGIN_VERSION,
			true
		);
		wp_register_style(
			'plugin-template',
			PLUGIN_TEMPLATE_PLUGIN_URL . '/css/plugin-template.css',
			array(),
			PLUGIN_TEMPLATE_PLUGIN_VERSION
		);
	}

	/**
	 * Get plugin options.
	 * 
	 * @return array
	 */
	public static function get_options() {
		$data = get_option( self::OPTIONS, null );
		if ( $data === null ) {
			if ( add_option( self::OPTIONS, array(), '', 'no' ) ) {
				$data = get_option( self::OPTIONS, null );
			}
		}
		return $data;
	}

	/**
	 * Set plugin options.
	 * 
	 * @param array $data
	 */
	public static function set_options( $data ) {
		$current_data = get_option( self::OPTIONS, null );
		if ( $current_data === null ) {
			add_option( self::OPTIONS, $data, '', 'no' );
		} else {
			update_option( self::OPTIONS, $data );
		}
	}
}
Plugin_Template::init();
