<?php
/**
 * Print the field content.
 *
 * @package Coming2Live
 *
 * @var $field, $escaped_value, $object_id, $object_type, $types
 */

// Parse the background args.
$value = c2l_parse_background_atts_args( $field->escaped_value() );

$size_options = [
	'auto'    => esc_html__( 'Original', 'coming2live' ),
	'contain' => esc_html__( 'Fit to Screen', 'coming2live' ),
	'cover'   => esc_html__( 'Fill Screen', 'coming2live' ),
];

$position_options = [ // @codingStandardsIgnoreStart
	[
		'left top'      => [ 'label' => esc_html__( 'Top Left', 'coming2live' ),    'icon' => 'dashicons dashicons-arrow-left-alt' ],
		'center top'    => [ 'label' => esc_html__( 'Top', 'coming2live' ),         'icon' => 'dashicons dashicons-arrow-up-alt' ],
		'right top'     => [ 'label' => esc_html__( 'Top Right', 'coming2live' ),   'icon' => 'dashicons dashicons-arrow-right-alt' ],
	],
	[
		'left center'   => [ 'label' => esc_html__( 'Left', 'coming2live' ),         'icon' => 'dashicons dashicons-arrow-left-alt' ],
		'center center' => [ 'label' => esc_html__( 'Center', 'coming2live' ),       'icon' => 'background-position-center-icon' ],
		'right center'  => [ 'label' => esc_html__( 'Right', 'coming2live' ),        'icon' => 'dashicons dashicons-arrow-right-alt' ],
	],
	[
		'left bottom'   => [ 'label' => esc_html__( 'Bottom Left', 'coming2live' ),  'icon' => 'dashicons dashicons-arrow-left-alt' ],
		'center bottom' => [ 'label' => esc_html__( 'Bottom', 'coming2live' ),       'icon' => 'dashicons dashicons-arrow-down-alt' ],
		'right bottom'  => [ 'label' => esc_html__( 'Bottom Right', 'coming2live' ), 'icon' => 'dashicons dashicons-arrow-right-alt' ],
	],
]; // @codingStandardsIgnoreEnd

?>

<div class="c2l-flex c2l-bg-attributes">
	<div class="c2l-bgatts-column1">
		<label><?php echo esc_html__( 'Image Position', 'coming2live' ); ?></label>

		<div class="background-position-control">
			<?php foreach ( $position_options as $group ) : ?>
				<div class="button-group" style="display: block;">
				<?php foreach ( $group as $key => $input ) : ?>
					<label>
						<input type="radio" name="<?php echo esc_attr( $types->_name( '[background_position]' ) ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $value['background_position'], $key ); ?> class="screen-reader-text">
						<span class="button display-options position"><span class="<?php echo esc_attr( $input['icon'] ); ?>" aria-hidden="true"></span></span>
						<span class="screen-reader-text"><?php echo esc_html( $input['label'] ); ?></span>
					</label>
				<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="c2l-bgatts-column2">
		<label><?php echo esc_html__( 'Image Size', 'coming2live' ); ?></label>

		<?php
		echo $this->clone_field( $field, [ // @WPCS: XSS OK.
			'type'       => 'radio',
			'id'         => $types->_id( '_background_size' ),
			'_name'      => $types->_name( '[background_size]' ),
			'value'      => $value['background_size'],
			'options'    => $size_options,
		])->render();
		?>

		<p>
			<?php
			echo $this->clone_field( $field, [ // @WPCS: XSS OK.
				'type'        => 'checkbox',
				'id'          => $types->_id( '_background_repeat' ),
				'_name'       => $types->_name( '[background_repeat]' ),
				'description' => esc_html__( 'Repeat Background Image', 'coming2live' ),
			])->checkbox(
				[ 'value' => 'repeat' ],
				( 'repeat' === $value['background_repeat'] )
			);
			?>
		</p>

		<p>
			<?php
			echo $this->clone_field( $field, [ // @WPCS: XSS OK.
				'type'        => 'checkbox',
				'id'          => $types->_id( '_background_attachment' ),
				'_name'       => $types->_name( '[background_attachment]' ),
				'description' => esc_html__( 'Scroll with Page', 'coming2live' ),
			])
			->checkbox(
				[ 'value' => 'scroll' ],
				( 'scroll' === $value['background_attachment'] )
			);
			?>
		</p>
	</div>
</div><!-- /.c2l-bg-attributes -->
