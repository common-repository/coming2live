<?php
namespace Coming2Live;

class Landing_Page {
	/**
	 * The plugin class instance.
	 *
	 * @var \Coming2Live\Plugin
	 */
	protected $plugin;

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
		add_action( 'template_redirect', [ $this, 'output' ] );
	}

	/**
	 * Output the landing page.
	 *
	 * This will hooked in "template_redirect" action and
	 * output the comming landing-page when enabled.
	 *
	 * @return void
	 */
	public function output() {
		if ( ! $this->should_output_page() ) {
			return;
		}

		// Handle the countdown time.
		$handled = $this->maybe_handle_countdown();
		if ( true === $handled ) {
			return;
		}

		// Prepare before output.
		$this->prepare_output();

		// Handle the redirect mode.
		if ( $this->plugin->is_active_mode( Plugin::REDIRECT_MODE ) ) {
			$redirect_url = c2l_sanitize_redirect_another_url( $this->plugin->get_option( 'redirect_url' ) );

			// In redirect mode, we need check redrect URL is valid too.
			if ( ! $redirect_url ) {
				trigger_error( esc_html__( 'The site was setting up in redirect mode but not given a valid redirect URL.', 'coming2live' ), E_USER_WARNING );
			} else {
				$this->send_redirect_response( $redirect_url );
				exit;
			}
		}

		// Maintanance mode, send a 503 headers.
		if ( $this->plugin->is_active_mode( Plugin::MAINTANANCE_MODE ) ) {
			status_header( 503 );
			header( 'Retry-After: 86400' ); // Retry after a day.
		}

		// Load the template.
		include apply_filters( 'template_include', c2l_get_template( $this->plugin->get_current_theme() ) );
		exit;
	}

	/**
	 * Maybe handle the countdown time.
	 *
	 * @return void
	 */
	protected function maybe_handle_countdown() {
		if ( ! $this->plugin->get_option( 'display_countdown' ) ) {
			return;
		}

		// Allow running in the preview.
		if ( defined( 'COMING2LIVE_IN_PREVIEW' ) ) {
			return;
		}

		if ( c2l_countdown_datetime()->getTimestamp() <= current_time( 'timestamp' ) ) {
			$this->plugin->disable( 'by_countdown' );
			return true;
		}
	}

	/**
	 * Prepare output response.
	 *
	 * @return void
	 */
	protected function prepare_output() {
		c2l_maybe_define( 'COMING2LIVE_ACTIVE', true );

		// Require template functions.
		require_once $this->plugin->get_plugin_path() . 'inc/templates.php';

		// Prevent caching page and headers.
		c2l_nocache_headers();

		do_action( 'c2l_prepare_output', $this->plugin );
	}

	/**
	 * Send a redirect response.
	 *
	 * @param  string $location The location redirect to.
	 * @return void
	 */
	protected function send_redirect_response( $location ) {
		wp_redirect( $location, 302 );

		// Print a fallback redirect by HTML.
		printf('<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="refresh" content="0;url=%1$s" />
		<title>Redirecting to %1$s</title>
	</head>
	<body>
		Redirecting to <a href="%1$s">%1$s</a>.
	</body>
</html>', esc_url( $location ) );
	}

	/**
	 * Determines in current request to output the "landing-page".
	 *
	 * @return bool
	 */
	protected function should_output_page() {
		// For logged-in users, we not output the "landing-page"
		// unless have a preview request.
		if ( is_user_logged_in() ) {
			if ( isset( $_REQUEST['c2l-preview'] ) && 1 === absint( $_REQUEST['c2l-preview'] ) ) {
				c2l_maybe_define( 'COMING2LIVE_IN_PREVIEW', true );
				return true;
			}

			return false;
		}

		// Don't output if the plugin is not enabled.
		if ( ! $this->plugin->is_enabled() ) {
			return false;
		}

		// Filter the whitelist pages.
		if ( $this->plugin->get_option( 'filter_whitelist_pages' ) ) {
			$current_page = get_the_ID();

			if ( false !== $current_page ) {
				$whitelist_pages = apply_filters( 'c2l_whitelist_pages', (array) $this->plugin->get_option( 'whitelist_pages', [] ) );
				$whitelist_pages = array_map( 'absint', array_unique( $whitelist_pages ) );

				// Prevent show in some whitelist pages.
				if ( ! empty( $whitelist_pages ) && in_array( $current_page, $whitelist_pages ) ) {
					return false;
				}
			}
		}

		return apply_filters( 'c2l_apply_condations', true );
	}
}
