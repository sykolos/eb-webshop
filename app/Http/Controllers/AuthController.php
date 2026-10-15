<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\User_invoce;
use App\Models\User_shipping;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Support\PasswordRules;
use App\Http\Requests\LoginRequest;
use App\Services\AuditLogger;
use App\Services\CartService;
use App\Models\PartnerRegistrationRequest;

class AuthController extends Controller
{
    //show login page
    public function showlogin(){
        return view('pages.login');
    }



    //show register page
    public function showregister(){
        return view('pages.register');
    }
    
    //register user
    public function postregister(Request $request){
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:225'],
            'email' => ['required', 'email', 'max:225', 'unique:users,email'],
            'company_name' => ['required', 'string', 'max:191'],
            'vatnumber' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:1000'],
            'password' => PasswordRules::required(),
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'partner',
                'account_status' => 'pending',
            ]);

            User_invoce::create([
                'user_id' => $user->id,
                'company_name' => $data['company_name'],
                'address' => '',
                'vatnumber' => $data['vatnumber'] ?? null,
            ]);

            if (!empty($data['phone'])) {
                User_shipping::create([
                    'user_id' => $user->id,
                    'address' => '',
                    'phone' => $data['phone'],
                    'receiver' => $data['name'],
                    'is_default' => true,
                ]);
            }

            PartnerRegistrationRequest::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'company_name' => $data['company_name'],
                'vatnumber' => $data['vatnumber'] ?? null,
                'phone' => $data['phone'] ?? null,
                'message' => $data['message'] ?? null,
                'status' => 'pending',
            ]);
        });

        return redirect()->route('login')->with('status', 'A partnerkérelmed beérkezett. Jóváhagyás után emailben vagy a megadott adatokkal tudsz belépni.');
    }
    //logout
    public function postlogout(Request $request, AuditLogger $audit){
        if ($request->user()) {
            $audit->record('auth.logout', $request->user());
        }
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back();
    }

    //login user

    public function postlogin(LoginRequest $request, AuditLogger $audit){
        $details = $request->validated();
        //login
        // login "remember me" opcióval
        $remember = $request->has('remember');

        if(Auth::attempt($details, $remember)){
            $request->session()->regenerate();

            $user = $request->user();
            if (($user->account_status ?: 'active') !== 'active') {
                Auth::logout();
                return back()->withInput($request->only('email', 'remember'))->withErrors([
                    'email' => match ($user->account_status) {
                        'pending' => 'A partnerkérelmed még jóváhagyásra vár.',
                        'rejected' => 'A partnerkérelmet elutasítottuk. Vedd fel velünk a kapcsolatot.',
                        'blocked' => 'A fiók jelenleg tiltva van.',
                        default => 'A fiók jelenleg nem aktív.',
                    },
                ]);
            }
            app(CartService::class)->migrateSession($user, (array) $request->session()->get('cart', []));
            $request->session()->forget('cart');
            $user->forceFill(['last_login_at' => now()])->save();
            $audit->record('auth.login', $user);

            if ($user->hasTwoFactorEnabled()) {
                $request->session()->put(config('two_factor.pending_session_key'), $user->id);
                $request->session()->forget(config('two_factor.verified_session_key'));

                return redirect()->route('admin.2fa.challenge');
            }

            return redirect()->intended('/');
        }
        return back()->withErrors([
            'email'=>'Érvénytelen belépési adatok'
        ]);

    }

    
    public function changePassword(){
        return view('auth.passwords.change-password');
    }
    public function updatePassword(Request $request)
{
        # Validation
        $request->validate([
            'old_password' => 'required',
            'new_password' => PasswordRules::required(),
        ]);


        #Match The Old Password
        if(!Hash::check($request->old_password, auth()->user()->password)){
            return back()->with("error", "A régi jelszó nem egyezik!");
        }


        #Update the new Password
        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with("status", "A jelszó sikeresen cserélve!");
}


    
}
