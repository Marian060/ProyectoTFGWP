<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

// Get the active tab
$active_tab = isset($_REQUEST['tab']) ? sanitize_text_field($_REQUEST['tab']) : 'faqs';
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e('Help & Support', 'eh-dynamic-pricing-discounts'); ?></title>
</head>

<body>

	<div class="elex-dynamic-pricing-wrap">

		<!-- content -->
		<div class="elex-dynamic-pricing-content d-flex">

			<!-- main content -->
			<div class="elex-dynamic-pricing-main w-100 ">
				<div class="bg-dark p-2"></div>

				<div class="p-3">
					<!-- banner -->
					 
					<img src="<?php echo esc_url( ELEX_DP_CRM_MAIN_IMG . 'top_banner.svg' ); ?>" alt="<?php esc_attr_e('Dynamic Pricing banner', 'eh-dynamic-pricing-discounts'); ?>" class="w-100  mb-3">

					<h5 class="mb-3"><?php esc_html_e('Help & Support', 'eh-dynamic-pricing-discounts'); ?></h5>

					<!-- help & support navbar -->
					<div class=" mb-3 ">
						<div
							class="d-flex elex-light-blue-bg m-0 mb-3 justify-content-start gap-2 align-items-center elex-dynamic-pricing-main-links elex-dynamic-pricing-main-setting-links">
							<a class="elex-dynamic-pricing-main-link <?php echo $active_tab == 'faqs' ? 'active' : ''; ?>" href="<?php echo esc_url(add_query_arg('tab', 'faqs', admin_url('admin.php?page=dp-help-and-support-page'))); ?>" ><?php esc_html_e('FAQs', 'eh-dynamic-pricing-discounts'); ?></a>
							<a class="elex-dynamic-pricing-main-link <?php echo $active_tab == 'ticket' ? 'active' : ''; ?>" href="<?php echo esc_url(add_query_arg('tab', 'ticket', admin_url('admin.php?page=dp-help-and-support-page'))); ?>"><?php esc_html_e('Raise a Ticket', 'eh-dynamic-pricing-discounts'); ?></a></li>
						</div>
					</div>
					<?php 
						
					if ($active_tab === 'faqs') {
						require_once ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/view/help-support/faqs.php';

					} elseif ($active_tab === 'ticket') {
						require_once ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/view/help-support/ticket.php';

					}

					?>
				</div>

			</div>

		</div>
	</div>
</body>

</html>
