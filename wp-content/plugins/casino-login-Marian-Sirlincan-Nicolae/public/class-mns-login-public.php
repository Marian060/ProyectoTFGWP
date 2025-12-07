<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNS_Login_Public {

    /**
     * Errores de login
     *
     * @var array
     */
    public static $login_errors = array();

    /**
     * Errores de registro
     *
     * @var array
     */
    public static $register_errors = array();

    /**
     * Registro correcto
     *
     * @var bool
     */
    public static $register_success = false;

    /**
     * Manejar envío de login
     */
    public static function handle_login() {
        if ( ! isset( $_POST['mns_login_submit'] ) ) {
            return;
        }

        if ( ! isset( $_POST['mns_login_nonce'] ) || ! wp_verify_nonce( $_POST['mns_login_nonce'], 'mns_login_action' ) ) {
            self::$login_errors[] = __( 'Solicitud no válida.', 'casino-login-mns' );
            return;
        }

        $username_or_email = isset( $_POST['mns_login_user'] ) ? sanitize_text_field( $_POST['mns_login_user'] ) : '';
        $password          = isset( $_POST['mns_login_pass'] ) ? $_POST['mns_login_pass'] : '';
        $remember          = ! empty( $_POST['mns_login_remember'] );

        if ( empty( $username_or_email ) || empty( $password ) ) {
            self::$login_errors[] = __( 'Usuario y contraseña son obligatorios.', 'casino-login-mns' );
            return;
        }

        // Permitir login tanto por usuario como por email
        if ( is_email( $username_or_email ) ) {
            $user = get_user_by( 'email', $username_or_email );
            if ( $user ) {
                $username = $user->user_login;
            } else {
                $username = $username_or_email;
            }
        } else {
            $username = $username_or_email;
        }

        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        );

        $user = wp_signon( $creds, false );

        if ( is_wp_error( $user ) ) {
            self::$login_errors[] = __( 'Usuario o contraseña incorrectos.', 'casino-login-mns' );
            return;
        }

        // Login correcto: redirigir
        $redirect_to = isset( $_POST['mns_redirect_to'] ) ? esc_url_raw( $_POST['mns_redirect_to'] ) : home_url( '/' );

        // Evitar bucles: si el redirect es la página de login o registro, ir al home
        $login_url    = MNS_Login_Helper::get_page_url_by_slug( Casino_Login_MNS::PAGE_LOGIN_SLUG );
        $register_url = MNS_Login_Helper::get_page_url_by_slug( Casino_Login_MNS::PAGE_REGISTER_SLUG );

        if ( $redirect_to === $login_url || $redirect_to === $register_url ) {
            $redirect_to = home_url( '/' );
        }

        wp_safe_redirect( $redirect_to );
        exit;
    }

    /**
     * Manejar envío de registro
     */
    public static function handle_register() {
        if ( ! isset( $_POST['mns_register_submit'] ) ) {
            return;
        }

        if ( ! isset( $_POST['mns_register_nonce'] ) || ! wp_verify_nonce( $_POST['mns_register_nonce'], 'mns_register_action' ) ) {
            self::$register_errors[] = __( 'Solicitud no válida.', 'casino-login-mns' );
            return;
        }

        $username    = isset( $_POST['mns_reg_user'] ) ? sanitize_user( $_POST['mns_reg_user'] ) : '';
        $email       = isset( $_POST['mns_reg_email'] ) ? sanitize_email( $_POST['mns_reg_email'] ) : '';
        $birthdate   = isset( $_POST['mns_reg_birthdate'] ) ? sanitize_text_field( $_POST['mns_reg_birthdate'] ) : '';
        $pass1       = isset( $_POST['mns_reg_pass1'] ) ? $_POST['mns_reg_pass1'] : '';
        $pass2       = isset( $_POST['mns_reg_pass2'] ) ? $_POST['mns_reg_pass2'] : '';
        $terms       = ! empty( $_POST['mns_reg_terms'] );
        $privacy     = ! empty( $_POST['mns_reg_privacy'] );
        $responsible = ! empty( $_POST['mns_reg_responsible'] );
        $redirect_to = isset( $_POST['mns_redirect_to'] ) ? esc_url_raw( $_POST['mns_redirect_to'] ) : home_url( '/' );

        // Validaciones básicas
        if ( empty( $username ) || empty( $email ) || empty( $birthdate ) || empty( $pass1 ) || empty( $pass2 ) ) {
            self::$register_errors[] = __( 'Todos los campos son obligatorios.', 'casino-login-mns' );
        }

        if ( ! is_email( $email ) ) {
            self::$register_errors[] = __( 'El correo electrónico no es válido.', 'casino-login-mns' );
        }

        if ( email_exists( $email ) ) {
            self::$register_errors[] = __( 'Ya existe una cuenta con ese correo electrónico.', 'casino-login-mns' );
        }

        if ( username_exists( $username ) ) {
            self::$register_errors[] = __( 'El nombre de usuario ya está en uso.', 'casino-login-mns' );
        }

        if ( $pass1 !== $pass2 ) {
            self::$register_errors[] = __( 'Las contraseñas no coinciden.', 'casino-login-mns' );
        }

        if ( strlen( $pass1 ) < 6 ) {
            self::$register_errors[] = __( 'La contraseña debe tener al menos 6 caracteres.', 'casino-login-mns' );
        }

        if ( ! $terms ) {
            self::$register_errors[] = __( 'Debes aceptar los términos y condiciones.', 'casino-login-mns' );
        }

        if ( ! $privacy ) {
            self::$register_errors[] = __( 'Debes aceptar la política de privacidad.', 'casino-login-mns' );
        }

        if ( ! $responsible ) {
            self::$register_errors[] = __( 'Debes confirmar el uso responsable y que entiendes que es un entorno educativo sin dinero real.', 'casino-login-mns' );
        }

        // Validar mayoría de edad (18+)
        $age = MNS_Login_Helper::calculate_age( $birthdate );
        if ( $age === null ) {
            self::$register_errors[] = __( 'La fecha de nacimiento no es válida.', 'casino-login-mns' );
        } elseif ( $age < 18 ) {
            self::$register_errors[] = __( 'Debes ser mayor de 18 años para registrarte.', 'casino-login-mns' );
        }

        if ( ! empty( self::$register_errors ) ) {
            return;
        }

        // Crear usuario
        $user_id = wp_create_user( $username, $pass1, $email );

        if ( is_wp_error( $user_id ) ) {
            self::$register_errors[] = __( 'Ha ocurrido un error al crear la cuenta.', 'casino-login-mns' );
            return;
        }

        // Guardar fecha de nacimiento
        update_user_meta( $user_id, Casino_Login_MNS::META_BIRTHDATE, $birthdate );

        // Enviar email de bienvenida
        self::send_welcome_email( $user_id, $email, $username );

        // Autologin
        $user = get_user_by( 'id', $user_id );
        if ( $user ) {
            wp_set_current_user( $user_id );
            wp_set_auth_cookie( $user_id, true ); // mantener logueado
        }

        self::$register_success = true;

        // Evitar bucles: si el redirect es login o registro, ir al home
        $login_url    = MNS_Login_Helper::get_page_url_by_slug( Casino_Login_MNS::PAGE_LOGIN_SLUG );
        $register_url = MNS_Login_Helper::get_page_url_by_slug( Casino_Login_MNS::PAGE_REGISTER_SLUG );

        if ( $redirect_to === $login_url || $redirect_to === $register_url ) {
            $redirect_to = home_url( '/' );
        }

        wp_safe_redirect( $redirect_to );
        exit;
    }

    /**
     * Enviar email de bienvenida
     *
     * @param int $user_id
     * @param string $email
     * @param string $username
     */
    private static function send_welcome_email( $user_id, $email, $username ) {
        $subject = __( '¡Bienvenido a Casino M.N.S!', 'casino-login-mns' );

        $message  = "Hola {$username},\n\n";
        $message .= "Muchas gracias por registrarte en Casino M.N.S, un entorno educativo donde podrás explorar simulaciones de juegos de casino sin dinero real.\n\n";
        $message .= "A partir de ahora podrás:\n";
        $message .= "- Iniciar sesión con tu usuario o correo.\n";
        $message .= "- Gestionar tus fichas simuladas.\n";
        $message .= "- Acceder a juegos como Blackjack y Ruleta (cuando estén disponibles).\n\n";
        $message .= "Recuerda que este proyecto es únicamente con fines educativos y no utiliza dinero real.\n\n";
        $message .= "¡Que disfrutes de la experiencia!\n";
        $message .= "Casino M.N.S\n";

        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

        wp_mail( $email, $subject, $message, $headers );
    }

    /**
     * Obtener URL de redirección "previa"
     *
     * @return string
     */
    private static function get_redirect_url() {
        // Intentar obtener la URL previa
        $referer = wp_get_referer();

        if ( $referer ) {
            $login_url    = MNS_Login_Helper::get_page_url_by_slug( Casino_Login_MNS::PAGE_LOGIN_SLUG );
            $register_url = MNS_Login_Helper::get_page_url_by_slug( Casino_Login_MNS::PAGE_REGISTER_SLUG );

            // Evitar que la última página sea login/registro
            if ( $referer === $login_url || $referer === $register_url ) {
                return home_url( '/' );
            }
            return $referer;
        }

        return home_url( '/' );
    }

    /**
     * Shortcode: [mns_login_form]
     */
    public static function shortcode_login_form( $atts ) {
        if ( is_user_logged_in() ) {
            return esc_html__( 'Ya has iniciado sesión.', 'casino-login-mns' );
        }

        $redirect_to = self::get_redirect_url();

        ob_start();
        ?>
        <div class="mns-auth-container mns-login-container">
            <?php if ( ! empty( self::$login_errors ) ) : ?>
                <div class="mns-auth-errors mns-login-errors">
                    <ul class="mns-error-list">
                        <?php foreach ( self::$login_errors as $error ) : ?>
                            <li class="mns-error-item"><?php echo esc_html( $error ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="mns-form mns-login-form">
                <div class="mns-form-group">
                    <label for="mns_login_user" class="mns-label mns-label-user"><?php esc_html_e( 'Usuario o correo electrónico', 'casino-login-mns' ); ?></label>
                    <input type="text" id="mns_login_user" name="mns_login_user" class="mns-input mns-input-user" required />
                </div>

                <div class="mns-form-group">
                    <label for="mns_login_pass" class="mns-label mns-label-pass"><?php esc_html_e( 'Contraseña', 'casino-login-mns' ); ?></label>
                    <div class="mns-password-wrapper">
                        <input type="password" id="mns_login_pass" name="mns_login_pass" class="mns-input mns-input-pass" required />
                        <button type="button"
                                class="mns-toggle-password"
                                data-target="#mns_login_pass"
                                aria-label="<?php esc_attr_e( 'Mostrar u ocultar contraseña', 'casino-login-mns' ); ?>">
                            👁
                        </button>
                    </div>
                    <p class="mns-password-hint">
                        <?php esc_html_e( 'La contraseña debe tener al menos 6 caracteres.', 'casino-login-mns' ); ?>
                    </p>
                </div>

                <div class="mns-form-group mns-form-remember">
                    <label class="mns-label mns-label-remember">
                        <input type="checkbox" name="mns_login_remember" class="mns-input-checkbox mns-input-remember" />
                        <?php esc_html_e( 'Mantener sesión iniciada', 'casino-login-mns' ); ?>
                    </label>
                </div>

                <div class="mns-form-group mns-form-policies">
                    <a href="#"
                       class="mns-policies-toggle"
                       data-target="#mns-login-policies"
                       aria-expanded="false">
                        <?php esc_html_e( 'Ver políticas y aviso educativo', 'casino-login-mns' ); ?>
                    </a>
                    <div id="mns-login-policies" class="mns-policies-content" style="display:none;">
                        <p><?php esc_html_e( 'Casino M.N.S es un entorno educativo. No se utilizan fichas ni dinero real con valor fuera del sistema. Todas las partidas son simulaciones.', 'casino-login-mns' ); ?></p>
                        <p><?php esc_html_e( 'Al usar esta plataforma aceptas los términos del proyecto, la política de privacidad del sitio y te comprometes a un uso responsable.', 'casino-login-mns' ); ?></p>
                    </div>
                </div>

                <input type="hidden" name="mns_redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />

                <?php wp_nonce_field( 'mns_login_action', 'mns_login_nonce' ); ?>

                <div class="mns-form-group mns-form-actions">
                    <button type="submit" name="mns_login_submit" class="mns-button mns-button-login">
                        <?php esc_html_e( 'Iniciar sesión', 'casino-login-mns' ); ?>
                    </button>
                </div>
            </form>

            <div class="mns-auth-secondary mns-login-secondary">
                <?php
                $register_url = MNS_Login_Helper::get_page_url_by_slug( Casino_Login_MNS::PAGE_REGISTER_SLUG );
                ?>
                <a href="<?php echo esc_url( $register_url ); ?>" class="mns-link mns-link-register">
                    <?php esc_html_e( '¿No tienes cuenta? Regístrate', 'casino-login-mns' ); ?>
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Shortcode: [mns_register_form]
     */
    public static function shortcode_register_form( $atts ) {
        if ( is_user_logged_in() ) {
            return esc_html__( 'Ya tienes una sesión activa.', 'casino-login-mns' );
        }

        $redirect_to = self::get_redirect_url();

        ob_start();
        ?>
        <div class="mns-auth-container mns-register-container">
            <?php if ( ! empty( self::$register_errors ) ) : ?>
                <div class="mns-auth-errors mns-register-errors">
                    <ul class="mns-error-list">
                        <?php foreach ( self::$register_errors as $error ) : ?>
                            <li class="mns-error-item"><?php echo esc_html( $error ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="mns-form mns-register-form">
                <div class="mns-form-group">
                    <label for="mns_reg_user" class="mns-label mns-label-user"><?php esc_html_e( 'Nombre de usuario', 'casino-login-mns' ); ?></label>
                    <input type="text" id="mns_reg_user" name="mns_reg_user" class="mns-input mns-input-user" required />
                </div>

                <div class="mns-form-group">
                    <label for="mns_reg_email" class="mns-label mns-label-email"><?php esc_html_e( 'Correo electrónico', 'casino-login-mns' ); ?></label>
                    <input type="email" id="mns_reg_email" name="mns_reg_email" class="mns-input mns-input-email" required />
                </div>

                <div class="mns-form-group">
                    <label for="mns_reg_birthdate" class="mns-label mns-label-birthdate"><?php esc_html_e( 'Fecha de nacimiento', 'casino-login-mns' ); ?></label>
                    <input type="date" id="mns_reg_birthdate" name="mns_reg_birthdate" class="mns-input mns-input-birthdate" required />
                </div>

                <div class="mns-form-group">
                    <label for="mns_reg_pass1" class="mns-label mns-label-pass1"><?php esc_html_e( 'Contraseña', 'casino-login-mns' ); ?></label>
                    <div class="mns-password-wrapper">
                        <input type="password" id="mns_reg_pass1" name="mns_reg_pass1" class="mns-input mns-input-pass1" required />
                        <button type="button"
                                class="mns-toggle-password"
                                data-target="#mns_reg_pass1"
                                aria-label="<?php esc_attr_e( 'Mostrar u ocultar contraseña', 'casino-login-mns' ); ?>">
                            👁
                        </button>
                    </div>
                    <p class="mns-password-hint">
                        <?php esc_html_e( 'La contraseña debe tener al menos 6 caracteres.', 'casino-login-mns' ); ?>
                    </p>
                </div>

                <div class="mns-form-group">
                    <label for="mns_reg_pass2" class="mns-label mns-label-pass2"><?php esc_html_e( 'Confirmar contraseña', 'casino-login-mns' ); ?></label>
                    <div class="mns-password-wrapper">
                        <input type="password" id="mns_reg_pass2" name="mns_reg_pass2" class="mns-input mns-input-pass2" required />
                        <button type="button"
                                class="mns-toggle-password"
                                data-target="#mns_reg_pass2"
                                aria-label="<?php esc_attr_e( 'Mostrar u ocultar contraseña', 'casino-login-mns' ); ?>">
                            👁
                        </button>
                    </div>
                </div>

                <div class="mns-form-group mns-form-terms">
                    <label class="mns-label mns-label-terms">
                        <input type="checkbox" name="mns_reg_terms" class="mns-input-checkbox mns-input-terms" required />
                        <?php esc_html_e( 'Acepto los términos y condiciones del proyecto educativo.', 'casino-login-mns' ); ?>
                    </label>
                </div>

                <div class="mns-form-group mns-form-privacy">
                    <label class="mns-label mns-label-privacy">
                        <input type="checkbox" name="mns_reg_privacy" class="mns-input-checkbox mns-input-privacy" required />
                        <?php esc_html_e( 'He leído y acepto la política de privacidad del sitio.', 'casino-login-mns' ); ?>
                    </label>
                </div>

                <div class="mns-form-group mns-form-responsible">
                    <label class="mns-label mns-label-responsible">
                        <input type="checkbox" name="mns_reg_responsible" class="mns-input-checkbox mns-input-responsible" required />
                        <?php esc_html_e( 'Entiendo que es un entorno educativo sin dinero real y me comprometo a un uso responsable.', 'casino-login-mns' ); ?>
                    </label>
                </div>

                <div class="mns-form-group mns-form-policies">
                    <a href="#"
                       class="mns-policies-toggle"
                       data-target="#mns-register-policies"
                       aria-expanded="false">
                        <?php esc_html_e( 'Ver políticas completas y aviso educativo', 'casino-login-mns' ); ?>
                    </a>
                    <div id="mns-register-policies" class="mns-policies-content" style="display:none;">
                        <p><?php esc_html_e( 'Este registro forma parte de un proyecto educativo de simulación de casino. No se ofrecen premios ni se maneja dinero real.', 'casino-login-mns' ); ?></p>
                        <p><?php esc_html_e( 'Las fichas son elementos virtuales sin valor monetario. El objetivo es aprender conceptos de desarrollo web, lógica de juego y experiencia de usuario.', 'casino-login-mns' ); ?></p>
                        <p><?php esc_html_e( 'Puedes ejercer tus derechos de protección de datos siguiendo la política de privacidad publicada en el sitio principal.', 'casino-login-mns' ); ?></p>
                    </div>
                </div>

                <input type="hidden" name="mns_redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />

                <?php wp_nonce_field( 'mns_register_action', 'mns_register_nonce' ); ?>

                <div class="mns-form-group mns-form-actions">
                    <button type="submit" name="mns_register_submit" class="mns-button mns-button-register">
                        <?php esc_html_e( 'Registrarse', 'casino-login-mns' ); ?>
                    </button>
                </div>
            </form>

            <div class="mns-auth-secondary mns-register-secondary">
                <?php
                $login_url = MNS_Login_Helper::get_page_url_by_slug( Casino_Login_MNS::PAGE_LOGIN_SLUG );
                ?>
                <a href="<?php echo esc_url( $login_url ); ?>" class="mns-link mns-link-login">
                    <?php esc_html_e( '¿Ya tienes cuenta? Inicia sesión', 'casino-login-mns' ); ?>
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Shortcode: [mns_profile_page]
     *
     * Muestra la página de perfil con datos básicos, cambio de avatar y el historial.
     */
    public static function shortcode_profile_page( $atts ) {
        if ( ! is_user_logged_in() ) {
            $login_url = MNS_Login_Helper::get_page_url_by_slug( Casino_Login_MNS::PAGE_LOGIN_SLUG );
            ob_start();
            ?>
            <div class="mns-profile-not-logged">
                <p><?php esc_html_e( 'Debes iniciar sesión para ver tu perfil.', 'casino-login-mns' ); ?></p>
                <a href="<?php echo esc_url( $login_url ); ?>" class="mns-link mns-link-login">
                    <?php esc_html_e( 'Ir a iniciar sesión', 'casino-login-mns' ); ?>
                </a>
            </div>
            <?php
            return ob_get_clean();
        }

        $user_id = get_current_user_id();
        $user    = get_userdata( $user_id );

        // Actualizar avatar si se envía formulario
        if ( isset( $_POST['mns_profile_update'] ) && isset( $_POST['mns_profile_nonce'] ) && wp_verify_nonce( $_POST['mns_profile_nonce'], 'mns_profile_action' ) ) {
            $avatar_url = isset( $_POST['mns_avatar_url'] ) ? esc_url_raw( $_POST['mns_avatar_url'] ) : '';
            if ( $avatar_url ) {
                update_user_meta( $user_id, Casino_Login_MNS::META_AVATAR_URL, $avatar_url );
            }
        }

        $avatar_url = MNS_Login_Helper::get_avatar_url( $user_id );
        $birthdate  = get_user_meta( $user_id, Casino_Login_MNS::META_BIRTHDATE, true );
        $age        = $birthdate ? MNS_Login_Helper::calculate_age( $birthdate ) : null;

        ob_start();
        ?>
        <div class="mns-profile-container">
            <div class="mns-profile-header">
                <div class="mns-profile-avatar">
                    <img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php esc_attr_e( 'Avatar', 'casino-login-mns' ); ?>" class="mns-avatar-img" />
                </div>
                <div class="mns-profile-main">
                    <h2 class="mns-profile-username"><?php echo esc_html( $user->user_login ); ?></h2>
                    <p class="mns-profile-email"><?php echo esc_html( $user->user_email ); ?></p>
                    <?php if ( $age !== null ) : ?>
                        <p class="mns-profile-age">
                            <?php
                            printf(
                                esc_html__( 'Edad: %d años', 'casino-login-mns' ),
                                intval( $age )
                            );
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="mns-profile-actions">
                    <?php
                    $logout_url = wp_logout_url( home_url( '/' ) );
                    ?>
                    <a href="<?php echo esc_url( $logout_url ); ?>" class="mns-button mns-button-logout">
                        <?php esc_html_e( 'Cerrar sesión', 'casino-login-mns' ); ?>
                    </a>
                </div>
            </div>

            <div class="mns-profile-edit">
                <h3 class="mns-profile-section-title"><?php esc_html_e( 'Actualizar foto de perfil', 'casino-login-mns' ); ?></h3>
                <form method="post" class="mns-form mns-profile-form">
                    <div class="mns-form-group">
                        <label for="mns_avatar_url" class="mns-label mns-label-avatar">
                            <?php esc_html_e( 'URL de la imagen de avatar', 'casino-login-mns' ); ?>
                        </label>
                        <input type="url" id="mns_avatar_url" name="mns_avatar_url" class="mns-input mns-input-avatar" value="<?php echo esc_attr( $avatar_url ); ?>" />
                    </div>
                    <?php wp_nonce_field( 'mns_profile_action', 'mns_profile_nonce' ); ?>
                    <div class="mns-form-group mns-form-actions">
                        <button type="submit" name="mns_profile_update" class="mns-button mns-button-profile-update">
                            <?php esc_html_e( 'Guardar cambios', 'casino-login-mns' ); ?>
                        </button>
                    </div>
                </form>
            </div>

            <div class="mns-profile-history">
                <h3 class="mns-profile-section-title"><?php esc_html_e( 'Historial de transacciones', 'casino-login-mns' ); ?></h3>
                <div class="mns-profile-history-inner">
                    <?php
                    // Insertar el historial de tokens si el shortcode existe
                    echo do_shortcode( '[mns_token_history]' );
                    ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Shortcode: [mns_auth_menu]
     *
     * Muestra el botón de login/registro o el menú de usuario con desplegable.
     */
    public static function shortcode_auth_menu( $atts ) {
        ob_start();

        if ( ! is_user_logged_in() ) {
            $login_url    = MNS_Login_Helper::get_page_url_by_slug( Casino_Login_MNS::PAGE_LOGIN_SLUG );
            $register_url = MNS_Login_Helper::get_page_url_by_slug( Casino_Login_MNS::PAGE_REGISTER_SLUG );
            ?>
            <div class="mns-auth-menu mns-auth-logged-out">
                <a href="<?php echo esc_url( $login_url ); ?>" class="mns-button mns-button-header-login">
                    <?php esc_html_e( 'Login', 'casino-login-mns' ); ?>
                </a>
                <a href="<?php echo esc_url( $register_url ); ?>" class="mns-button mns-button-header-register">
                    <?php esc_html_e( 'Registrarse', 'casino-login-mns' ); ?>
                </a>
            </div>
            <?php
        } else {
            $user_id    = get_current_user_id();
            $user       = get_userdata( $user_id );
            $avatar_url = MNS_Login_Helper::get_avatar_url( $user_id );
            $profile_url= MNS_Login_Helper::get_page_url_by_slug( Casino_Login_MNS::PAGE_PROFILE_SLUG );
            $history_url= home_url( '/historial/' );
            $logout_url = wp_logout_url( home_url( '/' ) );

            // Obtener fichas si el plugin de tokens está activo
            $tokens_display = '';
            if ( class_exists( 'MNS_Tokens_Helper' ) ) {
                $tokens = MNS_Tokens_Helper::get_tokens( $user_id );
                $tokens_display = intval( $tokens );
            }

            ?>
            <div class="mns-auth-menu mns-auth-logged-in">
                <button type="button" class="mns-user-toggle">
                    <span class="mns-user-avatar">
                        <img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php esc_attr_e( 'Avatar', 'casino-login-mns' ); ?>" class="mns-avatar-img" />
                    </span>
                    <span class="mns-user-name"><?php echo esc_html( $user->user_login ); ?></span>
                    <?php if ( $tokens_display !== '' ) : ?>
                        <span class="mns-user-tokens">
                            <?php echo esc_html( $tokens_display ); ?>
                        </span>
                    <?php endif; ?>
                </button>
                <div class="mns-user-dropdown">
                    <div class="mns-dropdown-header">
                        <span class="mns-dropdown-name"><?php echo esc_html( $user->user_login ); ?></span>
                    </div>
                    <a href="<?php echo esc_url( $profile_url ); ?>" class="mns-dropdown-link mns-link-profile">
                        <?php esc_html_e( 'Ver perfil', 'casino-login-mns' ); ?>
                    </a>
                    <a href="<?php echo esc_url( $history_url ); ?>" class="mns-dropdown-link mns-link-history">
                        <?php esc_html_e( 'Historial de fichas', 'casino-login-mns' ); ?>
                    </a>
                    <a href="<?php echo esc_url( $logout_url ); ?>" class="mns-dropdown-link mns-link-logout">
                        <?php esc_html_e( 'Cerrar sesión', 'casino-login-mns' ); ?>
                    </a>
                </div>
            </div>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * Shortcode: [mns_logout_button]
     *
     * Botón simple de logout para usar dentro de páginas.
     */
    public static function shortcode_logout_button( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '';
        }

        $atts = shortcode_atts(
            array(
                'redirect_to' => home_url( '/' ),
                'label'       => __( 'Cerrar sesión', 'casino-login-mns' ),
            ),
            $atts,
            'mns_logout_button'
        );

        $logout_url = wp_logout_url( esc_url_raw( $atts['redirect_to'] ) );

        ob_start();
        ?>
        <a href="<?php echo esc_url( $logout_url ); ?>" class="mns-button mns-button-logout-shortcode">
            <?php echo esc_html( $atts['label'] ); ?>
        </a>
        <?php
        return ob_get_clean();
    }

}
