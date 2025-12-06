<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNS_Login_Helper {

    /**
     * Obtener URL de página por slug
     *
     * @param string $slug
     * @return string
     */
    public static function get_page_url_by_slug( $slug ) {
        $page = get_page_by_path( $slug );
        if ( $page ) {
            return get_permalink( $page->ID );
        }
        // Fallback
        return home_url( '/' . trailingslashit( $slug ) );
    }

    /**
     * Calcular edad a partir de fecha YYYY-MM-DD
     *
     * @param string $birthdate
     * @return int|null
     */
    public static function calculate_age( $birthdate ) {
        if ( empty( $birthdate ) ) {
            return null;
        }

        try {
            $birth = new DateTime( $birthdate );
            $today = new DateTime();
            $diff  = $today->diff( $birth );
            return (int) $diff->y;
        } catch ( Exception $e ) {
            return null;
        }
    }

    /**
     * Restringir acceso a wp-admin para usuarios no administradores
     * Usuarios deslogueados → redirigidos al login personalizado.
     * Usuarios logueados sin permisos → redirigidos a /perfil.
     */
    public static function restrict_admin_area() {

        // 1️⃣ Usuario NO logueado intenta acceder a /wp-admin → ir a /login
        if ( is_admin() && ! is_user_logged_in() ) {
            $login_url = self::get_page_url_by_slug( Casino_Login_MNS::PAGE_LOGIN_SLUG );
            wp_safe_redirect( $login_url );
            exit;
        }

        // 2️⃣ Usuario logueado SIN permisos administrativos → ir a /perfil
        if ( is_admin() && is_user_logged_in() && ! current_user_can( 'manage_options' ) && ! defined( 'DOING_AJAX' ) ) {
            $profile_url = self::get_page_url_by_slug( Casino_Login_MNS::PAGE_PROFILE_SLUG );
            wp_safe_redirect( $profile_url );
            exit;
        }
    }

    /**
     * Ocultar barra de admin para usuarios no administradores
     */
    public static function maybe_hide_admin_bar() {
        if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
            add_filter( 'show_admin_bar', '__return_false' );
        }
    }

    /**
     * Obtener URL de avatar para un usuario
     *
     * @param int $user_id
     * @return string
     */
    public static function get_avatar_url( $user_id ) {
        $custom = get_user_meta( $user_id, Casino_Login_MNS::META_AVATAR_URL, true );
        if ( $custom ) {
            return esc_url( $custom );
        }
        return esc_url( Casino_Login_MNS::DEFAULT_AVATAR_URL );
    }

}
