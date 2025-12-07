<?php
/**
 * Plugin Name: Casino Tokens Marian Sirlincan Nicolae
 * Plugin URI:  https://example.com
 * Description: Sistema de fichas (tokens) educativo para el proyecto Casino M.N.S.
 * Version:     1.2.1
 * Author:      Marian Sirlincan Nicolae
 * Text Domain: casino-tokens-mns
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Cargar clases necesarias
require_once plugin_dir_path( __FILE__ ) . 'includes/class-mns-tokens-helper.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-mns-tokens-activator.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-mns-tokens-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'public/class-mns-tokens-public.php';

if ( ! class_exists( 'Casino_Tokens_MNS' ) ) :

class Casino_Tokens_MNS {

    /**
     * Meta key para el saldo de fichas.
     */
    const META_KEY_TOKENS = 'mns_tokens';

    /**
     * Nombre base de la tabla de transacciones (sin prefijo).
     */
    const TABLE_TRANSACTIONS = 'mns_transactions';

    /**
     * Fichas de bienvenida para nuevos usuarios.
     */
    const WELCOME_TOKENS = 100;

    /**
     * Instancia singleton.
     *
     * @var Casino_Tokens_MNS|null
     */
    private static $instance = null;

    /**
     * Obtener instancia.
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
     * Constructor privado (singleton).
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Registrar hooks principales.
     */
    private function init_hooks() {

        // Crear tabla al activar el plugin.
        register_activation_hook( __FILE__, array( 'MNS_Tokens_Activator', 'activate' ) );

        // Fichas de bienvenida al registrar usuario.
        add_action( 'user_register', array( $this, 'give_welcome_tokens' ), 10, 1 );

        // Manejar compras (POST).
        add_action( 'init', array( 'MNS_Tokens_Helper', 'handle_token_purchase' ) );

        // Admin / público.
        if ( is_admin() ) {
            MNS_Tokens_Admin::instance();
        }
        MNS_Tokens_Public::instance();

        // Shortcodes.
        add_shortcode( 'mns_token_balance', array( 'MNS_Tokens_Public', 'shortcode_balance' ) );
        add_shortcode( 'mns_token_packs', array( 'MNS_Tokens_Public', 'shortcode_packs' ) );
        add_shortcode( 'mns_token_history', array( 'MNS_Tokens_Public', 'shortcode_history' ) );
    }

    /**
     * Dar fichas de bienvenida a nuevos usuarios.
     *
     * @param int $user_id
     */
    public function give_welcome_tokens( $user_id ) {
        $existing = get_user_meta( $user_id, self::META_KEY_TOKENS, true );
        if ( $existing === '' ) {
            update_user_meta( $user_id, self::META_KEY_TOKENS, self::WELCOME_TOKENS );
        }
    }
}

/**
 * Inicializar plugin.
 *
 * @return Casino_Tokens_MNS
 */
function casino_tokens_mns_init() {
    return Casino_Tokens_MNS::instance();
}
casino_tokens_mns_init();

endif;
