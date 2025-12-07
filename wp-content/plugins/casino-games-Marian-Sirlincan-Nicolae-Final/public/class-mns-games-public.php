<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNS_Games_Public {

    public static $blackjack_message = '';
    public static $roulette_message  = '';
    public static $russian_message   = '';

    public static function handle_blackjack_actions() {
        if ( ! isset( $_POST['mns_bj_action'] ) ) {
            return;
        }

        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! isset( $_POST['mns_bj_nonce'] ) || ! wp_verify_nonce( $_POST['mns_bj_nonce'], 'mns_blackjack_action' ) ) {
            return;
        }

        if ( ! class_exists( 'MNS_Tokens_Helper' ) ) {
            self::$blackjack_message = __( 'El sistema de fichas no está disponible.', 'casino-games-mns' );
            return;
        }

        $user_id = get_current_user_id();
        $action  = sanitize_text_field( wp_unslash( $_POST['mns_bj_action'] ) );
        $state   = MNS_Games_Helper::get_blackjack_state( $user_id );

        if ( $action === 'start' ) {
            $bet = isset( $_POST['mns_bj_bet'] ) ? intval( $_POST['mns_bj_bet'] ) : 0;

            if ( $bet <= 0 ) {
                self::$blackjack_message = __( 'La apuesta debe ser mayor que cero.', 'casino-games-mns' );
                return;
            }

            $tokens = MNS_Tokens_Helper::get_tokens( $user_id );
            if ( $tokens < $bet ) {
                self::$blackjack_message = __( 'No tienes fichas suficientes para esa apuesta.', 'casino-games-mns' );
                return;
            }

            $ok = MNS_Tokens_Helper::subtract_tokens( $user_id, $bet, 'blackjack_bet' );
            if ( ! $ok ) {
                self::$blackjack_message = __( 'No ha sido posible registrar la apuesta.', 'casino-games-mns' );
                return;
            }

            $deck = MNS_Games_Helper::generate_deck();

            $player_hand = array(
                MNS_Games_Helper::draw_card_from_deck( $deck ),
                MNS_Games_Helper::draw_card_from_deck( $deck ),
            );
            $dealer_hand = array(
                MNS_Games_Helper::draw_card_from_deck( $deck ),
                MNS_Games_Helper::draw_card_from_deck( $deck ),
            );

            $state = array(
                'bet'         => $bet,
                'player_hand' => $player_hand,
                'dealer_hand' => $dealer_hand,
                'turn'        => 'player',
                'split'       => false,
                'deck'        => $deck,
            );

            MNS_Games_Helper::set_blackjack_state( $state, $user_id );
            self::$blackjack_message = __( 'Nueva mano iniciada.', 'casino-games-mns' );
            return;
        }

        if ( empty( $state ) || ! isset( $state['turn'] ) || $state['turn'] === 'finished' ) {
            self::$blackjack_message = __( 'No hay una partida de Blackjack en curso.', 'casino-games-mns' );
            return;
        }

        $deck = isset( $state['deck'] ) && is_array( $state['deck'] ) ? $state['deck'] : MNS_Games_Helper::generate_deck();

        switch ( $action ) {
            case 'hit':
                if ( $state['turn'] !== 'player' ) {
                    break;
                }
                $state['player_hand'][] = MNS_Games_Helper::draw_card_from_deck( $deck );
                $player_value           = MNS_Games_Helper::calculate_blackjack_hand_value( $state['player_hand'] );

                if ( $player_value['is_bust'] ) {
                    $state['turn'] = 'finished';
                    MNS_Games_Helper::set_blackjack_state( $state, $user_id );
                    self::$blackjack_message = __( 'Te has pasado. Has perdido la mano.', 'casino-games-mns' );
                    return;
                }

                $state['deck'] = $deck;
                MNS_Games_Helper::set_blackjack_state( $state, $user_id );
                break;

            case 'stand':
                if ( $state['turn'] !== 'player' ) {
                    break;
                }

                $state['turn'] = 'dealer';

                $dealer_value = MNS_Games_Helper::calculate_blackjack_hand_value( $state['dealer_hand'] );
                while ( $dealer_value['value'] < 17 || ( $dealer_value['value'] === 17 && $dealer_value['is_soft'] ) ) {
                    $state['dealer_hand'][] = MNS_Games_Helper::draw_card_from_deck( $deck );
                    $dealer_value           = MNS_Games_Helper::calculate_blackjack_hand_value( $state['dealer_hand'] );
                }

                $player_value = MNS_Games_Helper::calculate_blackjack_hand_value( $state['player_hand'] );
                $result       = MNS_Games_Helper::evaluate_blackjack_result( $player_value, $dealer_value );

                $bet = isset( $state['bet'] ) ? intval( $state['bet'] ) : 0;

                if ( $bet > 0 ) {
                    if ( $result === 'blackjack' ) {
                        $payout = (int) round( $bet * 2.5 );
                        MNS_Tokens_Helper::add_tokens( $user_id, $payout, 'blackjack_win' );
                        self::$blackjack_message = __( 'Blackjack. Cobras 3:2.', 'casino-games-mns' );
                    } elseif ( $result === 'win' ) {
                        $payout = $bet * 2;
                        MNS_Tokens_Helper::add_tokens( $user_id, $payout, 'blackjack_win' );
                        self::$blackjack_message = __( 'Has ganado la mano.', 'casino-games-mns' );
                    } elseif ( $result === 'push' ) {
                        MNS_Tokens_Helper::add_tokens( $user_id, $bet, 'blackjack_push' );
                        self::$blackjack_message = __( 'Empate. Recuperas tu apuesta.', 'casino-games-mns' );
                    } else {
                        self::$blackjack_message = __( 'Has perdido la mano.', 'casino-games-mns' );
                    }
                }

                $state['turn'] = 'finished';
                $state['deck'] = $deck;
                MNS_Games_Helper::set_blackjack_state( $state, $user_id );
                break;

            case 'double':
                if ( $state['turn'] !== 'player' ) {
                    break;
                }

                $bet    = isset( $state['bet'] ) ? intval( $state['bet'] ) : 0;
                $tokens = MNS_Tokens_Helper::get_tokens( $user_id );

                if ( $bet <= 0 || $tokens < $bet ) {
                    self::$blackjack_message = __( 'No puedes doblar la apuesta.', 'casino-games-mns' );
                    break;
                }

                $ok = MNS_Tokens_Helper::subtract_tokens( $user_id, $bet, 'blackjack_bet' );
                if ( ! $ok ) {
                    self::$blackjack_message = __( 'No ha sido posible doblar la apuesta.', 'casino-games-mns' );
                    break;
                }

                $state['bet'] = $bet * 2;

                $state['player_hand'][] = MNS_Games_Helper::draw_card_from_deck( $deck );
                $player_value           = MNS_Games_Helper::calculate_blackjack_hand_value( $state['player_hand'] );

                if ( $player_value['is_bust'] ) {
                    $state['turn'] = 'finished';
                    $state['deck'] = $deck;
                    MNS_Games_Helper::set_blackjack_state( $state, $user_id );
                    self::$blackjack_message = __( 'Te has pasado tras doblar. Has perdido.', 'casino-games-mns' );
                    return;
                }

                $state['turn'] = 'dealer';
                $dealer_value  = MNS_Games_Helper::calculate_blackjack_hand_value( $state['dealer_hand'] );

                while ( $dealer_value['value'] < 17 || ( $dealer_value['value'] === 17 && $dealer_value['is_soft'] ) ) {
                    $state['dealer_hand'][] = MNS_Games_Helper::draw_card_from_deck( $deck );
                    $dealer_value           = MNS_Games_Helper::calculate_blackjack_hand_value( $state['dealer_hand'] );
                }

                $result = MNS_Games_Helper::evaluate_blackjack_result( $player_value, $dealer_value );
                $bet    = $state['bet'];

                if ( $bet > 0 ) {
                    if ( $result === 'blackjack' ) {
                        $payout = (int) round( $bet * 2.5 );
                        MNS_Tokens_Helper::add_tokens( $user_id, $payout, 'blackjack_win' );
                        self::$blackjack_message = __( 'Blackjack (doble).', 'casino-games-mns' );
                    } elseif ( $result === 'win' ) {
                        $payout = $bet * 2;
                        MNS_Tokens_Helper::add_tokens( $user_id, $payout, 'blackjack_win' );
                        self::$blackjack_message = __( 'Has ganado tras doblar.', 'casino-games-mns' );
                    } elseif ( $result === 'push' ) {
                        MNS_Tokens_Helper::add_tokens( $user_id, $bet, 'blackjack_push' );
                        self::$blackjack_message = __( 'Empate tras doblar. Recuperas tu apuesta.', 'casino-games-mns' );
                    } else {
                        self::$blackjack_message = __( 'Has perdido tras doblar.', 'casino-games-mns' );
                    }
                }

                $state['turn'] = 'finished';
                $state['deck'] = $deck;
                MNS_Games_Helper::set_blackjack_state( $state, $user_id );
                break;

            case 'split':
                self::$blackjack_message = __( 'La opción de Split está marcada pero su lógica avanzada está pendiente de ampliar.', 'casino-games-mns' );
                break;
        }
    }

    public static function shortcode_blackjack( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div class="mns-game mns-game-blackjack"><p>' .
                esc_html__( 'Debes iniciar sesión para jugar.', 'casino-games-mns' ) .
                '</p></div>';
        }

        if ( ! class_exists( 'MNS_Tokens_Helper' ) ) {
            return '<div class="mns-game mns-game-blackjack"><p>' .
                esc_html__( 'Sistema de fichas no disponible.', 'casino-games-mns' ) .
                '</p></div>';
        }

        $user_id = get_current_user_id();
        $tokens  = MNS_Tokens_Helper::get_tokens( $user_id );
        $state   = MNS_Games_Helper::get_blackjack_state( $user_id );

        ob_start();
        ?>
        <div class="mns-game mns-game-blackjack">
            <div class="mns-game-balance">
                <?php echo esc_html__( 'Tus fichas:', 'casino-games-mns' ) . ' ' . intval( $tokens ); ?>
            </div>

            <?php if ( ! empty( self::$blackjack_message ) ) : ?>
                <div class="mns-game-messages">
                    <?php echo esc_html( self::$blackjack_message ); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="mns-blackjack-form">
                <?php wp_nonce_field( 'mns_blackjack_action', 'mns_bj_nonce' ); ?>

                <div class="mns-bj-bet-area">
                    <label>
                        <?php esc_html_e( 'Apuesta:', 'casino-games-mns' ); ?>
                        <input type="number" name="mns_bj_bet" class="bj-bet-input" min="1" step="1" value="<?php echo isset( $state['bet'] ) ? intval( $state['bet'] ) : ''; ?>">
                    </label>
                    <div class="mns-bj-bet-quick">
                        <button type="button" class="bj-bet-quick" data-bet="100">100</button>
                        <button type="button" class="bj-bet-quick" data-bet="500">500</button>
                        <button type="button" class="bj-bet-quick" data-bet="1000">1000</button>
                        <button type="button" class="bj-bet-quick" data-bet="max"><?php esc_html_e( 'Máximo', 'casino-games-mns' ); ?></button>
                    </div>
                </div>

                <div class="mns-bj-hands">
                    <div class="bj-player-hand">
                        <h4><?php esc_html_e( 'Jugador', 'casino-games-mns' ); ?></h4>
                        <div class="bj-cards">
                            <?php
                            if ( ! empty( $state['player_hand'] ) && is_array( $state['player_hand'] ) ) {
                                foreach ( $state['player_hand'] as $card ) {
                                    $rank = preg_replace( '/[HDCS]$/', '', $card );
                                    $suit = substr( $card, -1 );
                                    $unicode_suit = array(
                                        'S' => '♠',
                                        'H' => '♥',
                                        'D' => '♦',
                                        'C' => '♣',
                                    );
                                    $unicode = isset( $unicode_suit[ $suit ] ) ? $unicode_suit[ $suit ] : '';
                                    $is_red  = in_array( $suit, array( 'H', 'D' ), true );
                                    echo '<span class="bj-card ' . ( $is_red ? 'red' : '' ) . '" data-rank="' . esc_attr( $rank ) . '" data-suit="' . esc_attr( $unicode ) . '"></span>';
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <div class="bj-dealer-hand">
                        <h4><?php esc_html_e( 'Banca', 'casino-games-mns' ); ?></h4>
                        <div class="bj-cards">
                            <?php
                            if ( ! empty( $state['dealer_hand'] ) && is_array( $state['dealer_hand'] ) ) {
                                $turn = isset( $state['turn'] ) ? $state['turn'] : 'finished';

                                $first = $state['dealer_hand'][0];
                                $rank1 = preg_replace( '/[HDCS]$/', '', $first );
                                $suit1 = substr( $first, -1 );
                                $unicode_map = array(
                                    'S' => '♠',
                                    'H' => '♥',
                                    'D' => '♦',
                                    'C' => '♣',
                                );
                                $unicode1 = isset( $unicode_map[ $suit1 ] ) ? $unicode_map[ $suit1 ] : '';
                                $is_red1  = in_array( $suit1, array( 'H', 'D' ), true );

                                echo '<span class="bj-card ' . ( $is_red1 ? 'red' : '' ) . '" data-rank="' . esc_attr( $rank1 ) . '" data-suit="' . esc_attr( $unicode1 ) . '"></span>';

                                if ( $turn === 'player' && count( $state['dealer_hand'] ) > 1 ) {
                                    echo '<span class="bj-card back"></span>';
                                }

                                if ( $turn !== 'player' ) {
                                    for ( $i = 1; $i < count( $state['dealer_hand'] ); $i++ ) {
                                        $card   = $state['dealer_hand'][ $i ];
                                        $rank   = preg_replace( '/[HDCS]$/', '', $card );
                                        $suit   = substr( $card, -1 );
                                        $unicode = isset( $unicode_map[ $suit ] ) ? $unicode_map[ $suit ] : '';
                                        $is_red = in_array( $suit, array( 'H', 'D' ), true );
                                        echo '<span class="bj-card ' . ( $is_red ? 'red' : '' ) . '" data-rank="' . esc_attr( $rank ) . '" data-suit="' . esc_attr( $unicode ) . '"></span>';
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="mns-bj-actions">
                    <button type="submit" name="mns_bj_action" value="start" class="bj-action bj-action-start">
                        <?php esc_html_e( 'Repartir / Nueva mano', 'casino-games-mns' ); ?>
                    </button>

                    <?php
                    $turn      = isset( $state['turn'] ) ? $state['turn'] : 'finished';
                    $is_active = ( 'player' === $turn );
                    $disabled  = $is_active ? '' : ' bj-action--disabled';
                    ?>

                    <button type="submit" name="mns_bj_action" value="hit" class="bj-action bj-action-hit<?php echo esc_attr( $disabled ); ?>" <?php echo $is_active ? '' : 'disabled'; ?>>
                        <?php esc_html_e( 'Pedir (Hit)', 'casino-games-mns' ); ?>
                    </button>

                    <button type="submit" name="mns_bj_action" value="stand" class="bj-action bj-action-stand<?php echo esc_attr( $disabled ); ?>" <?php echo $is_active ? '' : 'disabled'; ?>>
                        <?php esc_html_e( 'Plantarse (Stand)', 'casino-games-mns' ); ?>
                    </button>

                    <?php
                    $can_double   = $is_active && ! empty( $state );
                    $double_class = $can_double ? '' : ' bj-action--disabled';
                    ?>
                    <button type="submit" name="mns_bj_action" value="double" class="bj-action bj-action-double<?php echo esc_attr( $double_class ); ?>" <?php echo $can_double ? '' : 'disabled'; ?>>
                        <?php esc_html_e( 'Doblar (Double)', 'casino-games-mns' ); ?>
                    </button>

                    <?php
                    $can_split = false;
                    if ( ! empty( $state['player_hand'] ) && count( $state['player_hand'] ) === 2 ) {
                        $c1 = preg_replace( '/[HDCS]$/', '', $state['player_hand'][0] );
                        $c2 = preg_replace( '/[HDCS]$/', '', $state['player_hand'][1] );
                        if ( $c1 === $c2 ) {
                            $can_split = true;
                        }
                    }
                    $split_class = ( $is_active && $can_split ) ? '' : ' bj-action--disabled';
                    ?>
                    <button type="submit" name="mns_bj_action" value="split" class="bj-action bj-action-split<?php echo esc_attr( $split_class ); ?>" <?php echo ( $is_active && $can_split ) ? '' : 'disabled'; ?>>
                        <?php esc_html_e( 'Dividir (Split)', 'casino-games-mns' ); ?>
                    </button>
                </div>
            </form>

            <script>
                (function(){
                    var form  = document.querySelector('.mns-blackjack-form');
                    if (!form) return;

                    var input   = form.querySelector('.bj-bet-input');
                    var buttons = form.querySelectorAll('.bj-bet-quick');
                    var balance = <?php echo intval( $tokens ); ?>;

                    buttons.forEach(function(btn){
                        btn.addEventListener('click', function(e){
                            e.preventDefault();
                            var val = this.getAttribute('data-bet');
                            if (val === 'max') {
                                input.value = balance > 0 ? balance : 0;
                            } else {
                                input.value = parseInt(val, 10) || 0;
                            }
                        });
                    });
                })();
            </script>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function handle_roulette_actions() {
        if ( ! isset( $_POST['mns_rl_action'] ) ) {
            return;
        }

        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! isset( $_POST['mns_rl_nonce'] ) || ! wp_verify_nonce( $_POST['mns_rl_nonce'], 'mns_roulette_action' ) ) {
            return;
        }

        if ( ! class_exists( 'MNS_Tokens_Helper' ) ) {
            self::$roulette_message = __( 'Sistema de fichas no disponible.', 'casino-games-mns' );
            return;
        }

        $user_id = get_current_user_id();
        $action  = sanitize_text_field( wp_unslash( $_POST['mns_rl_action'] ) );

        if ( $action !== 'spin' ) {
            return;
        }

        $bets_raw = isset( $_POST['mns_rl_bets'] ) ? wp_unslash( $_POST['mns_rl_bets'] ) : '';
        $bets     = array();

        if ( ! empty( $bets_raw ) ) {
            $decoded = json_decode( $bets_raw, true );
            if ( is_array( $decoded ) ) {
                $bets = $decoded;
            }
        }

        if ( empty( $bets ) ) {
            self::$roulette_message = __( 'No has realizado ninguna apuesta.', 'casino-games-mns' );
            return;
        }

        $total_bet = 0;
        foreach ( $bets as $bet ) {
            $amount = isset( $bet['amount'] ) ? intval( $bet['amount'] ) : 0;
            if ( $amount > 0 ) {
                $total_bet += $amount;
            }
        }

        if ( $total_bet <= 0 ) {
            self::$roulette_message = __( 'No has realizado ninguna apuesta válida.', 'casino-games-mns' );
            return;
        }

        $tokens = MNS_Tokens_Helper::get_tokens( $user_id );
        if ( $tokens < $total_bet ) {
            self::$roulette_message = __( 'No tienes fichas suficientes para esas apuestas.', 'casino-games-mns' );
            return;
        }

        $ok = MNS_Tokens_Helper::subtract_tokens( $user_id, $total_bet, 'roulette_bet' );
        if ( ! $ok ) {
            self::$roulette_message = __( 'No se ha podido registrar la apuesta.', 'casino-games-mns' );
            return;
        }

        $number = isset( $_POST['mns_rl_winning'] ) ? intval( $_POST['mns_rl_winning'] ) : -1;

        if ( $number < 0 || $number > 36 ) {
            self::$roulette_message = __( 'Ha ocurrido un error con el número ganador de la ruleta.', 'casino-games-mns' );
            return;
        }

        $total_win = MNS_Games_Helper::evaluate_roulette_bets( $bets, $number );
        if ( $total_win > 0 ) {
            MNS_Tokens_Helper::add_tokens( $user_id, $total_win, 'roulette_win' );
        }

        $color  = MNS_Games_Helper::get_roulette_color( $number );
        $result = sprintf(
            __( 'Número: %1$d (%2$s). Has apostado %3$d fichas y ganado %4$d.', 'casino-games-mns' ),
            $number,
            $color,
            $total_bet,
            $total_win
        );

        self::$roulette_message = $result;

        $GLOBALS['mns_rl_last_number'] = $number;
        $GLOBALS['mns_rl_last_color']  = $color;
    }

    public static function shortcode_roulette( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div class="mns-game mns-game-roulette"><p>' .
                esc_html__( 'Debes iniciar sesión para jugar.', 'casino-games-mns' ) .
                '</p></div>';
        }

        if ( ! class_exists( 'MNS_Tokens_Helper' ) ) {
            return '<div class="mns-game mns-game-roulette"><p>' .
                esc_html__( 'Sistema de fichas no disponible.', 'casino-games-mns' ) .
                '</p></div>';
        }

        $user_id = get_current_user_id();
        $tokens  = MNS_Tokens_Helper::get_tokens( $user_id );

        $last_number = isset( $GLOBALS['mns_rl_last_number'] ) ? intval( $GLOBALS['mns_rl_last_number'] ) : null;
        $last_color  = isset( $GLOBALS['mns_rl_last_color'] ) ? sanitize_text_field( $GLOBALS['mns_rl_last_color'] ) : null;

        ob_start();
        ?>
        <div class="mns-game mns-game-roulette">
            <div class="mns-game-balance">
                <?php echo esc_html__( 'Tus fichas:', 'casino-games-mns' ) . ' ' . intval( $tokens ); ?>
            </div>

            <?php if ( ! empty( self::$roulette_message ) ) : ?>
                <div class="mns-game-messages">
                    <?php echo esc_html( self::$roulette_message ); ?>
                </div>
            <?php endif; ?>

            <?php if ( $last_number !== null ) : ?>
                <div class="rl-last-result">
                    <?php
                    echo esc_html__( 'Último resultado:', 'casino-games-mns' ) . ' ' . intval( $last_number ) . ' (' . esc_html( $last_color ) . ')';
                    ?>
                </div>
            <?php endif; ?>

            <form method="post" class="mns-roulette-form">
                <?php wp_nonce_field( 'mns_roulette_action', 'mns_rl_nonce' ); ?>
                <input type="hidden" name="mns_rl_action" value="spin">
                <input type="hidden" name="mns_rl_bets" class="mns-rl-bets-field" value="[]">
                <input type="hidden" name="mns_rl_winning" class="mns-rl-winning-field" value="">

                <div class="mns-rl-chip-selector">
                    <span><?php esc_html_e( 'Selecciona ficha:', 'casino-games-mns' ); ?></span>
                    <button type="button" class="rl-chip" data-chip="10">10</button>
                    <button type="button" class="rl-chip" data-chip="50">50</button>
                    <button type="button" class="rl-chip" data-chip="100">100</button>
                    <button type="button" class="rl-chip" data-chip="500">500</button>
                    <button type="button" class="rl-chip" data-chip="1000">1000</button>
                </div>

                <div class="mns-rl-table">
                    <div class="rl-wheel">
                        <div class="rl-wheel-inner" id="mns-roulette-wheel"></div>
                        <div class="rl-ball" id="mns-roulette-ball"></div>
                    </div>

                    <div class="rl-grid">
                        <div class="rl-row rl-row-zero">
                            <?php
                            $color_zero = MNS_Games_Helper::get_roulette_color( 0 );
                            $zero_class = 'rl-cell-number';
                            if ( $color_zero === 'red' || $color_zero === 'rojo' ) {
                                $zero_class .= ' rl-color-red';
                            } elseif ( $color_zero === 'black' || $color_zero === 'negro' ) {
                                $zero_class .= ' rl-color-black';
                            } else {
                                $zero_class .= ' rl-color-green';
                            }
                            ?>
                            <button type="button"
                                    class="<?php echo esc_attr( $zero_class ); ?>"
                                    data-type="straight"
                                    data-value="0">
                                0
                            </button>
                        </div>

                        <div class="rl-row rl-row-numbers-horizontal">
                            <?php for ( $row = 0; $row < 3; $row++ ) : ?>
                                <div class="rl-row-numbers-row">
                                    <?php
                                    for ( $col = 0; $col < 12; $col++ ) :
                                        $num   = ( $row * 12 ) + $col + 1;
                                        $color = MNS_Games_Helper::get_roulette_color( $num );
                                        $classes = 'rl-cell-number';

                                        if ( $color === 'red' || $color === 'rojo' ) {
                                            $classes .= ' rl-color-red';
                                        } elseif ( $color === 'black' || $color === 'negro' ) {
                                            $classes .= ' rl-color-black';
                                        } elseif ( $color === 'green' || $color === 'verde' ) {
                                            $classes .= ' rl-color-green';
                                        }
                                        ?>
                                        <button type="button"
                                                class="<?php echo esc_attr( $classes ); ?>"
                                                data-type="straight"
                                                data-value="<?php echo intval( $num ); ?>">
                                            <?php echo intval( $num ); ?>
                                        </button>
                                    <?php endfor; ?>
                                </div>
                            <?php endfor; ?>
                        </div>

                        <div class="rl-row rl-row-external">
                            <button type="button" class="rl-cell-external rl-cell-red" data-type="color" data-value="red">
                                <?php esc_html_e( 'Rojo', 'casino-games-mns' ); ?>
                            </button>
                            <button type="button" class="rl-cell-external rl-cell-black" data-type="color" data-value="black">
                                <?php esc_html_e( 'Negro', 'casino-games-mns' ); ?>
                            </button>
                            <button type="button" class="rl-cell-external rl-cell-even" data-type="even_odd" data-value="even">
                                <?php esc_html_e( 'Par', 'casino-games-mns' ); ?>
                            </button>
                            <button type="button" class="rl-cell-external rl-cell-odd" data-type="even_odd" data-value="odd">
                                <?php esc_html_e( 'Impar', 'casino-games-mns' ); ?>
                            </button>
                            <button type="button" class="rl-cell-external rl-cell-low" data-type="low_high" data-value="low">
                                <?php esc_html_e( '1-18', 'casino-games-mns' ); ?>
                            </button>
                            <button type="button" class="rl-cell-external rl-cell-high" data-type="low_high" data-value="high">
                                <?php esc_html_e( '19-36', 'casino-games-mns' ); ?>
                            </button>
                        </div>

                        <div class="rl-row rl-row-dozens">
                            <button type="button" class="rl-cell-external rl-cell-dozen" data-type="dozen" data-value="1">
                                <?php esc_html_e( '1ª Docena (1-12)', 'casino-games-mns' ); ?>
                            </button>
                            <button type="button" class="rl-cell-external rl-cell-dozen" data-type="dozen" data-value="2">
                                <?php esc_html_e( '2ª Docena (13-24)', 'casino-games-mns' ); ?>
                            </button>
                            <button type="button" class="rl-cell-external rl-cell-dozen" data-type="dozen" data-value="3">
                                <?php esc_html_e( '3ª Docena (25-36)', 'casino-games-mns' ); ?>
                            </button>
                        </div>

                        <div class="rl-row rl-row-columns">
                            <button type="button" class="rl-cell-external rl-cell-column" data-type="column" data-value="1">
                                <?php esc_html_e( 'Columna 1', 'casino-games-mns' ); ?>
                            </button>
                            <button type="button" class="rl-cell-external rl-cell-column" data-type="column" data-value="2">
                                <?php esc_html_e( 'Columna 2', 'casino-games-mns' ); ?>
                            </button>
                            <button type="button" class="rl-cell-external rl-cell-column" data-type="column" data-value="3">
                                <?php esc_html_e( 'Columna 3', 'casino-games-mns' ); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="rl-current-bets">
                    <h4><?php esc_html_e( 'Apuestas actuales', 'casino-games-mns' ); ?></h4>
                    <ul class="rl-bets-list"></ul>
                </div>

                <div class="mns-rl-actions">
                    <button type="submit" class="rl-spin-button">
                        <?php esc_html_e( 'Girar ruleta', 'casino-games-mns' ); ?>
                    </button>
                </div>
            </form>

            <script>
                (function(){
                    var form      = document.querySelector('.mns-roulette-form');
                    if (!form) return;

                    var chipButtons  = form.querySelectorAll('.rl-chip');
                    var currentChip  = null;
                    var betsField    = form.querySelector('.mns-rl-bets-field');
                    var winningField = form.querySelector('.mns-rl-winning-field');
                    var betsList     = form.querySelector('.rl-bets-list');
                    var balance      = <?php echo intval( $tokens ); ?>;
                    var bets         = [];

                    chipButtons.forEach(function(btn){
                        btn.addEventListener('click', function(e){
                            e.preventDefault();
                            chipButtons.forEach(function(b){ b.classList.remove('rl-chip--active'); });
                            this.classList.add('rl-chip--active');
                            currentChip = parseInt(this.getAttribute('data-chip'), 10) || 0;
                        });
                    });

                    form.querySelectorAll('.rl-cell-number, .rl-cell-external').forEach(function(cell){
                        cell.addEventListener('click', function(e){
                            e.preventDefault();
                            if (!currentChip || currentChip <= 0) {
                                return;
                            }

                            var type  = this.getAttribute('data-type');
                            var value = this.getAttribute('data-value');

                            var totalBet = bets.reduce(function(acc, b){
                                return acc + (parseInt(b.amount,10) || 0);
                            }, 0);

                            if (totalBet + currentChip > balance) {
                                return;
                            }

                            var bet = {
                                type: type,
                                value: value,
                                amount: currentChip
                            };
                            bets.push(bet);

                            betsField.value = JSON.stringify(bets);

                            var li = document.createElement('li');
                            li.className = 'rl-bet-chip';
                            li.textContent = type + ' - ' + value + ' : ' + currentChip;
                            betsList.appendChild(li);
                        });
                    });

                    form.addEventListener('submit', function(e){
                        e.preventDefault();

                        if (!bets.length) {
                            return;
                        }

                        var wheelInner = form.querySelector('#mns-roulette-wheel');
                        var ball       = form.querySelector('#mns-roulette-ball');
                        var spinButton = form.querySelector('.rl-spin-button');

                        if (spinButton) {
                            spinButton.disabled = true;
                        }

                        if (!window.MNSRoulette || !wheelInner || !winningField) {
                            form.submit();
                            return;
                        }

                        window.MNSRoulette.spin({
                            wheelEl: wheelInner,
                            ballEl: ball,
                            duration: 4000,
                            callback: function(winningNumber) {
                                winningField.value = winningNumber;
                                form.submit();
                            }
                        });
                    });
                })();
            </script>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function handle_russian_roulette_actions() {
        if ( ! isset( $_POST['mns_rr_action'] ) ) {
            return;
        }

        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! isset( $_POST['mns_rr_nonce'] ) || ! wp_verify_nonce( $_POST['mns_rr_nonce'], 'mns_russian_roulette_action' ) ) {
            return;
        }

        if ( ! class_exists( 'MNS_Tokens_Helper' ) ) {
            self::$russian_message = __( 'Sistema de fichas no disponible.', 'casino-games-mns' );
            return;
        }

        $user_id = get_current_user_id();

        if ( method_exists( 'MNS_Games_Helper', 'is_admin_protected' ) && MNS_Games_Helper::is_admin_protected( $user_id ) ) {
            self::$russian_message = __( 'Los administradores no pueden jugar a la ruleta rusa.', 'casino-games-mns' );
            return;
        }

        $action = sanitize_text_field( wp_unslash( $_POST['mns_rr_action'] ) );

        if ( $action === 'start_game' ) {
            $choice = isset( $_POST['choice'] ) ? sanitize_text_field( wp_unslash( $_POST['choice'] ) ) : '';
            if ( $choice !== 'heads' && $choice !== 'tails' ) {
                self::$russian_message = __( 'Debes elegir cara o cruz.', 'casino-games-mns' );
                return;
            }

            $coin_result = ( wp_rand( 0, 1 ) === 0 ) ? 'heads' : 'tails';
            $starts      = ( $choice === $coin_result ) ? 'player' : 'paco';

            $start = MNS_Games_Helper::start_rr_game( $user_id, $starts );
            if ( ! empty( $start['error'] ) ) {
                self::$russian_message = isset( $start['message'] ) ? $start['message'] : __( 'No se ha podido iniciar la partida.', 'casino-games-mns' );
                return;
            }

            $txt_choice = ( $choice === 'heads' ) ? __( 'Cara', 'casino-games-mns' ) : __( 'Cruz', 'casino-games-mns' );
            $txt_result = ( $coin_result === 'heads' ) ? __( 'Cara', 'casino-games-mns' ) : __( 'Cruz', 'casino-games-mns' );

            if ( $starts === 'player' ) {
                self::$russian_message = sprintf(
                    __( 'Has elegido %1$s y ha salido %2$s. Empiezas tú. %3$s', 'casino-games-mns' ),
                    $txt_choice,
                    $txt_result,
                    isset( $start['message'] ) ? $start['message'] : ''
                );
            } else {
                self::$russian_message = sprintf(
                    __( 'Has elegido %1$s y ha salido %2$s. Empieza Paco %3$d. %4$s', 'casino-games-mns' ),
                    $txt_choice,
                    $txt_result,
                    intval( MNS_Games_Helper::get_paco_level() ),
                    isset( $start['message'] ) ? $start['message'] : ''
                );
            }

            $GLOBALS['mns_rr_last_result'] = array(
                'outcome'      => 'start',
                'coin_choice'  => $choice,
                'coin_result'  => $coin_result,
                'turn'         => $starts,
            );

            return;
        }

        if ( $action === 'player_shoot' || $action === 'paco_shoot' ) {
            $shooter = ( $action === 'player_shoot' ) ? 'player' : 'paco';

            $result = MNS_Games_Helper::resolve_rr_shot( $user_id, $shooter );

            if ( isset( $result['error'] ) && $result['error'] ) {
                self::$russian_message = isset( $result['message'] ) ? $result['message'] : __( 'Ha ocurrido un error en la partida.', 'casino-games-mns' );
                return;
            }

            self::$russian_message = isset( $result['message'] ) ? $result['message'] : '';

            $outcome = array(
                'outcome' => '',
            );

            if ( isset( $result['fatal'] ) && $result['fatal'] ) {
                if ( isset( $result['dead'] ) && $result['dead'] === 'player' ) {
                    $outcome['outcome'] = 'player_dead';
                } elseif ( isset( $result['dead'] ) && $result['dead'] === 'paco' ) {
                    $outcome['outcome'] = 'paco_dead';
                }
            } else {
                $outcome['outcome']    = 'safe';
                $outcome['next_turn']  = isset( $result['next_turn'] ) ? $result['next_turn'] : '';
            }

            $GLOBALS['mns_rr_last_result'] = $outcome;
            return;
        }
    }

    public static function shortcode_russian_roulette( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div class="mns-game mns-game-russian-roulette"><p>' .
                esc_html__( 'Debes iniciar sesión para jugar.', 'casino-games-mns' ) .
                '</p></div>';
        }

        $user_id = get_current_user_id();

        if ( method_exists( 'MNS_Games_Helper', 'is_admin_protected' ) && MNS_Games_Helper::is_admin_protected( $user_id ) ) {
            return '<div class="mns-game mns-game-russian-roulette"><p>' .
                esc_html__( 'Los administradores no pueden jugar a la ruleta rusa.', 'casino-games-mns' ) .
                '</p></div>';
        }

        if ( ! class_exists( 'MNS_Tokens_Helper' ) ) {
            return '<div class="mns-game mns-game-russian-roulette"><p>' .
                esc_html__( 'Sistema de fichas no disponible.', 'casino-games-mns' ) .
                '</p></div>';
        }

        $tokens    = MNS_Tokens_Helper::get_tokens( $user_id );
        $rr_state  = MNS_Games_Helper::get_rr_state( $user_id );
        $rr_turn   = isset( $rr_state['turn'] ) ? $rr_state['turn'] : '';
        $rr_finished = ! empty( $rr_state['finished'] );
        $last_result = isset( $GLOBALS['mns_rr_last_result'] ) && is_array( $GLOBALS['mns_rr_last_result'] ) ? $GLOBALS['mns_rr_last_result'] : array();

        ob_start();
        ?>
<div class="mns-game mns-game-russian-roulette">

    <div class="mns-game-balance">
        Tus fichas: <?php echo intval( $tokens ); ?>
    </div>

    <?php if ( ! empty( self::$russian_message ) ) : ?>
        <div class="mns-game-messages">
            <?php echo esc_html( self::$russian_message ); ?>
        </div>
    <?php endif; ?>

    <div class="rr-paco-name">
        Paco <?php echo intval( MNS_Games_Helper::get_paco_level() ); ?>
    </div>

    <div class="rr-choice-box">
        <button type="button" class="rr-choice-btn" data-choice="heads">Cara</button>
        <button type="button" class="rr-choice-btn" data-choice="tails">Cruz</button>
    </div>

    <div class="rr-coin" id="rr-coin">
        <div class="rr-coin-inner">
            <div class="rr-coin-face-heads">Cara</div>
            <div class="rr-coin-face-tails">Cruz</div>
        </div>
    </div>

    <div class="rr-turn-indicator" id="rr-turn-indicator"></div>

    <div class="rr-chamber" id="rr-chamber"></div>

    <div class="rr-countdown" id="rr-countdown"></div>

    <div class="rr-bang" id="rr-bang">BANG</div>
    <div class="rr-empty" id="rr-empty">CLICK</div>

    <div class="rr-buttons">
        <button type="button" class="rr-btn rr-btn-red" id="rr-btn-shoot-me" disabled>Dispararme</button>
        <button type="button" class="rr-btn rr-btn-green" id="rr-btn-shoot-paco" disabled>Disparar a Paco</button>
    </div>

    <form method="post" id="rr-form">
        <?php wp_nonce_field( 'mns_russian_roulette_action', 'mns_rr_nonce' ); ?>
        <input type="hidden" name="mns_rr_action" value="">
        <input type="hidden" name="choice" id="rr-choice-field" value="">
        <input type="hidden" name="turn_action" id="rr-turn-action" value="">
    </form>

</div>

<script>
(function(){
    var containers = document.querySelectorAll('.mns-game-russian-roulette');
    if (!containers.length) return;
    var container = containers[containers.length - 1];

    var state = <?php echo wp_json_encode( array(
        'turn'        => $rr_turn,
        'finished'    => $rr_finished,
        'last_result' => $last_result,
    ) ); ?>;

    var form         = container.querySelector('#rr-form');
    var actionField  = form ? form.querySelector('input[name="mns_rr_action"]') : null;
    var choiceField  = form ? form.querySelector('#rr-choice-field') : null;
    var turnField    = form ? form.querySelector('#rr-turn-action') : null;
    var choiceBtns   = container.querySelectorAll('.rr-choice-btn');
    var shootMeBtn   = container.querySelector('#rr-btn-shoot-me');
    var shootPacoBtn = container.querySelector('#rr-btn-shoot-paco');
    var turnIndicator= container.querySelector('#rr-turn-indicator');
    var chamberEl    = container.querySelector('#rr-chamber');
    var countdownEl  = container.querySelector('#rr-countdown');
    var bangEl       = container.querySelector('#rr-bang');
    var emptyEl      = container.querySelector('#rr-empty');
    var coinEl       = container.querySelector('#rr-coin');

    if (turnIndicator) {
        if (state.turn === 'player') {
            turnIndicator.textContent = 'Tu turno';
        } else if (state.turn === 'paco') {
            turnIndicator.textContent = 'Turno de Paco';
        } else {
            turnIndicator.textContent = 'Selecciona Cara o Cruz para empezar';
        }
    }

    if (shootMeBtn && shootPacoBtn) {
        shootMeBtn.disabled   = (state.turn !== 'player') || state.finished;
        shootPacoBtn.disabled = (state.turn !== 'paco') || state.finished;
    }

    choiceBtns.forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            choiceBtns.forEach(function(b){ b.classList.remove('rr-choice-btn-active'); });
            this.classList.add('rr-choice-btn-active');
            var choice = this.getAttribute('data-choice');
            if (choiceField) choiceField.value = choice;

            if (window.MNSRussianRoulette && typeof window.MNSRussianRoulette.flipCoin === 'function' && coinEl) {
                window.MNSRussianRoulette.flipCoin({
                    element: coinEl,
                    choice: choice,
                    callback: function(){
                        if (actionField) actionField.value = 'start_game';
                        if (form) form.submit();
                    }
                });
            } else {
                if (actionField) actionField.value = 'start_game';
                if (form) form.submit();
            }
        });
    });

    if (shootMeBtn) {
        shootMeBtn.addEventListener('click', function(e){
            e.preventDefault();
            if (!form || !actionField) return;
            actionField.value = 'player_shoot';
            if (turnField) turnField.value = 'player';
            if (form) form.submit();
        });
    }

    if (shootPacoBtn) {
        shootPacoBtn.addEventListener('click', function(e){
            e.preventDefault();
            if (!form || !actionField) return;
            actionField.value = 'paco_shoot';
            if (turnField) turnField.value = 'paco';
            if (form) form.submit();
        });
    }

    if (state.last_result && state.last_result.outcome) {
        var outcome = state.last_result.outcome;

        var showResult = function(){
            if (outcome === 'player_dead' || outcome === 'paco_dead') {
                if (bangEl) {
                    bangEl.classList.add('rr-bang-show');
                    setTimeout(function(){
                        bangEl.classList.remove('rr-bang-show');
                    },1500);
                }
            } else if (outcome === 'safe') {
                if (emptyEl) {
                    emptyEl.classList.add('rr-empty-show');
                    setTimeout(function(){
                        emptyEl.classList.remove('rr-empty-show');
                    },1000);
                }
            }
        };

        if (window.MNSRussianRoulette &&
            typeof window.MNSRussianRoulette.spinChamber === 'function' &&
            typeof window.MNSRussianRoulette.countdown === 'function' &&
            chamberEl && countdownEl) {

            window.MNSRussianRoulette.spinChamber({
                element: chamberEl,
                callback: function(){
                    window.MNSRussianRoulette.countdown({
                        element: countdownEl,
                        callback: showResult
                    });
                }
            });
        } else {
            showResult();
        }
    }
})();
</script>
        <?php
        return ob_get_clean();
    }
}
