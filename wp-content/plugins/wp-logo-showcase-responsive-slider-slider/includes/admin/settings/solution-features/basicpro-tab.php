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
<div id="wpls_basic_tabs" class="wpls-vtab-cnt wpls_basic_tabs wpls-clearfix">

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
		<div class="wpls-deal-offer"> 
			<div class="wpls-inn-deal-offer">
				<h3 class="wpls-inn-deal-hedding"><span>Buy Logo Showcase Pro</span> today and unlock all the powerful features.</h3>
				<h4 class="wpls-inn-deal-sub-hedding"><span style="color:red;">Extra Bonus: </span>Users will get <span>extra best discount</span> on the regular price using this coupon code.</h4>
			</div>
			<div class="wpls-inn-deal-offer-btn">
				<div class="wpls-inn-deal-code"><span>EPSEXTRA</span></div>
				<a href="<?php // echo esc_url(WPLS_PLUGIN_BUNDLE_LINK); ?>"  target="_blank" class="wpls-sf-btn wpls-sf-btn-orange"><span class="dashicons dashicons-cart"></span> Get Essential Bundle Now</a>
				<em class="risk-free-guarantee"><span class="heading">Risk-Free Guarantee </span> - We offer a <span>30-day money back guarantee on all purchases</span>. If you are not happy with your purchases, we will refund your purchase. No questions asked!</em>
			</div>
		</div>
	</div> -->

	<!-- <div class="wpls-deal-offer-wrap">
		<div class="wpls-deal-offer"> 
			<div class="wpls-inn-deal-offer">
				<h3 class="wpls-inn-deal-hedding"><span>Try WP Logo Showcase Responsive Slider and Carousel Pro</span> in Essential Bundle Free For 5 Days.</h3>
			</div>
			<div class="wpls-deal-free-offer">
				<a href="<?php // echo esc_url( WPLS_PLUGIN_BUNDLE_LINK ); ?>" target="_blank" class="wpls-sf-free-btn"><span class="dashicons dashicons-cart"></span> Try Pro For 5 Days Free</a>
			</div>
		</div>
	</div> -->

	<!-- <div class="wpls-black-friday-banner-wrp">
		<a href="<?php // echo esc_url( WPLS_PLUGIN_BUNDLE_LINK ); ?>" target="_blank"><img style="width: 100%" src="<?php // echo esc_url( WPLS_URL ); ?>assets/images/black-friday-banner.png" alt="black-friday-banner" /></a>
	</div> -->

	<h3 class="wpls-basic-heading">Compare <span class="wpls-blue">"Logo Showcase Responsive Slider"</span> Basic VS Pro</h3>

	<table class="wpos-plugin-pricing-table">
		<colgroup></colgroup>
		<colgroup></colgroup>
		<colgroup></colgroup>
		<thead>
			<tr>
				<th></th>
				<th>
					<h2><?php esc_html_e('Free', 'wp-logo-showcase-responsive-slider-slider'); ?></h2>
				</th>
				<th>
					<h2 class="wpos-epb" style="margin-bottom: 10px;"><?php esc_html_e('Premium', 'wp-logo-showcase-responsive-slider-slider'); ?></h2>
				</th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<th><?php esc_html_e('Designs', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Designs that make your website better', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td>1</td>
				<td>15+</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Shortcodes', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Shortcode provide output to the front-end side', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td>2 (Slider, Center)</td>
				<td>6 (Grid,  Slider, Center, List, Ticker and Filter )</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Shortcode Parameters', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Add extra power to the shortcode', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td>20</td>
				<td>30+</td>
			</tr>
			<tr>
				<th><?php esc_html_e('WP Templating Features', 'wp-logo-showcase-responsive-slider-slider'); ?><span class="subtext"><?php esc_html_e('You can modify plugin html/designs in your current theme.', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Center Mode', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Display slider with center mode.', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Shortcode Generator', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Play with all shortcode parameters with preview panel. No documentation required!!', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Option Show/Hide Title', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Display logo title', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr> 
			<tr>
				<th><?php esc_html_e('Tooltip', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Enable tooltip on logo.', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Animation', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Enable animation effect on logo', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>   
			<tr>
				<th><?php esc_html_e('Animation Type', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Enable animation effect on logo', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td>0</td>
				<td>15</td>
			</tr>
			
			<tr>
				<th><?php esc_html_e('Drag & Drop Slide Order Change', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Arrange your desired slides with your desired order and display', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			
			<tr>
				<th><?php esc_html_e('Loop Control for slider', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Infinite scroll control', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Lazyload Support', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Add lazyload support for image.', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Gutenberg Block Supports', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Use this plugin with Gutenberg easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Elementor Page Builder Support', 'wp-logo-showcase-responsive-slider-slider'); ?> <em class="wpos-new-feature">New</em><span><?php esc_html_e('Use this plugin with Elementor easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Bevear Builder Support', 'wp-logo-showcase-responsive-slider-slider'); ?> <em class="wpos-new-feature">New</em> <span><?php esc_html_e('Use this plugin with Bevear Builder easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('SiteOrigin Page Builder Support', 'wp-logo-showcase-responsive-slider-slider'); ?> <em class="wpos-new-feature">New</em><span><?php esc_html_e('Use this plugin with SiteOrigin easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Divi Page Builder Native Support', 'wp-logo-showcase-responsive-slider-slider'); ?> <em class="wpos-new-feature">New</em> <span><?php esc_html_e('Use this plugin with Divi Builder easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
				<tr>
				<th><?php esc_html_e('Fusion Page Builder (Avada) native support', 'wp-logo-showcase-responsive-slider-slider'); ?> <em class="wpos-new-feature">New</em> <span><?php esc_html_e('Use this plugin with Fusion( Avada ) Builder easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Visual Composer Page Builder Supports', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Use this plugin with Visual Composer easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>       
			
			<tr>
				<th><?php esc_html_e('Display logos for Particular Categories', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Display only logos with particular category', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
					<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Exclude Logos', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Do not display the logos you want', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Exclude Some Categories', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Do not display the logos for particular categories', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Logo Order / Order By Parameters', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Display logo according to date, title and etc', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Multiple Slider Parameters', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Slider parameters like autoplay, number of slide, sider dots and etc.', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Slider RTL Support', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Slider supports for RTL website', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Automatic Update', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Get automatic  plugin updates', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><?php esc_html_e('Lifetime', 'wp-logo-showcase-responsive-slider-slider'); ?></td>
				<td><?php esc_html_e('Lifetime', 'wp-logo-showcase-responsive-slider-slider'); ?></td>
			</tr> 
			<tr>
				<th><?php esc_html_e('Support', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Get support for plugin', 'wp-logo-showcase-responsive-slider-slider') ?></span></th>
				<td><?php esc_html_e('Limited', 'wp-logo-showcase-responsive-slider-slider') ?></td>
				<td><?php esc_html_e('1 Year', 'wp-logo-showcase-responsive-slider-slider') ?></td>
			</tr>
		</tbody>
	</table>
	<!-- <div class="wpls-deal-offer-wrap">
		<div class="wpls-deal-offer"> 
			<div class="wpls-inn-deal-offer">
				<h3 class="wpls-inn-deal-hedding"><span>Buy Logo Showcase Pro</span> today and unlock all the powerful features.</h3>
				<h4 class="wpls-inn-deal-sub-hedding"><span style="color:red;">Extra Bonus: </span>Users will get <span>extra best discount</span> on the regular price using this coupon code.</h4>
			</div>
			<div class="wpls-inn-deal-offer-btn">
				<div class="wpls-inn-deal-code"><span>EPSEXTRA</span></div>
				<a href="<?php //echo esc_url(WPLS_PLUGIN_BUNDLE_LINK); ?>"  target="_blank" class="wpls-sf-btn wpls-sf-btn-orange"><span class="dashicons dashicons-cart"></span> Get Essential Bundle Now</a>
				<em class="risk-free-guarantee"><span class="heading">Risk-Free Guarantee </span> - We offer a <span>30-day money back guarantee on all purchases</span>. If you are not happy with your purchases, we will refund your purchase. No questions asked!</em>
			</div>
		</div>
	</div> -->

	<!-- <div class="wpls-deal-offer-wrap">
		<div class="wpls-deal-offer"> 
			<div class="wpls-inn-deal-offer">
				<h3 class="wpls-inn-deal-hedding"><span>Try WP Logo Showcase Responsive Slider and Carousel Pro</span> in Essential Bundle Free For 5 Days.</h3>
			</div>
			<div class="wpls-deal-free-offer">
				<a href="<?php // echo esc_url( WPLS_PLUGIN_BUNDLE_LINK ); ?>" target="_blank" class="wpls-sf-free-btn"><span class="dashicons dashicons-cart"></span> Try Pro For 5 Days Free</a>
			</div>
		</div>
	</div> -->

	<!-- <div class="wpls-black-friday-banner-wrp">
		<a href="<?php // echo esc_url( WPLS_PLUGIN_BUNDLE_LINK ); ?>" target="_blank"><img style="width: 100%" src="<?php // echo esc_url( WPLS_URL ); ?>assets/images/black-friday-banner.png" alt="black-friday-banner" /></a>
	</div> -->

</div>