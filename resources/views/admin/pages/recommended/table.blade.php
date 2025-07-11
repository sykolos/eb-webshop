<table class="table table-striped">
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
        <tr>
            <td>
                <input 
                    type="checkbox" 
                    name="products[]" 
                    value="{{ $product->id }}"
                    {{ in_array($product->id, $recommendedIds) ? 'checked' : '' }}
                >
            </td>
            <td>{{ $product->id }}</td>
            <td>{{ $product->serial_number }}</td>
            <td>{{ $product->title }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="d-flex justify-content-center mt-3">
    {{ $products->appends(request()->query())->links() }}
</div>
