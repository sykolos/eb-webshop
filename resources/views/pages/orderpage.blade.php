@extends('layouts.master')
@section('title','Rendelés oldal')
@section('content')
<main class="shop-page">
    
    <section class="products">
    <div class="container w-100">
            <div class="row mt-5 product-list w-100">
                <div class="col-lg-3 ">
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
                                <li><a href="{{route('orderpage',['category'=>$category->id])}}" class="text-dark ">{{$category->name}}</a></li>
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
                <strong class="d-block py-2">Összesen {{$products->count()}} termék </strong>
                <div class="ms-auto">
                  <form action="{{route('orderpage')}}" method="get">
                    @csrf
                  <select name="sort" onchange="this.form.submit()" class="form-select d-inline-block w-auto border pt-1">
                    <option value="">Alapértelmezett</option>
                    <option value="a_to_z">A-Z sorrend</option>
                    <option value="z_to_a">Z-A sorrend</option>
                    <option value="low_high">Ár szerint növekő</option>
                    <option value="high_low">Ár szerint csökkenő</option>
                  </select>
                </form> 
                </div>
              </header>
                    <div class="products-row">
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