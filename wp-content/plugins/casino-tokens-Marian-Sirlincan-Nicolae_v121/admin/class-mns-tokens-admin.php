<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNS_Tokens_Admin {

    /**
     * Instancia singleton.
     *
     * @var MNS_Tokens_Admin|null
     */
    private static $instance = null;

    /**
     * Obtener instancia.
     *
     * @return MNS_Tokens_Admin
     */
    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
    }

    /**
     * Registrar página de administración.
     */
    public function register_menu() {
        add_menu_page(
            __( 'Casino Tokens', 'casino-tokens-mns' ),
            __( 'Casino Tokens', 'casino-tokens-mns' ),
            'manage_options',
            'mns-casino-tokens',
            array( $this, 'render_history_page' ),
            'dashicons-tickets',
            65
        );
    }

    /**
     * Página de historial global de fichas.
     */
    public function render_history_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . Casino_Tokens_MNS::TABLE_TRANSACTIONS;

        $per_page = isset( $_GET['mns_per_page'] ) ? intval( $_GET['mns_per_page'] ) : 20;
        if ( ! in_array( $per_page, array( 10, 20, 50, 100 ), true ) ) {
            $per_page = 20;
        }

        $paged  = isset( $_GET['mns_page'] ) ? max( 1, intval( $_GET['mns_page'] ) ) : 1;
        $offset = ( $paged - 1 ) * $per_page;

        // Filtro opcional por user_id.
        $user_filter = '';
        $user_param  = null;
        if ( isset( $_GET['mns_user'] ) && $_GET['mns_user'] !== '' ) {
            $user_id     = intval( $_GET['mns_user'] );
            $user_filter = 'WHERE user_id = %d';
            $user_param  = $user_id;
        }

        if ( $user_filter ) {
            $total = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table_name} {$user_filter}",
                    $user_param
                )
            );
            $rows  = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name} {$user_filter} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                    $user_param,
                    $per_page,
                    $offset
                )
            );
        } else {
            $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
            $rows  = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                    $per_page,
                    $offset
                )
            );
        }

        $total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Historial global de fichas', 'casino-tokens-mns' ); ?></h1>

            <form method="get" style="margin-bottom:1rem;">
                <input type="hidden" name="page" value="mns-casino-tokens" />
                <label for="mns_user"><?php esc_html_e( 'Filtrar por ID de usuario:', 'casino-tokens-mns' ); ?></label>
                <input type="number" name="mns_user" id="mns_user" value="<?php echo isset( $_GET['mns_user'] ) ? esc_attr( intval( $_GET['mns_user'] ) ) : ''; ?>" />
                <label for="mns_per_page"><?php esc_html_e( 'Por página:', 'casino-tokens-mns' ); ?></label>
                <select name="mns_per_page" id="mns_per_page">
                    <option value="10" <?php selected( $per_page, 10 ); ?>>10</option>
                    <option value="20" <?php selected( $per_page, 20 ); ?>>20</option>
                    <option value="50" <?php selected( $per_page, 50 ); ?>>50</option>
                    <option value="100" <?php selected( $per_page, 100 ); ?>>100</option>
                </select>
                <button class="button button-primary" type="submit"><?php esc_html_e( 'Filtrar', 'casino-tokens-mns' ); ?></button>
            </form>

            <?php if ( ! empty( $rows ) ) : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'ID', 'casino-tokens-mns' ); ?></th>
                            <th><?php esc_html_e( 'Usuario', 'casino-tokens-mns' ); ?></th>
                            <th><?php esc_html_e( 'Fecha', 'casino-tokens-mns' ); ?></th>
                            <th><?php esc_html_e( 'Cambio', 'casino-tokens-mns' ); ?></th>
                            <th><?php esc_html_e( 'Acción', 'casino-tokens-mns' ); ?></th>
                            <th><?php esc_html_e( 'Saldo antes', 'casino-tokens-mns' ); ?></th>
                            <th><?php esc_html_e( 'Saldo después', 'casino-tokens-mns' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $rows as $row ) : ?>
                            <tr>
                                <td><?php echo esc_html( $row->id ); ?></td>
                                <td><?php echo esc_html( $row->user_id ); ?></td>
                                <td><?php echo esc_html( $row->created_at ); ?></td>
                                <td><?php echo esc_html( $row->change_amount ); ?></td>
                                <td><?php echo esc_html( $row->action ); ?></td>
                                <td>
                                    <?php echo esc_html( $row->balance_before ); ?>
                                    <img class="mns-token-icon" src="https://nojodas.es/wp-content/uploads/2025/12/Tokens.png" alt="<?php esc_attr_e( 'Icono de fichas', 'casino-tokens-mns' ); ?>" />
                                </td>
                                <td>
                                    <?php echo esc_html( $row->balance_after ); ?>
                                    <img class="mns-token-icon" src="https://nojodas.es/wp-content/uploads/2025/12/Tokens.png" alt="<?php esc_attr_e( 'Icono de fichas', 'casino-tokens-mns' ); ?>" />
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        if ( $total_pages > 1 ) {
                            for ( $i = 1; $i <= $total_pages; $i++ ) {
                                $url = add_query_arg(
                                    array(
                                        'page'         => 'mns-casino-tokens',
                                        'mns_page'     => $i,
                                        'mns_per_page' => $per_page,
                                        'mns_user'     => isset( $_GET['mns_user'] ) ? intval( $_GET['mns_user'] ) : '',
                                    ),
                                    admin_url( 'admin.php' )
                                );
                                if ( $i === $paged ) {
                                    echo '<span class="tablenav-pages-navspan" style="margin-right:4px;">' . esc_html( $i ) . '</span>';
                                } else {
                                    echo '<a class="tablenav-pages-navspan" style="margin-right:4px;" href="' . esc_url( $url ) . '">' . esc_html( $i ) . '</a>';
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php else : ?>
                <p><?php esc_html_e( 'No hay transacciones registradas.', 'casino-tokens-mns' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
}
