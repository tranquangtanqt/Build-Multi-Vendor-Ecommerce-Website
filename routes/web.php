<?php

use App\DataTables\UsersDataTable;
use App\Helpers\ImageFilter;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Intervention\Image\ImageManagerStatic;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('user/{id}/edit', function($id){
    return $id;
})->name('user.edit');

Route::get('/dashboard', function (UsersDataTable $dataTable) {
    return $dataTable->render('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


Route::get('image', function(){
    $img = ImageManagerStatic::make('car.jpg');

    $img->filter(new ImageFilter(15));


    // $img->save('car1.jpg', 80);
    return $img->response();
});

Route::get('shop', [CartController::class, 'shop'])->name('shop');
Route::get('cart', [CartController::class, 'cart'])->name('cart');
Route::get('add-to-cart/{product_id}', [CartController::class, 'addToCart'])->name('add-to-cart');

Route::get('qty-increment/{rowId}', [CartController::class, 'qtyIncrement'])->name('qty-increment');
Route::get('qty-decrement/{rowId}', [CartController::class, 'qtyDecrement'])->name('qty-decrement');
Route::get('remove-product/{rowId}', [CartController::class, 'removeProduct'])->name('remove-product');


Route::get('create-role', function(){

    // $role = Role::create(['name' => 'publisher']);

    // return $role;

    // $permission = Permission::create(['name' => 'edit articles']);

    // return $permission;

    $user = auth()->user();
    // $user->assignRole('writer');
    // $user->givePermissionTo('edit articles');

    if($user->can('delete articles')){
        return 'user have permission';
    }else {
        return 'user dont have permission';
    }
});

Route::get('posts', function(){

    $posts = Post::all();
    return view('post.post', compact('posts'));
});

Route::get('/auth/redirect', function(){
    return Socialite::driver('github')->redirect();
})->name('github.login');

Route::get('/auth/callback', function(){
    $user = Socialite::driver('github')->user();

    $user = User::firstOrCreate([
        'email' => $user->email
    ],[
        'name' => $user->name,
        'password' => bcrypt(Str::random(24))
    ]);

    Auth::login($user, true);

    return redirect('/dashboard');
});
