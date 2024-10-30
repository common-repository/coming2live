<?php

/**
 * Free 01 default options.
 *
 * @param  Coming2Live\Plugin $plugin plugin
 * @return void
 */
function free02_default_options( $plugin ) {
	// delete_option( $plugin->get_theme_mod_key() );

	$default_options = apply_filters( 'free02_default_options', [
		'logo'           => '',
		'address'        => '90 Queen St Melbourne Vic. AU',
		'phone'          => '+1-202-555-0192',
		'email'          => 'hello@coming2.live',
		'enable_overlay' => true,
		'overlay_color'  => '#000000',
		'overlay_opacity'=> 50,
		'background'     => [
			'background_triangle'   => '#111111',
			'triangle_color'        => ['rgba(255, 214, 108, .5)', 'rgba(192, 55, 23, .5)', 'rgba(255, 153, 53, .5)', 'rgba(141, 16, 12, .5)', 'rgba(53, 71, 45, .5)'],
			'fss_light_ambient'     => '#111122',
			'fss_light_diffuse'     => '#FF0022',
			'fss_material_ambient'  => '#FFFFFF',
			'fss_material_diffuse'  => '#FFFFFF',
		],
		'footer_title_1' => 'Many themes',
		'footer_desc_1'  => 'Duis porttitor libero ac egestas euismod. Maecenas quis felis turpis. Nulla quis turpis sed augue egestas dapibus vel at nibh. Nullam at justo at arcu egestas laoreet. Integer nunc.',
		'footer_title_2' => 'Background effects',
		'footer_desc_2'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut laoreet ut lacus a tincidunt. Quisque luctus nibh augue, non ultrices arcu molestie in. Integer finibus dolor lorem tem.',
		'footer_title_3' => 'It\'s free',
		'footer_desc_3'  => 'Nam suscipit nisi risus, et porttitor metus molestie a. Phasellus id quam id turpis suscipit pretium. Maecenas ultrices, lacus ut accumsan maximus, odio augue rhoncus augue vulput',
	] );

	$plugin->set_starter_content( $default_options );
}
add_action( 'c2l_setup_theme', 'free02_default_options' );


/**
 * Handles JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @return void
 */
function free02_javascript_detection() {
	echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}
add_action( 'c2l_head', 'free02_javascript_detection', 0 );

/**
 * Enqueue scripts and styles.
 *
 * @return void
 */
function free02_enqueue_scripts() {
	wp_enqueue_style( 'free02-fonts', 'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Work+Sans:300,400,500,700', [], null );
	wp_enqueue_style( 'free02-grid', c2l_get_theme_uri() . 'assets/vendors/bootstrap/grid.css', [], '1.0.0' );
	wp_enqueue_style( 'free02-fontawesome', c2l_get_theme_uri() . 'assets/fonts/fontawesome/font-awesome.min.css', [], '1.0.0' );
	wp_enqueue_style( 'free02-style', c2l_get_stylesheet_uri(), [], '1.0.0' );

	wp_enqueue_script( 'free02-script', c2l_get_theme_uri() . 'assets/js/main.js', [], '1.0.0' );

	if ( c2l_theme_support( 'countdown' ) && c2l_option( 'display_countdown' ) ) {
		wp_enqueue_script( 'c2l-countdown-js' );
	}
}
add_action( 'c2l_enqueue_scripts', 'free02_enqueue_scripts' );

/**
 * Enqueue admin scripts and styles.
 *
 * @return void
 */
function free01_setting_enqueue_scripts() {
	wp_enqueue_script( 'free01-settings', c2l_get_theme_uri() . 'assets/js/settings.js', [ 'coming2live-settings' ], '1.0.0' );
}
add_action( 'c2l_setting_enqueue_scripts', 'free01_setting_enqueue_scripts' );

/**
 * Register theme settings.
 *
 * @param  CMB2 $section The CMB2 instance of theme section.
 * @return void
 */
function free02_theme_settings( $section ) {
	$section->add_field([
		'id'    => '__logo__',
		'type'  => 'title',
		'name'  => esc_html__( 'Logo', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => 'logo',
		'type'  => 'file',
		'name'  => esc_html__( 'Logo', 'coming2live' ),
		'options'      => [ 'url' => false ],
		'query_args'   => [ 'type' => 'image' ],
		'text'         => [ 'add_upload_file_text' => esc_html__( 'Select Image', 'coming2live' ) ],
		'preview_size' => 'small',
	]);

	// Overlay
	$section->add_field([
		'id'    => '__overlay__',
		'type'  => 'title',
		'name'  => esc_html__( 'Overlay', 'coming2live' ),
	]);

	$section->add_field([
		'id'         => 'enable_overlay',
		'type'       => 'c2l_toggle',
		'name'       => esc_html__( 'Enable overlay', 'coming2live' ),
	]);

	$section->add_field([
		'id'         => 'overlay_color',
		'type'       => 'colorpicker',
		'name'       => esc_html__( 'Overlay color', 'coming2live' ),
		'classes_cb' => function() {
			return c2l_option( 'enable_overlay' ) ? 'show' : '';
		},
	]);

	$section->add_field([
		'id'    => 'overlay_opacity',
		'type'  => 'text',
		'desc'    => esc_html__( 'Fill value from 0 to 100', 'coming2live' ),
		'name'  => esc_html__( 'Overlay opacity', 'coming2live' ),
	]);

	// Other informations
	$section->add_field([
		'id'    => '__header__',
		'type'  => 'title',
		'name'  => esc_html__( 'Informations', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => 'address',
		'type'  => 'text',
		'name'  => esc_html__( 'Address', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => 'phone',
		'type'  => 'text',
		'name'  => esc_html__( 'Phone', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => 'email',
		'type'  => 'text',
		'name'  => esc_html__( 'Email', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => '__footer_1__',
		'type'  => 'title',
		'name'  => esc_html__( 'Footer column 1', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => 'footer_title_1',
		'type'  => 'text',
		'name'  => esc_html__( 'Title', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => 'footer_desc_1',
		'type'  => 'textarea_small',
		'name'  => esc_html__( 'Description', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => '__footer_2__',
		'type'  => 'title',
		'name'  => esc_html__( 'Footer column 2', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => 'footer_title_2',
		'type'  => 'text',
		'name'  => esc_html__( 'Title', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => 'footer_desc_2',
		'type'  => 'textarea_small',
		'name'  => esc_html__( 'Description', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => '__footer_3__',
		'type'  => 'title',
		'name'  => esc_html__( 'Footer column 3', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => 'footer_title_3',
		'type'  => 'text',
		'name'  => esc_html__( 'Title', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => 'footer_desc_3',
		'type'  => 'textarea_small',
		'name'  => esc_html__( 'Description', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => '__footer_4__',
		'type'  => 'title',
		'name'  => esc_html__( 'Footer column 4', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => 'footer_title_4',
		'type'  => 'text',
		'name'  => esc_html__( 'Title', 'coming2live' ),
	]);

	$section->add_field([
		'id'    => 'footer_desc_4',
		'type'  => 'textarea_small',
		'name'  => esc_html__( 'Description', 'coming2live' ),
	]);
}
add_action( 'c2l_theme_settings', 'free02_theme_settings' );
