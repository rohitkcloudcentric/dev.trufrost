<?php
/**
 * Admin Class
 *
 * Handles the Admin side functionality of plugin
 *
 * @package WP Logo Showcase Responsive Slider and Carousel
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>

<div id="wpls_welcome_tabs" class="wpls-vtab-cnt wpls_welcome_tabs wpls-clearfix">
	
	<!-- <div class="wpls-black-friday-banner-wrp">
		<a href="<?php // echo esc_url( WPLS_PLUGIN_BUNDLE_LINK ); ?>" target="_blank"><img style="width: 100%" src="<?php // echo esc_url( WPLS_URL ); ?>assets/images/black-friday-banner.png" alt="black-friday-banner" /></a>
	</div> -->

	<div class="wpls-black-friday-banner-wrp" style="background:#e1ecc8;padding: 20px 20px 40px; border-radius:5px; text-align:center;margin-bottom: 40px;">
		<h2 style="font-size:30px; margin-bottom:10px;"><span style="color:#0055fb;">Logo Showcase</span> is included in <span style="color:#0055fb;">Essential Plugin Bundle</span> </h2> 
		<h4 style="font-size: 18px;margin-top: 0px;color: #ff5d52;margin-bottom: 24px;">Now get Designs, Optimization, Security, Backup, Migration Solutions @ one stop. </h4>

		<div class="wpls-black-friday-feature">

			<div class="wpls-inner-deal-class" style="width:40%;">
				<div class="wpls-inner-Bonus-class">Bonus</div>
				<div class="wpls-image-logo" style="font-weight: bold;font-size: 26px;color: #222;"><img style="width: 34px; height:34px;vertical-align: middle;margin-right: 5px;" class="wpls-img-logo" src="<?php echo esc_url( WPLS_URL ); ?>assets/images/essential-logo-small.png" alt="essential-logo" /><span class="wpls-esstial-name" style="color:#0055fb;">Essential </span>Plugin</div>
				<div class="wpls-sub-heading" style="font-size: 16px;text-align: left;font-weight: bold;color: #222;margin-bottom: 10px;">Includes All premium plugins at no extra cost.</div>
				<a class="wpls-sf-btn" href="<?php echo esc_url( WPLS_PLUGIN_BUNDLE_LINK ); ?>" target="_blank">Grab The Deal</a>
			</div>

			<div class="wpls-main-list-class" style="width:60%;">
				<div class="wpls-inner-list-class">
					<div class="wpls-list-img-class"><img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/img-slider.png" alt="essential-logo" /> Image Slider</li></div>

					<div class="wpls-list-img-class"><img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/advertising.png" alt="essential-logo" /> Publication</li></div>

					<div class="wpls-list-img-class"><img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/marketing.png" alt="essential-logo" /> Marketing</li></div>

					<div class="wpls-list-img-class"><img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/photo-album.png" alt="essential-logo" /> Photo album</li></div>

					<div class="wpls-list-img-class"><img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/showcase.png" alt="essential-logo" /> Showcase</li></div>

					<div class="wpls-list-img-class"><img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/shopping-bag.png" alt="essential-logo" /> WooCommerce</li></div>

					<div class="wpls-list-img-class"><img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/performance.png" alt="essential-logo" /> Performance</li></div>

					<div class="wpls-list-img-class"><img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/security.png" alt="essential-logo" /> Security</li></div>

					<div class="wpls-list-img-class"><img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/forms.png" alt="essential-logo" /> Pro Forms</li></div>

					<div class="wpls-list-img-class"><img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/seo.png" alt="essential-logo" /> SEO</li></div>

					<div class="wpls-list-img-class"><img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/backup.png" alt="essential-logo" /> Backups</li></div>

					<div class="wpls-list-img-class"><img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/White-labeling.png" alt="essential-logo" /> Migration</li></div>
				</div>
			</div>
		</div>
		<div class="wpls-main-feature-item">
			<div class="wpls-inner-feature-item">
				<div class="wpls-list-feature-item">
					<img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/layers.png" alt="layer" />
					<h5>Site management</h5>
					<p>Manage, update, secure & optimize unlimited sites.</p>
				</div>
				<div class="wpls-list-feature-item">
					<img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/risk.png" alt="backup" />
					<h5>Backup storage</h5>
					<p>Secure sites with auto backups and easy restore.</p>
				</div>
				<div class="wpls-list-feature-item">
					<img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/logo-image/support.png" alt="support" />
					<h5>Support</h5>
					<p>Get answers on everything WordPress at anytime.</p>
				</div>
			</div>
		</div>
		<a class="wpls-sf-btn" href="<?php echo esc_url( WPLS_PLUGIN_BUNDLE_LINK ); ?>" target="_blank">Grab The Deal</a>
	</div>

	<!-- <div class="wpls-deal-offer-wrap">
		<h3 style="font-weight: bold; font-size: 30px; color:#ffef00; text-align:center; margin: 15px 0 5px 0;">Why Invest Time On Free Version?</h3>

		<h3 style="font-size: 18px; text-align:center; margin:0; color:#fff;">Explore WP Blog and Widgets Pro with Essential Bundle Free for 5 Days.</h3>			

		<div class="wpls-deal-free-offer">
			<a href="<?php //echo esc_url( WPLS_PLUGIN_BUNDLE_LINK ); ?>" target="_blank" class="wpls-sf-free-btn"><span class="dashicons dashicons-cart"></span> Try Pro For 5 Days Free</a>
		</div>
	</div> -->

	<!-- Start - Industry Wise Solutions -->
	<div class="wpls-sf-solutions-section wpls-sf-top-rsn wpls-sf-left">
		<h1 class="wpls-sf-heading">Top 4 Reasons Why People Love <span class="wpls-sf-blue">Logo Showcase </span>Including in <span class="wpls-sf-blue">Essential Plugin Bundle</span></h1>
		<div class="wpls-sf-cont wpls-sf-center">Here's why business owners <span class="wpls-sf-blue">love Logo Showcase</span>, and you will too!</div>
		<div class="wpls-sf-solutions-section-inr">
			<div class="wpls-solutions-box-wrap">
				<div class="wpls-sf-solutions-box-grid">
					<img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/model-popup.png" alt="model-popup" />
				</div>
				<div class="wpls-sf-solutions-box-grid">
					<div class="wpls-sf-feature__text">
						<h3>Display as many logos as you want</h3>
						<p>Whether you have <span class="wpls-sf-blue">2 or 20 logos</span>, you can display them all with a logo slider, logo carousel or logo grid since space isn’t a concern. </p>
					</div>
				</div>
			</div>
			<div class="wpls-solutions-box-wrap">
				<div class="wpls-sf-solutions-box-grid">
					<div class="wpls-sf-feature__text">
						<h3>Center Mode</h3>
						<p>Our <span class="wpls-sf-blue">center mode</span> will help you to highlight the active logo in the center with a beautiful design.</p>
					</div>
				</div>
				<div class="wpls-sf-solutions-box-grid">
					<img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/center-mode.png" alt="center-mode" />
				</div>
			</div>
			<div class="wpls-solutions-box-wrap">
				<div class="wpls-sf-solutions-box-grid">
					<img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/drawing-customers-attention.png" alt="drawing-customers-attention" />
				</div>
				<div class="wpls-sf-solutions-box-grid">
					<div class="wpls-sf-feature__text">
						<h3>Drawing Customers attention</h3>
						<p><span class="wpls-sf-blue">Scrolling images or brand slider </span>are an extremely effective and powerful way to draw the customers and to publicize the brands that are supported by you.</p>
					</div>
				</div>
			</div>
			<div class="wpls-solutions-box-wrap">
				<div class="wpls-sf-solutions-box-grid">
					<div class="wpls-sf-feature__text">
						<h3>Easy to represent</h3>
						<p>It is easy to represent all the details of the <span class="wpls-sf-blue">brands and logo images</span> towards the bottom of the website. Without much efforts the visitors can have a look on all the<span class="wpls-sf-blue"> brand images</span> that is supported by your website. Thus it makes the work quite simpler.</p>
					</div>
				</div>
				<div class="wpls-sf-solutions-box-grid">
					<img src="<?php echo esc_url( WPLS_URL ); ?>assets/images/easy-to-represent.png" alt="easy-to-represent" />
				</div>
			</div>
		</div>
		<div style="margin-top: 15px; text-transform: uppercase; text-align:center;">
			<a href="<?php echo esc_url( $wpls_add_link ); ?>" class="wpls-sf-btn">Launch Logoshowcase With Free Features</a>
		</div>
	</div>
	<!-- End - Industry Wise Solutions -->
</div>