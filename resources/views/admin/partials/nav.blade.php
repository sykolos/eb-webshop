<aside class="side-nav" id="adminSidebar">
    <div class="logo">
        <img src="{{asset('img/logo.svg')}}" alt="">
        ADMINPANEL
    </div>
    <ul>
        <li> <a href="{{route('adminpanel')}}">Vezérlőpult</a>
        </li>
        <li> <a href="{{route('adminpanel.products')}}">Termékek</a>
        </li>
        <li> <a href="{{route('adminpanel.categories')}}">Kategóriák</a>
        </li>
        <li> <a href="{{route('adminpanel.orders')}}">Rendelések</a>
        </li>
        <li> <a href="{{route('adminpanel.users')}}">Felhasználók</a>
        </li>        
        <li> <a href="{{route('adminpanel.units')}}">Mennyiségi egységek</a>
        </li>               
        <li><a href="{{ route('adminpanel.special_prices') }}">Külön árak</a>
        </li>               
        <li><a href="{{ route('adminpanel.recommended.edit') }}">Kiemelt Termékek</a>
        </li>
    </ul>

    <div class="logout">
        <a href="{{route('home')}}" class="mx-2 text-white">Vissza a főoldalra</a>
        <form action="{{route('logout')}}" method="post">
            @csrf
            <button type="submit">
             Logout
            </button>
        </form>
    </div>
</aside>
