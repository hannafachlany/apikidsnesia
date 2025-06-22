<?php

use Illuminate\Support\Facades\Route;
 use Illuminate\Support\Facades\Mail;
use App\Mail\BuktiTransferMembershipTerkirim;
use App\Models\Pelanggan;
use App\Http\Controllers\{
    AuthController,
    ProfilController,
    EventController,
    MerchandiseController,
    AdminDashboardController,
    EventCartController,
    PembayaranEventController,
    AdminPembelianEventController,
    AdminPembayaranController,
    MerchCartController,
    PembayaranMerchController,
    AdminPembelianMerchController,
    AdminPembayaranMerchController,
    MembershipController,
    AdminMembershipController,
    DetailEventController,
    AdminVideoController,
    VideoController
};

// ==================== AUTH ====================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/send-reset-email', [AuthController::class, 'sendResetEmail']);
Route::post('/verify-reset-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('reset.password.token');


// ==================== EVENT (Pelanggan) ====================
Route::prefix('event')->group(function () {
    //detail event
    Route::get('/detail-event', [DetailEventController::class, 'listAll']);
    Route::get('/detail-event/{idEvent}', [DetailEventController::class, 'index']);

    //list event
    Route::get('/', [EventController::class, 'showAll']);
    Route::get('/{idEvent}', [EventController::class, 'show']);
    
});

// ==================== MERCHANDISE (Pelanggan) ====================
Route::prefix('merch')->group(function () {
    //list merch
    Route::get('/', [MerchandiseController::class, 'index']);
    Route::get('/{idMerchandise}', [MerchandiseController::class, 'show']);
});

// ==================== AUTH TOKEN (PROFIL Pelanggan) ====================
Route::middleware('auth.token')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('profil')->group(function () {
        Route::get('/', [ProfilController::class, 'profil']);
        Route::post('upload-foto', [ProfilController::class, 'uploadFoto']);
        Route::delete('hapus-foto', [ProfilController::class, 'hapusFoto']);
        
    });
});

// ==================== MEMBERSHIP (Pelanggan) ====================
Route::middleware('auth.token')->group(function () {
    Route::post('/membership', [MembershipController::class, 'store']);
    Route::get('/membership/history', [MembershipController::class, 'history']);
    Route::get('/membership/payment/{idPembayaranMembership}', [MembershipController::class, 'showPayment']);
    Route::post('/membership/upload-bukti/{idPembayaranMembership}', [MembershipController::class, 'uploadBuktiTransfer']);
    Route::get('/membership/current', [MembershipController::class, 'current']);
});

// ==================== EVENT TRANSACTION (Pelanggan)====================
Route::prefix('event')->middleware('auth.token')->group(function () {
    Route::post('cart', [EventCartController::class, 'store']);
    Route::get('cart/listcart', [EventCartController::class, 'listCart']); // tidak bentrok dengan {id}
    Route::get('cart/{idPembelianEvent}', [EventCartController::class, 'detailCart']);
    Route::patch('cart/{idPembelianEvent}/update-jumlah', [EventCartController::class, 'updateJumlahTiket']);
    Route::delete('cart/{idPembelianEvent}', [EventCartController::class, 'deleteCart']);
    Route::post('checkout/{idPembelianEvent}', [EventCartController::class, 'checkout']);

    // Pembayaran
    Route::get('pembayaran/{idPembelianEvent}', [PembayaranEventController::class, 'getDetailBayar']);
    Route::post('pembayaran/pilih-bank', [PembayaranEventController::class, 'pilihBank']);
    Route::post('pembayaran/{idPembayaranEvent}/upload-bukti', [PembayaranEventController::class, 'uploadBukti']);
    Route::get('nota/{idPembelianEvent}', [PembayaranEventController::class, 'notaPembelian']);
});

// ==================== MERCH TRANSACTION (Pelanggan) ====================
Route::prefix('merch')->middleware('auth.token')->group(function () {
    // CART
    Route::post('cart', [MerchCartController::class, 'store']);
    Route::get('cart/listcart', [MerchCartController::class, 'listCart']);
    Route::get('cart/{idPembelianMerch}', [MerchCartController::class, 'detailCart']);
    Route::delete('cart/{idPembelianMerch}', [MerchCartController::class, 'deleteCart']);
    Route::post('checkout/{idPembelianMerch}', [MerchCartController::class, 'checkout']);

    // PEMBAYARAN
    Route::prefix('pembayaran')->group(function () {
        Route::get('/{idPembelianMerch}', [PembayaranMerchController::class, 'getDetailBayar']);
        Route::post('/pilih-bank', [PembayaranMerchController::class, 'pilihBank']);
        Route::post('/{idPembayaranMerch}/upload-bukti', [PembayaranMerchController::class, 'uploadBukti']);
    });

    // NOTA
    Route::get('nota/{idPembelianMerch}', [PembayaranMerchController::class, 'notaPembelian']);
});

// ==================== VIDEO (PELANGGAN) ====================
Route::prefix('videos')->middleware('auth.token')->group(function () {
    Route::get('/', [VideoController::class, 'index']); // bisa diakses siapa saja yang login
    Route::get('/{id_video}', [VideoController::class, 'show']);
});

// ==================== ADMIN ====================
Route::prefix('admin')->group(function () {

    //dashboard
    Route::get('dashboard/stats', [AdminDashboardController::class, 'getDashboardStats']);
    
    Route::get('dashboard/stats-by-date', [AdminDashboardController::class, 'getFilteredStats']);
    //akses event
    Route::get('event', [EventController::class, 'showAll']);
    Route::post('event', [EventController::class, 'store']);
    Route::put('event/{idEvent}', [EventController::class, 'update']);
    Route::delete('event/{idEvent}', [EventController::class, 'destroy']);
    Route::get('event/{idEvent}', [EventController::class, 'show']);

    //akses merch
    Route::get('/merch', [MerchandiseController::class, 'index']);
    Route::get('merch/{idMerchandise}', [MerchandiseController::class, 'show']);
    Route::post('merch/', [MerchandiseController::class, 'store']);
    Route::put('merch/{idMerchandise}', [MerchandiseController::class, 'update']);
    Route::delete('merch/{idMerchandise}', [MerchandiseController::class, 'destroy']);

    //akses pembelian event
    Route::get('pembelian-event', [AdminPembelianEventController::class, 'index']);
    Route::get('pembelian-event/{id}', [AdminPembelianEventController::class, 'show']);
    Route::get('pembayaran-event/{id}', [AdminPembayaranController::class, 'show']);
    Route::patch('pembayaran-event/{idPembayaran}/status', [AdminPembayaranController::class, 'updateStatusBayar']);

    //akses pembelian merch
    Route::get('pembelian-merch', [AdminPembelianMerchController::class, 'index']);
    Route::get('pembelian-merch/{id}', [AdminPembelianMerchController::class, 'show']);
    Route::get('pembayaran-merch/{id}', [AdminPembayaranMerchController::class, 'show']);
    Route::patch('pembayaran-merch/{id}/status', [AdminPembayaranMerchController::class, 'updateStatus']);
    
    //pelanggan & membership
    Route::get('/pelanggan', [AdminMembershipController::class, 'index']);
    Route::get('/pembayaran-membership/{id}', [AdminMembershipController::class, 'showPembayaranByMembership']);
    Route::put('/pembayaran-membership/{id}', [AdminMembershipController::class, 'updateStatus']);

    //detail event
    Route::get('/detail-event', [DetailEventController::class, 'listAll']);
    Route::get('/detail-event/{id_event}', [DetailEventController::class, 'index']);
    Route::post('/detail-event', [DetailEventController::class, 'store']);
    Route::patch('/detail-event/{id_detail_event}', [DetailEventController::class, 'update']);
    Route::delete('/detail-event/{id_detail_event}', [DetailEventController::class, 'destroy']);

    //video
    Route::get('/videos', [AdminVideoController::class, 'index']);
    Route::post('/videos', [AdminVideoController::class, 'store']);
    Route::get('/videos/{id_video}', [AdminVideoController::class, 'show']);
    Route::patch('/videos/{id_video}', [AdminVideoController::class, 'update']);
    Route::delete('/videos/{id_video}', [AdminVideoController::class, 'destroy']);

});





