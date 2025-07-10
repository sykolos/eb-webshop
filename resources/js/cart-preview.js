let previewTimer = null;

$('#cart-icon-container').on('mouseenter', function () {
    clearTimeout(previewTimer);

    $('#cart-preview')
        .html('<div class="text-center py-3"><div class="spinner-border" role="status"><span class="visually-hidden">Betöltés...</span></div></div>')
        .stop()
        .fadeIn(150);

    $.get("/cart/preview", function (data) {
        $('#cart-preview').html(data);
    });
});



$('#cart-icon-container').on('mouseleave', function () {
    previewTimer = setTimeout(function () {
        $('#cart-preview').stop().fadeOut(150);
    }, 300);
});

$('#cart-preview').on('mouseenter', function () {
    clearTimeout(previewTimer);
}).on('mouseleave', function () {
    previewTimer = setTimeout(function () {
        $('#cart-preview').stop().fadeOut(150);
    }, 300);
});

// Mennyiség növelés/csökkentés
$(document).on('click', '.btn-increase, .btn-decrease', function () {
    const key = $(this).data('key');
    const change = $(this).hasClass('btn-increase') ? 1 : -1;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    $.post('/cart/update', { key, change, _token: token }, function () {
        reloadCartPreview();
        updateCartCount();
    });
});

// Törlés
$(document).on('click', '.btn-remove', function () {
    const key = $(this).data('key');
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    $.ajax({
        url: '/cart/remove',
        type: 'DELETE',
        data: { key, _token: token },
        success: function () {
            reloadCartPreview();
            updateCartCount();
        }
    });
});

// Kosár előnézet újratöltés
function reloadCartPreview() {
    $('#cart-preview')
        .html('<div class="text-center py-3"><div class="spinner-border" role="status"></div></div>')
        .stop()
        .fadeIn(150);

    $.get("/cart/preview", function (data) {
        $('#cart-preview').html(data);
    });
}

function updateCartCount() {
    $.get('/cart/count', function (data) {
        $('.cart-badge').text(data.count);
    });
}