<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.xadapter.com
 * @since      1.0.0
 *
 * @package    xa_dynamic_pricing_plugin
 * @subpackage xa_dynamic_pricing_plugin/includes
 */
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    xa_dynamic_pricing_plugin
 * @subpackage xa_dynamic_pricing_plugin/includes
 * @author     Your Name <email@example.com>
 */
if (!class_exists('Elex_dynamic_pricing_plugin')) {


	class Elex_dynamic_pricing_plugin {

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      xa_dynamic_pricing_plugin_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $xa_dynamic_pricing_plugin    The string used to uniquely identify this plugin.
		 */
		protected $xa_dynamic_pricing_plugin;

		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $version    The current version of the plugin.
		 */
		protected $version;

		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

			$this->xa_dynamic_pricing_plugin = 'xa-dynamic-pricing-plugin';
			$this->version                   = '3.0.1';

			$this->elex_dp_load_dependencies();
			$this->elex_dp_set_locale();
			if ( is_admin() ) {
				$this->elex_dp_define_admin_hooks();
			}
			$this->elex_dp_define_public_hooks();


			if (!function_exists('elex_dp_plugin_override')) {
				add_action('plugins_loaded', 'elex_dp_plugin_override');

				function elex_dp_plugin_override() {
					if (!function_exists('WC')) {

						function WC() {
							return $GLOBALS['woocommerce'];
						}

					}
				}

			}
		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - xa_dynamic_pricing_plugin_Loader. Orchestrates the hooks of the plugin.
		 * - xa_dynamic_pricing_plugin_i18n. Defines internationalization functionality.
		 * - xa_dynamic_pricing_plugin_Admin. Defines all hooks for the admin area.
		 * - xa_dynamic_pricing_plugin_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function elex_dp_load_dependencies() {

			/**
			 * The class responsible for organizing the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path(dirname(__FILE__)) . 'includes/elex-dynamic-pricing-plugin-loader.php';

			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
			require_once plugin_dir_path(dirname(__FILE__)) . 'includes/elex-dynamic-pricing-plugin-i18n.php';
			require_once plugin_dir_path(dirname(__FILE__)) . 'includes/elex-common-functions.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			if (is_admin()) {
				require_once plugin_dir_path(dirname(__FILE__)) . 'admin/elex-dynamic-pricing-plugin-admin.php';
			}
			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path(dirname(__FILE__)) . 'public/elex-dynamic-pricing-plugin-public.php';


			$this->loader = new Elex_dynamic_pricing_plugin_Loader();
		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the xa_dynamic_pricing_plugin_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function elex_dp_set_locale() {

			$plugin_i18n = new Elex_dynamic_pricing_plugin_i18n();

			$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function elex_dp_define_admin_hooks() {
			$http_referer = '';
			if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
			   $http_referer = $_SERVER['HTTP_REFERER'];
			}
			require_once plugin_dir_path(dirname(__FILE__)) . 'admin/elex-admin-actions-function.php';  // class contains list of Function for Actions
			$list_of_actions_function = new Elex_dy_admin_actions_function();

			if ( ( isset($_GET['page']) )  ) {
			
				require_once plugin_dir_path(dirname(__FILE__)) . 'admin/elex-admin-filters-function.php';  // class contains list of Function for Filters

				$plugin_admin             = new Elex_dynamic_pricing_plugin_Admin($this->elex_dp_get_dynamic_pricing_plugin(), $this->get_version());
				$list_of_filters_function = new Elex_admin_filters_function();

				$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
				$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
				$this->loader->add_action('admin_print_styles', $list_of_actions_function, 'elex_dp_func_enqueue_search_product_enhanced_select');
				$this->loader->add_action('admin_enqueue_scripts', $list_of_actions_function, 'func_enqueue_jquery');
				$this->loader->add_action('admin_enqueue_scripts', $list_of_actions_function, 'func_enqueue_jquery_ui_datepicker');

				//Adding ui scripts/styles dependencies
				$this->loader->add_action('admin_enqueue_scripts', $list_of_actions_function, 'func_enqueue_ui_dependencies');

			}

			//Adding main menu
			$this->loader->add_action('admin_menu', $list_of_actions_function, 'elex_dp_add_admin_main_menu');
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function elex_dp_define_public_hooks() {

			//$plugin_public = new xa_dynamic_pricing_plugin_Public($this->get_xa_dynamic_pricing_plugin(), $this->get_version());

			//$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
			//$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		}

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			$this->loader->run();
		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    string    The name of the plugin.
		 */
		public function elex_dp_get_dynamic_pricing_plugin() {
			return $this->xa_dynamic_pricing_plugin;
		}

		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     1.0.0
		 * @return    xa_dynamic_pricing_plugin_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}

	}

}
