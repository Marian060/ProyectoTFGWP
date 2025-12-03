<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('Elex_dy_admin_actions_function')) {

	class Elex_dy_admin_actions_function {

		function elex_dp_func_enqueue_search_product_enhanced_select() {
			global $wp_scripts;
			wp_enqueue_script('wc-enhanced-select'); // if your are using recent versions
			wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/admin/css/xa-dynamic-pricing-plugin-admin.css');
			wp_enqueue_script( 'jquery-tiptip' );
		}

		function func_enqueue_jquery() {
			wp_enqueue_style('jquery');
		}

		function func_enqueue_ui_dependencies() {
			//Style
			wp_enqueue_style('dp-admin-ui-css', plugins_url('ui/css/app.css', __FILE__));

			//Script
			// Enqueue Bootstrap from a reliable CDN
			wp_enqueue_script('dp-admin-ui-popper', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js', array('jquery'), null, true);
			wp_enqueue_script('dp-admin-ui-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js', array('jquery', 'dp-admin-ui-popper'), null, true);
			// Enqueue Font Awesome from its official source
			wp_enqueue_script('dp-admin-ui-fontawesome', 'https://kit.fontawesome.com/f982eab5be.js', null, null, true);
			// Enqueue your custom script
			wp_enqueue_script('dp-admin-ui-js', plugins_url('ui/js/script.js', __FILE__), array('jquery', 'dp-admin-ui-popper', 'dp-admin-ui-bootstrap', 'dp-admin-ui-fontawesome'), null, true);
		}

		function func_enqueue_jquery_ui_datepicker() {
			//jQuery UI date picker file
			wp_enqueue_script('jquery-ui-datepicker');
			//jQuery UI theme css file
			wp_enqueue_style('e2b-admin-ui-css', plugins_url('css/jquery-ui.css', __FILE__));
		}

		function elex_dp_register_sub_menu() {
	/// Creates New Sub Menu under main Woocommerce menu
			add_submenu_page('woocommerce', 'Dynamic Pricing Main Page', __('Dynamic Pricing'), 'manage_woocommerce', 'dynamic-pricing-main-page', array($this, 'elex_dp_dynamic_pricing_admin_page'));
		}
		
		// Creates dynamic pricing main admin menu and submenu
		function elex_dp_add_admin_main_menu() {
			$parent_slug = 'dp-discount-rules-page';
	
			$cap = 'manage_woocommerce';
	
			if ( is_super_admin() ) {
				$cap = 'administrator';
			}
			$image = esc_html( ELEX_DP_CRM_MAIN_IMG . 'elex.svg' );
			add_menu_page(
				'Discount Rules',
				__('Dynamic Pricing'),
				$cap,
				$parent_slug,
				array( $this, 'elex_dp_discount_rules_page' ),
				$image,
				35
			);
	
			add_submenu_page( $parent_slug, 'Discount Rules', __( 'Discount Rules', 'eh-dynamic-pricing-discounts' ), $cap, 'dp-discount-rules-page', array( $this, 'elex_dp_discount_rules_page' ) );
			add_submenu_page( $parent_slug, 'Settings', __( 'Settings', 'eh-dynamic-pricing-discounts' ), 'administrator', 'dp-settings-page', array( $this, 'elex_dp_settings_page' ) );
			add_submenu_page( $parent_slug, 'Import/Export', __( "Import/Export<sup class='elex_dp_go_premium_color'>[Premium]</sup>" ), 'administrator', 'dp-import-export-page', array( $this, 'elex_dp_import_export_page' ));
			add_submenu_page( $parent_slug, 'Help & Support', __( 'Help & Support', 'eh-dynamic-pricing-discounts' ), 'administrator', 'dp-help-and-support-page', array( $this, 'elex_dp_help_and_support_page' ) );
			add_submenu_page( $parent_slug, 'Go Premium', __( "<span class='elex_dp_go_premium_color'>Go Premium!</span>", 'eh-dynamic-pricing-discounts' ), 'administrator', 'dp-go-premium-page', array( $this, 'elex_dp_go_premium_page' ) );

		}
		
		// Gets the help and support and display to user.
		function elex_dp_discount_rules_page() {
			require_once ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/display/discount-rules.php';
		}

		// Gets the help and support and display to user.
		function elex_dp_settings_page() {
			require_once ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/display/settings.php';
		}

		// Gets the help and support and display to user.
		function elex_dp_import_export_page() {
			require_once ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/view/import-export.php';
		}

		// Gets the help and support and display to user.
		function elex_dp_help_and_support_page() {
			require_once ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/renderer/help-support.php';
		}

		// Gets the help and support and display to user.
		function elex_dp_go_premium_page() {
			require_once ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/renderer/go-premium.php';
		}


		function elex_dp_dynamic_pricing_admin_page() {
	//Gets the plugin page and display to user
			require 'view/elex-dynamic-pricing-plugin-admin-display.php';
		}

		function dynamic_pricing_main_page_init() {
   /// Adds fields and options to database and Register Settings

		}


	}

}
add_action('wp_ajax_update_rules_arrangement', 'elex_dp_update_rules_arrangement');

function elex_dp_update_rules_arrangement() {
	$nonce       = !empty($_POST['xa-nonce'])?$_POST['xa-nonce']:'';
	$rules_order = !empty($_POST['rules-order'])?$_POST['rules-order']:'';
	$rules_type  = !empty($_POST['rules-type'])?$_POST['rules-type']:'';
	if (!wp_verify_nonce($nonce, 'update_rules_arrangement')) {
		wp_die('unauthorised access [unable to verify nonce]');
	} else {
		$allrules = get_option('xa_dp_rules');
		$allRules = !empty($allrules[$rules_type])?$allrules[$rules_type]:array();

		//Making array of existing rules on perpage
		$existing_perpage_rules_order = array();
		$allRules = array_reverse($allRules, true);

		$existing_perpage_rules_order[1] = $allRules;
		//Rules reordered array for the provided page
		$reordered_rules_on_given_page = [];
		foreach ($rules_order as $index) {
			$reordered_rules_on_given_page[] = !empty($allRules[$index])?$allRules[$index]:array();
		}

		//Updating rules order in existing_perpage_rules_order
		if ($reordered_rules_on_given_page) {
			if ($paged_rules = $existing_perpage_rules_order[1]) {
				$updated_order_on_index = [];
				$i=0;
				foreach ($paged_rules as $key => $rule) {
					$updated_order_on_index[$key] = $reordered_rules_on_given_page[$i];
					$i++;
				}
				$existing_perpage_rules_order[1] = $updated_order_on_index;
			}
		}

		//Setting up final data
		$reordered_final_rules_array = [];
		foreach ($existing_perpage_rules_order as $rules_array) {
			if ($rules_array) {
				foreach ($rules_array as $key => $rules) {
					$reordered_final_rules_array[$key] = $rules;
				}
			}
		}

		//Saving it to database
		if (!empty($reordered_final_rules_array)) {
			$reordered_final_rules_array = array_reverse($reordered_final_rules_array, true);
			$allrules[$rules_type] = $reordered_final_rules_array;
			update_option('xa_dp_rules', $allrules);
			wp_die('Arrangements Saved');
		} else {
			wp_die('unable to save');
		}
	}
}

add_action('wp_ajax_update_coupons_field', 'elex_dp_update_coupons_field');

function elex_dp_update_coupons_field() {
	$couponcode      = !empty($_POST['couponcode'])? $_POST['couponcode'] : '';
	$coupon_postdata = new stdClass();
	if (!empty($couponcode)) {
		global $woocommerce;
		$coupon                           = new WC_Coupon($couponcode);
		$coupon_postdata->discount_type   = $coupon->get_discount_type();
		$coupon_postdata->discount_amount = $coupon->get_amount();
		$coupon_postdata->usage_limit     = $coupon->get_usage_limit();
		$coupon_postdata->date_created    = $coupon->get_date_created()? ( date_format($coupon->get_date_created(), 'Y-m-d') ): '';
		$coupon_postdata->date_expires    = $coupon->get_date_expires()? ( date_format($coupon->get_date_expires(), 'Y-m-d') ): '';
		echo json_encode($coupon_postdata);
		exit;
	}
}
