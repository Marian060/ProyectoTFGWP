<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$active_tab = ( isset($_REQUEST['tab']) && is_string($_REQUEST['tab']) ) ? sanitize_text_field($_REQUEST['tab']) : 'product_rules';

if (isset($_REQUEST['cancel_btn'])) {
	wp_safe_redirect(add_query_arg(array('page' => 'dp-discount-rules-page', 'tab' => $active_tab), admin_url('admin.php')));
	die();
}

if ( !empty($_REQUEST['delete']) || ( isset($_REQUEST['update']) && empty($_REQUEST['update']) ) || !empty($_REQUEST['update']) ) {
	
	global $wpdb;

	// Specify the transient key pattern
	$transient_key_pattern = 'elex_dp_product_data_%';

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_%' . $transient_key_pattern,
			'_transient_timeout_%' . $transient_key_pattern
		)
	);

	// Clear the object cache
	wp_cache_flush();
	
}

if (!empty($_REQUEST['delete'])) {
	if (!isset($_REQUEST['eh_rule_form_nonce']) || !wp_verify_nonce(sanitize_text_field($_REQUEST['eh_rule_form_nonce']), 'eh_rule_form_nonce')) {
		wp_safe_redirect(add_query_arg(array('page' => 'dp-discount-rules-page', 'tab' => $active_tab), admin_url('admin.php')));
		die();
	}
	$prev_data = get_option('xa_dp_rules', array());
	$dp_coupons_data = get_option('dp_coupons_data', array());
	$deleting_rule_coupon_code = isset($prev_data[$active_tab][$_REQUEST['delete']]['coupon_code']) ? sanitize_text_field($prev_data[$active_tab][$_REQUEST['delete']]['coupon_code']) : '';
	if (isset($deleting_rule_coupon_code) && !empty($deleting_rule_coupon_code) && ( array_key_exists($deleting_rule_coupon_code, $dp_coupons_data) )) {
		unset($dp_coupons_data[$deleting_rule_coupon_code]);
		$dp_coupons_data = array_values($dp_coupons_data);
		update_option('dp_coupons_data', $dp_coupons_data);
	}
	$activetab_rules_data = $prev_data[$active_tab];
	$index_to_delete = sanitize_text_field($_REQUEST['delete']);
	unset($activetab_rules_data[$index_to_delete]);
	if ($activetab_rules_data) {
		$activetab_rules_data = array_values($activetab_rules_data);
		$activetab_rules_data = array_combine(range(1, count($activetab_rules_data)), array_values($activetab_rules_data));
	}
	$prev_data[$active_tab] = $activetab_rules_data;
	update_option('xa_dp_rules', $prev_data);
	wp_redirect(add_query_arg(array('page' => 'dp-discount-rules-page', 'tab' => $active_tab, 'deletesuccess' => 1), admin_url('admin.php')));

}

if (!empty($_REQUEST['deletesuccess'])) {
	echo '<div class="notice notice-warning inline is-dismissible"><p></br><lable>Deleted Successfully !!</p></div>';
}

if (isset($_REQUEST['update']) && empty($_REQUEST['update'])) {    //Submit And Not Edit Then Saving New Record
	if (!isset($_REQUEST['save_rule_nonce']) || !wp_verify_nonce(sanitize_text_field($_REQUEST['save_rule_nonce']), 'save_rule_nonce')) {
		wp_safe_redirect(add_query_arg(array('page' => 'dp-discount-rules-page', 'tab' => $active_tab), admin_url('admin.php')));
		die();
	}
	// Specify the transient key pattern
	$transient_key_pattern = 'elex_dp_product_data_';

	// Delete transients with similar keys
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
			'transient' . $transient_key_pattern,
			'transient_timeout' . $transient_key_pattern
		)
	);
	$current_tab_loc = ( isset($_REQUEST['tab']) && is_string($_REQUEST['tab']) ) ? sanitize_text_field($_REQUEST['tab']) . '/' : 'product_rules/';
	$path            = ELEX_DP_BASIC_ROOT_PATH . 'admin/data/' . $current_tab_loc . 'elex-save-options.php';
	if (file_exists($path) == true) {
		include_once  $path ;
	}

} elseif (isset($_REQUEST['edit']) && !empty($_REQUEST['edit'])) {    //Loading Edit Form Or Updating Data
	if (!isset($_REQUEST['eh_rule_form_nonce']) || !wp_verify_nonce(sanitize_text_field($_REQUEST['eh_rule_form_nonce']), 'eh_rule_form_nonce')) {
		wp_safe_redirect(add_query_arg(array('page' => 'dp-discount-rules-page', 'tab' => $active_tab), admin_url('admin.php')));
		die();
	}
	$old_option = get_option('xa_dp_rules', array($active_tab => array()));
	$old_option = $old_option[$active_tab];
	$_REQUEST = array_merge($_REQUEST, $old_option[$_REQUEST['edit']]);
	$current_tab_loc = ( isset($_REQUEST['tab']) && is_string($_REQUEST['tab']) ) ? sanitize_text_field($_REQUEST['tab']) . '/' : 'product_rules/';
	$path            = ELEX_DP_BASIC_ROOT_PATH . 'admin/data/' . $current_tab_loc . 'elex-load-edit.php';
	include_once  $path ;

} elseif ( !empty($_REQUEST['update'])) {
	if (!isset($_REQUEST['update_rule_' . sanitize_text_field($_REQUEST['update'])]) || !wp_verify_nonce(sanitize_text_field($_REQUEST['update_rule_' . sanitize_text_field($_REQUEST['update'])]), 'update_rule_' . sanitize_text_field($_REQUEST['update']))) {
		wp_safe_redirect(add_query_arg(array('page' => 'dp-discount-rules-page', 'tab' => $active_tab), admin_url('admin.php')));
		die();
	}
		// Specify the transient key pattern
		$transient_key_pattern = 'elex_dp_product_data_';

		// Delete transients with similar keys
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
				'transient' . $transient_key_pattern,
				'transient_timeout' . $transient_key_pattern
			)
		);
		
	$path = ( isset($_REQUEST['tab']) && is_string($_REQUEST['tab']) ) ? sanitize_text_field($_REQUEST['tab']) . '/' : 'product_rules/';
	$path = ELEX_DP_BASIC_ROOT_PATH . 'admin/data/' . $path . 'elex-update-options.php';
	include_once  $path ;
}

require_once ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/renderer/discount-rules.php';
