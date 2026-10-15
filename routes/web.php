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
use App\Http\Controllers\AdminExportController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RecommendedController;
use App\Http\Controllers\PagesController;
use Illuminate\Auth\Events\PasswordReset;
use App\Support\PasswordRules;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AccountpageController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\ProductRecommend;
use App\Http\Controllers\AdminTwoFactorController;
use App\Http\Controllers\PrivateFileController;
use App\Http\Controllers\DesignSystemController;
use App\Http\Controllers\PartnerRegistrationRequestController;
use App\Http\Controllers\ProductMediaController;
use App\Http\Controllers\FilterDefinitionController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\BannerAdminController;
use App\Http\Controllers\ActivityTrackingController;
use App\Http\Controllers\EmailAdminController;
use App\Http\Controllers\EmailPublicController;
use App\Http\Controllers\AutomationAdminController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AdminTicketController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;



Route::get('/success',[PagesController::class,'success'])->name('success');
Route::get('/',[PagesController::class,'home'])->name('home');
Route::post('/tracking/events', [ActivityTrackingController::class, 'store'])->middleware('throttle:tracking')->name('tracking.events');
Route::get('/email/unsubscribe/{delivery}', [EmailPublicController::class, 'unsubscribe'])->middleware('signed')->name('email.unsubscribe');
Route::get('impresszum/',[PagesController::class,'impresszum'])->name('impresszum');
Route::get('aszf/',[PagesController::class,'aszf'])->name('aszf');
Route::get('gyik/',[PagesController::class,'gyik'])->name('gyik');
Route::get('elallasi-nyilatkozat/',[PagesController::class,'eny'])->name('elallasi-nyilatkozat');
Route::get('adatkezelesi-nyilatkozat/',[PagesController::class,'any'])->name('adatkezelesi-nyilatkozat');
Route::get('info/{slug}', function (string $slug) {
    abort_unless(in_array($slug, ['szallitas', 'fizetes', 'katalogus'], true), 404);
    return view('pages.m14.info', ['slug' => $slug]);
})->name('info');
Route::get('info/katalogus/download', function () {
    $path = public_path(config('site.catalog.path'));
    abort_unless(is_file($path), 404);
    return response()->download($path, config('site.catalog.name'), ['Content-Type' => 'application/pdf']);
})->name('info.catalog.download');
Route::get('contact/',[PagesController::class,'contact'])->name('contact');
Route::get('cart/',[PagesController::class,'cart'])->name('cart')->middleware('auth');
Route::get('checkout/',[PagesController::class,'checkout'])->name('checkout')->middleware('auth');
Route::post('validcheckout/',[Checkout::class,'checkout'])->name('validcheckout')->middleware('auth');
Route::get('products/{id}',[PagesController::class,'product'])->whereNumber('id')->name('product');
Route::get('termekek/{slug}',[PagesController::class,'productBySlug'])->name('product.slug');
Route::get('shop/',[PagesController::class,'orderpage'])->name('orderpage');
Route::get('quick-order/',[PagesController::class,'quickOrder'])->name('quickorder');
Route::post('/shop/view-mode', function (\Illuminate\Http\Request $request) {
    $request->validate(['view' => 'required|in:quick,normal']);
    session(['shop_view' => $request->view]);
    return back();
})->name('shop.setViewMode');
Route::get('/shop/quick-products', [PagesController::class, 'quickProductsAjax'])->name('shop.quick.products');
Route::get('search/autocomplete', [PagesController::class, 'autocomplete'])->name('search.autocomplete');
Route::get('search/',[PagesController::class,'search'])->name('search');
//cart

Route::post('add-to-cart/{id}/{q}/{m}',[CartController::class,'addtocart'])
    ->whereNumber('id')
    ->middleware('auth')
    ->name('addtocart');
Route::post('remove-from-cart/{key}',[CartController::class,'removefromcart'])
    ->whereNumber('key')
    ->middleware('auth')
    ->name('removefromcart');
//cart count
Route::get('/cart/count', [CartController::class, 'cartCount'])->middleware('auth')->name('cart.count');
//cart preview
Route::get('/cart/preview', [CartController::class, 'previewcart'])->middleware('auth')->name('cart.preview');
//-cart preview gyorsgombok
Route::post('/cart/update', [CartController::class, 'updateQuantity'])->middleware('auth')->name('cart.update');
Route::delete('/cart/remove', [CartController::class, 'removeItem'])->middleware('auth')->name('cart.remove');

//recommended products
Route::get('/recommendations', [ProductRecommend::class, 'getRecommended'])->name('recommendations');
Route::get('/top-categories', [ProductRecommend::class, 'getTopCategories'])->name('top-categories');
Route::get('/product/{id}/similar', [ProductRecommend::class, 'similarProducts'])
    ->whereNumber('id')
    ->name('product.similar');


//users
Route::get('login/',[AuthController::class,'showlogin'])->name('login')->middleware('guest');
Route::get('register/',[AuthController::class,'showregister'])->name('register')->middleware(['guest', 'throttle:registration']);
Route::post('register',[AuthController::class,'postregister'])->name('register.submit')->middleware(['guest', 'throttle:registration']);
Route::post('/login',[AuthController::class,'postlogin'])->name('login.submit')->middleware(['guest', 'throttle:login']);
Route::post('logout/',[AuthController::class,'postlogout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/products/{productId}/media/{mediaId}/download', [ProductMediaController::class, 'customerDownload'])
        ->whereNumber('productId')->whereNumber('mediaId')->name('product.media.download');
    Route::get('/private-files/{path}', [PrivateFileController::class, 'download'])
        ->where('path', '.*')
        ->middleware('signed')
        ->name('private-file.download');
    Route::get('/adminpanel/security/2fa/challenge', [AdminTwoFactorController::class, 'challenge'])
        ->name('admin.2fa.challenge');
    Route::post('/adminpanel/security/2fa/challenge', [AdminTwoFactorController::class, 'verify'])
        ->name('admin.2fa.verify');
});

Route::group(['prefix'=>'account','middleware'=>'auth'],function(){
    Route::get('/',[AccountpageController::class,'dashboard'])->name('account');
    Route::get('/orders',[AccountpageController::class,'orders'])->name('account.orders');
    Route::get('/userinfo',[AccountpageController::class,'userinfo'])->name('account.userinfo');
    Route::get('/details/{id}',[AccountpageController::class,'details'])->name('account.details');
    Route::get('/modify',[AccountpageController::class,'modify'])->name('account.modify');
    Route::put('/edit',[AccountpageController::class,'edit'])->name('account.edit');
    //pdf generálás
    Route::get('/getpdf/{id}',[AccountpageController::class,'getpdf'])->name('account.getpdf');
    Route::post('/orders/{order}/reorder',[AccountpageController::class,'reorder'])->name('account.orders.reorder');
    Route::get('/favorites',[AccountpageController::class,'favorites'])->name('account.favorites');
    Route::post('/favorites/{productId}',[AccountpageController::class,'addFavorite'])->whereNumber('productId')->name('account.favorite.store');
    Route::post('/shipping/{address}/default',[AccountpageController::class,'setDefaultShipping'])->name('shipping.default');
    Route::get('/tickets', [TicketController::class, 'index'])->name('account.tickets');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('account.tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('account.tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('account.tickets.show');
    Route::post('/tickets/{ticket}/messages', [TicketController::class, 'message'])->name('account.tickets.messages.store');
    Route::get('/tickets/attachments/{attachment}', [TicketController::class, 'download'])->name('account.tickets.attachments.download');

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
        Route::get('/carts', [\App\Http\Controllers\AdminActivityController::class, 'carts'])->name('adminpanel.carts');
        Route::get('/activity', [\App\Http\Controllers\AdminActivityController::class, 'activity'])->name('adminpanel.activity');
        Route::get('/emails', [EmailAdminController::class, 'index'])->name('adminpanel.emails');
        Route::get('/automations', [AutomationAdminController::class, 'index'])->name('adminpanel.automations');
        Route::post('/automations/{rule}', [AutomationAdminController::class, 'update'])->name('adminpanel.automations.update');
        Route::post('/automations/{rule}/run', [AutomationAdminController::class, 'run'])->name('adminpanel.automations.run');
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('adminpanel.tickets');
        Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('adminpanel.tickets.show');
        Route::put('/tickets/{ticket}', [AdminTicketController::class, 'update'])->name('adminpanel.tickets.update');
        Route::post('/tickets/{ticket}/messages', [AdminTicketController::class, 'message'])->name('adminpanel.tickets.messages.store');
        Route::get('/tickets/attachments/{attachment}', [AdminTicketController::class, 'download'])->name('adminpanel.tickets.attachments.download');
        Route::post('/emails/campaigns', [EmailAdminController::class, 'campaign'])->name('adminpanel.emails.campaigns.store');
        Route::post('/emails/test', [EmailAdminController::class, 'test'])->name('adminpanel.emails.test');
        Route::post('/emails/campaigns/{campaign}/send', [EmailAdminController::class, 'send'])->name('adminpanel.emails.campaigns.send');
        Route::post('/emails/campaigns/{campaign}/cancel', [EmailAdminController::class, 'cancel'])->name('adminpanel.emails.campaigns.cancel');
        Route::get('/design-system', [DesignSystemController::class, 'index'])->name('adminpanel.design-system');
        Route::get('/design-system/shop', [DesignSystemController::class, 'shop'])->name('adminpanel.design-system.shop');
        Route::get('/design-system/account', [DesignSystemController::class, 'account'])->name('adminpanel.design-system.account');
        Route::get('/design-system/admin', [DesignSystemController::class, 'admin'])->name('adminpanel.design-system.admin');
        Route::get('/exports', [AdminExportController::class, 'index'])->name('adminpanel.exports');
        Route::post('/exports', [AdminExportController::class, 'store'])->name('adminpanel.exports.store');
        Route::get('/exports/template/products', [AdminExportController::class, 'template'])->name('adminpanel.exports.template');
        Route::post('/exports/import/products', [AdminExportController::class, 'importPreview'])->name('adminpanel.exports.import.preview.store');
        Route::get('/exports/import/{import}/preview', [AdminExportController::class, 'importView'])->name('adminpanel.exports.import.preview');
        Route::post('/exports/import/{import}/remap', [AdminExportController::class, 'importRemap'])->name('adminpanel.exports.import.remap');
        Route::post('/exports/import/{import}/apply', [AdminExportController::class, 'importApply'])->name('adminpanel.exports.import.apply');
        Route::get('/exports/import/{import}/errors', [AdminExportController::class, 'importErrors'])->name('adminpanel.exports.import.errors');
        Route::get('/exports/{export}/download', [AdminExportController::class, 'download'])->name('adminpanel.exports.download');
        Route::get('/banners', [BannerAdminController::class, 'index'])->name('adminpanel.banners');
        Route::post('/banners', [BannerAdminController::class, 'store'])->name('adminpanel.banners.store');
        Route::post('/banners/{banner}/publish', [BannerAdminController::class, 'publish'])->name('adminpanel.banners.publish');
        Route::post('/banners/{banner}/archive', [BannerAdminController::class, 'archive'])->name('adminpanel.banners.archive');
        Route::delete('/banners/{banner}', [BannerAdminController::class, 'destroy'])->name('adminpanel.banners.destroy');
        Route::get('/security/2fa', [AdminTwoFactorController::class, 'setup'])->name('admin.2fa.setup');
        Route::post('/security/2fa/confirm', [AdminTwoFactorController::class, 'confirm'])->name('admin.2fa.confirm');
        Route::post('/security/2fa/disable', [AdminTwoFactorController::class, 'disable'])->name('admin.2fa.disable');
        Route::get('/partner-requests', [PartnerRegistrationRequestController::class, 'index'])->name('adminpanel.partner-requests');
        Route::post('/partner-requests/{registrationRequest}', [PartnerRegistrationRequestController::class, 'update'])->name('adminpanel.partner-requests.update');
        
        //products group
        Route::group(['prefix'=>'products',], function(){
            Route::get('/',[ProductController::class, 'index'])->name('adminpanel.products');
            Route::get('/data-quality',[ProductController::class, 'dataQuality'])->name('adminpanel.products.data-quality');
            Route::get('/create',[ProductController::class, 'create'])->name('adminpanel.products.create');
            Route::post('/create',[ProductController::class, 'store'])->name('adminpanel.products.store');
            Route::post('/{productId}/media',[ProductMediaController::class, 'store'])->name('adminpanel.products.media.store');
            Route::put('/{productId}/media/{mediaId}',[ProductMediaController::class, 'update'])->name('adminpanel.products.media.update');
            Route::delete('/{productId}/media/{mediaId}',[ProductMediaController::class, 'destroy'])->name('adminpanel.products.media.destroy');
            Route::get('/{productId}/media/{mediaId}/download',[ProductMediaController::class, 'download'])->name('adminpanel.products.media.download');
            Route::post('/{id}/restore',[ProductController::class, 'restore'])->name('adminpanel.products.restore');
            Route::get('/{id}',[ProductController::class, 'edit'])->name('adminpanel.products.edit');
            Route::put('/{id}',[ProductController::class, 'update'])->name('adminpanel.products.update');
            Route::delete('/{id}',[ProductController::class, 'destroy'])->name('adminpanel.products.destroy');
        });

        //categories group
        Route::group(['prefix'=>'categories',], function(){
            Route::get('/',[CategoryController::class, 'index'])->name('adminpanel.categories');
            Route::post('/',[CategoryController::class, 'store'])->name('adminpanel.category.store');
            Route::put('/{id}',[CategoryController::class, 'update'])->name('adminpanel.category.update');
            Route::delete('/{id}',[CategoryController::class, 'destroy'])->name('adminpanel.category.destroy');    
        });

        Route::group(['prefix' => 'filters'], function () {
            Route::post('/', [FilterDefinitionController::class, 'store'])->name('adminpanel.filters.store');
            Route::put('/{id}', [FilterDefinitionController::class, 'update'])->name('adminpanel.filters.update');
            Route::delete('/{id}', [FilterDefinitionController::class, 'destroy'])->name('adminpanel.filters.destroy');
        });

        //order group
        Route::group(['prefix'=>'orders',], function(){
            Route::get('/',[OrderController::class, 'index'])->name('adminpanel.orders');
            Route::get('/create',[OrderController::class, 'create'])->name('adminpanel.orders.create');
            Route::post('/create',[OrderController::class, 'store'])->name('adminpanel.orders.store');
            Route::get('/view/{id}',[OrderController::class, 'view'])->name('adminpanel.orders.view');
            Route::post('/bulk-status',[OrderController::class, 'bulkStatus'])->name('adminpanel.orders.bulk-status');
            Route::post('/store-filter-url', [OrderController::class, 'storeFilterUrl'])->name('adminpanel.orders.storeFilterUrl');
            Route::post('/{id}',[OrderController::class, 'updateStatus'])->name('adminpanel.orders.status.update');
            Route::delete('/{id}',[OrderController::class, 'destroy'])->name('adminpanel.orders.destroy');
            //pdf generálás
            Route::get('/{id}',[OrderController::class,'getpdf'])->name('adminpanel.orders.getpdf');
        });

        //users group
        Route::group(['prefix'=>'users',], function(){
            Route::get('/',[AdminController::class, 'users'])->name('adminpanel.users');
            Route::post('/',[AdminController::class, 'user_store'])->name('adminpanel.users.user_store');
            Route::get('/{id}',[AdminController::class, 'user_view'])->name('adminpanel.users.user_view');
            Route::put('/{id}',[AdminController::class, 'user_update'])->name('adminpanel.users.user_update');
            Route::post('/{id}/restore',[AdminController::class, 'user_restore'])->name('adminpanel.users.user_restore');
            Route::post('/{id}/password-reset',[AdminController::class, 'user_password_reset'])->name('adminpanel.users.user_password_reset');
            Route::delete('/{id}',[AdminController::class, 'user_destroy'])->name('adminpanel.users.user_destroy');
        });

        //units group
        Route::group(['prefix'=>'units',], function(){
            Route::get('/',[AdminController::class, 'units'])->name('adminpanel.units');
            Route::post('/',[AdminController::class, 'unit_store'])->name('adminpanel.units.unit_store');
            Route::put('/{id}',[AdminController::class, 'unit_update'])->name('adminpanel.units.unit_update');
            Route::delete('/{id}',[AdminController::class, 'unit_destroy'])->name('adminpanel.units.unit_destroy');
            });

        // special prices – új AJAX-os változat
        Route::group(['prefix' => 'special-prices'], function () {
            Route::get('/', [PricingController::class, 'index'])->name('adminpanel.special_prices');
            Route::get('/ajax/{user}', [PricingController::class, 'matrix'])->name('adminpanel.special_prices.ajax');
            Route::post('/set', [PricingController::class, 'set'])->name('adminpanel.special_prices.set');
            Route::delete('/delete', [PricingController::class, 'delete'])->name('adminpanel.special_prices.delete');
            Route::get('/matrix-ajax/{user}', [PricingController::class, 'matrix'])->name('adminpanel.special_prices.matrix');
            Route::get('/product-users-ajax/{product}', [PricingController::class, 'productUsers'])->name('adminpanel.special_prices.product_users');
            Route::get('/product-select-search', [PricingController::class, 'productSearch'])->name('adminpanel.special_prices.product_select');
            Route::post('/save-matrix', [PricingController::class, 'saveMatrix'])->name('adminpanel.special_prices.save_matrix');
            Route::post('/global-category-update', [PricingController::class, 'globalCategory'])->name('adminpanel.special_prices.global_category');
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
    return view('auth.verify');
    })->middleware('auth')->name('verification.notice');
    
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        
        return redirect('/account');
        })->middleware(['auth', 'signed'])->name('verification.verify');
        
//email verification újraküldés
Route::post('/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
 
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:verification-resend'])->name('verification.send');

//password reset 
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');


 
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email|max:255']);
 
    $status = Password::sendResetLink(
        $request->only('email')
    );
 
    return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)]);
})->middleware(['guest', 'throttle:password-reset-request'])->name('password.email');

Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.passwords.reset', ['token' => $token]);
})->middleware('guest')->name('password.reset');
Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => PasswordRules::required(),
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
})->middleware(['guest', 'throttle:password-reset'])->name('password.update');

//password change by user
Route::get('/change-password', [AuthController::class, 'changePassword'])->name('change-password')->middleware('auth');;
Route::post('/change-password', [AuthController::class, 'updatePassword'])->name('update-password')->middleware(['auth', 'throttle:password-change']);

//-------------sending emails
Route::post('/send-contact',[PagesController::class,'sendContact'])->name('sendContact')->middleware('throttle:contact');
