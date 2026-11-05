<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PedidoUsuarioController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VentaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CuponController;
use App\Http\Controllers\Api\ImageController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/api/imagen/{filename}', [ImageController::class, 'show'])
    ->where('filename', '.*')
    ->name('imagen.show');

Route::get('/producto/{id}', [HomeController::class, 'show'])->name('producto.show');

Route::get('/api/provincias/{id_departamento}', [CheckoutController::class, 'getProvincias']);
Route::get('/api/distritos/{id_provincia}', [CheckoutController::class, 'getDistritos']);

Route::middleware('auth')->group(function () {
    Route::get('/carrito',                   [CarritoController::class, 'index'])->name('carrito.index');
    Route::post('/carrito/add/{id}',         [CarritoController::class, 'add'])->name('carrito.add');
    Route::get('/carrito/aumentar/{id}',     [CarritoController::class, 'aumentar'])->name('carrito.aumentar');
    Route::get('/carrito/disminuir/{id}',    [CarritoController::class, 'disminuir'])->name('carrito.disminuir');
    Route::get('/carrito/eliminar/{id}',     [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
});

Route::post('/pago/webhook', [PagoController::class, 'webhook'])->name('pago.webhook');

Route::get('/logout-redirect', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout.redirect');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/finalizar-compra',            [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/finalizar-compra/confirmar', [CheckoutController::class, 'confirmar'])->name('checkout.confirmar');

    Route::put('/usuario/actualizar', [UsuarioController::class, 'actualizar'])->name('usuario.actualizar');

    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/',          [PerfilController::class, 'index'])->name('index');
        Route::get('/editar',    [PerfilController::class, 'edit'])->name('edit');
        Route::put('/update',    [PerfilController::class, 'update'])->name('update');
        Route::put('/email',     [PerfilController::class, 'updateEmail'])->name('update-email');
        Route::put('/password',  [PerfilController::class, 'updatePassword'])->name('update-password');
        Route::delete('/',       [PerfilController::class, 'destroy'])->name('destroy');

        Route::get('/pedidos/{id}/descargar', [PedidoUsuarioController::class, 'descargarArchivo'])
            ->name('pedidos.descargar');
    });

    Route::get('/pago/exito',     [PagoController::class, 'exito'])->name('pago.exito');
    Route::get('/pago/fallo',     [PagoController::class, 'fallo'])->name('pago.fallo');
    Route::get('/pago/pendiente', [PagoController::class, 'pendiente'])->name('pago.pendiente');
});

Route::middleware(['auth', 'verified', 'role:1'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('productos', ProductoController::class)->except(['show']);
        Route::resource('categorias', CategoriaController::class)->except(['show']);

        Route::get('pedidos',                  [PedidoController::class, 'index'])->name('pedidos.index');
        Route::get('pedidos/{id}',             [PedidoController::class, 'show'])->name('pedidos.show');
        Route::put('pedidos/{id}',             [PedidoController::class, 'update'])->name('pedidos.update');
        Route::get('pedidos/{id}/descargar',   [PedidoController::class, 'descargarArchivo'])->name('pedidos.descargar');

        Route::resource('banners', BannerController::class)->except(['show']);
        Route::resource('cupones', CuponController::class)->except(['show']);
        Route::patch('cupones/{cupon}/toggle', [CuponController::class, 'toggle'])->name('cupones.toggle');

        Route::get('ventas',  [VentaController::class, 'index'])->name('ventas.index');
        Route::post('ventas', [VentaController::class, 'store'])->name('ventas.store');
    });

require __DIR__ . '/auth.php';