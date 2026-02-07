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

	$('#hcotp-email-template-preview').on('click', function (e) {
		e.preventDefault();

		var choice = $('select[name="hcotp_email_template_choice"]').val() || 'template_1';
		var templateField = 'textarea[name="hcotp_email_template_html_1"]';
		if (choice === 'template_2') {
			templateField = 'textarea[name="hcotp_email_template_html_2"]';
		} else if (choice === 'template_3') {
			templateField = 'textarea[name="hcotp_email_template_html_3"]';
		}

		var templateHtml = $(templateField).val() || '';
		var bodyContent = $('textarea[name="hcotp_email_otp_body"]').val() || '';
		var headerUrl = $('input[name="hcotp_email_otp_header_image"]').val() || '';
		var footerUrl = $('input[name="hcotp_email_otp_footer_image"]').val() || '';

		if (!bodyContent.trim()) {
			bodyContent = "Hello,\n\nYour one-time password (OTP) is {{otp}}.\n\nThis OTP is valid for {{expiry}} minutes.\n\nThanks,\n{{site_name}}";
		}

		var hasHtml = /<[^>]+>/.test(bodyContent);
		var contentHtml = hasHtml ? bodyContent : bodyContent.replace(/\n/g, '<br>');

		var headerImage = '';
		if (headerUrl) {
			headerImage = '<tr><td style="padding:20px 30px 0;text-align:center;"><img src="' + headerUrl + '" alt="Header" style="max-width:200px;height:auto;border:0;outline:none;text-decoration:none;"></td></tr>';
		}

		var footerImage = '';
		if (footerUrl) {
			footerImage = '<tr><td style="padding:0 30px 20px;text-align:center;"><img src="' + footerUrl + '" alt="Footer" style="max-width:200px;height:auto;border:0;outline:none;text-decoration:none;opacity:0.85;"></td></tr>';
		}

		var replacements = {
			'{{content}}': contentHtml,
			'{{otp}}': '123456',
			'{{expiry}}': '5',
			'{{site_name}}': 'WP Demo',
			'{{site_url}}': 'https://example.com',
			'{{user_email}}': 'user@example.com',
			'{{user_mobile}}': '+1 555 0100',
			'{{date}}': new Date().toISOString().slice(0, 10),
			'{{header_image}}': headerImage,
			'{{footer_image}}': footerImage,
			'{{header_image_url}}': headerUrl,
			'{{footer_image_url}}': footerUrl
		};

		Object.keys(replacements).forEach(function (key) {
			var value = replacements[key];
			templateHtml = templateHtml.split(key).join(value);
		});

		if (!templateHtml.trim()) {
			templateHtml = '<div style="padding:20px;font-family:Arial,Helvetica,sans-serif;">' + contentHtml + '</div>';
		}

		$('#hcotp-email-preview-iframe').attr('srcdoc', templateHtml);
		$('#hcotp-email-preview-wrap').show();
	});

	$('#hcotp-email-preview-close').on('click', function (e) {
		e.preventDefault();
		$('#hcotp-email-preview-wrap').hide();
	});
});
