<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNS_Tokens_Public {

    /**
     * Singleton instance
     *
     * @var MNS_Tokens_Public
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return MNS_Tokens_Public
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
        // We could enqueue styles/scripts here if needed.
    }

    /**
     * Shortcode: [mns_token_balance]
     *
     * Muestra el saldo actual de fichas del usuario logueado.
     */
    public static function shortcode_balance( $atts ) {
        if ( ! is_user_logged_in() ) {
            return esc_html__( 'Debes iniciar sesión para ver tus fichas.', 'casino-tokens-mns' );
        }

        $user_id = get_current_user_id();
        $tokens  = MNS_Tokens_Helper::get_tokens( $user_id );

        ob_start();
        ?>
        <span class="mns-token-balance">
            <?php
            printf(
                /* translators: %d: número de fichas */
                esc_html__( 'Tienes %d fichas', 'casino-tokens-mns' ),
                intval( $tokens )
            );
            ?>
        </span>
        <?php
        return ob_get_clean();
    }

    /**
     * Shortcode: [mns_token_packs]
     *
     * Muestra los packs de fichas en forma de botones de compra.
     * Marian puede maquetar la estructura externa con su propio contenido;
     * aquí se generan los formularios de compra.
     */
    public static function shortcode_packs( $atts ) {
        $packs = array(
            100   => '1 €',
            500   => '5 €',
            1000  => '10 €',
            2500  => '25 €',
            5000  => '50 €',
            10000 => '100 €',
            100000=> '1000 €',
        );

        $msg = '';
        if ( isset( $_GET['mns_msg'] ) && $_GET['mns_msg'] === 'success' ) {
            $msg = '<div class="mns-msg mns-msg-success">' . esc_html__( 'Compra realizada correctamente.', 'casino-tokens-mns' ) . '</div>';
        }

        ob_start();
        echo $msg;

        ?>
        <div class="mns-token-packs">
            <?php foreach ( $packs as $tokens => $label ) : ?>
                <form method="post" class="mns-token-pack-form" style="display:inline-block; margin:0.5rem;">
                    <?php wp_nonce_field( 'mns_buy_tokens_action', 'mns_buy_tokens_nonce' ); ?>
                    <input type="hidden" name="mns_tokens_amount" value="<?php echo esc_attr( $tokens ); ?>" />
                    <button type="submit" name="mns_buy_tokens_submit" class="button mns-buy-button">
                        <?php echo esc_html( $label ); ?>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Shortcode: [mns_token_history]
     *
     * Muestra el historial del usuario logueado con paginación y selector
     * de número de operaciones a mostrar (10 / 50 / 100).
     */
    public static function shortcode_history( $atts ) {
        if ( ! is_user_logged_in() ) {
            return esc_html__( 'Debes iniciar sesión para ver tu historial.', 'casino-tokens-mns' );
        }

        global $wpdb;

        $user_id    = get_current_user_id();
        $table_name = $wpdb->prefix . Casino_Tokens_MNS::TABLE_TRANSACTIONS;

        $per_page = isset( $_GET['mns_per_page'] ) ? intval( $_GET['mns_per_page'] ) : 10;
        if ( ! in_array( $per_page, array( 10, 50, 100 ), true ) ) {
            $per_page = 10;
        }

        $paged  = isset( $_GET['mns_page'] ) ? max( 1, intval( $_GET['mns_page'] ) ) : 1;
        $offset = ( $paged - 1 ) * $per_page;

        // Count total rows for this user
        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d",
                $user_id
            )
        );

        // Get paginated rows
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $user_id,
                $per_page,
                $offset
            )
        );

        $total_pages = $total ? ceil( $total / $per_page ) : 1;

        ob_start();
        ?>
        <div class="mns-token-history-wrapper">
            <form method="get" class="mns-history-controls" style="margin-bottom:1rem;">
                <?php
                // Mantener otros parámetros de la URL
                foreach ( $_GET as $key => $value ) {
                    if ( in_array( $key, array( 'mns_per_page', 'mns_page' ), true ) ) {
                        continue;
                    }
                    if ( is_array( $value ) ) {
                        continue;
                    }
                    ?>
                    <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
                    <?php
                }
                ?>
                <label for="mns_per_page"><?php esc_html_e( 'Mostrar operaciones:', 'casino-tokens-mns' ); ?></label>
                <select name="mns_per_page" id="mns_per_page">
                    <?php foreach ( array( 10, 50, 100 ) as $option ) : ?>
                        <option value="<?php echo esc_attr( $option ); ?>" <?php selected( $per_page, $option ); ?>>
                            <?php echo esc_html( $option ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit"><?php esc_html_e( 'Actualizar', 'casino-tokens-mns' ); ?></button>
            </form>

            <table class="mns-token-history-table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="border:1px solid #ccc; padding:4px;"><?php esc_html_e( 'Fecha', 'casino-tokens-mns' ); ?></th>
                        <th style="border:1px solid #ccc; padding:4px;"><?php esc_html_e( 'Ganancia / Pérdida', 'casino-tokens-mns' ); ?></th>
                        <th style="border:1px solid #ccc; padding:4px;"><?php esc_html_e( 'Acción realizada', 'casino-tokens-mns' ); ?></th>
                        <th style="border:1px solid #ccc; padding:4px;"><?php esc_html_e( 'Saldo antes', 'casino-tokens-mns' ); ?></th>
                        <th style="border:1px solid #ccc; padding:4px;"><?php esc_html_e( 'Saldo después', 'casino-tokens-mns' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( ! empty( $rows ) ) : ?>
                        <?php foreach ( $rows as $row ) : ?>
                            <tr>
                                <td style="border:1px solid #ccc; padding:4px;"><?php echo esc_html( $row->created_at ); ?></td>
                                <td style="border:1px solid #ccc; padding:4px;">
                                    <?php
                                    $change = intval( $row->change_amount );
                                    if ( $change > 0 ) {
                                        echo '+' . esc_html( $change );
                                    } else {
                                        echo esc_html( $change );
                                    }
                                    ?>
                                </td>
                                <td style="border:1px solid #ccc; padding:4px;"><?php echo esc_html( $row->action ); ?></td>
                                <td style="border:1px solid #ccc; padding:4px;"><?php echo esc_html( $row->balance_before ); ?></td>
                                <td style="border:1px solid #ccc; padding:4px;"><?php echo esc_html( $row->balance_after ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" style="border:1px solid #ccc; padding:4px; text-align:center;">
                                <?php esc_html_e( 'No hay transacciones que mostrar.', 'casino-tokens-mns' ); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ( $total_pages > 1 ) : ?>
                <div class="mns-history-pagination" style="margin-top:1rem; text-align:center;">
                    <?php
                    $base_url = remove_query_arg( 'mns_page' );
                    for ( $i = 1; $i <= $total_pages; $i++ ) :
                        $url = esc_url( add_query_arg( 'mns_page', $i, $base_url ) );
                        ?>
                        <a href="<?php echo $url; ?>" class="mns-page-link <?php echo $i === $paged ? 'mns-current-page' : ''; ?>" style="margin:0 4px;">
                            <?php echo esc_html( $i ); ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php

        return ob_get_clean();
    }

}
