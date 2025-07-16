<table class="table table-stripped">
    <thead>
        <tr>
            <th>Id</th>
            <th>Felhasználó név</th>
            <th>Email</th>                   
            <th>Létrehozva</th>                          
            <th>Műveletek</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
        <tr>
            <td>{{$user->id}}</td>
            <td>{{$user->name}}</td>
            <td>{{$user->email}}</td>
            <td>{{\Carbon\Carbon::parse($user->created_at)}}</td>
            <td>
                <div class="d-flex" style="gap: 5px">
                    <a href="{{ route('adminpanel.users.user_view', $user->id) }}" class="btn btn-secondary">Részletek</a>
                    <form action="{{ route('adminpanel.users.user_destroy', $user->id) }}" method="post">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Törlés</button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Lapozás --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $users->appends(request()->query())->links() }}
        </div>