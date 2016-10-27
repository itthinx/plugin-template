<?php
/**
 * class-plugin-template-shortcodes.php
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
				'show_settings' => 'no',
				'content' => __(' Example content from the Plugin Template plugin.', 'plugin-template' ),
			),
			$atts
		);

		$atts['content'] = strip_tags( trim( $atts['content'] ) );

		$options = Plugin_Template::get_options();
		$enable  = isset( $options[Plugin_Template::ENABLE] ) ? $options[Plugin_Template::ENABLE] : false;
		$text    = isset( $options[Plugin_Template::TEXT] ) ? $options[Plugin_Template::TEXT] : '';

		$output = '';

		if ( !empty( $atts['content'] ) ) {
			$output .= '<div class="plugin-template-content">';
			$output .= '<p>';
			$output .= esc_html( $atts['content'] );
			$output .= '</p>';
			$output .= '</div>';
		}

		if ( strtolower( $atts['show_settings'] ) == 'yes' ) {
			$output .= '<div>';
			$output .= '<p>';
			$output .= sprintf( __( 'Enable : %s', 'plugin_template' ), $enable ? __( 'yes', 'plugin-template' ) : __( 'no', 'plugin-template' ) );
			$output .= '</p>';
			$output .= '<p>';
			$output .= sprintf( __( 'Text : %s', 'plugin-template' ), esc_html( stripslashes( $text ) ) );
			$output .= '</p>';
			$output .= '</div>';
		}
		
		if ( $enable ) {
			$output .= Plugin_Template_Service::render();
		}

		if ( !empty( $output ) ) {
			$output =
				'<div class="plugin-template">' .
				$output .
				'</div>';
		}

		return $output;
	}

}
Plugin_Template_Shortcodes::init();
