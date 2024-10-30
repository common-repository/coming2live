<?php
namespace Coming2Live;

use CMB2_hookup;

class Admin_Options {
	/**
	 * The plugin class instance.
	 *
	 * @var \Coming2Live\Plugin
	 */
	protected $plugin;

	/**
	 * All sections.
	 *
	 * @var array CMB2[]
	 */
	protected $sections = [];

	/**
	 * Constructor.
	 *
	 * @param \Coming2Live\Plugin $plugin The plugin class instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Init the class.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
		add_action( 'admin_post_c2l_save_setting', [ $this, 'save' ] );
		add_action( 'admin_action_c2l_activate', [ $this, 'activate_theme' ] );

		add_action( 'c2l_after_setup', [ $this, 'register' ] );
		add_action( 'c2l_register_settings', [ $this, 'register_setting_fields' ], 1 );

		if ( $this->need_import_starter_content() ) {
			add_action( 'c2l_setup_theme', [ $this, 'import_theme_starter_content' ], 100 );
		}
	}

	/**
	 * Register core section settings.
	 *
	 * @return void
	 */
	public function register() {
		$this->register_section( 'general', [
			'title' => esc_html__( 'General', 'coming2live' ),
		]);

		$this->register_section( 'advanced', [
			'title' => esc_html__( 'Advanced', 'coming2live' ),
		]);

		$this->register_section( 'content', [
			'title' => esc_html__( 'Content', 'coming2live' ),
		]);

		$this->register_section( 'theme', [
			'title'      => esc_html__( 'Theme', 'coming2live' ),
			'option_key' => $this->plugin->get_theme_mod_key(),
		]);

		$this->register_section( 'custom_css', [
			'title' => esc_html__( 'Custom CSS', 'coming2live' ),
		]);

		/**
		 * Fires once WordPress has loaded.
		 *
		 * @param \Coming2Live\Admin_Options $this The Admin_Options instance.
		 */
		do_action( 'c2l_register_settings', $this );
	}

	/**
	 * Register the core fields.
	 *
	 * @access private
	 */
	public function register_setting_fields() {
		foreach ( $this->sections as $name => $section ) {
			$call_method = "register_{$name}_fields";

			// Call the class method to register core fields.
			if ( method_exists( $this, $call_method ) ) {
				$this->$call_method( $section );
			}

			do_action( "c2l_register_{$name}_fields", $section, $this );
		}
	}

	/**
	 * Perform import theme starter content.
	 *
	 * @access private
	 */
	public function import_theme_starter_content() {
		$starter_content = $this->plugin->get_starter_content();
		if ( empty( $starter_content ) ) {
			return;
		}

		// Get the option object.
		$options = cmb2_options( $this->plugin->get_theme_mod_key() );

		foreach ( $starter_content as $key => $value ) {
			$options->update( $key, $value, false, true );
		}

		// Perform `update_option`.
		$imported = $options->set();

		do_action( 'c2l_imported_starter_content', $imported, $options, $starter_content );
	}

	/**
	 * Determines if neeed to import starter content.
	 *
	 * @return bool
	 */
	protected function need_import_starter_content() {
		if ( ! isset( $_GET['page'] ) || 'c2l-settings' !== $_GET['page'] ) {
			return false;
		}

		return null === get_option( $this->plugin->get_theme_mod_key(), null );
	}

	/**
	 * Output the settings.
	 *
	 * @access private
	 */
	public function output() {
		// Find the current section from request.
		$current = 'general';
		if ( isset( $_GET['section'] ) && array_key_exists( $_GET['section'], $this->sections ) ) {
			$current = sanitize_text_field( $_GET['section'] );
		}

		// Something went wrong.
		if ( ! isset( $this->sections[ $current ] ) ) {
			return;
		}

		// Resolve the CMB2.
		$cmb2 = $this->sections[ $current ];

		// Correct the CMB2 section object_ID.
		$option_key = $cmb2->prop( 'option_key' )[0];
		$cmb2->object_id( $option_key );

		// Send variables to the view.
		extract([ // @codingStandardsIgnoreLine
			'cmb2'        => $cmb2,
			'current'     => $current,
			'sections'    => $this->sections,
			'option_key'  => $option_key,
			'current_url' => menu_page_url( 'c2l-settings', false ),
		], EXTR_SKIP );

		// Output the settings.
		include trailingslashit( __DIR__ ) . 'views/settings.php';
	}

	/**
	 * Handle save the settings.
	 *
	 * @access private
	 */
	public function save() {
		$referer_url = wp_get_referer();
		if ( empty( $referer_url ) ) {
			$referer_url = admin_url();
		}

		// Redirect back if request input is invalid, @codingStandardsIgnoreLine.
		if ( ! isset( $_POST['submit-cmb'], $_POST['cmb2_section'] ) || ! array_key_exists( $_POST['cmb2_section'], $this->sections ) ) {
			wp_safe_redirect( esc_url_raw( $referer_url ), 303 );
			exit;
		}

		// Get the section instance.
		$cmb2 = $this->get_section( $_POST['cmb2_section'] ); // @codingStandardsIgnoreLine

		// Try validate the nonce before save.
		if ( isset( $_POST[ $cmb2->nonce() ] ) && wp_verify_nonce( $_POST[ $cmb2->nonce() ], $cmb2->nonce() ) ) {
			$option_key = $cmb2->prop( 'option_key' )[0];

			$updated = $cmb2
				->save_fields( $option_key, 'options-page', $_POST )
				->was_updated(); // Will be false if no values were changed/updated.

			$referer_url = add_query_arg( 'settings-updated', $updated ? 'true' : 'false', $referer_url );
		}

		wp_safe_redirect( esc_url_raw( $referer_url ) );
		exit;
	}

	/**
	 * Handle activate theme.
	 *
	 * @access private
	 */
	public function activate_theme() {
		if ( empty( $_REQUEST['theme'] ) ) {
			return;
		}

		$switch_theme = sanitize_text_field( $_REQUEST['theme'] );
		check_admin_referer( 'activate_' . $switch_theme );

		// Switch to new theme.
		$this->plugin->switch_theme( $switch_theme );

		wp_safe_redirect( admin_url( 'tools.php?page=c2l-settings' ) );
		exit;
	}

	/**
	 * Get back a registered section.
	 *
	 * @param  string $section The section ID.
	 * @return CMB2
	 */
	public function get_section( $section ) {
		return array_key_exists( $section, $this->sections )
			? $this->sections[ $section ]
			: null;
	}

	/**
	 * Register a setting section.
	 *
	 * @param  string $section The section ID.
	 * @param  array  $args    The section CMB2 args, @see CMB2::$mb_defaults.
	 * @return CMB2
	 */
	public function register_section( $section, $args = [] ) {
		if ( array_key_exists( $section, $this->sections ) ) {
			return $this->sections[ $section ];
		}

		$option_name = isset( $args['option_key'] )
			? $args['option_key']
			: $this->plugin->get_option_key();

		// Create new instance static CMB2.
		return $this->sections[ $section ] = new_cmb2_box( array_merge( $args, [
			'id'              => 'c2l_section_' . $section,
			'tab_group'       => 'c2l_section_group',
			'object_types'    => [ 'options-page' ],
			'option_key'      => $option_name,
			'parent_slug'     => null,
			'admin_menu_hook' => null,  // Disable hook into the 'admin_menu'.
			'hookup'          => false, // No hookup.
		]));
	}

	/**
	 * Register the admin menu.
	 *
	 * @access private
	 */
	public function register_admin_menu() {
		$page_hook = add_management_page(
			esc_html__( 'Coming2Live Settings', 'coming2live' ), esc_html__( 'Coming2Live', 'coming2live' ), 'manage_options', 'c2l-settings', [ $this, 'output' ]
		);

		// Include CMB CSS in the head to avoid FOUC.
		add_action( "admin_print_styles-{$page_hook}", [ $this, 'enqueue_setting_scripts' ] );
	}

	/**
	 * Perform enqueue the styles & scripts.
	 *
	 * @return void
	 */
	public function enqueue_setting_scripts() {
		wp_enqueue_style( 'jquery-ui-slider-pips', $this->plugin->get_plugin_url( 'assets/vendor/jquery-ui-slider-pips/jquery-ui-slider-pips.css' ), [], '1.11.4' );
		wp_enqueue_script( 'jquery-ui-slider-pips', $this->plugin->get_plugin_url( 'assets/vendor/jquery-ui-slider-pips/jquery-ui-slider-pips.min.js' ), array( 'jquery-ui-slider' ), '1.11.4', true );

		\CMB2_hookup::enqueue_cmb_css();
		wp_enqueue_style( 'coming2live-settings', $this->plugin->get_plugin_url( 'assets/css/settings.css' ), [ 'cmb2-styles' ], $this->plugin->get_version() );

		\CMB2_hookup::enqueue_cmb_js();
		wp_enqueue_script( 'coming2live-settings', $this->plugin->get_plugin_url( 'assets/js/settings.js' ), [], $this->plugin->get_version(), true );


		// Fire setting enqueue scripts action.
		do_action( 'c2l_setting_enqueue_scripts' );

		// Support code editor in WP 4.9.
		// @see https://make.wordpress.org/core/2017/10/22/code-editing-improvements-in-wordpress-4-9 .
		if ( function_exists( 'wp_enqueue_code_editor' ) ) {
			// Bail if current screen is not "custom_css" section.
			if ( ! isset( $_REQUEST['section'] ) || ! in_array( $_REQUEST['section'], [ 'custom_css' ] ) ) {
				return;
			}

			// Enqueue code editor and settings for manipulating CSS.
			$settings = wp_enqueue_code_editor([
				'type' => 'text/css',
				'codemirror' => [
					'tabSize'    => 2,
					'indentUnit' => 2,
				],
			]);

			// Bail if user disabled CodeMirror.
			if ( false === $settings ) {
				return;
			}

			wp_add_inline_script( 'code-editor', sprintf(
				'jQuery( function() { wp.codeEditor.initialize( "custom_css", %s ); } );',
				wp_json_encode( $settings )
			));
		}
	}

	/**
	 * Register general section fields.
	 *
	 * @param  CMB2 $section The CMB2 section instance.
	 * @return void
	 */
	protected function register_general_fields( $section ) {
		$section->add_field([
			'id'    => '__general__',
			'type'  => 'title',
			'name'  => esc_html__( 'General', 'coming2live' ),
		]);

		$section->add_field([
			'id'    => 'enable',
			'type'  => 'c2l_toggle',
			'name'  => esc_html__( 'Enable?', 'coming2live' ),
		]);

		$section->add_field([
			'id'      => 'mode',
			'type'    => 'select',
			'name'    => esc_html__( 'Mode', 'coming2live' ),
			'options' => c2l_get_modes(),
			'before'  => function() {
				echo '<div class="c2l-sweet-note c2l-mb1">';
				echo '<strong>' . esc_html__( 'Coming Soon Mode', 'coming2live' ) . '</strong>';
				echo '<p class="c2l-mt0">' . esc_html__( 'Returns standard 200 HTTP OK response code to indexing robots. Set this option if you want to use our plugin as "Coming Soon" page.', 'coming2live' ) . '</p>';

				echo '<strong>' . esc_html__( 'Maintanance Mode', 'coming2live' ) . '</strong>';
				echo '<p class="c2l-mt0">' . esc_html__( 'Returns 503 HTTP Service unavailable code to indexing robots. Set this option if your site is down due to maintanance and you want to display Maintanance page.', 'coming2live' ) . '</p>';

				echo '<strong>' . esc_html__( 'Redirect Mode', 'coming2live' ) . '</strong>';
				echo '<p class="c2l-mt0">' . esc_html__( 'When you want to redirect your website to another URL.', 'coming2live' ) . '</p>';

				echo '</div>';
			},
		]);

		$section->add_field([
			'id'              => 'redirect_url',
			'type'            => 'text',
			'name'            => esc_html__( 'Redirect URL', 'coming2live' ),
			'desc'            => esc_html__( 'Please do not enter any current site URL to avoid  the infinite redirect loop.', 'coming2live' ),
			'sanitization_cb' => 'c2l_sanitize_redirect_another_url',
			'attributes'      => [
				'placeholder' => 'http://example.com',
			],
			'classes_cb'      => function() {
				return cl2_active_mode( Plugin::REDIRECT_MODE ) ? 'show' : '';
			},
		]);

		$section->add_field([
			'id'    => 'show_admin_bar',
			'type'  => 'c2l_toggle',
			'name'  => esc_html__( 'Display the admin bar?', 'coming2live' ),
		]);

		$section->add_field([
			'id'    => '__themes__',
			'type'  => 'title',
			'name'  => esc_html__( 'Themes', 'coming2live' ),
		]);

		$section->add_field([
			'id'         => '_theme',
			'type'       => 'c2l_themes',
			'save_field' => false,
		]);
	}

	/**
	 * Register advanced section fields.
	 *
	 * @param  CMB2 $section The CMB2 section instance.
	 * @return void
	 */
	protected function register_advanced_fields( $section ) {
		$section->add_field([
			'id'              => '__advanced__',
			'type'            => 'title',
			'name'            => esc_html__( 'Advanced', 'coming2live' ),
		]);

		$section->add_field([
			'id'              => 'filter_whitelist_pages',
			'type'            => 'c2l_toggle',
			'name'            => esc_html__( 'Filter whitelist pages?', 'coming2live' ),
		]);

		$section->add_field([
			'id'              => 'whitelist_pages',
			'type'            => 'post_search_text',
			'name'            => esc_html__( 'Whitelist pages', 'coming2live' ),
			'post_type'       => 'page',
			// 'select_behavior' => 'replace',
			'sanitization_cb' => 'c2l_sanitize_page_ids',
			'before'          => '<div class="c2l-input-addon">',
			'after'           => '</div><p class="cmb2-metabox-description">' . esc_html__( 'Enter a list comma-separated list of IDs.', 'coming2live' ) . '</p>',
			'classes_cb'      => function() {
				return c2l_option( 'filter_whitelist_pages' ) ? 'show' : '';
			},
		]);

		// SEO...
		$section->add_field([
			'id'    => '__seo__',
			'type'  => 'title',
			'name'  => esc_html__( 'SEO', 'coming2live' ),
		]);

		$section->add_field([
			'id'           => 'favicon',
			'type'         => 'file',
			'name'         => esc_html__( 'Favicon', 'coming2live' ),
			'options'      => [ 'url' => false ],
			'query_args'   => [ 'type' => 'image' ],
			'text'         => [ 'add_upload_file_text' => esc_html__( 'Select Favicon', 'coming2live' ) ],
			'preview_size' => 'medium',
		]);

		$section->add_field([
			'id'              => 'site_title',
			'type'            => 'text',
			'name'            => esc_html__( 'Site Title', 'coming2live' ),
			'sanitization_cb' => 'c2l_sanitize_text',
		]);

		$section->add_field([
			'id'              => 'site_description',
			'type'            => 'text',
			'name'            => esc_html__( 'Site Description', 'coming2live' ),
			'sanitization_cb' => 'c2l_sanitize_text',
		]);

		$section->add_field([
			'id'              => 'google_analytics',
			'type'            => 'text',
			'name'            => esc_html__( 'Google Analytics', 'coming2live' ),
			'desc'            => esc_html__( 'Insert Google Analytics Tracking ID', 'coming2live' ),
			'sanitization_cb' => 'c2l_sanitize_ga_code',
			'attributes'      => [
				'placeholder' => 'UA-xxxxxx-xx',
			],
		]);
	}

	/**
	 * Register content section fields.
	 *
	 * @param  CMB2 $section The CMB2 section instance.
	 * @return void
	 */
	protected function register_content_fields( $section ) {
		$section->add_field([
			'id'    => '__body__',
			'type'  => 'title',
			'name'  => esc_html__( 'Content', 'coming2live' ),
		]);

		$section->add_field([
			'id'              => 'body_title',
			'type'            => 'text',
			'name'            => esc_html__( 'Title', 'coming2live' ),
			'sanitization_cb' => 'c2l_sanitize_html',
		]);

		$section->add_field([
			'id'         => 'body_enable_animatedtitle',
			'type'       => 'c2l_toggle',
			'name'       => esc_html__( 'Enable animated title', 'coming2live' ),
		]);

		$section->add_field([
			'id'    => 'body_title_animated',
			'type'  => 'text',
			'repeatable' => true,
			'name'  => esc_html__( 'Animated text', 'coming2live' ),
		]);

		$section->add_field([
			'id'              => 'body_message',
			'type'            => 'wysiwyg',
			'name'            => esc_html__( 'Message', 'coming2live' ),
			'sanitization_cb' => 'c2l_sanitize_html',
			'options'         => [
				'textarea_rows' => 5,
			],
		]);

		// Countdown.
		$section->add_field([
			'id'    => '__countdown__',
			'type'  => 'title',
			'name'  => esc_html__( 'Countdown', 'coming2live' ),
		]);

		$section->add_field([
			'id'         => 'display_countdown',
			'type'       => 'c2l_toggle',
			'name'       => esc_html__( 'Enable countdown', 'coming2live' ),
			'show_on_cb' => $this->theme_support( 'countdown' ),
		]);

		$section->add_field([
			'id'         => 'countdown_datetime',
			'type'       => 'text_datetime_timestamp',
			'name'       => esc_html__( 'Countdown date', 'coming2live' ),
			'show_on_cb' => $this->theme_support( 'countdown' ),
			'date_format' => 'Y-m-d',
			'time_format' => 'H:i:s',
			'classes_cb' => function() {
				return c2l_option( 'display_countdown' ) ? 'show' : '';
			},
			'attributes'  => [
				'data-timepicker' => json_encode([
					'timeFormat' => 'HH:mm:ss',
					'stepMinute' => 1,
				]),
			],
		]);

		/*$section->add_field([
			'id'         => 'countdown_action',
			'type'       => 'select',
			'name'       => esc_html__( 'When countdown finish', 'coming2live' ),
			'options'    => [
				'disable_plugin' => esc_html__( 'Disable plugin', 'coming2live' ),
				'redirect_to'    => esc_html__( 'Disable plugin the redirect to a URL', 'coming2live' ),
			],
			'classes_cb' => function() {
				return c2l_option( 'display_countdown' ) ? 'show' : '';
			},
		]);*/

		$section->add_field([ // Fallback a unavailable message.
			'id'         => '__countdown_fallback__',
			'type'       => 'c2l_fallback',
			'save_field' => false,
			'show_on_cb' => $this->theme_not_support( 'countdown' ),
		]);

		// Subscribe.
		$section->add_field([
			'id'    => '__subscribe__',
			'type'  => 'title',
			'name'  => esc_html__( 'Subscribe', 'coming2live' ),
		]);

		$section->add_field([
			'id'         => 'display_subscribe',
			'type'       => 'c2l_toggle',
			'name'       => esc_html__( 'Enable subscribe', 'coming2live' ),
			'show_on_cb' => $this->theme_support( 'subscribe' ),
		]);

		$section->add_field([
			'id'              => 'subscribe_title',
			'type'            => 'text',
			'name'            => esc_html__( 'Title', 'coming2live' ),
			'sanitization_cb' => 'c2l_sanitize_html',
		]);

		$section->add_field([
			'id'              => 'subscribe_message',
			'type'            => 'wysiwyg',
			'name'            => esc_html__( 'Description', 'coming2live' ),
			'sanitization_cb' => 'c2l_sanitize_html',
			'options'         => [
				'textarea_rows' => 5,
			],
		]);

		$section->add_field([
			'id'         => 'subscribe_shortcode',
			'type'       => 'textarea',
			'name'       => esc_html__( 'Shortcode or HTML code', 'coming2live' ),
			'sanitization_cb' => function( $value ) { return $value; },
			'show_on_cb' => $this->theme_support( 'subscribe' ),
			'classes_cb' => function() {
				return c2l_option( 'display_subscribe' ) ? 'show' : '';
			},
		]);

		$section->add_field([ // Fallback a unavailable message.
			'id'         => '__subscribe_fallback__',
			'type'       => 'c2l_fallback',
			'save_field' => false,
			'show_on_cb' => $this->theme_not_support( 'subscribe' ),
		]);

		// Social Network.
		$section->add_field([
			'id'    => '__social__',
			'type'  => 'title',
			'name'  => esc_html__( 'Social Network', 'coming2live' ),
		]);

		$section->add_field([
			'id'         => 'display_social',
			'type'       => 'c2l_toggle',
			'name'       => esc_html__( 'Enable Social', 'coming2live' ),
			'show_on_cb' => $this->theme_support( 'social' ),
		]);

		$section->add_field([
			'id'         => 'social_links',
			'type'       => 'c2l_social',
			'name'       => esc_html__( 'Social', 'coming2live' ),
			'repeatable' => true,
			'show_on_cb' => $this->theme_support( 'social' ),
			'classes_cb' => function() {
				return c2l_option( 'display_social' ) ? 'show' : '';
			},
		]);

		$section->add_field([ // Fallback a unavailable message.
			'id'         => '__social_fallback__',
			'type'       => 'c2l_fallback',
			'save_field' => false,
			'show_on_cb' => $this->theme_not_support( 'social' ),
		]);

		// Footer copyright.
		$section->add_field([
			'id'    => '__copyright__',
			'type'  => 'title',
			'name'  => esc_html__( 'Copyright', 'coming2live' ),
		]);

		$section->add_field([
			'id'              => 'copyright',
			'type'            => 'text',
			'name'            => esc_html__( 'Footer copyright', 'coming2live' ),
			'show_on_cb'      => $this->theme_support( 'copyright' ),
			'sanitization_cb' => 'c2l_sanitize_html',
		]);

		$section->add_field([ // Fallback a unavailable message.
			'id'         => '__copyright_fallback__',
			'type'       => 'c2l_fallback',
			'save_field' => false,
			'show_on_cb' => $this->theme_not_support( 'copyright' ),
		]);
	}

	/**
	 * Register theme section fields.
	 *
	 * @param  CMB2 $section The CMB2 section instance.
	 * @return void
	 */
	protected function register_theme_fields( $section ) {
		$section->add_field([
			'id'         => '__background__',
			'type'       => 'title',
			'name'       => esc_html__( 'Background', 'coming2live' ),
			'show_on_cb' => $this->theme_support( 'custom-background' ),
		]);

		$section->add_field( array(
			'id'         => 'background',
			'type'       => 'c2l_background',
			'name'       => esc_html__( 'Background', 'coming2live' ),
			'show_on_cb' => $this->theme_support( 'custom-background' ),
		));

		$section->add_field( array(
			'id'         => 'background_effect',
			'type'       => 'c2l_background_effects',
			'name'       => esc_html__( 'Background Effect', 'coming2live' ),
			'show_on_cb' => $this->theme_support( 'background-effect' ),
		));

		/**
		 * Allow theme register own controls.
		 *
		 * @param CMB2 $section The CMB2 instance of theme section.
		 */
		do_action( 'c2l_theme_settings', $section );
	}

	/**
	 * Register custom_css section fields.
	 *
	 * @param  CMB2 $section The CMB2 section instance.
	 * @return void
	 */
	protected function register_custom_css_fields( $section ) {
		$section->add_field([
			'id'    => '__custom_css__',
			'type'  => 'title',
			'name'  => esc_html__( 'Custom CSS', 'coming2live' ),
		]);

		$section->add_field([
			'id'              => 'custom_css',
			'type'            => 'textarea',
			'name'            => esc_html__( 'Custom CSS', 'coming2live' ),
			'show_names'      => false,
			'before'          => function() {
				echo '<p class="c2l-mt0">' . esc_html__( 'Add your own CSS code here to customize the appearance and layout of current theme', 'coming2live' ) . '. <a href="https://codex.wordpress.org/CSS" class="external-link" target="_blank">' . esc_html__( 'Learn more about CSS', 'coming2live' ) . '</a></p>';
			},
			'sanitization_cb' => function( $value ) {
				return strip_tags( $value );
			},
		]);
	}

	/**
	 * Returns a callback to determines if current theme is support given features.
	 *
	 * @param  string|array $features The features.
	 * @return Closure
	 */
	protected function theme_support( $features ) {
		$features = is_array( $features ) ? $features : func_get_args();

		return function() use ( $features ) {
			return $this->plugin->get_theme()->support( $features );
		};
	}

	/**
	 * Returns a callback to determines if current theme is not support given feature.
	 *
	 * @param  string $feature The feature.
	 * @return Closure
	 */
	protected function theme_not_support( $feature ) {
		$features = is_array( $feature ) ? $feature : func_get_args();

		return function() use ( $features ) {
			return ! $this->plugin->get_theme()->support( $features );
		};
	}
}
