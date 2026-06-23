<?php
/**
 * The template for displaying the footer.
 *
 * @since   1.0.0
 * @package Claue
 */
?>		
			<?php jas_claue_footer(); ?>
		</div><!-- #jas-wrapper -->
		<a id="jas-backtop" class="pf br__50"><span class="tc bgp br__50 db cw"><i class="pr pe-7s-angle-up"></i></span></a>
		
		<?php if ( cs_get_option( 'preloader' ) ) : ?>
			<div class="preloader pf">
				<?php if ( cs_get_option( 'preloader-type' ) == 'css' ) : ?>
					<div class="progress pa">
						<div class="indeterminate"></div>
					</div>
				<?php elseif ( cs_get_option( 'preloader-img' ) ) : ?>
					<?php $img = wp_get_attachment_image_src( cs_get_option( 'preloader-img' ), 'full', true ); ?>

					<img class="pr" src="<?php echo esc_url( $img[0] ); ?>" width="<?php echo esc_attr( $img[1] ); ?>" height="<?php echo esc_attr( $img[2] ); ?>" alt="<?php get_bloginfo( 'name' ); ?>" />
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php wp_footer(); ?>

<div class="social-icn" style="position:absolute; top:20px; right:200px; width:auto; z-index:9999999;">
	<a href="https://www.linkedin.com/company/trufrostbutler/" target="_blank"><i class="fa-brands fa-linkedin" aria-hidden="true"></i></a> &nbsp; &nbsp; <a href="https://www.facebook.com/@trufrostbutler" target="_blank"><i class="fa-brands fa-facebook" aria-hidden="true"></i></a> &nbsp; &nbsp; <a href="https://www.instagram.com/trufrostbutler/" target="_blank"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a> &nbsp; &nbsp; <a href="https://www.youtube.com/@trufrost-butler" target="_blank"><i class="fa-brands fa-youtube-play" aria-hidden="true"></i></a>
</div>

<div class="call-stic"><a href="tel:+917303166766" style="text-decoration:none; color:#fff;">Customer Care: +91-7303166766</a></div>
	</body>
</html>