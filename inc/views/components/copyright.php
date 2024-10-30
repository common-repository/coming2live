<?php

$copyright = get_option( 'copyright' );
if ( empty( $copyright ) ) {
	return;
}

?>

<div id="c2l-copyright" class="<?php echo isset( $el_class ) ? esc_attr( $el_class ) : ''; ?>">
	<?php echo wp_kses_post( $copyright ); ?>
</div>
