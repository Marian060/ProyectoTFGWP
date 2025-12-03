<?php
namespace Elex\DynamicPricing;

use Elex\DynamicPricing\WF_API_Manager_Software_Update;
use Elex\DynamicPricing\WF_Software_Activate;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require 'class-wc-am-plugin-activate.php';
require 'class-wc-am-plugin-update.php';


class WF_API_Manager {
	private $is_ajax;
	private $upgrade_url;
	private $plugin_name;
	private $product_id;
	private $api_key;
	private $activation_unique_product_id;
	private $renew_license_url;
	private $instance;
	private $domain;
	private $software_version;
	private $wf_unique_product_id_key;
	private $wf_api_licence_key;
	private $wf_instance_key;
	private $plugin_settings_url;
	private $plugin_or_theme;
	private $text_domain;
	private $extra;
	
	public function __construct( $product_id, $plugin_version, $plugin_slug, $server_url, $plugin_settings_url = '' ) {
		$this->wf_unique_product_id_key = $product_id . '_unique_product_id';
		$this->wf_api_licence_key       = $product_id . '_licence_key';
		$this->wf_instance_key          = $product_id . '_instance_id';
		$this->plugin_settings_url      = $plugin_settings_url;
		$this->product_id               = $product_id; // Software Title
		$this->software_version         = $plugin_version;
		$this->plugin_name              = $plugin_slug; //this might me plugin folder directory/plugin name

		$this->upgrade_url = $server_url; // URL to access the Update API Manager.





		$this->domain = home_url(); // blog domain name
		
		$wf_api_licence_key = get_option( $this->wf_api_licence_key );
		$this->api_key      = ! empty( $wf_api_licence_key ) ? $wf_api_licence_key : ''; // API License Key
		
		$wf_unique_product_id_key           = get_option( $this->wf_unique_product_id_key );
		$this->activation_unique_product_id = ! empty( $wf_unique_product_id_key ) ? $wf_unique_product_id_key : ''; // License Email
		
		$wf_instance_key = get_option( $this->wf_instance_key );
		$this->instance  = ! empty( $wf_instance_key ) ? $wf_instance_key : '';  // Instance ID (unique to each blog activation)
		
		$this->renew_license_url = ''; // URL to renew a license
		$this->plugin_or_theme   = 'plugin'; // 'theme' or 'plugin'
		$this->text_domain       = ''; // localization for translation
		$this->extra             = ''; // Used to send any extra information.

		$this->wf_init();
	}
	
	
	private function wf_init() {
		$wf_unique_product_id_key     = $this->wf_unique_product_id_key;
		$wf_api_licence_key           = $this->wf_api_licence_key;
		$wf_instance_key              = $this->wf_instance_key;
		$plugin_settings_url          = $this->plugin_settings_url;
		$upgrade_url                  = $this->upgrade_url;
		$plugin_name                  = $this->plugin_name;
		$product_id                   = $this->product_id;
		$api_key                      = $this->api_key;
		$activation_unique_product_id = $this->activation_unique_product_id;
		$renew_license_url            = $this->renew_license_url;
		$instance                     = $this->instance;
		$domain                       = $this->domain;
		$software_version             = $this->software_version;
		$plugin_or_theme              = $this->plugin_or_theme;
		$text_domain                  = $this->text_domain;
		$extra                        = $this->extra;

		include_once  'class-wc-am-plugin-activate.php' ;
		include_once  'class-wc-am-plugin-update.php' ;
		
		$activation_obj = new WF_Software_Activate( $upgrade_url, $plugin_name, $product_id, $api_key, $activation_unique_product_id, $renew_license_url, $instance, $domain, $software_version, $plugin_or_theme, $text_domain, $extra, $wf_unique_product_id_key, $wf_api_licence_key, $wf_instance_key );

		add_action( 'wp_ajax_wf_activate_license_keys_' . $this->product_id, array( $activation_obj, 'wf_activation' ) );
		add_action( 'wp_ajax_wf_deactivate_license_keys_' . $this->product_id, array( $activation_obj, 'wf_deactivation' ) );

		new WF_API_Manager_Software_Update( $upgrade_url, $plugin_name, $product_id, $api_key, $activation_unique_product_id, $renew_license_url, $instance, $domain, $software_version, $plugin_or_theme, $text_domain, $extra, $plugin_settings_url );
		
	}


} // End of class
