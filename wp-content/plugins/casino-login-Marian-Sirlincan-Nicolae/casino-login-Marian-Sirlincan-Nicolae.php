<?php
/**
 * Plugin Name: Casino Login Marian Sirlincan Nicolae
 * Plugin URI:  https://example.com
 * Description: Sistema de login, registro y perfil personalizado para Casino M.N.S, integrado con el sistema de fichas.
 * Version:     1.1.0
 * Author:      Marian Sirlincan Nicolae
 * Text Domain: casino-login-mns
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Casino_Login_MNS' ) ) :

class Casino_Login_MNS {

    /**
     * Singleton instance
     *
     * @var Casino_Login_MNS
     */
    private static $instance = null;

    /**
     * Slugs de páginas
     */
    const PAGE_LOGIN_SLUG    = 'login';
    const PAGE_REGISTER_SLUG = 'registrarse';
    const PAGE_PROFILE_SLUG  = 'perfil';

    /**
     * Meta keys
     */
    const META_BIRTHDATE  = 'mns_birthdate';
    const META_AVATAR_URL = 'mns_avatar_url';

    /**
     * Default avatar placeholder
     */
    const DEFAULT_AVATAR_URL = 'https://nojodas.es/wp-content/uploads/2025/12/blank-profile-picture-973460_640.webp';

    /**
     * Get singleton instance
     *
     * @return Casino_Login_MNS
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
     * Define constants
     */
    private function define_constants() {
        if ( ! defined( 'CASINO_LOGIN_MNS_VERSION' ) ) {
            define( 'CASINO_LOGIN_MNS_VERSION', '1.1.0' );
        }
        if ( ! defined( 'CASINO_LOGIN_MNS_PLUGIN_FILE' ) ) {
            define( 'CASINO_LOGIN_MNS_PLUGIN_FILE', __FILE__ );
        }
        if ( ! defined( 'CASINO_LOGIN_MNS_PLUGIN_DIR' ) ) {
            define( 'CASINO_LOGIN_MNS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        }
        if ( ! defined( 'CASINO_LOGIN_MNS_PLUGIN_URL' ) ) {
            define( 'CASINO_LOGIN_MNS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        }
    }

    /**
     * Include files
     */
    private function includes() {
        require_once CASINO_LOGIN_MNS_PLUGIN_DIR . 'includes/class-mns-login-helper.php';
        require_once CASINO_LOGIN_MNS_PLUGIN_DIR . 'public/class-mns-login-public.php';
    }

    /**
     * Init hooks
     */
    private function init_hooks() {
        // Manejo de login/registro
        add_action( 'init', array( 'MNS_Login_Public', 'handle_login' ) );
        add_action( 'init', array( 'MNS_Login_Public', 'handle_register' ) );

        // Restricción de wp-admin para usuarios normales
        add_action( 'init', array( 'MNS_Login_Helper', 'restrict_admin_area' ) );

        // Ocultar barra de admin para usuarios no administradores
        add_action( 'after_setup_theme', array( 'MNS_Login_Helper', 'maybe_hide_admin_bar' ) );

        // Shortcodes
        add_shortcode( 'mns_login_form', array( 'MNS_Login_Public', 'shortcode_login_form' ) );
        add_shortcode( 'mns_register_form', array( 'MNS_Login_Public', 'shortcode_register_form' ) );
        add_shortcode( 'mns_profile_page', array( 'MNS_Login_Public', 'shortcode_profile_page' ) );
        add_shortcode( 'mns_auth_menu', array( 'MNS_Login_Public', 'shortcode_auth_menu' ) );
        add_shortcode( 'mns_logout_button', array( 'MNS_Login_Public', 'shortcode_logout_button' ) );

        // Encolar estilos y scripts del login/registro/perfil
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Encolar CSS y JS del plugin
     */
    public function enqueue_assets() {
        // CSS principal de login/registro/perfil
        wp_enqueue_style(
            'mns-login-register',
            CASINO_LOGIN_MNS_PLUGIN_URL . 'assets/css/login-register.css',
            array(),
            CASINO_LOGIN_MNS_VERSION
        );

        // JS para mostrar/ocultar contraseñas y desplegar políticas
        wp_enqueue_script(
            'mns-login-register',
            CASINO_LOGIN_MNS_PLUGIN_URL . 'assets/js/login-register.js',
            array( 'jquery' ),
            CASINO_LOGIN_MNS_VERSION,
            true
        );
    }

}

// Initialize plugin
function casino_login_mns_init() {
    return Casino_Login_MNS::instance();
}
casino_login_mns_init();

endif;
