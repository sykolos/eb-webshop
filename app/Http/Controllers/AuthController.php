<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\User_invoce;
use App\Models\User_shipping;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Support\PasswordRules;
use App\Http\Requests\LoginRequest;
use App\Services\AuditLogger;
use App\Services\CartService;

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
       // dd($request->all());
        //validation
        $request->validate([
            'name'=>'required|min:3|max:225',            
            'email'=>'required|email|max:225|unique:users',            
            'password' => PasswordRules::required(),
        ]);
        //registration
        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);

        

        $id=$user->id;
        
        $user_i=User_invoce::create([
            'user_id'=>$id,
        ]);
        $user_s=User_shipping::create([
            'user_id'=>$id,
        ]);

        // event(new Registered($user));
        $user->sendEmailVerificationNotification();
        //login
        Auth::login($user);
        app(CartService::class)->migrateSession($user, (array) $request->session()->get('cart', []));
        $request->session()->forget('cart');

        return back()->with('success','Sikeres bejelentkezés');
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
