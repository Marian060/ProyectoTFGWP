<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
$rule_tab = false;

$active_tab = isset($_REQUEST['tab']) && !empty($_REQUEST['tab']) ? sanitize_text_field($_REQUEST['tab']) : 'No Rules Selected';

$allrules = get_option('xa_dp_rules', array());

$weight_unit = get_option('woocommerce_weight_unit');

$settings        = get_option('xa_dynamic_pricing_setting');
$execution_order = isset($settings['execution_order']) ? $settings['execution_order'] : array(
	'product_rules',
	'category_rules'
);
if (in_array($active_tab, $execution_order)) {
	$rule_tab = true;
}
if ($active_tab === 'No Rules Selected' && !empty($settings['execution_order'])) {
	$active_tab = current($settings['execution_order']);
	$rule_tab   = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e('Discount Rules', 'eh-dynamic-pricing-discounts'); ?></title>
</head>

<body>

	<div class="elex-dynamic-pricing-wrap">

		<!-- content -->
		<div class="elex-dynamic-pricing-content d-flex">

			<!-- main content -->
			<div class="elex-dynamic-pricing-main w-100 p-2 pe-4">

				<!-- banner -->
				<img src="<?php echo esc_url(ELEX_DP_CRM_MAIN_IMG . 'top_banner.svg'); ?>" alt="<?php esc_attr_e('Dynamic Pricing banner', 'eh-dynamic-pricing-discounts'); ?>" class="w-100  mb-2">


				<!-- links -->
				<div class="overflow-auto mb-3">
					<div class="d-flex elex-light-blue-bg m-0  align-items-center elex-dynamic-pricing-main-links">
						<?php
						foreach ($execution_order as $key => $tabkey) {
							$tablink = esc_url(add_query_arg(array('page' => 'dp-discount-rules-page', 'tab' => $tabkey), admin_url('admin.php')));
							switch ($tabkey) {
								case 'product_rules':
									?>
									<a href="<?php echo $tablink; ?>" class=" elex-dynamic-pricing-main-link <?php echo $active_tab === 'product_rules' ? 'active' : ''; ?>">
										<?php esc_html_e('Product Rule', 'eh-dynamic-pricing-discounts'); ?>
									</a>
									<svg xmlns="http://www.w3.org/2000/svg" width="2" height="100%" viewBox="0 0 2 25.587">
										<path id="carrier_seperator" data-name="carrier seperator" d="M2565.89-576v23.587" transform="translate(-2564.89 577)" fill="none" stroke="#707070" stroke-linecap="round" stroke-width="2" />
									</svg>
								<?php
									break;
								case 'category_rules':  
									?>
									<a href="<?php echo $tablink; ?>" class=" elex-dynamic-pricing-main-link <?php echo $active_tab === 'category_rules' ? 'active' : ''; ?>">
										<?php esc_html_e('Category Rules', 'eh-dynamic-pricing-discounts'); ?>
									</a>
									<svg xmlns="http://www.w3.org/2000/svg" width="2" height="100%" viewBox="0 0 2 25.587">
										<path id="carrier_seperator" data-name="carrier seperator" d="M2565.89-576v23.587" transform="translate(-2564.89 577)" fill="none" stroke="#707070" stroke-linecap="round" stroke-width="2" />
									</svg>
								<?php
									break;
								default:
									break;
							}
						}
						?>
					</div>

				</div>

				<div>
					<?php if (!in_array($active_tab, $execution_order)) { ?>
						<!-- when no rule tab is there -->
						<div class="w-100 text-center">
							<img src="<?php echo esc_url( ELEX_DP_CRM_MAIN_IMG . 'IsolationMode_2.svg' ); ?>" alt="" class="w-100" style="max-width: 684px; margin: auto;">
							<div class="text-center">
							</br></br><a href="<?php echo esc_url(add_query_arg(array('page' => 'dp-settings-page'), admin_url('admin.php'))); ?>" class="btn btn-primary" ><?php esc_html_e('Go to Settings Page and Enable Rules', 'eh-dynamic-pricing-discounts'); ?></a></br></br>
							</div>
						</div>
					<?php } else if (!isset($allrules[$active_tab]) || empty($allrules[$active_tab])) { ?>
						<!-- when rule is empty -->
						<div class="w-100 text-center">
							<img src="<?php echo esc_url( ELEX_DP_CRM_MAIN_IMG . 'IsolationMode.svg' ); ?>" alt="" class="w-100" style="max-width: 684px; margin: auto;">
							<div class="text-center">
								<button class="btn btn-primary elex-dynamic-pricing-popup-open-btn">
								<?php
								esc_html_e('Create My First
									Rule', 'eh-dynamic-pricing-discounts');
								?>
																									</button>
							</div>
						</div>

					<?php
					} else {
						$allrules       = $allrules[$active_tab];
						$settings       = get_option('xa_dynamic_pricing_setting', array());

						?>

						<div class="d-flex justify-content-between align-items-center mb-3">
							<h6 class="mb-0 elex-dynamic-pricing-rules-header"><?php esc_html_e('Rules Sets', 'eh-dynamic-pricing-discounts'); ?></h6>
							<button class="btn btn-primary elex-dynamic-pricing-popup-open-btn"><?php esc_html_e('Add New Rule', 'eh-dynamic-pricing-discounts'); ?></button>
						</div>

						<!-- when rules are added -->
						<form method="get" id="eh_rule_form">

							<?php wp_nonce_field('eh_rule_form_nonce', 'eh_rule_form_nonce'); ?>
							<input type="hidden" name="page" value="<?php echo esc_attr('dp-discount-rules-page'); ?>">
							<input type="hidden" id="tab" name="tab" value="<?php echo esc_attr($active_tab); ?>">
							<input type="hidden" id="weight_unit" name="weight_unit" value="<?php echo esc_attr($weight_unit); ?>">
							<input type="hidden" name="deleteValue" id="deleteValue" value="<?php echo esc_attr(''); ?>">

							<br>
							<div id="sortableIds">
								<!-- Cards -->
								<?php

								//Updating rules data according to pagination
								$allrules = array_reverse($allrules, true);

								if (function_exists('wc_memberships_get_membership_plans')) {
									$member_plans = wc_memberships_get_membership_plans();
								}
								$no_of_rules = count($allrules);
								?>
								<input type="hidden" id="no_of_rules" name="no_of_rules" value="<?php echo esc_attr($no_of_rules); ?>">
								<?php
								//Rendering Cards
								foreach ($allrules as $key => $value) {
									//Checkon
									$checkon= '';
									if (isset($value['check_on']) && !empty($value['check_on'])) {
										switch ($value['check_on']) {
											case 'Quantity':
												$checkon = 'No. of Items';
												break;
											case 'TotalQuantity':
												$checkon = 'No. of Units';
												break;
											case 'Weight':
												$checkon = 'Weight';
												break;
											case 'Units':
												$checkon = 'No. of Units';
												break;
											case 'Price':
												$checkon = 'Price';
												break;
										}
									}
									if ($active_tab === 'product_rules') {
										require ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/view/discount-rules/card/product-rules.php';
									} else if ($active_tab === 'category_rules') {
										require ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/view/discount-rules/card/category-rules.php';
									}
								}

								?>
							</div>
						</form>

					<?php } ?>

				</div>

			</div>

			<!-- popups -->
			<form method="get" id="popup_form">
				<?php
				if (isset($_REQUEST['edit'])) {
					wp_nonce_field('update_rule_' . $_REQUEST['edit'], 'update_rule_' . $_REQUEST['edit']);
				} else {
					wp_nonce_field('save_rule_nonce', 'save_rule_nonce');
				}
				?>
				<input type="hidden" name="page" value="<?php echo esc_attr('dp-discount-rules-page'); ?>">
				<input type="hidden" id="tab" name="tab" value="<?php echo esc_attr($active_tab); ?>">
				<input type="hidden" id="weight_unit" name="weight_unit" value="<?php echo esc_attr($weight_unit); ?>">

				<?php

				do_action('my_admin_notification');

				if ($active_tab === 'product_rules') {
					require ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/view/discount-rules/popup/product-rules.php';
				} elseif ($active_tab === 'category_rules') {
					require ELEX_DP_BASIC_ROOT_PATH . 'admin/ui/view/discount-rules/popup/category-rules.php';
				} 

				?>
			</form>


			<!-- toast message -->

			<!-- for succesfully adding rule -->
			<div class="mt-5 toast p-2 position-fixed top-0 start-50 translate-middle-x bg-white fade hide" id="elex-dynamic-add-rule-toast">
				<div class="toast-header border-0 ">
					<strong class="me-auto">
						<svg xmlns="http://www.w3.org/2000/svg" width="22.415" height="22.026" viewBox="0 0 22.415 22.026" class="me-2">
							<g id="Icon_feather-check-circle" data-name="Icon feather-check-circle" transform="translate(-1.998 -1.979)">
								<path id="Path_646" data-name="Path 646" d="M23,12.076V13a10,10,0,1,1-5.93-9.139" fill="none" stroke="#28a745" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
								<path id="Path_647" data-name="Path 647" d="M26.5,6l-10,10.009-3-3" transform="translate(-3.5 -1.003)" fill="none" stroke="#28a745" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
							</g>
						</svg>
						<?php echo esc_html('Rule Added Successfully'); ?></strong>
					<button type="button" class="btn-close" data-bs-dismiss="toast" data-key="<?php echo esc_attr('save'); ?>"></button>
				</div>

			</div>

			<!-- for succesfully edited rule -->
			<div class="mt-5 toast p-2 position-fixed top-0 start-50 translate-middle-x bg-white fade" id="elex-dynamic-edit-rule-toast">
				<div class="toast-header border-0 ">
					<strong class="me-auto">
						<svg xmlns="http://www.w3.org/2000/svg" width="22.415" height="22.026" viewBox="0 0 22.415 22.026" class="me-2">
							<g id="Icon_feather-check-circle" data-name="Icon feather-check-circle" transform="translate(-1.998 -1.979)">
								<path id="Path_646" data-name="Path 646" d="M23,12.076V13a10,10,0,1,1-5.93-9.139" fill="none" stroke="#28a745" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
								<path id="Path_647" data-name="Path 647" d="M26.5,6l-10,10.009-3-3" transform="translate(-3.5 -1.003)" fill="none" stroke="#28a745" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
							</g>
						</svg>
						<?php echo esc_html('Rule Updated Successfully'); ?></strong>
					<button type="button" class="btn-close" data-bs-dismiss="toast" data-key="<?php echo esc_attr('update'); ?>"></button>
				</div>
			</div>


			<!-- rule Deleted succesfully  rule -->
			<div class="mt-5 toast p-2 position-fixed top-0 start-50 translate-middle-x bg-white fade hide" id="elex-dynamic-delete-rule-toast">
				<div class="toast-header border-0 ">
					<strong class="me-auto">
						<svg xmlns="http://www.w3.org/2000/svg" width="22.415" height="22.026" viewBox="0 0 22.415 22.026" class="me-2">
							<g id="Icon_feather-check-circle" data-name="Icon feather-check-circle" transform="translate(-1.998 -1.979)">
								<path id="Path_646" data-name="Path 646" d="M23,12.076V13a10,10,0,1,1-5.93-9.139" fill="none" stroke="#28a745" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
								<path id="Path_647" data-name="Path 647" d="M26.5,6l-10,10.009-3-3" transform="translate(-3.5 -1.003)" fill="none" stroke="#28a745" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
							</g>
						</svg>
						<?php echo esc_html('Rule Deleted Successfully'); ?></strong>
					<button type="button" class="btn-close" data-bs-dismiss="toast" data-key="<?php echo esc_attr('delete'); ?>"></button>
				</div>
			</div>

			<!-- for deleteing  rule confirmation -->
			<div class="mt-5 toast p-2 position-fixed top-0 start-50 translate-middle-x bg-white fade hide " id="elex-dynamic-delete-rule-confirm-toast">
				<div class="toast-header justify-content-end border-0 py-0">
					<button type="button" class="btn-close" data-bs-dismiss="toast"></button>
				</div>
				<div class="toast-body border-0 text-center ">
					<strong class="me-auto"><?php echo esc_html('Are you sure, you want to delete this rule?'); ?></strong>
					<div class="mt-4 pt-2 border-top d-flex gap-3">
						<button type="button" class="btn btn-primary btn-sm w-50"><?php echo esc_html('Confirm'); ?></button>
						<button type="button" class="btn btn-secondary btn-sm w-50" data-bs-dismiss="toast"><?php echo esc_html('Cancel'); ?></button>
					</div>
				</div>
			</div>

		</div>

	</div>

</body>

</html>

<style>
	.editbtn {
		background: url(<?php echo plugins_url('/elex-woocommerce-dynamic-pricing-and-discounts-premium/img/edit.png'); ?>) 10px 10px no-repeat;
		width: 15px;
		height: 15px;
		background-size: 100%;
		background-position: top left;
		border: none;
		margin-left: 10px;
		cursor: pointer;
	}

	.nextbtn {
		background: url(<?php echo plugins_url('/elex-woocommerce-dynamic-pricing-and-discounts-premium/img/next.png'); ?>) 10px 10px no-repeat;
		width: 20px;
		height: 18px;
		background-size: 100%;
		background-position: top left;
		border: none;
		cursor: pointer;
	}

	.nextbtndisable {
		background: url(<?php echo plugins_url('/elex-woocommerce-dynamic-pricing-and-discounts-premium/img/nextdisable.png'); ?>) 10px 10px no-repeat;
		width: 20px;
		height: 18px;
		background-size: 100%;
		background-position: top left;
		border: none;
		cursor: pointer;
	}

	.prevbtn {
		background: url(<?php echo plugins_url('/elex-woocommerce-dynamic-pricing-and-discounts-premium/img/previous.png'); ?>) 10px 10px no-repeat;
		width: 20px;
		height: 18px;
		background-size: 100%;
		background-position: top left;
		border: none;
		cursor: pointer;
	}

	.prevbtndisable {
		background: url(<?php echo plugins_url('/elex-woocommerce-dynamic-pricing-and-discounts-premium/img/previousdisable.png'); ?>) 10px 10px no-repeat;
		width: 20px;
		height: 18px;
		background-size: 100%;
		background-position: top left;
		border: none;
		cursor: pointer;
	}
</style>

<script>

	jQuery(document).ready(function() {
		//For the popup window to open when edit buttion is clicked on rule card ==>
		let searchParams = new URLSearchParams(window.location.search);
		if (searchParams.has('edit')) {
			let param = searchParams.get('edit');
			jQuery(".elex-dynamic-pricing-popup").addClass('active');

		}

		//Fot the rules card dragging and up and down ==>
		jQuery('#sortableIds').sortable({
			// placeholder: "ui-widget-shadow",
			handle: '.icon-move',
			update: function(event, ui) {
				elex_dp_update_rules_arrangement();
			}
		});
		// Get all the move-up buttons
		const moveUpButtons = document.querySelectorAll('.elex-ac-edit-rule-up-btn');
		// Get all the move-down buttons
		const moveDownButtons = document.querySelectorAll('.elex-ac-edit-rule-down-btn');
		// Add event listeners to move-up buttons
		moveUpButtons.forEach(button => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				const listItem = button.closest('.listitemClass');
				const previousItem = listItem.previousElementSibling;

				if (previousItem) {
					listItem.parentNode.insertBefore(listItem, previousItem);
					elex_dp_update_rules_arrangement()
				}
			});
		});
		// Add event listeners to move-down buttons
		moveDownButtons.forEach(button => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				const listItem = button.closest('.listitemClass');
				const nextItem = listItem.nextElementSibling;

				if (nextItem) {
					listItem.parentNode.insertBefore(nextItem, listItem);
					elex_dp_update_rules_arrangement()
				}
			});
		});

		//For disabling the cards move up and down button ==>
		var no_of_rules = jQuery('#no_of_rules').val();
		if(no_of_rules == 1){
			// var className = document.getElementById('className').value;
			document.getElementsByClassName('elex-ac-edit-rule-up-btn')[0].disabled=true;
			document.getElementsByClassName('elex-ac-edit-rule-down-btn')[0].disabled=true;
		}
		var isFormSubmitting = false;
		//For the popup form rule validation alert ==>
		document.getElementById("popup_form").addEventListener("submit", function(event) {
			var form = document.getElementById("popup_form");
			// Check if the submit button was clicked
			if (event.submitter.getAttribute("type") === "submit") {
				if (isFormSubmitting) {
					event.preventDefault();
					return;
				}

				// Check if any required fields are empty
				var requiredFields = document.querySelectorAll("[required]");
				for (var i = 0; i < requiredFields.length; i++) {
					if (!requiredFields[i].value) {
					event.preventDefault(); // Prevent form submission
					alert("Please fill in all required fields.");
					// form.reportValidity();
					return;
					}
				}

				// Check the values of the min and max input fields
				const minInput = document.getElementById("min");
				const maxInput = document.getElementById("max");
				if (minInput && maxInput) {
					const minValue = parseFloat(minInput.value);
					const maxValue = parseFloat(maxInput.value);
					if (minValue > maxValue) {
						event.preventDefault();
						alert("Min value cannot be greater than Max value.");
						minInput.focus();
						return;
					}
				}

				isFormSubmitting = true;

				setTimeout(function () {
					isFormSubmitting = false;
				}, 2000);
			}
		});

		//For clearning the rule form filled data when clicked cancel ==>
		document.getElementById("cancel_btn").addEventListener("click", function() {
			// Clear the form fields or redirect to another page
			document.getElementById("myForm").reset();
		});
	

		//Js for rule deleting confirmation Alert ==>
		var cardform = document.getElementById("eh_rule_form");
		jQuery(cardform).on("click", "button.elex-ac-edit-rule-delete-btn", function () {
			var btn = jQuery(this);
			var btnValue = btn.val();
			var btnName = btn.attr("name");
			event.preventDefault();
			// Function to show the custom confirm alert
			function showCustomConfirmAlert(message, callback) {
				var customConfirmToast = document.getElementById("elex-dynamic-delete-rule-confirm-toast");
				customConfirmToast.classList.remove("hide");
				customConfirmToast.classList.add("show");

				var confirmButton = customConfirmToast.querySelector(".btn-primary");
				var cancelButton = customConfirmToast.querySelector(".btn-secondary");

				var confirmCallback = function () {
				customConfirmToast.classList.remove("show");
				customConfirmToast.classList.add("hide");
				callback(true);
				};

				var cancelCallback = function () {
				customConfirmToast.classList.remove("show");
				customConfirmToast.classList.add("hide");
				callback(false);
				};

				confirmButton.onclick = confirmCallback;
				cancelButton.onclick = cancelCallback;
			}

			// Using the custom confirm alert
			showCustomConfirmAlert("Are you sure you want to delete this rule?", function (result) {
				if (result) {
					var deleteInputName = document.createElement("input");
					deleteInputName.type = "hidden";
					deleteInputName.name = btnName;
					deleteInputName.value = btnValue;

					cardform.appendChild(deleteInputName);

					// Submit the form
					cardform.submit();
				} else {
				return false;
				}
			});
		});

		// Check if the URL contains the "updatesuccess" key and show the update success notification accordingly
		if (hasURLKey('updatesuccess')) {
			showSuccessNotification('edit');
		}

		// Check if the URL contains the "savesuccess" key and show the save success notification accordingly
		if (hasURLKey('savesuccess')) {
			showSuccessNotification('add');
		}

		// Check if the URL contains the "deletesuccess" key and show the delete success notification accordingly
		if (hasURLKey('deletesuccess')) {
			showSuccessNotification('delete');
		}

		// Add event listener to the close buttons of the success notifications
		var closeButtons = document.querySelectorAll(".toast .btn-close");
		if (closeButtons) {
			closeButtons.forEach(function (closeButton) {
				closeButton.addEventListener("click", function () {
				var key = this.dataset.key;
				removeURLKey(key + 'success');
				});
			});
		}

	});

	// Function to check if the URL contains a specific key
	function hasURLKey(key) {
		var urlParams = new URLSearchParams(window.location.search);
		return urlParams.has(key);
	}

	// Function to show a success notification based on the URL key
	function showSuccessNotification(key) {
		var notificationId = "elex-dynamic-" + key + "-rule-toast";
		var successToast = document.getElementById(notificationId);
		successToast.classList.remove("hide");
		successToast.classList.add("show");
	}

	// Function to remove a specific key from the URL
	function removeURLKey(key) {
		var url = window.location.href;
		var urlParams = new URLSearchParams(window.location.search);
		urlParams.delete(key);
		var newURL = url.split('?')[0] + '?' + urlParams.toString();
		window.history.replaceState({}, document.title, newURL);
	}

	function elex_dp_update_rules_arrangement() {
		var rules_order = [];

		jQuery('.listitemClass').each(function(index) {
			rules_order.push(jQuery(this).attr("id"));
		});
		jQuery.post(
			ajaxurl, {
				'action': 'update_rules_arrangement',
				'rules-order': rules_order,
				'rules-type': '<?php echo esc_js($active_tab); ?>',
				'xa-nonce': '<?php echo esc_js(wp_create_nonce('update_rules_arrangement')); ?>'
			},
			function(response) {
				window.location.reload();
			}
		);
	
		return false;
	}
	// Function to get URL parameters
	function getUrlParameter(name) {
		name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
		var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
		var results = regex.exec(location.search);
		return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
	}
</script>
<style>
	td.icon-move {
		background-image: url('<?php echo plugins_url('elex-woocommerce-dynamic-pricing-and-discounts-premium/jquery-ui/drag2.png'); ?>');
		background-size: auto auto;
		background-position: center;
		background-repeat: no-repeat;
	}
</style>
