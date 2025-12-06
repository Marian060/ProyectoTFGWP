<?php
/**
 * Plugin Name: Casino Games Marian Sirlincan Nicolae
 * Plugin URI:  https://example.com
 * Description: Tres juegos (Blackjack, Ruleta Europea y Ruleta Rusa) conectados al sistema de fichas Casino Tokens MNS.
 * Version:     1.1.0
 * Author:      Marian Sirlincan Nicolae
 * Text Domain: casino-games-mns
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Casino_Games_MNS' ) ) :

class Casino_Games_MNS {

    /**
     * Singleton
     *
     * @var Casino_Games_MNS|null
     */
    private static $instance = null;

    /**
     * Meta key para estado de Blackjack
     */
    const META_KEY_BJ_STATE = 'mns_bj_state';

    /**
     * Obtener instancia
     *
     * @return Casino_Games_MNS
     */
    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor privado
     */
    private function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Definir constantes del plugin
     */
    private function define_constants() {
        if ( ! defined( 'CASINO_GAMES_MNS_VERSION' ) ) {
            define( 'CASINO_GAMES_MNS_VERSION', '1.1.0' );
        }
        if ( ! defined( 'CASINO_GAMES_MNS_PLUGIN_FILE' ) ) {
            define( 'CASINO_GAMES_MNS_PLUGIN_FILE', __FILE__ );
        }
        if ( ! defined( 'CASINO_GAMES_MNS_PLUGIN_DIR' ) ) {
            define( 'CASINO_GAMES_MNS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        }
        if ( ! defined( 'CASINO_GAMES_MNS_PLUGIN_URL' ) ) {
            define( 'CASINO_GAMES_MNS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        }
    }

    /**
     * Incluir archivos
     */
    private function includes() {
        require_once CASINO_GAMES_MNS_PLUGIN_DIR . 'includes/class-mns-games-helper.php';
        require_once CASINO_GAMES_MNS_PLUGIN_DIR . 'public/class-mns-games-public.php';
    }

    /**
     * Hooks
     */
    private function init_hooks() {

        // Validar que el plugin de tokens existe
        add_action( 'admin_notices', array( $this, 'maybe_show_tokens_dependency_notice' ) );

        // Cargar JS público
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Manejo de acciones (POST) para cada juego
        add_action( 'init', array( 'MNS_Games_Public', 'handle_blackjack_actions' ) );
        add_action( 'init', array( 'MNS_Games_Public', 'handle_roulette_actions' ) );
        add_action( 'init', array( 'MNS_Games_Public', 'handle_russian_roulette_actions' ) );

        // Shortcodes
        add_shortcode( 'mns_game_blackjack', array( 'MNS_Games_Public', 'shortcode_blackjack' ) );
        add_shortcode( 'mns_game_roulette', array( 'MNS_Games_Public', 'shortcode_roulette' ) );
        add_shortcode( 'mns_game_russian_roulette', array( 'MNS_Games_Public', 'shortcode_russian_roulette' ) );
    }

    /**
     * Encolar scripts públicos
     */
    public function enqueue_scripts() {
        // Solo frontend
        if ( is_admin() ) {
            return;
        }

        // No tiene sentido cargar si no hay usuario
        if ( ! is_user_logged_in() ) {
            return;
        }

        // JS de la ruleta (giro real)
        wp_enqueue_script(
            'casino-games-mns-roulette',
            CASINO_GAMES_MNS_PLUGIN_URL . 'public/js/mns-roulette.js',
            array(),
            CASINO_GAMES_MNS_VERSION,
            true
        );
    }

    /**
     * Aviso si no está activo el plugin de fichas
     */
    public function maybe_show_tokens_dependency_notice() {
        if ( ! class_exists( 'MNS_Tokens_Helper' ) ) {
            echo '<div class="notice notice-error"><p>' .
                esc_html__( 'Casino Games MNS requiere el plugin "Casino Tokens Marian Sirlincan Nicolae" activo.', 'casino-games-mns' ) .
                '</p></div>';
        }
    }
}

endif;

/**
 * Inicializar plugin
 *
 * @return Casino_Games_MNS
 */
function casino_games_mns_init() {
    return Casino_Games_MNS::instance();
}
casino_games_mns_init();
