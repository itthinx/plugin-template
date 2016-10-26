<?php
/**
 * class-plugin-template-admin.php
 *
 * Copyright (c) @todo
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author itthinx
 * @package plugin-template
 * @since 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings.
 */
class Plugin_Template_Admin {

	const NONCE                 = 'plugin-template-admin-nonce';
	const HELP_POSITION         = 999;
	const MENU_SLUG             = 'plugin-template';
	const MENU_SLUG_SETTINGS    = 'plugin-template-settings';
	const MENU_POSITION         = '47.013579';

	/**
	 * Register a hook on the init action.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
	}

	/**
	 * Registers admin scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_register_style( 'plugin-template-admin', PLUGIN_TEMPLATE_PLUGIN_URL . '/css/admin.css', array(), PLUGIN_TEMPLATE_PLUGIN_VERSION );
		wp_register_style( 'plugin-template-admin-menu', PLUGIN_TEMPLATE_PLUGIN_URL . '/css/admin-menu.css', array(), PLUGIN_TEMPLATE_PLUGIN_VERSION );
	}

	/**
	 * Enqueues our admin stylesheet.
	 */
	public static function admin_print_styles() {
		wp_enqueue_style( 'plugin-template-admin' );
	}

	/**
	 * Enqueues our admin menu stylesheet (on all admin pages,
	 * as it sets the icon for our menu).
	 */
	public static function admin_print_styles_menu() {
		wp_enqueue_style( 'plugin-template-admin-menu' );
	}

	/**
	 * Register our menu and its sections.
	 */
	public static function admin_menu() {
		$pages = array();
		$pages[] = add_menu_page(
			'Plugin Template', // don't translate
			'Plugin Template', // don't translate, also note core bug http://core.trac.wordpress.org/ticket/18857
			Plugin_Template::MANAGE_PLUGIN_TEMPLATE,
			self::MENU_SLUG,
			array( __CLASS__, 'plugin_template' ),
			'none', // we use our admin-menu.css to set the icon as a background image
			self::MENU_POSITION
		);
		$pages[] = add_submenu_page(
			self::MENU_SLUG,
			__( 'Settings', 'plugin-template' ),
			__( 'Settings', 'plugin-template' ),
			Plugin_Template::MANAGE_PLUGIN_TEMPLATE,
			self::MENU_SLUG_SETTINGS,
			array( __CLASS__, 'plugin_template_settings' )
		);
		foreach( $pages as $page ) {
			add_action( 'admin_print_styles-' . $page, array( __CLASS__, 'admin_print_styles' ) );
		}
		// this one is for all admin pages, sets the icon menu
		add_action( 'admin_print_styles', array( __CLASS__, 'admin_print_styles_menu' ) );
	}

	/**
	 * Admin setup.
	 */
	public static function wp_init() {
		add_filter( 'plugin_action_links_'. plugin_basename( PLUGIN_TEMPLATE_FILE ), array( __CLASS__, 'admin_settings_link' ) );
		add_action( 'current_screen', array( __CLASS__, 'current_screen' ), self::HELP_POSITION );
	}

	/**
	 * Adds plugin links.
	 *
	 * @param array $links
	 * @param array $links with additional links
	 */
	public static function admin_settings_link( $links ) {
		if ( current_user_can( Plugin_Template::MANAGE_PLUGIN_TEMPLATE ) ) {
			$url = self::get_admin_section_url();
			$links[] = '<a href="' . esc_url( $url ) . '">' . __( 'Settings', 'plugin-template' ) . '</a>';
			$links[] = '<a href="http://github.com/itthinx/plugin-template/">' . __( 'Plugin Template on GitHub', 'plugin-template' ) . '</a>';
		}
		return $links;
	}

	/**
	 * Adds the help sections.
	 */
	public static function current_screen() {

		$screen = get_current_screen();
		if ( $screen && stripos( $screen->id,'plugin-template' ) !== false ) {

			$screen->add_help_tab( array(
				'id'      => 'plugin-template-github',
				'title'   => __( 'Plugin Template', 'plugin-template' ),
				'content' =>
					'<div class="plugin-template-help">' .
					'<h3>' . __( 'Plugin Template', 'plugin-template' ) . '</h3>' .
					'<h4>' . __( 'GitHub', 'plugin-template' ) . '</h4>' .
					'<p>' .
					__( 'Please refer to the <a href="http://github.com/itthinx/plugin-template/">Plugin Template</a> repository for this plugin.', 'plugin-template' ) .
					'</p>' .
					'</div>'
			) );

		}
	}

	/**
	 * Capture the request to save our settings and invoke our handler.
	 * Nonce is not checked here.
	 */
	public static function admin_init() {
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'plugin-template-save-settings' ) {
			self::save();
		}
	}

	/**
	 * Records changes made to the settings if the request is deemed valid and authorized.
	 * Checks nonce and authorization.
	 */
	public static function save() {

		if ( !current_user_can( Plugin_Template::MANAGE_PLUGIN_TEMPLATE ) ) {
			wp_die( __( 'Access denied.', 'plugin-template' ) );
		}

		$options = Plugin_Template::get_options();
		if ( isset( $_POST['submit'] ) ) {
			if ( wp_verify_nonce( $_POST[self::NONCE], 'set' ) ) {
				$options[Plugin_Template::ENABLE] = isset( $_POST[Plugin_Template::ENABLE] );
				$options[Plugin_Template::TEXT]   = isset( $_POST[Plugin_Template::TEXT] ) ? trim( wp_strip_all_tags( $_POST[Plugin_Template::TEXT] ) ) : '';
				Plugin_Template::set_options( $options );
			}
		}
	}

	public static function plugin_template_settings() {
		echo __( 'Just another section.', 'plugin-template' );
	}

	/**
	 * Renders the admin section.
	 */
	public static function plugin_template() {

		global $wpdb;

		if ( !current_user_can( Plugin_Template::MANAGE_PLUGIN_TEMPLATE ) ) {
			wp_die( __( 'Access denied.', 'plugin-template' ) );
		}

		$options = Plugin_Template::get_options();
		$enable = isset( $options[Plugin_Template::ENABLE] ) ? $options[Plugin_Template::ENABLE] : false;
		$text   = isset( $options[Plugin_Template::TEXT] ) ? $options[Plugin_Template::TEXT] : '';

		echo '<div class="plugin-template">';
		echo '<form action="" name="options" method="post">';
		echo '<div>';

		echo '<h1 class="section-heading">' . __( 'Plugin Template', 'plugin-template' ) . '</h1>';

		echo '<p>';
		echo '<label>';
		printf( '<input name="%s" type="checkbox" %s />', Plugin_Template::ENABLE, $enable ? ' checked="checked" ' : '' );
		echo ' ';
		_e( 'Enable it?', 'plugin-template');
		echo '</label>';
		echo '</p>';
		echo '<p class="description">';
		_e( 'An example setting ...', 'plugin-template');
		echo '</p>';

		echo '<p>';
		echo '<label>';
		_e( 'Text', 'plugin-template');
		echo '<br/>';
		printf( '<textarea style="font-family:monospace;width:50%%;height:25em;" name="%s">%s</textarea>', Plugin_Template::TEXT, stripslashes( esc_textarea( $text ) ) );
		echo '</label>';
		echo '</p>';
		echo '<p class="description">';
		_e( 'Example text setting ...', 'plugin-template');
		echo '</p>';

		global $hide_save_button;
		$hide_save_button = true;

		wp_nonce_field( 'set', self::NONCE );
		echo '<input type="hidden" name="action" value="plugin-template-save-settings" />';

		echo '<p>';
		echo '<input class="button button-primary" type="submit" name="submit" value="' . __( 'Save', 'plugin-template' ) . '"/>';
		echo '</p>';

		echo '</div>';
		echo '</form>';
		echo '</div>';

	}
}
Plugin_Template_Admin::init();
