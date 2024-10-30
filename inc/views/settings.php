<style type="text/css">
	.cmb-row.cmb2-id-redirect-url:not(.show),
	.cmb-row.cmb2-id-whitelist-pages:not(.show),
	.cmb-row.cmb2-id-subscribe-shortcode:not(.show),
	.cmb-row.cmb2-id-social-links:not(.show),
	.cmb-row.cmb2-id-countdown-action:not(.show),
	.cmb-row.cmb2-id-countdown-datetime:not(.show) {
		display: none;
	}
</style>

<div class="wrap cmb2-options-page c2l-options-page">
	<h1 class="wp-heading-inline screen-reader-text"><?php echo esc_html__( 'Settings', 'coming2live' ); ?></h1>
	<hr class="wp-header-end">

	<?php if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) : ?>
		<div class="notice updated is-dismissible"><p><?php echo esc_html__( 'Settings updated.', 'coming2live' ); ?></p></div>
	<?php endif; ?>

	<h2 class="c2l-nav-tabs nav-tab-wrapper">
		<?php foreach ( $sections as $key => $section ) : ?>
			<a class="nav-tab <?php echo ( $key === $current ) ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'section', $key, $current_url ) ); ?>"><?php echo wp_kses_post( $section->prop( 'title' ) ); ?></a>
		<?php endforeach; ?>

		<?php /*if ( ! apply_filters( 'c2l_remove_premium_ads', false ) ) : ?>
			<a class="nav-tab cl2-premium-nav" href="<?php echo esc_url( 'http://awethemes.com' ); ?>" target="_blank"><span class="dashicons dashicons-star-filled"></span> <?php esc_html_e( 'Upgrade Premium', 'coming2live' ); ?></a>
		<?php endif */ ?>

		<a class="c2l-nav-preview" href="<?php echo esc_url( add_query_arg( 'c2l-preview', 1, get_home_url() ) ); ?>" target="_blank"><span class="dashicons dashicons-external"></span> <?php esc_html_e( 'Preview', 'coming2live' ); ?></a>
	</h2>

	<div class="c2l-setting-container">
		<main class="c2l-setting-main">
			<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" enctype="multipart/form-data" encoding="multipart/form-data">
				<input type="hidden" name="action" value="c2l_save_setting">
				<input type="hidden" name="cmb2_section" value="<?php echo esc_attr( $current ); ?>">

				<?php $cmb2->show_form( 0, 'options-page' ); ?>

				<?php submit_button( esc_html__( 'Save Changes', 'coming2live' ), 'primary', 'submit-cmb' ); ?>
			</form>
		</main>

		<aside class="c2l-setting-aside">
			<?php include trailingslashit( __DIR__ ) . 'aside.php'; ?>
		</aside>
	</div><!-- /.c2l-setting-container -->

</div><!-- /.wrap -->
