jQuery(document).ready(function ($) {
    
    $(document).on('click', '#msg91_send_otp', function () {
        let $button = $(this); 
        let $container = $button.closest('#otp-form-wrap'); 
    
        let mobile = $container.find('#msg91_mobile').val().trim();
        let countryCode = $container.find('#msg91_country_code').val();
        let msg = msg91_ajax_obj.sendotp_validation_msg || 'Please enter a valid mobile number (between 5 and 12 digits).';
    
        if (!mobile || mobile.length < 5 || mobile.length > 12) {
            $container.find('#otp-send-status').html('<span style="color:red;">' + msg + '</span>');
            return;
        }
    
        let mobileWithCode = countryCode + mobile;
    
        $button.prop('disabled', true).text('Sending...');
    
        $.post(msg91_ajax_obj.ajax_url, {
            action: 'happycoders_send_msg91_otp_ajax',
            mobile: mobileWithCode,
             security_nonce: msg91_ajax_obj.nonce 
            
        }, function (res) {
             console.log('OTP sent successfully, starting timer...');
            if (res) {
                 console.log('OTP sent');
                if (res.success) {
                    $container.find('#send_otp_section').hide();
                    $container.find('#otp_input_wrap').show();
                    startOTPTimer($container); 
                } else {
                    $container.find('#otp-send-status').html('<span style="color:red;">' + res.data.message + '</span>');
                    $button.prop('disabled', false).text('Send OTP');
                }
            } else {
                 console.log('OTP');
                $container.find('#otp-send-status').html('<span style="color:red;">Something went wrong. Try again.</span>');
                $button.prop('disabled', false).text('Send OTP');
            }
        });
    });
    

    $('.otp-field').on('input', function () {
        let currentInput = $(this);
        if (currentInput.val().length === 1) {
            let nextInput = currentInput.next('.otp-field');
            if (nextInput.length) {
                nextInput.focus();
            }
        }
    });

    $(document).on('click', '#msg91_verify_otp', function () {
        let $button = $(this);
        let $container = $button.closest('#otp-form-wrap');
        let mobile = $container.find('#msg91_mobile').val().trim();
        let countryCode = $container.find('#msg91_country_code').val();

        let mobileWithCode = countryCode + mobile;

        let otp = $container.find('#otp1').val().trim() +
                  $container.find('#otp2').val().trim() +
                  $container.find('#otp3').val().trim() +
                  $container.find('#otp4').val().trim();
    
        let msg = msg91_ajax_obj.verifyotp_validation_msg || 'Please enter the OTP.';
    
        if (otp.length !== 4) {
            $container.find('#otp-verify-status').html('<span style="color:red;font-size: 14px;">' + msg + '</span>');
            return;
        }
    
        $container.find('#otp-verify-status').html('Verifying...');
        $button.prop('disabled', true).text('Verifying...');
    
        $.post(msg91_ajax_obj.ajax_url, {
            action: 'happycoders_verify_msg91_otp_ajax',
            mobile: mobileWithCode,
            otp: otp,
              security_nonce: msg91_ajax_obj.nonce 
        }, function (res) {
            if (res && res.success) {
                $container.find('#otp-verify-status').html('<span style="color:green;">OTP Verified!</span>');

                let redirectUrl = msg91_ajax_obj.redirect_page;
    
                if (redirectUrl) {
                  
                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 1500);
                } else {
                 
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
        
                $container.find('#msg91_verify_otp').hide();
                $container.find('#resend_otp').hide();
                $container.find('#resend_timer_text').hide();

                $('#account-tab').removeClass('active show');
           
                if ($container.data('is-popup') == '1') {
                    setTimeout(function () {
                        window.location.href = '/';
                    }, 1000);
                }

            } else {
                $container.find('#otp-verify-status').html('<span style="color:red;">Invalid OTP.</span>');
            }
    
            $button.prop('disabled', false).text('Verify OTP');
        }).fail(function () {
            $container.find('#otp-verify-status').html('<span style="color:red;">Server error. Try again.</span>');
            $button.prop('disabled', false).text('Verify OTP');
        });
    });
    
   
    $(document).on('click', '#resend_otp', function () {
        let $button = $(this);
        let $container = $button.closest('#otp-form-wrap');
    
        let mobile = $container.find('#msg91_mobile').val().trim();
         let countryCode = $container.find('#msg91_country_code').val();
    
        let msg = msg91_ajax_obj.sendotp_validation_msg || 'Please enter a valid mobile number (between 5 and 12 digits).';
        if (!mobile || mobile.length < 5 || mobile.length > 12) {
            $container.find('#otp-send-status').html('<span style="color:red;">' + msg + '</span>');
            return;
        }
    
        let mobileWithCode = countryCode + mobile;
    
        $button.prop('disabled', true).text('Sending...');
        $.post(msg91_ajax_obj.ajax_url, {
            action: 'happycoders_send_msg91_otp_ajax',
            mobile: mobileWithCode,
              security_nonce: msg91_ajax_obj.nonce 
        }, function (res) {
            if (res && res.success) {
                startOTPTimer($container);
            } else {
                $container.find('#otp-send-status').html('<span style="color:red;">' + res.data.message + '</span>');
                $button.prop('disabled', false).text('Resend OTP');
            }
        });
    });
    

    function startOTPTimer($container) {
        let timer = msg91_ajax_obj.resend_timer || 30;
        const resendBtn = $container.find('#resend_otp');
    
        resendBtn.prop('disabled', true).text('Resend OTP (' + timer + 's)');
    
        const interval = setInterval(() => {
            timer--;
            resendBtn.text('Resend OTP (' + timer + 's)');
            if (timer <= 0) {
                clearInterval(interval);
                resendBtn.prop('disabled', false)
                    .html("Didn't receive an OTP?<strong> Resend OTP</strong>")
                    .css({
                        'cursor': 'pointer'
                    });
            }
        }, 1000);
    }
    
});
document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="msg91_login_form_type"]');
    const shortcodePreview = document.getElementById('msg91-shortcode-preview');

    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            const shortcode = (this.value === 'screen') ? '[msg91_otp_form]' : '[msg91_otp_popup_form]';
            shortcodePreview.textContent = shortcode;
        });
    });
});

document.querySelectorAll('.otp-field').forEach((input, index, inputs) => {
    input.addEventListener('input', (event) => {
        if (input.value.length === 1 && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }
    });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Backspace' && input.value.length === 0 && index > 0) {
            inputs[index - 1].focus();
        }
    });
});



document.addEventListener('DOMContentLoaded', function () {
    const countryCodeSelect = document.getElementById('msg91_country_code');

    if (countryCodeSelect) {
        countryCodeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const dialCode = selectedOption.value;
            const flag = selectedOption.getAttribute('data-flag');
            const flagContainer = document.getElementById('country-flag');

            if (flagContainer) {
                flagContainer.innerHTML = flag + ' ' + dialCode;
            }

            this.style.display = 'none';
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const popupModal = document.getElementById("otp-popup-modal");

    document.addEventListener("click", function (e) {
        const trigger = e.target.closest('.otp-popup-trigger');
        if (trigger) {
            e.preventDefault();
            if (popupModal) {
                popupModal.style.display = "block";
            }
        }
    });

    window.openMsg91OtpPopup = function () {
        if (popupModal) {
            popupModal.style.display = "block";
        }
    };
});


jQuery(document).ready(function($) {
			// Tab functionality
			$('.nav-tab-wrapper a.nav-tab').click(function(e) {
				e.preventDefault();
				var tab_id = $(this).data('tab');

				// Set active class on tab link
				$('.nav-tab-wrapper a.nav-tab').removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');

				// Show/hide tab content
				$('.tab-content').removeClass('active-tab').hide();
				$('#' + tab_id).addClass('active-tab').show();

				// Update hidden input for active tab
				$('#msg91_active_tab_input').val(tab_id);
			});
});








