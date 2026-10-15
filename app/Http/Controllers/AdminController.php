<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Products;
use App\Models\product_unit;
use Illuminate\Http\Request;
use App\Models\RecommendedProduct;
use App\Services\AuditLogger;
use App\Services\AdminExportCatalogService;
use App\Services\UnitCatalogService;
use App\Services\UserManagementService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Password;
use App\Services\AdminDashboardService;
use Illuminate\Support\Carbon;


class AdminController extends Controller
{
    //dashboard
    public function dashboard(Request $request, AdminDashboardService $dashboard){
        $to = $request->date('to')?->endOfDay() ?: now()->endOfDay();
        $from = $request->date('from')?->startOfDay() ?: $to->copy()->subDays(29)->startOfDay();
        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }
        $summary = $dashboard->summary($from, $to);
        return view('admin.m15.dashboard', [
            'from' => $from, 'to' => $to, 'summary' => $summary,
            'topProducts' => $dashboard->topProducts($from, $to),
            'featuredProducts' => $dashboard->featuredProducts(),
            'availability' => collect($summary['availability'])->sortKeys(),
        ]);
    }

    public function exportCatalog(AdminExportCatalogService $catalog)
    {
        return view('admin.m13.exports.index', ['datasets' => $catalog->all()]);
    }

    public function users(Request $request, UserManagementService $users){
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:191'],
            'status' => ['nullable', 'in:active,pending,rejected,blocked,archived'],
            'role' => ['nullable', 'in:partner,administrator'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date'],
            'login_from' => ['nullable', 'date'],
            'login_to' => ['nullable', 'date'],
            'order_from' => ['nullable', 'date'],
            'order_to' => ['nullable', 'date'],
            'inactive_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'without_orders' => ['nullable', 'boolean'],
            'include_archived' => ['nullable', 'boolean'],
        ]);

        $usersList = $users->list($filters);
        if ($request->ajax()) {
            return view('admin.m12.users.partials.list', ['users' => $usersList])->render();
        }

        return view('admin.m12.users.index', ['users' => $usersList]);
    }
    public function user_store(){
        //létrehozni
    }
    public function user_view(int $id, UserManagementService $users)    {
        return view('admin.m12.users.view', ['user' => $users->find($id)]);
    }

    public function user_update(Request $request, int $id, UserManagementService $users)
    {
        $user = $users->find($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:225'],
            'email' => ['required', 'email', 'max:225', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:partner,administrator'],
            'account_status' => ['required', 'in:active,pending,rejected,blocked'],
            'admin_note' => ['nullable', 'string', 'max:5000'],
            'invoice' => ['required', 'array'],
            'invoice.company_name' => ['required', 'string', 'max:191'],
            'invoice.address' => ['nullable', 'string', 'max:191'],
            'invoice.city' => ['nullable', 'string', 'max:191'],
            'invoice.state' => ['nullable', 'string', 'max:191'],
            'invoice.zipcode' => ['nullable', 'string', 'max:30'],
            'invoice.country' => ['nullable', 'string', 'max:191'],
            'invoice.vatnumber' => ['nullable', 'string', 'max:50'],
            'invoice.registrynumber' => ['nullable', 'string', 'max:100'],
            'shipping' => ['nullable', 'array'],
            'shipping.*.address' => ['required', 'string', 'max:191'],
            'shipping.*.city' => ['nullable', 'string', 'max:191'],
            'shipping.*.zipcode' => ['nullable', 'string', 'max:30'],
            'shipping.*.phone' => ['nullable', 'string', 'max:50'],
            'shipping.*.receiver' => ['nullable', 'string', 'max:191'],
            'shipping.*.comment' => ['nullable', 'string', 'max:500'],
            'shipping.*.is_default' => ['nullable', 'boolean'],
            'new_shipping' => ['nullable', 'array'],
            'new_shipping.address' => ['nullable', 'string', 'max:191'],
            'new_shipping.city' => ['nullable', 'string', 'max:191'],
            'new_shipping.zipcode' => ['nullable', 'string', 'max:30'],
            'new_shipping.phone' => ['nullable', 'string', 'max:50'],
            'new_shipping.receiver' => ['nullable', 'string', 'max:191'],
            'new_shipping.comment' => ['nullable', 'string', 'max:500'],
        ]);

        $users->update($user, $data, $request->user());
        return redirect()->route('adminpanel.users.user_view', $id)->with('success', 'A partner adatai frissítve.');
    }

    public function user_destroy(int $id, UserManagementService $users, Request $request)
    {
        $users->archive($users->find($id), $request->user());
        return redirect()->route('adminpanel.users')->with('success', 'A felhasználó archiválva, a történeti adatai megmaradtak.');
    }

    public function user_restore(int $id, UserManagementService $users, Request $request)
    {
        $users->restore($users->find($id), $request->user());
        return back()->with('success', 'A felhasználó visszaállítva.');
    }

    public function user_password_reset(int $id, UserManagementService $users, Request $request)
    {
        $status = $users->sendPasswordReset($users->find($id), $request->user());
        return back()->with($status === Password::RESET_LINK_SENT ? 'success' : 'warning',
            $status === Password::RESET_LINK_SENT ? 'A jelszó-visszaállító link elküldve.' : 'A visszaállító link nem küldhető el: '.$status);
    }
    public function units(){
        $units = product_unit::withCount('products')->orderBy('unit')->get();
        return view('admin.m06.units.index',['units'=>$units]);
    }
    public function unit_store(Request $request, UnitCatalogService $units, AuditLogger $audit)    {
        $data = $request->validate([
            'unit' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'measure' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $unit = $units->create($data);
        $audit->record('unit.created', $unit, ['unit' => $unit->unit, 'measure' => $unit->measure]);

        return back()->with('success','Mennyiségi egység létrehozva');
    }
    public function unit_update(Request $request, int $id, UnitCatalogService $units, AuditLogger $audit){
        $data = $request->validate([
            'unit' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'measure' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $unit = $units->update(product_unit::findOrFail($id), $data);
        $audit->record('unit.updated', $unit, ['unit' => $unit->unit, 'measure' => $unit->measure]);

        return back()->with('success','Mennyiségi egység frissítve');
    }
    public function unit_destroy($id, UnitCatalogService $units, AuditLogger $audit){
        $unit = product_unit::findOrFail($id);
        $name = $unit->unit;
        $units->delete($unit);
        $audit->record('unit.deleted', $unit, ['unit' => $name]);

        return back()->with('success','Sikeresen törölve');
    }    
    
    public function recommendedEdit()
    {
        $products = Products::orderBy('id')->paginate(15);
        $recommendedIds = RecommendedProduct::orderBy('sort_order')->orderBy('id')->pluck('product_id')->toArray();
        $recommendedProducts = Products::whereIn('id', $recommendedIds)->get();

        // Itt készítjük elő a JS-ben használt adatokat
        $recommendedData = $recommendedProducts->keyBy('id')->map(function ($item) {
            return [
                'id' => $item->id,
                'serial_number' => $item->serial_number,
                'title' => $item->title,
            ];
        });

        return view('admin.pages.recommended.index', compact(
            'products',
            'recommendedIds',
            'recommendedProducts',
            'recommendedData'
        ));
    }

    public function ajaxList(Request $request)
    {
        $query = Products::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('serial_number', 'like', $q . '%')
                        ->orWhere('title', 'like', '%' . $q . '%');
            });
        }

        $products = $query->orderBy('id')->paginate(15);
        $recommendedIds = RecommendedProduct::orderBy('sort_order')->orderBy('id')->pluck('product_id')->toArray();

        $html = view('admin.pages.recommended.table', compact('products', 'recommendedIds'))->render();

        return response()->json(['html' => $html]);
    }

    public function recommendedUpdate(Request $request)
    {
        \Log::info('--- Kiemelt termékek mentés indul ---');
        \Log::info('products raw:', $request->all());

        $incomingIds = collect($request->input('products', []))
            ->filter(fn($id) => $id !== '__empty__')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();

        \Log::info('products integer IDs:', $incomingIds);

        $currentIds = RecommendedProduct::pluck('product_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        \Log::info('jelenlegi recommended DB-ben:', $currentIds);

        $idsToDelete = array_diff($currentIds, $incomingIds);

        \Log::info('Törlendő ID-k:', $idsToDelete);

        if (!empty($idsToDelete)) {
            RecommendedProduct::whereIn('product_id', $idsToDelete)->delete();
        }

        foreach ($incomingIds as $sortOrder => $id) {
            RecommendedProduct::updateOrCreate(
                ['product_id' => $id],
                ['sort_order' => $sortOrder]
            );
        }

        return redirect()->back()->with('success', 'Kiemelt termékek frissítve!');
    }

}
