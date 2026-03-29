<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Auth & shared
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SignatureController;

// Admin
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\TukController as AdminTukController;
use App\Http\Controllers\Admin\SkemaController;
use App\Http\Controllers\Admin\AplController;
use App\Http\Controllers\Admin\AsesmenController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\AsesorAssignmentController;
use App\Http\Controllers\Admin\AdminAsesorController;
use App\Http\Controllers\Admin\AdminPraAsesmenController;
use App\Http\Controllers\Admin\AdminMandiriVerificationController;
use App\Http\Controllers\Admin\FrAk01Controller as FrAk01AdminController;
use App\Http\Controllers\Admin\FrAk04Controller as FrAk04AdminController;
use App\Http\Controllers\Admin\AdminScheduleController;

// Asesi
use App\Http\Controllers\Asesi\AsesiController;
use App\Http\Controllers\Asesi\FrAk01Controller as FrAk01AsesiController;
use App\Http\Controllers\Asesi\FrAk04Controller as FrAk04AsesiController;

// TUK
use App\Http\Controllers\Tuk\TukController;
use App\Http\Controllers\Tuk\TukVerificationController;

// Asesor
use App\Http\Controllers\Asesor\AsesorController;
use App\Http\Controllers\Asesor\FrAk01Controller;
use App\Http\Controllers\Asesor\FrAk04Controller as FrAk04AsesorController; 

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('welcome'))->name('home');




/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])   ->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/forgot-password',        [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password',       [AuthController::class, 'sendResetLink'])     ->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])
    ->name('password.reset')
    ->where('token', '.*');
Route::post('/reset-password',        [AuthController::class, 'resetPassword'])     ->name('password.update');

/*
|--------------------------------------------------------------------------
| Profile & Email Verification (semua role)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile',         [ProfileController::class, 'show'])        ->name('profile.show');
    Route::put('/profile/info',    [ProfileController::class, 'updateInfo'])  ->name('profile.update-info');
    Route::put('/profile/password',[ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::post('/profile/photo',  [ProfileController::class, 'uploadPhoto']) ->name('profile.upload-photo');
    Route::delete('/profile/photo',[ProfileController::class, 'deletePhoto']) ->name('profile.delete-photo');

    // Email verification notice
    Route::get('/email/verify', function () {
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return $user->isAsesi()
                ? ($user->isFirstLogin()
                    ? redirect()->route('asesi.first-login')
                    : redirect()->route('asesi.dashboard')->with('info', 'Email sudah terverifikasi.'))
                : redirect()->route('home');
        }

        return view('auth.verify-email');
    })->name('verification.notice');

    // Resend verification email
    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('info', 'Email sudah terverifikasi.');
        }

        $request->user()->sendEmailVerificationNotification();
        return back()->with('resent', true);
    })->middleware('throttle:6,1')->name('verification.send');

    // Verify email link
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        // Validasi manual — menggantikan authorize() yang bermasalah
        if ((string) $request->user()->getKey() !== (string) $request->route('id')) {
            abort(403, 'Unauthorized verification attempt.');
        }

        if (sha1($request->user()->getEmailForVerification()) !== (string) $request->route('hash')) {
            abort(403, 'Invalid verification hash.');
        }

        if (!$request->user()->hasVerifiedEmail()) {
            $request->user()->markEmailAsVerified();
            event(new \Illuminate\Auth\Events\Verified($request->user()));
        }

        Log::info('[EMAIL-VERIFY] Verified: ' . $request->user()->email);

        $user = $request->user();

        if ($user->isAsesi()) {
            return $user->isFirstLogin()
                ? redirect()->route('asesi.first-login')->with('success', 'Email terverifikasi! Silakan ganti password.')
                : redirect()->route('asesi.dashboard')->with('verified', true);
        }

        return redirect()->route('home')->with('success', 'Email berhasil diverifikasi!');

    })->middleware('signed')->name('verification.verify');

    Route::post('/user/signature', [SignatureController::class, 'store'])
    ->name('user.signature.store');

    Route::delete('/user/signature', [SignatureController::class, 'destroy'])
        ->name('user.signature.destroy');
});

/*
|--------------------------------------------------------------------------
| Payment — Midtrans webhook (public) + authenticated actions
|--------------------------------------------------------------------------
*/

Route::post('/payment/notification', [PaymentController::class, 'handleNotification'])
    ->name('payment.notification');

Route::middleware('auth')->group(function () {
    Route::post('/payment/create-snap-token/{asesmen}', [PaymentController::class, 'createSnapToken'])->name('payment.create-snap-token');
    Route::get('/payment/finish/{asesmen}',             [PaymentController::class, 'finish'])          ->name('payment.finish');
    Route::get('/payment/check-status/{asesmen}',       [PaymentController::class, 'checkStatus'])     ->name('payment.check-status');

    // TEST ONLY — hapus di production
    Route::post('/payment/test-verify/{asesmen}', [PaymentController::class, 'testVerify'])->name('payment.test-verify');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // ── Dashboard ──────────────────────────────────────────────────────────
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // ── Laporan ────────────────────────────────────────────────────────────
    Route::get('/reports',         [AdminController::class, 'reports'])      ->name('reports');
    Route::post('/reports/export', [AdminController::class, 'exportReport']) ->name('reports.export');

    // ── TUK ────────────────────────────────────────────────────────────────
    Route::prefix('tuks')->name('tuks.')->group(function () {
        Route::get('/',             [AdminTukController::class, 'index'])  ->name('index');
        Route::get('/create',       [AdminTukController::class, 'create']) ->name('create');
        Route::post('/',            [AdminTukController::class, 'store'])  ->name('store');
        Route::get('/{tuk}/detail', [AdminTukController::class, 'show'])   ->name('detail');  // AJAX modal
        Route::get('/{tuk}/edit',   [AdminTukController::class, 'edit'])   ->name('edit');
        Route::put('/{tuk}',        [AdminTukController::class, 'update']) ->name('update');
        Route::delete('/{tuk}',     [AdminTukController::class, 'destroy'])->name('destroy');
    });
    Route::get('/tuks', [AdminTukController::class, 'index'])->name('tuks'); // alias lama

    // ── Skema ──────────────────────────────────────────────────────────────
    Route::prefix('skemas')->name('skemas.')->group(function () {
        Route::get('/',                    [SkemaController::class, 'index'])    ->name('index');
        Route::get('/create',              [SkemaController::class, 'create'])   ->name('create');
        Route::post('/',                   [SkemaController::class, 'store'])    ->name('store');
        Route::get('/{skema}',             [SkemaController::class, 'show'])     ->name('show');
        Route::get('/{skema}/edit',        [SkemaController::class, 'edit'])     ->name('edit');
        Route::put('/{skema}',             [SkemaController::class, 'update'])   ->name('update');
        Route::delete('/{skema}',          [SkemaController::class, 'destroy'])  ->name('destroy');
        Route::post('/{skema}/import-muk', [SkemaController::class, 'importMuk'])->name('import-muk');
    });
    Route::get('/skemas', [SkemaController::class, 'index'])->name('skemas'); // alias lama

    // Skema sub-resources — AJAX
    Route::post('/skemas/{skema}/units',   [SkemaController::class, 'storeUnit'])    ->name('skemas.units.store');
    Route::put('/units/{unit}',            [SkemaController::class, 'updateUnit'])   ->name('skemas.units.update');
    Route::delete('/units/{unit}',         [SkemaController::class, 'destroyUnit'])  ->name('skemas.units.destroy');
    Route::post('/units/{unit}/elemens',   [SkemaController::class, 'storeElemen'])  ->name('skemas.elemens.store');
    Route::put('/elemens/{elemen}',        [SkemaController::class, 'updateElemen']) ->name('skemas.elemens.update');
    Route::delete('/elemens/{elemen}',     [SkemaController::class, 'destroyElemen'])->name('skemas.elemens.destroy');
    Route::post('/elemens/{elemen}/kuks',  [SkemaController::class, 'storeKuk'])     ->name('skemas.kuks.store');
    Route::put('/kuks/{kuk}',             [SkemaController::class, 'updateKuk'])    ->name('skemas.kuks.update');
    Route::delete('/kuks/{kuk}',          [SkemaController::class, 'destroyKuk'])   ->name('skemas.kuks.destroy');

    // ── Asesor (master data) ───────────────────────────────────────────────
    Route::prefix('asesors')->name('asesors.')->group(function () {
        Route::get('/',              [AdminAsesorController::class, 'index'])  ->name('index');
        Route::get('/create',        [AdminAsesorController::class, 'create']) ->name('create');
        Route::post('/',             [AdminAsesorController::class, 'store'])  ->name('store');
        Route::post('/import',       [AdminAsesorController::class, 'import']) ->name('import');
        Route::get('/{asesor}',      [AdminAsesorController::class, 'show'])   ->name('show');
        Route::get('/{asesor}/edit', [AdminAsesorController::class, 'edit'])   ->name('edit');
        Route::put('/{asesor}',      [AdminAsesorController::class, 'update']) ->name('update');
        Route::delete('/{asesor}',   [AdminAsesorController::class, 'destroy'])->name('destroy');
        Route::post('/{asesor}/buat-akun', [AdminAsesorController::class, 'buatAkun'])->name('buat-akun');

    });

    // ── Verifikasi kolektif & mandiri ──────────────────────────────────────
    Route::prefix('praasesmen')->name('praasesmen.')->group(function () {
        Route::get('/',                   [AdminPraAsesmenController::class, 'index'])         ->name('index');
        Route::get('/{asesmen}',          [AdminPraAsesmenController::class, 'show'])          ->name('show');
        Route::post('/{asesmen}',         [AdminPraAsesmenController::class, 'process'])       ->name('process');
        Route::get('/batch/{batchId}',    [AdminPraAsesmenController::class, 'showBatch'])     ->name('batch.show');
        Route::post('/batch/process',     [AdminPraAsesmenController::class, 'processBatch'])->name('batch.process');
        Route::post('/batch',             [AdminPraAsesmenController::class, 'processBatchFee'])  ->name('batch');
    });
    Route::get('/praasesmen', [AdminPraAsesmenController::class, 'index'])->name('praasesmen.index'); // alias lama

    Route::prefix('mandiri')->name('mandiri.')->group(function () {
        Route::get('/verifications',       [AdminMandiriVerificationController::class, 'index'])         ->name('verifications');
        Route::get('/verify/{asesmen}',    [AdminMandiriVerificationController::class, 'show'])          ->name('verify');
        Route::post('/verify/{asesmen}',   [AdminMandiriVerificationController::class, 'process'])       ->name('verify.process');
        Route::get('/assignment',          [AdminMandiriVerificationController::class, 'assignmentIndex'])->name('assignment');
        Route::post('/assign/{asesmen}',   [AdminMandiriVerificationController::class, 'assignToTuk'])   ->name('assign');
        Route::get('/tuk/{tuk}/schedules/{skemaId}', [AdminMandiriVerificationController::class, 'getTukSchedules'])->name('tuk.schedules');
    });

    // ── Penugasan Asesor ───────────────────────────────────────────────────
    Route::get('/asesor-assignments', [AsesorAssignmentController::class, 'index'])->name('asesor-assignments.index');

    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/',                               [AdminScheduleController::class, 'index'])           ->name('index');
        Route::get('/create',                         [AdminScheduleController::class, 'create'])          ->name('create');
        Route::post('/',                              [AdminScheduleController::class, 'store'])           ->name('store');
        Route::get('/{schedule}',                     [AdminScheduleController::class, 'show'])            ->name('show');
        Route::get('/{schedule}/edit',                [AdminScheduleController::class, 'edit'])            ->name('edit');
        Route::put('/{schedule}',                     [AdminScheduleController::class, 'update'])          ->name('update');
        Route::delete('/{schedule}',                  [AdminScheduleController::class, 'destroy'])         ->name('destroy');
        Route::get('/{schedule}/available-asesors',   [AdminScheduleController::class, 'availableAsesors'])->name('available-asesors');
        // Assign asesor tetap pakai AsesorAssignmentController
        Route::post('/{schedule}/assign-asesor',      [AsesorAssignmentController::class, 'assign'])       ->name('assign-asesor');
        Route::post('/{schedule}/unassign-asesor',    [AsesorAssignmentController::class, 'unassign'])     ->name('unassign-asesor');
        Route::get('/{schedule}/assignment-history',  [AsesorAssignmentController::class, 'history'])      ->name('assignment-history');
    });

    // ── Proses Asesmen — Dokumen APL ──────────────────────────────────────
    Route::get('/apl', [AplController::class, 'index'])->name('apl.index');

    // APL-01
    Route::prefix('apl01')->name('apl01.')->group(function () {
        Route::get('/{aplsatu}',        [AplController::class, 'showApl01'])        ->name('show');
        Route::post('/{aplsatu}/verify',[AplController::class, 'verifyApl01'])      ->name('verify');
        Route::post('/{aplsatu}/return',[AplController::class, 'returnApl01'])      ->name('return');
        Route::get('/{aplsatu}/pdf',    [AplController::class, 'pdfApl01'])         ->name('pdf');
    });
    Route::get('/apl', [AplController::class, 'index'])->name('apl01.index'); // alias lama

    // APL-01 bukti
    Route::prefix('apl01-bukti')->name('apl01.bukti.')->group(function () {
        Route::post('/{bukti}/status', [AplController::class, 'updateBuktiStatus'])->name('status');
        Route::post('/{bukti}/upload', [AplController::class, 'uploadBukti'])      ->name('upload');
    });

    // APL-02
    Route::prefix('apl02')->name('apl02.')->group(function () {
        Route::get('/{apldua}/pdf', [AplController::class, 'pdfApl02'])->name('pdf');
    });

    // FR.AK.01
    Route::prefix('frak01')->name('frak01.')->group(function () {
        Route::get('/{frak01}/pdf', [FrAk01AdminController::class, 'adminPdf'])->name('pdf');
    });

    // FR.AK.04
    Route::prefix('frak04')->name('frak04.')->group(function () {
        Route::get('/{frak04}/pdf', [FrAk04AdminController::class, 'adminPdf'])->name('pdf');
    });

    // ── Semua Asesi ────────────────────────────────────────────────────────
    Route::prefix('asesi')->name('asesi.')->group(function () {
        Route::get('/',                            [AsesmenController::class, 'index'])              ->name('index');
        Route::get('/batch/{batchId}',             [AsesmenController::class, 'batchShow'])          ->name('batch.show');
        Route::get('/batch/{batchId}/export',      [AsesmenController::class, 'exportBatchBiodata']) ->name('batch.export');
        Route::patch('/batch/{batchId}/rename',    [AsesmenController::class, 'renameBatch'])        ->name('batch.rename');
        Route::get('/export', [AsesmenController::class, 'exportAllBiodata'])->name('export');
        Route::get('/{asesmen}',                   [AsesmenController::class, 'show'])               ->name('show');
        Route::get('/{asesmen}/detail',            [AsesmenController::class, 'detail'])             ->name('detail');

    });
    Route::get('/asesi', [AsesmenController::class, 'index'])->name('asesi'); // alias lama
    
    // Alias lama — AJAX detail dari dashboard
    Route::get('/asesmens/{asesmen}/detail', [AsesmenController::class, 'detail'])->name('asesmens.detail');
    Route::get('/admin/asesi/{asesmen}', [AsesmenController::class, 'show'])
        ->name('admin.asesi.show');
    // ── Input Hasil Asesmen ────────────────────────────────────────────────
    Route::get('/assessments',            [AsesmenController::class, 'assessments'])->name('assessments');
    Route::post('/assessments/{asesmen}', [AsesmenController::class, 'inputHasil']) ->name('assessments.input');

    // ── Pembayaran ─────────────────────────────────────────────────────────
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/',                  [AdminPaymentController::class, 'index']) ->name('index');
        Route::get('/{payment}/detail',  [AdminPaymentController::class, 'detail'])->name('detail');
        Route::post('/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('verify');
    });
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments'); // alias lama



});

/*
|--------------------------------------------------------------------------
| Asesi
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:asesi'])->prefix('asesi')->name('asesi.')->group(function () {

    Route::get('/first-login',  [AsesiController::class, 'showFirstLogin'])     ->name('first-login');
    Route::post('/first-login', [AsesiController::class, 'updateFirstPassword'])->name('first-login.update');

    Route::middleware(['verified', 'check.first.login'])->group(function () {

        Route::get('/dashboard',  [AsesiController::class, 'dashboard']) ->name('dashboard');
        Route::get('/batch-info', [AsesiController::class, 'batchInfo']) ->name('batch-info');
        Route::get('/tracking',   [AsesiController::class, 'tracking'])  ->name('tracking');
        Route::get('/schedule',   [AsesiController::class, 'schedule'])  ->name('schedule');

        // Complete data
        Route::get('/complete-data',  [AsesiController::class, 'completeData'])->name('complete-data');
        Route::post('/complete-data', [AsesiController::class, 'storeData'])   ->name('store-data');

        // Payment
        Route::get('/payment',         [AsesiController::class, 'payment'])       ->name('payment');
        Route::get('/payment/status',  [AsesiController::class, 'paymentStatus']) ->name('payment.status');
        Route::get('/payment/invoice', [AsesiController::class, 'downloadInvoice'])->name('payment.invoice');

        // Pre-assessment
        Route::get('/pre-assessment',  [AsesiController::class, 'preAssessment'])      ->name('pre-assessment');
        Route::post('/pre-assessment', [AsesiController::class, 'submitPreAssessment'])->name('pre-assessment.submit');

        // Certificate
        Route::get('/certificate',          [AsesiController::class, 'certificate'])        ->name('certificate');
        Route::get('/certificate/download', [AsesiController::class, 'downloadCertificate'])->name('certificate.download');

        // APL-01
        Route::get('/apl01',          [AsesiController::class, 'aplsatuForm'])   ->name('apl01');
        Route::post('/apl01/update',  [AsesiController::class, 'aplsatuUpdate']) ->name('apl01.update');
        Route::post('/apl01/submit',  [AsesiController::class, 'aplsatuSubmit']) ->name('apl01.submit');
        Route::get('/apl01/pdf',      [AsesiController::class, 'aplsatuPdf'])    ->name('apl01.pdf');
        Route::post('/apl01/bukti/save', [AsesiController::class, 'aplsatuBuktiSave'])->name('apl01.bukti.save');

        // APL-02
        Route::get('/apldua',         [AsesiController::class, 'apldua'])       ->name('apldua');
        Route::post('/apldua/save',   [AsesiController::class, 'apldua_save'])  ->name('apldua.save');
        Route::post('/apldua/submit', [AsesiController::class, 'apldua_submit'])->name('apldua.submit');
        Route::get('/apldua/pdf',     [AsesiController::class, 'aplduaPdf'])    ->name('apldua.pdf');

        // FR.AK.01
        Route::get('/frak01',       [FrAk01AsesiController::class, 'showAsesi']) ->name('frak01');
        Route::post('/frak01/sign', [FrAk01AsesiController::class, 'signAsesi']) ->name('frak01.sign');
        Route::get('/frak01/pdf',   [FrAk01AsesiController::class, 'asesiPdf'])  ->name('frak01.pdf');
        Route::post('/frak01/bukti/save', [FrAk01AsesiController::class, 'saveBukti'])->name('frak01.bukti.save');

        // FR.AK.04 — Banding Asesmen (opsional)
        Route::get('/frak04',        [FrAk04AsesiController::class, 'showAsesi']) ->name('frak04');
        Route::post('/frak04/submit',[FrAk04AsesiController::class, 'submitAsesi'])->name('frak04.submit');
        Route::get('/frak04/pdf',    [FrAk04AsesiController::class, 'asesiPdf'])  ->name('frak04.pdf');

        Route::get('/documents', [AsesiController::class, 'documents'])->name('documents');
    });
});

/*
|--------------------------------------------------------------------------
| TUK
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:tuk'])->prefix('tuk')->name('tuk.')->group(function () {

    Route::get('/dashboard', [TukController::class, 'dashboard'])->name('dashboard');

    // Collective registration
    Route::get('/collective-registration',  [TukController::class, 'collectiveRegistration'])     ->name('collective');
    Route::post('/collective-registration', [TukController::class, 'storeCollectiveRegistration'])->name('collective.store');
    Route::get('/collective/payments',      [TukController::class, 'collectivePayments'])          ->name('collective.payments');

    Route::prefix('collective/payment/{batchId}')->name('collective.payment.')->group(function () {
        Route::get('/',              [TukController::class, 'collectivePayment'])           ->name('index');
        Route::post('/create-token', [TukController::class, 'createCollectiveSnapToken'])  ->name('create-token');
        Route::get('/finish',        [TukController::class, 'collectivePaymentFinish'])    ->name('finish');
        Route::post('/check-status', [TukController::class, 'checkCollectivePaymentStatus'])->name('check');
        Route::get('/invoice',       [TukController::class, 'downloadCollectiveInvoice']) ->name('invoice');
    });

    Route::get('/collective/download-template/{type}', [TukController::class, 'downloadTemplate'])      ->name('collective.download-template');
    Route::post('/collective/parse-file',              [TukController::class, 'parseParticipantsFile']) ->name('collective.parse-file');

    // Asesi
    Route::get('/asesi',           [TukController::class, 'asesi'])      ->name('asesi');
    Route::get('/asesi/{asesmen}', [TukController::class, 'asesiDetail'])->name('asesi.show');
    Route::get('/batch/{batchId}', [TukController::class, 'batchDetail'])->name('batch.detail');

    // Jadwal
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/',                         [TukController::class, 'schedules'])          ->name('index');
        Route::post('/batch-create',            [TukController::class, 'batchCreateSchedule'])->name('batch-create');
        Route::get('/{schedule}/view',          [TukController::class, 'viewSchedule'])       ->name('view');
        Route::get('/{schedule}/edit',          [TukController::class, 'editSchedule'])       ->name('edit');
        Route::put('/{schedule}/update-ajax',   [TukController::class, 'updateScheduleAjax'])->name('update-ajax');
        Route::delete('/{schedule}/delete-ajax',[TukController::class, 'deleteScheduleAjax'])->name('delete-ajax');
        Route::put('/{schedule}',               [TukController::class, 'updateScheduleSubmit'])->name('update');
        Route::delete('/{schedule}',            [TukController::class, 'deleteSchedule'])     ->name('delete');
        Route::post('/export/{groupKey}',       [TukController::class, 'exportScheduleBatch'])->name('export-batch');
    });

    // Verifikasi
    Route::prefix('verifications')->name('verifications.')->group(function () {
        Route::get('/',              [TukVerificationController::class, 'index'])        ->name('index');
        Route::get('/{asesmen}',     [TukController::class, 'showVerification'])         ->name('show');
        Route::post('/{asesmen}',    [TukVerificationController::class, 'process'])      ->name('process');
        Route::post('/batch/process',[TukVerificationController::class, 'processBatch'])->name('batch');
    });
});

/*
|--------------------------------------------------------------------------
| Asesor
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:asesor'])->prefix('asesor')->name('asesor.')->group(function () {

    Route::get('/dashboard', [AsesorController::class, 'dashboard'])->name('dashboard');

    // Jadwal
    Route::get('/schedule',          [AsesorController::class, 'schedule'])      ->name('schedule');
    Route::get('/schedule/{schedule}',[AsesorController::class, 'scheduleDetail'])->name('schedule.detail');

    // Asesi di dalam jadwal
    Route::prefix('schedule/{schedule}/asesi/{asesmen}')->name('asesi.')->group(function () {
        Route::get('/',               [AsesorController::class, 'asesiDetail']) ->name('detail');
        Route::post('/apl02/verify',  [AsesorController::class, 'verifyApl02'])->name('apl02.verify');  // note: name jadi asesor.asesi.apl02.verify
        Route::get('/apl01/preview',  [AsesorController::class, 'previewApl01'])->name('apl01.preview');
        Route::get('/apl02/preview',  [AsesorController::class, 'previewApl02'])->name('apl02.preview');
    });

    // FR.AK.01 untuk asesor — hanya bisa akses asesmen yang dijadwalkan ke dia
    Route::prefix('schedule/{schedule}/asesi/{asesmen}/frak01')->name('frak01.')->group(function () {
        Route::get('/',      [FrAk01Controller::class, 'show'])       ->name('show');
        // DIHAPUS: Route::post('/bukti', ...) — asesor tidak isi checklist lagi di sini
        Route::post('/sign', [FrAk01Controller::class, 'signAsesor']) ->name('sign');
        Route::get('/pdf',   [FrAk01Controller::class, 'previewPdf']) ->name('pdf');
    });

    // Perhatian: route lama pakai name asesor.apl02.verify — tambahkan alias agar tidak break
    Route::post('/schedule/{schedule}/asesi/{asesmen}/apl02/verify',
        [AsesorController::class, 'verifyApl02'])->name('apl02.verify');

    // Alias untuk akses langsung dari dashboard (tanpa masuk ke detail asesmen)
    Route::get('/dokumen/sk', [AsesorController::class, 'dokumentSk'])->name('dokumen.sk');

    // FR.AK.04 untuk asesor — hanya bisa akses asesmen yang dijadwalkan ke dia, dan hanya untuk preview PDF (tanpa tanda tangan)
    Route::prefix('schedule/{schedule}/asesi/{asesmen}/frak04')->name('frak04.')->group(function () {
        Route::get('/pdf', [FrAk04AsesorController::class, 'previewPdf'])->name('pdf');
    });
});

// Upload foto asesor — hanya untuk role asesor, karena terkait profile yang akan diverifikasi admin
Route::post('/profile/foto-asesor', [ProfileController::class, 'uploadFotoAsesor'])
     ->name('profile.upload-foto-asesor')
     ->middleware('role:asesor');
     

/*
|--------------------------------------------------------------------------
| Debug — hapus di production
|--------------------------------------------------------------------------
*/

Route::get('/debug-paths', function () {
    return [
        'base_path'        => base_path(),
        'public_path'      => public_path(),
        'storage_path'     => storage_path(),
        'public_exists'    => file_exists(public_path()),
        'storage_exists'   => file_exists(storage_path()),
        'index_php_exists' => file_exists(public_path('index.php')),
    ];
})->middleware('auth');