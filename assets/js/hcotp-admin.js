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
		if (!templateHtml.trim()) {
			if (choice === 'template_2') {
				templateHtml = $('#hcotp-email-template-default-2').val() || '';
			} else if (choice === 'template_3') {
				templateHtml = $('#hcotp-email-template-default-3').val() || '';
			} else {
				templateHtml = $('#hcotp-email-template-default-1').val() || '';
			}
		}
		var bodyContent = $('textarea[name="hcotp_email_otp_body"]').val() || '';
		var headerUrl = $('input[name="hcotp_email_otp_header_image"]').val() || '';
		var footerUrl = $('input[name="hcotp_email_otp_footer_image"]').val() || '';
		var headerWidth = parseInt($('input[name="hcotp_email_otp_header_image_width"]').val(), 10);
		var headerHeight = parseInt($('input[name="hcotp_email_otp_header_image_height"]').val(), 10);
		var footerWidth = parseInt($('input[name="hcotp_email_otp_footer_image_width"]').val(), 10);
		var footerHeight = parseInt($('input[name="hcotp_email_otp_footer_image_height"]').val(), 10);
		if (!bodyContent.trim()) {
			bodyContent = "Hello,\n\nYour one-time password (OTP) is {{otp}}.\n\nThis OTP is valid for {{expiry}} minutes.\n\nThanks,\n{{site_name}}";
		}

		var hasHtml = /<[^>]+>/.test(bodyContent);
		var contentHtml = hasHtml ? bodyContent : bodyContent.replace(/\n/g, '<br>');

		var headerImage = '';
		if (headerUrl) {
			var headerStyle = 'border:0;outline:none;text-decoration:none;height:auto;';
			if (!isNaN(headerWidth) && headerWidth > 0) {
				headerStyle += 'width:' + headerWidth + 'px;max-width:' + headerWidth + 'px;';
			} else {
				headerStyle += 'max-width:200px;';
			}
			if (!isNaN(headerHeight) && headerHeight > 0) {
				headerStyle += 'height:' + headerHeight + 'px;';
			}
			headerImage = '<tr><td style="padding:20px 30px 0;text-align:center;"><img src="' + headerUrl + '" alt="Header" style="' + headerStyle + '"></td></tr>';
			console.log("headerImage", headerImage);
		}

		var footerImage = '';
		if (footerUrl) {
			var footerStyle = 'border:0;outline:none;text-decoration:none;height:auto;opacity:0.85;';
			if (!isNaN(footerWidth) && footerWidth > 0) {
				footerStyle += 'width:' + footerWidth + 'px;max-width:' + footerWidth + 'px;';
			} else {
				footerStyle += 'max-width:200px;';
			}
			if (!isNaN(footerHeight) && footerHeight > 0) {
				footerStyle += 'height:' + footerHeight + 'px;';
			}
			footerImage = '<tr><td style="padding:0 30px 20px;text-align:center;"><img src="' + footerUrl + '" alt="Footer" style="' + footerStyle + '"></td></tr>';
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

		var iframe = document.getElementById('hcotp-email-preview-iframe');
		if (iframe) {
			if ('srcdoc' in iframe) {
				iframe.srcdoc = templateHtml;
			} else if (iframe.contentWindow && iframe.contentWindow.document) {
				var doc = iframe.contentWindow.document;
				doc.open();
				doc.write(templateHtml);
				doc.close();
			}
		}
		$('#hcotp-email-preview-wrap').show();
	});

	$('#hcotp-email-preview-close').on('click', function (e) {
		e.preventDefault();
		$('#hcotp-email-preview-wrap').hide();
	});
});
