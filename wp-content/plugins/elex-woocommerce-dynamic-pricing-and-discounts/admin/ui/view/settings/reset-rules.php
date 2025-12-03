<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
?>

<div class="row">
	<div class="col-md-11">
		<div class="border elex-border-secondary-light p-3">

			<h6><?php esc_html_e('Reset Rules Tabs', 'eh-dynamic-pricing-discounts'); ?></h6>

			<div class="elex-dynamic-pricing-alert p-2 mb-3">
				<h6 class="mb-0 elex-dynamic-input-label"><?php esc_html_e('This option will delete all the rules in all the rules tabs', 'eh-dynamic-pricing-discounts'); ?></h6>
			</div>
			<input class="btn btn-primary" name="submit" id="submit" type="submit" value="<?php esc_attr_e('Reset All The Tabs', 'eh-dynamic-pricing-discounts'); ?>" />
		</div>
	</div>
</div>
