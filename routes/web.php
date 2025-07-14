<?php
namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Checkout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RecommendedController;
use App\Http\Controllers\PagesController;
use Illuminate\Auth\Events\PasswordReset;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AccountpageController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\ProductRecommend;
use Illuminate\Foundation\Auth\EmailVerificationRequest;



Route::get('/success',[PagesController::class,'success'])->name('success');
Route::get('/',[PagesController::class,'home'])->name('home');
Route::get('impresszum/',[PagesController::class,'impresszum'])->name('impresszum');
Route::get('aszf/',[PagesController::class,'aszf'])->name('aszf');
Route::get('gyik/',[PagesController::class,'gyik'])->name('gyik');
Route::get('elallasi-nyilatkozat/',[PagesController::class,'eny'])->name('elallasi-nyilatkozat');
Route::get('adatkezelesi-nyilatkozat/',[PagesController::class,'any'])->name('adatkezelesi-nyilatkozat');
Route::get('contact/',[PagesController::class,'contact'])->name('contact');
Route::get('cart/',[PagesController::class,'cart'])->name('cart')->middleware('auth');
Route::get('checkout/',[PagesController::class,'checkout'])->name('checkout')->middleware('auth');
Route::post('validcheckout/',[Checkout::class,'checkout'])->name('validcheckout')->middleware('auth');
Route::get('products/{id}',[PagesController::class,'product'])->name('product')->middleware('auth');
Route::get('shop/',[PagesController::class,'orderpage'])->name('orderpage')->middleware('auth');
Route::get('search/',[PagesController::class,'search'])->name('search')->middleware('auth');
//cart

Route::post('add-to-cart/{id}/{q}/{m}',[CartController::class,'addtocart'])->name('addtocart');
Route::post('remove-from-cart/{id}',[CartController::class,'removefromcart'])->name('removefromcart');
//cart count
Route::get('/cart/count', [CartController::class, 'cartCount'])->name('cart.count');
//cart preview
Route::get('/cart/preview', [CartController::class, 'previewcart'])->name('cart.preview');
//-cart preview gyorsgombok
Route::post('/cart/update', [CartController::class, 'updateQuantity'])->name('cart.update');
Route::delete('/cart/remove', [CartController::class, 'removeItem'])->name('cart.remove');
//cart count AJAX esetén
Route::get('/cart/count', function () {
    return response()->json([
        'count' => collect(session('cart', []))->sum('quantity')
    ]);
})->name('cart.count');

//recommended products
Route::get('/recommendations', [ProductRecommend::class, 'recommended']);
Route::get('/top-categories', [ProductRecommend::class, 'topCategories']);
Route::get('/product/{id}/similar', [ProductRecommend::class, 'similarProducts']);


//users
Route::get('login/',[AuthController::class,'showlogin'])->name('login')->middleware('guest');
Route::get('register/',[AuthController::class,'showregister'])->name('register')->middleware('admin');
Route::post('register',[AuthController::class,'postregister'])->name('register.submit')->middleware('admin');
Route::post('/login',[AuthController::class,'postlogin'])->name('login.submit')->middleware('guest');
Route::post('logout/',[AuthController::class,'postlogout'])->name('logout')->middleware('auth');

Route::group(['prefix'=>'account','middleware'=>'auth'],function(){
    Route::get('/',[PagesController::class,'account'])->name('account');
    Route::get('/orders',[AccountpageController::class,'orders'])->name('account.orders');
    Route::get('/userinfo',[AccountpageController::class,'userinfo'])->name('account.userinfo');
    Route::get('/details/{id}',[AccountpageController::class,'details'])->name('account.details');
    Route::get('/modify',[AccountpageController::class,'modify'])->name('account.modify');
    Route::put('/edit',[AccountpageController::class,'edit'])->name('account.edit');
    //pdf generálás
    Route::get('/getpdf/{id}',[AccountpageController::class,'getpdf'])->name('account.getpdf');

    // 💡 Szállítási címek kezelése (csak új/létrehoz/szerkeszt/töröl)
    Route::get('/shipping/create', [ShippingAddressController::class, 'create'])->name('shipping.create');
    Route::post('/shipping', [ShippingAddressController::class, 'store'])->name('shipping.store');
    Route::get('/shipping/{address}/edit', [ShippingAddressController::class, 'edit'])->name('shipping.edit');
    Route::put('/shipping/{address}', [ShippingAddressController::class, 'update'])->name('shipping.update');
    Route::delete('/shipping/{address}', [ShippingAddressController::class, 'destroy'])->name('shipping.destroy');
});

//adminpanel group
Route::group(['prefix' =>'adminpanel','middleware'=>'admin'],function(){

    Route::get('/',[AdminController::class, 'dashboard'])->name('adminpanel');

    //products group
    Route::group(['prefix'=>'products',], function(){
        Route::get('/',[ProductController::class, 'index'])->name('adminpanel.products');
        Route::get('/create',[ProductController::class, 'create'])->name('adminpanel.products.create');
        Route::post('/create',[ProductController::class, 'store'])->name('adminpanel.products.store');
        Route::get('/{id}',[ProductController::class, 'edit'])->name('adminpanel.products.edit');
        Route::put('/{id}',[ProductController::class, 'update'])->name('adminpanel.products.update');
        Route::delete('/{id}',[ProductController::class, 'destroy'])->name('adminpanel.products.destroy');
    });
    //categories group
    Route::group(['prefix'=>'categories',], function(){
        Route::get('/',[CategoryController::class, 'index'])->name('adminpanel.categories');
        Route::post('/',[CategoryController::class, 'store'])->name('adminpanel.category.store');
        Route::delete('/{id}',[CategoryController::class, 'destroy'])->name('adminpanel.category.destroy');

    });
    //order group
    Route::group(['prefix'=>'orders',], function(){
        Route::get('/',[OrderController::class, 'index'])->name('adminpanel.orders');
        Route::get('/view/{id}',[OrderController::class, 'view'])->name('adminpanel.orders.view');
        Route::post('/{id}',[OrderController::class, 'updateStatus'])->name('adminpanel.orders.status.update');
        //pdf generálás
        Route::get('/{id}',[OrderController::class,'getpdf'])->name('adminpanel.orders.getpdf');
    });
    //users group
    Route::group(['prefix'=>'users',], function(){
        Route::get('/',[AdminController::class, 'users'])->name('adminpanel.users');
        Route::post('/',[AdminController::class, 'user_store'])->name('adminpanel.users.user_store');
        Route::get('/{id}',[AdminController::class, 'user_view'])->name('adminpanel.users.user_view');
        Route::delete('/{id}',[AdminController::class, 'user_destroy'])->name('adminpanel.users.user_destroy');

    });
    //units group
    Route::group(['prefix'=>'units',], function(){
        Route::get('/',[AdminController::class, 'units'])->name('adminpanel.units');
        Route::post('/',[AdminController::class, 'unit_store'])->name('adminpanel.units.unit_store');
        Route::delete('/{id}',[AdminController::class, 'unit_destroy'])->name('adminpanel.units.unit_destroy');
    });
    // special prices – új AJAX-os változat
    Route::group(['prefix' => 'special-prices'], function () {
        Route::get('/', [AdminController::class, 'special_prices_show'])->name('adminpanel.special_prices');
        Route::get('/ajax/{user}', [AdminController::class, 'ajaxProductList'])->name('adminpanel.special_prices.ajax');
        Route::post('/set', [AdminController::class, 'ajaxSetPrice'])->name('adminpanel.special_prices.set');
        Route::delete('/delete', [AdminController::class, 'ajaxDeletePrice'])->name('adminpanel.special_prices.delete');
    });
    //recommended group
    Route::group(['prefix'=>'recommended'], function(){
        Route::get('/ajax', [AdminController::class, 'ajaxList'])->name('adminpanel.recommended.ajax');

        Route::get('/', [AdminController::class, 'recommendedEdit'])->name('adminpanel.recommended.edit');
        Route::post('/', [AdminController::class, 'recommendedUpdate'])->name('adminpanel.recommended.update');
    });
});




//email verification
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/account');
})->middleware(['auth', 'signed'])->name('verification.verify');

//email verification újraküldés
Route::post('/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
 
    return back()->with('message', 'Verification link sent!');
})->middleware('auth')->name('verification.send');

//password reset 
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');


 
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);
 
    $status = Password::sendResetLink(
        $request->only('email')
    );
 
    return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.passwords.reset', ['token' => $token]);
})->middleware('guest')->name('password.reset');
Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);
 
    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));
 
            $user->save();
 
            event(new PasswordReset($user));
        }
    );
 
    return $status === Password::PASSWORD_RESET
                ? redirect()->route('login')->with('status', __($status))
                : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');

//password change by user
Route::get('/change-password', [AuthController::class, 'changePassword'])->name('change-password')->middleware('auth');;
Route::post('/change-password', [AuthController::class, 'updatePassword'])->name('update-password')->middleware('auth');;

//-------------sending emails
Route::post('/send-contact',[MailController::class,'sendContact'])->name('sendContact');



Route::get('/clear', function() {
    Artisan::call('optimize:clear');
    return redirect()->back();
});