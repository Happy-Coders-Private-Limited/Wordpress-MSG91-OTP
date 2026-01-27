jQuery(document).ready(function ($) {
	'use strict';

	$('.hcotp-upload').on('click', function (e) {
		e.preventDefault();

		var input = $(this).prev('input');
		var frame = wp.media({
			title: 'Select Image',
			button: { text: 'Use this image' },
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			input.val(attachment.url);
		});

		frame.open();
	});
});