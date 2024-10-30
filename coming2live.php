<?php
/*
 * Plugin Name: Coming2Live
 * Plugin URI: https://coming2.live
 * Description: Coming2live plugin help you to set up a Coming Soon or Maintenance Mode page with some simple clicks.
 * Author: coming2live
 * Author URI: https://coming2.live
 * Text Domain: coming2live
 * Domain Path: /languages
 * Version: 1.0.0
 *
 * @package         Coming2Live
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Silence is golden.' );
}

/**
 * Plugin works only with PHP 5.4.0 or later.
 */
if ( version_compare( phpversion(), '5.4.0', '<' ) ) {
	/**
	 * Adds a message for outdate PHP version.
	 */
	function c2l_php_upgrade_notice() {
		/* translators: %s Current PHP version */
		$message = sprintf( esc_html__( 'Coming2Live requires at least PHP version 5.4.0 to works, you are running version %s. Please contact to your administrator to upgrade PHP version!', 'coming2live' ), phpversion() );
		printf( '<div class="error"><p>%s</p></div>', $message ); // WPCS: XSS OK.

		deactivate_plugins( array( 'coming2live/coming2live.php' ) );
	}

	add_action( 'admin_notices', 'c2l_php_upgrade_notice' );
	return;
}

if ( ! class_exists( 'Coming2Live\Plugin', false ) ) {
	require_once trailingslashit( dirname( __FILE__ ) ) . 'autoload.php';

	// Create the Coming2Live.
	$coming2live = new Coming2Live( __FILE__ );

	// Register the activation hooks.
	register_activation_hook( __FILE__, array( $coming2live, 'activation' ) );

	// Run the plugin when 'plugins_loaded'.
	add_action( 'plugins_loaded', array( $coming2live, 'run' ) );
}
