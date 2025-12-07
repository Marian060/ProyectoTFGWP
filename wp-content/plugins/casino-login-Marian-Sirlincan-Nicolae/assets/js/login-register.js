(function($){
    $(document).ready(function(){

        // Mostrar / ocultar contraseña
        $('.mns-auth-container').on('click', '.mns-toggle-password', function(){
            var targetSelector = $(this).data('target');
            if (!targetSelector) return;

            var $input = $(targetSelector);
            if (!$input.length) return;

            var currentType = $input.attr('type');
            var newType = currentType === 'password' ? 'text' : 'password';
            $input.attr('type', newType);

            $(this).toggleClass('is-visible');
        });

        // Desplegar / ocultar políticas
        $('.mns-auth-container').on('click', '.mns-policies-toggle', function(e){
            e.preventDefault();

            var targetSelector = $(this).data('target');
            if (!targetSelector) return;

            var $box = $(targetSelector);
            if (!$box.length) return;

            var isVisible = $box.is(':visible');

            if (isVisible) {
                $box.slideUp(200);
                $(this).attr('aria-expanded', 'false');
            } else {
                $box.slideDown(200);
                $(this).attr('aria-expanded', 'true');
            }
        });

    });
})(jQuery);
