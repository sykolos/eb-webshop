<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\User_invoce;
use App\Models\User_shipping;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            'password'=>'required|min:8|same:password-confirm',
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

        return back()->with('success','Sikeres bejelentkezés');
    }
    //logout
    public function postlogout(){
        Auth::logout();
        return back();
    }

    //login user

    public function postlogin(Request $request){
        //validate
        $details = $request->validate([     
            'email'=>'required|email|',            
            'password'=>'required',
        ]);
        //login
        if(Auth::attempt($details)){
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
            'new_password' => 'required|confirmed',
        ]);


        #Match The Old Password
        if(!Hash::check($request->old_password, auth()->user()->password)){
            return back()->with("error", "Old Password Doesn't match!");
        }


        #Update the new Password
        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with("status", "Password changed successfully!");
}


    
}
