<?php
/**
 * Print the field content.
 *
 * @package Coming2Live
 *
 * @var $field, $escaped_value, $object_id, $object_type, $types
 */

?>

<?php if ( $field->prop( 'message' ) ) : ?>
	<span style="color: #898989;"><?php echo wp_kses_post( $field->prop( 'message' ) ); ?></span>
<?php else : ?>
	<span style="color: #898989;"><?php echo esc_html__( 'This feature is not supported by current theme.', 'coming2live' ); ?></span>
<?php endif ?>
