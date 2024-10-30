<?php
namespace Coming2Live;

use WP_Error;
use CMB2_Utils;
use DirectoryIterator;
use JsonSerializable;

class C2L_Theme implements JsonSerializable {
	/**
	 * The theme directory name.
	 *
	 * @var string
	 */
	protected $theme_dir;

	/**
	 * Absolute path to the theme root.
	 *
	 * @var string
	 */
	protected $theme_root;

	/**
	 * The theme header.
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Errors encountered when initializing the theme.
	 *
	 * @var WP_Error
	 */
	protected $errors;

	/**
	 * Cache the theme screenshot URL.
	 *
	 * @var string
	 */
	protected $screenshot;

	/**
	 * Cache the resolved theme_root_uri.
	 *
	 * @var array
	 */
	protected static $theme_roots_uri = [];

	/**
	 * Headers for style.css files.
	 *
	 * @var array
	 */
	protected static $file_headers = array(
		'name'        => 'Theme Name',
		'theme_uri'   => 'Theme URI',
		'description' => 'Description',
		'author'      => 'Author',
		'author_uri'  => 'Author URI',
		'version'     => 'Version',
		'premium'     => 'Premium',
		'features'    => 'Features',
		'supports'    => 'Supports',
	);

	/**
	 * Scan themes by given an array of theme directories.
	 *
	 * @param  array $theme_directories The theme directories.
	 * @return array
	 */
	public static function scan_themes( $theme_directories ) {
		$found_themes = [];

		// Don't do anything if empty the theme_directories.
		if ( empty( $theme_directories ) ) {
			return $found_themes;
		}

		foreach ( (array) $theme_directories as $theme_root ) {
			$theme_root = @realpath( $theme_root );
			if ( ! is_dir( $theme_root ) ) {
				continue;
			}

			foreach ( ( new DirectoryIterator( $theme_root ) ) as $fileinfo ) {
				$directory = $fileinfo->getPathName();

				if ( ! $fileinfo->isDir() || $fileinfo->isDot()
					|| ! file_exists( $directory . '/style.css' ) ) {
					continue;
				}

				// Push the found theme in the list.
				$found_themes[ $fileinfo->getBaseName() ] = [
					'theme_file' => $directory . '/style.css',
					'theme_root' => $theme_root,
				];
			}
		}

		// Sort the list found themes.
		asort( $found_themes );

		return $found_themes;
	}

	/**
	 * Constructor.
	 *
	 * @param string $theme_dir  Directory of the theme within the theme_root.
	 * @param string $theme_root Theme root.
	 */
	public function __construct( $theme_dir, $theme_root ) {
		$this->theme_dir  = $theme_dir;
		$this->theme_root = $theme_root;

		$this->prepare_reading();

		if ( is_null( $this->errors ) ) {
			$this->reading_headers();
		}
	}

	/**
	 * Returns errors property.
	 *
	 * @return WP_Error|false WP_Error if there are errors.
	 */
	public function errors() {
		return is_wp_error( $this->errors ) ? $this->errors : null;
	}

	/**
	 * Whether the theme exists.
	 *
	 * @return bool
	 */
	public function exists() {
		return ! ( $this->errors() && in_array( 'theme_not_found', $this->errors()->get_error_codes() ) );
	}

	/**
	 * Return the theme headers.
	 *
	 * @return array
	 */
	public function headers() {
		return $this->headers;
	}

	/**
	 * Determines if given feature is supported by theme.
	 *
	 * @param  string $feature The feature.
	 * @return bool
	 */
	public function support( $feature ) {
		$features = is_array( $feature ) ? $feature : func_get_args();

		$supported = (array) $this->get( 'supports', [] );

		foreach ( $features as $key ) {
			if ( ! in_array( $key, $supported ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get a header by given a key-name.
	 *
	 * @param  string $name The header key name.
	 * @return mixed
	 */
	public function get( $name ) {
		switch ( $name ) {
			case 'theme':
				return $this->get_theme();
			case 'screenshot':
				return $this->get_screenshot();
			case 'theme_dir':
				return $this->get_theme_dir();
			case 'theme_uri':
				return $this->get_theme_uri();
			case 'theme_root':
				return $this->get_theme_root();
			case 'theme_root_uri':
				return $this->get_theme_root_uri();
			default:
				return array_key_exists( $name, $this->headers ) ? $this->headers[ $name ] : null;
		}
	}

	/**
	 * Get the theme name (directory name).
	 *
	 * @return string
	 */
	public function get_theme() {
		return $this->theme_dir;
	}

	/**
	 * Get the theme name.
	 *
	 * @return string
	 */
	public function get_name() {
		$name = $this->get( 'name' );

		return $name ?: $this->get_theme();
	}

	/**
	 * Get the theme directory.
	 *
	 * @return string
	 */
	public function get_theme_dir() {
		return trailingslashit( $this->get_theme_root() . $this->get_theme() );
	}

	/**
	 * Get the theme URI.
	 *
	 * @return string
	 */
	public function get_theme_uri() {
		return trailingslashit( $this->get_theme_root_uri() . $this->get_theme() );
	}

	/**
	 * Get the theme root.
	 *
	 * @return string
	 */
	public function get_theme_root() {
		return trailingslashit( $this->theme_root );
	}

	/**
	 * Returns the URL to the directory of the theme root.
	 *
	 * @return string
	 */
	public function get_theme_root_uri() {
		$theme_root = $this->get_theme_root();

		if ( array_key_exists( $theme_root, static::$theme_roots_uri ) ) {
			return static::$theme_roots_uri[ $theme_root ];
		}

		return static::$theme_roots_uri[ $theme_root ] = $this->guest_url_from_dir( $theme_root );
	}

	/**
	 * Returns the main screenshot file for the theme.
	 *
	 * @return string
	 */
	public function get_screenshot() {
		if ( ! is_null( $this->screenshot ) ) {
			return $this->screenshot;
		}

		$screenshot = '';
		foreach ( array( 'png', 'gif', 'jpg', 'jpeg' ) as $ext ) {
			if ( file_exists( $this->get_theme_dir() . "screenshot.{$ext}" ) ) {
				$screenshot = $this->get_theme_uri() . "screenshot.{$ext}";
				break;
			}
		}

		return $this->screenshot = $screenshot;
	}

	/**
	 * Guest the url from a path.
	 *
	 * @param  string $path The absolute path.
	 * @return string
	 */
	protected function guest_url_from_dir( $path ) {
		if ( 0 === strpos( $path, coming2live()->get_plugin_path( 'themes/' ) ) ) {
			return coming2live()->get_plugin_url( 'themes/' );
		}

		if ( ! class_exists( 'CMB2_Utils' ) ) {
			require_once __DIR__ . '/../vendor/webdevstudios/cmb2/includes/CMB2_Utils.php';
		}

		return CMB2_Utils::get_url_from_dir( $path );
	}

	/**
	 * Prepare reading headers.
	 *
	 * @return void
	 */
	protected function prepare_reading() {
		if ( ! is_dir( $this->theme_root . '/' . $this->theme_dir ) ) {
			/* translators: %s The theme name */
			$this->errors = new WP_Error( 'theme_not_found', sprintf( esc_html__( 'The theme directory "%s" does not exist.', 'coming2live' ), esc_html( $this->theme_dir ) ) );
			return;
		}

		// Check the theme stylesheet.
		$stylesheet = $this->theme_dir . '/style.css';

		if ( ! file_exists( $this->theme_root . '/' . $stylesheet ) ) {
			$this->errors = new WP_Error( 'theme_no_stylesheet', esc_html__( 'Stylesheet is missing.', 'coming2live' ) );
		} elseif ( ! is_readable( $this->theme_root . '/' . $stylesheet ) ) {
			$this->errors = new WP_Error( 'theme_stylesheet_not_readable', esc_html__( 'Stylesheet is not readable.', 'coming2live' ) );
		}
	}

	/**
	 * Perform reading headers.
	 *
	 * @return void
	 */
	protected function reading_headers() {
		$headers = @get_file_data( $this->theme_root . '/' . $this->theme . '/style.css', static::$file_headers, 'c2l_theme' );

		foreach ( $headers as $key => &$value ) {
			$value = $this->sanitize_header( $key, $value );
		}

		$this->headers = $headers;
	}

	/**
	 * Sanitize a theme header.
	 *
	 * @param  string $header Theme header.
	 * @param  string $value  Value to sanitize.
	 * @return mixed
	 */
	protected function sanitize_header( $header, $value ) {
		switch ( $header ) {
			case 'name':
				$value = wp_kses( $value, [
					'abbr'    => [ 'title' => true ],
					'acronym' => [ 'title' => true ],
					'code'    => true,
					'em'      => true,
					'strong'  => true,
				]);
				break;
			case 'features':
			case 'description':
				$value = wp_kses( $value, [
					'a'       => [ 'title' => true, 'href' => true ], // @codingStandardsIgnoreLine
					'abbr'    => [ 'title' => true ],
					'acronym' => [ 'title' => true ],
					'code'    => true,
					'em'      => true,
					'strong'  => true,
				]);
				break;
			case 'theme_uri':
			case 'author_uri':
				$value = esc_url_raw( $value );
				break;
			case 'author':
			case 'version':
				$value = strip_tags( $value );
				break;
			case 'premium':
				$value = (bool) $value;
				break;
			case 'supports':
				$supports = explode( ',', strip_tags( $value ) );

				// Lower each string feature.
				$value = array_map( function( $feature ) {
					return strtolower( trim( $feature ) );
				}, $supports );
				break;
		}

		return $value;
	}

	/**
	 * Retrieves the theme data.
	 *
	 * @return array
	 */
	public function to_array() {
		return array_merge( $this->headers(), [
			'active'     => ( $this->get_theme() === coming2live()->get_current_theme() ),
			'screenshot' => $this->get_screenshot(),
		]);
	}

	/**
	 * Retrieves the theme data for JSON serialization.
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->to_array();
	}

	/**
	 * Magic check isset a property.
	 *
	 * @param  string $name Property name.
	 * @return boolean
	 */
	public function __isset( $name ) {
		return ! is_null( $this->get( $name ) );
	}

	/**
	 * Magic getter a property.
	 *
	 * @param  string $name Property name.
	 * @return mixed
	 */
	public function __get( $name ) {
		return $this->get( $name );
	}

	/**
	 * To string this class.
	 *
	 * @return string
	 */
	public function __toString() {
		return (string) $this->get( 'name' );
	}
}
