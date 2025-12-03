<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (isset($_REQUEST['edit']) && !empty($_REQUEST['edit'])) {
	$edit_value = sanitize_text_field($_REQUEST['edit']);
	echo '<input type="hidden" name="update" value="' . esc_attr($edit_value) . '" >';
}
?>

<div class="elex-dynamic-pricing-popup bg-dark bg-opacity-50 h-100 w-100 elex-dynamic-pricing-popup">
	<div class="row w-100 h-100 m-0">
		<div class="col-lg-10 ms-auto bg-white elex-dynamic-pricing-popup-content">
			<div class="w-100 h-100 p-4 ">

				<!-- popup header -->
				<div class="position-relative my-4">
					<?php if (isset($_REQUEST['edit']) && !empty($_REQUEST['edit'])) { ?>
						<h6 class="text-center"><?php esc_html_e('Update Category Rule', 'eh-dynamic-pricing-discounts'); ?></h6>
					<?php } else { ?>
						<h6 class="text-center"><?php esc_html_e('Add New Category Rule', 'eh-dynamic-pricing-discounts'); ?></h6>
					<?php } ?>
					<button id="cancel_btn" name="cancel_btn" 
						class="btn-close position-absolute top-50 end-0 translate-middle-y elex-dynamic-pricing-popup-close-btn"></button>
				</div>

				<!-- popup main content -->
				<div class="border  d-flex mb-5 elex-dynamic-pricing-popup-tab-box">

					<!-- tab links -->
					<div class="elex-dynamic-pricing-popup-tabs nav flex-column nav-pills py-3 px-1 border-0 border-end"
						id="elex-dynamic-add-new-product-rule-tabs" role="tablist"
						aria-orientation="vertical">
						<div class="nav-link active d-flex gap-2"
							id=" elex-dynamic-add-new-category-rule-btn" data-bs-toggle="pill"
							data-bs-target="#elex-dynamic-add-new-category-rule" type="button" role="tab"
							aria-controls="elex-dynamic-add-new-category-rule" aria-selected="true">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="20"
								viewBox="0 0 24 20">
								<g id="Icon_feather-sliders" data-name="Icon feather-sliders"
									transform="translate(0 -2)">
									<path id="Path_482" data-name="Path 482" d="M4,21V14"
										stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
									<path id="Path_483" data-name="Path 483" d="M4,10V3"
										stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
									<path id="Path_484" data-name="Path 484" d="M12,21V12"
										stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
									<path id="Path_485" data-name="Path 485" d="M12,8V3"
										stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
									<path id="Path_486" data-name="Path 486" d="M20,21V16"
										stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
									<path id="Path_487" data-name="Path 487" d="M20,12V3"
										stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
									<path id="Path_488" data-name="Path 488" d="M1,14H7"
										stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
									<path id="Path_489" data-name="Path 489" d="M9,8h6"
										stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
									<path id="Path_490" data-name="Path 490" d="M17,16h6"
										stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
								</g>
							</svg>
							<?php esc_html_e('Rule', 'eh-dynamic-pricing-discounts'); ?>
						</div>
						<div class="nav-link elex-adjustment-tab" id="elex-dynamic-add-new-category-adjustment-btn"
							data-bs-toggle="pill" data-bs-target="#elex-dynamic-add-new-category-adjustment"
							type="button" role="tab"
							aria-controls="elex-dynamic-add-new-category-adjustment" aria-selected="false">
							<svg xmlns="http://www.w3.org/2000/svg" width="21.307" height="21.307"
								viewBox="0 0 21.307 21.307">
								<path id="tool"
									d="M14.7,6.3a1,1,0,0,0,0,1.4l1.6,1.6a1,1,0,0,0,1.4,0l3.77-3.77a6,6,0,0,1-7.94,7.94L6.62,20.38a2.121,2.121,0,0,1-3-3l6.91-6.91a6,6,0,0,1,7.94-7.94L14.71,6.29Z"
									transform="translate(-1.809 -0.884)" 
									stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
							</svg>
							<span><?php esc_html_e('Adjustments', 'eh-dynamic-pricing-discounts'); ?><sup class="elex_dp_go_premium_color"><?php esc_html_e('[Premium]', 'eh-dynamic-pricing-discounts'); ?></sup></span>
						</div>
						<div class="nav-link" id="elex-dynamic-add-new-category-associate-btn"
							data-bs-toggle="pill" data-bs-target="#elex-dynamic-add-new-category-associate"
							type="button" role="tab" aria-controls="elex-dynamic-add-new-category-associate"
							aria-selected="false">
							<svg xmlns="http://www.w3.org/2000/svg" width="22.183" height="21.995" viewBox="0 0 22.183 21.995">
								<g id="Icon_feather-tag" data-name="Icon feather-tag" transform="translate(1.183 1)">
									<path id="Path_520" data-name="Path 520" d="M3.607,14.9l7.48,7.48a2.086,2.086,0,0,0,2.952,0L23,13.432V3H12.568L3.607,11.961A2.086,2.086,0,0,0,3.607,14.9Z" transform="translate(-3 -3)"  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
									<path id="Path_521" data-name="Path 521" d="M10.5,10.5h0" transform="translate(4.284 -5.284)"  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
								</g>
								</svg>
							<span><?php esc_html_e('Allowed Roles & Dates', 'eh-dynamic-pricing-discounts'); ?><sup class="elex_dp_go_premium_color"><?php esc_html_e('[Premium]', 'eh-dynamic-pricing-discounts'); ?></sup></span>
						</div>
						<div class="nav-link" id="elex-dynamic-add-new-category-restrictions-btn"
							data-bs-toggle="pill"
							data-bs-target="#elex-dynamic-add-new-category-restrictions" type="button"
							role="tab" aria-controls="elex-dynamic-add-new-category-restrictions"
							aria-selected="false">
							<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22">
								<g id="Icon_feather-target" data-name="Icon feather-target" transform="translate(-1 -1)">
									<path id="Path_495" data-name="Path 495" d="M22,12A10,10,0,1,1,12,2,10,10,0,0,1,22,12Z"  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
									<path id="Path_496" data-name="Path 496" d="M18,12a6,6,0,1,1-6-6A6,6,0,0,1,18,12Z"  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
									<path id="Path_497" data-name="Path 497" d="M14,12a2,2,0,1,1-2-2A2,2,0,0,1,14,12Z"  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
								</g>
								</svg>
							<span><?php esc_html_e('Restrictions', 'eh-dynamic-pricing-discounts'); ?><sup class="elex_dp_go_premium_color"><?php esc_html_e('[Premium]', 'eh-dynamic-pricing-discounts'); ?></sup></span>
						</div>
					</div>

					<!-- tabs content -->
					<div class="tab-content flex-fill p-3"
						id="elex-dynamic-add-new-category-rule-tabs-content">

						<!-- rule content -->
						<div class="tab-pane fade show active" id="elex-dynamic-add-new-category-rule"
							role="tabpanel" aria-labelledby="elex-dynamic-add-new-category-rule-btn">
							<div class="row mb-3 align-items-center">
								<div class="col-md-4">
									
									<div class="d-flex gap-2 justify-content-between">
										<h6 class="mb-0 elex-dynamic-input-label"><?php esc_html_e('Offer Name', 'eh-dynamic-pricing-discounts'); ?><span class="text-danger">*</span></h6>
										<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('Name/Text of the offer to be displayed in the Offer Table. We suggest a detailed description of the discount.', 'eh-dynamic-pricing-discounts'); ?>"></i>
									</div>
									
								</div>
								<div class="col-md-8">
									<input id='offer_name' name='offer_name' type="text" class="form-control" placeholder="<?php esc_attr_e('Enter a descriptive offer name', 'eh-dynamic-pricing-discounts'); ?>"
										value="<?php echo !empty($_REQUEST['offer_name']) ? esc_attr($_REQUEST['offer_name']) : ''; ?>" required>
								</div>
							</div>

							<div class="row mb-3 align-items-center">
								<div class="col-md-4">
								<div class="d-flex gap-2 justify-content-between">
									<h6 class="elex-dynamic-input-label"><?php esc_html_e('Product Categories', 'eh-dynamic-pricing-discounts'); ?><span class="text-danger">*</span></h6>
									<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('Select the category for which the rule would be applied.', 'eh-dynamic-pricing-discounts'); ?>"></i>
								</div>
								</div>
								<div class="col-md-8">
									<select id="category_id" name="category_id[]" multiple class="wc-enhanced-select form-select min-width-100"  data-placeholder="<?php esc_attr_e('Search for a category...', 'eh-dynamic-pricing-discounts'); ?>" required>
										<?php
										$category_ids = !empty($_REQUEST['category_id']) ? $_REQUEST['category_id'] : '';  //selected product categorie
										if (!is_array($category_ids)) {
											$category_ids =array($category_ids);
										}
										$categories   = get_terms('product_cat', 'orderby=name&hide_empty=0');
										if ($categories) {
											foreach ($categories as $cat) {
												echo '<option value="' . esc_attr($cat->term_id) . '"' . selected(in_array($cat->term_id, $category_ids), true, false) . '>' . esc_html($cat->name) . '</option>';
											}
										}
										?>
									</select>
								</div>
							</div>

							<div class="row mb-3 align-items-center">
								<div class="col-md-4">
									
									<div class="d-flex gap-2 justify-content-between">
										<h6 class="mb-0 elex-dynamic-input-label"><?php esc_html_e('Check for', 'eh-dynamic-pricing-discounts'); ?><span class="text-danger">*</span></h6>
										<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('The rules can be applied based on `No. of items/Price/Weight/No. of Units`', 'eh-dynamic-pricing-discounts'); ?>"></i>
									</div>
									
								</div>
								<div class="col-md-8">
									<select name="check_on" id="check_on" class="form-select min-width-100">
										<option value="Quantity" <?php selected(!empty($_REQUEST['check_on']) && ( $_REQUEST['check_on'] == 'Quantity' )); ?>><?php esc_html_e('No. of items', 'eh-dynamic-pricing-discounts'); ?></option>
										<option value="Weight" <?php selected(!empty($_REQUEST['check_on']) && ( $_REQUEST['check_on'] == 'Weight' )); ?>><?php esc_html_e('Weight', 'eh-dynamic-pricing-discounts'); ?></option>
										<option value="Price" <?php selected(!empty($_REQUEST['check_on']) && ( $_REQUEST['check_on'] == 'Price' )); ?>><?php esc_html_e('Price', 'eh-dynamic-pricing-discounts'); ?></option>
										<option value="TotalQuantity" <?php selected(!empty($_REQUEST['check_on']) && ( $_REQUEST['check_on'] == 'TotalQuantity' )); ?>><?php esc_html_e('No. of Units', 'eh-dynamic-pricing-discounts'); ?></option>
									</select>
								</div>
							</div>

							<div class="row mb-3 align-items-center">
								<div class="col-md-4">
									
									<div class="d-flex gap-2 justify-content-between">
										<h6 class="mb-0 elex-dynamic-input-label" id='minprice'><?php esc_html_e('Minimum Price', 'eh-dynamic-pricing-discounts'); ?><span class="text-danger">*</span></h6>
										<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('Minimum value to check', 'eh-dynamic-pricing-discounts'); ?>"></i>
									</div>
									
								</div>
								<div class="col-md-8">
									<input id='min' name='min' type="number" class="form-control" step='any' min="0"
										value="<?php echo !empty($_REQUEST['min']) ? esc_attr($_REQUEST['min']) : '1'; ?>" required>
								</div>
							</div>

							<div class="row mb-3 align-items-center">
								<div class="col-md-4">
									
									<div class="d-flex gap-2 justify-content-between">
										<h6 class="mb-0 elex-dynamic-input-label" id='maxprice'><?php esc_html_e('Maximum Price', 'eh-dynamic-pricing-discounts'); ?></h6>
										<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('Maximum value to check, set it empty for no limit', 'eh-dynamic-pricing-discounts'); ?>"></i>
									</div>
									
								</div>
								<div class="col-md-8">
									<input id='max' name='max' type="number" class="form-control" step='any' min="0"
										value="<?php echo !empty($_REQUEST['max']) ? esc_attr($_REQUEST['max']) : ''; ?>">
								</div>
							</div>

							<!--rules discount type -->
							<div class=" elex-dynamic-pricing-discount-types">
								<div class="row mb-3 align-items-center">
									<div class="col-md-4">
									<div class="d-flex gap-2 justify-content-between">
										<h6 class="mb-0 elex-dynamic-input-label"><?php esc_html_e('Discount Type', 'eh-dynamic-pricing-discounts'); ?><span class="text-danger">*</span> </h6>
										<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('Four types of discounts can be applied – `Percentage Discount/Flat Discount/Fixed Price/Coupon discount`.', 'eh-dynamic-pricing-discounts'); ?>"></i>
									</div>
									</div>
									<div class="col-md-8">
										<select name="discount_type" id="discount_type" 
											class="form-select min-width-100 elex-dynamic-pricing-discount-type-select">
											<option value="Percent Discount" <?php selected(!empty($_REQUEST['discount_type']) && ( $_REQUEST['discount_type'] == 'Percent Discount' )); ?>><?php esc_html_e('Percent Discount', 'eh-dynamic-pricing-discounts'); ?></option>
											<option value="Flat Discount" <?php selected(!empty($_REQUEST['discount_type']) && ( $_REQUEST['discount_type'] == 'Flat Discount' )); ?>><?php esc_html_e('Flat Discount', 'eh-dynamic-pricing-discounts'); ?></option>
											<option value="Fixed Price" <?php selected(!empty($_REQUEST['discount_type']) && ( $_REQUEST['discount_type'] == 'Fixed Price' )); ?>><?php esc_html_e('Fixed Price', 'eh-dynamic-pricing-discounts'); ?></option>
											<option value="Coupon Discount" disabled><?php esc_html_e('Coupon Discount', 'eh-dynamic-pricing-discounts'); ?>
												<sup class="elex_dp_go_premium_color"><?php esc_html_e('[Premium]', 'eh-dynamic-pricing-discounts'); ?></sup></option>
										</select>
									</div>
								</div>
								<!-- discount percent -->
								<div class="row mb-3 align-items-center elex-dynamic-pricing-discount-type-value">
									<div class="col-md-4" id="discount_type_namesection">
										<div class="d-flex gap-2 justify-content-between">
											<h6 id="discount_value" class="mb-0 elex-dynamic-input-label"><?php esc_html_e('Discount Percent', 'eh-dynamic-pricing-discounts'); ?><span class="text-danger">*</span> </h6>
											<i id="discount_desc" class="desc-tip fa-regular fa-circle-question" data-tip='
											<?php
											echo esc_attr__('If you select `Percentage Discount`, the given percentage (value) would be discounted on each unit of the product in the cart.
	If you select `Flat Discount`, the given amount (value) would be discounted at subtotal level in the cart.
	If you select `Fixed Price`, the original price of the product is replaced by the given fixed price (value).
	If you select `Coupon dicount`, the original price of the cart total is discounted with the selected coupon offer (value).');
											?>
											'></i>
										</div>
									</div>
									<div class="col-md-8">
										<input id='value1' name='value' type="number" class="form-control" step='any' min="0"
											value="<?php echo !empty($_REQUEST['value']) ? esc_attr($_REQUEST['value']) : ''; ?>" required>
									</div>
								</div>
							</div>
						</div>

						<!-- adjustment content -->
						<div class="tab-pane fade" id="elex-dynamic-add-new-category-adjustment"
							role="tabpanel" aria-labelledby="elex-dynamic-add-new-category-adjustment-btn">
							<div class="row mb-3 align-items-center" id="max_discount_parent">
								<div class="col-md-4">
									<div class="d-flex gap-2 justify-content-between">
										<h6 class="mb-0 elex-dynamic-input-label"><?php esc_html_e('Maximum Discount Amount', 'eh-dynamic-pricing-discounts'); ?></h6>
										<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('This value is used to set up a limit for the discount. This is usually left blank if you do not want to limit the discount.', 'eh-dynamic-pricing-discounts'); ?>"></i>
									</div>
								</div>
								<div class="col-md-8">
									<input id='max_discount' name='max_discount' type="number" class="form-control" step = 'any' min="0"
									value = "" disabled>
								</div>
							</div>
							<div class="row mb-3 align-items-center">
								<div class="col-md-4">
									
									<div class="d-flex gap-2 justify-content-between">
										<h6 class="mb-0 elex-dynamic-input-label"><?php esc_html_e('Adjustment Amount', 'eh-dynamic-pricing-discounts'); ?></h6>
										<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('Adjust final discount amount by this amount', 'eh-dynamic-pricing-discounts'); ?>"></i>
									</div>
									
								</div>
								<div class="col-md-8">
									<input id='adjustment' name='adjustment' type="number" class="form-control" step = 'any' min="0"
									value = "" disabled>
								</div>
							</div>
						</div>

						<!-- Associate Coupon & Roles content -->
						<div class="tab-pane fade" id="elex-dynamic-add-new-category-associate"
							role="tabpanel" aria-labelledby="elex-dynamic-add-new-category-associate-btn">
							<div class="row mb-3 align-items-center">
								<div class="col-md-4">
									<div class="d-flex gap-2 justify-content-between">
										<h6 class="mb-0 elex-dynamic-input-label"><?php esc_html_e('Discount Valid From Date', 'eh-dynamic-pricing-discounts'); ?></h6>
										<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('The date from which the rule would be applied. This can be left blank if you do not wish to set up any date range.', 'eh-dynamic-pricing-discounts'); ?>"></i>
									</div>
								</div>
								<div class="col-md-8">
									<input id='from_date' name='from_date' type="date" class="form-control" step = 'any' placeholder="YYYY-MM-DD" custom_attributes = "<?php "echo ['pattern' => apply_filters('woocommerce_date_input_html_pattern', '(0[1-9]|1[0-9]|2[0-9]|3[01])-(0[1-9]|1[012])-[0-9]{4} (0[0-9]|1[0-9]|2[0-3]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])')]"; ?>"
									value = "" disabled>
								</div>
							</div>

							<div class="row mb-3 align-items-center">
								<div class="col-md-4">
									<div class="d-flex gap-2 justify-content-between">
										<h6 class="mb-0 elex-dynamic-input-label"><?php esc_html_e('Discount Valid Till Date', 'eh-dynamic-pricing-discounts'); ?></h6>
										<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('The date till which the rule would be valid. You can leave it blank if you wish the rule to be applied forever or would like to end it manually.', 'eh-dynamic-pricing-discounts'); ?>"></i>
									</div>
								</div>
								<div class="col-md-8">
								   <input id='to_date' name='to_date' type="date" class="form-control" step = 'any' placeholder="YYYY-MM-DD" custom_attributes = "<?php echo "['pattern' => apply_filters('woocommerce_date_input_html_pattern', '(0[1-9]|1[0-9]|2[0-9]|3[01])-(0[1-9]|1[012])-[0-9]{4} (0[0-9]|1[0-9]|2[0-3]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])')]"; ?>"
									value = "" disabled>
								</div>
							</div>

							<div class="row mb-3 align-items-center">
								<div class="col-md-4">
									<div class="d-flex gap-2 justify-content-between">
										<h6 class="mb-0 elex-dynamic-input-label"><?php esc_html_e('Allowed Roles', 'eh-dynamic-pricing-discounts'); ?></h6>
										<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('Select the roles for which you want to apply this discount rule. If no user role is selected, the rule will be applied to all user roles by default.', 'eh-dynamic-pricing-discounts'); ?>"></i>
									</div>
								</div>
								<div class="col-md-8">
								<select id="allow_roles" name="allow_roles[]" class="wc-enhanced-select form-select span-w-100"  multiple="" tabindex="-1" aria-hidden="true" data-placeholder= "<?php esc_attr_e('Select roles ...', 'eh-dynamic-pricing-discounts'); ?>" disabled>
									<option></option>
								</select>
								</div>
							</div>
						</div>

						<!-- Restrictions -->
						<div class="tab-pane fade" id="elex-dynamic-add-new-category-restrictions"
							role="tabpanel"
							aria-labelledby="elex-dynamic-add-new-category-restrictions-btn">

							<div class="row mb-3 align-items-center">
								<div class="col-md-4">
									
									<div class="d-flex gap-2 justify-content-between">
										<h6 class="mb-0 elex-dynamic-input-label"><?php esc_html_e('Allowed Email Ids', 'eh-dynamic-pricing-discounts'); ?></h6>
										<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('Enter Email ids separated by commas, for which you want to allow this rule. and leave blank to allow for all', 'eh-dynamic-pricing-discounts'); ?>"></i>
									</div>
									
								</div>
								<div class="col-md-8">
									<input id='email_ids' name='email_ids' type="text" class="form-control" step='any' placeholder="<?php esc_attr_e('Enter email ids separated by commas', 'eh-dynamic-pricing-discounts'); ?>"
										value="" disabled>
								</div>
							</div>

							<div class="row mb-3 align-items-center">
								<div class="col-md-4">
									
									<div class="d-flex gap-2 justify-content-between">
										<h6 class="mb-0 elex-dynamic-input-label"><?php esc_html_e('Minimum Number of Previous Orders', 'eh-dynamic-pricing-discounts'); ?></h6>
										<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('Minimum count of previous orders required for this rule to be executed', 'eh-dynamic-pricing-discounts'); ?>"></i>
									</div>
									
								</div>
								<div class="col-md-8">
								   <input id='prev_order_count' name='prev_order_count' type="number" class="form-control" step = "1" min="0"
									value = "" disabled>
								</div>
							</div>

							<div class="row mb-3 align-items-center">
								<div class="col-md-4">
									
									<div class="d-flex gap-2 justify-content-between">
										<h6 class="mb-0 elex-dynamic-input-label"><?php esc_html_e('Minimum Total Spending on Previous Orders', 'eh-dynamic-pricing-discounts'); ?></h6>
										<i class="desc-tip fa-regular fa-circle-question" data-tip="<?php echo esc_attr__('Minimum amount the user has spent till now for the rule to execute. Total calculated from all previous orders', 'eh-dynamic-pricing-discounts'); ?>"></i>
									</div>
									
								</div>
								<div class="col-md-8">
								   <input id='prev_order_total_amt' name='prev_order_total_amt' type="number" class="form-control" step = "1" min="0"
									value = "" disabled>
								</div>
							</div>
						</div>
					</div>

				</div>

				<div class="row">
					<div class="col-md-2">
						<button id="cancel_btn" name="cancel_btn" class="btn btn-outline-secondary w-100 elex-dynamic-pricing-popup-close-btn"><?php esc_html_e('Cancel', 'eh-dynamic-pricing-discounts'); ?></button>
					</div>

					<div class="col-md-2">
						<button type="submit" name="update" id="update" value="<?php echo esc_attr(!empty($_REQUEST['edit']) ? $_REQUEST['edit'] : ''); ?>" class="btn btn-primary w-100">
							<?php
							if (isset($_REQUEST['edit'])) {
								esc_html_e('Update Rule', 'eh-dynamic-pricing-discounts');
							} else {
								esc_html_e('Save Rule', 'eh-dynamic-pricing-discounts');
							}
							?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>
