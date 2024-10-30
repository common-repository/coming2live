<?php

if ( ! c2l_option( 'display_social' ) ) {
	return;
}

// Get the social links.
$social_links = c2l_option( 'social_links', [] );
$social_links = array_filter( array_map( 'c2l_sanitize_social', $social_links ) );

// Leave if not have any links.
if ( empty( $social_links ) ) {
	return;
}

// Enqueue svg-icons if requested.
$svgicon = ( ! isset( $svgicon ) || ( isset( $svgicon ) && true === $svgicon ) );
if ( $svgicon ) {
	c2l_enqueue_svg_icons();
}

// The social providers.
$providers = c2l_social_providers();

?><nav id="c2l-social" class="<?php echo isset( $el_class ) ? esc_attr( $el_class ) : ''; ?>">
	<?php foreach ( $social_links as $social ) : ?>
		<a href="<?php echo esc_url( $social['link'] ); ?>" class="<?php echo esc_attr( isset( $link_class ) ? $link_class : '' ); ?>" title="<?php echo isset( $providers[ $social['name'] ] ) ? esc_html( $providers[ $social['name'] ] ) : ''; ?>">

			<?php if ( $svgicon ) : ?>
				<?php c2l_svg_icon( $social['name'] ); ?>
			<?php else : ?>
				<span class="<?php echo esc_attr( ( isset( $prefix ) ? $prefix : 'fa fa-' ) . $social['name'] ); ?>" aria-hidden="true"></span>
			<?php endif ?>

		</a>
	<?php endforeach; ?>
</nav><!-- /#c2l-social -->
