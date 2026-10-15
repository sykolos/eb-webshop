<div class="v6-table-wrap">
    <table class="v6-table" style="min-width:1040px">
        <thead><tr><th>ID</th><th>Partner / cég</th><th>Email</th><th>Állapot</th><th>Szerepkör</th><th>Utolsó belépés</th><th>Utolsó látogatás</th><th>Utolsó rendelés</th><th>Rendelések</th><th>Művelet</th></tr></thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td><strong>{{ $user->user_invoice?->company_name ?: $user->name }}</strong><br><small>{{ $user->name }}{{ $user->trashed() ? ' · archivált' : '' }}</small></td>
                <td>{{ $user->email }}</td>
                <td><span class="v6-status v6-status--{{ $user->trashed() || $user->account_status === 'blocked' ? 'danger' : ($user->account_status === 'active' ? 'success' : 'warning') }}">{{ $user->trashed() ? 'archived' : ($user->account_status ?: 'active') }}</span></td>
                <td>{{ $user->technicalRole() === 'administrator' ? 'Adminisztrátor' : 'Partner' }}</td>
                <td>{{ optional($user->last_login_at)->format('Y. m. d. H:i') ?: '-' }}</td>
                <td>{{ optional($user->last_seen_at)->format('Y. m. d. H:i') ?: '-' }}</td>
                <td>{{ $user->orders_max_created_at ? \Carbon\Carbon::parse($user->orders_max_created_at)->format('Y. m. d. H:i') : 'Nincs rendelés' }}</td>
                <td>{{ $user->orders_count }}</td>
                <td><a class="v6-button v6-button--small v6-button--outline" href="{{ route('adminpanel.users.user_view', $user->id) }}">Adatlap</a></td>
            </tr>
        @empty
            <tr><td colspan="10"><div class="v6-empty"><strong>Nincs találat</strong><span>A megadott szűrőkkel nem található felhasználó.</span></div></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="v6-actions" style="justify-content:center;margin-top:18px">{{ $users->links() }}</div>
