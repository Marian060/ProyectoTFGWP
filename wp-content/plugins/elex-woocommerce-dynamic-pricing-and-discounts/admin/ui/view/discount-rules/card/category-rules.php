<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
?>

<div class="border elex-border-secondary-light rounded p-1 elex-dynamic-pricing-rules mb-3 listitemClass" id="<?php echo esc_attr($key); ?>">
	<div class="pe-3 pt-3 p-1 d-flex gap-2">
		<div class="icon-move" style="cursor: move">
			<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
				<g id="Icon_feather-grid" data-name="Icon feather-grid" transform="translate(0.75 0.75)">
					<path id="Path_508" data-name="Path 508" d="M2.25,2.25H7.5V7.5H2.25Z" transform="translate(-2.25 -2.25)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" />
					<path id="Path_509" data-name="Path 509" d="M10.5,2.25h5.25V7.5H10.5Z" transform="translate(-2.25 -2.25)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" />
					<path id="Path_510" data-name="Path 510" d="M10.5,10.5h5.25v5.25H10.5Z" transform="translate(-2.25 -2.25)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" />
					<path id="Path_511" data-name="Path 511" d="M2.25,10.5H7.5v5.25H2.25Z" transform="translate(-2.25 -2.25)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" />
				</g>
			</svg>
		</div>
		<div class="flex-fill">
			<div class="d-flex border-0 border-bottom elex-border-secondary-light pb-3  mb-3">
				<div class="flex-fill">
					<div class="d-flex justify-content-between mb-3">
						<h6 class="mb-0"><?php esc_html_e('Rule #: ', 'eh-dynamic-pricing-discounts'); ?><span class="fw-normal"><?php echo esc_html($key); ?></span> | <?php esc_html_e('Rule Name: ', 'eh-dynamic-pricing-discounts'); ?><span class="fw-normal"><?php echo ( isset($value['offer_name']) && !empty($value['offer_name']) ) ? esc_html($value['offer_name']) : ' - - '; ?></span></h6>
						<h6 class="mb-0"><?php esc_html_e('Validty: ', 'eh-dynamic-pricing-discounts'); ?><span class="fw-normal"><?php echo ( isset($value['from_date']) && !empty($value['from_date']) && isset($value['to_date']) && !empty($value['to_date']) ) ? esc_html($value['from_date'] . '/' . esc_html($value['to_date'])) : ' --/--'; ?></span>
						</h6>
					</div>

					<div class="d-flex justify-content-between ">
						<h6 class="mb-0"><?php esc_html_e('Discount Type: ', 'eh-dynamic-pricing-discounts'); ?><span class="fw-normal"><?php echo ( isset($value['discount_type']) && !empty($value['discount_type']) )? esc_html($value['discount_type']) : ' - - '; ?></span>
						</h6>
						<h6 class="mb-0"><?php esc_html_e('Discount Value: ', 'eh-dynamic-pricing-discounts'); ?><span class="fw-normal"><?php echo ( isset($value['value']) && !empty($value['value']) ) ? esc_html($value['value']) : ' - - '; ?></span>
						</h6>
						<h6 class="mb-0"><?php esc_html_e('Max Discount: ', 'eh-dynamic-pricing-discounts'); ?><span class="fw-normal"><?php echo ( isset($value['max_discount']) && !empty($value['max_discount']) ) ? esc_html($value['max_discount']) : ' - - '; ?></span></h6>
					</div>
				</div>

				<!-- edit and delete option -->
				<div class="border-0 border-start ms-3 px-2">
					<label class=" md:font-bold m-0 mb-3"><b><?php esc_html_e('Actions', 'eh-dynamic-pricing-discounts'); ?></b></label>
					<div class="d-flex align-items-center ">
						<button class="btn btn-sm btn-white rounded-circle primary-hover elex-ac-edit-rule-up-btn" data-bs-custom-class="tooltip-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Move Up">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="14"><path d="M233.4 105.4c12.5-12.5 32.8-12.5 45.3 0l192 192c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L256 173.3 86.6 342.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l192-192z"/></svg>
						</button>
						<button class="btn btn-sm btn-white rounded-circle primary-hover elex-ac-edit-rule-down-btn" data-bs-custom-class="tooltip-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Move Down">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="14"><path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z"/></svg>
						</button>
						<button class="btn btn-sm btn-white rounded-circle success-hover elex-ac-edit-rule-remove-btn" data-bs-custom-class="tooltip-outline-success" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php esc_attr_e('Edit', 'eh-dynamic-pricing-discounts'); ?>" type="submit" name="edit" value="<?php echo esc_attr($key); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="14"><path d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152L0 424c0 48.6 39.4 88 88 88l272 0c48.6 0 88-39.4 88-88l0-112c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 112c0 22.1-17.9 40-40 40L88 464c-22.1 0-40-17.9-40-40l0-272c0-22.1 17.9-40 40-40l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L88 64z"/></svg>
						</button>
						<button class="btn btn-sm btn-white rounded-circle danger-hover elex-ac-edit-rule-delete-btn" data-bs-custom-class="tooltip-outline-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php esc_attr_e('Delete', 'eh-dynamic-pricing-discounts'); ?>" type="submit" name="delete" value="<?php echo esc_attr($key); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="14"><path d="M135.2 17.7C140.6 6.8 151.7 0 163.8 0L284.2 0c12.1 0 23.2 6.8 28.6 17.7L320 32l96 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 96C14.3 96 0 81.7 0 64S14.3 32 32 32l96 0 7.2-14.3zM32 128l384 0 0 320c0 35.3-28.7 64-64 64L96 512c-35.3 0-64-28.7-64-64l0-320zm96 64c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16z"/></svg>
						</button>
					</div>
				</div>
			</div>

			<div class="pb-2">
				<h6><?php esc_html_e('Category', 'eh-dynamic-pricing-discounts'); ?></h6>
				<div class="d-flex gap-2 flex-wrap">
					<?php
					if (isset($value['category_id']) && !empty($value['category_id'])) {
						if (!is_array($value['category_id'])) {
							$value['category_id'] = array($value['category_id']);
						}
						foreach ($value['category_id'] as $cid) {
							$product = elex_dp_get_product_category_by_id($cid);
							if (!empty($cid) && !empty($product)) {
								echo '<div class="btn elex-light-blue-bg">' . esc_html($product) . '</div>';
							}
						}
					} else {
						echo '<div class="btn elex-light-blue-bg">' . esc_html__(' - - ', 'eh-dynamic-pricing-discounts') . '</div>';
					}
					?>
				</div>
			</div>
		</div>
	</div>

	<div class="elex-dynamic-pricing-more-details">
		<div class=" bg-light rounded-bottom ">
			<div class="p-3 elex-dynamic-pricing-show-details-btn">
				<div class="d-flex w-100 justify-content-end align-items-center gap-3">
					<?php esc_html_e('Show More Details', 'eh-dynamic-pricing-discounts'); ?>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16">
						<path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z" />
					</svg>
				</div>
			</div>

			<div class="p-3 w-100 elex-dynamic-pricing-hide-details-btn">
				<div class="d-flex w-100 justify-content-end align-items-center gap-3">
					<?php esc_html_e('Hide Details', 'eh-dynamic-pricing-discounts'); ?>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16">
						<path d="M233.4 105.4c12.5-12.5 32.8-12.5 45.3 0l192 192c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L256 173.3 86.6 342.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l192-192z" />
					</svg>
				</div>
			</div>
		</div>

		<div class="p-3 pt-2 elex-dynamic-pricing-show-details">
			<div class="border-0 border-bottom mb-3">
				<div class="d-flex gap-4">
					<h6><?php esc_html_e('Check On: ', 'eh-dynamic-pricing-discounts'); ?><span class="fw-normal"><?php echo esc_html(isset($checkon) && !empty($checkon) ? $checkon : ' - - '); ?></span></h6>
					<h6><?php esc_html_e('Minimum ', 'eh-dynamic-pricing-discounts'); ?><?php echo esc_html(isset($checkon) && !empty($checkon) ? $checkon : ' - - '); ?>: <span class="fw-normal"><?php echo esc_html(isset($value['min']) && !empty($value['min']) ? $value['min'] : ' - - '); ?></span></h6>
					<h6><?php esc_html_e('Maximum ', 'eh-dynamic-pricing-discounts'); ?><?php echo esc_html(isset($checkon) && !empty($checkon) ? $checkon : ' - - '); ?>: <span class="fw-normal"><?php echo esc_html(isset($value['max']) && !empty($value['max']) ? $value['max'] : ' - - '); ?></span></h6>
				</div>
			</div>

			<div class="border-0 border-bottom mb-3">
				<div class="d-flex gap-2 align-items-center mb-3">
					<h6 class="mb-0"><?php esc_html_e('Associated Coupon: ', 'eh-dynamic-pricing-discounts'); ?></h6>
					<div class="d-flex gap-2">
						<div class="btn elex-light-blue-bg"><?php echo esc_html(isset($value['coupon_code']) && !empty($value['coupon_code']) ? $value['coupon_code'] : ' - - '); ?></div>
					</div>
				</div>
			</div>

			<div class="border-0 border-bottom mb-3">
				<div class="d-flex gap-2 align-items-center mb-3">
					<h6 class="mb-0"><?php esc_html_e('Allowed Roles: ', 'eh-dynamic-pricing-discounts'); ?></h6>
					<div class="d-flex gap-2">
						<?php
						if (isset($value['allow_roles']) && !empty($value['allow_roles'])) {
							foreach ($value['allow_roles'] as $role) {
								if (!empty($role)) {
									echo '<div class="btn elex-light-blue-bg">' . esc_html($role) . '</div>';
								}
							}
						} else {
							echo '<div class="btn elex-light-blue-bg">' . esc_html__(' - - ', 'eh-dynamic-pricing-discounts') . '</div>';
						}
						?>
					</div>
				</div>
			</div>

			<div class="border-0 border-bottom mb-3">
				<div class="d-flex gap-2 align-items-center mb-3">
					<h6 class="mb-0"><?php esc_html_e('Allowed Amount:', 'eh-dynamic-pricing-discounts'); ?> <span class="fw-normal"><?php echo esc_html(isset($value['adjustment']) && !empty($value['adjustment']) ? $value['adjustment'] : ' - - '); ?></span></h6>
				</div>
			</div>


			<div>
				<h6><?php esc_html_e('Restrictions', 'eh-dynamic-pricing-discounts'); ?></h6>
				<div class="d-flex">
					<h6><?php esc_html_e('For Emails Ids: ', 'eh-dynamic-pricing-discounts'); ?></h6>
					<p><?php echo esc_html(isset($value['email_ids']) && !empty($value['email_ids']) ? $value['email_ids'] : ' - - '); ?></p>
				</div>
				<div class="d-flex justify-content-between">
					<h6 class="mb-0"><?php esc_html_e('Minimum Number of Previous Order: ', 'eh-dynamic-pricing-discounts'); ?> <?php echo esc_html(isset($value['prev_order_count']) && !empty($value['prev_order_count']) ? $value['prev_order_count'] : ' - - '); ?></h6>
					<h6 class="mb-0"><?php esc_html_e('Minimum Total Spending on Previous Order: ', 'eh-dynamic-pricing-discounts'); ?> <?php echo esc_html(isset($value['prev_order_total_amt']) && !empty($value['prev_order_total_amt']) ? $value['prev_order_total_amt'] : ' - - '); ?></h6>
				</div>
			</div>
		</div>
	</div>
</div>
