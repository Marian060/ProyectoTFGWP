<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNS_Tokens_Helper {

    /**
     * Obtener saldo de fichas de un usuario.
     *
     * @param int|null $user_id
     * @return int
     */
    public static function get_tokens( $user_id = null ) {
        if ( $user_id === null ) {
            $user_id = get_current_user_id();
        }
        $tokens = get_user_meta( $user_id, Casino_Tokens_MNS::META_KEY_TOKENS, true );
        if ( $tokens === '' ) {
            $tokens = 0;
        }
        return intval( $tokens );
    }

    /**
     * Establecer saldo de fichas.
     *
     * @param int $user_id
     * @param int $amount
     * @return bool
     */
    public static function set_tokens( $user_id, $amount ) {
        $amount = max( 0, intval( $amount ) );
        return update_user_meta( $user_id, Casino_Tokens_MNS::META_KEY_TOKENS, $amount );
    }

    /**
     * Sumar fichas al saldo.
     *
     * @param int    $user_id
     * @param int    $amount
     * @param string $action
     * @return bool
     */
    public static function add_tokens( $user_id, $amount, $action = 'compra' ) {
        $amount = intval( $amount );
        if ( $amount <= 0 ) {
            return false;
        }

        $before  = self::get_tokens( $user_id );
        $after   = $before + $amount;
        $updated = self::set_tokens( $user_id, $after );

        if ( $updated ) {
            self::log_transaction( $user_id, $amount, $action, $before, $after );
        }

        return $updated;
    }

    /**
     * Restar fichas del saldo (apuestas).
     *
     * @param int    $user_id
     * @param int    $amount
     * @param string $action
     * @return bool
     */
    public static function subtract_tokens( $user_id, $amount, $action = 'apuesta' ) {
        $amount = intval( $amount );
        if ( $amount <= 0 ) {
            return false;
        }

        $before = self::get_tokens( $user_id );
        if ( $before < $amount ) {
            return false;
        }
        $after   = $before - $amount;
        $updated = self::set_tokens( $user_id, $after );

        if ( $updated ) {
            self::log_transaction( $user_id, - $amount, $action, $before, $after );
        }

        return $updated;
    }

    /**
     * Registrar transacción en la tabla de historial.
     *
     * @param int    $user_id
     * @param int    $change
     * @param string $action
     * @param int    $before
     * @param int    $after
     * @return void
     */
    public static function log_transaction( $user_id, $change, $action, $before, $after ) {
        global $wpdb;

        $table_name = $wpdb->prefix . Casino_Tokens_MNS::TABLE_TRANSACTIONS;

        $wpdb->insert(
            $table_name,
            array(
                'user_id'        => intval( $user_id ),
                'change_amount'  => intval( $change ),
                'action'         => sanitize_text_field( $action ),
                'balance_before' => intval( $before ),
                'balance_after'  => intval( $after ),
                'created_at'     => current_time( 'mysql' ),
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%d',
                '%d',
                '%s',
            )
        );
    }

    /**
     * Manejar la compra de fichas enviada por formulario.
     *
     * Hookeado en init.
     *
     * @return void
     */
    public static function handle_token_purchase() {
        if ( ! isset( $_POST['mns_buy_tokens_submit'] ) ) {
            return;
        }

        // Comprobar nonce.
        if ( ! isset( $_POST['mns_buy_tokens_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['mns_buy_tokens_nonce'] ), 'mns_buy_tokens_action' ) ) {
            return;
        }

        // Si no está logueado, redirigir a login.
        if ( ! is_user_logged_in() ) {
            $redirect_to = isset( $_POST['_wp_http_referer'] ) ? esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) ) : home_url( '/' );
            wp_safe_redirect( wp_login_url( $redirect_to ) );
            exit;
        }

        $user_id = get_current_user_id();
        $amount  = isset( $_POST['mns_tokens_amount'] ) ? intval( $_POST['mns_tokens_amount'] ) : 0;

        if ( $amount > 0 ) {
            $before  = self::get_tokens( $user_id );
            $after   = $before + $amount;
            $updated = self::set_tokens( $user_id, $after );
            if ( $updated ) {
                self::log_transaction( $user_id, $amount, 'compra', $before, $after );
            }
        }

        // Redirigir de vuelta con mensaje de éxito.
        $redirect_to = isset( $_POST['_wp_http_referer'] ) ? esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) ) : home_url( '/' );
        $redirect_to = add_query_arg( 'mns_msg', 'success', $redirect_to );
        wp_safe_redirect( $redirect_to );
        exit;
    }
}
