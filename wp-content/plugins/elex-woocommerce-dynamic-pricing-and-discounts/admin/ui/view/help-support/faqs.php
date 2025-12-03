<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$faqs = array(
	array(
		'question' => esc_html__('Can the BOGO rule apply percentage discounts on the second product?', 'eh-dynamic-pricing-discounts'),
		'answer'   => esc_html__('Currently, the plugin only allows you to keep the second product as a free product. However, our team has explored the feasibility of giving a percentage discount on it, and this feature will be available in the upcoming version.', 'eh-dynamic-pricing-discounts'),
	),
	array(
		'question' => esc_html__('How can I apply coupon codes for the discount rules?', 'eh-dynamic-pricing-discounts'),
		'answer'   => esc_html__('While creating a rule, you can apply a coupon discount by selecting "Coupon Discount" as the discount type. Then, you can either create a dynamic coupon or associate an existing WooCommerce coupon with the rule from the "Associate Coupon & Roles" section.', 'eh-dynamic-pricing-discounts'),
	),
	array(
		'question' => esc_html__('Can I offer the cheapest product in the cart as a free product to the customer?', 'eh-dynamic-pricing-discounts'),
		'answer'   => esc_html__('Yes, you can offer the cheapest product in the cart as a free product. To do this, you need to create a BOGO rule and select "The Cheapest product in the Cart" under the free products section.', 'eh-dynamic-pricing-discounts'),
	),
	array(
		'question' => esc_html__('What does the "504 Timeout error" on the shop page mean?', 'eh-dynamic-pricing-discounts'),
		'answer'   => esc_html__('When a website has a large number of products and multiple rules created with the plugin, the scanning of all the products during page loading can lead to performance issues and cause a 504 error to occur.', 'eh-dynamic-pricing-discounts'),
	),
	array(
		'question' => esc_html__('If the BOGO rule is satisfied, does it automatically add the free product to the cart?', 'eh-dynamic-pricing-discounts'),
		'answer'   => esc_html__('Yes, the plugin automatically adds the free product to the cart page if the rule is satisfied.', 'eh-dynamic-pricing-discounts'),
	),
	array(
		'question' => esc_html__('What is the difference between a product rule and a cart rule?', 'eh-dynamic-pricing-discounts'),
		'answer'   => esc_html__('In a product rule, you can select and apply discounts to the specific product(s) in your store. On the other hand, a cart rule allows you to give a discount for the products that are added to the cart.', 'eh-dynamic-pricing-discounts'),
	),
	array(
		'question' => esc_html__('How can I customize the pricing table?', 'eh-dynamic-pricing-discounts'),
		'answer'   => esc_html__('You can customize the pricing table by configuring it in the "Settings" > "Other Options" > "Pricing Table" section.', 'eh-dynamic-pricing-discounts'),
	),
);
?>

<div class="accordion accordion-flush" id="accordionFlushExample">
	<?php foreach ($faqs as $index => $faq) : ?>
		<div class="accordion-item mb-2 border-0 bg-light">
			<h2 class="accordion-header" id="flush-heading<?php echo esc_attr($index + 1); ?>">
				<button class="accordion-button collapsed fw-bold gap-2 align-items-start bg-transparent text-dark "
					type="button" data-bs-toggle="collapse"
					data-bs-target="#flush-collapse<?php echo esc_attr($index + 1); ?>"
					aria-expanded="false" aria-controls="flush-collapse<?php echo esc_attr($index + 1); ?>">
					<div><?php echo esc_html__('Q', 'eh-dynamic-pricing-discounts') . ( $index + 1 ) . '.'; ?></div>
					<div><?php echo esc_html($faq['question']); ?></div>
				</button>
			</h2>
			<div id="flush-collapse<?php echo esc_attr($index + 1); ?>" class="accordion-collapse collapse"
				aria-labelledby="flush-heading<?php echo esc_attr($index + 1); ?>"
				data-bs-parent="#accordionFlushExample">
				<div class="accordion-body" style="padding-left: 50px;">
					<small><?php echo esc_html($faq['answer']); ?></small>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
