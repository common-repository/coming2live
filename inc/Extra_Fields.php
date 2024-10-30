<?php
namespace Coming2Live;

use CMB2_Types;

class Extra_Fields {
	/**
	 * Init the fields.
	 *
	 * @return void
	 */
	public function init() {
		$this->register( 'c2l_range' );
		$this->register( 'c2l_toggle' );
		$this->register( 'c2l_themes' );
		$this->register( 'c2l_fallback' );
		$this->register( 'c2l_social', [ $this, 'sanitize_social' ], true );
		$this->register( 'c2l_background', [ $this, 'sanitize_background' ], true );
		$this->register( 'c2l_background_effects' );
		$this->register( 'c2l_background_attributes' );
	}

	/**
	 * Add new field type to the CMB2.
	 *
	 * @param string   $field_type        The field type.
	 * @param callable $sanitize_callback The sanitize_callback.
	 * @param boolean  $recursive_escape  Is tis field need escape recursive.
	 */
	public function register( $field_type, $sanitize_callback = null, $recursive_escape = false ) {
		/**
		 * Rendering the field.
		 *
		 * @param array      $field         The passed in `CMB2_Field` object.
		 * @param mixed      $escaped_value The value of this field escaped.
		 * @param int        $object_id     The ID of the current object.
		 * @param string     $object_type   The type of object you are working with.
		 * @param CMB2_Types $types         The `CMB2_Types` object.
		 */
		$render_callback = function( $field, $escaped_value, $object_id, $object_type, $types ) use ( $field_type ) {
			include trailingslashit( __DIR__ ) . "views/fields/{$field_type}.php";
		};

		// Register render action for this field type.
		add_action( "cmb2_render_{$field_type}", $render_callback, 10, 5 );

		// Add fillter to sanitize if provided.
		if ( ! is_null( $sanitize_callback ) ) {
			add_filter( "cmb2_sanitize_{$field_type}", $sanitize_callback, 10, 5 );
		}

		// Need recursive escape?
		if ( $recursive_escape ) {
			/**
			 * Escape recursive the field value.
			 *
			 * @param  mixed      $check      The check variable.
			 * @param  mixed      $meta_value The meta_value.
			 * @param  array      $field_args The current field's arguments.
			 * @param  CMB2_Field $field      The `CMB2_Field` object.
			 * @return mixed
			 */
			$recursive_escape_callback = function( $check, $meta_value, $field_args, $field ) {
				return c2l_recursive_sanitizer( $field->val_or_default( $meta_value ), 'esc_attr' );
			};

			add_filter( "cmb2_types_esc_{$field_type}", $recursive_escape_callback, 10, 4 );
		}
	}

	/**
	 * Helper to clone a field_types based on a field.
	 *
	 * @param  CMB2_Field $field The field object.
	 * @param  array      $args  The clone field args.
	 * @return CMB2_Types
	 */
	public function clone_field( $field, $args = [] ) {
		$clone = $field->get_field_clone( $args );

		// Set field the value.
		if ( isset( $args['value'] ) ) {
			$clone->value = $args['value'];
			$clone->escaped_value = null;
		}

		// Overwrite the _name.
		if ( isset( $args['_name'] ) ) {
			$clone->set_prop( '_name', $args['_name'] );
		}

		return new CMB2_Types( $clone );
	}

	/**
	 * Filter the value before it is saved.
	 *
	 * @param bool|mixed    $check      The check variable.
	 * @param mixed         $value      The value to be saved to this field.
	 * @param int           $object_id  The ID of the object where the value will be saved.
	 * @param array         $field_args The current field's arguments.
	 * @param CMB2_Sanitize $sanitizer  The `CMB2_Sanitize` object.
	 */
	public function sanitize_background( $check, $value, $object_id, $field_args, $sanitizer ) {
		return c2l_sanitize_background( $value );
	}

	/**
	 * Filter the value before it is saved.
	 *
	 * @param bool|mixed    $check      The check variable.
	 * @param mixed         $value      The value to be saved to this field.
	 * @param int           $object_id  The ID of the object where the value will be saved.
	 * @param array         $field_args The current field's arguments.
	 * @param CMB2_Sanitize $sanitizer  The `CMB2_Sanitize` object.
	 */
	public function sanitize_social( $check, $value, $object_id, $field_args, $sanitizer ) {
		// If not repeatable, sanitize value only.
		if ( ! $field_args['repeatable'] ) {
			return c2l_sanitize_social( $value );
		}

		// Sanitize repeatable values.
		foreach ( (array) $value as $key => $val ) {
			$value[ $key ] = c2l_sanitize_social( $val );
		}

		return array_filter( $value );
	}
}
