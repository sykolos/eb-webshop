<div class="card">
    <div class="card-header bg-dark text-white">
        <h5>Termékek</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Cikkszám</th>
                    <th>Termékneve</th>
                    <th>Egység ár</th>
                    <th>Menny.egység</th>
                    <th>Kategória</th>
                    <th>Kép</th>
                    <th>Létrehozva</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->serial_number }}</td>
                    <td>{{ $product->title }}</td>
                    <td>{{ $product->price }}</td>
                    <td>
                        @foreach ($units as $unit)
                            @if ($unit->id == $product->unit_id)
                                {{ $unit->unit }} ({{ $unit->quantity }} {{ $unit->measure }})
                            @endif
                        @endforeach
                    </td>
                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                    <td>
                        @if ($product->image)
                            <img src="{{ Storage::url($product->image) }}" style="height: 40px" alt="{{ $product->title }}" loading="lazy">
                        @else
                            <em>Nincs kép</em>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($product->created_at)->format('Y-m-d') }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('adminpanel.products.edit', $product->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                            <form action="{{ route('adminpanel.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Biztosan törlöd?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Lapozás --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
</div>