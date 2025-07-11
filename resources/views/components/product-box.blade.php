
    <a href="{{route('product',$product->id)}}" class="product-box bg-dark">
        <div class="image bg-light">
            <img class="img-fluid w-100" src="{{ Storage::url($product->image) }}" alt="{{ $product->title }}" width="100" height="100" loading="lazy">
        </div>
        <div class="product-info">
            <div class="product-title">{{$product->title}}</div>
            <div class="product-serialnum">{{$product->serial_number}} </div>
            <div class="product-price">
                {{$product->getPriceForUser()}} Ft /{{$product->product_unit->measure}}
            </div>
    
        </div>
        
    </a> 
 