(function($) {
	'use strict';

	window.C2L = window.C2L || {};

	/**
	 * Setting Class.
	 */
	C2L.Setting = {
		init: function() {
			this.deps('#mode', '==', 'redirect', '.cmb2-id-redirect-url');

			this.depsCheckbox('#filter_whitelist_pages', '.cmb2-id-whitelist-pages');
			this.depsCheckbox('#body_enable_animatedtitle', '.cmb2-id-body-title-animated');
			this.depsCheckbox('#display_countdown', '.cmb2-id-countdown-datetime, .cmb2-id-countdown-action');
			this.depsCheckbox('#display_subscribe', '.cmb2-id-subscribe-shortcode, .cmb2-id-subscribe-title, .cmb2-id-subscribe-message');
			this.depsCheckbox('#display_social', '.cmb2-id-social-links');
		},

		deps: function(selector, operator, value, deps) {
			if (! $(selector).length) {
				return;
			}

			var self = this;
			var onChange = function() {
				if (self._performOperator($(selector).val(), operator, value)) {
					self._setDepsVisible(deps, 'show');
				} else {
					self._setDepsVisible(deps, 'hide');
				}
			};

			onChange();
			$(selector).on('change', onChange);
		},

		depsCheckbox: function(toggle, deps) {
			var self = this;

			var onChange = function() {
				if ($(toggle).is(':checked')) {
					self._setDepsVisible(deps, 'show');
				} else {
					self._setDepsVisible(deps, 'hide');
				}
			};

			onChange();
			$(toggle).on('change', onChange);
		},

		_performOperator: function(a, operator, b) {
			if ( operator == '=' || operator == '==') {
				return a == b;
			} if ( operator == '!=' || operator == '!==') {
				return a != b;
			} else if ( operator == '>' ) {
				return a > b;
			} else if ( operator == '>=' ) {
				return a >= b;
			} else if ( operator == '<' ) {
				return a < b;
			} else if ( operator == '<=' ) {
				return a <= b;
			}

			return !a;
		},

		_setDepsVisible: function(deps, visible) {
			$(deps).each(function() {
				( visible == 'show' ) ? $(this).show() : $(this).hide();
			});
		},
	};

	/**
	 * Background field controls.
	 */
	C2L.BackgroundControls = function(el) {
		var $el = $(el);

		var $select = $el.find('[data-select] > select');
		var $controls = $el.find('[data-sections]');

		var handleVisible = function() {
			var value = $select.val();
			$controls.find('[data-type].show').removeClass('show').addClass('hidden').hide();
			$controls.find('[data-type="' + value + '"]').addClass('show').removeClass('hidden').show();
		};

		handleVisible();
		$select.on('change', handleVisible);
	};

	/**
	 * Range field controls.
	 */
	C2L.RangeControls = function(el) {
		$(el).find('.cmb2-ui-slider-input').each(function() {
			var $input = $(this);

			if ($input.closest('.empty-row').length) {
				return;
			}

			var $text = $input.parent().find('.cmb2-ui-slider-preview');
			var $range = $input.parent().find('.cmb2-ui-slider');

			// Setup jQuery UI Slider.
			var rangeSlider = $range.slider({
				range: 'min',
				min: $input.data('min'),
				max: $input.data('max'),
				step: $input.data('step'),
				value: $input.data('value'),
				animate: true,
				slide: function(e, ui) {
					syncInputValue(ui.value);
				}
			});

			// Enable pips ui float.
			if ($input.data('float') && _.isObject($input.data('float'))) {
				rangeSlider.slider('float', $input.data('float'));
			}

			// Enable pips ui.
			if ($input.data('pips') && _.isObject($input.data('pips'))) {
				rangeSlider.slider('pips', $input.data('pips'));
			}

			var syncInputValue = function(value) {
				$text.text(value);
				$input.val(value).trigger('change');
			};

			var syncInputRange = function() {
				const inputValue = parseInt($(this).val());
				rangeSlider.slider('value', inputValue);

				// Fallback invalid value.
				if (rangeSlider.slider('value') !== inputValue) {
					$(this).val(rangeSlider.slider('value'));
				}
			};

			// Initiate the display.
			syncInputValue(rangeSlider.slider('value'));
			$input.on('change blur', syncInputRange);
		});
	};

	// Document ready!
	$(function() {
		C2L.Setting.init();

		$('[data-fieldtype="c2l_range"]').each(function() {
			new C2L.RangeControls(this);
		});

		$('[data-fieldtype="c2l_background"], [data-fieldtype="c2l_background_effects"]').each(function() {
			new C2L.BackgroundControls(this);
		});
	});

})(jQuery);
