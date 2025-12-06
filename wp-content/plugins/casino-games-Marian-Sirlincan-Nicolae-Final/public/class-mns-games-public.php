<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNS_Games_Public {

    /**
     * Mensajes por juego
     */
    public static $blackjack_message = '';
    public static $roulette_message  = '';
    public static $russian_message   = '';

    /**
     * Manejar acciones de Blackjack (POST)
     */
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

        // Iniciar nueva partida
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

            // Descontar apuesta inicial
            $ok = MNS_Tokens_Helper::subtract_tokens( $user_id, $bet, 'blackjack_bet' );
            if ( ! $ok ) {
                self::$blackjack_message = __( 'No ha sido posible registrar la apuesta.', 'casino-games-mns' );
                return;
            }

            // Preparar mazo y repartir
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

        // Si no hay estado, no hay partida en curso
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
                    // Pierde directamente
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
                // Turno de la banca
                $state['turn'] = 'dealer';

                // Jugar banca hasta soft 17
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
                        // lose: nada que devolver, ya se pagó la apuesta al inicio.
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

                // Cobrar segunda parte de la apuesta
                $ok = MNS_Tokens_Helper::subtract_tokens( $user_id, $bet, 'blackjack_bet' );
                if ( ! $ok ) {
                    self::$blackjack_message = __( 'No ha sido posible doblar la apuesta.', 'casino-games-mns' );
                    break;
                }

                $state['bet'] = $bet * 2;

                // Roba una carta y pasa a banca
                $state['player_hand'][] = MNS_Games_Helper::draw_card_from_deck( $deck );
                $player_value           = MNS_Games_Helper::calculate_blackjack_hand_value( $state['player_hand'] );

                if ( $player_value['is_bust'] ) {
                    $state['turn'] = 'finished';
                    $state['deck'] = $deck;
                    MNS_Games_Helper::set_blackjack_state( $state, $user_id );
                    self::$blackjack_message = __( 'Te has pasado tras doblar. Has perdido.', 'casino-games-mns' );
                    return;
                }

                // Banca
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
                // Implementación básica: marcar como split permitido, pero no se desarrolla lógica completa de mano doble.
                // Se deja preparado para extender.
                self::$blackjack_message = __( 'La opción de Split está marcada pero su lógica avanzada está pendiente de ampliar.', 'casino-games-mns' );
                break;
        }
    }

    /**
     * Shortcode Blackjack
     */
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
                                    echo '<span class="bj-card">' . esc_html( $card ) . '</span>';
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
                                foreach ( $state['dealer_hand'] as $card ) {
                                    echo '<span class="bj-card">' . esc_html( $card ) . '</span>';
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
                    $turn       = isset( $state['turn'] ) ? $state['turn'] : 'finished';
                    $is_active  = ( $turn === 'player' );
                    $disabled   = $is_active ? '' : ' bj-action--disabled';
                    ?>

                    <button type="submit" name="mns_bj_action" value="hit" class="bj-action bj-action-hit<?php echo esc_attr( $disabled ); ?>" <?php echo $is_active ? '' : 'disabled'; ?>>
                        <?php esc_html_e( 'Pedir (Hit)', 'casino-games-mns' ); ?>
                    </button>

                    <button type="submit" name="mns_bj_action" value="stand" class="bj-action bj-action-stand<?php echo esc_attr( $disabled ); ?>" <?php echo $is_active ? '' : 'disabled'; ?>>
                        <?php esc_html_e( 'Plantarse (Stand)', 'casino-games-mns' ); ?>
                    </button>

                    <?php
                    // Double: solo si hay estado y el turno es del jugador
                    $can_double   = $is_active && ! empty( $state );
                    $double_class = $can_double ? '' : ' bj-action--disabled';
                    ?>
                    <button type="submit" name="mns_bj_action" value="double" class="bj-action bj-action-double<?php echo esc_attr( $double_class ); ?>" <?php echo $can_double ? '' : 'disabled'; ?>>
                        <?php esc_html_e( 'Doblar (Double)', 'casino-games-mns' ); ?>
                    </button>

                    <?php
                    // Split: botón visual, lógica interna aún básica
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

                    var input = form.querySelector('.bj-bet-input');
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

    /**
     * Manejar acciones de Ruleta (POST)
     */
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

        // Recoger apuestas desde campos ocultos (generados por JS)
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

        // Calcular total apostado
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

        // Cobrar total apostado
        $ok = MNS_Tokens_Helper::subtract_tokens( $user_id, $total_bet, 'roulette_bet' );
        if ( ! $ok ) {
            self::$roulette_message = __( 'No se ha podido registrar la apuesta.', 'casino-games-mns' );
            return;
        }

        // Número ganador enviado desde el giro visual (JS)
        $number = isset( $_POST['mns_rl_winning'] ) ? intval( $_POST['mns_rl_winning'] ) : -1;

        if ( $number < 0 || $number > 36 ) {
            self::$roulette_message = __( 'Ha ocurrido un error con el número ganador de la ruleta.', 'casino-games-mns' );
            return;
        }

        // Calcular ganancias
        $total_win = MNS_Games_Helper::evaluate_roulette_bets( $bets, $number );
        if ( $total_win > 0 ) {
            MNS_Tokens_Helper::add_tokens( $user_id, $total_win, 'roulette_win' );
        }

        $color  = MNS_Games_Helper::get_roulette_color( $number );
        $result = sprintf(
            /* translators: 1: number, 2: color */
            __( 'Número: %1$d (%2$s). Has apostado %3$d fichas y ganado %4$d.', 'casino-games-mns' ),
            $number,
            $color,
            $total_bet,
            $total_win
        );

        self::$roulette_message = $result;

        // Guardar en variable global para mostrar resultado
        $GLOBALS['mns_rl_last_number'] = $number;
        $GLOBALS['mns_rl_last_color']  = $color;
    }

    /**
     * Shortcode Ruleta Europea
     */
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
                    echo esc_html__(
                        'Último resultado:',
                        'casino-games-mns'
                    ) . ' ' . intval( $last_number ) . ' (' . esc_html( $last_color ) . ')';
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
                        <!-- AQUÍ aplicas tú la imagen por CSS a .rl-wheel-inner -->
                        <div class="rl-wheel-inner" id="mns-roulette-wheel"></div>
                        <div class="rl-ball" id="mns-roulette-ball"></div>
                    </div>

                    <div class="rl-grid">

                        <!-- 0 a la izquierda, en vertical -->
                        <div class="rl-row rl-row-zero">
                            <?php
                            $color_zero = MNS_Games_Helper::get_roulette_color( 0 );
                            $zero_class = 'rl-cell-number';
                            if ( $color_zero === 'red' || $color_zero === 'rojo' ) {
                                $zero_class .= ' rl-color-red';
                            } elseif ( $color_zero === 'black' || $color_zero === 'negro' ) {
                                $zero_class .= ' rl-color-black';
                            } else {
                                // Por defecto 0 verde
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

                        <!-- Números 1–36 en 3 columnas x 12 filas (mesa clásica) -->
                        <div class="rl-row rl-row-numbers-horizontal">
                            <?php for ( $col = 1; $col <= 3; $col++ ) : ?>
                                <div class="rl-col">
                                    <?php
                                    for ( $row = 0; $row < 12; $row++ ) :
                                        $num   = ( $row * 3 ) + $col;
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

                        <!-- Apuestas externas -->
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

                    var chipButtons = form.querySelectorAll('.rl-chip');
                    var currentChip = null;
                    var betsField   = form.querySelector('.mns-rl-bets-field');
                    var winningField = form.querySelector('.mns-rl-winning-field');
                    var betsList    = form.querySelector('.rl-bets-list');
                    var balance     = <?php echo intval( $tokens ); ?>;
                    var bets        = [];

                    // Selección de ficha
                    chipButtons.forEach(function(btn){
                        btn.addEventListener('click', function(e){
                            e.preventDefault();
                            chipButtons.forEach(function(b){ b.classList.remove('rl-chip--active'); });
                            this.classList.add('rl-chip--active');
                            currentChip = parseInt(this.getAttribute('data-chip'), 10) || 0;
                        });
                    });

                    // Click en casillas de apuesta
                    form.querySelectorAll('.rl-cell-number, .rl-cell-external').forEach(function(cell){
                        cell.addEventListener('click', function(e){
                            e.preventDefault();
                            if (!currentChip || currentChip <= 0) {
                                return;
                            }

                            var type  = this.getAttribute('data-type');
                            var value = this.getAttribute('data-value');

                            var totalBet = bets.reduce(function(acc, b){ return acc + (parseInt(b.amount,10)||0); }, 0);
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

                    // Envío del formulario -> primero giramos, luego mandamos número ganador
                    form.addEventListener('submit', function(e){
                        e.preventDefault();

                        // Si no hay apuestas, no hacemos nada
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
                            // Fallback: si algo falla, manda sin número visual
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

    /**
     * Manejar acciones de Ruleta Rusa
     */
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

        $user_id = get_current_user_id();

        if ( ! class_exists( 'MNS_Tokens_Helper' ) ) {
            self::$russian_message = __( 'Sistema de fichas no disponible.', 'casino-games-mns' );
            return;
        }

        $result = MNS_Games_Helper::play_russian_roulette( $user_id );

        if ( $result['result'] === 'lose' ) {
            // Pantalla de muerte total
            wp_die(
                '<div class="mns-russian-roulette-result mns-russian-roulette-lose">' .
                '<h2>' . esc_html__( 'HAS PERDIDO', 'casino-games-mns' ) . '</h2>' .
                '<p>' . esc_html__( 'Tu cuenta ha sido eliminada.', 'casino-games-mns' ) . '</p>' .
                '</div>',
                esc_html__( 'Ruleta rusa', 'casino-games-mns' ),
                array( 'response' => 200 )
            );
        }

        self::$russian_message = ! empty( $result['message'] ) ? $result['message'] : '';
    }

    /**
     * Shortcode Ruleta Rusa
     */
    public static function shortcode_russian_roulette( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div class="mns-game mns-game-russian-roulette"><p>' .
                esc_html__( 'Debes iniciar sesión para jugar.', 'casino-games-mns' ) .
                '</p></div>';
        }

        $user_id = get_current_user_id();

        if ( MNS_Games_Helper::is_admin_protected( $user_id ) ) {
            return '<div class="mns-game mns-game-russian-roulette"><p>' .
                esc_html__( 'Los administradores no pueden jugar a la ruleta rusa.', 'casino-games-mns' ) .
                '</p></div>';
        }

        if ( ! class_exists( 'MNS_Tokens_Helper' ) ) {
            return '<div class="mns-game mns-game-russian-roulette"><p>' .
                esc_html__( 'Sistema de fichas no disponible.', 'casino-games-mns' ) .
                '</p></div>';
        }

        $tokens = MNS_Tokens_Helper::get_tokens( $user_id );

        ob_start();
        ?>
        <div class="mns-game mns-game-russian-roulette">
            <div class="mns-game-balance">
                <?php echo esc_html__( 'Tus fichas:', 'casino-games-mns' ) . ' ' . intval( $tokens ); ?>
            </div>

            <?php if ( ! empty( self::$russian_message ) ) : ?>
                <div class="mns-game-messages">
                    <?php echo esc_html( self::$russian_message ); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="mns-russian-roulette-form">
                <?php wp_nonce_field( 'mns_russian_roulette_action', 'mns_rr_nonce' ); ?>
                <input type="hidden" name="mns_rr_action" value="play">

                <p class="mns-russian-warning">
                    <?php esc_html_e( 'Si pierdes, tu cuenta será eliminada. Si ganas, tus fichas se duplicarán.', 'casino-games-mns' ); ?>
                </p>

                <button type="submit" class="rr-play-button">
                    <?php esc_html_e( 'Jugar a la Ruleta Rusa (ALL IN)', 'casino-games-mns' ); ?>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
