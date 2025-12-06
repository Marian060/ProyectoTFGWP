(function(window){
    'use strict';

    // Orden estándar de la ruleta europea (empezando en 0 y siguiendo el sentido)
    var pockets = [
        0,
        32,15,19,4,21,2,25,17,34,6,
        27,13,36,11,30,8,23,10,5,24,
        16,33,1,20,14,31,9,22,18,29,
        7,28,12,35,3,26
    ];

    var pocketCount = pockets.length;
    var fullCircle  = 360;
    var pocketAngle = fullCircle / pocketCount;

    /**
     * options:
     *  - wheelEl: elemento que gira (obligatorio)
     *  - ballEl:  elemento de la bola (opcional)
     *  - duration: duración en ms (opcional, por defecto 4000)
     *  - callback(winningNumber): función al terminar el giro
     */
    function spin(options) {
        options = options || {};
        var wheelEl  = options.wheelEl;
        var ballEl   = options.ballEl || null;
        var duration = options.duration || 4000;
        var callback = typeof options.callback === 'function' ? options.callback : function(){};

        if (!wheelEl) {
            // Fallback: si no hay rueda, devolvemos un número aleatorio
            var randomNum = Math.floor(Math.random() * 37);
            callback(randomNum);
            return;
        }

        // Reset transición previa
        wheelEl.style.transition = 'none';
        wheelEl.style.transform  = 'rotate(0deg)';

        if (ballEl) {
            ballEl.style.transition = 'none';
            ballEl.style.transform  = 'rotate(0deg)';
        }

        // Forzar reflow para que el reset se aplique
        void wheelEl.offsetHeight;

        // Elegir bolsillo ganador
        var index = Math.floor(Math.random() * pocketCount);
        var winningNumber = pockets[index];

        // Rotaciones extra enteras + desplazamiento hasta el bolsillo
        var extraRotations = 3 + Math.floor(Math.random() * 3); // 3 - 5 vueltas completas
        var baseAngle      = index * pocketAngle;
        var randomOffset   = (Math.random() - 0.5) * pocketAngle * 0.4; // pequeño margen para no ser robótico
        var finalAngle     = extraRotations * fullCircle + baseAngle + randomOffset;

        // Animar rueda
        wheelEl.style.transition = 'transform ' + (duration / 1000) + 's cubic-bezier(0.25, 0.8, 0.25, 1)';
        wheelEl.style.transform  = 'rotate(' + (-finalAngle) + 'deg)';

        // Opción: animar bola en sentido contrario
        if (ballEl) {
            ballEl.style.transition = 'transform ' + (duration / 1000) + 's linear';
            ballEl.style.transform  = 'rotate(' + (finalAngle) + 'deg)';
        }

        window.setTimeout(function(){
            callback(winningNumber);
        }, duration + 60);
    }

    window.MNSRoulette = {
        spin: spin
    };

})(window);
