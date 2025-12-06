<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNS_Tokens_Helper {

    /**
     * Get tokens balance for a user
     *
     * @param int|null $user_id
     * @return int
     */
    public static function get_tokens( $user_id = null ) {
        if ( $user_id === null ) {
            $user_id = get_current_user_id();
        }
        $user_id = intval( $user_id );
        if ( $user_id <= 0 ) {
            return 0;
        }

        $tokens = get_user_meta( $user_id, Casino_Tokens_MNS::META_KEY_TOKENS, true );
        if ( $tokens === '' ) {
            return 0;
        }
        return intval( $tokens );
    }

    /**
     * Set tokens balance for a user
     *
     * @param int $user_id
     * @param int $amount
     * @return bool
     */
    public static function set_tokens( $user_id, $amount ) {
        $user_id = intval( $user_id );
        $amount  = max( 0, intval( $amount ) );
        if ( $user_id <= 0 ) {
            return false;
        }
        return (bool) update_user_meta( $user_id, Casino_Tokens_MNS::META_KEY_TOKENS, $amount );
    }

    /**
     * Add tokens to user balance
     *
     * @param int $user_id
     * @param int $amount
     * @param string $action
     * @return bool
     */
    public static function add_tokens( $user_id, $amount, $action = 'compra' ) {
        $user_id = intval( $user_id );
        $amount  = intval( $amount );
        if ( $user_id <= 0 || $amount <= 0 ) {
            return false;
        }

        $before = self::get_tokens( $user_id );
        $after  = $before + $amount;

        $updated = self::set_tokens( $user_id, $after );

        if ( $updated ) {
            self::log_transaction( $user_id, $amount, $action, $before, $after );
        }

        return $updated;
    }

    /**
     * Subtract tokens from user balance (for apuestas, pérdidas, etc.)
     *
     * @param int $user_id
     * @param int $amount
     * @param string $action
     * @return bool
     */
    public static function subtract_tokens( $user_id, $amount, $action = 'apuesta' ) {
        $user_id = intval( $user_id );
        $amount  = intval( $amount );
        if ( $user_id <= 0 || $amount <= 0 ) {
            return false;
        }

        $before = self::get_tokens( $user_id );
        if ( $before < $amount ) {
            return false;
        }
        $after = $before - $amount;

        $updated = self::set_tokens( $user_id, $after );

        if ( $updated ) {
            self::log_transaction( $user_id, - $amount, $action, $before, $after );
        }

        return $updated;
    }

    /**
     * Log transaction in DB
     *
     * @param int $user_id
     * @param int $change
     * @param string $action
     * @param int $before
     * @param int $after
     */
    public static function log_transaction( $user_id, $change, $action, $before, $after ) {
        global $wpdb;

        $table_name = $wpdb->prefix . Casino_Tokens_MNS::TABLE_TRANSACTIONS;

        $wpdb->insert(
            $table_name,
            array(
                'user_id'       => intval( $user_id ),
                'change_amount' => intval( $change ),
                'action'        => sanitize_text_field( $action ),
                'balance_before'=> intval( $before ),
                'balance_after' => intval( $after ),
                'created_at'    => current_time( 'mysql' ),
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
     * Handle token purchase form submissions
     */
    public static function handle_token_purchase() {
        if ( ! isset( $_POST['mns_buy_tokens_submit'] ) ) {
            return;
        }

        // Check nonce
        if ( ! isset( $_POST['mns_buy_tokens_nonce'] ) || ! wp_verify_nonce( $_POST['mns_buy_tokens_nonce'], 'mns_buy_tokens_action' ) ) {
            return;
        }

        // Must be logged in
        if ( ! is_user_logged_in() ) {
            $redirect_to = isset( $_POST['_wp_http_referer'] ) ? esc_url_raw( $_POST['_wp_http_referer'] ) : home_url( '/' );
            wp_safe_redirect( wp_login_url( $redirect_to ) );
            exit;
        }

        $user_id = get_current_user_id();
        $amount  = isset( $_POST['mns_tokens_amount'] ) ? intval( $_POST['mns_tokens_amount'] ) : 0;

        // Only allow expected packs
        $allowed_packs = array( 100, 500, 1000, 2500, 5000, 10000, 100000 );
        if ( ! in_array( $amount, $allowed_packs, true ) ) {
            return;
        }

        $before = self::get_tokens( $user_id );
        $after  = $before + $amount;

        $updated = self::set_tokens( $user_id, $after );
        if ( $updated ) {
            self::log_transaction( $user_id, $amount, 'compra', $before, $after );
        }

        // Redirect back with success message
        $redirect_to = isset( $_POST['_wp_http_referer'] ) ? esc_url_raw( $_POST['_wp_http_referer'] ) : home_url( '/' );
        $redirect_to = add_query_arg( 'mns_msg', 'success', $redirect_to );
        wp_safe_redirect( $redirect_to );
        exit;
    }

}
