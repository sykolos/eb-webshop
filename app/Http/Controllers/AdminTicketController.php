<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Services\SecureFileStorage;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class AdminTicketController extends Controller
{
    public function index(Request $request, TicketService $tickets)
    {
        $ticketPage = $tickets->adminQuery($request->only(['search', 'status', 'priority', 'type', 'assigned_to']))->paginate(20)->withQueryString();
        $ticketPage->getCollection()->each(fn (Ticket $ticket) => $ticket->setAttribute('has_unread', $tickets->unread($ticket, $request->user())));

        return view('admin.m18.tickets.index', [
            'tickets' => $ticketPage,
            'statuses' => config('tickets.status_labels'),
            'types' => config('tickets.type_labels'),
            'priorities' => config('tickets.priority_labels'),
            'assignees' => User::query()->where('account_status', 'active')->get()->filter(fn (User $user) => $user->hasPermission('admin.access'))->sortBy('name'),
        ]);
    }

    public function show(Ticket $ticket, TicketService $tickets)
    {
        return view('admin.m18.tickets.show', [
            'ticket' => $tickets->forActor($ticket, request()->user(), true),
            'statuses' => config('tickets.status_labels'),
            'priorities' => config('tickets.priority_labels'),
            'assignees' => User::query()->where('account_status', 'active')->get()->filter(fn (User $user) => $user->hasPermission('admin.access'))->sortBy('name'),
        ]);
    }

    public function update(Request $request, Ticket $ticket, TicketService $tickets)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('tickets.status_labels')))],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:191'],
        ]);
        $tickets->changeStatus($ticket, $request->user(), $data['status'], $data['note'] ?? null);
        $assignee = $data['assigned_to'] ? User::findOrFail($data['assigned_to']) : null;
        $tickets->assign($ticket, $request->user(), $assignee);
        return back()->with('success', 'A ticket állapota és felelőse frissítve.');
    }

    public function message(Request $request, Ticket $ticket, TicketService $tickets)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:10000'],
            'is_internal' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'file', 'max:'.config('tickets.attachment_max_kilobytes'), 'mimes:pdf,jpg,jpeg,png,webp'],
        ]);
        $tickets->addMessage($ticket, $request->user(), $data['body'], $request->boolean('is_internal'), $request->file('attachment'));
        return back()->with('success', $request->boolean('is_internal') ? 'A belső megjegyzés elmentve.' : 'A válasz elküldve.');
    }

    public function download(TicketAttachment $attachment, TicketService $tickets, SecureFileStorage $storage)
    {
        $attachment = $tickets->attachmentForActor($attachment, request()->user());
        return $storage->download($attachment->path);
    }
}
