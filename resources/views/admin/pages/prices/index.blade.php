@extends('layouts.admin')
@section('title','Külön árak')
@section('content')
<div class="container mt-4">
    <h1 class="page-title">Felhasználóspecifikus árak</h1>

    <div class="row mb-4">
        <div class="col-md-4">
            <label for="userSelect">Válassz felhasználót:</label>
            <select class="form-control" id="userSelect">
                <option value="">-- Felhasználó kiválasztása --</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <h4 id="selectedUserLabel" class="mb-3" style="display:none;"></h4>
    <div id="loadingIndicator" class="mb-3 text-info" style="display:none;">Betöltés...</div>

    <div id="searchWrapper" class="mb-3" style="display:none;">
        <input type="text" id="productSearch" class="form-control" placeholder="Keresés cikkszám vagy név alapján...">
    </div>
    <div id="filterOptions" class="mb-3" style="display:none;">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="hasPrice" value="1">
            <label class="form-check-label" for="hasPrice">Csak ahol van külön ár</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="noPrice" value="1">
            <label class="form-check-label" for="noPrice">Csak ahol nincs külön ár</label>
        </div>
        <button id="resetFilters" class="btn btn-secondary btn-sm ms-3">Szűrők törlése</button>
    </div>

    <div id="productTableWrapper" style="display:none;">
        <div class="table-responsive">
            <table class="table table-bordered" id="productTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cikkszám</th>
                        <th>Megnevezés</th>
                        <th>Alapár</th>
                        <th>Külön ár</th>
                        <th>Mentés</th>
                        <th>Törlés</th>
                    </tr>
                </thead>
                <tbody id="productTableBody"></tbody>
            </table>
        </div>
        <div id="paginationWrapper"></div>
    </div>
</div>

<script>
    $(document).ready(function () {
        let selectedUser = null;

        $('#userSelect').on('change', function () {
            selectedUser = $(this).val();
            let userName = $(this).find('option:selected').text();

            if (selectedUser) {
                $('#selectedUserLabel').text(`Felhasználó: ${userName}`).show();
                $('#loadingIndicator').show();
                $('#productTableWrapper').hide();
                $('#searchWrapper').show();
                $('#filterOptions').show();
                $('#productSearch').val('');
                $('#hasPrice').prop('checked', false);
                $('#noPrice').prop('checked', false);
                loadProducts(1);
            } else {
                $('#selectedUserLabel').hide();
                $('#productTableWrapper').hide();
                $('#searchWrapper').hide();
                $('#filterOptions').hide();
            }
        });

        function loadProducts(page = 1, query = '') {
            let hasPrice = $('#hasPrice').is(':checked') ? 1 : 0;
            let noPrice = $('#noPrice').is(':checked') ? 1 : 0;

            $.get(`/adminpanel/special-prices/ajax/${selectedUser}?page=${page}&search=${encodeURIComponent(query)}&has_price=${hasPrice}&no_price=${noPrice}`, function (data) {
                $('#productTableBody').empty(); // ← EZ FONTOS!
                data.products.data.forEach(product => {
                    let special = product.special_prices[0]?.price || '';
                    let row = `
                    <tr data-id="${product.id}">
                        <td>${product.id}</td>
                        <td>${product.serial_number || '-'}</td>
                        <td>${product.title}</td>
                        <td>${product.price} Ft</td>
                        <td><input type="number" class="form-control price-input" value="${special}"/></td>
                        <td><button class="btn btn-success btn-save">Mentés</button></td>
                        <td><button class="btn btn-danger btn-delete">Törlés</button></td>
                    </tr>`;
                    $('#productTableBody').append(row);
                });

                $('#paginationWrapper').html(paginate(data.products));
                $('#loadingIndicator').hide();
                $('#productTableWrapper').show();
            });
        }

        function paginate(data) {
            let html = '<nav><ul class="pagination">';
            for (let i = 1; i <= data.last_page; i++) {
                html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>`;
            }
            html += '</ul></nav>';
            return html;
        }

        $(document).on('click', '.pagination .page-link', function (e) {
            e.preventDefault();
            let page = $(this).data('page');
            loadProducts(page, $('#productSearch').val());
        });

        $(document).on('click', '.btn-save', function () {
            let row = $(this).closest('tr');
            let productId = row.data('id');
            let price = row.find('.price-input').val();

            $.post('/adminpanel/special-prices/set', {
                _token: '{{ csrf_token() }}',
                user_id: selectedUser,
                product_id: productId,
                price: price
            }, function () {
                row.addClass('table-success');
                setTimeout(() => row.removeClass('table-success'), 1500);
            });
        });

        $(document).on('click', '.btn-delete', function () {
            let row = $(this).closest('tr');
            let productId = row.data('id');

            $.ajax({
                url: '/adminpanel/special-prices/delete',
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id: selectedUser,
                    product_id: productId
                },
                success: function () {
                    row.find('.price-input').val('');
                    row.addClass('table-warning');
                    setTimeout(() => row.removeClass('table-warning'), 1500);
                }
            });
        });

        $('#productSearch').on('input', function () {
            loadProducts(1, $(this).val());
        });

        $('#hasPrice').on('change', function () {
            if ($(this).is(':checked')) {
                $('#noPrice').prop('checked', false);
            }
            loadProducts(1, $('#productSearch').val());
        });

        $('#noPrice').on('change', function () {
            if ($(this).is(':checked')) {
                $('#hasPrice').prop('checked', false);
            }
            loadProducts(1, $('#productSearch').val());
        });

        $('#resetFilters').on('click', function () {
            $('#productSearch').val('');
            $('#hasPrice').prop('checked', false);
            $('#noPrice').prop('checked', false);
            loadProducts(1, '');
        });
    });
</script>
@endsection
