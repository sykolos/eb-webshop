{{-- Asztali: táblázat --}}
<div class="card d-none d-lg-block">
    <div class="card-header bg-dark text-white">
        <h5>Termékek</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped align-middle">
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
                    <td>{{ $product->price }} Ft</td>
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

        <div class="d-flex justify-content-center mt-4">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
</div>

{{-- Mobil: kártyák --}}
<div class="d-block d-lg-none">
    <div class="row row-cols-1 g-3">
        @foreach ($products as $product)
        <div class="col">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">{{ $product->title }}</h5>
                    <p class="mb-1"><strong>Cikkszám:</strong> {{ $product->serial_number }}</p>
                    <p class="mb-1"><strong>Ár:</strong> {{ $product->price }} Ft</p>
                    <p class="mb-1"><strong>Egység:</strong>
                        @foreach ($units as $unit)
                            @if ($unit->id == $product->unit_id)
                                {{ $unit->unit }} ({{ $unit->quantity }} {{ $unit->measure }})
                            @endif
                        @endforeach
                    </p>
                    <p class="mb-1"><strong>Kategória:</strong> {{ $product->category->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Létrehozva:</strong> {{ \Carbon\Carbon::parse($product->created_at)->format('Y-m-d') }}</p>

                    @if ($product->image)
                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->title }}" style="max-height: 100px;" class="mt-2 img-fluid">
                    @else
                        <em class="d-block mt-2">Nincs kép</em>
                    @endif

                    <div class="mt-3 d-grid gap-2">
                        <a href="{{ route('adminpanel.products.edit', $product->id) }}" class="btn btn-primary w-50 me-2">
                            <i class="bi bi-pencil-square"></i> Szerkeszt
                        </a>
                        <form action="{{ route('adminpanel.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Biztosan törlöd?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger w-50">
                                <i class="bi bi-trash"></i> Törlés
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <div class="mt-4">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
</div>
