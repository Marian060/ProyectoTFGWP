<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e('Import-Export', 'eh-dynamic-pricing-discounts'); ?></title>
</head>
<body>

	<div class="elex-dynamic-pricing-wrap">

		<!-- content -->
		<div class="elex-dynamic-pricing-content d-flex">
			
			<!-- main content -->
			<div class="elex-dynamic-pricing-main w-100 p-2 pe-4">

				<!-- banner -->
				<img src="<?php echo esc_url( ELEX_DP_CRM_MAIN_IMG . 'top_banner.svg' ); ?>" alt="<?php esc_attr_e('Dynamic Pricing banner', 'eh-dynamic-pricing-discounts'); ?>" class="w-100  mb-2">

				<!-- export -->
				<div class="border elex-border-secondary-light p-3 mb-3">
						<h6 class="mb-3 elex-dynamic-input-label"><?php esc_html_e('Export Rules', 'eh-dynamic-pricing-discounts'); ?></h6>
						<div class="row mb-3">
							<div class="col-lg-3 col-md-4">
								<select disabled name="export_tab" id="export_tab" class="form-select min-width-100">
									<option value="product_rules"><?php esc_html_e('Product Rules', 'eh-dynamic-pricing-discounts'); ?></option>
								</select>
							</div>
							<div class="col-lg-3 col-md-4">
								<select disabled name="export_type" id="export_type" class="form-select min-width-100">
									<option value="eha-export-none"><?php esc_html_e('Select a File Format', 'eh-dynamic-pricing-discounts'); ?></option>
								</select>
							</div>
						</div>
						<?php submit_button(__('Export', 'eh-dynamic-pricing-discounts'), 'btn btn-primary elex-dp-min-width-btn disabled', 'eha-export', false); ?>
				</div>

				<!-- import -->
				<div class="border elex-border-secondary-light p-3 mb-3">
						<h6 class="mb-3 elex-dynamic-input-label"><?php esc_html_e('Import Rules', 'eh-dynamic-pricing-discounts'); ?></h6>
						<div class="row mb-3">
							<div class="col-lg-3 col-md-4">
								<select disabled name="import_tab" id="import_tab" class="form-select min-width-100">
								<option value="product_rules"><?php esc_html_e('Product Rules', 'eh-dynamic-pricing-discounts'); ?></option>
								</select>
							</div>
							<div class="col-lg-3 col-md-4">
								<select disabled name="import_type" class="form-select min-width-100">
									<option value="eha-import-none"><?php esc_html_e('Select a File Format', 'eh-dynamic-pricing-discounts'); ?></option>
								</select>
							</div>
							<div class="col-lg-3 col-md-4">
								<select disabled name="import_mode" class="form-select min-width-100">
									<option value="overwrite"><?php esc_html_e('Overwrite if Rules Number Exists', 'eh-dynamic-pricing-discounts'); ?></option>
								</select>
							</div>
						</div>
						<div class="mb-3 ">
							<div class="btn btn-outline-primary position-relative px-3 elex-dp-min-width-btn disabled" id="elex-dp-import-file"><?php esc_html_e('Upload File', 'eh-dynamic-pricing-discounts'); ?>
								<input disabled type="file" name="import_file"  accept=".json,.csv"
									class="position-absolute top-0 file-upload-input start-0 w-100 h-100 opacity-0">
							</div>
						</div>
						<div>
						<?php submit_button(__('Import', 'eh-dynamic-pricing-discounts'), 'btn btn-primary px-3 elex-dp-min-width-btn disabled', 'eha-import', false); ?>
						</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
