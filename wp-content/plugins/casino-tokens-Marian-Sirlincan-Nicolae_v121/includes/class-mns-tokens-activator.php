<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNS_Tokens_Activator {

    /**
     * Ejecutado al activar el plugin.
     *
     * @return void
     */
    public static function activate() {
        self::create_transactions_table();
    }

    /**
     * Crear tabla de historial de transacciones.
     *
     * @return void
     */
    private static function create_transactions_table() {
        global $wpdb;

        $table_name      = $wpdb->prefix . Casino_Tokens_MNS::TABLE_TRANSACTIONS;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            change_amount int(11) NOT NULL,
            action varchar(50) NOT NULL,
            balance_before int(11) NOT NULL,
            balance_after int(11) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}
