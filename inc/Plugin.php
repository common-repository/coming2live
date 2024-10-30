<?php
namespace Coming2Live;

final class Plugin {
	/* Constants */
	const VERSION          = '0.1.0';
	const REDIRECT_MODE    = 'redirect';
	const COMING_SOON_MODE = 'landingpage';
	const MAINTANANCE_MODE = 'maintanance';
	const OPTION_KEY_NAME  = 'coming2live_settings';
	const THEME_MOD_PREFIX = 'coming2live_themes_';

	/**
	 * The plugin file path.
	 *
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * Cache the options.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Cache the ttheme_mods.
	 *
	 * @var array
	 */
	protected $theme_mods;

	/**
	 * The starter content for theme_mods.
	 *
	 * @var array
	 */
	protected $starter_content;

	/**
	 * List of the found themes.
	 *
	 * @var array
	 */
	protected $found_themes = [];

	/**
	 * List the themes.
	 *
	 * @var array
	 */
	protected $themes = [];

	/**
	 * The cached of current theme.
	 *
	 * @var string
	 */
	protected $current_theme;

	/**
	 * Singleton class instance implementation.
	 *
	 * @var static
	 */
	protected static $instance;

	/**
	 * Set the globally available instance of the container.
	 *
	 * @return static
	 */
	public static function get_instance() {
		return static::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @param string $plugin_file The plugin file path.
	 */
	public function __construct( $plugin_file ) {
		static::$instance = $this;

		$this->plugin_file = $plugin_file;

		c2l_maybe_define( 'C2L_VERSION', static::VERSION );
	}

	/**
	 * Get the plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return static::VERSION;
	}

	/**
	 * Doing actions when plugin 'activation'.
	 *
	 * @access private
	 */
	public function activation() {
		if ( apply_filters( 'c2l_activation_redirect', true ) ) {
			set_transient( '_c2l_activation_redirect', 1, 30 );
		}
	}

	/**
	 * Run the plugin when 'plugins_loaded'.
	 *
	 * @access private
	 */
	public function run() {
		( new Landing_Page( $this ) )->init();

		add_action( 'cmb2_admin_init', function() {
			( new Extra_Fields )->init();

			( new Admin_Options( $this ) )->init();
		});

		add_action( 'wp_loaded', [ $this, 'setup_plugins' ], 10 );
		add_action( 'c2l_after_setup', [ $this, 'setup_theme' ], 5 );

		add_action( 'admin_init', [ $this, 'admin_redirect' ] );
		add_action( 'admin_head', [ $this, 'print_admin_css' ] );
		add_action( 'admin_bar_menu', [ $this, 'add_menu_admin_bar' ], 1001 );

		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
		add_filter( "plugin_action_links_{$this->get_plugin_slug()}", [ $this, 'plugin_action_links' ] );

		// Load the plugin text-domain.
		load_plugin_textdomain( 'coming2live', false, dirname( $this->get_plugin_slug() ) . '/languages/' );
	}

	/**
	 * Determines if the C2L is enabled.
	 *
	 * @return boolean
	 */
	public function is_enabled() {
		return apply_filters( 'c2l_enabled', $this->get_option( 'enable' ), $this );
	}

	/**
	 * Perform disable state of the plugin.
	 *
	 * @param  string $reason The reason to disable.
	 * @return bpol
	 */
	public function disable( $reason ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		// Disable the state of the plugin.
		$this->update_option( 'enable', false );

		// Fire action after disable.
		do_action( 'c2l_disabled', $reason );

		return true;
	}

	/**
	 * Is current site is under a maintenance mode.
	 *
	 * @param  string|null $mode If null will check any mode.
	 * @return boolean
	 */
	public function is_active_mode( $mode = null ) {
		$site_mode = $this->get_option( 'mode' );

		if ( is_null( $mode ) || 'any' === $mode ) {
			return ! is_null( $site_mode );
		}

		return $mode == $site_mode;
	}

	/**
	 * Get the plugin url.
	 *
	 * @param  string|null $path Optional, extra path.
	 * @return string
	 */
	public function get_plugin_url( $path = null ) {
		return plugin_dir_url( $this->plugin_file ) . ( $path ? ltrim( $path, '/' ) : '' );
	}

	/**
	 * Get the plugin path.
	 *
	 * @param string|null $path Optional, extra path.
	 * @return string
	 */
	public function get_plugin_path( $path = null ) {
		return plugin_dir_path( $this->plugin_file ) . ( $path ? ltrim( $path, '/' ) : '' );
	}

	/**
	 * Get the plugin slug (coming2live/coming2live.php).
	 *
	 * @return string
	 */
	public function get_plugin_slug() {
		return plugin_basename( $this->get_plugin_path() ) . '/coming2live.php';
	}

	/**
	 * Returns the option key-name.
	 *
	 * @return string
	 */
	public function get_option_key() {
		return apply_filters( 'c2l_option_key', static::OPTION_KEY_NAME );
	}

	/**
	 * Returns the theme_mod key-name.
	 *
	 * @return string
	 */
	public function get_theme_mod_key() {
		return apply_filters( 'c2l_theme_mod_key', static::THEME_MOD_PREFIX . $this->get_current_theme() );
	}

	/**
	 * Get the current theme.
	 *
	 * @return string
	 */
	public function get_current_theme() {
		if ( is_null( $this->current_theme ) ) {
			$default = apply_filters( 'c2l_default_theme', 'free-01' );

			// Allow user hook into this to change the current theme.
			$this->current_theme = apply_filters( 'c2l_current_theme',
				$this->get_option( 'current_theme', $default ), $this
			);
		}

		return $this->current_theme;
	}

	/**
	 * Get all stored options.
	 *
	 * @return array
	 */
	public function get_options() {
		if ( is_null( $this->options ) ) {
			$this->options = get_option( $this->get_option_key(), [] );
		}

		return $this->options;
	}

	/**
	 * Retrieves an option by key-name.
	 *
	 * @param  string $key     The key name.
	 * @param  mixed  $default The default value.
	 * @return mixed
	 */
	public function get_option( $key, $default = null ) {
		$options = $this->get_options();

		$option = array_key_exists( $key, $options )
			? $options[ $key ]
			: $default;

		return $this->sanitize_option( $key, $option );
	}

	/**
	 * Update the value of an option.
	 *
	 * @param  string $key   The key name.
	 * @param  mixed  $value The update value.
	 * @return bool
	 */
	public function update_option( $key, $value ) {
		return cmb2_update_option( $this->get_option_key(), $key, $value );
	}

	/**
	 * Sanitises various option values based on the nature of the option.
	 *
	 * @param  string $key   The name of the option.
	 * @param  string $value The unsanitised value.
	 * @return string
	 */
	protected function sanitize_option( $key, $value ) {
		// Pre-sanitize option by key name.
		switch ( $key ) {
			case 'enable':
			case 'show_admin_bar':
			case 'filter_whitelist_pages':
			case 'display_countdown':
			case 'display_subscribe':
				$value = ( 'on' === $value || true === $value || 1 == $value );
				break;

			case 'whitelist_pages':
				$value = wp_parse_id_list( $value );
				break;

			case 'social_links':
				$value = is_array( $value ) ? $value : [];
				break;
		}

		/**
		 * Allow custom sanitize a special option value.
		 *
		 * @param mixed $value Mixed option value.
		 * @var   mixed
		 */
		$value = apply_filters( "c2l_sanitize_option_{$key}", $value );

		/**
		 * Allow custom sanitize option values.
		 *
		 * @param mixed  $value The option value.
		 * @param string $key   The option key name.
		 * @var   mixed
		 */
		return apply_filters( 'c2l_sanitize_option', $value, $key );
	}

	/**
	 * Retrieve all theme modifications.
	 *
	 * @return array
	 */
	public function get_theme_mods() {
		if ( is_null( $this->theme_mods ) ) {
			$this->theme_mods = get_option( $this->get_theme_mod_key(), [] );
		}

		return $this->theme_mods;
	}

	/**
	 * Retrieves theme modification by key-name.
	 *
	 * @param  string $key     The key name.
	 * @param  mixed  $default The default value.
	 * @return mixed
	 */
	public function get_theme_mod( $key, $default = null ) {
		$theme_mods = $this->get_theme_mods();

		$theme_mod = array_key_exists( $key, $theme_mods )
			? $theme_mods[ $key ]
			: $default;

		return $this->sanitize_theme_mod( $key, $theme_mod );
	}

	/**
	 * Update the value of an theme_mod.
	 *
	 * @param  string $key   The key name.
	 * @param  mixed  $value The update value.
	 * @return bool
	 */
	public function update_theme_mod( $key, $value ) {
		return cmb2_update_option( $this->get_theme_mod_key(), $key, $value );
	}

	/**
	 * Sanitises the theme_mod values.
	 *
	 * @param  string $key   The key name.
	 * @param  string $value The unsanitised value.
	 * @return string
	 */
	protected function sanitize_theme_mod( $key, $value ) {
		switch ( $key ) {
			case 'logo':
				$value = ! is_array( $value ) ? $value : [];
				break;
		}

		/**
		 * Allow custom sanitize a special theme_mod value.
		 *
		 * @param mixed $value Mixed theme_mod value.
		 * @var   mixed
		 */
		$value = apply_filters( "c2l_sanitize_theme_mod_{$key}", $value );

		/**
		 * Allow custom sanitize the theme_mod values.
		 *
		 * @param mixed  $value The theme_mod value.
		 * @param string $key   The theme_mod key name.
		 * @var   mixed
		 */
		return apply_filters( 'c2l_sanitize_theme_mod', $value, $key );
	}

	/**
	 * Get the starter_content.
	 *
	 * @return array|null
	 */
	public function get_starter_content() {
		return $this->starter_content;
	}

	/**
	 * Set the starter_content.
	 *
	 * @param array $content The starter content.
	 */
	public function set_starter_content( $content ) {
		$this->starter_content = $this->parse_starter_content( $content );
	}

	/**
	 * Parse the starter_content.
	 *
	 * @param  array $content The starter content.
	 * @return array
	 */
	protected function parse_starter_content( $content ) {
		if ( ! is_array( $content ) ) {
			return;
		}

		foreach ( $content as $key => &$value ) {
			switch ( $key ) {
				case 'background':
					$value = c2l_sanitize_background( $value );
					break;

				default:
					$value = apply_filters( 'parse_starter_content', $value, $key, $content );
					break;
			}
		}

		return array_filter( $content );
	}

	/**
	 * Get a C2L_Theme instance.
	 *
	 * @param  string $theme The theme name.
	 * @return \Coming2Live\C2L_Theme|null
	 */
	public function get_theme( $theme = null ) {
		if ( is_null( $theme ) ) {
			$theme = $this->get_current_theme();
		}

		// If theme is resolved, just return it.
		if ( ! is_null( $this->themes ) && array_key_exists( $theme, $this->themes ) ) {
			return $this->themes[ $theme ];
		}

		// Create the theme object.
		$c2l_theme = $this->create_theme_object( $theme );

		// Bail if theme is not exists.
		if ( is_null( $c2l_theme ) || ! $c2l_theme->exists() ) {
			return;
		}

		return $this->themes[ $theme ] = $c2l_theme;
	}

	/**
	 * Create the theme object.
	 *
	 * @param  string $theme The theme name.
	 * @return \Coming2Live\C2L_Theme|null
	 */
	protected function create_theme_object( $theme ) {
		// Bail if not found the theme.
		if ( ! array_key_exists( $theme, $this->found_themes ) ) {
			return;
		}

		return new C2L_Theme( $theme,
			$this->found_themes[ $theme ]['theme_root']
		);
	}

	/**
	 * Get all themes.
	 *
	 * @return array
	 */
	public function get_themes() {
		foreach ( $this->found_themes as $name => $themeinfo ) {
			if ( array_key_exists( $name, $this->themes ) ) {
				continue;
			}

			$theme = $this->create_theme_object( $name );
			if ( is_null( $theme ) || ! $theme->exists() ) {
				continue;
			}

			$this->themes[ $name ] = $theme;
		}

		return $this->themes;
	}

	/**
	 * Switches the theme.
	 *
	 * @param  string $theme The new name.
	 * @return bool
	 */
	public function switch_theme( $theme ) {
		$old_theme = $this->get_theme();
		$new_theme = $this->get_theme( $theme );

		// Prevent switch to non-exists theme.
		if ( ! $new_theme->exists() ) {
			return false;
		}

		$new_name = $new_theme->get_theme();

		// Perform update new theme.
		$this->update_option( 'current_theme', $new_name );
		$this->current_theme = $new_name;

		do_action( 'c2l_switch_theme', $new_name, $new_theme, $old_theme );

		return true;
	}

	/**
	 * Perform setup plugins.
	 *
	 * @access private
	 */
	public function setup_plugins() {
		// Scan the themes.
		$theme_directories = apply_filters( 'c2l_theme_directories', [
			realpath( __DIR__ . '/../themes/' ),
		]);

		do_action( 'c2l_setup', $this );

		$this->found_themes = C2L_Theme::scan_themes( $theme_directories );

		do_action( 'c2l_after_setup', $this );
	}

	/**
	 * Perform setup theme.
	 *
	 * @access private
	 */
	public function setup_theme() {
		// Resolve the theme object.
		$theme = $this->get_theme();

		// Load the theme functions.
		if ( file_exists( $theme->get_theme_dir() . 'functions.php' ) ) {
			require_once $theme->get_theme_dir() . 'functions.php';
		}

		// Fire action setup theme.
		do_action( 'c2l_setup_theme', $this );
	}

	/**
	 * Handle redirects to settings page after install.
	 *
	 * @return void
	 * @access private
	 */
	public function admin_redirect() {
		// Bail if no activation redirect.
		if ( ! get_transient( '_c2l_activation_redirect' ) ) {
			return;
		}

		// Delete the transient.
		delete_transient( '_c2l_activation_redirect' );

		// Bail if activating from network, or bulk.
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'tools.php?page=c2l-settings' ) );
		exit;
	}

	/**
	 * Print the admin CSS>
	 *
	 * @access private
	 */
	public function print_admin_css() {
		?><style type="text/css">
			#wpadminbar .c2l-indicator-status { background-color: #9e9e9e; display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px; }
			#wpadminbar .c2l-indicator-status.active { background-color: #ff9800; }
		</style><?php // @codingStandardsIgnoreLine
	}

	/**
	 * Add menus into the admin_bar.
	 *
	 * @param  WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar class instance.
	 * @access private
	 */
	public function add_menu_admin_bar( $wp_admin_bar ) {
		if ( is_network_admin() ) {
			return;
		}

		// Let's build the title.
		$title = esc_html__( 'Coming2Live', 'coming2live' );
		$indicator = '<span class="c2l-indicator-status ' . ( $this->is_enabled() ? 'active' : '' ) . '"></span>';

		switch ( $this->get_option( 'mode', static::COMING_SOON_MODE ) ) {
			case static::COMING_SOON_MODE:
				$title = esc_html__( 'Coming Soon Mode', 'coming2live' );
				break;
			case static::MAINTANANCE_MODE:
				$title = esc_html__( 'Maintanance Mode', 'coming2live' );
				break;
			case static::REDIRECT_MODE:
				$title = esc_html__( 'Redirect Mode', 'coming2live' );
				break;
		}

		$wp_admin_bar->add_node([
			'id'     => 'coming2live-node',
			'title'  => $indicator . $title,
			'href'   => admin_url( 'tools.php?page=c2l-settings' ),
			'parent' => false,
			'meta'   => [ 'class' => 'c2l-adminbar' ],
		]);

		$wp_admin_bar->add_node([
			'parent' => 'coming2live-node',
			'id'     => 'coming2live-node-preview',
			'title'  => esc_html__( 'Preview', 'coming2live' ),
			'href'   => add_query_arg( 'c2l-preview', 1, get_home_url() ),
			'meta'   => [ 'target' => '_blank' ],
		]);

		if ( current_user_can( 'manage_options' ) ) {
			$wp_admin_bar->add_node([
				'parent' => 'coming2live-node',
				'id'     => 'coming2live-node-settings',
				'title'  => esc_html__( 'C2L Settings', 'coming2live' ),
				'href'   => admin_url( 'tools.php?page=c2l-settings' ),
			]);
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @access private
	 *
	 * @param  mixed $links Plugin action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$action_links = apply_filters( 'c2l_plugin_action_links', [
			'settings' => '<a href="' . admin_url( 'tools.php?page=c2l-settings' ) . '" aria-label="' . esc_attr__( 'View Settings', 'coming2live' ) . '">' . esc_html__( 'Settings', 'coming2live' ) . '</a>',
		]);

		return array_merge( $action_links, $links );
	}

	/**
	 * Adds links to the docs and GitHub.
	 *
	 * @param  array  $plugin_meta The current array of links.
	 * @param  string $plugin_file The plugin file.
	 * @return array
	 *
	 * @access private
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( $this->get_plugin_slug() === $plugin_file ) {
			$plugin_meta[] = sprintf( '<a href="%s" target="_blank">%s</a>',
				esc_url( 'http://docs.awethemes.com/coming2live' ),
				esc_html__( 'Documentation', 'coming2live' )
			);

			$plugin_meta[] = sprintf( '<a href="%s" target="_blank">%s</a>',
				esc_url( 'https://github.com/awethemes/coming2live' ),
				esc_html__( 'GitHub Repo', 'coming2live' )
			);

			$plugin_meta[] = sprintf( '<a href="%s" target="_blank">%s</a>',
				esc_url( 'https://github.com/awethemes/coming2live/issues' ),
				esc_html__( 'Issue Tracker', 'coming2live' )
			);
		}

		return $plugin_meta;
	}
}
