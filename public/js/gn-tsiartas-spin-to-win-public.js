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
                '#ff3b30',
                '#ff9500',
                '#ffd60a',
                '#34c759',
                '#00c7be',
                '#0a84ff',
                '#5856d6',
                '#af52de',
                '#ff2d55',
        ];
        var POINTER_ALIGNMENT_OFFSET = 90;
        var DEFAULT_TRY_AGAIN_PHRASES = [
                'try again',
                'better luck',
                'thank you',
                'no prize',
                'δοκιμάστε',
                'ξανά',
                'ευχαριστούμε',
        ];

        function slugifyIdentifier( value ) {
                if ( ! value && 0 !== value ) {
                        return '';
                }

                var stringValue = String( value );

                if ( stringValue.normalize ) {
                        stringValue = stringValue.normalize( 'NFD' ).replace( /[\u0300-\u036f]/g, '' );
                }

                stringValue = stringValue.replace( /([a-z0-9])([A-Z])/g, '$1-$2' );
                stringValue = stringValue.replace( /[^a-zA-Z0-9]+/g, '-' );
                stringValue = stringValue.toLowerCase();
                stringValue = stringValue.replace( /^-+|-+$/g, '' );

                return stringValue;
        }

        function toBoolean( value ) {
                if ( 'boolean' === typeof value ) {
                        return value;
                }

                if ( 'number' === typeof value ) {
                        return value !== 0;
                }

                if ( 'string' === typeof value ) {
                        var lowered = value.toLowerCase().trim();

                        if ( [ '1', 'true', 'yes', 'on' ].indexOf( lowered ) !== -1 ) {
                                return true;
                        }

                        if ( [ '0', 'false', 'no', 'off' ].indexOf( lowered ) !== -1 ) {
                                return false;
                        }
                }

                return !! value;
        }

        function normaliseTryAgainMatchers( matchers ) {
                var phrases = DEFAULT_TRY_AGAIN_PHRASES.slice();
                var slugs = { 'try-again': true };

                if ( matchers && 'object' === typeof matchers ) {
                        if ( Array.isArray( matchers.phrases ) ) {
                                phrases = phrases.concat( matchers.phrases );
                        }

                        if ( matchers.slugs && 'object' === typeof matchers.slugs ) {
                                Object.keys( matchers.slugs ).forEach( function( key ) {
                                        var slug = slugifyIdentifier( key );
                                        if ( slug ) {
                                                slugs[ slug ] = true;
                                        }
                                } );
                        }
                }

                var seen = {};
                var normalisedPhrases = [];

                phrases.forEach( function( phrase ) {
                        if ( ! phrase && 0 !== phrase ) {
                                return;
                        }

                        var trimmed = String( phrase ).trim();

                        if ( ! trimmed ) {
                                return;
                        }

                        var lowered = trimmed.toLowerCase();

                        if ( seen[ lowered ] ) {
                                return;
                        }

                        seen[ lowered ] = true;
                        normalisedPhrases.push( lowered );

                        var slug = slugifyIdentifier( trimmed );
                        if ( slug ) {
                                slugs[ slug ] = true;
                        }
                } );

                if ( ! seen['try again'] ) {
                        normalisedPhrases.push( 'try again' );
                        slugs['try-again'] = true;
                }

                return {
                        phrases: normalisedPhrases,
                        slugs: slugs,
                };
        }

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
                this.$currentDate = $root.find( '[data-role="current-date"]' );
                this.$modalDate = this.$modal.find( '[data-role="modal-date"]' );
                this.$desktopNotice = $root.find( '[data-role="desktop-notice"]' );
                this.storageKey = STORAGE_PREFIX + this.config.id;
                this.baseRotation = 0;
                this.isAnimating = false;
                this.rotationOffset = POINTER_ALIGNMENT_OFFSET;
                this.audio = this.prepareAudio( this.config.audio || {} );
                this.state = {
                        hasSpun: false,
                        prizeId: null,
                        serverData: null,
                };
                this.desktopRestrictionAddedDisable = false;
                this.reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
                this.pendingSpinData = null;
                this.tryAgainMatchers = normaliseTryAgainMatchers( this.settings.tryAgainMatchers );

                var configuredDuration = DEFAULT_SPIN_DURATION;
                var providedDuration = parseInt( this.settings && this.settings.spinDuration, 10 );

                if ( ! isNaN( providedDuration ) && providedDuration >= 1000 ) {
                        configuredDuration = providedDuration;
                }

                this.spinDuration = this.reducedMotion ? 600 : configuredDuration;

                this.normalisePrizeConfig();

        }

        SpinToWin.prototype.init = function() {

                if ( ! Array.isArray( this.config.prizes ) || ! this.config.prizes.length ) {
                        return;
                }

                this.updateCurrentDate();
                this.renderWheel();
                this.highlightAvailablePrizes();
                this.restoreState();
                this.bindEvents();
                this.enforceDeviceAvailability();
        };

        SpinToWin.prototype.normalisePrizeConfig = function() {

                if ( ! Array.isArray( this.config.prizes ) ) {
                        this.config.prizes = [];
                        return;
                }

                var usedIds = {};
                var matchers = this.getTryAgainMatchers();

                this.config.prizes.forEach( function( prize, index ) {
                        if ( ! prize || 'object' !== typeof prize ) {
                                this.config.prizes[ index ] = {};
                                prize = this.config.prizes[ index ];
                        }

                        var id = prize.id ? String( prize.id ).trim() : '';
                        if ( ! id ) {
                                id = 'prize-' + ( index + 1 );
                        }

                        var baseId = id;
                        var suffix = 2;
                        while ( usedIds[ id ] ) {
                                id = baseId + '-' + suffix;
                                suffix++;
                        }

                        usedIds[ id ] = true;
                        prize.id = id;

                        var typeSlug = prize.type ? this.normalizePrizeType( prize.type ) : '';
                        prize.type = typeSlug;

                        var explicitTryAgain;
                        if ( Object.prototype.hasOwnProperty.call( prize, 'isTryAgain' ) ) {
                                explicitTryAgain = toBoolean( prize.isTryAgain );
                        } else if ( Object.prototype.hasOwnProperty.call( prize, 'is_try_again' ) ) {
                                explicitTryAgain = toBoolean( prize.is_try_again );
                        }

                        var explicitVoucher;
                        if ( Object.prototype.hasOwnProperty.call( prize, 'isVoucher' ) ) {
                                explicitVoucher = toBoolean( prize.isVoucher );
                        } else if ( Object.prototype.hasOwnProperty.call( prize, 'is_voucher' ) ) {
                                explicitVoucher = toBoolean( prize.is_voucher );
                        }

                        var normalizedTryAgain = 'boolean' === typeof explicitTryAgain ? explicitTryAgain : false;

                        if ( ! normalizedTryAgain && typeSlug && matchers.slugs[ typeSlug ] ) {
                                normalizedTryAgain = true;
                        }

                        if ( ! normalizedTryAgain ) {
                                var idSlug = slugifyIdentifier( prize.id );
                                if ( idSlug && matchers.slugs[ idSlug ] ) {
                                        normalizedTryAgain = true;
                                }
                        }

                        if ( ! normalizedTryAgain ) {
                                var haystack = ( ( prize.label || '' ) + ' ' + ( prize.description || '' ) ).toLowerCase();
                                normalizedTryAgain = matchers.phrases.some( function( phrase ) {
                                        return phrase && haystack.indexOf( phrase ) !== -1;
                                } );
                        }

                        var normalizedVoucher = 'boolean' === typeof explicitVoucher ? explicitVoucher : false;

                        if ( normalizedTryAgain ) {
                                normalizedVoucher = false;
                        } else if ( 'boolean' !== typeof explicitVoucher ) {
                                var numericValue = extractEuroValue( prize );
                                if ( null !== numericValue ) {
                                        normalizedVoucher = true;
                                } else if ( null !== prize.denomination && prize.denomination !== undefined ) {
                                        var parsedDenomination = parseInt( prize.denomination, 10 );
                                        if ( ! isNaN( parsedDenomination ) ) {
                                                normalizedVoucher = true;
                                        }
                                }
                        }

                        if ( normalizedTryAgain && ! typeSlug ) {
                                prize.type = 'try-again';
                        } else if ( normalizedVoucher && ! typeSlug ) {
                                prize.type = 'voucher';
                        }

                        prize.isTryAgain = normalizedTryAgain;
                        prize.is_try_again = normalizedTryAgain;
                        prize.isVoucher = normalizedVoucher;
                        prize.is_voucher = normalizedVoucher;
                }, this );
        };

        SpinToWin.prototype.getTryAgainMatchers = function() {

                if ( ! this.tryAgainMatchers ) {
                        this.tryAgainMatchers = normaliseTryAgainMatchers();
                }

                return this.tryAgainMatchers;
        };

        SpinToWin.prototype.normalizePrizeType = function( type ) {

                return slugifyIdentifier( type );
        };

        SpinToWin.prototype.getLocalizedDate = function() {
                var dateText = this.settings && this.settings.formattedDate ? String( this.settings.formattedDate ) : '';
                return dateText.trim();
        };

        SpinToWin.prototype.updateCurrentDate = function( dateText ) {
                if ( ! this.$currentDate.length ) {
                        return;
                }

                var resolved = dateText || this.getLocalizedDate();

                if ( resolved ) {
                        this.$currentDate.text( resolved );
                }
        };

        SpinToWin.prototype.updateModalDate = function( dateText ) {
                if ( ! this.$modalDate.length ) {
                        return;
                }

                var resolved = dateText || this.getLocalizedDate();

                if ( resolved ) {
                        this.$modalDate.text( resolved ).attr( 'aria-hidden', 'false' );
                } else {
                        this.$modalDate.text( '' ).attr( 'aria-hidden', 'true' );
                }
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
                        // Intentionally ignore errors to keep the experience smooth.
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
                // Intentionally ignore errors to keep the experience smooth.
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
                // Intentionally ignore errors to keep the experience smooth.
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
                var alignmentOffset = this.rotationOffset || 0;
                this.baseRotation = anglePerSegment / 2 - alignmentOffset;

                var gradientStops = [];
                var currentAngle = 0;
                var $preservedHub = this.$wheel.find( '[data-role="wheel-hub"]' ).first();
                var _this = this;

                if ( $preservedHub.length ) {
                        $preservedHub = $preservedHub.detach();
                } else {
                        $preservedHub = null;
                }

                this.$wheel.empty();

                prizes.forEach( function( prize, index ) {
                        var colour = prize.colour || prize.color || DEFAULT_COLOURS[ index % DEFAULT_COLOURS.length ];
                        var startAngle = currentAngle - alignmentOffset;
                        var endAngle = startAngle + anglePerSegment;
                        gradientStops.push( colour + ' ' + startAngle + 'deg ' + endAngle + 'deg' );
                        currentAngle += anglePerSegment;

                        var rotation = anglePerSegment * index + anglePerSegment / 2;
                        var labelText = 'string' === typeof prize.label ? prize.label : '';
                        var icon = prize.icon;
                        if ( ! icon && _this.isTryAgainPrize( prize ) ) {
                                icon = '✖';
                        }

                        var artworkUrl = resolveSliceArtwork( prize );
                        var hasArt = !! artworkUrl;
                        var hasIcon = !! icon && ! hasArt;
                        var labelClass = 'gn-tsiartas-spin-to-win__slice-label';
                        if ( hasIcon ) {
                                labelClass += ' gn-tsiartas-spin-to-win__slice-label--has-icon';
                        }
                        if ( hasArt ) {
                                labelClass += ' gn-tsiartas-spin-to-win__slice-label--has-art';
                        }

                        if ( hasIcon && 'string' === typeof icon ) {
                                icon = icon.trim();
                        }

                        var labelHtml = '<span class="gn-tsiartas-spin-to-win__slice-label-content">';
                        if ( hasArt ) {
                                labelHtml += '<span class="gn-tsiartas-spin-to-win__slice-label-art" aria-hidden="true"></span>';
                        }
                        if ( hasIcon ) {
                                labelHtml +=
                                        '<span class="gn-tsiartas-spin-to-win__slice-label-icon" aria-hidden="true">' +
                                        escapeHtml( icon ) +
                                        '</span>';
                        }
                        labelHtml +=
                                '<span class="gn-tsiartas-spin-to-win__slice-label-text">' +
                                escapeHtml( labelText ) +
                                '</span>';
                        labelHtml += '</span>';

                        var $label = $( '<span>', {
                                class: labelClass,
                                html: labelHtml,
                        } );

                        $label.attr( 'data-slice-id', prize.id || ( 'slice-' + ( index + 1 ) ) );
                        $label.attr( 'data-slice-index', index );
                        var sliceType = this.normalizePrizeType( prize.type );
                        if ( sliceType ) {
                                $label.attr( 'data-slice-type', sliceType );
                        }
                        if ( this.isTryAgainPrize( prize ) ) {
                                $label.attr( 'data-slice-try-again', 'true' );
                        }
                        if ( this.isVoucherPrize( prize ) ) {
                                $label.attr( 'data-slice-voucher', 'true' );
                        }
                        $label[ 0 ].style.setProperty( '--slice-rotation', rotation + 'deg' );
                        if ( hasArt ) {
                                $label[ 0 ].style.setProperty( '--slice-art', formatCssUrl( artworkUrl ) );
                        }

                        this.$wheel.append( $label );
                }.bind( this ) );

                if ( $preservedHub ) {
                        this.$wheel.append( $preservedHub );
                }

                var gradient = 'conic-gradient(' + gradientStops.join( ', ' ) + ')';
                if ( this.$wheel.length ) {
                        this.$wheel[ 0 ].style.setProperty( '--gn-tsiartas-slice-gradient', gradient );
                        this.$wheel[ 0 ].style.setProperty( '--rotation-angle', ( this.baseRotation + alignmentOffset ) + 'deg' );
                        this.$wheel[ 0 ].style.setProperty( '--gn-tsiartas-spin-duration', this.spinDuration + 'ms' );
                }

                this.setupWheelResizeHandling();

        };

        SpinToWin.prototype.setupWheelResizeHandling = function() {
                if ( ! this.$wheel.length ) {
                        return;
                }

                var _this = this;

                this.updateWheelLayout();

                if ( window.requestAnimationFrame ) {
                        window.requestAnimationFrame( function() {
                                _this.updateWheelLayout();
                        } );
                }

                if ( 'ResizeObserver' in window ) {
                        if ( this.wheelResizeObserver ) {
                                this.wheelResizeObserver.disconnect();
                        }

                        this.wheelResizeObserver = new window.ResizeObserver( function() {
                                _this.updateWheelLayout();
                        } );

                        this.wheelResizeObserver.observe( this.$wheel[ 0 ] );
                        return;
                }

                if ( this.boundResizeHandler ) {
                        $( window ).off( 'resize.gnSpinToWin-' + this.config.id, this.boundResizeHandler );
                }

                this.boundResizeHandler = function() {
                        window.requestAnimationFrame( function() {
                                _this.updateWheelLayout();
                        } );
                };

                $( window ).on( 'resize.gnSpinToWin-' + this.config.id, this.boundResizeHandler );
        };

        SpinToWin.prototype.updateWheelLayout = function() {
                if ( ! this.$wheel.length ) {
                        return;
                }

                var wheelElement = this.$wheel[ 0 ];
                var diameter = wheelElement.getBoundingClientRect().width;

                if ( ! diameter ) {
                        return;
                }

                var radius = diameter / 2;
                var labelDistance = radius * 0.68;
                var fontSize = Math.max( 12, Math.min( 16, diameter * 0.04 ) );

                wheelElement.style.setProperty( '--slice-distance', labelDistance + 'px' );
                wheelElement.style.setProperty( '--slice-label-width', '37.261755px' );
                wheelElement.style.setProperty( '--slice-font-size', fontSize + 'px' );
        };

        SpinToWin.prototype.highlightAvailablePrizes = function() {

                if ( ! this.$prizeList.length ) {
                        return;
                }

                var colourCount = DEFAULT_COLOURS.length;
                var _this = this;

                this.$prizeList.find( '.gn-tsiartas-spin-to-win__prize-item' ).each( function( index, element ) {
                        var prize = _this.config.prizes[ index ] || {};
                        var accent = prize.colour || prize.color || DEFAULT_COLOURS[ index % colourCount ];
                        if ( prize.id ) {
                                element.setAttribute( 'data-prize-id', prize.id );
                        } else {
                                element.removeAttribute( 'data-prize-id' );
                        }
                        var typeSlug = _this.normalizePrizeType( prize.type );
                        if ( typeSlug ) {
                                element.setAttribute( 'data-prize-type', typeSlug );
                        } else {
                                element.removeAttribute( 'data-prize-type' );
                        }
                        if ( _this.isTryAgainPrize( prize ) ) {
                                element.setAttribute( 'data-prize-try-again', 'true' );
                        } else {
                                element.removeAttribute( 'data-prize-try-again' );
                        }
                        if ( _this.isVoucherPrize( prize ) ) {
                                element.setAttribute( 'data-prize-voucher', 'true' );
                        } else {
                                element.removeAttribute( 'data-prize-voucher' );
                        }
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
        };

        SpinToWin.prototype.enforceDeviceAvailability = function() {
                if ( ! this.$desktopNotice || ! this.$desktopNotice.length ) {
                        return;
                }

                var _this = this;

                var updateNotice = function() {
                        var isMobile = _this.isLikelyMobileDevice();

                        if ( isMobile ) {
                                _this.$root.removeClass( 'is-desktop-restricted' );
                                _this.$desktopNotice.attr( 'aria-hidden', 'true' );

                                if ( _this.desktopRestrictionAddedDisable && ! _this.state.hasSpun ) {
                                        _this.$spinButton.removeClass( 'is-disabled' ).prop( 'disabled', false ).removeAttr( 'disabled' );
                                }

                                _this.desktopRestrictionAddedDisable = false;
                                return;
                        }

                        _this.$root.addClass( 'is-desktop-restricted' );
                        _this.$desktopNotice.attr( 'aria-hidden', 'false' );

                        var alreadyDisabled = _this.$spinButton.is( ':disabled' ) || _this.$spinButton.hasClass( 'is-disabled' );

                        if ( alreadyDisabled ) {
                                _this.desktopRestrictionAddedDisable = false;
                                return;
                        }

                        _this.desktopRestrictionAddedDisable = true;
                        _this.$spinButton.addClass( 'is-disabled' ).prop( 'disabled', true ).attr( 'disabled', 'disabled' );
                };

                updateNotice();

                $( window ).on( 'resize.gnTsiartasSpinToWin orientationchange.gnTsiartasSpinToWin', function() {
                        updateNotice();
                } );
        };

        SpinToWin.prototype.isLikelyMobileDevice = function() {
                var coarsePointer = false;

                if ( window.matchMedia ) {
                        try {
                                coarsePointer = window.matchMedia( '(pointer: coarse)' ).matches;
                        } catch ( error ) {
                                coarsePointer = false;
                        }
                }

                if ( coarsePointer ) {
                        return true;
                }

                var userAgent = navigator.userAgent || navigator.vendor || ( window.opera && window.opera.toString() ) || '';

                return /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test( userAgent );
        };

        SpinToWin.prototype.restoreState = function() {

                var persisted = this.readStorage();
                if ( ! persisted || ! persisted.hasSpun || ! persisted.prizeId ) {
                        return;
                }

                var prize = this.findPrizeById( persisted.prizeId );
                if ( ! prize ) {

                        this.clearStorage();
                        this.state = {
                                hasSpun: false,
                                prizeId: null,
                                serverData: null,
                        };

                        this.$root.removeClass( 'has-spun' );
                        this.$spinButton.removeClass( 'is-disabled' ).removeAttr( 'disabled' );
                        return;
                }

                this.state = persisted;
                if ( 'undefined' === typeof this.state.serverData ) {
                        this.state.serverData = null;
                }
                this.$root.addClass( 'has-spun' );
                this.$spinButton.addClass( 'is-disabled' ).attr( 'disabled', 'disabled' );

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
                // Intentionally ignore errors to keep the experience smooth.
                }
        };

        SpinToWin.prototype.clearStorage = function() {
                try {
                        window.sessionStorage.removeItem( this.storageKey );
                } catch ( error ) {
                // Intentionally ignore errors to keep the experience smooth.
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

                this.requestSpinResult();
        };

        SpinToWin.prototype.requestSpinResult = function() {
                var _this = this;

                $.ajax( {
                        url: this.settings.ajaxUrl,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                                action: 'gn_tsiartas_spin_to_win_spin',
                                nonce: this.settings.nonce,
                                instance: this.config.id,
                        },
                } )
                        .done( function( response ) {
                                if ( response && true === response.success && response.data ) {
                                        _this.processSpinSuccess( response.data );
                                        return;
                                }

                                var payload = ( response && response.data ) || {};
                                if ( response && response.message && ! payload.message ) {
                                        payload.message = response.message;
                                }
                                _this.handleSpinError( payload );
                        } )
                        .fail( function( jqXHR ) {
                                var payload = {};

                                if ( jqXHR && jqXHR.responseJSON ) {
                                        payload = jqXHR.responseJSON.data || {};
                                        if ( jqXHR.responseJSON.message ) {
                                                payload.message = jqXHR.responseJSON.message;
                                        }
                                }

                                _this.handleSpinError( payload );
                        } );
        };

        SpinToWin.prototype.processSpinSuccess = function( data ) {
                data = data || {};

                var matchers = this.getTryAgainMatchers();
                var normalizedType = data.type ? this.normalizePrizeType( data.type ) : '';
                var prize = this.findPrizeById( data.prizeId );

                if ( ! prize && normalizedType ) {
                        prize = this.findPrizeByType( normalizedType );
                }

                var serverTryAgain = null;
                if ( Object.prototype.hasOwnProperty.call( data, 'isTryAgain' ) ) {
                        serverTryAgain = toBoolean( data.isTryAgain );
                } else if ( Object.prototype.hasOwnProperty.call( data, 'is_try_again' ) ) {
                        serverTryAgain = toBoolean( data.is_try_again );
                }

                var serverVoucher = null;
                if ( Object.prototype.hasOwnProperty.call( data, 'isVoucher' ) ) {
                        serverVoucher = toBoolean( data.isVoucher );
                } else if ( Object.prototype.hasOwnProperty.call( data, 'is_voucher' ) ) {
                        serverVoucher = toBoolean( data.is_voucher );
                }

                var targetDenomination = null;
                if ( Object.prototype.hasOwnProperty.call( data, 'awardedDenomination' ) && data.awardedDenomination !== null && data.awardedDenomination !== undefined ) {
                        var awardValue = data.awardedDenomination;
                        var parsedAward = parseInt( awardValue, 10 );
                        if ( ! isNaN( parsedAward ) ) {
                                targetDenomination = parsedAward;
                        } else {
                                var awardMatch = String( awardValue ).match( /(\d+)/ );
                                if ( awardMatch && awardMatch[ 1 ] ) {
                                        targetDenomination = parseInt( awardMatch[ 1 ], 10 );
                                }
                        }
                }

                if ( null === targetDenomination && Object.prototype.hasOwnProperty.call( data, 'value' ) && data.value !== null && data.value !== undefined ) {
                        var parsedValue = parseInt( data.value, 10 );
                        if ( ! isNaN( parsedValue ) ) {
                                targetDenomination = parsedValue;
                        } else {
                                var valueMatch = String( data.value ).match( /(\d+)/ );
                                if ( valueMatch && valueMatch[ 1 ] ) {
                                        targetDenomination = parseInt( valueMatch[ 1 ], 10 );
                                }
                        }
                }

                if ( ! prize && serverTryAgain === true ) {
                        prize = this.findFirstTryAgainPrize();
                }

                if ( ! prize && normalizedType && matchers.slugs[ normalizedType ] ) {
                        prize = this.findFirstTryAgainPrize();
                }

                if ( ! prize && ( serverVoucher === true || normalizedType === 'voucher' ) ) {
                        prize = this.findFirstVoucherPrize( targetDenomination );
                }

                if ( ! prize && normalizedType && ! matchers.slugs[ normalizedType ] && normalizedType !== 'voucher' ) {
                        prize = this.findPrizeByType( normalizedType );
                }

                if ( ! prize ) {
                        prize = this.findFirstTryAgainPrize() || this.findFirstVoucherPrize( targetDenomination ) || this.config.prizes[ 0 ] || {};
                }

                var resolvedPrize = $.extend( true, {}, prize );

                if ( data.formattedDate ) {
                        this.settings.formattedDate = data.formattedDate;
                        this.updateCurrentDate( data.formattedDate );
                }

                if ( data.label ) {
                        resolvedPrize.label = data.label;
                }

                if ( data.description ) {
                        resolvedPrize.description = data.description;
                }

                var baseTryAgain = this.isTryAgainPrize( resolvedPrize );
                var baseVoucher = this.isVoucherPrize( resolvedPrize );

                resolvedPrize.isTryAgain = baseTryAgain;
                resolvedPrize.is_try_again = baseTryAgain;
                resolvedPrize.isVoucher = baseVoucher;
                resolvedPrize.is_voucher = baseVoucher;

                var resolvedType = this.normalizePrizeType( resolvedPrize.type );

                if ( normalizedType ) {
                        resolvedType = normalizedType;
                }

                if ( serverTryAgain !== null ) {
                        resolvedPrize.isTryAgain = serverTryAgain;
                        resolvedPrize.is_try_again = serverTryAgain;

                        if ( serverTryAgain ) {
                                resolvedPrize.isVoucher = false;
                                resolvedPrize.is_voucher = false;
                                resolvedType = 'try-again';
                        } else if ( resolvedType && matchers.slugs[ resolvedType ] ) {
                                resolvedType = '';
                        }
                }

                if ( serverVoucher !== null ) {
                        resolvedPrize.isVoucher = serverVoucher;
                        resolvedPrize.is_voucher = serverVoucher;

                        if ( serverVoucher && resolvedType !== 'try-again' ) {
                                resolvedType = resolvedType || 'voucher';
                        } else if ( ! serverVoucher && 'voucher' === resolvedType ) {
                                resolvedType = '';
                        }
                }

                if ( ! resolvedType ) {
                        if ( resolvedPrize.isTryAgain ) {
                                resolvedType = 'try-again';
                        } else if ( resolvedPrize.isVoucher ) {
                                resolvedType = 'voucher';
                        }
                }

                resolvedPrize.type = resolvedType;

                var finalTryAgain = this.isTryAgainPrize( resolvedPrize );
                var finalVoucher = this.isVoucherPrize( resolvedPrize );

                resolvedPrize.isTryAgain = finalTryAgain;
                resolvedPrize.is_try_again = finalTryAgain;
                resolvedPrize.isVoucher = finalVoucher;
                resolvedPrize.is_voucher = finalVoucher;

                if ( ! resolvedPrize.type ) {
                        if ( finalTryAgain ) {
                                resolvedPrize.type = 'try-again';
                        } else if ( finalVoucher ) {
                                resolvedPrize.type = 'voucher';
                        }
                }

                resolvedPrize.serverData = data;
                this.pendingSpinData = {
                        prize: resolvedPrize,
                        serverData: data,
                };

                var targetRotation = this.computeTargetRotation( resolvedPrize );

                window.requestAnimationFrame( function() {
                        if ( ! this.$wheel.length ) {
                                return;
                        }

                        this.$wheel[ 0 ].style.setProperty( '--rotation-angle', targetRotation + 'deg' );
                }.bind( this ) );

                window.setTimeout( function() {
                        this.completeSpin( resolvedPrize, data );
                }.bind( this ), this.spinDuration + 350 );
        };

        SpinToWin.prototype.handleSpinError = function( payload ) {
                payload = payload || {};

                this.stopAudio( 'spin' );
                this.isAnimating = false;
                this.$root.removeClass( 'is-spinning' );
                this.pendingSpinData = null;

                var messages = this.config.messages || {};
                var isDepleted = !! payload.depleted;
                var message;
                var title;

                if ( isDepleted ) {
                        message = messages.depleted || payload.message || 'Όλες οι δωροεπιταγές έχουν διατεθεί για σήμερα.';
                        title = messages.depletedTitle || 'Δεν υπάρχουν διαθέσιμα δώρα';
                        this.$spinButton.addClass( 'is-disabled' ).attr( 'disabled', 'disabled' );
                } else {
                        message = payload.message || messages.error || 'Η κλήρωση δεν ήταν διαθέσιμη. Προσπαθήστε ξανά αργότερα.';
                        title = messages.errorTitle || 'Προσωρινό ζήτημα';

                        if ( ! this.state.hasSpun ) {
                                this.$spinButton.removeClass( 'is-disabled' ).prop( 'disabled', false ).removeAttr( 'disabled' );
                        }
                }

                if ( this.$message.length ) {
                        this.$message.text( message );
                }

                this.openModal( title, message );
        };

        SpinToWin.prototype.computeTargetRotation = function( prize ) {
                var index = this.config.prizes.findIndex( function( item ) {
                        return item.id === prize.id;
                } );

                var segmentCount = this.config.prizes.length;
                var anglePerSegment = 360 / segmentCount;
                var rotations = this.reducedMotion ? 2 : 6 + Math.floor( Math.random() * 3 );
                var randomOffset = ( Math.random() - 0.5 ) * anglePerSegment * ( this.reducedMotion ? 0.2 : 0.4 );

                var alignmentOffset = this.rotationOffset || 0;
                var targetRotation = rotations * 360 + ( anglePerSegment * index ) + this.baseRotation + alignmentOffset + randomOffset;
                this.currentRotation = targetRotation % 360;


                return targetRotation;
        };

        SpinToWin.prototype.completeSpin = function( prize, serverData ) {

                this.stopAudio( 'spin' );
                this.state = {
                        hasSpun: true,
                        prizeId: prize.id,
                        serverData: serverData || null,
                };

                this.writeStorage( this.state );
                this.$root.removeClass( 'is-spinning' ).addClass( 'has-spun' );
                this.showResult( prize );
                this.highlightPrize( prize.id );
                this.triggerIntegrationHook( prize, serverData );
                this.pendingSpinData = null;
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
                var alignmentOffset = this.rotationOffset || 0;
                var rotation = this.baseRotation + alignmentOffset + anglePerSegment * index;
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

                        var modalDate = ( prize.serverData && prize.serverData.formattedDate ) || this.getLocalizedDate();
                        this.openModal( headline, formattedMessage, modalDate );
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

        SpinToWin.prototype.findPrizeByType = function( type ) {

                if ( ! type && 0 !== type ) {
                        return null;
                }

                var normalized = this.normalizePrizeType( type );

                if ( ! normalized ) {
                        return null;
                }

                var matchers = this.getTryAgainMatchers();
                var treatAsTryAgain = !! matchers.slugs[ normalized ];

                for ( var i = 0; i < this.config.prizes.length; i++ ) {
                        var prize = this.config.prizes[ i ];
                        var prizeType = this.normalizePrizeType( prize.type );

                        if ( prizeType && prizeType === normalized ) {
                                return prize;
                        }

                        if ( treatAsTryAgain && this.isTryAgainPrize( prize ) ) {
                                return prize;
                        }
                }

                return null;
        };

        SpinToWin.prototype.findPrizeById = function( prizeId ) {

                for ( var i = 0; i < this.config.prizes.length; i++ ) {
                        if ( this.config.prizes[ i ].id === prizeId ) {
                                return this.config.prizes[ i ];
                        }
                }

                return null;
        };

        SpinToWin.prototype.findFirstTryAgainPrize = function() {

                for ( var i = 0; i < this.config.prizes.length; i++ ) {
                        if ( this.isTryAgainPrize( this.config.prizes[ i ] ) ) {
                                return this.config.prizes[ i ];
                        }
                }

                return null;
        };

        SpinToWin.prototype.findFirstVoucherPrize = function( denomination ) {

                var target = null;

                if ( denomination !== undefined && denomination !== null ) {
                        var parsed = parseInt( denomination, 10 );
                        if ( ! isNaN( parsed ) ) {
                                target = parsed;
                        }
                }

                var fallback = null;

                for ( var i = 0; i < this.config.prizes.length; i++ ) {
                        var prize = this.config.prizes[ i ];

                        if ( ! this.isVoucherPrize( prize ) ) {
                                continue;
                        }

                        if ( null === fallback ) {
                                fallback = prize;
                        }

                        if ( null === target ) {
                                continue;
                        }

                        var prizeValue = this.getPrizeDenomination( prize );

                        if ( prizeValue === target ) {
                                return prize;
                        }
                }

                return fallback;
        };

        SpinToWin.prototype.isTryAgainPrize = function( prize ) {
                if ( ! prize ) {
                        return false;
                }

                if ( Object.prototype.hasOwnProperty.call( prize, 'isTryAgain' ) ) {
                        return toBoolean( prize.isTryAgain );
                }

                if ( Object.prototype.hasOwnProperty.call( prize, 'is_try_again' ) ) {
                        return toBoolean( prize.is_try_again );
                }

                var matchers = this.getTryAgainMatchers();
                var typeSlug = this.normalizePrizeType( prize.type );

                if ( typeSlug && matchers.slugs[ typeSlug ] ) {
                        return true;
                }

                var idSlug = slugifyIdentifier( prize.id );

                if ( idSlug && matchers.slugs[ idSlug ] ) {
                        return true;
                }

                var haystack = ( ( prize.label || '' ) + ' ' + ( prize.description || '' ) ).toLowerCase();

                return matchers.phrases.some( function( phrase ) {
                        return phrase && haystack.indexOf( phrase ) !== -1;
                } );
        };

        SpinToWin.prototype.isVoucherPrize = function( prize ) {
                if ( ! prize ) {
                        return false;
                }

                if ( Object.prototype.hasOwnProperty.call( prize, 'isVoucher' ) ) {
                        return toBoolean( prize.isVoucher );
                }

                if ( Object.prototype.hasOwnProperty.call( prize, 'is_voucher' ) ) {
                        return toBoolean( prize.is_voucher );
                }

                var typeSlug = this.normalizePrizeType( prize.type );

                if ( typeSlug && ( 'voucher' === typeSlug || 'coupon' === typeSlug ) ) {
                        return true;
                }

                return this.getPrizeDenomination( prize ) !== null;
        };

        SpinToWin.prototype.getPrizeDenomination = function( prize ) {
                if ( ! prize ) {
                        return null;
                }

                if ( Object.prototype.hasOwnProperty.call( prize, 'denomination' ) && prize.denomination !== null && prize.denomination !== undefined ) {
                        var parsedDenomination = parseInt( prize.denomination, 10 );
                        if ( ! isNaN( parsedDenomination ) ) {
                                return parsedDenomination;
                        }
                }

                var numericValue = extractEuroValue( prize );
                if ( null !== numericValue ) {
                        return numericValue;
                }

                if ( Object.prototype.hasOwnProperty.call( prize, 'value' ) && prize.value !== null && prize.value !== undefined ) {
                        var parsedValue = parseInt( prize.value, 10 );
                        if ( ! isNaN( parsedValue ) ) {
                                return parsedValue;
                        }
                }

                return null;
        };

        SpinToWin.prototype.triggerIntegrationHook = function( prize, serverData ) {

                var eventData = {
                        instanceId: this.config.id,
                        prize: prize,
                        timestamp: Date.now(),
                        serverData: serverData || null,
                };

                this.$root.trigger( 'gnTsiartasSpinToWin:prizeAwarded', [ eventData ] );

                if ( window.CustomEvent ) {
                        var customEvent = new window.CustomEvent( 'gnTsiartasSpinToWin:prize', {
                                detail: eventData,
                        } );
                        window.dispatchEvent( customEvent );
                }
        };

        SpinToWin.prototype.openModal = function( title, message, dateText ) {

                if ( ! this.$modal.length ) {
                        return;
                }

                this.$modalTitle.text( title );
                this.$modalMessage.text( message );
                this.updateModalDate( dateText );
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

        function looksLikeImageUrl( value ) {
                if ( ! value ) {
                        return false;
                }

                var stringValue = String( value ).trim();

                if ( ! stringValue ) {
                        return false;
                }

                if ( /^data:image\//i.test( stringValue ) ) {
                        return true;
                }

                if ( /^(https?:)?\/\//i.test( stringValue ) ) {
                        return true;
                }

                if ( stringValue.charAt( 0 ) === '/' ) {
                        return true;
                }

                return /\.(svg|png|jpe?g|gif|webp)(\?|#|$)/i.test( stringValue );
        }

        function resolveSliceArtwork( prize ) {
                if ( ! prize || 'object' !== typeof prize ) {
                        return '';
                }

                var candidates = [ prize.artwork, prize.art, prize.iconUrl, prize.icon_url, prize.icon ];

                for ( var i = 0; i < candidates.length; i++ ) {
                        var candidate = candidates[ i ];

                        if ( ! candidate || 'string' !== typeof candidate ) {
                                continue;
                        }

                        var trimmed = candidate.trim();

                        if ( ! trimmed ) {
                                continue;
                        }

                        if ( looksLikeImageUrl( trimmed ) ) {
                                return trimmed;
                        }
                }

                return '';
        }

        function formatCssUrl( url ) {
                if ( ! url ) {
                        return '';
                }

                var sanitised = String( url ).replace( /"/g, '\\"' );
                return 'url("' + sanitised + '")';
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
