<?php
/**
 * class-plugin-template-widget.php
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
 * Live search widget.
 */
class Plugin_Template_Widget extends WP_Widget {

	static $the_name = '';

	/**
	 * @var string cache id
	 */
	static $cache_id = 'plugin_template_widget';

	/**
	 * @var string cache flag
	 */
	static $cache_flag = 'widget';

	/**
	 * 
	 * @var array 
	 */
	static $defaults = array();

	/**
	 * Initialize.
	 */
	static function init() {
		add_action( 'widgets_init', array( __CLASS__, 'widgets_init' ) );
		self::$the_name = __( 'Plugin Template', 'plugin-template' );
	}

	/**
	 * Registers the widget.
	 */
	static function widgets_init() {
		register_widget( 'Plugin_Template_Widget' );
	}

	/**
	 * Creates the widget.
	 */
	function __construct() {
		parent::__construct(
			self::$cache_id,
			self::$the_name,
			array(
				'description' => __( 'The Plugin Template Widget', 'plugin-template' )
			)
		);
	}

	/**
	 * Clears cached widget.
	 */
	static function cache_delete() {
		wp_cache_delete( self::$cache_id, self::$cache_flag );
	}

	/**
	 * Widget output
	 * 
	 * @see WP_Widget::widget()
	 * @link http://codex.wordpress.org/Class_Reference/WP_Object_Cache
	 */
	function widget( $args, $instance ) {

		// This is done within the shortcode but the required scripts can
		// go missing if we don't do it here, too.
		Plugin_Template_Shortcodes::load_resources();

		$cache = wp_cache_get( self::$cache_id, self::$cache_flag );
		if ( ! is_array( $cache ) ) {
			$cache = array();
		}
		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );

		$output = '';

		$output .= $before_widget;
		if ( !empty( $title ) ) {
			$output .= $before_title . $title . $after_title;
		}
		$instance['title'] = $instance['query_title'];

		$output .= Plugin_Template_Shortcodes::plugin_template( $instance );
		$output .= $after_widget;

		echo $output;

		$cache[$args['widget_id']] = $output;
		wp_cache_set( self::$cache_id, $cache, self::$cache_flag );

	}

	/**
	 * Save widget options
	 * 
	 * @see WP_Widget::update()
	 */
	function update( $new_instance, $old_instance ) {

		global $wpdb;

		$settings = $old_instance;

		// widget title
		$settings['title'] = trim( strip_tags( $new_instance['title'] ) );

		// whether to show plugin settings
		$settings['show_settings'] = !empty( $new_instance['show_settings'] ) ? 'yes' : 'no';

		// some content
		$settings['content'] = trim( strip_tags( $new_instance['content'] ) );

		$this->cache_delete();

		return $settings;
	}

	/**
	 * Output admin widget options form
	 * 
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {

		extract( self::$defaults );

		// title
		$widget_title = isset( $instance['title'] ) ? $instance['title'] : "";
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'The widget title.', 'plugin-template' ) ) );
		echo __( 'Title', 'plugin-template' );
		echo '<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr( $widget_title ) . '" />';
		echo '</label>';
		echo '</p>';

		$show_settings = isset( $instance['show_settings'] ) ? $instance['show_settings'] : 'yes';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Whether to show the plugin settings in the widget.', 'plugin-template' ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'show_settings' ),
			$this->get_field_name( 'show_settings' ),
			$show_settings == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Show Settings?', 'plugin-template' );
		echo '</label>';
		echo '</p>';

		// some content
		$placeholder = isset( $instance['content'] ) ? $instance['content'] : __( 'Content', 'plugin-template' );
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Some example text field.', 'plugin-template' ) ) );
		echo __( 'Content', 'plugin-template' );
		echo ' ';
		echo '<input id="' . $this->get_field_id( 'content' ) . '" name="' . $this->get_field_name( 'content' ) . '" type="text" value="' . esc_attr( $content ) . '" />';
		echo '</label>';
		echo '</p>';

	}

}

Plugin_Template_Widget::init();
