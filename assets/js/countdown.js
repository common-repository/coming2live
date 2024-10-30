(function($) {
	'use strict';

	$(function() {
		$('[data-countdown]').each(function() {
			var $el  = $(this);
			var html = $el.html();

			$el.countdown($el.data('countdown'), function(e) {
				$el.html(e.strftime(html));
				$el.removeClass('hide hidden').show();
			})

			$el.on('finish.countdown', function() {
				window.location.reload();
			});
		});
	});

})(jQuery)
