@if (!isset(auth()->user()->user_invoice->company_name))
    <script>alert('Fontos! Töltsd ki a felhasználó adataidat mindenek elött.')</script>
@endif  
<section class="user-box bg-dark text-light">
    <div class="row">
            <div class="user-info">
                <p class="user-name text-light">
                    {{auth()->user()->name}}
                </p>
                <p class="user-email">
                    
                    {{auth()->user()->email}}
                    
                    @if (auth()->user()->hasVerifiedEmail())
                    <br>
                    <span>hitelesítve</span>
                    @else
                    <span>nincs hitelesítve</span>
                    <form action="{{route('verification.send')}}" method="post">
                        @csrf
                    <button type="submit">email ujraküldés</button>
                    </form>
                    @endif
                </p>
                <p class="user-company">
                    {{auth()->user()->user_invoice->company_name ?? "Nincs megadva cégnév"}}
                    
                </p>
                
            </div>
            <div class="user-info">
                <p class="">
                    Összes rendelés:
                </p>
                <p>
                    {{auth()->user()->orders->count()}}
                </p>
            </div>
        <div class="row user-btns">
            <a class="btn btn-primary my-2 bg-gradient" href="{{route('account.orders')}}">Rendelések</a>
            <a class="btn btn-primary my-2 bg-gradient" href="{{route('account.userinfo')}}">Fh. Adatok</a>
            
        </div>
    
</div>
</section>