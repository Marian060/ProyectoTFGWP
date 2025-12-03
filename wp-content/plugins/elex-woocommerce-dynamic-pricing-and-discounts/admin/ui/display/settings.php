<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

// Sets active tab 
$active_tab = ( isset($_REQUEST['tab']) && is_string($_REQUEST['tab']) ) ? sanitize_text_field($_REQUEST['tab']) : 'rules_order';

// Data handling of form submit request.
if (isset($_REQUEST['submit'])) {
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
	$path = ELEX_DP_BASIC_ROOT_PATH . 'admin/data/settings_page/elex-save-options.php';
	if (file_exists($path) == true) {
		include_once  $path;
	}
}

// Renders view
require_once ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/renderer/settings.php';
