<?php
/**
 * Print the field content.
 *
 * @package Coming2Live
 *
 * @var $field, $escaped_value, $object_id, $object_type, $types
 */

$plugin = coming2live()->get_instance();

$themes = $plugin->get_themes();

wp_enqueue_script( 'wp-util' );
?>

<div class="theme-browser rendered">
	<div class="themes wp-clearfix">

		<?php foreach ( $themes as $name => $theme ) : ?>
			<?php $is_active = ( $plugin->get_current_theme() === $name ); ?>

			<div class="theme <?php echo ( $is_active ? 'active' : '' ); ?>" tabindex="0" data-slug="<?php echo esc_attr( $name ); ?>">
				<div class="theme-screenshot">
					<?php if ( $screenshot = $theme->get_screenshot() ) : ?>
						<img src="<?php echo esc_url( $screenshot ); ?>" alt="screenshot">
					<?php endif ?>
				</div>

				<span class="more-details"><?php echo esc_html__( 'Details', 'coming2live' ); ?></span>

				<div class="theme-id-container">
					<h2 class="theme-name">
						<?php if ( $is_active ) : ?>
							<span><?php echo esc_html__( 'Active:', 'coming2live' ); ?></span>
						<?php endif ?>

						<?php echo esc_html( $theme->get_name() ); ?>
					</h2>

					<div class="theme-actions">
						<?php if ( $is_active ) : ?>
							<a class="button button-primary" href="<?php echo esc_url( admin_url( 'tools.php?page=c2l-settings&section=theme' ) ); ?>"><?php echo esc_html__( 'Setting', 'coming2live' ); ?></a>
						<?php else : ?>
							<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( "tools.php?action=c2l_activate&theme=${name}" ), 'activate_' . $name ) ); ?>"><?php echo esc_html__( 'Active', 'coming2live' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endforeach ?>

	</div><!-- /.themes -->
</div><!-- /.theme-browser -->

<!-- JS theme overlay -->
<div class="theme-overlay"></div>

<script id="tmpl-theme-single" type="text/template">
	<div class="theme-backdrop"></div>
	<div class="theme-wrap wp-clearfix" role="document">

		<div class="theme-header">
			<button class="close dashicons dashicons-no"><span class="screen-reader-text"><?php esc_html_e( 'Close details dialog', 'coming2live' ); ?></span></button>
		</div>

		<div class="theme-about wp-clearfix">
			<div class="theme-screenshots">
			<# if ( data.screenshot ) { #>
				<div class="screenshot"><img src="{{ data.screenshot }}" alt="" /></div>
			<# } else { #>
				<div class="screenshot blank"></div>
			<# } #>
			</div>

			<div class="theme-info">
				<# if ( data.active ) { #>
					<span class="current-label"><?php esc_html_e( 'Current Theme', 'coming2live' ); ?></span>
				<# } #>

				<h2 class="theme-name">
					<span>{{{ data.name }}}</span>
					<span class="theme-version"><?php printf( esc_html__( 'Version: %s', 'coming2live' ), '{{ data.version }}' ); // @codingStandardsIgnoreLine ?></span>
				</h2>

				<p class="theme-author"><?php printf( esc_html__( 'By %s', 'coming2live' ), '{{{ data.author }}}' ); // @codingStandardsIgnoreLine ?></p>
				<p class="theme-description">{{{ data.description }}}</p>

				<# if ( data.supports ) { #>
					<p class="theme-tags"><span><?php esc_html_e( 'Supports:', 'coming2live' ); ?></span> {{{ data.supports.join(', ') }}}</p>
				<# } #>
			</div>
		</div>
	</div>
</script>

<script type="text/javascript">
var _c2lThemes = <?php echo json_encode( $themes ); ?>;
</script>

<script type="text/javascript">
(function($) {
	'use strict';

	/**
	 * Theme Details.
	 *
	 * @param {string} theme The theme name.
	 */
	function ThemeDetails(theme) {
		this.theme = theme;
		this.$el   = $('.theme-overlay');

		// Perform render.
		this.render();

		// Binding events.
		this.$el.on('click', this.collapse.bind(this));
		$('body').on('keyup', this.collapse.bind(this));
	};

	ThemeDetails.prototype = {
		/**
		 * Render the overlay.
		 *
		 * @return {void}
		 */
		render: function() {
			$('body').addClass('modal-open');
			this.$el.show();

			var data = _c2lThemes[this.theme];
			var template = wp.template('theme-single');

			this.$el.html(template(data));
		},

		/**
		 * Handle collapse overlay.
		 *
		 * @param  {Event} e The event.
		 * @return {void}
		 */
		collapse: function(e) {
			var self = this;
			var e = e || window.e;

			if ($(e.target).is('.theme-backdrop') || $(e.target).is('.close') || e.keyCode === 27) {
				e.preventDefault();

				// Add a temporary closing class while overlay fades out.
				$('body').addClass('closing-overlay');

				// With a quick fade out animation
				this.$el.fadeOut(130, function() {
					$('body').removeClass('closing-overlay');

					// Handle event cleanup.
					self.$el.off();

					$('body').removeClass('modal-open');
				});
			}
		},
	};

	$(function() {
		$('.theme .theme-screenshot, .theme .more-details').on('click', function() {
			new ThemeDetails( $(this).closest('.theme').data('slug') );
		});
	});

})(jQuery);
</script>
