{{--  Asztali nézet – táblázat --}}
<div class="d-none d-lg-block table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Id</th>
                <th>Felhasználónév</th>
                <th>Email</th>                   
                <th>Létrehozva</th>                          
                <th>Műveletek</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ \Carbon\Carbon::parse($user->created_at)->format('Y-m-d') }}</td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="{{ route('adminpanel.users.user_view', $user->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-eye"></i> Részletek
                        </a>
                        <form action="{{ route('adminpanel.users.user_destroy', $user->id) }}" method="post" onsubmit="return confirm('Biztosan törlöd?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i> Törlés
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{--  Mobil nézet – kártyák --}}
<div class="d-block d-lg-none">
    <div class="row row-cols-1 g-3">
        @foreach ($users as $user)
        <div class="col">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">{{ $user->name }}</h5>
                    <p class="mb-1"><strong>Email:</strong> {{ $user->email }}</p>
                    <p class="mb-1"><strong>Felhasználó ID:</strong> {{ $user->id }}</p>
                    <p class="mb-1"><strong>Létrehozva:</strong> {{ \Carbon\Carbon::parse($user->created_at)->format('Y-m-d') }}</p>
                    
                    <div class="mt-3 d-grid gap-2">
                        <a href="{{ route('adminpanel.users.user_view', $user->id) }}" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Részletek
                        </a>
                        <form action="{{ route('adminpanel.users.user_destroy', $user->id) }}" method="post" onsubmit="return confirm('Biztosan törlöd?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Törlés
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Lapozás --}}
<div class="d-flex justify-content-center mt-4">
    {{ $users->appends(request()->query())->links() }}
</div>
