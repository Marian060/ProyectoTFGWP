<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
?>

<div class="p-1 fw-bold">
	<p><?php esc_html_e('Before raising the ticket, we recommend you to go through our', 'eh-dynamic-pricing-discounts'); ?>
	   <a href="<?php echo esc_url('https://elextensions.com/knowledge-base/set-up-elex-dynamic-pricing-and-discounts-plugin-for-woocommerce/'); ?>" target="_blank"><?php esc_html_e('detailed documentation.', 'eh-dynamic-pricing-discounts'); ?></a></p>
	<p><?php esc_html_e('Or', 'eh-dynamic-pricing-discounts'); ?></p>
	<p class="mb-0"><?php esc_html_e('To get in touch with our helpdesk representative. Just raise a support ticket on our website.', 'eh-dynamic-pricing-discounts'); ?></p>
	<div class="text-danger fw-normal"><small><?php esc_html_e('*Please don\'t forget to attach your System info, Error log & Debug File with the request for better support.', 'eh-dynamic-pricing-discounts'); ?></small></div>

	<a href="<?php echo esc_url('https://support.elextensions.com/'); ?>" class="btn btn-primary  my-3" target="_blank"><?php esc_html_e('Raise a Ticket', 'eh-dynamic-pricing-discounts'); ?></a>
	<div class="d-flex gap-3">
		<form id="import-export" method="post" enctype="multipart/form-data">
		<?php submit_button(esc_html__('Download System Info', 'eh-dynamic-pricing-discounts'), 'btn btn-outline-primary', 'system-info-export', false); ?>
		</form>
		<!-- <button class="btn btn-outline-primary">Download System Info</button> -->
	</div>
</div>
<div class="elex-dynamic-pricing-wrap">
	<div class="mt-3">
		<!-- Export Failure3 -->
		<div class="mt-5 toast p-2 position-fixed top-0 start-50 translate-middle-x bg-white fade hide" id="elex-dynamic-pricing-downloadfailure-toast">
			<div class="toast-header border-0 ">
				<div class="d-flex gap-2">
					<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20">
						<path id="Icon_material-error-outline" data-name="Icon material-error-outline" d="M10.667,14.667h2v2h-2Zm0-8h2v6h-2Zm.99-5a10,10,0,1,0,10.01,10,10,10,0,0,0-10.01-10Zm.01,18a8,8,0,1,1,8-8A8,8,0,0,1,11.667,19.667Z" transform="translate(-1.667 -1.667)" fill="#dc3545"/>
						</svg>
						<strong class="me-auto"><?php esc_html_e('System info is not available.', 'eh-dynamic-pricing-discounts'); ?></strong>
				</div>
				<button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" data-key="downloadfailure">></button>
			</div>
		</div>
	</div>     
</div>
<script>
	jQuery(document).ready(function() {
	
		if (hasURLKey('downloadfailure')) {
			showSuccessNotification('downloadfailure');
		}

		// Add event listener to the close buttons of the success notifications
		var closeButtons = document.querySelectorAll(".toast .btn-close");
		if (closeButtons) {
			closeButtons.forEach(function (closeButton) {
				closeButton.addEventListener("click", function () {
				var key = this.dataset.key;
				removeURLKey(key);
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
		var notificationId = "elex-dynamic-pricing-" + key + "-toast";
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

</script>
