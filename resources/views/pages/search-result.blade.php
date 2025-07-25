@extends('layouts.master')
@section('title','Keresés eredmény')
@section('content')
<main class="homepage">
    
    <section class="products">
    <div class="container w-100">
            <div class="row mt-5 w-100">
              <div class="col-lg-3">
                <div class="sidebar-item card sidebar-search-card my-3">
                  <h4 class="card-header bg-dark text-white" id="">
                    Keresés
                  </h4>
                  <div>
                    @include('pages.components.shop.search')
                  </div>
                  {{-- <div id="" class="">
                    <div class="card-body">
                      <input type="text" class="form-control" id="">
                    </div>
                  </div> --}}
                </div>
                  <div class="sidebar-item card sidebar-category-card my-3">
                      <h4 class="card-header bg-dark text-white" id="">
                        Kategóriák
                      </h4>
                      <div id="" class="">
                        <div class="card-body mx-3">
                          <ul class="list-unstyled">
                            <li><a href="{{route('orderpage')}}" class="text-dark">Összes Termék</a></li>
                            @foreach($categories as $category)
                              <li><a href="{{route('orderpage',['category'=>$category->name])}}" class="text-dark ">{{$category->name}}</a></li>
                            @endforeach
                          </ul>
                        </div>
                      </div>
                    </div>
                    <div class="sidebar-item card sidebar-search-card my-3">
                      <h4 class="card-header bg-dark text-white" id="">
                        Szűrők
                      </h4>
                      <div id="" class="">
                        <div class="card-body">
                        </div>
                      </div>
                    </div>
                    
              </div>
        <div class="col-lg-9">
            <header class="d-sm-flex align-items-center border-bottom mb-4 pb-3">
              <strong class="d-block py-2 ">Keresésett termék '{{request()->input('query')}}'</strong> 
              </strong>
              <span class="mx-2">Összesen {{$products->count()}} találat </span>
              <div class="ms-auto">
              </div>
              </header>
                    <div class="products-row w-100">
                        @foreach($products as $product)
                            <x-product-box :product='$product'/>
                        @endforeach
                    </div>
                       
    </div>
    </div>
    </div>
    
</section>
    
</main>
@endsection