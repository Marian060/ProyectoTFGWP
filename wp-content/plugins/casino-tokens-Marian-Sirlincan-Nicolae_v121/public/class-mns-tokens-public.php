<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNS_Tokens_Public {

    /**
     * Instancia singleton.
     *
     * @var MNS_Tokens_Public|null
     */
    private static $instance = null;

    /**
     * Obtener instancia.
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
     * Shortcode: [mns_token_balance]
     *
     * Muestra el saldo de fichas del usuario + icono.
     *
     * @param array $atts
     * @return string
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
            <img class="mns-token-icon" src="https://nojodas.es/wp-content/uploads/2025/12/Tokens.png" alt="<?php esc_attr_e( 'Icono de fichas', 'casino-tokens-mns' ); ?>" />
        </span>
        <?php
        return ob_get_clean();
    }

    /**
     * Shortcode: [mns_token_packs]
     *
     * Muestra los packs de fichas como "content boxes":
     * imagen + cantidad de fichas + botón con el precio.
     *
     * @param array $atts
     * @return string
     */
    public static function shortcode_packs( $atts ) {

        if ( ! is_user_logged_in() ) {
            return esc_html__( 'Debes iniciar sesión para comprar fichas.', 'casino-tokens-mns' );
        }

        // Definición de packs: precio (€) => datos.
        $packs = array(
            1    => array(
                'tokens' => 100,
                'img'    => 'https://nojodas.es/wp-content/uploads/2025/12/Tokens.png',
            ),
            5    => array(
                'tokens' => 500,
                'img'    => 'https://nojodas.es/wp-content/uploads/2025/12/Capa_2.png',
            ),
            10   => array(
                'tokens' => 1000,
                'img'    => 'https://nojodas.es/wp-content/uploads/2025/12/Capa_3.png',
            ),
            25   => array(
                'tokens' => 2500,
                'img'    => 'https://nojodas.es/wp-content/uploads/2025/12/Capa_5.png',
            ),
            50   => array(
                'tokens' => 5000,
                'img'    => 'https://nojodas.es/wp-content/uploads/2025/12/Capa_4.png',
            ),
            100  => array(
                'tokens' => 10000,
                'img'    => 'https://nojodas.es/wp-content/uploads/2025/12/Capa_6.png',
            ),
            1000 => array(
                'tokens' => 100000,
                'img'    => 'https://nojodas.es/wp-content/uploads/2025/12/Capa_1.png',
            ),
        );

        $msg = '';
        if ( isset( $_GET['mns_msg'] ) && $_GET['mns_msg'] === 'success' ) {
            $msg = '<div class="mns-msg mns-msg-success">' . esc_html__( 'Compra de fichas realizada correctamente.', 'casino-tokens-mns' ) . '</div>';
        }

        ob_start();
        echo $msg;
        ?>
        <div class="mns-token-packs">
            <?php foreach ( $packs as $price => $data ) : ?>
                <div class="mns-pack-box">
                    <img class="mns-pack-img" src="<?php echo esc_url( $data['img'] ); ?>" alt="<?php echo esc_attr( sprintf( 'Pack de %d fichas', intval( $data['tokens'] ) ) ); ?>" />
                    <div class="mns-pack-tokens">
                        <?php echo esc_html( number_format_i18n( $data['tokens'] ) ); ?> <?php esc_html_e( 'fichas', 'casino-tokens-mns' ); ?>
                    </div>

                    <form method="post" class="mns-token-pack-form">
                        <?php wp_nonce_field( 'mns_buy_tokens_action', 'mns_buy_tokens_nonce' ); ?>
                        <input type="hidden" name="mns_tokens_amount" value="<?php echo esc_attr( intval( $data['tokens'] ) ); ?>" />
                        <button type="submit" name="mns_buy_tokens_submit" class="button mns-buy-button">
                            <?php echo esc_html( $price ); ?> €
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Shortcode: [mns_token_history]
     *
     * Historial del usuario logueado con paginación y selector
     * de número de operaciones (10 / 50 / 100).
     *
     * @param array $atts
     * @return string
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

        // Total de filas para este usuario.
        $total = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d",
                $user_id
            )
        );

        // Filas paginadas.
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $user_id,
                $per_page,
                $offset
            )
        );

        $total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;

        ob_start();
        ?>
        <div class="mns-token-history-wrapper">
            <h3><?php esc_html_e( 'Historial de fichas', 'casino-tokens-mns' ); ?></h3>

            <?php if ( ! empty( $rows ) ) : ?>
                <table class="mns-token-history-table" style="width:100%; border-collapse:collapse; margin-bottom:1rem;">
                    <thead>
                        <tr>
                            <th style="border:1px solid #ccc; padding:4px;"><?php esc_html_e( 'Fecha', 'casino-tokens-mns' ); ?></th>
                            <th style="border:1px solid #ccc; padding:4px;"><?php esc_html_e( 'Cambio', 'casino-tokens-mns' ); ?></th>
                            <th style="border:1px solid #ccc; padding:4px;"><?php esc_html_e( 'Acción', 'casino-tokens-mns' ); ?></th>
                            <th style="border:1px solid #ccc; padding:4px;"><?php esc_html_e( 'Saldo antes', 'casino-tokens-mns' ); ?></th>
                            <th style="border:1px solid #ccc; padding:4px;"><?php esc_html_e( 'Saldo después', 'casino-tokens-mns' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
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
                                <td style="border:1px solid #ccc; padding:4px;">
                                    <?php echo esc_html( $row->balance_before ); ?>
                                    <img class="mns-token-icon" src="https://nojodas.es/wp-content/uploads/2025/12/Tokens.png" alt="<?php esc_attr_e( 'Icono de fichas', 'casino-tokens-mns' ); ?>" />
                                </td>
                                <td style="border:1px solid #ccc; padding:4px;">
                                    <?php echo esc_html( $row->balance_after ); ?>
                                    <img class="mns-token-icon" src="https://nojodas.es/wp-content/uploads/2025/12/Tokens.png" alt="<?php esc_attr_e( 'Icono de fichas', 'casino-tokens-mns' ); ?>" />
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="mns-token-history-footer" style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">

                    <form method="get" class="mns-token-history-per-page">
                        <?php
                        // Mantener otros parámetros de la URL.
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
                        <select name="mns_per_page" id="mns_per_page" onchange="this.form.submit()">
                            <option value="10" <?php selected( $per_page, 10 ); ?>>10</option>
                            <option value="50" <?php selected( $per_page, 50 ); ?>>50</option>
                            <option value="100" <?php selected( $per_page, 100 ); ?>>100</option>
                        </select>
                    </form>

                    <div class="mns-token-history-pagination">
                        <?php
                        if ( $total_pages > 1 ) {
                            for ( $i = 1; $i <= $total_pages; $i++ ) {
                                $url = add_query_arg(
                                    array(
                                        'mns_page'     => $i,
                                        'mns_per_page' => $per_page,
                                    )
                                );
                                if ( $i === $paged ) {
                                    echo '<span style="margin-right:4px; font-weight:bold;">' . esc_html( $i ) . '</span>';
                                } else {
                                    echo '<a style="margin-right:4px;" href="' . esc_url( $url ) . '">' . esc_html( $i ) . '</a>';
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php else : ?>
                <p><?php esc_html_e( 'No hay transacciones que mostrar.', 'casino-tokens-mns' ); ?></p>
            <?php endif; ?>
        </div>
        <?php

        return ob_get_clean();
    }
}
