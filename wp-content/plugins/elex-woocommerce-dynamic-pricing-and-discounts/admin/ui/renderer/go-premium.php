<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e('Go Premium!', 'eh-dynamic-pricing-discounts'); ?></title>
</head>

<body>

	<div class="elex-dynamic-pricing-wrap">

		<!-- content -->
		<div class="elex-dynamic-pricing-content d-flex">

			<!-- main content -->
			<div class="elex-dynamic-pricing-main w-100 ">
				<div class="p-3">
					<!-- banner -->
					<img src="<?php echo esc_url(ELEX_DP_CRM_MAIN_IMG . 'top_banner.svg'); ?>" alt="<?php esc_attr_e('Dynamic Pricing banner', 'eh-dynamic-pricing-discounts'); ?>" class="w-100  mb-3">

					<?php
					// Include the market.php file using the correct path
					require_once ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/view/market.php';
					?>
				</div>

			</div>

		</div>
	</div>
</body>

</html>
