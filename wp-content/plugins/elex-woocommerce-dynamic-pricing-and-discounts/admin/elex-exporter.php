<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

add_action('admin_init', 'elex_dp_export_rules');

function elex_dp_export_rules() {
	if (!current_user_can('manage_options')) {
		return;
	}
	
	if ( isset($_POST['eha-export']) && ( !isset($_POST['eha_export_nonce']) || !wp_verify_nonce($_POST['eha_export_nonce'], 'eha_export_nonce') ) ) {
		// Nonce is invalid or not present
		wp_safe_redirect(admin_url('admin.php?page=dp-import-export-page&nonce_verification_failed'));
		return false;
	
	}
	if (isset($_POST['eha-export'])) {
		if (isset($_POST['export_type']) && ( $_POST['export_type'] == 'eha-export-json' )) {

			$rules = get_option('xa_dp_rules', array());
			$tab_c = $_POST['export_tab'];

			$file_rule = $tab_c;
			if ($tab_c == 'combinational_rules') {
				$file_rule = 'multi_product_rules';
			} else if ($tab_c == 'cat_combinational_rules') {
				$file_rule = 'multi_category_rules';
			} else if ($tab_c == 'buy_get_free_rules') {
				$file_rule = 'bogo_product_rules';
			}

			if (!isset($rules[$tab_c])) {
				wp_safe_redirect(admin_url('admin.php?page=dp-import-export-page&exportfailure1'));
				return false;
			}
			if (empty($rules[$tab_c])) {
				wp_safe_redirect(admin_url('admin.php?page=dp-import-export-page&exportfailure2'));
				return false;
			}
			nocache_headers();
			header('Content-Type: application/json; charset=utf-8');
			header('Content-Disposition: attachment; filename=' . $file_rule . '-export-' . date('d-M-Y') . '.json');
			header('Expires: 0');
			$data =array('type'=> $tab_c , 'rules'=>$rules[$tab_c]);
			echo json_encode($data);
			exit;

		} elseif (isset($_POST['export_type']) && ( $_POST['export_type'] == 'eha-export-csv' )) {

			$rules = get_option('xa_dp_rules', array());
			$tab_c = $_POST['export_tab'];

			$file_rule = $tab_c;
			if ($tab_c == 'combinational_rules') {
				$file_rule = 'multi_product_rules';
			} else if ($tab_c == 'cat_combinational_rules') {
				$file_rule = 'multi_category_rules';
			} else if ($tab_c == 'buy_get_free_rules') {
				$file_rule = 'bogo_product_rules';
			}

			if (!isset($rules[$tab_c])) {
				wp_safe_redirect(admin_url('admin.php?page=dp-import-export-page&exportfailure1'));
				return false;
			}
			if (empty($rules[$tab_c])) {
				wp_safe_redirect(admin_url('admin.php?page=dp-import-export-page&exportfailure2'));
				return false;
			}
			nocache_headers();
			header('Content-Type: application/json; charset=utf-8');
			header('Content-Disposition: attachment; filename=' . $file_rule . '-export-' . date('d-M-Y') . '.csv');
			header('Expires: 0');
			$output_file_name =$tab_c . '-export-' . date('m-d-Y') . '.csv';
			foreach (current($rules[$tab_c]) as $colname=>$colval) {
				$line[] =$colname;
			}
			$lines =implode(',', $line);
			echo $lines . "\n";
			foreach ($rules[$tab_c] as $row) {
				$line =array();
				foreach ($row as $k=>$v) {
					if (is_array($v) || is_object($v)) {
						$v   =(array) $v;
						$tmp ='';
						foreach ($v as $k2=>$v2) {
							$tmp .=( empty($tmp) ? '   ' : ' | ' ) . "$k2=>$v2";
						}
						$line[] ="[$tmp]";
					} else {
						if ($v) {
							$v      = str_replace(',', '&comma', $v);
						}
						$line[] =$v;
					}
				}
				$lines =implode(',', $line);
				echo $lines . "\n";
			}

			exit;

		} else {
			wp_safe_redirect(admin_url('admin.php?page=dp-import-export-page&exportfailure3'));
			return false;
		}
	}
	if (isset($_POST['system-info-export'])) {
		$Info = phpinfo(1);
		if ($Info) {
			nocache_headers();
			header('Content-Type: application/html; charset=utf-8');
			header('Content-Disposition: attachment; filename= System-info-export-' . date('d-M-Y') . '.html');
			header('Expires: 0');
			echo ( $Info );
			exit;
		} else {
			wp_safe_redirect(admin_url('admin.php?page=dp-help-and-support-page&tab=ticket&downloadfailure'));
			return false;
		}
	}

}
