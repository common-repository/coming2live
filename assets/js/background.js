(function($) {
	'use strict';

	window.C2L = window.C2L || {};

	/**
	 * The background object.
	 *
	 * @type {Object}
	 */
	C2L.Background = {
		/**
		 * Setup the background youtube video.
		 *
		 * @param  {object} $el The jQuery selector.
		 * @return void
		 */
		youtube: function($el, filters) {
			if (! $el.length) {
				return;
			}

			$el.YTPlayer();

			if (filters) {
				$el.YTPApplyFilters(filters);
			}
		},

		/**
		 * Setup background slider.
		 *
		 * @param  {Object} settings The "vegas" settings.
		 * @return void
		 */
		slider: function(settings) {
			if (settings.slides && settings.slides.length) {
				$('body.background-slider .vegas-container').vegas(settings);
			}
		},

		/**
		 * Setup background triangle.
		 *
		 * @param  {Object} settings The "quietflow" settings.
		 * @return void
		 */
		quietflow: function(settings) {
			$('body.background-triangle').quietflow(settings);
		},

		/**
		 * Render FSS effect.
		 *
		 * @param  {Object} container The container.
		 * @param  {Object} options   Options.
		 * @return {void}
		 */
		fssEffect: function(container, options) {
			options = options || {};

			var renderer = new FSS.CanvasRenderer();
			var scene    = new FSS.Scene();
			var light    = new FSS.Light(options.light_ambient, options.light_diffuse);
			var material = new FSS.Material(options.material_ambient, options.material_diffuse);
			var geometry = new FSS.Plane(window.innerWidth, window.innerHeight, 6, 4);
			var mesh     = new FSS.Mesh(geometry, material);

			var now, start = Date.now();

			var initialise = function initialise() {
				scene.add(mesh);
				scene.add(light);
				container.appendChild(renderer.element);
				window.addEventListener('resize', resize);
			};

			var resize = function resize() {
				renderer.setSize(container.offsetWidth, container.offsetHeight);
			};

			var animate = function animate() {
				now = Date.now() - start;
				light.setPosition(300 * Math.sin(now * 0.001), 200 * Math.cos(now * 0.0005), 60);
				renderer.render(scene);
				requestAnimationFrame(animate);
			};

			initialise();
			resize();
			animate();
		},
	};

	// Document ready!
	$(function() {
		var $body = $('body');

		if ($.fn.YTPlayer && $body.hasClass('background-video-youtube')) {
			C2L.Background.youtube($('.c2l-youtube-bgvideo'), window._bgYoutubeFilters);
		}

		if (window._c2lBgSlider && $.fn.vegas && $body.hasClass('background-slider')) {
			C2L.Background.slider(window._c2lBgSlider);
		}

		if (window._c2lTriangle && $.fn.quietflow && $body.hasClass('background-triangle')) {
		console.log(window._c2lTriangle);
			C2L.Background.quietflow(window._c2lTriangle);
		}

		if (window._fssColors && window.FSS && $body.hasClass('background-fss')) {
			C2L.Background.fssEffect(document.getElementById('fss-js'), window._fssColors);
		}
	});

})(jQuery);
