<div class="elex_dp_wrapper" style="
	height: 100%;
	display: flex;
	padding: 20px;">

	<!-- content -->
	<div class="elex-dp-gopremium-content h-100">


		<!-- main content -->
		<div class="box14 table-box-main rounded-3">

			<center class="my-3 px-3">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="row">
							<div class=" elex-license-like-img-container col-md-5">
								<?php $dp_premium_url = 'https://elextensions.com/plugin/dynamic-pricing-and-discounts-plugin-for-woocommerce/?utm_source=plugin-settings-related&utm_medium=wp-admin&utm_campaign=in-prod-ads'; ?>
								<a target="_blank" href="<?php echo esc_url($dp_premium_url); ?>">
									<img src="<?php echo esc_url(ELEX_DP_CRM_MAIN_URL . '/img/dynamic_pricing.png'); ?>" class="marketing_logos" alt="<?php esc_attr_e('Dynamic Pricing Logo', 'eh-dynamic-pricing-discounts'); ?>">
								</a>
								<br />
							</div>
							<div class="col-md-5">
								<ul style="list-style-type:disc;">
									<p><?php esc_html_e('Note: Basic version supports only few features.', 'eh-dynamic-pricing-discounts'); ?></p>
									<p style="color:red;"><strong><?php esc_html_e('Your business is precious! Go premium with additional features!.', 'eh-dynamic-pricing-discounts'); ?></strong></p>
									<p style="text-align:left">
										<?php esc_html_e(' - All the features in the Free Version.', 'eh-dynamic-pricing-discounts'); ?><br>
										<?php esc_html_e(' - Display Pricing Table.', 'eh-dynamic-pricing-discounts'); ?><br>
										<?php esc_html_e(' - Apply Multi-Product Rule.', 'eh-dynamic-pricing-discounts'); ?><br>
										<?php esc_html_e(' - Apply the Cart Rule for all the products in the cart.', 'eh-dynamic-pricing-discounts'); ?><br>
										<?php esc_html_e(' - Set the Maximum Possible Discount Amount on Every Rule.', 'eh-dynamic-pricing-discounts'); ?><br>
										<?php esc_html_e(' - Apply the BOGO Rule based on Product, Category, or Tags.', 'eh-dynamic-pricing-discounts'); ?><br>
										<?php esc_html_e(' - Add Coupon Discounts on the Rule.', 'eh-dynamic-pricing-discounts'); ?><br>
										<?php esc_html_e(' - Premium Support!', 'eh-dynamic-pricing-discounts'); ?>
										<br>
									</p>
								</ul>
								<?php $dp_documentation_url = 'https://elextensions.com/knowledge-base/set-up-elex-dynamic-pricing-and-discounts-plugin-for-woocommerce/'; ?>
								<center> <a href="<?php echo esc_url($dp_documentation_url); ?>" target="_blank" class="button button-primary"><?php esc_html_e('Documentation', 'eh-dynamic-pricing-discounts'); ?></a></center>
							</div>
						</div>
					</div>
				</div>
			</center>

			<h6 class="mb-4"><b><?php esc_html_e('You May Also like', 'eh-dynamic-pricing-discounts'); ?></b></h6>
			<div class="row">
				<?php
				$plugins = array(
					array(
						'url'   => 'https://elextensions.com/plugin/woocommerce-catalog-mode-wholesale-role-based-pricing/?utm_source=plugin-settings-related&utm_medium=wp-admin&utm_campaign=in-prod-ads',
						'image' => 'catalog_mode.png',
						'alt'   => 'catalog mode logo',
					),
					array(
						'url'   => 'https://elextensions.com/plugin/woocommerce-google-product-feed-plugin/?utm_source=plugin-settings-related&utm_medium=wp-admin&utm_campaign=in-prod-ads',
						'image' => 'gpf.png',
						'alt'   => 'google shopping feed logo',
					),
					array(
						'url'   => 'https://elextensions.com/plugin/wsdesk-wordpress-support-desk-plugin/?utm_source=plugin-settings-related&utm_medium=wp-admin&utm_campaign=in-prod-ads',
						'image' => 'wsdesk.png',
						'alt'   => 'wsdesk logo',
					),
					array(
						'url'   => 'https://elextensions.com/plugin/woocommerce-shipping-calculator-purchase-shipping-label-tracking-for-customers/',
						'image' => 'ship calculator.png',
						'alt'   => 'shipping calculator logo',
					),
				);

				foreach ($plugins as $plugin) :
					?>
					<div class="col-md-3 col-6">
						<div class="elex-license-like-img-container w-100  mb-3">
							<a target="_blank" href="<?php echo esc_url($plugin['url']); ?>" class="elex-license-like-img-container w-100 d-flex">
								<img src="<?php echo esc_url(ELEX_DP_CRM_MAIN_URL . '/img/' . $plugin['image']); ?>" alt="<?php esc_attr_e($plugin['alt'], 'eh-dynamic-pricing-discounts'); ?>" class="w-100">
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
<style>
	.box14 {
		width: 100%;
		margin-top: 2px;
		min-height: 310px;
		margin-right: 400px;
		padding: 10px;
		z-index: 1;
		right: 0px;
		float: left;
		background: -webkit-gradient(linear, 0% 20%, 0% 92%, from(#fff), to(#f3f3f3), color-stop(.1, #fff));
		border: 1px solid #ccc;
		-webkit-border-radius: 60px 5px;
		-webkit-box-shadow: 0px 0px 35px rgba(0, 0, 0, 0.1) inset;
	}

	.box14 h3 {
		text-align: center;
		margin: 2px;
	}

	.box14 p {
		text-align: center;
		margin: 2px;
		border-width: 1px;
		border-style: solid;
		padding: 5px;
		border-color: rgb(204, 204, 204);
	}

	.box14 span {
		background: #fff;
		padding: 5px;
		display: block;
		box-shadow: green 0px 3px inset;
		margin-top: 10px;
	}

	.box14 img {
		margin-top: 5px;
	}

	.table-box-main {
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
		transition: all 0.3s cubic-bezier(.25, .8, .25, 1);
	}

	.table-box-main:hover {
		box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
	}

	span ul li {
		margin: 4px;
	}

	.marketing_logos {
		width: 300px;
		height: 300px;
		border-radius: 10px;
	}

	.marketing_redirect_links {
		padding: 0px 2px !important;
		background-color: #fcb800;
		height: 52px;
		font-weight: 600 !important;
		font-size: 18px !important;
		min-width: 210px;
	}
</style>
