<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNS_Games_Helper {

    /* ============================================================
     *                BLACKJACK (SIN CAMBIOS)
     * ============================================================ */

    public static function get_blackjack_state( $user_id = null ) {
        if ( $user_id === null ) $user_id = get_current_user_id();
        $state = get_user_meta( $user_id, Casino_Games_MNS::META_KEY_BJ_STATE, true );
        return is_array( $state ) ? $state : array();
    }

    public static function set_blackjack_state( $state, $user_id = null ) {
        if ( $user_id === null ) $user_id = get_current_user_id();
        if ( empty( $state ) ) delete_user_meta( $user_id, Casino_Games_MNS::META_KEY_BJ_STATE );
        else update_user_meta( $user_id, Casino_Games_MNS::META_KEY_BJ_STATE, $state );
    }

    public static function reset_blackjack_state( $user_id = null ) {
        if ( $user_id === null ) $user_id = get_current_user_id();
        delete_user_meta( $user_id, Casino_Games_MNS::META_KEY_BJ_STATE );
    }

    public static function generate_deck() {
        $ranks = array( 'A','2','3','4','5','6','7','8','9','10','J','Q','K' );
        $suits = array( 'H','D','C','S' );
        $deck = array();
        foreach ( $suits as $suit ) {
            foreach ( $ranks as $rank ) $deck[] = $rank . $suit;
        }
        shuffle( $deck );
        return $deck;
    }

    public static function draw_card_from_deck( &$deck ) {
        if ( empty( $deck ) ) $deck = self::generate_deck();
        return array_shift( $deck );
    }

    public static function calculate_blackjack_hand_value( array $cards ) {
        $value=0; $aces=0;
        foreach ( $cards as $card ) {
            $rank = preg_replace('/[HDCS]$/', '', $card);
            if ($rank==='A'){ $aces++; $value+=11; }
            elseif(in_array($rank,['K','Q','J'])) $value+=10;
            else $value+=intval($rank);
        }
        while($value>21 && $aces>0){ $value-=10; $aces--; }
        return array(
            'value'=>$value,
            'is_blackjack'=>count($cards)==2 && $value==21,
            'is_bust'=>$value>21,
            'is_soft'=>$aces>0
        );
    }

    public static function evaluate_blackjack_result( $player, $dealer ) {
        if ( $player['is_bust'] ) return 'lose';
        if ( $player['is_blackjack'] && ! $dealer['is_blackjack'] ) return 'blackjack';
        if ( $dealer['is_bust'] ) return 'win';
        if ( $player['value'] > $dealer['value'] ) return 'win';
        if ( $player['value'] < $dealer['value'] ) return 'lose';
        return 'push';
    }

    /* ============================================================
     *                RULETA EUROPEA (SIN CAMBIOS)
     * ============================================================ */

    public static function get_roulette_color( $number ) {
        $number = intval($number);
        if ($number===0) return 'green';
        $reds = array(1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36);
        return in_array($number,$reds) ? 'red' : 'black';
    }

    public static function evaluate_roulette_bets( array $bets, $number ) {
        $number=intval($number);
        $color=self::get_roulette_color($number);
        $win=0;
        foreach($bets as $b){
            $t=$b['type']??''; $v=$b['value']??''; $a=intval($b['amount']??0);
            if($a<=0)continue;

            switch($t){
                case 'straight': if(intval($v)==$number) $win+=$a*36; break;
                case 'color': if($number!=0 && $v==$color) $win+=$a*2; break;
                case 'even_odd':
                    if($number!=0){
                        if($v=='even' && $number%2==0) $win+=$a*2;
                        if($v=='odd' && $number%2==1) $win+=$a*2;
                    }
                break;
                case 'low_high':
                    if($v=='low' && $number>=1 && $number<=18) $win+=$a*2;
                    if($v=='high'&&$number>=19&&$number<=36) $win+=$a*2;
                break;
                case 'dozen':
                    $dz=intval($v);
                    if($dz==1 && $number>=1 && $number<=12) $win+=$a*3;
                    if($dz==2 && $number>=13&&$number<=24)$win+=$a*3;
                    if($dz==3 && $number>=25&&$number<=36)$win+=$a*3;
                break;
                case 'column':
                    if($number!=0){
                        $col=((($number-1)%3)+1);
                        if($col==intval($v)) $win+=$a*3;
                    }
                break;
            }
        }
        return $win;
    }

    /* ============================================================
     *                RULETA RUSA COMPLETAMENTE NUEVA
     * ============================================================ */

    /* -------------------------------
     *   OPCIÓN GLOBAL: NIVEL DE PACO
     * ------------------------------- */
    public static function get_paco_level() {
        $lvl = get_option('mns_rr_paco_level', 1);
        return max(1, intval($lvl));
    }

    public static function increment_paco_level() {
        $new = self::get_paco_level() + 1;
        update_option('mns_rr_paco_level', $new);
        return $new;
    }

    /* ------------------------------------------
     *  ESTADO DE PARTIDA POR USUARIO
     * ------------------------------------------*/

    public static function get_rr_state( $user_id ) {
        $state = get_user_meta($user_id,'mns_rr_state',true);
        return is_array($state) ? $state : array();
    }

    public static function set_rr_state( $user_id, $state ) {
        if (empty($state)) delete_user_meta($user_id,'mns_rr_state');
        else update_user_meta($user_id,'mns_rr_state',$state);
    }

    public static function reset_rr_state( $user_id ) {
        delete_user_meta($user_id,'mns_rr_state');
    }

    /* ------------------------------------------
     *  INICIAR PARTIDA: después de Cara/Cruz
     * ------------------------------------------*/

    public static function start_rr_game( $user_id, $starts ) {
        $balance = MNS_Tokens_Helper::get_tokens($user_id);
        if ($balance <= 0) {
            return array(
                'error'=>true,
                'message'=>__('No tienes fichas para jugar.', 'casino-games-mns')
            );
        }

        // ALL-IN: el jugador no pierde fichas al jugar, solo si muere.
        // Se duplica si gana al matar a Paco.

        $paco_level = self::get_paco_level();

        $state = array(
            'turn'     => $starts,        // 'player' o 'paco'
            'paco'     => $paco_level,
            'finished' => false
        );

        self::set_rr_state($user_id,$state);

        return array(
            'error'=>false,
            'state'=>$state,
            'message'=> sprintf(__('Paco %d es tu contrincante.', 'casino-games-mns'), $paco_level)
        );
    }

    /* ------------------------------------------
     *  RESOLVER DISPARO (jugador o Paco)
     * ------------------------------------------*/

    public static function resolve_rr_shot( $user_id, $shooter ) {

        $state = self::get_rr_state($user_id);
        if ( empty($state) || !isset($state['turn']) ) {
            return array('error'=>true,'message'=>'Partida no iniciada.');
        }

        if ($state['finished']) {
            return array('error'=>true,'message'=>'La partida ya ha terminado.');
        }

        // Validar turno
        if ($state['turn'] !== $shooter) {
            return array('error'=>true,'message'=>'No es tu turno.');
        }

        /* Cámara realista 1/6 */
        $fatal = ( wp_rand(1,6) === 1 );

        /* ----------------------------------------------------
         * SI MUERE EL JUGADOR
         * ----------------------------------------------------*/
        if ($shooter === 'player' && $fatal) {

            // Eliminar usuario igual que antes
            $balance = MNS_Tokens_Helper::get_tokens($user_id);

            global $wpdb;
            if ( class_exists('Casino_Tokens_MNS') ) {
                $table = $wpdb->prefix . Casino_Tokens_MNS::TABLE_TRANSACTIONS;
                $wpdb->delete($table, array('user_id'=>$user_id), array('%d'));
            }

            MNS_Tokens_Helper::set_tokens($user_id,0);

            require_once ABSPATH.'wp-admin/includes/user.php';
            wp_delete_user($user_id);
            if (function_exists('wp_destroy_current_session')) wp_destroy_current_session();
            wp_clear_auth_cookie();

            return array(
                'fatal'=>true,
                'dead'=>'player',
                'message'=>__('HAS PERDIDO. Tu cuenta ha sido eliminada.', 'casino-games-mns')
            );
        }

        /* ----------------------------------------------------
         * SI MUERE PACO
         * ----------------------------------------------------*/
        if ($shooter==='paco' && $fatal) {

            $level_before = self::get_paco_level();
            $new_level    = self::increment_paco_level();

            // DUPLICAR FICHAS
            $balance = MNS_Tokens_Helper::get_tokens($user_id);
            MNS_Tokens_Helper::add_tokens($user_id,$balance,'russian_roulette_win');

            $state['finished'] = true;
            self::set_rr_state($user_id,$state);

            return array(
                'fatal'=>true,
                'dead'=>'paco',
                'message'=> sprintf(
                    __('¡Has ganado! Paco %d ha muerto. El próximo será Paco %d.', 'casino-games-mns'),
                    $level_before,
                    $new_level
                )
            );
        }

        /* ----------------------------------------------------
         * Si NO muere nadie → cambiar turno
         * ----------------------------------------------------*/
        $state['turn'] = ( $shooter === 'player' ) ? 'paco' : 'player';
        self::set_rr_state($user_id,$state);

        return array(
            'fatal'=>false,
            'next_turn'=>$state['turn'],
            'message'=> __('CLIC... Te has salvado.', 'casino-games-mns')
        );
    }
}
