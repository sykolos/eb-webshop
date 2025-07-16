<div class="col-lg-9">
<header class="d-sm-flex align-items-center border-bottom mb-4 pb-3">
                        <strong class="d-block py-2">Összesen {{ $products->total() }} termék találat</strong>
                        <div class="ms-auto">
                            <form action="{{ route('orderpage') }}" method="get">
                                @csrf
                                @foreach(request()->except('sort', 'page') as $key => $val)
                                    @if(is_array($val))
                                        @foreach($val as $v)
                                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                        @endforeach
                                    @else
                                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                    @endif
                                @endforeach
                                <select name="sort" onchange="this.form.submit()" class="form-select d-inline-block w-auto border pt-1">
                                    <option value="">Alapértelmezett</option>
                                    <option value="a_to_z" {{ request('sort') == 'a_to_z' ? 'selected' : '' }}>A-Z sorrend</option>
                                    <option value="z_to_a" {{ request('sort') == 'z_to_a' ? 'selected' : '' }}>Z-A sorrend</option>
                                    <option value="low_high" {{ request('sort') == 'low_high' ? 'selected' : '' }}>Ár szerint növekő</option>
                                    <option value="high_low" {{ request('sort') == 'high_low' ? 'selected' : '' }}>Ár szerint csökkenő</option>
                                </select>
                            </form>
                        </div>
                    </header>

                    <div class="products-row">
                        @foreach($products as $product)
                            <x-product-box :product="$product" />
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $products->appends(request()->query())->links() }}
                    </div>
                    </div>