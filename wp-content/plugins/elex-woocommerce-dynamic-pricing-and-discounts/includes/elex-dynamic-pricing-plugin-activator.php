<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.xadapter.com
 * @since      1.0.0
 *
 * @package    xa_dynamic_pricing_plugin
 * @subpackage xa_dynamic_pricing_plugin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    xa_dynamic_pricing_plugin
 * @subpackage xa_dynamic_pricing_plugin/includes
 * @author     Your Name <email@example.com>
 */
class Elex_dynamic_pricing_plugin_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		//Updating xa_dp_rules in options
		$dummy_option = array('product_rules' => array(), 'combinational_rules' => array(), 'cat_combinational_rules' => array(), 'category_rules' => array(), 'cart_rules' => array(), 'buy_get_free_rules' => array() ,'BOGO_category_rules'=>array(), 'bogo_tag_rules' =>array(), 'tag_rules' => array());
		$prev_rules   = get_option('xa_dp_rules', $dummy_option);
		foreach ($prev_rules as $rule_type => $rules) {
			$reordered_rules = [];
			$i = 1;
			if ($rules) {
				foreach ($rules as $rule) {
					$reordered_rules[$i] = $rule;
					$i++;
				}
			}
			$prev_rules[$rule_type] = $reordered_rules;
		}
		try {
			$prev_rules = array(
				'product_rules' => ( isset($prev_rules['product_rules']) && !empty($prev_rules['product_rules']) ) ? $prev_rules['product_rules'] : array(),
				'combinational_rules' => ( isset($prev_rules['combinational_rules']) && !empty($prev_rules['combinational_rules']) ) ? $prev_rules['combinational_rules'] : array(),
				'cat_combinational_rules' => ( isset($prev_rules['cat_combinational_rules']) && !empty($prev_rules['cat_combinational_rules']) ) ? $prev_rules['cat_combinational_rules'] : array(),
				'category_rules' => ( isset($prev_rules['category_rules']) && !empty($prev_rules['category_rules']) ) ? $prev_rules['category_rules'] : array(),
				'cart_rules' => ( isset($prev_rules['cart_rules']) && !empty($prev_rules['cart_rules']) ) ? $prev_rules['cart_rules'] : array(),
				'buy_get_free_rules' => ( isset($prev_rules['buy_get_free_rules']) && !empty($prev_rules['buy_get_free_rules']) ) ? $prev_rules['buy_get_free_rules'] : array(),
				'BOGO_category_rules' => ( isset($prev_rules['BOGO_category_rules']) && !empty($prev_rules['BOGO_category_rules']) ) ? $prev_rules['BOGO_category_rules'] : array(),
				'bogo_tag_rules' => ( isset($prev_rules['bogo_tag_rules']) && !empty($prev_rules['bogo_tag_rules']) ) ? $prev_rules['bogo_tag_rules'] : array(),
				'tag_rules' => ( isset($prev_rules['tag_rules']) && !empty($prev_rules['tag_rules']) ) ? $prev_rules['tag_rules'] : array(),
			);
			update_option('xa_dp_rules', $prev_rules);
			update_option('xa_dp_rules_indexing_status', true);

		} catch (Exception $e) {
			error_log(print_r($e, true));
		}
		
		//Updating elex_dp_allowed_roles_to_show_pricing_table in options
		update_option('elex_dp_allowed_roles_to_show_pricing_table', get_option('elex_dp_allowed_roles_to_show_pricing_table', array()));

		//Updating xa_dynamic_pricing_setting in options
		$enabled_modes =array(   'product_rules', 'category_rules');

		$prev_data = get_option('xa_dynamic_pricing_setting', array());
		try {
			$prev_data = array(
				'product_rules_on_off' => in_array('product_rules', $enabled_modes) ? 'enable' : 'disable',
				'combinational_rules_on_off' => 'disable',
				'category_rules_on_off' => in_array('category_rules', $enabled_modes) ? 'enable' : 'disable',
				'cat_comb_rules_on_off' => 'disable',
				'cart_rules_on_off' => 'disable',
				'buy_and_get_free_rules_on_off' => 'disable',
				'BOGO_category_rules_on_off' => 'disable',
				'bogo_tag_rules_on_off' => 'disable',
				'tag_rules_on_off' => 'disable',
				'price_table_on_off' => !empty($prev_data['price_table_on_off']) ? $prev_data['price_table_on_off'] : 'disable',
				'xa_product_add_on_option' => !empty($prev_data['xa_product_add_on_option']) ? $prev_data['xa_product_add_on_option'] : 'disable',
				'offer_table_on_off' => !empty($prev_data['offer_table_on_off']) ? $prev_data['offer_table_on_off'] : 'disable',
				'auto_add_free_product_on_off' => '',
				'pricing_table_qnty_shrtcode' => !empty($prev_data['pricing_table_qnty_shrtcode']) ? $prev_data['pricing_table_qnty_shrtcode'] : 'nos.',
				'pricing_table_position' => !empty($prev_data['pricing_table_position']) ? $prev_data['pricing_table_position'] : 'woocommerce_before_add_to_cart_button',
				'offer_table_position' => !empty($prev_data['offer_table_position']) ? $prev_data['offer_table_position'] : 'woocommerce_before_add_to_cart_button',
				'mode' => !empty($prev_data['mode']) ? $prev_data['mode'] : 'first_match',
				'show_discount_in_line_item' => !empty($prev_data['show_discount_in_line_item']) ? $prev_data['show_discount_in_line_item'] : 'yes',
				'disable_shop_page_calculation' => !empty($prev_data['disable_shop_page_calculation']) ? $prev_data['disable_shop_page_calculation'] : 'no',
				'disable_product_page_calculation' => !empty($prev_data['disable_product_page_calculation']) ? $prev_data['disable_product_page_calculation'] : 'no',
				'show_on_sale' => !empty($prev_data['show_on_sale']) ? $prev_data['show_on_sale'] : 'yes',
				'discount_over_price_including_tax' => !empty($prev_data['discount_over_price_including_tax']) ? $prev_data['discount_over_price_including_tax'] : 'yes',
				'execution_order' => !empty($prev_data['execution_order']) ? $prev_data['execution_order'] : $enabled_modes,
				'rules_modes_order' => !empty($prev_data['rules_modes_order']) ? $prev_data['rules_modes_order'] : array('product_rules',
				'combinational_rules',
				'cat_combinational_rules',
				'category_rules',
				'cart_rules',
				'buy_get_free_rules',
				'BOGO_category_rules', 
				'tag_rules', 
				'bogo_tag_rules')
			);
			
			update_option('xa_dynamic_pricing_setting', $prev_data);

		} catch (Exception $e) {
			error_log(print_r($e, true));
		}
	}

	
}
