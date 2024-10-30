<?php
/**
 * Print the field content.
 *
 * @package Coming2Live
 *
 * @var $field, $escaped_value, $object_id, $object_type, $types
 */

$value = wp_parse_args( $field->escaped_value(), [
	'name' => '',
	'link' => '',
]);

?>

<div class="c2l-social-fields c2l-input-addon">
	<?php
	echo $this->clone_field( $field, [ // @WPCS: XSS OK.
		'type'        => 'select',
		'id'          => $types->_id( '_name' ),
		'_name'       => $types->_name( '[name]' ),
		'value'       => $value['name'],
		'options'     => c2l_social_providers(),
		'repeatable'  => false,
	])->render();

	echo $this->clone_field( $field, [ // @WPCS: XSS OK.
		'type'        => 'text',
		'id'          => $types->_id( '_link' ),
		'_name'       => $types->_name( '[link]' ),
		'value'       => $value['link'],
		'repeatable'  => false,
	])->render();
	?>
</div>
