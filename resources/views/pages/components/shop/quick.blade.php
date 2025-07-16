<div id="ajax-cart-popup" class="pop-up-overlay d-none">
    <div class="pop-up-box">
        <div class="pop-up-title">Sikeres kosárba helyezés</div>
        <div class="pop-up-actions">
            <button type="button" class="btn btn-outlined" id="closePopup">Tovább vásárlok</button>
            <a href="{{ route('cart') }}" class="btn btn-primary">Irány a kosár</a>
        </div>
    </div>
</div>
<div class="col-lg-12">
<div class="card bg-dark p-4 mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-6">
            <label for="quick-search" class="form-label fw-bold text-white">Keresés</label>
            <input type="text" id="quick-search" class="form-control" placeholder="Pl. kábelcsatorna...">
        </div>

        <div class="col-md-6">
            <label for="quick-category" class="form-label fw-bold text-white">Kategória</label>
            <select id="quick-category" class="form-select">
                <option value="">Összes kategória</option>
                <option value="latest"> Új termékeink</option>
                <option value="highlighted"> Kiemelt termékeink</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div id="quick-spinner" class="text-center mt-4 d-none">
    <div class="spinner-border text-dark" role="status">
        <span class="visually-hidden">Betöltés...</span>
    </div>
</div>
<div id="quick-info" class="mb-3 text-muted small"></div>
<div id="quick-products-wrapper" class="d-grid gap-3"></div>


</div>
<script>
function loadQuickProducts() {
    $('#quick-spinner').removeClass('d-none');
    const search = $('#quick-search').val();
    const category = $('#quick-category').val();

    $.get("{{ route('shop.quick.products') }}", { search, category }, function(data) {
        $('#quick-products-wrapper').html(data.html);
        $('#quick-spinner').addClass('d-none');
        $('#quick-info').html(data.info); 
    });
}

$('#quick-search, #quick-category').on('input change', function () {
    clearTimeout(window.quickTimeout);
    window.quickTimeout = setTimeout(loadQuickProducts, 400);
});
$(document).on('click', '#quick-products-wrapper .pagination a', function (e) {
    e.preventDefault();
    const url = $(this).attr('href');
    $('#quick-loading').removeClass('d-none');
    $.get(url, function (data) {
        $('#quick-products-wrapper').html(data.html);
        $('#quick-info').html(data.info);
        $('#quick-loading').addClass('d-none');
    });
});

$(document).on('click', '.add-to-cart', function () {
    const $button = $(this);
    const card = $button.closest('.card');
    const id = $button.data('id');
    const q = $button.data('q');
    const m = $button.data('m');
    const quantity = card.find('.quick-quantity').val() || 1;

    const originalText = $button.html();
    $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span>');

    $.post('/add-to-cart/' + id + '/' + q + '/' + m, {
        _token: '{{ csrf_token() }}',
        quantity: quantity
    }).done(function () {
        // Kosár visszajelző popup megjelenítése
        const ajaxPopup = document.getElementById('ajax-cart-popup');
        if (ajaxPopup) {
            ajaxPopup.classList.remove('d-none');
            ajaxPopup.style.display = 'flex';

            setTimeout(() => {
                ajaxPopup.classList.add('d-none');
                ajaxPopup.style.display = 'none';
            }, 4000);
        }

        // Kosár mennyiség frissítése
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        }

        // Gomb visszaállítása
        setTimeout(() => {
            $button.prop('disabled', false).html(originalText);
        }, 1500);
    }).fail(function () {
        $button.prop('disabled', false).html(originalText);
        alert('Hiba történt a kosárba helyezés során.');
    });
});

$(document).ready(function () {
    loadQuickProducts();
});
</script>
