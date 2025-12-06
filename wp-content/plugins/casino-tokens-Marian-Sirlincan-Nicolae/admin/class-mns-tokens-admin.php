<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNS_Tokens_Admin {

    /**
     * Singleton instance
     *
     * @var MNS_Tokens_Admin
     */
    private static $instance = null;

    /**
     * Get instance
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
     * Constructor
     */
    private function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
    }

    /**
     * Register admin menu
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
     * Render global history page for admin
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

        $paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $offset = ( $paged - 1 ) * $per_page;

        // Optional filter by user
        $user_filter = '';
        $user_param  = '';
        if ( ! empty( $_GET['mns_user'] ) ) {
            $user_filter = ' WHERE user_id = %d ';
            $user_param  = intval( $_GET['mns_user'] );
        }

        if ( $user_filter ) {
            $total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} {$user_filter}", $user_param ) );
            $rows  = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name} {$user_filter} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                    $user_param,
                    $per_page,
                    $offset
                )
            );
        } else {
            $total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
            $rows  = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                    $per_page,
                    $offset
                )
            );
        }

        $total_pages = $total ? ceil( $total / $per_page ) : 1;

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Historial global de transacciones', 'casino-tokens-mns' ); ?></h1>

            <form method="get" style="margin-bottom: 1em;">
                <input type="hidden" name="page" value="mns-casino-tokens" />
                <label for="mns_user"><?php esc_html_e( 'Filtrar por ID de usuario:', 'casino-tokens-mns' ); ?></label>
                <input type="number" name="mns_user" id="mns_user" value="<?php echo isset( $_GET['mns_user'] ) ? intval( $_GET['mns_user'] ) : ''; ?>" />
                <label for="mns_per_page"><?php esc_html_e( 'Mostrar:', 'casino-tokens-mns' ); ?></label>
                <select name="mns_per_page" id="mns_per_page">
                    <?php foreach ( array( 10, 20, 50, 100 ) as $option ) : ?>
                        <option value="<?php echo esc_attr( $option ); ?>" <?php selected( $per_page, $option ); ?>>
                            <?php echo esc_html( $option ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="button button-primary" type="submit"><?php esc_html_e( 'Aplicar', 'casino-tokens-mns' ); ?></button>
            </form>

            <table class="widefat fixed striped">
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
                    <?php if ( ! empty( $rows ) ) : ?>
                        <?php foreach ( $rows as $row ) : ?>
                            <tr>
                                <td><?php echo esc_html( $row->id ); ?></td>
                                <td><?php echo esc_html( $row->user_id ); ?></td>
                                <td><?php echo esc_html( $row->created_at ); ?></td>
                                <td><?php echo esc_html( $row->change_amount ); ?></td>
                                <td><?php echo esc_html( $row->action ); ?></td>
                                <td><?php echo esc_html( $row->balance_before ); ?></td>
                                <td><?php echo esc_html( $row->balance_after ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7"><?php esc_html_e( 'No hay transacciones registradas.', 'casino-tokens-mns' ); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ( $total_pages > 1 ) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        $base_url = remove_query_arg( 'paged' );
                        for ( $i = 1; $i <= $total_pages; $i++ ) :
                            $url = esc_url( add_query_arg( 'paged', $i, $base_url ) );
                            ?>
                            <a class="page-numbers <?php echo $i === $paged ? 'current' : ''; ?>" href="<?php echo $url; ?>">
                                <?php echo esc_html( $i ); ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

}
