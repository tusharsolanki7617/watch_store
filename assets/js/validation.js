$(document).ready(function () {
    // Check if jQuery Validate is loaded
    if (typeof $.fn.validate !== 'undefined') {

        // Default Configuration for Bootstrap 5
        $.validator.setDefaults({
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            highlight: function (element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function (element) {
                $(element).addClass('is-valid').removeClass('is-invalid');
            },
            errorPlacement: function (error, element) {
                if (element.hasClass('select2-hidden-accessible')) {
                    error.insertAfter(element.next('span'));
                } else if (element.parent('.input-group').length) {
                    error.insertAfter(element.parent());
                } else if (element.prop('type') === 'checkbox' || element.prop('type') === 'radio') {
                    error.parent().parent().append(error);
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function (form) {
                // If the form has a submit button with visual feedback, enable it
                var submitBtn = $(form).find('button[type="submit"]');
                var originalText = submitBtn.html();

                // Handle AJAX forms
                if ($(form).hasClass('ajax-form')) {
                    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');

                    var formData = $(form).serialize();
                    var action = $(form).attr('action');
                    if ($(form).attr('id') === 'registerForm') action = SITE_URL + '/ajax/register.php';
                    if ($(form).attr('id') === 'forgotPasswordForm') action = SITE_URL + '/ajax/forgot-password.php';

                    $.ajax({
                        url: action,
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                $('#registerAlert').removeClass('d-none alert-danger').addClass('alert-success').text(response.message);
                                if (response.redirect) {
                                    setTimeout(function () {
                                        window.location.href = response.redirect;
                                    }, 2000);
                                }
                            } else {
                                $('#registerAlert').removeClass('d-none alert-success').addClass('alert-danger').text(response.message);
                                submitBtn.prop('disabled', false).html(originalText);
                            }
                        },
                        error: function () {
                            $('#registerAlert').removeClass('d-none alert-success').addClass('alert-danger').text('An error occurred. Please try again.');
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    });
                    return false; // Prevent traditional submission
                }

                // Regular form submission
                if (!$(form).hasClass('ajax-form')) {
                    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
                    form.submit();
                }
            }
        });

        // ==========================================
        // USER FORMS
        // ==========================================

        // Login Form
        $('form[action*="login.php"], #loginForm').validate({
            rules: {
                email: { required: true, email: true },
                password: { required: true }
            },
            messages: {
                email: { required: "Please enter your email", email: "Please enter a valid email address" },
                password: { required: "Please enter your password" }
            }
        });

        // Register Form
        $('form[action*="register.php"], #registerForm').validate({
            rules: {
                full_name: { required: true, minlength: 3 },
                email: { required: true, email: true },
                password: { required: true, minlength: 8 },
                confirm_password: { required: true, equalTo: '[name="password"]' }
            },
            messages: {
                full_name: { required: "Full name is required", minlength: "Name must be at least 3 characters" },
                email: "Please enter a valid email address",
                password: { required: "Password is required", minlength: "Password must be at least 8 characters" },
                confirm_password: { required: "Please confirm your password", equalTo: "Passwords do not match" }
            }
        });

        // Checkout Form
        $('#checkoutForm').validate({
            rules: {
                full_name: "required",
                email: { required: true, email: true },
                phone: { required: true, digits: true, minlength: 10, maxlength: 10 },
                address: "required",
                city: "required",
                state: "required",
                zip: { required: true, digits: true, minlength: 6, maxlength: 6 },
                payment_method: "required"
            },
            messages: {
                phone: "Please enter a valid 10-digit phone number",
                zip: "Please enter a valid 6-digit PIN code"
            }
        });

        // Profile Update Form
        $('form[name="update_profile"], form[action*="profile.php"]').validate({
            rules: {
                full_name: "required",
                phone: { required: true, digits: true, minlength: 10, maxlength: 15 }
            }
        });

        // Contact Form
        $('form[action*="contact.php"]').validate({
            rules: {
                name: "required",
                email: { required: true, email: true },
                subject: "required",
                message: { required: true, minlength: 10 }
            }
        });

        // Forgot Password Form
        $('#forgotPasswordForm').validate({
            rules: {
                email: { required: true, email: true }
            },
            messages: {
                email: "Please enter your registered email address"
            }
        });

        // Reset Password Form
        $('#resetPasswordForm').validate({
            rules: {
                otp: { required: true, digits: true, minlength: 6, maxlength: 6 },
                new_password: { required: true, minlength: 8 },
                confirm_password: { required: true, equalTo: "#new_password" }
            },
            messages: {
                otp: "Please enter the 6-digit verification code",
                new_password: { required: "New password is required", minlength: "Password must be at least 8 characters" },
                confirm_password: { required: "Please confirm your password", equalTo: "Passwords do not match" }
            }
        });

        // ==========================================
        // ADMIN FORMS
        // ==========================================

        // Admin Login
        $('form[action*="admin/login.php"]').validate({
            rules: {
                username: { required: true },
                password: { required: true }
            }
        });

        // Add/Edit Product Form
        $('form[action*="add-product.php"], form[action*="edit-product.php"]').validate({
            rules: {
                name: "required",
                category_id: "required",
                price: { required: true, number: true, min: 0 },
                stock: { required: true, digits: true, min: 0 },
                description: "required"
            }
        });

        // Category Form
        $('form[action*="categories.php"]').validate({
            rules: {
                name: "required"
            }
        });

    } else {
        console.warn('jQuery Validate plugin is not loaded.');
    }
});
