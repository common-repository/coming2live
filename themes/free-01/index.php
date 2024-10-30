<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<?php c2l_head(); ?>
	<style type="text/css">
		<?php if ( c2l_theme_mod( 'enable_overlay' ) ) : ?>
			.hero:after {
				background-color: <?php print c2l_theme_mod( 'overlay_color' );?>;
				opacity: <?php print c2l_theme_mod( 'overlay_opacity' )/100;?>;
			}
		<?php endif; ?>
	</style>
</head>
<body <?php c2l_body_class(); ?>>

	<div class="page-wrap">
		<div class="md-content">
			<div class="hero md-skin-dark">

				<div class="header">
					<div class="header__left">
						<?php if ( $address = c2l_theme_mod( 'address' ) ) : ?>
							<span><?php echo esc_html( $address ); ?></span>
						<?php endif; ?>

						<?php if ( $phone = c2l_theme_mod( 'phone' ) ) : ?>
							<span><?php echo esc_html( $phone ); ?></span>
						<?php endif; ?>

						<?php if ( $email = c2l_theme_mod( 'email' ) ) : ?>
							<span><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></span>
						<?php endif; ?>
					</div>

					<div class="header__social">
						<?php c2l_get_component( 'social', [ 'link_class' => 'social-icon social-icon__rounded' ] ); ?>
					</div>
				</div><!-- /.header -->

				<div class="container">
					<div class="hero__wrapper">
						<div class="row">
							<div class="col-lg-10 col-xs-offset-0 col-sm-offset-0 col-md-offset-0 col-lg-offset-1 ">
								<div class="hero__title_inner">
									<?php if ( ! c2l_theme_mod( 'logo' ) ) : ?>
										<span class="hero__icon">
											<?php
												$bloginfo = get_bloginfo( 'name' );
												echo $bloginfo[0];
											?>
										</span>
									<?php else : ?>
										<?php
											printf( '<img src="%1$s" class="site-logo-img" alt="%2$s">', c2l_theme_mod( 'logo' ), get_bloginfo( 'name' ) );
										?>
									<?php endif; ?>
									<h1 class="hero__title"><?php c2l_body_title(); ?></h1>
									<div class="hero__text"><?php c2l_body_message(); ?></div>
								</div>
							</div>

						</div>

						<?php //c2l_get_component( 'subscribe' ); ?>

						<?php c2l_get_component( 'countdown', [ 'el_class' => 'countdown__module' ] ); ?>

						<div class="service-wrapper">
							<?php if ( $footer_title_1 = c2l_theme_mod( 'footer_title_1' ) ) : ?>
							<!-- service -->
							<div class="service">
									<h2 class="service__title"><?php echo esc_html( $footer_title_1 ); ?></h2>

								<?php if ( $footer_desc_1 = c2l_theme_mod( 'footer_desc_1' ) ) : ?>
									<p class="service__text"><?php print $footer_desc_1; ?></p>
								<?php endif; ?>
							</div><!-- End / service -->
							<?php endif; ?>

							
							<?php if ( $footer_title_2 = c2l_theme_mod( 'footer_title_2' ) ) : ?>
							<!-- service -->
							<div class="service">
									<h2 class="service__title"><?php echo esc_html( $footer_title_2 ); ?></h2>

								<?php if ( $footer_desc_2 = c2l_theme_mod( 'footer_desc_2' ) ) : ?>
									<p class="service__text"><?php print $footer_desc_2; ?></p>
								<?php endif; ?>
							</div><!-- End / service -->
							<?php endif; ?>

							<?php if ( $footer_title_3 = c2l_theme_mod( 'footer_title_3' ) ) : ?>
							<!-- service -->
							<div class="service">
									<h2 class="service__title"><?php echo esc_html( $footer_title_3 ); ?></h2>

								<?php if ( $footer_desc_3 = c2l_theme_mod( 'footer_desc_3' ) ) : ?>
									<p class="service__text"><?php print $footer_desc_3; ?></p>
								<?php endif; ?>
							</div><!-- End / service -->
							<?php endif; ?>

							<?php if ( $footer_title_4 = c2l_theme_mod( 'footer_title_4' ) ) : ?>
							<!-- service -->
							<div class="service">
									<h2 class="service__title"><?php echo esc_html( $footer_title_4 ); ?></h2>

								<?php if ( $footer_desc_4 = c2l_theme_mod( 'footer_desc_4' ) ) : ?>
									<p class="service__text"><?php print $footer_desc_4; ?></p>
								<?php endif; ?>
							</div><!-- End / service -->
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div><!-- End / hero -->

		</div>
		<!-- End / Content-->
	</div>

	<?php c2l_footer(); ?>
</body>
</html>
