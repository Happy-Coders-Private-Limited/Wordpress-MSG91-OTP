jQuery(document).ready(function ($) {
    
    $(document).on('click', '#msg91_send_otp', function () {

        let $button = $(this); 
        let $container = $button.closest('#otp-form-wrap'); 
    
        let mobile = $container.find('#msg91_mobile').val().trim();
        let countryCode = $container.find('#msg91_country_code').val();
        let msg = hcotp_params.sendotp_validation_msg || 'Please enter a valid mobile number (between 5 and 12 digits).';
    
        if (!mobile || mobile.length < 5 || mobile.length > 12) {
            $container.find('#otp-send-status').html('<span style="color:red;">' + msg + '</span>');
            return;
        }
    

        let mobileWithCode = countryCode + mobile;
    
        $button.prop('disabled', true).text('Sending...');

        $.post(hcotp_params.ajax_url, {
            action: 'hcotp_send_otp_ajax',
            mobile: mobileWithCode,
             otpprocess: 'sms',
             security_nonce: hcotp_params.nonce 
            
        }, function (res) {
             $container.find('.sms-button').prop('disabled', false).text('SMS');
               $container.find('.whatsapp-button').prop('disabled', false).text('Whatsapp');
           
             console.log('OTP sent successfully, starting timer...');
            if (res) {
                 console.log('OTP sent');
                if (res.success) {
                     $container.find('#otpprocess').val('sms');
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

     $(document).on('click', '#msg91_send_otp_whatsapp', function () {
        let $button = $(this); 
        let $container = $button.closest('#otp-form-wrap'); 
    
        let mobile = $container.find('#msg91_mobile').val().trim();
        let countryCode = $container.find('#msg91_country_code').val();
        let msg = hcotp_params.sendotp_validation_msg || 'Please enter a valid mobile number (between 5 and 12 digits).';
    
        if (!mobile || mobile.length < 5 || mobile.length > 12) {
            $container.find('#otp-send-status').html('<span style="color:red;">' + msg + '</span>');
            return;
        }
    
        let mobileWithCode = countryCode + mobile;
    
        $button.prop('disabled', true).text('Sending...');

    
        $.post(hcotp_params.ajax_url, {
            action: 'hcotp_send_otp_ajax',
            mobile: mobileWithCode,
               otpprocess: 'whatsapp',
             security_nonce: hcotp_params.nonce 
            
        }, function (res) {
              $container.find('.sms-button').prop('disabled', false).text('SMS');
              $container.find('.whatsapp-button').prop('disabled', false).text('Whatsapp');
             console.log('OTP sent successfully, starting timer...');
            if (res) {
                 console.log('OTP sent');
                if (res.success) {
                      $container.find('#otpprocess').val('whatsapp');
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
    
        let msg = hcotp_params.verifyotp_validation_msg || 'Please enter the OTP.';
    
        if (otp.length !== 4) {
            $container.find('#otp-verify-status').html('<span style="color:red;font-size: 14px;">' + msg + '</span>');
            return;
        }
         const otpButtons = $container.find('#otp_method_buttons');

        
            
    
        $container.find('#otp-verify-status').html('Verifying...');
        $button.prop('disabled', true).text('Verifying...');
        let otpprocess = $container.find('#otpprocess').val(); 
    
        $.post(hcotp_params.ajax_url, {
            action: 'hcotp_verify_otp_ajax',
            mobile: mobileWithCode,
            otpprocess : otpprocess,
            otp: otp,
              security_nonce: hcotp_params.nonce 
        }, function (res) {
              
            if (res && res.success) {
                $container.find('#otp-verify-status').html('<span style="color:green;">OTP Verified!</span>');
                    // Hide the SMS & WhatsApp buttons
            otpButtons.hide();

                let redirectUrl = hcotp_params.redirect_page;
    
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
                    // Hide the SMS & WhatsApp buttons
            otpButtons.show();
            }
    
            $button.prop('disabled', false).text('Verify OTP');
        }).fail(function () {
            $container.find('#otp-verify-status').html('<span style="color:red;">Server error. Try again.</span>');
            $button.prop('disabled', false).text('Verify OTP');
                otpButtons.show();
        });
    });
    
   
    $(document).on('click', '#resend_otp', function () {
        let $button = $(this);
        let $container = $button.closest('#otp-form-wrap');
    
        let mobile = $container.find('#msg91_mobile').val().trim();
         let countryCode = $container.find('#msg91_country_code').val();
    
        let msg = hcotp_params.sendotp_validation_msg || 'Please enter a valid mobile number (between 5 and 12 digits).';
        if (!mobile || mobile.length < 5 || mobile.length > 12) {
            $container.find('#otp-send-status').html('<span style="color:red;">' + msg + '</span>');
            return;
        }
    
        let mobileWithCode = countryCode + mobile;
    
       
        $.post(hcotp_params.ajax_url, {
            action: 'hcotp_send_otp_ajax',
            mobile: mobileWithCode,
              security_nonce: hcotp_params.nonce 
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
        let timer = hcotp_params.resend_timer || 30;
        const resendBtn = $container.find('#resend_otp');

         const otpButtons = $container.find('#otp_method_buttons');

            // Hide the SMS & WhatsApp buttons
            otpButtons.hide();
            
        resendBtn.prop('disabled', true).text('Resend OTP (' + timer + 's)');
    
        const interval = setInterval(() => {
            timer--;
            resendBtn.text('Resend OTP (' + timer + 's)');
            if (timer <= 0) {
                clearInterval(interval);
                resendBtn.prop('disabled', true)
                    .html("Didn't receive an OTP?<strong> Resend OTP</strong>")
                    
                     otpButtons.show();
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
	$('#hcotp-settings-wrap .nav-tab-wrapper a.nav-tab').on('click', function(e){
				e.preventDefault();
				var tab_id = $(this).data('tab');
				$('#hcotp-settings-wrap .nav-tab-wrapper a.nav-tab').removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');
				$('.tab-content').removeClass('active-tab').hide();
				$('#' + tab_id).addClass('active-tab').show();
				$('#hcotp_msg91_active_tab_input').val(tab_id);
	});
});
jQuery(document).ready(function($) {
    const $checkbox = $('#hcotp_whatsapp_auth_checkbox');
    const $inputsDiv = $('#hcotp_whatsapp_auth_inputs');

    $checkbox.on('change', function() {
        $inputsDiv.toggle(this.checked);
    });
});
