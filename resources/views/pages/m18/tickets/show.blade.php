@extends('layouts.v6.account')

@section('title', $ticket->ticket_number)

@section('content')
<div class="v6-section">
    <div class="v6-section__header">
        <div>
            <span class="v6-eyebrow">{{ $ticket->ticket_number }}</span>
            <h1>{{ $ticket->subject }}</h1>
            <p>
                {{ config('tickets.type_labels.' . $ticket->type, $ticket->type) }}
                · {{ config('tickets.status_labels.' . $ticket->status, $ticket->status) }}
                @if($ticket->order)
                    · {{ $ticket->order->displayNumber() }}
                @endif
            </p>
        </div>
        <a class="v6-button v6-button--outline" href="{{ route('account.tickets') }}">Vissza a ticketekhez</a>
    </div>

    @if(session('success'))
        <div class="v6-toast">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="v6-toast v6-toast--warning" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="v6-subsection">
        <div class="v6-section__header">
            <div>
                <h2>Kommunikáció</h2>
                <p>Az itt látható üzenetek partnernek látható válaszok. Belső admin-megjegyzések nem jelennek meg.</p>
            </div>
        </div>

        @forelse($ticket->messages as $message)
            <article class="v6-panel" style="margin-bottom:12px;border-left:3px solid var(--eb-red)">
                <div class="v6-panel__header">
                    <div>
                        <strong>{{ $message->author?->name ?: 'Ügyfélszolgálat' }}</strong>
                        <p>{{ $message->created_at?->format('Y-m-d H:i') }}</p>
                    </div>
                </div>

                <div style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $message->body }}</div>

                @if($message->attachments->isNotEmpty())
                    <div class="v6-media-list" style="margin-top:14px">
                        @foreach($message->attachments as $attachment)
                            <div class="v6-media-item">
                                <span>{{ $attachment->original_name }}</span>
                                <a class="v6-button v6-button--small v6-button--outline" href="{{ route('account.tickets.attachments.download', $attachment) }}">Letöltés</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        @empty
            <div class="v6-empty-state">Nincs még látható üzenet.</div>
        @endforelse
    </div>

    <section class="v6-panel">
        <div class="v6-panel__header">
            <div>
                <h2>Válasz küldése</h2>
                <p>Az üzenet a tickethez kerül, és az ügyfélszolgálati munkalistában megjelenik.</p>
            </div>
        </div>

        <form class="v6-form" method="POST" action="{{ route('account.tickets.messages.store', $ticket) }}" enctype="multipart/form-data">
            @csrf
            <label>
                Üzenet
                <textarea name="body" rows="7" required maxlength="10000"></textarea>
            </label>
            <label>
                Csatolmány
                <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp">
                <small>PDF vagy kép, legfeljebb {{ config('tickets.attachment_max_kilobytes') / 1024 }} MB.</small>
            </label>
            <button class="v6-button v6-button--primary" type="submit">Válasz elküldése</button>
        </form>
    </section>
</div>
@endsection
