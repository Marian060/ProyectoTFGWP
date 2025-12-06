<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNS_Games_Helper {

    /**
     * Obtener estado de Blackjack del usuario
     */
    public static function get_blackjack_state( $user_id = null ) {
        if ( $user_id === null ) {
            $user_id = get_current_user_id();
        }
        $state = get_user_meta( $user_id, Casino_Games_MNS::META_KEY_BJ_STATE, true );
        if ( ! is_array( $state ) ) {
            $state = array();
        }
        return $state;
    }

    /**
     * Guardar estado de Blackjack
     */
    public static function set_blackjack_state( $state, $user_id = null ) {
        if ( $user_id === null ) {
            $user_id = get_current_user_id();
        }
        if ( empty( $state ) || ! is_array( $state ) ) {
            delete_user_meta( $user_id, Casino_Games_MNS::META_KEY_BJ_STATE );
            return;
        }
        update_user_meta( $user_id, Casino_Games_MNS::META_KEY_BJ_STATE, $state );
    }

    /**
     * Resetear la partida de Blackjack
     */
    public static function reset_blackjack_state( $user_id = null ) {
        if ( $user_id === null ) {
            $user_id = get_current_user_id();
        }
        delete_user_meta( $user_id, Casino_Games_MNS::META_KEY_BJ_STATE );
    }

    /**
     * Crear un mazo estándar de 52 cartas
     *
     * Formato de carta: "AH", "10D", "KC", etc.
     */
    public static function generate_deck() {
        $ranks = array( 'A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K' );
        $suits = array( 'H', 'D', 'C', 'S' );
        $deck  = array();

        foreach ( $suits as $suit ) {
            foreach ( $ranks as $rank ) {
                $deck[] = $rank . $suit;
            }
        }

        shuffle( $deck );

        return $deck;
    }

    /**
     * Robar una carta del mazo y actualizar el mazo
     */
    public static function draw_card_from_deck( &$deck ) {
        if ( empty( $deck ) ) {
            $deck = self::generate_deck();
        }
        return array_shift( $deck );
    }

    /**
     * Calcular valor de mano de Blackjack
     *
     * @param array $cards
     * @return array [ 'value' => int, 'is_blackjack' => bool, 'is_bust' => bool, 'is_soft' => bool ]
     */
    public static function calculate_blackjack_hand_value( array $cards ) {
        $value      = 0;
        $aces       = 0;
        $card_count = count( $cards );

        foreach ( $cards as $card ) {
            // Extraer rank (todo menos el último char si es letra de palo)
            $rank = preg_replace( '/[HDCS]$/', '', $card );

            if ( $rank === 'A' ) {
                $aces++;
                $value += 11;
            } elseif ( in_array( $rank, array( 'K', 'Q', 'J' ), true ) ) {
                $value += 10;
            } else {
                $value += intval( $rank );
            }
        }

        $is_soft = false;

        // Ajustar Ases de 11 a 1 si es necesario
        while ( $value > 21 && $aces > 0 ) {
            $value -= 10;
            $aces--;
        }

        if ( $aces > 0 ) {
            $is_soft = true;
        }

        $is_blackjack = ( $card_count === 2 && $value === 21 );
        $is_bust      = ( $value > 21 );

        return array(
            'value'        => $value,
            'is_blackjack' => $is_blackjack,
            'is_bust'      => $is_bust,
            'is_soft'      => $is_soft,
        );
    }

    /**
     * Evaluar resultado de Blackjack (sin split) y devolver estado final
     *
     * @param array $player_value
     * @param array $dealer_value
     * @return string 'blackjack'|'win'|'push'|'lose'
     */
    public static function evaluate_blackjack_result( array $player_value, array $dealer_value ) {
        if ( $player_value['is_bust'] ) {
            return 'lose';
        }

        if ( $player_value['is_blackjack'] && ! $dealer_value['is_blackjack'] ) {
            return 'blackjack';
        }

        if ( $dealer_value['is_bust'] ) {
            return 'win';
        }

        if ( $player_value['value'] > $dealer_value['value'] ) {
            return 'win';
        } elseif ( $player_value['value'] < $dealer_value['value'] ) {
            return 'lose';
        }

        return 'push';
    }

    /**
     * Obtener color de ruleta europea
     *
     * @param int $number
     * @return string 'red'|'black'|'green'
     */
    public static function get_roulette_color( $number ) {
        $number = intval( $number );
        if ( $number === 0 ) {
            return 'green';
        }

        // Estándar ruleta europea
        $reds = array( 1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36 );
        if ( in_array( $number, $reds, true ) ) {
            return 'red';
        }

        return 'black';
    }

    /**
     * Evaluar apuestas de ruleta y devolver total de ganancias
     *
     * @param array $bets
     * @param int   $number
     * @return int
     */
    public static function evaluate_roulette_bets( array $bets, $number ) {
        $number = intval( $number );
        $color  = self::get_roulette_color( $number );

        $total_win = 0;

        foreach ( $bets as $bet ) {
            $type   = isset( $bet['type'] ) ? $bet['type'] : '';
            $value  = isset( $bet['value'] ) ? $bet['value'] : '';
            $amount = isset( $bet['amount'] ) ? intval( $bet['amount'] ) : 0;

            if ( $amount <= 0 ) {
                continue;
            }

            switch ( $type ) {
                case 'straight':
                    if ( intval( $value ) === $number ) {
                        $total_win += $amount * 36;
                    }
                    break;

                case 'color':
                    if ( $number !== 0 && $value === $color ) {
                        $total_win += $amount * 2;
                    }
                    break;

                case 'even_odd':
                    if ( $number !== 0 ) {
                        if ( $value === 'even' && $number % 2 === 0 ) {
                            $total_win += $amount * 2;
                        } elseif ( $value === 'odd' && $number % 2 === 1 ) {
                            $total_win += $amount * 2;
                        }
                    }
                    break;

                case 'low_high':
                    if ( $number >= 1 && $number <= 18 && $value === 'low' ) {
                        $total_win += $amount * 2;
                    } elseif ( $number >= 19 && $number <= 36 && $value === 'high' ) {
                        $total_win += $amount * 2;
                    }
                    break;

                case 'dozen':
                    $dozen = intval( $value ); // 1,2,3
                    if ( $dozen === 1 && $number >= 1 && $number <= 12 ) {
                        $total_win += $amount * 3;
                    } elseif ( $dozen === 2 && $number >= 13 && $number <= 24 ) {
                        $total_win += $amount * 3;
                    } elseif ( $dozen === 3 && $number >= 25 && $number <= 36 ) {
                        $total_win += $amount * 3;
                    }
                    break;

                case 'column':
                    $col = intval( $value ); // 1,2,3
                    if ( $number !== 0 ) {
                        $col_number = ( ( $number - 1 ) % 3 ) + 1; // 1,2,3
                        if ( $col_number === $col ) {
                            $total_win += $amount * 3;
                        }
                    }
                    break;
            }
        }

        return $total_win;
    }

    /**
     * Comprobar si un usuario está protegido como admin (para ruleta rusa)
     */
    public static function is_admin_protected( $user_id ) {
        return user_can( $user_id, 'manage_options' );
    }

    /**
     * Ejecutar la ruleta rusa
     *
     * @param int $user_id
     * @return array [ 'result' => 'win'|'lose'|'no_tokens'|'protected', 'message' => string ]
     */
    public static function play_russian_roulette( $user_id ) {
        if ( self::is_admin_protected( $user_id ) ) {
            return array(
                'result'  => 'protected',
                'message' => __( 'Los administradores no pueden jugar a la ruleta rusa.', 'casino-games-mns' ),
            );
        }

        if ( ! class_exists( 'MNS_Tokens_Helper' ) ) {
            return array(
                'result'  => 'error',
                'message' => __( 'Sistema de fichas no disponible.', 'casino-games-mns' ),
            );
        }

        $balance = MNS_Tokens_Helper::get_tokens( $user_id );
        if ( $balance <= 0 ) {
            return array(
                'result'  => 'no_tokens',
                'message' => __( 'No tienes fichas para jugar.', 'casino-games-mns' ),
            );
        }

        // Cámara 1/6
        $chamber = wp_rand( 1, 6 );
        $bullet  = 1;
        $is_dead = ( $chamber === $bullet );

        if ( $is_dead ) {
            // Eliminar historial de transacciones y usuario
            global $wpdb;

            if ( class_exists( 'Casino_Tokens_MNS' ) ) {
                $table = $wpdb->prefix . Casino_Tokens_MNS::TABLE_TRANSACTIONS;
                $wpdb->delete( $table, array( 'user_id' => $user_id ), array( '%d' ) );
            }

            // Opcional: poner balance a 0 antes de borrar
            if ( method_exists( 'MNS_Tokens_Helper', 'set_tokens' ) ) {
                MNS_Tokens_Helper::set_tokens( $user_id, 0 );
            }

            // Borrar usuario (wp_users + wp_usermeta)
            require_once ABSPATH . 'wp-admin/includes/user.php';
            wp_delete_user( $user_id );

            // Destruir sesión actual
            if ( function_exists( 'wp_destroy_current_session' ) ) {
                wp_destroy_current_session();
            }
            wp_clear_auth_cookie();

            return array(
                'result'  => 'lose',
                'message' => __( 'HAS PERDIDO. Tu cuenta ha sido eliminada.', 'casino-games-mns' ),
            );
        }

        // Sobrevive: duplica fichas -> gana exactamente su balance actual
        $gain = $balance;
        MNS_Tokens_Helper::add_tokens( $user_id, $gain, 'russian_roulette_win' );

        return array(
            'result'  => 'win',
            'message' => __( 'Has sobrevivido. Tus fichas se han duplicado.', 'casino-games-mns' ),
        );
    }
}
