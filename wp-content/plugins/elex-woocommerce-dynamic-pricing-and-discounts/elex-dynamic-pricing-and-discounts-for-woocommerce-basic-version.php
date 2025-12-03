<?php
/**
* 	Plugin Name: ELEX Dynamic Pricing and Discounts for WooCommerce Basic Version
*	Plugin URI: https://elextensions.com/plugin/elex-dynamic-pricing-and-discounts-plugin-for-woocommerce-free-version/
*	Description: This plugin helps you to set discounts and pricing dynamically based on minimum quantity,weight,price and allow you to set maximum allowed discounts on every rule.
*	Version: 2.2.5
*	Author: ELEXtensions
*   WC requires at least: 3.0.0
*   WC tested up to: 10.0
*	Author URI: https://elextensions.com
*	Copyright: 2018 ELEX.
*/
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

define('ELEX_DP_BASIC_ROOT_PATH', plugin_dir_path(__FILE__));
if (!defined('WPINC')) {
	die;
}

if ( ! defined( 'ELEX_DP_CRM_MAIN_URL' ) ) {
	define( 'ELEX_DP_CRM_MAIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'ELEX_DP_CRM_MAIN_IMG' ) ) {
	define( 'ELEX_DP_CRM_MAIN_IMG', ELEX_DP_CRM_MAIN_URL . 'admin/ui/images/' );
}

global $elex_dp_cached_prices;

// Check if woocommerce is active
$active_plugins = (array) get_option( 'active_plugins', array() );
if ( is_multisite() ) {
	$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins' ), array() );
}

// for Required functions
if ( ! function_exists( 'elex_dp_premium_is_woocommerce_active' ) ) {
	require_once  'includes/elex-dynamic-pricing-plugin-functions.php' ;
}
// to check woocommerce is active
if ( ! ( elex_dp_premium_is_woocommerce_active() ) ) {
	add_action( 'admin_notices', 'woocommerce_activation_notice_in_basic_dp' );
	return;
}

function woocommerce_activation_notice_in_basic_dp() {  ?>
	<div id="message" class="error">
		<p>
			<?php echo( esc_attr_e( 'WooCommerce plugin must be active for ELEX Dynamic Pricing and Discounts Plugin to work.', 'eh-dynamic-pricing-discounts' ) ); ?>
		</p>
	</div>
	<?php
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-xa-dynamic-pricing-plugin-activator.php
 */
if (!function_exists('elex_dp_activate_dynamic_pricing_plugin_basic')) {
	function elex_dp_activate_dynamic_pricing_plugin_basic() {
		$error_msg = "Premium Version of Dynamic Pricing Plugin is installed and activated. Please deactivate the Premium Version of Dynamic Pricing before activating BASIC version.<br>Go back to <a href='" . esc_html( admin_url( 'plugins.php' ) ) . "'>plugins page</a>";
		if (is_plugin_active('elex-woocommerce-dynamic-pricing-and-discounts-premium/elex-woocommerce-dynamic-pricing-and-discounts-premium.php')) {
			deactivate_plugins(basename(__FILE__));
			wp_die( wp_kses_post( $error_msg ) );
		}

		require_once plugin_dir_path(__FILE__) . 'includes/elex-dynamic-pricing-plugin-activator.php';
		Elex_dynamic_pricing_plugin_Activator::activate();
	}

}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-xa-dynamic-pricing-plugin-deactivator.php
 */
if (!function_exists('elex_dp_deactivate_dynamic_pricing_plugin_basic')) {

	function elex_dp_deactivate_dynamic_pricing_plugin_basic() {
		if (!class_exists('woocommerce')) {
			new WP_Error('1', 'Dynamic Pricing And Discounts Plugin could not start because WooCommerce Plugin is Deactivated!!');
		}

		require_once plugin_dir_path(__FILE__) . 'includes/elex-dynamic-pricing-plugin-deactivator.php';
		Elex_dynamic_pricing_plugin_Deactivator::deactivate();
	}

}

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
function elex_dp_clear_all_transients() {
	global $wpdb;
	// Delete all transients related to the discount plugin
	$transient_key_pattern = 'elex_dp_product_data_%';
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_' . $transient_key_pattern,
			'_transient_timeout_' . $transient_key_pattern
		)
	);

	// Clear object cache
	wp_cache_flush();
}

register_activation_hook(__FILE__, 'elex_dp_activate_dynamic_pricing_plugin_basic');
register_deactivation_hook(__FILE__, 'elex_dp_deactivate_dynamic_pricing_plugin_basic');
add_action('upgrader_process_complete', 'elex_dp_clear_all_transients', 10, 2);
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/elex-dynamic-pricing-plugin.php';

add_action('init', 'elex_dp_load_plugin_textdomain');
add_action('init', 'elex_dp_init');

if (!function_exists('elex_dp_load_plugin_textdomain')) {

	function elex_dp_load_plugin_textdomain() {
		load_plugin_textdomain('eh-dynamic-pricing-discounts', false, dirname(plugin_basename(__FILE__)) . '/languages');
	}

}
if (!function_exists('elex_dp_init')) {

	function elex_dp_init() {
		if (is_admin()) {
			include 'admin/elex-ajax-function.php';
			include 'admin/elex-exporter.php';
			include 'admin/elex-importer.php';
		}
	}

}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
if (!function_exists('elex_dp_run_dynamic_pricing_plugin')) {

	function elex_dp_run_dynamic_pricing_plugin() {
		$plugin = new Elex_dynamic_pricing_plugin();
		$plugin->run();
	}

}
global $offers;
$offers = array();
if (!function_exists('elex_dp_plugin_settings_link')) {

	function elex_dp_plugin_settings_link( $links) {
		$settings_link = '<a href="admin.php?page=dp-settings-page">Settings</a>';
		$doc_link      = '<a href="https://elextensions.com/set-up-elex-dynamic-pricing-and-discounts-plugin-for-woocommerce/" target="_blank">' . __('Documentation', 'eh-dynamic-pricing-discounts') . '</a>';
		$support_link  = '<a href="https://elextensions.com/support/" target="_blank">' . __('Support', 'eha_multi_carrier_shipping') . '</a>';

		array_unshift($links, $support_link);
		array_unshift($links, $doc_link);
		array_unshift($links, $settings_link);
		return $links;
	}

}


$pluginbasename = plugin_basename(__FILE__);
add_filter("plugin_action_links_$pluginbasename", 'elex_dp_plugin_settings_link');

if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once  ABSPATH . 'wp-admin/includes/plugin.php' ;
}
include_once dirname( __DIR__ ) . '/elex-woocommerce-dynamic-pricing-and-discounts/includes/review_and_troubleshoot_notify/review-and-troubleshoot-notify-class.php';
$data                      = get_plugin_data( WP_PLUGIN_DIR . '/' . $pluginbasename, false, false);
$data['name']              = $data['Name'];
$data['basename']          = $pluginbasename;
$data['rating_url']        = 'https://elextensions.com/plugin/elex-dynamic-pricing-and-discounts-plugin-for-woocommerce-free-version/#reviews';
$data['documentation_url'] = 'https://elextensions.com/knowledge-base/set-up-elex-dynamic-pricing-and-discounts-plugin-for-woocommerce/';
$data['support_url']       = 'https://support.elextensions.com/';

new \Elex_Review_Components( $data );

elex_dp_run_dynamic_pricing_plugin();

$rules_indexing_status   = get_option('xa_dp_rules_indexing_status');
if (!isset($rules_indexing_status) || $rules_indexing_status == false) {
	require_once plugin_dir_path(__FILE__) . 'includes/elex-dynamic-pricing-plugin-activator.php';
	Elex_dynamic_pricing_plugin_Activator::activate();
}
