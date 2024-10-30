<?php

if ( ! c2l_option( 'display_countdown' ) ) {
	return;
}

?>

<div id="c2l-countdown" class="<?php echo isset( $el_class ) ? esc_attr( $el_class ) : ''; ?>" data-countdown="<?php echo esc_attr( c2l_countdown_datetime()->format( 'Y-m-d H:i:s' ) ); ?>">
	<?php if ( isset( $slot ) ) : ?>

		<?php print $slot; // WPCS: XSS OK. ?>

	<?php else : ?>

		<p><span>%D</span> <?php esc_html_e( 'Days', 'coming2live' ); ?></p>
		<p><span>%H</span> <?php esc_html_e( 'Hours', 'coming2live' ); ?></p>
		<p><span>%M</span> <?php esc_html_e( 'Minutes', 'coming2live' ); ?></p>
		<p><span>%S</span> <?php esc_html_e( 'Seconds', 'coming2live' ); ?></p>

	<?php endif ?>
</div>
