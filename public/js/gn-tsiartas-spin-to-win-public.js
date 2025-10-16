        /**
         * All of the code for your public-facing JavaScript source
         * should reside in this file.
         *
         * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
 */

(function( $ ) {
        'use strict';

        var STORAGE_PREFIX = 'gn-tsiartas-spin-to-win:';
        var DEFAULT_SPIN_DURATION = 4600;
        var DEFAULT_COLOURS = [
                '#d90429',
                '#ef233c',
                '#ffb703',
                '#2a9d8f',
                '#118ab2',
                '#073b4c',
        ];

        function SpinToWin( $root, config, settings ) {
                this.$root = $root;
                this.config = $.extend( true, {}, config );
                this.settings = settings || {};
                this.$wheel = $root.find( '[data-role="wheel"]' );
                this.$message = $root.find( '[data-role="message-text"]' );
                this.$prizeList = $root.find( '[data-role="prize-list"]' );
                this.$spinButton = $root.find( '[data-action="spin"]' );
                this.$modal = $root.find( '[data-role="result-modal"]' );
                this.$modalTitle = this.$modal.find( '[data-role="modal-title"]' );
                this.$modalMessage = this.$modal.find( '[data-role="modal-message"]' );
                this.storageKey = STORAGE_PREFIX + this.config.id;
                this.baseRotation = 0;
                this.isAnimating = false;
                this.audio = this.prepareAudio( this.config.audio || {} );
                this.state = {
                        hasSpun: false,
                        prizeId: null,
                };
                this.reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

                if ( this.reducedMotion ) {
                        this.spinDuration = 600;
                } else {
                        var configuredDuration = parseInt( this.settings.spinDuration, 10 );
                        if ( isFinite( configuredDuration ) && configuredDuration >= 600 && configuredDuration <= 60000 ) {
                                this.spinDuration = configuredDuration;
                        } else {
                                this.spinDuration = DEFAULT_SPIN_DURATION;
                        }
                }
        }

        SpinToWin.prototype.init = function() {
                if ( ! Array.isArray( this.config.prizes ) || ! this.config.prizes.length ) {
                        return;
                }

                this.calculateWeights();
                this.renderWheel();
                this.highlightAvailablePrizes();
                this.restoreState();
                this.bindEvents();
        };

        SpinToWin.prototype.prepareAudio = function( audioConfig ) {
                var audio = {};

                [ 'spin', 'win', 'lose' ].forEach( function( type ) {
                        var url = audioConfig && audioConfig[ type ];
                        if ( ! url ) {
                                return;
                        }

                        try {
                                var element = new window.Audio( url );
                                element.preload = 'auto';
                                if ( 'spin' === type ) {
                                        element.loop = true;
                                }

                                audio[ type ] = element;
                        } catch ( error ) {
                                // eslint-disable-next-line no-console
                                console.warn( 'Unable to initialise audio for', type, error );
                        }
                } );

                return audio;
        };

        SpinToWin.prototype.playAudio = function( type ) {
                var element = this.audio[ type ];
                if ( ! element ) {
                        return;
                }

                try {
                        element.currentTime = 0;
                        element.play();
                } catch ( error ) {
                        // eslint-disable-next-line no-console
                        console.warn( 'Unable to play audio:', type, error );
                }
        };

        SpinToWin.prototype.stopAudio = function( type ) {
                var element = this.audio[ type ];
                if ( ! element ) {
                        return;
                }

                try {
                        element.pause();
                        element.currentTime = 0;
                } catch ( error ) {
                        // eslint-disable-next-line no-console
                        console.warn( 'Unable to stop audio:', type, error );
                }
        };

        SpinToWin.prototype.calculateWeights = function() {
                var prizes = this.config.prizes;
                var totalWeight = 0;
                var hasFifty = false;
                var hasHundred = false;
                var baseWeights = [];

                prizes.forEach( function( prize, index ) {
                        var parsedWeight = Number( prize.weight );
                        var weight = parsedWeight && parsedWeight > 0 ? parsedWeight : 1;
                        baseWeights[ index ] = weight;

                        var value = extractEuroValue( prize );
                        if ( 100 === value ) {
                                hasHundred = true;
                        } else if ( 50 === value ) {
                                hasFifty = true;
                        }
                } );

                var remainingWeight = 100;
                this.prizeWeights = {};

                if ( hasHundred ) {
                        assignWeight( this, findPrizeIndexByValue( prizes, 100 ), 1 );
                        remainingWeight -= 1;
                }

                if ( hasFifty ) {
                        assignWeight( this, findPrizeIndexByValue( prizes, 50 ), 2 );
                        remainingWeight -= 2;
                }

                if ( remainingWeight < 0 ) {
                        remainingWeight = 0;
                }

                var baseTotal = baseWeights.reduce( function( carry, weight, idx ) {
                        if ( undefined !== this.prizeWeights[ prizes[ idx ].id ] ) {
                                return carry;
                        }

                        return carry + weight;
                }.bind( this ), 0 );

                prizes.forEach( function( prize, index ) {
                        if ( undefined !== this.prizeWeights[ prize.id ] ) {
                                totalWeight += this.prizeWeights[ prize.id ];
                                return;
                        }

                        var proportionalWeight = baseTotal > 0 ? ( baseWeights[ index ] / baseTotal ) * remainingWeight : 0;
                        var normalizedWeight = proportionalWeight || ( remainingWeight / Math.max( 1, prizes.length - Object.keys( this.prizeWeights ).length ) );

                        this.prizeWeights[ prize.id ] = normalizedWeight;
                        totalWeight += normalizedWeight;
                }.bind( this ) );

                if ( totalWeight <= 0 ) {
                        prizes.forEach( function( prize ) {
                                this.prizeWeights[ prize.id ] = 1;
                        }.bind( this ) );
                        totalWeight = prizes.length;
                }

                this.totalWeight = totalWeight;
        };

        function extractEuroValue( prize ) {
                var fields = [ prize.value, prize.label, prize.description ];
                for ( var i = 0; i < fields.length; i++ ) {
                        var field = fields[ i ];
                        if ( ! field || 'string' !== typeof field ) {
                                continue;
                        }

                        var euroMatch = field.match( /€\s*([\d]+)/ ) || field.match( /([\d]+)\s*€/ );
                        if ( euroMatch && euroMatch[ 1 ] ) {
                                return parseInt( euroMatch[ 1 ], 10 );
                        }
                }

                return null;
        }

        function findPrizeIndexByValue( prizes, value ) {
                for ( var i = 0; i < prizes.length; i++ ) {
                        if ( value === extractEuroValue( prizes[ i ] ) ) {
                                return i;
                        }
                }

                return -1;
        }

        function assignWeight( context, index, weight ) {
                if ( index < 0 || index >= context.config.prizes.length ) {
                        return;
                }

                context.prizeWeights[ context.config.prizes[ index ].id ] = weight;
        }

        SpinToWin.prototype.renderWheel = function() {
                var prizes = this.config.prizes;
                var segmentCount = prizes.length;
                var anglePerSegment = 360 / segmentCount;
                this.baseRotation = anglePerSegment / 2;

                var gradientStops = [];
                var currentAngle = 0;
                this.$wheel.empty();

                prizes.forEach( function( prize, index ) {
                        var colour = prize.colour || prize.color || DEFAULT_COLOURS[ index % DEFAULT_COLOURS.length ];
                        var startAngle = currentAngle;
                        var endAngle = currentAngle + anglePerSegment;
                        gradientStops.push( colour + ' ' + startAngle + 'deg ' + endAngle + 'deg' );
                        currentAngle += anglePerSegment;

                        var rotation = anglePerSegment * index + anglePerSegment / 2;
                        var $label = $( '<span>', {
                                class: 'gn-tsiartas-spin-to-win__slice-label',
                                html: '<span>' + escapeHtml( prize.label ) + '</span>',
                        } );

                        $label[ 0 ].style.setProperty( '--slice-rotation', rotation + 'deg' );

                        this.$wheel.append( $label );
                }.bind( this ) );

                var gradient = 'conic-gradient(' + gradientStops.join( ', ' ) + ')';
                this.$wheel.css( {
                        background: gradient,
                        '--rotation-angle': this.baseRotation + 'deg',
                } );

                if ( this.reducedMotion ) {
                        this.$wheel.css( 'transition-duration', '0.6s' );
                }

                this.updateSliceDistance();
        };

        SpinToWin.prototype.updateSliceDistance = function() {
                if ( ! this.$wheel.length ) {
                        return;
                }

                var element = this.$wheel[ 0 ];
                if ( ! element ) {
                        return;
                }

                var width = element.offsetWidth;
                var height = element.offsetHeight;

                if ( ! width || ! height ) {
                        return;
                }

                var radius = Math.min( width, height ) / 2;
                var rootFontSize = parseFloat( window.getComputedStyle( document.documentElement ).fontSize ) || 16;
                var offset = 3 * rootFontSize;
                var sliceDistance = Math.max( radius - offset, 0 );

                element.style.setProperty( '--slice-distance', sliceDistance + 'px' );
        };

        SpinToWin.prototype.highlightAvailablePrizes = function() {
                if ( ! this.$prizeList.length ) {
                        return;
                }

                var colourCount = DEFAULT_COLOURS.length;

                this.$prizeList.find( '.gn-tsiartas-spin-to-win__prize-item' ).each( function( index, element ) {
                        var accent = DEFAULT_COLOURS[ index % colourCount ];
                        element.style.setProperty( '--prize-accent', accent );
                } );
        };

        SpinToWin.prototype.bindEvents = function() {
                var _this = this;

                this.$spinButton.on( 'click', function( event ) {
                        event.preventDefault();
                        _this.handleSpin();
                } );

                this.$modal.on( 'click', function( event ) {
                        if ( event.target === _this.$modal[ 0 ] ) {
                                _this.closeModal();
                        }
                } );

                this.$modal.find( '[data-action="close-modal"]' ).on( 'click', function( event ) {
                        event.preventDefault();
                        _this.closeModal();
                } );

                if ( ! this.boundUpdateSliceDistance ) {
                        this.boundUpdateSliceDistance = this.updateSliceDistance.bind( this );
                        $( window ).on( 'resize', this.boundUpdateSliceDistance );
                        $( window ).on( 'load', this.boundUpdateSliceDistance );
                }
        };

        SpinToWin.prototype.restoreState = function() {
                var persisted = this.readStorage();
                if ( ! persisted || ! persisted.hasSpun || ! persisted.prizeId ) {
                        return;
                }

                this.state = persisted;
                this.$root.addClass( 'has-spun' );
                this.$spinButton.addClass( 'is-disabled' ).attr( 'disabled', 'disabled' );

                var prize = this.findPrizeById( persisted.prizeId );
                if ( ! prize ) {
                        return;
                }

                this.setWheelToPrize( prize );
                this.showResult( prize, { silent: true } );
                this.highlightPrize( prize.id );
        };

        SpinToWin.prototype.readStorage = function() {
                try {
                        var raw = window.sessionStorage.getItem( this.storageKey );
                        return raw ? JSON.parse( raw ) : null;
                } catch ( error ) {
                        return null;
                }
        };

        SpinToWin.prototype.writeStorage = function( data ) {
                try {
                        window.sessionStorage.setItem( this.storageKey, JSON.stringify( data ) );
                } catch ( error ) {
                        // eslint-disable-next-line no-console
                        console.warn( 'Unable to persist spin state', error );
                }
        };

        SpinToWin.prototype.handleSpin = function() {
                if ( this.isAnimating ) {
                        return;
                }

                if ( this.state.hasSpun ) {
                        this.showAlreadyPlayedMessage();
                        return;
                }

                if ( ! this.config.prizes.length ) {
                        return;
                }

                this.isAnimating = true;
                this.$root.addClass( 'is-spinning' );
                this.$spinButton.addClass( 'is-disabled' ).attr( 'disabled', 'disabled' );
                this.playAudio( 'spin' );

                var selectedPrize = this.selectPrize();
                var targetRotation = this.computeTargetRotation( selectedPrize );

                window.requestAnimationFrame( function() {
                        this.$wheel[ 0 ].style.setProperty( '--rotation-angle', targetRotation + 'deg' );
                }.bind( this ) );

                window.setTimeout( function() {
                        this.completeSpin( selectedPrize );
                }.bind( this ), this.spinDuration + 350 );
        };

        SpinToWin.prototype.selectPrize = function() {
                var random = Math.random() * this.totalWeight;
                var accumulator = 0;

                for ( var i = 0; i < this.config.prizes.length; i++ ) {
                        var prize = this.config.prizes[ i ];
                        accumulator += this.prizeWeights[ prize.id ];

                        if ( random <= accumulator ) {
                                return prize;
                        }
                }

                return this.config.prizes[ this.config.prizes.length - 1 ];
        };

        SpinToWin.prototype.computeTargetRotation = function( prize ) {
                var index = this.config.prizes.findIndex( function( item ) {
                        return item.id === prize.id;
                } );

                var segmentCount = this.config.prizes.length;
                var anglePerSegment = 360 / segmentCount;
                var rotations = this.reducedMotion ? 2 : 6 + Math.floor( Math.random() * 3 );
                var randomOffset = ( Math.random() - 0.5 ) * anglePerSegment * ( this.reducedMotion ? 0.2 : 0.4 );

                var targetRotation = rotations * 360 + ( anglePerSegment * index ) + this.baseRotation + randomOffset;
                this.currentRotation = targetRotation % 360;

                return targetRotation;
        };

        SpinToWin.prototype.completeSpin = function( prize ) {
                this.stopAudio( 'spin' );
                this.state = {
                        hasSpun: true,
                        prizeId: prize.id,
                };

                this.writeStorage( this.state );
                this.$root.removeClass( 'is-spinning' ).addClass( 'has-spun' );
                this.showResult( prize );
                this.highlightPrize( prize.id );
                this.triggerIntegrationHook( prize );
                this.isAnimating = false;
        };

        SpinToWin.prototype.setWheelToPrize = function( prize ) {
                var index = -1;
                for ( var i = 0; i < this.config.prizes.length; i++ ) {
                        if ( this.config.prizes[ i ].id === prize.id ) {
                                index = i;
                                break;
                        }
                }

                if ( index < 0 ) {
                        return;
                }

                var anglePerSegment = 360 / this.config.prizes.length;
                var rotation = this.baseRotation + anglePerSegment * index;
                var element = this.$wheel[ 0 ];
                var originalTransition = element.style.transition;

                element.style.transition = 'none';
                element.style.setProperty( '--rotation-angle', rotation + 'deg' );
                // Force reflow so the transition reset applies.
                // eslint-disable-next-line no-unused-expressions
                element.offsetHeight;
                element.style.transition = originalTransition;
        };

        SpinToWin.prototype.showResult = function( prize, options ) {
                options = options || {};
                var tryAgain = this.isTryAgainPrize( prize );
                var messages = this.config.messages || {};
                var template = tryAgain ? messages.lose : messages.win;
                var formattedMessage = template ? formatMessage( template, prize.label ) : tryAgain ? 'Δοκιμάστε ξανά!' : formatMessage( '%s', prize.label );
                var headlineTemplate = tryAgain ? ( messages.tryAgainTitle || messages.lose ) : ( messages.winTitle || messages.win );
                var headline = headlineTemplate ? formatMessage( headlineTemplate, prize.label ) : tryAgain ? 'Δοκιμάστε Ξανά' : 'Συγχαρητήρια!';

                if ( this.$message.length ) {
                        this.$message.text( formattedMessage );
                }

                if ( ! options.silent ) {
                        this.stopAudio( 'win' );
                        this.stopAudio( 'lose' );

                        if ( tryAgain ) {
                                this.playAudio( 'lose' );
                        } else {
                                this.playAudio( 'win' );
                        }

                        this.openModal( headline, formattedMessage );
                }
        };

        SpinToWin.prototype.showAlreadyPlayedMessage = function() {
                var alreadyMessage = this.config.messages && this.config.messages.alreadyPlayed ? this.config.messages.alreadyPlayed : 'Έχετε ήδη παίξει σήμερα. Επισκεφθείτε μας ξανά σύντομα!';
                if ( this.$message.length ) {
                        this.$message.text( alreadyMessage );
                }

                this.openModal( 'Ήδη παίξατε', alreadyMessage );
        };

        SpinToWin.prototype.highlightPrize = function( prizeId ) {
                if ( ! this.$prizeList.length ) {
                        return;
                }

                this.$prizeList.find( '.gn-tsiartas-spin-to-win__prize-item' ).removeClass( 'is-active' );
                this.$prizeList.find( '[data-prize-id="' + prizeId + '"]' ).addClass( 'is-active' );
        };

        SpinToWin.prototype.findPrizeById = function( prizeId ) {
                for ( var i = 0; i < this.config.prizes.length; i++ ) {
                        if ( this.config.prizes[ i ].id === prizeId ) {
                                return this.config.prizes[ i ];
                        }
                }

                return null;
        };

        SpinToWin.prototype.isTryAgainPrize = function( prize ) {
                var keywords = [ 'try again', 'better luck', 'thank you', 'no prize', 'δοκιμάστε', 'ξανά', 'ευχαριστούμε' ];
                var haystack = ( ( prize.label || '' ) + ' ' + ( prize.description || '' ) ).toLowerCase();

                return keywords.some( function( keyword ) {
                        return haystack.indexOf( keyword ) !== -1;
                } );
        };

        SpinToWin.prototype.triggerIntegrationHook = function( prize ) {
                var eventData = {
                        instanceId: this.config.id,
                        prize: prize,
                        timestamp: Date.now(),
                };

                this.$root.trigger( 'gnTsiartasSpinToWin:prizeAwarded', [ eventData ] );

                if ( window.CustomEvent ) {
                        var customEvent = new window.CustomEvent( 'gnTsiartasSpinToWin:prize', {
                                detail: eventData,
                        } );
                        window.dispatchEvent( customEvent );
                }
        };

        SpinToWin.prototype.openModal = function( title, message ) {
                if ( ! this.$modal.length ) {
                        return;
                }

                this.$modalTitle.text( title );
                this.$modalMessage.text( message );
                this.$modal.attr( 'aria-hidden', 'false' ).addClass( 'is-open' );

                window.requestAnimationFrame( function() {
                        this.$modal.find( '.gn-tsiartas-spin-to-win__modal-close' ).trigger( 'focus' );
                }.bind( this ) );
        };

        SpinToWin.prototype.closeModal = function() {
                if ( ! this.$modal.length ) {
                        return;
                }

                this.$modal.attr( 'aria-hidden', 'true' ).removeClass( 'is-open' );
                this.stopAudio( 'win' );
                this.stopAudio( 'lose' );
        };

        function formatMessage( template, label ) {
                if ( ! template ) {
                        return label;
                }

                if ( template.indexOf( '%s' ) !== -1 ) {
                        return template.replace( '%s', label );
                }

                return template;
        }

        function escapeHtml( value ) {
                if ( ! value ) {
                        return '';
                }

                return value
                        .replace( /&/g, '&amp;' )
                        .replace( /</g, '&lt;' )
                        .replace( />/g, '&gt;' )
                        .replace( /"/g, '&quot;' )
                        .replace( /'/g, '&#039;' );
        }

        function bootstrap() {
                if ( 'undefined' === typeof window.gnTsiartasSpinToWinConfig ) {
                        return;
                }

                var config = window.gnTsiartasSpinToWinConfig;
                var settings = config.settings || {};
                var instances = config.instances || {};

                Object.keys( instances ).forEach( function( id ) {
                        var instanceConfig = instances[ id ];
                        var $root = $( '[data-gn-tsiartas-spin-instance="' + id + '"]' );
                        if ( ! $root.length ) {
                                return;
                        }

                        var instance = new SpinToWin( $root, instanceConfig, settings );
                        instance.init();
                } );
        }

        $( bootstrap );
})( jQuery );
