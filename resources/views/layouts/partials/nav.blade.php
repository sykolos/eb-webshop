

  <nav class="navbar navbar-light sticky-top navbar-expand-lg bg-opacity-75 fixed-top
    ">
        <div class="container-fluid lead text-uppercase">
          <a class="navbar-brand" href="{{route('home')}}">
            <img class="logo img-fluid" src="{{asset('img/logo.svg')}}" alt="ElectroBusiness" ></a>
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="{{route('home')}}">Főoldal</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="{{route('contact')}}">Kapcsolat</a>
              </li>
              @guest
              <li class="nav-item">
                <a class="nav-link" href="{{route('login')}}">Bejelentkezés</a>
              </li>
              @endguest
              @auth
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-person-fill"></i>
                </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="{{route('account')}}">Fiók</a></li>
                  <li><a class="dropdown-item" href="{{route('orderpage')}}">Rendelés</a></li>
                  <li><a class="dropdown-item" href="{{route('cart')}}">Kosár</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                  <form action="{{route('logout')}}" method="post">
                    @csrf
                    <input type="submit" value="KIJELENTKEZÉS" class="dropdown-item" />
                  </form>
                </li>

                  @if(auth()->user()->is_admin==1)
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="{{route('adminpanel')}}">admin</a></li>
                  @endif
                </ul>
              </li>
              <li class="nav-item">
              </li>
              @endauth
      
              

            </ul>
          </div>
        </div>
      </nav>

      