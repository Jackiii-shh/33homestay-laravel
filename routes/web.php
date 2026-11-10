<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Owner;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Staff;
use Illuminate\Support\Facades\Route;

/*
| Publik: hanya informasi (tidak ada pemesanan mandiri). Pemesanan lewat WhatsApp Business Pengelola.
*/
Route::get('/', [PublicController::class, 'home'])->middleware('throttle:60,1')->name('home');
Route::post('/chatbot', ChatbotController::class)->middleware('throttle:20,1')->name('chatbot');
Route::get('/verifikasi-invoice/{number}/{token}', [PublicController::class, 'verifyInvoice'])->name('invoice.verify');
Route::get('/invoice/{token}/unduh', [PublicController::class, 'publicInvoicePdf'])->middleware('signed')->name('invoice.public.pdf');

// Autentikasi staf
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route(auth()->user()->isOwner() ? 'owner.dashboard' : 'staff.dashboard'))->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Dapat dibaca Owner (pemantau) & Pengelola
    Route::middleware('role:owner,pengelola')->group(function () {
        Route::get('/kalender', [Staff\CalendarController::class, 'index'])->name('calendar');
        Route::get('/reservasi', [Staff\ReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservasi/{reservation}', [Staff\ReservationController::class, 'show'])->whereNumber('reservation')->name('reservations.show');
        Route::get('/invoice/{invoice}/pdf', [Staff\InvoiceController::class, 'pdf'])->whereNumber('invoice')->name('invoices.pdf');
    });

    // Pengguna operasional: Pengelola (Admin Sosmed + Admin Lapangan)
    Route::middleware('role:pengelola')->group(function () {
        Route::get('/pengelola/dashboard', [Staff\DashboardController::class, 'index'])->name('staff.dashboard');
        Route::get('/pengelola/api/kamar-tersedia', [Staff\ApiController::class, 'availableRooms'])->name('staff.api.rooms');

        Route::get('/reservasi/buat', [Staff\ReservationController::class, 'create'])->name('reservations.create');
        Route::post('/reservasi', [Staff\ReservationController::class, 'store'])->name('reservations.store');
        Route::get('/reservasi/{reservation}/ubah', [Staff\ReservationController::class, 'edit'])->whereNumber('reservation')->name('reservations.edit');
        Route::put('/reservasi/{reservation}', [Staff\ReservationController::class, 'update'])->whereNumber('reservation')->name('reservations.update');

        Route::post('/reservasi/{reservation}/pembayaran', [Staff\PaymentController::class, 'store'])->whereNumber('reservation')->name('reservations.payments.store');
        Route::post('/reservasi/{reservation}/check-in', [Staff\ReservationController::class, 'checkIn'])->whereNumber('reservation')->name('reservations.check-in');
        Route::post('/reservasi/{reservation}/check-out', [Staff\ReservationController::class, 'checkOut'])->whereNumber('reservation')->name('reservations.check-out');
        Route::post('/reservasi/{reservation}/no-show', [Staff\ReservationController::class, 'noShow'])->whereNumber('reservation')->name('reservations.no-show');
        Route::post('/reservasi/{reservation}/batal', [Staff\ReservationController::class, 'cancel'])->whereNumber('reservation')->name('reservations.cancel');
        Route::post('/reservasi/{reservation}/ota-blokir', [Staff\ReservationController::class, 'otaBlock'])->whereNumber('reservation')->name('reservations.ota-block');
        Route::post('/reservasi/{reservation}/ota-buka', [Staff\ReservationController::class, 'otaUnblock'])->whereNumber('reservation')->name('reservations.ota-unblock');

        Route::get('/invoice/{invoice}/whatsapp', [Staff\InvoiceController::class, 'whatsapp'])->whereNumber('invoice')->name('invoices.whatsapp');

        Route::get('/kamar', [Staff\RoomController::class, 'index'])->name('rooms.index');
        Route::post('/kamar/{room}/status', [Staff\RoomController::class, 'updateStatus'])->whereNumber('room')->name('rooms.status');
    });

    // Owner: pemantau + data master
    Route::middleware('role:owner')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [Owner\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/pembayaran', [Owner\PaymentReportController::class, 'index'])->name('payments');
        Route::get('/audit', [Owner\AuditController::class, 'index'])->name('audit');

        Route::get('/tipe-kamar', [Owner\RoomTypeController::class, 'index'])->name('room-types.index');
        Route::get('/tipe-kamar/tambah', [Owner\RoomTypeController::class, 'create'])->name('room-types.create');
        Route::post('/tipe-kamar', [Owner\RoomTypeController::class, 'store'])->name('room-types.store');
        Route::get('/tipe-kamar/{roomType}/ubah', [Owner\RoomTypeController::class, 'edit'])->name('room-types.edit');
        Route::put('/tipe-kamar/{roomType}', [Owner\RoomTypeController::class, 'update'])->name('room-types.update');

        Route::get('/unit-kamar', [Owner\RoomController::class, 'index'])->name('rooms.index');
        Route::get('/unit-kamar/tambah', [Owner\RoomController::class, 'create'])->name('rooms.create');
        Route::post('/unit-kamar', [Owner\RoomController::class, 'store'])->name('rooms.store');
        Route::get('/unit-kamar/{room}/ubah', [Owner\RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/unit-kamar/{room}', [Owner\RoomController::class, 'update'])->name('rooms.update');

        Route::get('/pengguna', [Owner\UserController::class, 'index'])->name('users.index');
        Route::get('/pengguna/tambah', [Owner\UserController::class, 'create'])->name('users.create');
        Route::post('/pengguna', [Owner\UserController::class, 'store'])->name('users.store');
        Route::get('/pengguna/{user}/ubah', [Owner\UserController::class, 'edit'])->name('users.edit');
        Route::put('/pengguna/{user}', [Owner\UserController::class, 'update'])->name('users.update');
    });
});
