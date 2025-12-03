<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!defined('WPINC')) {
	die;
}


if (isset($_REQUEST['offer_name']) && !empty($_REQUEST['offer_name']) && isset($_REQUEST['check_on']) && !empty($_REQUEST['check_on']) && isset($_REQUEST['min']) && !empty($_REQUEST['min']) && isset($_REQUEST['discount_type']) && !empty($_REQUEST['discount_type']) && ( !empty($_REQUEST['value']) ) && isset($_REQUEST['update']) && isset($_REQUEST['category_id'])) {


	$prev_data = get_option('xa_dp_rules');
	$new_rule = array(
		'offer_name'             => sanitize_text_field($_REQUEST['offer_name']),
		'category_id'            => array_map('sanitize_text_field', $_REQUEST['category_id']),
		'check_on'               => sanitize_text_field($_REQUEST['check_on']),
		'min'                    => sanitize_text_field($_REQUEST['min']),
		'max'                    => !empty($_REQUEST['max']) ? sanitize_text_field($_REQUEST['max']) : null,
		'discount_type'          => sanitize_text_field($_REQUEST['discount_type']),
		'value'                  => sanitize_text_field($_REQUEST['value']),
		'max_discount' => null,
		'allow_roles' => array(),
		'allow_membership_plans' => array(),
		'from_date' => null,
		'to_date' => null,
		'adjustment' => null,
		'email_ids' => null,
		'prev_order_count' => null,
		'prev_order_total_amt' => null,
	);

	$prev_data[$active_tab][sanitize_text_field($_REQUEST['update'])] = $new_rule;
	
	do_action( 'wpml_register_single_string', 'eh-dynamic-pricing', $active_tab . ':' . sanitize_text_field($_REQUEST['update']), sanitize_text_field($_REQUEST['offer_name']));
	update_option('xa_dp_rules', $prev_data);

	$_REQUEST = array();
	?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e('Updated Successfully', 'eh-dynamic-pricing-discounts'); ?></p>
		</div>
		<?php
		wp_safe_redirect(add_query_arg(array('page' => 'dp-discount-rules-page', 'tab' => $active_tab,'updatesuccess' => 1), admin_url('admin.php')));
} else {
	echo '<div class="notice notice-error is-dismissible">';
	echo '<p>' . esc_html_e('Please Enter All Fields ,Then Try To Update!!', 'eh-dynamic-pricing-discounts') . '</p> </div>';
}
