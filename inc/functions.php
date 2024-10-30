<?php
/**
 * Coming2Live functions.
 *
 * @package Coming2Live
 */

/**
 * Get the Coming2Live class instance.
 *
 * @return \Coming2Live\Plugin
 */
function coming2live() {
	return Coming2Live::get_instance();
}

/**
 * Return the plugin URL.
 *
 * @param  string $path Optional, extra path to added.
 * @return string
 */
function c2l_plugin_url( $path = null ) {
	return coming2live()->get_plugin_url( $path );
}

/**
 * Determines if the C2L is enabled.
 *
 * @return boolean
 */
function c2l_enabled() {
	return coming2live()->is_enabled();
}

/**
 * Is current site is under a maintenance mode.
 *
 * @param  string|null $mode If null will check any mode.
 * @return boolean
 */
function cl2_active_mode( $mode = null ) {
	return coming2live()->is_active_mode( $mode );
}

/**
 * Retrieves an option by key-name.
 *
 * @param  string $key     The key name.
 * @param  mixed  $default The default value.
 * @return mixed
 */
function c2l_option( $key, $default = null ) {
	return coming2live()->get_option( $key, $default );
}

/**
 * Retrieves theme modification by key-name.
 *
 * @param  string $key     The key name.
 * @param  mixed  $default The default value.
 * @return mixed
 */
function c2l_theme_mod( $key, $default = null ) {
	return coming2live()->get_theme_mod( $key, $default );
}

/**
 * Get a current theme object.
 *
 * @return \Coming2Live\C2L_Theme
 */
function c2l_theme() {
	return coming2live()->get_theme();
}

/**
 * Get the theme object.
 *
 * @param  string $theme Optional, the theme name.
 * @return \Coming2Live\C2L_Theme|null
 */
function c2l_get_theme( $theme = null ) {
	return coming2live()->get_theme( $theme );
}

/**
 * Returns the theme directory.
 *
 * @param  string $theme Optional, the theme name.
 * @return string
 */
function c2l_get_theme_dir( $theme = null ) {
	return c2l_get_theme( $theme )->get_theme_dir();
}

/**
 * Returns the theme URI.
 *
 * @param  string $theme Optional, the theme name.
 * @return string
 */
function c2l_get_theme_uri( $theme = null ) {
	return c2l_get_theme( $theme )->get_theme_uri();
}

/**
 * Retrieves the URI of current theme stylesheet.
 *
 * @return string
 */
function c2l_get_stylesheet_uri() {
	/**
	 * Filters the URI of the current theme stylesheet.
	 *
	 * @param string $stylesheet_uri Stylesheet URI for the current theme.
	 */
	return apply_filters( 'stylesheet_uri', c2l_get_theme_uri() . 'style.css' );
}

/**
 * Determines if current theme support a feature.
 *
 * @param  string|array $feature The theme feature.
 * @return bool|mixed
 */
function c2l_theme_support( $feature ) {
	$features = is_array( $feature ) ? $feature : func_get_args();

	return c2l_theme()->support( $features );
}

/*
| --------------------------------------------------------------------
| Helpers.
| --------------------------------------------------------------------
*/

/**
 * Return a list supported mode.
 *
 * @return array
 */
function c2l_get_modes() {
	return apply_filters( 'c2l_get_modes', [
		Coming2Live::COMING_SOON_MODE => esc_html__( 'Coming Soon', 'coming2live' ),
		Coming2Live::MAINTANANCE_MODE => esc_html__( 'Maintanance Mode', 'coming2live' ),
		Coming2Live::REDIRECT_MODE    => esc_html__( 'Redirect Mode', 'coming2live' ),
	]);
}

/**
 * Define a constant if not defined.
 *
 * @param  string $constant The constant name.
 * @param  mixed  $value    The constant value.
 * @return void
 */
function c2l_maybe_define( $constant, $value ) {
	if ( ! defined( $constant ) ) {
		define( $constant, $value );
	}
}

/**
 * Sets nocache_headers which also disables page caching.
 *
 * @return void
 */
function c2l_nocache_headers() {
	// Do not cache.
	c2l_maybe_define( 'DONOTCACHEPAGE', true );
	c2l_maybe_define( 'DONOTCACHEOBJECT', true );
	c2l_maybe_define( 'DONOTCACHEDB', true );

	// Set the headers to prevent caching for the different browsers.
	nocache_headers();
}

/**
 * Build a CSS attributes string from an array.
 *
 * @param  array $attributes The CSS attributes.
 * @return string
 */
function c2l_css_attributes( $attributes ) {
	$atts = '';

	foreach ( (array) $attributes as $key => $value ) {
		// CSS atribute can not be have a empty value.
		if ( empty( $value ) ) {
			continue;
		}

		$atts .= "\n" . $key . ': ' . $value . ';';
	}

	return $atts;
}

/**
 * Build an HTML attribute string from an array.
 *
 * @param  array $attributes The HTML attributes.
 * @return string
 */
function c2l_html_attributes( $attributes ) {
	$html = [];

	// For numeric keys we will assume that the key and the value are the same
	// as this will convert HTML attributes such as "required" to a correct
	// form like required="required" instead of using incorrect numerics.
	foreach ( (array) $attributes as $key => $value ) {
		if ( is_numeric( $key ) ) {
			$key = $value;
		}

		if ( ! is_null( $value ) ) {
			$html[] = $key . '="' . esc_attr( $value ) . '"';
		}
	}

	return count( $html ) > 0 ? ' ' . implode( ' ', $html ) : '';
}

/*
| --------------------------------------------------------------------
| Template functions.
| --------------------------------------------------------------------
*/

/**
 * Get the theme template.
 *
 * @param  string $theme The name.
 * @return string|false
 */
function c2l_get_template( $theme = null ) {
	$theme = c2l_get_theme( $theme );

	if ( is_null( $theme ) || ! $theme->exists() ) {
		trigger_error( "The [{$theme}] theme could not be found." );
		return false;
	}

	// Locale default {theme}/index.php.
	$localed = $theme->get_theme_dir() . '/index.php';

	return apply_filters( 'c2l_locale_template', $localed, $theme );
}

/**
 * Load a component.
 *
 * @param  string $component The component name.
 * @param  array  $vars      Send variables to the component.
 * @return void
 */
function c2l_get_component( $component, $vars = [] ) {
	// Trim .php in the component name.
	$component = rtrim( $component, '.php' ) . '.php';

	$component_dir = trailingslashit( __DIR__ ) . 'views/components/';
	$component_theme_dir = c2l_get_theme_dir() . 'components/';

	// @codingStandardsIgnoreLine
	extract( $vars, EXTR_SKIP );

	if ( file_exists( $component_theme_dir . $component ) ) {
		include $component_theme_dir . $component;
	} elseif ( file_exists( $component_dir . $component ) ) {
		include $component_dir . $component;
	}
}

/**
 * Returns the favicon URL.
 *
 * @param  int    $size Optional. Size of the site icon. Default 512 (pixels).
 * @param  string $url  Optional. Fallback url if no site icon is found. Default empty.
 * @return string
 */
function c2l_get_favicon_url( $size = 512, $url = '' ) {
	$favicon_id = c2l_option( 'favicon_id' );

	if ( $favicon_id ) {
		$url = wp_get_attachment_image_url( $favicon_id, $size >= 512 ? 'full' : [ $size, $size ] );
	}

	/**
	 * Filters the favicon URL.
	 *
	 * @param string $url  Site icon URL.
	 * @param int    $size Size of the site icon.
	 */
	return apply_filters( 'c2l_get_favicon_url', $url, $size );
}

/**
 * Get the countdown DateTime.
 *
 * @return DateTime
 */
function c2l_countdown_datetime() {
	$dt = new DateTime;

	$dt->setTimestamp( (int) c2l_option( 'countdown_datetime' ) );

	return apply_filters( 'c2l_countdown_datetime', $dt );
}

/**
 * Check the current background type.
 *
 * @param  string $type The valid background type.
 * @return bool
 */
function c2l_current_background_is( $type ) {
	return c2l_get_background_data( 'type' ) === $type;
}

/**
 * Retrieve the background data.
 *
 * @param  string $key     Optional, get special key.
 * @param  mixed  $default The default value.
 * @return mixed
 */
function c2l_get_background_data( $key = null, $default = null ) {
	static $background_data;

	// Get the background data, cache it in static variable.
	if ( is_null( $background_data ) ) {
		$background_data = c2l_sanitize_background( c2l_theme_mod( 'background', [] ) );
		$background_data = apply_filters( 'c2l_background_data', $background_data );
	}

	// No need a key, just return the whole data.
	if ( is_null( $key ) ) {
		return $background_data;
	}

	return array_key_exists( $key, $background_data )
		? $background_data[ $key ]
		: $default;
}

/**
 * Retrieve the background effect data.
 *
 * @param  string $key     Optional, get special key.
 * @param  mixed  $default The default value.
 * @return mixed
 */
function c2l_get_background_effect_data( $key = null, $default = null ) {
	// Get the background data, cache it in static variable.
	static $background_effect;

	if ( is_null( $background_effect ) ) {
		$background_effect = c2l_theme_mod( 'background_effect', [] );
		$background_effect = apply_filters( 'c2l_background_effect', $background_effect );
	}

	// No need a key, just return the whole data.
	if ( is_null( $key ) ) {
		return $background_effect;
	}

	return array_key_exists( $key, $background_effect )
		? $background_effect[ $key ]
		: $default;
}

/**
 * Return an array of social providers.
 *
 * @return array
 */
function c2l_social_providers() {
	return apply_filters( 'c2l_social_providers', [
		'facebook'     => esc_html__( 'Facebook', 'coming2live' ),
		'google-plus'  => esc_html__( 'Google plus', 'coming2live' ),
		'twitter'      => esc_html__( 'Twitter', 'coming2live' ),
		'messenger'    => esc_html__( 'Messenger', 'coming2live' ),
		'github'       => esc_html__( 'Github', 'coming2live' ),
		'gitlab'       => esc_html__( 'Gitlab', 'coming2live' ),
		'instagram'    => esc_html__( 'Instagram', 'coming2live' ),
		'pinterest'    => esc_html__( 'Pinterest', 'coming2live' ),
		'linkedin'     => esc_html__( 'LinkedIn', 'coming2live' ),
		'skype'        => esc_html__( 'Skype', 'coming2live' ),
		'slack'        => esc_html__( 'Slack', 'coming2live' ),
		'tumblr'       => esc_html__( 'Tumblr', 'coming2live' ),
		'youtube'      => esc_html__( 'Youtube', 'coming2live' ),
		'vimeo'        => esc_html__( 'Vimeo', 'coming2live' ),
		'flickr'       => esc_html__( 'Flickr', 'coming2live' ),
		'dribbble'     => esc_html__( 'Dribbble', 'coming2live' ),
		'foursquare'   => esc_html__( 'Foursquare', 'coming2live' ),
		'kickstarter'  => esc_html__( 'Kickstarter', 'coming2live' ),
		'paypal'       => esc_html__( 'Paypal', 'coming2live' ),
		'reddit'       => esc_html__( 'Reddit', 'coming2live' ),
		'soundcloud'   => esc_html__( 'SoundCloud', 'coming2live' ),
		'tripadvisor'  => esc_html__( 'TripAdvisor', 'coming2live' ),
		'wordpress'    => esc_html__( 'WordPress', 'coming2live' ),
	]);
}

/**
 * Get list particles effects.
 *
 * @return array
 */
function c2l_particles_effects() {
	return apply_filters( 'c2l_particles_effects', [
		'default' => [
			'name' => esc_html__( 'Default', 'coming2live' ),
			'json' => c2l_plugin_url( 'inc/resources/particles/default.json' ),
		],
		'bubble' => [
			'name' => esc_html__( 'Bubble', 'coming2live' ),
			'json' => c2l_plugin_url( 'inc/resources/particles/bubble.json' ),
		],
		'nasa' => [
			'name' => esc_html__( 'NASA', 'coming2live' ),
			'json' => c2l_plugin_url( 'inc/resources/particles/nasa.json' ),
		],
		'snow' => [
			'name' => esc_html__( 'Snow', 'coming2live' ),
			'json' => c2l_plugin_url( 'inc/resources/particles/snow.json' ),
		],
		'star' => [
			'name' => esc_html__( 'Star', 'coming2live' ),
			'json' => c2l_plugin_url( 'inc/resources/particles/star.json' ),
		],
	]);
}
