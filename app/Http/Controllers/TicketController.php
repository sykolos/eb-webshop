<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Services\SecureFileStorage;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class TicketController extends Controller
{
    public function index(Request $request, TicketService $tickets)
    {
        $ticketPage = $tickets->customerQuery($request->user())->paginate(12)->withQueryString();
        $ticketPage->getCollection()->each(fn (Ticket $ticket) => $ticket->setAttribute('has_unread', $tickets->unread($ticket, $request->user())));

        return view('pages.m18.tickets.index', [
            'tickets' => $ticketPage,
            'statuses' => config('tickets.status_labels'),
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.m18.tickets.create', [
            'orders' => $request->user()->orders()->latest()->limit(50)->get(),
            'types' => config('tickets.type_labels'),
            'priorities' => config('tickets.priority_labels'),
        ]);
    }

    public function store(Request $request, TicketService $tickets)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:191'],
            'body' => ['required', 'string', 'min:3', 'max:10000'],
            'type' => ['required', Rule::in(array_keys(config('tickets.type_labels')))],
            'priority' => ['required', Rule::in(array_keys(config('tickets.priority_labels')))],
            'order_id' => ['nullable', 'integer'],
            'attachment' => ['nullable', 'file', 'max:'.config('tickets.attachment_max_kilobytes'), 'mimes:pdf,jpg,jpeg,png,webp'],
        ]);
        $ticket = $tickets->create($request->user(), $data, $request->file('attachment'));
        return redirect()->route('account.tickets.show', $ticket)->with('success', 'A ticket létrejött: '.$ticket->ticket_number.'.');
    }

    public function show(Ticket $ticket, TicketService $tickets)
    {
        $ticket = $tickets->forActor($ticket, request()->user());
        return view('pages.m18.tickets.show', [
            'ticket' => $ticket,
            'statuses' => config('tickets.status_labels'),
            'priorities' => config('tickets.priority_labels'),
        ]);
    }

    public function message(Request $request, Ticket $ticket, TicketService $tickets)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:10000'],
            'attachment' => ['nullable', 'file', 'max:'.config('tickets.attachment_max_kilobytes'), 'mimes:pdf,jpg,jpeg,png,webp'],
        ]);
        $tickets->addMessage($ticket, $request->user(), $data['body'], false, $request->file('attachment'));
        return back()->with('success', 'Az üzenet elküldve.');
    }

    public function download(TicketAttachment $attachment, TicketService $tickets, SecureFileStorage $storage)
    {
        $attachment = $tickets->attachmentForActor($attachment, request()->user());
        return $storage->download($attachment->path);
    }
}
