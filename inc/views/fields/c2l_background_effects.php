<?php
/**
 * Print the field content.
 *
 * @package Coming2Live
 *
 * @var $field, $escaped_value, $object_id, $object_type, $types
 */

// Parse the value.
$value = wp_parse_args( $field->escaped_value(), [
	'effect'            => 'none',
	'particles_effect'  => 'default', // [default, snow, star, bubble, nasa]
]);

$effects_options = [
	'none'            => esc_html__( 'None', 'coming2live' ),
	'particles'       => esc_html__( 'Particles', 'coming2live' ),
	// 'ribbon'          => esc_html__( 'Ribbon', 'coming2live' ),
	// 'smoky'           => esc_html__( 'Smoky', 'coming2live' ),
	// 'gradient'        => esc_html__( 'Image with Gradient', 'coming2live' ),
];

?><div class="c2l-background-effect-field">
	<div class="c2l-background-types" data-select="true">
		<label for="<?php echo esc_attr( $types->_id( '_effect' ) ); ?>" class="screen-reader-text">
			<span><?php esc_html_e( 'Background Effect', 'coming2live' ); ?></span>
		</label>

		<?php
		echo $this->clone_field( $field, [ // @WPCS: XSS OK.
			'type'    => 'select',
			'id'      => $types->_id( '_effect' ),
			'_name'   => $types->_name( '[effect]' ),
			'value'   => $value['effect'],
			'options' => $effects_options,
		])->render();
		?>
	</div>

	<div class="c2l-background-controls" data-sections="true">

		<div class="c2l-background-section hidden" data-type="particles">
			<label for="<?php echo esc_attr( $types->_id( '_particles_effect' ) ); ?>" class="c2l-block-label">
				<span><?php esc_html_e( 'Select Style', 'coming2live' ); ?></span>
			</label>

			<?php
			echo $this->clone_field( $field, [ // @WPCS: XSS OK.
				'type'         => 'radio',
				'id'           => $types->_id( '_particles_effect' ),
				'_name'        => $types->_name( '[particles_effect]' ),
				'value'        => $value['particles_effect'],
				'options'      => wp_list_pluck( c2l_particles_effects(), 'name' ),
			])->render();
			?>
		</div>

	</div><!-- /.c2l-background-controls -->
</div><!-- /.c2l-background-field -->
