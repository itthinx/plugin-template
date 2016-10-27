<?php
/**
 * class-plugin-template-service.php
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
 * An example service which uses admin-ajax.
 */
class Plugin_Template_Service {

	const NUMBER           = 'number';
	const DEFAULT_NUMBER   = 10;
	const ORDER            = 'order';
	const DEFAULT_ORDER    = 'DESC';
	const ORDER_BY         = 'order_by';
	const DEFAULT_ORDER_BY = 'date';

	const MAX_EXCERPT_WORDS      = 10;
	const MAX_EXCERPT_CHARACTERS = 50;
	const ELLIPSIS               = '&hellip;';

	const CACHE_LIFETIME     = 15; // seconds
	const RESULT_CACHE_GROUP = 'ixptr';
	const MILLISECONDS = 1000;

	/**
	 * Registers actions on wp_ajax_(action) and wp_ajax_nopriv_(action)
	 * 
	 * In our case, the request must carry action="plugin_template" so that those
	 * hooks are fired and we can handle the request through our
	 * Plugin_Template_Service::wp_ajax_plugin_template() method.
	 * 
	 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/wp_ajax_(action)
	 */
	public static function init() {
		add_action( 'wp_ajax_plugin_template', array( __CLASS__, 'wp_ajax_plugin_template' ) );
		add_action( 'wp_ajax_nopriv_plugin_template', array( __CLASS__, 'wp_ajax_plugin_template' ) );
	}

	/**
	 * Handles wp_ajax_plugin_template and wp_ajax_nopriv_plugin_template actions.
	 * The request must carry action='plugin_template' for these actions
	 * to be invoked and this handler to be triggered. This is done in
	 * plugin-template.js where the params are passed to the jQuery.post() call.
	 */
	public static function wp_ajax_plugin_template() {
		ob_start();
		$results = Plugin_Template_Service::request_results();
		$ob = ob_get_clean();
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && $ob ) {
			error_log( $ob );
		}
		echo json_encode( $results );
		exit;
	}

	/**
	 * Looks at the $_REQUEST to produce an array of valid parameters.
	 * 
	 * @return array
	 */
	private static function get_request_parameters() {

		$number = isset( $_REQUEST[self::NUMBER] ) ? intval( $_REQUEST[self::NUMBER] ) : self::DEFAULT_NUMBER;
		$order  = isset( $_REQUEST[self::ORDER] ) ? strtoupper( trim( $_REQUEST[self::ORDER] ) ) : self::DEFAULT_ORDER;
		switch( $order ) {
			case 'DESC' :
			case 'ASC' :
				break;
			default :
				$order = 'DESC';
		}
		$order_by    = isset( $_REQUEST[self::ORDER_BY] ) ? strtolower( trim( $_REQUEST[self::ORDER_BY] ) ) : self::DEFAULT_ORDER_BY;
		switch( $order_by ) {
			case 'date' :
			case 'title' :
			case 'ID' :
			case 'rand' :
				break;
			default :
				$order_by = 'date';
		}

		return array(
			'number'   => $number,
			'order'    => $order,
			'order_by' => $order_by,
		);
	}

	/**
	 * Obtains service results based on the request parameters.
	 * 
	 * @return array
	 */
	public static function request_results() {

		// parameters: number, order, order_by
		$parameters = self::get_request_parameters();
		$cache_key = self::get_cache_key( $parameters );

		// only approved comments!
		$parameters['status'] = 'approve';

		$results = wp_cache_get( $cache_key, self::RESULT_CACHE_GROUP, true );
		if ( $results === false ) {
			$comments = get_comments( $parameters );
			foreach( $comments as $comment ) {

				$output = '';

				$content = strip_tags( $comment->comment_content );

				// guard against shortcodes in comments
				$content = str_replace( "[", "&#91;", $content );
				$content = str_replace( "]", "&#93;", $content );

				// word and character limits
				$max_excerpt_words = apply_filters( 'plugin_template_max_excerpt_words', self::MAX_EXCERPT_WORDS );
				$max_excerpt_characters = apply_filters( 'plugin_template_max_excerpt_characters', self::MAX_EXCERPT_CHARACTERS );
				$ellipsis = apply_filters( 'plugin_template_ellipsis', self::ELLIPSIS );

				$add_ellipsis = false;

				// word limit
				$content = preg_replace( "/\s+/", " ", $content );
				$words = explode( " ", $content );
				$nwords = count( $words );
				for ( $i = 0; ( $i < $max_excerpt_words ) && ( $i < $nwords ); $i++ ) {
					$output .= $words[$i];
					if ( $i < $max_excerpt_words - 1) {
						$output .= " ";
					} else {
						$add_ellipsis = true;
					}
				}

				// character limit
				if ( $max_excerpt_characters > 0 ) {
					if ( function_exists( 'mb_substr' ) ) {
						$charset = get_bloginfo( 'charset' );
						$length = mb_strlen( $output, $charset );
						$output = mb_substr( $output, 0, $max_excerpt_characters, $charset );
						if ( mb_strlen( $output ) < $length ) {
							$add_ellipsis = true;
						}
					} else {
						$length = strlen( $output );
						$output = substr( $output, 0, $max_excerpt_characters );
						if ( strlen( $output ) < $length ) {
							$add_ellipsis = true;
						}
					}
				}

				if ( $add_ellipsis ) {
					$output .= $ellipsis;
				}

				$results[] = array(
					'url' => get_comment_link( $comment ),
					'comment_ID' => $comment->comment_ID,
					'comment_post_ID' => $comment->comment_post_ID,
					'content' => $output
				);
			}
			$cached = wp_cache_set( $cache_key, $results, self::RESULT_CACHE_GROUP, self::get_cache_lifetime() );
		}
		return $results;
	}

	/**
	 * Computes a cache key based on the parameters provided.
	 * 
	 * @param array $parameters
	 * @return string
	 */
	public static function get_cache_key( $parameters ) {
		return md5( implode( '-', $parameters ) );
	}

	/**
	 * Returns the cache lifetime for stored results in seconds.
	 * 
	 * @return int
	 */
	public static function get_cache_lifetime() {
		$l = intval( apply_filters( 'plugin_template_cache_lifetime', self::CACHE_LIFETIME ) );
		return $l;
	}

	/**
	 * Renders the HTML to use the service.
	 *
	 * Enqueues required scripts and styles.
	 *
	 * @param array $atts
	 * @param array $content not used
	 * @return string HTML
	 */
	public static function render( $atts = array(), $content = '' ) {

		Plugin_Template_Shortcodes::load_resources();

		$atts = shortcode_atts(
			array(
				'order'               => null,
				'order_by'            => null,
				'number'              => null,
			),
			$atts
		);

		$url_params = array();
		foreach( $atts as $key => $value ) {
			if ( $value !== null ) {
				$add = true;
				$value = strip_tags( trim( $value ) );
				switch( $key ) {
					case 'order' :
					case 'order_by' :
						break;
					case 'number' :
						$value = intval( $value );
						break;
					default :
						$add = false;
				}
				if ( $add ) {
					$url_params[$key] = urlencode( $value );
				}
			}
		}

		$output = '';

		$n            = rand();
		$container_id = 'plugin-template-' . $n;
		$results_id   = 'plugin-template-results-' .$n;

		$output .= sprintf(
			'<div id="%s" class="plugin-template">',
			esc_attr( $container_id )
		);

		$output .= sprintf( '<div id="%s" class="plugin-template-results">', $results_id );
		$output .= '</div>'; // .plugin-template-results

		$output .= '</div>'; // .plugin-template

		$js_args = array();
		$js_args[] = sprintf( 'no_results:"%s"', esc_js( apply_filters( 'plugin_template_no_results', __( 'There are no comments to show yet.', 'plugin-template' ) ) ) );
		$js_args = '{' . implode( ',', $js_args ) . '}';

		$post_target_url = add_query_arg( $url_params , admin_url( 'admin-ajax.php' ) );

		$output .= '<script type="text/javascript">';
		$output .= 'if ( typeof jQuery !== "undefined" ) {';
		$output .= 'jQuery(document).ready(function(){';
		$output .= 'var ixPluginTemplateCycle = function() {';
		$output .= sprintf( 'ixPluginTemplate.getResults(\'%s\', \'%s\', \'%s\', \'%s\');',
			esc_attr( $container_id ), // container selector
			esc_attr( $container_id . ' div.plugin-template-results' ), // results container selector
			$post_target_url,
			$js_args
		);
		$output .= sprintf( 'setTimeout(ixPluginTemplateCycle,%d);', self::CACHE_LIFETIME * self::MILLISECONDS );
		$output .= '};';
		$output .= 'ixPluginTemplateCycle();';
		$output .= '});'; // ready
		$output .= '}'; // if
		$output .= '</script>';

		return $output;
	}

}
Plugin_Template_Service::init();
