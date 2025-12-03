<?php 
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
?>

<h6><?php esc_html_e('In order to configure the discounts for the following rules, go to', 'eh-dynamic-pricing-discounts'); ?> <a href="<?php echo esc_url(add_query_arg('page', 'dp-discount-rules-page', admin_url('admin.php'))); ?>" class="text-info"><?php esc_html_e('Discount Rules', 'eh-dynamic-pricing-discounts'); ?></a> <?php esc_html_e('tab', 'eh-dynamic-pricing-discounts'); ?></h6>

<!-- table -->
<div class="d-flex flex-column justify-content-between" style="min-height: 50vh;">
	<div class="table-responsive">
		<table class="table table-borderless align-middle text-dark  p-1 mb-3 elex-dynamic-pricing-rules-execution-table" style="border-collapse:separate;border-spacing: 0 10px; font-size: 14px;">
			<thead class="bg-secondary bg-opacity-10 rounded-2">
				<tr class="" mb-2>
					<th scope="col" style="width: 20px;"></th>
					<th scope="col" class="text-start"><?php esc_html_e('Rule Name', 'eh-dynamic-pricing-discounts'); ?></th>
					<th scope="col" style="width: 150px;" class="text-start"><?php esc_html_e('Status', 'eh-dynamic-pricing-discounts'); ?></th>
					
				</tr>
			</thead>
			<tbody class="">
				<?php foreach ($rules_modes_order as $key) { ?>
					<tr class="elex-box-table-shadow rounded-3 settings_saved_row">
						<th scope="row" class="icon-move" style="width:10px;cursor: move">
							<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
								<g id="Icon_feather-grid" data-name="Icon feather-grid" transform="translate(0.75 0.75)">
									<path id="Path_508" data-name="Path 508" d="M2.25,2.25H7.5V7.5H2.25Z" transform="translate(-2.25 -2.25)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>
									<path id="Path_509" data-name="Path 509" d="M10.5,2.25h5.25V7.5H10.5Z" transform="translate(-2.25 -2.25)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>
									<path id="Path_510" data-name="Path 510" d="M10.5,10.5h5.25v5.25H10.5Z" transform="translate(-2.25 -2.25)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>
									<path id="Path_511" data-name="Path 511" d="M2.25,10.5H7.5v5.25H2.25Z" transform="translate(-2.25 -2.25)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>
								</g>
								</svg>
						</th>
						<td class="text-start fw-bold">
							<?php
							if ($key == 'product_rules' || $key == 'category_rules') {
									echo esc_html($rules_modes[$key]);
							} else {
								echo esc_html($rules_modes[$key]);
								?>
									<sup class="elex_dp_go_premium_color"><?php esc_html_e('[Premium]', 'eh-dynamic-pricing-discounts'); ?></sup>
																										<?php

							}
							?>
						</td>
						<td>
							<label class="elex-switch-btn">
								<?php if ($key == 'product_rules' || $key == 'category_rules') { ?>
									<input type="checkbox" name='enabled_modes[<?php echo esc_attr($key); ?>]' value='<?php echo esc_attr($key); ?>' <?php echo in_array($key, $execution_order) ? 'checked' : ''; ?>>
									<div class="elex-switch-icon round"></div>
									<?php } else { ?>
										<div class="elex-switch-icon round"></div>
										<?php

									}
									?>
							</label>
						</td>
						<input type="hidden" name="rules_modes_order[]" value="<?php echo esc_attr($key); ?>">
					</tr>
				<?php } ?>
				
			</tbody>
		</table>
		<input class="btn btn-primary" name="submit" id="submit" type="submit" value="<?php esc_attr_e('Save Changes', 'eh-dynamic-pricing-discounts'); ?>" />        
	</div>  
</div>
