<?php

/**
 * This Class Handles Rules Filtering
 *
 * @author Akshay
 */
class Elex_RulesValidator {


	public $execution_mode      = 'first_match';
	public $execution_order     = array('product_rules', 'category_rules');
	public $rule_based_quantity = array();
	public $for_offers_table    = false;

	/**
	 * Finds valid rules for a Product
	 *
	 * @param wc_product $product (object of product for which we need discounted price)
	 * @param integer $pid (id of product)
	 *
	 * @return array $valid_rules
	 */
	function __construct( $mode = '', $for_offers_table = false, $only_execute_this_mode = '') {
		global $xa_dp_setting;

		$this->for_offers_table = $for_offers_table;
		$this->execution_mode   = empty($mode) ? $xa_dp_setting['mode'] : $mode;
		$this->execution_order  = empty($only_execute_this_mode) ? ( isset($xa_dp_setting['execution_order']) ? $xa_dp_setting['execution_order'] : array(
			'product_rules',
			'category_rules'
		) ) : array($only_execute_this_mode);
	}
	/**
	 * Function which converts product and category id's based on current language selected by user
	 */

	public function elex_dp_getValidRulesForProduct( $product, $pid = null, $current_quantity = 1, $price = 0, $weight = 0) {
		if (empty($pid)) {
			$pid = elex_dp_get_pid($product);
		}
		if (!empty($pid)) {
			switch ($this->execution_mode) {
				case 'first_match':
					return $this->elex_dp_getFirstMatchedRule($product, $pid, $current_quantity, $price, $weight);
				case 'best_discount':
					return $this->elex_dp_getBestMatchedRules($product, $pid, $current_quantity, $price, $weight);
				case 'all_match':
					return $this->elex_dp_getAllMatchedRules($product, $pid, $current_quantity, $price, $weight);
				default:
					return false;
			}
		}
		return false;
	}


	function elex_dp_getFirstMatchedRule( $product, $pid, $current_quantity = 1, $price = 0, $weight = 0) {
		global $xa_dp_rules, $xa_first_match_rule_executed;
		//if(!$xa_first_match_rule_executed)
		{
		foreach ($this->execution_order as $rule_type) {
			$rules = !empty($xa_dp_rules[$rule_type]) ? $xa_dp_rules[$rule_type] : array();
			foreach ($rules as $rule_no => $rule) {
				//print_r($rule_type.'->'.$rule_no." pid=".$pid);
				$rule['rule_no']   = $rule_no;
				$rule['rule_type'] = $rule_type;
				if ($this->elex_dp_checkRuleApplicableForProduct($rule, $rule_type, $product, $pid, $current_quantity, $price, $weight) === true) {
					if ($rule_type == 'product_rules') {
						$xa_first_match_rule_executed = true;
					}
					//error_log('type='.$rule_type.' ruleno='.$rule_no.' pid='.$pid);
					return array($rule_type . ':' . $rule_no => $rule);
				}
			}
		}
		}
		return array();
	}

	function elex_dp_getAllMatchedRules( $product, $pid, $current_quantity = 1, $price = 0, $weight = 0) {
		global $xa_dp_rules;
		$valid_rules = array();
		foreach ($this->execution_order as $rule_type) {
			$rules = !empty($xa_dp_rules[$rule_type]) ? $xa_dp_rules[$rule_type] : array();
			if (!empty($rules)) {

				foreach ($rules as $rule_no => $rule) {
					//error_log($rule_type.'->'.$rule_no." pid=".$pid);
					$rule['rule_no']   = $rule_no;
					$rule['rule_type'] = $rule_type;
					if ($this->elex_dp_checkRuleApplicableForProduct($rule, $rule_type, $product, $pid, $current_quantity, $price, $weight) === true) {
						//error_log('type='.$rule_type.' ruleno='.$rule_no.' pid='.$pid);
						$valid_rules[$rule_type . ':' . $rule_no] = $rule;
					}
				}
			}
		}
		return $valid_rules;
	}

	function elex_dp_getBestMatchedRules( $product, $pid, $current_quantity = 1, $price = 0, $weight = 0) {
		global $xa_dp_rules;
		$valid_rules = array();
		$max_price   = PHP_INT_MIN;
		$_product = wc_get_product( $pid );
		$product_price = $_product->get_regular_price();
		foreach ($this->execution_order as $rule_type) {
			$rules = !empty($xa_dp_rules[$rule_type]) ? $xa_dp_rules[$rule_type] : array();
			if (!empty($rules)) {
				foreach ($rules as $rule_no => $rule) {
					$rule['rule_no']   = $rule_no;
					$rule['rule_type'] = $rule_type;
					if ($this->elex_dp_checkRuleApplicableForProduct($rule, $rule_type, $product, $pid, $current_quantity, $product_price, $weight) === true) {
						if (!empty($rule['calculated_discount']) && $max_price < $rule['calculated_discount']) {   //error_log('type='.$rule_type.' ruleno='.$rule_no.' pid='.$pid);
							$max_price   = $rule['calculated_discount'];
							$valid_rules = array($rule_type . ':' . $rule_no => $rule);
						}
					}
				}
			}
		}
		return $valid_rules;
	}

	public function elex_dp_checkRuleApplicableForProduct( &$rule = null, $rule_type = '', $product = null, $pid = null, $current_quantity = 1, $price = 0, $weight = 0) {
		if (apply_filters('eha_dp_skip_product', false, $pid, $rule, $rule_type) != false) {
			return false;
		}
		if (!empty($rule) && !empty($rule_type) && !empty($pid)) {
			switch ($rule_type) {
				case 'product_rules':
					$valid = $this->elex_dp_checkProductRuleApplicableForProduct($rule, $product, $pid, $current_quantity, $price, $weight);
					break;
				case 'category_rules':
					$valid = $this->elex_dp_checkCategoryRuleApplicableForProduct($rule, $product, $pid, $current_quantity, $price, $weight);
					break;
				case 'tag_rules':
					$valid = $this->elex_dp_checkTagRuleApplicableForProduct($rule, $product, $pid, $current_quantity, $price, $weight);
					break;
				case 'cart_rules':
					$valid = $this->elex_dp_checkCartRuleApplicableForProduct($rule, $product, $pid, $current_quantity, $price, $weight);
					break;
				case 'combinational_rules':
					$valid = $this->elex_dp_checkCombinationalRuleApplicableForProduct($rule, $product, $pid, $current_quantity, $price, $weight);
					break;
				case 'cat_combinational_rules':
					$valid = $this->elex_dp_checkCategoryCombinationalRuleApplicableForProduct($rule, $product, $pid, $current_quantity, $price, $weight);
					break;
				case 'buy_get_free_rules':
					$valid = $this->elex_dp_checkBOGO_RuleApplicableForProduct($rule, $product, $pid, $current_quantity, $price, $weight);
					break;
				case 'BOGO_category_rules':
					$valid = $this->elex_dp_checkBOGO_category_RuleApplicableForProduct($rule, $product, $pid, $current_quantity, $price, $weight);
					break;
				case 'bogo_tag_rules':
					$valid = $this->elex_dp_checkbogo_tag_rulesApplicableForProduct($rule, $product, $pid, $current_quantity, $price, $weight);
					break;
			}
			global $customer;
			if (( !empty($rule['prev_order_count']) || !empty($rule['prev_order_total_amt']) ) && !empty($customer)) {
				$order_count = elex_dp_is_wc_version_gt_eql('2.7') ? $customer->get_order_count() : wc_get_customer_order_count($customer->id);
				$total_spent = elex_dp_is_wc_version_gt_eql('2.7') ? $customer->get_total_spent() : wc_get_customer_total_spent($customer->id);
				//error_log('order_count='.$order_count." total spent=".$total_spent);
				if (!empty($rule['prev_order_count']) && (int) $rule['prev_order_count'] > $order_count) {
					return false;
				}
				if (!empty($rule['prev_order_total_amt']) && (float) $rule['prev_order_total_amt'] > $total_spent) {
					return false;
				}
			}
			global $current_user;
			if (!empty($rule['email_ids'])) {
				$customer_email = $current_user->user_email;
				$emails         = explode(',', $rule['email_ids']);
				if (empty($customer_email) || !in_array($customer_email, $emails)) {
					return false;
				}
			}
			return $valid;
		}
		return false;
	}

	function elex_dp_checkBOGO_RuleApplicableForProduct( &$rule = null, $product = null, $pid = null, $current_quantity = 1, $price = 0, $weight = 0) {
		$rule['purchased_product_id'] = elex_dp_WPML_Compatible_ids($rule['purchased_product_id'], 'product', true);
		$rule['free_product_id']      = elex_dp_WPML_Compatible_ids($rule['free_product_id'], 'product', true);
		global $xa_cart_quantities, $xa_cart_price, $xa_dp_setting;

		//If free product is selected
		if (!isset($rule['set_free_option']) || ( isset($rule['set_free_option']) && $rule['set_free_option'] == 'select_product' )) {

			if (empty($rule['purchased_product_id']) || empty($rule['free_product_id'])) {
				return false;
			}
			if (in_array($pid, array_keys($rule['purchased_product_id'])) && in_array($pid, array_keys($rule['free_product_id'])) && $current_quantity == 1 && $xa_dp_setting['auto_add_free_product_on_off'] != 'on') {
				return false;
			}
			$parent_id = $pid;
			if (!empty($product) && $product->is_type('variation')) {
				$parent_id = elex_dp_is_wc_version_gt_eql('2.7') ? $product->get_parent_id() : $product->parent->id;
			}

			if (!in_array($pid, array_keys($rule['purchased_product_id'])) && !in_array($parent_id, array_keys($rule['purchased_product_id']))   && !in_array($pid, array_keys($rule['free_product_id']))) {
				return false;
			}

			if ($this->for_offers_table == true) {
				return $this->elex_dp_check_date_range_and_roles($rule, 'buy_get_free_rules');
			} // to show in offers table
			$cart_itmes = array_keys($xa_cart_quantities);
			foreach ($rule['purchased_product_id'] as $_pid => $_qnty) {
				$each_item_q = 0;
				if (!isset($xa_cart_quantities[$_pid]) || $xa_cart_quantities[$_pid] < $_qnty) {
					$_product = wc_get_product($_pid);
					if (!$product) {
						continue;
					}
					if ($_product->is_type('variable')) {
						foreach ($_product->get_children() as $cid) {
							if (in_array($cid, $cart_itmes)) {
								$each_item_q += $xa_cart_quantities[$cid];
							}
						}
						if ($each_item_q >= $_qnty) {
							continue;
						}
					}
					return false;
				}
			}
			if (( in_array($pid, array_keys($rule['purchased_product_id'])) || in_array($parent_id, array_keys($rule['purchased_product_id'])) ) || in_array($pid, array_keys($rule['free_product_id']))) {
				$dprice = 0;
				foreach ($rule['free_product_id'] as $_pid => $_qnty) {
					// Check if the product is not in the trash
					$product_status = get_post_status($_pid);
					if ($product_status === 'trash') {
						continue;
					}
					$_product = wc_get_product( $_pid );
					if ($_product) {
						$product_price = $_product->get_price();
						$price_val = !empty($product_price) ? $product_price : 0;
						$dprice   += ( $price_val * $_qnty );
					}
				}
				if ($this->execution_mode == 'best_discount') {
					$rule['calculated_discount'] = $dprice;    //to check best descount rule
				}
			}
		} else if ($rule['set_free_option'] == 'set_cheapest') {
			//If product with min price in cart is to be free

			if (empty($rule['purchased_product_id'])) {
				return false;
			}
			$parent_id = $pid;
			if (!empty($product) && $product->is_type('variation')) {
				$parent_id = elex_dp_is_wc_version_gt_eql('2.7') ? $product->get_parent_id() : $product->parent->id;
			}

			//Getting min priced product from cart
			$minvalue          = !empty($xa_cart_price) ? max($xa_cart_price) : '';
			$product_tosetfree = '';
			foreach ($xa_cart_quantities as $_pid => $_qnty) {
				$_product = wc_get_product($_pid);
				if ($_product->get_price() <= $minvalue) {
					$minvalue          = $_product->get_price();
					$product_tosetfree = $_pid;
				}
			}

			if ($pid != $product_tosetfree) {
				return false;
			}
			if (in_array($pid, array_keys($rule['purchased_product_id'])) && !( $current_quantity > 1 ) ) {
				return false;
			}
			if ($this->for_offers_table == true) {
				return $this->elex_dp_check_date_range_and_roles($rule, 'buy_get_free_rules'); // to show in offers table
			}

			$cart_itmes = array_keys($xa_cart_quantities);
			foreach ($rule['purchased_product_id'] as $_pid => $_qnty) {
				$each_item_q = 0;
				if (!isset($xa_cart_quantities[$_pid]) || $xa_cart_quantities[$_pid] < $_qnty) {
					$_product = wc_get_product($_pid);
					if (!$product) {
						continue;
					}
					if ($_product->is_type('variable')) {
						foreach ($_product->get_children() as $cid) {
							if (in_array($cid, $cart_itmes)) {
								$each_item_q += $xa_cart_quantities[$cid];
							}
						}
						if ($each_item_q >= $_qnty) {
							continue;
						}
					}
					return false;
				}
			}
			////////if free product is already in cart with exact quanitty this code will set its price as zero
			if (( in_array($pid, array_keys($rule['purchased_product_id'])) || in_array($parent_id, array_keys($rule['purchased_product_id'])) || ( $pid == $product_tosetfree ) )) {
				$dprice = 0;
				$dprice = $minvalue;
				if ($this->execution_mode == 'best_discount') {
					$rule['calculated_discount'] = $dprice;    //to check best descount rule
				}
			}
		} else {
			return false;
		}
		//checking roles and tofrom date for which rule is applicable
		return $this->elex_dp_check_date_range_and_roles($rule, 'buy_get_free_rules');
	}
	function elex_dp_checkBOGO_category_RuleApplicableForProduct( &$rule = null, $product = null, $pid = null, $current_quantity = 1, $price = 0, $weight = 0) {

		$rule['purchased_category_id'] = elex_dp_WPML_Compatible_ids($rule['purchased_category_id'], 'category', true);
		$rule['free_product_id']       = elex_dp_WPML_Compatible_ids($rule['free_product_id'], 'product', true);
		global $xa_cart_quantities, $xa_cart_price, $xa_cart_categories_items, $xa_cart_categories_units, $xa_dp_setting;
		global $xa_cart_categories;

		//If free product is selected
		if (!isset($rule['set_free_option']) || ( isset($rule['set_free_option']) && $rule['set_free_option'] == 'select_product' )) {

			if (empty($rule['purchased_category_id']) || empty($rule['free_product_id'])) {
				return false;
			}
			$parent_id          = $pid;
			$product_categories = !empty($xa_cart_categories[$pid]) ? $xa_cart_categories[$pid] : array();
			$cids               = array();
			$cat_ids            = $rule['purchased_category_id'];
			$cids               = elex_dp_WPML_Compatible_ids($cat_ids, 'category', true);
			if ($this->for_offers_table == true) {
				return $this->elex_dp_check_date_range_and_roles($rule, 'BOGO_category_rules');
			} // to show in offers table
			$add_if_not_auto = 0;
			foreach ($rule['purchased_category_id'] as $_cid => $_qnty_and_checkon) {

				$tmp1 = array_keys($rule['free_product_id']);
				if (in_array($pid, $tmp1) && in_array($_cid, $product_categories) && $xa_dp_setting['auto_add_free_product_on_off'] != 'on' && !( $xa_cart_categories_items[$_cid] > 1 ) && $current_quantity == 1) {
					$add_if_not_auto = $add_if_not_auto + 1;
				}
				$tmp     = explode(':', $_qnty_and_checkon);
				$_qnty   = !empty($tmp[0]) ? $tmp[0] : 0;
				$checkon = !empty($tmp[1]) ? $tmp[1] : 'items';
				if ($checkon == 'items' && ( !isset($xa_cart_categories_items[$_cid]) || $xa_cart_categories_items[$_cid] < ( $_qnty ) )) {
					return false;
				} elseif ($checkon == 'units' && ( !isset($xa_cart_categories_units[$_cid]) || $xa_cart_categories_units[$_cid] < $_qnty )) {
					return false;
				}
			}
			if ($add_if_not_auto > 0) {
				return false;
			}
			$dprice = 0;
			foreach ($rule['free_product_id'] as $_pid => $_qnty) {
				$_product = wc_get_product( $_pid );
				$product_price = $_product->get_price();
				$price_val = !empty($product_price) ? $product_price : 0;
				$dprice   += ( $price_val * $_qnty );
			}
			if ($this->execution_mode == 'best_discount') {
				$rule['calculated_discount'] = $dprice;    //to check best descount rule
			}

		} elseif ($rule['set_free_option'] == 'set_cheapest') {
			//If product with min price in cart is to be free

			//Getting min priced product from cart
			$minvalue          = !empty($xa_cart_price) ? max($xa_cart_price) : '';
			$product_tosetfree = '';
			$total_cart_quantity_count = 0;
			foreach ($xa_cart_quantities as $_pid => $_qnty) {
				$total_cart_quantity_count = $total_cart_quantity_count + $_qnty;
				$_product = wc_get_product($_pid);
				if ($_product->get_price() <= $minvalue) {
					$minvalue          = $_product->get_price();
					$product_tosetfree = $_pid;
				}
			}

			if (empty($rule['purchased_category_id']) || empty($product_tosetfree)) {
				return false;
			}

			$parent_id          = $pid;
			$product_categories = !empty($xa_cart_categories[$pid]) ? $xa_cart_categories[$pid] : array();
			$cids               = array();
			$cat_ids            = $rule['purchased_category_id'];
			$cids               = elex_dp_WPML_Compatible_ids($cat_ids, 'category', true);

			if ($this->for_offers_table == true) {
				return $this->elex_dp_check_date_range_and_roles($rule, 'BOGO_category_rules');
			} // to show in offers table

			$add_if_not_auto = 0;
			foreach ($rule['purchased_category_id'] as $_cid => $_qnty_and_checkon) {

				if (( $pid == $product_tosetfree ) && in_array($_cid, $product_categories) && !( $xa_cart_categories_items[$_cid] > 1 ) && !( $current_quantity > 1 )) {
					$add_if_not_auto = $add_if_not_auto + 1;
				}
				$tmp     = explode(':', $_qnty_and_checkon);
				$_qnty   = !empty($tmp[0]) ? $tmp[0] : 0;
				$checkon = !empty($tmp[1]) ? $tmp[1] : 'items';
				if ($checkon == 'items' && ( !isset($xa_cart_categories_items[$_cid]) || $xa_cart_categories_items[$_cid] < ( $_qnty ) )) {
					return false;
				} elseif ($checkon == 'units' && ( !isset($xa_cart_categories_units[$_cid]) || $xa_cart_categories_units[$_cid] < $_qnty )) {
					return false;
				}
			}
			if ($add_if_not_auto > 0) {
				return false;
			}
			$dprice = 0;
			$dprice = $minvalue;
			if ($this->execution_mode == 'best_discount') {
				$rule['calculated_discount'] = $dprice;    //to check best descount rule
			}

		} else {

			return false;
		}
		//checking roles and tofrom date for which rule is applicable

		return $this->elex_dp_check_date_range_and_roles($rule, 'BOGO_category_rules');
	}

	function elex_dp_checkbogo_tag_rulesApplicableForProduct( &$rule = null, $product = null, $pid = null, $current_quantity = 1, $price = 0, $weight = 0) {

		$rule['purchased_tag_id'] = elex_dp_WPML_Compatible_ids($rule['purchased_tag_id'], 'tag', true);
		$rule['free_product_id']  = elex_dp_WPML_Compatible_ids($rule['free_product_id'], 'product', true);
		global $xa_cart_quantities, $xa_cart_price, $xa_cart_tags_items, $xa_cart_tags_units, $xa_dp_setting, $xa_cart_tags;

		//If free product is selected
		if (!isset($rule['set_free_option']) || ( isset($rule['set_free_option']) && $rule['set_free_option'] == 'select_product' )) {

			if (empty($rule['purchased_tag_id']) || empty($rule['free_product_id'])) {
				return false;
			}
			$parent_id    = $pid;
			$product_tags = !empty(xa_get_tag_ids($pid)) ? xa_get_tag_ids($pid) : array();
			$cids         = array();
			$tag_ids      = $rule['purchased_tag_id'];
			$cids         = elex_dp_WPML_Compatible_ids($tag_ids, 'tag', true);
			if ($this->for_offers_table == true) {
				return $this->elex_dp_check_date_range_and_roles($rule, 'bogo_tag_rules');
			} // to show in offers table
			$add_if_not_auto = 0;
			foreach ($rule['purchased_tag_id'] as $_cid => $_qnty_and_checkon) {

				$tmp1 = array_keys($rule['free_product_id']);
				if (in_array($pid, $tmp1) && in_array($_cid, $product_tags) && $xa_dp_setting['auto_add_free_product_on_off'] != 'on' && !( $xa_cart_tags_items[$_cid] > 1 ) && $current_quantity == 1) {
					$add_if_not_auto = $add_if_not_auto + 1;
				}
				$tmp     = explode(':', $_qnty_and_checkon);
				$_qnty   = !empty($tmp[0]) ? $tmp[0] : 0;
				$checkon = !empty($tmp[1]) ? $tmp[1] : 'items';
				if ($checkon == 'items' && ( !isset($xa_cart_tags_items[$_cid]) || $xa_cart_tags_items[$_cid] < ( $_qnty ) )) {
					return false;
				} elseif ($checkon == 'units' && ( !isset($xa_cart_tags_units[$_cid]) || $xa_cart_tags_units[$_cid] < $_qnty )) {
					return false;
				}
			}
			if ($add_if_not_auto > 0) {
				return false;
			}
			$dprice = 0;
			foreach ($rule['free_product_id'] as $_pid => $_qnty) {
				$_product = wc_get_product( $_pid );
				$product_price = $_product->get_price();
				$price_val = !empty($product_price) ? $product_price : 0;
				$dprice   += ( $price_val * $_qnty );
			}
			if ($this->execution_mode == 'best_discount') {
				$rule['calculated_discount'] = $dprice;    //to check best descount rule
			}
		} elseif ($rule['set_free_option'] == 'set_cheapest') {
			//If product with min price in cart is to be free

			//Getting min priced product from cart
			$minvalue          = !empty($xa_cart_price) ? max($xa_cart_price) : '';
			$product_tosetfree = '';
			foreach ($xa_cart_quantities as $_pid => $_qnty) {
				$_product = wc_get_product($_pid);
				if ($_product->get_price() <= $minvalue) {
					$minvalue          = $_product->get_price();
					$product_tosetfree = $_pid;
				}
			}

			if (empty($rule['purchased_tag_id']) || empty($product_tosetfree)) {
				return false;
			}

			$parent_id    = $pid;
			$product_tags = !empty(xa_get_tag_ids($pid)) ? xa_get_tag_ids($pid) : array();
			$cids         = array();
			$tag_ids      = $rule['purchased_tag_id'];
			$cids         = elex_dp_WPML_Compatible_ids($tag_ids, 'tag', true);

			if ($this->for_offers_table == true) {
				return $this->elex_dp_check_date_range_and_roles($rule, 'bogo_tag_rules');
			} // to show in offers table

			$add_if_not_auto = 0;
			foreach ($rule['purchased_tag_id'] as $_cid => $_qnty_and_checkon) {

				if (( $pid == $product_tosetfree ) && in_array($_cid, $product_tags) && !( $xa_cart_tags_items[$_cid] > 1 ) && !( $current_quantity > 1 )) {
					$add_if_not_auto = $add_if_not_auto + 1;
				}
				$tmp     = explode(':', $_qnty_and_checkon);
				$_qnty   = !empty($tmp[0]) ? $tmp[0] : 0;
				$checkon = !empty($tmp[1]) ? $tmp[1] : 'items';
				if ($checkon == 'items' && ( !isset($xa_cart_tags_items[$_cid]) || $xa_cart_tags_items[$_cid] < ( $_qnty ) )) {
					return false;
				} elseif ($checkon == 'units' && ( !isset($xa_cart_tags_units[$_cid]) || $xa_cart_tags_units[$_cid] < $_qnty )) {
					return false;
				}
			}
			if ($add_if_not_auto > 0) {
				return false;
			}
			$dprice = 0;
			$dprice = $minvalue;
			if ($this->execution_mode == 'best_discount') {
				$rule['calculated_discount'] = $dprice;    //to check best descount rule
			}
		} else {

			return false;
		}

		//checking roles and tofrom date for which rule is applicable

		return $this->elex_dp_check_date_range_and_roles($rule, 'bogo_tag_rules');
	}

	function elex_dp_checkCategoryCombinationalRuleApplicableForProduct( &$rule = null, $product = null, $pid = null, $current_quantity = 1, $price = 0, $weight = 0) {

		global $xa_cart_quantities;
		global $xa_cart_categories;
		$total_units        = 0;
		$product_categories = !empty($xa_cart_categories[$pid]) ? $xa_cart_categories[$pid] : array();
		//error_log("rule cat=".print_r($rule['cat_id'],true)." current prod cat=".print_r($product_categories,true));
		$rule['cat_id'] = elex_dp_WPML_Compatible_ids($rule['cat_id'], 'category', true);
		$tmp            = array_keys($rule['cat_id']);
		$tmp            = array_intersect($tmp, $product_categories);
		if (empty($rule['cat_id']) || count($rule['cat_id']) == 0 || empty($tmp)) {
			return false;
		}

		if ($this->for_offers_table == true) {
			return $this->elex_dp_check_date_range_and_roles($rule, 'cat_combinational_rules');
		} // to show in offers table

		$total_items_of_this_category_in_cart     = array();
		$total_all_units_of_this_category_in_cart = array();
		foreach ($xa_cart_categories as $_pid => $_categories) {
			$cat_id = array_intersect(array_keys($rule['cat_id']), $_categories);
			if (!empty($cat_id)) {
				if (!isset($total_items_of_this_category_in_cart[current($cat_id)])) {
					$total_items_of_this_category_in_cart[current($cat_id)]     = 0;
					$total_all_units_of_this_category_in_cart[current($cat_id)] = 0;
				}
				$total_items_of_this_category_in_cart[current($cat_id)]++;
				$total_all_units_of_this_category_in_cart[current($cat_id)] += !empty($xa_cart_quantities[$_pid]) ? $xa_cart_quantities[$_pid] : 0;
			}
		}
		foreach ($rule['cat_id'] as $cat_id => $qnty) {
			if (empty($total_all_units_of_this_category_in_cart[$cat_id]) || $total_all_units_of_this_category_in_cart[$cat_id] < $qnty) {
				return false;
			} else {
				$total_units = !empty($total_all_units_of_this_category_in_cart[$cat_id]) ? $total_all_units_of_this_category_in_cart[$cat_id] : 1;
			}
		}
		$this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']] = $total_units;   // for adjustment
		//to check best descount rule
		if ($this->execution_mode == 'best_discount') {
			$new_price = $this->elex_dp_calculate_discount($price, $rule['rule_no'], $rule, $pid, $current_quantity, true);
			$_product = wc_get_product($pid);
			$old_price = $_product->get_price();
			$rule['calculated_discount'] = ( (float) $old_price - (float) $new_price ) * $current_quantity;
		}
		//checking roles and tofrom date for which rule is applicable
		return $this->elex_dp_check_date_range_and_roles($rule, 'cat_combinational_rules');
	}

	function elex_dp_checkCategoryRuleApplicableForProduct( &$rule = null, $product = null, $pid = null, $current_quantity = 1, $price = 0, $weight = 0) {

		global $xa_cart_quantities;
		global $xa_cart_weight;
		global $xa_cart_price;
		global $xa_cart_categories;
		$min = ( empty($rule['min']) == true ) ? 1 : $rule['min'];
		$max = ( empty($rule['max']) == true ) ? 999999999 : $rule['max'];

		if ($max < $min && $max != 0) {
			return false;
		}
		//if pid is selected in this rule

		$cids    = array();
		$cat_ids = $rule['category_id'];
		if (!is_array($cat_ids)) {
			$cat_ids = array($cat_ids);
		}
		foreach ($cat_ids as $_cid) {
			$cids[] = elex_dp_WPML_Compatible_ids($_cid, 'category');
		}
		$tmp                = elex_dp_get_category_ids($pid);
		$product_categories = !empty($tmp) ? $tmp : array();
		$matched            = array_intersect($cids, $product_categories);
		if (empty($cids) || empty($matched)) {
			return false;
		}
		$rule['selected_cids'] = $matched;
		if ($this->for_offers_table == true) {
			return $this->elex_dp_check_date_range_and_roles($rule, 'category_rules');
		} // to show in offers table

		$total_items_of_this_category_in_cart     = 0;
		$total_all_units_of_this_category_in_cart = 0;
		$total_weight_of_this_category            = 0;
		$total_price_of_this_category             = 0;
		if (is_shop() || is_product_category() || is_product() || is_product_tag()) {
			$current_quantity++;
			if (empty($xa_cart_quantities[$pid])) {
				$total_items_of_this_category_in_cart++;
			}
			$total_all_units_of_this_category_in_cart++;
			$total_weight_of_this_category += !empty($xa_cart_weight[$pid]) ? $xa_cart_weight[$pid] : (float) $product->get_weight();
			$total_price_of_this_category  += !empty($xa_cart_price[$pid]) ? $xa_cart_price[$pid] : (float) $price;
		}
		foreach ($xa_cart_categories as $_pid => $_categories) {
			$match = array_intersect($matched, $_categories);
			if (!empty($match)) {
				$total_items_of_this_category_in_cart++;
				$qnty = !empty($xa_cart_quantities[$_pid]) ? $xa_cart_quantities[$_pid] : 1;
				if (!empty($xa_cart_quantities[$_pid])) {
					$total_all_units_of_this_category_in_cart += (float) $qnty;
				}
				if (!empty($xa_cart_weight[$_pid])) {
					$total_weight_of_this_category += (float) ( $qnty * $xa_cart_weight[$_pid] );
				}
				if (!empty($xa_cart_price[$_pid])) {
					$total_price_of_this_category += (float) ( $qnty * $xa_cart_price[$_pid] );
				}
			}
		}
		if ($total_items_of_this_category_in_cart == 0) {
			$total_items_of_this_category_in_cart     = 1;
			$total_all_units_of_this_category_in_cart = 1;
			$total_weight_of_this_category            = (float) $product->get_weight();
			$total_price_of_this_category             = $price;
		}
		$this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']] = $total_all_units_of_this_category_in_cart;   // for adjustment
		//error_log('total units=' . $total_all_units_of_this_category_in_cart . " total items=" . $total_items_of_this_category_in_cart);
		//error_log('total price=' . $total_price_of_this_category . " total weights=" . $total_weight_of_this_category);
		if ($rule['check_on'] == 'TotalQuantity' && ( $total_all_units_of_this_category_in_cart < $min || $total_all_units_of_this_category_in_cart > $max || empty($total_all_units_of_this_category_in_cart) )) {
			return false;
		} elseif ($rule['check_on'] == 'Quantity' && ( $total_items_of_this_category_in_cart < $min || $total_items_of_this_category_in_cart > $max || empty($total_items_of_this_category_in_cart) )) {
			return false;
		} elseif ($rule['check_on'] == 'Weight' && ( $total_weight_of_this_category < $min || $total_weight_of_this_category > $max || empty($total_weight_of_this_category) )) {
			return false;
		} elseif ($rule['check_on'] == 'Price' && ( $total_price_of_this_category < $min || $total_price_of_this_category > $max || empty($total_price_of_this_category) )) {
			return false;
		}

		//to check best descount rule
		if ($this->execution_mode == 'best_discount') {
			$new_price = $this->elex_dp_calculate_discount($price, $rule['rule_no'], $rule, $pid, $current_quantity, true);
			$_product = wc_get_product($pid);
			$old_price = $_product->get_price();
			$rule['calculated_discount'] = ( (float) $old_price - (float) $new_price ) * $current_quantity;
		}

		//checking roles and tofrom date for which rule is applicable
		return $this->elex_dp_check_date_range_and_roles($rule, 'category_rules');
	}

	function elex_dp_checkTagRuleApplicableForProduct( &$rule = null, $product = null, $pid = null, $current_quantity = 1, $price = 0, $weight = 0) {

		global $xa_cart_quantities;
		global $xa_cart_weight;
		global $xa_cart_price;
		global $xa_cart_tags;
		$min = ( empty($rule['min']) == true ) ? 1 : $rule['min'];
		$max = ( empty($rule['max']) == true ) ? 999999999 : $rule['max'];

		if ($max < $min && $max != 0) {
			return false;
		}
		//if pid is selected in this rule

		$tids    = array();
		$tag_ids = $rule['tag_id'];
		if (!is_array($tag_ids)) {
			$tag_ids = array($tag_ids);
		}
		foreach ($tag_ids as $_tid) {
			$tids[] = elex_dp_WPML_Compatible_ids($_tid, 'tag');
		}
		$tmp          = xa_get_tag_ids($pid);
		$product_tags = !empty($tmp) ? $tmp : array();
		$matched      = array_intersect($tids, $product_tags);
		if (empty($tids) || empty($matched)) {
			return false;
		}
		$rule['selected_tids'] = $matched;
		if ($this->for_offers_table == true) {
			return $this->elex_dp_check_date_range_and_roles($rule, 'tag_rules');
		} // to show in offers table

		$total_items_of_this_tag_in_cart     = 0;
		$total_all_units_of_this_tag_in_cart = 0;
		$total_weight_of_this_tag            = 0;
		$total_price_of_this_tag             = 0;
		if (is_shop() || is_product_category() || is_product() || is_product_tag()) {
			$current_quantity++;
			if (empty($xa_cart_quantities[$pid])) {
				$total_items_of_this_tag_in_cart++;
			}
			$total_all_units_of_this_tag_in_cart++;
			$total_weight_of_this_tag += !empty($xa_cart_weight[$pid]) ? $xa_cart_weight[$pid] : (float) $product->get_weight();
			$total_price_of_this_tag  += !empty($xa_cart_price[$pid]) ? $xa_cart_price[$pid] : (float) $price;
		}
		foreach ($xa_cart_tags as $_pid => $_tags) {
			$match = array_intersect($matched, $_tags);
			if (!empty($match)) {
				$total_items_of_this_tag_in_cart++;
				$qnty = !empty($xa_cart_quantities[$_pid]) ? $xa_cart_quantities[$_pid] : 1;
				if (!empty($xa_cart_quantities[$_pid])) {
					$total_all_units_of_this_tag_in_cart += (int) $qnty;
				}
				if (!empty($xa_cart_weight[$_pid])) {
					$total_weight_of_this_tag += (int) ( $qnty * $xa_cart_weight[$_pid] );
				}
				if (!empty($xa_cart_price[$_pid])) {
					$total_price_of_this_tag += (int) ( $qnty * $xa_cart_price[$_pid] );
				}
			}
		}
		if ($total_items_of_this_tag_in_cart == 0) {
			$total_items_of_this_tag_in_cart     = 1;
			$total_all_units_of_this_tag_in_cart = 1;
			$total_weight_of_this_tag            = (float) $product->get_weight();
			$total_price_of_this_tag             = $price;
		}
		$this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']] = $total_all_units_of_this_tag_in_cart;   // for adjustment
		//error_log('total units=' . $total_all_units_of_this_tag_in_cart . " total items=" . $total_items_of_this_tag_in_cart);
		//error_log('total price=' . $total_price_of_this_tag . " total weights=" . $total_weight_of_this_tag);
		if ($rule['check_on'] == 'TotalQuantity' && ( $total_all_units_of_this_tag_in_cart < $min || $total_all_units_of_this_tag_in_cart > $max || empty($total_all_units_of_this_tag_in_cart) )) {
			return false;
		} elseif ($rule['check_on'] == 'Quantity' && ( $total_items_of_this_tag_in_cart < $min || $total_items_of_this_tag_in_cart > $max || empty($total_items_of_this_tag_in_cart) )) {
			return false;
		} elseif ($rule['check_on'] == 'Weight' && ( $total_weight_of_this_tag < $min || $total_weight_of_this_tag > $max || empty($total_weight_of_this_tag) )) {
			return false;
		} elseif ($rule['check_on'] == 'Price' && ( $total_price_of_this_tag < $min || $total_price_of_this_tag > $max || empty($total_price_of_this_tag) )) {
			return false;
		}

		//to check best descount rule
		if ($this->execution_mode == 'best_discount') {
			$new_price = $this->elex_dp_calculate_discount($price, $rule['rule_no'], $rule, $pid, $current_quantity, true);
			$_product = wc_get_product($pid);
			$old_price = $_product->get_price();
			$rule['calculated_discount'] = ( (float) $old_price - (float) $new_price ) * $current_quantity;
		}

		//checking roles and tofrom date for which rule is applicable
		return $this->elex_dp_check_date_range_and_roles($rule, 'tag_rules');
	}

	function elex_dp_checkCartRuleApplicableForProduct( &$rule = null, $product = null, $pid = null, $current_quantity = 1, $price = 0, $weight = 0) {

		global $xa_cart_quantities;
		global $xa_cart_weight;
		global $xa_cart_price;
		$min  = ( empty($rule['min']) == true ) ? 1 : $rule['min'];
		$max  = ( empty($rule['max']) == true ) ? 999999999 : $rule['max'];
		$attr = $product->get_attributes();
		$attribute_data = array();
		foreach ($attr as $attribute_name => $attribute) {
			if (is_object($attribute)) {
				$attribute_values = $attribute->get_options();

				if (!empty($attribute_values)) {
					$term_slugs = array();
					foreach ($attribute_values as $term_id) {
						$term = get_term($term_id, $attribute_name);
						if ($term && !is_wp_error($term)) {
							$term_slugs[] = $term->slug;
						}
					}
					$attribute_data[$attribute_name] = $term_slugs;
				}
			} else if (is_string($attribute)) {
				$attribute_data[$attribute_name] = (array) $attribute;

			}
		}

		if (!empty($rule['attributes']) && !empty($rule['attributes']['at_taxonomy'])) {
			$valid = false;
			foreach ($rule['attributes']['at_taxonomy'] as $key => $tax_slug) {
				$attr_val_slug = !empty($rule['attributes']['at_val'][$key]) ? $rule['attributes']['at_val'][$key] : '';
				if (isset($attribute_data[$tax_slug]) && in_array($attr_val_slug, $attribute_data[$tax_slug])) {
					$valid = true;
				} elseif (!empty($rule['attributes_mode']) && $rule['attributes_mode'] == 'and') {
					return false;
				}
			}
			if ($valid == false) {
				return $valid;
			}
		}
		if (!empty($rule['allowed_payment_methods'])) {
			$chosen_payment_method = WC()->session ? WC()->session->get('chosen_payment_method') : '';
			if (empty($chosen_payment_method)) {
				return false;
			}
			$selected_methods       = !empty($rule['allowed_payment_methods']) ? $rule['allowed_payment_methods'] : array();

			if (!in_array($chosen_payment_method, $selected_methods)) {
				return false;
			}
		}
		if (!empty($rule['allowed_shipping_methods'])) {
			$chosen_methods  = WC()->session ? WC()->session->get('chosen_shipping_methods') : '';
			$chosen_shipping = !empty($chosen_methods[0]) ? $chosen_methods[0] : '';
			$chosen_shipping = explode(':', $chosen_shipping);
			$chosen_shipping = !empty($chosen_shipping[0]) ? $chosen_shipping[0] : '';
			if (empty($chosen_shipping)) {
				return false;
			}
			$selected_methods = !empty($rule['allowed_shipping_methods']) ? $rule['allowed_shipping_methods'] : array();
			if (!in_array($chosen_shipping, $selected_methods)) {
				return false;
			}
		}

		/* Author: RavikumarMG 4/12/2017
		 * Getting minimum and maximum stock quantity values
		 * START
		 */
		$stock           = $product->get_stock_quantity(); // getting product stock quantity
		$min_stock_limit = !empty($rule['minimum_stock_limit']) ? $rule['minimum_stock_limit'] : 0;
		$max_stock_limit = !empty($rule['maximum_stock_limit']) ? $rule['maximum_stock_limit'] : 0;
		if (!empty($min_stock_limit) && $stock < $min_stock_limit) {
			return false;
		}
		if (!empty($max_stock_limit) && $stock > $max_stock_limit) {
			return false;
		}
		/*END*/

		if ($max < $min && $max != 0) {
			return false;
		}

		if ($this->for_offers_table == true) {
			return $this->elex_dp_check_date_range_and_roles($rule, 'cart_rules');
		} // to show in offers table
		//if pid is selected in this rule

		if (is_cart() && ( empty($pid) || !in_array($pid, array_keys($xa_cart_quantities)) )) {
			return false;
		}

		$total_items_in_cart     = 0;
		$total_all_units_in_cart = 0;
		$total_weight_in_cart    = 0;
		$total_price_in_cart     = 0;
		if (is_shop() || is_product_category() || is_product() || is_product_tag()) {
			$current_quantity++;
			if (empty($xa_cart_quantities[$pid])) {
				$total_items_in_cart++;
			}
			$total_all_units_in_cart++;
			$total_weight_in_cart += !empty($xa_cart_weight[$pid]) ? (float) $xa_cart_weight[$pid] : (float) $product->get_weight();
			$total_price_in_cart  += !empty($xa_cart_price[$pid]) ? $xa_cart_price[$pid] : (float) $price;
		}
		foreach ($xa_cart_quantities as $_pid => $_qnty) {
			$total_items_in_cart++;
			if (!empty($_qnty)) {
				$total_all_units_in_cart += $_qnty;
				if (!empty($xa_cart_weight[$_pid])) {
					$total_weight_in_cart += ( $_qnty * $xa_cart_weight[$_pid] );
				}
				if (!empty($xa_cart_price[$_pid])) {
					$total_price_in_cart += ( $_qnty * $xa_cart_price[$_pid] );
				}
			}
		}
		//error_log('total units=' . $total_all_units_of_this_category_in_cart . " total items=" . $total_items_of_this_category_in_cart);
		//error_log('total price=' . $total_price_of_this_category . " total weights=" . $total_weight_of_this_category);
		if ($rule['check_on'] == 'TotalQuantity' && ( $total_all_units_in_cart < $min || $total_all_units_in_cart > $max || empty($total_all_units_in_cart) )) {
			return false;
		} elseif ($rule['check_on'] == 'Quantity' && ( $total_items_in_cart < $min || $total_items_in_cart > $max || empty($total_items_in_cart) )) {
			return false;
		} elseif ($rule['check_on'] == 'Weight' && ( $total_weight_in_cart < $min || $total_weight_in_cart > $max || empty($total_weight_in_cart) )) {
			return false;
		} elseif ($rule['check_on'] == 'Price' && ( $total_price_in_cart < $min || $total_price_in_cart > $max || empty($total_price_in_cart) )) {
			return false;
		}

		$this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']] = $total_all_units_in_cart;   // for adjustment
		//to check best descount rule
		if ($this->execution_mode == 'best_discount') {
			$new_price = $this->elex_dp_calculate_discount($price, $rule['rule_no'], $rule, $pid, $current_quantity, true);
			$_product = wc_get_product($pid);
			$old_price = $_product->get_price();
			$rule['calculated_discount'] = ( (float) $old_price - (float) $new_price ) * $current_quantity;
		}
		//checking roles and tofrom date for which rule is applicable
		return $this->elex_dp_check_date_range_and_roles($rule, 'cart_rules');
	}

	function elex_dp_checkCombinationalRuleApplicableForProduct( &$rule = null, $product = null, $pid = null, $current_quantity = 1, $price = 0, $weight = 0) {
		global $xa_cart_quantities;
		$total_units        = 0;
		$rule['product_id'] = elex_dp_WPML_Compatible_ids($rule['product_id'], 'product', true);
		$check_for_pid      = 0;
		if (!empty($product) && $product->is_type('variation')) {

			$check_for_pid = elex_dp_is_wc_version_gt_eql('2.7') ? $product->get_parent_id() : $product->parent->id;
		}
		if (empty($rule['product_id']) || count($rule['product_id']) == 0 || !in_array($pid, array_keys($rule['product_id']))) {
			if (empty($product) || !$product->is_type('variation') ||  !in_array($check_for_pid, array_keys($rule['product_id']))) {
				return false;
			}
		}

		if ($this->for_offers_table == true) {
			return $this->elex_dp_check_date_range_and_roles($rule, 'combinational_rules');
		} // to show in offers table
		//if pid is selected in this rule
		foreach ($rule['product_id'] as $_pid => $_qnty) {
			$tmp_prod        = wc_get_product($_pid);
			$total_child_qty = 0;
			if (!empty($tmp_prod) && $tmp_prod->is_type('variable')) {
				$child_ids = $tmp_prod->get_children();
				foreach ($child_ids as $child_key => $child_value) {
					if (in_array($child_value, array_keys($xa_cart_quantities))) {
						$total_child_qty += $xa_cart_quantities[$child_value];
					}
				}
			}
			if (( empty($xa_cart_quantities[$_pid]) || $xa_cart_quantities[$_pid] < $_qnty ) && ( $total_child_qty == 0 || $total_child_qty < $_qnty )) {
				if ($_pid != $check_for_pid || empty($xa_cart_quantities[$pid]) || $xa_cart_quantities[$pid] < $_qnty) { //code to consider parent id of variable products
					return false;
				} else {
					$total_units += $xa_cart_quantities[$pid];
				}
			} else {
				$total_units += !empty($xa_cart_quantities[$_pid]) ? $xa_cart_quantities[$_pid] : 1;
			}
		}
		$rule['discount_on_product_id'] = elex_dp_WPML_Compatible_ids($rule['discount_on_product_id']);
		if (!empty($rule['discount_on_product_id']) && is_array($rule['discount_on_product_id']) && ( !in_array($pid, $rule['discount_on_product_id']) && !in_array($product->get_parent_id(), $rule['discount_on_product_id']) )) {
			return false;
		}
		$this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']] = $total_units;   // for adjustment
		//to check best descount rule
		if ($this->execution_mode == 'best_discount') {
			$new_price = $this->elex_dp_calculate_discount($price, $rule['rule_no'], $rule, $pid, $current_quantity, true);
			$_product = wc_get_product($pid);
			$old_price = $_product->get_price();
			$rule['calculated_discount'] = ( (float) $old_price - (float) $new_price ) * $current_quantity;
		}
		//checking roles and tofrom date for which rule is applicable
		return $this->elex_dp_check_date_range_and_roles($rule, 'combinational_rules');
	}

	function elex_dp_checkProductRuleApplicableForProduct( &$rule = null, $product = null, $pid = null, $current_quantity = 1, $price = 0, $weight = 0) {
		global $xa_cart_categories;
		global $xa_cart_quantities;
		global $xa_variation_parentid;

		if (empty($pid)) {
			$pid = elex_dp_get_pid($product);
		}
		$min          = ( empty($rule['min']) == true ) ? 1 : $rule['min'];
		$max          = ( empty($rule['max']) == true ) ? 999999999 : $rule['max'];
		$total_price  = !empty($price) ? ( $price * $current_quantity ) : 0;
		$total_weight = !empty($weight) ? ( (float) $weight * $current_quantity ) : 0;
		if ($max < $min && $max != 0) {
			return false;
		}
		$repeat = false;
		if (isset($rule['repeat_rule']) && $rule['repeat_rule'] == 'yes') {
			$repeat = true;
		}
		//if pid is selected in this rule
		if (!empty($product) && $product->is_type('variation')) {
			$check_for_pid = elex_dp_is_wc_version_gt_eql('2.7') ? $product->get_parent_id() : $product->parent->id;
			if ($rule['rule_on'] == 'products') {
				$parent_product = wc_get_product($check_for_pid);
				$child_products = $parent_product->get_children();
				if (in_array($check_for_pid, $rule['product_id'])) {
					if (!isset($xa_variation_parentid[$rule['rule_no'] . '_' . $rule['rule_type']])) {
						$xa_variation_parentid[$rule['rule_no'] . '_' . $rule['rule_type']] = array();
					} elseif ($rule['discount_type'] == 'Flat Discount') {
						return false;
					}
					if ($rule['discount_type'] != 'Flat Discount' || !in_array($check_for_pid, $xa_variation_parentid[$rule['rule_no'] . '_' . $rule['rule_type']])) {
						foreach ($xa_cart_quantities as $key => $value) { //to allow variations of a parent product be counted while calculating quantity
							if ($key != $pid) {
								if (in_array($key, $parent_product->get_children())) {
									$current_quantity = $current_quantity + $value;
								}
							}
						}
						array_push($xa_variation_parentid[$rule['rule_no'] . '_' . $rule['rule_type']], $check_for_pid);
					}
				}
			}
		} else {
			$check_for_pid = $pid;
		}
		if ($rule['rule_on'] == 'products') {
			$pids = elex_dp_WPML_Compatible_ids($rule['product_id']);
			if (empty($pids) || ( !is_array($pids) || ( !in_array($check_for_pid, $pids) && !in_array($pid, $pids) ) )) {
				return false;
			}
		} elseif ($rule['rule_on'] == 'categories') {
			if ($product->is_type('variation')) {
				$parent_id          = elex_dp_is_wc_version_gt_eql('2.7') ? $product->get_parent_id() : $product->parent->id;
				$parent_product     = wc_get_product($parent_id);
				$product_categories = elex_dp_is_wc_version_gt_eql('2.7') ? $parent_product->get_category_ids() : elex_dp_get_category_ids($parent_product);
			} else {
				$product_categories = elex_dp_is_wc_version_gt_eql('2.7') ? $product->get_category_ids() : elex_dp_get_category_ids($product);
			}
			$cids    = array();
			$cat_ids = $rule['category_id'];
			if (!is_array($cat_ids)) {
				$cat_ids = array($cat_ids);
			}
			foreach ($cat_ids as $_cid) {
				$cids[] = elex_dp_WPML_Compatible_ids($_cid, 'category');
			}
			$matched = array_intersect($cids, $product_categories);
			if (empty($cids) || empty($matched)) {
				return false;
			}
		} elseif ($rule['rule_on'] == 'cart') {
			global $xa_cart_quantities, $xa_cart_categories;
			if (empty($xa_cart_quantities) || !in_array($pid, array_keys($xa_cart_quantities)) && !$product->is_type('variable')) {
				return false;
			}
		}
		if ($this->for_offers_table == true) {
			return $this->elex_dp_check_date_range_and_roles($rule, 'product_rules');
		} // to show in offers table
		if ($rule['check_on'] == 'Quantity') {

			if (is_cart() && ( empty($pid) || !in_array($pid, array_keys($xa_cart_quantities)) )) {
				return false;
			}

			//Calculating total items in the cart
			$total_items_in_cart = 0;
			$total_items_of_this_category_in_cart = 0;
			$total_all_units_of_this_category_in_cart = 0;
			$total_all_product_items = 0;

			if (is_shop() || is_product_category() || is_product() || is_product_tag()) {
				if (empty($xa_cart_quantities[$pid])) {
					$total_items_in_cart++;
					$total_items_of_this_category_in_cart++;
					$total_all_product_items++;
				}
				$total_all_units_of_this_category_in_cart++;
			}
			$pids = elex_dp_WPML_Compatible_ids($rule['product_id']);
			foreach ($xa_cart_quantities as $_pid => $_qnty) {
				$total_items_in_cart++;
				if ($pids && in_array($_pid, $pids)) {
					$total_all_product_items++;
				}
			}
			$rule_product_ids = elex_dp_WPML_Compatible_ids($rule['product_id']);

			//Conditions for different rule type
			if ('products' === $rule['rule_on'] && in_array($pid, $rule_product_ids) && ( $total_all_product_items < $min || ( $total_all_product_items > $max && $repeat == false ) || empty($total_all_product_items) )) {
				return false;
				
			} else if ('categories' === $rule['rule_on'] && $this->validate_product_matches_categories(wc_get_product($pid), $rule)) {
				$tmp=elex_dp_get_category_ids($pid);
				$product_categories=!empty($tmp)?$tmp:array();
				$matched=array_intersect($cids, $product_categories);
				if (empty($cids) || empty($matched)) {
					return false;
				}
				foreach ($xa_cart_categories as $_pid => $_categories) {
					$match=array_intersect($matched, $_categories);
					if (!empty($match)) {
						$total_items_of_this_category_in_cart++;
						$qnty=!empty($xa_cart_quantities[$_pid])?$xa_cart_quantities[$_pid]:1;
						if (!empty($xa_cart_quantities[$_pid])) {
							$total_all_units_of_this_category_in_cart += (int) $qnty;
						}
					}
				}
				if ($total_items_of_this_category_in_cart == 0) {
					$total_items_of_this_category_in_cart = 1;
					$total_all_units_of_this_category_in_cart = 1;
				}
				if ($total_items_of_this_category_in_cart < $min || ( ( $total_items_of_this_category_in_cart > $max && $repeat == false ) ) || empty($total_items_of_this_category_in_cart)) {
					return false;
				}

			} else if (( 'cart' === $rule['rule_on'] )  && ( $total_items_in_cart < $min || ( ( $total_items_in_cart > $max && $repeat == false ) ) || empty($total_items_in_cart) )) {
				return false;
			}
		} elseif ($rule['check_on'] == 'Weight' && ( $total_weight < $min || ( $total_weight > $max && $repeat == false ) || empty($total_weight) )) {
			return false;
		} elseif ($rule['check_on'] == 'Price' && ( $total_price < $min || ( $total_price > $max && $repeat == false ) || empty($total_price) )) {
			return false;
		} elseif ($rule['check_on'] == 'Units' && ( $current_quantity < $min || ( $current_quantity > $max && $repeat == false ) )) {
			return false;
		}

		$this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']] = $current_quantity;   // for adjustment
		//to check best descount rule
		if ($this->execution_mode == 'best_discount') {
			$new_price = $this->elex_dp_calculate_discount($price, $rule['rule_no'], $rule, $pid, $current_quantity, true);
			$_product = wc_get_product($pid);
			$old_price = $_product->get_price();
			$rule['calculated_discount'] = ( (float) $old_price - (float) $new_price ) * $current_quantity;
		}
		//checking roles and tofrom date for which rule is applicable
		return $this->elex_dp_check_date_range_and_roles($rule, 'product_rules');
	}

	function elex_dp_check_date_range_and_roles( $rule, $rule_type) {
		$fromdate   = $rule['from_date'];
		$todate     = $rule['to_date'];
		$user_roles = $rule['allow_roles'];
		if (!is_array($user_roles)) {
			$user_roles = array($user_roles);
		}
		global $current_user;
		//Compatibility with Woocommerce membership plugin
		if (function_exists('wc_memberships_get_membership_plans')) {
			if (isset($rule['allow_membership_plans']) && !empty($rule['allow_membership_plans'])) {
				$current_user_id = get_current_user_id();
				$mem_plan        = false;
				foreach ($rule['allow_membership_plans'] as $key => $value) {
					if ($value == 'all' || wc_memberships_is_user_active_member($current_user_id, $value)) {
						$mem_plan = true;
						break;
					}
				}
				if (!$mem_plan) {
					return false;
				}
			}
		}
		$match = array_intersect((array) $user_roles, (array) $current_user->roles);
		if (!in_array('all', $user_roles) && empty($match) && !empty($user_roles)) {
			if (!( in_array('guest_user', $user_roles) && !is_user_logged_in() )) {
				return false;
			}
		}

		$now = current_time('d-m-Y');
		if (( empty($fromdate) && empty($todate) ) || ( empty($fromdate) && empty($todate) == false && ( strtotime($now) <= strtotime($todate) ) ) || ( empty($fromdate) == false && ( strtotime($now) >= strtotime($fromdate) ) && empty($todate) ) || ( ( strtotime($now) >= strtotime($fromdate) ) && ( strtotime($now) <= strtotime($todate) ) )) {
		} else {
			return false;
		}

		return true;
	}

	public function elex_dp_execute_rule( $old_price, $rule_type_colon_rule_no, $rule, $current_quantity = 1, $pid = 0, $object_hash = '') {
		global $executed_rule_pid_price, $executed_pids;
		$new_price = $old_price;

		$data      = explode(':', $rule_type_colon_rule_no);
		$rule_type = $data[0];
		$rule_no   = $data[1];
		if (isset($executed_rule_pid_price[$rule_type_colon_rule_no])  && !empty($object_hash)) {  // this code is using cache if already executed
			if (isset($executed_rule_pid_price[$rule_type_colon_rule_no][$object_hash])) {
				return $executed_rule_pid_price[$rule_type_colon_rule_no][$object_hash];
			}
		} else {
			$executed_rule_pid_price[$rule_type_colon_rule_no] = array();
		}

		switch ($rule_type) {
			case 'product_rules':
				$new_price = $this->elex_dp_SimpleExecute($old_price, $rule_no, $rule, $pid, $current_quantity, false, $object_hash);
				break;
			case 'category_rules':
				$new_price = $this->elex_dp_Simple_Category_Execute($old_price, $rule_no, $rule, $pid, $current_quantity, false, $object_hash);
				break;
			case 'tag_rules':
				$new_price = $this->elex_dp_Simple_Tag_Execute($old_price, $rule_no, $rule, $pid, $current_quantity, false, $object_hash);
				break;
			case 'cart_rules':
				$new_price = $this->elex_dp_Simple_Cart_Execute($old_price, $rule_no, $rule, $pid, $current_quantity, false, $object_hash);
				break;
			case 'combinational_rules':
				$new_price = $this->elex_dp_Simple_Combinational_Execute($old_price, $rule_no, $rule, $pid, $current_quantity, false, $object_hash);
				break;
			case 'cat_combinational_rules':
				$new_price = $this->elex_dp_Simple_Category_Combinational_Execute($old_price, $rule_no, $rule, $pid, $current_quantity, false, $object_hash);
				break;
			case 'buy_get_free_rules':
				$new_price = $this->elex_dp_ExecuteBOGORule($old_price, $rule_no, $rule, $pid, $current_quantity);
				break;
			case 'BOGO_category_rules':
				$new_price = $this->elex_dp_ExecuteBOGO_category_Rule($old_price, $rule_no, $rule, $pid, $current_quantity);
				break;
			case 'bogo_tag_rules':
				$new_price = $this->elex_dp_Executebogo_tag_rules($old_price, $rule_no, $rule, $pid, $current_quantity);
				break;
		}
		if (empty($executed_pids[$pid]) || $executed_pids[$pid] > $new_price) {
			$executed_pids[$pid] = $new_price;
		}
		return $new_price;
	}

	public function elex_dp_calculate_discount( $old_price, $rule_no, $rule, $pid = 0, $current_quantity = 1, $do_not_execute = false) {
		global $xa_common_flat_discount, $xa_cart_quantities, $executed_rule_pid_price, $xa_cart_categories_units, $xa_cart_categories, $xa_cart_price;
		$new_price           = $old_price;
		$type_code           = $rule['rule_type'] == 'product_rules'  ?  ( $rule['rule_type'] . $pid )   :  $rule['rule_type'];
		$cart_quantity       = 0;
		$prev_total_discount = 0;
		if ($rule['rule_type'] == 'product_rules') {
			if (isset($rule['repeat_rule']) && $rule['repeat_rule'] == 'yes' && !empty($rule['max']) && !empty($rule['min'])) {
				$cart_quantity = $current_quantity;
			} else {
				$cart_quantity = isset($xa_cart_quantities[$pid]) ?  $xa_cart_quantities[$pid] : 0;
			}
		} elseif ($rule['rule_type'] == 'combinational_rules') {
			foreach ($rule['product_id'] as $_id => $qnty) {
				$avl_units      = isset($xa_cart_quantities[$_id]) ? $xa_cart_quantities[$_id] : 0;
				$cart_quantity += $avl_units;
			}
		} elseif ($rule['rule_type'] == 'cart_rules') {
			$cart_quantity = isset($xa_cart_quantities[$pid]) ?  $xa_cart_quantities[$pid] : 0;
			foreach ($xa_cart_quantities as $ppid => $qnty) {
				if ($ppid !== $pid) {
					$rprice               = isset($xa_cart_price[$ppid]) ?  $xa_cart_price[$ppid] : 0;
					$sprice               = isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid]) ?  $executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid] : $rprice;
					$prev_total_discount += ( $rprice - $sprice ) * $qnty;
				}
			}
		} elseif ($rule['rule_type'] == 'category_rules') {
			if (isset($rule['selected_cids'])) {
				$cid           = current($rule['selected_cids']);
				$cart_quantity = isset($xa_cart_quantities[$pid]) ?  $xa_cart_quantities[$pid] : 0;
			}
			foreach ($xa_cart_categories as $ppid => $cids) {
				$matched = array_intersect($cids, $rule['selected_cids']);
				if ($ppid !== $pid && !empty($matched) && isset($xa_cart_quantities[$ppid])) {
					$units                = isset($xa_cart_quantities[$ppid]) ?  $xa_cart_quantities[$ppid] : 0;
					$rprice               = isset($xa_cart_price[$ppid]) ?  $xa_cart_price[$ppid] : 0;
					$sprice               = isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid]) ?  $executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid] : $rprice;
					$prev_total_discount += ( $rprice - $sprice ) * $units;
				}
			}
		} elseif ($rule['rule_type'] == 'cat_combinational_rules') {
			foreach ($rule['cat_id'] as $cid => $qnty) {
				$avl_units      = isset($xa_cart_categories_units[$cid]) ? $xa_cart_categories_units[$cid] : 0;
				$cart_quantity += $avl_units;
			}
		} else {
			$cart_quantity = isset($xa_cart_quantities[$pid]) ?  $xa_cart_quantities[$pid] : 0;
		}

		if (is_product() || is_shop() || is_product_category() || is_product_tag() || empty($cart_quantity)) {
			$cart_quantity++;
		}
		extract($rule);
		$discount_amt = 0;
		if ($discount_type == 'Percent Discount') {
			$discount_amt = floatval($value) * floatval($old_price) / 100;
		} elseif ($discount_type == 'Flat Discount') {
			if ($current_quantity) {
				if ($do_not_execute === true) {
					$discount_amt = floatval($value);
				} else {
					$xa_common_flat_discount[$rule['rule_type'] . ':' . $rule_no . ':' . $pid] = floatval($value);
				}
			}
		} elseif ($discount_type == 'Fixed Price') {
			$discount_amt = floatval($old_price) - floatval($value);
		} elseif ($discount_type == 'Coupon Discount') {

			if ($rule['coupon_discount_type'] == 'percent') {

				$discount_amt = floatval($value) * floatval($old_price) / 100;
			} elseif ($rule['coupon_discount_type'] == 'fixed_cart') {
				if ($current_quantity) {
					if ($do_not_execute === true) {
						$discount_amt = floatval($value);
					} else {
						$xa_common_flat_discount[$rule['rule_type'] . ':' . $rule_no . ':' . $pid] = floatval($value);
					}
				}

			} elseif ($rule['coupon_discount_type'] == 'fixed_product') {
				$discount_amt = floatval($old_price) - floatval($value);
			} else {
				$discount_amt = 0;
			}
		} else {
			$discount_amt = 0;
		}
		$total_units = 1;
		if (!empty($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']]) && is_numeric($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']])) {
			$total_units = !empty($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']]) ? $this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']] : 1;
		}

		if (!empty($max_discount) && is_numeric($max_discount) && ( ( ( $discount_amt * $cart_quantity ) + $prev_total_discount ) >= $max_discount )) {
			$discount_amt = ( $max_discount - $prev_total_discount ) / $cart_quantity;
		}

		if (isset($adjustment) && is_numeric($adjustment)) {
			$units         = !empty($cart_quantity) ? $cart_quantity : $total_units;
			$discount_amt -= $adjustment / $units;

		}
		if ($old_price && $discount_amt) {
			$new_price = (float) $old_price - (float) $discount_amt;
		}
		if (isset($_GET['debug']) && $do_not_execute == false) {
			echo "\n<div id='rules_info' style=''><pre> RuleType= " . $rule['rule_type'] . ' |   RuleNo=' . $rule_no . '  |   OldPrice=' . $old_price . "   |   Discount=$discount_amt  NewPrice=$new_price |   OfferName=" . $rule['offer_name'] . '</pre></div>';
		}
		//// code added to support discount on specified quantity in combinational rules only when it is restricted to discount on $pid
		if (!empty($rule['discount_on_product_id']) && in_array($pid, $rule['discount_on_product_id']) && !empty($rule['product_id'][$pid]) && !empty($xa_cart_quantities[$pid]) && $rule['product_id'][$pid] < $xa_cart_quantities[$pid]) {
			$remaining_qnty = $xa_cart_quantities[$pid] - $rule['product_id'][$pid];
			$new_price      =  ( ( $new_price * $rule['product_id'][$pid] ) + ( $old_price * $remaining_qnty ) ) / $xa_cart_quantities[$pid];
		}
		return $new_price;
	}

	public function elex_dp_SimpleExecute( $old_price, $rule_no, $rule, $pid = 0, $current_quantity = 1, $do_not_execute = false, $object_hash = '') {
		global $xa_common_flat_discount, $xa_cart_quantities, $executed_rule_pid_price, $xa_cart_categories_units, $xa_cart_categories, $xa_cart_price;
		$old_price = floatval($old_price);
		$new_price           = $old_price;
		$type_code           = $rule['rule_type'] == 'product_rules'  ?  ( $rule['rule_type'] . $pid )   :  $rule['rule_type'];
		$cart_quantity       = 0;
		$prev_total_discount = 0;
		if ($rule['rule_type'] == 'product_rules') {
			if (isset($rule['repeat_rule']) && $rule['repeat_rule'] == 'yes' && !empty($rule['max']) && !empty($rule['min'])) {
				$cart_quantity = $current_quantity;
			} else {
				$cart_quantity = isset($xa_cart_quantities[$pid]) ?  $xa_cart_quantities[$pid] : 0;
			}
		} elseif ($rule['rule_type'] == 'combinational_rules') {
			foreach ($rule['product_id'] as $_id => $qnty) {
				$avl_units      = isset($xa_cart_quantities[$_id]) ? $xa_cart_quantities[$_id] : 0;
				$cart_quantity += $avl_units;
			}
		} elseif ($rule['rule_type'] == 'cart_rules') {
			$cart_quantity = isset($xa_cart_quantities[$pid]) ?  $xa_cart_quantities[$pid] : 0;
			foreach ($xa_cart_quantities as $ppid => $qnty) {
				if ($ppid !== $pid) {
					$rprice               = isset($xa_cart_price[$ppid]) ?  $xa_cart_price[$ppid] : 0;
					$sprice               = isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid]) ?  $executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid] : $rprice;
					$prev_total_discount += ( $rprice - $sprice ) * $qnty;
				}
			}
		} elseif ($rule['rule_type'] == 'category_rules') {
			if (isset($rule['selected_cids'])) {
				$cid           = current($rule['selected_cids']);
				$cart_quantity = isset($xa_cart_quantities[$pid]) ?  $xa_cart_quantities[$pid] : 0;
			}
			foreach ($xa_cart_categories as $ppid => $cids) {
				$matched = array_intersect($cids, $rule['selected_cids']);
				if ($ppid !== $pid && !empty($matched) && isset($xa_cart_quantities[$ppid])) {
					$units                = isset($xa_cart_quantities[$ppid]) ?  $xa_cart_quantities[$ppid] : 0;
					$rprice               = isset($xa_cart_price[$ppid]) ?  $xa_cart_price[$ppid] : 0;
					$sprice               = isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid]) ?  $executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid] : $rprice;
					$prev_total_discount += ( $rprice - $sprice ) * $units;
				}
			}
		} elseif ($rule['rule_type'] == 'cat_combinational_rules') {
			foreach ($rule['cat_id'] as $cid => $qnty) {
				$avl_units      = isset($xa_cart_categories_units[$cid]) ? $xa_cart_categories_units[$cid] : 0;
				$cart_quantity += $avl_units;
			}
		} else {
			$cart_quantity = isset($xa_cart_quantities[$pid]) ?  $xa_cart_quantities[$pid] : 0;
		}

		if (is_product() || is_shop() || is_product_category() || is_product_tag() || empty($cart_quantity)) {
			$cart_quantity++;
		}
		extract($rule);
		$discount_amt = 0;
		if ($discount_type == 'Percent Discount') {
			$discount_amt = floatval($value) * floatval($old_price) / 100;
		} elseif ($discount_type == 'Flat Discount') {
			if ($current_quantity) {
				if ($do_not_execute === true) {
					$discount_amt = floatval($value);
				} else {
					$xa_common_flat_discount[$rule['rule_type'] . ':' . $rule_no . ':' . $pid] = floatval($value);
				}
			}
		} elseif ($discount_type == 'Fixed Price') {
			$discount_amt = floatval($old_price) - floatval($value);
		} else {
			$discount_amt = 0;
		}
		$total_units = 1;
		if (!empty($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']]) && is_numeric($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']])) {
			$total_units = !empty($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']]) ? $this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']] : 1;
		}

		if (!empty($max_discount) && is_numeric($max_discount) && ( ( ( $discount_amt * $cart_quantity ) + $prev_total_discount ) >= $max_discount )) {
			$discount_amt = ( $max_discount - $prev_total_discount ) / $cart_quantity;
		}

		if (isset($adjustment) && is_numeric($adjustment)) {
			$units         = !empty($cart_quantity) ? $cart_quantity : $total_units;
			$discount_amt -= $adjustment / $units;
		}

		$new_price = $old_price - $discount_amt;
		if (isset($_GET['debug']) && $do_not_execute == false) {
			echo "\n<div id='rules_info' style=''><pre> RuleType= " . $rule['rule_type'] . ' |   RuleNo=' . $rule_no . '  |   OldPrice=' . $old_price . "   |   Discount=$discount_amt  NewPrice=$new_price |   OfferName=" . $rule['offer_name'] . '</pre></div>';
		}
		//// code added to support discount on specified quantity in combinational rules only when it is restricted to discount on $pid
		if (!empty($rule['discount_on_product_id']) && in_array($pid, $rule['discount_on_product_id']) && !empty($rule['product_id'][$pid]) && !empty($xa_cart_quantities[$pid]) && $rule['product_id'][$pid] < $xa_cart_quantities[$pid]) {
			$remaining_qnty = $xa_cart_quantities[$pid] - $rule['product_id'][$pid];
			$new_price      =  ( ( $new_price * $rule['product_id'][$pid] ) + ( $old_price * $remaining_qnty ) ) / $xa_cart_quantities[$pid];
		}
		///// adding to cache
		if (!isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$object_hash])) {
			$executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$object_hash] = $new_price;
		}
		if (!isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$pid])) {
			$executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$pid] = $new_price;
		}
		return $new_price;
	}

	public function elex_dp_Simple_Category_Combinational_Execute( $old_price, $rule_no, $rule, $pid = 0, $current_quantity = 1, $do_not_execute = false, $object_hash = '') {
		global $xa_common_flat_discount, $xa_cart_quantities, $executed_rule_pid_price, $xa_cart_categories_units, $xa_cart_categories, $xa_cart_price;
		$new_price           = $old_price;
		$type_code           = $rule['rule_type'] == 'product_rules'  ?  ( $rule['rule_type'] . $pid )   :  $rule['rule_type'];
		$prev_total_discount = 0;
		$cart_quantity       = isset($xa_cart_quantities[$pid]) ?  $xa_cart_quantities[$pid] : 0;
		$combinational_cids  = array_keys($rule['cat_id']);
		foreach ($xa_cart_quantities as $ppid => $qnty) {
			$matched = array_intersect($xa_cart_categories[$ppid], $combinational_cids);
			if ($ppid !== $pid &&  !empty($matched)) {
				$rprice               = isset($xa_cart_price[$ppid]) ?  $xa_cart_price[$ppid] : 0;
				$sprice               = isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid]) ?  $executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid] : $rprice;
				$prev_total_discount += ( $rprice - $sprice ) * $qnty;
			}
		}
		if (is_product() || is_shop() || is_product_category() || is_product_tag() || empty($cart_quantity)) {
			$cart_quantity++;
		}
		extract($rule);
		$discount_amt = 0;
		if ($discount_type == 'Percent Discount') {
			$discount_amt = floatval($value) * floatval($old_price) / 100;
		} elseif ($discount_type == 'Flat Discount') {
			if ($current_quantity) {
				if ($do_not_execute === true) {
					$discount_amt = floatval($value);
				} else {
					$xa_common_flat_discount[$rule['rule_type'] . ':' . $rule_no . ':' . $pid] = floatval($value);
				}
			}
		} elseif ($discount_type == 'Fixed Price') {
			$discount_amt = floatval($old_price) - floatval($value);
		} else {
			$discount_amt = 0;
		}
		$total_units = 1;
		if (!empty($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']]) && is_numeric($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']])) {
			$total_units = !empty($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']]) ? $this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']] : 1;
		}

		if (!empty($max_discount) && is_numeric($max_discount) && ( ( ( $discount_amt * $cart_quantity ) + $prev_total_discount ) >= $max_discount )) {
			$discount_amt = ( $max_discount - $prev_total_discount ) / $cart_quantity;
		}

		if (isset($adjustment) && is_numeric($adjustment)) {
			$units         = !empty($cart_quantity) ? $cart_quantity : $total_units;
			$discount_amt -= $adjustment / $units;
		}

		$new_price = $old_price - $discount_amt;
		if (isset($_GET['debug']) && $do_not_execute == false) {
			echo "\n<div id='rules_info' style=''><pre> RuleType= " . $rule['rule_type'] . ' |   RuleNo=' . $rule_no . '  |   OldPrice=' . $old_price . "   |   Discount=$discount_amt  NewPrice=$new_price |   OfferName=" . $rule['offer_name'] . '</pre></div>';
		}
		//// code added to support discount on specified quantity in combinational rules only when it is restricted to discount on $pid
		if (!empty($rule['discount_on_product_id']) && in_array($pid, $rule['discount_on_product_id']) && !empty($rule['product_id'][$pid]) && !empty($xa_cart_quantities[$pid]) && $rule['product_id'][$pid] < $xa_cart_quantities[$pid]) {
			$remaining_qnty = $xa_cart_quantities[$pid] - $rule['product_id'][$pid];
			$new_price      =  ( ( $new_price * $rule['product_id'][$pid] ) + ( $old_price * $remaining_qnty ) ) / $xa_cart_quantities[$pid];
		}
		///// adding to cache
		if (!isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$object_hash])) {
			$executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$object_hash] = $new_price;
		}
		if (!isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$pid])) {
			$executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$pid] = $new_price;
		}
		return $new_price;
	}

	public function elex_dp_Simple_Combinational_Execute( $old_price, $rule_no, $rule, $pid = 0, $current_quantity = 1, $do_not_execute = false, $object_hash = '') {
		global $xa_common_flat_discount, $xa_cart_quantities, $executed_rule_pid_price, $xa_cart_categories_units, $xa_cart_categories, $xa_cart_price;
		$new_price           = $old_price;
		$type_code           = $rule['rule_type'] == 'product_rules'  ?  ( $rule['rule_type'] . $pid )   :  $rule['rule_type'];
		$prev_total_discount = 0;
		$cart_quantity       = isset($xa_cart_quantities[$pid]) ?  $xa_cart_quantities[$pid] : 0;
		$combinational_pids  = array_keys($rule['product_id']);
		foreach ($xa_cart_quantities as $ppid => $qnty) {
			if ($ppid !== $pid && in_array($ppid, $combinational_pids)) {
				$rprice               = isset($xa_cart_price[$ppid]) ?  $xa_cart_price[$ppid] : 0;
				$sprice               = isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid]) ?  $executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid] : $rprice;
				$prev_total_discount += ( $rprice - $sprice ) * $qnty;
			}
		}
		if (is_product() || is_shop() || is_product_category() || is_product_tag() || empty($cart_quantity)) {
			$cart_quantity++;
		}
		extract($rule);
		$discount_amt = 0;
		if ($discount_type == 'Percent Discount') {
			$discount_amt = floatval($value) * floatval($old_price) / 100;
		} elseif ($discount_type == 'Flat Discount') {
			if ($current_quantity) {
				if ($do_not_execute === true) {
					$discount_amt = floatval($value);
				} else {
					$xa_common_flat_discount[$rule['rule_type'] . ':' . $rule_no . ':' . $pid] = floatval($value);
				}
			}
		} elseif ($discount_type == 'Fixed Price') {
			$discount_amt = floatval($old_price) - floatval($value);
		} else {
			$discount_amt = 0;
		}
		$total_units = 1;
		if (!empty($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']]) && is_numeric($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']])) {
			$total_units = !empty($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']]) ? $this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']] : 1;
		}

		if (!empty($max_discount) && is_numeric($max_discount) && ( ( ( $discount_amt * $cart_quantity ) + $prev_total_discount ) >= $max_discount )) {
			$discount_amt = ( $max_discount - $prev_total_discount ) / $cart_quantity;
		}

		if (isset($adjustment) && is_numeric($adjustment)) {
			$units         = !empty($cart_quantity) ? $cart_quantity : $total_units;
			$discount_amt -= $adjustment / $units;
		}

		$new_price = $old_price - $discount_amt;
		if (isset($_GET['debug']) && $do_not_execute == false) {
			echo "\n<div id='rules_info' style=''><pre> RuleType= " . $rule['rule_type'] . ' |   RuleNo=' . $rule_no . '  |   OldPrice=' . $old_price . "   |   Discount=$discount_amt  NewPrice=$new_price |   OfferName=" . $rule['offer_name'] . '</pre></div>';
		}
		//// code added to support discount on specified quantity in combinational rules only when it is restricted to discount on $pid
		if (!empty($rule['discount_on_product_id']) && in_array($pid, $rule['discount_on_product_id']) && !empty($rule['product_id'][$pid]) && !empty($xa_cart_quantities[$pid]) && $rule['product_id'][$pid] < $xa_cart_quantities[$pid]) {
			// removed this code to make restrictions work for any quantity
			//$remaining_qnty=$xa_cart_quantities[$pid]-$rule['product_id'][$pid];
			$new_price =  ( $new_price * $rule['product_id'][$pid] ); // + ($old_price * $remaining_qnty ))/$xa_cart_quantities[$pid];
		}
		///// adding to cache
		if (!isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$object_hash])) {
			$executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$object_hash] = $new_price;
		}
		if (!isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$pid])) {
			$executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$pid] = $new_price;
		}
		return $new_price;
	}


	public function elex_dp_Simple_Cart_Execute( $old_price, $rule_no, $rule, $pid = 0, $current_quantity = 1, $do_not_execute = false, $object_hash = '') {
		global $xa_common_flat_discount, $xa_cart_quantities, $executed_rule_pid_price, $xa_cart_categories_units, $xa_cart_categories, $xa_cart_price;
		$new_price           = $old_price;
		$type_code           = $rule['rule_type'] == 'product_rules'  ?  ( $rule['rule_type'] . $pid )   :  $rule['rule_type'];
		$cart_quantity       = 0;
		$prev_total_discount = 0;

		$cart_quantity = isset($xa_cart_quantities[$pid]) ?  $xa_cart_quantities[$pid] : 0;
		foreach ($xa_cart_quantities as $ppid => $qnty) {
			if ($ppid !== $pid) {
				$rprice               = isset($xa_cart_price[$ppid]) ?  $xa_cart_price[$ppid] : 0;
				$sprice               = isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid]) ?  $executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid] : $rprice;
				$prev_total_discount += ( $rprice - $sprice ) * $qnty;
			}
		}

		if (is_product() || is_shop() || is_product_category() || is_product_tag() || empty($cart_quantity)) {
			$cart_quantity++;
		}
		extract($rule);
		$discount_amt = 0;
		if ($discount_type == 'Percent Discount') {
			$discount_amt = floatval($value) * floatval($old_price) / 100;
		} elseif ($discount_type == 'Flat Discount') {
			if ($current_quantity) {
				if ($do_not_execute === true) {
					$discount_amt = floatval($value);
				} else {
					$xa_common_flat_discount[$rule['rule_type'] . ':' . $rule_no . ':' . $pid] = floatval($value);
				}
			}
		} elseif ($discount_type == 'Fixed Price') {
			$discount_amt = floatval($old_price) - floatval($value);
		} else {
			$discount_amt = 0;
		}
		$total_units = 1;
		if (!empty($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']]) && is_numeric($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']])) {
			$total_units = !empty($this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']]) ? $this->rule_based_quantity[$rule['rule_type'] . ':' . $rule['rule_no']] : 1;
		}

		if (!empty($max_discount) && is_numeric($max_discount) && ( ( ( $discount_amt * $cart_quantity ) + $prev_total_discount ) >= $max_discount )) {
			$discount_amt = ( $max_discount - $prev_total_discount ) / $cart_quantity;
		}

		if (isset($adjustment) && is_numeric($adjustment)) {
			$discount_amt -= $adjustment / $total_units;
		}

		$new_price = $old_price - $discount_amt;
		if (isset($_GET['debug']) && $do_not_execute == false) {
			echo "\n<div id='rules_info' style=''><pre> RuleType= " . $rule['rule_type'] . ' |   RuleNo=' . $rule_no . '  |   OldPrice=' . $old_price . "   |   Discount=$discount_amt  NewPrice=$new_price |   OfferName=" . $rule['offer_name'] . '</pre></div>';
		}
		//// code added to support discount on specified quantity in combinational rules only when it is restricted to discount on $pid
		if (!empty($rule['discount_on_product_id']) && in_array($pid, $rule['discount_on_product_id']) && !empty($rule['product_id'][$pid]) && !empty($xa_cart_quantities[$pid]) && $rule['product_id'][$pid] < $xa_cart_quantities[$pid]) {
			$remaining_qnty = $xa_cart_quantities[$pid] - $rule['product_id'][$pid];
			$new_price      =  ( ( $new_price * $rule['product_id'][$pid] ) + ( $old_price * $remaining_qnty ) ) / $xa_cart_quantities[$pid];
		}
		///// adding to cache
		if (!isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$object_hash])) {
			$executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$object_hash] = $new_price;
		}
		if (!isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$pid])) {
			$executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$pid] = $new_price;
		}
		return $new_price;
	}


	public function elex_dp_Simple_Category_Execute( $old_price, $rule_no, $rule, $pid = 0, $current_quantity = 1, $do_not_execute = false, $object_hash = '') {
		global $xa_common_flat_discount, $xa_cart_quantities, $executed_rule_pid_price, $xa_cart_categories_units, $xa_cart_categories, $xa_cart_price;
		$old_price = floatval($old_price);
		$new_price           = $old_price;
		$type_code           = $rule['rule_type'] == 'product_rules'  ?  ( $rule['rule_type'] . $pid )   :  $rule['rule_type'];
		$cart_quantity       = 0;
		$prev_total_discount = 0;
		$total_units         = 0;
		if (isset($rule['selected_cids'])) {
			$cid           = current($rule['selected_cids']);
			$cart_quantity = isset($xa_cart_quantities[$pid]) ?  $xa_cart_quantities[$pid] : 0;
		}
		foreach ($xa_cart_categories as $ppid => $cids) {
			$matched      = array_intersect($cids, $rule['selected_cids']);
			$units        = isset($xa_cart_quantities[$ppid]) ?  $xa_cart_quantities[$ppid] : 0;
			$total_units += $units;
			if ($ppid !== $pid && !empty($matched) && isset($xa_cart_quantities[$ppid])) {

				$rprice               = isset($xa_cart_price[$ppid]) ?  $xa_cart_price[$ppid] : 0;
				$sprice               = isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid]) ?  $executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid] : $rprice;
				$prev_total_discount += ( $rprice - $sprice ) * $units;
			}
		}

		if (is_product() || is_shop() || is_product_category() || is_product_tag() || empty($cart_quantity)) {
			$cart_quantity++;
			$total_units++;
		}
		if ($total_units == 0) {
			$total_units = 1;
		}
		extract($rule);
		$discount_amt = 0;
		if ($discount_type == 'Percent Discount') {
			$discount_amt = floatval($value) * floatval($old_price) / 100;
		} elseif ($discount_type == 'Flat Discount') {
			if ($current_quantity) {
				if ($do_not_execute === true) {
					$discount_amt = floatval($value);
				} else {
					$xa_common_flat_discount[$rule['rule_type'] . ':' . $rule_no . ':' . $pid] = floatval($value);
				}
			}
		} elseif ($discount_type == 'Fixed Price') {
			$discount_amt = floatval($old_price) - floatval($value);
		} else {
			$discount_amt = 0;
		}

		if (!empty($max_discount) && is_numeric($max_discount) && ( ( ( $discount_amt * $cart_quantity ) + $prev_total_discount ) >= $max_discount )) {
			$discount_amt = ( $max_discount - $prev_total_discount ) / $cart_quantity;
		}

		if (isset($adjustment) && is_numeric($adjustment)) {
			$discount_amt -= $adjustment / $cart_quantity;
		}

		$new_price = $old_price - $discount_amt;
		if (isset($_GET['debug']) && $do_not_execute == false) {
			echo "\n<div id='rules_info' style=''><pre> RuleType= " . $rule['rule_type'] . ' |   RuleNo=' . $rule_no . '  |   OldPrice=' . $old_price . "   |   Discount=$discount_amt  NewPrice=$new_price |   OfferName=" . $rule['offer_name'] . '</pre></div>';
		}
		//// code added to support discount on specified quantity in combinational rules only when it is restricted to discount on $pid
		if (!empty($rule['discount_on_product_id']) && in_array($pid, $rule['discount_on_product_id']) && !empty($rule['product_id'][$pid]) && !empty($xa_cart_quantities[$pid]) && $rule['product_id'][$pid] < $xa_cart_quantities[$pid]) {
			$remaining_qnty = $xa_cart_quantities[$pid] - $rule['product_id'][$pid];
			$new_price      =  ( ( $new_price * $rule['product_id'][$pid] ) + ( $old_price * $remaining_qnty ) ) / $xa_cart_quantities[$pid];
		}
		///// adding to cache
		if (!isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$object_hash])) {
			$executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$object_hash] = $new_price;
		}
		if (!isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$pid])) {
			$executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$pid] = $new_price;
		}
		return $new_price;
	}

	public function elex_dp_Simple_Tag_Execute( $old_price, $rule_no, $rule, $pid = 0, $current_quantity = 1, $do_not_execute = false, $object_hash = '') {
		global $xa_common_flat_discount, $xa_cart_quantities, $executed_rule_pid_price, $xa_cart_tags_units, $xa_cart_tags, $xa_cart_price;
		$new_price           = $old_price;
		$type_code           = $rule['rule_type'] == 'product_rules'  ?  ( $rule['rule_type'] . $pid )   :  $rule['rule_type'];
		$cart_quantity       = 0;
		$prev_total_discount = 0;
		$total_units         = 0;
		if (isset($rule['selected_tids'])) {
			$cid           = current($rule['selected_tids']);
			$cart_quantity = isset($xa_cart_quantities[$pid]) ?  $xa_cart_quantities[$pid] : 0;
		}
		foreach ($xa_cart_tags as $ppid => $tids) {
			$matched      = array_intersect($tids, $rule['selected_tids']);
			$units        = isset($xa_cart_quantities[$ppid]) ?  $xa_cart_quantities[$ppid] : 0;
			$total_units += $units;
			if ($ppid !== $pid && !empty($matched) && isset($xa_cart_quantities[$ppid])) {

				$rprice               = isset($xa_cart_price[$ppid]) ?  $xa_cart_price[$ppid] : 0;
				$sprice               = isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid]) ?  $executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$ppid] : $rprice;
				$prev_total_discount += ( $rprice - $sprice ) * $units;
			}
		}

		if (is_product() || is_shop() || is_product_category() || is_product_tag() || empty($cart_quantity)) {
			$cart_quantity++;
			$total_units++;
		}
		if ($total_units == 0) {
			$total_units = 1;
		}
		extract($rule);
		$discount_amt = 0;
		if ($discount_type == 'Percent Discount') {
			$discount_amt = floatval($value) * floatval($old_price) / 100;
		} elseif ($discount_type == 'Flat Discount') {
			if ($current_quantity) {
				if ($do_not_execute === true) {
					$discount_amt = floatval($value);
				} else {
					$xa_common_flat_discount[$rule['rule_type'] . ':' . $rule_no . ':' . $pid] = floatval($value);
				}
			}
		} elseif ($discount_type == 'Fixed Price') {
			$discount_amt = floatval($old_price) - floatval($value);
		} else {
			$discount_amt = 0;
		}

		if (!empty($max_discount) && is_numeric($max_discount) && ( ( ( $discount_amt * $cart_quantity ) + $prev_total_discount ) >= $max_discount )) {
			$discount_amt = ( $max_discount - $prev_total_discount ) / $cart_quantity;
		}

		if (isset($adjustment) && is_numeric($adjustment)) {
			$discount_amt -= $adjustment / $cart_quantity;
		}

		$new_price = $old_price - $discount_amt;
		if (isset($_GET['debug']) && $do_not_execute == false) {
			echo "\n<div id='rules_info' style=''><pre> RuleType= " . $rule['rule_type'] . ' |   RuleNo=' . $rule_no . '  |   OldPrice=' . $old_price . "   |   Discount=$discount_amt  NewPrice=$new_price |   OfferName=" . $rule['offer_name'] . '</pre></div>';
		}
		//// code added to support discount on specified quantity in combinational rules only when it is restricted to discount on $pid
		if (!empty($rule['discount_on_product_id']) && in_array($pid, $rule['discount_on_product_id']) && !empty($rule['product_id'][$pid]) && !empty($xa_cart_quantities[$pid]) && $rule['product_id'][$pid] < $xa_cart_quantities[$pid]) {
			$remaining_qnty = $xa_cart_quantities[$pid] - $rule['product_id'][$pid];
			$new_price      =  ( ( $new_price * $rule['product_id'][$pid] ) + ( $old_price * $remaining_qnty ) ) / $xa_cart_quantities[$pid];
		}
		///// adding to cache
		if (!isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$object_hash])) {
			$executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$object_hash] = $new_price;
		}
		if (!isset($executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$pid])) {
			$executed_rule_pid_price[$rule['rule_type'] . ':' . $rule_no][$pid] = $new_price;
		}
		return $new_price;
	}

	public function elex_dp_ExecuteBOGORule( $old_price, $rule_no, $rule, $pid = 0, $current_quantity = 1) {
		global $xa_dp_setting;
		global $woocommerce;
		global $xa_cart_quantities, $xa_cart_price;
		extract($rule);
		$product   = wc_get_product($pid);
		$parent_id = $pid;
		if (!empty($product) && $product->is_type('variation')) {
			$parent_id = elex_dp_is_wc_version_gt_eql('2.7') ? $product->get_parent_id() : $product->parent->id;
		}
		$rule['purchased_product_id'] = elex_dp_WPML_Compatible_ids($rule['purchased_product_id'], 'product', true);
		$rule['free_product_id']      = elex_dp_WPML_Compatible_ids($rule['free_product_id'], 'product', true);
		$multiple                     = 1;
		if (!empty($rule['repeat_rule']) && $rule['repeat_rule'] == 'yes') {
			$multiple = 9999;
			foreach ($rule['purchased_product_id'] as $_pid => $_qnty) {
				if (!empty($xa_cart_quantities[$_pid]) && !empty($_qnty) && $xa_cart_quantities[$_pid] > $_qnty) {
					$tmp = (int) ( $xa_cart_quantities[$_pid] / $_qnty );
					if ($tmp > 1 && $tmp < $multiple) {
						$multiple = $tmp;
					}
				} else {
					$parent_vari_prod = wc_get_product($_pid);
					if ($parent_vari_prod->is_type('variable')) {
						$children_ids             = $parent_vari_prod->get_children();
						$total_childs_qty_in_cart = 0;
						foreach ($xa_cart_quantities as $prod_id => $quantities) {
							if (in_array($prod_id, $children_ids)) {
								$total_childs_qty_in_cart += $quantities;
							}
						}
						if ($total_childs_qty_in_cart) {
							$tmp = (int) ( $total_childs_qty_in_cart / $_qnty );
							if ($tmp > 1 && $tmp < $multiple) {
								$multiple = $tmp;
							}
						}
					}
				}
			}
			if ($multiple == 9999) {
				$multiple = 1;
			}
		}

		//If free product is set
		if (!isset($rule['set_free_option']) || ( isset($rule['set_free_option']) && $rule['set_free_option'] == 'select_product' )) {

			////////if free product is already in cart with exact quanitty this code will set its price as zero
			if (in_array($pid, array_keys($rule['free_product_id'])) &&  $xa_dp_setting['auto_add_free_product_on_off'] != 'on') {
				$all_free_product_present = true;
				foreach ($rule['free_product_id'] as $_pid => $_qnty) {
					if (empty($xa_cart_quantities[$_pid]) || $xa_cart_quantities[$_pid] < $_qnty) {
						$all_free_product_present = false;
						break;
					}
				}
				$unit      = isset($xa_cart_quantities[$pid]) ? $xa_cart_quantities[$pid] : 1;
				$free_unit = isset($rule['free_product_id'][$pid]) ? $rule['free_product_id'][$pid] : 0;
				$free_unit = $free_unit > $unit ? $unit : $free_unit;

				if (array_key_exists($pid, $rule['purchased_product_id']) && array_key_exists($pid, $rule['free_product_id'])) {
					if ($free_unit >= $unit) {
						$free_unit = $unit - (int) $rule['purchased_product_id'][$pid];
					} else {
						$free_unit = ( $rule['free_product_id'][$pid] );
					}
				}
				if ($all_free_product_present == true && ( is_cart() || is_ajax() || is_checkout() )) {
					$total_adjustment_price = 0;
					if (isset($adjustment) && is_numeric($adjustment)) {
						if ($multiple >= $unit) { //to make repeat rule work with auto add disabled
							$total_adjustment_price = $adjustment * $free_unit * $unit;
						} else {
							$total_adjustment_price = $adjustment * $free_unit * $multiple;
						}
					} else {
						$total_adjustment_price = 0;
					}
					if (( $unit - (float) $free_unit * $multiple ) < 0) { //to make repeat rule work with auto add disabled
						$total_old_price = 0;
					} else {
						$total_old_price = $old_price * ( $unit - (float) $free_unit * $multiple );
					}
					if ($xa_dp_setting['auto_add_free_product_on_off'] == 'on') {
						return  $old_price;
					} else {
						return ( ( $total_old_price + $total_adjustment_price ) / $unit );
					}
				}
			}
			/////////////////////////////////////////////////////////
			$cart = $woocommerce->cart;

			$line_subtotal_total = 0; //added to fix adjustments not working issue
			foreach ($cart->cart_contents as $value) {
				if (isset($value['line_subtotal'])) {
					$line_subtotal_total += $value['line_subtotal'];
				}
			}

			if ($xa_dp_setting['auto_add_free_product_on_off'] == 'on') {         // only works for different products
				foreach ($free_product_id as $pid2 => $qnty2) {
					// Check if the product is not in the trash
					$product_status = get_post_status($pid2);
					if ($product_status === 'trash') {
						continue;
					}
					$product_data = wc_get_product($pid2);
					if (empty($pid2) || empty($product_data)) {
						continue;
					}
					if (isset($adjustment) && is_numeric($adjustment)) {
						$product_data->set_price($adjustment);
						$product_data->set_price($adjustment);
						$cart->set_subtotal($line_subtotal_total + $adjustment); //added to fix adjustments not working issue
						$cart->set_total($line_subtotal_total + $adjustment); //added to fix adjustments not working issue
					} else {
						$product_data->set_price(0.0);
					}

					$cart_item_key                       = 'FreeForRule-' . $rule['rule_type'] . '-' . $rule['rule_no'] . '-' . md5($pid2);
					$cart->cart_contents[$cart_item_key] = array(
						'product_id' => $pid2,
						'variation_id' => 0,
						'variation' => array(),
						'quantity' => $qnty2 * $multiple,
						'data' => $product_data,
						'line_total' => 0,
						'line_subtotal' => 0,
						'line_subtotal_tax' => 0
					);
				}
			}
		} else if ($rule['set_free_option'] == 'set_cheapest') {
			//Set the cheapest product in cart free.
			$cheapest_product_quantity = !empty($rule['cheapest_product_quantity']) ? $rule['cheapest_product_quantity'] : 1;
			$all_free_product_present = false;
			$minvalue                 = !empty($xa_cart_price) ? max($xa_cart_price) : '';
			$product_tosetfree        = '';
			foreach ($xa_cart_quantities as $_pid => $_qnty) {
				$_product = wc_get_product($_pid);
				if ($_product->get_price() <= $minvalue) {
					$minvalue                 = $_product->get_price();
					$product_tosetfree        = $_pid;
					$all_free_product_present = true;
				}
			}


			if (( $pid == $product_tosetfree )) {

				$unit      = isset($xa_cart_quantities[$product_tosetfree]) ? $xa_cart_quantities[$product_tosetfree] : 1;
				$free_unit = $cheapest_product_quantity > $unit ? $unit : $cheapest_product_quantity;

				if (array_key_exists($pid, $rule['purchased_product_id'])) {
					if ($cheapest_product_quantity >= $unit) {
						$free_unit = $unit - (int) $rule['purchased_product_id'][$pid];
					} else {
						$free_unit = $cheapest_product_quantity ;
					}
				}

				if (empty($xa_cart_quantities[$_pid]) ) {
					$all_free_product_present = false;
				}

				if ($all_free_product_present == true && ( is_cart() || is_ajax() || is_checkout() )) {
					$total_adjustment_price = 0;
					if (isset($adjustment) && is_numeric($adjustment)) {
						if ($multiple >= $unit) { //to make repeat rule work with auto add disabled
							$total_adjustment_price = $adjustment * $free_unit * $unit;
						} else {
							$total_adjustment_price = $adjustment * $free_unit * $multiple;
						}
					} else {
						$total_adjustment_price = 0;
					}
					if (( $unit - (float) $free_unit * $multiple ) < 0) { //to make repeat rule work with auto add disabled
						$total_old_price = 0;
					} else {
						$total_old_price = $old_price * ( $unit - (float) $free_unit * $multiple );
					}
					return ( ( $total_old_price + $total_adjustment_price ) / $unit );
				}
			}
			/////////////////////////////////////////////////////////
			$cart = $woocommerce->cart;

			$line_subtotal_total = 0; //added to fix adjustments not working issue
			foreach ($cart->cart_contents as $value) {
				if (isset($value['line_subtotal'])) {
					$line_subtotal_total += $value['line_subtotal'];
				}
			}
		}

		if (isset($_REQUEST['debug'])) {
			echo "\n<div id='rules_info' style=''><pre> RuleType= " . $rule['rule_type'] . ' |   RuleNo=' . $rule_no . '  |   OldPrice=' . $old_price . '   |   OfferName=' . $rule['offer_name'] . '</pre></div>';
		}
		return $old_price;
	}
	public function elex_dp_ExecuteBOGO_category_Rule( $old_price, $rule_no, $rule, $pid = 0, $current_quantity = 1) {
		global $xa_dp_setting;
		global $woocommerce;
		global $xa_cart_quantities, $xa_cart_price, $xa_cart_categories, $xa_cart_categories_items;
		extract($rule);
		if (empty($xa_cart_quantities[$pid])) {
			$xa_cart_quantities[$pid] = $current_quantity;
		}
		$product   = wc_get_product($pid);
		$parent_id = $pid;
		if (!empty($product) && $product->is_type('variation')) {
			$parent_id = elex_dp_is_wc_version_gt_eql('2.7') ? $product->get_parent_id() : $product->parent->id;
		}
		$rule['purchased_category_id'] = elex_dp_WPML_Compatible_ids($rule['purchased_category_id'], 'category', true);
		$rule['free_product_id']       = elex_dp_WPML_Compatible_ids($rule['free_product_id'], 'product', true);

		//If free product is set
		if (!isset($rule['set_free_option']) || ( isset($rule['set_free_option']) && $rule['set_free_option'] == 'select_product' )) {

			////////if free product is already in cart with exact quanitty this code will set its price as zero
			if (in_array($pid, array_keys($rule['free_product_id'])) && $xa_cart_quantities[$pid] >= $rule['free_product_id'][$pid] && $xa_dp_setting['auto_add_free_product_on_off'] != 'on') {
				$all_free_product_present = true;
				foreach ($rule['free_product_id'] as $_pid => $_qnty) {
					if (empty($xa_cart_quantities[$_pid]) || $xa_cart_quantities[$_pid] < $_qnty) {
						$all_free_product_present = false;
						break;
					}
				}
				$unit      = isset($xa_cart_quantities[$pid]) ? $xa_cart_quantities[$pid] : 1;
				$free_unit = isset($rule['free_product_id'][$pid]) ? $rule['free_product_id'][$pid] : 0;

				$product_categories = !empty($xa_cart_categories[$pid]) ? $xa_cart_categories[$pid] : array();
				$add_if_not_auto = 0;

				foreach ($rule['purchased_category_id'] as $_cid => $_qnty_and_checkon) {
	
					if (( array_key_exists($pid, $rule['free_product_id']) ) && in_array($_cid, $product_categories) && !( $xa_cart_categories_items[$_cid] > 1 )) {
						$add_if_not_auto = $add_if_not_auto + 1;
					}
				}
				if ($add_if_not_auto > 0) {
					if ($free_unit >= $unit) {
						$free_unit = $unit - 1;
					}
				}

				if ($all_free_product_present == true && ( is_cart() || is_checkout() )) {
					$total_adjustment_price = 0;
					if (isset($adjustment) && is_numeric($adjustment)) {
						$total_adjustment_price = $adjustment * $free_unit;
					}
					$total_old_price = $old_price * ( $unit - (float) $free_unit );
					if ($xa_dp_setting['auto_add_free_product_on_off'] == 'on') {
						return  $old_price;
					} else {
						return ( ( $total_old_price + $total_adjustment_price ) ) / $unit;
					}
				}
			}
			/////////////////////////////////////////////////////////
			$cart = $woocommerce->cart;
			if ($xa_dp_setting['auto_add_free_product_on_off'] == 'on') {         // only works for different products

				foreach ($free_product_id as $pid2 => $qnty2) {
					// Check if the product is not in the trash
					$product_status = get_post_status($pid2);
					if ($product_status === 'trash') {
						continue;
					}
					$product_data = wc_get_product($pid2);
					if (empty($pid2) || empty($product_data)) {
						continue;
					}
					if (isset($adjustment) && is_numeric($adjustment)) {
						$product_data->set_price($adjustment);
					} else {
						$product_data->set_price(0.0);
					}

					$cart_item_key                       = 'FreeForRule-' . $rule['rule_type'] . '-' . $rule['rule_no'] . '-' . md5($pid2);
					$cart->cart_contents[$cart_item_key] = array(
						'product_id' => $pid2,
						'variation_id' => 0,
						'variation' => array(),
						'quantity' => $qnty2,
						'data' => $product_data,
						'line_total' => 0,
						'line_subtotal' => 0,
						'line_subtotal_tax' => 0
					);
				}
			}
		} elseif ($rule['set_free_option'] == 'set_cheapest') {
			//Set the cheapest product in cart free.
			$cheapest_product_quantity = !empty($rule['cheapest_product_quantity']) ? $rule['cheapest_product_quantity'] : 1;
			$all_free_product_present = false;
			$minvalue                 = !empty($xa_cart_price) ? max($xa_cart_price) : '';
			$product_tosetfree        = '';
			foreach ($xa_cart_quantities as $_pid => $_qnty) {
				$_product = wc_get_product($_pid);
				if ($_product->get_price() <= $minvalue) {
					$minvalue                 = $_product->get_price();
					$product_tosetfree        = $_pid;
					$all_free_product_present = true;
				}
			}

			$unit      = isset($xa_cart_quantities[$product_tosetfree]) ? $xa_cart_quantities[$product_tosetfree] : 1;
			$free_unit = $cheapest_product_quantity > $unit ? $unit : $cheapest_product_quantity;

			////////if free product is already in cart with exact quanitty this code will set its price as zero
			if (( $pid == $product_tosetfree )) {

				$product_categories = !empty($xa_cart_categories[$pid]) ? $xa_cart_categories[$pid] : array();
				$add_if_not_auto = 0;

				foreach ($rule['purchased_category_id'] as $_cid => $_qnty_and_checkon) {
	
					if (( $pid == $product_tosetfree ) && in_array($_cid, $product_categories) && !( $xa_cart_categories_items[$_cid] > 1 )) {
						$add_if_not_auto = $add_if_not_auto + 1;
					}
				}
				if ($add_if_not_auto > 0) {
					if ($cheapest_product_quantity >= $unit) {
						$free_unit = $unit - 1;
					} else {
						$free_unit = $cheapest_product_quantity ;
					}
				}

				if (empty($xa_cart_quantities[$_pid])) {
					$all_free_product_present = false;
				}

				if ($all_free_product_present == true && ( is_cart() || is_checkout() )) {

					$total_adjustment_price = 0;
					if (isset($adjustment) && is_numeric($adjustment)) {
						$total_adjustment_price = $adjustment * $free_unit;
					}

					$total_old_price = $old_price * ( $unit - (float) $free_unit );

					return ( ( $total_old_price + $total_adjustment_price ) ) / $unit;
				}
			}
		}

		if (isset($_REQUEST['debug'])) {
			echo "\n<div id='rules_info' style=''><pre> RuleType= " . $rule['rule_type'] . ' |   RuleNo=' . $rule_no . '  |   OldPrice=' . $old_price . '   |   OfferName=' . $rule['offer_name'] . '</pre></div>';
		}

		return $old_price;
	}

	public function elex_dp_Executebogo_tag_rules( $old_price, $rule_no, $rule, $pid = 0, $current_quantity = 1) {
		global $xa_dp_setting;
		global $woocommerce;
		global $xa_cart_quantities, $xa_cart_price, $xa_cart_tags_items;
		global $xa_cart_tags;
		extract($rule);
		if (empty($xa_cart_quantities[$pid])) {
			$xa_cart_quantities[$pid] = $current_quantity;
		}
		$product   = wc_get_product($pid);
		$parent_id = $pid;
		if (!empty($product) && $product->is_type('variation')) {
			$parent_id = elex_dp_is_wc_version_gt_eql('2.7') ? $product->get_parent_id() : $product->parent->id;
		}
		$rule['purchased_tag_id'] = elex_dp_WPML_Compatible_ids($rule['purchased_tag_id'], 'tag', true);
		$rule['free_product_id']  = elex_dp_WPML_Compatible_ids($rule['free_product_id'], 'product', true);

		$multiple = 1;
		if (!empty($rule['repeat_rule']) && $rule['repeat_rule'] == 'yes') {
			$multiple = 9999;
			foreach ($rule['purchased_tag_id'] as $_pid => $_qnty_and_checkon) {
				$_qnty_and_checkon_arr = explode(':', $_qnty_and_checkon);
				$_qnty                 = !empty($_qnty_and_checkon_arr[0]) ? $_qnty_and_checkon_arr[0] : 0;
				if ($_qnty_and_checkon_arr[1] == 'items') {
					$count = 0;
					foreach ($xa_cart_tags as $cart_prod_id => $cart_tagids) {
						if (in_array($_pid, $cart_tagids)) {
							$count++;
						}
					}
					if ($count != 0) {
						$tmp = (int) ( $count / $_qnty );
						if ($tmp > 1 && $tmp < $multiple) {
							$multiple = $tmp;
						}
					}
				} else {
					$total_items_with_tag = 0;
					foreach ($xa_cart_tags as $t_pid => $tag_ids) {
						if (in_array($_pid, $tag_ids)) {
							$total_items_with_tag += $xa_cart_quantities[$t_pid];
						}
					}

					if ($total_items_with_tag != 0) {
						$tmp = (int) ( $total_items_with_tag / $_qnty );
						if ($tmp > 1 && $tmp < $multiple) {
							$multiple = $tmp;
						}
					}
				}
			}
			if ($multiple == 9999) {
				$multiple = 1;
			}
		}

		//If free product is set
		if (!isset($rule['set_free_option']) || ( isset($rule['set_free_option']) && $rule['set_free_option'] == 'select_product' )) {

			////////if free product is already in cart with exact quanitty this code will set its price as zero
			if (in_array($pid, array_keys($rule['free_product_id'])) && $xa_cart_quantities[$pid] >= $rule['free_product_id'][$pid] && $xa_dp_setting['auto_add_free_product_on_off'] != 'on') {
				$all_free_product_present = true;
				foreach ($rule['free_product_id'] as $_pid => $_qnty) {
					if (empty($xa_cart_quantities[$_pid]) || $xa_cart_quantities[$_pid] < $_qnty) {
						$all_free_product_present = false;
						break;
					}
				}
				$unit      = isset($xa_cart_quantities[$pid]) ? $xa_cart_quantities[$pid] : 1;
				$free_unit = isset($rule['free_product_id'][$pid]) ? $rule['free_product_id'][$pid] : 0;

				$product_tags = !empty($xa_cart_tags[$pid]) ? $xa_cart_tags[$pid] : array();
				$add_if_not_auto = 0;
	
				foreach ($rule['purchased_tag_id'] as $_cid => $_qnty_and_checkon) {
	
					if (( array_key_exists($pid, $rule['free_product_id']) ) && in_array($_cid, $product_tags) && !( $xa_cart_tags_items[$_cid] > 1 )) {
						$add_if_not_auto = $add_if_not_auto + 1;
					}

				}
				if ($add_if_not_auto > 0) {
					if ($free_unit >= $unit) {
						$free_unit = $unit - 1;
					}
				}

				if ($all_free_product_present == true && ( is_cart() || is_checkout() )) {
					$total_adjustment_price = 0;
					if (isset($adjustment) && is_numeric($adjustment)) {
						if ($multiple >= $unit) { //to make repeat rule work with auto add disabled
							$total_adjustment_price = $adjustment * $free_unit * $unit;
						} else {
							$total_adjustment_price = $adjustment * $free_unit * $multiple;
						}
					} else {
						$total_adjustment_price = 0;
					}
					if (( $unit - (float) $free_unit * $multiple ) < 0) { //to make repeat rule work with auto add disabled
						$total_old_price = 0;
					} else {
						$total_old_price = $old_price * ( $unit - (float) $free_unit * $multiple );
					}
					if ($xa_dp_setting['auto_add_free_product_on_off'] == 'on') {
						return  $old_price;
					} else {
						return ( ( $total_old_price + $total_adjustment_price ) ) / $unit;
					}
				}
			}
			/////////////////////////////////////////////////////////
			$cart = $woocommerce->cart;
			if ($xa_dp_setting['auto_add_free_product_on_off'] == 'on') {         // only works for different products

				foreach ($free_product_id as $pid2 => $qnty2) {
					// Check if the product is not in the trash
					$product_status = get_post_status($pid2);
					if ($product_status === 'trash') {
						continue;
					}
					$product_data = wc_get_product($pid2);
					if (empty($pid2) || empty($product_data)) {
						continue;
					}
					if (isset($adjustment) && is_numeric($adjustment)) {
						$product_data->set_price($adjustment);
					} else {
						$product_data->set_price(0.0);
					}

					$cart_item_key                       = 'FreeForRule-' . $rule['rule_type'] . '-' . $rule['rule_no'] . '-' . md5($pid2);
					$cart->cart_contents[$cart_item_key] = array(
						'product_id' => $pid2,
						'variation_id' => 0,
						'variation' => array(),
						'quantity' => $qnty2 * $multiple,
						'data' => $product_data,
						'line_total' => 0,
						'line_subtotal' => 0,
						'line_subtotal_tax' => 0
					);
				}
			}
		} elseif ($rule['set_free_option'] == 'set_cheapest') {
			//Set the cheapest product in cart free.
			$cheapest_product_quantity = !empty($rule['cheapest_product_quantity']) ? $rule['cheapest_product_quantity'] : 1;
			$all_free_product_present = false;
			$minvalue                 = !empty($xa_cart_price) ? max($xa_cart_price) : '';
			$product_tosetfree        = '';
			foreach ($xa_cart_quantities as $_pid => $_qnty) {
				$_product = wc_get_product($_pid);
				if ($_product->get_price() <= $minvalue) {
					$minvalue                 = $_product->get_price();
					$product_tosetfree        = $_pid;
					$all_free_product_present = true;
				}
			}

			$unit      = isset($xa_cart_quantities[$product_tosetfree]) ? $xa_cart_quantities[$product_tosetfree] : 1;
			$free_unit = $cheapest_product_quantity > $unit ? $unit : $cheapest_product_quantity;

			////////if free product is already in cart with exact quanitty this code will set its price as zero
			if (( $pid == $product_tosetfree )) {

				$product_tags = !empty($xa_cart_tags[$pid]) ? $xa_cart_tags[$pid] : array();
				$add_if_not_auto = 0;
	
				foreach ($rule['purchased_tag_id'] as $_cid => $_qnty_and_checkon) {
	
					if (( $pid == $product_tosetfree ) && in_array($_cid, $product_tags) && !( $xa_cart_tags_items[$_cid] > 1 )) {
						$add_if_not_auto = $add_if_not_auto + 1;
					}
				}
				if ($add_if_not_auto > 0) {
					if ($cheapest_product_quantity >= $unit) {
						$free_unit = $unit - 1;
					} else {
						$free_unit = $cheapest_product_quantity ;
					}
				}

				if (empty($xa_cart_quantities[$_pid])) {
					$all_free_product_present = false;
				}


				if ($all_free_product_present == true && ( is_cart() || is_checkout() )) {

					$total_adjustment_price = 0;
					if (isset($adjustment) && is_numeric($adjustment)) {
						if ($multiple >= $unit) { //to make repeat rule work with auto add disabled
							$total_adjustment_price = $adjustment * $free_unit * $unit;
						} else {
							$total_adjustment_price = $adjustment * $free_unit * $multiple;
						}
					} else {
						$total_adjustment_price = 0;
					}
					if (( $unit - (float) $free_unit * $multiple ) < 0) { //to make repeat rule work with auto add disabled
						$total_old_price = 0;
					} else {
						$total_old_price = $old_price * ( $unit - (float) $free_unit * $multiple );
					}

					return ( ( $total_old_price + $total_adjustment_price ) ) / $unit;
				}
			}
		}

		if (isset($_REQUEST['debug'])) {
			echo "\n<div id='rules_info' style=''><pre> RuleType= " . $rule['rule_type'] . ' |   RuleNo=' . $rule_no . '  |   OldPrice=' . $old_price . '   |   OfferName=' . $rule['offer_name'] . '</pre></div>';
		}

		return $old_price;
	}

	public function validate_product_matches_categories( $product, $rule) {
		if ($product->is_type('variation')) {
			$parent_id          = elex_dp_is_wc_version_gt_eql('2.7') ? $product->get_parent_id() : $product->parent->id;
			$parent_product     = wc_get_product($parent_id);
			$product_categories = elex_dp_is_wc_version_gt_eql('2.7') ? $parent_product->get_category_ids() : elex_dp_get_category_ids($parent_product);
		} else {
			$product_categories = elex_dp_is_wc_version_gt_eql('2.7') ? $product->get_category_ids() : elex_dp_get_category_ids($product);
		}
		$cids    = array();
		$cat_ids = $rule['category_id'];
		if (!is_array($cat_ids)) {
			$cat_ids = array($cat_ids);
		}
		foreach ($cat_ids as $_cid) {
			$cids[] = elex_dp_WPML_Compatible_ids($_cid, 'category');
		}
		$matched = array_intersect($cids, $product_categories);
		if (empty($cids) || empty($matched)) {
			return false;
		}

		return true;
	}
}
