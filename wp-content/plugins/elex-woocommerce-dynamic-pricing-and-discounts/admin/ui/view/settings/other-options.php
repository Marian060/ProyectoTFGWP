<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
?>

<div class="row">
	<div class="col-md-11">
		<div class="border  d-flex mb-4 elex-dynamic-pricing-other-option-tab-box">
			<!-- other option tab links -->
			<div class="elex-dynamic-pricing-other-option-tabs nav flex-column gap-1 nav-pills py-3 px-1 border-0 border-end"
					role="tablist" aria-orientation="vertical">

				<!-- general tab link -->
				<div class="nav-link active d-flex gap-2" id="elex-dynamic-setting-other-general-btn"
					data-bs-toggle="pill" data-bs-target="#elex-dynamic-setting-other-general-content"
					type="button" role="tab" aria-controls="elex-dynamic-setting-other-general-content"
					aria-selected="true">
					<svg xmlns="http://www.w3.org/2000/svg" width="21.307" height="21.307"
						viewBox="0 0 21.307 21.307">
						<path id="tool"
							d="M14.7,6.3a1,1,0,0,0,0,1.4l1.6,1.6a1,1,0,0,0,1.4,0l3.77-3.77a6,6,0,0,1-7.94,7.94L6.62,20.38a2.121,2.121,0,0,1-3-3l6.91-6.91a6,6,0,0,1,7.94-7.94L14.71,6.29Z"
							transform="translate(-1.809 -0.884)" stroke-linecap="round"
							stroke-linejoin="round" stroke-width="2" />
					</svg>
					<?php esc_html_e('General', 'eh-dynamic-pricing-discounts'); ?>
				</div>

				<!-- pricing table tab link -->
				<div class="nav-link d-flex gap-2" id="elex-dynamic-setting-pricing-table-btn" data-bs-toggle="pill"
					data-bs-target="#elex-dynamic-setting-pricing-table-content" type="button"
					role="tab" aria-controls="elex-dynamic-setting-pricing-table-content"
					aria-selected="false">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
						<path id="table" d="M9,3H5A2,2,0,0,0,3,5V9M9,3H19a2,2,0,0,1,2,2V9M9,3V21m0,0H19a2,2,0,0,0,2-2V9M9,21H5a2,2,0,0,1-2-2V9M3,9H21" transform="translate(-2 -2)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
						</svg>
					<?php esc_html_e('Pricing Table', 'eh-dynamic-pricing-discounts'); ?>
				</div>

				<!-- offer table tab link -->
				<div class="nav-link d-flex gap-2" id="elex-dynamic-setting-offers-table-btn" data-bs-toggle="pill"
					data-bs-target="#elex-dynamic-setting-offers-table-content" type="button" role="tab"
					aria-controls="elex-dynamic-setting-offers-table-content" aria-selected="false">
					<svg xmlns="http://www.w3.org/2000/svg" width="22.183" height="21.995"
						viewBox="0 0 22.183 21.995">
						<g id="Icon_feather-tag" data-name="Icon feather-tag"
							transform="translate(1.183 1)">
							<path id="Path_520" data-name="Path 520"
								d="M3.607,14.9l7.48,7.48a2.086,2.086,0,0,0,2.952,0L23,13.432V3H12.568L3.607,11.961A2.086,2.086,0,0,0,3.607,14.9Z"
								transform="translate(-3 -3)" stroke-linecap="round"
								stroke-linejoin="round" stroke-width="2" />
							<path id="Path_521" data-name="Path 521" d="M10.5,10.5h0"
								transform="translate(4.284 -5.284)" stroke-linecap="round"
								stroke-linejoin="round" stroke-width="2" />
						</g>
					</svg>
					<span><?php esc_html_e('Offers Table', 'eh-dynamic-pricing-discounts'); ?><sup class="elex_dp_go_premium_color"><?php esc_html_e( '[Premium]', 'eh-dynamic-pricing-discounts' ); ?></sup></span>
				</div>

				<!-- bogo option tab link -->
				<div class="nav-link d-flex gap-2" id="elex-dynamic-add-new-product-restrictions-btn"
					data-bs-toggle="pill" data-bs-target="#elex-dynamic-add-new-product-restrictions"
					type="button" role="tab" aria-controls="elex-dynamic-add-new-product-restrictions"
					aria-selected="false">
					<svg xmlns="http://www.w3.org/2000/svg" width="18" height="22" viewBox="0 0 18 22">
						<g id="Icon_feather-file-plus" data-name="Icon feather-file-plus" transform="translate(-3 -1)">
							<path id="Path_514" data-name="Path 514" d="M14,2H6A2,2,0,0,0,4,4V20a2,2,0,0,0,2,2H18a2,2,0,0,0,2-2V8Z" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
							<path id="Path_515" data-name="Path 515" d="M14,2V8h6" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
							<path id="Path_516" data-name="Path 516" d="M12,18V12" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
							<path id="Path_517" data-name="Path 517" d="M9,15h6" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
						</g>
						</svg>
					<span><?php esc_html_e('BOGO Option', 'eh-dynamic-pricing-discounts'); ?><sup class="elex_dp_go_premium_color"><?php esc_html_e( '[Premium]', 'eh-dynamic-pricing-discounts' ); ?></sup></span>
				</div>
			</div>

			<div class="tab-content flex-fill p-3" id="elex-dynamic-add-new-product-rule-tabs-content">

				<!-- general content -->
				<div class="tab-pane fade show active" id="elex-dynamic-setting-other-general-content"
					role="tabpanel" aria-labelledby="elex-dynamic-setting-other-general-btn">
					<div class="row mb-3 align-items-center">
						<div class="col-md-4">
						<div class="d-flex gap-2 justify-content-between">
							<h6 class="elex-dynamic-input-label"><?php esc_html_e('Calculation Mode', 'eh-dynamic-pricing-discounts'); ?></h6>
							<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php esc_attr_e('1. `Calculation Mode` – In case of multiple rules being satisfied by the products on the cart, `Best Discount` option would calculate all the applicable discounts and select the best among them. This option is selected in the settings page by default. In case of `First Match`, as per the order of the rule categories in the settings page and the rule numbers in the corresponding rule categories, the first matched rule will be selected among all the available rules. In case of `All Match`, all the available rules which matches with the current scenario will be applied.', 'eh-dynamic-pricing-discounts'); ?>"></i>
						</div>
						</div>
						<div class="col-md-8">
							<select id="mode" name="mode" class="form-select min-width-100">
								<option value='<?php echo esc_attr('best_discount'); ?>' <?php selected($mode, 'best_discount'); ?>><?php esc_html_e('Best Discount', 'eh-dynamic-pricing-discounts'); ?></option>
								<option value='<?php echo esc_attr('first_match'); ?>' <?php selected($mode, 'first_match'); ?>><?php esc_html_e('First Match Rule', 'eh-dynamic-pricing-discounts'); ?></option>
								<option value='<?php echo esc_attr('all_match'); ?>' <?php selected($mode, 'all_match'); ?>><?php esc_html_e('All Matched Rules', 'eh-dynamic-pricing-discounts'); ?></option>
							</select>
						</div>
					</div>
					<div class="row mb-3 align-items-center">
						<div class="col-md-4">
						<div class="d-flex gap-2 justify-content-between">	
							<h6 class="elex-dynamic-input-label"><?php esc_html_e('Rules per Page', 'eh-dynamic-pricing-discounts'); ?><sup class="elex_dp_go_premium_color"><?php esc_html_e( '[Premium]', 'eh-dynamic-pricing-discounts' ); ?></sup></h6>
							<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php esc_attr_e('Pagination: Enter the number of rules you want to display per page.', 'eh-dynamic-pricing-discounts'); ?>"></i>
						</div>
						</div>
						<div class="col-md-8">
							<input type="number" min="1" id="xa_rules_per_page" name="xa_rules_per_page" class="form-control" disabled>
						</div>
					</div>
					<?php
					if (is_plugin_active('woocommerce-product-addons/woocommerce-product-addons.php')) :
						?>
						<div class="row mb-3 align-items-center">
							<div class="col-md-4">
							<div class="d-flex gap-2 justify-content-between">	
								<h6 class="elex-dynamic-input-label"><?php esc_html_e('Product Add On Support', 'eh-dynamic-pricing-discounts'); ?></h6>
								<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php esc_attr_e('This option will add discount to the add on price for third party plugin WooCommerce Product Add-ons', 'eh-dynamic-pricing-discounts'); ?>"></i>
							</div>
							</div>
							<div class="col-md-8">
								<select id="xa_product_add_on_option" name="xa_product_add_on_option" class="form-select min-width-100" style="" selected='<?php echo $xa_product_add_on_option; ?>'>
									<option value='<?php echo esc_attr('enable'); ?>' <?php selected($xa_product_add_on_option, 'enable'); ?>><?php esc_html_e('Yes', 'eh-dynamic-pricing-discounts'); ?></option>
									<option value='<?php echo esc_attr('disable'); ?>' <?php selected($xa_product_add_on_option, 'disable'); ?>><?php esc_html_e('No', 'eh-dynamic-pricing-discounts'); ?></option>
								</select>
							</div>
						</div>
						<?php
					endif;
					?>
				</div>

				<!-- pricing table content -->
				<div class="tab-pane fade" id="elex-dynamic-setting-pricing-table-content"
					role="tabpanel" aria-labelledby="elex-dynamic-setting-pricing-table-btn">
					<div class="row mb-3 align-items-center">
						<div class="col-md-4">
						<div class="d-flex gap-2 justify-content-between">	
							<h6 class="elex-dynamic-input-label"><?php esc_html_e('Display Prices Table on Product Page', 'eh-dynamic-pricing-discounts'); ?></h6>
							<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr(__('This Option will create a pricing table from rules and show on the product page', 'eh-dynamic-pricing-discounts')); ?>"></i>
						</div>
						</div>
						<div class="col-md-8">
							<select id="price_table_on_off" name="price_table_on_off" class="form-select min-width-100" >
								<option value='<?php echo esc_attr('enable'); ?>' <?php selected($price_table_on_off, 'enable'); ?>><?php esc_html_e('Yes', 'eh-dynamic-pricing-discounts'); ?></option>
								<option value='<?php echo esc_attr('disable'); ?>' <?php selected($price_table_on_off, 'disable'); ?>><?php esc_html_e('No', 'eh-dynamic-pricing-discounts'); ?></option>
							</select>
						</div>
					</div>
					<?php
						global $wp_roles;
						$roles = $wp_roles->get_names();
					?>
					<div class="row mb-3 align-items-center">
						<div class="col-md-4">
						<div class="d-flex gap-2 justify-content-between">	
							<h6 class="elex-dynamic-input-label"><?php esc_html_e('Allowed Roles', 'eh-dynamic-pricing-discounts'); ?><sup class="elex_dp_go_premium_color"><?php esc_html_e( '[Premium]', 'eh-dynamic-pricing-discounts' ); ?></sup></h6>
							<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr(__('Select the user roles for which you want to display the pricing table. Leave this field empty to display for all user roles.', 'eh-dynamic-pricing-discounts')); ?>"></i>
						</div>
						</div>
						<div class="col-md-8">
							<select disabled id="allow_roles" name="allow_roles[]" class="wc-enhanced-select form-select span-w-100 min-width-100 select2-hidden-accessible"  multiple="" tabindex="-1" aria-hidden="true" >
							</select> 
						</div>
					</div>

					<div class="row mb-3 align-items-center">
						<div class="col-md-4">
						<div class="d-flex gap-2 justify-content-between">	
							<h6 class="elex-dynamic-input-label"><?php esc_html_e('Position of Pricing Table on Product Page', 'eh-dynamic-pricing-discounts'); ?></h6>
							<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr(__('Select where you want to show this table on the product page.', 'eh-dynamic-pricing-discounts')); ?>"></i>
						</div>
						</div>
						<div class="col-md-8">
							<select name='pricing_table_position' class='form-select min-width-100' selected='<?php echo esc_attr($pricing_table_position); ?>'>
								<?php
								$positions = array(
									'woocommerce_before_single_product'            => __('Before Product', 'eh-dynamic-pricing-discounts'),
									'woocommerce_after_single_product'             => __('After Product', 'eh-dynamic-pricing-discounts'),
									'woocommerce_before_single_product_summary'    => __('Before Product Summary', 'eh-dynamic-pricing-discounts'),
									'woocommerce_single_product_summary'           => __('In Product Summary', 'eh-dynamic-pricing-discounts'),
									'woocommerce_after_single_product_summary'     => __('After Product Summary', 'eh-dynamic-pricing-discounts'),
									'woocommerce_before_add_to_cart_button'        => __('Before Add To Cart Button', 'eh-dynamic-pricing-discounts'),
									'woocommerce_after_add_to_cart_button'         => __('After Add To Cart Button', 'eh-dynamic-pricing-discounts'),
									'woocommerce_before_add_to_cart_form'          => __('Before Add To Cart Form', 'eh-dynamic-pricing-discounts'),
									'woocommerce_after_add_to_cart_form'           => __('After Add To Cart Form', 'eh-dynamic-pricing-discounts'),
									'woocommerce_product_thumbnails'               => __('Product Thumbnails', 'eh-dynamic-pricing-discounts'),
									'woocommerce_product_meta_start'               => __('Product Meta Start', 'eh-dynamic-pricing-discounts'),
									'woocommerce_product_meta_end'                 => __('Product Meta End', 'eh-dynamic-pricing-discounts'),
								);

								foreach ($positions as $value => $label) {
									echo '<option value="' . esc_attr($value) . '" ' . selected($pricing_table_position, $value, false) . '>' . esc_html($label) . '</option>';
								}
								?>
							</select>
						</div>
					</div>
					<div class="row mb-3 align-items-center">
						<div class="col-md-4">
						<div class="d-flex gap-2 justify-content-between">	
							<h6 class="elex-dynamic-input-label"><?php esc_html_e('Short Name For Quantity', 'eh-dynamic-pricing-discounts'); ?></h6>
							<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr(__('Customize the short name for the Quantity.', 'eh-dynamic-pricing-discounts')); ?>"></i>
						</div>
						</div>
						<div class="col-md-8">
							<input type="text" class="form-control" style="" name="pricing_table_qnty_shrtcode" id="pricing_table_qnty_shrtcode" value='<?php echo esc_attr($pricing_table_qnty_shrtcode); ?>'>
						</div>
					</div>
					<div>
					   <br><br>
					   <hr>
					   <h6><center><span><?php esc_html_e('The pricing table will only show offers from "Product Rules"', 'eh-dynamic-pricing-discounts'); ?></span></center></h6>
					</div>
				</div>

				<!-- Offers Table -->
				<div class="tab-pane fade" id="elex-dynamic-setting-offers-table-content"
					role="tabpanel" aria-labelledby="elex-dynamic-setting-offers-table-btn">
					<div class="row mb-3 align-items-center">
						<div class="col-md-4">
						<div class="d-flex gap-2 justify-content-between">
							<h6 class="elex-dynamic-input-label"><?php esc_html_e('Display Prices Table on Product Page', 'eh-dynamic-pricing-discounts'); ?></h6>
							<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr(__('This option will create a list of offers applicable to that product, visible on the product page.', 'eh-dynamic-pricing-discounts')); ?>"></i>
						</div>
						</div>
						<div class="col-md-8">
							<select disabled id="offer_table_on_off" name="offer_table_on_off" class="form-select min-width-100" style="" >
							<option value='<?php echo esc_attr('disable'); ?>' selected><?php esc_html_e('No', 'eh-dynamic-pricing-discounts'); ?></option>
							</select>
						</div>
					</div>



					<div class="row mb-3 align-items-center">
						<div class="col-md-4">
							<div class="d-flex gap-2 justify-content-between">	
								<h6 class="elex-dynamic-input-label"><?php esc_html_e('Position of Pricing Table on Product Page', 'eh-dynamic-pricing-discounts'); ?></h6>
								<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr(__('Select where you want to show this table on the product page.', 'eh-dynamic-pricing-discounts')); ?>"></i>
							</div>
						</div>
						<div class="col-md-8">
							<select id="offer_table_position" name="offer_table_position" class="form-select min-width-100" style=""  disabled>
								<option value='<?php echo esc_attr('woocommerce_before_add_to_cart_button'); ?>'  <?php selected($offer_table_position, 'woocommerce_before_add_to_cart_button'); ?>><?php esc_html_e('Before Add To Cart Button', 'eh-dynamic-pricing-discounts'); ?></option>
							</select>
						</div>
					</div>

				</div>

				<!-- BOGO Option -->
				<div class="tab-pane fade" id="elex-dynamic-add-new-product-restrictions"
					role="tabpanel" aria-labelledby="elex-dynamic-add-new-product-restrictions-btn">

					<div class="row mb-3 align-items-center">
						<div class="col-md-4">
						<div class="d-flex gap-2 justify-content-between">	
							<h6 class="elex-dynamic-input-label"><?php esc_html_e('Automatically Add Free Products', 'eh-dynamic-pricing-discounts'); ?></h6>
							<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr(__('If this field is enabled, the free product will be automatically added to the cart. If this field is disabled, the free product will have to be selected manually. By default, this field is set to `enabled` as that is what most store owners desire.', 'eh-dynamic-pricing-discounts')); ?>"></i>
						</div>
						</div>
						<div class="col-md-8">
							<label class="elex-switch-btn">
								<div class="elex-switch-icon round"></div>
							</label>
						</div>
					</div>


				</div>
			</div>

		</div>

		<input class="btn btn-primary" name="submit" id="submit" type="submit" value="<?php esc_attr_e('Save Rule', 'eh-dynamic-pricing-discounts'); ?>" />
	</div>
</div>
