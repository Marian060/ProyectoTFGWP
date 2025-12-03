<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!defined('WPINC')) {
	die;
}

if (isset($_REQUEST['submit'])) {
	$saved_data = get_option('xa_dynamic_pricing_setting');

	if (!isset($_REQUEST['elex-dp-settings-nonce']) || !wp_verify_nonce($_REQUEST['elex-dp-settings-nonce'], 'elex-dp-settings-nonce')) {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e('Security check failed!', 'eh-dynamic-pricing-discounts'); ?></p>
		</div>
		<?php
	} else {

		if (isset($_REQUEST['tab'])) {
			$active_tab = sanitize_text_field($_REQUEST['tab']);

			if ('rules_order' === $active_tab) {
				$enabled_modes_data = [];
				$enabled_modes = !empty($_REQUEST['enabled_modes']) ? $_REQUEST['enabled_modes'] : array();
			
				foreach ($enabled_modes as $key => $value) {
					$enabled_modes_data[] = sanitize_text_field($key);
				}
			
				$saved_data['product_rules_on_off'] = in_array('product_rules', $enabled_modes_data) ? 'enable' : 'disable';
				$saved_data['combinational_rules_on_off'] = in_array('combinational_rules', $enabled_modes_data) ? 'enable' : 'disable';
				$saved_data['category_rules_on_off'] = in_array('category_rules', $enabled_modes_data) ? 'enable' : 'disable';
				$saved_data['cat_comb_rules_on_off'] = in_array('cat_combinational_rules', $enabled_modes_data) ? 'enable' : 'disable';
				$saved_data['cart_rules_on_off'] = in_array('cart_rules_on_off', $enabled_modes_data) ? 'enable' : 'disable';
				$saved_data['buy_and_get_free_rules_on_off'] = in_array('buy_and_get_free_rules', $enabled_modes_data) ? 'enable' : 'disable';
				$saved_data['BOGO_category_rules_on_off'] = in_array('BOGO_category_rules', $enabled_modes_data) ? 'enable' : 'disable';
				$saved_data['bogo_tag_rules_on_off'] = in_array('bogo_tag_rules', $enabled_modes_data) ? 'enable' : 'disable';
				$saved_data['tag_rules_on_off'] = in_array('tag_rules', $enabled_modes_data) ? 'enable' : 'disable';
				$saved_data['execution_order'] = $enabled_modes_data;
				
				$rules_modes_order = !empty($_REQUEST['rules_modes_order']) ? $_REQUEST['rules_modes_order'] : array(
					'product_rules',
					'combinational_rules',
					'cat_combinational_rules',
					'category_rules',
					'cart_rules',
					'buy_get_free_rules',
					'BOGO_category_rules',
					'tag_rules',
					'bogo_tag_rules'
				);
				// Sanitize each element in the array
				$sanitized_rules_modes_order = array_map('sanitize_text_field', $rules_modes_order);
				$saved_data['rules_modes_order'] = $sanitized_rules_modes_order;

			} elseif ('other_options' === $active_tab) {
				$saved_data['price_table_on_off'] = !empty($_REQUEST['price_table_on_off']) ? sanitize_text_field($_REQUEST['price_table_on_off']) : 'disable';
				$saved_data['xa_product_add_on_option'] = !empty($_REQUEST['xa_product_add_on_option']) ? sanitize_text_field($_REQUEST['xa_product_add_on_option']) : 'disable';
				$saved_data['offer_table_on_off'] = 'disable'; // Hardcoded 'disable'
				$saved_data['auto_add_free_product_on_off'] = ''; // Empty for now, modify as needed
				$saved_data['pricing_table_qnty_shrtcode'] = !empty($_REQUEST['pricing_table_qnty_shrtcode']) ? sanitize_text_field($_REQUEST['pricing_table_qnty_shrtcode']) : 'nos.';
				$saved_data['pricing_table_position'] = !empty($_REQUEST['pricing_table_position']) ? sanitize_text_field($_REQUEST['pricing_table_position']) : 'woocommerce_before_add_to_cart_button';
				$saved_data['offer_table_position'] = !empty($_REQUEST['offer_table_position']) ? sanitize_text_field($_REQUEST['offer_table_position']) : 'woocommerce_before_add_to_cart_button';
				$saved_data['show_discount_in_line_item'] = !empty($_REQUEST['show_discount_in_line_item']) ? sanitize_text_field($_REQUEST['show_discount_in_line_item']) : 'yes';
				$saved_data['disable_shop_page_calculation'] = !empty($_REQUEST['disable_shop_page_calculation']) ? sanitize_text_field($_REQUEST['disable_shop_page_calculation']) : 'no';
				$saved_data['disable_product_page_calculation'] = !empty($_REQUEST['disable_product_page_calculation']) ? sanitize_text_field($_REQUEST['disable_product_page_calculation']) : 'no';
				$saved_data['show_on_sale'] = !empty($_REQUEST['show_on_sale']) ? sanitize_text_field($_REQUEST['show_on_sale']) : 'yes';
				$saved_data['discount_over_price_including_tax'] = !empty($_REQUEST['discount_over_price_including_tax']) ? sanitize_text_field($_REQUEST['discount_over_price_including_tax']) : 'yes';
				$saved_data['mode'] = !empty($_REQUEST['mode']) ? sanitize_text_field($_REQUEST['mode']) : 'first_match';

			} elseif ('reset_rules' === $active_tab) {
				$saved_data = array(
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
					'rules_modes_order' => array('product_rules', 'combinational_rules', 'cat_combinational_rules', 'category_rules', 'cart_rules', 'buy_get_free_rules', 'BOGO_category_rules', 'tag_rules', 'bogo_tag_rules'),
				);

				update_option('elex_dp_allowed_roles_to_show_pricing_table', array());
				$dummy_option = array(
					'product_rules' => array(),
					'combinational_rules' => array(),
					'cat_combinational_rules' => array(),
					'category_rules' => array(),
					'cart_rules' => array(),
					'buy_get_free_rules' => array(),
					'BOGO_category_rules' => array(),
					'bogo_tag_rules' => array(),
					'tag_rules' => array(),
				);

				update_option('xa_dp_rules', $dummy_option);
			}

			update_option('xa_dynamic_pricing_setting', $saved_data);
			wp_safe_redirect(add_query_arg(array('page' => 'dp-settings-page', 'tab' => $active_tab), admin_url('admin.php')));
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e('Saved Successfully', 'eh-dynamic-pricing-discounts'); ?></p>
			</div>
		<?php
		} else {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e('Please Enter All Fields!! Then Save', 'eh-dynamic-pricing-discounts'); ?></p>
			</div>
			<?php
		}
	}
}
