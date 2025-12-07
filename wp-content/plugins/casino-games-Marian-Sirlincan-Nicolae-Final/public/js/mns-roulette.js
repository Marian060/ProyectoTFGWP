(function(window){
    'use strict';

    // ============================================================
    //             RULETA EUROPEA (EXISTENTE)
    // ============================================================

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

    function spin(options) {
        options = options || {};
        var wheelEl  = options.wheelEl;
        var ballEl   = options.ballEl || null;
        var duration = options.duration || 4000;
        var callback = typeof options.callback === 'function' ? options.callback : function(){};

        if (!wheelEl) {
            var randomNum = Math.floor(Math.random() * 37);
            callback(randomNum);
            return;
        }

        wheelEl.style.transition = 'none';
        wheelEl.style.transform  = 'rotate(0deg)';

        if (ballEl) {
            ballEl.style.transition = 'none';
            ballEl.style.transform  = 'rotate(0deg)';
        }

        void wheelEl.offsetHeight;

        var index = Math.floor(Math.random() * pocketCount);
        var winningNumber = pockets[index];

        var extraRotations = 3 + Math.floor(Math.random() * 3);
        var baseAngle      = index * pocketAngle;
        var randomOffset   = (Math.random() - 0.5) * pocketAngle * 0.4;
        var finalAngle     = extraRotations * fullCircle + baseAngle + randomOffset;

        wheelEl.style.transition = 'transform ' + (duration / 1000) + 's cubic-bezier(0.25, 0.8, 0.25, 1)';
        wheelEl.style.transform  = 'rotate(' + (-finalAngle) + 'deg)';

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

    // ============================================================
    //             NUEVO: RULETA RUSA EXTENDIDA
    // ============================================================
    //
    // Este módulo controla:
    //  - Manera 3D de moneda
    //  - Tambor del revólver
    //  - Turnos jugador / Paco
    //  - Cuenta atrás
    //  - Disparo (BANG / CLICK)
    //

    window.MNSRussianRoulette = {

        // ------------------------------
        //  ANIMACIÓN MONEDA 3D
        // ------------------------------
        flipCoin: function(options){
            var element = options.element;
            var userChoice = options.choice;   // "heads" | "tails"
            var callback   = options.callback || function(){};

            if (!element) return callback('heads');

            element.classList.remove('rr-coin-anim-heads');
            element.classList.remove('rr-coin-anim-tails');

            void element.offsetHeight;

            var result = Math.random() < 0.5 ? 'heads' : 'tails';

            if (result === 'heads') {
                element.classList.add('rr-coin-anim-heads');
            } else {
                element.classList.add('rr-coin-anim-tails');
            }

            setTimeout(function(){
                callback(result);
            }, 2000);
        },

        // ------------------------------
        //  GIRAR TAMBOR 3D
        // ------------------------------
        spinChamber: function(options){
            var element = options.element;
            var callback = options.callback || function(){};

            if (!element) return callback();

            element.classList.add('rr-chamber-spin');

            setTimeout(function(){
                element.classList.remove('rr-chamber-spin');
                callback();
            }, 1200);
        },

        // ------------------------------
        //  CUENTA ATRÁS 3,2,1
        // ------------------------------
        countdown: function(options){
            var element  = options.element;
            var callback = options.callback || function(){};
            var n = 3;

            if (!element) return callback();

            function tick(){
                element.textContent = n;
                n--;
                if (n < 0) {
                    element.textContent = '';
                    callback();
                } else {
                    setTimeout(tick, 800);
                }
            }

            tick();
        },

        // ------------------------------
        //  DISPARO (BANG / CLICK)
        // ------------------------------
        fire: function(options){
            var bangScreen  = options.bangElement;  
            var emptyScreen = options.emptyElement; 
            var callback    = options.callback || function(){};

            var fatal = (Math.floor(Math.random() * 6) === 0);

            if (fatal) {
                if (bangScreen) {
                    bangScreen.classList.add('rr-bang-show');
                    setTimeout(function(){
                        bangScreen.classList.remove('rr-bang-show');
                        callback(true);  
                    }, 1500);
                } else {
                    callback(true);
                }
            } else {
                if (emptyScreen) {
                    emptyScreen.classList.add('rr-empty-show');
                    setTimeout(function(){
                        emptyScreen.classList.remove('rr-empty-show');
                        callback(false);
                    }, 1000);
                } else {
                    callback(false);
                }
            }
        },

        // ------------------------------
        //  TURNO AUTOMÁTICO DE PACO
        // ------------------------------
        pacoTurn: function(options){
            var chamberEl = options.chamberElement;
            var countdownEl = options.countdownElement;
            var bangEl = options.bangElement;
            var emptyEl = options.emptyElement;
            var callback = options.callback || function(){};

            this.spinChamber({
                element: chamberEl,
                callback: () => {

                    this.countdown({
                        element: countdownEl,
                        callback: () => {

                            this.fire({
                                bangElement: bangEl,
                                emptyElement: emptyEl,
                                callback: (isFatal) => {
                                    callback(isFatal);
                                }
                            });

                        }
                    });

                }
            });
        }

    };

})(window);
