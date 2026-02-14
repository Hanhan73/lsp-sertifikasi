<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminVerificationController;
use App\Http\Controllers\Asesi\AsesiController;
use App\Http\Controllers\Tuk\TukController;
use App\Http\Controllers\Tuk\TukVerificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.update-info');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto'])->name('profile.upload-photo');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.delete-photo');

    // Route untuk melihat halaman notice (jika user login tapi belum verifikasi)
    Route::get('/email/verify', function () {
        $user = Auth::user();
        
        // ✅ Jika sudah verified, redirect ke dashboard
        if ($user->hasVerifiedEmail()) {
            if ($user->isAsesi()) {
                if ($user->isFirstLogin()) {
                    return redirect()->route('asesi.first-login');
                }
                return redirect()->route('asesi.dashboard')
                    ->with('info', 'Email Anda sudah terverifikasi.');
            }
            return redirect()->route('home');
        }
        
        return view('auth.verify-email');
    })->name('verification.notice');

    // Route untuk mengirim ulang email verifikasi
    Route::post('/email/verification-notification', function (Request $request) {
        // ✅ Cek apakah sudah verified
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('info', 'Email Anda sudah terverifikasi.');
        }
        
        $request->user()->sendEmailVerificationNotification();
        return back()->with('resent', true);
    })->middleware(['throttle:6,1'])->name('verification.send');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        Log::info('VERIFICATION ROUTE HIT!');
        Log::info('User ID from URL: ' . $request->route('id'));
        Log::info('Authenticated User: ' . ($request->user() ? $request->user()->email : 'No User'));

        // ✅ Validasi manual ID & hash (menggantikan authorize() yang error)
        if ((string) $request->user()->getKey() !== (string) $request->route('id')) {
            abort(403, 'Unauthorized verification attempt.');
        }

        if (sha1($request->user()->getEmailForVerification()) !== (string) $request->route('hash')) {
            abort(403, 'Invalid verification hash.');
        }

        // ✅ Fulfill verification
        if (!$request->user()->hasVerifiedEmail()) {
            $request->user()->markEmailAsVerified();
            event(new \Illuminate\Auth\Events\Verified($request->user()));
        }

        Log::info('Email verification fulfilled for user: ' . $request->user()->email);

        // ✅ Redirect ke dashboard sesuai role
        $user = $request->user();
        
        if ($user->isAsesi()) {
            if ($user->isFirstLogin()) {
                return redirect()->route('asesi.first-login')
                    ->with('success', 'Email berhasil diverifikasi! Silakan ganti password Anda.');
            }
            return redirect()->route('asesi.dashboard')
                ->with('verified', true);
        }

        return redirect()->route('home')
            ->with('success', 'Email berhasil diverifikasi!');
            
    })->middleware(['signed'])->name('verification.verify'); // ✅ Signed tetap ada
});

/*
|--------------------------------------------------------------------------
| Payment Routes (Midtrans)
|--------------------------------------------------------------------------
*/
// Public webhook for Midtrans notification (no auth needed)
Route::post('/payment/notification', [PaymentController::class, 'handleNotification'])
    ->name('payment.notification');

// Authenticated payment routes
Route::middleware('auth')->group(function () {
    Route::post('/payment/create-snap-token/{asesmen}', [PaymentController::class, 'createSnapToken'])
        ->name('payment.create-snap-token');
    
    Route::get('/payment/finish/{asesmen}', [PaymentController::class, 'finish'])
        ->name('payment.finish');
    
    Route::get('/payment/check-status/{asesmen}', [PaymentController::class, 'checkStatus'])
        ->name('payment.check-status');
    
    // TEST ONLY: Manual verify for testing (remove in production)
    Route::post('/payment/test-verify/{asesmen}', [PaymentController::class, 'testVerify'])
        ->name('payment.test-verify');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // TUK Management
    Route::get('/tuks', [AdminController::class, 'tuks'])->name('tuks');
    Route::get('/tuks/create', [AdminController::class, 'createTuk'])->name('tuks.create');
    Route::post('/tuks', [AdminController::class, 'storeTuk'])->name('tuks.store');
    Route::get('/tuks/{tuk}/edit', [AdminController::class, 'editTuk'])->name('tuks.edit');
    Route::put('/tuks/{tuk}', [AdminController::class, 'updateTuk'])->name('tuks.update');
    
    // Skema Management
    Route::get('/skemas', [AdminController::class, 'skemas'])->name('skemas');
    Route::get('/skemas/create', [AdminController::class, 'createSkema'])->name('skemas.create');
    Route::post('/skemas', [AdminController::class, 'storeSkema'])->name('skemas.store');
    Route::get('/skemas/{skema}/edit', [AdminController::class, 'editSkema'])->name('skemas.edit');
    Route::put('/skemas/{skema}', [AdminController::class, 'updateSkema'])->name('skemas.update');
    
    // Verification
    Route::get('/verifications', [AdminVerificationController::class, 'index'])->name('verifications');
    Route::get('/verifications/{asesmen}', [AdminVerificationController::class, 'show'])->name('verifications.show');
    Route::post('/verifications/{asesmen}', [AdminVerificationController::class, 'process'])->name('verifications.process');
    
    // NEW: Batch Verification for Collective
    Route::post('/verifications/batch', [AdminController::class, 'processBatchVerification'])->name('verifications.batch');
    
    // Payments (Monitoring & Manual Verification Backup)
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::post('/payments/{payment}/verify', [AdminController::class, 'verifyPayment'])->name('payments.verify');
    
    
    // Assessments
    Route::get('/assessments', [AdminController::class, 'assessments'])->name('assessments');
    Route::post('/assessments/{asesmen}', [AdminController::class, 'inputAssessment'])->name('assessments.input');
    
    // All Asesi
    Route::get('/asesi', [AdminController::class, 'allAsesi'])->name('asesi');
    
    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
});

/*
|--------------------------------------------------------------------------
| Asesi Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:asesi'])->prefix('asesi')->name('asesi.')->group(function () {

    Route::get('/first-login', [AsesiController::class, 'showFirstLogin'])
        ->name('first-login');

    Route::post('/first-login', [AsesiController::class, 'updateFirstPassword'])
        ->name('first-login.update');

    // ROUTE UNTUK MANDIRI (WAJIB VERIFIED)
    Route::middleware(['verified', 'check.first.login'])
        ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AsesiController::class, 'dashboard'])->name('dashboard');
        

        Route::get('/batch-info', [AsesiController::class, 'batchInfo'])->name('batch-info');

        // Complete Data
        Route::get('/complete-data', [AsesiController::class, 'completeData'])->name('complete-data');
        Route::post('/complete-data', [AsesiController::class, 'storeData'])->name('store-data');
        
        // Payment (will redirect if collective)
        Route::get('/payment', [AsesiController::class, 'payment'])->name('payment');
        Route::get('/payment/status', [AsesiController::class, 'paymentStatus'])->name('payment.status');
        
        // Invoice download
        Route::get('/payment/invoice', [AsesiController::class, 'downloadInvoice'])->name('payment.invoice');

        // Pre-Assessment
        Route::get('/pre-assessment', [AsesiController::class, 'preAssessment'])->name('pre-assessment');
        Route::post('/pre-assessment', [AsesiController::class, 'submitPreAssessment'])->name('pre-assessment.submit');
        
        // Certificate
        Route::get('/certificate', [AsesiController::class, 'certificate'])->name('certificate');
        Route::get('/certificate/download', [AsesiController::class, 'downloadCertificate'])->name('certificate.download');
        
        // Tracking
        Route::get('/tracking', [AsesiController::class, 'tracking'])->name('tracking');
    });
});




/*
|--------------------------------------------------------------------------
| TUK Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:tuk'])->prefix('tuk')->name('tuk.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [TukController::class, 'dashboard'])->name('dashboard');
    
    // Collective Registration
    Route::get('/collective-registration', [TukController::class, 'collectiveRegistration'])->name('collective');
    Route::post('/collective-registration', [TukController::class, 'storeCollectiveRegistration'])->name('collective.store');
    
    // NEW: Collective Payment Routes
    Route::get('/collective/payment/{batchId}', [TukController::class, 'collectivePayment'])->name('collective.payment');
    Route::post('/collective/payment/{batchId}/create-token', [TukController::class, 'createCollectiveSnapToken'])->name('collective.payment.create-token');
    Route::get('/collective/payment/{batchId}/finish', [TukController::class, 'collectivePaymentFinish'])->name('collective.payment.finish');
    Route::post('collective/payment/{batchId}/check-status', [TukController::class, 'checkCollectivePaymentStatus'])
        ->name('collective.payment.check');
    Route::get('/collective/payment/{batchId}/invoice', [TukController::class, 'downloadCollectiveInvoice'])->name('collective.payment.invoice');

        // Collective registration template & import
    Route::get('/collective/download-template/{type}', [TukController::class, 'downloadTemplate'])
        ->name('collective.download-template');
    Route::post('/collective/parse-file', [TukController::class, 'parseParticipantsFile'])
        ->name('collective.parse-file');
        
    // Manage Asesi
    Route::get('/asesi', [TukController::class, 'asesi'])->name('asesi');
    Route::get('/asesi/{asesmen}', [TukController::class, 'asesiDetail'])->name('asesi.show');

    Route::get('/batch/{batchId}', [TukController::class, 'batchDetail'])->name('batch.detail');

    // Schedule
    Route::get('/schedules', [TukController::class, 'schedules'])->name('schedules');
    Route::post('/schedules/batch-create', [TukController::class, 'batchCreateSchedule'])->name('schedules.batch-create');
    Route::get('/schedules/{schedule}/view', [TukController::class, 'viewSchedule'])->name('schedules.view');
    Route::get('/schedules/{schedule}/edit', [TukController::class, 'editSchedule'])->name('schedules.edit');
    Route::put('/schedules/{schedule}/update-ajax', [TukController::class, 'updateScheduleAjax'])->name('schedules.update-ajax');
    Route::delete('/schedules/{schedule}/delete-ajax', [TukController::class, 'deleteScheduleAjax'])->name('schedules.delete-ajax');

    Route::put('/schedules/{schedule}', [TukController::class, 'updateScheduleSubmit'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [TukController::class, 'deleteSchedule'])->name('schedules.delete');
    
    // Export schedule batch
    Route::post('/schedules/export/{groupKey}', [TukController::class, 'exportScheduleBatch'])->name('schedules.export-batch');

    // Verifikasi data asesi oleh TUK
    Route::get('/verifications', [TukVerificationController::class, 'index'])->name('verifications');
    Route::get('/verifications/{asesmen}', [TukVerificationController::class, 'show'])->name('verifications.show');
    Route::post('/verifications/{asesmen}', [TukVerificationController::class, 'process'])->name('verifications.process');
    Route::post('/verifications/batch/process', [TukVerificationController::class, 'processBatch'])->name('verifications.batch');

});

Route::get('/debug-paths', function() {
    return [
        'base_path' => base_path(),
        'public_path' => public_path(),
        'storage_path' => storage_path(),
        'public_exists' => file_exists(public_path()),
        'storage_exists' => file_exists(storage_path()),
        'index_php_exists' => file_exists(public_path('index.php')),
    ];
})->middleware('auth');