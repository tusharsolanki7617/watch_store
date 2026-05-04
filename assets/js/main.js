/**
 * Main JavaScript Functions
 * Watch Store E-Commerce Website
 */

$(document).ready(function () {

    // === Initialize Tooltips ===
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // === Smooth Scrolling ===
    $('a[href^="#"]').on('click', function (e) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 800);
        }
    });

    // === Scroll Animations ===
    function animateOnScroll() {
        $('.fade-in-up').each(function () {
            var elementTop = $(this).offset().top;
            var elementBottom = elementTop + $(this).outerHeight();
            var viewportTop = $(window).scrollTop();
            var viewportBottom = viewportTop + $(window).height();

            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                $(this).addClass('visible');
            }
        });
    }

    $(window).on('scroll', animateOnScroll);
    animateOnScroll();

    // === Add to Cart (AJAX) ===
    $(document).on('click', '.btn-add-to-cart', function (e) {
        e.preventDefault();

        var productId = $(this).data('product-id');
        var quantity = $(this).data('quantity') || 1;
        var button = $(this);

        // Disable button
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Adding...');

        $.ajax({
            url: SITE_URL + '/ajax/add-to-cart.php',
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity,
                csrf_token: CSRF_TOKEN
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Update cart count
                    updateCartCount();

                    // Show success message
                    showNotification('success', 'Product added to cart!');

                    // Reset button
                    button.prop('disabled', false).html('<i class="bi bi-cart-plus"></i> Add to Cart');
                } else {
                    showNotification('error', response.message || 'Failed to add product to cart');
                    button.prop('disabled', false).html('<i class="bi bi-cart-plus"></i> Add to Cart');
                }
            },
            error: function () {
                showNotification('error', 'An error occurred. Please try again.');
                button.prop('disabled', false).html('<i class="bi bi-cart-plus"></i> Add to Cart');
            }
        });
    });

    // === Update Cart Quantity ===
    $(document).on('change', '.cart-quantity', function () {
        var cartId = $(this).data('cart-id');
        var quantity = $(this).val();

        updateCartItem(cartId, quantity);
    });

    // === Remove from Cart ===
    $(document).on('click', '.btn-remove-cart', function (e) {
        e.preventDefault();

        var cartId = $(this).data('cart-id');

        if (confirm('Are you sure you want to remove this item?')) {
            removeCartItem(cartId);
        }
    });

    // === Apply Coupon ===
    $(document).on('click', '#applyCouponBtn', function (e) {
        e.preventDefault();

        var couponCode = $('#couponCode').val().trim();

        if (!couponCode) {
            showNotification('error', 'Please enter a coupon code');
            return;
        }

        applyCoupon(couponCode);
    });

    // === Product Search (Autocomplete) ===
    var searchTimeout;
    $('#searchInput').on('keyup', function () {
        clearTimeout(searchTimeout);
        var query = $(this).val();

        if (query.length >= 2) {
            searchTimeout = setTimeout(function () {
                productSearch(query);
            }, 300);
        } else {
            $('#searchResults').hide();
        }
    });

    // === Image Zoom Effect ===
    $('.product-image-zoom').on('mouseenter', function () {
        $(this).css('overflow', 'visible');
    }).on('mouseleave', function () {
        $(this).css('overflow', 'hidden');
    });

    // === Star Rating (for reviews) ===
    $('.rating-stars .star').on('click', function () {
        var rating = $(this).data('rating');
        $('#ratingValue').val(rating);

        $(this).parent().find('.star').each(function (index) {
            if (index < rating) {
                $(this).removeClass('bi-star').addClass('bi-star-fill');
            } else {
                $(this).removeClass('bi-star-fill').addClass('bi-star');
            }
        });
    });

    // === Review Form Submission ===
    $(document).on('submit', '#reviewForm', function (e) {
        e.preventDefault();

        var form = $(this);
        var submitBtn = $('#submitReviewBtn');

        if (!this.checkValidity()) {
            form.addClass('was-validated');
            return;
        }

        var rating = $('#ratingValue').val();
        if (!rating) {
            showNotification('error', 'Please select a star rating');
            return;
        }

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting...');

        $.ajax({
            url: SITE_URL + '/ajax/submit-review.php',
            method: 'POST',
            data: form.serialize() + '&csrf_token=' + CSRF_TOKEN,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showNotification('success', response.message);

                    // Hide the form and the "No reviews" message
                    $('#review-form-container').fadeOut();
                    $('#no-reviews-msg').fadeOut();

                    // Create a preview of the submitted review
                    var stars = '';
                    for (var i = 1; i <= 5; i++) {
                        stars += '<i class="bi bi-star' + (i <= rating ? '-fill' : '') + ' text-warning"></i> ';
                    }

                    var titleHtml = form.find('input[name="title"]').val() ? '<h6>' + form.find('input[name="title"]').val() + '</h6>' : '';
                    var comment = form.find('textarea[name="comment"]').val();

                    var previewHtml = `
                        <div class="card mb-3 border-warning shadow-sm fade-in">
                            <div class="card-body">
                                <div class="badge bg-warning text-dark mb-2">Your review is awaiting moderation</div>
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">You</h6>
                                        <div class="mb-2">${stars}</div>
                                    </div>
                                    <small class="text-muted">Just now</small>
                                </div>
                                ${titleHtml}
                                <p class="mb-0">${comment}</p>
                            </div>
                        </div>
                    `;

                    $('#new-review-placeholder').html(previewHtml).hide().fadeIn();

                    // Optional: Still reload after few seconds to sync with server if desired, 
                    // but now the user sees the "Details" immediately.
                    // setTimeout(function () { location.reload(); }, 5000);
                } else {
                    showNotification('error', response.message);
                    submitBtn.prop('disabled', false).html('Submit Review');
                }
            },
            error: function () {
                showNotification('error', 'An error occurred. Please try again.');
                submitBtn.prop('disabled', false).html('Submit Review');
            }
        });
    });

    // === Form Validation ===
    $('.needs-validation').on('submit', function (e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

});

// === Helper Functions ===

/**
 * Update cart count in header
 */
function updateCartCount() {
    $.ajax({
        url: SITE_URL + '/ajax/get-cart-count.php',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                $('.cart-badge').text(response.count);
            }
        }
    });
}

/**
 * Update cart item quantity
 */
function updateCartItem(cartId, quantity) {
    $.ajax({
        url: SITE_URL + '/ajax/update-cart.php',
        method: 'POST',
        data: {
            cart_id: cartId,
            quantity: quantity,
            csrf_token: CSRF_TOKEN
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                // Reload page to update totals
                location.reload();
            } else {
                showNotification('error', response.message || 'Failed to update cart');
            }
        },
        error: function () {
            showNotification('error', 'An error occurred');
        }
    });
}

/**
 * Remove item from cart
 */
function removeCartItem(cartId) {
    $.ajax({
        url: SITE_URL + '/ajax/remove-from-cart.php',
        method: 'POST',
        data: { 
            cart_id: cartId,
            csrf_token: CSRF_TOKEN 
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                showNotification('success', 'Item removed from cart');
                location.reload();
            } else {
                showNotification('error', response.message || 'Failed to remove item');
            }
        },
        error: function () {
            showNotification('error', 'An error occurred');
        }
    });
}

/**
 * Apply coupon code
 */
function applyCoupon(code) {
    $.ajax({
        url: SITE_URL + '/ajax/apply-coupon.php',
        method: 'POST',
        data: { 
            coupon_code: code,
            csrf_token: CSRF_TOKEN
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                showNotification('success', 'Coupon applied successfully!');
                location.reload();
            } else {
                showNotification('error', response.message || 'Invalid coupon code');
            }
        },
        error: function () {
            showNotification('error', 'An error occurred');
        }
    });
}

/**
 * Product search
 */
function productSearch(query) {
    $.ajax({
        url: SITE_URL + '/ajax/search.php',
        method: 'GET',
        data: { q: query },
        dataType: 'json',
        success: function (response) {
            if (response.success && response.results.length > 0) {
                displaySearchResults(response.results);
            } else {
                $('#searchResults').hide();
            }
        }
    });
}

/**
 * Display search results
 */
function displaySearchResults(results) {
    var html = '<div class="list-group">';

    results.forEach(function (product) {
        html += '<a href="' + SITE_URL + '/product-detail.php?id=' + product.id + '" class="list-group-item list-group-item-action">';
        html += '<div class="d-flex">';
        if (product.image) {
            html += '<img src="' + SITE_URL + '/uploads/products/' + product.image + '" style="width: 50px; height: 50px; object-fit: cover; margin-right: 15px;">';
        }
        html += '<div>';
        html += '<h6 class="mb-1">' + product.name + '</h6>';
        html += '<small>₹' + product.price + '</small>';
        html += '</div></div></a>';
    });

    html += '</div>';

    $('#searchResults').html(html).show();
}

/**
 * Show notification
 */
function showNotification(type, message) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle';

    var alert = $('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
        '<i class="bi ' + icon + '"></i> ' + message +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>');

    // Prepend to body or specific container
    if ($('#notificationContainer').length) {
        $('#notificationContainer').html(alert);
    } else {
        $('body').prepend('<div id="notificationContainer" style="position: fixed; top: 80px; right: 20px; z-index: 9999; max-width: 350px;"></div>');
        $('#notificationContainer').html(alert);
    }

    // Auto hide after 5 seconds
    setTimeout(function () {
        alert.fadeOut(function () {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Format price
 */
function formatPrice(price) {
    return '₹' + parseFloat(price).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Change main product image
 */
function changeImage(src) {
    $('#mainImage').attr('src', src);
    $('.thumbnail-img').removeClass('border-primary');
    // Add border to clicked thumbnail if needed (requires passing element or using event)
}
