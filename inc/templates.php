<?php
/**
 * Coming2Live templates functions.
 *
 * @package Coming2Live
 */

// Force to hide the admin_bar.
show_admin_bar( c2l_option( 'show_admin_bar', false ) );

/*
| --------------------------------------------------------------------
| Global hooks.
| --------------------------------------------------------------------
*/

// @codingStandardsIgnoreStart
add_action( 'c2l_head',                 'noindex',                         1    );
add_action( 'c2l_head',                 'c2l_print_title',                 1    );
add_action( 'c2l_head',                 'c2l_print_seo',                   2    );
add_action( 'c2l_head',                 'wp_resource_hints',               2    );
add_action( 'c2l_head',                 'print_emoji_detection_script',    7    );
add_action( 'c2l_head',                 'wp_print_styles',                 8    );
add_action( 'c2l_head',                 'wp_print_head_scripts',           9    );
add_action( 'c2l_head',                 'c2l_print_favicon',              99    );
add_action( 'c2l_head',                 'c2l_print_custom_css',           101   );
add_action( 'c2l_head',                 'c2l_print_tracking_code',        101   );
add_action( 'c2l_footer',               'wp_admin_bar_render',            1000  );
add_action( 'c2l_print_footer_scripts', '_wp_footer_scripts'                    );
add_filter( 'wp_resource_hints',        'c2l_resource_hints',             10, 2 );
add_filter( 'c2l_enqueue_scripts',      'c2l_enqueue_core_scripts',       1     );
add_filter( 'c2l_enqueue_scripts',      'c2l_enqueue_theme_scripts',      9     );
add_filter( 'c2l_custom_css_code',      'c2l_print_background_css'              );
add_filter( 'c2l_footer',               'c2l_print_background_js'               );
add_filter( 'c2l_footer',               'c2l_print_background_effect_js'        );
// @codingStandardsIgnoreEnd

/*
| --------------------------------------------------------------------
| Core template functions.
| --------------------------------------------------------------------
*/

/**
 * Fire the c2l_head action.
 *
 * @return void
 */
function c2l_head() {
	// Fires when scripts and styles are enqueued.
	do_action( 'c2l_enqueue_scripts' );

	// Prints scripts or data in the head tag on the front end.
	do_action( 'c2l_head' );
}

/**
 * Fire the c2l_footer action.
 *
 * @return void
 */
function c2l_footer() {
	// Prints scripts or data before the default footer scripts.
	do_action( 'c2l_footer' );

	// Prints any scripts and data queued for the footer.
	do_action( 'c2l_print_footer_scripts' );
}

/**
 * Get the body classes.
 *
 * @return void
 */
function c2l_body_class() {
	$classes = [];

	if ( c2l_theme_support( 'custom-background' ) ) {
		$classes[] = 'custom-background';

		if ( c2l_current_background_is( 'video' ) ) {
			$classes[] = 'background-video-' . sanitize_html_class( c2l_get_background_data( 'background_video_source' ) );
		} elseif ( c2l_current_background_is( 'slider' ) ) {
			$classes[] = 'background-slider';
		} elseif ( c2l_current_background_is( 'triangle' ) ) {
			$classes[] = 'background-triangle';
		} elseif ( c2l_current_background_is( 'fss' ) ) {
			$classes[] = 'background-fss';
		}
	}

	// Apply filter body class.
	$classes = array_unique( apply_filters( 'c2l_body_class', $classes ) );

	echo 'class="' . join( ' ', array_map( 'sanitize_html_class', $classes ) ) . '"';
}

/*
| --------------------------------------------------------------------
| Private template hooks.
| --------------------------------------------------------------------
*/

// Back-compat with older WP-version.
if ( ! function_exists( 'wp_resource_hints' ) ) {
	function wp_resource_hints() {} // @codingStandardsIgnoreLine
}

/**
 * Displays title tag with content.
 *
 * @access private
 */
function c2l_print_title() {
	$title = c2l_option( 'site_title' );

	if ( empty( $title ) ) {
		$title = get_bloginfo( 'name', 'display' );
	}

	// Santize before output.
	$title = wptexturize( convert_chars( $title ) );
	$title = esc_html( capital_P_dangit( $title ) );

	echo '<title>' . apply_filters( 'c2l_document_title', $title ) . '</title>' . "\n"; // WPCS: XSS OK.
}

/**
 * Displays SEO tags in the <head>.
 *
 * @access private
 */
function c2l_print_seo() {
	$meta_tags = [];

	if ( $description = c2l_option( 'site_description' ) ) {
		$meta_tags['description'] = '<meta name="description" content="' . esc_attr( $description ) . '">';
	}

	/**
	 * Filters the seo meta tags.
	 *
	 * @param array $meta_tags Seo meta tags.
	 */
	$meta_tags = array_filter( apply_filters( 'c2l_seo_meta_tags', $meta_tags ) );

	// Then, output them.
	foreach ( $meta_tags as $meta_tag ) {
		echo "$meta_tag\n"; // WPCS: XSS OK.
	}
}

/**
 * Print the site favicon.
 *
 * @see  wp_site_icon()
 * @link https://www.whatwg.org/specs/web-apps/current-work/multipage/links.html#rel-icon HTML5 specification link icon.
 *
 * @access private
 */
function c2l_print_favicon() {
	// If empty favicon, fallback to use "wp_site_icon".
	if ( ! c2l_get_favicon_url() ) {
		wp_site_icon();
		return;
	}

	$meta_tags = [];

	if ( $icon_32 = c2l_get_favicon_url( 32 ) ) {
		$meta_tags[] = sprintf( '<link rel="icon" href="%s" sizes="32x32" />', esc_url( $icon_32 ) );
	}

	if ( $icon_192 = c2l_get_favicon_url( 192 ) ) {
		$meta_tags[] = sprintf( '<link rel="icon" href="%s" sizes="192x192" />', esc_url( $icon_192 ) );
	}

	if ( $icon_180 = c2l_get_favicon_url( 180 ) ) {
		$meta_tags[] = sprintf( '<link rel="apple-touch-icon-precomposed" href="%s" />', esc_url( $icon_180 ) );
	}

	if ( $icon_270 = c2l_get_favicon_url( 270 ) ) {
		$meta_tags[] = sprintf( '<meta name="msapplication-TileImage" content="%s" />', esc_url( $icon_270 ) );
	}

	/**
	 * Filters the favicon meta tags.
	 *
	 * @param array $meta_tags Site favicon meta tags.
	 */
	$meta_tags = array_filter( apply_filters( 'c2l_favicon_meta_tags', $meta_tags ) );

	// Then, output them.
	foreach ( $meta_tags as $meta_tag ) {
		echo "$meta_tag\n"; // WPCS: XSS OK.
	}
}

/**
 * Print the custom CSS.
 *
 * @access private
 */
function c2l_print_custom_css() {
	// Get the custom CSS code.
	$styles = apply_filters( 'c2l_custom_css', trim( c2l_option( 'custom_css' ) ) );

	// Bail if have no custom_css.
	if ( $styles ) {
		?><style type="text/css" id="c2l-custom-css">
			<?php echo strip_tags( $styles ); // Note that esc_html() cannot be used because `div &gt; span` is not interpreted properly. @codingStandardsIgnoreLine ?>
		</style><?php // @codingStandardsIgnoreLine
	}

	// Print some custom CSS.
	?><style type="text/css">
		svg.c2lsvg {
			width: 20px;
			height: 20px;
			display: inline-block;
			vertical-align: middle;
		}
	</style><?php // @codingStandardsIgnoreLine

	// Allow add custom tracking code.
	do_action( 'c2l_custom_css_code' );
}

/**
 * Print the tracking code.
 *
 * @access private
 */
function c2l_print_tracking_code() {
	$tracking_id = c2l_option( 'google_analytics' );

	// Bail if have no tracking ID entered.
	if ( ! empty( $tracking_id ) ) {
		// @codingStandardsIgnoreLine ?><!-- Google Analytics -->
		<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

			ga('create', '<?php echo esc_attr( $tracking_id ); ?>', 'auto');
			ga('send', 'pageview');
		</script><?php // @codingStandardsIgnoreLine
	}

	// Allow add custom tracking code.
	do_action( 'c2l_tracking_code' );
}

/**
 * Add preconnect for Google Fonts.
 *
 * @param  array  $urls           URLs to print for resource hints.
 * @param  string $relation_type  The relation type the URLs are printed.
 * @return array $urls            URLs to print for resource hints.
 *
 * @access private
 */
function c2l_resource_hints( $urls, $relation_type ) {
	if ( c2l_theme()->support( 'google-fonts' ) && 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}

	return $urls;
}

/**
 * Enqueue the core styles & scripts.
 *
 * @return void
 */
function c2l_enqueue_core_scripts() {
	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	$asset_url = trailingslashit( coming2live()->get_plugin_url() ) . 'assets/';

	// The vendor JS.
	wp_register_style( 'vegas', $asset_url . 'vendor/vegas/vegas' . $min . '.css', [], '2.4.0' );
	wp_register_style( 'jquery.mb.YTPlayer', $asset_url . 'vendor/jquery.mb.YTPlayer/css/jquery.mb.YTPlayer.min.css', [], '3.1.12' );
	wp_register_style( 'animated-headlines', $asset_url . 'vendor/animated-headlines/animated-headlines' . $min . '.css', [], '1.0.0' );

	wp_register_script( 'vegas', $asset_url . 'vendor/vegas/vegas' . $min . '.js', [ 'jquery' ], '2.4.0', true );
	wp_register_script( 'particles', $asset_url . 'vendor/particles/particles' . $min . '.js', [], '2.0.0', true );
	wp_register_script( 'flat-surface-shader', $asset_url . 'vendor/flat-surface-shader/fss' . $min . '.js', [], '1.0.0', true );
	wp_register_script( 'jquery.countdown', $asset_url . 'vendor/jquery.countdown/jquery.countdown' . $min . '.js', [ 'jquery' ], '2.2.0', true );
	wp_register_script( 'jquery.mb.YTPlayer', $asset_url . 'vendor/jquery.mb.YTPlayer/jquery.mb.YTPlayer' . $min . '.js', [ 'jquery' ], '3.1.12', true );
	wp_register_script( 'animated-headlines', $asset_url . 'vendor/animated-headlines/animated-headlines' . $min . '.js', [ 'jquery' ], '1.0.0', true );
	wp_register_script( 'quietflow', $asset_url . 'vendor/quietflow/quietflow' . $min . '.js', [ 'jquery' ], '1.0.0', true );


	// The core JS.
	wp_register_script( 'c2l-background-js', $asset_url . 'js/background.js', [ 'jquery' ], C2L_VERSION, true );
	wp_register_script( 'c2l-countdown-js', $asset_url . 'js/countdown.js', [ 'jquery', 'jquery.countdown' ], C2L_VERSION, true );
}

/*
| --------------------------------------------------------------------
| Theme template functions.
| --------------------------------------------------------------------
*/

/**
 * Enqueue the theme styles & scripts.
 *
 * @return void
 */
function c2l_enqueue_theme_scripts() {
	if ( c2l_theme_support( 'custom-background' ) ) {
		$bgtype = c2l_get_background_data( 'type' );

		switch ( $bgtype ) {
			case 'video':
				if ( 'youtube' == c2l_get_background_data( 'background_video_source' ) ) {
					wp_enqueue_style( 'jquery.mb.YTPlayer' );
					wp_enqueue_script( 'jquery.mb.YTPlayer' );
				}
				break;

			case 'slider':
				wp_enqueue_style( 'vegas' );
				wp_enqueue_script( 'vegas' );
				break;

			case 'triangle':
				wp_enqueue_script( 'quietflow' );
				break;

			case 'fss':
				wp_enqueue_script( 'flat-surface-shader' );
				break;
		}

		if ( $bgtype && 'none' !== $bgtype ) {
			wp_enqueue_script( 'c2l-background-js' );
		}
	}

	if ( c2l_theme_support( 'background-effect' ) ) {
		$effect = c2l_get_background_effect_data( 'effect' );

		switch ( $effect ) {
			case 'particles':
				wp_enqueue_script( 'particles' );
				break;
		}

		if ( $effect && 'none' !== $effect ) {
			wp_enqueue_script( 'c2l-background-js' );
		}
	}

	if ( c2l_option( 'body_enable_animatedtitle' ) ) {
		wp_enqueue_style( 'animated-headlines' );
		wp_enqueue_script( 'animated-headlines' );
	}
}

/**
 * Setup and print the background CSS code.
 *
 * @access private
 */
function c2l_print_background_css() {
	// Theme is not support custom background, just leave.
	if ( ! c2l_theme_support( 'custom-background' ) ) {
		return;
	}

	// Get the custom background data.
	$background_data = c2l_get_background_data();
	if ( empty( $background_data['type'] ) || 'none' === $background_data['type'] ) {
		return;
	}

	// Begin build the attributes.
	$atts = [];
	switch ( $background_data['type'] ) {
		case 'background':
			if ( ! empty( $background_data['background_color'] ) ) {
				$atts['background-color'] = c2l_sanitize_color( $background_data['background_color'] );
			}

			if ( ! empty( $background_data['background_image'] ) ) {
				$atts['background-image'] = 'url(' . esc_url( $background_data['background_image'] ) . ');';
			}

			if ( ! empty( $background_data['background_image_atts'] ) ) {
				$bg_atts = c2l_sanitize_background_atts( $background_data['background_image_atts'] );
				$atts['background-size'] = $bg_atts['background_size'];
				$atts['background-position'] = $bg_atts['background_position'];
				$atts['background-attachment'] = $bg_atts['background_attachment'];
			}
			break;

		case 'gradient': // TODO: ...
			$gradient = sprintf( 'linear-gradient(to left, %1$s, %2$s);', $background_data['background_gradient1'], $background_data['background_gradient2'] );
			$atts = [ 'background' => $gradient ];
			break;
	}

	// Remove empty keys.
	$atts = array_filter( $atts );

	if ( ! empty( $atts ) ) {
		?><style type="text/css">
			body.custom-background { <?php echo c2l_css_attributes( $atts ); // @WPCS: XSS OK. ?> }
		</style><?php // @codingStandardsIgnoreLine
	}
}

/**
 * Setup and print the background JS code.
 *
 * @access private
 */
function c2l_print_background_js() {
	// Theme is not support custom background, just leave.
	if ( ! c2l_theme_support( 'custom-background' ) ) {
		return;
	}

	switch ( c2l_get_background_data( 'type' ) ) {
		case 'slider':
			$slides = apply_filters( 'c2l_background_sliders', (array) c2l_get_background_data( 'background_slider', [] ) );
			$slides = array_filter( array_unique( $slides ) );

			// Build the slides.
			$slides = array_map( function( $key ) {
				return [ 'src' => esc_url_raw( $key ) ];
			}, array_values( $slides ) );

			if ( ! empty( $slides ) ) {
				wp_localize_script( 'c2l-background-js', '_c2lBgSlider', apply_filters( 'c2l_background_slider_options', [
					'timer'    => true,
					'loop'     => true,
					'shuffle'  => false,
					'autoplay' => true,
					'slides'   => $slides,
				]));
			}

			echo '<div class="vegas-container"></div>';
			break;

		case 'video':
			$video_source = c2l_get_background_data( 'background_video_source' );
			if ( 'youtube' === $video_source ) {
				wp_localize_script( 'c2l-background-js', '_bgYoutubeFilters', [
					'blur'    => c2l_get_background_data( 'youtube_filter_blur', 0 ),
				]);

				_c2l_youtube_background_markup();
			}
			break;

		case 'triangle':
			$background_triangle = c2l_get_background_data( 'background_triangle' );
			wp_localize_script( 'c2l-background-js', '_c2lTriangle', apply_filters( 'c2l_background_slider_options', [
				'theme'          => "layeredTriangles",
				'backgroundCol'  => $background_triangle,
				'specificColors' => array_filter( (array) c2l_get_background_data( 'triangle_color' ) ),
			]));
			// echo '<div class="quietflow" data-options=\'{"theme":"layeredTriangles","backgroundCol":"' . $background_triangle . '"}\'></div>';
			break;

		case 'fss':
			wp_localize_script( 'c2l-background-js', '_fssColors', [
				'light_ambient'    => c2l_get_background_data( 'fss_light_ambient' ),
				'light_diffuse'    => c2l_get_background_data( 'fss_light_diffuse' ),
				'material_ambient' => c2l_get_background_data( 'fss_material_ambient' ),
				'material_diffuse' => c2l_get_background_data( 'fss_material_diffuse' ),
			]);

			// Print the markup template.
			echo '<div id="fss-js" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 0"></div>';
			break;
	}
}

/**
 * Print the youtube background video HTML structure.
 *
 * @access private
 */
function _c2l_youtube_background_markup() {
	// Ensure the video link is valid.
	$video_link = esc_url_raw( c2l_get_background_data( 'background_video_link' ) );
	if ( ! $video_link ) {
		return;
	}

	// Default the inline style.
	$inline_style = 'position: fixed; z-index: 0; min-width: 100vw; min-height: 100vh; top: 0; left: 0;';

	// Fallback images.
	$fallback_image = c2l_get_background_data( 'background_video_thumbnail' );
	if ( $fallback_image ) {
		$inline_style .= 'background: url("' . esc_url( $fallback_image ) . '") center center / cover no-repeat rgba(0, 0, 0, 0.5);';
	}

	$options = apply_filters( 'c2l_background_video_youtube_options', [
		'videoURL'            => esc_url( $video_link ),
		'ratio'               => 'auto',
		'mute'                => true,
		'autoPlay'            => true,
		'showControls'        => false,
		'containment'         => 'body',
		'backgroundImage'     => $fallback_image ? esc_url( $fallback_image ) : '',
		'mobileFallbackImage' => $fallback_image ? esc_url( $fallback_image ) : '',
	]);

	echo '<!-- Youtube Background Video -->' . "\n";
	echo '<div class="c2l-youtube-bgvideo" data-property=\'' . json_encode( $options, JSON_UNESCAPED_SLASHES ) . '\' style="' . esc_attr( $inline_style ) . '"></div>' . "\n";
}

/**
 * Setup and print the background effect JS code.
 *
 * @access private
 */
function c2l_print_background_effect_js() {
	// Theme is not support custom background, just leave.
	if ( ! c2l_theme_support( 'background-effect' ) ) {
		return;
	}

	switch ( c2l_get_background_effect_data( 'effect' ) ) {
		case 'particles':
				$style   = c2l_get_background_effect_data( 'particles_effect' );
				$effects = c2l_particles_effects();

				// Get the particles_json style.
				$particles_json = isset( $effects[ $style ] )
					? $effects[ $style ]['json']
					: c2l_plugin_url( 'inc/resources/particles/default.json' );

				// Print the JS & markup template.
				wp_add_inline_script( 'particles', "(function(){ particlesJS.load('particles-js', '${particles_json}'); })();", 'after' );
				echo '<div id="particles-js"></div>';
			break;
	}
}

/**
 * Get the body title.
 *
 * @param  boolean $echo Should echo or not.
 * @return mixed
 */
function c2l_body_title( $echo = true ) {
	$body_title = c2l_option( 'body_title' );
	if ( c2l_option( 'body_enable_animatedtitle' ) ) {
		$body_title = '<span class="cd-headline clip">';
		$body_title .= '<span>' . c2l_option( 'body_title' ) . '</span> ';
		$body_title .= '<span class="cd-words-wrapper">';

		foreach (c2l_option( 'body_title_animated' ) as $key => $value) {
			if ($key == 0)
				$body_title .= '<b class="is-visible">'.$value.'</b>';
			else
				$body_title .= '<b>'.$value.'</b>';
		}

		$body_title .= '</span>';
		$body_title .= '</span>';
	}

	if ( ! $body_title ) {
		return;
	}

	if ( $echo ) {
		echo wp_kses_post( $body_title );
	} else {
		return $body_title;
	}
}


/**
 * Get the body_message.
 *
 * @param  boolean $echo Should echo or not.
 * @return mixed
 */
function c2l_body_message( $echo = true ) {
	$body_message = c2l_option( 'body_message' );
	if ( ! $body_message ) {
		return;
	}

	if ( $echo ) {
		echo do_shortcode( wpautop( $body_message ) );
	} else {
		return $body_message;
	}
}

/**
 * Return SVG markup.
 *
 * @param  string $icon     The icon name.
 * @param  bool   $fallback The fallback icon.
 * @return void
 */
function c2l_svg_icon( $icon, $fallback = false ) {
	// Begin SVG markup.
	$svg = '<svg class="c2lsvg c2lsvg-' . esc_attr( $icon ) . '" aria-hidden="true" role="img">';

	/*
	 * Display the icon.
	 *
	 * The whitespace around `<use>` is intentional - it is a work around to a keyboard navigation bug in Safari 10.
	 *
	 * See https://core.trac.wordpress.org/ticket/38387.
	 */
	$svg .= ' <use href="#c2lsvg-' . esc_html( $icon ) . '" xlink:href="#c2lsvg-' . esc_html( $icon ) . '"></use> ';

	// Add some markup to use as a fallback for browsers that do not support SVGs.
	if ( $fallback ) {
		$svg .= '<span class="svg-fallback c2lsvg-' . $icon . '"></span>';
	}

	$svg .= '</svg>';

	echo $svg; // WPXS: XSS OK.
}

/**
 * Enqueue the SVG icons.
 *
 * @return void
 */
function c2l_enqueue_svg_icons() {
	if ( ! has_action( 'c2l_footer', '_c2l_include_svg_icons' ) ) {
		add_action( 'c2l_footer', '_c2l_include_svg_icons', 9999 );
	}
}

/**
 * Add SVG definitions to the 'c2l_footer'.
 *
 * @access private
 */
function _c2l_include_svg_icons() {
	// Define SVG sprite file.
	$svg_icons = apply_filters( 'c2l_svg_social_icons_path',
		coming2live()->get_plugin_path( 'assets/img/svg-defs.svg' )
	);

	// If it exists, include it.
	if ( file_exists( $svg_icons ) ) {
		require_once( $svg_icons );
	}
}
