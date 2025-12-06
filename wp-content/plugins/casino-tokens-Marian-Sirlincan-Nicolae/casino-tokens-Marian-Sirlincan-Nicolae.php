<?php
/**
 * Plugin Name: Casino Tokens Marian Sirlincan Nicolae
 * Plugin URI:  https://example.com
 * Description: Sistema de fichas (tokens) educativo para un casino simulado. Gestiona saldo de fichas por usuario, compras por packs y registro de historial de transacciones.
 * Version:     1.0.0
 * Author:      Marian Sirlincan Nicolae
 * Text Domain: casino-tokens-mns
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Casino_Tokens_MNS' ) ) :

class Casino_Tokens_MNS {

    /**
     * Singleton instance
     *
     * @var Casino_Tokens_MNS
     */
    private static $instance = null;

    /**
     * Meta key for user tokens
     */
    const META_KEY_TOKENS = 'mns_tokens';

    /**
     * DB table name for transactions (without prefix)
     */
    const TABLE_TRANSACTIONS = 'mns_transactions';

    /**
     * Default welcome tokens for new users
     */
    const WELCOME_TOKENS = 100;

    /**
     * Get singleton instance
     *
     * @return Casino_Tokens_MNS
     */
    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define plugin constants
     */
    private function define_constants() {
        if ( ! defined( 'CASINO_TOKENS_MNS_VERSION' ) ) {
            define( 'CASINO_TOKENS_MNS_VERSION', '1.0.0' );
        }
        if ( ! defined( 'CASINO_TOKENS_MNS_PLUGIN_FILE' ) ) {
            define( 'CASINO_TOKENS_MNS_PLUGIN_FILE', __FILE__ );
        }
        if ( ! defined( 'CASINO_TOKENS_MNS_PLUGIN_DIR' ) ) {
            define( 'CASINO_TOKENS_MNS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        }
        if ( ! defined( 'CASINO_TOKENS_MNS_PLUGIN_URL' ) ) {
            define( 'CASINO_TOKENS_MNS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        }
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once CASINO_TOKENS_MNS_PLUGIN_DIR . 'includes/class-mns-tokens-helper.php';
        require_once CASINO_TOKENS_MNS_PLUGIN_DIR . 'includes/class-mns-tokens-activator.php';
        require_once CASINO_TOKENS_MNS_PLUGIN_DIR . 'admin/class-mns-tokens-admin.php';
        require_once CASINO_TOKENS_MNS_PLUGIN_DIR . 'public/class-mns-tokens-public.php';
    }

    /**
     * Init hooks
     */
    private function init_hooks() {
        register_activation_hook( __FILE__, array( 'MNS_Tokens_Activator', 'activate' ) );

        // Give welcome tokens on user registration
        add_action( 'user_register', array( $this, 'give_welcome_tokens' ), 10, 1 );

        // Handle purchases (POST)
        add_action( 'init', array( 'MNS_Tokens_Helper', 'handle_token_purchase' ) );

        // Init admin/public functionality
        if ( is_admin() ) {
            MNS_Tokens_Admin::instance();
        }
        MNS_Tokens_Public::instance();

        // Shortcodes
        add_shortcode( 'mns_token_balance', array( 'MNS_Tokens_Public', 'shortcode_balance' ) );
        add_shortcode( 'mns_token_packs', array( 'MNS_Tokens_Public', 'shortcode_packs' ) );
        add_shortcode( 'mns_token_history', array( 'MNS_Tokens_Public', 'shortcode_history' ) );
    }

    /**
     * Give welcome tokens to a new user
     *
     * @param int $user_id
     */
    public function give_welcome_tokens( $user_id ) {
        $user_id = intval( $user_id );
        if ( $user_id <= 0 ) {
            return;
        }

        // Only set if not already defined
        $existing = get_user_meta( $user_id, self::META_KEY_TOKENS, true );
        if ( $existing === '' ) {
            update_user_meta( $user_id, self::META_KEY_TOKENS, self::WELCOME_TOKENS );
        }
    }

}

// Initialize plugin
function casino_tokens_mns_init() {
    return Casino_Tokens_MNS::instance();
}
casino_tokens_mns_init();

endif;
