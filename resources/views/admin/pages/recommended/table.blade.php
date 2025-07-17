{{-- Asztali nézet --}}
<div class="d-none d-lg-block table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th></th>
                <th>#ID</th>
                <th>Cikkszám</th>
                <th>Megnevezés</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr data-name="{{ $product->title }}" data-id="{{ $product->id }}">
                <td>
                    <input type="checkbox" class="product-checkbox" value="{{ $product->id }}"
    {{ in_array($product->id, $recommendedIds) ? 'checked' : '' }}>
                </td>
                <td>{{ $product->id }}</td>
                <td>{{ $product->serial_number }}</td>
                <td>{{ $product->title }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Mobil nézet – kártyák --}}
<div class="d-block d-lg-none">
    <div class="row row-cols-1 g-3">
        @foreach($products as $product)
        <div class="col">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-start gap-3">
                    <input 
                        type="checkbox" 
                        class="product-checkbox"
                        value="{{ $product->id }}"
                        {{ in_array($product->id, $recommendedIds) ? 'checked' : '' }}
                        style="margin-top: 5px;">
                    <div>
                        <h5 class="card-title mb-1">{{ $product->title }}</h5>
                        <p class="mb-1"><strong>ID:</strong> {{ $product->id }}</p>
                        <p class="mb-0"><strong>Cikkszám:</strong> {{ $product->serial_number }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="mt-3">
    {{ $products->appends(request()->query())->links() }}
</div>
