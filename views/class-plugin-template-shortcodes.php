<?php
/**
 * class-plugin-template-shortcodes.php
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
 * Shortcode definitions and renderers.
 */
class Plugin_Template_Shortcodes {

	/**
	 * Adds shortcodes.
	 */
	public static function init() {
		add_shortcode( 'plugin_template', array( __CLASS__, 'plugin_template' ) );
	}

	/**
	 * Enqueues scripts and styles.
	 */
	public static function load_resources() {
		$options = Plugin_Template::get_options();
		$enable = isset( $options[Plugin_Template::ENABLE] ) ? $options[Plugin_Template::ENABLE] : false;
		wp_enqueue_script( 'plugin-template' );
		wp_enqueue_style( 'plugin-template' );
	}

	/**
	 * Shortcode handler, renders the content to be produced by the shortcode.
	 * 
	 * Enqueues required scripts and styles.
	 * 
	 * @param array $atts
	 * @param array $content not used
	 * @return string form HTML
	 */
	public static function plugin_template( $atts = array(), $content = '' ) {

		self::load_resources();

		$atts = shortcode_atts(
			array(
				'title_tag' => 'h2',
				'title'   => __( 'Plugin Template', 'plugin-template' ),
				'content' => __(' Example content from the Plugin Template plugin.', 'plugin-template' ),
			),
			$atts
		);

		$atts['title_tag'] = strtolower( $atts['title_tag'] );
		switch( $atts['title_tag'] ) {
			case 'h1' :
			case 'h2' :
			case 'h3' :
			case 'h4' :
			case 'h5' :
			case 'h6' :
				break;
			default :
				$atts['title_tag'] = 'h1';
		}
		$atts['title'] = strip_tags( trim( $atts['title'] ) );
		$atts['content'] = strip_tags( trim( $atts['content'] ) );

		$output = '<div class="plugin-template">';
		if ( !empty( $atts['title'] ) ) {
			$output .= '<div class="plugin-template-title">';
			$output .= sprintf( '<%s>', esc_attr( $atts['title_tag'] ) );
			$output .= esc_html( $atts['title'] );
			$output .= sprintf( '</%s>', esc_attr( $atts['title_tag'] ) );
			$output .= '</div>';
		}
		if ( !empty( $atts['content'] ) ) {
			$output .= '<div class="plugin-template-content">';
			$output .= esc_html( $atts['content'] );
			$output .= '</div>';
		}

		return $output;
	}

}
Plugin_Template_Shortcodes::init();
