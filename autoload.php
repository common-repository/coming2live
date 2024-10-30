<?php
/**
 * Coming2Live autoload file.
 *
 * @package Coming2Live
 */

/**
 * Require the CMB2.
 */
require_once trailingslashit( __DIR__ ) . 'vendor/webdevstudios/cmb2/init.php';
require_once trailingslashit( __DIR__ ) . 'vendor/webdevstudios/cmb2-post-search-field/cmb2_post_search_field.php';
require_once trailingslashit( __DIR__ ) . 'vendor/eduplessis/cmb2-typography/typography-field-type.php';

/**
 * Require the functions.php & sanitizer.php
 */
require_once trailingslashit( __DIR__ ) . 'inc/functions.php';
require_once trailingslashit( __DIR__ ) . 'inc/sanitizer.php';

/**
 * Require the main class.
 */
require_once trailingslashit( __DIR__ ) . 'inc/Plugin.php';

/**
 * Alias the class "Coming2Live\Plugin" to "Coming2Live".
 */
class_alias( 'Coming2Live\Plugin', 'Coming2Live', false );

/**
 * Coming2Live PSR-4 autoload implementation.
 *
 * @link http://www.php-fig.org/psr/psr-4/examples/
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ( $class ) {
	// Project-specific namespace prefix.
	$prefix = 'Coming2Live\\';

	// Base directory for the namespace prefix.
	$base_dir = __DIR__ . '/inc/';

	// Does the class use the namespace prefix?
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		// No, move to the next registered autoloader.
		return;
	}

	// Get the relative class name.
	$relative_class = substr( $class, $len );

	// Replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php.
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	// Ff the file exists, require it.
	if ( file_exists( $file ) ) {
		require $file;
	}
});
