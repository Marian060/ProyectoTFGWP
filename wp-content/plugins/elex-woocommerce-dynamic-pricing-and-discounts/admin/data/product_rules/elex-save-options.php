<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!defined('WPINC')) {
	die;
}


if (!empty($_REQUEST['offer_name']) && isset($_REQUEST['rule_on']) && ( ( isset($_REQUEST['product_id']) ) && ( !empty($_REQUEST['product_id']) && ( 'products' == $_REQUEST['rule_on'] ) ) || ( isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id']) && ( 'categories' === $_REQUEST['rule_on'] ) ) || ( 'cart' === $_REQUEST['rule_on'] ) ) && !empty($_REQUEST['check_on']) && !empty($_REQUEST['min']) && !empty($_REQUEST['discount_type']) && ( !empty($_REQUEST['value']) ) && !isset($_REQUEST['edit'])) {
	$dummy_settings = [];
	$dummy_settings['product_rules']       = array();
	$dummy_settings['combinational_rules'] = array();
	$dummy_settings['category_rules']      = array();
	$dummy_settings['cart_rules']          = array();
	$dummy_settings['buy_get_free_rules']  = array();
	$dummy_settings['BOGO_category_rules'] = array();
	$dummy_settings['bogo_tag_rules']      = array();

	$products_ids = null;
	$categories   = null;

	if ($_REQUEST['rule_on'] === 'products' && isset($_REQUEST['product_id']) && !empty($_REQUEST['product_id'])) {
		if (elex_dp_is_wc_version_gt_eql('2.7')) {
			$products_ids = array_map('sanitize_text_field', $_REQUEST['product_id']);// array
		} else {
			$products_ids = explode(',', sanitize_text_field($_REQUEST['product_id'])); // string
		}
	} elseif ($_REQUEST['rule_on'] === 'categories' && isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id'])) {
		$categories = sanitize_text_field($_REQUEST['category_id']); // string
	}

	$prev_data = get_option('xa_dp_rules', $dummy_settings);
	$new_rule = array(
		'offer_name'             => sanitize_text_field($_REQUEST['offer_name']),
		'rule_on'                => sanitize_text_field($_REQUEST['rule_on']),
		'product_id'             => $products_ids,
		'category_id'            => $categories,
		'check_on'               => sanitize_text_field($_REQUEST['check_on']),
		'min'                    => sanitize_text_field($_REQUEST['min']),
		'max'                    => !empty($_REQUEST['max']) ? sanitize_text_field($_REQUEST['max']) : null,
		'discount_type'          => sanitize_text_field($_REQUEST['discount_type']),
		'value'                  => sanitize_text_field($_REQUEST['value']),
		'max_discount'           => null,
		'allow_roles'            => ( isset($_REQUEST['from_date']) && !empty($_REQUEST['allow_roles']) ) ? array_map('sanitize_text_field', $_REQUEST['allow_roles']) : array(),
		'allow_membership_plans' => ( isset($_REQUEST['from_date']) && !empty($_REQUEST['allow_membership_plans']) ) ? array_map('sanitize_text_field', $_REQUEST['allow_membership_plans']) : array(),
		'from_date'              => ( isset($_REQUEST['from_date']) && !empty($_REQUEST['from_date']) ) ? sanitize_text_field($_REQUEST['from_date']) : null,
		'to_date'                => ( isset($_REQUEST['from_date']) &&!empty($_REQUEST['to_date']) ) ? sanitize_text_field($_REQUEST['to_date']) : null,
		'adjustment'             => null,
		'repeat_rule'            => '',
		'email_ids'              => null,
		'prev_order_count'       => null,
		'prev_order_total_amt'   => null,
	);

	if (!isset($prev_data[$active_tab]) || count($prev_data[$active_tab]) == 0) {
		$prev_data[$active_tab][1] = $new_rule;
	} else {
		$prev_data[$active_tab][] = $new_rule;
	}

	do_action( 'wpml_register_single_string', 'eh-dynamic-pricing', $active_tab . ':' . count($prev_data[$active_tab]), sanitize_text_field($_REQUEST['offer_name']));
	update_option('xa_dp_rules', $prev_data);

	?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e('Saved Successfully', 'eh-dynamic-pricing-discounts'); ?></p>
	</div>
	<?php
	wp_safe_redirect(add_query_arg(array('page' => 'dp-discount-rules-page', 'tab' => $active_tab,'savesuccess' => 1), admin_url('admin.php')));

} else {
	echo '<div class="notice notice-error is-dismissible">';
	echo '<p>' . esc_html_e('Please Enter All Fields!! Then Save', 'eh-dynamic-pricing-discounts') . '</p> </div>';
}
