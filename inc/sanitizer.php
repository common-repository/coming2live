<?php
/**
 * Coming2Live sanitize functions.
 *
 * @package Coming2Live
 */

if ( ! function_exists( 'c2l_recursive_sanitizer' ) ) {
	/**
	 * Recursive sanitize a given values.
	 *
	 * @param  mixed  $values    The values.
	 * @param  string $sanitizer The sanitizer callback.
	 * @return mixed
	 */
	function c2l_recursive_sanitizer( $values, $sanitizer ) {
		if ( ! is_array( $values ) ) {
			return $sanitizer( $values );
		}

		foreach ( $values as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = c2l_recursive_sanitizer( $value, $sanitizer );
			} else {
				$value = $sanitizer( $value );
			}
		}

		return $values;
	}
}

if ( ! function_exists( 'c2l_sanitize_text' ) ) {
	/**
	 * Sanitizes a simple text string.
	 *
	 * @param  mixed $value The string to sanitize.
	 * @return string
	 */
	function c2l_sanitize_text( $value ) {
		return strip_tags( stripslashes( $value ) );
	}
}

if ( ! function_exists( 'c2l_sanitize_html' ) ) {

	/**
	 * Sanitizes content that could contain HTML.
	 *
	 * @param  mixed $value The HTML string to sanitize.
	 * @return string
	 */
	function c2l_sanitize_html( $value ) {
		return balanceTags( wp_kses_post( $value ), true );
	}
}

if ( ! function_exists( 'c2l_sanitize_color' ) ) {
	/**
	 * Sanitizes a color value with support bold hex & rgba.
	 *
	 * @param  string $color The value to sanitize.
	 * @return string
	 */
	function c2l_sanitize_color( $color ) {
		if ( empty( $color ) ) {
			return '';
		}

		if ( false !== strpos( $color, '#' ) ) {
			return sanitize_hex_color( $color );
		}

		if ( false !== strpos( $color, 'rgba(' ) ) {
			return c2l_sanitize_rgba_color( $color );
		}

		return '';
	}
}

if ( ! function_exists( 'c2l_sanitize_rgba_color' ) ) {
	/**
	 * Sanitizes an RGBA color value.
	 *
	 * @param  string $color The RGBA color value to sanitize.
	 * @return string
	 */
	function c2l_sanitize_rgba_color( $color ) {
		// Trim unneeded whitespace.
		$color = trim( str_replace( ' ', '', $color ) );

		sscanf( $color, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );

		if ( ( $red >= 0 && $red <= 255 )
			&& ( $green >= 0 && $green <= 255 )
			&& ( $blue >= 0 && $blue <= 255 )
			&& ( $alpha >= 0 && $alpha <= 1 ) ) {
			return "rgba({$red},{$green},{$blue},{$alpha})";
		}

		return '';
	}
}

if ( ! function_exists( 'c2l_sanitize_page_ids' ) ) {
	/**
	 * Sanitizes comma-separated list of IDs.
	 *
	 * @param  string $list The value to sanitize.
	 * @return string
	 */
	function c2l_sanitize_page_ids( $list ) {
		return implode( ', ', wp_parse_id_list( $list ) );
	}
}

if ( ! function_exists( 'c2l_sanitize_redirect_another_url' ) ) {
	/**
	 * Sanitizes redirect to another URL (prevent redirect loop).
	 *
	 * @param  string $location The location to sanitize.
	 * @return string
	 */
	function c2l_sanitize_redirect_another_url( $location ) {
		// Need to look at the URL the way it will end up in wp_redirect().
		$location = wp_sanitize_redirect( $location );

		// Try validate the URL, if return empty string that mean good.
		$validated = wp_validate_redirect( $location );

		return ( '' === $validated ) ? esc_url_raw( $location ) : '';
	}
}

if ( ! function_exists( 'c2l_sanitize_ga_code' ) ) {
	/**
	 * Sanitizes google analytics code.
	 *
	 * @link https://gist.github.com/faisalman/924970
	 *
	 * @param  string $code The code to sanitize.
	 * @return string|null
	 */
	function c2l_sanitize_ga_code( $code ) {
		if ( preg_match( '/^ua-\d{4,9}-\d{1,4}$/i', (string) $code ) ) {
			return $code;
		}
	}
}

if ( ! function_exists( 'c2l_sanitize_social' ) ) {
	/**
	 * Sanitize social name & link.
	 *
	 * @param  array $social The social data.
	 * @return array|null
	 */
	function c2l_sanitize_social( $social ) {
		$providers = array_keys( c2l_social_providers() );

		// Return "NULL" when social name is invalid.
		if ( ! isset( $social['name'], $social['link'] ) || ! in_array( $social['name'], $providers ) ) {
			return;
		}

		// Escape the link.
		$social['link'] = esc_url_raw( $social['link'] );
		if ( empty( $social['link'] ) ) {
			return;
		}

		return $social;
	}
}

if ( ! function_exists( 'c2l_parse_background_atts_args' ) ) {
	/**
	 * Parse the background attributes args.
	 *
	 * @param  array|mixed $args The background attribute args.
	 * @return array
	 */
	function c2l_parse_background_atts_args( $args ) {
		return shortcode_atts([
			'background_position'   => 'center center',
			'background_size'       => 'auto',
			'background_repeat'     => 'no-repeat',
			'background_attachment' => 'fixed',
		], $args );
	}
}

if ( ! function_exists( 'c2l_sanitize_background_atts' ) ) {
	/**
	 * Sanitize the background image attributes.
	 *
	 * @param  array $attributes The background attributes data.
	 * @return array
	 */
	function c2l_sanitize_background_atts( $attributes ) {
		$attributes = c2l_parse_background_atts_args( $attributes );

		if ( ! preg_match( '/^(left|center|right)\s(top|center|bottom)$/', $attributes['background_position'] ) ) {
			$attributes['background_position'] = 'center center';
		}

		if ( ! in_array( $attributes['background_size'], [ 'auto', 'contain', 'cover' ], true ) ) {
			$attributes['background_size'] = 'auto';
		}

		if ( ! in_array( $attributes['background_repeat'], [ 'no-repeat', 'repeat' ], true ) ) {
			$attributes['background_repeat'] = 'no-repeat';
		}

		if ( ! in_array( $attributes['background_attachment'], [ 'scroll', 'fixed' ], true ) ) {
			$attributes['background_attachment'] = 'fixed';
		}

		return $attributes;
	}
}

if ( ! function_exists( 'c2l_parse_background_args' ) ) {
	/**
	 * Parse the background data.
	 *
	 * @param  array|mixed $args The background data.
	 * @return array
	 */
	function c2l_parse_background_args( $args ) {
		return shortcode_atts([
			'type'                       => 'solid',
			'background_color'           => '',
			'background_image'           => '',
			'background_image_atts'      => [],
			'background_slider'          => [],

			'background_gradient1'       => '',
			'background_gradient2'       => '',
			'background_gradient3'       => '',
			'background_gradient4'       => '',

			'background_video_source'    => 'youtube',
			'background_video_link'      => '',
			'background_video_file'      => '',
			'background_video_thumbnail' => '',
			'youtube_filter_blur'        => 0,

			'background_triangle'        => '',
			'triangle_color'            => [],
			
			'fss_light_ambient'        => '',
			'fss_light_diffuse'        => '',
			'fss_material_ambient'        => '',
			'fss_material_diffuse'        => '',
		], $args );
	}
}

if ( ! function_exists( 'c2l_sanitize_background' ) ) {
	/**
	 * Sanitize the background data.
	 *
	 * @param  array $background The background data.
	 * @return array
	 */
	function c2l_sanitize_background( $background ) {
		if ( empty( $background ) ) {
			return [];
		}

		// Parse the background data.
		$background = c2l_parse_background_args( $background );

		$background['background_color'] = c2l_sanitize_color( $background['background_color'] );
		$background['background_image'] = esc_url_raw( $background['background_image'] );
		$background['background_image_atts'] = c2l_sanitize_background_atts( $background['background_image_atts'] );

		$background['background_gradient1'] = c2l_sanitize_color( $background['background_gradient1'] );
		$background['background_gradient2'] = c2l_sanitize_color( $background['background_gradient2'] );
		$background['background_gradient3'] = c2l_sanitize_color( $background['background_gradient3'] );
		$background['background_gradient4'] = c2l_sanitize_color( $background['background_gradient4'] );

		$background['background_video_link'] = esc_url_raw( $background['background_video_link'] );
		if ( 'youtube' === $background['background_video_source'] && ! preg_match( '#^https?://(?:www\.)?(?:youtube\.com/watch|youtu\.be/)#', $background['background_video_link'] ) ) {
			$background['background_video_link'] = '';
		}

		$background['background_video_file'] = esc_url_raw( $background['background_video_file'] );
		$background['background_video_thumbnail'] = esc_url_raw( $background['background_video_thumbnail'] );

		$background['background_triangle'] = c2l_sanitize_color( $background['background_triangle'] );
		$background['triangle_color'] = array_map( 'c2l_sanitize_color', (array) $background['triangle_color'] );

		return $background;
	}
}
