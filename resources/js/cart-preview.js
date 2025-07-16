let previewTimer = null;

// Kosár előnézet betöltése hoverre
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

//  mennyiség módosítás
$(document).on('click', '.btn-increase, .btn-decrease', function () {
    const key = $(this).data('key');
    const change = $(this).hasClass('btn-increase') ? 1 : -1;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    //  Spinner jelenjen meg azonnal
    $('#cart-preview').html('<div class="text-center py-3"><div class="spinner-border" role="status"></div></div>');

    $.post('/cart/update', { key, change, _token: token }, function () {
        reloadCartPreview();
        updateCartCount();
    });
});

//  termék törlés
$(document).on('click', '.btn-remove', function () {
    const key = $(this).data('key');
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    //  Spinner jelenjen meg azonnal
    $('#cart-preview').html('<div class="text-center py-3"><div class="spinner-border" role="status"></div></div>');

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

// Kosár újratöltés
function reloadCartPreview() {
    $.get("/cart/preview", function (data) {
        $('#cart-preview').html(data);
    });
}

// Kosár badge frissítés
window.updateCartCount = function updateCartCount() {
    $.get('/cart/count', function (data) {
        $('.cart-badge').text(data.count);
    });
}

