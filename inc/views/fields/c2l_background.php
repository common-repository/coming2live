<?php
/**
 * Print the field content.
 *
 * @package Coming2Live
 *
 * @var $field, $escaped_value, $object_id, $object_type, $types
 */

// Parse the values.
$value = c2l_parse_background_args( $field->escaped_value() );

$background_types = apply_filters( 'c2l_background_types', [
	'none'       => esc_html__( 'Default', 'coming2live' ),
	'background' => esc_html__( 'Background', 'coming2live' ),
	'gradient'   => esc_html__( 'Gradient', 'coming2live' ),
	'slider'     => esc_html__( 'Image Slider', 'coming2live' ),
	'video'      => esc_html__( 'Video', 'coming2live' ),
	'triangle'   => esc_html__( 'Random triangle', 'coming2live' ),
	'fss'        => esc_html__( 'Flat surface shader', 'coming2live' ),
]);

?><div class="c2l-background-field">
	<div class="c2l-background-types" data-select="true">
		<label for="<?php echo esc_attr( $types->_id( '_type' ) ); ?>" class="screen-reader-text">
			<span><?php esc_html_e( 'Background Type', 'coming2live' ); ?></span>
		</label>

		<?php
		echo $this->clone_field( $field, [ // @WPCS: XSS OK.
			'type'       => 'select',
			'id'         => $types->_id( '_type' ),
			'_name'      => $types->_name( '[type]' ),
			'value'      => $value['type'],
			'options'    => $background_types,
		])->render();
		?>
	</div><!-- /.c2l-background-types -->

	<div class="c2l-background-controls" data-sections="true">
		<div class="c2l-background-section hidden" data-type="none">
			<p><?php echo esc_html__( 'The background will be depend by current theme.', 'coming2live' ); ?></p>
		</div>

		<div class="c2l-background-section hidden" data-type="background">
			<div class="c2l-mb1">
				<label class="c2l-block-label" for="<?php echo esc_attr( $types->_id( '_background_color' ) ); ?>">
					<span><?php esc_html_e( 'Background Color', 'coming2live' ); ?></span>
				</label>

				<?php
				echo $this->clone_field( $field, [ // @WPCS: XSS OK.
					'type'    => 'colorpicker',
					'id'      => $types->_id( '_background_color' ),
					'_name'   => $types->_name( '[background_color]' ),
					'value'   => $value['background_color'],
					'options' => [ 'alpha' => true ],
				])->render();
				?>
			</div>

			<label for="<?php echo esc_attr( $types->_id( '_background_image' ) ); ?>">
				<span><?php esc_html_e( 'Background Image', 'coming2live' ); ?></span>
			</label>

			<div class="c2l-flex">
				<div style="flex: 0 0 320px;">
					<?php
					echo $this->clone_field( $field, [ // @WPCS: XSS OK.
						'type'         => 'file',
						'id'           => $types->_id( '_background_image' ),
						'_name'        => $types->_name( '[background_image]' ),
						'value'        => $value['background_image'],
						'options'      => [ 'url' => false ],
						'query_args'   => [ 'type' => 'image' ],
						'text'         => [ 'add_upload_file_text' => esc_html__( 'Select Image', 'coming2live' ) ],
						'preview_size' => 'medium',
					])->render();
					?>
				</div>

				<div style="padding-top: 16px;">
					<?php
					echo $this->clone_field( $field, [ // @WPCS: XSS OK.
						'type'       => 'c2l_background_attributes',
						'id'         => $types->_id( '_background_image_atts' ),
						'_name'      => $types->_name( '[background_image_atts]' ),
						'value'      => $value['background_image_atts'],
					])->render();
					?>
				</div>
			</div>
		</div>

		<div class="c2l-background-section hidden" data-type="gradient">
			<label for="<?php echo esc_attr( $types->_id( '_background_gradient1' ) ); ?>">
				<span><?php esc_html_e( 'Gradient #1', 'coming2live' ); ?></span>
			</label>

			<div class="c2l-inline-colorpicker">
				<?php
				echo $this->clone_field( $field, [ // @WPCS: XSS OK.
					'type'       => 'colorpicker',
					'id'         => $types->_id( '_background_gradient1' ),
					'_name'      => $types->_name( '[background_gradient1]' ),
					'value'      => $value['background_gradient1'],
				])->render();

				echo $this->clone_field( $field, [ // @WPCS: XSS OK.
					'type'       => 'colorpicker',
					'id'         => $types->_id( '_background_gradient2' ),
					'_name'      => $types->_name( '[background_gradient2]' ),
					'value'      => $value['background_gradient2'],
				])->render();
				?>
			</div>

			<label for="<?php echo esc_attr( $types->_id( '_background_gradient3' ) ); ?>">
				<span><?php esc_html_e( 'Gradient #2', 'coming2live' ); ?></span>
			</label>

			<div class="c2l-inline-colorpicker">
				<?php
				echo $this->clone_field( $field, [ // @WPCS: XSS OK.
					'type'       => 'colorpicker',
					'id'         => $types->_id( '_background_gradient3' ),
					'_name'      => $types->_name( '[background_gradient3]' ),
					'value'      => $value['background_gradient3'],
				])->render();

				echo $this->clone_field( $field, [ // @WPCS: XSS OK.
					'type'       => 'colorpicker',
					'id'         => $types->_id( '_background_gradient4' ),
					'_name'      => $types->_name( '[background_gradient4]' ),
					'value'      => $value['background_gradient4'],
				])->render();
				?>
			</div>
		</div>

		<div class="c2l-background-section hidden" data-type="slider">
			<label for="<?php echo esc_attr( $types->_id( '_background_slider' ) ); ?>">
				<span><?php esc_html_e( 'Images', 'coming2live' ); ?></span>
			</label>

			<?php
			echo $this->clone_field( $field, [ // @WPCS: XSS OK.
				'type'         => 'file_list',
				'id'           => $types->_id( '_background_slider' ),
				'_name'        => $types->_name( '[background_slider]' ),
				'value'        => $value['background_slider'],
				'query_args'   => [ 'type' => 'image' ],
				'text'         => [ 'add_upload_files_text' => esc_html__( 'Select Images', 'coming2live' ) ],
				'preview_size' => 'medium',
			])->render();
			?>
		</div>

		<div class="c2l-background-section hidden" data-type="video">
			<label for="<?php echo esc_attr( $types->_id( '_background_video_link' ) ); ?>">
				<span><?php esc_html_e( 'Enter Video URL', 'coming2live' ); ?></span>
			</label>

			<div class="c2l-social-fields c2l-input-addon c2l-mb1">
				<?php
				echo $this->clone_field( $field, [ // @WPCS: XSS OK.
					'type'       => 'select',
					'id'         => $types->_id( '_background_video_source' ),
					'_name'      => $types->_name( '[background_video_source]' ),
					'value'      => $value['background_video_source'],
					'options'    => [
						'youtube' => esc_html__( 'Youtube', 'coming2live' ),
					],
				])->render();
				?>

				<?php
				echo $this->clone_field( $field, [ // @WPCS: XSS OK.
					'type'       => 'text',
					'id'         => $types->_id( '_background_video_link' ),
					'_name'      => $types->_name( '[background_video_link]' ),
					'value'      => $value['background_video_link'],
				])->render();
				?>
			</div>

			<div class="bg-youtube-filters c2l-mb1">
				<label for="<?php echo esc_attr( $types->_id( '_youtube_filter_blur' ) ); ?>">
					<span><?php esc_html_e( 'Video Blur', 'coming2live' ); ?></span>
				</label>

				<div data-fieldtype="c2l_range">
					<?php
					echo $this->clone_field( $field, [ // @WPCS: XSS OK.
						'type'         => 'c2l_range',
						'id'           => $types->_id( '_youtube_filter_blur' ),
						'_name'        => $types->_name( '[youtube_filter_blur]' ),
						'value'        => $value['youtube_filter_blur'],
						'min'          => 0,
						'max'          => 100,
						'step'         => 5,
					])->render();
					?>
				</div>
			</div>

			<div class="c2l-mb1"></div>
			<label for="<?php echo esc_attr( $types->_id( '_background_video_thumbnail' ) ); ?>">
				<span><?php esc_html_e( 'Video Thumbnail', 'coming2live' ); ?></span>
			</label>

			<?php
			echo $this->clone_field( $field, [ // @WPCS: XSS OK.
				'type'         => 'file',
				'id'           => $types->_id( '_background_video_thumbnail' ),
				'_name'        => $types->_name( '[background_video_thumbnail]' ),
				'value'        => $value['background_video_thumbnail'],
				'options'      => [ 'url' => false ],
				'query_args'   => [ 'type' => 'image' ],
				'text'         => [ 'add_upload_file_text' => esc_html__( 'Select Thumbnail', 'coming2live' ) ],
				'preview_size' => 'medium',
			])->render();
			?>

			<div class="c2l-sweet-note c2l-mt1">
				<p class="c2l-mt0"><?php esc_html_e( 'Video backgrounds doesn\'t work on mobile devices therefore only thumbnail video image will be displayed on mobile devices.', 'coming2live' ); ?></p>
			</div>
		</div>

		<div class="c2l-background-section hidden" data-type="triangle">
			<label for="<?php echo esc_attr( $types->_id( '_background_triangle' ) ); ?>">
				<span><?php esc_html_e( 'Background color', 'coming2live' ); ?></span>
			</label>

			<div class="c2l-inline-colorpicker">
				<?php
					echo $this->clone_field( $field, [
						'type'       => 'colorpicker',
						'id'         => $types->_id( '_background_triangle' ),
						'_name'      => $types->_name( '[background_triangle]' ),
						'value'      => $value['background_triangle'],
					])->render();
				?>
			</div>
			<label for="<?php echo esc_attr( $types->_id( '_triangle_color' ) ); ?>">
				<span><?php esc_html_e( 'Colors', 'coming2live' ); ?></span>
			</label>

			<div class="c2l-inline-colorpicker">

				<?php
				echo $this->clone_field( $field, [
					'type'       => 'colorpicker',
					'id'         => $types->_id( '_triangle_color' ),
					'_name'      => $types->_name( '[triangle_color]' ),
					'value'      => $value['triangle_color'],
					'repeatable' => true,
					'options' => array(
						'alpha' => true,
					),
				])->render();
				?>
			</div>
		</div>

		<div class="c2l-background-section hidden" data-type="fss">
			<label><?php esc_html_e( 'Light', 'coming2live' ); ?></label>
			<div class="c2l-inline-colorpicker">
				<?php
				echo $this->clone_field( $field, [
					'type'       => 'colorpicker',
					'id'         => $types->_id( '_fss_light_ambient' ),
					'_name'      => $types->_name( '[fss_light_ambient]' ),
					'value'      => $value['fss_light_ambient'],
				])->render();

				echo $this->clone_field( $field, [
					'type'       => 'colorpicker',
					'id'         => $types->_id( '_fss_light_diffuse' ),
					'_name'      => $types->_name( '[fss_light_diffuse]' ),
					'value'      => $value['fss_light_diffuse'],
				])->render();
				?>
			</div>

			<label><?php esc_html_e( 'Material', 'coming2live' ); ?></label>
			<div class="c2l-inline-colorpicker">
				<?php
				echo $this->clone_field( $field, [
					'type'       => 'colorpicker',
					'id'         => $types->_id( '_fss_material_ambient' ),
					'_name'      => $types->_name( '[fss_material_ambient]' ),
					'value'      => $value['fss_material_ambient'],
				])->render();

				echo $this->clone_field( $field, [
					'type'       => 'colorpicker',
					'id'         => $types->_id( '_fss_material_diffuse' ),
					'_name'      => $types->_name( '[fss_material_diffuse]' ),
					'value'      => $value['fss_material_diffuse'],
				])->render();
				?>
			</div>
		</div>
	</div><!-- /.c2l-background-controls -->
</div><!-- /.c2l-background-field -->
