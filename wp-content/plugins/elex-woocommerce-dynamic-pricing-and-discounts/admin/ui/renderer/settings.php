<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$dummy_settings = array(
	'product_rules_on_off' => 'enable',
	'combinational_rules_on_off' => 'enable',
	'cat_comb_rules_on_off' => 'enable',
	'category_rules_on_off' => 'enable',
	'cart_rules_on_off' => 'enable',
	'buy_and_get_free_rules_on_off' => 'enable',
	'BOGO_category_rules_on_off' => 'enable',
	'bogo_tag_rules_on_off' => 'enable',
	'tag_rules_on_off' => 'enable',
	'price_table_on_off' => 'enable',
	'xa_product_add_on_option' => 'enable',
	'offer_table_on_off' => 'disable',
	'offer_table_position' => 'woocommerce_before_add_to_cart_button',
	'auto_add_free_product_on_off' => '',
	'pricing_table_qnty_shrtcode' => 'nos.',
	'show_discount_in_line_item' => 'yes',
	'pricing_table_position' => 'woocommerce_before_add_to_cart_button',
	'mode' => 'first_match',
	'disable_shop_page_calculation' => 'no',
	'disable_product_page_calculation' => 'no',
	'show_on_sale' => 'yes',
	'discount_over_price_including_tax' => 'yes',
	'execution_order' => array('product_rules', 'category_rules'),
	'rules_modes_order' => array(
		'product_rules',
		'combinational_rules',
		'cat_combinational_rules',
		'category_rules',
		'cart_rules',
		'buy_get_free_rules',
		'BOGO_category_rules',
		'tag_rules',
		'bogo_tag_rules'
	),
);

$settings = get_option('xa_dynamic_pricing_setting', $dummy_settings);
extract($settings);

$default_values = array(
	'disable_shop_page_calculation' => 'no',
	'cat_comb_rules_on_off' => 'enable',
	'disable_product_page_calculation' => 'no',
	'pricing_table_qnty_shrtcode' => 'nos.',
	'show_discount_in_line_item' => 'yes',
	'mode' => 'best_discount',
	'buy_and_get_free_rules_on_off' => 'enable',
	'BOGO_category_rules_on_off' => 'enable',
	'pricing_table_position' => 'woocommerce_before_add_to_cart_button',
	'offer_table_position' => 'woocommerce_before_add_to_cart_button',
	'offer_table_on_off' => 'disable',
	'xa_product_add_on_option' => 'enable',
	'show_on_sale' => 'yes',
	'discount_over_price_including_tax' => 'yes',
	'execution_order' => array('product_rules', 'category_rules'),
);

foreach ($default_values as $key => $default_value) {
	if (!isset($key)) {
		$key = $default_value;
	}
}

if (!isset($rules_modes_order) || empty($rules_modes_order)) {
	$rules_modes_order = array(
		'product_rules',
		'combinational_rules',
		'cat_combinational_rules',
		'category_rules',
		'cart_rules',
		'buy_get_free_rules', 'BOGO_category_rules', 'tag_rules', 'bogo_tag_rules'
	);
}

$rules_modes = array(
	'product_rules' => 'Product Rules',
	'combinational_rules' => 'Multi Product Rules',
	'category_rules' => 'Category Rules',
	'cat_combinational_rules' => 'Multi Category Rules',
	'cart_rules' => 'Cart Rules',
	'buy_get_free_rules' => 'BOGO Product Rules',
	'BOGO_category_rules' => 'BOGO Category Rules',
	'tag_rules' => 'Tag Rules',
	'bogo_tag_rules' => 'BOGO Tag Rules'
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e('Settings', 'eh-dynamic-pricing-discounts'); ?></title>
</head>

<body>

	<div class="elex-dynamic-pricing-wrap">

		<!-- content -->
		<div class="elex-dynamic-pricing-content d-flex">

			<!-- main content -->
			<div class="elex-dynamic-pricing-main w-100 p-2 pe-4">

				<!-- banner -->
				<img src="<?php echo esc_url(ELEX_DP_CRM_MAIN_IMG . 'top_banner.svg'); ?>" alt="<?php esc_attr_e('Dynamic Pricing banner', 'eh-dynamic-pricing-discounts'); ?>" class="w-100  mb-2">


				<!-- links -->
				<div class="d-flex elex-light-blue-bg m-0 mb-3 justify-content-start gap-2 align-items-center elex-dynamic-pricing-main-links elex-dynamic-pricing-main-setting-links">
					<a href="<?php echo esc_url(add_query_arg('page', 'dp-settings-page', add_query_arg('tab', 'rules_order', admin_url('admin.php')))); ?>" class=" elex-dynamic-pricing-main-link <?php echo esc_attr($active_tab == 'rules_order' ? 'active' : ''); ?>">
						<?php esc_html_e('Rules & Execution Order', 'eh-dynamic-pricing-discounts'); ?>
					</a>
					<svg xmlns="http://www.w3.org/2000/svg" width="2" height="100%" viewBox="0 0 2 25.587">
						<path id="carrier_seperator" data-name="carrier seperator" d="M2565.89-576v23.587" transform="translate(-2564.89 577)" fill="none" stroke="#707070" stroke-linecap="round" stroke-width="2" />
					</svg>

					<a href="<?php echo esc_url(add_query_arg('page', 'dp-settings-page', add_query_arg('tab', 'other_options', admin_url('admin.php')))); ?>" class="elex-dynamic-pricing-main-link <?php echo esc_attr($active_tab == 'other_options' ? 'active' : ''); ?>">
						<?php esc_html_e('Other Options', 'eh-dynamic-pricing-discounts'); ?>
					</a>
					<svg xmlns="http://www.w3.org/2000/svg" width="2" height="100%" viewBox="0 0 2 25.587">
						<path id="carrier_seperator" data-name="carrier seperator" d="M2565.89-576v23.587" transform="translate(-2564.89 577)" fill="none" stroke="#707070" stroke-linecap="round" stroke-width="2" />
					</svg>
					<a href="<?php echo esc_url(add_query_arg('page', 'dp-settings-page', add_query_arg('tab', 'reset_rules', admin_url('admin.php')))); ?>" class="elex-dynamic-pricing-main-link <?php echo esc_attr($active_tab == 'reset_rules' ? 'active' : ''); ?>">
						<?php esc_html_e('Reset Rules', 'eh-dynamic-pricing-discounts'); ?>
					</a>
				</div>

				<!-- table -->
				<form name="post" method="post" id="post">

					<?php wp_nonce_field('elex-dp-settings-nonce', 'elex-dp-settings-nonce'); ?>
					<input type="hidden" id="page" name="page" value="dp-settings-page">
					<input type="hidden" id="tab" name="tab" value="<?php echo esc_attr($active_tab); ?>">
					<?php

					if ($active_tab == 'rules_order') {
						require_once ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/view/settings/rules-execution-order.php';
					} elseif ($active_tab == 'other_options') {
						require_once ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/view/settings/other-options.php';
					} elseif ($active_tab == 'reset_rules') {
						require_once ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/view/settings/reset-rules.php';
					}

					?>
				</form>
			</div>
		</div>
	</div>
</body>

</html>

<script>
	jQuery(document).ready(function() {

		jQuery('tbody').sortable({
			placeholder: "ui-widget-shadow",
			handle: 'th.icon-move',
			update: function() {}
		});
	});
</script>
<style>
	td.icon-move {
		background-image: url('<?php echo plugins_url('elex-woocommerce-dynamic-pricing-and-discounts-premium/jquery-ui/drag2.png'); ?>');
		background-size: auto auto;
		background-position: center;
		background-repeat: no-repeat;
	}
</style>
