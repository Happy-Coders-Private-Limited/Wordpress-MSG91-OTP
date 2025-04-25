jQuery(document).ready(function ($) {
    $(document).on('click', '#msg91_send_otp', function () {
        let $button = $(this); 
        let mobile = $('#msg91_mobile').val().trim();
        let countryCode = $('#country_code').text().trim();
        let msg = msg91_ajax_obj.sendotp_validation_msg || 'Please enter a valid mobile number (between 5 and 12 digits).';
        if (!mobile || mobile.length < 5 || mobile.length > 12) {
            $('#otp-send-status').html('<span style="color:red;">' + msg + '</span>');
            return;
        }
        let mobileWithCode = countryCode + mobile;
    
        $button.prop('disabled', true).text('Sending...');
    
        $.post(msg91_ajax_obj.ajax_url, {
            action: 'send_msg91_otp_ajax',
            mobile: mobileWithCode
        }, function (res) {
            if(res){
                if (res.success) {
                    $('#send_otp_section').hide(); 
                    $('#otp_input_wrap').show();  
                    startOTPTimer(); 
                } else {
                    $('#otp-send-status').html('<span style="color:red;">' + res.data.message + '</span>');
                    $button.prop('disabled', false).text('Send OTP'); 
                }
            } else {
                $('#otp-send-status').html('<span style="color:red;">' + res.data.message + '</span>');
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
        let mobile = $('#msg91_mobile').val().trim();
        let otp = $('#otp1').val().trim() + $('#otp2').val().trim() + $('#otp3').val().trim() + $('#otp4').val().trim();
        let msg = msg91_ajax_obj.verifyotp_validation_msg || 'Please enter the OTP.';
        if (otp.length !== 4) {
            $('#otp-verify-status').html('<span style="color:red;font-size: 14px;">' + msg + '</span>');
            return;
        }

        $('#otp-verify-status').html('Verifying...');
        $(this).prop('disabled', true).text('Verifying...');

        $.post(msg91_ajax_obj.ajax_url, {
            action: 'verify_msg91_otp_ajax',
            mobile: mobile,
            otp: otp
        }, function (res) {
            if(res){
                if (res.success) {
                    $('#otp-verify-status').html('<span style="color:green;">OTP Verified!</span>');
                    $('#accountNext').show();
                    $.post(msg91_ajax_obj.ajax_url, {
                        action: 'msg91_auto_login_user',
                        mobile: mobile
                    }, function (loginRes) {
                        if (loginRes.success) {
                    
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
                        } else {
                            $('#otp-verify-status').html('<span style="color:red;">Login failed. Please try again.</span>');
                            $('#msg91_verify_otp').prop('disabled', false).text('Verify OTP');
                            $('#msg91_verify_otp').text('Verify OTP');
                        }
                    });
                } else {
                    $('#otp-verify-status').html('<span style="color:red;">' + ('Invalid OTP.') + '</span>');
                    $('#msg91_verify_otp').prop('disabled', false).text('Verify OTP');
                    $('#msg91_verify_otp').text('Verify OTP');
                }
            } else {
                $('#otp-verify-status').html('<span style="color:red;">' + ('Invalid OTP.') + '</span>');
                    $('#msg91_verify_otp').prop('disabled', false).text('Verify OTP');
                    $('#msg91_verify_otp').text('Verify OTP');
            }
            
        }).fail(function () {
            $('#otp-verify-status').html('<span style="color:red;">Server error. Try again.</span>');
            $('#msg91_verify_otp').prop('disabled', false).text('Verify OTP');
            $('#msg91_verify_otp').text('Verify OTP');
        });
    });
    
    $(document).on('click', '#resend_otp', function () {
        let mobile = $('#msg91_mobile').val().trim();
        let countryCode = $('#country_code').text().trim();

        let msg = msg91_ajax_obj.sendotp_validation_msg || 'Please enter a valid mobile number (between 5 and 12 digits).';
        if (!mobile || mobile.length < 5 || mobile.length > 12) {
            $('#otp-send-status').html('<span style="color:red;">' + msg + '</span>');
            return;
        }

        let mobileWithCode = countryCode + mobile;

        $(this).prop('disabled', true).text('Sending...');
        $.post(msg91_ajax_obj.ajax_url, {
            action: 'send_msg91_otp_ajax',
            mobile: mobileWithCode
        }, function (res) {
            if(res){
                if (res.success) {
                    startOTPTimer();
                } else {
                    $('#otp-send-status').html('<span style="color:red;">' + res.data.message + '</span>');
                    $('#resend_otp').prop('disabled', false).text('Resend OTP');
                }
            } else {
                $('#otp-send-status').html('<span style="color:red;">' + res.data.message + '</span>');
                $('#resend_otp').prop('disabled', false).text('Resend OTP');
            }
        });
    });

    function startOTPTimer() {
        let timer = msg91_ajax_obj.resend_timer || 30;
        const resendBtn = $('#resend_otp');
    
        resendBtn.prop('disabled', true)
            .text('Resend OTP (' + timer + 's)')
           
        const interval = setInterval(() => {
            timer--;
            resendBtn.text('Resend OTP (' + timer + 's)');
            if (timer <= 0) {
                clearInterval(interval);
                resendBtn.prop('disabled', false)
                .html("Didn't receive an OTP?.<strong> Resend OTP</strong>")
                    .css({
                        'background-color': '<?php echo esc_js($button_color); ?>',
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

document.getElementById('msg91_country_code').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    var dialCode = selectedOption.value;
    var flag = selectedOption.getAttribute('data-flag');
    document.getElementById('country-flag').innerHTML = flag + ' ' + dialCode;

    this.style.display = 'none';
});

document.addEventListener("DOMContentLoaded", function () {
    const popupModal = document.getElementById("otp-popup-modal");
    const classTriggers = document.querySelectorAll('.otp-popup-trigger');
    classTriggers.forEach(function(trigger) {
        trigger.addEventListener("click", function () {
            popupModal.style.display = "block";
        });
    });
    window.openMsg91OtpPopup = function() {
        if (popupModal) {
            popupModal.style.display = "block";
        }
    };
});



