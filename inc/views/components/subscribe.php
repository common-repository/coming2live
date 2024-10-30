<?php

if ( ! c2l_option( 'display_subscribe' ) ) {
	return;
}

$shortcode = c2l_option( 'subscribe_shortcode' );
if ( empty( $shortcode ) ) {
	return;
}

// Doing parse the shortcode.
$the_shortcode = do_shortcode( $shortcode );
$the_shortcode = str_replace( [ '<p>', '</p>' ], '', trim( $the_shortcode ) );

?>

<div id="c2l-subscribe-shortcode" class="<?php echo isset( $el_class ) ? esc_attr( $el_class ) : ''; ?>">
	<div class="title">
		<h2 class="title__title"><?php echo c2l_option( 'subscribe_title' ); ?></h2>
		<div class="title__text"><?php echo c2l_option( 'subscribe_message' ); ?></div>
	</div>

	<?php print $the_shortcode; // @codingStandardsIgnoreLine ?>
</div><!-- /.c2l-subscribe-shortcode -->
